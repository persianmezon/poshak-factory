<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// خواندن داده‌ها از درخواست
$data = json_decode(file_get_contents("php://input"), true);

// بررسی داده‌های ضروری
if (
    !isset($data['id']) ||
    !isset($data['workerName']) ||
    !isset($data['partName']) ||
    !isset($data['quantity'])
) {
    echo json_encode([
        "success" => false,
        "message" => "داده‌های لازم برای ویرایش ناقص است."
    ]);
    exit;
}

// مسیر فایل‌ها
$logFile = "batches-log.txt";
$tempFile = "batches-log-temp.txt";

// بررسی وجود فایل
if (!file_exists($logFile)) {
    echo json_encode([
        "success" => false,
        "message" => "فایل لاگ وجود ندارد."
    ]);
    exit;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$updated = false;
$output = fopen($tempFile, "w");

// جایگزینی دسته قدیمی با اطلاعات جدید
foreach ($lines as $line) {
    $parts = explode(" | ", $line);
    if (count($parts) === 2) {
        $oldData = json_decode($parts[1], true);
        if ($oldData && isset($oldData['id']) && $oldData['id'] == $data['id']) {
            $newLine = date("Y-m-d H:i:s") . " | " . json_encode($data);
            fwrite($output, $newLine . PHP_EOL);
            $updated = true;
        } else {
            fwrite($output, $line . PHP_EOL);
        }
    } else {
        fwrite($output, $line . PHP_EOL);
    }
}

fclose($output);

// جایگزینی فایل نهایی
if ($updated) {
    rename($tempFile, $logFile);
    echo json_encode([
        "success" => true,
        "message" => "✅ دسته با موفقیت ویرایش شد."
    ]);
} else {
    unlink($tempFile);
    echo json_encode([
        "success" => false,
        "message" => "دسته‌ای با این شناسه پیدا نشد."
    ]);
}
