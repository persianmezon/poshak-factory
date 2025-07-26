<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// اعتبارسنجی داده‌ها
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
$updated = false;
$output = fopen($tempFile, "w");

foreach ($lines as $line) {
    $parts = explode(" | ", $line);
    if (count($parts) === 2) {
        $json = json_decode($parts[1], true);
        if ($json && isset($json['id']) && $json['id'] == $data['id']) {
            // جایگزینی با اطلاعات جدید
            $newEntry = date("Y-m-d H:i:s") . " | " . json_encode($data, JSON_UNESCAPED_UNICODE);
            fwrite($output, $newEntry . PHP_EOL);
            $updated = true;
            continue;
        }
    }
    fwrite($output, $line . PHP_EOL);
}

fclose($output);

if ($updated) {
    rename($tempFile, $logFile);
    echo json_encode([
        "success" => true,
        "message" => "دسته با موفقیت ویرایش شد.",
        "data" => $data
    ]);
} else {
    unlink($tempFile);
    echo json_encode([
        "success" => false,
        "message" => "دسته مورد نظر برای ویرایش پیدا نشد."
    ]);
}
