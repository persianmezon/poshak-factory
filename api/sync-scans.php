<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require __DIR__ . '/firebase-init.php'; // اتصال به Firebase

use Kreait\Firebase\Database;

$db = $firebase->getDatabase();

try {
  $snapshot = $db->getReference('qr_stats')->getValue();

  $records = [];
  if ($snapshot && is_array($snapshot)) {
    foreach ($snapshot as $key => $data) {
      $records[] = array_merge($data, ['id' => $key]);
    }
  }

  file_put_contents(__DIR__ . '/scans.json', json_encode($records, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

  echo json_encode(["success" => true, "message" => "scans.json بروزرسانی شد", "count" => count($records)]);
} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
