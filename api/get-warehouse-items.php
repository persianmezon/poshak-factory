<?php
// 🔐 مشخصات اتصال به دیتابیس رو وارد کن
$host = "localhost"; // یا IP سرور
$user = "your_username";
$pass = "your_password";
$dbname = "your_database_name";

// 🔌 اتصال به دیتابیس
$conn = new mysqli($host, $user, $pass, $dbname);

// ❌ بررسی خطا
if ($conn->connect_error) {
  die("خطا در اتصال به دیتابیس: " . $conn->connect_error);
}

// 🌐 ست کردن UTF-8 برای نمایش درست فارسی
$conn->set_charset("utf8");
?>
