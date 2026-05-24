async function getSpecialities() {
    document.addEventListener("DOMContentLoaded", () => {
        const skillInput = document.getElementById("skillInput");
        const skillsList = document.getElementById("skillsList");

        let specialities = [];

        fetch("../php/getSpecialities.php")
            .then(response => response.json())
            .then(data => {
                specialities = data;
                showSpecialities(specialities);
            })
            .catch(error => {
                console.error("Erreur chargement spécialités:", error);
            });

        function showSpecialities(items) {
            skillsList.innerHTML = "";

            items.forEach(speciality => {
                const li = document.createElement("li");
                li.textContent = speciality.name;

                li.addEventListener("click", () => {
                    skillInput.value = speciality.name;
                    skillsList.style.display = "none";

                    skillInput.blur();
                });

                skillsList.appendChild(li);
            });

            skillsList.style.display = items.length > 0 ? "block" : "none";
        }

        skillInput.addEventListener("focus", () => {
            showSpecialities(specialities);
        });

        skillInput.addEventListener("input", () => {
            const search = skillInput.value.toLowerCase();

            const filtered = specialities.filter(speciality =>
                speciality.name.toLowerCase().includes(search)
            );

            showSpecialities(filtered);
        });

        document.addEventListener("click", event => {
            if (!event.target.closest(".custom-select")) {
                skillsList.style.display = "none";
            }
        });
    });
}

getSpecialities();


async function getPathologies() {
    document.addEventListener("DOMContentLoaded", () => {
        const skillInput = document.getElementById("skillInput");
        const skillsList = document.getElementById("skillsList");

        const pathologyInput = document.getElementById("pathologyInput");
        const pathologyList = document.getElementById("pathologyList");

        let specialities = [];
        let allPathologies = [];
        let currentPathologies = [];

        let selectedSpecialityId = null;
        let selectedPathologyId = null;

        fetch("../php/getSpecialities.php")
            .then(response => response.json())
            .then(data => {
                specialities = data;
            })
            .catch(error => console.error("Erreur spécialités:", error));

        fetch("../php/getPathologies.php")
            .then(response => response.json())
            .then(data => {
                allPathologies = data;
                currentPathologies = data;
            })
            .catch(error => console.error("Erreur pathologies:", error));

        function showSpecialities(items) {
            skillsList.innerHTML = "";

            items.forEach(speciality => {
                const li = document.createElement("li");
                li.textContent = speciality.name;

                li.addEventListener("click", () => {
                    skillInput.value = speciality.name;
                    selectedSpecialityId = speciality.id;
                    skillsList.style.display = "none";

                    loadPathologiesBySpeciality(speciality.id);
                });

                skillsList.appendChild(li);
            });

            skillsList.style.display = items.length ? "block" : "none";
        }

        function loadPathologiesBySpeciality(specialityId) {
            fetch(`../php/getPathologies.php?speciality_id=${specialityId}`)
                .then(response => response.json())
                .then(data => {
                    currentPathologies = data;

                    const pathologyStillValid = currentPathologies.some(
                        pathology => pathology.id == selectedPathologyId
                    );

                    if (selectedPathologyId && !pathologyStillValid) {
                        pathologyInput.value = "";
                        selectedPathologyId = null;
                    }

                    showPathologies(currentPathologies);
                })
                .catch(error => console.error("Erreur pathologies filtrées:", error));
        }

        function showPathologies(items) {
            pathologyList.innerHTML = "";

            items.forEach(pathology => {
                const li = document.createElement("li");
                li.textContent = pathology.name;

                li.addEventListener("click", () => {
                    pathologyInput.value = pathology.name;
                    selectedPathologyId = pathology.id;
                    pathologyList.style.display = "none";
                });

                pathologyList.appendChild(li);
            });

            pathologyList.style.display = items.length ? "block" : "none";
        }

        skillInput.addEventListener("focus", () => {
            showSpecialities(specialities);
        });

        skillInput.addEventListener("input", () => {
            selectedSpecialityId = null;

            const search = skillInput.value.toLowerCase();

            const filtered = specialities.filter(speciality =>
                speciality.name.toLowerCase().includes(search)
            );

            showSpecialities(filtered);

            currentPathologies = allPathologies;
        });

        pathologyInput.addEventListener("focus", () => {
            if (selectedSpecialityId) {
                showPathologies(currentPathologies);
            } else {
                showPathologies(allPathologies);
            }
        });

        pathologyInput.addEventListener("input", () => {
            selectedPathologyId = null;

            const search = pathologyInput.value.toLowerCase();

            const source = selectedSpecialityId ? currentPathologies : allPathologies;

            const filtered = source.filter(pathology =>
                pathology.name.toLowerCase().includes(search)
            );

            showPathologies(filtered);
        });

        document.addEventListener("click", event => {
            if (!event.target.closest(".custom-select")) {
                skillsList.style.display = "none";
                pathologyList.style.display = "none";
            }
        });
    });
}

getPathologies();