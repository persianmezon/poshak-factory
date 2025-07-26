<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require 'firebase-init.php';

$file = __DIR__ . '/scans.json';

try {
  $database = $factory->createDatabase();
  $reference = $database->getReference('qr_stats');
  $data = $reference->getValue();

  $records = [];
  if ($data && is_array($data)) {
    foreach ($data as $id => $item) {
      if (is_array($item)) {
        $records[] = array_merge($item, ['id' => $id]);
      }
    }
  }

  // ذخیره در فایل لوکال (scans.json)
  file_put_contents($file, json_encode($records, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

  echo json_encode(['success' => true, 'records' => $records]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => '❌ خطا در دریافت داده‌ها',
    'error' => $e->getMessage()
  ]);
}
