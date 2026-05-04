<?php
require_once __DIR__ . '/controller/BlogController.php';

$controller = new BlogController();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'Tous';
$postsPerPage = 6;

if (!empty($search)) {
    // La recherche n'est pas encore paginée dans le controller, on simule une coupe
    $allSearchResults = $controller->RecherchePost($search);
    $posts = array_slice($allSearchResults, ($page - 1) * $postsPerPage, $postsPerPage);
} else {
    // Si filtrage par catégorie (à implémenter si nécessaire, ici simplifié)
    // On utilise la pagination normale
    $posts = $controller->Pagination($page, $postsPerPage);
}

if (empty($posts)) {
    echo ''; // Rien à charger
    exit;
}

$colors = ['formation-blue', 'formation-purple', 'formation-teal', 'formation-orange', 'formation-green'];
$colorIndex = ($page - 1) * $postsPerPage;

foreach ($posts as $post): 
?>
<div class="formation-card <?php echo $colors[$colorIndex % count($colors)]; ?>" data-view-post-id="<?php echo $post['id']; ?>">
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
            <div class="star-rating-widget" id="rating-widget-<?php echo $post['id']; ?>">
                <span class="star-rating-label">Votre note</span>
                <div class="star-rating-row">
                    <div class="star-rating-stars" id="stars-<?php echo $post['id']; ?>">
                        <?php 
                            $ratingInfo = $controller->GetPostRating($post['id']);
                            $userRating = $controller->GetUserRating($post['id'], 1);
                            $avgRating  = $ratingInfo['avg'];
                            $ratingCount = $ratingInfo['count'];
                            for ($s = 1; $s <= 5; $s++): 
                        ?>
                        <button
                            type="button"
                            class="star-btn <?php echo ($s <= $userRating) ? 'selected' : ''; ?>"
                            data-value="<?php echo $s; ?>"
                            onclick="ratePost(<?php echo $post['id']; ?>, <?php echo $s; ?>)"
                            onmouseenter="hoverStars(<?php echo $post['id']; ?>, <?php echo $s; ?>)"
                            onmouseleave="resetStarHover(<?php echo $post['id']; ?>, <?php echo $userRating; ?>)"
                            title="<?php echo $s; ?> étoile<?php echo $s > 1 ? 's' : ''; ?>"
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
                    $hasLiked = $controller->HasLiked($post['id'], 1); // Simulation user 1
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
                        <button type="button" class="voice-comment-btn" onclick="toggleVoiceComment(this, <?php echo $post['id']; ?>)">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>
                    <div class="comment-command-row">
                        <button type="button" class="comment-command-btn" onclick="runCommentCommand(this, <?php echo $post['id']; ?>, 'send')">
                            <i class="fas fa-paper-plane"></i><span>Envoyer</span>
                        </button>
                        <button type="button" class="comment-command-btn" onclick="runCommentCommand(this, <?php echo $post['id']; ?>, 'clear')">
                            <i class="fas fa-eraser"></i><span>Effacer</span>
                        </button>
                        <button type="button" class="comment-command-btn" onclick="runCommentCommand(this, <?php echo $post['id']; ?>, 'correct')">
                            <i class="fas fa-wand-magic-sparkles"></i><span>Corriger</span>
                        </button>
                    </div>
                    <div id="voiceStatus-<?php echo $post['id']; ?>" class="voice-comment-status"></div>
                    <div id="commentError-<?php echo $post['id']; ?>" style="color: #ff4d4d; font-size: 12px; margin-top: 5px; display: none; text-align: left; padding-left: 10px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $colorIndex++; ?>
<?php endforeach; ?>
