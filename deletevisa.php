<?php
session_start();

// د اډمین د لاګین تصدیق
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

include "config.php";

$id = $_GET['id'] ?? null;

if ($id) {
    // د خوندیتوب لپاره د پریسپیورډ سټېټمنټ (Prepared Statement) کارول
    $stmt = $conn->prepare("DELETE FROM visa_records1_new WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // د حذف کیدو وروسته بیرته ډشبورډ ته لیږل
        header("Location: admin_dashboard.php?msg=deleted");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: admin_dashboard.php");
    exit;
}
?>