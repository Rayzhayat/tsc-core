<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$p = $perf;
$rel_cfg = [
    'andal'      => ['color' => '#1cc88a', 'badge' => 'bg-success',  'icon' => 'shield-alt',       'label' => 'Andal'],
    'cukup'      => ['color' => '#f6c23e', 'badge' => 'bg-warning text-dark', 'icon' => 'exclamation-circle', 'label' => 'Cukup'],
    'bermasalah' => ['color' => '#e74a3b', 'badge' => 'bg-danger',   'icon' => 'times-circle',     'label' => 'Bermasalah'],
];
$rc = $rel_cfg[$p->reliability_label] ?? $rel_cfg['bermasalah'];
?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <!-- Breadcrumb + Header -->
                <div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-1 small">
                                <li class="breadcrumb-item"><a href="<?= base_url('analytics') ?>">Analytics</a></li>
                                <li class="breadcrumb-item"><a href="<?= base_url('vendor_performance') ?>">Vendor Performance</a></li>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($vendor) ?></li>
                            </ol>
                        </nav>
                        <h1 class="page-title mb-0 d-flex align-items-center gap-2">
                            <span class="badge <?= $rc['badge'] ?> fs-6">
                                <i class="fas fa-<?= $rc['icon'] ?> me-1"></i> <?= $rc['label'] ?>
                            </span>
                            <?= htmlspecialchars($vendor) ?>
                        </h1>
                        <?php if ($p->first_trip && $p->last_trip): ?>
                            <small class="text-muted">
                                Data: <?= date('d M Y', strtotime($p->first_trip)) ?> –
                                      <?= date('d M Y', strtotime($p->last_trip)) ?>
                            </small>
                        <?php endif ?>
                    </div>

                    <!-- Sheet Type Filter -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?php foreach ($sheet_types as $st): ?>
                            <a href="<?= base_url('vendor_performance/detail/' . rawurlencode($vendor) . '?sheet_type=' . urlencode($st->sheet_type)) ?>"
                               class="btn btn-sm <?= $sheet_type == $st->sheet_type ? 'btn-primary' : 'btn-outline-secondary' ?>">
                                <?= $st->sheet_type ?>
                            </a>
                        <?php endforeach ?>
                        <?php if (!empty($sheet_type)): ?>
                            <a href="<?= base_url('vendor_performance/detail/' . rawurlencode($vendor)) ?>"
                               class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times"></i> Semua Sheet
                            </a>
                        <?php endif ?>
                    </div>
                </div>

                <!-- Scorecard Cards -->
                <div class="row g-3 mb-4">
                    <!-- Total Trip -->
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100" style="border-top:3px solid #4e73df !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1" style="font-size:.65rem;letter-spacing:.05em">Total Trip</div>
                                <div class="fw-bold h4 mb-0"><?= number_format($p->total_trip) ?></div>
                                <div class="text-muted" style="font-size:.7rem"><?= $p->total_rute ?> rute · <?= $p->total_customer ?> customer</div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Cost -->
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100" style="border-top:3px solid #36b9cc !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1" style="font-size:.65rem;letter-spacing:.05em">Total Cost</div>
                                <div class="fw-bold" style="font-size:.95rem">
                                    Rp <?= number_format($p->total_cost, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Margin terkait -->
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100"
                             style="border-top:3px solid <?= $p->total_margin_terkait >= 0 ? '#1cc88a' : '#e74a3b' ?> !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1" style="font-size:.65rem;letter-spacing:.05em">Margin Terkait</div>
                                <div class="fw-bold <?= $p->total_margin_terkait >= 0 ? 'text-success' : 'text-danger' ?>" style="font-size:.95rem">
                                    Rp <?= number_format($p->total_margin_terkait, 0, ',', '.') ?>
                                </div>
                                <div class="text-muted" style="font-size:.65rem">dari shipment yang pakai vendor ini</div>
                            </div>
                        </div>
                    </div>
                    <!-- Unfulfill -->
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100"
                             style="border-top:3px solid <?= $p->unfulfill_pct >= 10 ? '#e74a3b' : '#adb5bd' ?> !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1" style="font-size:.65rem;letter-spacing:.05em">Unfulfill Rate</div>
                                <div class="fw-bold h4 mb-0 <?= $p->unfulfill_pct >= 10 ? 'text-danger' : '' ?>">
                                    <?= $p->unfulfill_pct ?>%
                                </div>
                                <div class="text-muted" style="font-size:.7rem"><?= $p->total_unfulfill ?> trip</div>
                            </div>
                        </div>
                    </div>
                    <!-- Reliability Score -->
                    <div class="col-xl-3 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100" style="border-top:3px solid <?= $rc['color'] ?> !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1" style="font-size:.65rem;letter-spacing:.05em">Reliability Score</div>
                                <div class="fw-bold h4 mb-0" style="color:<?= $rc['color'] ?>">
                                    <?= $p->reliability_score ?> <span class="fs-6 fw-normal text-muted">/ 100</span>
                                </div>
                                <div class="progress mt-1" style="height:5px">
                                    <div class="progress-bar" style="width:<?= min($p->reliability_score, 100) ?>%;background:<?= $rc['color'] ?>"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Avg margin/trip -->
                    <div class="col-xl-1 col-md-4 col-6 d-none d-xl-block"></div>
                </div>

                <!-- Row 1: Trend Chart -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">
                                    <i class="fas fa-chart-line me-2"></i> Trend Trip & Unfulfill per Bulan
                                </h6>
                                <div class="btn-group btn-group-sm">
                                    <input type="radio" class="btn-check" name="trendMode" id="trendTrip" checked>
                                    <label class="btn btn-outline-primary btn-sm" for="trendTrip">Total Trip</label>
                                    <input type="radio" class="btn-check" name="trendMode" id="trendUnfulfillPct">
                                    <label class="btn btn-outline-danger btn-sm" for="trendUnfulfillPct">Unfulfill %</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="chartTrend" height="90"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Customer Breakdown + Rute Breakdown -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="card shadow h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-success">
                                    <i class="fas fa-users me-2"></i> Customer yang Dilayani
                                    <span class="badge bg-secondary ms-2"><?= count($customer_breakdown) ?></span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-success sticky-top">
                                            <tr>
                                                <th>Customer</th>
                                                <th class="text-center">Trip</th>
                                                <th class="text-end">Cost</th>
                                                <th class="text-center">Unfulfill</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($customer_breakdown as $c): ?>
                                                <tr>
                                                    <td class="small fw-semibold"><?= htmlspecialchars($c->customer) ?></td>
                                                    <td class="text-center fw-bold"><?= $c->total_trip ?></td>
                                                    <td class="text-end small">Rp <?= number_format($c->total_cost, 0, ',', '.') ?></td>
                                                    <td class="text-center">
                                                        <?php if ($c->total_unfulfill > 0): ?>
                                                            <span class="badge bg-danger"><?= $c->total_unfulfill ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($customer_breakdown)): ?>
                                                <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada data</td></tr>
                                            <?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-info">
                                    <i class="fas fa-route me-2"></i> Rute yang Dilayani
                                    <span class="badge bg-secondary ms-2"><?= count($rute_breakdown) ?></span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-info sticky-top">
                                            <tr>
                                                <th>Origin</th>
                                                <th>Dest</th>
                                                <th class="text-center">Trip</th>
                                                <th class="text-center">Unfulfill</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rute_breakdown as $r): ?>
                                                <tr>
                                                    <td class="small"><?= htmlspecialchars($r->origin) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r->dest_1) ?></td>
                                                    <td class="text-center fw-bold"><?= $r->total_trip ?></td>
                                                    <td class="text-center">
                                                        <?php if ($r->total_unfulfill > 0): ?>
                                                            <span class="badge bg-danger"><?= $r->total_unfulfill ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($rute_breakdown)): ?>
                                                <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada data</td></tr>
                                            <?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Trip History -->
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="m-0 fw-bold">
                            <i class="fas fa-list me-2 text-info"></i> Riwayat Trip
                        </h6>
                        <form method="GET" action="<?= base_url('vendor_performance/detail/' . rawurlencode($vendor)) ?>"
                              class="d-flex gap-2 align-items-center flex-wrap">
                            <?php if (!empty($sheet_type)): ?>
                                <input type="hidden" name="sheet_type" value="<?= htmlspecialchars($sheet_type) ?>">
                            <?php endif ?>
                            <input type="date" name="date_from" class="form-control form-control-sm" style="width:140px" value="<?= $date_from ?>">
                            <span class="text-muted small">s/d</span>
                            <input type="date" name="date_to" class="form-control form-control-sm" style="width:140px" value="<?= $date_to ?>">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <?php if ($date_from || $date_to): ?>
                                <a href="<?= base_url('vendor_performance/detail/' . rawurlencode($vendor) . (!empty($sheet_type) ? '?sheet_type=' . urlencode($sheet_type) : '')) ?>"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif ?>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Sheet</th>
                                        <th>Customer</th>
                                        <th>Origin → Dest</th>
                                        <th>Nopol</th>
                                        <th>Driver</th>
                                        <th>Status</th>
                                        <th class="text-end">Cost</th>
                                        <th class="text-end">Margin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trips as $t): ?>
                                        <?php
                                        $status_lc = strtolower($t->status ?? '');
                                        $is_unfulfill = strpos($status_lc, 'unfulfill') !== false
                                            || strpos($status_lc, 'cancel') !== false
                                            || strpos($status_lc, 'not support') !== false
                                            || strpos($status_lc, 'standby') !== false
                                            || $status_lc === 'belum jalan'
                                            || $status_lc === 'off';
                                        ?>
                                        <tr class="<?= $is_unfulfill ? 'table-warning' : '' ?>">
                                            <td class="small"><?= $t->start_date ? date('d/m/Y', strtotime($t->start_date)) : '—' ?></td>
                                            <td><span class="badge bg-secondary" style="font-size:.6rem"><?= $t->sheet_type ?></span></td>
                                            <td class="small"><?= htmlspecialchars($t->customer ?: '—') ?></td>
                                            <td class="small">
                                                <?= htmlspecialchars($t->origin) ?>
                                                <?php if ($t->dest_1): ?>
                                                    <i class="fas fa-arrow-right text-muted mx-1" style="font-size:.6rem"></i>
                                                    <?= htmlspecialchars($t->dest_1) ?>
                                                <?php endif ?>
                                            </td>
                                            <td class="small text-muted"><?= htmlspecialchars($t->nopol ?: '—') ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($t->driver ?: '—') ?></td>
                                            <td>
                                                <span class="badge <?= $is_unfulfill ? 'bg-warning text-dark' : 'bg-light text-dark' ?>" style="font-size:.65rem">
                                                    <?= htmlspecialchars($t->status ?: '—') ?>
                                                </span>
                                            </td>
                                            <td class="text-end small">Rp <?= number_format($t->trip_cost_to_vendor, 0, ',', '.') ?></td>
                                            <td class="text-end small fw-semibold <?= $t->margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                Rp <?= number_format($t->margin, 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                    <?php if (empty($trips)): ?>
                                        <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada trip di periode ini</td></tr>
                                    <?php endif ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const trendData = <?= json_encode(array_map(fn($r) => [
    'periode'        => $r->periode,
    'sheet'          => $r->sheet_type,
    'total_trip'     => (int)$r->total_trip,
    'unfulfill_pct'  => (float)$r->unfulfill_pct,
], $trend)) ?>;

const MONTH_ORDER = ['januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];
const SHEET_COLORS = {
    'FTL_Non_SPX':     '#4e73df',
    'Dailyrent':       '#1cc88a',
    'FTL_A1_SPX':      '#f6c23e',
    'FTL_Dedicated':   '#e74a3b',
    'FTL_COC_SPX':     '#36b9cc',
    'FTL_Reguler_SPX': '#6f42c1',
};
const FALLBACK = ['#fd7e14','#20c997','#d63384','#0dcaf0','#adb5bd'];

function monthIdx(p) {
    const i = MONTH_ORDER.indexOf((p || '').toLowerCase().trim());
    return i !== -1 ? i : 99;
}

let trendChart = null;

function buildTrendChart(mode = 'trip') {
    const canvas = document.getElementById('chartTrend');
    if (!trendData.length) {
        canvas.parentElement.innerHTML = '<div class="text-center py-4 text-muted">Belum ada data trend</div>';
        return;
    }

    const periodes = [...new Set(trendData.map(r => r.periode))].sort((a, b) => monthIdx(a) - monthIdx(b));
    const sheets   = [...new Set(trendData.map(r => r.sheet))];
    let fallbackIdx = 0;

    const datasets = sheets.map(sheet => {
        const color = SHEET_COLORS[sheet] || FALLBACK[fallbackIdx++ % FALLBACK.length];
        return {
            label: sheet,
            data: periodes.map(p => {
                const found = trendData.find(r => r.sheet === sheet && r.periode.toLowerCase().trim() === p.toLowerCase().trim());
                if (!found) return null;
                return mode === 'trip' ? found.total_trip : found.unfulfill_pct;
            }),
            borderColor: color,
            backgroundColor: color + '22',
            borderWidth: 2,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6,
            spanGaps: false,
            fill: false,
        };
    });

    if (trendChart) { trendChart.destroy(); trendChart = null; }

    trendChart = new Chart(canvas, {
        type: 'line',
        data: { labels: periodes, datasets },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + ': ' + (ctx.parsed.y ?? 0) + (mode === 'trip' ? ' trip' : '%')
                    }
                }
            },
            scales: {
                y: mode === 'trip'
                    ? { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }
                    : { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } }
            }
        }
    });
}

buildTrendChart('trip');
document.getElementById('trendTrip').addEventListener('change', () => buildTrendChart('trip'));
document.getElementById('trendUnfulfillPct').addEventListener('change', () => buildTrendChart('unfulfill_pct'));
</script>