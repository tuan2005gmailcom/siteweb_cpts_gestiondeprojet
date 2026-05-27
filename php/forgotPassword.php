<?php
header("Content-Type: application/json; charset=utf-8");

require_once "db.php";

$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$newPassword = $_POST["new_password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if ($email === "" || $phone === "" || $newPassword === "" || $confirmPassword === "") {
    echo json_encode([
        "success" => false,
        "message" => "Veuillez remplir tous les champs."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Adresse email invalide."
    ]);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode([
        "success" => false,
        "message" => "Les mots de passe ne correspondent pas."
    ]);
    exit;
}

if (strlen($newPassword) < 8) {
    echo json_encode([
        "success" => false,
        "message" => "Le mot de passe doit contenir au moins 8 caractères."
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id
    FROM users
    WHERE email = ?
    AND phone = ?
    LIMIT 1
");

$stmt->execute([$email, $phone]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Aucun compte ne correspond à cet email et ce téléphone."
    ]);
    exit;
}

$newPasswordHash = hash("sha256", $newPassword);

$update = $pdo->prepare("
    UPDATE users
    SET 
        password_hash = ?,
        temp_password = ?
    WHERE id = ?
");

$update->execute([
    $newPasswordHash,
    $newPassword,
    $user["id"]
]);

echo json_encode([
    "success" => true,
    "message" => "Mot de passe modifié avec succès."
]);
?>