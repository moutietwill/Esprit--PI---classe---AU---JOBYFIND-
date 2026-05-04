<?php
require_once __DIR__ . '/../controller/BlogController.php';
require_once __DIR__ . '/../controller/StoryController.php';

$controller = new BlogController();
$storyController = new StoryController();

// Pagination params
$page = 1; // Toujours 1 au premier chargement pour l'infinite scroll
$postsPerPage = 6;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'Tous';

// Retrieve posts
if (!empty($search)) {
    $allSearchResults = $controller->RecherchePost($search);
    $totalPosts = count($allSearchResults);
    $totalPages = ceil($totalPosts / $postsPerPage);
    $postsToDisplay = array_slice($allSearchResults, 0, $postsPerPage);
} else {
    $allPosts = $controller->AfficherPublies();
    $totalPosts = count($allPosts);
    $totalPages = ceil($totalPosts / $postsPerPage);
    
    $postsToDisplay = $controller->Pagination($page, $postsPerPage);
}

// Get unique categories
$categories = ['Tous', 'Développement', 'Design', 'Marketing', 'Gestion', 'Communication'];
$activeStories = $storyController->GetActiveStories(8);
$storyPayload = array_map(function ($story) {
    return [
        'id' => (int) $story['id'],
        'title' => $story['title'],
        'content' => $story['content'] ?? '',
        'image' => !empty($story['media_image']) ? '../uploads/stories/' . $story['media_image'] : '',
        'post_id' => !empty($story['post_id']) ? (int) $story['post_id'] : null,
        'post_title' => $story['post_title'] ?? '',
        'cta_label' => $story['cta_label'] ?: 'Voir le blog'
    ];
}, $activeStories);
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

    <?php if (!empty($activeStories)): ?>
    <section class="stories-section" aria-label="Stories">
        <div class="stories-container">
            <div class="stories-header">
                <h2>Stories</h2>
                <span><?= count($activeStories) ?> active(s)</span>
            </div>
            <div class="stories-row">
                <?php foreach ($activeStories as $index => $story): ?>
                    <button type="button" class="story-bubble" onclick="openStory(<?= $index ?>)" aria-label="Ouvrir la story <?= htmlspecialchars($story['title']) ?>">
                        <span class="story-ring">
                            <?php if (!empty($story['media_image'])): ?>
                                <img src="../uploads/stories/<?= htmlspecialchars($story['media_image']) ?>" alt="<?= htmlspecialchars($story['title']) ?>">
                            <?php else: ?>
                                <i class="fas fa-circle-play"></i>
                            <?php endif; ?>
                        </span>
                        <span class="story-name"><?= htmlspecialchars($story['title']) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div id="storyModal" class="story-modal" aria-hidden="true">
        <div class="story-viewer" role="dialog" aria-modal="true" aria-label="Story">
            <div class="story-progress"><span id="storyProgressBar"></span></div>
            <button type="button" class="story-close" onclick="closeStory()" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
            <button type="button" class="story-arrow story-arrow-prev" onclick="prevStory()" aria-label="Story précédente"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="story-arrow story-arrow-next" onclick="nextStory()" aria-label="Story suivante"><i class="fas fa-chevron-right"></i></button>
            <div id="storyMedia" class="story-media"></div>
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
    <?php endif; ?>

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
            <?php if (count($postsToDisplay ?? []) > 0): ?>
                <div class="formations-grid">
                    <?php 
                    $colors = ['formation-blue', 'formation-purple', 'formation-teal', 'formation-orange', 'formation-green'];
                    $colorIndex = 0;
                    foreach ($postsToDisplay as $post): 
                    ?>
                        <div class="formation-card <?php echo $colors[$colorIndex % count($colors)]; ?>" data-view-post-id="<?php echo $post['id']; ?>">
                            <div class="formation-image">
                                <?php if ($post['cover_image']): ?>
                                    <?php 
                                        $imageSrc = $post['cover_image'];
                                        // Si ce n'est pas un lien externe (IA), on ajoute le dossier uploads
                                        if (strpos($imageSrc, 'http') !== 0) {
                                            $imageSrc = '../uploads/' . $imageSrc;
                                        }
                                    ?>
                                    <img src="<?php echo $imageSrc; ?>" 
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
                                    <?php 
                                        $ratingInfo  = $controller->GetPostRating($post['id']);
                                        $userRating  = $controller->GetUserRating($post['id'], 1);
                                        $avgRating   = $ratingInfo['avg'];
                                        $ratingCount = $ratingInfo['count'];
                                    ?>
                                    <div class="star-rating-widget" id="rating-widget-<?php echo $post['id']; ?>">
                                        <span class="star-rating-label">Votre note</span>
                                        <div class="star-rating-row">
                                            <div class="star-rating-stars" id="stars-<?php echo $post['id']; ?>">
                                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                                <button
                                                    type="button"
                                                    class="star-btn <?php echo ($s <= $userRating) ? 'selected' : ''; ?>"
                                                    data-value="<?php echo $s; ?>"
                                                    onclick="ratePost(<?php echo $post['id']; ?>, <?php echo $s; ?>)"
                                                    onmouseenter="hoverStars(<?php echo $post['id']; ?>, <?php echo $s; ?>)"
                                                    onmouseleave="resetStarHover(<?php echo $post['id']; ?>, <?php echo $userRating; ?>)"
                                                    title="<?php echo $s; ?> étoile<?php echo $s > 1 ? 's' : ''; ?>"
                                                    aria-label="Donner <?php echo $s; ?> étoile<?php echo $s > 1 ? 's' : ''; ?>"
                                                ><i class="<?php echo ($s <= $userRating) ? 'fas' : 'far'; ?> fa-star"></i></button>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="star-rating-summary" id="rating-summary-<?php echo $post['id']; ?>">
                                                <span class="star-avg-value"><?php echo $avgRating > 0 ? number_format($avgRating, 1) : '—'; ?></span>
                                                <i class="fas fa-star" style="color:#f59e0b;font-size:11px;"></i>
                                                <span class="star-count-badge"><?php echo $ratingCount; ?> avis</span>
                                            </div>
                                            <span class="star-feedback" id="star-feedback-<?php echo $post['id']; ?>">✓ Merci !</span>
                                        </div>
                                    </div>
                                    <div class="engagement-buttons">
                                        <?php 
                                            $likeCount = $controller->GetLikesCount($post['id']);
                                            $hasLiked  = $controller->HasLiked($post['id'], 1);
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
                                            <div class="comment-input-row">
                                                <input 
                                                    type="text" 
                                                    class="comment-input" 
                                                    placeholder="Ajouter un commentaire..."
                                                    maxlength="500"
                                                    onkeydown="submitComment(event, this, <?php echo $post['id']; ?>)"
                                                >
                                                <button 
                                                    type="button" 
                                                    class="voice-comment-btn" 
                                                    onclick="toggleVoiceComment(this, <?php echo $post['id']; ?>)"
                                                    title="Dicter un commentaire"
                                                    aria-label="Dicter un commentaire"
                                                    aria-pressed="false"
                                                >
                                                    <i class="fas fa-microphone"></i>
                                                </button>
                                            </div>
                                            <div class="comment-command-row">
                                                <button type="button" class="comment-command-btn" onclick="runCommentCommand(this, <?php echo $post['id']; ?>, 'send')" title="Envoyer">
                                                    <i class="fas fa-paper-plane"></i><span>Envoyer</span>
                                                </button>
                                                <button type="button" class="comment-command-btn" onclick="runCommentCommand(this, <?php echo $post['id']; ?>, 'clear')" title="Effacer">
                                                    <i class="fas fa-eraser"></i><span>Effacer</span>
                                                </button>
                                                <button type="button" class="comment-command-btn" onclick="runCommentCommand(this, <?php echo $post['id']; ?>, 'correct')" title="Corriger">
                                                    <i class="fas fa-wand-magic-sparkles"></i><span>Corriger</span>
                                                </button>
                                            </div>
                                            <div id="voiceStatus-<?php echo $post['id']; ?>" class="voice-comment-status" aria-live="polite"></div>
                                            <div id="commentError-<?php echo $post['id']; ?>" style="color: #ff4d4d; font-size: 12px; margin-top: 5px; display: none; text-align: left; padding-left: 10px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $colorIndex++; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Load More Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="load-more-container" style="text-align: center; margin-top: 40px;">
                        <button id="loadMoreBtn" class="btn btn-primary" onclick="loadMorePosts()" data-page="1" data-total="<?php echo $totalPages; ?>" data-search="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="padding: 12px 24px; font-size: 16px; border-radius: 8px; cursor: pointer;">
                            Charger plus de formations
                        </button>
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

    <script>
        window.storyItems = <?= json_encode($storyPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    </script>
    <script src="assets/js/validation.js?v=<?php echo time(); ?>"></script>
</body>
</html>
