<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit;
}

require_once "db.php";

function fail($message)
{
    echo "<h2>Erreur de changement de mot de passe</h2>";
    echo "<p>" . htmlspecialchars($message, ENT_QUOTES, "UTF-8") . "</p>";
    echo '<p><a href="monprofil.php">Retour au profil</a></p>';
    exit;
}

$userId = $_SESSION["user_id"];

$oldPassword = $_POST["old_password"] ?? "";
$newPassword = $_POST["new_password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if ($oldPassword === "" || $newPassword === "" || $confirmPassword === "") {
    fail("Veuillez remplir tous les champs.");
}

if ($newPassword !== $confirmPassword) {
    fail("Les nouveaux mots de passe ne correspondent pas.");
}

if (strlen($newPassword) < 8) {
    fail("Le nouveau mot de passe doit contenir au moins 8 caractères.");
}

$stmt = $pdo->prepare("
    SELECT password_hash
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    fail("Utilisateur introuvable.");
}

$oldPasswordHash = hash("sha256", $oldPassword);

if (!hash_equals($user["password_hash"], $oldPasswordHash)) {
    fail("Ancien mot de passe incorrect.");
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
    $userId
]);

header("Location: monprofil.php?password_updated=1");
exit;
?>