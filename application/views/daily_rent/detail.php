<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .badge-purple {
            background-color: #6f42c1;
            color: white;
        }

        .badge-status {
            font-size: 0.72rem;
            padding: 4px 8px;
            font-weight: 600;
            border-radius: 4px;
            white-space: nowrap;
        }

        /* ── INFO HEADER CARD ── */
        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #858796;
            font-weight: 700;
        }

        .info-value {
            font-size: 0.88rem;
            font-weight: 600;
            color: #3a3b45;
        }

        .info-item {
            padding: 10px 0;
            border-bottom: 1px solid #f0f2f5;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        /* ── UNIT CARD ── */
        .unit-card {
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 12px;
            position: relative;
            transition: box-shadow .15s;
            background: #fff;
        }

        .unit-card:hover {
            box-shadow: 0 .25rem 1rem rgba(0, 0, 0, .08);
        }

        .unit-card.status-pending {
            border-left: 4px solid #6c757d;
        }

        .unit-card.status-assigned {
            border-left: 4px solid #17a2b8;
        }

        .unit-card.status-active {
            border-left: 4px solid #4e73df;
        }

        .unit-card.status-extended {
            border-left: 4px solid #ffc107;
        }

        .unit-card.status-returned {
            border-left: 4px solid #28a745;
            background: #f8fff9;
        }

        .unit-card.status-cancelled {
            border-left: 4px solid #dc3545;
            background: #fff8f8;
            opacity: .75;
        }

        .unit-nopol {
            font-family: monospace;
            font-weight: 800;
            font-size: 1rem;
            color: #4e73df;
            letter-spacing: 1px;
        }

        .unit-meta {
            font-size: 0.78rem;
            color: #6c757d;
        }

        .unit-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 8px;
        }

        .unit-actions .btn {
            font-size: 0.72rem;
            padding: 3px 10px;
        }

        /* ── OVERRUN BADGE ── */
        .overrun-badge {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
        }

        /* ── TIMELINE ── */
        .timeline {
            position: relative;
            padding-left: 24px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e3e6f0;
        }

        .tl-item {
            position: relative;
            margin-bottom: 14px;
        }

        .tl-dot {
            position: absolute;
            left: -20px;
            top: 3px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #dee2e6;
            background: #adb5bd;
        }

        .tl-dot.dot-location {
            background: #17a2b8;
            box-shadow: 0 0 0 2px #bee5eb;
        }

        .tl-dot.dot-driver {
            background: #6f42c1;
            box-shadow: 0 0 0 2px #e0cffc;
        }

        .tl-dot.dot-ext {
            background: #ffc107;
            box-shadow: 0 0 0 2px #ffeeba;
        }

        .tl-content {
            background: #f8f9fc;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.78rem;
        }

        .tl-time {
            font-size: 0.68rem;
            color: #aaa;
            margin-top: 2px;
        }

        /* ── TABS ── */
        .nav-tabs .nav-link {
            font-size: 0.82rem;
            font-weight: 600;
            color: #858796;
        }

        .nav-tabs .nav-link.active {
            color: #4e73df;
            border-bottom: 2px solid #4e73df;
        }

        /* ── DURATION BOX ── */
        .dur-box {
            text-align: center;
        }

        .dur-days {
            font-size: 2rem;
            font-weight: 800;
            color: #4e73df;
            line-height: 1;
        }

        .dur-label {
            font-size: 0.72rem;
            color: #aaa;
            text-transform: uppercase;
        }

        .dur-remaining {
            font-size: 0.78rem;
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #aaa;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 12px;
            display: block;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <?php
                    $rent = $rent;
                    $units = $rent->units ?? [];
                    $extensions = $rent->extensions ?? [];
                    $loc_history = $loc_history ?? [];
                    $driver_logs = $driver_logs ?? [];
                    $level = $this->session->userdata('login')['user_level'] ?? '';
                    $can_edit = in_array($level, ['superadmin', 'admin_operational', 'operational_staff']);
                    $can_delete = in_array($level, ['superadmin', 'admin_operational']);

                    $status = $rent->status_rent ?? 'Sourcing Vendor';
                    $status_color = $statuses[$status] ?? 'secondary';

                    $start_date = $rent->rent_start_date ?? null;
                    $end_date = $rent->rent_end_date ?? null;
                    $dur_days = ($start_date && $end_date) ? round((strtotime($end_date) - strtotime($start_date)) / 86400) : null;

                    $today = date('Y-m-d');
                    $remaining = ($end_date && !in_array($status, ['Completed', 'Cancelled']))
                        ? round((strtotime($end_date) - strtotime($today)) / 86400) : null;

                    $fmt_date = fn($d) => (!empty($d) && $d !== '0000-00-00') ? date('d/m/Y', strtotime($d)) : '-';
                    $fmt_time = fn($t) => !empty($t) ? substr($t, 0, 5) : '-';

                    $unit_status_color = [
                        'Pending Assign' => 'secondary',
                        'Assigned' => 'info',
                        'Active' => 'primary',
                        'Extended' => 'warning',
                        'Returned' => 'success',
                        'Cancelled' => 'danger'
                    ];
                    $unit_status_cls = [
                        'Pending Assign' => 'pending',
                        'Assigned' => 'assigned',
                        'Active' => 'active',
                        'Extended' => 'extended',
                        'Returned' => 'returned',
                        'Cancelled' => 'cancelled'
                    ];
                    ?>

                    <!-- BREADCRUMB & HEADER -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-3">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-1" style="font-size:0.78rem;">
                                    <li class="breadcrumb-item"><a href="<?= base_url('daily_rent') ?>">Daily Rent</a>
                                    </li>
                                    <li class="breadcrumb-item active"><?= htmlspecialchars($rent->no_rent) ?></li>
                                </ol>
                            </nav>
                            <h1 class="h3 mb-0">
                                <span
                                    style="font-family:monospace; color:#4e73df;"><?= htmlspecialchars($rent->no_rent) ?></span>
                                <span
                                    class="badge badge-status badge-<?= $status_color ?> ml-2"><?= htmlspecialchars($status) ?></span>
                            </h1>
                        </div>
                        <div class="d-flex flex-wrap mt-2 mt-sm-0" style="gap:6px;">
                            <?php if ($can_edit && !in_array($status, ['Completed', 'Cancelled'])): ?>
                                <a href="<?= base_url('daily_rent/ubah/' . $rent->id) ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-edit"></i> Edit Order
                                </a>
                            <?php endif ?>
                            <a href="<?= base_url('daily_rent') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <!-- ALERTS -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <div class="row">

                        <!-- ── KOLOM KIRI: Info Order ── -->
                        <div class="col-lg-4 mb-4">

                            <!-- Info Card -->
                            <div class="card shadow mb-3">
                                <div class="card-header bg-info text-white py-2">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-info-circle"></i> Info Order</h6>
                                </div>
                                <div class="card-body py-2 px-3">

                                    <div class="info-item">
                                        <div class="info-label">Customer</div>
                                        <div class="info-value"><?= htmlspecialchars($rent->nama_customer ?? '-') ?>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Vendor Default</div>
                                        <div class="info-value"><?= htmlspecialchars($rent->nama_vendor ?? '-') ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">PIC Customer</div>
                                        <div class="info-value"><?= htmlspecialchars($rent->pic_customer ?? '-') ?>
                                        </div>
                                        <?php if (!empty($rent->pic_customer_phone)): ?>
                                            <div class="text-muted small"><i class="fas fa-phone"></i>
                                                <?= htmlspecialchars($rent->pic_customer_phone) ?></div>
                                        <?php endif ?>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Lokasi Operasional</div>
                                        <div class="info-value"><i class="fas fa-map-marker-alt text-danger"></i>
                                            <?= htmlspecialchars($rent->location ?? '-') ?></div>
                                    </div>
                                    <?php if (!empty($rent->notes)): ?>
                                        <div class="info-item">
                                            <div class="info-label">Notes</div>
                                            <div class="info-value text-muted small"><?= htmlspecialchars($rent->notes) ?>
                                            </div>
                                        </div>
                                    <?php endif ?>
                                    <div class="info-item">
                                        <div class="info-label">Dibuat</div>
                                        <div class="info-value small text-muted">
                                            <?= !empty($rent->created_at) ? date('d/m/Y H:i', strtotime($rent->created_at)) : '-' ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($rent->cancel_reason)): ?>
                                        <div class="info-item">
                                            <div class="info-label text-danger">Alasan Cancel</div>
                                            <div class="text-danger small"><?= htmlspecialchars($rent->cancel_reason) ?>
                                            </div>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>

                            <!-- Durasi Card -->
                            <div class="card shadow mb-3">
                                <div class="card-header bg-warning text-white py-2">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-calendar-alt"></i> Periode Sewa
                                    </h6>
                                </div>
                                <div class="card-body text-center py-3">
                                    <?php if ($dur_days !== null): ?>
                                        <div class="dur-box mb-2">
                                            <div class="dur-days"><?= $dur_days ?></div>
                                            <div class="dur-label">Hari Total</div>
                                        </div>
                                    <?php endif ?>
                                    <div class="small mb-1">
                                        <span class="text-success fw-bold"><i class="fas fa-play-circle"></i>
                                            Mulai:</span>
                                        <?= $fmt_date($start_date) ?> <?= $fmt_time($rent->rent_start_time ?? '') ?>
                                    </div>
                                    <div class="small mb-2">
                                        <span class="text-danger fw-bold"><i class="fas fa-stop-circle"></i>
                                            Selesai:</span>
                                        <?= $fmt_date($end_date) ?> <?= $fmt_time($rent->rent_end_time ?? '') ?>
                                    </div>
                                    <?php if ($remaining !== null): ?>
                                        <?php if ($remaining < 0): ?>
                                            <span class="badge bg-danger">🔴 Overdue <?= abs($remaining) ?> hari</span>
                                        <?php elseif ($remaining === 0): ?>
                                            <span class="badge bg-warning text-dark">🕐 Berakhir hari ini</span>
                                        <?php elseif ($remaining <= 3): ?>
                                            <span class="badge bg-warning text-dark">⏳ <?= $remaining ?> hari lagi</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">📅 <?= $remaining ?> hari lagi</span>
                                        <?php endif ?>
                                    <?php endif ?>
                                    <?php if (!empty($rent->actual_start_date)): ?>
                                        <div class="small text-muted mt-2">
                                            <i class="fas fa-check-circle text-success"></i> Actual Start:
                                            <?= $fmt_date($rent->actual_start_date) ?>
                                        </div>
                                    <?php endif ?>

                                    <?php if ($can_edit && !in_array($status, ['Completed', 'Cancelled'])): ?>
                                        <hr class="my-2">
                                        <button class="btn btn-outline-warning btn-sm w-100" id="btnExtendOrder">
                                            <i class="fas fa-calendar-plus"></i> Perpanjang Semua Unit
                                        </button>
                                    <?php endif ?>
                                </div>
                            </div>

                            <!-- Unit Summary -->
                            <div class="card shadow mb-3">
                                <div class="card-header bg-secondary text-white py-2">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-truck"></i> Ringkasan Unit</h6>
                                </div>
                                <div class="card-body py-2">
                                    <?php
                                    $sum = ['Pending Assign' => 0, 'Assigned' => 0, 'Active' => 0, 'Extended' => 0, 'Returned' => 0, 'Cancelled' => 0];
                                    foreach ($units as $u)
                                        $sum[$u->status_unit] = ($sum[$u->status_unit] ?? 0) + 1;
                                    $total_u = array_sum($sum);
                                    ?>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted">Total Unit</span>
                                        <span class="fw-bold"><?= $total_u ?></span>
                                    </div>
                                    <?php foreach ($sum as $st => $cnt):
                                        if ($cnt > 0): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small"><?= $st ?></span>
                                                <span
                                                    class="badge badge-<?= $unit_status_color[$st] ?? 'secondary' ?>"><?= $cnt ?></span>
                                            </div>
                                        <?php endif; endforeach ?>
                                </div>
                            </div>

                        </div>

                        <!-- ── KOLOM KANAN: Units + Tabs ── -->
                        <div class="col-lg-8 mb-4">

                            <!-- UNITS CARD -->
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-truck"></i> Unit Kendaraan
                                        <span class="badge bg-light text-dark ml-2"><?= count($units) ?></span>
                                    </h6>
                                    <?php if ($can_edit && !in_array($status, ['Completed', 'Cancelled'])): ?>
                                        <button class="btn btn-light btn-sm" id="btnTambahUnit">
                                            <i class="fas fa-plus"></i> Tambah Unit
                                        </button>
                                    <?php endif ?>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($units)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-truck text-muted"></i>
                                            <p>Belum ada unit. Klik <strong>Tambah Unit</strong> untuk menambahkan
                                                kendaraan.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($units as $u):
                                            $ucls = $unit_status_cls[$u->status_unit] ?? 'pending';
                                            $ucolor = $unit_status_color[$u->status_unit] ?? 'secondary';
                                            $u_dur = ($u->actual_start_date && $u->actual_return_date)
                                                ? round((strtotime($u->actual_return_date) - strtotime($u->actual_start_date)) / 86400) : null;
                                            $u_rem = ($u->rent_end_date && !in_array($u->status_unit, ['Returned', 'Cancelled']))
                                                ? round((strtotime($u->rent_end_date) - strtotime($today)) / 86400) : null;
                                            ?>
                                            <div class="unit-card status-<?= $ucls ?>">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <span
                                                            class="unit-nopol"><?= htmlspecialchars($u->nopol ?: '???') ?></span>
                                                        <span class="badge badge-dark ml-1"
                                                            style="font-size:0.65rem;"><?= htmlspecialchars($u->truck_type ?: '-') ?></span>
                                                        <span
                                                            class="badge badge-<?= $ucolor ?> ml-1 badge-status"><?= htmlspecialchars($u->status_unit) ?></span>
                                                        <?php if ($u->overrun_days > 0): ?>
                                                            <span class="overrun-badge ml-1">+<?= $u->overrun_days ?>h
                                                                overrun</span>
                                                        <?php endif ?>
                                                    </div>
                                                    <small class="text-muted">#<?= $u->id ?></small>
                                                </div>

                                                <div class="unit-meta mt-1">
                                                    <i class="fas fa-building"></i>
                                                    <?= htmlspecialchars($u->nama_vendor ?: '-') ?>
                                                    <?php if (!empty($u->driver)): ?>
                                                        &nbsp;|&nbsp; <i class="fas fa-user"></i>
                                                        <?= htmlspecialchars($u->driver) ?>
                                                        <?php if (!empty($u->no_hp)): ?>
                                                            <a href="tel:<?= htmlspecialchars($u->no_hp) ?>" class="text-muted">
                                                                <i class="fas fa-phone" style="font-size:0.65rem;"></i>
                                                                <?= htmlspecialchars($u->no_hp) ?>
                                                            </a>
                                                        <?php endif ?>
                                                    <?php endif ?>
                                                </div>

                                                <!-- Periode unit -->
                                                <div class="unit-meta mt-1">
                                                    <i class="fas fa-calendar text-info"></i>
                                                    <?= $fmt_date($u->rent_start_date) ?> – <?= $fmt_date($u->rent_end_date) ?>
                                                    <?php if ($u_rem !== null): ?>
                                                        <span
                                                            class="ml-1 <?= $u_rem < 0 ? 'text-danger fw-bold' : ($u_rem <= 3 ? 'text-warning' : 'text-muted') ?>">
                                                            (<?= $u_rem < 0 ? 'overdue ' . abs($u_rem) . 'h' : ($u_rem === 0 ? 'hari ini' : $u_rem . 'h lagi') ?>)
                                                        </span>
                                                    <?php endif ?>
                                                    <?php if (!empty($u->current_location)): ?>
                                                        &nbsp;|&nbsp; <i class="fas fa-map-marker-alt text-danger"></i>
                                                        <?= htmlspecialchars($u->current_location) ?>
                                                    <?php endif ?>
                                                </div>

                                                <!-- Actual timestamps -->
                                                <?php if (!empty($u->actual_start_date) || !empty($u->actual_return_date)): ?>
                                                    <div class="unit-meta mt-1">
                                                        <?php if (!empty($u->actual_start_date)): ?>
                                                            <span class="text-success"><i class="fas fa-play-circle"></i> Start:
                                                                <?= $fmt_date($u->actual_start_date) ?>
                                                                <?= $fmt_time($u->actual_start_time) ?></span>
                                                        <?php endif ?>
                                                        <?php if (!empty($u->actual_return_date)): ?>
                                                            &nbsp;|&nbsp;
                                                            <span class="text-danger"><i class="fas fa-stop-circle"></i> Return:
                                                                <?= $fmt_date($u->actual_return_date) ?>
                                                                <?= $fmt_time($u->actual_return_time) ?></span>
                                                        <?php endif ?>
                                                        <?php if ($u_dur !== null): ?>
                                                            <span class="badge bg-secondary ml-1"><?= $u_dur ?> hari aktual</span>
                                                        <?php endif ?>
                                                    </div>
                                                <?php endif ?>

                                                <!-- Notes unit -->
                                                <?php if (!empty($u->notes)): ?>
                                                    <div class="unit-meta mt-1 text-muted"><i class="fas fa-sticky-note"></i>
                                                        <?= htmlspecialchars($u->notes) ?></div>
                                                <?php endif ?>

                                                <!-- Action Buttons -->
                                                <?php if ($can_edit): ?>
                                                    <div class="unit-actions">
                                                        <?php if ($u->status_unit === 'Pending Assign'): ?>
                                                            <button class="btn btn-primary btn-sm btn-assign-unit"
                                                                data-id="<?= $u->id ?>"
                                                                data-nopol="<?= htmlspecialchars($u->nopol ?? '') ?>"
                                                                data-vendor="<?= $u->vendor_id ?>"
                                                                data-driver="<?= htmlspecialchars($u->driver ?? '') ?>"
                                                                data-nohp="<?= htmlspecialchars($u->no_hp ?? '') ?>">
                                                                <i class="fas fa-user-check"></i> Assign
                                                            </button>
                                                        <?php endif ?>
                                                        <?php if (in_array($u->status_unit, ['Assigned'])): ?>
                                                            <button class="btn btn-success btn-sm btn-activate-unit"
                                                                data-id="<?= $u->id ?>"
                                                                data-nopol="<?= htmlspecialchars($u->nopol ?? '') ?>">
                                                                <i class="fas fa-play"></i> Activate
                                                            </button>
                                                        <?php endif ?>
                                                        <?php if (in_array($u->status_unit, ['Active', 'Extended'])): ?>
                                                            <button class="btn btn-warning btn-sm btn-extend-unit"
                                                                data-id="<?= $u->id ?>"
                                                                data-nopol="<?= htmlspecialchars($u->nopol ?? '') ?>"
                                                                data-enddate="<?= $u->rent_end_date ?>">
                                                                <i class="fas fa-calendar-plus"></i> Extend
                                                            </button>
                                                            <button class="btn btn-info btn-sm btn-catat-lokasi" data-id="<?= $u->id ?>"
                                                                data-nopol="<?= htmlspecialchars($u->nopol ?? '') ?>">
                                                                <i class="fas fa-map-marker-alt"></i> Lokasi
                                                            </button>
                                                            <button class="btn btn-purple btn-sm btn-ganti-driver"
                                                                data-id="<?= $u->id ?>"
                                                                data-nopol="<?= htmlspecialchars($u->nopol ?? '') ?>"
                                                                data-driver="<?= htmlspecialchars($u->driver ?? '') ?>">
                                                                <i class="fas fa-user-cog"></i> Ganti Driver
                                                            </button>
                                                            <button class="btn btn-success btn-sm btn-return-unit"
                                                                data-id="<?= $u->id ?>"
                                                                data-nopol="<?= htmlspecialchars($u->nopol ?? '') ?>">
                                                                <i class="fas fa-undo"></i> Return
                                                            </button>
                                                        <?php endif ?>
                                                        <?php if (!in_array($u->status_unit, ['Returned', 'Cancelled'])): ?>
                                                            <button class="btn btn-outline-danger btn-sm btn-cancel-unit"
                                                                data-id="<?= $u->id ?>"
                                                                data-nopol="<?= htmlspecialchars($u->nopol ?? '') ?>">
                                                                <i class="fas fa-ban"></i> Cancel Unit
                                                            </button>
                                                        <?php endif ?>
                                                        <?php if ($can_delete && in_array($u->status_unit, ['Pending Assign', 'Cancelled'])): ?>
                                                            <button class="btn btn-danger btn-sm btn-hapus-unit" data-id="<?= $u->id ?>"
                                                                data-nopol="<?= htmlspecialchars($u->nopol ?? '') ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif ?>
                                                    </div>
                                                <?php endif ?>
                                            </div>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </div>
                            </div>

                            <!-- TABS: Extensions / Locations / Driver Logs -->
                            <div class="card shadow">
                                <div class="card-header py-2">
                                    <ul class="nav nav-tabs card-header-tabs" id="logTabs">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#tabExtensions">
                                                <i class="fas fa-calendar-plus"></i> Perpanjangan
                                                <?php if (count($extensions) > 0): ?>
                                                    <span
                                                        class="badge bg-warning text-dark ml-1"><?= count($extensions) ?></span>
                                                <?php endif ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#tabLocations">
                                                <i class="fas fa-map-marker-alt"></i> Lokasi
                                                <?php if (count($loc_history) > 0): ?>
                                                    <span class="badge bg-info ml-1"><?= count($loc_history) ?></span>
                                                <?php endif ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#tabDriverLogs">
                                                <i class="fas fa-user-cog"></i> Driver Log
                                                <?php if (count($driver_logs) > 0): ?>
                                                    <span class="badge bg-purple ml-1"><?= count($driver_logs) ?></span>
                                                <?php endif ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body py-3">
                                    <div class="tab-content">

                                        <!-- Extensions Tab -->
                                        <div class="tab-pane fade show active" id="tabExtensions">
                                            <?php if (empty($extensions)): ?>
                                                <div class="empty-state"><i class="fas fa-calendar-plus"></i>
                                                    <p>Belum ada perpanjangan.</p>
                                                </div>
                                            <?php else: ?>
                                                <div class="timeline">
                                                    <?php foreach ($extensions as $ext): ?>
                                                        <div class="tl-item">
                                                            <div class="tl-dot dot-ext"></div>
                                                            <div class="tl-content">
                                                                <div class="fw-bold small">
                                                                    <?= !empty($ext->nopol) ? htmlspecialchars($ext->nopol) . ' — ' : '<span class="badge bg-warning text-dark">Semua Unit</span> ' ?>
                                                                    Extend <span
                                                                        class="text-success">+<?= round($ext->extension_days) ?>
                                                                        hari</span>
                                                                </div>
                                                                <div class="text-muted small">
                                                                    <?= $fmt_date($ext->old_end_date) ?> →
                                                                    <strong><?= $fmt_date($ext->new_end_date) ?></strong>
                                                                    <?php if (!empty($ext->reason)): ?>|
                                                                        <?= htmlspecialchars($ext->reason) ?>        <?php endif ?>
                                                                </div>
                                                                <div class="tl-time">
                                                                    <?= !empty($ext->extended_at) ? date('d/m/Y H:i', strtotime($ext->extended_at)) : '' ?>
                                                                    • <?= htmlspecialchars($ext->extended_by ?? '-') ?></div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach ?>
                                                </div>
                                            <?php endif ?>
                                        </div>

                                        <!-- Locations Tab -->
                                        <div class="tab-pane fade" id="tabLocations">
                                            <?php if (empty($loc_history)): ?>
                                                <div class="empty-state"><i class="fas fa-map-marker-alt"></i>
                                                    <p>Belum ada log lokasi.</p>
                                                </div>
                                            <?php else: ?>
                                                <div class="timeline">
                                                    <?php foreach ($loc_history as $loc): ?>
                                                        <div class="tl-item">
                                                            <div class="tl-dot dot-location"></div>
                                                            <div class="tl-content">
                                                                <div class="fw-bold small">
                                                                    <span
                                                                        style="font-family:monospace;"><?= htmlspecialchars($loc->nopol ?? '-') ?></span>
                                                                    pindah ke <span
                                                                        class="text-info"><?= htmlspecialchars($loc->location) ?></span>
                                                                </div>
                                                                <?php if (!empty($loc->notes)): ?>
                                                                    <div class="text-muted small">
                                                                        <?= htmlspecialchars($loc->notes) ?></div>
                                                                <?php endif ?>
                                                                <div class="tl-time">
                                                                    <?= !empty($loc->moved_at) ? date('d/m/Y H:i', strtotime($loc->moved_at)) : '' ?>
                                                                    • <?= htmlspecialchars($loc->moved_by ?? '-') ?></div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach ?>
                                                </div>
                                            <?php endif ?>
                                        </div>

                                        <!-- Driver Logs Tab -->
                                        <div class="tab-pane fade" id="tabDriverLogs">
                                            <?php if (empty($driver_logs)): ?>
                                                <div class="empty-state"><i class="fas fa-user-cog"></i>
                                                    <p>Belum ada pergantian driver.</p>
                                                </div>
                                            <?php else: ?>
                                                <div class="timeline">
                                                    <?php foreach ($driver_logs as $dl): ?>
                                                        <div class="tl-item">
                                                            <div class="tl-dot dot-driver"></div>
                                                            <div class="tl-content">
                                                                <div class="fw-bold small">
                                                                    <span
                                                                        style="font-family:monospace;"><?= htmlspecialchars($dl->nopol ?? '-') ?></span>:
                                                                    <span
                                                                        class="text-danger"><?= htmlspecialchars($dl->old_driver ?? '-') ?></span>
                                                                    → <span
                                                                        class="text-success"><?= htmlspecialchars($dl->new_driver) ?></span>
                                                                </div>
                                                                <?php if (!empty($dl->reason)): ?>
                                                                    <div class="text-muted small">Alasan:
                                                                        <?= htmlspecialchars($dl->reason) ?></div>
                                                                <?php endif ?>
                                                                <div class="tl-time">
                                                                    <?= !empty($dl->changed_at) ? date('d/m/Y H:i', strtotime($dl->changed_at)) : '' ?>
                                                                    • <?= htmlspecialchars($dl->changed_by ?? '-') ?></div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach ?>
                                                </div>
                                            <?php endif ?>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- MODAL: TAMBAH UNIT -->
    <div class="modal fade" id="modalTambahUnit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-plus"></i> Tambah Unit Kendaraan</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">Truck Type</label>
                            <select id="tuTruckType" class="form-control form-control-sm">
                                <option value="">-- Tipe --</option>
                                <?php foreach (['Blindvan', 'L300', 'CDE', 'CDE Long', 'CDD', 'CDD Long', 'Fuso', 'Tronton Wingbox', 'Tronton Box'] as $t): ?>
                                    <option><?= $t ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">Nopol</label>
                            <input type="text" id="tuNopol" class="form-control form-control-sm text-uppercase"
                                placeholder="B 1234 XYZ" style="font-family:monospace;font-weight:700;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Vendor Unit</label>
                        <select id="tuVendorId" class="form-control form-control-sm">
                            <option value="">-- Vendor (opsional) --</option>
                            <?php foreach ($vendors ?? [] as $v): ?>
                                <option value="<?= $v->id ?>"><?= htmlspecialchars($v->nama_vendor) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">Driver</label>
                            <input type="text" id="tuDriver" class="form-control form-control-sm"
                                placeholder="Nama driver (opsional)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">No HP</label>
                            <input type="text" id="tuNoHp" class="form-control form-control-sm" placeholder="08xx">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">Notes Unit</label>
                        <input type="text" id="tuNotes" class="form-control form-control-sm"
                            placeholder="Catatan khusus unit ini (opsional)">
                    </div>
                    <div id="tuDupAlert" class="alert alert-danger py-2 d-none mt-3 mb-0 small"></div>
                    <div class="alert alert-info py-2 small mt-3 mb-0">
                        <i class="fas fa-info-circle"></i> Kalau Vendor + Nopol + Driver sudah diisi → status
                        <strong>Assigned</strong>. Kosong → <strong>Pending Assign</strong>.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveTambahUnit"><i
                            class="fas fa-save"></i> Tambah Unit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ASSIGN UNIT -->
    <div class="modal fade" id="modalAssignUnit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-user-check"></i> Assign Unit — <span id="auNopol"></span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small fw-bold">Vendor <span class="text-danger">*</span></label>
                        <select id="auVendorId" class="form-control">
                            <option value="">-- Pilih Vendor --</option>
                            <?php foreach ($vendors ?? [] as $v): ?>
                                <option value="<?= $v->id ?>"><?= htmlspecialchars($v->nama_vendor) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">Nopol <span class="text-danger">*</span></label>
                            <input type="text" id="auNopolInput" class="form-control text-uppercase"
                                style="font-family:monospace;font-weight:700;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">Driver <span class="text-danger">*</span></label>
                            <input type="text" id="auDriver" class="form-control">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">No HP Driver</label>
                        <input type="text" id="auNoHp" class="form-control" placeholder="08xx">
                    </div>
                    <div id="auDupAlert" class="alert alert-danger py-2 d-none mt-3 mb-0 small"></div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="auUnitId">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveAssignUnit"><i
                            class="fas fa-save"></i> Simpan Assign</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ACTIVATE UNIT -->
    <div class="modal fade" id="modalActivate" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-play"></i> Activate Unit — <span id="actNopol"></span></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="small fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" id="actDate" class="form-control form-control-sm">
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">Jam</label>
                        <input type="time" id="actTime" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="actUnitId">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success btn-sm" id="btnSaveActivate"><i
                            class="fas fa-check"></i> Aktivasi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: RETURN UNIT -->
    <div class="modal fade" id="modalReturn" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-undo"></i> Return Unit — <span id="retNopol"></span></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="small fw-bold">Tanggal Return <span class="text-danger">*</span></label>
                        <input type="date" id="retDate" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">Jam</label>
                        <input type="time" id="retTime" class="form-control form-control-sm">
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">Catatan Overrun</label>
                        <input type="text" id="retOverrunNotes" class="form-control form-control-sm"
                            placeholder="Alasan jika terlambat return...">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="retUnitId">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning btn-sm text-white" id="btnSaveReturn"><i
                            class="fas fa-check"></i> Konfirmasi Return</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: EXTEND UNIT -->
    <div class="modal fade" id="modalExtendUnit" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-calendar-plus"></i> Extend Unit — <span
                            id="exuNopol"></span></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">End date saat ini: <strong id="exuCurrentEnd"></strong></p>
                    <div class="mb-2">
                        <label class="small fw-bold">End Date Baru <span class="text-danger">*</span></label>
                        <input type="date" id="exuNewEnd" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">Jam</label>
                        <input type="time" id="exuNewTime" class="form-control form-control-sm">
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">Alasan</label>
                        <input type="text" id="exuReason" class="form-control form-control-sm"
                            placeholder="Alasan perpanjangan...">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="exuUnitId">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning btn-sm text-white" id="btnSaveExtendUnit"><i
                            class="fas fa-check"></i> Extend</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: EXTEND ORDER -->
    <div class="modal fade" id="modalExtendOrder" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-calendar-plus"></i> Perpanjang Semua Unit</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">End date order saat ini:
                        <strong><?= $fmt_date($end_date) ?></strong></p>
                    <div class="mb-2">
                        <label class="small fw-bold">End Date Baru <span class="text-danger">*</span></label>
                        <input type="date" id="exoNewEnd" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">Jam</label>
                        <input type="time" id="exoNewTime" class="form-control form-control-sm">
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">Alasan</label>
                        <input type="text" id="exoReason" class="form-control form-control-sm"
                            placeholder="Alasan perpanjangan...">
                    </div>
                    <div class="alert alert-info py-2 small mt-3 mb-0">
                        Semua unit yang masih <strong>Active / Extended</strong> akan ikut diperpanjang.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning btn-sm text-white" id="btnSaveExtendOrder"><i
                            class="fas fa-check"></i> Perpanjang</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: CATAT LOKASI -->
    <div class="modal fade" id="modalLokasi" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-info text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-map-marker-alt"></i> Catat Lokasi — <span
                            id="lokNopol"></span></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="small fw-bold">Lokasi Saat Ini <span class="text-danger">*</span></label>
                        <input type="text" id="lokLocation" class="form-control form-control-sm"
                            placeholder="Contoh: Area Cikarang Barat">
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">Catatan</label>
                        <input type="text" id="lokNotes" class="form-control form-control-sm" placeholder="Opsional">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="lokUnitId">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info btn-sm text-white" id="btnSaveLokasi"><i
                            class="fas fa-check"></i> Simpan Lokasi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: GANTI DRIVER -->
    <div class="modal fade" id="modalGantiDriver" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2" style="background:#6f42c1;">
                    <h6 class="modal-title text-white"><i class="fas fa-user-cog"></i> Ganti Driver — <span
                            id="gdNopol"></span></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Driver saat ini: <strong id="gdCurrentDriver"></strong></p>
                    <div class="mb-2">
                        <label class="small fw-bold">Driver Baru <span class="text-danger">*</span></label>
                        <input type="text" id="gdNewDriver" class="form-control form-control-sm"
                            placeholder="Nama driver baru">
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">No HP Baru</label>
                        <input type="text" id="gdNewNoHp" class="form-control form-control-sm" placeholder="08xx">
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">Alasan Ganti</label>
                        <input type="text" id="gdReason" class="form-control form-control-sm"
                            placeholder="Sakit, cuti, dsb...">
                    </div>
                    <div id="gdDupAlert" class="alert alert-danger py-2 d-none mt-3 mb-0 small"></div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="gdUnitId">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm text-white" id="btnSaveGantiDriver"
                        style="background:#6f42c1;"><i class="fas fa-check"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: CANCEL UNIT -->
    <div class="modal fade" id="modalCancelUnit" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-ban"></i> Cancel Unit — <span id="cuNopol"></span></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-0">
                        <label class="small fw-bold">Alasan Cancel</label>
                        <input type="text" id="cuReason" class="form-control form-control-sm"
                            placeholder="Alasan cancel unit ini...">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="cuUnitId">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnSaveCancelUnit"><i
                            class="fas fa-ban"></i> Cancel Unit</button>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {
            const BASE = '<?= base_url() ?>';
            const RENT_ID = <?= (int) $rent->id ?>;
            const CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
            let CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';

            $(document).ajaxSend(function (e, xhr, settings) {
                if (settings.type && settings.type.toUpperCase() === 'POST') {
                    if (typeof settings.data === 'string') settings.data += '&' + CSRF_NAME + '=' + CSRF_HASH;
                    else if (typeof settings.data === 'object' && settings.data !== null) settings.data[CSRF_NAME] = CSRF_HASH;
                    else settings.data = CSRF_NAME + '=' + CSRF_HASH;
                }
            });
            $(document).ajaxSuccess(function (e, xhr) {
                try { let r = JSON.parse(xhr.responseText); if (r && r.csrf_hash) CSRF_HASH = r.csrf_hash; } catch (err) { }
            });

            function doAjax(url, data, modalId, reloadPage) {
                $.ajax({
                    url, method: 'POST', data, dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        if (modalId) { let el = document.getElementById(modalId); if (el) bootstrap.Modal.getInstance(el)?.hide(); }
                        if (res.success) {
                            if (reloadPage) location.reload();
                        } else { alert(res.message || 'Gagal!'); }
                    },
                    error: function (xhr) { alert('Gagal koneksi! (' + xhr.status + ')'); }
                });
            }

            // ── TAMBAH UNIT ──
            $('#btnTambahUnit').on('click', function () {
                $('#tuNopol,#tuDriver,#tuNoHp,#tuNotes').val('');
                $('#tuTruckType,#tuVendorId').val('');
                $('#tuDupAlert').addClass('d-none').html('');
                new bootstrap.Modal(document.getElementById('modalTambahUnit')).show();
            });
            $('#tuNopol').on('input', function () { $(this).val($(this).val().toUpperCase()); });

            $('#btnSaveTambahUnit').on('click', function () {
                let nopol = $('#tuNopol').val().trim();
                let driver = $('#tuDriver').val().trim();
                if (!nopol) { alert('Nopol wajib diisi!'); return; }

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                $.ajax({
                    url: BASE + 'daily_rent/cek_duplikasi', method: 'POST',
                    data: { nopol, driver, unit_id: 0 }, dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        $('#btnSaveTambahUnit').prop('disabled', false).html('<i class="fas fa-save"></i> Tambah Unit');
                        if (res.ada_duplikasi) {
                            let msg = '<strong>⚠️ Duplikasi!</strong>';
                            if (res.pesan.nopol) msg += '<br>' + res.pesan.nopol;
                            if (res.pesan.driver) msg += '<br>' + res.pesan.driver;
                            $('#tuDupAlert').html(msg).removeClass('d-none'); return;
                        }
                        $('#tuDupAlert').addClass('d-none');
                        doAjax(BASE + 'daily_rent/aksi_tambah_unit', {
                            rent_id: RENT_ID, truck_type: $('#tuTruckType').val(),
                            nopol, vendor_id: $('#tuVendorId').val(),
                            driver, no_hp: $('#tuNoHp').val(), notes: $('#tuNotes').val()
                        }, 'modalTambahUnit', true);
                    },
                    error: function () { $('#btnSaveTambahUnit').prop('disabled', false).html('<i class="fas fa-save"></i> Tambah Unit'); alert('Gagal cek duplikasi!'); }
                });
            });

            // ── ASSIGN UNIT ──
            $(document).on('click', '.btn-assign-unit', function () {
                $('#auUnitId').val($(this).data('id'));
                $('#auNopol').text($(this).data('nopol'));
                $('#auNopolInput').val($(this).data('nopol'));
                $('#auVendorId').val($(this).data('vendor'));
                $('#auDriver').val($(this).data('driver'));
                $('#auNoHp').val($(this).data('nohp'));
                $('#auDupAlert').addClass('d-none').html('');
                new bootstrap.Modal(document.getElementById('modalAssignUnit')).show();
            });
            $('#auNopolInput').on('input', function () { $(this).val($(this).val().toUpperCase()); });

            $('#btnSaveAssignUnit').on('click', function () {
                let nopol = $('#auNopolInput').val().trim();
                let driver = $('#auDriver').val().trim();
                let vendor = $('#auVendorId').val();
                let unit_id = $('#auUnitId').val();
                if (!vendor || !nopol || !driver) { alert('Vendor, Nopol, dan Driver wajib!'); return; }

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                $.ajax({
                    url: BASE + 'daily_rent/cek_duplikasi', method: 'POST',
                    data: { nopol, driver, unit_id }, dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        $('#btnSaveAssignUnit').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Assign');
                        if (res.ada_duplikasi) {
                            let msg = '<strong>⚠️ Duplikasi!</strong>';
                            if (res.pesan.nopol) msg += '<br>' + res.pesan.nopol;
                            if (res.pesan.driver) msg += '<br>' + res.pesan.driver;
                            $('#auDupAlert').html(msg).removeClass('d-none'); return;
                        }
                        $('#auDupAlert').addClass('d-none');
                        doAjax(BASE + 'daily_rent/aksi_assign_unit',
                            { unit_id, vendor_id: vendor, nopol, driver, no_hp: $('#auNoHp').val() },
                            'modalAssignUnit', true);
                    },
                    error: function () { $('#btnSaveAssignUnit').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Assign'); }
                });
            });

            // ── ACTIVATE ──
            $(document).on('click', '.btn-activate-unit', function () {
                $('#actUnitId').val($(this).data('id'));
                $('#actNopol').text($(this).data('nopol'));
                let now = new Date();
                $('#actDate').val(now.toISOString().split('T')[0]);
                $('#actTime').val(now.toTimeString().substr(0, 5));
                new bootstrap.Modal(document.getElementById('modalActivate')).show();
            });
            $('#btnSaveActivate').on('click', function () {
                if (!$('#actDate').val()) { alert('Tanggal wajib!'); return; }
                doAjax(BASE + 'daily_rent/aksi_activate_unit',
                    { unit_id: $('#actUnitId').val(), start_date: $('#actDate').val(), start_time: $('#actTime').val() },
                    'modalActivate', true);
            });

            // ── RETURN UNIT ──
            $(document).on('click', '.btn-return-unit', function () {
                $('#retUnitId').val($(this).data('id'));
                $('#retNopol').text($(this).data('nopol'));
                $('#retOverrunNotes').val('');
                let now = new Date();
                $('#retDate').val(now.toISOString().split('T')[0]);
                $('#retTime').val(now.toTimeString().substr(0, 5));
                new bootstrap.Modal(document.getElementById('modalReturn')).show();
            });
            $('#btnSaveReturn').on('click', function () {
                if (!$('#retDate').val()) { alert('Tanggal return wajib!'); return; }
                doAjax(BASE + 'daily_rent/aksi_return_unit',
                    { unit_id: $('#retUnitId').val(), return_date: $('#retDate').val(), return_time: $('#retTime').val(), overrun_notes: $('#retOverrunNotes').val() },
                    'modalReturn', true);
            });

            // ── EXTEND UNIT ──
            $(document).on('click', '.btn-extend-unit', function () {
                $('#exuUnitId').val($(this).data('id'));
                $('#exuNopol').text($(this).data('nopol'));
                let ed = $(this).data('enddate');
                $('#exuCurrentEnd').text(ed ? ed.split('-').reverse().join('/') : '-');
                $('#exuNewEnd').val(''); $('#exuNewTime').val(''); $('#exuReason').val('');
                new bootstrap.Modal(document.getElementById('modalExtendUnit')).show();
            });
            $('#btnSaveExtendUnit').on('click', function () {
                if (!$('#exuNewEnd').val()) { alert('Tanggal end baru wajib!'); return; }
                doAjax(BASE + 'daily_rent/aksi_extend_unit',
                    { unit_id: $('#exuUnitId').val(), new_end_date: $('#exuNewEnd').val(), new_end_time: $('#exuNewTime').val(), reason: $('#exuReason').val() },
                    'modalExtendUnit', true);
            });

            // ── EXTEND ORDER ──
            $('#btnExtendOrder').on('click', function () {
                $('#exoNewEnd').val(''); $('#exoNewTime').val(''); $('#exoReason').val('');
                new bootstrap.Modal(document.getElementById('modalExtendOrder')).show();
            });
            $('#btnSaveExtendOrder').on('click', function () {
                if (!$('#exoNewEnd').val()) { alert('Tanggal end baru wajib!'); return; }
                doAjax(BASE + 'daily_rent/aksi_extend_order',
                    { rent_id: RENT_ID, new_end_date: $('#exoNewEnd').val(), new_end_time: $('#exoNewTime').val(), reason: $('#exoReason').val() },
                    'modalExtendOrder', true);
            });

            // ── CATAT LOKASI ──
            $(document).on('click', '.btn-catat-lokasi', function () {
                $('#lokUnitId').val($(this).data('id'));
                $('#lokNopol').text($(this).data('nopol'));
                $('#lokLocation').val(''); $('#lokNotes').val('');
                new bootstrap.Modal(document.getElementById('modalLokasi')).show();
            });
            $('#btnSaveLokasi').on('click', function () {
                if (!$('#lokLocation').val().trim()) { alert('Lokasi wajib diisi!'); return; }
                doAjax(BASE + 'daily_rent/aksi_catat_lokasi',
                    { unit_id: $('#lokUnitId').val(), location: $('#lokLocation').val(), notes: $('#lokNotes').val() },
                    'modalLokasi', true);
            });

            // ── GANTI DRIVER ──
            $(document).on('click', '.btn-ganti-driver', function () {
                $('#gdUnitId').val($(this).data('id'));
                $('#gdNopol').text($(this).data('nopol'));
                $('#gdCurrentDriver').text($(this).data('driver') || '-');
                $('#gdNewDriver,#gdNewNoHp,#gdReason').val('');
                $('#gdDupAlert').addClass('d-none').html('');
                new bootstrap.Modal(document.getElementById('modalGantiDriver')).show();
            });
            $('#btnSaveGantiDriver').on('click', function () {
                let nd = $('#gdNewDriver').val().trim();
                if (!nd) { alert('Driver baru wajib!'); return; }
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                $.ajax({
                    url: BASE + 'daily_rent/cek_duplikasi', method: 'POST',
                    data: { nopol: '', driver: nd, unit_id: $('#gdUnitId').val() }, dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        $('#btnSaveGantiDriver').prop('disabled', false).html('<i class="fas fa-check"></i> Simpan');
                        if (res.ada_duplikasi && res.pesan.driver) {
                            $('#gdDupAlert').html('<strong>⚠️</strong> ' + res.pesan.driver).removeClass('d-none'); return;
                        }
                        $('#gdDupAlert').addClass('d-none');
                        doAjax(BASE + 'daily_rent/aksi_ganti_driver',
                            { unit_id: $('#gdUnitId').val(), new_driver: nd, new_no_hp: $('#gdNewNoHp').val(), reason: $('#gdReason').val() },
                            'modalGantiDriver', true);
                    },
                    error: function () { $('#btnSaveGantiDriver').prop('disabled', false).html('<i class="fas fa-check"></i> Simpan'); }
                });
            });

            // ── CANCEL UNIT ──
            $(document).on('click', '.btn-cancel-unit', function () {
                $('#cuUnitId').val($(this).data('id'));
                $('#cuNopol').text($(this).data('nopol'));
                $('#cuReason').val('');
                new bootstrap.Modal(document.getElementById('modalCancelUnit')).show();
            });
            $('#btnSaveCancelUnit').on('click', function () {
                doAjax(BASE + 'daily_rent/aksi_cancel_unit',
                    { unit_id: $('#cuUnitId').val(), reason: $('#cuReason').val() },
                    'modalCancelUnit', true);
            });

            // ── HAPUS UNIT ──
            $(document).on('click', '.btn-hapus-unit', function () {
                if (!confirm('Hapus unit ' + $(this).data('nopol') + '?')) return;
                doAjax(BASE + 'daily_rent/aksi_hapus_unit', { unit_id: $(this).data('id') }, null, true);
            });

            setTimeout(function () { $('.alert').fadeOut('slow'); }, 5000);
        });
    </script>
</body>

</html>