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
            console.log("Form submission triggered");
            
            // Minimal check for testing
            const terms = document.getElementById('terms');
            const errorTerms = document.getElementById('error-terms');
            if (errorTerms) errorTerms.textContent = "";
            
            if (!terms.checked) {
                if (errorTerms) errorTerms.textContent = "Vous devez accepter les conditions.";
                e.preventDefault();
                return;
            }

            // If we reach here, let it submit
        });
    }
});