<?php
require_once "db.php";

function fail($message)
{
    echo "<h2>Erreur d'inscription</h2>";
    echo "<p>" . htmlspecialchars($message, ENT_QUOTES, "UTF-8") . "</p>";
    echo '<p><a href="../html/register.html">Retour à l’inscription</a></p>';
    exit;
}

$username = trim($_POST["username"] ?? "");
$fullName = trim($_POST["full_name"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";
$phone = trim($_POST["phone"] ?? "");
$address = trim($_POST["address"] ?? "");
$city = trim($_POST["city"] ?? "");
$postalCode = trim($_POST["postal_code"] ?? "");
$role = $_POST["role"] ?? "patient";

$birthDate = $_POST["birth_date"] ?? null;
$gender = $_POST["gender"] ?? null;

$specialityId = $_POST["speciality_id"] ?? null;
$description = trim($_POST["description"] ?? "");
$pathologyIds = $_POST["pathology_ids"] ?? [];

if ($username === "" || $fullName === "" || $email === "" || $password === "") {
    fail("Veuillez remplir tous les champs obligatoires.");
}

if ($password !== $confirmPassword) {
    fail("Les mots de passe ne correspondent pas.");
}

if (strlen($password) < 8) {
    fail("Le mot de passe doit contenir au moins 8 caractères.");
}

if (!in_array($role, ["patient", "doctor"], true)) {
    fail("Rôle invalide.");
}

if ($role === "doctor" && empty($specialityId)) {
    fail("Veuillez choisir une compétence principale.");
}

$passwordHash = hash("sha256", $password);

try {
    $pdo->beginTransaction();

    $check = $pdo->prepare("
        SELECT id 
        FROM users 
        WHERE email = ? OR username = ?
        LIMIT 1
    ");
    $check->execute([$email, $username]);

    if ($check->fetch()) {
        $pdo->rollBack();
        fail("Cet email ou ce nom d’utilisateur existe déjà.");
    }

    $insertUser = $pdo->prepare("
    INSERT INTO users (
        username,
        full_name,
        email,
        phone,
        address,
        city,
        postal_code,
        birth_date,
        gender,
        password_hash,
        role,
        temp_password
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

    $insertUser->execute([
        $username,
        $fullName,
        $email,
        $phone,
        $address,
        $city,
        $postalCode,
        $role === "patient" ? $birthDate : null,
        $role === "patient" ? $gender : null,
        $passwordHash,
        $role,
        $password
    ]);

    $userId = $pdo->lastInsertId();

    if ($role === "doctor") {
        $nameParts = preg_split("/\s+/", $fullName);
        $firstName = $nameParts[0] ?? $fullName;
        $lastName = count($nameParts) > 1 ? implode(" ", array_slice($nameParts, 1)) : "";

        $stmtSpeciality = $pdo->prepare("
            SELECT name 
            FROM specialities 
            WHERE id = ?
            LIMIT 1
        ");
        $stmtSpeciality->execute([$specialityId]);
        $speciality = $stmtSpeciality->fetch(PDO::FETCH_ASSOC);

        $jobTitle = $speciality ? $speciality["name"] : "Professionnel de santé";

        $insertProfessional = $pdo->prepare("
            INSERT INTO professionals (
                user_id,
                first_name,
                last_name,
                job_title,
                description,
                phone,
                email,
                address,
                city,
                postal_code,
                latitude,
                longitude,
                is_available
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, 1)
        ");

        $insertProfessional->execute([
            $userId,
            $firstName,
            $lastName,
            $jobTitle,
            $description,
            $phone,
            $email,
            $address,
            $city,
            $postalCode
        ]);

        $professionalId = $pdo->lastInsertId();

        $insertSpeciality = $pdo->prepare("
            INSERT INTO professional_specialities (
                professional_id,
                speciality_id
            ) VALUES (?, ?)
        ");

        $insertSpeciality->execute([
            $professionalId,
            $specialityId
        ]);

        if (!empty($pathologyIds)) {
            $insertPathology = $pdo->prepare("
                INSERT INTO professional_pathologies (
                    professional_id,
                    pathology_id
                ) VALUES (?, ?)
            ");

            foreach ($pathologyIds as $pathologyId) {
                if ($pathologyId !== "") {
                    $insertPathology->execute([
                        $professionalId,
                        $pathologyId
                    ]);
                }
            }
        }
    }

    $pdo->commit();

    header("Location: ../html/login.html?registered=1");
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fail("Erreur SQL : " . $e->getMessage());
}
