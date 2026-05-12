// validation_back.js

document.addEventListener('DOMContentLoaded', function() {
    
    // --- Validation Formulaire Offre ---
    const formOffre = document.getElementById('form-offre');
    if (formOffre) {
        formOffre.addEventListener('submit', function(e) {
            let isValid = true;
            
            const titre = document.getElementById('titre');
            const description = document.getElementById('description_offre');
            const datePub = document.getElementById('datePublication');
            const type = document.getElementById('type');
            
            // Validate Titre
            if (titre.value.trim().length === 0) {
                document.getElementById('error-titre').style.display = 'block';
                titre.style.borderColor = 'red';
                isValid = false;
            } else {
                document.getElementById('error-titre').style.display = 'none';
                titre.style.borderColor = '';
            }

            // Validate Description
            if (description.value.trim().length < 2) {
                document.getElementById('error-description_offre').style.display = 'block';
                description.style.borderColor = 'red';
                isValid = false;
            } else {
                document.getElementById('error-description_offre').style.display = 'none';
                description.style.borderColor = '';
            }

            // Validate Date (YYYY-MM-DD Regex purely in JS)
            const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
            if (!dateRegex.test(datePub.value.trim())) {
                document.getElementById('error-datePublication').style.display = 'block';
                datePub.style.borderColor = 'red';
                isValid = false;
            } else {
                document.getElementById('error-datePublication').style.display = 'none';
                datePub.style.borderColor = '';
            }

            // Validate Select Type
            if (type.value === "") {
                document.getElementById('error-type').style.display = 'block';
                type.style.borderColor = 'red';
                isValid = false;
            } else {
                document.getElementById('error-type').style.display = 'none';
                type.style.borderColor = '';
            }

            if (!isValid) {
                e.preventDefault(); // Stop form from submitting
            }
        });

        // Validation en temps réel (supprimée car annulée)
    }

    // --- Validation Formulaire Candidature ---
    const formCandidature = document.getElementById('form-candidature');
    if (formCandidature) {
        formCandidature.addEventListener('submit', function(e) {
            let isValid = true;
            
            const nom = document.getElementById('nom_candidat');
            const prenom = document.getElementById('prenom_candidat');
            const email = document.getElementById('email_candidat');
            const telephone = document.getElementById('telephone');
            const lettre = document.getElementById('lettre_motivation');
            const cv = document.getElementById('cv_fichier');
            const statut = document.getElementById('statut');

            // Reset all borders
            [nom, prenom, email, telephone, lettre, cv].forEach(e => e && (e.style.borderColor = ''));

            // Validate Nom (min 2 chars, max 255)
            if (nom.value.trim().length === 0) {
                showError('error-nom_candidat', 'Le nom est obligatoire.');
                nom.style.borderColor = 'red';
                isValid = false;
            } else if (nom.value.trim().length < 2) {
                showError('error-nom_candidat', 'Le nom doit contenir au minimum 2 caractères.');
                nom.style.borderColor = 'red';
                isValid = false;
            } else if (nom.value.length > 255) {
                showError('error-nom_candidat', 'Le nom ne peut pas dépasser 255 caractères.');
                nom.style.borderColor = 'red';
                isValid = false;
            } else {
                hideError('error-nom_candidat');
            }

            // Validate Prenom (min 2 chars, max 255)
            if (prenom.value.trim().length === 0) {
                showError('error-prenom_candidat', 'Le prénom est obligatoire.');
                prenom.style.borderColor = 'red';
                isValid = false;
            } else if (prenom.value.trim().length < 2) {
                showError('error-prenom_candidat', 'Le prénom doit contenir au minimum 2 caractères.');
                prenom.style.borderColor = 'red';
                isValid = false;
            } else if (prenom.value.length > 255) {
                showError('error-prenom_candidat', 'Le prénom ne peut pas dépasser 255 caractères.');
                prenom.style.borderColor = 'red';
                isValid = false;
            } else {
                hideError('error-prenom_candidat');
            }

            // Validate Email (valid format, max 255 chars)
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.value.trim().length === 0) {
                showError('error-email_candidat', 'L\'email est obligatoire.');
                email.style.borderColor = 'red';
                isValid = false;
            } else if (!emailRegex.test(email.value.trim())) {
                showError('error-email_candidat', 'L\'email saisi n\'est pas valide.');
                email.style.borderColor = 'red';
                isValid = false;
            } else if (email.value.length > 255) {
                showError('error-email_candidat', 'L\'email ne peut pas dépasser 255 caractères.');
                email.style.borderColor = 'red';
                isValid = false;
            } else {
                hideError('error-email_candidat');
            }

            // Validate Telephone (min 8 chars, max 20)
            const telRegex = /^[0-9+() -]{8,20}$/;
            if (telephone.value.trim().length === 0) {
                showError('error-telephone', 'Le téléphone est obligatoire.');
                telephone.style.borderColor = 'red';
                isValid = false;
            } else if (!telRegex.test(telephone.value.trim())) {
                showError('error-telephone', 'Le téléphone est invalide. Minimum 8 chiffres.');
                telephone.style.borderColor = 'red';
                isValid = false;
            } else {
                hideError('error-telephone');
            }

            // Validate Lettre Motivation (min 20 chars, max 5000)
            if (lettre.value.trim().length === 0) {
                showError('error-lettre_motivation', 'La lettre de motivation est obligatoire.');
                lettre.style.borderColor = 'red';
                isValid = false;
            } else if (lettre.value.trim().length < 20) {
                showError('error-lettre_motivation', 'La lettre de motivation doit contenir au minimum 20 caractères.');
                lettre.style.borderColor = 'red';
                isValid = false;
            } else if (lettre.value.length > 5000) {
                showError('error-lettre_motivation', 'La lettre de motivation ne peut pas dépasser 5000 caractères.');
                lettre.style.borderColor = 'red';
                isValid = false;
            } else {
                hideError('error-lettre_motivation');
            }

            // Validate Statut (if element exists, i.e., edit mode)
            if (statut && statut.value.trim().length === 0) {
                showError('error-statut', 'Le statut est obligatoire.');
                statut.style.borderColor = 'red';
                isValid = false;
            } else if (statut) {
                hideError('error-statut');
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Helper functions
        function showError(elementId, message) {
            const elem = document.getElementById(elementId);
            if (elem) {
                elem.textContent = message;
                elem.style.display = 'block';
            }
        }

        function hideError(elementId) {
            const elem = document.getElementById(elementId);
            if (elem) {
                elem.textContent = '';
                elem.style.display = 'none';
            }
        }
    }

});
