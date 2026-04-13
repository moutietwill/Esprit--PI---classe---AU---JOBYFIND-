<?php
require_once __DIR__ . '/../controller/BlogController.php';

$controller = new BlogController();

// Déterminer la page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$postsPerPage = 6;

// Récupérer les posts publiés
$posts = $controller->Pagination($page, $postsPerPage);
$totalPosts = $controller->NombreDesPosts();
$totalPages = ceil($totalPosts / $postsPerPage);

// Recherche
$searchResults = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchResults = $controller->RecherchePost($_GET['search']);
}

// Determine posts to display
$postsToDisplay = $searchResults ?? $posts;

// Get unique categories
$categories = ['Tous', 'Développement', 'Design', 'Marketing', 'Gestion', 'Communication'];
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'Tous';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formations - Trouvez la formation parfaite</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="assets/css/formations.css">
</head>
<body>
    <!-- ═════════════════════════════════════════════ -->
    <!-- NAVBAR -->
    <!-- ═════════════════════════════════════════════ -->

    <nav class="navbar">
        <div class="navbar-content">
            <a href="/projetweb/" class="navbar-brand">
                <i class="fas fa-play-circle"></i>
                <span>JobyFind</span>
            </a>
            <div class="navbar-menu">
                <a href="/projetweb/" class="navbar-link active">blog</a>
                <a href="#" class="navbar-link">À propos</a>
                <a href="#" class="navbar-link">Contact</a>
                <div class="navbar-buttons">
                    <a href="backoffice.php" class="btn btn-dark">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ═════════════════════════════════════════════ -->
    <!-- HERO SECTION -->
    <!-- ═════════════════════════════════════════════ -->

    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Développez vos compétences</h1>
                <p>Découvrez nos meilleures formations pour progresser dans votre carrière</p>
                <form method="GET" class="hero-search" id="searchForm">
                    <input type="text" name="search" id="searchInput" class="search-input-hero" placeholder="Que voulez-vous apprendre ?" 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <div id="searchError" style="color: #ffcccc; font-size: 14px; margin-top: 10px; display: none;"></div>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════ -->
    <!-- FORMATIONS SECTION -->
    <!-- ═════════════════════════════════════════════ -->

    <section class="formations-section">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section">
                <h2>Blogs en vedette</h2>
                <div class="category-filters">
                    <?php foreach ($categories as $cat): ?>
                        <a href="?category=<?php echo urlencode($cat); ?>" 
                           class="category-badge <?php echo $selectedCategory === $cat ? 'active' : ''; ?>">
                            <?php echo $cat; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Formations Grid -->
            <?php if (count($postsToDisplay ?? $posts) > 0): ?>
                <div class="formations-grid">
                    <?php 
                    $colors = ['formation-blue', 'formation-purple', 'formation-teal', 'formation-orange', 'formation-green'];
                    $colorIndex = 0;
                    foreach ($postsToDisplay ?? $posts as $post): 
                    ?>
                        <div class="formation-card <?php echo $colors[$colorIndex % count($colors)]; ?>">
                            <div class="formation-image">
                                <?php if ($post['cover_image']): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($post['cover_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($post['title']); ?>">
                                <?php else: ?>
                                    <div class="formation-image-placeholder">
                                        <i class="fas fa-book"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="formation-badge">En ligne</div>
                            </div>
                            <div class="formation-content">
                                <div class="formation-category"><?php echo htmlspecialchars($post['category'] ?? 'Général'); ?></div>
                                <h3 class="formation-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                <p class="formation-description"><?php echo htmlspecialchars(substr($post['content'], 0, 80)); ?>...</p>
                                <div class="formation-engagement">
                                    <div class="engagement-buttons">
                                        <?php 
                                            // Get initial count from DB
                                            $likeCount = $controller->GetLikesCount($post['id']);
                                            $hasLiked = $controller->HasLiked($post['id'], 1); // 1 is simulated user_id
                                        ?>
                                        <button class="like-btn <?= $hasLiked ? 'liked' : '' ?>" onclick="toggleLike(this, <?php echo $post['id']; ?>)">
                                            <i class="fa<?= $hasLiked ? 's' : 'r' ?> fa-heart"></i>
                                            <span class="engagement-count"><?php echo $likeCount; ?></span>
                                        </button>
                                        <?php $postComments = $controller->GetCommentsByPost($post['id']); ?>
                                        <button class="comment-btn" onclick="toggleComments(this, <?php echo $post['id']; ?>)">
                                            <i class="far fa-comment"></i>
                                            <span class="engagement-count"><?php echo count($postComments); ?></span>
                                        </button>
                                    </div>
                                    <div class="comments-section" id="comments-<?php echo $post['id']; ?>">
                                        <div class="comments-list" id="comments-list-<?php echo $post['id']; ?>">
                                            <?php if (empty($postComments)): ?>
                                                <p style="text-align: center; color: var(--text-secondary); padding: 10px;" class="no-comment-msg">Aucun commentaire</p>
                                            <?php else: ?>
                                                <?php foreach ($postComments as $comment): ?>
                                                    <div class="comment-item">
                                                        <div class="comment-text">
                                                            <strong style="color: var(--primary);"><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                                                            <br>
                                                            <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($comment['created_at']); ?></small>
                                                            <br>
                                                            <?php echo htmlspecialchars($comment['content']); ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="comment-input-wrapper">
                                            <input 
                                                type="text" 
                                                class="comment-input" 
                                                placeholder="Ajouter un commentaire..."
                                                onkeypress="submitComment(event, this, <?php echo $post['id']; ?>)"
                                            >
                                            <div id="commentError-<?php echo $post['id']; ?>" style="color: #ff4d4d; font-size: 12px; margin-top: 5px; display: none; text-align: left; padding-left: 10px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $colorIndex++; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if (empty($_GET['search']) && $totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1" class="pagination-btn">«</a>
                            <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">‹</a>
                        <?php else: ?>
                            <span class="pagination-btn disabled">«</span>
                            <span class="pagination-btn disabled">‹</span>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="pagination-btn active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>" class="pagination-btn"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">›</a>
                            <a href="?page=<?php echo $totalPages; ?>" class="pagination-btn">»</a>
                        <?php else: ?>
                            <span class="pagination-btn disabled">›</span>
                            <span class="pagination-btn disabled">»</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h2>Aucune formation trouvée</h2>
                    <p><?php echo isset($_GET['search']) ? 'Aucun résultat pour votre recherche' : 'Il n\'y a pas encore de formations disponibles'; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ═════════════════════════════════════════════ -->
    <!-- FOOTER -->
    <!-- ═════════════════════════════════════════════ -->

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>À propos</h4>
                    <p>ProjetWeb est votre plateforme de formation en ligne pour développer vos compétences</p>
                </div>
                <div class="footer-section">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="#">Formations</a></li>
                        <li><a href="#">À propos</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>Email: contact@projetweb.tn</p>
                    <p>Tél: +216 XX XXX XXX</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 ProjetWeb. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- ═════════════════════════════════════════════ -->
    <!-- SEARCH FORM VALIDATION -->
    <!-- ═════════════════════════════════════════════ -->

    <script src="assets/js/validation.js"></script>
</body>
</html>
