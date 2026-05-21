<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .badge-purple {
            background-color: #6f42c1;
            color: #fff;
        }

        .badge-status {
            font-size: 0.78rem;
            padding: 5px 14px;
            font-weight: 600;
            border-radius: 20px;
        }

        .detail-card {
            border-radius: 10px;
            border: 1px solid #e3e6f0;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58, 59, 69, .1);
        }

        .detail-card .card-header {
            border-radius: 10px 10px 0 0;
            padding: 10px 18px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #f0f2f5;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            width: 140px;
            flex-shrink: 0;
            font-size: 0.78rem;
            color: #858796;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .info-value {
            flex: 1;
            font-size: 0.88rem;
            color: #3a3b45;
            font-weight: 500;
        }

        .info-value.mono {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #4e73df;
            font-size: 1rem;
        }

        .route-visual {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 0;
            flex-wrap: wrap;
        }

        .route-point {
            text-align: center;
            flex: 1;
            min-width: 80px;
        }

        .route-point .city {
            font-size: 1rem;
            font-weight: 700;
            color: #3a3b45;
        }

        .route-point .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #858796;
            margin-top: 2px;
        }

        .route-arrow {
            font-size: 1.5rem;
            color: #d1d3e2;
            flex-shrink: 0;
        }

        .timeline {
            position: relative;
            padding: 0;
            list-style: none;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 28px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e3e6f0;
        }

        .tl-item {
            position: relative;
            padding: 0 0 20px 62px;
        }

        .tl-item:last-child {
            padding-bottom: 0;
        }

        .tl-icon {
            position: absolute;
            left: 14px;
            top: 2px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e3e6f0;
            z-index: 1;
        }

        .tl-item.done .tl-icon {
            background: #1cc88a;
            color: #fff;
            box-shadow: 0 0 0 2px #1cc88a;
        }

        .tl-item.current .tl-icon {
            background: #4e73df;
            color: #fff;
            box-shadow: 0 0 0 2px #4e73df;
            animation: pulse-ring 1.5s infinite;
        }

        .tl-item.pending .tl-icon {
            background: #f8f9fc;
            color: #b7b9cc;
        }

        @keyframes pulse-ring {
            0% {
                box-shadow: 0 0 0 2px #4e73df;
            }

            50% {
                box-shadow: 0 0 0 5px rgba(78, 115, 223, .3);
            }

            100% {
                box-shadow: 0 0 0 2px #4e73df;
            }
        }

        .tl-content {
            background: #f8f9fc;
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid #e3e6f0;
        }

        .tl-item.done .tl-content {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .tl-item.current .tl-content {
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .tl-item.pending .tl-content {
            opacity: 0.6;
        }

        .tl-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #3a3b45;
        }

        .tl-item.done .tl-title {
            color: #166534;
        }

        .tl-item.current .tl-title {
            color: #1e40af;
        }

        .tl-item.pending .tl-title {
            color: #9ca3af;
        }

        .tl-time {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 3px;
        }

        .tl-time.actual {
            color: #059669;
            font-weight: 600;
        }

        .tl-time.target {
            color: #9ca3af;
            font-style: italic;
        }

        .tl-badge-current {
            display: inline-block;
            font-size: 0.65rem;
            background: #4e73df;
            color: #fff;
            border-radius: 10px;
            padding: 1px 8px;
            margin-left: 6px;
            vertical-align: middle;
        }

        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: #fff;
        }

        .btn-purple:hover {
            background-color: #5a32a3;
            color: #fff;
        }

        @media print {

            .sidebar,
            .topbar,
            .btn,
            .no-print {
                display: none !important;
            }

            #content-wrapper {
                margin-left: 0 !important;
            }

            .detail-card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
            }
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
                    $s = $shipment;
                    $level = $this->session->userdata('login')['user_level'] ?? '';
                    $can_edit = in_array($level, ['superadmin', 'admin_operational', 'operational_staff']);
                    $can_delete = in_array($level, ['superadmin', 'admin_operational']);
                    $can_manual = in_array($level, ['superadmin', 'admin_operational']);

                    $status = $s->status_shipment ?? 'Sourcing Vendor';
                    $status_colors = [
                        'Sourcing Vendor' => 'dark',
                        'Scheduled' => 'secondary',
                        'Tiba di Lokasi Muat' => 'warning',
                        'Loading' => 'info',
                        'On Trip' => 'primary',
                        'Tiba di Lokasi Bongkar' => 'purple',
                        'Completed' => 'success',
                        'Cancelled' => 'danger',
                    ];
                    $status_color = $status_colors[$status] ?? 'secondary';

                    $flow_steps = [
                        ['key' => 'sourcing', 'label' => 'Sourcing Vendor', 'icon' => 'fa-search', 'status_val' => 'Sourcing Vendor', 'date_field' => null, 'time_field' => null],
                        ['key' => 'scheduled', 'label' => 'Scheduled (Vendor Assigned)', 'icon' => 'fa-calendar-check', 'status_val' => 'Scheduled', 'date_field' => null, 'time_field' => null],
                        ['key' => 'tiba_muat', 'label' => 'Tiba di Lokasi Muat', 'icon' => 'fa-map-marker-alt', 'status_val' => 'Tiba di Lokasi Muat', 'date_field' => 'actual_tiba_muat_date', 'time_field' => 'actual_tiba_muat_time'],
                        ['key' => 'loading', 'label' => 'Loading', 'icon' => 'fa-boxes', 'status_val' => 'Loading', 'date_field' => 'actual_loading_date', 'time_field' => 'actual_loading_time'],
                        ['key' => 'depart', 'label' => 'Depart / On Trip', 'icon' => 'fa-truck-moving', 'status_val' => 'On Trip', 'date_field' => 'actual_depart_date', 'time_field' => 'actual_depart_time'],
                        ['key' => 'tiba_bongkar', 'label' => 'Tiba di Lokasi Bongkar', 'icon' => 'fa-warehouse', 'status_val' => 'Tiba di Lokasi Bongkar', 'date_field' => 'actual_tiba_bongkar_date', 'time_field' => 'actual_tiba_bongkar_time'],
                        ['key' => 'done', 'label' => 'Completed', 'icon' => 'fa-check-circle', 'status_val' => 'Completed', 'date_field' => 'actual_done_at', 'time_field' => null],
                    ];

                    $status_order = ['Sourcing Vendor', 'Scheduled', 'Tiba di Lokasi Muat', 'Loading', 'On Trip', 'Tiba di Lokasi Bongkar', 'Completed'];
                    $current_idx = array_search($status, $status_order);
                    ?>

                    <!-- HEADER -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">
                                <i class="fas fa-truck text-primary"></i> Detail Shipment &nbsp;
                                <span
                                    style="font-family:monospace; color:#4e73df;"><?= htmlspecialchars($s->no_shipment) ?></span>
                            </h1>
                            <div class="mt-1">
                                <span
                                    class="badge badge-status badge-<?= $status_color ?>"><?= htmlspecialchars($status) ?></span>
                                <?php if ($status === 'Cancelled'): ?>
                                    <span class="badge badge-danger ml-1"><i class="fas fa-ban"></i> Dibatalkan</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex gap-2 no-print">
                            <button onclick="window.print()" class="btn btn-light btn-sm shadow-sm">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <?php if ($can_edit): ?>
                                <a href="<?= base_url('ftl_non_spx/ubah/' . $s->id) ?>"
                                    class="btn btn-success btn-sm shadow-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            <?php endif; ?>
                            <a href="<?= base_url('ftl_non_spx') ?>" class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <!-- FLASH ALERTS -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif; ?>

                    <div class="row">

                        <!-- ══════════════ KOLOM KIRI ══════════════ -->
                        <div class="col-xl-8">

                            <!-- INFO SHIPMENT -->
                            <div class="card detail-card mb-4">
                                <div class="card-header bg-gradient-primary text-white">
                                    <i class="fas fa-file-alt"></i> Informasi Shipment
                                </div>
                                <div class="card-body">

                                    <!-- ROUTE VISUAL — origin2 ditambahkan di sini -->
                                    <div class="route-visual mb-3">
                                        <div class="route-point">
                                            <div class="city"><?= htmlspecialchars($s->origin ?: '-') ?></div>
                                            <div class="label">
                                                <i class="fas fa-circle text-danger" style="font-size:8px;"></i> Origin
                                                1
                                            </div>
                                        </div>

                                        <?php if (!empty($s->origin2)): ?>
                                            <div class="route-arrow" style="color:#74b9ff;">→</div>
                                            <div class="route-point">
                                                <div class="city" style="color:#0984e3;">
                                                    <?= htmlspecialchars($s->origin2) ?></div>
                                                <div class="label">Origin 2</div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="route-arrow">→</div>
                                        <div class="route-point">
                                            <div class="city text-success"><?= htmlspecialchars($s->dest1 ?: '-') ?>
                                            </div>
                                            <div class="label">
                                                <i class="fas fa-flag-checkered" style="font-size:8px;"></i> Dest 1
                                            </div>
                                        </div>

                                        <?php if (!empty($s->dest2)): ?>
                                            <div class="route-arrow" style="color:#6f42c1;">→</div>
                                            <div class="route-point">
                                                <div class="city" style="color:#6f42c1;"><?= htmlspecialchars($s->dest2) ?>
                                                </div>
                                                <div class="label">Dest 2</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <hr class="my-2">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">No Shipment</div>
                                                <div class="info-value mono"><?= htmlspecialchars($s->no_shipment) ?>
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Customer</div>
                                                <div class="info-value">
                                                    <?= htmlspecialchars($s->nama_customer ?? '-') ?></div>
                                            </div>
                                            <!-- Origin 1 -->
                                            <div class="info-row">
                                                <div class="info-label">Origin 1</div>
                                                <div class="info-value"><?= htmlspecialchars($s->origin ?: '-') ?></div>
                                            </div>
                                            <!-- Origin 2 — hanya tampil jika ada isinya -->
                                            <?php if (!empty($s->origin2)): ?>
                                                <div class="info-row">
                                                    <div class="info-label">Origin 2</div>
                                                    <div class="info-value" style="color:#0984e3;">
                                                        <?= htmlspecialchars($s->origin2) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="info-row">
                                                <div class="info-label">Truck Type</div>
                                                <div class="info-value">
                                                    <?php if (!empty($s->truck_type)): ?>
                                                        <span
                                                            class="badge badge-dark"><?= htmlspecialchars($s->truck_type) ?></span>
                                                    <?php else: ?> - <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Status</div>
                                                <div class="info-value">
                                                    <span
                                                        class="badge badge-status badge-<?= $status_color ?>"><?= htmlspecialchars($status) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">Target Standby</div>
                                                <div class="info-value">
                                                    <?php if (!empty($s->target_standby_date)): ?>
                                                        <i class="fas fa-calendar text-primary" style="font-size:11px;"></i>
                                                        <?= date('d/m/Y', strtotime($s->target_standby_date)) ?>
                                                        <?php if (!empty($s->target_standby_time)): ?>
                                                            <span class="text-muted ml-1">
                                                                <i class="fas fa-clock" style="font-size:10px;"></i>
                                                                <?= substr($s->target_standby_time, 0, 5) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php else: ?> - <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Target Arrival</div>
                                                <div class="info-value">
                                                    <?php if (!empty($s->target_arrival_date)): ?>
                                                        <i class="fas fa-calendar-check text-success"
                                                            style="font-size:11px;"></i>
                                                        <?= date('d/m/Y', strtotime($s->target_arrival_date)) ?>
                                                        <?php if (!empty($s->target_arrival_time)): ?>
                                                            <span class="text-muted ml-1">
                                                                <i class="fas fa-clock" style="font-size:10px;"></i>
                                                                <?= substr($s->target_arrival_time, 0, 5) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php else: ?> - <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Created At</div>
                                                <div class="info-value text-muted" style="font-size:0.8rem;">
                                                    <?= !empty($s->created_at) ? date('d/m/Y H:i', strtotime($s->created_at)) : '-' ?>
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Updated At</div>
                                                <div class="info-value text-muted" style="font-size:0.8rem;">
                                                    <?= !empty($s->updated_at) ? date('d/m/Y H:i', strtotime($s->updated_at)) : '-' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($s->notes)): ?>
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small class="text-muted font-weight-bold text-uppercase">
                                                <i class="fas fa-sticky-note"></i> Notes:
                                            </small>
                                            <div class="mt-1"><?= nl2br(htmlspecialchars($s->notes)) ?></div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($status === 'Cancelled' && !empty($s->cancel_reason)): ?>
                                        <div class="mt-2 p-2 rounded"
                                            style="background:#fff5f5; border-left:4px solid #dc3545;">
                                            <small class="text-danger font-weight-bold text-uppercase">
                                                <i class="fas fa-ban"></i> Alasan Cancel:
                                            </small>
                                            <div class="mt-1 text-danger"><?= nl2br(htmlspecialchars($s->cancel_reason)) ?>
                                            </div>
                                            <?php if (!empty($s->cancelled_by)): ?>
                                                <small class="text-muted">
                                                    oleh: <strong><?= htmlspecialchars($s->cancelled_by) ?></strong>
                                                    <?php if (!empty($s->cancelled_at)): ?>
                                                        — <?= date('d/m/Y H:i', strtotime($s->cancelled_at)) ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($status === 'Completed' && !empty($s->done_notes)): ?>
                                        <div class="mt-2 p-2 rounded"
                                            style="background:#f0fdf4; border-left:4px solid #1cc88a;">
                                            <small class="text-success font-weight-bold text-uppercase">
                                                <i class="fas fa-check-circle"></i> Catatan Selesai:
                                            </small>
                                            <div class="mt-1 text-success"><?= nl2br(htmlspecialchars($s->done_notes)) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>

                            <!-- VENDOR & KENDARAAN -->
                            <div class="card detail-card mb-4">
                                <div class="card-header bg-success text-white">
                                    <i class="fas fa-truck"></i> Vendor & Kendaraan
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">Vendor</div>
                                                <div class="info-value">
                                                    <?php if (!empty($s->nama_vendor)): ?>
                                                        <i class="fas fa-building text-success" style="font-size:11px;"></i>
                                                        <?= htmlspecialchars($s->nama_vendor) ?>
                                                    <?php else: ?>
                                                        <span class="text-warning"><i
                                                                class="fas fa-exclamation-triangle"></i> Belum diisi</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Nopol</div>
                                                <div class="info-value">
                                                    <?php if (!empty($s->nopol)): ?>
                                                        <span
                                                            style="font-family:monospace; font-weight:700; font-size:1rem; background:#f0f2f5; padding:2px 10px; border-radius:4px; border:1px solid #d1d3e2;">
                                                            <?= htmlspecialchars($s->nopol) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-warning"><i
                                                                class="fas fa-exclamation-triangle"></i> Belum diisi</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">Driver</div>
                                                <div class="info-value">
                                                    <?php if (!empty($s->driver)): ?>
                                                        <i class="fas fa-user text-primary" style="font-size:11px;"></i>
                                                        <?= htmlspecialchars($s->driver) ?>
                                                    <?php else: ?>
                                                        <span class="text-warning"><i
                                                                class="fas fa-exclamation-triangle"></i> Belum diisi</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">No HP Driver</div>
                                                <div class="info-value">
                                                    <?php if (!empty($s->no_hp)): ?>
                                                        <a href="tel:<?= htmlspecialchars($s->no_hp) ?>" class="text-dark">
                                                            <i class="fas fa-phone text-success"
                                                                style="font-size:11px;"></i>
                                                            <?= htmlspecialchars($s->no_hp) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-warning"><i
                                                                class="fas fa-exclamation-triangle"></i> Belum diisi</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- ══════════════ KOLOM KANAN ══════════════ -->
                        <div class="col-xl-4">

                            <!-- AKSI KONTEKSTUAL -->
                            <?php if ($can_edit && !in_array($status, ['Completed', 'Cancelled'])): ?>
                                <div class="card detail-card mb-4 no-print">
                                    <div class="card-header bg-warning text-white">
                                        <i class="fas fa-bolt"></i> Aksi Selanjutnya
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $flow_actions = [
                                            'Sourcing Vendor' => ['btn' => 'btn-primary', 'aksi' => 'assign_vendor', 'icon' => 'fa-truck', 'label' => 'Assign Vendor → Scheduled'],
                                            'Scheduled' => ['btn' => 'btn-warning', 'aksi' => 'tiba_muat', 'icon' => 'fa-map-marker-alt', 'label' => 'Tiba di Lokasi Muat'],
                                            'Tiba di Lokasi Muat' => ['btn' => 'btn-info', 'aksi' => 'loading', 'icon' => 'fa-boxes', 'label' => 'Loading'],
                                            'Loading' => ['btn' => 'btn-primary', 'aksi' => 'depart', 'icon' => 'fa-truck-moving', 'label' => 'Depart / On Trip'],
                                            'On Trip' => ['btn' => 'btn-purple', 'aksi' => 'tiba_bongkar', 'icon' => 'fa-warehouse', 'label' => 'Tiba di Lokasi Bongkar'],
                                            'Tiba di Lokasi Bongkar' => ['btn' => 'btn-success', 'aksi' => 'done', 'icon' => 'fa-check-circle', 'label' => 'Shipment Done'],
                                        ];
                                        if (isset($flow_actions[$status])):
                                            $act = $flow_actions[$status];
                                            ?>
                                            <button class="btn <?= $act['btn'] ?> btn-block mb-2 btn-flow"
                                                data-id="<?= $s->id ?>" data-no="<?= htmlspecialchars($s->no_shipment) ?>"
                                                data-aksi="<?= $act['aksi'] ?>"
                                                data-vendor="<?= htmlspecialchars($s->vendor_id ?? '') ?>"
                                                data-nopol="<?= htmlspecialchars($s->nopol ?? '') ?>"
                                                data-driver="<?= htmlspecialchars($s->driver ?? '') ?>"
                                                data-nohp="<?= htmlspecialchars($s->no_hp ?? '') ?>">
                                                <i class="fas <?= $act['icon'] ?>"></i> <?= $act['label'] ?>
                                            </button>
                                        <?php endif; ?>
                                        <div class="d-flex gap-2">
                                            <?php if ($can_manual): ?>
                                                <button class="btn btn-outline-secondary btn-sm flex-fill btn-manual-status"
                                                    data-id="<?= $s->id ?>" data-no="<?= htmlspecialchars($s->no_shipment) ?>"
                                                    data-status="<?= htmlspecialchars($status) ?>">
                                                    <i class="fas fa-exchange-alt"></i> Manual Status
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-danger btn-sm flex-fill btn-cancel"
                                                data-id="<?= $s->id ?>" data-no="<?= htmlspecialchars($s->no_shipment) ?>">
                                                <i class="fas fa-ban"></i> Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- TIMELINE -->
                            <div class="card detail-card mb-4">
                                <div class="card-header bg-gradient-info text-white">
                                    <i class="fas fa-stream"></i> Progress Timeline
                                </div>
                                <div class="card-body">
                                    <ul class="timeline">
                                        <?php foreach ($flow_steps as $step):
                                            $step_idx = array_search($step['status_val'], $status_order);

                                            if ($status === 'Cancelled') {
                                                $state = 'pending';
                                            } elseif ($status === 'Completed') {
                                                $state = 'done';
                                            } elseif ($step['status_val'] === $status) {
                                                $state = 'current';
                                            } elseif ($step_idx !== false && $current_idx !== false && $step_idx < $current_idx) {
                                                $state = 'done';
                                            } else {
                                                $state = 'pending';
                                            }

                                            $actual_str = '';
                                            if (!empty($step['date_field'])) {
                                                $df = $step['date_field'];
                                                $tf = $step['time_field'];
                                                if ($df === 'actual_done_at') {
                                                    if (!empty($s->$df))
                                                        $actual_str = date('d/m/Y H:i', strtotime($s->$df));
                                                } else {
                                                    if (!empty($s->$df)) {
                                                        $actual_str = date('d/m/Y', strtotime($s->$df));
                                                        if (!empty($tf) && !empty($s->$tf))
                                                            $actual_str .= ' ' . substr($s->$tf, 0, 5);
                                                    }
                                                }
                                            }
                                            ?>
                                            <li class="tl-item <?= $state ?>">
                                                <div class="tl-icon">
                                                    <?php if ($state === 'done'): ?>
                                                        <i class="fas fa-check"></i>
                                                    <?php elseif ($state === 'current'): ?>
                                                        <i class="fas fa-circle" style="font-size:8px;"></i>
                                                    <?php else: ?>
                                                        <i class="fas <?= $step['icon'] ?>"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="tl-content">
                                                    <div class="tl-title">
                                                        <i class="fas <?= $step['icon'] ?>"
                                                            style="font-size:10px; margin-right:4px;"></i>
                                                        <?= htmlspecialchars($step['label']) ?>
                                                        <?php if ($state === 'current'): ?>
                                                            <span class="tl-badge-current">NOW</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($actual_str): ?>
                                                        <div class="tl-time actual">
                                                            <i class="fas fa-check-circle" style="font-size:10px;"></i> Aktual:
                                                            <?= $actual_str ?>
                                                        </div>
                                                    <?php elseif ($state === 'done' || $state === 'current'): ?>
                                                        <?php if ($step['key'] === 'sourcing' && !empty($s->created_at)): ?>
                                                            <div class="tl-time actual">
                                                                <i class="fas fa-check-circle" style="font-size:10px;"></i>
                                                                <?= date('d/m/Y H:i', strtotime($s->created_at)) ?>
                                                            </div>
                                                        <?php elseif ($step['key'] === 'scheduled' && !empty($s->updated_at) && $status !== 'Sourcing Vendor'): ?>
                                                            <div class="tl-time actual">
                                                                <i class="fas fa-check-circle" style="font-size:10px;"></i>
                                                                <?= date('d/m/Y H:i', strtotime($s->updated_at)) ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="tl-time"
                                                                style="color:#9ca3af; font-style:italic; font-size:0.72rem;">Waktu
                                                                tidak direkam</div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="tl-time target">Menunggu...</div>
                                                    <?php endif; ?>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>

                                        <?php if ($status === 'Cancelled'): ?>
                                            <li class="tl-item" style="padding-bottom:0;">
                                                <div class="tl-icon"
                                                    style="background:#dc3545; color:#fff; box-shadow:0 0 0 2px #dc3545;">
                                                    <i class="fas fa-ban"></i>
                                                </div>
                                                <div class="tl-content" style="background:#fff5f5; border-color:#fecdd3;">
                                                    <div class="tl-title" style="color:#991b1b;"><i class="fas fa-ban"
                                                            style="font-size:10px;"></i> Cancelled</div>
                                                    <div class="tl-time" style="color:#dc3545;">
                                                        <?= !empty($s->updated_at) ? date('d/m/Y H:i', strtotime($s->updated_at)) : '' ?>
                                                    </div>
                                                    <?php if (!empty($s->cancel_reason)): ?>
                                                        <div class="tl-time"
                                                            style="color:#dc3545; font-style:italic; margin-top:4px;">
                                                            "<?= htmlspecialchars(mb_strimwidth($s->cancel_reason, 0, 60, '...')) ?>"
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>

                            <!-- REKAP WAKTU AKTUAL -->
                            <?php
                            $has_actual = !empty($s->actual_tiba_muat_date) || !empty($s->actual_loading_date) ||
                                !empty($s->actual_depart_date) || !empty($s->actual_tiba_bongkar_date) || !empty($s->actual_done_at);
                            ?>
                            <?php if ($has_actual): ?>
                                <div class="card detail-card mb-4">
                                    <div class="card-header bg-light text-dark">
                                        <i class="fas fa-clock text-primary"></i> Rekap Waktu Aktual
                                    </div>
                                    <div class="card-body py-2">
                                        <?php
                                        $actual_rows = [
                                            ['label' => 'Tiba di Lokasi Muat', 'df' => 'actual_tiba_muat_date', 'tf' => 'actual_tiba_muat_time'],
                                            ['label' => 'Loading', 'df' => 'actual_loading_date', 'tf' => 'actual_loading_time'],
                                            ['label' => 'Depart / On Trip', 'df' => 'actual_depart_date', 'tf' => 'actual_depart_time'],
                                            ['label' => 'Tiba di Lokasi Bongkar', 'df' => 'actual_tiba_bongkar_date', 'tf' => 'actual_tiba_bongkar_time'],
                                            ['label' => 'Completed', 'df' => 'actual_done_at', 'tf' => null],
                                        ];
                                        foreach ($actual_rows as $ar):
                                            $df = $ar['df'];
                                            $tf = $ar['tf'];
                                            if (empty($s->$df))
                                                continue;
                                            $val = $df === 'actual_done_at'
                                                ? date('d/m/Y H:i', strtotime($s->$df))
                                                : date('d/m/Y', strtotime($s->$df)) . (!empty($tf) && !empty($s->$tf) ? ' ' . substr($s->$tf, 0, 5) : '');
                                            ?>
                                            <div class="info-row">
                                                <div class="info-label" style="width:160px;"><?= $ar['label'] ?></div>
                                                <div class="info-value"
                                                    style="color:#059669; font-weight:600; font-size:0.85rem;">
                                                    <i class="fas fa-check-circle" style="font-size:10px;"></i> <?= $val ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <?php if (!empty($s->done_notes)): ?>
                                            <div class="info-row">
                                                <div class="info-label" style="width:160px;">Catatan Selesai</div>
                                                <div class="info-value" style="color:#059669; font-size:0.85rem;">
                                                    <?= nl2br(htmlspecialchars($s->done_notes)) ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- MODAL: ASSIGN VENDOR -->
    <div class="modal fade" id="modalAssignVendor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-truck"></i> Assign Vendor — <span id="avShipmentNo"></span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Vendor <span class="text-danger">*</span></label>
                        <select id="avVendorId" class="form-control">
                            <option value="">-- Pilih Vendor --</option>
                            <?php foreach ($vendors ?? [] as $v): ?>
                                <option value="<?= $v->id ?>"><?= htmlspecialchars($v->nama_vendor) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">Nopol Kendaraan <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="avNopol" class="form-control text-uppercase" placeholder="B 1234 XYZ"
                                style="font-family:monospace; font-weight:600; letter-spacing:1px;">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">Nama Driver <span class="text-danger">*</span></label>
                            <input type="text" id="avDriver" class="form-control" placeholder="Nama lengkap driver">
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">No HP Driver</label>
                        <input type="text" id="avNoHp" class="form-control" placeholder="08xxxxxxxxxx">
                    </div>
                    <div id="avDuplikasiAlert" class="alert alert-danger py-2 d-none mt-3 mb-0"></div>
                    <div class="alert alert-info py-2 small mt-3 mb-0">
                        <i class="fas fa-info-circle"></i> Status otomatis berubah ke <strong>Scheduled</strong> setelah
                        vendor di-assign.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="avShipmentId">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveAssignVendor">
                        <i class="fas fa-save"></i> Simpan & Assign
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: TIMESTAMP -->
    <div class="modal fade" id="modalTimestamp" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2 text-white" id="tsModalHeader" style="background:#4e73df;">
                    <h6 class="modal-title" id="tsModalTitle"><i class="fas fa-clock"></i> Catat Waktu</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Shipment: <strong id="tsShipmentNo"></strong></p>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" id="tsDate" class="form-control form-control-sm">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Jam</label>
                        <input type="time" id="tsTime" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="tsShipmentId">
                    <input type="hidden" id="tsAksi">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm text-white" id="btnSaveTimestamp"
                        style="background:#4e73df;">
                        <i class="fas fa-check"></i> Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: STATUS MANUAL -->
    <div class="modal fade" id="modalStatus" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-exchange-alt"></i> Ubah Status Manual</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-1">Shipment: <strong id="msShipmentNo"></strong></p>
                    <select class="form-control" id="selectStatus">
                        <option value="Sourcing Vendor">Sourcing Vendor</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Tiba di Lokasi Muat">Tiba di Lokasi Muat</option>
                        <option value="Loading">Loading</option>
                        <option value="On Trip">On Trip</option>
                        <option value="Tiba di Lokasi Bongkar">Tiba di Lokasi Bongkar</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <input type="hidden" id="msShipmentId">
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-dark btn-sm" id="btnSaveStatus">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: CANCEL -->
    <div class="modal fade" id="modalCancel" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"
                style="border-radius:10px; overflow:hidden; border:none; box-shadow:0 10px 40px rgba(0,0,0,.2);">
                <div class="modal-header py-3" style="background:linear-gradient(135deg,#dc3545,#b02a37); border:none;">
                    <div>
                        <h6 class="modal-title text-white font-weight-bold mb-0">
                            <i class="fas fa-ban mr-2"></i>Batalkan Shipment
                        </h6>
                        <small class="text-white-50" id="cancelShipmentNoLabel"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        style="opacity:.8;"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="d-flex align-items-start p-3 mb-4"
                        style="background:#fff5f5; border-radius:8px; border-left:4px solid #dc3545;">
                        <i class="fas fa-exclamation-triangle text-danger mt-1 mr-3"
                            style="font-size:1.1rem; flex-shrink:0;"></i>
                        <div>
                            <div class="font-weight-bold text-danger" style="font-size:0.88rem;">Tindakan ini tidak
                                dapat dibatalkan</div>
                            <div class="text-muted" style="font-size:0.82rem; margin-top:3px;">
                                Shipment akan berpindah ke status <strong>Cancelled</strong> dan alasan akan tersimpan
                                di sistem.
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small font-weight-bold text-uppercase text-muted mb-2">
                            <i class="fas fa-list-ul"></i> Pilih Alasan Cepat
                        </label>
                        <div class="d-flex flex-wrap" style="gap:6px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Customer request pembatalan">Customer request</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Tidak ada vendor/unit tersedia">Tidak ada unit</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Rute berubah / Dipindah ke shipment lain">Rute berubah</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Barang belum siap / Jadwal ditunda">Barang belum siap</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Double booking / Duplikat order">Double booking</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Force majeure (bencana alam, dll)">Force majeure</button>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">
                            Alasan Cancel <span class="text-danger">*</span>
                            <span class="text-muted font-weight-normal">(wajib diisi, min. 10 karakter)</span>
                        </label>
                        <textarea id="cancelReason" class="form-control" rows="3"
                            placeholder="Tuliskan alasan pembatalan shipment ini..." maxlength="500"
                            style="border-radius:6px; resize:none;"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-danger d-none" id="cancelReasonError">
                                <i class="fas fa-exclamation-circle"></i> Alasan cancel wajib diisi (min. 10 karakter)
                            </small>
                            <small class="text-muted ml-auto" id="cancelReasonCount">0/500</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2" style="border-top:1px solid #f0f2f5;">
                    <input type="hidden" id="cancelShipmentId">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnConfirmCancel">
                        <i class="fas fa-ban"></i> Ya, Batalkan Shipment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: DONE -->
    <div class="modal fade" id="modalDone" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"
                style="border-radius:10px; overflow:hidden; border:none; box-shadow:0 10px 40px rgba(0,0,0,.2);">
                <div class="modal-header py-3" style="background:linear-gradient(135deg,#1cc88a,#17a673); border:none;">
                    <div>
                        <h6 class="modal-title text-white font-weight-bold mb-0">
                            <i class="fas fa-check-circle mr-2"></i>Selesaikan Shipment
                        </h6>
                        <small class="text-white-50" id="doneShipmentNoLabel"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        style="opacity:.8;"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="d-flex align-items-start p-3 mb-4"
                        style="background:#f0fdf4; border-radius:8px; border-left:4px solid #1cc88a;">
                        <i class="fas fa-truck-loading text-success mt-1 mr-3"
                            style="font-size:1.1rem; flex-shrink:0;"></i>
                        <div>
                            <div class="font-weight-bold text-success" style="font-size:0.88rem;">Konfirmasi Shipment
                                Selesai</div>
                            <div class="text-muted" style="font-size:0.82rem; margin-top:3px;">
                                Status akan berubah ke <strong>Completed</strong>. Pastikan barang sudah diterima di
                                tujuan.
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">
                            Catatan Selesai <span class="text-muted font-weight-normal">(opsional)</span>
                        </label>
                        <textarea id="doneNotes" class="form-control" rows="3"
                            placeholder="Contoh: Barang diterima lengkap, tanda terima sudah ditandatangani..."
                            maxlength="500" style="border-radius:6px; resize:none;"></textarea>
                        <small class="text-muted d-flex justify-content-end mt-1" id="doneNotesCount">0/500</small>
                    </div>
                </div>
                <div class="modal-footer py-2" style="border-top:1px solid #f0f2f5;">
                    <input type="hidden" id="doneShipmentId">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="btnConfirmDone">
                        <i class="fas fa-check-circle"></i> Ya, Shipment Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {
            const BASE = '<?= base_url() ?>';

            const CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
            let CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';

            $(document).ajaxSend(function (e, xhr, settings) {
                if (settings.type && settings.type.toUpperCase() === 'POST') {
                    if (typeof settings.data === 'string') {
                        settings.data += '&' + CSRF_NAME + '=' + CSRF_HASH;
                    } else if (typeof settings.data === 'object' && settings.data !== null) {
                        settings.data[CSRF_NAME] = CSRF_HASH;
                    } else {
                        settings.data = CSRF_NAME + '=' + CSRF_HASH;
                    }
                }
            });

            $(document).ajaxSuccess(function (e, xhr) {
                try {
                    let resp = JSON.parse(xhr.responseText);
                    if (resp && resp.csrf_hash) CSRF_HASH = resp.csrf_hash;
                } catch (err) { }
            });

            const TS_CONFIG = {
                'tiba_muat': { title: 'Tiba di Lokasi Muat', color: '#ffc107' },
                'loading': { title: 'Loading', color: '#17a2b8' },
                'depart': { title: 'Depart / On Trip', color: '#4e73df' },
                'tiba_bongkar': { title: 'Tiba di Lokasi Bongkar', color: '#6f42c1' },
            };

            // ── FLOW BUTTON CLICK ──
            $(document).on('click', '.btn-flow', function () {
                let id = $(this).data('id');
                let no = $(this).data('no');
                let aksi = $(this).data('aksi');

                if (aksi === 'assign_vendor') {
                    $('#avShipmentId').val(id);
                    $('#avShipmentNo').text(no);
                    $('#avVendorId').val($(this).data('vendor') || '');
                    $('#avNopol').val($(this).data('nopol') || '');
                    $('#avDriver').val($(this).data('driver') || '');
                    $('#avNoHp').val($(this).data('nohp') || '');
                    $('#modalAssignVendor').modal('show');

                } else if (aksi === 'done') {
                    $('#doneShipmentId').val(id);
                    $('#doneShipmentNoLabel').text('Shipment: ' + no);
                    $('#doneNotes').val('');
                    $('#doneNotesCount').text('0/500');
                    $('#modalDone').modal('show');

                } else {
                    let cfg = TS_CONFIG[aksi];
                    if (!cfg) return;
                    $('#tsShipmentId').val(id);
                    $('#tsShipmentNo').text(no);
                    $('#tsAksi').val(aksi);
                    $('#tsModalTitle').html('<i class="fas fa-clock"></i> ' + cfg.title);
                    $('#tsModalHeader').css('background', cfg.color);
                    $('#btnSaveTimestamp').css('background', cfg.color);
                    let now = new Date();
                    $('#tsDate').val(now.toISOString().split('T')[0]);
                    $('#tsTime').val(now.toTimeString().substring(0, 5));
                    $('#modalTimestamp').modal('show');
                }
            });

            // ── ASSIGN VENDOR ──
            $('#btnSaveAssignVendor').on('click', function () {
                if (!$('#avVendorId').val()) { alert('Vendor wajib dipilih!'); return; }
                if (!$('#avNopol').val()) { alert('Nopol Kendaraan wajib diisi!'); return; }
                if (!$('#avDriver').val()) { alert('Nama Driver wajib diisi!'); return; }

                const id = $('#avShipmentId').val(), nopol = $('#avNopol').val(), driver = $('#avDriver').val();
                $('#btnSaveAssignVendor').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengecek...');

                $.ajax({
                    url: BASE + 'ftl_non_spx/cek_duplikasi',
                    method: 'POST',
                    data: { id, nopol, driver },
                    dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        $('#btnSaveAssignVendor').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan & Assign');
                        if (res.ada_duplikasi) {
                            let pesan = '<div style="font-size:0.88rem;"><p class="font-weight-bold text-danger mb-2"><i class="fas fa-exclamation-triangle"></i> Duplikasi Terdeteksi!</p>';
                            if (res.pesan.nopol) pesan += '<p class="mb-1">🚛 ' + res.pesan.nopol + '</p>';
                            if (res.pesan.driver) pesan += '<p class="mb-1">👤 ' + res.pesan.driver + '</p>';
                            pesan += '<hr class="my-2"><p class="text-muted mb-0">Driver/kendaraan yang masih aktif di shipment lain tidak bisa di-assign ulang sebelum shipment tersebut selesai (Completed/Cancelled).</p></div>';
                            $('#avDuplikasiAlert').html(pesan).removeClass('d-none');
                            return;
                        }
                        $('#avDuplikasiAlert').addClass('d-none');
                        $('#btnSaveAssignVendor').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                        $.ajax({
                            url: BASE + 'ftl_non_spx/aksi_assign_vendor',
                            method: 'POST',
                            data: { id, vendor_id: $('#avVendorId').val(), nopol, driver, no_hp: $('#avNoHp').val() },
                            dataType: 'json',
                            success: function (res) {
                                if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                                $('#btnSaveAssignVendor').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan & Assign');
                                if (res.success) {
                                    $('#modalAssignVendor').modal('hide');
                                    let alertHtml = '<div class="alert alert-success alert-dismissible fade show" id="assignAlert"><i class="fas fa-check-circle"></i> Vendor berhasil di-assign — status → <strong>Scheduled</strong>.<button type="button" class="btn-close" data-bs-dismiss="alert">×</button></div>';
                                    $('.container-fluid').prepend(alertHtml);
                                    setTimeout(function () { location.reload(); }, 1800);
                                } else { alert(res.message || 'Gagal!'); }
                            },
                            error: function (xhr) {
                                $('#btnSaveAssignVendor').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan & Assign');
                                alert('Gagal assign vendor! Status: ' + xhr.status);
                            }
                        });
                    },
                    error: function (xhr) {
                        $('#btnSaveAssignVendor').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan & Assign');
                        alert('Gagal cek duplikasi! Status: ' + xhr.status);
                    }
                });
            });

            $('#modalAssignVendor').on('show.bs.modal hidden.bs.modal', function () {
                $('#avDuplikasiAlert').addClass('d-none').html('');
            });

            // ── TIMESTAMP ──
            $('#btnSaveTimestamp').on('click', function () {
                if (!$('#tsDate').val()) { alert('Tanggal wajib diisi!'); return; }
                doAjax(BASE + 'ftl_non_spx/aksi_timestamp', {
                    id: $('#tsShipmentId').val(),
                    aksi: $('#tsAksi').val(),
                    date: $('#tsDate').val(),
                    time: $('#tsTime').val(),
                }, function () { $('#modalTimestamp').modal('hide'); location.reload(); });
            });

            // ── CANCEL ──
            $(document).on('click', '.btn-cancel', function () {
                $('#cancelShipmentId').val($(this).data('id'));
                $('#cancelShipmentNoLabel').text('Shipment: ' + $(this).data('no'));
                $('#cancelReason').val('');
                $('#cancelReasonCount').text('0/500');
                $('#cancelReasonError').addClass('d-none');
                $('.reason-chip').removeClass('active btn-secondary').addClass('btn-outline-secondary');
                $('#modalCancel').modal('show');
            });

            $(document).on('click', '.reason-chip', function () {
                var txt = $(this).data('reason');
                $('#cancelReason').val(txt);
                $('#cancelReasonCount').text(txt.length + '/500');
                $('#cancelReasonError').addClass('d-none');
                $('.reason-chip').removeClass('active btn-secondary').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('btn-secondary active');
            });

            $('#cancelReason').on('input', function () {
                $('#cancelReasonCount').text($(this).val().length + '/500');
                if ($(this).val().length >= 10) $('#cancelReasonError').addClass('d-none');
                $('.reason-chip').removeClass('active btn-secondary').addClass('btn-outline-secondary');
            });

            $('#btnConfirmCancel').on('click', function () {
                var reason = $('#cancelReason').val().trim();
                if (reason.length < 10) { $('#cancelReasonError').removeClass('d-none'); $('#cancelReason').focus(); return; }
                var id = $('#cancelShipmentId').val();
                $('#btnConfirmCancel').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Membatalkan...');
                $.ajax({
                    url: BASE + 'ftl_non_spx/aksi_cancel',
                    method: 'POST',
                    data: { id, cancel_reason: reason },
                    dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        $('#btnConfirmCancel').prop('disabled', false).html('<i class="fas fa-ban"></i> Ya, Batalkan Shipment');
                        if (res.success) { $('#modalCancel').modal('hide'); location.reload(); }
                        else { alert(res.message || 'Gagal cancel!'); }
                    },
                    error: function (xhr) {
                        $('#btnConfirmCancel').prop('disabled', false).html('<i class="fas fa-ban"></i> Ya, Batalkan Shipment');
                        alert('Gagal koneksi ke server! (' + xhr.status + ')');
                    }
                });
            });

            $('#modalCancel').on('hidden.bs.modal', function () {
                $('#btnConfirmCancel').prop('disabled', false).html('<i class="fas fa-ban"></i> Ya, Batalkan Shipment');
                $('#cancelReason').val('');
                $('#cancelReasonError').addClass('d-none');
                $('.reason-chip').removeClass('active btn-secondary').addClass('btn-outline-secondary');
            });

            // ── DONE ──
            $('#doneNotes').on('input', function () {
                $('#doneNotesCount').text($(this).val().length + '/500');
            });

            $('#btnConfirmDone').on('click', function () {
                var id = $('#doneShipmentId').val();
                var notes = $('#doneNotes').val().trim();
                $('#btnConfirmDone').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                $.ajax({
                    url: BASE + 'ftl_non_spx/aksi_done',
                    method: 'POST',
                    data: { id, done_notes: notes },
                    dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        $('#btnConfirmDone').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Ya, Shipment Selesai');
                        if (res.success) { $('#modalDone').modal('hide'); location.reload(); }
                        else { alert(res.message || 'Gagal!'); }
                    },
                    error: function (xhr) {
                        $('#btnConfirmDone').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Ya, Shipment Selesai');
                        alert('Gagal koneksi ke server! (' + xhr.status + ')');
                    }
                });
            });

            $('#modalDone').on('hidden.bs.modal', function () {
                $('#btnConfirmDone').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Ya, Shipment Selesai');
            });

            // ── MANUAL STATUS ──
            $(document).on('click', '.btn-manual-status', function () {
                $('#msShipmentId').val($(this).data('id'));
                $('#msShipmentNo').text($(this).data('no'));
                $('#selectStatus').val($(this).data('status'));
                $('#modalStatus').modal('show');
            });

            $('#btnSaveStatus').on('click', function () {
                doAjax(BASE + 'ftl_non_spx/update_status', {
                    id: $('#msShipmentId').val(),
                    status: $('#selectStatus').val(),
                }, function () { $('#modalStatus').modal('hide'); location.reload(); });
            });

            // ── helper ──
            function doAjax(url, data, onSuccess) {
                $.ajax({
                    url, method: 'POST', data, dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        if (res.success) { if (onSuccess) onSuccess(); }
                        else { alert(res.message || 'Gagal!'); }
                    },
                    error: function (xhr) {
                        alert('Gagal koneksi ke server! (' + xhr.status + ')');
                    }
                });
            }

            $('#avNopol').on('input', function () { $(this).val($(this).val().toUpperCase()); });
            setTimeout(function () { $('.alert').fadeOut('slow'); }, 5000);
        });
    </script>
</body>

</html>