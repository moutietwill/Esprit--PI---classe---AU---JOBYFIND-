function toggleSection(sec) {
    let view = document.getElementById('view-' + sec);
    let edit = document.getElementById('edit-' + sec);
    if (view.style.display === 'none') {
        view.style.display = 'block';
        edit.style.display = 'none';
        document.getElementById('toggle-' + sec).innerHTML = '<i class="fa fa-pen"></i> Modifier';
    } else {
        view.style.display = 'none';
        edit.style.display = 'block';
        document.getElementById('toggle-' + sec).innerHTML = '<i class="fa fa-xmark"></i> Annuler';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    
    const avatarInput = document.getElementById('avatar-input');
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarInitials = document.getElementById('avatar-initials');

    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                    avatarPreview.style.display = 'block';
                    avatarPreview.classList.add('loaded');
                    if (avatarInitials) avatarInitials.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });
    }

    
    const infoForm = document.querySelector('#edit-info form');
    if (infoForm) {
        infoForm.addEventListener('submit', (e) => {
            let isValid = true;
            const fields = [
                { id: 'f-prenom', label: 'Le prénom' },
                { id: 'f-nom', label: 'Le nom' },
                { id: 'f-email', label: "L'adresse e-mail", type: 'email' }
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
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});