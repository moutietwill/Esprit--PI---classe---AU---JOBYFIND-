<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Articles et Actualités</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --light: #f7f9fc;
        }

        body {
            background: linear-gradient(135deg, var(--light) 0%, #fff 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .blog-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .blog-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .blog-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .blog-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .blog-card-image {
            height: 250px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            overflow: hidden;
        }

        .blog-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .blog-card:hover .blog-card-image img {
            transform: scale(1.05);
        }

        .blog-card-body {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .blog-category {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            width: fit-content;
        }

        .blog-card-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .blog-card-excerpt {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 1rem;
            flex-grow: 1;
            line-height: 1.6;
        }

        .blog-card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
            color: #999;
        }

        .blog-card-stats {
            display: flex;
            gap: 1.5rem;
        }

        .blog-card-stat {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .blog-card-stat i {
            color: var(--primary);
        }

        .blog-read-more {
            display: inline-block;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .blog-read-more:hover {
            color: var(--secondary);
            transform: translateX(5px);
        }

        .sidebar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .sidebar-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary);
        }

        .search-box {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-box input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .category-list {
            list-style: none;
            padding: 0;
        }

        .category-list li {
            margin-bottom: 0.8rem;
        }

        .category-list a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #666;
            text-decoration: none;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .category-list a:hover,
        .category-list a.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            color: var(--primary);
            font-weight: 600;
        }

        .pagination {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }

        .pagination .page-link {
            color: var(--primary);
            border-color: var(--primary);
        }

        .pagination .page-link:hover {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .no-posts {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .no-posts i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .btn-create {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-create:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="blog-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-newspaper"></i> Blog</h1>
                    <p class="mb-0">Articles, tutoriels et actualités</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="<?php echo htmlspecialchars($url('/blog/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-create">
                        <i class="fas fa-plus"></i> Créer un article
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Articles -->
            <div class="col-lg-8">
                <?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> Article créé avec succès!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($posts)): ?>
                    <div class="no-posts">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucun article trouvé</h3>
                        <p>Il n'y a pas d'articles correspondant à votre recherche.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($posts as $post): ?>
                            <div class="col-md-6">
                                <div class="blog-card">
                                    <div class="blog-card-image">
                                        <img src="<?php echo htmlspecialchars($asset($post->getCoverImage()), ENT_QUOTES, 'UTF-8'); ?>" 
                                             alt="<?php echo htmlspecialchars($post->getTitle()); ?>">
                                    </div>
                                    <div class="blog-card-body">
                                        <span class="blog-category"><?php echo htmlspecialchars($post->getCategory() ?? 'Général'); ?></span>
                                        <h3 class="blog-card-title"><?php echo htmlspecialchars($post->getTitle()); ?></h3>
                                        <p class="blog-card-excerpt"><?php echo htmlspecialchars($post->getShortContent(120)); ?></p>
                                        <div class="blog-card-meta">
                                            <span><?php echo $post->getFormattedDate(); ?></span>
                                            <div class="blog-card-stats">
                                                <div class="blog-card-stat">
                                                    <i class="fas fa-eye"></i> <?php echo $post->getViews(); ?>
                                                </div>
                                                <div class="blog-card-stat">
                                                    <i class="fas fa-heart"></i> <?php echo $post->getLikes(); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="<?php echo htmlspecialchars($url('/blog/' . $post->getId()), ENT_QUOTES, 'UTF-8'); ?>" class="blog-read-more mt-3">
                                            Lire l'article <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="pagination">
                            <ul class="pagination">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo htmlspecialchars($url('/blog') . '?page=1', ENT_QUOTES, 'UTF-8'); ?>">Début</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo htmlspecialchars($url('/blog') . '?page=' . ($currentPage - 1), ENT_QUOTES, 'UTF-8'); ?>">Précédent</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($url('/blog') . '?page=' . $i, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo htmlspecialchars($url('/blog') . '?page=' . ($currentPage + 1), ENT_QUOTES, 'UTF-8'); ?>">Suivant</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo htmlspecialchars($url('/blog') . '?page=' . $totalPages, ENT_QUOTES, 'UTF-8'); ?>">Fin</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Recherche -->
                <div class="sidebar">
                    <div class="sidebar-title">Rechercher</div>
                    <form method="GET" action="<?php echo htmlspecialchars($url('/blog'), ENT_QUOTES, 'UTF-8'); ?>" class="search-box">
                        <input type="text" name="search" placeholder="Rechercher un article..." 
                               value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>">
                    </form>
                </div>

                <!-- Catégories -->
                <div class="sidebar">
                    <div class="sidebar-title">Catégories</div>
                    <ul class="category-list">
                        <li>
                            <a href="<?php echo htmlspecialchars($url('/blog'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo empty($selectedCategory) ? 'active' : ''; ?>">
                                <span>Tous les articles</span>
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($url('/blog') . '?category=' . urlencode($cat['name']), ENT_QUOTES, 'UTF-8'); ?>" 
                                   class="<?php echo $selectedCategory === $cat['name'] ? 'active' : ''; ?>">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Info -->
                <div class="sidebar">
                    <div class="sidebar-title">À propos</div>
                    <p style="color: #666; margin-bottom: 1rem;">
                        Bienvenue sur notre blog! Découvrez les derniers articles sur la technologie, la formation et l'emploi.
                    </p>
                    <div style="display: flex; gap: 1rem;">
                        <a href="<?php echo htmlspecialchars($url('/events'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-create" style="flex: 1; text-align: center;">
                            <i class="fas fa-calendar"></i> Événements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
