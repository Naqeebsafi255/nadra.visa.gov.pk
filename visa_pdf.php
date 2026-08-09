<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/vendor/dompdf/autoload.inc.php";
include "config.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid Request");

$stmt = $conn->prepare("SELECT * FROM visa_records1_new WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) die("Visa Not Found");

$visa = $result->fetch_assoc();

function formatDate($date) {
    if (empty($date)) return '';
    return date("d M Y", strtotime($date));
}

/* QR URL */
$visa_ref = $visa['visa_reference_number'];
$app_id   = $visa['id'];
$secret   = "SafiSuperSecretKey2026";

$pdtx = base64_encode($visa_ref);
$r    = base64_encode($app_id);
$S    = base64_encode("943");
$raw  = $visa_ref . "|" . $app_id . "|943|" . $secret;
$h    = hash('sha256', $raw);

$domain     = "http://visa.nadra.gov.pk-visa.site/e-visa/";
$verify_url = $domain . "verifyqr.php?pdtx=" . urlencode($pdtx)
            . "&h=" . $h
            . "&r=" . urlencode($r)
            . "&S=" . urlencode($S);

$qr = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verify_url);

/* Background as Base64 */
$bg_path   = __DIR__ . '/visa-bg.png';
if (file_exists($bg_path)) {
    $bg_base64 = "data:image/png;base64," . base64_encode(file_get_contents($bg_path));
} else {
    $bg_base64 = '';
}

/* Photo as Base64 */
$photo_path = __DIR__ . '/uploads/' . $visa['photo'];
if (file_exists($photo_path)) {
    $photo_ext  = strtolower(pathinfo($photo_path, PATHINFO_EXTENSION));
    $photo_mime = ($photo_ext === 'png') ? 'image/png' : 'image/jpeg';
    $photo_b64  = "data:{$photo_mime};base64," . base64_encode(file_get_contents($photo_path));
} else {
    $photo_b64 = '';
}

// --- PRECISE MRZ LOGIC ---
function calculateMRZCheckDigit(string $data): int {
    $weights = [7, 3, 1];
    $sum = 0;
    $charMap = array_merge(range("0", "9"), range("A", "Z"));
    $charValues = array_flip($charMap);
    
    foreach (str_split($data) as $i => $char) {
        $value = 0;
        if ($char === "<") {
            $value = 0;
        } elseif (isset($charValues[$char])) {
            $value = $charValues[$char];
            if ($char >= "A" && $char <= "Z") {
                $value = ord($char) - ord("A") + 10;
            }
        }
        $sum += $value * $weights[$i % 3];
    }
    return $sum % 10;
}

$nationality_code = strtoupper(substr($visa['nationality'], 0, 3));
$dob = date("ymd", strtotime($visa['dob']));
$expiry = date("ymd", strtotime($visa['visa_end_date']));

$visa_no_clean = strtoupper(preg_replace("/[^A-Z0-9]/", "", $visa['visa_reference_number']));
$visa_no_11 = str_pad(substr($visa_no_clean, 0, 11), 11, "<");

$passport_no_clean = strtoupper(preg_replace("/[^A-Z0-9]/", "", $visa['passport_no']));
$passport_no_9 = str_pad(substr($passport_no_clean, 0, 9), 9, "<");

$sex = !empty($visa['sex']) ? strtoupper(substr($visa['sex'], 0, 1)) : "M";

$full_name = strtoupper(trim($visa['full_name']));
if (strpos($full_name, ',') !== false) {
    $parts = explode(',', $full_name, 2);
    $surname = trim($parts[0]);
    $given = trim($parts[1]);
} elseif (strpos($full_name, ' ') !== false) {
    $parts = explode(' ', $full_name, 2);
    $surname = trim($parts[0]);
    $given = trim($parts[1]);
} else {
    $surname = $full_name;
    $given = "";
}

$clean_surname = str_replace(" ", "<", preg_replace("/[^A-Z ]/", "", $surname));
$clean_given = str_replace(" ", "<", preg_replace("/[^A-Z ]/", "", $given));
$mrz_name = $clean_surname . "<<" . $clean_given;

$line1_raw = "V<PAK" . $mrz_name;
$line1 = str_pad(substr($line1_raw, 0, 44), 44, "<");

$visa_cd = calculateMRZCheckDigit($visa_no_11);
$dob_cd = calculateMRZCheckDigit($dob);
$expiry_cd = calculateMRZCheckDigit($expiry);

$line2_body = $visa_no_11 . "<" . $visa_cd . $nationality_code . $dob . $dob_cd . $sex . $expiry . $expiry_cd . $passport_no_9;
$line2 = str_pad(substr($line2_body, 0, 44), 44, "<");

/* DOMPDF OPTIONS */
$options = new Options();
$options->set("isRemoteEnabled", true);
$options->set("isHtml5ParserEnabled", true);

$dompdf = new Dompdf($options);

/* HTML */
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 0; }
    body {
        margin: 0;
        padding: 0;
        width: 210mm;
        height: 297mm;
        font-family: Arial, sans-serif;
    }
    .page {
        width: 210mm;
        height: 297mm;
        position: relative;
        overflow: hidden;
    }
    .bg-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 210mm;
        height: 297mm;
        z-index: -1;
    }
    .qr {
        position: absolute;
        top: 46.5mm;
        right: 24.8mm;
        width: 21.9mm;
        height: 24mm;
    }
    .photo {
        position: absolute;
        top: 48mm;
        left: 30mm;
        width: 23mm;
        height: 26mm;
        object-fit: cover;
    }
    .field {
        position: absolute;
        font-size: 13px;
        font-family: Arial, sans-serif;
        color: #000000;
    }
    .f-app-name    { top: 76mm;    left: 30mm; }
    .f-app-date    { top: 93mm;    left: 79mm; }
    .f-ref-no      { top: 99mm;    left: 79mm; }
    .f-name        { top: 121mm;   left: 79mm; }
    .f-dob         { top: 126mm;   left: 79mm; }
    .f-nat         { top: 131mm;   left: 79mm; }
    .f-pass        { top: 136mm;   left: 79mm; }
    .f-cat         { top: 155mm;   left: 79mm; }
    .f-sub         { top: 160mm;   left: 79mm; }
    .f-type        { top: 165mm;   left: 79mm; }
    .f-grant       { top: 170.5mm; left: 79mm; }
    .f-country     { top: 175.5mm; left: 79mm; }
    .f-facility    { top: 180.5mm; left: 79mm; }
    .f-start       { top: 186mm;   left: 79mm; }
    .f-end         { top: 191mm;   left: 79mm; }
    .f-dur         { top: 196mm;   left: 79mm; }
    .f-bottom-date { top: 265mm;   left: 30mm; }
    
    .mrz-box {
        position: absolute;
        top: 235mm;
        left: 50.5mm;
        width: 190mm;
        font-family: "Courier New", Courier, monospace;
        font-size: 10.5pt;
        font-weight: bold;
        letter-spacing: 1.85px;
        color: #000000;
        line-height: 1.85;
    }
</style>
</head>
<body>
<div class="page">

    <!-- Background Image Fixed for High Quality -->
    <img src="' . $bg_base64 . '" class="bg-image">

    <img src="' . $qr . '" class="qr">
    ' . ($photo_b64 ? '<img src="' . $photo_b64 . '" class="photo">' : '') . '

    <div class="field f-app-name">'    . htmlspecialchars($visa['full_name'])             . '</div>
    <div class="field f-app-date">'    . htmlspecialchars($visa['application_date'])      . '</div>
    <div class="field f-ref-no">'      . htmlspecialchars($visa['visa_reference_number']) . '</div>
    <div class="field f-name">'        . htmlspecialchars($visa['full_name'])             . '</div>
    <div class="field f-dob">'         . formatDate($visa['dob'])                         . '</div>
    <div class="field f-nat">'         . htmlspecialchars($visa['nationality'])           . '</div>
    <div class="field f-pass">'        . htmlspecialchars($visa['passport_no'])           . '</div>
    <div class="field f-cat">'         . htmlspecialchars($visa['visa_category'])         . '</div>
    <div class="field f-sub">'         . htmlspecialchars($visa['visa_sub_category'])     . '</div>
    <div class="field f-type">'        . htmlspecialchars($visa['application_type'])      . '</div>
    <div class="field f-grant">'       . formatDate($visa['visa_grant_date'])             . '</div>
    <div class="field f-country">'     . htmlspecialchars($visa['passport_country'])      . '</div>
    <div class="field f-facility">'    . htmlspecialchars($visa['staying_facility'])      . '</div>
    <div class="field f-start">'       . formatDate($visa['visa_start_date'])             . '</div>
    <div class="field f-end">'         . formatDate($visa['visa_end_date'])               . '</div>
    <div class="field f-dur">'         . htmlspecialchars($visa['visa_duration'])         . '</div>
    <div class="field f-bottom-date">' . formatDate($visa['visa_grant_date'])             . '</div>

    <div class="mrz-box">
        <div>' . htmlspecialchars($line1) . '</div>
        <div>' . htmlspecialchars($line2) . '</div>
    </div>

</div>
</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("visa_" . $visa['visa_reference_number'] . ".pdf", ["Attachment" => true]);
exit;
?>