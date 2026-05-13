<?php
require_once __DIR__ . '/controller/BlogController.php';
$controller = new BlogController();
$db = Config::GetConnexion();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 2;
$postsPerPage = 6;
$offset = ($page - 1) * $postsPerPage;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$posts = $controller->RecupererPostPage($offset, $postsPerPage, $search);

foreach ($posts as $post): 
    $imageSrc = '';
    if (!empty($post['cover_image'])) {
        $imageSrc = (strpos($post['cover_image'], 'http') === 0) ? $post['cover_image'] : 'uploads/' . $post['cover_image'];
        if (strpos($post['cover_image'], 'uploads/') === 0) {
            $imageSrc = $post['cover_image'];
        }
    }
?>
<div class="formation-card">
    <div class="formation-image">
        <?php if ($imageSrc): ?>
            <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="formation-image-placeholder" style="display:none;"><i class="fas fa-image"></i></div>
        <?php else: ?>
            <div class="formation-image-placeholder"><i class="fas fa-book"></i></div>
        <?php endif; ?>
        <div class="formation-badge">En ligne</div>
    </div>
    <div class="formation-content">
        <div class="formation-category"><?php echo htmlspecialchars($post['category'] ?? 'Général'); ?></div>
        <h3 class="formation-title"><?php echo htmlspecialchars($post['title']); ?></h3>
        <p class="formation-description"><?php echo htmlspecialchars(substr($post['content'], 0, 80)); ?>...</p>
        
        <div class="eng-zone">
            <!-- Rating Section -->
            <?php
                $ratingInfo  = $controller->GetPostRating($post['id']);
                $userRating  = $controller->GetUserRating($post['id'], 1);
                $avgRating   = $ratingInfo['avg'];
                $ratingCount = $ratingInfo['count'];
            ?>
            <div class="rating-section">
                <span class="rating-label">Votre note</span>
                <div class="star-box-row" id="stars-<?php echo $post['id']; ?>">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                    <button 
                        class="star-box <?php echo ($s <= $userRating) ? 'active' : ''; ?>"
                        onclick="ratePost(<?php echo $post['id']; ?>, <?php echo $s; ?>)"
                        onmouseenter="hoverStars(<?php echo $post['id']; ?>, <?php echo $s; ?>)"
                        onmouseleave="resetStarHover(<?php echo $post['id']; ?>, <?php echo $userRating; ?>)"
                    ><i class="fas fa-star"></i></button>
                    <?php endfor; ?>
                </div>
                <div class="rating-stats" id="rating-summary-<?php echo $post['id']; ?>">
                    <span><?php echo $avgRating > 0 ? number_format($avgRating, 1) : '0.0'; ?></span>
                    <i class="fas fa-star"></i>
                    <?php echo $ratingCount; ?> avis
                </div>
            </div>

            <!-- Big Main Buttons -->
            <?php 
                $likeCount = $controller->GetLikesCount($post['id']);
                $hasLiked  = $controller->HasLiked($post['id'], 1);
                $postComments = $controller->GetCommentsByPost($post['id']);
            ?>
            <div class="main-eng-btns">
                <button class="big-eng-btn btn-like <?php echo $hasLiked ? '' : 'unliked'; ?>" onclick="toggleLike(this, <?php echo $post['id']; ?>)">
                    <i class="fas fa-heart"></i>
                    <span class="engagement-count"><?php echo $likeCount; ?></span>
                </button>
                <button class="big-eng-btn btn-cmt" onclick="toggleComments(this, <?php echo $post['id']; ?>)">
                    <i class="far fa-comment"></i>
                    <span id="cmt-count-<?php echo $post['id']; ?>"><?php echo count($postComments); ?></span>
                </button>
            </div>

            <!-- Comments Area -->
            <div class="cmt-status-text" id="cmt-status-<?php echo $post['id']; ?>" style="<?php echo !empty($postComments) ? 'display:none;' : ''; ?>">
                Aucun commentaire
            </div>
            
            <div class="cmt-section" id="comments-<?php echo $post['id']; ?>">
                <div class="cmt-list" id="comments-list-<?php echo $post['id']; ?>">
                    <?php foreach ($postComments as $comment): ?>
                        <div class="cmt-item">
                            <span class="cmt-author"><?php echo htmlspecialchars($comment['user_name']); ?></span>
                            <span class="cmt-date"><?php echo date('d/m/y', strtotime($comment['created_at'])); ?></span>
                            <p class="cmt-text"><?php echo htmlspecialchars($comment['content']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cmt-input-container">
                    <div class="cmt-input-wrapper">
                        <input type="text" class="cmt-input-field comment-input" placeholder="Ajouter un commentaire..." onkeydown="submitComment(event, this, <?php echo $post['id']; ?>)">
                        <button class="mic-btn" onclick="toggleVoiceComment(this, <?php echo $post['id']; ?>)"><i class="fas fa-microphone"></i></button>
                    </div>
                    <div class="cmt-actions">
                        <button class="action-btn" onclick="runCommentCommand(this, <?php echo $post['id']; ?>, 'send')">
                            <i class="fas fa-paper-plane"></i> Envoyer
                        </button>
                        <button class="action-btn" onclick="runCommentCommand(this, <?php echo $post['id']; ?>, 'clear')">
                            <i class="fas fa-eraser"></i> Effacer
                        </button>
                        <button class="action-btn" onclick="runCommentCommand(this, <?php echo $post['id']; ?>, 'correct')">
                            <i class="fas fa-wand-magic-sparkles"></i> Corriger
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
