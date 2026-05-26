<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CPTS - Mon profil</title>
    <link rel="stylesheet" href="../css/index.css">

    <style>
        .profile-page {
            padding: 42px;
            background: #f8fbff;
        }

        .profile-header {
            margin-bottom: 28px;
        }

        .profile-header h1 {
            font-size: 32px;
            color: var(--text);
            margin-bottom: 8px;
        }

        .profile-header p {
            color: var(--muted);
        }

        .profile-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 24px;
            align-items: flex-start;
        }

        .profile-card,
        .profile-form-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 12px 28px rgba(18, 76, 145, 0.08);
        }

        .profile-card {
            padding: 24px;
            text-align: center;
        }

        .profile-avatar {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: grid;
            place-items: center;
            font-size: 34px;
            font-weight: 900;
            margin: 0 auto 16px;
        }

        .profile-card h2 {
            font-size: 20px;
            margin-bottom: 6px;
            color: var(--text);
        }

        .profile-role {
            display: inline-flex;
            padding: 6px 12px;
            border-radius: 999px;
            background: #e9fbe9;
            color: #15803d;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .profile-menu {
            display: grid;
            gap: 10px;
            margin-top: 18px;
        }

        .profile-menu a {
            text-decoration: none;
            color: var(--muted);
            font-weight: 700;
            padding: 12px;
            border-radius: 12px;
            background: #f8fbff;
            text-align: left;
        }

        .profile-menu a.active,
        .profile-menu a:hover {
            color: var(--primary);
            background: var(--primary-light);
        }

        .profile-form-card {
            padding: 28px;
        }

        .profile-form-card h2 {
            margin-bottom: 20px;
            color: var(--text);
        }

        .profile-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .profile-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .profile-field.full {
            grid-column: 1 / -1;
        }

        .profile-field span {
            font-weight: 800;
            color: var(--text);
            font-size: 14px;
        }

        .profile-field input,
        .profile-field select,
        .profile-field textarea {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 13px 14px;
            font: inherit;
            outline: none;
            background: white;
        }

        .profile-field input:focus,
        .profile-field select:focus,
        .profile-field textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .profile-actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 8px;
        }

        .save-profile-btn {
            border-radius: 12px;
            padding: 13px 20px;
            background: var(--primary);
            color: white;
            font-weight: 800;
        }

        .logout-btn {
            border-radius: 12px;
            padding: 13px 20px;
            background: #fff0f2;
            color: #d6324b;
            font-weight: 800;
        }

        @media (max-width: 900px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }

            .profile-form {
                grid-template-columns: 1fr;
            }

            .profile-page {
                padding: 28px 20px;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <header id="header">
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Ouvrir le menu latéral">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>

                <div class="brand">
                    <a href="index.html" class="logo-mark" aria-hidden="true"></a>
                </div>
            </div>

            <nav aria-label="Navigation principale">
                <a href="index.html">Accueil</a>
                <a href="recherche.html">Recherche</a>
                <a href="apropos.html">À propos</a>
                <a href="contact.html">Contact</a>
            </nav>

            <div class="header-actions">
                <a href="login.html" class="btn-outline"
                    style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                    Se connecter
                </a>
                <a href="register.html" class="btn-primary"
                    style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                    S’inscrire
                </a>
            </div>

            <button class="mobile-toggle" type="button" id="mobileMenu" aria-label="Ouvrir le menu">☰</button>
        </header>

        <section class="profile-page">
            <div class="profile-header">
                <h1>Mon profil</h1>
                <p>Consultez et modifiez vos informations personnelles.</p>
            </div>

            <div class="profile-layout">
                <aside class="profile-card">
                    <div class="profile-avatar" id="profileAvatar">U</div>
                    <h2 id="profileName">Utilisateur</h2>
                    <span class="profile-role" id="profileRole">Compte</span>

                    <div class="profile-menu">
                        <a href="monprofil.html" class="active">Mon profil</a>
                        <a href="favoris.html">Mes favoris</a>
                        <a href="rendezvous.html">Mes rendez-vous</a>
                    </div>
                </aside>

                <section class="profile-form-card">
                    <h2>Informations du compte</h2>

                    <form class="profile-form" id="profileForm">
                        <label class="profile-field">
                            <span>Nom complet</span>
                            <input type="text" id="fullName" name="full_name" placeholder="Nom complet">
                        </label>

                        <label class="profile-field">
                            <span>Email</span>
                            <input type="email" id="email" name="email" placeholder="email@example.com">
                        </label>

                        <label class="profile-field">
                            <span>Téléphone</span>
                            <input type="tel" id="phone" name="phone" placeholder="06 12 34 56 78">
                        </label>

                        <label class="profile-field">
                            <span>Rôle</span>
                            <select id="role" name="role" disabled>
                                <option value="patient">Patient</option>
                                <option value="doctor">Professionnel de santé</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </label>

                        <label class="profile-field full">
                            <span>Adresse</span>
                            <input type="text" id="address" name="address" placeholder="Adresse complète">
                        </label>

                        <label class="profile-field">
                            <span>Ville</span>
                            <input type="text" id="city" name="city" placeholder="Ville">
                        </label>

                        <label class="profile-field">
                            <span>Code postal</span>
                            <input type="text" id="postalCode" name="postal_code" placeholder="Code postal">
                        </label>

                        <label class="profile-field full">
                            <span>À propos de moi</span>
                            <textarea id="description" name="description" rows="4"
                                placeholder="Petite description ou informations utiles..."></textarea>
                        </label>

                        <div class="profile-actions">
                            <button type="button" class="logout-btn" id="logoutBtn">Déconnexion</button>
                            <button type="submit" class="save-profile-btn">Enregistrer</button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </main>

    <div class="toast" id="toast">Profil</div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="app-sidebar" id="appSidebar">
        <div class="sidebar-header">
            <div class="brand">
                <a href="index.html" class="logo-mark" aria-hidden="true" style="width: 120px; height: 30px;"></a>
            </div>
            <button class="close-sidebar" id="closeSidebar">&times;</button>
        </div>

        <a href="monprofil.html" class="nav-item active">Mon profil</a>
        <a href="favoris.html" class="nav-item">Mes favoris</a>
        <a href="rendezvous.html" class="nav-item">Mes rendez-vous</a>
        <a href="login.html" class="nav-item logout">Déconnexion</a>
    </aside>

    <script src="../js/main.js"></script>

    <script>
        const header = document.getElementById("header");
        const mobileMenu = document.getElementById("mobileMenu");
        const toast = document.getElementById("toast");

        if (mobileMenu) {
            mobileMenu.addEventListener("click", () => {
                header.classList.toggle("menu-open");
                mobileMenu.textContent = header.classList.contains("menu-open") ? "×" : "☰";
            });
        }

        function showToast(message) {
            toast.textContent = message;
            toast.classList.add("show");
            setTimeout(() => toast.classList.remove("show"), 2200);
        }

        async function checkConnection() {
            try {
                const response = await fetch("../php/checkAuth.php");
                const data = await response.json();

                if (!data.connected) {
                    window.location.href = "login.html";
                    return;
                }

                loadProfile();
            } catch (error) {
                console.error(error);
                window.location.href = "login.html";
            }
        }

        async function loadProfile() {
            /*
               Plus tard, tu peux remplacer cette partie par :
               fetch("../php/getMyProfile.php")
            */

            const user = {
                full_name: "Utilisateur connecté",
                email: "",
                phone: "",
                role: "patient",
                address: "",
                city: "",
                postal_code: "",
                description: ""
            };

            document.getElementById("fullName").value = user.full_name || "";
            document.getElementById("email").value = user.email || "";
            document.getElementById("phone").value = user.phone || "";
            document.getElementById("role").value = user.role || "patient";
            document.getElementById("address").value = user.address || "";
            document.getElementById("city").value = user.city || "";
            document.getElementById("postalCode").value = user.postal_code || "";
            document.getElementById("description").value = user.description || "";

            document.getElementById("profileName").textContent = user.full_name || "Utilisateur";
            document.getElementById("profileRole").textContent =
                user.role === "doctor" ? "Professionnel de santé" :
                user.role === "admin" ? "Administrateur" :
                "Patient";

            document.getElementById("profileAvatar").textContent =
                (user.full_name || "U").charAt(0).toUpperCase();
        }

        document.getElementById("profileForm").addEventListener("submit", (event) => {
            event.preventDefault();
            showToast("Profil enregistré");
        });

        document.getElementById("logoutBtn").addEventListener("click", async () => {
            await fetch("../php/logout.php");
            window.location.href = "login.html";
        });

        checkConnection();
    </script>
</body>

</html>