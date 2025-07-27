<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require 'firebase-init.php';

try {
  $input = json_decode(file_get_contents('php://input'), true);

  if (
    !isset($input['code']) ||
    !isset($input['count']) ||
    !isset($input['workerId']) ||
    !isset($input['createdAt'])
  ) {
    echo json_encode([
      'success' => false,
      'message' => 'اطلاعات ناقص است'
    ]);
    exit;
  }

  $database = $factory->createDatabase();
  $ref = $database->getReference('sewing_to_final')->push([
    'code' => $input['code'],
    'count' => intval($input['count']),
    'workerId' => $input['workerId'],
    'createdAt' => intval($input['createdAt']),
    'part' => $input['part'] ?? null
  ]);

  echo json_encode([
    'success' => true,
    'message' => 'با موفقیت در نهایی‌کار ثبت شد',
    'id' => $ref->getKey()
  ]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'خطا در ثبت اطلاعات نهایی‌کار',
    'error' => $e->getMessage()
  ]);
}
