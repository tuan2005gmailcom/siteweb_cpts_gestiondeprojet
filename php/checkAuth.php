<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "connected" => false
    ]);
    exit;
}

echo json_encode([
    "connected" => true,
    "user_id" => $_SESSION["user_id"],
    "role" => $_SESSION["role"] ?? null
]);
?>