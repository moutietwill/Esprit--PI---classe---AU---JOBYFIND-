<?php include __DIR__ . '/../layout/header.php'; ?>

<?php
// Helper : construire l'URL de tri (préserve la page courante)
function sort_url($col, $current_sort, $current_order, $extra = []) {
    $new_order = ($current_sort === $col && $current_order === 'DESC') ? 'ASC' : 'DESC';
    $params = array_merge($_GET, ['sort' => $col, 'order' => $new_order, 'page' => 1], $extra);
    return 'index.php?' . http_build_query($params);
}
function sort_icon($col, $current_sort, $current_order) {
    if ($current_sort !== $col) return '<i class="fa fa-sort" style="opacity:.35;margin-left:4px;font-size:10px;"></i>';
    return $current_order === 'ASC'
        ? '<i class="fa fa-sort-up" style="color:var(--blue);margin-left:4px;font-size:10px;"></i>'
        : '<i class="fa fa-sort-down" style="color:var(--blue);margin-left:4px;font-size:10px;"></i>';
}

// URL de pagination (préserve tri & recherche)
function page_url($p) {
    $params = array_merge($_GET, ['page' => $p]);
    return 'index.php?' . http_build_query($params);
}
?>

<style>
/* ── Search bar ───────────────────────────────────────────── */
.search-bar-wrap {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 22px; border-bottom: 1px solid var(--border); background: #fafbfd;
    flex-wrap: wrap;
}
.search-input-wrap {
    position: relative; flex: 1; max-width: 340px;
}
.search-input-wrap i {
    position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
    color: var(--muted); font-size: 13px; pointer-events: none;
}
.search-input-wrap input {
    width: 100%; padding: 7px 12px 7px 32px;
    border: 1.5px solid var(--border); border-radius: 7px;
    font-family: 'DM Sans', sans-serif; font-size: 13px;
    color: var(--text); outline: none; background: white;
    transition: border-color .15s;
}
.search-input-wrap input:focus { border-color: var(--blue); }
.search-count { font-size: 12px; color: var(--muted); margin-left: auto; }
th a { text-decoration: none; color: inherit; display: flex; align-items: center; gap: 2px; white-space: nowrap; }
th a:hover { color: var(--blue); }

/* ── Pagination ───────────────────────────────────────────── */
.pagination-wrap {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 22px; border-top: 1px solid var(--border);
    background: #fafbfd; flex-wrap: wrap; gap: 10px;
}
.pagination-info { font-size: 12px; color: var(--muted); }
.pagination-links { display: flex; gap: 5px; align-items: center; }
.page-btn {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px; padding: 0 10px;
    border: 1.5px solid var(--border); border-radius: 7px;
    text-decoration: none; font-size: 12px; font-weight: 600;
    color: var(--text); background: #fff; transition: all .15s;
}
.page-btn:hover { border-color: var(--blue); color: var(--blue); background: #eff6ff; }
.page-btn.active { background: var(--blue); color: #fff; border-color: var(--blue); pointer-events: none; }
.page-btn.disabled { opacity: .4; pointer-events: none; }

/* ── Header actions ──────────────────────────────────────── */
.table-header-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.btn-stats { padding: 7px 14px; background: #7c3aed; color: #fff; border: none; border-radius: 7px;
             font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer;
             display: flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-stats:hover { opacity: .88; }
.btn-pdf   { padding: 7px 14px; background: #dc2626; color: #fff; border: none; border-radius: 7px;
             font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer;
             display: flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-pdf:hover { opacity: .88; }
</style>

<div class="table-card">
    <div class="table-header">
        <p class="table-title">Liste des Offres</p>
        <div class="table-header-actions">
            <a href="index.php?action=stats_offres" class="btn-stats">
                <i class="fa fa-chart-pie"></i> Statistiques
            </a>
            <a href="index.php?action=pdf_offres" class="btn-pdf" target="_blank">
                <i class="fa fa-file-pdf"></i> Exporter PDF
            </a>
            <a href="index.php?action=add_offre" class="btn-primary">
                <i class="fa fa-plus"></i> Ajouter une offre
            </a>
        </div>
    </div>

    <!-- Barre de recherche dynamique -->
    <div class="search-bar-wrap">
        <div class="search-input-wrap">
            <i class="fa fa-search"></i>
            <input type="text" id="searchOffres" placeholder="Rechercher par titre, type, statut…" autocomplete="off">
        </div>
        <span class="search-count" id="searchCount"><?= count($offres) ?> sur <?= $total_count ?> offre(s)</span>
    </div>

    <table id="offresTable">
        <thead>
            <tr>
                <th><a href="<?= sort_url('id_offre', $current_sort, $current_order) ?>">ID <?= sort_icon('id_offre', $current_sort, $current_order) ?></a></th>
                <th><a href="<?= sort_url('titre', $current_sort, $current_order) ?>">Titre <?= sort_icon('titre', $current_sort, $current_order) ?></a></th>
                <th><a href="<?= sort_url('datePublication', $current_sort, $current_order) ?>">Date Pub. <?= sort_icon('datePublication', $current_sort, $current_order) ?></a></th>
                <th><a href="<?= sort_url('type', $current_sort, $current_order) ?>">Type <?= sort_icon('type', $current_sort, $current_order) ?></a></th>
                <th><a href="<?= sort_url('statut', $current_sort, $current_order) ?>">Statut <?= sort_icon('statut', $current_sort, $current_order) ?></a></th>
                <th>Candidatures</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="offresBody">
            <?php foreach($offres as $o): ?>
            <tr class="offre-row">
                <td><?= htmlspecialchars($o['id_offre']) ?></td>
                <td><strong><?= htmlspecialchars($o['titre']) ?></strong></td>
                <td><?= htmlspecialchars($o['datePublication']) ?></td>
                <td><?= htmlspecialchars($o['type']) ?></td>
                <td>
                    <?php if($o['statut'] == 'Actif') {
                        echo '<span style="color:var(--success);"><i class="fa fa-circle" style="font-size:8px;"></i> Actif</span>';
                    } else {
                        echo '<span style="color:var(--danger);"><i class="fa fa-circle" style="font-size:8px;"></i> Inactif</span>';
                    } ?>
                </td>
                <td>
                    <a href="index.php?action=list_candidatures_offre&id_offre=<?= $o['id_offre'] ?>" class="btn-primary" style="padding: 5px 10px; font-size: 11px;"><i class="fa fa-file-pen"></i> Voir</a>
                </td>
                <td>
                    <a href="index.php?action=edit_offre&id=<?= $o['id_offre'] ?>" class="action-btn edit" title="Modifier"><i class="fa fa-pen"></i></a>
                    <a href="index.php?action=delete_offre&id=<?= $o['id_offre'] ?>" class="action-btn del" title="Supprimer" onclick="return confirm('Supprimer cette offre ?');"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($offres)): ?>
            <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--muted);">Aucune offre trouvée</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Ligne "aucun résultat" pour la recherche JS -->
    <div id="noResultsMsg" style="display:none; text-align:center; padding:20px; color:var(--muted);">
        <i class="fa fa-search" style="margin-right:6px;"></i> Aucune offre ne correspond à votre recherche.
    </div>

    <!-- ── PAGINATION ─────────────────────────────────────────────────────── -->
    <div class="pagination-wrap">
        <div class="pagination-info">
            Page <strong><?= $page ?></strong> sur <strong><?= $total_pages ?></strong>
            &nbsp;·&nbsp; <?= $total_count ?> offre(s) au total
        </div>
        <div class="pagination-links">
            <!-- Première page -->
            <a href="<?= page_url(1) ?>" class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>" title="Première page">
                <i class="fa fa-angles-left"></i>
            </a>
            <!-- Page précédente -->
            <a href="<?= page_url($page - 1) ?>" class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                <i class="fa fa-angle-left"></i>
            </a>

            <?php
            // Afficher au max 5 numéros de page autour de la page courante
            $range = 2;
            $start = max(1, $page - $range);
            $end   = min($total_pages, $page + $range);
            if ($start > 1): ?>
                <a href="<?= page_url(1) ?>" class="page-btn">1</a>
                <?php if ($start > 2): ?><span class="page-btn" style="border:none;background:none;color:var(--muted);">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="<?= page_url($i) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><span class="page-btn" style="border:none;background:none;color:var(--muted);">…</span><?php endif; ?>
                <a href="<?= page_url($total_pages) ?>" class="page-btn"><?= $total_pages ?></a>
            <?php endif; ?>

            <!-- Page suivante -->
            <a href="<?= page_url($page + 1) ?>" class="page-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <i class="fa fa-angle-right"></i>
            </a>
            <!-- Dernière page -->
            <a href="<?= page_url($total_pages) ?>" class="page-btn <?= $page >= $total_pages ? 'disabled' : '' ?>" title="Dernière page">
                <i class="fa fa-angles-right"></i>
            </a>
        </div>
    </div>
</div>

<script>
(function() {
    const input  = document.getElementById('searchOffres');
    const rows   = document.querySelectorAll('.offre-row');
    const count  = document.getElementById('searchCount');
    const noMsg  = document.getElementById('noResultsMsg');
    const total  = rows.length;
    const totalCount = <?= $total_count ?>;

    input.addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        let visible = 0;

        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            const match = !q || text.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        count.textContent = q
            ? visible + ' / ' + total + ' offre(s) sur cette page'
            : total + ' sur ' + totalCount + ' offre(s)';
        noMsg.style.display = (visible === 0 && q) ? 'block' : 'none';
    });

    // Focus automatique
    input.focus();
})();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
