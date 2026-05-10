function setRoleTab(btn, roleValue) {
    document.querySelectorAll('.role-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('role-input').value = roleValue;
}

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

function updateStrength(password) {
    const segments = ['s1', 's2', 's3', 's4'];
    segments.forEach(s => document.getElementById(s).style.background = '#e2e8f0');
    
    let strength = 0;
    if (password.length >= 4) strength = 1;
    if (password.length >= 6) strength = 2;
    if (password.length >= 8 && /[A-Z]/.test(password)) strength = 3;
    if (password.length >= 10 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) strength = 4;
    
    const colors = ['', '#ef4444', '#f59e0b', '#10b981', '#10b981'];
    const labels = ['Entrez un mot de passe', 'Faible', 'Moyen', 'Fort', 'Très fort'];
    
    for(let i = 0; i < strength; i++) {
        document.getElementById(segments[i]).style.background = colors[strength];
    }
    document.getElementById('strength-label').innerText = labels[strength];
}

document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            let isValid = true;
            
            
            const fields = [
                { id: 'first_name', label: 'Le prénom' },
                { id: 'last_name', label: 'Le nom' },
                { id: 'username', label: "Le nom d'utilisateur" },
                { id: 'email', label: "L'adresse e-mail", type: 'email' },
                { id: 'pwd-input', label: 'Le mot de passe', min: 8 },
                { id: 'date_of_birth', label: 'La date de naissance' }
            ];

            fields.forEach(field => {
                const input = document.getElementById(field.id);
                const error = document.getElementById('error-' + field.id);
                if (error) error.textContent = "";

                if (!input.value.trim()) {
                    if (error) error.textContent = field.label + " est requis.";
                    isValid = false;
                } else if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value)) {
                    if (error) error.textContent = "Format d'e-mail invalide.";
                    isValid = false;
                } else if (field.min && input.value.length < field.min) {
                    if (error) error.textContent = field.label + " doit contenir au moins " + field.min + " caractères.";
                    isValid = false;
                }
            });

            
            const terms = document.getElementById('terms');
            const errorTerms = document.getElementById('error-terms');
            if (errorTerms) errorTerms.textContent = "";
            if (!terms.checked) {
                if (errorTerms) errorTerms.textContent = "Vous devez accepter les conditions.";
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});