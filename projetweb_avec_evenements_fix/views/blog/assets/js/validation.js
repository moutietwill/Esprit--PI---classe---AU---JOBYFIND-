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
    // 1B. STORIES - backoffice.php
    // ----------------------------------------
    const storyForm = document.getElementById('storyForm');
    if (storyForm) {
        const storyTitleInput = document.getElementById('storyTitleInput');
        const storyContentInput = document.getElementById('storyContentInput');
        const storyStartsInput = document.getElementById('storyStartsInput');
        const storyExpiresInput = document.getElementById('storyExpiresInput');
        const storyTitleError = document.getElementById('storyTitleError');
        const storyContentError = document.getElementById('storyContentError');
        const storyDateError = document.getElementById('storyDateError');

        storyForm.addEventListener('submit', function (e) {
            let isValid = true;

            storyTitleError.style.display = 'none';
            storyContentError.style.display = 'none';
            storyDateError.style.display = 'none';
            storyTitleInput.style.borderColor = 'var(--border)';
            storyContentInput.style.borderColor = 'var(--border)';
            storyStartsInput.style.borderColor = 'var(--border)';
            storyExpiresInput.style.borderColor = 'var(--border)';

            if (storyTitleInput.value.trim().length < 3) {
                storyTitleError.textContent = "Le titre doit faire au moins 3 caractères.";
                storyTitleError.style.display = 'block';
                storyTitleInput.style.borderColor = '#ef4444';
                isValid = false;
            }

            if (storyContentInput.value.trim().length < 10) {
                storyContentError.textContent = "Le texte doit contenir au moins 10 caractères.";
                storyContentError.style.display = 'block';
                storyContentInput.style.borderColor = '#ef4444';
                isValid = false;
            }

            const startsAt = storyStartsInput.value ? new Date(storyStartsInput.value) : null;
            const expiresAt = storyExpiresInput.value ? new Date(storyExpiresInput.value) : null;
            if (!startsAt || !expiresAt || Number.isNaN(startsAt.getTime()) || Number.isNaN(expiresAt.getTime())) {
                storyDateError.textContent = "Veuillez renseigner les dates de début et de fin.";
                storyDateError.style.display = 'block';
                storyStartsInput.style.borderColor = '#ef4444';
                storyExpiresInput.style.borderColor = '#ef4444';
                isValid = false;
            } else if (expiresAt <= startsAt) {
                storyDateError.textContent = "La date de fin doit être après la date de début.";
                storyDateError.style.display = 'block';
                storyExpiresInput.style.borderColor = '#ef4444';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        [storyTitleInput, storyContentInput, storyStartsInput, storyExpiresInput].forEach(input => {
            input.addEventListener('input', function () {
                storyTitleError.style.display = 'none';
                storyContentError.style.display = 'none';
                storyDateError.style.display = 'none';
                this.style.borderColor = 'var(--border)';
            });
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

    // ----------------------------------------
    // 6. COMMENTAIRES (Backoffice) - comments
    // ----------------------------------------
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        const commentPostInput = document.getElementById('commentPostInput');
        const commentUserInput = document.getElementById('commentUserInput');
        const commentContentInput = document.getElementById('commentContentInput');

        commentForm.addEventListener('submit', function (e) {
            let isValid = true;

            // Reset errors
            const errorIds = ['commentPostError', 'commentUserError', 'commentContentError'];
            errorIds.forEach(function(id) {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
            if (commentPostInput) commentPostInput.style.borderColor = 'var(--border)';
            if (commentUserInput) commentUserInput.style.borderColor = 'var(--border)';
            if (commentContentInput) commentContentInput.style.borderColor = 'var(--border)';

            // Validate Blog (only in add mode, when select exists)
            if (commentPostInput && commentPostInput.value === '') {
                document.getElementById('commentPostError').textContent = "Veuillez choisir un blog.";
                document.getElementById('commentPostError').style.display = 'block';
                commentPostInput.style.borderColor = '#ef4444';
                isValid = false;
            }

            // Validate User Name
            if (commentUserInput) {
                const userName = commentUserInput.value.trim();
                if (userName.length === 0) {
                    document.getElementById('commentUserError').textContent = "Veuillez renseigner le nom d'utilisateur.";
                    document.getElementById('commentUserError').style.display = 'block';
                    commentUserInput.style.borderColor = '#ef4444';
                    isValid = false;
                } else if (userName.length < 2) {
                    document.getElementById('commentUserError').textContent = "Le nom doit faire au moins 2 caractères.";
                    document.getElementById('commentUserError').style.display = 'block';
                    commentUserInput.style.borderColor = '#ef4444';
                    isValid = false;
                } else if (!/^[A-Za-zÀ-ÿ\s\-]+$/.test(userName)) {
                    document.getElementById('commentUserError').textContent = "Seules les lettres, espaces et tirets sont autorisés.";
                    document.getElementById('commentUserError').style.display = 'block';
                    commentUserInput.style.borderColor = '#ef4444';
                    isValid = false;
                }
            }

            // Validate Content
            if (commentContentInput) {
                const content = commentContentInput.value.trim();
                if (content.length === 0) {
                    document.getElementById('commentContentError').textContent = "Veuillez renseigner le contenu du commentaire.";
                    document.getElementById('commentContentError').style.display = 'block';
                    commentContentInput.style.borderColor = '#ef4444';
                    isValid = false;
                } else if (content.length < 5) {
                    document.getElementById('commentContentError').textContent = "Le commentaire doit faire au moins 5 caractères.";
                    document.getElementById('commentContentError').style.display = 'block';
                    commentContentInput.style.borderColor = '#ef4444';
                    isValid = false;
                } else if (content.length > 500) {
                    document.getElementById('commentContentError').textContent = "Le commentaire ne peut pas dépasser 500 caractères.";
                    document.getElementById('commentContentError').style.display = 'block';
                    commentContentInput.style.borderColor = '#ef4444';
                    isValid = false;
                } else {
                    // Check for bad words
                    const badWords = ['merde', 'con', 'putain', 'salope', 'idiot', 'connard', 'bâtard', 'stupide'];
                    let foundWords = [];
                    badWords.forEach(word => {
                        const regex = new RegExp('\\b' + word + '\\b', 'gi');
                        if (regex.test(content)) {
                            foundWords.push(word);
                        }
                    });
                    if (foundWords.length > 0) {
                        document.getElementById('commentContentError').textContent = "Le commentaire contient des mots inappropriés (" + foundWords.join(', ') + "). Veuillez les supprimer avant d'envoyer.";
                        document.getElementById('commentContentError').style.display = 'block';
                        commentContentInput.style.borderColor = '#ef4444';
                        
                        // Masquer temporairement les mots dans l'input pour indiquer ce qui pose problème
                        let maskedContent = content;
                        badWords.forEach(word => {
                            const regex = new RegExp('\\b' + word + '\\b', 'gi');
                            maskedContent = maskedContent.replace(regex, '*'.repeat(word.length));
                        });
                        commentContentInput.value = maskedContent;
                        
                        isValid = false;
                    }
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Real-time UX: clear errors on input
        if (commentPostInput) {
            commentPostInput.addEventListener('change', function () {
                document.getElementById('commentPostError').style.display = 'none';
                this.style.borderColor = 'var(--border)';
            });
        }
        if (commentUserInput) {
            commentUserInput.addEventListener('input', function () {
                document.getElementById('commentUserError').style.display = 'none';
                this.style.borderColor = 'var(--border)';
            });
        }
        if (commentContentInput) {
            commentContentInput.addEventListener('input', function () {
                document.getElementById('commentContentError').style.display = 'none';
                this.style.borderColor = 'var(--border)';
            });
        }
    }

    // ----------------------------------------
    // 7. COMMENTAIRES - Recherche dynamique + Tri par date
    // ----------------------------------------
    const commentSearchInput = document.getElementById('commentSearchInput');
    const commentSortDateBtn = document.getElementById('commentSortDateBtn');
    const commentsTableBody = document.getElementById('commentsTableBody');

    if (commentSearchInput && commentsTableBody) {
        // ── Recherche dynamique ──
        commentSearchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            const rows = commentsTableBody.querySelectorAll('.comment-row');
            const noResults = document.getElementById('noSearchResults');
            let visibleCount = 0;

            rows.forEach(function (row) {
                const cells = row.querySelectorAll('td');
                let rowText = '';
                // Concatenate text from user, content, blog, date columns (skip actions)
                for (let i = 0; i < cells.length - 1; i++) {
                    rowText += ' ' + (cells[i].textContent || '').toLowerCase();
                }

                if (query === '' || rowText.indexOf(query) !== -1) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide "no results" message
            if (noResults) {
                if (visibleCount === 0 && query !== '') {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }
            }
        });
    }

    if (commentSortDateBtn && commentsTableBody) {
        // ── Tri par date ──
        let sortOrder = 'DESC'; // Default: newest first (matches the SQL ORDER BY)

        commentSortDateBtn.addEventListener('click', function () {
            const rows = Array.from(commentsTableBody.querySelectorAll('.comment-row'));
            const sortIcon = document.getElementById('commentSortIcon');

            // Toggle order
            sortOrder = (sortOrder === 'DESC') ? 'ASC' : 'DESC';

            // Update icon
            if (sortIcon) {
                sortIcon.className = sortOrder === 'DESC' ? 'fas fa-sort-down' : 'fas fa-sort-up';
            }

            // Update button visual
            if (sortOrder === 'ASC') {
                commentSortDateBtn.style.background = '#dbeafe';
                commentSortDateBtn.style.color = '#2d79ff';
                commentSortDateBtn.style.borderColor = '#2d79ff';
            } else {
                commentSortDateBtn.style.background = '#e5e7eb';
                commentSortDateBtn.style.color = '#374151';
                commentSortDateBtn.style.borderColor = 'transparent';
            }

            // Sort rows by data-date attribute
            rows.sort(function (a, b) {
                const dateA = new Date(a.getAttribute('data-date'));
                const dateB = new Date(b.getAttribute('data-date'));
                return sortOrder === 'ASC' ? dateA - dateB : dateB - dateA;
            });

            // Re-append sorted rows
            rows.forEach(function (row) {
                commentsTableBody.appendChild(row);
            });
        });
    }

    // ----------------------------------------
    // 8. BLOGS - Recherche dynamique
    // ----------------------------------------
    const blogSearchInput = document.getElementById('blogSearchInput');
    const blogsTableBody = document.getElementById('blogsTableBody');

    if (blogSearchInput && blogsTableBody) {
        blogSearchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            const rows = blogsTableBody.querySelectorAll('.blog-row');
            const noResults = document.getElementById('noBlogSearchResults');
            let visibleCount = 0;

            rows.forEach(function (row) {
                const cells = row.querySelectorAll('td');
                let rowText = '';
                // Concatenate text from blog title, category, price, status columns (skip actions)
                for (let i = 0; i < cells.length - 1; i++) {
                    rowText += ' ' + (cells[i].textContent || '').toLowerCase();
                }

                if (query === '' || rowText.indexOf(query) !== -1) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide "no results" message
            if (noResults) {
                if (visibleCount === 0 && query !== '') {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }
            }
        });
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
    sendComment(inputElement, postId);
}

function sendComment(inputElement, postId) {
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

    // Check for bad words
    const badWords = ['merde', 'con', 'putain', 'salope', 'idiot', 'connard', 'bâtard', 'stupide'];
    let foundWords = [];
    badWords.forEach(word => {
        const regex = new RegExp('\\b' + word + '\\b', 'gi');
        if (regex.test(content)) {
            foundWords.push(word);
        }
    });

    if (foundWords.length > 0) {
        errDiv.textContent = "Le commentaire contient des mots inappropriés (" + foundWords.join(', ') + "). Veuillez les supprimer.";
        errDiv.style.display = 'block';
        
        // Masquer les mots dans l'input pour l'utilisateur
        let maskedContent = content;
        badWords.forEach(word => {
            const regex = new RegExp('\\b' + word + '\\b', 'gi');
            maskedContent = maskedContent.replace(regex, '*'.repeat(word.length));
        });
        inputElement.value = maskedContent;
        
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

function runCommentCommand(btn, postId, commandName) {
    const inputWrapper = btn.closest('.comment-input-wrapper');
    const inputElement = inputWrapper ? inputWrapper.querySelector('.comment-input') : null;

    if (!inputElement) return;

    applyCommentCommand(commandName, inputElement, postId);
}

function applyCommentCommand(commandName, inputElement, postId, voiceText = '') {
    if (commandName === 'send') {
        updateVoiceStatus(postId, 'Commande: envoyer.');
        sendComment(inputElement, postId);
        return;
    }

    if (commandName === 'clear') {
        inputElement.value = '';
        updateVoiceStatus(postId, 'Commande: effacer.');
        const errDiv = document.getElementById(`commentError-${postId}`);
        if (errDiv) {
            errDiv.textContent = '';
            errDiv.style.display = 'none';
        }
        inputElement.focus();
        return;
    }

    if (commandName === 'correct') {
        inputElement.value = correctVoiceCommentText(voiceText || inputElement.value).slice(0, 500);
        updateVoiceStatus(postId, 'Commande: corriger.');
        inputElement.focus();
    }
}

// Frontoffice: Voice comments with the browser Web Speech API.
let activeVoiceRecognition = null;
let activeVoiceButton = null;

function getSpeechRecognitionConstructor() {
    return window.SpeechRecognition || window.webkitSpeechRecognition || null;
}

function toggleVoiceComment(btn, postId) {
    const Recognition = getSpeechRecognitionConstructor();
    const inputWrapper = btn.closest('.comment-input-wrapper');
    const inputElement = inputWrapper ? inputWrapper.querySelector('.comment-input') : null;

    if (!inputElement) return;

    if (!Recognition) {
        updateVoiceStatus(postId, "La reconnaissance vocale n'est pas supportée par ce navigateur.", true);
        return;
    }

    if (activeVoiceRecognition && activeVoiceButton === btn) {
        activeVoiceRecognition.stop();
        return;
    }

    stopActiveVoiceComment();

    const recognition = new Recognition();
    const initialText = inputElement.value.trim();
    let finalTranscript = '';
    let shouldSubmitAfterDictation = false;
    let hadError = false;

    recognition.lang = 'fr-FR';
    recognition.continuous = false;
    recognition.interimResults = true;
    recognition.maxAlternatives = 1;

    activeVoiceRecognition = recognition;
    activeVoiceButton = btn;

    recognition.onstart = () => {
        btn.classList.add('listening');
        btn.setAttribute('aria-pressed', 'true');
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-microphone');
            icon.classList.add('fa-stop');
        }
        updateVoiceStatus(postId, 'Ecoute en cours...');
    };

    recognition.onresult = (event) => {
        let interimTranscript = '';

        for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript.trim();
            if (event.results[i].isFinal) {
                finalTranscript += ' ' + transcript;
            } else {
                interimTranscript += ' ' + transcript;
            }
        }

        const finalText = normalizeVoiceText(finalTranscript);
        const interimText = normalizeVoiceText(interimTranscript);
        const spokenText = normalizeVoiceText(`${finalText} ${interimText}`);
        const command = parseVoiceCommand(spokenText);

        if (command.clear) {
            applyCommentCommand('clear', inputElement, postId);
            finalTranscript = '';
            shouldSubmitAfterDictation = false;
            return;
        }

        if (command.correct) {
            const textToCorrect = command.text ? mergeVoiceText(initialText, command.text) : initialText;
            applyCommentCommand('correct', inputElement, postId, textToCorrect);
            finalTranscript = '';
            shouldSubmitAfterDictation = false;
            return;
        }

        shouldSubmitAfterDictation = command.submit;

        let previewText = command.text;
        if (interimText && !command.submit) {
            previewText = normalizeVoiceText(`${previewText} ${interimText}`);
        }
        if (!previewText) {
            previewText = interimText;
        }

        inputElement.value = mergeVoiceText(initialText, previewText).slice(0, 500);

        if (inputElement.value.length >= 500) {
            updateVoiceStatus(postId, 'Limite de 500 caracteres atteinte.', true);
        } else if (command.submit) {
            updateVoiceStatus(postId, 'Dictée terminee, envoi du commentaire...');
        } else {
            updateVoiceStatus(postId, 'Dictée en cours...');
        }
    };

    recognition.onerror = (event) => {
        hadError = true;
        const messages = {
            'not-allowed': 'Autorisez le micro pour dicter un commentaire.',
            'service-not-allowed': 'Le service de reconnaissance vocale est bloque.',
            'no-speech': 'Aucune voix detectee. Reessayez.',
            'audio-capture': 'Micro introuvable ou indisponible.',
            'network': 'Service vocal indisponible pour le moment.'
        };
        updateVoiceStatus(postId, messages[event.error] || 'Erreur pendant la reconnaissance vocale.', true);
    };

    recognition.onend = () => {
        resetVoiceButton(btn);

        if (activeVoiceRecognition === recognition) {
            activeVoiceRecognition = null;
            activeVoiceButton = null;
        }

        if (shouldSubmitAfterDictation && inputElement.value.trim().length > 0) {
            sendComment(inputElement, postId);
            return;
        }

        if (!hadError) {
            updateVoiceStatus(postId, inputElement.value.trim() ? 'Dictée ajoutee au commentaire.' : '');
        }
    };

    try {
        recognition.start();
    } catch (error) {
        resetVoiceButton(btn);
        activeVoiceRecognition = null;
        activeVoiceButton = null;
        updateVoiceStatus(postId, 'Impossible de demarrer la reconnaissance vocale.', true);
    }
}

function stopActiveVoiceComment() {
    if (activeVoiceRecognition) {
        activeVoiceRecognition.stop();
    }

    if (activeVoiceButton) {
        resetVoiceButton(activeVoiceButton);
    }

    activeVoiceRecognition = null;
    activeVoiceButton = null;
}

function resetVoiceButton(btn) {
    btn.classList.remove('listening');
    btn.setAttribute('aria-pressed', 'false');
    const icon = btn.querySelector('i');
    if (icon) {
        icon.classList.remove('fa-stop');
        icon.classList.add('fa-microphone');
    }
}

function updateVoiceStatus(postId, message, isError = false) {
    const status = document.getElementById(`voiceStatus-${postId}`);
    if (!status) return;

    status.textContent = message;
    status.classList.toggle('error', isError);
}

function mergeVoiceText(initialText, voiceText) {
    const cleanVoiceText = (voiceText || '').trim();
    if (!cleanVoiceText) return initialText;
    if (!initialText) return cleanVoiceText;
    return `${initialText} ${cleanVoiceText}`;
}

function normalizeVoiceText(text) {
    return (text || '')
        .replace(/\s+/g, ' ')
        .trim();
}

function parseVoiceCommand(text) {
    const cleanText = normalizeVoiceText(text);
    const clearPattern = /^(effacer|efface|supprimer|supprime|vider|vide)( le commentaire| mon commentaire)?$/i;
    const correctPattern = /\b(corriger|corrige|corrigez)( le commentaire| mon commentaire)?$/i;
    const submitPattern = /\b(envoyer|envoie|publier|publie|poster|poste|valider|valide)( le commentaire| mon commentaire)?$/i;

    if (clearPattern.test(cleanText)) {
        return { text: '', clear: true, correct: false, submit: false };
    }

    const shouldCorrect = correctPattern.test(cleanText);
    const shouldSubmit = submitPattern.test(cleanText);
    const cleanedText = normalizeVoiceText(
        cleanText
            .replace(correctPattern, '')
            .replace(submitPattern, '')
    );

    return {
        text: cleanedText,
        clear: false,
        correct: shouldCorrect,
        submit: shouldSubmit
    };
}

function correctVoiceCommentText(text) {
    let corrected = normalizeVoiceText(text)
        .replace(/\bvirgule\b/gi, ',')
        .replace(/\bpoint d'interrogation\b/gi, '?')
        .replace(/\bpoint d interrogation\b/gi, '?')
        .replace(/\bpoint d'exclamation\b/gi, '!')
        .replace(/\bpoint d exclamation\b/gi, '!')
        .replace(/\bpoint\b/gi, '.')
        .replace(/\s+([,.!?])/g, '$1')
        .replace(/([,.!?])(?=\S)/g, '$1 ')
        .replace(/\bi l\b/gi, 'il')
        .replace(/\bj aime\b/gi, "j'aime")
        .replace(/\bc est\b/gi, "c'est")
        .replace(/\bd accord\b/gi, "d'accord")
        .trim();

    if (!corrected) return '';

    corrected = corrected.charAt(0).toUpperCase() + corrected.slice(1);

    if (!/[.!?]$/.test(corrected)) {
        corrected += '.';
    }

    return corrected;
}

document.addEventListener('DOMContentLoaded', initPostViewTracking);

function initPostViewTracking() {
    const cards = document.querySelectorAll('[data-view-post-id]');
    if (!cards.length) return;

    const viewedPosts = getViewedPostsFromSession();

    const trackCard = (card) => {
        const postId = card.getAttribute('data-view-post-id');
        if (!postId || viewedPosts.includes(postId)) return;

        viewedPosts.push(postId);
        saveViewedPostsToSession(viewedPosts);

        fetch('../ajax_track_view.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId })
        }).catch(error => {
            console.error('Erreur tracking vue:', error);
        });
    };

    if (!('IntersectionObserver' in window)) {
        cards.forEach(trackCard);
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && entry.intersectionRatio >= 0.55) {
                trackCard(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: [0.55] });

    cards.forEach(card => observer.observe(card));
}

function getViewedPostsFromSession() {
    try {
        return JSON.parse(sessionStorage.getItem('viewed_posts') || '[]');
    } catch (error) {
        return [];
    }
}

function saveViewedPostsToSession(viewedPosts) {
    try {
        sessionStorage.setItem('viewed_posts', JSON.stringify(viewedPosts));
    } catch (error) {
        // Le tracking reste fonctionnel meme si sessionStorage est bloque.
    }
}

function escapeHtml(unsafe) {
    return String(unsafe || '')
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

let currentStoryIndex = 0;
let storyAutoTimer = null;

function getStoryItems() {
    return Array.isArray(window.storyItems) ? window.storyItems : [];
}

function openStory(index) {
    const stories = getStoryItems();
    if (!stories.length || !stories[index]) return;

    currentStoryIndex = index;
    const modal = document.getElementById('storyModal');
    if (!modal) return;

    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    renderCurrentStory();
}

function closeStory() {
    const modal = document.getElementById('storyModal');
    if (modal) {
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
    }

    document.body.style.overflow = '';
    clearTimeout(storyAutoTimer);
    storyAutoTimer = null;
}

function nextStory() {
    const stories = getStoryItems();
    if (!stories.length) return;

    if (currentStoryIndex < stories.length - 1) {
        currentStoryIndex++;
        renderCurrentStory();
    } else {
        closeStory();
    }
}

function prevStory() {
    const stories = getStoryItems();
    if (!stories.length) return;

    currentStoryIndex = currentStoryIndex > 0 ? currentStoryIndex - 1 : stories.length - 1;
    renderCurrentStory();
}

function renderCurrentStory() {
    const stories = getStoryItems();
    const story = stories[currentStoryIndex];
    if (!story) return;

    const media = document.getElementById('storyMedia');
    const title = document.getElementById('storyModalTitle');
    const content = document.getElementById('storyModalContent');
    const cta = document.getElementById('storyCtaButton');
    const ctaText = document.getElementById('storyCtaText');
    const postLabel = document.getElementById('storyPostLabel');

    if (media) {
        media.innerHTML = story.image ? `<img src="${escapeHtml(story.image)}" alt="${escapeHtml(story.title)}">` : '';
    }
    if (title) title.textContent = story.title || '';
    if (content) content.textContent = story.content || '';
    if (ctaText) ctaText.textContent = story.cta_label || 'Voir le blog';
    if (postLabel) postLabel.textContent = story.post_title ? story.post_title : '';
    if (cta) cta.disabled = !story.post_id;

    trackStoryView(story.id);
    restartStoryProgress();
}

function restartStoryProgress() {
    clearTimeout(storyAutoTimer);

    const progress = document.getElementById('storyProgressBar');
    if (progress) {
        progress.classList.remove('running');
        progress.style.width = '0';
        void progress.offsetWidth;
        progress.style.width = '';
        progress.classList.add('running');
    }

    storyAutoTimer = setTimeout(nextStory, 7000);
}

function openStoryPost() {
    const stories = getStoryItems();
    const story = stories[currentStoryIndex];
    if (!story || !story.post_id) return;

    closeStory();
    setTimeout(() => {
        const card = document.querySelector(`[data-view-post-id="${story.post_id}"]`);
        if (!card) return;

        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        card.classList.add('story-highlight');
        setTimeout(() => card.classList.remove('story-highlight'), 1800);
    }, 120);
}

function trackStoryView(storyId) {
    if (!storyId) return;

    const viewedStories = getViewedStoriesFromSession();
    const id = String(storyId);
    if (viewedStories.includes(id)) return;

    viewedStories.push(id);
    saveViewedStoriesToSession(viewedStories);

    fetch('../ajax_track_story_view.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ story_id: storyId })
    }).catch(error => {
        console.error('Erreur tracking story:', error);
    });
}

function getViewedStoriesFromSession() {
    try {
        return JSON.parse(sessionStorage.getItem('viewed_stories') || '[]');
    } catch (error) {
        return [];
    }
}

function saveViewedStoriesToSession(viewedStories) {
    try {
        sessionStorage.setItem('viewed_stories', JSON.stringify(viewedStories));
    } catch (error) {
        // Story tracking stays optional if sessionStorage is blocked.
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const storyModal = document.getElementById('storyModal');
    if (storyModal) {
        storyModal.addEventListener('click', function (event) {
            if (event.target === storyModal) {
                closeStory();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        const modalIsOpen = storyModal && storyModal.classList.contains('show');
        if (!modalIsOpen) return;

        if (event.key === 'Escape') closeStory();
        if (event.key === 'ArrowRight') nextStory();
        if (event.key === 'ArrowLeft') prevStory();
    });
});

// Frontoffice: Load More Posts (Infinite Scroll / Pagination)
function loadMorePosts() {
    const btn = document.getElementById('loadMoreBtn');
    if (!btn) return;
    
    const currentPage = parseInt(btn.getAttribute('data-page'));
    const totalPages = parseInt(btn.getAttribute('data-total'));
    const search = btn.getAttribute('data-search') || '';
    
    if (currentPage >= totalPages) return;
    
    const nextPage = currentPage + 1;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';
    
    fetch(`../ajax_load_posts.php?page=${nextPage}&search=${encodeURIComponent(search)}`)
        .then(response => response.text())
        .then(html => {
            if (html.trim() !== '') {
                const grid = document.querySelector('.formations-grid');
                if (grid) {
                    grid.insertAdjacentHTML('beforeend', html);
                }
                
                btn.setAttribute('data-page', nextPage);
                if (nextPage >= totalPages) {
                    btn.parentElement.style.display = 'none'; // Hide if no more pages
                } else {
                    btn.disabled = false;
                    btn.innerHTML = 'Charger plus de formations';
                }
                
                // Re-init tracking for new elements
                if (typeof initPostViewTracking === 'function') {
                    initPostViewTracking();
                }
            } else {
                btn.parentElement.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading posts:', error);
            btn.disabled = false;
            btn.innerHTML = 'Erreur. Réessayer';
        });
}

// ============================================
// STAR RATING (Frontoffice)
// ============================================

/**
 * Highlights stars up to index on mouseenter.
 */
function hoverStars(postId, value) {
    const container = document.getElementById(`stars-${postId}`);
    if (!container) return;
    container.querySelectorAll('.star-btn').forEach(btn => {
        const v = parseInt(btn.getAttribute('data-value'));
        btn.classList.toggle('hovered', v <= value);
        btn.querySelector('i').className = v <= value ? 'fas fa-star' : 'far fa-star';
    });
}

/**
 * Restores stars to saved userRating on mouseleave.
 */
function resetStarHover(postId, savedRating) {
    const container = document.getElementById(`stars-${postId}`);
    if (!container) return;
    container.querySelectorAll('.star-btn').forEach(btn => {
        const v = parseInt(btn.getAttribute('data-value'));
        btn.classList.remove('hovered');
        const active = v <= savedRating;
        btn.classList.toggle('selected', active);
        btn.querySelector('i').className = active ? 'fas fa-star' : 'far fa-star';
    });
}

/**
 * Sends the rating to the server via AJAX and updates the card UI.
 */
function ratePost(postId, rating) {
    const container  = document.getElementById(`stars-${postId}`);
    const summary    = document.getElementById(`rating-summary-${postId}`);
    const feedback   = document.getElementById(`star-feedback-${postId}`);
    if (!container) return;

    // Optimistic UI: highlight immediately
    container.querySelectorAll('.star-btn').forEach(btn => {
        const v = parseInt(btn.getAttribute('data-value'));
        btn.classList.remove('hovered');
        const active = v <= rating;
        btn.classList.toggle('selected', active);
        btn.querySelector('i').className = active ? 'fas fa-star' : 'far fa-star';
        // Update mouseleave to use new saved rating
        btn.setAttribute('onmouseleave', `resetStarHover(${postId}, ${rating})`);
    });

    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('rating',  rating);

    fetch('../ajax_rate_post.php', {
        method: 'POST',
        body:   formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && summary) {
            // Update average and count
            const avgEl    = summary.querySelector('.star-avg-value');
            const countEl  = summary.querySelector('.star-count-badge');
            if (avgEl)   avgEl.textContent   = data.avg > 0 ? parseFloat(data.avg).toFixed(1) : '—';
            if (countEl) countEl.textContent = data.count + ' avis';

            // Show brief feedback toast
            if (feedback) {
                feedback.style.display = 'inline-block';
                feedback.style.animation = 'none';
                void feedback.offsetWidth; // reflow
                feedback.style.animation = 'fadeInOut 2.5s ease forwards';
                setTimeout(() => { feedback.style.display = 'none'; }, 2600);
            }
        }
    })
    .catch(err => {
        console.error('Erreur ratePost:', err);
    });
}

