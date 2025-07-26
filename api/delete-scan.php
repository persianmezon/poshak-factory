<?php
header('Content-Type: application/json');
require 'firebase-init.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
  echo json_encode(['success' => false, 'message' => 'شناسه ارسال نشده']);
  exit;
}

try {
  $database = $factory->createDatabase();
  $ref = $database->getReference('qr_stats/' . $id);
  $ref->remove();

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
