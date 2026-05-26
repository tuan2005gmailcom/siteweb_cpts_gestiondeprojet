<?php
header("Content-Type: application/json; charset=utf-8");
require_once "db.php";

$name = trim($_GET["name"] ?? "");
$skill = trim($_GET["skill"] ?? "");
$pathology = trim($_GET["pathology"] ?? "");
$location = trim($_GET["location"] ?? "");
$available = trim($_GET["available"] ?? "");

$sql = "
    SELECT DISTINCT
        p.id,
        CONCAT(p.first_name, ' ', p.last_name) AS name,
        UPPER(CONCAT(LEFT(p.first_name, 1), LEFT(p.last_name, 1))) AS initiales,
        p.first_name,
        p.last_name,
        p.job_title,
        p.description,
        p.phone,
        p.email,
        p.address,
        p.city,
        p.postal_code,
        p.latitude,
        p.longitude,
        p.is_available
    FROM professionals p

    LEFT JOIN professional_specialities ps
        ON p.id = ps.professional_id

    LEFT JOIN specialities s
        ON ps.speciality_id = s.id

    LEFT JOIN professional_pathologies pp
        ON p.id = pp.professional_id

    LEFT JOIN pathologies pa
        ON pp.pathology_id = pa.id

    WHERE 1 = 1
";

$params = [];

if ($name !== "") {
    $sql .= "
        AND (
            p.first_name LIKE ?
            OR p.last_name LIKE ?
            OR CONCAT(p.first_name, ' ', p.last_name) LIKE ?
        )
    ";

    $likeName = "%" . $name . "%";
    $params[] = $likeName;
    $params[] = $likeName;
    $params[] = $likeName;
}

if ($skill !== "") {
    $sql .= "
        AND (
            p.job_title LIKE ?
            OR s.name LIKE ?
        )
    ";

    $likeSkill = "%" . $skill . "%";
    $params[] = $likeSkill;
    $params[] = $likeSkill;
}

if ($pathology !== "") {
    $sql .= " AND pa.name LIKE ? ";

    $likePathology = "%" . $pathology . "%";
    $params[] = $likePathology;
}

if ($location !== "") {
    $sql .= "
        AND (
            p.city LIKE ?
            OR p.postal_code LIKE ?
            OR p.address LIKE ?
        )
    ";

    $likeLocation = "%" . $location . "%";
    $params[] = $likeLocation;
    $params[] = $likeLocation;
    $params[] = $likeLocation;
}

if ($available === "1") {
    $sql .= " AND p.is_available = 1 ";
}

$sql .= " ORDER BY p.last_name, p.first_name ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
?>