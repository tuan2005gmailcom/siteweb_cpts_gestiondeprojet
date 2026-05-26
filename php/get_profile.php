<?php
session_start();
header("Content-Type: application/json");
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Non connecté"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupération des données utilisateur
$stmt = $pdo->prepare("SELECT id, username, email, phone, address, city, postal_code, role, professional_number, description, restreindre_infos FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["error" => "Utilisateur introuvable"]);
    exit;
}

// Récupération des compétences (si professionnel)
$competences = [];
if ($user['role'] === 'medecin' || $user['role'] === 'contributeur') {
    $stmt2 = $pdo->prepare("SELECT s.name FROM specialities s
                            JOIN professional_specialities ps ON s.id = ps.speciality_id
                            JOIN professionals p ON ps.professional_id = p.id
                            WHERE p.user_id = ?");
    $stmt2->execute([$user_id]);
    $competences = $stmt2->fetchAll(PDO::FETCH_COLUMN);
}

$user['competences'] = $competences;
echo json_encode($user);