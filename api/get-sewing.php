<?php
header('Content-Type: application/json');

// دریافت دیتابیس از فایل firebase-init.php
$db = require_once 'firebase-init.php';

try {
    // 1. واکشی داده‌ها از مسیر cut_to_sewing
    $snapshot = $db->getReference('cut_to_sewing')->getSnapshot();
    $data = $snapshot->getValue();

    $result = [];
    if ($data) {
        foreach ($data as $id => $item) {
            $result[] = [
                'id' => $id,
                'section' => $item['section'] ?? '',
                'code' => $item['code'] ?? '',
                'part' => $item['part'] ?? '',
                'count' => $item['count'] ?? 0,
                'workerId' => $item['workerId'] ?? '',
                'createdAt' => $item['createdAt'] ?? null,
            ];
        }
    }

    echo json_encode(['success' => true, 'records' => $result]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'خطا در دریافت داده‌ها: ' . $e->getMessage()
    ]);
}

