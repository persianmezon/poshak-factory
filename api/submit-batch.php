<?php
// تنظیم هدرهای مورد نیاز برای CORS و JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// گرفتن داده‌ها از درخواست
$data = json_decode(file_get_contents("php://input"), true);

// بررسی داده‌های ضروری
if (
    isset($data['workerName']) &&
    isset($data['partName']) &&
    isset($data['quantity'])
) {
    // فرض می‌کنیم همه چیز درسته و دسته با موفقیت ذخیره شده

    // می‌تونی در اینجا اطلاعات رو داخل دیتابیس یا فایل ذخیره کنی

    echo json_encode([
        "success" => true,
        "message" => "دسته با موفقیت ثبت شد.",
        "data" => $data
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "داده‌های ارسال شده ناقص است."
    ]);
}
