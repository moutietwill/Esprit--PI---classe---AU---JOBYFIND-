# 📧 EMAIL - GUIDE D'UTILISATION RAPIDE

## ✅ Qu'est-ce qui a été mis en place?

1. **Classe Mailer** (`config/Mailer.php`) - Envoie les emails
2. **Configuration Email** (`config/Mail.php`) - Paramètres d'envoi
3. **Variables d'environnement** (`.env`) - Vos identifiants
4. **Intégration** - Les emails sont envoyés à l'inscription
5. **Page de test** (`test-email.php`) - Testez le système

## 🚀 DÉMARRAGE RAPIDE (MODE LOCAL)

### Étape 1: Installer Mailhog
```
1. Téléchargez: https://github.com/mailhog/MailHog/releases
2. Lancez: ./MailHog.exe (Windows)
3. Mailhog sera accessible sur: http://localhost:8025
```

### Étape 2: Vérifier la configuration
```
Le fichier .env est déjà configuré avec:
- MAIL_DRIVER=sendmail
- MAIL_FROM_EMAIL=noreply@evenements.local
```

### Étape 3: Tester
```
1. Allez sur: http://localhost/projet/projetweb_avec_evenements_fix/test-email.php
2. Remplissez le formulaire
3. Cliquez "Envoyer un Email de Test"
4. Ouvrez http://localhost:8025 pour voir l'email
```

## 🎯 TEST COMPLET (Inscription à un Événement)

1. Allez sur la page des événements
2. Cliquez "S'inscrire à l'événement"
3. Remplissez le formulaire avec **VOTRE EMAIL RÉEL**
4. Soumettez
5. Vous recevrez un email de confirmation!

### Voir l'email reçu:
- **Mailhog local**: Allez sur http://localhost:8025
- **Gmail/Autre serveur**: Vérifiez votre boîte email

## 📧 EMAIL DE CONFIRMATION

L'email contient automatiquement:
- ✓ Confirmation d'inscription
- ✓ Titre de l'événement
- ✓ Date de l'événement
- ✓ Lieu de l'événement
- ✓ Design professionnel

## 🔧 CONFIGURATION PERSONNALISÉE

### Option 1: Utiliser Gmail (RECOMMANDÉ POUR PRODUCTION)

1. Activez 2FA sur votre compte Gmail
2. Générez un mot de passe d'application: https://myaccount.google.com/apppasswords
3. Éditez `.env`:
```
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-applicatif
MAIL_ENCRYPTION=tls
MAIL_FROM_EMAIL=votre-email@gmail.com
```

### Option 2: Utiliser un autre serveur SMTP
Contactez votre fournisseur pour les paramètres et éditez `.env`

## 📝 FICHIERS CRÉÉS

```
config/
  ├─ Mailer.php          # Classe d'envoi d'email
  ├─ Mail.php            # Configuration
  └─ EnvLoader.php       # Loader des variables .env

.env                     # Vos paramètres email
.env.example             # Template de configuration
EMAIL_SETUP.md           # Documentation détaillée
test-email.php           # Page de test
```

## ⚠️ IMPORTANT

- **⚠️ NE COMMITTEZ PAS .env** - Contient vos identifiants!
- Ajoutez `.env` à `.gitignore`
- Les identifiants SMTP doivent rester privés

## ❓ DÉPANNAGE

### L'email n'est pas reçu?
1. Vérifiez les paramètres dans `.env`
2. Vérifiez Mailhog: http://localhost:8025
3. Vérifiez les logs PHP
4. Assurez-vous que le serveur SMTP est accessible

### L'inscription échoue?
- L'inscription est confirmée même si l'email échoue
- Vérifiez la base de données pour voir si l'inscription existe

### Besoin d'aide?
Consultez `EMAIL_SETUP.md` pour des informations détaillées

## 🎉 C'EST PRÊT!

Vous pouvez maintenant:
✓ Recevoir des emails lors des inscriptions
✓ Configurer vos propres paramètres SMTP
✓ Tester le système avant d'aller en production
