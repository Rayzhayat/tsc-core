<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$s = $summary;
$qstr = http_build_query(['date_from' => $date_from, 'date_to' => $date_to, 'sheet_type' => $sheet_type]);
$total_bolong = ($s->bolong_revenue ?? 0) + ($s->bolong_margin ?? 0) + ($s->bolong_vendor ?? 0);
?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title mb-0">
                            <i class="fas fa-file-alt text-primary"></i> Laporan Mingguan
                        </h1>
                        <small class="text-muted">
                            Periode: <strong><?= date('d M Y', strtotime($date_from)) ?></strong>
                            s/d <strong><?= date('d M Y', strtotime($date_to)) ?></strong>
                        </small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('analytics') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chart-bar me-1"></i> Dashboard
                        </a>
                        <a href="<?= base_url('analytics/daily') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-calendar-day me-1"></i> Daily
                        </a>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body py-2">
                        <form method="GET" action="<?= base_url('analytics/weekly') ?>" class="row g-2 align-items-end">
                            <div class="col-md-3 col-6">
                                <label class="form-label small mb-1 fw-semibold">Dari Tanggal</label>
                                <input type="date" name="date_from" class="form-control form-control-sm"
                                    value="<?= $date_from ?>">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label small mb-1 fw-semibold">Sampai Tanggal</label>
                                <input type="date" name="date_to" class="form-control form-control-sm"
                                    value="<?= $date_to ?>">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label small mb-1 fw-semibold">Sheet Type</label>
                                <select name="sheet_type" class="form-select form-select-sm">
                                    <option value="">Semua Sheet</option>
                                    <?php foreach ($sheet_type_list as $st): ?>
                                        <option value="<?= $st->sheet_type ?>" <?= $sheet_type == $st->sheet_type ? 'selected' : '' ?>>
                                            <?= $st->sheet_type ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                        <i class="fas fa-search me-1"></i> Tampilkan
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        onclick="setThisWeek()">Minggu Ini</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        onclick="setLastWeek()">Minggu Lalu</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-2">
                        <div class="card text-center py-2 shadow-sm" style="border-top:3px solid #4e73df">
                            <div class="card-body py-1 px-2">
                                <div class="text-xs text-primary fw-bold text-uppercase">Shipment</div>
                                <div class="h4 mb-0 fw-bold"><?= number_format($s->total_shipment ?? 0) ?></div>
                                <div style="font-size:.65rem" class="text-muted"><?= $s->total_customer ?? 0 ?> customer
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card text-center py-2 shadow-sm" style="border-top:3px solid #1cc88a">
                            <div class="card-body py-1 px-2">
                                <div class="text-xs text-success fw-bold text-uppercase">Revenue</div>
                                <div class="small fw-bold">Rp <?= number_format($s->total_revenue ?? 0, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card text-center py-2 shadow-sm"
                            style="border-top:3px solid <?= ($s->total_margin ?? 0) >= 0 ? '#1cc88a' : '#e74a3b' ?>">
                            <div class="card-body py-1 px-2">
                                <div
                                    class="text-xs fw-bold text-uppercase <?= ($s->total_margin ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                    Margin</div>
                                <div
                                    class="small fw-bold <?= ($s->total_margin ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                    Rp <?= number_format($s->total_margin ?? 0, 0, ',', '.') ?>
                                </div>
                                <?php if (($s->total_revenue ?? 0) > 0): ?>
                                    <div style="font-size:.65rem" class="text-muted">
                                        <?= round(($s->total_margin / $s->total_revenue) * 100, 1) ?>%
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card text-center py-2 shadow-sm" style="border-top:3px solid #e74a3b">
                            <div class="card-body py-1 px-2">
                                <div class="text-xs text-danger fw-bold text-uppercase">⚠️ Data Bolong</div>
                                <div class="h4 mb-0 fw-bold text-danger"><?= $total_bolong ?></div>
                                <div style="font-size:.65rem" class="text-muted">
                                    Rev:<?= $s->bolong_revenue ?? 0 ?> | Mar:<?= $s->bolong_margin ?? 0 ?> |
                                    Ven:<?= $s->bolong_vendor ?? 0 ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card text-center py-2 shadow-sm" style="border-top:3px solid #f6c23e">
                            <div class="card-body py-1 px-2">
                                <div class="text-xs text-warning fw-bold text-uppercase">Unfulfill</div>
                                <div class="h4 mb-0 fw-bold text-warning"><?= $s->total_unfulfill ?? 0 ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card text-center py-2 shadow-sm" style="border-top:3px solid #36b9cc">
                            <div class="card-body py-1 px-2">
                                <div class="text-xs text-info fw-bold text-uppercase">Pending Payment</div>
                                <div class="h4 mb-0 fw-bold text-info"><?= $s->pending_payment ?? 0 ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ SECTION 1: Per Customer ══ -->
                <div class="card shadow mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="m-0 fw-bold"><i class="fas fa-users me-2 text-primary"></i> Ringkasan per Customer
                        </h6>
                        <a href="<?= base_url('analytics/export_weekly?' . $qstr . '&section=per_customer') ?>"
                            class="btn btn-success btn-sm">
                            <i class="fas fa-download me-1"></i> Export CSV
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" style="font-size:.78rem">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Customer</th>
                                        <th class="text-center">Shipment</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Margin</th>
                                        <th class="text-center">Margin %</th>
                                        <th class="text-center">Unfulfill</th>
                                        <th class="text-center">Pending Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($per_customer)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada data</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($per_customer as $i => $r): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td class="fw-semibold"><?= htmlspecialchars($r->customer) ?></td>
                                                <td class="text-center"><?= $r->total_shipment ?></td>
                                                <td class="text-end">Rp <?= number_format($r->total_revenue, 0, ',', '.') ?>
                                                </td>
                                                <td
                                                    class="text-end <?= $r->total_margin >= 0 ? 'text-success' : 'text-danger' ?> fw-semibold">
                                                    Rp <?= number_format($r->total_margin, 0, ',', '.') ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-<?= $r->margin_pct >= 0 ? 'success' : 'danger' ?>">
                                                        <?= $r->margin_pct ?>%
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($r->total_unfulfill > 0): ?>
                                                        <span class="badge bg-warning text-dark"><?= $r->total_unfulfill ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($r->pending_payment > 0): ?>
                                                        <span class="badge bg-info"><?= $r->pending_payment ?></span>
                                                    <?php else: ?>
                                                        <span class="text-success">✓</span>
                                                    <?php endif ?>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ══ SECTION 2: Data Bolong ══ -->
                <?php if (!empty($bolong)): ?>
                    <div class="card shadow mb-3 border-danger">
                        <div
                            class="card-header d-flex justify-content-between align-items-center py-2 bg-danger text-white">
                            <h6 class="m-0 fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> ⚠️ Data Bolong
                                (<?= count($bolong) ?> item) — Perlu Di-follow Up</h6>
                            <a href="<?= base_url('analytics/export_weekly?' . $qstr . '&section=bolong') ?>"
                                class="btn btn-light btn-sm text-danger fw-bold">
                                <i class="fas fa-download me-1"></i> Export
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:300px;overflow-y:auto">
                                <table class="table table-sm table-hover mb-0" style="font-size:.75rem">
                                    <thead class="table-warning sticky-top">
                                        <tr>
                                            <th>Tgl</th>
                                            <th>Sheet</th>
                                            <th>Customer</th>
                                            <th>Origin</th>
                                            <th>Dest</th>
                                            <th>Vendor</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">Margin</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bolong as $r):
                                            $ket = [];
                                            if (empty($r->trip_cost_from_user) || $r->trip_cost_from_user == 0)
                                                $ket[] = '<span class="badge bg-danger" style="font-size:.6rem">Revenue ⚠️</span>';
                                            if (empty($r->margin) || $r->margin == 0)
                                                $ket[] = '<span class="badge bg-danger" style="font-size:.6rem">Margin ⚠️</span>';
                                            if (empty($r->vendor))
                                                $ket[] = '<span class="badge bg-warning text-dark" style="font-size:.6rem">Vendor ⚠️</span>';
                                            ?>
                                            <tr class="table-warning">
                                                <td><?= $r->start_date ? date('d/m/y', strtotime($r->start_date)) : '-' ?></td>
                                                <td><span class="badge bg-secondary"
                                                        style="font-size:.6rem"><?= $r->sheet_type ?></span></td>
                                                <td class="fw-semibold"><?= htmlspecialchars($r->customer ?? '-') ?></td>
                                                <td><?= htmlspecialchars($r->origin ?? '-') ?></td>
                                                <td><?= htmlspecialchars($r->dest_1 ?? '-') ?></td>
                                                <td><?= htmlspecialchars($r->vendor ?? '<em class="text-danger">—</em>') ?></td>
                                                <td class="text-end <?= ($r->trip_cost_from_user == 0) ? 'text-danger' : '' ?>">
                                                    <?= ($r->trip_cost_from_user == 0) ? '—' : 'Rp ' . number_format($r->trip_cost_from_user, 0, ',', '.') ?>
                                                </td>
                                                <td
                                                    class="text-end <?= ($r->margin == 0) ? 'text-danger' : ($r->margin > 0 ? 'text-success' : 'text-danger') ?>">
                                                    <?= ($r->margin == 0) ? '—' : 'Rp ' . number_format($r->margin, 0, ',', '.') ?>
                                                </td>
                                                <td><?= implode(' ', $ket) ?></td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <!-- ══ SECTION 3: Pending Payment ══ -->
                <?php if (!empty($pending_payment)): ?>
                    <div class="card shadow mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center py-2"
                            style="background:#e8f4f8">
                            <h6 class="m-0 fw-bold text-info"><i class="fas fa-clock me-2"></i> 💰 Pending Payment
                                (<?= count($pending_payment) ?> item)</h6>
                            <a href="<?= base_url('analytics/export_weekly?' . $qstr . '&section=pending_payment') ?>"
                                class="btn btn-info btn-sm text-white">
                                <i class="fas fa-download me-1"></i> Export
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:300px;overflow-y:auto">
                                <table class="table table-sm table-hover mb-0" style="font-size:.75rem">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Tgl</th>
                                            <th>Customer</th>
                                            <th>Origin → Dest</th>
                                            <th class="text-end">Revenue</th>
                                            <th>Status Bayar</th>
                                            <th>No. Invoice</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_payment as $r): ?>
                                            <tr>
                                                <td><?= $r->start_date ? date('d/m/y', strtotime($r->start_date)) : '-' ?></td>
                                                <td class="fw-semibold"><?= htmlspecialchars($r->customer ?? '-') ?></td>
                                                <td><?= htmlspecialchars($r->origin ?? '-') ?> →
                                                    <?= htmlspecialchars($r->dest_1 ?? '-') ?></td>
                                                <td class="text-end">Rp
                                                    <?= number_format($r->trip_cost_from_user, 0, ',', '.') ?></td>
                                                <td><span class="badge bg-warning text-dark" style="font-size:.65rem">⏳
                                                        <?= htmlspecialchars($r->status_payment_user ?: 'Belum ada status') ?></span>
                                                </td>
                                                <td class="text-muted small"><?= htmlspecialchars($r->no_invoice_user ?: '—') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <!-- ══ SECTION 4: Unfulfill ══ -->
                <?php if (!empty($unfulfill)): ?>
                    <div class="card shadow mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center py-2"
                            style="background:#fff3cd">
                            <h6 class="m-0 fw-bold text-warning"><i class="fas fa-times-circle me-2"></i> ❌ Unfulfill /
                                Cancel (<?= count($unfulfill) ?> item)</h6>
                            <a href="<?= base_url('analytics/export_weekly?' . $qstr . '&section=unfulfill') ?>"
                                class="btn btn-warning btn-sm">
                                <i class="fas fa-download me-1"></i> Export
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:250px;overflow-y:auto">
                                <table class="table table-sm table-hover mb-0" style="font-size:.75rem">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Tgl</th>
                                            <th>Sheet</th>
                                            <th>Customer</th>
                                            <th>Origin → Dest</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($unfulfill as $r): ?>
                                            <tr>
                                                <td><?= $r->start_date ? date('d/m/y', strtotime($r->start_date)) : '-' ?></td>
                                                <td><span class="badge bg-secondary"
                                                        style="font-size:.6rem"><?= $r->sheet_type ?></span></td>
                                                <td class="fw-semibold"><?= htmlspecialchars($r->customer ?? '-') ?></td>
                                                <td><?= htmlspecialchars($r->origin ?? '-') ?> →
                                                    <?= htmlspecialchars($r->dest_1 ?? '-') ?></td>
                                                <td><span class="badge bg-danger"
                                                        style="font-size:.65rem"><?= htmlspecialchars($r->status) ?></span></td>
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
    function setThisWeek() {
        const today = new Date();
        const day = today.getDay();
        const monday = new Date(today);
        monday.setDate(today.getDate() - (day === 0 ? 6 : day - 1));
        document.querySelector('[name=date_from]').value = monday.toISOString().split('T')[0];
        document.querySelector('[name=date_to]').value = today.toISOString().split('T')[0];
    }
    function setLastWeek() {
        const today = new Date();
        const day = today.getDay();
        const thisMonday = new Date(today);
        thisMonday.setDate(today.getDate() - (day === 0 ? 6 : day - 1));
        const lastMonday = new Date(thisMonday);
        lastMonday.setDate(thisMonday.getDate() - 7);
        const lastSunday = new Date(thisMonday);
        lastSunday.setDate(thisMonday.getDate() - 1);
        document.querySelector('[name=date_from]').value = lastMonday.toISOString().split('T')[0];
        document.querySelector('[name=date_to]').value = lastSunday.toISOString().split('T')[0];
    }
</script>