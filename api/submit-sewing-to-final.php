<?php
require 'firebase-init.php';

header('Content-Type: application/json');

$postData = json_decode(file_get_contents('php://input'), true);

// بررسی داده‌ها
if (
  !$postData ||
  !isset($postData['code'], $postData['count'], $postData['workerId'], $postData['createdAt'])
) {
  echo json_encode(['success' => false, 'message' => 'داده ناقص است']);
  exit;
}

$id = uniqid();
$data = [
  'code' => $postData['code'],
  'count' => (int) $postData['count'],
  'workerId' => $postData['workerId'],
  'createdAt' => (int) $postData['createdAt']
];

// ذخیره در مسیر Firebase → final_inventory
$database->getReference("final_inventory/{$id}")->set($data);

echo json_encode(['success' => true]);
