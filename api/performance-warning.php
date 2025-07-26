<?php
// هدرهای CORS و JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// خواندن داده‌ها
$data = json_decode(file_get_contents("php://input"), true);

// چک کردن فیلدهای موردنیاز
if (
    isset($data['workerName']) &&
    isset($data['dropPercent']) &&
    isset($data['month']) &&
    isset($data['timestamp'])
) {
    // اینجا معمولاً باید در دیتابیس هشدار ذخیره بشه

    // فعلاً پاسخ تستی
    echo json_encode([
        "success" => true,
        "message" => "هشدار افت عملکرد ثبت شد.",
        "data" => $data
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "برخی اطلاعات هشدار ناقص هستند."
    ]);
}
