function toggleSection(sec) {
    let view = document.getElementById('view-' + sec);
    let edit = document.getElementById('edit-' + sec);
    if (view.style.display === 'none') {
        view.style.display = 'block';
        edit.style.display = 'none';
        document.getElementById('toggle-' + sec).innerHTML = sec === 'pwd' ? '<i class="fa fa-lock"></i> Changer le mot de passe' : '<i class="fa fa-pen"></i> Modifier';
    } else {
        view.style.display = 'none';
        edit.style.display = 'block';
        document.getElementById('toggle-' + sec).innerHTML = '<i class="fa fa-xmark"></i> Annuler';
    }
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
    segments.forEach(s => {
        const el = document.getElementById(s);
        if (el) el.style.background = '#e2e8f0';
    });
    
    let strength = 0;
    if (password.length >= 4) strength = 1;
    if (password.length >= 6) strength = 2;
    if (password.length >= 8 && /[A-Z]/.test(password)) strength = 3;
    if (password.length >= 10 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) strength = 4;
    
    const colors = ['', '#ef4444', '#f59e0b', '#10b981', '#10b981'];
    const labels = ['Entrez un mot de passe', 'Faible', 'Moyen', 'Fort', 'Très fort'];
    
    for(let i = 0; i < strength; i++) {
        const el = document.getElementById(segments[i]);
        if (el) el.style.background = colors[strength];
    }
    const labelEl = document.getElementById('strength-label');
    if (labelEl) labelEl.innerText = labels[strength];
}

// Toast Notification Helper
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    let icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    if (type === 'warning') icon = 'exclamation-triangle';
    
    toast.innerHTML = `<i class="fa fa-${icon}"></i> <span>${message}</span>`;
    container.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remove after 4s
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

document.addEventListener('DOMContentLoaded', () => {
    
    const avatarInput = document.getElementById('avatar-input');
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarInitials = document.getElementById('avatar-initials');
    const aiScanner = document.getElementById('ai-scanner');

    let classifier = null;

    let nsfwModel = null;
    let objectModel = null;

    async function loadModels() {
        if (nsfwModel && objectModel) return;
        try {
            const { pipeline } = await import('https://cdn.jsdelivr.net/npm/@xenova/transformers@2.17.2');
            
            // Model 1: Dedicated NSFW detector (for nudity)
            if (!nsfwModel) {
                console.log("Loading NSFW Model...");
                nsfwModel = await pipeline('image-classification', 'Xenova/nsfw_image_detection');
            }
            
            // Model 2: General Object detector (for War/Violence)
            if (!objectModel) {
                console.log("Loading Object Model...");
                objectModel = await pipeline('image-classification', 'Xenova/resnet-50');
            }
        } catch (err) {
            console.error("AI Load Error:", err);
            // No more annoying alert, just log it.
        }
    }

    if (avatarInput) {
        avatarInput.addEventListener('change', async function() {
            const file = this.files[0];
            if (!file) return;

            const aiProgress = document.getElementById('ai-progress');
            aiScanner.style.display = 'flex';
            if (aiProgress) aiProgress.style.width = '20%';
            
            try {
                const reader = new FileReader();
                const imageData = await new Promise((resolve) => {
                    reader.onload = (e) => resolve(e.target.result);
                    reader.readAsDataURL(file);
                });

                if (aiProgress) aiProgress.style.width = '40%';

                // Load both models
                await loadModels();
                if (aiProgress) aiProgress.style.width = '60%';

                // --- PASS 1: NSFW DETECTION ---
                console.log("Scanning for NSFW...");
                const nsfwResults = await nsfwModel(imageData);
                const nsfwScore = nsfwResults.find(r => r.label === 'nsfw')?.score || 0;
                console.log("NSFW Result:", nsfwResults);

                if (nsfwScore > 0.4) {
                    throw new Error("Contenu indécent détecté (Nudité)");
                }

                if (aiProgress) aiProgress.style.width = '80%';

                // --- PASS 2: WAR/VIOLENCE DETECTION ---
                console.log("Scanning for War/Objects...");
                const objResults = await objectModel(imageData);
                console.log("Object Result:", objResults);

                const forbiddenObjects = ['soldier', 'military', 'weapon', 'gun', 'war', 'rifle', 'revolver', 'tank', 'cannon'];
                const violation = objResults.find(res => 
                    forbiddenObjects.some(key => res.label.toLowerCase().includes(key)) && res.score > 0.15
                );

                if (violation) {
                    throw new Error("Contenu violent détecté (" + violation.label + ")");
                }

                if (aiProgress) aiProgress.style.width = '100%';

                // --- ALL CLEAN ---
                showToast("Image validée par la double IA !", "success");
                
                const formData = new FormData();
                formData.append('avatar', file);
                const response = await fetch('upload_avatar.php', { method: 'POST', body: formData });
                const data = await response.json();
                
                if (data.success) {
                    avatarPreview.src = data.path;
                    avatarPreview.style.display = 'block';
                    avatarPreview.classList.add('loaded');
                    if (avatarInitials) avatarInitials.style.display = 'none';
                    showToast("Photo de profil mise à jour.");
                } else {
                    showToast(data.message || "Erreur upload", "error");
                }

            } catch (error) {
                console.error("AI Security Block:", error);
                showToast(error.message + ". Suspension du compte...", "error");
                
                // --- BAN THE ACCOUNT IN DATABASE ---
                await fetch('ban_account.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'reason=' + encodeURIComponent(error.message)
                });

                setTimeout(() => {
                    window.location.href = 'banned.php';
                }, 2500);
            } finally {
                aiScanner.style.display = 'none';
                if (aiProgress) aiProgress.style.width = '0%';
            }
        });
    }

    // Direct Reset Handler
    const btnSendReset = document.getElementById('btn-send-reset');
    const resetStatus = document.getElementById('reset-status');

    if (btnSendReset) {
        btnSendReset.addEventListener('click', async () => {
            btnSendReset.disabled = true;
            btnSendReset.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Envoi en cours...';
            resetStatus.style.display = 'block';
            resetStatus.style.color = '#64748b';
            resetStatus.textContent = "Tentative d'envoi du code...";

            try {
                const response = await fetch('send_reset_direct.php');
                const data = await response.json();

                if (data.success) {
                    resetStatus.style.color = '#059669';
                    resetStatus.innerHTML = '<i class="fa fa-check"></i> Code envoyé ! Redirection...';
                    setTimeout(() => {
                        window.location.href = 'verify_code.php';
                    }, 2000);
                } else {
                    resetStatus.style.color = '#ef4444';
                    resetStatus.textContent = data.message || "Erreur d'envoi.";
                    btnSendReset.disabled = false;
                    btnSendReset.innerHTML = '<i class="fa fa-paper-plane"></i> M\'envoyer un code de récupération';
                }
            } catch (e) {
                resetStatus.style.color = '#ef4444';
                resetStatus.textContent = "Erreur de connexion au serveur.";
                btnSendReset.disabled = false;
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

    const pwdForm = document.getElementById('profile-pwd-form');
    if (pwdForm) {
        pwdForm.addEventListener('submit', (e) => {
            let isValid = true;
            const pwd = document.getElementById('f-pwd-new');
            const confirm = document.getElementById('f-pwd-confirm');
            const errorPwd = document.getElementById('error-f-pwd-new');
            const errorConfirm = document.getElementById('error-f-pwd-confirm');
            if (errorPwd) errorPwd.textContent = "";
            if (errorConfirm) errorConfirm.textContent = "";
            if (pwd.value.length < 8) {
                if (errorPwd) errorPwd.textContent = "Le mot de passe doit faire au moins 8 caractères.";
                isValid = false;
            }
            if (pwd.value !== confirm.value) {
                if (errorConfirm) errorConfirm.textContent = "Les mots de passe ne correspondent pas.";
                isValid = false;
            }
            if (!isValid) e.preventDefault();
        });
    }

    // Reset Avatar Handler
    const btnResetAvatar = document.getElementById('btn-reset-avatar');
    if (btnResetAvatar) {
        btnResetAvatar.addEventListener('click', async () => {
            if (!confirm("Voulez-vous supprimer votre photo de profil ?")) return;
            try {
                const response = await fetch('reset_avatar.php');
                const data = await response.json();
                if (data.success) {
                    const avatarPreview = document.getElementById('avatar-preview');
                    const avatarInitials = document.getElementById('avatar-initials');
                    if (avatarPreview) {
                        avatarPreview.style.display = 'none';
                        avatarPreview.src = '';
                    }
                    if (avatarInitials) avatarInitials.style.display = 'flex';
                    showToast("Photo de profil supprimée.");
                } else {
                    showToast(data.message || "Erreur lors de la suppression.", "error");
                }
            } catch (e) {
                showToast("Erreur de connexion.", "error");
            }
        });
    }
});