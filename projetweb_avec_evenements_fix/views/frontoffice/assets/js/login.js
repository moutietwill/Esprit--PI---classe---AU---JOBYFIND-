function togglePasswordVisibility(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('form');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            let isValid = true;
            const email = document.getElementById('email');
            const password = document.getElementById('login-password');
            const errorEmail = document.getElementById('error-email');
            const errorPassword = document.getElementById('error-password');

            
            errorEmail.textContent = "";
            errorPassword.textContent = "";

            
            if (!email.value.trim()) {
                errorEmail.textContent = "L'adresse e-mail est requise.";
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                errorEmail.textContent = "Format d'e-mail invalide.";
                isValid = false;
            }

            
            if (!password.value.trim()) {
                errorPassword.textContent = "Le mot de passe est requis.";
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});