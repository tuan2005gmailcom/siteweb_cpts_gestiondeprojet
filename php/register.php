<?php
require_once "db.php";

function getCoordinatesFromAddress($address, $city, $postalCode)
{
    $fullAddress = trim($address . " " . $postalCode . " " . $city . " France");

    $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=fr&addressdetails=1&q=" . urlencode($fullAddress);

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

        $coordinates = getCoordinatesFromAddress($address, $city, $postalCode);

        if (!$coordinates["success"]) {
            $pdo->rollBack();
            fail("Adresse introuvable. Veuillez vérifier l’adresse, la ville et le code postal.");
        }

        $latitude = $coordinates["latitude"];
        $longitude = $coordinates["longitude"];

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
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
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
            $postalCode,
            $latitude,
            $longitude
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
?>

    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <title>Compte créé</title>
        <link rel="stylesheet" href="../css/index.css">
        <style>
            body {
                background: #f8fbff;
                font-family: Arial, sans-serif;
            }

            .success-page {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 30px;
            }

            .success-card {
                background: white;
                border: 1px solid #d9e6f7;
                border-radius: 20px;
                padding: 36px;
                max-width: 480px;
                width: 100%;
                text-align: center;
                box-shadow: 0 12px 28px rgba(18, 76, 145, 0.08);
            }

            .success-icon {
                width: 72px;
                height: 72px;
                border-radius: 50%;
                background: #e9fbe9;
                color: #15803d;
                display: grid;
                place-items: center;
                font-size: 36px;
                font-weight: 900;
                margin: 0 auto 18px;
            }

            .success-card h1 {
                color: #001f4d;
                margin-bottom: 10px;
            }

            .success-card p {
                color: #64748b;
                margin-bottom: 24px;
            }

            .success-actions {
                display: flex;
                gap: 12px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .success-actions a {
                text-decoration: none;
                padding: 12px 18px;
                border-radius: 12px;
                font-weight: 800;
            }

            .login-btn {
                background: #0b63ce;
                color: white;
            }

            .home-btn {
                background: #eaf4ff;
                color: #0b63ce;
            }
        </style>
    </head>

    <body>
        <div class="success-page">
            <div class="success-card">
                <div class="success-icon">✓</div>
                <h1>Compte créé avec succès</h1>
                <p>Votre compte a bien été enregistré dans la base de données.</p>

                <div class="success-actions">
                    <a href="../html/login.html" class="login-btn">Se connecter</a>
                    <a href="../html/index.html" class="home-btn">Retour à l’accueil</a>
                </div>
            </div>
        </div>
    </body>

    </html>

<?php
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fail("Erreur SQL : " . $e->getMessage());
}
