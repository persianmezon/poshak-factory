<?php
header('Content-Type: application/json');
require 'firebase-init.php';

$input = json_decode(file_get_contents("php://input"), true);

$worker   = $input['workerId'] ?? '';
$section  = $input['section']  ?? '';
$part     = $input['part']     ?? null;
$code     = $input['code']     ?? '';
$count    = $input['count']    ?? 0;

if (!$worker || !$section || !$code || $count <= 0) {
  echo json_encode(["success" => false, "message" => "داده ناقص است."]);
  exit;
}

try {
  $database = $factory->createDatabase();

  $data = [
    "workerId"  => $worker,
    "section"   => $section,
    "part"      => $part,
    "code"      => $code,
    "count"     => $count,
    "createdAt" => time()
  ];

  $database->getReference('qr_stats')->push($data);

  // بعد از ذخیره موفق: بروزرسانی فایل scans.json
  file_get_contents(__DIR__ . '/get-scans.php');

  echo json_encode(["success" => true]);
} catch (Exception $e) {
  echo json_encode([
    "success" => false,
    "message" => "❌ خطا در ارسال به Firebase",
    "error" => $e->getMessage()
  ]);
}
