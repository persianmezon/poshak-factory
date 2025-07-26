<?php
require 'db.php';
$data = json_decode(file_get_contents("php://input"), true);

$section = $data['section'];
$code = $data['code'];
$count = intval($data['count']);

$query = "INSERT INTO warehouse (section, code, count, created_at) VALUES ('$section', '$code', $count, NOW())";
echo json_encode(["success" => mysqli_query($conn, $query)]);
?>
