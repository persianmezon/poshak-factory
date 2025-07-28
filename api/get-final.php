<?php
require 'firebase-init.php';

header('Content-Type: application/json');

try {
  $snapshot = $database->getReference('final_inventory')->getSnapshot();

  $data = $snapshot->getValue();

  $records = [];

  if ($data && is_array($data)) {
    foreach ($data as $id => $entry) {
      $records[] = [
        'id' => $id,
        'code' => $entry['code'] ?? '',
        'count' => (int) ($entry['count'] ?? 0),
        'workerId' => $entry['workerId'] ?? '',
        'createdAt' => isset($entry['createdAt']) ? (int) $entry['createdAt'] : null
      ];
    }
  }

  echo json_encode(['success' => true, 'records' => $records]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
