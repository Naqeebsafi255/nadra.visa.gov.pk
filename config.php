<?php
$host = 'localhost';
$username = 'pkvisasc_pkvisasc';
$password = 'Naqeebsafi@12345';
$database = 'pkvisasc_visas';

// د MySQLi په واسطه نښلول
$conn = mysqli_connect($host, $username, $password, $database);

// د نښلیدو د تېروتنې چک کول
if (!$conn) {
    die("د ډیټابیس سره د نښلیدو ستونزه: " . mysqli_connect_error());
}

// د پښتو او عربي تورو د سمې لوستلو لپاره
mysqli_set_charset($conn, "utf8mb4");