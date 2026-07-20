<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title mb-0"><i class="fas fa-truck-pickup text-primary"></i> Margin Unit
                            Internal (OWN UNIT)</h1>
                        <small class="text-muted">Rekap margin per Nopol untuk unit milik internal TSC</small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('analytics') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                        </a>
                        <a href="<?= base_url('analytics/export?type=unit_internal&' . http_build_query($filters)) ?>"
                            class="btn btn-outline-success btn-sm">
                            <i class="fas fa-download me-1"></i> Export CSV
                        </a>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body py-2">
                        <form method="GET" action="<?= base_url('analytics/unit_internal') ?>"
                            class="row g-2 align-items-end" id="filterForm">
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
                                <label class="form-label small mb-1 fw-semibold">Customer</label>
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
                                    <a href="<?= base_url('analytics/unit_internal') ?>"
                                        class="btn btn-outline-secondary btn-sm">
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
                $margin_pct = $s && ($s->margin_pct ?? null) !== null ? $s->margin_pct : 0;
                ?>
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6 col-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #4e73df">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Trip</div>
                                <div class="h5 mb-0 fw-bold"><?= number_format($s->total_trip ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #6f42c1">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-uppercase mb-1" style="color:#6f42c1">Total Unit
                                    (Nopol)</div>
                                <div class="h5 mb-0 fw-bold"><?= number_format($s->total_unit ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-6">
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
                    <div class="col-xl-3 col-md-6 col-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #f6c23e">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Margin %</div>
                                <div class="h5 mb-0 fw-bold <?= $margin_pct >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $margin_pct ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($nopol_kosong)): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            Ada <strong><?= count($nopol_kosong) ?></strong> trip unit internal (OWN UNIT) yang
                            <strong>Nopol-nya masih kosong</strong> di data — kemungkinan CSV belum di-reimport setelah
                            update, atau kolom Nopol kosong di sumbernya. Trip ini tidak ikut dihitung di rekap per
                            Nopol di bawah.
                            <a href="#nopolKosongTable" class="alert-link">Lihat detail &darr;</a>
                        </div>
                    </div>
                <?php endif ?>

                <!-- Tabel Rekap per Nopol -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-id-card me-2"></i> Rekap Margin per
                            Nopol</h6>
                        <span class="badge bg-primary"><?= count($per_nopol) ?> unit</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-primary">
                                    <tr>
                                        <th>#</th>
                                        <th>Nopol</th>
                                        <th>Truck Type</th>
                                        <th class="text-center">Total Trip</th>
                                        <th class="text-center">Customer Dilayani</th>
                                        <th class="text-end">Total Revenue</th>
                                        <th class="text-end">Total Margin</th>
                                        <th class="text-end">Avg Margin/Trip</th>
                                        <th class="text-center">Margin %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($per_nopol as $i => $r): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($r->nopol) ?></td>
                                            <td>
                                                <?php if (!empty($r->truck_types)): ?>
                                                    <?php foreach (explode(', ', $r->truck_types) as $tt): ?>
                                                        <span class="badge bg-secondary"
                                                            style="font-size:.65rem"><?= htmlspecialchars(trim($tt)) ?></span>
                                                    <?php endforeach ?>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif ?>
                                            </td>
                                            <td class="text-center"><?= number_format($r->total_trip) ?></td>
                                            <td class="small" title="<?= htmlspecialchars($r->customer_dilayani) ?>">
                                                <?= $r->total_customer ?> customer
                                                <?php if (!empty($r->customer_dilayani)): ?>
                                                    <div class="text-muted" style="font-size:.7rem">
                                                        <?= htmlspecialchars(mb_strimwidth($r->customer_dilayani, 0, 40, '...')) ?>
                                                    </div>
                                                <?php endif ?>
                                            </td>
                                            <td class="text-end small">Rp
                                                <?= number_format($r->total_revenue, 0, ',', '.') ?>
                                            </td>
                                            <td
                                                class="text-end fw-semibold <?= $r->total_margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                Rp <?= number_format($r->total_margin, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-end small">Rp
                                                <?= number_format($r->avg_margin, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= $r->margin_pct >= 0 ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $r->margin_pct ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                    <?php if (empty($per_nopol)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                                Belum ada data unit internal (OWN UNIT) dengan Nopol terisi.
                                            </td>
                                        </tr>
                                    <?php endif ?>
                                </tbody>
                                <?php if (!empty($per_nopol)): ?>
                                    <tfoot>
                                        <tr class="table-light fw-bold">
                                            <td colspan="3">TOTAL</td>
                                            <td class="text-center"><?= number_format($s->total_trip ?? 0) ?></td>
                                            <td></td>
                                            <td class="text-end">Rp
                                                <?= number_format($s->total_revenue ?? 0, 0, ',', '.') ?>
                                            </td>
                                            <td
                                                class="text-end <?= ($s->total_margin ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                                Rp <?= number_format($s->total_margin ?? 0, 0, ',', '.') ?>
                                            </td>
                                            <td></td>
                                            <td class="text-center"><?= $margin_pct ?>%</td>
                                        </tr>
                                    </tfoot>
                                <?php endif ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tabel Nopol Kosong (data bolong) -->
                <?php if (!empty($nopol_kosong)): ?>
                    <div class="card shadow mb-4" id="nopolKosongTable">
                        <div class="card-header py-3 bg-warning">
                            <h6 class="m-0 fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Trip OWN UNIT
                                dengan Nopol Kosong</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:350px;overflow-y:auto">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-warning sticky-top">
                                        <tr>
                                            <th>Sheet</th>
                                            <th>Tanggal</th>
                                            <th>Customer</th>
                                            <th>Origin</th>
                                            <th>Dest</th>
                                            <th>Truck Type</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">Margin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($nopol_kosong as $r): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"
                                                        style="font-size:.65rem"><?= $r->sheet_type ?></span></td>
                                                <td class="small"><?= $r->start_date ?></td>
                                                <td class="small"><?= htmlspecialchars($r->customer) ?></td>
                                                <td class="small"><?= htmlspecialchars($r->origin) ?></td>
                                                <td class="small"><?= htmlspecialchars($r->dest_1) ?></td>
                                                <td class="small"><?= htmlspecialchars($r->truck_type) ?></td>
                                                <td class="text-end small">Rp
                                                    <?= number_format($r->trip_cost_from_user, 0, ',', '.') ?>
                                                </td>
                                                <td class="text-end small">Rp
                                                    <?= number_format($r->margin, 0, ',', '.') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

            </div>
        </div>
    </div>
</div>

<script>
    const AJAX_FILTER_URL = '<?= base_url('analytics/ajax_filter_options') ?>';
    const CURRENT_PERIODE = '<?= addslashes($filters['periode']) ?>';
    const CURRENT_CUSTOMER = '<?= addslashes($filters['customer']) ?>';

    function updateFilterDropdowns(sheetType) {
        const selPeriode = document.getElementById('filterPeriode');
        const selCustomer = document.getElementById('filterCustomer');
        const loadingP = document.getElementById('periodeLoading');

        loadingP.classList.remove('d-none');
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
                selPeriode.disabled = false;
                selCustomer.disabled = false;
            });
    }

    document.getElementById('filterSheetType').addEventListener('change', function () {
        if (this.value) {
            updateFilterDropdowns(this.value);
        } else {
            window.location.href = '<?= base_url('analytics/unit_internal') ?>';
        }
    });
</script>