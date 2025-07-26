<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "شناسه دسته برای حذف ارسال نشده است."
    ]);
    exit;
}

$logFile = "batches-log.txt";
$tempFile = "batches-log-temp.txt";

if (!file_exists($logFile)) {
    echo json_encode([
        "success" => false,
        "message" => "فایل لاگ وجود ندارد."
    ]);
    exit;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$deleted = false;
$output = fopen($tempFile, "w");

foreach ($lines as $line) {
    $parts = explode(" | ", $line);
    if (count($parts) === 2) {
        $json = json_decode($parts[1], true);
        if ($json && isset($json['id']) && $json['id'] == $data['id']) {
            // حذف این خط
            $deleted = true;
            continue;
        }
    }
    fwrite($output, $line . PHP_EOL);
}

fclose($output);

if ($deleted) {
    rename($tempFile, $logFile);
    echo json_encode([
        "success" => true,
        "message" => "دسته با موفقیت حذف شد."
    ]);
} else {
    unlink($tempFile);
    echo json_encode([
        "success" => false,
        "message" => "دسته مورد نظر برای حذف پیدا نشد."
    ]);
}
