<?php
header("Content-Type: application/json");
require_once "db.php";

$sql = "SELECT id,job_title,description FROM professionals ORDER BY id";
$stmt = $pdo->query($sql);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>