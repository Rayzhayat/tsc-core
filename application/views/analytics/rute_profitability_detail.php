<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$r = $rute;
$status_cfg = [
    'profitable' => ['color' => '#1cc88a', 'badge' => 'bg-success',  'icon' => 'check-circle',  'label' => 'Profitable'],
    'tipis'      => ['color' => '#f6c23e', 'badge' => 'bg-warning text-dark', 'icon' => 'exclamation-circle', 'label' => 'Margin Tipis'],
    'rugi'       => ['color' => '#e74a3b', 'badge' => 'bg-danger',   'icon' => 'times-circle',  'label' => 'Rugi'],
];
$sc = $status_cfg[$r->status_rute] ?? $status_cfg['rugi'];

function rute_detail_url($origin, $dest_1, $sheet_type = '') {
    $url = base_url('rute_profitability/detail/' . rawurlencode($origin) . '/' . rawurlencode($dest_1));
    if (!empty($sheet_type)) $url .= '?sheet_type=' . urlencode($sheet_type);
    return $url;
}
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
                                <li class="breadcrumb-item"><a href="<?= base_url('rute_profitability') ?>">Rute Profitability</a></li>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($origin) ?> → <?= htmlspecialchars($dest_1) ?></li>
                            </ol>
                        </nav>
                        <h1 class="page-title mb-0 d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge <?= $sc['badge'] ?> fs-6">
                                <i class="fas fa-<?= $sc['icon'] ?> me-1"></i> <?= $sc['label'] ?>
                            </span>
                            <?= htmlspecialchars($origin) ?>
                            <i class="fas fa-arrow-right text-muted mx-1" style="font-size:.8em"></i>
                            <?= htmlspecialchars($dest_1) ?>
                        </h1>
                        <?php if ($r->first_trip && $r->last_trip): ?>
                            <small class="text-muted">
                                Data: <?= date('d M Y', strtotime($r->first_trip)) ?> –
                                      <?= date('d M Y', strtotime($r->last_trip)) ?>
                            </small>
                        <?php endif ?>
                    </div>

                    <!-- Sheet Type Filter -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?php foreach ($sheet_types as $st): ?>
                            <a href="<?= rute_detail_url($origin, $dest_1, $st->sheet_type) ?>"
                               class="btn btn-sm <?= $sheet_type == $st->sheet_type ? 'btn-primary' : 'btn-outline-secondary' ?>">
                                <?= $st->sheet_type ?>
                            </a>
                        <?php endforeach ?>
                        <?php if (!empty($sheet_type)): ?>
                            <a href="<?= rute_detail_url($origin, $dest_1) ?>" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times"></i> Semua Sheet
                            </a>
                        <?php endif ?>
                    </div>
                </div>

                <!-- Scorecard Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100" style="border-top:3px solid #4e73df !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1" style="font-size:.65rem;letter-spacing:.05em">Total Revenue</div>
                                <div class="fw-bold" style="font-size:.95rem">Rp <?= number_format($r->total_revenue, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100"
                             style="border-top:3px solid <?= $r->total_margin >= 0 ? '#1cc88a' : '#e74a3b' ?> !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1" style="font-size:.65rem;letter-spacing:.05em">Total Margin</div>
                                <div class="fw-bold <?= $r->total_margin >= 0 ? 'text-success' : 'text-danger' ?>" style="font-size:.95rem">
                                    Rp <?= number_format($r->total_margin, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100"
                             style="border-top:3px solid <?= $r->margin_pct > 10 ? '#1cc88a' : ($r->margin_pct >= 0 ? '#f6c23e' : '#e74a3b') ?> !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1" style="font-size:.65rem;letter-spacing:.05em">Margin %</div>
                                <div class="fw-bold h4 mb-0 <?= $r->margin_pct > 10 ? 'text-success' : ($r->margin_pct >= 0 ? 'text-warning' : 'text-danger') ?>">
                                    <?= $r->margin_pct ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100" style="border-top:3px solid #36b9cc !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1" style="font-size:.65rem;letter-spacing:.05em">Total Trip</div>
                                <div class="fw-bold h4 mb-0"><?= number_format($r->total_trip) ?></div>
                                <div class="text-muted" style="font-size:.7rem"><?= $r->total_vendor ?> vendor · <?= $r->total_customer ?> customer</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100" style="border-top:3px solid #6f42c1 !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1" style="font-size:.65rem;letter-spacing:.05em">Avg Margin/Trip</div>
                                <div class="fw-bold h4 mb-0 <?= $r->avg_margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                    Rp <?= number_format($r->avg_margin / 1000, 0) ?>rb
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100"
                             style="border-top:3px solid <?= $r->unfulfill_pct >= 10 ? '#e74a3b' : '#adb5bd' ?> !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1" style="font-size:.65rem;letter-spacing:.05em">Unfulfill Rate</div>
                                <div class="fw-bold h4 mb-0 <?= $r->unfulfill_pct >= 10 ? 'text-danger' : '' ?>">
                                    <?= $r->unfulfill_pct ?>%
                                </div>
                                <div class="text-muted" style="font-size:.7rem"><?= $r->total_unfulfill ?> trip</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 1: Trend Chart -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-primary">
                                    <i class="fas fa-chart-line me-2"></i> Trend Margin per Bulan di Rute Ini
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartTrend" height="90"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Vendor Breakdown (KUNCI ANALISIS) -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 fw-bold">
                            <i class="fas fa-truck me-2"></i> Breakdown per Vendor — Siapa yang Bikin Untung/Rugi
                            <span class="badge bg-light text-dark ms-2"><?= count($vendor_breakdown) ?></span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Vendor</th>
                                        <th class="text-center">Trip</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Cost</th>
                                        <th class="text-end">Margin</th>
                                        <th class="text-end">Avg Margin</th>
                                        <th class="text-center">Margin%</th>
                                        <th class="text-center">Unfulfill</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vendor_breakdown as $v): ?>
                                        <tr class="<?= $v->total_margin < 0 ? 'table-danger' : ($v->margin_pct > 10 ? 'table-success' : '') ?>">
                                            <td class="small fw-semibold"><?= htmlspecialchars($v->vendor ?: '(kosong)') ?></td>
                                            <td class="text-center fw-bold"><?= $v->total_trip ?></td>
                                            <td class="text-end small">Rp <?= number_format($v->total_revenue, 0, ',', '.') ?></td>
                                            <td class="text-end small">Rp <?= number_format($v->total_cost, 0, ',', '.') ?></td>
                                            <td class="text-end small fw-semibold <?= $v->total_margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                Rp <?= number_format($v->total_margin, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-end small <?= $v->avg_margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                Rp <?= number_format($v->avg_margin, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= $v->margin_pct > 10 ? 'bg-success' : ($v->margin_pct >= 0 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                                    <?= $v->margin_pct ?>%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($v->total_unfulfill > 0): ?>
                                                    <span class="badge bg-danger"><?= $v->total_unfulfill ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif ?>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                    <?php if (empty($vendor_breakdown)): ?>
                                        <tr><td colspan="8" class="text-center py-3 text-muted">Belum ada data vendor</td></tr>
                                    <?php endif ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($vendor_breakdown) > 1): ?>
                            <div class="p-2 bg-light small text-muted border-top">
                                <i class="fas fa-lightbulb text-warning me-1"></i>
                                Baris hijau = vendor paling untung di rute ini, baris merah = vendor yang bikin rugi.
                                Kalau ada selisih jauh, pertimbangkan geser volume ke vendor yang lebih murah/efisien.
                            </div>
                        <?php endif ?>
                    </div>
                </div>

                <!-- Row 3: Customer Breakdown + Truck Type Breakdown -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="card shadow h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-success">
                                    <i class="fas fa-users me-2"></i> Breakdown per Customer
                                    <span class="badge bg-secondary ms-2"><?= count($customer_breakdown) ?></span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:280px;overflow-y:auto">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-success sticky-top">
                                            <tr>
                                                <th>Customer</th>
                                                <th class="text-center">Trip</th>
                                                <th class="text-end">Margin</th>
                                                <th class="text-center">Margin%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($customer_breakdown as $c): ?>
                                                <tr>
                                                    <td class="small fw-semibold"><?= htmlspecialchars($c->customer) ?></td>
                                                    <td class="text-center fw-bold"><?= $c->total_trip ?></td>
                                                    <td class="text-end small fw-semibold <?= $c->total_margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        Rp <?= number_format($c->total_margin, 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge <?= $c->margin_pct > 10 ? 'bg-success' : ($c->margin_pct >= 0 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                                            <?= $c->margin_pct ?>%
                                                        </span>
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
                                <h6 class="m-0 fw-bold" style="color:#6f42c1">
                                    <i class="fas fa-truck-pickup me-2"></i> Breakdown per Truck Type
                                    <span class="badge bg-secondary ms-2"><?= count($truck_breakdown) ?></span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:280px;overflow-y:auto">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="sticky-top" style="background:#f3eeff">
                                            <tr>
                                                <th>Truck Type</th>
                                                <th class="text-center">Trip</th>
                                                <th class="text-end">Avg Margin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($truck_breakdown as $t): ?>
                                                <tr>
                                                    <td class="small fw-semibold"><?= htmlspecialchars($t->truck_type) ?></td>
                                                    <td class="text-center fw-bold"><?= $t->total_trip ?></td>
                                                    <td class="text-end small fw-semibold <?= $t->avg_margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        Rp <?= number_format($t->avg_margin, 0, ',', '.') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($truck_breakdown)): ?>
                                                <tr><td colspan="3" class="text-center py-3 text-muted">Belum ada data</td></tr>
                                            <?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Trip History -->
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="m-0 fw-bold">
                            <i class="fas fa-list me-2 text-info"></i> Riwayat Trip di Rute Ini
                        </h6>
                        <form method="GET" action="<?= rute_detail_url($origin, $dest_1) ?>"
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
                                <a href="<?= rute_detail_url($origin, $dest_1, $sheet_type) ?>" class="btn btn-sm btn-outline-secondary">
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
                                        <th>Vendor</th>
                                        <th>Truck</th>
                                        <th>Nopol</th>
                                        <th>Status</th>
                                        <th class="text-end">Revenue</th>
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
                                        <tr class="<?= $is_unfulfill ? 'table-warning' : ($t->margin < 0 ? 'table-danger' : '') ?>">
                                            <td class="small"><?= $t->start_date ? date('d/m/Y', strtotime($t->start_date)) : '—' ?></td>
                                            <td><span class="badge bg-secondary" style="font-size:.6rem"><?= $t->sheet_type ?></span></td>
                                            <td class="small"><?= htmlspecialchars($t->customer ?: '—') ?></td>
                                            <td class="small"><?= htmlspecialchars($t->vendor ?: '—') ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($t->truck_type ?: '—') ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($t->nopol ?: '—') ?></td>
                                            <td>
                                                <span class="badge <?= $is_unfulfill ? 'bg-warning text-dark' : 'bg-light text-dark' ?>" style="font-size:.65rem">
                                                    <?= htmlspecialchars($t->status ?: '—') ?>
                                                </span>
                                            </td>
                                            <td class="text-end small">Rp <?= number_format($t->trip_cost_from_user, 0, ',', '.') ?></td>
                                            <td class="text-end small">Rp <?= number_format($t->trip_cost_to_vendor, 0, ',', '.') ?></td>
                                            <td class="text-end small fw-semibold <?= $t->margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                Rp <?= number_format($t->margin, 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                    <?php if (empty($trips)): ?>
                                        <tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada trip di periode ini</td></tr>
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
    'periode'    => $r->periode,
    'sheet'      => $r->sheet_type,
    'margin'     => (float)$r->total_margin,
    'revenue'    => (float)$r->total_revenue,
    'margin_pct' => (float)$r->margin_pct,
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

(function buildTrendChart() {
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
                return found ? found.margin : null;
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

    new Chart(canvas, {
        type: 'line',
        data: { labels: periodes, datasets },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + (ctx.parsed.y ?? 0).toLocaleString('id-ID') } }
            },
            scales: {
                y: { ticks: { callback: v => 'Rp ' + (v / 1e6).toFixed(1) + 'jt' }, grid: { color: 'rgba(0,0,0,0.05)' } }
            }
        }
    });
})();
</script>