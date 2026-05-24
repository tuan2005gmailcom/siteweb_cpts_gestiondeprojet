<?php
header("Content-Type: application/json");
require_once "db.php";

$speciality_id = $_GET["speciality_id"] ?? null;

if ($speciality_id) {
    $sql = "
        SELECT p.id, p.name
        FROM pathologies p
        JOIN speciality_pathologies sp ON p.id = sp.pathology_id
        WHERE sp.speciality_id = ?
        ORDER BY p.name
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$speciality_id]);
} else {
    $sql = "SELECT id, name FROM pathologies ORDER BY name";
    $stmt = $pdo->query($sql);
}

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>