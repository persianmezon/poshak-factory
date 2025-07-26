<?php
require 'db.php';
$id = intval($_GET['id']);
$query = "DELETE FROM warehouse WHERE id = $id";
echo json_encode(["success" => mysqli_query($conn, $query)]);
?>
