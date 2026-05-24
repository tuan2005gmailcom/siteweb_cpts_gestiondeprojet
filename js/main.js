// Sélection des éléments de la sidebar
const sidebarToggle = document.getElementById("sidebarToggle");
const appSidebar = document.getElementById("appSidebar");
const sidebarOverlay = document.getElementById("sidebarOverlay");
const closeSidebarBtn = document.getElementById("closeSidebar");

// Fonction pour ouvrir/fermer
function toggleSidebar() {
    appSidebar.classList.toggle("open");
    sidebarOverlay.classList.toggle("open");
}

// Événements
if (sidebarToggle) {
    sidebarToggle.addEventListener("click", toggleSidebar);
}
if (closeSidebarBtn) {
    closeSidebarBtn.addEventListener("click", toggleSidebar);
}
if (sidebarOverlay) {
    // Permet de fermer le menu en cliquant dans le fond sombre
    sidebarOverlay.addEventListener("click", toggleSidebar); 
}