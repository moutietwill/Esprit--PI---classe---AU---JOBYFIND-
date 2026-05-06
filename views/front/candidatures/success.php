<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Candidature envoyée — Jobyfind</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; }
    :root {
      --blue-primary: #153c8f;
      --blue-light: #2d79ff;
      --green: #10b981;
      --green-light: #d1fae5;
      --text-main: #1f2937;
      --text-muted: #6b7280;
      --bg: #f8fafc;
      --surface: #ffffff;
    }
    body { background: var(--bg); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }

    /* ── Navbar ── */
    .navbar {
      display: flex; justify-content: space-between; align-items: center;
      padding: 15px 40px; background: var(--surface);
      border-bottom: 1px solid #e2e8f0;
      box-shadow: 0 1px 8px rgba(0,0,0,.05);
    }
    .logo { font-family: 'DM Serif Display', serif; font-size: 24px; color: #111827; text-decoration: none; }
    .logo span { color: var(--blue-light); }
    .nav-links { display: flex; gap: 30px; }
    .nav-link { text-decoration: none; color: var(--text-muted); font-weight: 500; transition: color .15s; }
    .nav-link:hover { color: var(--blue-light); }

    /* ── Success Container ── */
    .success-wrapper {
      flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 20px;
    }
    .success-card {
      background: var(--surface);
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0,0,0,.08);
      max-width: 560px;
      width: 100%;
      text-align: center;
      overflow: hidden;
      animation: card-in 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes card-in {
      from { opacity: 0; transform: translateY(30px) scale(0.95); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ── Header Banner ── */
    .success-header {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      padding: 40px 30px 35px;
      position: relative;
      overflow: hidden;
    }
    .success-header::before {
      content: ''; position: absolute; width: 300px; height: 300px;
      border-radius: 50%; top: -150px; right: -80px;
      background: rgba(255,255,255,0.08);
    }
    .success-icon {
      width: 80px; height: 80px; border-radius: 50%;
      background: rgba(255,255,255,0.2);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 16px;
      font-size: 36px; color: white;
      animation: icon-bounce 0.8s 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }
    @keyframes icon-bounce {
      from { transform: scale(0); }
      to   { transform: scale(1); }
    }
    .success-header h1 {
      color: white; font-size: 24px; font-weight: 700; margin-bottom: 6px;
    }
    .success-header p {
      color: rgba(255,255,255,0.75); font-size: 14px;
    }

    /* ── Body Content ── */
    .success-body {
      padding: 30px;
    }

    /* ── SMS Status ── */
    .sms-status {
      display: flex; align-items: flex-start; gap: 14px;
      padding: 16px 18px; border-radius: 14px;
      margin-bottom: 24px; text-align: left;
    }
    .sms-status.sent {
      background: #f0fdf4; border: 1px solid #bbf7d0;
    }
    .sms-status.failed {
      background: #fffbeb; border: 1px solid #fde68a;
    }
    .sms-status-icon {
      width: 40px; height: 40px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; flex-shrink: 0;
    }
    .sms-status.sent .sms-status-icon { background: #dcfce7; color: #16a34a; }
    .sms-status.failed .sms-status-icon { background: #fef3c7; color: #d97706; }
    .sms-status-text h4 { font-size: 14px; font-weight: 700; margin-bottom: 3px; }
    .sms-status.sent .sms-status-text h4 { color: #166534; }
    .sms-status.failed .sms-status-text h4 { color: #92400e; }
    .sms-status-text p { font-size: 13px; color: var(--text-muted); line-height: 1.5; }

    /* ── Steps Timeline ── */
    .steps {
      text-align: left;
      margin-bottom: 28px;
    }
    .steps h3 {
      font-size: 15px; font-weight: 700; color: var(--text-main);
      margin-bottom: 16px;
      display: flex; align-items: center; gap: 8px;
    }
    .step {
      display: flex; gap: 14px; padding-bottom: 18px;
      position: relative;
    }
    .step:last-child { padding-bottom: 0; }
    .step-dot {
      width: 32px; height: 32px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; flex-shrink: 0; font-weight: 700;
      position: relative; z-index: 1;
    }
    .step.done .step-dot   { background: #dcfce7; color: #16a34a; }
    .step.active .step-dot { background: #dbeafe; color: #2563eb; }
    .step.pending .step-dot { background: #f1f5f9; color: #94a3b8; }
    
    /* Connector line */
    .step:not(:last-child)::after {
      content: '';
      position: absolute;
      left: 15px;
      top: 32px;
      width: 2px;
      height: calc(100% - 32px);
      background: #e2e8f0;
    }
    .step.done:not(:last-child)::after { background: #bbf7d0; }

    .step-info h4 { font-size: 14px; font-weight: 600; color: var(--text-main); margin-bottom: 2px; padding-top: 5px; }
    .step-info p  { font-size: 12px; color: var(--text-muted); }

    /* ── Action Buttons ── */
    .success-actions {
      display: flex; gap: 10px; justify-content: center;
    }
    .btn-primary {
      padding: 12px 28px; border-radius: 12px; border: none;
      background: linear-gradient(135deg, #2d79ff, #1e56c4);
      color: white; font-size: 14px; font-weight: 600;
      cursor: pointer; text-decoration: none;
      display: flex; align-items: center; gap: 8px;
      transition: all 0.2s ease;
      font-family: 'DM Sans', sans-serif;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(45, 121, 255, 0.3);
    }
    .btn-outline {
      padding: 12px 28px; border-radius: 12px;
      border: 1.5px solid #e2e8f0;
      background: transparent;
      color: var(--text-muted); font-size: 14px; font-weight: 600;
      cursor: pointer; text-decoration: none;
      display: flex; align-items: center; gap: 8px;
      transition: all 0.2s ease;
      font-family: 'DM Sans', sans-serif;
    }
    .btn-outline:hover {
      border-color: var(--blue-light); color: var(--blue-light);
      transform: translateY(-2px);
    }

    /* ── Confetti Animation ── */
    .confetti-container {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      pointer-events: none; z-index: 9999; overflow: hidden;
    }
    .confetti {
      position: absolute; width: 10px; height: 10px;
      top: -10px;
      animation: confetti-fall linear forwards;
    }
    @keyframes confetti-fall {
      to { top: 110vh; transform: rotate(720deg); }
    }
  </style>
</head>
<body>

  <nav class="navbar">
    <a href="index.php?action=front_offres" class="logo">Joby<span>find</span></a>
    <div class="nav-links">
      <a href="#" class="nav-link">Accueil</a>
      <a href="index.php?action=front_offres" class="nav-link">Offres</a>
    </div>
  </nav>

  <div class="success-wrapper">
    <div class="success-card">
      <div class="success-header">
        <div class="success-icon"><i class="fa-solid fa-check"></i></div>
        <h1>Candidature envoyée !</h1>
        <p>Votre dossier a été enregistré avec succès</p>
      </div>

      <div class="success-body">

        <!-- Email Status -->
        <?php
          $mailStatus = isset($_GET['mail']) ? $_GET['mail'] : 'unknown';
        ?>
        <?php if ($mailStatus === 'sent'): ?>
          <div class="sms-status sent">
            <div class="sms-status-icon"><i class="fa-solid fa-envelope-circle-check"></i></div>
            <div class="sms-status-text">
              <h4><i class="fa-solid fa-circle-check"></i> E-mail de confirmation envoyé</h4>
              <p>Un message a été envoyé à votre adresse e-mail confirmant la réception de votre candidature.</p>
            </div>
          </div>
        <?php elseif ($mailStatus === 'failed'): ?>
          <div class="sms-status failed">
            <div class="sms-status-icon"><i class="fa-solid fa-envelope-circle-xmark"></i></div>
            <div class="sms-status-text">
              <h4>E-mail non envoyé</h4>
              <p>Votre candidature a bien été enregistrée, mais l'e-mail de confirmation n'a pas pu être envoyé. Pas d'inquiétude, nous traiterons votre dossier normalement.</p>
            </div>
          </div>
        <?php endif; ?>

        <!-- Steps Timeline -->
        <div class="steps">
          <h3><i class="fa-solid fa-list-check"></i> Prochaines étapes</h3>
          <div class="step done">
            <div class="step-dot"><i class="fa-solid fa-check" style="font-size:12px;"></i></div>
            <div class="step-info">
              <h4>Candidature reçue</h4>
              <p>Votre dossier est enregistré dans notre système</p>
            </div>
          </div>
          <div class="step active">
            <div class="step-dot"><i class="fa-solid fa-spinner" style="font-size:12px;"></i></div>
            <div class="step-info">
              <h4>Vérification des documents</h4>
              <p>Notre équipe examine votre CV, lettre de motivation et pièces jointes</p>
            </div>
          </div>
          <div class="step pending">
            <div class="step-dot">3</div>
            <div class="step-info">
              <h4>Résultat envoyé</h4>
              <p>Vous recevrez le résultat par e-mail</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="success-actions">
          <a href="index.php?action=front_offres" class="btn-primary">
            <i class="fa-solid fa-arrow-left"></i> Retour aux offres
          </a>
        </div>

      </div>
    </div>
  </div>

  <!-- Confetti -->
  <div class="confetti-container" id="confettiContainer"></div>
  <script>
    (function() {
      const container = document.getElementById('confettiContainer');
      const colors = ['#2d79ff', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
      const shapes = ['circle', 'square'];

      for (let i = 0; i < 50; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        const color = colors[Math.floor(Math.random() * colors.length)];
        const shape = shapes[Math.floor(Math.random() * shapes.length)];
        const size = Math.random() * 8 + 6;
        const left = Math.random() * 100;
        const delay = Math.random() * 2;
        const duration = Math.random() * 2 + 2;

        confetti.style.left = left + '%';
        confetti.style.width = size + 'px';
        confetti.style.height = size + 'px';
        confetti.style.background = color;
        confetti.style.borderRadius = shape === 'circle' ? '50%' : '2px';
        confetti.style.animationDuration = duration + 's';
        confetti.style.animationDelay = delay + 's';
        confetti.style.opacity = Math.random() * 0.7 + 0.3;

        container.appendChild(confetti);
      }

      // Remove confetti after animation
      setTimeout(function() { container.remove(); }, 5000);
    })();
  </script>

</body>
</html>
