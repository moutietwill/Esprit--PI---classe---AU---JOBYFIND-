// validation_candidature_front.js
document.addEventListener('DOMContentLoaded', function() {
    const formCandFront = document.getElementById('form-candidature-front');
    if (formCandFront) {
        formCandFront.addEventListener('submit', function(e) {
            let isValid = true;
            
            const offre = document.getElementById('front_offre');
            const prenom = document.getElementById('front_prenom');
            const nom = document.getElementById('front_nom');
            const email = document.getElementById('front_email');
            const lm = document.getElementById('front_lm');

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (offre.value === "") {
                document.getElementById('error-front_offre').style.display = 'block'; offre.style.borderColor = 'red'; isValid = false;
            } else { document.getElementById('error-front_offre').style.display = 'none'; offre.style.borderColor = ''; }

            if (prenom.value.trim().length === 0) {
                document.getElementById('error-front_prenom').style.display = 'block'; prenom.style.borderColor = 'red'; isValid = false;
            } else { document.getElementById('error-front_prenom').style.display = 'none'; prenom.style.borderColor = ''; }

            if (nom.value.trim().length === 0) {
                document.getElementById('error-front_nom').style.display = 'block'; nom.style.borderColor = 'red'; isValid = false;
            } else { document.getElementById('error-front_nom').style.display = 'none'; nom.style.borderColor = ''; }

            if (!emailRegex.test(email.value.trim())) {
                document.getElementById('error-front_email').style.display = 'block'; email.style.borderColor = 'red'; isValid = false;
            } else { document.getElementById('error-front_email').style.display = 'none'; email.style.borderColor = ''; }

            if (lm.value.trim().length < 20) {
                document.getElementById('error-front_lm').style.display = 'block'; lm.style.borderColor = 'red'; isValid = false;
            } else { document.getElementById('error-front_lm').style.display = 'none'; lm.style.borderColor = ''; }

            if (!isValid) e.preventDefault();
        });
    }
});
