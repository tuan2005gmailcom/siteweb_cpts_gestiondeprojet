<?php
header("Content-Type: application/json");
require_once "db.php";

$name   = trim($_GET['name'] ?? '');
$skill  = trim($_GET['skill'] ?? '');
$pathology = trim($_GET['pathology'] ?? '');

// Construction de la requête avec jointures
$sql = "
    SELECT DISTINCT
        p.id,
        u.username AS name,
        p.job_title,
        u.address,
        u.city,
        u.postal_code,
        u.phone,
        -- Coordonnées GPS simulées (à remplacer par de vraies données géolocalisées)
        48.3904 + (RAND() * 0.2 - 0.1) AS latitude,
        -4.4869 + (RAND() * 0.2 - 0.1) AS longitude
    FROM professionals p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN professional_specialities ps ON p.id = ps.professional_id
    LEFT JOIN specialities s ON ps.speciality_id = s.id
    LEFT JOIN speciality_pathologies sp ON s.id = sp.speciality_id
    LEFT JOIN pathologies pat ON sp.pathology_id = pat.id
    WHERE 1=1
";

$params = [];

if (!empty($name)) {
    $sql .= " AND (u.username LIKE ? OR CONCAT(u.username, ' ', u.address) LIKE ?)";
    $params[] = "%$name%";
    $params[] = "%$name%";
}

if (!empty($skill)) {
    $sql .= " AND s.name LIKE ?";
    $params[] = "%$skill%";
}

if (!empty($pathology)) {
    $sql .= " AND pat.name LIKE ?";
    $params[] = "%$pathology%";
}

$sql .= " ORDER BY u.username LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajout d'initiales pour l'avatar
foreach ($results as &$row) {
    $parts = explode(' ', $row['name'] ?? 'P');
    $row['initiales'] = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}

echo json_encode($results);