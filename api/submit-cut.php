<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require 'firebase-init.php';

try {
  // بررسی مقادیر ورودی
  $input = json_decode(file_get_contents('php://input'), true);

  if (
    !isset($input['code']) ||
    !isset($input['count']) ||
    !isset($input['type']) ||  // مثل آستر یا اصلی یا نوع اسکن مثل exit_cut
    !isset($input['createdAt'])
  ) {
    echo json_encode([
      'success' => false,
      'message' => 'اطلاعات ناقص است'
    ]);
    exit;
  }

  // اتصال به دیتابیس
  $database = $factory->createDatabase();

  // مسیر ذخیره‌سازی بر اساس نوع
  if (isset($input['scanType']) && $input['scanType'] === 'exit_cut') {
    // اگر اسکن خروج از انبار برش بود
    $ref = $database->getReference('cut_to_sewing')->push([
      'code' => $input['code'],
      'count' => intval($input['count']),
      'part' => $input['type'],
      'createdAt' => $input['createdAt']
    ]);
  } else {
    // حالت پیش‌فرض: ذخیره در cut_inventory
    $ref = $database->getReference('cut_inventory')->push([
      'code' => $input['code'],
      'count' => intval($input['count']),
      'part' => $input['type'],
      'createdAt' => $input['createdAt']
    ]);
  }

  echo json_encode([
    'success' => true,
    'message' => 'با موفقیت ثبت شد',
    'id' => $ref->getKey()
  ]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'خطا در ثبت اطلاعات',
    'error' => $e->getMessage()
  ]);
}
