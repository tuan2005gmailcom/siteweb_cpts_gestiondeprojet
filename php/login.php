<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require_once "db.php";

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Veuillez remplir l'email et le mot de passe."
    ]);
    exit;
}

$sql = "
    SELECT 
        id,
        username,
        full_name,
        email,
        password_hash,
        role
    FROM users
    WHERE email = ?
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Aucun compte trouvé avec cet email."
    ]);
    exit;
}

if (!hash_equals($user["password_hash"], hash("sha256", $password))) {
    echo json_encode([
        "success" => false,
        "message" => "Mot de passe incorrect."
    ]);
    exit;
}

session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["username"] = $user["username"];
$_SESSION["full_name"] = $user["full_name"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];

$update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$update->execute([$user["id"]]);

echo json_encode([
    "success" => true,
    "message" => "Connexion réussie."
]);
