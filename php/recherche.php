<?php
session_start();
require_once "db.php";

header("Content-Type: application/json; charset=utf-8");

$name = trim($_GET["name"] ?? "");
$skill = trim($_GET["skill"] ?? "");
$pathology = trim($_GET["pathology"] ?? "");
$location = trim($_GET["location"] ?? "");
$available = trim($_GET["available"] ?? "");
$sort = trim($_GET["sort"] ?? "proximity");

$userLat = null;
$userLng = null;

if (isset($_SESSION["user_id"])) {
    $stmtUser = $pdo->prepare("
        SELECT latitude, longitude
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $stmtUser->execute([$_SESSION["user_id"]]);
    $currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($currentUser && $currentUser["latitude"] !== null && $currentUser["longitude"] !== null) {
        $userLat = $currentUser["latitude"];
        $userLng = $currentUser["longitude"];
    }
}

$distanceSql = "NULL AS distance_km";

if ($userLat !== null && $userLng !== null) {
    $distanceSql = "
        (
            6371 * ACOS(
                LEAST(1, GREATEST(-1,
                    COS(RADIANS(:userLat)) *
                    COS(RADIANS(p.latitude)) *
                    COS(RADIANS(p.longitude) - RADIANS(:userLng)) +
                    SIN(RADIANS(:userLat)) *
                    SIN(RADIANS(p.latitude))
                ))
            )
        ) AS distance_km
    ";
}

$sql = "
    SELECT
        p.id,
        p.first_name,
        p.last_name,
        CONCAT(p.first_name, ' ', p.last_name) AS name,
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
        $distanceSql
    FROM professionals p
    WHERE 1 = 1
";

$params = [];

if ($userLat !== null && $userLng !== null) {
    $params[":userLat"] = $userLat;
    $params[":userLng"] = $userLng;
}

if ($name !== "") {
    $sql .= " AND CONCAT(p.first_name, ' ', p.last_name) LIKE :name ";
    $params[":name"] = "%" . $name . "%";
}

if ($location !== "") {
    $sql .= " AND (
        p.city LIKE :location
        OR p.postal_code LIKE :location
        OR p.address LIKE :location
    ) ";
    $params[":location"] = "%" . $location . "%";
}

if ($available === "1") {
    $sql .= " AND p.is_available = 1 ";
}

if ($skill !== "") {
    $sql .= "
        AND EXISTS (
            SELECT 1
            FROM professional_specialities ps
            JOIN specialities s ON s.id = ps.speciality_id
            WHERE ps.professional_id = p.id
            AND s.name LIKE :skill
        )
    ";
    $params[":skill"] = "%" . $skill . "%";
}

if ($pathology !== "") {
    $sql .= "
        AND EXISTS (
            SELECT 1
            FROM professional_pathologies pp
            JOIN pathologies pa ON pa.id = pp.pathology_id
            WHERE pp.professional_id = p.id
            AND pa.name LIKE :pathology
        )
    ";
    $params[":pathology"] = "%" . $pathology . "%";
}

if ($sort === "proximity" && $userLat !== null && $userLng !== null) {
    $sql .= " ORDER BY distance_km ASC ";
} elseif ($sort === "availability") {
    $sql .= " ORDER BY p.is_available DESC, p.last_name ASC, p.first_name ASC ";
} else {
    $sql .= " ORDER BY p.last_name ASC, p.first_name ASC ";
}

$stmt = $pdo->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>