<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title mb-0"><i class="fas fa-chart-bar text-primary"></i> Analytics Dashboard
                        </h1>
                        <small class="text-muted">Data monitoring shipment TSC 2026</small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('analytics/daily') ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-calendar-day me-1"></i> Daily Monitoring
                        </a>
                        <a href="<?= base_url('analytics/weekly') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-calendar-week me-1"></i> Weekly Report
                        </a>
                        <a href="<?= base_url('analytics/unit_internal') ?>" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-truck-pickup me-1"></i> Unit Internal
                        </a>

                        <a href="<?= base_url('data_quality') ?>" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-database me-1"></i> Data Quality
                        </a>

                        <?php if (in_array($level, ['superadmin', 'finance_staff'])): ?>
                            <a href="<?= base_url('analytics/import') ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-import me-1"></i> Import CSV
                            </a>
                        <?php endif ?>

                        <div class="dropdown">
                            <button class="btn btn-outline-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item"
                                        href="<?= base_url('analytics/export?type=profitability&' . http_build_query($filters)) ?>"><i
                                            class="fas fa-file-csv me-2 text-success"></i> Profitability</a></li>
                                <li><a class="dropdown-item"
                                        href="<?= base_url('analytics/export?type=rute_non_profitable&' . http_build_query($filters)) ?>"><i
                                            class="fas fa-file-csv me-2 text-danger"></i> Rute Non Profitable</a></li>
                                <li><a class="dropdown-item"
                                        href="<?= base_url('analytics/export?type=top_revenue&' . http_build_query($filters)) ?>"><i
                                            class="fas fa-file-csv me-2 text-warning"></i> Top Revenue</a></li>
                                <li><a class="dropdown-item"
                                        href="<?= base_url('analytics/export?type=top_vendor&' . http_build_query($filters)) ?>"><i
                                            class="fas fa-file-csv me-2 text-info"></i> Top Vendor</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body py-2">
                        <form method="GET" action="<?= base_url('analytics') ?>" class="row g-2 align-items-end"
                            id="filterForm">
                            <div class="col-md-3 col-6">
                                <label class="form-label small mb-1 fw-semibold">Sheet Type</label>
                                <select name="sheet_type" class="form-select form-select-sm" id="filterSheetType">
                                    <option value="">Semua Sheet</option>
                                    <?php foreach ($sheet_type_list as $s): ?>
                                        <option value="<?= $s->sheet_type ?>" <?= $filters['sheet_type'] == $s->sheet_type ? 'selected' : '' ?>>
                                            <?= $s->sheet_type ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label small mb-1 fw-semibold">
                                    Periode
                                    <span id="periodeLoading" class="d-none">
                                        <span class="spinner-border spinner-border-sm text-primary ms-1"
                                            style="width:.7rem;height:.7rem"></span>
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
                            <div class="col-md-3 col-6">
                                <label class="form-label small mb-1 fw-semibold">
                                    Customer
                                    <span id="customerLoading" class="d-none">
                                        <span class="spinner-border spinner-border-sm text-primary ms-1"
                                            style="width:.7rem;height:.7rem"></span>
                                    </span>
                                </label>
                                <select name="customer" class="form-select form-select-sm" id="filterCustomer">
                                    <option value="">Semua Customer</option>
                                    <?php foreach ($customer_list as $c): ?>
                                        <option value="<?= htmlspecialchars($c->customer) ?>"
                                            <?= $filters['customer'] == $c->customer ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c->customer) ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="<?= base_url('analytics') ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <?php
                $s = $summary;
                $margin_pct = $s && $s->total_revenue > 0
                    ? round($s->total_margin / $s->total_revenue * 100, 1)
                    : 0;
                ?>
                <div class="row g-3 mb-4">
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #4e73df">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Shipment</div>
                                <div class="h5 mb-0 fw-bold"><?= number_format($s->total_shipment ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #1cc88a">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Revenue</div>
                                <div class="h6 mb-0 fw-bold">Rp
                                    <?= number_format($s->total_revenue ?? 0, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow h-100 py-2"
                            style="border-left:4px solid <?= ($s->total_margin ?? 0) >= 0 ? '#1cc88a' : '#e74a3b' ?>">
                            <div class="card-body py-2 px-3">
                                <div
                                    class="text-xs fw-bold <?= ($s->total_margin ?? 0) >= 0 ? 'text-success' : 'text-danger' ?> text-uppercase mb-1">
                                    Total Margin</div>
                                <div
                                    class="h6 mb-0 fw-bold <?= ($s->total_margin ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                    Rp <?= number_format($s->total_margin ?? 0, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #f6c23e">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Margin %</div>
                                <div class="h5 mb-0 fw-bold <?= $margin_pct >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $margin_pct ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #36b9cc">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">Total Customer</div>
                                <div class="h5 mb-0 fw-bold"><?= number_format($s->total_customer ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #e74a3b">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Unfulfill</div>
                                <div class="h5 mb-0 fw-bold text-danger"><?= number_format($s->total_unfulfill ?? 0) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 1: Chart Margin Trend + Top 5 Revenue -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-7">
                        <div class="card shadow h-100">
                            <div
                                class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i> Trend Margin
                                    per Bulan</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted">Tampilan:</small>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="chartMode" id="modeSheet"
                                            autocomplete="off" checked>
                                        <label class="btn btn-outline-primary btn-sm" for="modeSheet">Per Sheet</label>
                                        <input type="radio" class="btn-check" name="chartMode" id="modeTotal"
                                            autocomplete="off">
                                        <label class="btn btn-outline-secondary btn-sm" for="modeTotal">Total</label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="chartMarginTrend" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card shadow h-100">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-warning"><i class="fas fa-trophy me-2"></i> Top 5 Customer
                                    Revenue</h6>
                                <a href="<?= base_url('analytics/export?type=top_revenue&' . http_build_query($filters)) ?>"
                                    class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-warning">
                                            <tr>
                                                <th>#</th>
                                                <th>Customer</th>
                                                <th class="text-end">Revenue</th>
                                                <th class="text-end">Margin</th>
                                                <th class="text-center">Margin%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($top_revenue as $i => $r): ?>
                                                <tr>
                                                    <td><span class="badge bg-warning text-dark"><?= $i + 1 ?></span></td>
                                                    <td class="fw-semibold"><?= htmlspecialchars($r->customer) ?></td>
                                                    <td class="text-end small">Rp
                                                        <?= number_format($r->total_revenue, 0, ',', '.') ?>
                                                    </td>
                                                    <td
                                                        class="text-end small <?= $r->total_margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        Rp <?= number_format($r->total_margin, 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge <?= $r->margin_pct >= 0 ? 'bg-success' : 'bg-danger' ?>">
                                                            <?= $r->margin_pct ?>%
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($top_revenue)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-3">Belum ada data</td>
                                                </tr>
                                            <?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Profitability per Customer + Top 5 Vendor -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-7">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-success"><i class="fas fa-users me-2"></i> Profitability per
                                    Customer</h6>
                                <a href="<?= base_url('analytics/export?type=profitability&' . http_build_query($filters)) ?>"
                                    class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:350px; overflow-y:auto">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-success sticky-top">
                                            <tr>
                                                <th>Customer</th>
                                                <th>Sheet</th>
                                                <th class="text-center">Shipment</th>
                                                <th class="text-end">Revenue</th>
                                                <th class="text-end">Margin</th>
                                                <th class="text-center">Margin%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($profitability as $r): ?>
                                                <tr>
                                                    <td class="fw-semibold"><?= htmlspecialchars($r->customer) ?></td>
                                                    <td><span class="badge bg-secondary"
                                                            style="font-size:.65rem"><?= $r->sheet_type ?></span></td>
                                                    <td class="text-center"><?= number_format($r->total_shipment) ?></td>
                                                    <td class="text-end small">Rp
                                                        <?= number_format($r->total_revenue, 0, ',', '.') ?>
                                                    </td>
                                                    <td
                                                        class="text-end small fw-semibold <?= $r->total_margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        Rp <?= number_format($r->total_margin, 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge <?= $r->margin_pct >= 0 ? 'bg-success' : 'bg-danger' ?>">
                                                            <?= $r->margin_pct ?>%
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($profitability)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-3">Belum ada data</td>
                                                </tr>
                                            <?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-info"><i class="fas fa-truck me-2"></i> Top 5 Vendor Support
                                </h6>
                                <a href="<?= base_url('analytics/export?type=top_vendor&' . http_build_query($filters)) ?>"
                                    class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i></a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-info">
                                            <tr>
                                                <th>#</th>
                                                <th>Vendor</th>
                                                <th class="text-center">Trip</th>
                                                <th class="text-center">Cust</th>
                                                <th class="text-center">Rute</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($top_vendor as $i => $v): ?>
                                                <tr>
                                                    <td><span class="badge bg-info text-dark"><?= $i + 1 ?></span></td>
                                                    <td class="fw-semibold"><?= htmlspecialchars($v->vendor) ?></td>
                                                    <td class="text-center fw-bold"><?= number_format($v->total_trip) ?>
                                                    </td>
                                                    <td class="text-center"><?= $v->total_customer_dilayani ?></td>
                                                    <td class="text-center"><?= $v->total_rute ?></td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($top_vendor)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-3">Belum ada data</td>
                                                </tr>
                                            <?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Avg Shipment -->
                        <div class="card shadow mt-3">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold" style="color:#6f42c1"><i class="fas fa-calendar-check me-2"></i>
                                    Rata-rata Shipment/Bulan/Customer</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:200px;overflow-y:auto">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="sticky-top" style="background:#f3eeff">
                                            <tr>
                                                <th>Customer</th>
                                                <th class="text-center">Avg/Bulan</th>
                                                <th class="text-center">Total</th>
                                                <th class="text-center">Bulan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($avg_shipment as $r): ?>
                                                <tr>
                                                    <td class="fw-semibold small"><?= htmlspecialchars($r->customer) ?></td>
                                                    <td class="text-center fw-bold"><?= $r->avg_shipment ?></td>
                                                    <td class="text-center"><?= $r->total_shipment ?></td>
                                                    <td class="text-center text-muted"><?= $r->total_bulan ?></td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($avg_shipment)): ?>
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

                <!-- Row 3: Rute Non Profitable + Rute Unfulfill -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="card shadow">
                            <div
                                class="card-header py-3 d-flex justify-content-between align-items-center bg-danger text-white">
                                <h6 class="m-0 fw-bold"><i class="fas fa-route me-2"></i> Rute Non Profitable</h6>
                                <a href="<?= base_url('analytics/export?type=rute_non_profitable&' . http_build_query($filters)) ?>"
                                    class="btn btn-sm btn-light"><i class="fas fa-download"></i></a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-danger sticky-top">
                                            <tr>
                                                <th>Origin</th>
                                                <th>Dest</th>
                                                <th class="text-center">Trip</th>
                                                <th class="text-end">Avg Margin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rute_non_profitable as $r): ?>
                                                <tr>
                                                    <td class="small"><?= htmlspecialchars($r->origin) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r->dest_1) ?></td>
                                                    <td class="text-center"><?= $r->total_trip ?></td>
                                                    <td class="text-end text-danger fw-semibold">
                                                        Rp <?= number_format($r->avg_margin, 0, ',', '.') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($rute_non_profitable)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-3">
                                                        <i class="fas fa-check-circle text-success me-1"></i> Semua rute
                                                        profitable!
                                                    </td>
                                                </tr>
                                            <?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow">
                            <div class="card-header py-3 bg-warning">
                                <h6 class="m-0 fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Rute Sering
                                    Unfulfill</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-warning sticky-top">
                                            <tr>
                                                <th>Origin</th>
                                                <th>Dest</th>
                                                <th class="text-center">Unfulfill</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rute_unfulfill as $r): ?>
                                                <tr>
                                                    <td class="small"><?= htmlspecialchars($r->origin) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r->dest_1) ?></td>
                                                    <td class="text-center fw-bold text-danger"><?= $r->total_unfulfill ?>
                                                    </td>
                                                    <td class="small text-muted"><?= htmlspecialchars($r->statuses) ?></td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($rute_unfulfill)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-3">
                                                        <i class="fas fa-check-circle text-success me-1"></i> Tidak ada
                                                        unfulfill!
                                                    </td>
                                                </tr>
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // ══════════════════════════════════════════════════════
    // DATA dari PHP
    // ══════════════════════════════════════════════════════
    const trendRaw = <?= json_encode(array_map(fn($r) => [
        'periode' => $r->periode,
        'sheet_type' => $r->sheet_type,
        'margin' => (float) $r->total_margin,
        'revenue' => (float) $r->total_revenue,
    ], $margin_trend)) ?>;

    const AJAX_FILTER_URL = '<?= base_url('analytics/ajax_filter_options') ?>';
    const CURRENT_PERIODE = '<?= addslashes($filters['periode']) ?>';
    const CURRENT_CUSTOMER = '<?= addslashes($filters['customer']) ?>';

    // ── Warna per sheet type ──
    const SHEET_COLORS = {
        'FTL_Non_SPX': '#4e73df',
        'Dailyrent': '#1cc88a',
        'FTL_A1_SPX': '#f6c23e',
        'FTL_Dedicated': '#e74a3b',
        'FTL_COC_SPX': '#36b9cc',
        'FTL_Reguler_SPX': '#6f42c1',
    };
    const FALLBACK_COLORS = ['#fd7e14', '#20c997', '#d63384', '#0dcaf0', '#adb5bd'];

    // ── Urutan bulan ──
    const MONTH_ORDER = [
        'januari', 'februari', 'maret', 'april', 'mei', 'juni',
        'juli', 'agustus', 'september', 'oktober', 'november', 'desember',
    ];

    function monthIndex(p) {
        const idx = MONTH_ORDER.indexOf(p.toLowerCase().trim());
        return idx !== -1 ? idx : 99;
    }

    // ══════════════════════════════════════════════════════
    // CHART
    // ══════════════════════════════════════════════════════
    let chartInstance = null;

    function buildChart(mode) {
        const canvas = document.getElementById('chartMarginTrend');
        const labelSet = [...new Set(trendRaw.map(r => r.periode))];
        const labels = labelSet.sort((a, b) => monthIndex(a) - monthIndex(b));
        let datasets = [];

        if (mode === 'sheet') {
            const sheets = [...new Set(trendRaw.map(r => r.sheet_type))];
            let fallbackIdx = 0;
            datasets = sheets.map(sheet => {
                const color = SHEET_COLORS[sheet] || FALLBACK_COLORS[fallbackIdx++ % FALLBACK_COLORS.length];
                return {
                    label: sheet,
                    data: labels.map(p => {
                        const found = trendRaw.find(r =>
                            r.sheet_type === sheet &&
                            r.periode.toLowerCase().trim() === p.toLowerCase().trim()
                        );
                        return found ? found.margin : null;
                    }),
                    backgroundColor: color + '22',
                    borderColor: color,
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    spanGaps: false,
                    type: 'line',
                    fill: false,
                };
            });
        } else {
            const totalByPeriode = {};
            trendRaw.forEach(r => {
                const key = r.periode.trim();
                if (!totalByPeriode[key]) totalByPeriode[key] = { margin: 0, revenue: 0 };
                totalByPeriode[key].margin += r.margin;
                totalByPeriode[key].revenue += r.revenue;
            });
            datasets = [
                {
                    label: 'Revenue',
                    data: labels.map(p => totalByPeriode[p]?.revenue || 0),
                    backgroundColor: 'rgba(78,115,223,0.15)',
                    borderColor: '#4e73df',
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 4,
                    type: 'line',
                    yAxisID: 'y1',
                    fill: true,
                },
                {
                    label: 'Margin',
                    data: labels.map(p => totalByPeriode[p]?.margin || 0),
                    backgroundColor: labels.map(p => (totalByPeriode[p]?.margin || 0) >= 0
                        ? 'rgba(28,200,138,0.7)'
                        : 'rgba(231,74,59,0.7)'),
                    borderColor: labels.map(p => (totalByPeriode[p]?.margin || 0) >= 0
                        ? '#1cc88a'
                        : '#e74a3b'),
                    borderWidth: 1,
                    type: 'bar',
                    yAxisID: 'y',
                },
            ];
        }

        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        if (trendRaw.length === 0) {
            canvas.parentElement.innerHTML =
                '<div class="text-center py-5 text-muted">' +
                '<i class="fas fa-chart-line fa-3x mb-3 d-block opacity-25"></i>' +
                '<p>Belum ada data. <a href="<?= base_url('analytics/import') ?>">Import CSV dulu</a></p></div>';
            return;
        }

        const scalesConfig = mode === 'sheet'
            ? {
                y: {
                    ticks: { callback: v => 'Rp ' + (v / 1e6).toFixed(0) + 'jt' },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                }
            }
            : {
                y: {
                    type: 'linear',
                    position: 'left',
                    ticks: { callback: v => 'Rp ' + (v / 1e6).toFixed(0) + 'jt' },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { callback: v => 'Rp ' + (v / 1e6).toFixed(0) + 'jt' }
                },
            };

        chartInstance = new Chart(canvas, {
            type: 'bar',
            data: { labels, datasets },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': Rp ' + (ctx.parsed.y ?? 0).toLocaleString('id-ID')
                        }
                    }
                },
                scales: scalesConfig,
            }
        });
    }

    buildChart('sheet');
    document.getElementById('modeSheet').addEventListener('change', () => buildChart('sheet'));
    document.getElementById('modeTotal').addEventListener('change', () => buildChart('total'));

    // ══════════════════════════════════════════════════════
    // DYNAMIC FILTER — Periode & Customer ikut Sheet Type
    // ══════════════════════════════════════════════════════
    function updateFilterDropdowns(sheetType) {
        const selPeriode = document.getElementById('filterPeriode');
        const selCustomer = document.getElementById('filterCustomer');
        const loadingP = document.getElementById('periodeLoading');
        const loadingC = document.getElementById('customerLoading');

        loadingP.classList.remove('d-none');
        loadingC.classList.remove('d-none');
        selPeriode.disabled = true;
        selCustomer.disabled = true;

        fetch(AJAX_FILTER_URL + '?sheet_type=' + encodeURIComponent(sheetType))
            .then(r => r.json())
            .then(data => {
                selPeriode.innerHTML = '<option value="">Semua Bulan</option>';
                data.periode.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p;
                    opt.textContent = p;
                    if (p === CURRENT_PERIODE) opt.selected = true;
                    selPeriode.appendChild(opt);
                });

                selCustomer.innerHTML = '<option value="">Semua Customer</option>';
                data.customer.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c;
                    opt.textContent = c;
                    if (c === CURRENT_CUSTOMER) opt.selected = true;
                    selCustomer.appendChild(opt);
                });
            })
            .catch(err => console.error('Filter AJAX error:', err))
            .finally(() => {
                loadingP.classList.add('d-none');
                loadingC.classList.add('d-none');
                selPeriode.disabled = false;
                selCustomer.disabled = false;
            });
    }

    // ── Event listener saat sheet type diganti ──
    document.getElementById('filterSheetType').addEventListener('change', function () {
        if (this.value) {
            updateFilterDropdowns(this.value);
        } else {
            window.location.href = '<?= base_url('analytics') ?>';
        }
    });

    // IIFE auto-trigger DIHAPUS — PHP render sudah handle initial state
</script>