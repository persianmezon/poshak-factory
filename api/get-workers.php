<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$file = __DIR__ . "/workers.json";

if (!file_exists($file)) {
  echo json_encode(["success" => true, "workers" => []]);
  exit;
}

$json = file_get_contents($file);
$workers = json_decode($json, true);

if (!is_array($workers)) {
  echo json_encode(["success" => false, "message" => "خطا در خواندن فایل JSON"]);
  exit;
}

echo json_encode(["success" => true, "workers" => $workers], JSON_UNESCAPED_UNICODE);
