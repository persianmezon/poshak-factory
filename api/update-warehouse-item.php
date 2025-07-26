<?php
require 'db.php';
$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id']);
$section = $data['section'];
$code = $data['code'];
$count = intval($data['count']);

$query = "UPDATE warehouse SET section='$section', code='$code', count=$count WHERE id=$id";
echo json_encode(["success" => mysqli_query($conn, $query)]);
?>
