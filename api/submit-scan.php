<?php
header('Content-Type: application/json');
require 'firebase-init.php';

$input = json_decode(file_get_contents("php://input"), true);

$worker   = $input['workerId'] ?? '';
$section  = $input['section']  ?? '';
$part     = $input['part']     ?? null;
$code     = $input['code']     ?? '';
$count    = intval($input['count'] ?? 0);

// بررسی نیاز به کارگر
$requiresWorker = !in_array($section, ['خروج از برش', 'نهایی‌کار']);

// اعتبارسنجی
if ($requiresWorker && !$worker) {
  echo json_encode(["success" => false, "message" => "کارگر مشخص نشده است."]);
  exit;
}

if (!$section || !$code || $count <= 0) {
  echo json_encode(["success" => false, "message" => "داده ناقص است."]);
  exit;
}

try {
  $database = $factory->createDatabase();
  $now = time();

  // فقط اگر بخش "سالن دوخت" است → بررسی اسکن دوم
  if ($section === 'سالن دوخت') {
    $all = $database->getReference('qr_stats')->getValue();

    if ($all) {
      foreach ($all as $key => $record) {
        if (
          ($record['code'] ?? '') === $code &&
          ($record['workerId'] ?? '') === $worker &&
          ($record['section'] ?? '') === $section &&
          !isset($record['endTime'])
        ) {
          // رکوردی با اسکن اول پیدا شد → ثبت پایان کار
          $database->getReference('qr_stats/' . $key)->update([
            'endTime' => $now
          ]);

          echo json_encode(["success" => true, "message" => "⏱ پایان کار ثبت شد"]);
          exit;
        }
      }
    }
  }

  // در همه حالت‌های دیگر → ثبت شروع کار یا اسکن معمولی
  $data = [
    "workerId"  => $worker,
    "section"   => $section,
    "part"      => $part,
    "code"      => $code,
    "count"     => $count,
    "createdAt" => $now
  ];

  $database->getReference('qr_stats')->push($data);

  // بروزرسانی کش
  file_get_contents(__DIR__ . '/get-scans.php');

  echo json_encode(["success" => true, "message" => "▶️ شروع کار ثبت شد"]);
} catch (Exception $e) {
  echo json_encode([
    "success" => false,
    "message" => "❌ خطا در ارسال به Firebase",
    "error" => $e->getMessage()
  ]);
}
