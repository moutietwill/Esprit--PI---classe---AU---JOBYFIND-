<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post->getTitle()); ?> - Blog</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
        }

        body {
            background: #f7f9fc;
        }

        .post-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .post-cover {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .post-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            line-height: 1.8;
            font-size: 1.1rem;
            color: #333;
        }

        .post-content h2,
        .post-content h3 {
            color: #222;
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .post-content p {
            margin-bottom: 1rem;
        }

        .post-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .post-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }

        .post-meta-item i {
            color: var(--primary);
            font-size: 1.2rem;
        }

        .post-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-btn {
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            background: var(--primary);
            color: white;
        }

        .action-btn.liked {
            background: var(--primary);
            color: white;
        }

        .rating-box {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .rating-box h4 {
            margin-bottom: 1rem;
            color: #333;
        }

        .stars {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .star {
            font-size: 1.5rem;
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .star.filled {
            color: #ffc107;
        }

        .star:hover {
            transform: scale(1.1);
        }

        .sidebar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            position: sticky;
            top: 20px;
        }

        .sidebar-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary);
        }

        .comment-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .comment-form {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .comment-form input,
        .comment-form textarea {
            margin-bottom: 1rem;
            border: 2px solid #eee;
            border-radius: 8px;
            padding: 0.8rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .comment-form input:focus,
        .comment-form textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .comment {
            padding: 1.5rem;
            background: #f7f9fc;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .comment-author {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .comment-date {
            font-size: 0.85rem;
            color: #999;
            margin-bottom: 0.8rem;
        }

        .breadcrumb {
            background: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .post-actions {
                flex-direction: column;
            }

            .action-btn {
                justify-content: center;
            }

            .sidebar {
                position: static;
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation breadcrumb -->
    <div class="container mt-3">
        <nav class="breadcrumb">
            <a href="<?php echo htmlspecialchars($url('/blog'), ENT_QUOTES, 'UTF-8'); ?>" class="breadcrumb-item">Blog</a>
            <span class="breadcrumb-item active"><?php echo htmlspecialchars(substr($post->getTitle(), 0, 50)); ?></span>
        </nav>
    </div>

    <!-- Header -->
    <div class="post-header">
        <div class="container">
            <h1><?php echo htmlspecialchars($post->getTitle()); ?></h1>
            <p class="mb-0">Article publié le <?php echo $post->getFormattedDate(); ?></p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Article -->
            <div class="col-lg-8">
                <img src="<?php echo htmlspecialchars($asset($post->getCoverImage()), ENT_QUOTES, 'UTF-8'); ?>" 
                     alt="<?php echo htmlspecialchars($post->getTitle()); ?>" 
                     class="post-cover">

                <div class="post-meta">
                    <div class="post-meta-item">
                        <i class="fas fa-folder"></i>
                        <span><?php echo htmlspecialchars($post->getCategory() ?? 'Général'); ?></span>
                    </div>
                    <div class="post-meta-item">
                        <i class="fas fa-eye"></i>
                        <span><?php echo $post->getViews(); ?> vues</span>
                    </div>
                    <div class="post-meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo $post->getFormattedDate(); ?></span>
                    </div>
                </div>

                <div class="post-actions">
                    <button class="action-btn" id="likeBtn" onclick="toggleLike(<?php echo $post->getId(); ?>)">
                        <i class="fas fa-heart"></i> J'aime <span id="likesCount">(<?php echo $likesCount; ?>)</span>
                    </button>
                    <button class="action-btn" onclick="document.getElementById('commentSection').scrollIntoView({behavior: 'smooth'})">
                        <i class="fas fa-comment"></i> Commenter
                    </button>
                    <button class="action-btn" onclick="sharePost()">
                        <i class="fas fa-share"></i> Partager
                    </button>
                </div>

                <!-- Contenu de l'article -->
                <div class="post-content">
                    <?php echo $post->getContent(); ?>
                </div>

                <!-- Section Commentaires -->
                <div class="comment-section" id="commentSection">
                    <h3><i class="fas fa-comments"></i> Commentaires</h3>

                    <?php if (empty($comments)): ?>
                        <p style="color: #999; text-align: center; padding: 2rem;">
                            Aucun commentaire pour le moment. Soyez le premier!
                        </p>
                    <?php else: ?>
                        <div style="margin-bottom: 2rem;">
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <div class="comment-author"><?php echo htmlspecialchars($comment['author_name']); ?></div>
                                    <div class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></div>
                                    <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire commentaire -->
                    <form method="POST" action="<?php echo htmlspecialchars($url('/blog/add-comment'), ENT_QUOTES, 'UTF-8'); ?>" class="comment-form" id="commentForm">
                        <input type="hidden" name="post_id" value="<?php echo $post->getId(); ?>">
                        <input type="text" name="author_name" placeholder="Votre nom" required>
                        <input type="email" name="author_email" placeholder="Votre email" required>
                        <textarea name="content" placeholder="Votre commentaire..." rows="5" required></textarea>
                        <button type="submit" class="btn-submit">Publier le commentaire</button>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Rating -->
                <div class="sidebar">
                    <div class="sidebar-title">Évaluer cet article</div>
                    <div class="stars" id="ratingStars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star star <?php echo $i <= round($rating) ? 'filled' : ''; ?>" 
                               onclick="ratePost(<?php echo $post->getId(); ?>, <?php echo $i; ?>)"></i>
                        <?php endfor; ?>
                    </div>
                    <p style="color: #999; font-size: 0.9rem; text-align: center;">
                        Note moyenne: <?php echo $rating > 0 ? $rating . '/5' : 'Non évalué'; ?>
                    </p>
                </div>

                <!-- Autres articles -->
                <div class="sidebar">
                    <div class="sidebar-title">Articles connexes</div>
                    <div class="text-center" style="color: #999;">
                        <p>Consultez d'autres articles de la catégorie <?php echo htmlspecialchars($post->getCategory() ?? 'Général'); ?></p>
                        <a href="<?php echo htmlspecialchars($url('/blog') . '?category=' . urlencode($post->getCategory() ?? 'Général'), ENT_QUOTES, 'UTF-8'); ?>" 
                           style="color: var(--primary); font-weight: 600;">
                            Voir plus <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Actions -->
                <div class="sidebar">
                    <div class="sidebar-title">Actions</div>
                    <a href="<?php echo htmlspecialchars($url('/blog'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-create" style="width: 100%; text-align: center; display: block; margin-bottom: 0.5rem;">
                        <i class="fas fa-arrow-left"></i> Retour au blog
                    </a>
                    <a href="<?php echo htmlspecialchars($url('/events'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-create" style="width: 100%; text-align: center; display: block;">
                        <i class="fas fa-calendar"></i> Voir les événements
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const toggleLikeUrl = <?php echo json_encode($url('/blog/toggle-like')); ?>;
        const addRatingUrl = <?php echo json_encode($url('/blog/add-rating')); ?>;
        const addCommentUrl = <?php echo json_encode($url('/blog/add-comment')); ?>;

        function toggleLike(postId) {
            const btn = document.getElementById('likeBtn');
            const formData = new FormData();
            formData.append('post_id', postId);

            fetch(toggleLikeUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('likesCount').textContent = '(' + data.likesCount + ')';
                    btn.classList.toggle('liked');
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => console.error('Erreur:', error));
        }

        function ratePost(postId, rating) {
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('rating', rating);

            fetch(addRatingUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour les étoiles
                    const stars = document.querySelectorAll('#ratingStars .star');
                    stars.forEach((star, index) => {
                        if (index < Math.round(data.avgRating)) {
                            star.classList.add('filled');
                        } else {
                            star.classList.remove('filled');
                        }
                    });
                    alert('Merci pour votre évaluation!');
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => console.error('Erreur:', error));
        }

        function sharePost() {
            const url = window.location.href;
            const title = '<?php echo htmlspecialchars($post->getTitle()); ?>';
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    url: url
                });
            } else {
                alert('Lien copié: ' + url);
                navigator.clipboard.writeText(url);
            }
        }

        // Gérer la soumission du commentaire
        document.getElementById('commentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(addCommentUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Commentaire publié avec succès!');
                    this.reset();
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
