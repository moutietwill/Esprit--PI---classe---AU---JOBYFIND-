<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>JobyFind</title>
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

        .navbar-brand i {
            font-size: 1.8rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem !important;
            border-radius: 6px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 1rem;
            width: 0;
            height: 2px;
            background: white;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: calc(100% - 2rem);
        }

        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.5);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.55%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .navbar-nav {
            gap: 0.5rem;
        }

        .btn-login {
            background: white;
            color: var(--primary);
            font-weight: 600;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 3rem 0;
            margin-top: 4rem;
        }

        .footer-section h5 {
            font-weight: 700;
            margin-bottom: 1rem;
            color: #fff;
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

        .footer-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 2rem;
            padding-top: 2rem;
        }

        .breadcrumb-custom {
            background: transparent;
            margin-bottom: 2rem;
        }

        .breadcrumb-custom .breadcrumb-item {
            color: #666;
        }

        .breadcrumb-custom .breadcrumb-item.active {
            color: var(--primary);
            font-weight: 600;
        }

        .breadcrumb-custom a {
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .breadcrumb-custom a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.2rem;
            }

            .nav-link {
                margin: 0.2rem 0;
            }

            .nav-link::after {
                display: none;
            }

            .btn-login {
                margin-top: 1rem;
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
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
                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/blog') !== false) ? 'active' : ''; ?>" 
                           href="<?php echo htmlspecialchars($legacyBlogUrl, ENT_QUOTES, 'UTF-8'); ?>" onclick="return checkAccess(event)">
                            <i class="fas fa-newspaper"></i> Blog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/quiz') !== false) ? 'active' : ''; ?>" 
                           href="<?php echo htmlspecialchars($url('/quiz'), ENT_QUOTES, 'UTF-8'); ?>" onclick="return checkAccess(event)">
                            <i class="fas fa-graduation-cap"></i> Quiz
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/events') !== false) ? 'active' : ''; ?>" 
                           href="<?php echo htmlspecialchars($url('/events'), ENT_QUOTES, 'UTF-8'); ?>" onclick="return checkAccess(event)">
                            <i class="fas fa-calendar"></i> Événements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/formation') !== false) ? 'active' : ''; ?>" 
                           href="<?php echo htmlspecialchars($url('/formation'), ENT_QUOTES, 'UTF-8'); ?>" onclick="return checkAccess(event)">
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

    <!-- Page Content (children will be included here) -->
    <?php echo $content ?? ''; ?>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-3 footer-section">
                    <h5><i class="fas fa-newspaper"></i> Blog</h5>
                    <ul>
                        <li><a href="<?php echo htmlspecialchars($legacyBlogUrl, ENT_QUOTES, 'UTF-8'); ?>">Tous les articles</a></li>
                        <li><a href="<?php echo htmlspecialchars($legacyBlogUrl . '?category=' . urlencode('Développement'), ENT_QUOTES, 'UTF-8'); ?>">Développement</a></li>
                        <li><a href="<?php echo htmlspecialchars($legacyBlogUrl . '?category=' . urlencode('Gestion'), ENT_QUOTES, 'UTF-8'); ?>">Gestion</a></li>
                        <li><a href="<?php echo htmlspecialchars($legacyBlogCreateUrl, ENT_QUOTES, 'UTF-8'); ?>">Créer un article</a></li>
                    </ul>
                </div>
                <div class="col-md-3 footer-section">
                    <h5><i class="fas fa-calendar"></i> Événements</h5>
                    <ul>
                        <li><a href="<?php echo htmlspecialchars($url('/events'), ENT_QUOTES, 'UTF-8'); ?>">Tous les événements</a></li>
                        <li><a href="<?php echo htmlspecialchars($url('/events/create'), ENT_QUOTES, 'UTF-8'); ?>">Créer un événement</a></li>
                        <li><a href="<?php echo htmlspecialchars($url('/admin/events'), ENT_QUOTES, 'UTF-8'); ?>">Gestion</a></li>
                    </ul>
                </div>
                <div class="col-md-3 footer-section">
                    <h5><i class="fas fa-graduation-cap"></i> Formations</h5>
                    <ul>
                        <li><a href="<?php echo htmlspecialchars($url('/formation'), ENT_QUOTES, 'UTF-8'); ?>">Toutes les formations</a></li>
                        <li><a href="<?php echo htmlspecialchars($url('/admin/formations'), ENT_QUOTES, 'UTF-8'); ?>">Gestion</a></li>
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
                        <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>

            <div class="footer-divider">
                <div class="row">
                    <div class="col-md-6">
                        <p>&copy; 2026 JobyFind. Tous droits réservés.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="#">Politique de confidentialité</a> | 
                        <a href="#">Conditions d'utilisation</a> | 
                        <a href="#">Contact</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkAccess(event) {
            const isLoggedIn = <?php echo json_encode($isLoggedIn ?? false); ?>;
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
