<?php
header("Content-Type: application/json");
require_once "db.php";

$sql = "SELECT id, name FROM specialities ORDER BY name";
$stmt = $pdo->query($sql);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>