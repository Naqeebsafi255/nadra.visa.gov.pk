<?php
session_start(); // مهم! د سیشن فعالول

include "config.php";

$message = "";

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ثابت Admin Login
    if($username === "admin" && $password === "admin123"){
        
        $_SESSION['admin'] = $username;

        // اډمین ډیشبورډ ته اشاره
        header("Location: admin_dashboard.php");
        exit;

    } else {
        $message = "❌ غلط Username یا Password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    background: #eef1f5;
    font-family: Arial;
}
.box {
    width: 90%;
    max-width: 400px;
    margin: auto;
    margin-top: 80px;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 0 15px rgba(0,0,0,0.2);
}
input {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border-radius: 8px;
    border: 1px solid #bbb;
}
button {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 17px;
}
.error { color: red; text-align: center; }
</style>
</head>

<body>

<div class="box">
    <h2 style="text-align:center;">Admin Login</h2>

    <?php if($message != ""){ ?>
        <p class="error"><?= $message ?></p>
    <?php } ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="login">Login</button>
    </form>
</div>

</body>
</html>