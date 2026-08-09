<?php
include "config.php";

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM visa_records1_new WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Visa Not Found");
}

$visa = $result->fetch_assoc();
function formatDate($date) {
    if (empty($date)) return '';
    return date("d M Y", strtotime($date));
}

/* ------------------------------
   Generate MFA-Style Long Verify URL
--------------------------------*/

$visa_ref = $visa['visa_reference_number'];
$app_id   = $visa['id'];
$secret   = "SafiSuperSecretKey2026";

$pdtx = base64_encode($visa_ref);
$r    = base64_encode($app_id);
$S    = base64_encode("943");

$raw  = $visa_ref . "|" . $app_id . "|" . "943" . "|" . $secret;
$h    = hash('sha256', $raw);

$domain = "https://visa.nadra.gvo.pk-visas.online/e-visa/";

$verify_url = $domain . "verifyqr.php?pdtx=" . urlencode($pdtx)
            . "&h=" . $h
            . "&r=" . urlencode($r)
            . "&S=" . urlencode($S);

$qr = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verify_url);

// MRZ Line
$name_part = strtoupper(str_replace(" ", "<", $visa['full_name']));
$line1 = str_pad(substr("V<PAK" . $name_part, 0, 44), 44, "<");
$line2 = str_pad(substr(
    $visa['visa_reference_number'] . "<3" .
    strtoupper(substr($visa['nationality'], 0, 3)) .
    date("ymd", strtotime($visa['dob'])) . "M" .
    date("ymd", strtotime($visa['visa_end_date'])) .
    $visa['passport_no'] . "<<<<", 0, 44), 44, "<");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Visa Grant Notice</title>

<style>
.page {
    width: 750px;
    height: 864px;
    margin: auto;
    background-image: url('https://visa.nadra.gov.pk-visa.site/visa-bg.jpg');
    background-size: 100% 100%;
    background-repeat: no-repeat;
    position: relative;
    font-family: Arial, sans-serif;
}

/* QR Code */
.qr {
    position: absolute;
    top: 105px;
    right: 40px;
    width: 100px;
    height: 100px;
}

/* Applicant Photo */
.photo {
    position: absolute;
    top: 105px;
    left: 60px;
    width: 90px;
    height: 100px;
    object-fit: cover;
}

/* Fields */
.field {
    position: absolute;
    font-size: 14px;
}

/* Application Details */
.f1 { top: 255px; left: 260px; }
.f2 { top: 280px; left: 260px; }

/* Applicant Details */
.f3  { top: 210px; left: 65px; }
.f18 { top: 355px; left: 265px; }
.f4  { top: 373px; left: 265px; }
.f5  { top: 390px; left: 265px; }
.f6  { top: 405px; left: 265px; }

/* Visa Grant Details */
.f7  { top: 471px; left: 265px; }
.f8  { top: 486px; left: 265px; }
.f9  { top: 503px; left: 265px; }
.f10 { top: 521px; left: 265px; }
.f11 { top: 536px; left: 265px; }
.f12 { top: 554px; left: 265px; }
.f13 { top: 570px; left: 265px; }
.f14 { top: 588px; left: 265px; }
.f15 { top: 840px; left: 65px; }
.f16 { top: 604px; left: 265px; }
.f17 { top: 721px; left: 265px; }

/* MRZ Line */
.mrz {
    position: absolute;
    bottom:80px;
    left: 175px;
    font-size: 11px;
    font-family: "Courier New", monospace;
    letter-spacing: 1.5px;
    line-height: 18px;
    color: #000;
}
</style>

</head>
<body>

<div class="page">

    <!-- QR Code -->
    <img src="<?= $qr ?>" class="qr">

    <!-- Applicant Photo -->
    <img src="uploads/<?= $visa['photo'] ?>" class="photo">

    <!-- Application Details -->
    <div class="field f1"><?= htmlspecialchars($visa['application_date']) ?></div>
    <div class="field f2"><?= htmlspecialchars($visa['visa_reference_number']) ?></div>

   <!-- Applicant Details -->
<div class="field f18"><?= htmlspecialchars($visa['full_name']) ?></div>
<div class="field f3"><?= htmlspecialchars($visa['full_name']) ?></div>
<div class="field f4"><?= formatDate($visa['dob']) ?></div>
<div class="field f5"><?= htmlspecialchars($visa['nationality']) ?></div>
<div class="field f6"><?= htmlspecialchars($visa['passport_no']) ?></div>

<!-- Visa Grant Details -->
<div class="field f7"><?= htmlspecialchars($visa['visa_category']) ?></div>
<div class="field f8"><?= htmlspecialchars($visa['visa_sub_category']) ?></div>
<div class="field f9"><?= htmlspecialchars($visa['application_type']) ?></div>
<div class="field f10"><?= formatDate($visa['visa_grant_date']) ?></div>
<div class="field f11"><?= htmlspecialchars($visa['passport_country']) ?></div>
<div class="field f12"><?= htmlspecialchars($visa['staying_facility']) ?></div>
<div class="field f13"><?= formatDate($visa['visa_start_date']) ?></div>
<div class="field f14"><?= formatDate($visa['visa_end_date']) ?></div>
<div class="field f15"><?= formatDate($visa['visa_start_date']) ?></div>
<div class="field f16"><?= htmlspecialchars($visa['visa_duration']) ?></div>

    <!-- MRZ -->
<div class="mrz">
    <?= htmlspecialchars($line1) ?><br>
    <?= htmlspecialchars($line2) ?>
</div>

    <!-- Download PDF Button -->
    <a href="visa_pdf.php?id=<?= $visa['id']; ?>" 
       style="
           position:absolute;
           bottom:20px;
           left:20px;
           padding:10px 20px;
           background:#0b6b3a;
           color:white;
           font-size:16px;
           border-radius:5px;
           text-decoration:none;
           font-weight:bold;
       ">
        Download Visa PDF
    </a>

</div>

</body>
</html>