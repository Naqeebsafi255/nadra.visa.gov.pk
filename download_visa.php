<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/dompdf/autoload.inc.php";
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

$domain     = "https://visa.nadra.gov.pk-visa.site/e-visa/";
$verify_url = $domain . "verifyqr.php?pdtx=" . urlencode($pdtx)
            . "&h=" . $h
            . "&r=" . urlencode($r)
            . "&S=" . urlencode($S);

$qr = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verify_url);

/* Background as Base64 */
$bg_path   = __DIR__ . '/visa-bg.png';
$bg_base64 = "data:image/png;base64," . base64_encode(file_get_contents($bg_path));

/* Photo as Base64 */
$photo_path = __DIR__ . '/uploads/' . $visa['photo'];
if (file_exists($photo_path)) {
    $photo_ext  = strtolower(pathinfo($photo_path, PATHINFO_EXTENSION));
    $photo_mime = ($photo_ext === 'png') ? 'image/png' : 'image/jpeg';
    $photo_b64  = "data:{$photo_mime};base64," . base64_encode(file_get_contents($photo_path));
} else {
    $photo_b64 = '';
}

/* MRZ LOGIC */
function calculateMRZCheckDigit(string $data): int {
    $weights = [7, 3, 1];
    $sum = 0;
    foreach (str_split($data) as $i => $char) {
        if ($char === "<") $value = 0;
        elseif ($char >= "0" && $char <= "9") $value = intval($char);
        elseif ($char >= "A" && $char <= "Z") $value = ord($char) - ord("A") + 10;
        else $value = 0;
        $sum += $value * $weights[$i % 3];
    }
    return $sum % 10;
}

$nationality_code   = strtoupper(substr($visa['nationality'], 0, 3));
$dob                = date("ymd", strtotime($visa['dob']));
$expiry             = date("ymd", strtotime($visa['visa_end_date']));
$visa_no            = strtoupper(preg_replace("/[^A-Z0-9]/", "", $visa['visa_reference_number']));
$visa_no_padded     = str_pad(substr($visa_no, 0, 9), 9, "<");
$passport_no        = strtoupper(preg_replace("/[^A-Z0-9]/", "", $visa['passport_no']));
$passport_no_padded = str_pad(substr($passport_no, 0, 14), 14, "<");
$sex                = !empty($visa['sex']) ? strtoupper(substr($visa['sex'], 0, 1)) : "M";

$full_name = strtoupper(trim($visa['full_name']));
if (strpos($full_name, ',') !== false) {
    $parts = explode(',', $full_name, 2);
    $surname = trim($parts[0]);
    $given   = trim($parts[1]);
} elseif (strpos($full_name, ' ') !== false) {
    $parts = explode(' ', $full_name, 2);
    $surname = trim($parts[0]);
    $given   = trim($parts[1]);
} else {
    $surname = $full_name;
    $given   = "";
}

$clean_surname = str_replace(" ", "<", preg_replace("/[^A-Z ]/", "", $surname));
$clean_given   = str_replace(" ", "<", preg_replace("/[^A-Z ]/", "", $given));
$mrz_name      = $clean_surname . "<<" . $clean_given;
$line1         = "V<PAK" . str_pad(substr($mrz_name, 0, 39), 39, "<");

$visa_cd     = calculateMRZCheckDigit($visa_no_padded);
$dob_cd      = calculateMRZCheckDigit($dob);
$expiry_cd   = calculateMRZCheckDigit($expiry);
$passport_cd = calculateMRZCheckDigit($passport_no_padded);

$line2_raw = $visa_no_padded . $visa_cd . $nationality_code . $dob . $dob_cd . $sex . $expiry . $expiry_cd . $passport_no_padded . $passport_cd;
$line2     = str_pad(substr($line2_raw, 0, 44), 44, "<");

/* MRZ GD Image */
function createMRZImage($line1, $line2) {
    $width  = 2800;
    $height = 160;
    $img    = imagecreatetruecolor($width, $height);
    $white  = imagecolorallocate($img, 255, 255, 255);
    $black  = imagecolorallocate($img, 0, 0, 0);
    imagefill($img, 0, 0, $white);

    $fonts = [
        '/usr/share/fonts/truetype/liberation/LiberationMono-Regular.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSansMono.ttf',
        '/usr/share/fonts/dejavu/DejaVuSansMono.ttf',
        '/usr/share/fonts/truetype/freefont/FreeMono.ttf',
        '/usr/share/fonts/liberation/LiberationMono-Regular.ttf',
        '/usr/share/fonts/truetype/ubuntu/UbuntuMono-R.ttf',
        '/usr/share/fonts/truetype/courier-prime/CourierPrime-Regular.ttf',
    ];

    $font = null;
    foreach ($fonts as $f) {
        if (file_exists($f)) {
            $font = $f;
            break;
        }
    }

    if ($font) {
        imagettftext($img, 28, 0, 0, 60,  $black, $font, $line1);
        imagettftext($img, 28, 0, 0, 130, $black, $font, $line2);
    } else {
        imagestring($img, 5, 0, 5,  $line1, $black);
        imagestring($img, 5, 0, 85, $line2, $black);
    }

    ob_start();
    imagepng($img, null, 0);
    $data = ob_get_clean();
    imagedestroy($img);

    return "data:image/png;base64," . base64_encode($data);
}

$mrz_img = createMRZImage($line1, $line2);

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
        background-image: url("' . $bg_base64 . '");
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden;
    }
    .qr {
        position: absolute;
        top: 47mm;
        right: 17.5mm;
        width: 24mm;
        height: 24mm;
    }
    .photo {
        position: absolute;
        top: 45mm;
        left: 25mm;
        width: 25mm;
        height: 30mm;
        object-fit: cover;
    }
    .field {
        position: absolute;
        font-size: 13px;
        font-family: Arial, sans-serif;
        color: #000000;
    }
    .f-app-name    { top: 76mm;    left: 24mm; }
    .f-app-date    { top: 93mm;    left: 77mm; }
    .f-ref-no      { top: 99mm;    left: 77mm; }
    .f-name        { top: 121mm;   left: 77mm; }
    .f-dob         { top: 126mm;   left: 77mm; }
    .f-nat         { top: 131mm;   left: 77mm; }
    .f-pass        { top: 136mm;   left: 77mm; }
    .f-cat         { top: 155mm;   left: 77mm; }
    .f-sub         { top: 160mm;   left: 77mm; }
    .f-type        { top: 165mm;   left: 77mm; }
    .f-grant       { top: 170.5mm; left: 77mm; }
    .f-country     { top: 175.5mm; left: 77mm; }
    .f-facility    { top: 180.5mm; left: 77mm; }
    .f-start       { top: 186mm;   left: 77mm; }
    .f-end         { top: 191mm;   left: 77mm; }
    .f-dur         { top: 196mm;   left: 77mm; }
    .f-bottom-date { top: 265mm;   left: 24mm; }
    .mrz-box {
        position: absolute;
        top: 272mm;
        left: 10mm;
        width: 190mm;
        height: 12mm;
    }
</style>
</head>
<body>
<div class="page">

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
        <img src="' . $mrz_img . '" style="width:190mm; height:12mm;">
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