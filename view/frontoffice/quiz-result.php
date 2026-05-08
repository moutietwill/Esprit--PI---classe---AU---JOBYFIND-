<?php
$score    = isset($_GET['score']) ? (int)$_GET['score'] : 0;
$max      = isset($_GET['max']) ? (int)$_GET['max'] : 0;
$quizName = isset($_GET['quiz']) ? $_GET['quiz'] : "Quiz";
$userName = isset($_GET['name']) && !empty(trim($_GET['name'])) ? $_GET['name'] : "Candidat Anonyme";

$percentage = ($max > 0) ? round(($score / $max) * 100) : 0;

$message = "Bien joué !";
$icon = "fa-trophy";
$color = "#22c55e"; // Success green

if ($percentage < 50) {
    $message = "Continuez vos efforts !";
    $icon = "fa-redo";
    $color = "#f59e0b"; // Warning amber
} elseif ($percentage >= 80) {
    $message = "Excellent travail !";
    $icon = "fa-award";
    $color = "#2d79ff"; // Brand blue
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat - <?= htmlspecialchars($quizName) ?> - Jobyfind</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --blue: #2d79ff;
            --navy: #0b1f4b;
            --bg: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --muted: #64748b;
            --radius: 16px;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { 
            font-family: 'DM Sans', sans-serif; 
            background: var(--bg); 
            display: flex; 
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── NAVBAR ── */
        .navbar {
            background: var(--surface);
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .logo {
            font-family: 'DM Serif Display', serif;
            font-size: 24px;
            color: var(--navy);
            text-decoration: none;
        }
        .logo span { color: var(--blue); }

        .nav-links {
            display: flex;
            gap: 30px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }
        .nav-link {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            font-size: 15px;
        }
        .nav-link.active { color: var(--blue); }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .result-card { 
            background: var(--surface); 
            padding: 50px; 
            border-radius: var(--radius); 
            box-shadow: 0 20px 40px rgba(11, 31, 75, 0.08); 
            max-width: 550px; 
            width: 100%; 
            text-align: center; 
            border: 1px solid var(--border);
        }

        .icon-container { 
            width: 100px; 
            height: 100px; 
            background: <?= $color ?>; 
            color: white; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 48px; 
            margin: 0 auto 30px; 
            box-shadow: 0 10px 20px <?= $color ?>44; 
        }

        h1 { font-family: 'DM Serif Display', serif; margin: 0 0 10px; color: var(--navy); font-size: 36px; }
        .quiz-title { color: var(--muted); font-size: 18px; margin-bottom: 40px; }

        .score-box {
            background: #f8fafc;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }
        .score-display { font-size: 72px; font-weight: 800; color: <?= $color ?>; line-height: 1; }
        .score-total { font-size: 24px; color: var(--muted); margin-top: 10px; }

        .percentage-container { margin-bottom: 40px; text-align: left; }
        .percentage-label { font-size: 14px; font-weight: 600; color: var(--navy); margin-bottom: 8px; display: flex; justify-content: space-between; }
        .percentage-bar { background: #e2e8f0; height: 12px; border-radius: 99px; overflow: hidden; }
        .percentage-fill { background: <?= $color ?>; height: 100%; width: <?= $percentage ?>%; transition: width 1s ease-out; }

        .btn-home { 
            display: inline-flex; 
            align-items: center; 
            gap: 10px;
            padding: 16px 40px; 
            background: #e2e8f0; 
            color: var(--text); 
            text-decoration: none; 
            border-radius: 12px; 
            font-weight: 600; 
            transition: all 0.2s; 
        }
        .btn-home:hover { background: #cbd5e1; }

        .btn-certif {
            display: inline-flex; 
            align-items: center; 
            gap: 10px;
            padding: 16px 40px; 
            background: linear-gradient(135deg, #eab308, #ca8a04); 
            color: white; 
            text-decoration: none; 
            border-radius: 12px; 
            font-weight: 700; 
            border: none;
            cursor: pointer;
            transition: all 0.2s; 
            font-family: inherit;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(202, 138, 4, 0.4);
        }
        .btn-certif:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(202, 138, 4, 0.5); }
        .btn-certif:disabled { opacity:0.7; cursor:wait; transform:none; }

        /* Secret Certificate Design (Offscreen rendering) */
        .cert-wrapper {
            position: absolute;
            left: -9999px;
            top: 0;
            width: 800px;
            height: 560px;
            background: #ffffff;
            padding: 20px;
        }
        .cert-inner {
            border: 8px solid #0b1f4b;
            padding: 8px;
            height: 100%;
        }
        .cert-content {
            border: 2px solid #ca8a04;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            text-align: center;
            position: relative;
            background: repeating-linear-gradient(45deg, #f8fafc, #f8fafc 10px, #ffffff 10px, #ffffff 20px);
        }
        .cert-title {
            font-family: 'DM Serif Display', serif;
            font-size: 42px;
            color: #0b1f4b;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
            position: relative;
        }
        .cert-subtitle {
            font-size: 18px;
            color: #64748b;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .cert-name {
            font-family: 'DM Serif Display', serif;
            font-size: 48px;
            color: #2d79ff;
            margin-bottom: 30px;
            font-style: italic;
        }
        .cert-details {
            font-size: 18px;
            color: #1e293b;
            line-height: 1.6;
            margin-bottom: 40px;
        }
        .cert-score { font-weight: 700; color: #ca8a04; }

        /* Badge/Ribbon Pure CSS */
        .ribbon-badge {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .badge-circle {
            width: 70px;
            height: 70px;
            background: #eab308;
            border-radius: 50%;
            border: 4px dashed white;
            box-shadow: 0 0 0 4px #ca8a04, 0 4px 10px rgba(0,0,0,0.2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            z-index: 2;
        }
        .badge-ribbons {
            display: flex;
            gap: 20px;
            margin-top: -20px;
            z-index: 1;
        }
        .ribbon-tail {
            width: 15px;
            height: 40px;
            background: #ca8a04;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 50% calc(100% - 10px), 0 100%);
        }
        .ribbon-tail.left { transform: rotate(15deg); }
        .ribbon-tail.right { transform: rotate(-15deg); }

        .cert-signature {
            position: absolute;
            bottom: 40px;
            right: 50px;
            text-align: center;
        }
        .sig-line {
            width: 150px;
            border-bottom: 2px solid #0b1f4b;
            margin-bottom: 5px;
        }
        .sig-text { font-size: 13px; color: #64748b; font-weight: 600; }
        .cert-date {
            position: absolute;
            bottom: 40px;
            left: 50px;
            text-align: center;
            font-size: 14px;
            color: #1e293b;
            font-weight: 600;
        }
    </style>
    <!-- Include HTML2Canvas & jsPDF securely -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>

<nav class="navbar">
    <a href="quizzes-list.php" class="logo">Joby<span>find</span></a>
    
    <div class="nav-links">
        <a href="#" class="nav-link">Formations</a>
        <a href="quizzes-list.php" class="nav-link active">Quiz</a>
        <a href="#" class="nav-link">À propos</a>
        <a href="#" class="nav-link">Contact</a>
    </div>

    <div></div>
</nav>

<div class="main-content">
    <div class="result-card">
        <div class="icon-container">
            <i class="fas <?= $icon ?>"></i>
        </div>
        <h1><?= $message ?></h1>
        <p class="quiz-title"><?= htmlspecialchars($quizName) ?></p>

        <div class="score-box">
            <div class="score-display"><?= $score ?></div>
            <div class="score-total">Points sur <?= $max ?></div>
        </div>

        <div class="percentage-container">
            <div class="percentage-label">
                <span>Progression</span>
                <span><?= $percentage ?>%</span>
            </div>
            <div class="percentage-bar">
                <div class="percentage-fill"></div>
            </div>
        </div>

        <div style="display:flex; justify-content:center; gap:16px;">
            <a href="quizzes-list.php" class="btn-home"><i class="fas fa-arrow-left"></i> Explorer</a>
            <?php if ($percentage >= 80): ?>
                <button id="btn-certif" class="btn-certif" onclick="downloadCertificate()">
                    <i class="fas fa-award"></i> Mon Certificat
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($percentage >= 80): ?>
<!-- Hidden Certificate Template -->
<div class="cert-wrapper" id="cert-wrapper">
    <div class="cert-inner">
        <div class="cert-content">
            <div style="position:absolute; top:30px; left:30px; font-family:'DM Serif Display',serif; font-size:24px; color:#0b1f4b; opacity:0.8;">Joby<span style="color:#2d79ff">find</span></div>
            
            <div class="cert-subtitle">Décerné et certifié officiel</div>
            <h2 class="cert-title">Certificat de Réussite</h2>
            
            <div style="font-size:16px; color:#64748b; margin-bottom:10px;">Fièrement délivré à</div>
            <div class="cert-name"><?= htmlspecialchars($userName) ?></div>
            
            <div class="cert-details">
                Pour avoir complété avec succès l'évaluation technique et théorique :<br>
                <strong>"<?= htmlspecialchars($quizName) ?>"</strong><br>
                Score d'excellence : <span class="cert-score"><?= $percentage ?>%</span>
            </div>

            <div class="ribbon-badge">
                <div class="badge-circle"><i class="fas fa-star"></i></div>
                <div class="badge-ribbons">
                    <div class="ribbon-tail left"></div>
                    <div class="ribbon-tail right"></div>
                </div>
            </div>

            <div class="cert-date">
                Date certifiée<br>
                <div style="border-bottom:1px solid #cbd5e1; padding-bottom:5px; margin-top:5px; font-weight:400;"><?= date('d F Y') ?></div>
            </div>

            <div class="cert-signature">
                <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 40'><path d='M10,30 Q20,10 30,25 T50,20 T70,30' fill='none' stroke='%230b1f4b' stroke-width='2'/></svg>" width="100" style="margin-bottom:-10px; opacity:0.6;">
                <div class="sig-line"></div>
                <div class="sig-text">Direction Pédagogique</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
async function downloadCertificate() {
    const btn = document.getElementById('btn-certif');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération en cours...';

    const certNode = document.getElementById('cert-wrapper');

    try {
        // html2canvas capture l'élément caché avec haute résolution
        const canvas = await html2canvas(certNode, {
            scale: 2, // Haute qualité
            backgroundColor: '#ffffff'
        });

        const imgData = canvas.toDataURL('image/png');
        
        // Orientation paysage, pixels
        const { jsPDF } = window.jspdf;
        // On crée un pdf A4 Landscape
        const pdf = new jsPDF({
            orientation: "landscape",
            unit: "px",
            format: [800, 560]
        });

        pdf.addImage(imgData, 'PNG', 0, 0, 800, 560);
        pdf.save("Certificat_Jobyfind_<?= htmlspecialchars($userName) ?>.pdf");

        btn.innerHTML = '<i class="fas fa-check"></i> Téléchargé !';
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-award"></i> Mon Certificat';
        }, 3000);
    } catch (err) {
        console.error("Erreur lors de l'export PDF: ", err);
        alert("Une erreur est apparue lors de la génération.");
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-award"></i> Mon Certificat';
    }
}
</script>

</body>
</html>
