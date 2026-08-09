<?php
// د Supabase د ډاتابیس نښلونې معلومات (PostgreSQL)
$host = 'db.bwciadrnuxjowqgaflgu.supabase.co'; 
$db   = 'postgres';             
$user = 'postgres';             
$pass = 'Naqeebsafi@123';         
$port = '5432';

// د PDO په واسطه نښلول (چې د Supabase/PostgreSQL لپاره تر ټولو خوندي او په Vercel کې تر ټولو غوره لار ده)
try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    // د خطاګانو د لیدلو لپاره تنظیمات
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ د ډاتابیس نښلونې تېروتنه: " . $e->getMessage());
}
?>
