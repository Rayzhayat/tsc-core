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
                            <i class="fas fa-calendar-day text-primary"></i> Daily Monitoring
                        </h1>
                        <small class="text-muted">Pantau data harian & push ke tim operasional</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('analytics') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chart-bar me-1"></i> Dashboard Analytics
                        </a>
                        <a href="<?= base_url('analytics/export_daily?' . http_build_query([
                            'date_from' => $date_from,
                            'date_to' => $date_to,
                            'sheet_type' => $sheet_type,
                            'status_filter' => 'bolong',
                        ])) ?>" class="btn btn-danger btn-sm">
                            <i class="fas fa-download me-1"></i> Export Data Bolong
                        </a>
                        <a href="<?= base_url('analytics/export_daily?' . http_build_query([
                            'date_from' => $date_from,
                            'date_to' => $date_to,
                            'sheet_type' => $sheet_type,
                            'status_filter' => '',
                        ])) ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-1"></i> Export Semua
                        </a>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body py-2">
                        <form method="GET" action="<?= base_url('analytics/daily') ?>" class="row g-2 align-items-end">
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">Tanggal Dari</label>
                                <input type="date" name="date_from" class="form-control form-control-sm"
                                    value="<?= $date_from ?>">
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">Tanggal Sampai</label>
                                <input type="date" name="date_to" class="form-control form-control-sm"
                                    value="<?= $date_to ?>">
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">Sheet Type</label>
                                <select name="sheet_type" class="form-select form-select-sm">
                                    <option value="">Semua Sheet</option>
                                    <?php foreach ($sheet_type_list as $s): ?>
                                        <option value="<?= $s->sheet_type ?>" <?= $sheet_type == $s->sheet_type ? 'selected' : '' ?>>
                                            <?= $s->sheet_type ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <!-- ✅ TAMBAH: Filter Customer -->
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">Customer</label>
                                <input type="text" name="customer" class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($customer ?? '') ?>" placeholder="cth: savoria">
                            </div>

                            <!-- ✅ TAMBAH: Filter Origin -->
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">Origin</label>
                                <input type="text" name="origin" class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($origin ?? '') ?>" placeholder="cth: subang">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label small mb-1 fw-semibold">Filter Status</label>
                                <select name="status_filter" class="form-select form-select-sm">
                                    <option value="" <?= $status_filter == '' ? 'selected' : '' ?>>Semua Data</option>
                                    <option value="bolong" <?= $status_filter == 'bolong' ? 'selected' : '' ?>>⚠️ Data
                                        Bolong / Belum Diisi</option>
                                    <option value="unfulfill" <?= $status_filter == 'unfulfill' ? 'selected' : '' ?>>❌
                                        Unfulfill / Cancel</option>
                                    <option value="pending_payment" <?= $status_filter == 'pending_payment' ? 'selected' : '' ?>>💰 Pending Payment</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                        <i class="fas fa-search me-1"></i> Cari
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        onclick="setToday()">Hari Ini</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setWeek()">7
                                        Hari</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <?php $s = $summary; ?>
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-2">
                        <div class="card shadow-sm text-center py-2" style="border-top:3px solid #4e73df">
                            <div class="card-body py-1 px-2">
                                <div class="text-xs text-primary fw-bold text-uppercase">Shipment</div>
                                <div class="h4 mb-0 fw-bold"><?= number_format($s->total_shipment ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card shadow-sm text-center py-2" style="border-top:3px solid #1cc88a">
                            <div class="card-body py-1 px-2">
                                <div class="text-xs text-success fw-bold text-uppercase">Revenue</div>
                                <div class="small fw-bold">Rp <?= number_format($s->total_revenue ?? 0, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card shadow-sm text-center py-2"
                            style="border-top:3px solid <?= ($s->total_margin ?? 0) >= 0 ? '#1cc88a' : '#e74a3b' ?>">
                            <div class="card-body py-1 px-2">
                                <div
                                    class="text-xs fw-bold text-uppercase <?= ($s->total_margin ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                    Margin</div>
                                <div
                                    class="small fw-bold <?= ($s->total_margin ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                    Rp <?= number_format($s->total_margin ?? 0, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card shadow-sm text-center py-2"
                            style="border-top:3px solid #e74a3b; cursor:pointer" onclick="filterBolong()">
                            <div class="card-body py-1 px-2">
                                <div class="text-xs text-danger fw-bold text-uppercase">⚠️ Data Bolong</div>
                                <div class="h4 mb-0 fw-bold text-danger">
                                    <?= ($s->bolong_revenue ?? 0) + ($s->bolong_margin ?? 0) + ($s->bolong_vendor ?? 0) ?>
                                </div>
                                <div style="font-size:.65rem" class="text-muted">
                                    Rev:<?= $s->bolong_revenue ?? 0 ?> | Margin:<?= $s->bolong_margin ?? 0 ?> |
                                    Vendor:<?= $s->bolong_vendor ?? 0 ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card shadow-sm text-center py-2" style="border-top:3px solid #f6c23e">
                            <div class="card-body py-1 px-2">
                                <div class="text-xs text-warning fw-bold text-uppercase">Unfulfill</div>
                                <div class="h4 mb-0 fw-bold text-warning"><?= $s->total_unfulfill ?? 0 ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card shadow-sm text-center py-2"
                            style="border-top:3px solid #36b9cc; cursor:pointer"
                            onclick="document.querySelector('[name=status_filter]').value='pending_payment'; document.querySelector('form').submit()">
                            <div class="card-body py-1 px-2">
                                <div class="text-xs text-info fw-bold text-uppercase">Pending Payment</div>
                                <div class="h4 mb-0 fw-bold text-info"><?= $s->pending_payment ?? 0 ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert kalau ada bolong -->
                <?php
                $total_bolong = ($s->bolong_revenue ?? 0) + ($s->bolong_margin ?? 0) + ($s->bolong_vendor ?? 0);
                if ($total_bolong > 0): ?>
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong><?= $total_bolong ?> data belum lengkap!</strong>
                                Ada shipment yang revenue / margin / vendor-nya belum diisi di periode ini.
                                Perlu di-push ke tim operasional.
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?= base_url('analytics/export_daily?' . http_build_query([
                                    'date_from' => $date_from,
                                    'date_to' => $date_to,
                                    'sheet_type' => $sheet_type,
                                    'status_filter' => 'bolong',
                                ])) ?>" class="btn btn-danger btn-sm">
                                    <i class="fas fa-download me-1"></i> Download List Bolong
                                </a>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif ?>

                <!-- Tabel Data -->
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="m-0 fw-bold">
                                <i class="fas fa-table me-2 text-primary"></i>
                                Data Shipment
                                <?php if ($status_filter == 'bolong'): ?>
                                    <span class="badge bg-danger ms-1">⚠️ Bolong Only</span>
                                <?php elseif ($status_filter == 'unfulfill'): ?>
                                    <span class="badge bg-warning text-dark ms-1">❌ Unfulfill Only</span>
                                <?php elseif ($status_filter == 'pending_payment'): ?>
                                    <span class="badge bg-info ms-1">💰 Pending Payment Only</span>
                                <?php endif ?>
                            </h6>
                            <small class="text-muted">
                                <?= date('d M Y', strtotime($date_from)) ?> s/d
                                <?= date('d M Y', strtotime($date_to)) ?>
                                — <strong><?= number_format(count($shipments)) ?></strong> baris
                            </small>
                        </div>
                        <input type="text" id="tableSearch" class="form-control form-control-sm" style="max-width:200px"
                            placeholder="Cari customer / origin...">
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:500px; overflow-y:auto">
                            <table class="table table-sm table-hover mb-0" id="dailyTable">
                                <thead class="table-dark sticky-top" style="font-size:.72rem">
                                    <tr>
                                        <th>#</th>
                                        <th>Tgl</th>
                                        <th>Sheet</th>
                                        <th>Customer</th>
                                        <th>Origin</th>
                                        <th>Dest</th>
                                        <th>Vendor</th>
                                        <th>Status Trip</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Margin</th>
                                        <!-- ✅ UPDATED: Kolom payment dibagi 2 — status + no invoice -->
                                        <th>Status Bayar</th>
                                        <th>No. Invoice</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody style="font-size:.75rem">
                                    <?php if (empty($shipments)): ?>
                                        <tr>
                                            <td colspan="13" class="text-center py-5 text-muted">
                                                <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                                Tidak ada data untuk filter ini
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($shipments as $i => $r):
                                            $status_lower = strtolower($r->status ?? '');

                                            $is_bolong = (
                                                empty($r->trip_cost_from_user) || $r->trip_cost_from_user == 0
                                                || empty($r->margin) || $r->margin == 0
                                                || empty($r->vendor)
                                            );
                                            $is_unfulfill = (
                                                strpos($status_lower, 'unfulfill') !== false
                                                || strpos($status_lower, 'not fulfilled') !== false
                                                || strpos($status_lower, 'not fullfill') !== false
                                                || strpos($status_lower, 'cancel') !== false
                                            );

                                            $notes = [];
                                            if (empty($r->trip_cost_from_user) || $r->trip_cost_from_user == 0)
                                                $notes[] = '<span class="badge bg-danger" style="font-size:.6rem">Revenue ⚠️</span>';
                                            if (empty($r->margin) || $r->margin == 0)
                                                $notes[] = '<span class="badge bg-danger" style="font-size:.6rem">Margin ⚠️</span>';
                                            if (empty($r->vendor))
                                                $notes[] = '<span class="badge bg-warning text-dark" style="font-size:.6rem">Vendor ⚠️</span>';

                                            $row_class = $is_bolong ? 'table-warning' : ($is_unfulfill ? 'table-danger' : '');

                                            // ── Status trip badge ──
                                            $st = strtolower($r->status ?? '');
                                            if ($st === 'done') {
                                                $badge = 'success';
                                            } elseif (strpos($st, 'unfulfill') !== false || strpos($st, 'not fulfill') !== false || strpos($st, 'cancel') !== false) {
                                                $badge = 'danger';
                                            } elseif (in_array($st, ['running', 'otw', 'process', 'ongoing', 'in progress'])) {
                                                $badge = 'warning';
                                            } elseif ($st === 'fulfilled') {
                                                $badge = 'success';
                                            } else {
                                                $badge = 'secondary';
                                            }

                                            // ✅ FIXED: status_payment_user sekarang = "Waiting Payment"/"Done Payment"
                                            $pu = strtolower($r->status_payment_user ?? '');
                                            if (strpos($pu, 'done') !== false) {
                                                $pu_badge = 'success';
                                                $pu_icon = '✓';
                                            } elseif (strpos($pu, 'waiting sj') !== false) {
                                                $pu_badge = 'secondary';
                                                $pu_icon = '📋';
                                            } elseif (strpos($pu, 'waiting') !== false) {
                                                $pu_badge = 'warning';
                                                $pu_icon = '⏳';
                                            } else {
                                                $pu_badge = 'secondary';
                                                $pu_icon = '';
                                            }

                                            // No invoice: ambil dari no_invoice_user (kolom "Status Payment to User" di CSV = nomor invoice)
                                            $no_inv = trim($r->no_invoice_user ?? '');
                                            ?>
                                            <tr class="<?= $row_class ?>"
                                                data-search="<?= strtolower(($r->customer ?? '') . ' ' . ($r->origin ?? '') . ' ' . ($r->dest_1 ?? '') . ' ' . ($r->vendor ?? '')) ?>">
                                                <td><?= $i + 1 ?></td>
                                                <td style="white-space:nowrap">
                                                    <?= $r->start_date ? date('d/m/y', strtotime($r->start_date)) : '-' ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"
                                                        style="font-size:.6rem"><?= $r->sheet_type ?></span>
                                                </td>
                                                <td class="fw-semibold"><?= htmlspecialchars($r->customer ?? '-') ?></td>
                                                <td class="text-truncate" style="max-width:90px"
                                                    title="<?= htmlspecialchars($r->origin ?? '') ?>">
                                                    <?= htmlspecialchars($r->origin ?? '-') ?>
                                                </td>
                                                <td class="text-truncate" style="max-width:90px"
                                                    title="<?= htmlspecialchars($r->dest_1 ?? '') ?>">
                                                    <?= htmlspecialchars($r->dest_1 ?? '-') ?>
                                                </td>
                                                <td class="text-truncate" style="max-width:80px"
                                                    title="<?= htmlspecialchars($r->vendor ?? '') ?>">
                                                    <?= htmlspecialchars($r->vendor ?? '-') ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $badge ?>" style="font-size:.62rem">
                                                        <?= htmlspecialchars($r->status ?? 'N/A') ?>
                                                    </span>
                                                </td>
                                                <td
                                                    class="text-end <?= ($r->trip_cost_from_user == 0) ? 'text-danger fw-bold' : '' ?>">
                                                    <?= ($r->trip_cost_from_user == 0) ? '—' : 'Rp ' . number_format($r->trip_cost_from_user, 0, ',', '.') ?>
                                                </td>
                                                <td
                                                    class="text-end fw-semibold <?= $r->margin > 0 ? 'text-success' : ($r->margin < 0 ? 'text-danger' : 'text-muted') ?>">
                                                    <?= ($r->margin == 0) ? '—' : 'Rp ' . number_format($r->margin, 0, ',', '.') ?>
                                                </td>
                                                <!-- ✅ FIXED: Status bayar sekarang tampil "Waiting Payment" / "Done Payment" -->
                                                <td>
                                                    <span
                                                        class="badge bg-<?= $pu_badge ?> text-<?= $pu_badge === 'warning' ? 'dark' : 'white' ?>"
                                                        style="font-size:.6rem">
                                                        <?= $pu_icon ?>
                                                        <?= htmlspecialchars($r->status_payment_user ?: 'N/A') ?>
                                                    </span>
                                                </td>
                                                <!-- ✅ NEW: Nomor invoice tampil terpisah, bisa dicopy -->
                                                <td class="text-truncate" style="max-width:120px"
                                                    title="<?= htmlspecialchars($no_inv) ?>">
                                                    <?php if ($no_inv): ?>
                                                        <small class="text-muted"
                                                            style="font-size:.65rem"><?= htmlspecialchars($no_inv) ?></small>
                                                    <?php else: ?>
                                                        <small class="text-muted">—</small>
                                                    <?php endif ?>
                                                </td>
                                                <td>
                                                    <?= !empty($notes)
                                                        ? implode(' ', $notes)
                                                        : '<span class="text-success" style="font-size:.7rem">✓ OK</span>' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if (!empty($shipments)): ?>
                        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
                            <small class="text-muted">Total: <strong><?= count($shipments) ?></strong> baris
                                ditampilkan</small>
                            <a href="<?= base_url('analytics/export_daily?' . http_build_query([
                                'date_from' => $date_from,
                                'date_to' => $date_to,
                                'sheet_type' => $sheet_type,
                                'status_filter' => $status_filter,
                            ])) ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-file-csv me-1"></i> Export Tabel Ini ke CSV
                            </a>
                        </div>
                    <?php endif ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('tableSearch').addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#dailyTable tbody tr').forEach(function (row) {
            row.style.display = (row.dataset.search && row.dataset.search.indexOf(q) !== -1) ? '' : 'none';
        });
    });

    function setToday() {
        var today = new Date().toISOString().split('T')[0];
        document.querySelector('[name=date_from]').value = today;
        document.querySelector('[name=date_to]').value = today;
    }
    function setWeek() {
        var today = new Date();
        var from = new Date(today);
        from.setDate(today.getDate() - 6);
        document.querySelector('[name=date_from]').value = from.toISOString().split('T')[0];
        document.querySelector('[name=date_to]').value = today.toISOString().split('T')[0];
    }
    function filterBolong() {
        document.querySelector('[name=status_filter]').value = 'bolong';
        document.querySelector('form').submit();
    }
</script>