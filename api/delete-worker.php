<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// دریافت uid از ورودی
$data = json_decode(file_get_contents("php://input"), true);
$uid = trim($data['uid'] ?? '');

if ($uid === '') {
  echo json_encode(["success" => false, "message" => "شناسه uid ارسال نشده."]);
  exit;
}

// مسیر فایل
$file = __DIR__ . "/workers.json";

// بررسی وجود فایل
if (!file_exists($file)) {
  echo json_encode(["success" => false, "message" => "فایل پیدا نشد"]);
  exit;
}

// خواندن محتویات فایل
$json = file_get_contents($file);
$workers = json_decode($json, true) ?? [];

// فیلتر کردن کارگرهایی که uid متفاوت دارند
$newList = array_filter($workers, fn($w) => $w['uid'] !== $uid);

// ذخیره لیست جدید در فایل
file_put_contents($file, json_encode(array_values($newList), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo json_encode(["success" => true, "message" => "کارگر حذف شد."]);
