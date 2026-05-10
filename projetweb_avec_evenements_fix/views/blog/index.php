<?php
$page = $page ?? 1;
$search = $search ?? '';
$categorie = $categorie ?? '';
$totalPages = $totalPages ?? 1;
$posts = $posts ?? [];
$categories = $categories ?? [];
$featuredEvent = $featuredEvent ?? null;
$currentUser = $currentUser ?? null;
$activeStories = $activeStories ?? [];

$selectedCategory = $categorie !== '' ? $categorie : 'Tous';
$storyPayload = array_map(function ($story) use ($baseUrl) {
    return [
        'id' => (int) ($story['id'] ?? 0),
        'title' => $story['title'] ?? '',
        'content' => $story['content'] ?? '',
        'image' => !empty($story['media_image']) ? $baseUrl . '/../uploads/stories/' . $story['media_image'] : '',
        'post_id' => !empty($story['post_id']) ? (int) $story['post_id'] : null,
        'post_title' => $story['post_title'] ?? '',
        'cta_label' => $story['cta_label'] ?? 'Voir le blog',
    ];
}, $activeStories);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Jobyfind</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: #f4f7fb;
            --surface: #ffffff;
            --surface-alt: #eef4ff;
            --text: #1f2a44;
            --muted: #67758f;
            --line: #e3eaf5;
            --blue: #2563eb;
            --navy: #0b1f4b;
            --teal: #0f766e;
            --gold: #f59e0b;
            --danger: #dc2626;
            --shadow: 0 18px 48px rgba(17, 31, 63, 0.08);
            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 14px;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: radial-gradient(circle at top left, #e9f1ff 0%, var(--bg) 45%, #eef2f7 100%);
            color: var(--text);
            font-family: "DM Sans", sans-serif;
        }
        a { color: inherit; text-decoration: none; }
        .container { width: min(1180px, calc(100% - 32px)); margin: 0 auto; }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(14px);
            background: rgba(255,255,255,0.84);
            border-bottom: 1px solid rgba(227,234,245,0.9);
        }
        .navbar-inner {
            min-height: 74px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--navy);
        }
        .brand-badge {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--blue), #5b9dff);
            color: #fff;
            box-shadow: 0 10px 24px rgba(37,99,235,0.28);
        }
        .nav-links, .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .nav-link {
            padding: 10px 14px;
            border-radius: 999px;
            color: var(--muted);
            font-weight: 600;
        }
        .nav-link.active, .nav-link:hover {
            color: var(--navy);
            background: #edf3ff;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            cursor: pointer;
            padding: 12px 18px;
            border-radius: 999px;
            font-weight: 700;
            transition: .2s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--blue), #4d8cff);
            color: #fff;
            box-shadow: 0 14px 30px rgba(37,99,235,0.24);
        }
        .btn-primary:hover { transform: translateY(-1px); }
        .btn-outline {
            background: #fff;
            color: var(--navy);
            border: 1px solid var(--line);
        }

        .hero {
            padding: 58px 0 26px;
        }
        .hero-grid {
            display: grid;
            grid-template-columns: 1.4fr .9fr;
            gap: 24px;
            align-items: stretch;
        }
        .hero-main, .hero-side {
            background: var(--surface);
            border: 1px solid rgba(227,234,245,0.9);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
        }
        .hero-main {
            padding: 34px;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(37,99,235,0.16), transparent 34%),
                linear-gradient(135deg, #ffffff, #f7fbff);
        }
        .hero-main h1 {
            margin: 0 0 14px;
            font-family: "DM Serif Display", serif;
            font-size: clamp(2.2rem, 3vw, 3.5rem);
            line-height: 1.05;
            color: var(--navy);
        }
        .hero-main p {
            margin: 0 0 26px;
            color: var(--muted);
            font-size: 1.05rem;
            max-width: 680px;
        }
        .hero-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }
        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: #eef4ff;
            color: #244c94;
            font-weight: 600;
        }
        .hero-search {
            display: flex;
            gap: 12px;
            align-items: center;
            background: #fff;
            border-radius: 999px;
            padding: 10px;
            border: 1px solid var(--line);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
        }
        .hero-search input {
            flex: 1;
            min-width: 0;
            border: 0;
            outline: none;
            font-size: 1rem;
            padding: 0 12px;
            background: transparent;
            color: var(--text);
        }
        .hero-side {
            padding: 26px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background:
                linear-gradient(160deg, rgba(11,31,75,0.98), rgba(37,99,235,0.95)),
                #123;
            color: #fff;
        }
        .hero-side .eyebrow {
            text-transform: uppercase;
            letter-spacing: .12em;
            font-size: .78rem;
            opacity: .72;
            margin-bottom: 8px;
        }
        .hero-side h2 {
            margin: 0 0 10px;
            font-size: 1.45rem;
            line-height: 1.2;
        }
        .hero-side p { color: rgba(255,255,255,.78); margin: 0 0 18px; }
        .hero-side .meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            color: rgba(255,255,255,.86);
        }

        .stories {
            padding: 8px 0 16px;
        }
        .stories-card {
            background: rgba(255,255,255,0.82);
            border: 1px solid rgba(227,234,245,0.9);
            border-radius: var(--radius-xl);
            padding: 22px;
            box-shadow: var(--shadow);
        }
        .stories-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }
        .stories-head h3 { margin: 0; font-size: 1.05rem; }
        .stories-row {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            padding-bottom: 4px;
        }
        .story-bubble {
            min-width: 92px;
            background: transparent;
            border: 0;
            color: var(--text);
            cursor: pointer;
        }
        .story-ring {
            width: 72px;
            height: 72px;
            margin: 0 auto 8px;
            padding: 3px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), var(--blue));
            display: grid;
            place-items: center;
        }
        .story-ring img, .story-ring i {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            background: #fff;
            display: grid;
            place-items: center;
            color: var(--blue);
        }
        .story-name {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: var(--muted);
        }

        .filters {
            padding: 12px 0 22px;
        }
        .filters-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .filter-chip {
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.86);
            border: 1px solid var(--line);
            color: var(--muted);
            font-weight: 700;
        }
        .filter-chip.active {
            background: var(--navy);
            color: #fff;
            border-color: var(--navy);
        }

        .grid-section {
            padding-bottom: 70px;
        }
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
        }
        .post-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid rgba(227,234,245,0.9);
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
        }
        .post-cover {
            position: relative;
            aspect-ratio: 16 / 10;
            background: linear-gradient(135deg, #dbeafe, #f3f8ff);
        }
        .post-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .post-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: rgba(11,31,75,0.84);
            color: #fff;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
        }
        .post-content { padding: 20px; display: flex; flex-direction: column; gap: 14px; flex: 1; }
        .post-topline {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            color: var(--muted);
            font-size: .85rem;
        }
        .post-category {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 999px;
            background: var(--surface-alt);
            color: #2753a5;
            font-weight: 700;
        }
        .post-event {
            font-size: .82rem;
            color: var(--teal);
            font-weight: 700;
        }
        .post-title {
            margin: 0;
            font-size: 1.14rem;
            line-height: 1.35;
            color: var(--navy);
        }
        .post-excerpt {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
            flex: 1;
        }
        .author-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: var(--muted);
            font-size: .9rem;
        }
        .author-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        .author-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), #7fb5ff);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: .85rem;
            font-weight: 700;
        }
        .engagement {
            border-top: 1px solid var(--line);
            padding-top: 14px;
            display: grid;
            gap: 12px;
        }
        .rating-row, .action-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .stars {
            display: inline-flex;
            gap: 4px;
        }
        .star-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 0;
            background: #f8fafc;
            color: #c4cbd8;
            cursor: pointer;
        }
        .star-btn.selected, .star-btn.hovered { color: var(--gold); background: #fff7e6; }
        .like-btn, .comment-btn {
            border: 1px solid var(--line);
            background: #fff;
            color: var(--muted);
            padding: 9px 12px;
            border-radius: 999px;
            cursor: pointer;
            display: inline-flex;
            gap: 8px;
            align-items: center;
            font-weight: 700;
        }
        .like-btn.liked { color: #c026d3; border-color: #ecc8f9; background: #fff3ff; }
        .comments-section { display: none; background: #f8fbff; border-radius: 14px; padding: 14px; border: 1px solid #e8f0fa; }
        .comments-list { max-height: 220px; overflow: auto; display: grid; gap: 10px; margin-bottom: 12px; }
        .comment-item { background: #fff; border-radius: 12px; padding: 12px; border: 1px solid var(--line); }
        .comment-input-row { display: flex; gap: 8px; }
        .comment-input {
            flex: 1;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 12px 14px;
            outline: none;
        }
        .voice-comment-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 0;
            background: var(--navy);
            color: #fff;
            cursor: pointer;
        }
        .rating-summary { color: var(--muted); font-weight: 700; font-size: .9rem; }
        .comment-error { color: var(--danger); font-size: .84rem; margin-top: 8px; display: none; }
        .load-more-wrap { text-align: center; margin-top: 28px; }
        .empty-state {
            background: rgba(255,255,255,0.9);
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            text-align: center;
            padding: 54px 20px;
            box-shadow: var(--shadow);
        }

        .story-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(7, 16, 35, 0.78);
            z-index: 100;
            padding: 20px;
        }
        .story-modal.open { display: grid; place-items: center; }
        .story-viewer {
            width: min(440px, 100%);
            min-height: 720px;
            max-height: 92vh;
            background: linear-gradient(180deg, #0c1324, #1e293b);
            color: #fff;
            border-radius: 28px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .story-progress { height: 4px; background: rgba(255,255,255,0.16); }
        .story-progress span { display: block; height: 100%; width: 0; background: #fff; transition: width linear; }
        .story-close, .story-arrow {
            position: absolute;
            top: 14px;
            z-index: 3;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 0;
            background: rgba(255,255,255,0.12);
            color: #fff;
            cursor: pointer;
        }
        .story-close { right: 14px; }
        .story-arrow-prev { left: 14px; top: 50%; transform: translateY(-50%); }
        .story-arrow-next { right: 14px; top: 50%; transform: translateY(-50%); }
        .story-media { flex: 1; display: grid; place-items: center; padding: 56px 22px 18px; }
        .story-media img { width: 100%; height: 100%; object-fit: cover; border-radius: 0; }
        .story-media.placeholder {
            font-size: 4rem;
            background: radial-gradient(circle at top, rgba(255,255,255,0.18), transparent 50%);
        }
        .story-overlay {
            padding: 22px;
            display: grid;
            gap: 12px;
            background: linear-gradient(180deg, transparent, rgba(4,8,20,.88) 28%);
        }
        .story-post-label { color: #a9b9d8; font-size: .82rem; text-transform: uppercase; letter-spacing: .08em; }
        .story-overlay h3 { margin: 0; font-size: 1.3rem; }
        .story-overlay p { margin: 0; color: rgba(255,255,255,.82); line-height: 1.55; }
        .story-cta {
            border: 0;
            border-radius: 999px;
            background: #fff;
            color: var(--navy);
            padding: 13px 16px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        @media (max-width: 1024px) {
            .hero-grid, .posts-grid { grid-template-columns: 1fr 1fr; }
            .hero-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 720px) {
            .navbar-inner { padding: 10px 0; align-items: flex-start; }
            .nav-links { display: none; }
            .hero-main, .hero-side, .stories-card, .empty-state { border-radius: 20px; }
            .hero-main { padding: 24px; }
            .hero-search { flex-direction: column; border-radius: 20px; }
            .hero-search input { width: 100%; padding: 8px 6px; }
            .hero-search .btn { width: 100%; }
            .posts-grid { grid-template-columns: 1fr; }
            .story-viewer { min-height: 78vh; }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="container navbar-inner">
            <a href="<?php echo $baseUrl; ?>/blog" class="brand">
                <span class="brand-badge"><i class="fas fa-feather-pointed"></i></span>
                <span>Jobyfind Blog</span>
            </a>
            <nav class="nav-links">
                <a class="nav-link active" href="<?php echo $baseUrl; ?>/blog">Blog</a>
                <a class="nav-link" href="<?php echo $baseUrl; ?>/events">Evenements</a>
                <a class="nav-link" href="<?php echo $baseUrl; ?>/admin/blog">Backoffice</a>
            </nav>
            <div class="nav-actions">
                <?php if ($currentUser): ?>
                    <a class="btn btn-outline" href="<?php echo $baseUrl; ?>/admin/blog/create"><i class="fas fa-pen-nib"></i> Ecrire</a>
                <?php else: ?>
                    <a class="btn btn-outline" href="<?php echo $baseUrl; ?>/../views/frontoffice/signin.php">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-main">
                <div class="hero-tags">
                    <span class="hero-tag"><i class="fas fa-user-pen"></i> Auteurs reels</span>
                    <span class="hero-tag"><i class="fas fa-calendar-day"></i> Evenements lies</span>
                    <span class="hero-tag"><i class="fas fa-comments"></i> Interactions live</span>
                </div>
                <h1>Le front blog original, reconnecte a votre plateforme.</h1>
                <p>Retrouvez un espace editorial vivant avec stories, notes, likes, commentaires et pont direct entre contenu, utilisateurs et evenements.</p>
                <form method="GET" class="hero-search">
                    <input type="text" name="search" id="searchInput" placeholder="Rechercher un article, un theme ou un evenement..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-magnifying-glass"></i> Rechercher</button>
                </form>
            </div>
            <aside class="hero-side">
                <div>
                    <div class="eyebrow">Evenement mis en avant</div>
                    <?php if ($featuredEvent): ?>
                        <h2><?php echo htmlspecialchars($featuredEvent['titre'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p>Un article peut maintenant etre lie directement a cet evenement et afficher son contexte aux visiteurs.</p>
                        <div class="meta">
                            <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($featuredEvent['date'])); ?></span>
                            <span><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($featuredEvent['lieu'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php else: ?>
                        <h2>Aucun evenement a venir</h2>
                        <p>Le blog reste disponible, meme si aucun evenement n'est encore programme.</p>
                    <?php endif; ?>
                </div>
                <?php if ($featuredEvent): ?>
                    <a class="btn btn-primary" href="<?php echo $baseUrl . '/events/show/' . (int) $featuredEvent['idEvenement']; ?>">Voir l'evenement</a>
                <?php endif; ?>
            </aside>
        </div>
    </section>

    <?php if (!empty($activeStories)): ?>
        <section class="stories">
            <div class="container stories-card">
                <div class="stories-head">
                    <h3>Stories du blog</h3>
                    <span><?php echo count($activeStories); ?> active(s)</span>
                </div>
                <div class="stories-row">
                    <?php foreach ($activeStories as $index => $story): ?>
                        <button type="button" class="story-bubble" onclick="openStory(<?php echo $index; ?>)">
                            <span class="story-ring">
                                <?php if (!empty($story['media_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($baseUrl . '/../uploads/stories/' . $story['media_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($story['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php else: ?>
                                    <i class="fas fa-circle-play"></i>
                                <?php endif; ?>
                            </span>
                            <span class="story-name"><?php echo htmlspecialchars($story['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="filters">
        <div class="container filters-bar">
            <a href="<?php echo $baseUrl; ?>/blog" class="filter-chip <?php echo $selectedCategory === 'Tous' ? 'active' : ''; ?>">Tous</a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?php echo $baseUrl . '/blog?categorie=' . urlencode($cat['nom']); ?>" class="filter-chip <?php echo $selectedCategory === $cat['nom'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="grid-section">
        <div class="container">
            <?php if (!empty($posts)): ?>
                <div class="posts-grid" id="postsGrid">
                    <?php foreach ($posts as $post): ?>
                        <?php
                        $authorLabel = trim((string) ($post['auteur_nom'] ?: $post['auteur_username'] ?: $post['auteur_email'] ?: 'Auteur inconnu'));
                        $initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $authorLabel), 0, 2));
                        $initials = $initials !== '' ? $initials : 'JB';
                        $postRating = (new BlogController())->GetPostRating($post['id']);
                        $userRating = (new BlogController())->GetUserRating($post['id'], isset($currentUser['id']) ? (int) $currentUser['id'] : 0);
                        $likeCount = (new BlogController())->GetLikesCount($post['id']);
                        $hasLiked = (new BlogController())->HasLiked($post['id'], isset($currentUser['id']) ? (int) $currentUser['id'] : 0);
                        $postComments = (new BlogController())->GetCommentsByPost($post['id']);
                        ?>
                        <article class="post-card" data-post-id="<?php echo (int) $post['id']; ?>">
                            <div class="post-cover">
                                <?php if (!empty($post['image_couverture'])): ?>
                                    <img src="<?php echo htmlspecialchars($baseUrl . '/uploads/blog/' . $post['image_couverture'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($post['titre'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php endif; ?>
                                <span class="post-badge"><i class="fas fa-eye"></i> <?php echo (int) $post['vues']; ?> vues</span>
                            </div>
                            <div class="post-content">
                                <div class="post-topline">
                                    <span class="post-category"><i class="fas fa-folder-open"></i> <?php echo htmlspecialchars($post['categorie'] ?: 'General', ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if (!empty($post['event_titre'])): ?>
                                        <span class="post-event"><i class="fas fa-calendar-check"></i> <?php echo htmlspecialchars($post['event_titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <h2 class="post-title"><?php echo htmlspecialchars($post['titre'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                <p class="post-excerpt"><?php echo htmlspecialchars(substr(strip_tags($post['resume'] ?: $post['contenu']), 0, 140), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                <div class="author-row">
                                    <span class="author-chip">
                                        <span class="author-avatar"><?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span><?php echo htmlspecialchars($authorLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </span>
                                    <a class="btn btn-outline" href="<?php echo $baseUrl . '/blog/post/' . (int) $post['id']; ?>">Lire</a>
                                </div>
                                <div class="engagement">
                                    <div class="rating-row">
                                        <span style="font-weight:700;color:#67758f;">Votre note</span>
                                        <div class="stars" data-post-id="<?php echo (int) $post['id']; ?>" data-user-rating="<?php echo (int) $userRating; ?>">
                                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                                <button type="button" class="star-btn <?php echo $s <= $userRating ? 'selected' : ''; ?>" data-value="<?php echo $s; ?>">
                                                    <i class="<?php echo $s <= $userRating ? 'fas' : 'far'; ?> fa-star"></i>
                                                </button>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-summary" id="rating-summary-<?php echo (int) $post['id']; ?>">
                                            <?php echo $postRating['avg'] > 0 ? number_format($postRating['avg'], 1) : '-'; ?>/5
                                            (<?php echo (int) $postRating['count']; ?>)
                                        </span>
                                    </div>
                                    <div class="action-row">
                                        <button type="button" class="like-btn <?php echo $hasLiked ? 'liked' : ''; ?>" data-post-id="<?php echo (int) $post['id']; ?>">
                                            <i class="fa<?php echo $hasLiked ? 's' : 'r'; ?> fa-heart"></i>
                                            <span class="like-count"><?php echo (int) $likeCount; ?></span>
                                        </button>
                                        <button type="button" class="comment-btn" data-post-id="<?php echo (int) $post['id']; ?>">
                                            <i class="far fa-comment"></i>
                                            <span class="comment-count"><?php echo count($postComments); ?></span>
                                        </button>
                                    </div>
                                    <div class="comments-section" id="comments-<?php echo (int) $post['id']; ?>">
                                        <div class="comments-list" id="comments-list-<?php echo (int) $post['id']; ?>">
                                            <?php if (empty($postComments)): ?>
                                                <p class="no-comment-msg" style="text-align:center;color:#67758f;">Aucun commentaire</p>
                                            <?php else: ?>
                                                <?php foreach ($postComments as $comment): ?>
                                                    <div class="comment-item">
                                                        <strong><?php echo htmlspecialchars((string) (($comment['auteur_nom'] ?? '') !== '' ? $comment['auteur_nom'] : (($comment['auteur_username'] ?? '') !== '' ? $comment['auteur_username'] : ($comment['nom'] ?? 'Anonyme'))), ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                                        <small style="color:#67758f;"><?php echo htmlspecialchars($comment['date_creation'], ENT_QUOTES, 'UTF-8'); ?></small><br>
                                                        <?php echo htmlspecialchars($comment['contenu'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="comment-input-row">
                                            <input type="text" class="comment-input" maxlength="500" placeholder="Ajouter un commentaire..." data-post-id="<?php echo (int) $post['id']; ?>">
                                            <button type="button" class="voice-comment-btn submit-comment-btn" data-post-id="<?php echo (int) $post['id']; ?>">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                        <div class="comment-error" id="comment-error-<?php echo (int) $post['id']; ?>"></div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="load-more-wrap">
                        <button id="loadMoreBtn" class="btn btn-primary" data-page="<?php echo (int) $page; ?>" data-total="<?php echo (int) $totalPages; ?>" data-search="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                            Charger plus d'articles
                        </button>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-newspaper fa-3x" style="color:#9cb1d3"></i>
                    <h2>Aucun article trouve</h2>
                    <p>Ajustez votre recherche ou publiez un nouveau contenu depuis le backoffice.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div id="storyModal" class="story-modal" aria-hidden="true">
        <div class="story-viewer" role="dialog" aria-modal="true">
            <div class="story-progress"><span id="storyProgressBar"></span></div>
            <button type="button" class="story-close" onclick="closeStory()" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
            <button type="button" class="story-arrow story-arrow-prev" onclick="prevStory()" aria-label="Precedente"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="story-arrow story-arrow-next" onclick="nextStory()" aria-label="Suivante"><i class="fas fa-chevron-right"></i></button>
            <div id="storyMedia" class="story-media placeholder"><i class="fas fa-camera-retro"></i></div>
            <div class="story-overlay">
                <div>
                    <span id="storyPostLabel" class="story-post-label"></span>
                    <h3 id="storyModalTitle"></h3>
                    <p id="storyModalContent"></p>
                </div>
                <button type="button" id="storyCtaButton" class="story-cta" onclick="openStoryPost()">
                    <span id="storyCtaText">Voir le blog</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        const basePath = <?php echo json_encode($baseUrl); ?>;
        const storyItems = <?php echo json_encode($storyPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
        let activeStoryIndex = 0;
        let storyTimer = null;
        let activeStoryPostId = null;

        function openStory(index) {
            if (!storyItems.length) return;
            activeStoryIndex = index;
            renderStory();
            document.getElementById('storyModal').classList.add('open');
        }

        function renderStory() {
            const item = storyItems[activeStoryIndex];
            const media = document.getElementById('storyMedia');
            const progress = document.getElementById('storyProgressBar');
            const postLabel = document.getElementById('storyPostLabel');
            const title = document.getElementById('storyModalTitle');
            const content = document.getElementById('storyModalContent');
            const ctaText = document.getElementById('storyCtaText');

            activeStoryPostId = item.post_id;
            postLabel.textContent = item.post_title ? 'Lie a: ' + item.post_title : 'Story blog';
            title.textContent = item.title || 'Story';
            content.textContent = item.content || 'Apercu rapide du contenu.';
            ctaText.textContent = item.cta_label || 'Voir le blog';

            if (item.image) {
                media.className = 'story-media';
                media.innerHTML = '<img src="' + item.image + '" alt="">';
            } else {
                media.className = 'story-media placeholder';
                media.innerHTML = '<i class="fas fa-circle-play"></i>';
            }

            progress.style.transition = 'none';
            progress.style.width = '0';
            clearTimeout(storyTimer);
            requestAnimationFrame(() => {
                progress.style.transition = 'width 4.8s linear';
                progress.style.width = '100%';
            });
            storyTimer = setTimeout(nextStory, 5000);
        }

        function closeStory() {
            clearTimeout(storyTimer);
            document.getElementById('storyModal').classList.remove('open');
        }

        function nextStory() {
            if (!storyItems.length) return;
            activeStoryIndex = (activeStoryIndex + 1) % storyItems.length;
            renderStory();
        }

        function prevStory() {
            if (!storyItems.length) return;
            activeStoryIndex = (activeStoryIndex - 1 + storyItems.length) % storyItems.length;
            renderStory();
        }

        function openStoryPost() {
            if (activeStoryPostId) {
                window.location.href = basePath + '/blog/post/' + activeStoryPostId;
            } else {
                closeStory();
            }
        }

        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', async function () {
                const nextPage = Number(this.dataset.page) + 1;
                const total = Number(this.dataset.total);
                if (nextPage > total) return;

                const search = encodeURIComponent(this.dataset.search || '');
                const response = await fetch(basePath + '/ajax_load_posts.php?page=' + nextPage + '&search=' + search);
                const html = await response.text();
                if (html.trim() !== '') {
                    document.getElementById('postsGrid').insertAdjacentHTML('beforeend', html);
                    bindInteractiveCards(document.getElementById('postsGrid'));
                    this.dataset.page = String(nextPage);
                }
                if (nextPage >= total || html.trim() === '') {
                    this.remove();
                }
            });
        }

        async function postJson(url, payload) {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            return response.json();
        }

        function bindInteractiveCards(scope = document) {
            scope.querySelectorAll('.comment-btn').forEach(button => {
                if (button.dataset.bound === '1') return;
                button.dataset.bound = '1';
                button.addEventListener('click', () => {
                    const postId = button.dataset.postId;
                    const panel = document.getElementById('comments-' + postId);
                    panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
                });
            });

            scope.querySelectorAll('.submit-comment-btn').forEach(button => {
                if (button.dataset.bound === '1') return;
                button.dataset.bound = '1';
                button.addEventListener('click', async () => {
                    const postId = button.dataset.postId;
                    const input = document.querySelector('.comment-input[data-post-id="' + postId + '"]');
                    const errorBox = document.getElementById('comment-error-' + postId);
                    const list = document.getElementById('comments-list-' + postId);
                    const countEl = document.querySelector('.comment-btn[data-post-id="' + postId + '"] .comment-count');
                    errorBox.style.display = 'none';
                    errorBox.textContent = '';
                    const content = input.value.trim();
                    if (!content) {
                        errorBox.textContent = 'Le commentaire ne peut pas etre vide.';
                        errorBox.style.display = 'block';
                        return;
                    }
                    const result = await postJson(basePath + '/ajax_add_comment.php', { post_id: Number(postId), content });
                    if (!result.success) {
                        errorBox.textContent = result.message || 'Erreur lors de l\'envoi.';
                        errorBox.style.display = 'block';
                        return;
                    }
                    const noComment = list.querySelector('.no-comment-msg');
                    if (noComment) noComment.remove();
                    list.insertAdjacentHTML('beforeend',
                        '<div class="comment-item"><strong>' + result.comment.author + '</strong><br><small style="color:#67758f;">' + result.comment.timestamp + '</small><br>' + result.comment.text + '</div>'
                    );
                    countEl.textContent = result.count;
                    input.value = '';
                });
            });

            scope.querySelectorAll('.like-btn').forEach(button => {
                if (button.dataset.bound === '1') return;
                button.dataset.bound = '1';
                button.addEventListener('click', async () => {
                    const postId = button.dataset.postId;
                    const result = await postJson(basePath + '/ajax_toggle_like.php', { post_id: Number(postId) });
                    if (!result.success) return;
                    button.classList.toggle('liked', !!result.liked);
                    button.querySelector('.like-count').textContent = result.count;
                    button.querySelector('i').className = (result.liked ? 'fas' : 'far') + ' fa-heart';
                });
            });

            scope.querySelectorAll('.stars').forEach(wrapper => {
                if (wrapper.dataset.bound === '1') return;
                wrapper.dataset.bound = '1';
                const postId = wrapper.dataset.postId;
                const buttons = Array.from(wrapper.querySelectorAll('.star-btn'));

                const paint = (value) => {
                    buttons.forEach((btn, index) => {
                        const active = index < value;
                        btn.classList.toggle('selected', active);
                        btn.querySelector('i').className = (active ? 'fas' : 'far') + ' fa-star';
                    });
                };

                buttons.forEach(btn => {
                    btn.addEventListener('mouseenter', () => paint(Number(btn.dataset.value)));
                    btn.addEventListener('mouseleave', () => paint(Number(wrapper.dataset.userRating || 0)));
                    btn.addEventListener('click', async () => {
                        const value = Number(btn.dataset.value);
                        const formData = new FormData();
                        formData.append('post_id', postId);
                        formData.append('rating', value);
                        const response = await fetch(basePath + '/ajax_rate_post.php', { method: 'POST', body: formData });
                        const result = await response.json();
                        if (!result.success) return;
                        wrapper.dataset.userRating = String(value);
                        paint(value);
                        document.getElementById('rating-summary-' + postId).textContent = result.avg.toFixed(1) + '/5 (' + result.count + ')';
                    });
                });
            });
        }

        bindInteractiveCards(document);
    </script>
</body>
</html>
