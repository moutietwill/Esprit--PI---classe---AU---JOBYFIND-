<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Accès Refusé — Jobyfind</title>
  <link rel="icon" type="image/png" href="assets/images/jlog.png">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    :root { --red: #ef4444; --dark: #0f172a; }
    body { 
      margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; 
      background: #f8fafc; display: flex; align-items: center; 
      justify-content: center; height: 100vh; text-align: center;
    }
    .card {
      background: white; padding: 40px; border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1); max-width: 450px;
      border-top: 5px solid var(--red);
    }
    i { font-size: 60px; color: var(--red); margin-bottom: 20px; }
    h1 { color: var(--dark); margin: 0 0 10px; font-size: 24px; }
    p { color: #64748b; line-height: 1.6; margin-bottom: 30px; }
    .btn {
      display: inline-block; padding: 12px 24px; background: var(--dark);
      color: white; text-decoration: none; border-radius: 8px; font-weight: 600;
      transition: opacity 0.2s;
    }
    .btn:hover { opacity: 0.9; }
  </style>
</head>
<body>
  <div class="card">
    <i class="fa fa-user-slash"></i>
    <h1>Compte Suspendu</h1>
    <p>
      Votre compte a été automatiquement suspendu par notre système de sécurité intelligent suite à une violation grave de nos conditions d'utilisation (détection de contenu inapproprié).
    </p>
    <a href="signin.php" class="btn">Retour à l'accueil</a>
  </div>
</body>
</html>
