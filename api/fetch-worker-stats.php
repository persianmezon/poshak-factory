<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$logFile = "batches-log.txt";
$stats = [];

if (!file_exists($logFile)) {
  echo json_encode([
    "success" => true,
    "stats" => []
  ]);
  exit;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
  $parts = explode(" | ", $line);
  if (count($parts) === 2) {
    $json = json_decode($parts[1], true);
    if ($json && isset($json['workerName']) && isset($json['quantity'])) {
      $name = $json['workerName'];
      $stats[$name]['quantity'] = ($stats[$name]['quantity'] ?? 0) + intval($json['quantity']);
      $stats[$name]['count'] = ($stats[$name]['count'] ?? 0) + 1;
    }
  }
}

echo json_encode([
  "success" => true,
  "stats" => $stats
]);
