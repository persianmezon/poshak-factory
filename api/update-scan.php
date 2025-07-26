<?php
header('Content-Type: application/json');
require 'firebase-init.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
  echo json_encode(['success' => false, 'message' => 'شناسه ارسال نشده']);
  exit;
}

// فیلدهای مجاز برای ویرایش
$allowedFields = ['section', 'part', 'code', 'count'];

$updateData = array_filter(
  $data,
  fn($key) => in_array($key, $allowedFields),
  ARRAY_FILTER_USE_KEY
);

try {
  $database = $factory->createDatabase();
  $ref = $database->getReference('qr_stats/' . $id);
  $ref->update($updateData);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
