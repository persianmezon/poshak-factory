<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $uploadDir = __DIR__ . '/pdf-archive/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['pdf']['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['pdf']['tmp_name'], $filePath)) {
        echo json_encode(['success' => true, 'path' => $filePath]);
    } else {
        echo json_encode(['success' => false, 'message' => 'خطا در ذخیره فایل']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر']);
}
