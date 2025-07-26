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

// ✅ اگه به اینجا رسید یعنی اتصال موفق بوده
