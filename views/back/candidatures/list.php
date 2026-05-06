<?php include __DIR__ . '/../layout/header.php'; ?>

<?php
function sort_url_cand($col, $current_sort, $current_order) {
    $new_order = ($current_sort === $col && $current_order === 'DESC') ? 'ASC' : 'DESC';
    $params = array_merge($_GET, ['sort' => $col, 'order' => $new_order, 'page' => 1]);
    return 'index.php?' . http_build_query($params);
}
function sort_icon_cand($col, $current_sort, $current_order) {
    if ($current_sort !== $col) return '<i class="fa fa-sort" style="opacity:.35;margin-left:4px;font-size:10px;"></i>';
    return $current_order === 'ASC'
        ? '<i class="fa fa-sort-up" style="color:var(--blue);margin-left:4px;font-size:10px;"></i>'
        : '<i class="fa fa-sort-down" style="color:var(--blue);margin-left:4px;font-size:10px;"></i>';
}
function page_url_cand($p) {
    $params = array_merge($_GET, ['page' => $p]);
    return 'index.php?' . http_build_query($params);
}
?>

<style>
.search-bar-wrap {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 22px; border-bottom: 1px solid var(--border); background: #fafbfd;
    flex-wrap: wrap;
}
.search-input-wrap {
    position: relative; flex: 1; max-width: 360px;
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

.filter-statut-wrap { display: flex; gap: 6px; align-items: center; }
.chip {
    padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;
    cursor: pointer; border: 1.5px solid var(--border); background: white;
    color: var(--muted); transition: all .15s; user-select: none;
}
.chip:hover { border-color: var(--blue); color: var(--blue); }
.chip.active { background: var(--blue); color: white; border-color: var(--blue); }
.chip[data-statut="Acceptée"].active { background: var(--success); border-color: var(--success); }
.chip[data-statut="Rejetée"].active  { background: var(--danger);  border-color: var(--danger); }

.search-count { font-size: 12px; color: var(--muted); margin-left: auto; white-space: nowrap; }
th a { text-decoration: none; color: inherit; display: flex; align-items: center; gap: 2px; white-space: nowrap; }
th a:hover { color: var(--blue); }

/* Pagination */
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

/* Header actions */
.table-header-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.btn-stats-cand { padding: 7px 14px; background: #7c3aed; color: #fff; border: none; border-radius: 7px;
             font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer;
             display: flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-stats-cand:hover { opacity: .88; }
</style>

<div class="table-card">
    <div class="table-header">
        <p class="table-title">Liste des Candidatures</p>
        <div class="table-header-actions">
            <a href="index.php?action=stats_candidatures" class="btn-stats-cand">
                <i class="fa fa-chart-pie"></i> Statistiques
            </a>
            <a href="index.php?action=add_candidature" class="btn-primary"><i class="fa fa-plus"></i> Ajouter manuellement</a>
        </div>
    </div>

    <!-- Barre de recherche + filtre statut -->
    <div class="search-bar-wrap">
        <div class="search-input-wrap">
            <i class="fa fa-search"></i>
            <input type="text" id="searchCand" placeholder="Rechercher par nom, email, offre…" autocomplete="off">
        </div>
        <div class="filter-statut-wrap">
            <span class="chip active" data-statut="">Tous</span>
            <span class="chip" data-statut="En attente">⏳ En attente</span>
            <span class="chip" data-statut="Acceptée">✅ Acceptée</span>
            <span class="chip" data-statut="Rejetée">❌ Rejetée</span>
        </div>
        <span class="search-count" id="searchCount"><?= count($candidatures) ?> sur <?= $total_count ?> candidature(s)</span>
    </div>

    <table>
        <thead>
            <tr>
                <th><a href="<?= sort_url_cand('nom_candidat', $current_sort, $current_order) ?>">Candidat <?= sort_icon_cand('nom_candidat', $current_sort, $current_order) ?></a></th>
                <th><a href="<?= sort_url_cand('email_candidat', $current_sort, $current_order) ?>">Email <?= sort_icon_cand('email_candidat', $current_sort, $current_order) ?></a></th>
                <th><a href="<?= sort_url_cand('titre_offre', $current_sort, $current_order) ?>">Offre <?= sort_icon_cand('titre_offre', $current_sort, $current_order) ?></a></th>
                <th><a href="<?= sort_url_cand('date_candidature', $current_sort, $current_order) ?>">Date <?= sort_icon_cand('date_candidature', $current_sort, $current_order) ?></a></th>
                <th><a href="<?= sort_url_cand('statut', $current_sort, $current_order) ?>">Statut <?= sort_icon_cand('statut', $current_sort, $current_order) ?></a></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="candBody">
            <?php foreach($candidatures as $c): ?>
            <tr class="cand-row" data-statut="<?= htmlspecialchars($c['statut']) ?>">
                <td><strong><?= htmlspecialchars($c['nom_candidat'] ?? '') ?> <?= htmlspecialchars($c['prenom_candidat'] ?? '') ?></strong></td>
                <td><?= htmlspecialchars($c['email_candidat'] ?? '') ?></td>
                <td><span style="background:#f1f5f9;padding:3px 8px;border-radius:12px;font-size:11px;"><?= htmlspecialchars($c['titre_offre'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($c['date_candidature']) ?></td>
                <td>
                    <?php 
                        if($c['statut'] == 'Acceptée')     echo '<span class="badge" style="color:var(--success)">✅ Acceptée</span>';
                        elseif($c['statut'] == 'Rejetée')  echo '<span class="badge" style="color:var(--danger)">❌ Rejetée</span>';
                        else                               echo '<span class="badge" style="color:var(--warning)">⏳ En attente</span>';
                    ?>
                </td>
                <td>
                    <a href="index.php?action=edit_candidature&id=<?= $c['id_candidature'] ?>" class="action-btn edit" title="Éditer"><i class="fa fa-pen"></i></a>
                    <a href="index.php?action=delete_candidature&id=<?= $c['id_candidature'] ?>" class="action-btn del" title="Supprimer" onclick="return confirm('Supprimer cette candidature ?');"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($candidatures)): ?>
            <tr><td colspan="6" style="text-align:center;">Aucune candidature trouvée</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div id="noResultsCand" style="display:none; text-align:center; padding:20px; color:var(--muted);">
        <i class="fa fa-search" style="margin-right:6px;"></i> Aucune candidature ne correspond à votre recherche.
    </div>

    <!-- ── PAGINATION ──────────────────────────────────────────────────────── -->
    <div class="pagination-wrap">
        <div class="pagination-info">
            Page <strong><?= $page ?></strong> sur <strong><?= $total_pages ?></strong>
            &nbsp;·&nbsp; <?= $total_count ?> candidature(s) au total
        </div>
        <div class="pagination-links">
            <a href="<?= page_url_cand(1) ?>" class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>" title="Première page">
                <i class="fa fa-angles-left"></i>
            </a>
            <a href="<?= page_url_cand($page - 1) ?>" class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                <i class="fa fa-angle-left"></i>
            </a>
            <?php
            $range = 2;
            $start = max(1, $page - $range);
            $end   = min($total_pages, $page + $range);
            if ($start > 1): ?>
                <a href="<?= page_url_cand(1) ?>" class="page-btn">1</a>
                <?php if ($start > 2): ?><span class="page-btn" style="border:none;background:none;color:var(--muted);">…</span><?php endif; ?>
            <?php endif; ?>
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="<?= page_url_cand($i) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><span class="page-btn" style="border:none;background:none;color:var(--muted);">…</span><?php endif; ?>
                <a href="<?= page_url_cand($total_pages) ?>" class="page-btn"><?= $total_pages ?></a>
            <?php endif; ?>
            <a href="<?= page_url_cand($page + 1) ?>" class="page-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <i class="fa fa-angle-right"></i>
            </a>
            <a href="<?= page_url_cand($total_pages) ?>" class="page-btn <?= $page >= $total_pages ? 'disabled' : '' ?>" title="Dernière page">
                <i class="fa fa-angles-right"></i>
            </a>
        </div>
    </div>
</div>

<script>
(function () {
    const searchInput = document.getElementById('searchCand');
    const rows        = document.querySelectorAll('.cand-row');
    const countEl     = document.getElementById('searchCount');
    const noMsg       = document.getElementById('noResultsCand');
    const chips       = document.querySelectorAll('.chip[data-statut]');
    const total       = rows.length;

    let activeStatut = '';

    function applyFilters() {
        const q = searchInput.value.trim().toLowerCase();
        let visible = 0;

        rows.forEach(function (row) {
            const text   = row.textContent.toLowerCase();
            const statut = row.dataset.statut;

            const matchText   = !q || text.includes(q);
            const matchStatut = !activeStatut || statut === activeStatut;

            const show = matchText && matchStatut;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        countEl.textContent = visible + (visible < total ? ' / ' + total : '') + ' candidature(s)';
        noMsg.style.display = (visible === 0) ? 'block' : 'none';
    }

    searchInput.addEventListener('input', applyFilters);

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            chips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            activeStatut = this.dataset.statut;
            applyFilters();
        });
    });

    searchInput.focus();
})();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
