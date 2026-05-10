<?php
session_start();
require_once __DIR__ . '/../controllers/BlogController.php';

$controller = new BlogController();

$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim((string) ($_GET['search'] ?? ''));
$postsPerPage = 6;

if ($search !== '') {
    $allSearchResults = $controller->RecherchePost($search);
    $posts = array_slice($allSearchResults, ($page - 1) * $postsPerPage, $postsPerPage);
} else {
    $posts = $controller->Pagination($page, $postsPerPage);
}

if (empty($posts)) {
    exit;
}

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$basePath = ($basePath && $basePath !== '.') ? $basePath : '';

foreach ($posts as $post):
    $ratingInfo = $controller->GetPostRating($post['id']);
    $userRating = $controller->GetUserRating($post['id'], $userId);
    $likeCount = $controller->GetLikesCount($post['id']);
    $hasLiked = $controller->HasLiked($post['id'], $userId);
    $postComments = $controller->GetCommentsByPost($post['id']);
    $authorLabel = trim((string) ($post['auteur_nom'] ?: $post['auteur_username'] ?: $post['auteur_email'] ?: 'Auteur inconnu'));
    $initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $authorLabel), 0, 2));
    $initials = $initials !== '' ? $initials : 'JB';
?>
<article class="post-card" data-post-id="<?php echo (int) $post['id']; ?>">
    <div class="post-cover">
        <?php if (!empty($post['image_couverture'])): ?>
            <img src="<?php echo htmlspecialchars($basePath . '/uploads/blog/' . $post['image_couverture'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($post['titre'], ENT_QUOTES, 'UTF-8'); ?>">
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
            <a class="btn btn-outline" href="<?php echo $basePath . '/blog/post/' . (int) $post['id']; ?>">Lire</a>
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
                    <?php echo $ratingInfo['avg'] > 0 ? number_format($ratingInfo['avg'], 1) : '-'; ?>/5
                    (<?php echo (int) $ratingInfo['count']; ?>)
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
                <div class="comment-error" id="comment-error-<?php echo (int) $post['id']; ?>" style="display:none;color:#dc2626;font-size:.85rem;margin-top:8px;"></div>
            </div>
        </div>
    </div>
</article>
<?php endforeach; ?>
