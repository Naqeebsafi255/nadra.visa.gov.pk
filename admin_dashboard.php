<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php"; // د PDO نښلول شوی کانفیګ

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT id, visa_reference_number, full_name, passport_no FROM visa_records1_new WHERE 1=1";

if ($search != '') {
    $sql .= " AND (full_name LIKE :search 
                OR visa_reference_number LIKE :search 
                OR passport_no LIKE :search)";
}
$sql .= " ORDER BY id DESC";

try {
    $stmt = $conn->prepare($sql);
    if ($search != '') {
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt->execute();
    }
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visa Management Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#15803d',
                        primaryDark: '#166534',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Header -->
<header class="bg-gradient-to-r from-primary to-primaryDark text-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-passport text-xl"></i>
            <h1 class="text-lg md:text-xl font-bold">Naqeeb Safi Visa System</h1>
        </div>
        <a href="admin_dashboard.php?logout=true" class="bg-white/25 hover:bg-white/35 px-3 py-1.5 rounded-lg flex items-center gap-1.5 text-sm transition-all">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span class="hidden sm:inline">Logout</span>
        </a>
    </div>
</header>

<!-- Main Content -->
<main class="container mx-auto px-3 py-5">

    <!-- Top Bar -->
    <div class="bg-white rounded-lg shadow p-4 mb-5">
        <div class="flex flex-col md:flex-row gap-3 justify-between items-stretch md:items-center">
            <a href="add.php" class="bg-primary hover:bg-primaryDark text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 font-medium transition-all">
                <i class="fa-solid fa-plus"></i> Add New Visa
            </a>

            <form method="GET" class="flex flex-1 max-w-xl gap-2">
                <div class="relative flex-1">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" placeholder="Search Name, Ref or Passport..."
                           value="<?= htmlspecialchars($search); ?>"
                           class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-primary outline-none transition-all">
                </div>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-all">Search</button>
                <?php if ($search): ?>
                <a href="admin_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-lg transition-all flex items-center">
                    <i class="fa-solid fa-xmark"></i>
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Records Table -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <i class="fa-solid fa-list-check text-primary"></i> Visa Records
            </h2>
            <span class="text-sm text-gray-500">Total: <strong class="text-primary"><?= count($results) ?></strong></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="p-3 text-left">#</th>
                        <th class="p-3 text-left">Reference No</th>
                        <th class="p-3 text-left">Full Name</th>
                        <th class="p-3 text-left">Passport No</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if(count($results) > 0): ?>
                        <?php foreach($results as $row): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-3 font-medium"><?= $row['id'] ?></td>
                            <td class="p-3 font-mono"><?= htmlspecialchars($row['visa_reference_number']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($row['full_name']) ?></td>
                            <td class="p-3 font-mono"><?= htmlspecialchars($row['passport_no']) ?></td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-1.5 justify-center">
                                    <a href="download.php?id=<?= $row['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1 rounded text-xs transition-all" title="Download PDF">
                                        <i class="fa-solid fa-file-pdf"></i>
                                        <span class="hidden md:inline"> PDF</span>
                                    </a>
                                    <a href="editvisa.php?id=<?= $row['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-black px-2.5 py-1 rounded text-xs transition-all" title="Edit Record">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span class="hidden md:inline"> Edit</span>
                                    </a>
                                    <a href="deletevisa.php?id=<?= $row['id'] ?>" class="bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded text-xs transition-all" title="Delete Record"
                                       onclick="return confirm('Are you sure you want to delete this record?');">
                                        <i class="fa-solid fa-trash"></i>
                                        <span class="hidden md:inline"> Delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">
                                <i class="fa-solid fa-folder-open text-3xl mb-2 opacity-50"></i>
                                <p class="font-medium">No records found</p>
                                <?php if ($search): ?>
                                    <a href="admin_dashboard.php" class="text-primary font-medium mt-2 inline-block">Clear search</a>
                                <?php else: ?>
                                    <a href="add.php" class="text-primary font-medium mt-2 inline-block">Add your first visa record</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Footer -->
<footer class="bg-gray-800 text-gray-300 py-4 mt-8">
    <div class="container mx-auto px-4 text-center text-sm">
        <p>© 2025 Naqeeb Safi Visa Management System | All Rights Reserved</p>
    </div>
</footer>

</body>
</html>
