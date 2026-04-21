// ============================================
// VALIDATION JS - GLOBAL
// Ce fichier centralise tous les contrôles de
// saisie et appels AJAX pour les formulaires
// du projet (sans validation HTML5).
// ============================================

document.addEventListener('DOMContentLoaded', function () {

    // ----------------------------------------
    // 1. POSTS (Formations) - posts.php
    // ----------------------------------------
    const formationForm = document.getElementById('formationForm');
    if (formationForm) {
        const titleInput = document.getElementById('titleInput');
        const categoryInput = document.getElementById('categoryInput');
        const contentInput = document.getElementById('contentInput');

        formationForm.addEventListener('submit', function (e) {
            let isValid = true;

            // Reset errors
            document.getElementById('titleError').style.display = 'none';
            document.getElementById('categoryError').style.display = 'none';
            document.getElementById('contentError').style.display = 'none';
            titleInput.style.borderColor = 'var(--border)';
            categoryInput.style.borderColor = 'var(--border)';
            contentInput.style.borderColor = 'var(--border)';

            // Validate Title
            if (titleInput.value.trim().length === 0) {
                document.getElementById('titleError').textContent = "Veuillez renseigner ce champ.";
                document.getElementById('titleError').style.display = 'block';
                titleInput.style.borderColor = '#ef4444';
                isValid = false;
            } else if (titleInput.value.trim().length < 3) {
                document.getElementById('titleError').textContent = "Le titre doit faire au moins 3 caractères.";
                document.getElementById('titleError').style.display = 'block';
                titleInput.style.borderColor = '#ef4444';
                isValid = false;
            }

            // Validate Category
            if (categoryInput.value === "") {
                document.getElementById('categoryError').textContent = "Veuillez renseigner ce champ.";
                document.getElementById('categoryError').style.display = 'block';
                categoryInput.style.borderColor = '#ef4444';
                isValid = false;
            }

            // Validate Content
            if (contentInput.value.trim().length === 0) {
                document.getElementById('contentError').textContent = "Veuillez renseigner ce champ.";
                document.getElementById('contentError').style.display = 'block';
                contentInput.style.borderColor = '#ef4444';
                isValid = false;
            } else if (contentInput.value.trim().length < 10) {
                document.getElementById('contentError').textContent = "La description doit contenir au moins 10 caractères.";
                document.getElementById('contentError').style.display = 'block';
                contentInput.style.borderColor = '#ef4444';
                isValid = false;
            }


        });

        // Real-time UX
        titleInput.addEventListener('input', function () {
            document.getElementById('titleError').style.display = 'none';
            this.style.borderColor = 'var(--border)';
        });
        categoryInput.addEventListener('change', function () {
            document.getElementById('categoryError').style.display = 'none';
            this.style.borderColor = 'var(--border)';
        });
        contentInput.addEventListener('input', function () {
            document.getElementById('contentError').style.display = 'none';
            this.style.borderColor = 'var(--border)';
        });
    }

    // ----------------------------------------
    // 2. CATÉGORIES - categories.php
    // ----------------------------------------
    const categoryForm = document.getElementById('categoryForm');
    if (categoryForm) {
        const catNameInput = document.getElementById('catNameInput');

        categoryForm.addEventListener('submit', function (e) {
            let isValid = true;

            document.getElementById('catNameError').style.display = 'none';
            catNameInput.style.borderColor = 'var(--border)';

            const val = catNameInput.value.trim();
            if (val.length === 0) {
                document.getElementById('catNameError').textContent = "Veuillez renseigner le nom de la catégorie.";
                document.getElementById('catNameError').style.display = 'block';
                catNameInput.style.borderColor = '#ef4444';
                isValid = false;
            } else if (val.length < 3) {
                document.getElementById('catNameError').textContent = "Le nom doit faire au moins 3 caractères.";
                document.getElementById('catNameError').style.display = 'block';
                catNameInput.style.borderColor = '#ef4444';
                isValid = false;
            } else if (!/^[A-Za-zÀ-ÿ\s\-]+$/.test(val)) {
                document.getElementById('catNameError').textContent = "Seules les lettres, espaces et tirets sont autorisés.";
                document.getElementById('catNameError').style.display = 'block';
                catNameInput.style.borderColor = '#ef4444';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            } else {
                e.preventDefault();
                const formData = new FormData(categoryForm);
                formData.append('ajax', '1');
                fetch(categoryForm.action || 'categories.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(response => response.text())
                    .then(text => {
                        try {
                            const data = JSON.parse(text);
                            if (data.success) {
                                alert("Catégorie enregistrée !");
                                window.location.href = categoryForm.action || "categories.php";
                            }
                        } catch (e) {
                            alert("Erreur du serveur:\n" + text);
                        }
                    }).catch(err => alert("Erreur requête: " + err.message));
            }
        });

        catNameInput.addEventListener('input', function () {
            document.getElementById('catNameError').style.display = 'none';
            this.style.borderColor = 'var(--border)';
        });
    }

    // ----------------------------------------
    // 3. LOGIN - login.html
    // ----------------------------------------
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        const emailInput = document.getElementById('loginEmail');
        const passwordInput = document.getElementById('loginPassword');

        loginForm.addEventListener('submit', function (e) {
            let isValid = true;

            document.getElementById('emailError').style.display = 'none';
            document.getElementById('passwordError').style.display = 'none';
            emailInput.style.borderColor = 'var(--border)';
            passwordInput.style.borderColor = 'var(--border)';

            // Validate Email with regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailInput.value.trim().length === 0) {
                document.getElementById('emailError').textContent = "L'adresse e-mail est requise.";
                document.getElementById('emailError').style.display = 'block';
                emailInput.style.borderColor = '#ef4444';
                isValid = false;
            } else if (!emailRegex.test(emailInput.value.trim())) {
                document.getElementById('emailError').textContent = "Format d'e-mail invalide.";
                document.getElementById('emailError').style.display = 'block';
                emailInput.style.borderColor = '#ef4444';
                isValid = false;
            }

            // Validate password
            if (passwordInput.value.length === 0) {
                document.getElementById('passwordError').textContent = "Le mot de passe est requis.";
                document.getElementById('passwordError').style.display = 'block';
                passwordInput.style.borderColor = '#ef4444';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            } else {
                e.preventDefault();
                alert('Connexion JS validée avec succès ! (Simulation)');
            }
        });

        emailInput.addEventListener('input', function () {
            document.getElementById('emailError').style.display = 'none';
            this.style.borderColor = 'var(--border)';
        });
        passwordInput.addEventListener('input', function () {
            document.getElementById('passwordError').style.display = 'none';
            this.style.borderColor = 'var(--border)';
        });
    }

    // ----------------------------------------
    // 4. REGISTER - register.html
    // ----------------------------------------
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const ins = {
            fname: document.getElementById('regFirstName'),
            lname: document.getElementById('regLastName'),
            uname: document.getElementById('regUsername'),
            email: document.getElementById('regEmail'),
            pwd: document.getElementById('pwd-input'),
            terms: document.getElementById('regTerms')
        };
        const errs = {
            fname: document.getElementById('errFirstName'),
            lname: document.getElementById('errLastName'),
            uname: document.getElementById('errUsername'),
            email: document.getElementById('errEmail'),
            pwd: document.getElementById('errPassword'),
            terms: document.getElementById('errTerms')
        };

        registerForm.addEventListener('submit', function (e) {
            let isValid = true;

            Object.values(errs).forEach(err => err.style.display = 'none');
            Object.values(ins).forEach(el => { if (el.type !== 'checkbox') el.style.borderColor = 'var(--border)'; });

            // FirstName
            if (ins.fname.value.trim().length < 2) {
                errs.fname.textContent = "Le prénom doit avoir au moins 2 caractères.";
                errs.fname.style.display = 'block';
                ins.fname.style.borderColor = '#ef4444';
                isValid = false;
            }
            if (ins.lname.value.trim().length < 2) {
                errs.lname.textContent = "Le nom doit avoir au moins 2 caractères.";
                errs.lname.style.display = 'block';
                ins.lname.style.borderColor = '#ef4444';
                isValid = false;
            }
            if (!/^[a-zA-Z0-9_]{3,}$/.test(ins.uname.value.trim())) {
                errs.uname.textContent = "Minimal 3 caractères, lettres et chiffres uniquement.";
                errs.uname.style.display = 'block';
                ins.uname.style.borderColor = '#ef4444';
                isValid = false;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(ins.email.value.trim())) {
                errs.email.textContent = "Email invalide.";
                errs.email.style.display = 'block';
                ins.email.style.borderColor = '#ef4444';
                isValid = false;
            }
            if (ins.pwd.value.length < 8) {
                errs.pwd.textContent = "Le mot de passe doit contenir 8 caractères minimum.";
                errs.pwd.style.display = 'block';
                ins.pwd.style.borderColor = '#ef4444';
                isValid = false;
            }
            if (!ins.terms.checked) {
                errs.terms.textContent = "Vous devez accepter les conditions.";
                errs.terms.style.display = 'block';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            } else {
                e.preventDefault();
                alert('Inscription JS validée avec succès ! (Simulation)');
            }
        });

        Object.keys(ins).forEach(k => {
            if (ins[k].type !== 'checkbox') {
                ins[k].addEventListener('input', function () {
                    errs[k].style.display = 'none';
                    this.style.borderColor = 'var(--border)';
                });
            } else {
                ins[k].addEventListener('change', function () {
                    errs[k].style.display = 'none';
                });
            }
        });
    }

    // ----------------------------------------
    // 5. FRONTOFFICE - Recherche
    // ----------------------------------------
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            const searchBar = document.getElementById('searchInput');
            const searchError = document.getElementById('searchError');
            let isValid = true;

            searchError.style.display = 'none';
            if (searchBar) searchBar.style.border = '1px solid #e2e8f0'; // assuming normal border is e2e8f0

            if (!searchBar || searchBar.value.trim().length === 0) {
                searchError.textContent = "Veuillez entrer un mot-clé pour chercher.";
                searchError.style.display = 'block';
                searchBar.style.border = '1px solid red';
                isValid = false;
            } else if (searchBar.value.trim().length < 2) {
                searchError.textContent = "Le mot-clé doit contenir au moins 2 caractères.";
                searchError.style.display = 'block';
                searchBar.style.border = '1px solid red';
                isValid = false;
            } else if (searchBar.value.trim().length > 100) {
                searchError.textContent = "Le mot-clé ne peut pas dépasser 100 caractères.";
                searchError.style.display = 'block';
                searchBar.style.border = '1px solid red';
                isValid = false;
            } else if (!/^[a-zA-Z0-9\s\-À-ÿ]+$/.test(searchBar.value.trim())) {
                searchError.textContent = "Seuls les lettres, chiffres, espaces et tirets sont autorisés.";
                searchError.style.display = 'block';
                if (searchBar) searchBar.style.border = '1px solid red';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        const searchB = document.getElementById('searchInput');
        if (searchB) {
            searchB.addEventListener('input', function () {
                document.getElementById('searchError').style.display = 'none';
                this.style.border = 'none';
            });
        }
    }
});

// Frontoffice: Likes (global function)
function toggleLike(btn, postId) {
    btn.disabled = true;

    fetch('../ajax_toggle_like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            // Update button UI
            if (data.liked) {
                btn.classList.add('liked');
                btn.querySelector('i').classList.remove('far');
                btn.querySelector('i').classList.add('fas');
            } else {
                btn.classList.remove('liked');
                btn.querySelector('i').classList.remove('fas');
                btn.querySelector('i').classList.add('far');
            }
            // Update count
            btn.querySelector('.engagement-count').textContent = data.count;
        } else {
            console.error("Erreur toggleLike: ", data.message);
        }
    })
    .catch(error => {
        btn.disabled = false;
        console.error('Erreur fetch toggleLike:', error);
        alert('Erreur de connexion au serveur (Like).');
    });
}

// Frontoffice: Toggle comments visibility (global function)
function toggleComments(btn, postId) {
    const commentsSection = document.getElementById(`comments-${postId}`);
    if (commentsSection) {
        commentsSection.classList.toggle('show');
    }
}

// Frontoffice: Submit comments (global function)
function submitComment(event, inputElement, postId) {
    // Only submit on Enter key
    if (event.key !== 'Enter') return;
    event.preventDefault();

    const errDiv = document.getElementById(`commentError-${postId}`);
    const content = inputElement.value.trim();

    errDiv.style.display = 'none';

    // JS Validation
    if (content.length === 0) {
        errDiv.textContent = "Le commentaire ne peut pas être vide.";
        errDiv.style.display = 'block';
        return;
    }
    if (content.length < 2) {
        errDiv.textContent = "Le commentaire doit faire au moins 2 caractères.";
        errDiv.style.display = 'block';
        return;
    }
    if (content.length > 500) {
        errDiv.textContent = "Le commentaire ne peut excéder 500 caractères.";
        errDiv.style.display = 'block';
        return;
    }
    if (/<[^>]*>?/gm.test(content)) {
        errDiv.textContent = "Les balises HTML ne sont pas autorisées.";
        errDiv.style.display = 'block';
        return;
    }

    inputElement.disabled = true;

    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('content', content);
    formData.append('user_name', 'Vous'); // Using "Vous" as author name for frontend display

    fetch('../ajax_add_comment.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            inputElement.disabled = false;
            if (data.success) {
                inputElement.value = '';

                const commentsList = document.getElementById(`comments-list-${postId}`);
                
                // Remove 'Aucun commentaire' message if present
                const noCommentMsg = commentsList.querySelector('.no-comment-msg');
                if (noCommentMsg) {
                    noCommentMsg.remove();
                }

                // Add new comment to DOM
                const newComment = document.createElement('div');
                newComment.className = 'comment-item';
                newComment.innerHTML = `
                    <div class="comment-text">
                        <strong style="color: var(--primary);">${escapeHtml(data.comment.author)}</strong>
                        <br>
                        <small style="color: var(--text-secondary);">${escapeHtml(data.comment.timestamp)}</small>
                        <br>
                        ${escapeHtml(data.comment.text)}
                    </div>
                `;
                commentsList.appendChild(newComment);
                
                // Increment comment count on the button
                const commentBtn = document.querySelector(`button.comment-btn[onclick*="toggleComments(this, ${postId})"]`);
                if (commentBtn) {
                    const countSpan = commentBtn.querySelector('.engagement-count');
                    if (countSpan) {
                        countSpan.textContent = parseInt(countSpan.textContent || '0') + 1;
                    }
                }
            } else {
                errDiv.textContent = "Erreur: " + data.message;
                errDiv.style.display = 'block';
            }
        })
        .catch(error => {
            inputElement.disabled = false;
            errDiv.textContent = "Erreur de connexion au serveur.";
            errDiv.style.display = 'block';
            console.error('Erreur fetch addComment:', error);
            alert('Erreur de connexion au serveur (Commentaire).');
        });
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
