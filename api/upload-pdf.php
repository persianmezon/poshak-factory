<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// بررسی اینکه فایل PDF ارسال شده
if (!isset($_FILES['pdf']) || !isset($_POST['filename'])) {
    echo json_encode([
        "success" => false,
        "message" => "فایل یا نام فایل ارسال نشده است."
    ]);
    exit;
}

$filename = basename($_POST['filename']);
$targetDir = __DIR__ . '/uploads/';
$targetPath = $targetDir . $filename;

// انتقال فایل
if (move_uploaded_file($_FILES['pdf']['tmp_name'], $targetPath)) {
    echo json_encode([
        "success" => true,
        "message" => "فایل با موفقیت ذخیره شد.",
        "path" => "api/uploads/" . $filename
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "خطا در ذخیره فایل."
    ]);
}
