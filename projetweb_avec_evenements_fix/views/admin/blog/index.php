<?php
/**
 * admin/blog/index.php - Liste des posts du blog
 */
?>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-newspaper"></i> Gestion du Blog</h1>
        </div>
        <div class="col-auto">
            <a href="<?php echo $this->baseUrl('/admin/blog/create'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau Post
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Titre</th>
                        <th>Categorie</th>
                        <th>Auteur</th>
                        <th>Evenement</th>
                        <th>Statut</th>
                        <th>Vues</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <p class="text-muted">Aucun post pour le moment</p>
                                <a href="<?php echo $this->baseUrl('/admin/blog/create'); ?>" class="btn btn-sm btn-primary">
                                    Creer le premier post
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <?php $authorName = trim((string) ($post['auteur_nom'] ?: $post['auteur_username'] ?: $post['auteur_email'] ?: 'Auteur inconnu')); ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars(substr($post['titre'], 0, 60)); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($post['categorie'] ?: 'General'); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($authorName); ?></td>
                                <td>
                                    <?php if (!empty($post['event_titre'])): ?>
                                        <div><?php echo htmlspecialchars($post['event_titre']); ?></div>
                                        <small class="text-muted"><?php echo !empty($post['event_date']) ? date('d/m/Y', strtotime($post['event_date'])) : ''; ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Aucun</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo ($post['statut'] === 'publie' ? 'success' : 'warning'); ?>">
                                        <?php echo ucfirst($post['statut']); ?>
                                    </span>
                                </td>
                                <td><i class="fas fa-eye"></i> <?php echo (int) $post['vues']; ?></td>
                                <td><small><?php echo date('d/m/Y H:i', strtotime($post['date_creation'])); ?></small></td>
                                <td>
                                    <a href="<?php echo $this->baseUrl('/admin/blog/edit/' . $post['id']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="publishPost(<?php echo (int) $post['id']; ?>)" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-<?php echo ($post['statut'] === 'publie' ? 'lock' : 'unlock'); ?>"></i>
                                    </button>
                                    <a href="<?php echo $this->baseUrl('/admin/blog/delete/' . $post['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce post ?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function publishPost(id) {
    if (!confirm('Changer le statut de publication de ce post ?')) return;

    fetch('<?php echo $this->baseUrl('/admin/blog/publish'); ?>/' + id, {
        method: 'POST'
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        } else {
            alert('Erreur: ' + d.error);
        }
    });
}
</script>
