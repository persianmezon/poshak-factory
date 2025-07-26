<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// فایل لاگ یا دیتابیس
$logFile = "batches-log.txt";

if (!file_exists($logFile)) {
  echo json_encode(["success" => true, "batches" => []]);
  exit;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$batches = [];

foreach ($lines as $line) {
  $parts = explode(" | ", $line);
  if (count($parts) === 2) {
    $json = json_decode($parts[1], true);
    if ($json) $batches[] = $json;
  }
}

echo json_encode([
  "success" => true,
  "batches" => $batches
]);
