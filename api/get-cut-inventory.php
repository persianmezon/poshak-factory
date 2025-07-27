<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require 'firebase-init.php';

try {
  $database = $factory->createDatabase();
  $ref = $database->getReference('cut_inventory');
  $data = $ref->getValue();

  $records = [];
  if ($data && is_array($data)) {
    foreach ($data as $id => $item) {
      if (is_array($item)) {
        $records[] = array_merge($item, ['id' => $id]);
      }
    }
  }

  echo json_encode(['success' => true, 'records' => $records], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => '❌ خطا در دریافت داده‌ها',
    'error' => $e->getMessage()
  ]);
}
