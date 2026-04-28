# ⚙️ CONFIGURATION EMAIL POUR VOTRE PROJET

## 🔧 Options de Configuration

### Option 1: SENDMAIL (Local avec Mailhog) - **RECOMMANDÉ POUR LES TESTS**

**Avantages:** Facile à configurer, parfait pour le développement

**Étapes:**
1. Téléchargez et installez Mailhog: https://github.com/mailhog/MailHog/releases
2. Lancez Mailhog: `./MailHog` (Windows) ou `./mailhog` (Linux/Mac)
3. Mailhog sera accessible sur `http://localhost:1025` (SMTP) et `http://localhost:8025` (UI)
4. Configurez votre `.env`:
```
MAIL_DRIVER=sendmail
MAIL_FROM_NAME=Gestion Événements
MAIL_FROM_EMAIL=noreply@evenements.local
```

### Option 2: SMTP Gmail

**Étapes:**
1. Activez l'authentification 2FA sur votre compte Gmail
2. Générez un mot de passe d'application: https://myaccount.google.com/apppasswords
3. Configurez votre `.env`:
```
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-passe-applicatif
MAIL_ENCRYPTION=tls
MAIL_FROM_NAME=Gestion Événements
MAIL_FROM_EMAIL=votre-email@gmail.com
```

### Option 3: SMTP Autres Serveurs

**Exemples:**
- **Mailtrap**: `smtp.mailtrap.io:587` (Gratuit pour tests)
- **SendGrid**: `smtp.sendgrid.net:587`
- **Mailgun**: `smtp.mailgun.org:587`

Contactez votre fournisseur pour les identifiants.

## 📝 Création du fichier .env

1. Copiez `.env.example` en `.env`:
```bash
cp .env.example .env
```

2. Éditez `.env` et remplissez vos paramètres

3. **⚠️ IMPORTANT:** Ajoutez `.env` à `.gitignore` pour ne pas committer vos identifiants!

## 🧪 Test de l'Email

1. Allez sur la page des événements
2. Cliquez sur "S'inscrire à l'événement"
3. Remplissez le formulaire avec votre email
4. Soumettez

Si SENDMAIL+Mailhog est utilisé:
- Ouvrez `http://localhost:8025` pour voir l'email dans l'interface Mailhog

Si SMTP est utilisé:
- L'email sera envoyé au serveur configuré
- Vérifiez votre boîte email

## 📧 Texte de l'Email

L'email de confirmation contient:
- Confirmation d'inscription
- Titre de l'événement
- Date et lieu de l'événement
- Design professionnel et responsive

## ❓ Dépannage

### L'email n'est pas envoyé
- Vérifiez les logs: `error_log()` dans `config/Mailer.php`
- Vérifiez que vos identifiants SMTP sont corrects
- Assurez-vous que Mailhog fonctionne (si sendmail)

### L'inscription échoue
- L'inscription est toujours confirmée même si l'email échoue
- Vérifiez la base de données pour voir si l'inscription est présente

### Erreur "SMTP Connection Failed"
- Vérifiez le host et le port
- Vérifiez que le serveur SMTP est accessible
- Essayez avec Mailhog pour éliminer les problèmes de réseau
