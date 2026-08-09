<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

include "config.php";

$message = "";

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    
    // د معلوماتو راایستل
    $stmt = mysqli_prepare($conn, "SELECT * FROM visa_records1_new WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) == 0){
        header("Location: admin_dashboard.php");
        exit;
    }
    
    $row = mysqli_fetch_assoc($result);
} else {
    header("Location: admin_dashboard.php");
    exit;
}

if(isset($_POST['submit'])){

    $ref      = trim($_POST['visa_reference_number'] ?? '');
    $name     = trim($_POST['full_name'] ?? '');
    $dob      = $_POST['dob'] ?? '';
    $passport = trim($_POST['passport_no'] ?? '');
    $country  = trim($_POST['passport_country'] ?? '');
    $nation   = trim($_POST['nationality'] ?? '');
    $cat      = trim($_POST['visa_category'] ?? '');
    $subcat   = trim($_POST['visa_sub_category'] ?? '');
    $type     = trim($_POST['application_type'] ?? '');
    $grant    = $_POST['visa_grant_date'] ?? '';
    $stay     = trim($_POST['staying_facility'] ?? '');
    $start    = $_POST['visa_start_date'] ?? '';
    $end      = $_POST['visa_end_date'] ?? '';
    $duration = trim($_POST['visa_duration'] ?? '');
    $status   = trim($_POST['status'] ?? '');
    $appdate  = $_POST['application_date'] ?? '';
    $sex      = trim($_POST['sex'] ?? '');

    $photo_name = $row['photo'];
    if(isset($_FILES['photo']['name']) && $_FILES['photo']['name'] != ""){
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        if(in_array(strtolower($ext), $allowed)){
            $photo_name = time() . "_" . uniqid() . "." . $ext;
            $target = "uploads/" . $photo_name;
            if(!is_dir("uploads")) mkdir("uploads", 0777, true);
            move_uploaded_file($_FILES['photo']['tmp_name'], $target);
        }
    }

    if(empty($ref) || empty($name) || empty($dob) || empty($passport) || empty($country) || empty($nation) || empty($cat) || empty($subcat) || empty($type) || empty($status) || empty($appdate) || empty($stay)) {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>❌ Please fill all required fields!</div>";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE visa_records1_new SET 
            visa_reference_number = ?, full_name = ?, dob = ?, passport_no = ?, 
            passport_country = ?, nationality = ?, visa_category = ?, visa_sub_category = ?, 
            application_type = ?, visa_grant_date = ?, staying_facility = ?, visa_start_date = ?, 
            visa_end_date = ?, visa_duration = ?, photo = ?, status = ?, application_date = ?, sex = ?
            WHERE id = ?");

        mysqli_stmt_bind_param($stmt, "ssssssssssssssssssi",
            $ref, $name, $dob, $passport, $country, $nation, $cat, $subcat, $type,
            $grant, $stay, $start, $end, $duration, $photo_name, $status, $appdate, $sex, $id
        );

        if(mysqli_stmt_execute($stmt)){
            $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>✔ Visa Record Updated Successfully!</div>";
            
            $stmt2 = mysqli_prepare($conn, "SELECT * FROM visa_records1_new WHERE id = ?");
            mysqli_stmt_bind_param($stmt2, "i", $id);
            mysqli_stmt_execute($stmt2);
            $result2 = mysqli_stmt_get_result($stmt2);
            $row = mysqli_fetch_assoc($result2);
        } else {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>❌ Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Visa | Nader System</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
<style>
    body { font-size: 15px; }
    label { font-size: 14px !important; font-weight: 600 !important; color: #1a1a1a !important; }
    input, select { font-size: 14px !important; color: #111 !important; }
    .section-title {
        font-size: 13px;
        font-weight: 700;
        color: #0b6b3a;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 2px solid #0b6b3a;
        padding-bottom: 4px;
        margin-bottom: 12px;
    }
</style>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#0b6b3a',
            }
        }
    }
}
</script>
</head>
<body class="bg-gray-100 min-h-screen py-6">

<div class="container mx-auto px-4 max-w-5xl">
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">

        <div class="flex justify-between items-center mb-6 pb-4 border-b-2 border-primary">
            <h2 class="text-2xl font-bold text-primary flex items-center gap-2">
                <i class="fa fa-pencil-square-o"></i> Edit Visa Record
            </h2>
            <a href="admin_dashboard.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>

        <?php if($message != "") echo $message; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">

            <!-- REFERENCE & NAME -->
            <div>
                <p class="section-title"><i class="fa fa-id-card"></i> Basic Information</p>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1">Reference Number <span class="text-red-500">*</span></label>
                        <input type="text" name="visa_reference_number" value="<?= htmlspecialchars($row['visa_reference_number']) ?>"
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary font-bold text-lg">
                    </div>
                    <div>
                        <label class="block mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($row['full_name']) ?>" required
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                    </div>
                </div>
            </div>

            <!-- PASSPORT -->
            <div>
                <p class="section-title"><i class="fa fa-book"></i> Passport Details</p>
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-1">Passport No <span class="text-red-500">*</span></label>
                        <input type="text" name="passport_no" value="<?= htmlspecialchars($row['passport_no']) ?>" required
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block mb-1">Passport Country <span class="text-red-500">*</span></label>
                        <input type="text" name="passport_country" value="<?= htmlspecialchars($row['passport_country']) ?>" required
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block mb-1">Nationality <span class="text-red-500">*</span></label>
                        <input type="text" name="nationality" value="<?= htmlspecialchars($row['nationality']) ?>" required
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                    </div>
                </div>
            </div>

            <!-- DOB + GENDER -->
            <div>
                <p class="section-title"><i class="fa fa-user"></i> Personal Details</p>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1">Date of Birth <span class="text-red-500">*</span></label>
                        <input type="date" name="dob" value="<?= $row['dob'] ?>" required
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block mb-1">Gender <span class="text-red-500">*</span></label>
                        <div class="flex gap-4 mt-1">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="sex" value="M" class="hidden peer" <?= ($row['sex'] == 'M') ? 'checked' : '' ?>>
                                <div class="peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 border-2 border-gray-300 rounded-lg py-2.5 flex items-center justify-center gap-2 text-gray-600 font-semibold transition-all hover:border-blue-400">
                                    <i class="fa fa-male text-lg"></i> Male
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="sex" value="F" class="hidden peer" <?= ($row['sex'] == 'F') ? 'checked' : '' ?>>
                                <div class="peer-checked:bg-pink-500 peer-checked:text-white peer-checked:border-pink-500 border-2 border-gray-300 rounded-lg py-2.5 flex items-center justify-center gap-2 text-gray-600 font-semibold transition-all hover:border-pink-400">
                                    <i class="fa fa-female text-lg"></i> Female
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VISA DETAILS -->
            <div>
                <p class="section-title"><i class="fa fa-file-text"></i> Visa Details</p>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1">Visa Category <span class="text-red-500">*</span></label>
                        <select name="visa_category" required class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                            <option value="">-- Select Category --</option>
                            <option value="Tourist/Visa" <?= ($row['visa_category']=='Tourist/Visa')?'selected':'' ?>>Tourist/Visit</option>
                            <option value="Business" <?= ($row['visa_category']=='Business')?'selected':'' ?>>Business</option>
                            <option value="Work" <?= ($row['visa_category']=='Work')?'selected':'' ?>>Work</option>
                            <option value="Medical" <?= ($row['visa_category']=='Medical')?'selected':'' ?>>Medical</option>
                            <option value="Student" <?= ($row['visa_category']=='Student')?'selected':'' ?>>Study</option>
                            <option value="Family Visa" <?= ($row['visa_category']=='Family Visa')?'selected':'' ?>>Family Visit</option>
                            <option value="Diplomatic Visa" <?= ($row['visa_category']=='Diplomatic Visa')?'selected':'' ?>>Diplomatic / Official</option>
                            <option value="Transit Visa" <?= ($row['visa_category']=='Transit Visa')?'selected':'' ?>>Transit/Visa</option>
                            <option value="Refugee" <?= ($row['visa_category']=='Refugee')?'selected':'' ?>>Refugee / Humanitarian</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">Visa Sub Category <span class="text-red-500">*</span></label>
                        <select name="visa_sub_category" required class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                            <option value="">-- Select Sub Category --</option>
                            <option value="Individual (less Than 3 Months)" <?= ($row['visa_sub_category']=='Individual (less Than 3 Months)')?'selected':'' ?>>Individual (less Than 3 Months)</option>
                            <option value="Individual" <?= ($row['visa_sub_category']=='Individual')?'selected':'' ?>>Individual</option>
                            <option value="Transit/Transport(entry)" <?= ($row['visa_sub_category']=='Transit/Transport(entry)')?'selected':'' ?>>Transit/Transport(entry)</option>
                            <option value="General" <?= ($row['visa_sub_category']=='General')?'selected':'' ?>>General</option>
                            <option value="Family Visit" <?= ($row['visa_sub_category']=='Family Visit')?'selected':'' ?>>Family Visit</option>
                            <option value="Short-Term" <?= ($row['visa_sub_category']=='Short-Term')?'selected':'' ?>>Short-Term</option>
                            <option value="Group Entry" <?= ($row['visa_sub_category']=='Group Entry')?'selected':'' ?>>Group Entry - Single Visit</option>
                            <option value="Emergency Entry" <?= ($row['visa_sub_category']=='Emergency Entry')?'selected':'' ?>>Emergency Entry - Short Stay</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- APPLICATION -->
            <div>
                <p class="section-title"><i class="fa fa-calendar"></i> Application Info</p>
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-1">Application Type <span class="text-red-500">*</span></label>
                        <select name="application_type" required class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                            <option value="">-- Select Type --</option>
                            <option value="Entry" <?= ($row['application_type']=='Entry')?'selected':'' ?>>Entry</option>
                            <option value="Extension" <?= ($row['application_type']=='Extension')?'selected':'' ?>>Extension</option>
                            <option value="Renewal" <?= ($row['application_type']=='Renewal')?'selected':'' ?>>Renewal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">Application Date <span class="text-red-500">*</span></label>
                        <input type="date" name="application_date" value="<?= $row['application_date'] ?>" required
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block mb-1">Visa Grant Date</label>
                        <input type="date" name="visa_grant_date" value="<?= $row['visa_grant_date'] ?>"
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                    </div>
                </div>
            </div>

            <!-- VALIDITY -->
            <div>
                <p class="section-title"><i class="fa fa-clock-o"></i> Validity</p>
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-1">Start Date</label>
                        <input type="date" name="visa_start_date" value="<?= $row['visa_start_date'] ?>"
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block mb-1">End Date</label>
                        <input type="date" name="visa_end_date" value="<?= $row['visa_end_date'] ?>"
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block mb-1">Duration</label>
                        <input type="text" name="visa_duration" value="<?= htmlspecialchars($row['visa_duration']) ?>" placeholder="e.g. 30 Days / 1 Year"
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                    </div>
                </div>
            </div>

            <!-- OTHER -->
            <div>
                <p class="section-title"><i class="fa fa-cog"></i> Other Details</p>
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                            <option value="Pending" <?= ($row['status']=='Pending')?'selected':'' ?>>Pending</option>
                            <option value="Approved" <?= ($row['status']=='Approved')?'selected':'' ?>>Approved</option>
                            <option value="Rejected" <?= ($row['status']=='Rejected')?'selected':'' ?>>Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">Stay / Facility <span class="text-red-500">*</span></label>
                        <select name="staying_facility" required class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none focus:border-primary">
                            <option value="">-- Select Option --</option>
                            <option value="Single Entry" <?= ($row['staying_facility']=='Single Entry')?'selected':'' ?>>Single Entry</option>
                            <option value="Multiple Entry - Upto 1 Year" <?= ($row['staying_facility']=='Multiple Entry - Upto 1 Year')?'selected':'' ?>>Multiple Entry - Upto 1 Year</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">Upload New Photo</label>
                        <input type="file" name="photo" accept="image/*"
                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg outline-none">
                        <p class="text-xs text-gray-400 mt-1">Current: <b><?= $row['photo'] ?></b></p>
                    </div>
                </div>
            </div>

            <button type="submit" name="submit"
                    class="w-full bg-primary hover:bg-green-800 text-white font-bold py-3 rounded-lg transition text-lg flex items-center justify-center gap-2">
                <i class="fa fa-save"></i> Update Visa Record
            </button>

        </form>

        <br>
        <a href="admin_dashboard.php" class="text-sm font-medium text-primary hover:underline">
            ⬅ Back to Dashboard
        </a>
    </div>
</div>

</body>
</html>