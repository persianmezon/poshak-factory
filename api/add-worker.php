<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// دریافت اطلاعات ورودی
$data = json_decode(file_get_contents("php://input"), true);
$uid = trim($data['uid'] ?? '');
$name = trim($data['name'] ?? '');

if ($uid === '' || $name === '') {
  echo json_encode(["success" => false, "message" => "نام یا uid ناقص است"]);
  exit;
}

// مسیر فایل JSON
$file = __DIR__ . "/workers.json";

// خواندن لیست قبلی
$workers = [];
if (file_exists($file)) {
  $json = file_get_contents($file);
  $workers = json_decode($json, true) ?? [];
}

// بررسی تکراری نبودن
foreach ($workers as $worker) {
  if ($worker['uid'] === $uid) {
    echo json_encode(["success" => false, "message" => "uid تکراری است"]);
    exit;
  }
}

// افزودن کارگر جدید
$workers[] = ["uid" => $uid, "name" => $name];

// ذخیره در فایل
file_put_contents($file, json_encode($workers, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo json_encode(["success" => true, "message" => "کارگر اضافه شد."]);
