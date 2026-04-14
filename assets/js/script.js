// Fonction pour changer de rôle
function setRole(element, role) {
    // 1. Récupère tous les boutons de rôle
    const buttons = document.querySelectorAll('.role-tabs button');
    
    // 2. Retire le style "actif" de tous les boutons
    buttons.forEach(btn => {
        btn.style.background = "transparent";
        btn.style.color = "#666";
        btn.style.boxShadow = "none";
    });

    // 3. Applique le style actif au bouton cliqué
    element.style.background = "#fff";
    element.style.color = "#2d79ff";
    element.style.boxShadow = "0 2px 5px rgba(0,0,0,0.1)";

    // 4. Met à jour la valeur cachée pour le formulaire
    document.getElementById('selected-role').value = role;
    console.log("Rôle sélectionné : " + role);
}

// Fonction pour afficher/masquer le mot de passe
function togglePass() {
    const passwordField = document.getElementById('login-pass');
    const eyeIcon = document.getElementById('eye-icon');

    if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = "password";
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}

// Fonction de connexion
function handleLogin() {
    const email = document.getElementById('login-email').value;
    const role = document.getElementById('selected-role').value;
    
    if(email === "") {
        alert("Veuillez entrer votre email");
    } else {
        alert("Tentative de connexion en tant que " + role + " avec l'adresse : " + email);
    }
}
