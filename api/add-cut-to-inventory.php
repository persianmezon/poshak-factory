<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require 'firebase-init.php';

try {
  $input = json_decode(file_get_contents('php://input'), true);

  if (
    !isset($input['code']) || 
    !isset($input['count']) || 
    !isset($input['part']) ||
    !isset($input['createdAt'])
  ) {
    echo json_encode(['success' => false, 'message' => 'اطلاعات ناقص است']);
    exit;
  }

  $database = $factory->createDatabase();
  $ref = $database->getReference('cut_inventory')->push([
    'section' => 'برش',
    'code' => $input['code'],
    'part' => $input['part'],
    'count' => $input['count'],
    'createdAt' => $input['createdAt']
  ]);

  echo json_encode(['success' => true, 'id' => $ref->getKey()]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'خطا در ثبت اطلاعات',
    'error' => $e->getMessage()
  ]);
}
