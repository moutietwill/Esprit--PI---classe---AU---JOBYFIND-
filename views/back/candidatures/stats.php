<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
/* ── KPI Cards ────────────────────────────────────────────── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 18px;
    margin-bottom: 28px;
}
.kpi-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 22px 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: box-shadow .2s, transform .2s;
}
.kpi-card:hover { box-shadow: 0 6px 24px rgba(45,121,255,.1); transform: translateY(-2px); }
.kpi-icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: #fff; margin-bottom: 4px;
}
.kpi-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); }
.kpi-value { font-size: 28px; font-weight: 700; color: var(--navy); line-height: 1; }
.kpi-sub   { font-size: 11px; color: var(--muted); }

/* ── Charts grid ─────────────────────────────────────────── */
.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 28px;
}
@media (max-width: 800px) { .charts-grid { grid-template-columns: 1fr; } }
.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px 22px;
}
.chart-title {
    font-size: 13px; font-weight: 600; color: var(--navy);
    margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
}
.chart-title i { color: var(--blue); }

/* ── Top offres table ────────────────────────────────────── */
.stats-table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 28px;
}
.stats-table-card .table-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 22px; border-bottom: 1px solid var(--border);
}
.progress-bar-wrap { background: #e5e7eb; border-radius: 99px; height: 8px; overflow: hidden; }
.progress-bar-fill { height: 100%; border-radius: 99px; transition: width .6s ease; }

/* ── Back button ─────────────────────────────────────────── */
.page-actions { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }

/* ── Taux badge ──────────────────────────────────────────── */
.taux-badge {
    display: inline-block; padding: 3px 10px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
    background: rgba(34,197,94,.12); color: var(--success);
}
</style>

<!-- Page actions -->
<div class="page-actions">
    <a href="index.php?action=list_candidatures" class="btn-outline">
        <i class="fa fa-arrow-left"></i> Retour aux candidatures
    </a>
</div>

<!-- ── KPI Cards ──────────────────────────────────────────────────────────── -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:var(--blue);"><i class="fa fa-file-pen"></i></div>
        <span class="kpi-label">Total Candidatures</span>
        <span class="kpi-value"><?= $totalCandidatures ?></span>
        <span class="kpi-sub">candidatures reçues</span>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:var(--warning);"><i class="fa fa-clock"></i></div>
        <span class="kpi-label">En attente</span>
        <span class="kpi-value"><?= $totalEnAttente ?></span>
        <span class="kpi-sub"><?= $totalCandidatures > 0 ? round($totalEnAttente/$totalCandidatures*100) : 0 ?>% du total</span>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:var(--success);"><i class="fa fa-circle-check"></i></div>
        <span class="kpi-label">Acceptées</span>
        <span class="kpi-value"><?= $totalAcceptees ?></span>
        <span class="kpi-sub"><?= $totalCandidatures > 0 ? round($totalAcceptees/$totalCandidatures*100) : 0 ?>% du total</span>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:var(--danger);"><i class="fa fa-circle-xmark"></i></div>
        <span class="kpi-label">Rejetées</span>
        <span class="kpi-value"><?= $totalRejetees ?></span>
        <span class="kpi-sub"><?= $totalCandidatures > 0 ? round($totalRejetees/$totalCandidatures*100) : 0 ?>% du total</span>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#7c3aed;"><i class="fa fa-percent"></i></div>
        <span class="kpi-label">Taux d'acceptation</span>
        <span class="kpi-value"><?= $tauxAcceptation ?>%</span>
        <span class="kpi-sub"><span class="taux-badge"><?= $tauxAcceptation >= 50 ? 'Bon taux' : 'À améliorer' ?></span></span>
    </div>
</div>

<!-- ── Charts row ─────────────────────────────────────────────────────────── -->
<div class="charts-grid">
    <!-- Doughnut : répartition par statut -->
    <div class="chart-card">
        <p class="chart-title"><i class="fa fa-chart-pie"></i> Répartition par statut</p>
        <canvas id="chartStatut" height="220"></canvas>
    </div>
    <!-- Bar : candidatures par mois -->
    <div class="chart-card">
        <p class="chart-title"><i class="fa fa-chart-bar"></i> Candidatures reçues par mois</p>
        <canvas id="chartMonth" height="220"></canvas>
    </div>
</div>

<!-- ── Top 5 offres ────────────────────────────────────────────────────────── -->
<div class="stats-table-card">
    <div class="table-header">
        <p class="table-title"><i class="fa fa-trophy" style="color:#f59e0b;margin-right:8px;"></i>Top 5 offres les plus demandées</p>
    </div>
    <?php $maxTop = !empty($topOffres) ? $topOffres[0]['total'] : 1; ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Titre de l'offre</th>
                <th>Candidatures</th>
                <th style="width:220px;">Répartition</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $medals = ['🥇','🥈','🥉'];
            $topColors = ['#2d79ff','#7c3aed','#059669','#f59e0b','#0891b2'];
            foreach ($topOffres as $i => $t):
                $pct = $maxTop > 0 ? round($t['total'] / $maxTop * 100, 1) : 0;
                $color = $topColors[$i % count($topColors)];
            ?>
            <tr>
                <td style="font-size:18px; text-align:center;"><?= $medals[$i] ?? ($i + 1) ?></td>
                <td><strong><?= htmlspecialchars($t['titre']) ?></strong></td>
                <td><span style="font-weight:700;color:var(--navy);"><?= $t['total'] ?></span></td>
                <td>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($topOffres)): ?>
            <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--muted);">Aucune donnée disponible</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Données PHP → JS ──────────────────────────────────────────────────────
const statutLabels = <?= json_encode(array_column($statsByStatut, 'statut')) ?>;
const statutData   = <?= json_encode(array_column($statsByStatut, 'total')) ?>;
const monthLabels  = <?= json_encode(array_column($statsByMonth, 'mois')) ?>;
const monthData    = <?= json_encode(array_column($statsByMonth, 'total')) ?>;

const statutColors = {
    'En attente': '#f59e0b',
    'Acceptée':   '#22c55e',
    'Rejetée':    '#ef4444'
};
const bgColors = statutLabels.map(l => statutColors[l] || '#9ca3af');

// ── Doughnut : statuts ────────────────────────────────────────────────────
new Chart(document.getElementById('chartStatut'), {
    type: 'doughnut',
    data: {
        labels: statutLabels,
        datasets: [{
            data: statutData,
            backgroundColor: bgColors,
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { family: 'DM Sans', size: 12 }, padding: 16 } },
            tooltip: { callbacks: {
                label: ctx => ' ' + ctx.label + ' : ' + ctx.parsed + ' candidature(s)'
            }}
        },
        cutout: '62%'
    }
});

// ── Bar chart : par mois ──────────────────────────────────────────────────
new Chart(document.getElementById('chartMonth'), {
    type: 'bar',
    data: {
        labels: monthLabels.map(m => {
            if (!m) return '';
            const [y, mo] = m.split('-');
            const date = new Date(y, mo - 1);
            return date.toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' });
        }),
        datasets: [{
            label: 'Candidatures',
            data: monthData,
            backgroundColor: 'rgba(124,58,237,0.15)',
            borderColor: '#7c3aed',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: {
                label: ctx => ' ' + ctx.parsed.y + ' candidature(s)'
            }}
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1, font: { family: 'DM Sans', size: 11 } },
                grid: { color: 'rgba(0,0,0,.05)' }
            },
            x: {
                ticks: { font: { family: 'DM Sans', size: 11 } },
                grid: { display: false }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
