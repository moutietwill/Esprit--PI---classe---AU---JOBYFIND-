<?php /** @var Event $event */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event->getTitre(), ENT_QUOTES, 'UTF-8'); ?> - Inscription</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f1f5f9; color: #1e293b; }
        
        .container { max-width: 900px; margin: 0 auto; padding: 2rem; }
        
        .event-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .event-header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .event-header p { font-size: 0.95rem; opacity: 0.9; }
        
        .content { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem; }
        @media (max-width: 768px) { .content { grid-template-columns: 1fr; } }
        
        .event-details {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .event-details h2 { font-size: 1.25rem; margin-bottom: 1rem; color: #333; }
        .detail-item { margin-bottom: 1.5rem; }
        .detail-label { font-size: 0.85rem; color: #64748b; text-transform: uppercase; font-weight: 600; }
        .detail-value { font-size: 1.1rem; color: #1e293b; margin-top: 0.25rem; }
        
        .inscription-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .inscription-form h2 { font-size: 1.25rem; margin-bottom: 1.5rem; color: #333; }
        
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.95rem; color: #333; }
        input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem; transition: border-color 0.2s; }
        input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        
        .button-group { display: flex; gap: 1rem; margin-top: 2rem; }
        button { flex: 1; padding: 0.85rem 1.5rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem; transition: all 0.2s; }
        .btn-submit { background: #667eea; color: white; }
        .btn-submit:hover { background: #5568d3; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
        .btn-submit:disabled { background: #cbd5e1; cursor: not-allowed; transform: none; }
        
        .btn-cancel { background: #e2e8f0; color: #475569; }
        .btn-cancel:hover { background: #cbd5e1; }
        
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        
        .loading { display: none; text-align: center; color: #64748b; }
        .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid #e2e8f0; border-top-color: #667eea; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 0.5rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .back-link { display: inline-block; margin-bottom: 2rem; color: #667eea; text-decoration: none; font-weight: 600; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="<?php echo htmlspecialchars($url('/events/show/' . urlencode((string) $event->getId())), ENT_QUOTES, 'UTF-8'); ?>" class="back-link">← Retour à l'événement</a>
        
        <div class="event-header">
            <h1><?php echo htmlspecialchars($event->getTitre(), ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>Inscription à l'événement</p>
        </div>

        <div class="content">
            <!-- Détails de l'événement -->
            <div class="event-details">
                <h2>📌 Détails de l'événement</h2>
                
                <div class="detail-item">
                    <div class="detail-label">Date</div>
                    <div class="detail-value"><?php echo htmlspecialchars($event->getDate(), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Lieu</div>
                    <div class="detail-value"><?php echo htmlspecialchars($event->getLieu(), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Description</div>
                    <div class="detail-value"><?php echo nl2br(htmlspecialchars(substr($event->getDescription(), 0, 200), ENT_QUOTES, 'UTF-8')); ?><?php echo strlen($event->getDescription()) > 200 ? '...' : ''; ?></div>
                </div>
            </div>

            <!-- Formulaire d'inscription -->
            <div class="inscription-form">
                <h2>📝 Inscription</h2>
                
                <div id="message-container"></div>
                
                <form id="inscription-form">
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" required placeholder="Votre prénom">
                    </div>

                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" required placeholder="Votre nom">
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required placeholder="votre.email@exemple.com">
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn-submit" id="submit-btn">
                            <span id="button-text">S'inscrire maintenant</span>
                            <span class="loading" id="loading"><span class="spinner"></span> Inscription en cours...</span>
                        </button>
                        <button type="button" class="btn-cancel" onclick="history.back()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('inscription-form');
        const messageContainer = document.getElementById('message-container');
        const submitBtn = document.getElementById('submit-btn');
        const buttonText = document.getElementById('button-text');
        const loading = document.getElementById('loading');
        const eventId = <?php echo json_encode($event->getId()); ?>;
        const inscriptionUrl = <?php echo json_encode($url('/events/inscrire/' . $event->getId())); ?>;
        const eventUrl = <?php echo json_encode($url('/events/show/' . $event->getId())); ?>;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const prenom = document.getElementById('prenom').value.trim();
            const nom = document.getElementById('nom').value.trim();
            const email = document.getElementById('email').value.trim();

            if (!prenom || !nom || !email) {
                showMessage('Veuillez remplir tous les champs', 'error');
                return;
            }

            // Valider email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showMessage('Veuillez entrer une adresse email valide', 'error');
                return;
            }

            submitBtn.disabled = true;
            buttonText.style.display = 'none';
            loading.style.display = 'inline';

            try {
                const response = await fetch(inscriptionUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        prenom,
                        nom,
                        email
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showMessage(`✓ ${data.message}`, 'success');
                    form.reset();
                    setTimeout(() => {
                        window.location.href = eventUrl;
                    }, 2000);
                } else {
                    showMessage(`✗ ${data.message}`, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showMessage('Erreur lors de l\'inscription. Veuillez réessayer.', 'error');
            } finally {
                submitBtn.disabled = false;
                buttonText.style.display = 'inline';
                loading.style.display = 'none';
            }
        });

        function showMessage(message, type) {
            messageContainer.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            messageContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    </script>
</body>
</html>
