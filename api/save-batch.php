<?php
// تنظیم هدرها برای CORS و JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// گرفتن داده‌های JSON از درخواست
$input = json_decode(file_get_contents("php://input"), true);

// بررسی اعتبار داده‌ها
$requiredFields = ['workerName', 'partName', 'quantity', 'timestamp', 'month'];
$missing = [];

foreach ($requiredFields as $field) {
  if (empty($input[$field])) {
    $missing[] = $field;
  }
}

if (!empty($missing)) {
  echo json_encode([
    "success" => false,
    "message" => "فیلدهای الزامی ناقص هستند: " . implode(', ', $missing)
  ]);
  exit;
}

// آماده‌سازی ورودی‌ها
$workerName = trim($input['workerName']);
$partName = trim($input['partName']);
$quantity = (int) $input['quantity'];
$timestamp = $input['timestamp'];
$month = $input['month'];
$fabricType = isset($input['fabricType']) ? trim($input['fabricType']) : '';
$metersUsed = isset($input['metersUsed']) ? (float) $input['metersUsed'] : 0;
$createdBy = isset($input['createdBy']) ? trim($input['createdBy']) : 'unknown';

// آماده‌سازی متن لاگ
$logData = [
  'timestamp'   => $timestamp,
  'workerName'  => $workerName,
  'partName'    => $partName,
  'quantity'    => $quantity,
  'month'       => $month,
  'fabricType'  => $fabricType,
  'metersUsed'  => $metersUsed,
  'createdBy'   => $createdBy,
  'ip'          => $_SERVER['REMOTE_ADDR']
];

$logLine = date("Y-m-d H:i:s") . " | " . json_encode($logData, JSON_UNESCAPED_UNICODE) . "\n";

try {
  $logFile = __DIR__ . '/batches-log.txt';
  file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

  echo json_encode([
    "success" => true,
    "message" => "✅ دسته با موفقیت ذخیره شد."
  ]);
} catch (Exception $e) {
  echo json_encode([
    "success" => false,
    "message" => "خطا در نوشتن فایل لاگ: " . $e->getMessage()
  ]);
}
?>
