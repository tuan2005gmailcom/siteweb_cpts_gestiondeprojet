<?php
session_start();
if (!isset($_SESSION['favoris'])) {
    $_SESSION['favoris'] = [];
}

$id = isset($_GET['professional_id']) ? (int)$_GET['professional_id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "ID invalide"]);
    exit;
}

// Toggle : si l'ID existe, on le retire, sinon on l'ajoute
$key = array_search($id, $_SESSION['favoris']);
if ($key !== false) {
    unset($_SESSION['favoris'][$key]);
    $is_fav = false;
} else {
    $_SESSION['favoris'][] = $id;
    $is_fav = true;
}

// Réindexer le tableau
$_SESSION['favoris'] = array_values($_SESSION['favoris']);

echo json_encode(["is_favorite" => $is_fav]);