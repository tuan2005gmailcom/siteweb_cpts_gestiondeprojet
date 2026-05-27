<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require_once "db.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    echo json_encode([
        "success" => false,
        "message" => "ID du professionnel manquant."
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        p.id,
        p.user_id,
        p.first_name,
        p.last_name,
        CONCAT(p.first_name, ' ', p.last_name) AS full_name,
        p.job_title,
        p.description,
        p.phone,
        p.email,
        p.address,
        p.city,
        p.postal_code,
        p.latitude,
        p.longitude,
        p.is_available,
        p.created_at
    FROM professionals p
    WHERE p.id = ?
    LIMIT 1
");

$stmt->execute([$id]);
$professional = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professional) {
    echo json_encode([
        "success" => false,
        "message" => "Professionnel introuvable."
    ]);
    exit;
}

$stmtSpecialities = $pdo->prepare("
    SELECT s.id, s.name
    FROM specialities s
    JOIN professional_specialities ps
        ON ps.speciality_id = s.id
    WHERE ps.professional_id = ?
    ORDER BY s.name
");

$stmtSpecialities->execute([$id]);
$specialities = $stmtSpecialities->fetchAll(PDO::FETCH_ASSOC);

$stmtPathologies = $pdo->prepare("
    SELECT p.id, p.name
    FROM pathologies p
    JOIN professional_pathologies pp
        ON pp.pathology_id = p.id
    WHERE pp.professional_id = ?
    ORDER BY p.name
");

$stmtPathologies->execute([$id]);
$pathologies = $stmtPathologies->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "professional" => $professional,
    "specialities" => $specialities,
    "pathologies" => $pathologies
]);
exit;
?>