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

/* ── Type table ──────────────────────────────────────────── */
.stats-table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}
.stats-table-card .table-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 22px; border-bottom: 1px solid var(--border);
}
.progress-bar-wrap { background: #e5e7eb; border-radius: 99px; height: 8px; overflow: hidden; }
.progress-bar-fill { height: 100%; border-radius: 99px; transition: width .6s ease; }

/* ── Back button ─────────────────────────────────────────── */
.page-actions { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
</style>

<!-- Page actions -->
<div class="page-actions">
    <a href="index.php?action=list_offres" class="btn-outline">
        <i class="fa fa-arrow-left"></i> Retour aux offres
    </a>
    <a href="index.php?action=pdf_offres" class="btn-danger" target="_blank">
        <i class="fa fa-file-pdf"></i> Exporter PDF
    </a>
</div>

<!-- ── KPI Cards ──────────────────────────────────────────────────────────── -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:var(--blue);"><i class="fa fa-briefcase"></i></div>
        <span class="kpi-label">Total Offres</span>
        <span class="kpi-value"><?= $totalOffres ?></span>
        <span class="kpi-sub">offres publiées</span>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:var(--success);"><i class="fa fa-circle-check"></i></div>
        <span class="kpi-label">Actives</span>
        <span class="kpi-value"><?= $totalActives ?></span>
        <span class="kpi-sub"><?= $totalOffres > 0 ? round($totalActives/$totalOffres*100) : 0 ?>% du total</span>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:var(--danger);"><i class="fa fa-circle-xmark"></i></div>
        <span class="kpi-label">Inactives</span>
        <span class="kpi-value"><?= $totalInactives ?></span>
        <span class="kpi-sub"><?= $totalOffres > 0 ? round($totalInactives/$totalOffres*100) : 0 ?>% du total</span>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#7c3aed;"><i class="fa fa-users"></i></div>
        <span class="kpi-label">Candidatures</span>
        <span class="kpi-value"><?= $totalCandidatures ?></span>
        <span class="kpi-sub">au total</span>
    </div>
</div>

<!-- ── Charts row ─────────────────────────────────────────────────────────── -->
<div class="charts-grid">
    <!-- Doughnut : répartition par type -->
    <div class="chart-card">
        <p class="chart-title"><i class="fa fa-chart-pie"></i> Répartition par type de contrat</p>
        <canvas id="chartType" height="220"></canvas>
    </div>
    <!-- Bar : offres par mois -->
    <div class="chart-card">
        <p class="chart-title"><i class="fa fa-chart-bar"></i> Offres publiées par mois</p>
        <canvas id="chartMonth" height="220"></canvas>
    </div>
</div>

<!-- ── Tableau détaillé par type ──────────────────────────────────────────── -->
<div class="stats-table-card">
    <div class="table-header">
        <p class="table-title">Détail par type de contrat</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Nombre d'offres</th>
                <th>Pourcentage</th>
                <th style="width:200px;">Répartition</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $colors = ['#2d79ff','#7c3aed','#059669','#f59e0b','#ef4444','#0891b2'];
            $ci = 0;
            foreach ($statsByType as $s):
                $pct = $totalOffres > 0 ? round($s['total'] / $totalOffres * 100, 1) : 0;
                $color = $colors[$ci % count($colors)]; $ci++;
            ?>
            <tr>
                <td>
                    <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:<?= $color ?>;margin-right:8px;vertical-align:middle;"></span>
                    <strong><?= htmlspecialchars($s['type'] ?: 'Non défini') ?></strong>
                </td>
                <td><?= $s['total'] ?></td>
                <td><?= $pct ?>%</td>
                <td>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($statsByType)): ?>
            <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--muted);">Aucune donnée disponible</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Données PHP → JS ──────────────────────────────────────────────────────
const typeLabels  = <?= json_encode(array_column($statsByType,  'type')) ?>;
const typeData    = <?= json_encode(array_column($statsByType,  'total')) ?>;
const monthLabels = <?= json_encode(array_column($statsByMonth, 'mois')) ?>;
const monthData   = <?= json_encode(array_column($statsByMonth, 'total')) ?>;

const palette = ['#2d79ff','#7c3aed','#059669','#f59e0b','#ef4444','#0891b2','#db2777','#84cc16'];

// ── Doughnut ──────────────────────────────────────────────────────────────
new Chart(document.getElementById('chartType'), {
    type: 'doughnut',
    data: {
        labels: typeLabels,
        datasets: [{
            data: typeData,
            backgroundColor: palette.slice(0, typeLabels.length),
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
                label: ctx => ' ' + ctx.label + ' : ' + ctx.parsed + ' offre(s)'
            }}
        },
        cutout: '62%'
    }
});

// ── Bar chart par mois ────────────────────────────────────────────────────
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
            label: 'Offres publiées',
            data: monthData,
            backgroundColor: 'rgba(45,121,255,0.15)',
            borderColor: '#2d79ff',
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
                label: ctx => ' ' + ctx.parsed.y + ' offre(s)'
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
