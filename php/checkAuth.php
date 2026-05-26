<?php
session_start();
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

if (isset($_SESSION["user_id"])) {
    echo json_encode([
        "connected" => true,
        "user_id" => $_SESSION["user_id"],
        "full_name" => $_SESSION["full_name"] ?? "",
        "email" => $_SESSION["email"] ?? "",
        "role" => $_SESSION["role"] ?? "patient"
    ]);
    exit;
}

echo json_encode([
    "connected" => false
]);
?>