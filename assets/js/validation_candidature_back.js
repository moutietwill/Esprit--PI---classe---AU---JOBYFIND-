// validation_candidature_back.js
document.addEventListener('DOMContentLoaded', function() {
    // Le formulaire a l'ID form-candidature d'après votre vue!
    const formCandBack = document.getElementById('form-candidature');
    
    if (formCandBack) {
        formCandBack.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Récupération des bons IDs
            const nom = document.getElementById('nom_candidat');
            const prenom = document.getElementById('prenom_candidat');
            const email = document.getElementById('email_candidat');
            const tel = document.getElementById('telephone');
            const offre = document.getElementById('id_offre');
            const lm = document.getElementById('lettre_motivation');

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const telRegex = /^[0-9+() -]{8,}$/;
            
            // Validation Nom
            if (nom && nom.value.trim().length < 2) {
                document.getElementById('error-nom_candidat').style.display = 'block'; 
                document.getElementById('error-nom_candidat').innerText = 'Le nom doit contenir au moins 2 caractères.';
                nom.style.borderColor = 'red'; 
                isValid = false;
            } else if (nom) { 
                document.getElementById('error-nom_candidat').style.display = 'none'; 
                nom.style.borderColor = ''; 
            }

            // Validation Prenom
            if (prenom && prenom.value.trim().length < 2) {
                document.getElementById('error-prenom_candidat').style.display = 'block'; 
                document.getElementById('error-prenom_candidat').innerText = 'Le prénom doit contenir au moins 2 caractères.';
                prenom.style.borderColor = 'red'; 
                isValid = false;
            } else if (prenom) { 
                document.getElementById('error-prenom_candidat').style.display = 'none'; 
                prenom.style.borderColor = ''; 
            }

            // Validation Email
            if (email && !emailRegex.test(email.value.trim())) {
                document.getElementById('error-email_candidat').style.display = 'block'; 
                document.getElementById('error-email_candidat').innerText = 'Email invalide.';
                email.style.borderColor = 'red'; 
                isValid = false;
            } else if (email) { 
                document.getElementById('error-email_candidat').style.display = 'none'; 
                email.style.borderColor = ''; 
            }

            // Validation Téléphone
            if (tel && !telRegex.test(tel.value.trim())) {
                document.getElementById('error-telephone').style.display = 'block'; 
                document.getElementById('error-telephone').innerText = 'Téléphone invalide (minimum 8 chiffres).';
                tel.style.borderColor = 'red'; 
                isValid = false;
            } else if (tel) { 
                document.getElementById('error-telephone').style.display = 'none'; 
                tel.style.borderColor = ''; 
            }

            // Validation Offre (seulement si elle n'est pas "désactivée" pendant l'édition)
            if (offre && !offre.disabled && offre.value === "") {
                if(document.getElementById('error-id_offre')) {
                    document.getElementById('error-id_offre').style.display = 'block'; 
                }
                offre.style.borderColor = 'red'; 
                isValid = false;
            } else if (offre && !offre.disabled) { 
                if(document.getElementById('error-id_offre')){
                    document.getElementById('error-id_offre').style.display = 'none';
                }
                offre.style.borderColor = ''; 
            }

            // Validation Lettre de motivation
            if (lm && lm.value.trim().length < 20) {
                document.getElementById('error-lettre_motivation').style.display = 'block'; 
                document.getElementById('error-lettre_motivation').innerText = 'Minimum 20 caractères.';
                lm.style.borderColor = 'red'; 
                isValid = false;
            } else if (lm) { 
                document.getElementById('error-lettre_motivation').style.display = 'none'; 
                lm.style.borderColor = ''; 
            }

            if (!isValid) {
                e.preventDefault(); // Annule l'envoi du formulaire si erreur JS
            }
        });
    }
});
