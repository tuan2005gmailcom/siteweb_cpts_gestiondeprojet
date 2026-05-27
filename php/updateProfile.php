<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit;
}

require_once "db.php";

function fail($message)
{
    echo "<h2>Erreur de mise à jour</h2>";
    echo "<p>" . htmlspecialchars($message, ENT_QUOTES, "UTF-8") . "</p>";
    echo '<p><a href="monprofil.php">Retour au profil</a></p>';
    exit;
}

function getCoordinatesFromAddress($address, $city, $postalCode)
{
    $fullAddress = trim($address . " " . $postalCode . " " . $city . " France");

    if ($fullAddress === "France") {
        return [
            "success" => false,
            "latitude" => null,
            "longitude" => null
        ];
    }

    $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=fr&q=" . urlencode($fullAddress);

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => "CPTS-Project/1.0"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return [
            "success" => false,
            "latitude" => null,
            "longitude" => null
        ];
    }

    $data = json_decode($response, true);

    if (empty($data[0]["lat"]) || empty($data[0]["lon"])) {
        return [
            "success" => false,
            "latitude" => null,
            "longitude" => null
        ];
    }

    return [
        "success" => true,
        "latitude" => $data[0]["lat"],
        "longitude" => $data[0]["lon"]
    ];
}

$userId = $_SESSION["user_id"];
$username = trim($_POST["username"] ?? "");
$fullName = trim($_POST["full_name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$address = trim($_POST["address"] ?? "");
$city = trim($_POST["city"] ?? "");
$postalCode = trim($_POST["postal_code"] ?? "");
$birthDate = $_POST["birth_date"] ?? null;
$gender = $_POST["gender"] ?? null;

if ($username === "" || $fullName === "" || $email === "" || $phone === "" || $address === "" || $city === "" || $postalCode === "") {
    fail("Veuillez remplir les champs obligatoires.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail("Adresse email invalide.");
}

if (!in_array($gender, ["", "male", "female", "other"], true)) {
    fail("Genre invalide.");
}

try {
    $pdo->beginTransaction();

    $stmtUser = $pdo->prepare("
        SELECT id, role
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $stmtUser->execute([$userId]);
    $currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        $pdo->rollBack();
        fail("Utilisateur introuvable.");
    }

    $role = $currentUser["role"];

    $checkEmail = $pdo->prepare("
        SELECT id
        FROM users
        WHERE email = ?
        AND id <> ?
        LIMIT 1
    ");
    $checkEmail->execute([$email, $userId]);

    if ($checkEmail->fetch()) {
        $pdo->rollBack();
        fail("Cet email est déjà utilisé par un autre compte.");
    }

    $coordinates = getCoordinatesFromAddress($address, $city, $postalCode);

    $latitude = null;
    $longitude = null;

    if ($coordinates["success"]) {
        $latitude = $coordinates["latitude"];
        $longitude = $coordinates["longitude"];
    }

    $latitude = $coordinates["latitude"];
    $longitude = $coordinates["longitude"];

    $updateUser = $pdo->prepare("
        UPDATE users
        SET
            username = ?,
            full_name = ?,
            email = ?,
            phone = ?,
            address = ?,
            city = ?,
            postal_code = ?,
            birth_date = ?,
            gender = ?,
            latitude = ?,
            longitude = ?
        WHERE id = ?
    ");

    $updateUser->execute([
        $username,
        $fullName,
        $email,
        $phone,
        $address,
        $city,
        $postalCode,
        $role === "patient" ? $birthDate : null,
        $role === "patient" ? $gender : null,
        $latitude,
        $longitude,
        $userId
    ]);

    if ($role === "doctor") {
        $nameParts = preg_split("/\s+/", $fullName);
        $firstName = $nameParts[0] ?? $fullName;
        $lastName = count($nameParts) > 1 ? implode(" ", array_slice($nameParts, 1)) : "";

        $updateProfessional = $pdo->prepare("
            UPDATE professionals
            SET
                first_name = ?,
                last_name = ?,
                phone = ?,
                email = ?,
                address = ?,
                city = ?,
                postal_code = ?,
                latitude = ?,
                longitude = ?
            WHERE user_id = ?
        ");

        $updateProfessional->execute([
            $firstName,
            $lastName,
            $phone,
            $email,
            $address,
            $city,
            $postalCode,
            $latitude,
            $longitude,
            $userId
        ]);
    }

    $_SESSION["full_name"] = $fullName;
    $_SESSION["email"] = $email;

    $pdo->commit();

    header("Location: monprofil.php?updated=1");
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fail("Erreur SQL : " . $e->getMessage());
}
