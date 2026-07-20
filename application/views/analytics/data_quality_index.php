<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title mb-0">
                            <i class="fas fa-database text-primary me-2"></i> Data Quality Tracker
                        </h1>
                        <small class="text-muted">Trend kelengkapan data monitoring shipment — bukan snapshot, tapi
                            progress dari waktu ke waktu</small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('analytics') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chart-bar me-1"></i> Analytics Dashboard
                        </a>
                        <a href="<?= base_url('data_quality/export?' . http_build_query($filters)) ?>"
                            class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-csv me-1"></i> Export Baris Bermasalah
                        </a>
                    </div>
                </div>

                <!-- Summary Cards -->
                <?php $s = $summary;
                $score_color = $s->quality_score >= 90 ? '#1cc88a' : ($s->quality_score >= 70 ? '#f6c23e' : '#e74a3b');
                $score_text = $s->quality_score >= 90 ? 'text-success' : ($s->quality_score >= 70 ? 'text-warning' : 'text-danger');
                $score_label = ['baik' => 'Baik', 'cukup' => 'Cukup', 'buruk' => 'Buruk'][$s->quality_label] ?? '-';
                ?>
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100"
                            style="border-left:4px solid <?= $score_color ?> !important">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:50px;height:50px;background:<?= $score_color ?>22">
                                    <i class="fas fa-heartbeat fs-5" style="color:<?= $score_color ?>"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Quality Score</div>
                                    <div class="h4 mb-0 fw-bold <?= $score_text ?>"><?= $s->quality_score ?>
                                        <span class="fs-6 fw-normal">/ 100</span>
                                    </div>
                                    <span class="badge"
                                        style="background:<?= $score_color ?>;font-size:.65rem"><?= $score_label ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #6c757d !important">
                            <div class="card-body py-3">
                                <div class="text-muted small fw-semibold text-uppercase mb-1"
                                    style="font-size:.7rem;letter-spacing:.05em">Total Baris</div>
                                <div class="h4 mb-0 fw-bold"><?= number_format($s->total_rows) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #e74a3b !important">
                            <div class="card-body py-3">
                                <div class="text-muted small fw-semibold text-uppercase mb-1"
                                    style="font-size:.7rem;letter-spacing:.05em">Revenue / Margin Kosong</div>
                                <div class="h6 mb-0 fw-bold text-danger">
                                    <?= $s->pct_revenue ?>% <span class="text-muted fw-normal small">rev</span>
                                    &nbsp;·&nbsp;
                                    <?= $s->pct_margin ?>% <span class="text-muted fw-normal small">margin</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f6c23e !important">
                            <div class="card-body py-3">
                                <div class="text-muted small fw-semibold text-uppercase mb-1"
                                    style="font-size:.7rem;letter-spacing:.05em">Status / Payment Kosong</div>
                                <div class="h6 mb-0 fw-bold text-warning">
                                    <?= $s->pct_status ?>% <span class="text-muted fw-normal small">status</span>
                                    &nbsp;·&nbsp;
                                    <?= $s->pct_payment ?>% <span class="text-muted fw-normal small">payment</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body py-2">
                        <form method="GET" action="<?= base_url('data_quality') ?>" class="row g-2 align-items-end"
                            id="filterForm">
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">Sheet Type</label>
                                <select name="sheet_type" class="form-select form-select-sm" id="filterSheetType">
                                    <option value="">Semua Sheet</option>
                                    <?php foreach ($sheet_type_list as $st): ?>
                                        <option value="<?= $st->sheet_type ?>" <?= $filters['sheet_type'] == $st->sheet_type ? 'selected' : '' ?>>
                                            <?= $st->sheet_type ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">
                                    Periode
                                    <span id="periodeLoading" class="d-none">
                                        <span class="spinner-border spinner-border-sm text-primary ms-1"
                                            style="width:.6rem;height:.6rem"></span>
                                    </span>
                                </label>
                                <select name="periode" class="form-select form-select-sm" id="filterPeriode">
                                    <option value="">Semua Bulan</option>
                                    <?php foreach ($periode_list as $p): ?>
                                        <option value="<?= $p->periode ?>" <?= $filters['periode'] == $p->periode ? 'selected' : '' ?>>
                                            <?= $p->periode ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">Dari Tanggal</label>
                                <input type="date" name="date_from" class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($filters['start_date_from']) ?>">
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">Sampai Tanggal</label>
                                <input type="date" name="date_to" class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($filters['start_date_to']) ?>">
                            </div>
                            <div class="col-md-2 col-8">
                                <label class="form-label small mb-1 fw-semibold">Jenis Masalah</label>
                                <select name="issue" class="form-select form-select-sm">
                                    <option value="all" <?= $issue == 'all' ? 'selected' : '' ?>>Semua Masalah</option>
                                    <option value="revenue" <?= $issue == 'revenue' ? 'selected' : '' ?>>Revenue Kosong
                                    </option>
                                    <option value="margin" <?= $issue == 'margin' ? 'selected' : '' ?>>Margin Kosong
                                    </option>
                                    <option value="vendor" <?= $issue == 'vendor' ? 'selected' : '' ?>>Vendor Kosong
                                    </option>
                                    <option value="customer" <?= $issue == 'customer' ? 'selected' : '' ?>>Customer Kosong
                                    </option>
                                    <option value="status" <?= $issue == 'status' ? 'selected' : '' ?>>Status Kosong
                                    </option>
                                    <option value="payment" <?= $issue == 'payment' ? 'selected' : '' ?>>Payment Status
                                        Kosong</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-4">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="<?= base_url('data_quality') ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Row 1: Trend Chart + Breakdown per Sheet -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-7">
                        <div class="card shadow h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-primary">
                                    <i class="fas fa-chart-line me-2"></i> Trend Quality Score per Bulan
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartQualityTrend" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card shadow h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-info">
                                    <i class="fas fa-layer-group me-2"></i> Quality Score per Sheet Type
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Sheet</th>
                                                <th class="text-center">Baris</th>
                                                <th class="text-center">Score</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($by_sheet as $bs): ?>
                                                <?php
                                                $bcolor = $bs->quality_score >= 90 ? 'success' : ($bs->quality_score >= 70 ? 'warning' : 'danger');
                                                $blabel = ['baik' => 'Baik', 'cukup' => 'Cukup', 'buruk' => 'Buruk'][$bs->quality_label] ?? '-';
                                                ?>
                                                <tr>
                                                    <td class="small fw-semibold"><?= $bs->sheet_type ?></td>
                                                    <td class="text-center"><?= number_format($bs->total_rows) ?></td>
                                                    <td class="text-center fw-bold text-<?= $bcolor ?>">
                                                        <?= $bs->quality_score ?></td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge bg-<?= $bcolor ?> <?= $bcolor == 'warning' ? 'text-dark' : '' ?>"
                                                            style="font-size:.65rem">
                                                            <?= $blabel ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($by_sheet)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-3">Belum ada data</td>
                                                </tr>
                                            <?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Tabel Baris Bermasalah -->
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="m-0 fw-bold text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i> Baris Bermasalah
                            <span class="badge bg-secondary ms-2"><?= number_format($problem_count) ?></span>
                            <?php if ($problem_count > 100): ?>
                                <small class="text-muted fw-normal">(tampil 100 terbaru, export buat lihat semua)</small>
                            <?php endif ?>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:450px;overflow-y:auto">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>Sheet</th>
                                        <th>Periode</th>
                                        <th>Tanggal</th>
                                        <th>Customer</th>
                                        <th>Origin → Dest</th>
                                        <th>Vendor</th>
                                        <th>Status</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Margin</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($problem_rows as $r): ?>
                                        <?php
                                        $notes = [];
                                        if (empty($r->trip_cost_from_user))
                                            $notes[] = 'Revenue kosong';
                                        if (empty($r->margin))
                                            $notes[] = 'Margin kosong';
                                        if (empty($r->vendor))
                                            $notes[] = 'Vendor kosong';
                                        if (empty($r->customer))
                                            $notes[] = 'Customer kosong';
                                        if (empty($r->status))
                                            $notes[] = 'Status kosong';
                                        if (empty($r->status_payment_user))
                                            $notes[] = 'Payment kosong';
                                        ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"
                                                    style="font-size:.6rem"><?= $r->sheet_type ?></span></td>
                                            <td class="small"><?= $r->periode ?></td>
                                            <td class="small">
                                                <?= $r->start_date ? date('d/m/Y', strtotime($r->start_date)) : '—' ?></td>
                                            <td class="small <?= empty($r->customer) ? 'text-danger fw-semibold' : '' ?>">
                                                <?= htmlspecialchars($r->customer ?: '(kosong)') ?>
                                            </td>
                                            <td class="small">
                                                <?= htmlspecialchars($r->origin ?: '—') ?>
                                                <?php if ($r->dest_1): ?> <i class="fas fa-arrow-right text-muted mx-1"
                                                        style="font-size:.6rem"></i>
                                                    <?= htmlspecialchars($r->dest_1) ?>    <?php endif ?>
                                            </td>
                                            <td class="small <?= empty($r->vendor) ? 'text-danger fw-semibold' : '' ?>">
                                                <?= htmlspecialchars($r->vendor ?: '(kosong)') ?>
                                            </td>
                                            <td class="small <?= empty($r->status) ? 'text-danger fw-semibold' : '' ?>">
                                                <?= htmlspecialchars($r->status ?: '(kosong)') ?>
                                            </td>
                                            <td
                                                class="text-end small <?= empty($r->trip_cost_from_user) ? 'text-danger fw-semibold' : '' ?>">
                                                Rp <?= number_format($r->trip_cost_from_user, 0, ',', '.') ?>
                                            </td>
                                            <td
                                                class="text-end small <?= empty($r->margin) ? 'text-danger fw-semibold' : '' ?>">
                                                Rp <?= number_format($r->margin, 0, ',', '.') ?>
                                            </td>
                                            <td class="small text-muted"><?= implode(', ', $notes) ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                    <?php if (empty($problem_rows)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center py-4 text-muted">
                                                <i class="fas fa-check-circle text-success me-1"></i> Tidak ada baris
                                                bermasalah untuk filter ini!
                                            </td>
                                        </tr>
                                    <?php endif ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-3 p-3 bg-light rounded small text-muted">
                    <strong class="text-dark">Cara baca Quality Score:</strong>
                    <span class="mx-3">🟢 <strong>Baik</strong> — Score ≥ 90</span>
                    <span class="mx-3">🟡 <strong>Cukup</strong> — Score 70–89</span>
                    <span class="mx-3">🔴 <strong>Buruk</strong> — Score &lt; 70</span>
                    <br>
                    <span class="mx-3 d-inline-block mt-1">Score dihitung dari rata-rata persentase 6 field yang kosong:
                        Revenue, Margin, Vendor, Customer, Status, dan Status Payment.</span>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const trendRaw = <?= json_encode(array_map(fn($r) => [
        'periode' => $r->periode,
        'sheet_type' => $r->sheet_type,
        'score' => (float) $r->quality_score,
        'total_rows' => (int) $r->total_rows,
    ], $trend)) ?>;

    const AJAX_PERIODE_URL = '<?= base_url('data_quality/ajax_periode') ?>';
    const CURRENT_PERIODE = '<?= addslashes($filters['periode']) ?>';

    const SHEET_COLORS = {
        'FTL_Non_SPX': '#4e73df',
        'Dailyrent': '#1cc88a',
        'FTL_A1_SPX': '#f6c23e',
        'FTL_Dedicated': '#e74a3b',
        'FTL_COC_SPX': '#36b9cc',
        'FTL_Reguler_SPX': '#6f42c1',
    };
    const FALLBACK_COLORS = ['#fd7e14', '#20c997', '#d63384', '#0dcaf0', '#adb5bd'];

    const MONTH_ORDER = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
    function monthIndex(p) {
        const idx = MONTH_ORDER.indexOf((p || '').toLowerCase().trim());
        return idx !== -1 ? idx : 99;
    }

    (function buildQualityChart() {
        const canvas = document.getElementById('chartQualityTrend');
        if (!trendRaw.length) {
            canvas.parentElement.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-chart-line fa-3x mb-3 d-block opacity-25"></i><p>Belum ada data.</p></div>';
            return;
        }

        const labels = [...new Set(trendRaw.map(r => r.periode))].sort((a, b) => monthIndex(a) - monthIndex(b));
        const sheets = [...new Set(trendRaw.map(r => r.sheet_type))];
        let fallbackIdx = 0;

        const datasets = sheets.map(sheet => {
            const color = SHEET_COLORS[sheet] || FALLBACK_COLORS[fallbackIdx++ % FALLBACK_COLORS.length];
            return {
                label: sheet,
                data: labels.map(p => {
                    const found = trendRaw.find(r => r.sheet_type === sheet && r.periode.toLowerCase().trim() === p.toLowerCase().trim());
                    return found ? found.score : null;
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
            data: { labels, datasets },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + (ctx.parsed.y ?? 0) + ' / 100' } }
                },
                scales: {
                    y: { min: 0, max: 100, ticks: { stepSize: 20 }, grid: { color: 'rgba(0,0,0,0.05)' } }
                }
            }
        });
    })();

    // ── Dynamic periode dropdown ikut sheet type ──
    document.getElementById('filterSheetType').addEventListener('change', function () {
        const sheetType = this.value;
        const selPeriode = document.getElementById('filterPeriode');
        const loadingP = document.getElementById('periodeLoading');

        loadingP.classList.remove('d-none');
        selPeriode.disabled = true;

        fetch(AJAX_PERIODE_URL + '?sheet_type=' + encodeURIComponent(sheetType))
            .then(r => r.json())
            .then(data => {
                selPeriode.innerHTML = '<option value="">Semua Bulan</option>';
                (data.periode || []).forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p;
                    opt.textContent = p;
                    if (p === CURRENT_PERIODE) opt.selected = true;
                    selPeriode.appendChild(opt);
                });
            })
            .catch(err => console.error('Periode AJAX error:', err))
            .finally(() => {
                loadingP.classList.add('d-none');
                selPeriode.disabled = false;
            });
    });
</script>