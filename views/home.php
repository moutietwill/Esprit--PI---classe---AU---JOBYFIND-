<?php
    require_once(__DIR__ . '/../config/session.php');
    startAppSession();
    $isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobyFind - Accueil</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2d79ff;
            --secondary: #0b1f4b;
            --light: #f7f9fc;
        }

        * {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light) 0%, #fff 100%);
        }

        /* Navigation */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            text-decoration: underline;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 5rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-cta {
            padding: 1rem 2.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary-cta {
            background: white;
            color: var(--primary);
        }

        .btn-primary-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            color: var(--primary);
        }

        .btn-secondary-cta {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary-cta:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            color: white;
        }

        /* Section Title */
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            margin-top: 2rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0b1f4b;
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 1.5rem;
            letter-spacing: -0.5px;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 5px;
            background: linear-gradient(135deg, #2d79ff 0%, #0b1f4b 100%);
            border-radius: 3px;
        }

        .section-title p {
            font-size: 1.1rem;
            color: #666;
            font-weight: 500;
        }

        /* Card Styles */
        .card-modern {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card-modern:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .card-image {
            height: 200px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }

        .card-body {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 0.8rem;
        }

        .card-text {
            color: #666;
            margin-bottom: 1rem;
            flex-grow: 1;
        }

        .card-link {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-link:hover {
            color: var(--secondary);
            gap: 0.8rem;
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            padding: 3rem 0;
            margin: 3rem 0;
            border-radius: 16px;
        }

        .stat-box {
            text-align: center;
            padding: 2.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: white;
            margin: 0.5rem;
            box-shadow: 0 2px 8px rgba(45, 121, 255, 0.1);
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(45, 121, 255, 0.2);
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #2d79ff 0%, #0b1f4b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #0b1f4b;
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
            margin-bottom: 3rem;
        }

        .feature {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #2d79ff;
        }

        .feature:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(45, 121, 255, 0.2);
            border-top-color: #0b1f4b;
        }

        .feature-icon {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #2d79ff 0%, #0b1f4b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.2rem;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .feature:hover .feature-icon {
            font-size: 4rem;
            transform: scale(1.1);
        }

        .feature h4 {
            font-weight: 700;
            color: #0b1f4b;
            margin-bottom: 0.8rem;
            font-size: 1.2rem;
        }

        .feature p {
            color: #666;
            line-height: 1.6;
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .footer-section h5 {
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 2rem;
            padding-top: 2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .section-title h2 {
                font-size: 1.8rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-cta {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
<?php
    // Compute login URL for unauthenticated redirects
    $loginUrl = htmlspecialchars($url('/login'), ENT_QUOTES, 'UTF-8');
    // If not logged in, ALL module links point to login page
    $blogHref    = $isLoggedIn ? htmlspecialchars($legacyBlogUrl, ENT_QUOTES, 'UTF-8') : $loginUrl;
    $quizHref    = $isLoggedIn ? htmlspecialchars($url('/quiz'), ENT_QUOTES, 'UTF-8') : $loginUrl;
    $eventsHref  = $isLoggedIn ? htmlspecialchars($url('/events'), ENT_QUOTES, 'UTF-8') : $loginUrl;
    $formationHref = $isLoggedIn ? htmlspecialchars($url('/formation'), ENT_QUOTES, 'UTF-8') : $loginUrl;
?>
<?php
    // Calculer la base publique (préfixe d'URL) pour référencer correctement les assets/uploads
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $publicBase = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $publicBase = ($publicBase && $publicBase !== '.' && $publicBase !== '/') ? $publicBase : '';
    $uploadsPrefix = $publicBase . '/uploads';
?>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo htmlspecialchars($url('/'), ENT_QUOTES, 'UTF-8'); ?>">
                <i class="fas fa-rocket"></i> JobyFind
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $blogHref ?>" onclick="return checkAccess(event)">
                            <i class="fas fa-newspaper"></i> Blog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $quizHref ?>" onclick="return checkAccess(event)">
                            <i class="fas fa-graduation-cap"></i> Quiz
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $eventsHref ?>" onclick="return checkAccess(event)">
                            <i class="fas fa-calendar"></i> Événements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $formationHref ?>" onclick="return checkAccess(event)">
                            <i class="fas fa-graduation-cap"></i> Formations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo htmlspecialchars($url('/profile'), ENT_QUOTES, 'UTF-8'); ?>" onclick="return checkAccess(event)">
                            <i class="fas fa-user"></i> Espace utilisateur
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
                <h1><i class="fas fa-star"></i> Bienvenue sur Notre JobyFind</h1>
            <p>Découvrez des articles inspirants et des événements exceptionnels</p>
            <div class="hero-buttons">
                <a href="<?= $blogHref ?>" class="btn-cta btn-primary-cta" onclick="return checkAccess(event)">
                    <i class="fas fa-newspaper"></i> Lire le Blog
                </a>
                <a href="<?= $eventsHref ?>" class="btn-cta btn-secondary-cta" onclick="return checkAccess(event)">
                    <i class="fas fa-calendar"></i> Voir les Événements
                </a>
                <a href="<?= $formationHref ?>" class="btn-cta btn-secondary-cta" onclick="return checkAccess(event)">
                    <i class="fas fa-graduation-cap"></i> Nos Formations
                </a>
                <a href="<?php echo htmlspecialchars($url('/login'), ENT_QUOTES, 'UTF-8'); ?>" class="btn-cta btn-secondary-cta">
                    <i class="fas fa-user"></i> Connexion
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Stats Section -->
        <div class="stats-section">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-number">150+</div>
                        <div class="stat-label">Articles</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Événements</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Utilisateurs</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Support</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="section-title">
            <h2>Nos Fonctionnalités</h2>
            <p>Tout ce que vous devez savoir en un seul endroit</p>
        </div>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h4>Blog Riche</h4>
                <p>Articles détaillés, tutoriels et guides pour vous aider à progresser.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h4>Événements Variés</h4>
                <p>Conférences, formations et rencontres professionnelles régulières.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h4>Communauté Active</h4>
                <p>Connectez-vous avec d'autres professionnels et partagez vos idées.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h4>Contenu de Qualité</h4>
                <p>Articles et événements sélectionnés par nos experts.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h4>Recherche Facile</h4>
                <p>Trouvez rapidement ce que vous cherchez avec nos filtres avancés.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h4>Mobile Friendly</h4>
                <p>Accédez à JobyFind depuis n'importe quel appareil.</p>
            </div>
        </div>

        <!-- Blog Preview -->
        <div class="section-title" style="margin-top: 4rem;">
            <h2>Derniers Articles</h2>
            <p>Les publications les plus récentes du blog</p>
        </div>

        <div class="row mb-5">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-modern">
                    <div class="card-image">
                        <i class="fas fa-code"></i>
                    </div>
                    <div class="card-body">
                        <div class="card-title">Développement Web</div>
                        <p class="card-text">Apprenez les dernières technologies pour développer des sites web modernes et performants.</p>
                        <a href="<?= $blogHref ?>" class="card-link" onclick="return checkAccess(event)">
                            Lire plus <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-modern">
                    <div class="card-image">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="card-body">
                        <div class="card-title">Formations</div>
                        <p class="card-text">Découvrez nos guides complets pour développer vos compétences professionnelles.</p>
                        <a href="<?php echo htmlspecialchars($legacyBlogUrl . '?category=' . urlencode('Gestion'), ENT_QUOTES, 'UTF-8'); ?>" class="card-link" onclick="return checkAccess(event)">
                            Lire plus <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-modern">
                    <div class="card-image">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="card-body">
                        <div class="card-title">Emploi</div>
                        <p class="card-text">Conseils, offres d'emploi et actualités du marché du travail.</p>
                        <a href="<?php echo htmlspecialchars($legacyBlogUrl . '?category=' . urlencode('Communication'), ENT_QUOTES, 'UTF-8'); ?>" class="card-link" onclick="return checkAccess(event)">
                            Lire plus <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Blog Posts -->
        <div class="section-title">
            <h2>Derniers Articles</h2>
            <p>Les publications les plus récentes de notre blog</p>
        </div>

        <div class="row mb-5">
            <?php
            // Récupérer les articles récents
            $blogPosts = [];
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
                $stmt->execute();
                $blogPosts = $stmt->fetchAll();
            } catch (Exception $e) {
                // Erreur de connexion
            }

            if (!empty($blogPosts)): 
                foreach ($blogPosts as $post):
            ?>
            <div class="col-md-4 mb-4">
                <div class="card-modern">
                    <?php
                        // Déterminer la source de l'image du post (uploads ou chemin public)
                        $postImageSrc = '';
                        if (!empty($post['cover_image'])) {
                            $raw = $post['cover_image'];
                            if (preg_match('#^(https?:)?//#i', $raw) || strpos($raw, 'public/') === 0 || strpos($raw, '/') === 0) {
                                $postImageSrc = $raw;
                            } else {
                                $postImageSrc = $uploadsPrefix . '/' . $raw;
                            }
                        }
                    ?>
                    <div class="card-image" style="background: linear-gradient(135deg, #2d79ff 0%, #0b1f4b 100%);">
                        <?php if ($postImageSrc): ?>
                            <img src="<?php echo htmlspecialchars($postImageSrc); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width:100%;height:100%;object-fit:cover;display:block;border-top-left-radius:12px;border-top-right-radius:12px;">
                        <?php else: ?>
                            <i class="fas fa-newspaper"></i>
                        <?php endif; ?>
                    </div>
                    <div class="card-body" style="cursor: pointer;" onclick="checkAccess(event)">
                        <div style="font-size: 0.85rem; color: #2d79ff; font-weight: 700; margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($post['category'] ?? 'Blog'); ?>
                        </div>
                        <h5 class="card-title"><?php echo htmlspecialchars(substr($post['title'], 0, 60)); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($post['content'], 0, 100)); ?>...</p>
                        <small style="color: #999;">
                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($post['created_at'] ?? date('Y-m-d'))); ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="col-12" style="text-align: center; padding: 2rem; color: #999;">
                <p>Aucun article disponible pour le moment.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Latest Events -->
        <div class="section-title">
            <h2>Événements à Venir</h2>
            <p>Découvrez nos prochains événements et opportunités</p>
        </div>

        <div class="row mb-5">
            <?php
            // Récupérer les événements à venir
            $upcomingEvents = [];
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT * FROM evenement WHERE date >= CURDATE() ORDER BY date ASC LIMIT 3");
                $stmt->execute();
                $upcomingEvents = $stmt->fetchAll();
            } catch (Exception $e) {
                // Erreur de connexion
            }

            if (!empty($upcomingEvents)): 
                foreach ($upcomingEvents as $event):
            ?>
            <div class="col-md-4 mb-4">
                <div class="card-modern">
                    <?php
                        // Déterminer la source de l'image de l'événement
                        $eventImageSrc = '';
                        if (!empty($event['image'])) {
                            $rawE = $event['image'];
                            if (preg_match('#^(https?:)?//#i', $rawE) || strpos($rawE, 'public/') === 0 || strpos($rawE, '/') === 0) {
                                $eventImageSrc = $rawE;
                            } else {
                                // si c'est juste un nom de fichier, on tente uploads/
                                $eventImageSrc = $uploadsPrefix . '/' . $rawE;
                            }
                        }
                    ?>
                    <div class="card-image" style="background: linear-gradient(135deg, #2d79ff 0%, #0b1f4b 100%); font-size: 2rem;">
                        <?php if ($eventImageSrc): ?>
                            <img src="<?php echo htmlspecialchars($eventImageSrc); ?>" alt="<?php echo htmlspecialchars($event['titre']); ?>" style="width:100%;height:100%;object-fit:cover;display:block;border-top-left-radius:12px;border-top-right-radius:12px;">
                        <?php else: ?>
                            <i class="fas fa-calendar-alt"></i>
                        <?php endif; ?>
                    </div>
                    <div class="card-body" style="cursor: pointer;" onclick="checkAccess(event)">
                        <div style="font-size: 0.85rem; color: #2d79ff; font-weight: 700; margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($event['categorie'] ?? 'Événement'); ?>
                        </div>
                        <h5 class="card-title"><?php echo htmlspecialchars(substr($event['titre'], 0, 50)); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($event['description'], 0, 80)); ?>...</p>
                        <small style="color: #999;">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(substr($event['lieu'], 0, 30)); ?>
                        </small>
                        <br>
                        <small style="color: #999;">
                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($event['date'] ?? date('Y-m-d'))); ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="col-12" style="text-align: center; padding: 2rem; color: #999;">
                <p>Aucun événement à venir pour le moment.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- CTA Section -->
        <div style="text-align: center; padding: 3rem; background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); border-radius: 12px; margin-bottom: 3rem; border: 2px solid #2d79ff;">
            <h3 style="color: #0b1f4b; margin-bottom: 1rem; font-weight: 800;">Prêt à explorer?</h3>
            <p style="color: #666; margin-bottom: 1.5rem; font-size: 1.1rem;">Plongez dans notre communauté et découvrez des contenus extraordinaires</p>
            <a href="<?= $blogHref ?>" class="btn-cta btn-primary-cta" style="background: linear-gradient(135deg, #2d79ff 0%, #0b1f4b 100%); color: white;" onclick="return checkAccess(event)">
                Commencer maintenant
            </a>
        </div>
    </div>

        

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-3 footer-section">
                    <h5><i class="fas fa-newspaper"></i> Blog</h5>
                    <ul>
                        <li><a href="<?= $blogHref ?>" onclick="return checkAccess(event)">Tous les articles</a></li>
                        <li><a href="<?= $blogHref ?>" onclick="return checkAccess(event)">Développement</a></li>
                        <li><a href="<?= $blogHref ?>" onclick="return checkAccess(event)">Gestion</a></li>
                        <li><a href="<?= $blogHref ?>" onclick="return checkAccess(event)">Créer un article</a></li>
                    </ul>
                </div>
                <div class="col-md-3 footer-section">
                    <h5><i class="fas fa-calendar"></i> Événements</h5>
                    <ul>
                        <li><a href="<?= $eventsHref ?>" onclick="return checkAccess(event)">Tous les événements</a></li>
                        <li><a href="<?= $eventsHref ?>" onclick="return checkAccess(event)">Créer un événement</a></li>
                        <li><a href="<?= $eventsHref ?>" onclick="return checkAccess(event)">Gestion</a></li>
                    </ul>
                </div>
                <div class="col-md-3 footer-section">
                    <h5><i class="fas fa-book"></i> Ressources</h5>
                    <ul>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">API</a></li>
                        <li><a href="#">Support</a></li>
                    </ul>
                </div>
                <div class="col-md-3 footer-section">
                    <h5><i class="fas fa-link"></i> Suivez-nous</h5>
                    <div style="display: flex; gap: 1rem; font-size: 1.3rem;">
                        <a href="#">Facebook</a> | 
                        <a href="#">Twitter</a> | 
                        <a href="#">LinkedIn</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 JobyFind. Tous droits réservés. | 
                <a href="#">Politique de confidentialité</a> | 
                <a href="#">Conditions d'utilisation</a></p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkAccess(event) {
            const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
            if (!isLoggedIn) {
                event.preventDefault();
                window.location.href = "<?php echo htmlspecialchars($url('/login'), ENT_QUOTES, 'UTF-8'); ?>";
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
