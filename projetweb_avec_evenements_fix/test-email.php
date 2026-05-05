<?php
/**
 * Script de test pour le système d'email
 * Accédez à: http://localhost/projet/projetweb_avec_evenements_fix/test-email.php
 */

require_once __DIR__ . '/config/Mailer.php';
require_once __DIR__ . '/config/EnvLoader.php';

header('Content-Type: text/html; charset=UTF-8');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Email - Gestion Événements</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .info {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 12px;
            margin: 15px 0;
            border-radius: 3px;
        }
        .success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 12px;
            margin: 15px 0;
            border-radius: 3px;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 12px;
            margin: 15px 0;
            border-radius: 3px;
            color: #721c24;
        }
        form {
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .config {
            background-color: #f0f0f0;
            padding: 12px;
            border-radius: 3px;
            margin: 10px 0;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Système Email</h1>
        
        <div class="info">
            <strong>Configuration Actuelle:</strong><br>
            <div class="config">
                <strong>Pilote:</strong> <code><?php echo EnvLoader::get('MAIL_DRIVER', 'sendmail'); ?></code><br>
                <strong>De:</strong> <code><?php echo EnvLoader::get('MAIL_FROM_EMAIL', 'noreply@evenements.local'); ?></code><br>
                <?php if (EnvLoader::get('MAIL_DRIVER') === 'smtp'): ?>
                    <strong>Host SMTP:</strong> <code><?php echo EnvLoader::get('MAIL_HOST'); ?></code><br>
                    <strong>Port:</strong> <code><?php echo EnvLoader::get('MAIL_PORT'); ?></code>
                <?php else: ?>
                    <strong>Mode:</strong> Sendmail (Local)<br>
                    <em>Pour voir les emails: Lancez Mailhog sur http://localhost:8025</em>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <?php
                $to = $_POST['email'] ?? '';
                $subject = $_POST['subject'] ?? 'Test Email';
                $body = $_POST['body'] ?? '';
                $result = false;

                if ($to && $subject && $body) {
                    try {
                        $mailer = new Mailer();
                        $result = $mailer->send($to, $subject, nl2br($body));
                        
                        if ($result) {
                            echo '<div class="success">✓ Email envoyé avec succès à ' . htmlspecialchars($to) . '</div>';
                        } else {
                            echo '<div class="error">✗ Erreur lors de l\'envoi de l\'email</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="error">✗ Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
            ?>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email Destinataire:</label>
                <input type="email" id="email" name="email" required placeholder="test@example.com">
            </div>
            
            <div class="form-group">
                <label for="subject">Sujet:</label>
                <input type="text" id="subject" name="subject" value="Test d'Email - Gestion Événements" required>
            </div>
            
            <div class="form-group">
                <label for="body">Corps du Message (HTML):</label>
                <textarea id="body" name="body" rows="8" required><?php 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo htmlspecialchars('<h1>Test Email</h1>
<p>Bonjour,</p>
<p>Ceci est un email de test pour vérifier que le système d\'email fonctionne correctement.</p>
<p>À bientôt !</p>');
}
                ?></textarea>
            </div>
            
            <button type="submit">📧 Envoyer un Email de Test</button>
        </form>

        <div class="info">
            <strong>📝 Instructions de Test:</strong>
            <ol>
                <li>Remplissez le formulaire avec votre email</li>
                <li>Cliquez sur "Envoyer un Email de Test"</li>
                <li>Si vous utilisez Mailhog, allez sur http://localhost:8025 pour voir l'email</li>
                <li>Sinon, vérifiez votre boîte email réelle</li>
            </ol>
        </div>

        <div class="info">
            <strong>🔗 Liens Utiles:</strong><br>
            • <a href="EMAIL_SETUP.md" target="_blank">Configuration Email (EMAIL_SETUP.md)</a><br>
            • <a href="/" target="_blank">Retour à la page d'accueil</a><br>
            • Mailhog UI: <a href="http://localhost:8025" target="_blank">http://localhost:8025</a>
        </div>
    </div>
</body>
</html>
