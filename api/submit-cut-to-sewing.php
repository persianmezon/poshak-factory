<?php
header('Content-Type: application/json');

// اتصال به Firebase
$database = require_once 'firebase-init.php';

// دریافت داده از POST
$input = json_decode(file_get_contents('php://input'), true);

// اعتبارسنجی اولیه
if (!$input || !isset($input['code']) || !isset($input['count']) || !isset($input['createdAt'])) {
    echo json_encode(['success' => false, 'message' => 'داده ناقص است']);
    exit;
}

// آماده‌سازی داده برای ذخیره
$dataToSave = [
    'code' => $input['code'],
    'count' => intval($input['count']),
    'createdAt' => intval($input['createdAt'])
];

if (!empty($input['workerId'])) {
    $dataToSave['workerId'] = $input['workerId'];
}

// ثبت در مسیر sewing_to_final
$ref = $database->getReference('sewing_to_final')->push($dataToSave);

echo json_encode(['success' => true, 'id' => $ref->getKey()]);
