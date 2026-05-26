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
        password_hash
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

if (!password_verify($password, $user["password_hash"])) {
    echo json_encode([
        "success" => false,
        "message" => "Mot de passe incorrect."
    ]);
    exit;
}

$_SESSION["user_id"] = $user["id"];
$_SESSION["username"] = $user["username"];
$_SESSION["full_name"] = $user["full_name"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = "patient";

$update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$update->execute([$user["id"]]);

echo json_encode([
    "success" => true,
    "message" => "Connexion réussie."
]);
?>