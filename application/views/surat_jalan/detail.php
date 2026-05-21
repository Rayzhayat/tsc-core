<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .info-card {
            border-left: 4px solid #4e73df;
            transition: transform 0.2s;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .badge-draft {
            background: #858796;
        }

        .badge-scheduled {
            background: #4e73df;
        }

        .badge-on_trip {
            background: #f6c23e;
            color: #000;
        }

        .badge-completed {
            background: #1cc88a;
        }

        .badge-cancelled {
            background: #e74a3b;
        }

        .badge-on_time {
            background: #1cc88a;
        }

        .badge-late {
            background: #f6c23e;
            color: #000;
        }

        .badge-very_late {
            background: #e74a3b;
        }

        .header-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .status-update-form {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 3px solid #2196f3;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .status-update-form h6 {
            color: #1565c0;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .action-btn-group .btn {
            margin: 5px 0;
        }

        .nominal-display {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline-item {
            position: relative;
            padding-left: 40px;
            padding-bottom: 20px;
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e3e6f0;
        }

        .timeline-item:last-child:before {
            display: none;
        }

        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #4e73df;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            z-index: 1;
        }

        .route-arrow {
            font-size: 20px;
            color: #858796;
            margin: 0 10px;
        }

        .info-label {
            font-weight: 600;
            color: #5a5c69;
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1rem;
            color: #2c2f33;
            margin-bottom: 15px;
        }

        .biaya-row {
            border-bottom: 1px solid #e3e6f0;
            padding: 10px 0;
        }

        .biaya-row:last-child {
            border-bottom: none;
        }

        .photo-preview {
            max-width: 150px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s;
        }

        .photo-preview:hover {
            transform: scale(1.05);
        }

        .overdue-alert {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ff9800;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(255, 152, 0, 0.7);
            }

            50% {
                box-shadow: 0 0 0 10px rgba(255, 152, 0, 0);
            }
        }

        .destination-tracker {
            background: #f8f9fc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .destination-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .dest-pending {
            background: #e3e6f0;
            color: #5a5c69;
        }

        .dest-arrived {
            background: #d1ecf1;
            color: #0c5460;
        }

        .dest-loading {
            background: #fff3cd;
            color: #856404;
        }

        .dest-completed {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar') ?>

                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-file-alt text-primary"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('surat_jalan') ?>" class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <a href="<?= base_url('surat_jalan/export_pdf/' . $surat_jalan->id) ?>"
                                class="btn btn-danger btn-sm shadow-sm" target="_blank">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php if ($this->session->flashdata('warning')): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('warning') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Overdue Alert -->
                    <?php
                    $is_overdue = false;
                    if ($surat_jalan->status == 'on_trip' && $surat_jalan->target_tiba) {
                        $is_overdue = (strtotime($surat_jalan->target_tiba) < time());
                    }
                    ?>

                    <?php if ($is_overdue): ?>
                        <div class="overdue-alert">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-2">
                                        <i class="fas fa-exclamation-triangle text-danger"></i>
                                        <strong>PERHATIAN: TRIP OVERDUE!</strong>
                                    </h5>
                                    <p class="mb-0">
                                        Trip ini sudah melewati target waktu tiba!<br>
                                        <strong>Target:</strong> <?= date('d/m/Y H:i', strtotime($surat_jalan->target_tiba)) ?><br>
                                        <strong>Keterlambatan:</strong>
                                        <?= round((time() - strtotime($surat_jalan->target_tiba)) / 60) ?> menit
                                    </p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="<?= base_url('surat_jalan/complete_trip/' . $surat_jalan->id) ?>"
                                        class="btn btn-success btn-lg">
                                        <i class="fas fa-check-circle"></i> Selesaikan Trip
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row">

                        <!-- Left Column -->
                        <div class="col-lg-8">

                            <!-- Main Info Card -->
                            <div class="card shadow mb-4">
                                <div class="card-body p-0">
                                    <div class="header-gradient">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h3 class="mb-2 font-weight-bold">
                                                    <?= htmlspecialchars($surat_jalan->no_surat_jalan) ?>
                                                </h3>
                                                <p class="mb-2">
                                                    <i class="fas fa-building"></i>
                                                    <strong><?= htmlspecialchars($surat_jalan->customer) ?></strong>
                                                </p>
                                                <p class="mb-0">
                                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($surat_jalan->service) ?>
                                                    <span class="mx-2">|</span>
                                                    <i class="fas fa-clock"></i> <?= htmlspecialchars($surat_jalan->sla) ?>
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <?php
                                                $status_badge = 'badge-draft';
                                                $status_icon = 'fa-file';

                                                switch ($surat_jalan->status) {
                                                    case 'scheduled':
                                                        $status_badge = 'badge-scheduled';
                                                        $status_icon = 'fa-calendar-check';
                                                        break;
                                                    case 'on_trip':
                                                        $status_badge = 'badge-on_trip';
                                                        $status_icon = 'fa-truck';
                                                        break;
                                                    case 'completed':
                                                        $status_badge = 'badge-completed';
                                                        $status_icon = 'fa-check-circle';
                                                        break;
                                                    case 'cancelled':
                                                        $status_badge = 'badge-cancelled';
                                                        $status_icon = 'fa-times-circle';
                                                        break;
                                                }
                                                ?>
                                                <h2 class="mb-2">
                                                    <span class="badge <?= $status_badge ?> badge-lg">
                                                        <i class="fas <?= $status_icon ?>"></i> <?= strtoupper($surat_jalan->status) ?>
                                                    </span>
                                                </h2>

                                                <?php if ($surat_jalan->status == 'completed' && $surat_jalan->sla_status): ?>
                                                    <?php
                                                    $sla_badge = 'badge-on_time';
                                                    $sla_icon = 'fa-check';
                                                    $sla_text = 'ON TIME';

                                                    if ($surat_jalan->sla_status == 'late') {
                                                        $sla_badge = 'badge-late';
                                                        $sla_icon = 'fa-clock';
                                                        $sla_text = 'LATE';
                                                    } elseif ($surat_jalan->sla_status == 'very_late') {
                                                        $sla_badge = 'badge-very_late';
                                                        $sla_icon = 'fa-exclamation-triangle';
                                                        $sla_text = 'VERY LATE';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $sla_badge ?> badge-lg">
                                                        <i class="fas <?= $sla_icon ?>"></i> <?= $sla_text ?>
                                                    </span>
                                                    <?php if ($surat_jalan->keterlambatan > 0): ?>
                                                        <br><small style="color: rgba(255,255,255,0.9);">
                                                            Terlambat: <?= round($surat_jalan->keterlambatan) ?> menit
                                                        </small>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="info-label">
                                                    <i class="fas fa-calendar"></i> Tanggal
                                                </div>
                                                <div class="info-value">
                                                    <?= date('d F Y', strtotime($surat_jalan->tanggal)) ?>
                                                </div>

                                                <div class="info-label">
                                                    <i class="fas fa-route"></i> Rute
                                                </div>
                                                <div class="info-value">
                                                    <strong><?= htmlspecialchars($surat_jalan->origin) ?></strong>
                                                    <i class="fas fa-arrow-right route-arrow"></i>
                                                    <strong><?= htmlspecialchars($surat_jalan->dest1) ?></strong>
                                                    <?php if (!empty($surat_jalan->dest2)): ?>
                                                        <i class="fas fa-arrow-right route-arrow"></i>
                                                        <strong><?= htmlspecialchars($surat_jalan->dest2) ?></strong>
                                                    <?php endif; ?>
                                                    <?php if (!empty($surat_jalan->dest3)): ?>
                                                        <i class="fas fa-arrow-right route-arrow"></i>
                                                        <strong><?= htmlspecialchars($surat_jalan->dest3) ?></strong>
                                                    <?php endif; ?>
                                                    <?php if (!empty($surat_jalan->dest4)): ?>
                                                        <i class="fas fa-arrow-right route-arrow"></i>
                                                        <strong><?= htmlspecialchars($surat_jalan->dest4) ?></strong>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="info-label">
                                                    <i class="fas fa-box"></i> Muatan
                                                </div>
                                                <div class="info-value">
                                                    <?= htmlspecialchars($surat_jalan->muatan) ?>
                                                    <?php if ($surat_jalan->tonase_aktual > 0): ?>
                                                        <br><small class="text-muted">
                                                            Tonase: <?= $surat_jalan->tonase_aktual ?> ton
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="info-label">
                                                    <i class="fas fa-user"></i> Driver
                                                </div>
                                                <div class="info-value">
                                                    <strong><?= htmlspecialchars($surat_jalan->nama_driver) ?></strong>
                                                    <br><small class="text-muted">
                                                        NIK: <?= htmlspecialchars($surat_jalan->driver_nik) ?>
                                                        <?php if (!empty($surat_jalan->driver_sim)): ?>
                                                            | SIM: <?= htmlspecialchars($surat_jalan->driver_sim) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>

                                                <div class="info-label">
                                                    <i class="fas fa-truck"></i> Unit
                                                </div>
                                                <div class="info-value">
                                                    <strong><?= htmlspecialchars($surat_jalan->no_polisi) ?></strong>
                                                    <br><small class="text-muted">
                                                        <?= htmlspecialchars($surat_jalan->unit_tipe) ?>
                                                        <?php if (!empty($surat_jalan->tipe_box)): ?>
                                                            | <?= htmlspecialchars($surat_jalan->tipe_box) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>

                                                <div class="info-label">
                                                    <i class="fas fa-clock"></i> Waktu
                                                </div>
                                                <div class="info-value">
                                                    <?php if (!empty($surat_jalan->jam_berangkat)): ?>
                                                        <strong>Berangkat:</strong> <?= date('d/m/Y H:i', strtotime($surat_jalan->jam_berangkat)) ?><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($surat_jalan->target_tiba)): ?>
                                                        <strong>Target Tiba:</strong> <?= date('d/m/Y H:i', strtotime($surat_jalan->target_tiba)) ?><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($surat_jalan->jam_tiba)): ?>
                                                        <strong>Tiba:</strong> <?= date('d/m/Y H:i', strtotime($surat_jalan->jam_tiba)) ?><br>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (!empty($surat_jalan->catatan)): ?>
                                            <hr>
                                            <div class="info-label">
                                                <i class="fas fa-sticky-note"></i> Catatan
                                            </div>
                                            <div class="info-value">
                                                <?= nl2br(htmlspecialchars($surat_jalan->catatan)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Destination Tracking -->
                            <?php if (!empty($surat_jalan->dest1)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-map-marked-alt"></i> Tracking Destinasi
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <?php for ($i = 1; $i <= 4; $i++):
                                            $dest_field = 'dest' . $i;
                                            $dest_status_field = 'dest' . $i . '_status';
                                            $dest_time_field = 'dest' . $i . '_time';
                                            $dest_catatan_field = 'dest' . $i . '_catatan';

                                            if (empty($surat_jalan->$dest_field)) continue;
                                        ?>
                                            <div class="destination-tracker">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <h6 class="mb-1">
                                                            <i class="fas fa-map-pin text-danger"></i>
                                                            Destinasi <?= $i ?>: <strong><?= htmlspecialchars($surat_jalan->$dest_field) ?></strong>
                                                        </h6>
                                                        <?php if (!empty($surat_jalan->$dest_status_field)): ?>
                                                            <?php
                                                            $dest_status_class = 'dest-pending';
                                                            $dest_status_icon = 'fa-circle';

                                                            switch ($surat_jalan->$dest_status_field) {
                                                                case 'arrived':
                                                                    $dest_status_class = 'dest-arrived';
                                                                    $dest_status_icon = 'fa-map-marker-alt';
                                                                    break;
                                                                case 'loading':
                                                                    $dest_status_class = 'dest-loading';
                                                                    $dest_status_icon = 'fa-box-open';
                                                                    break;
                                                                case 'completed':
                                                                    $dest_status_class = 'dest-completed';
                                                                    $dest_status_icon = 'fa-check-circle';
                                                                    break;
                                                            }
                                                            ?>
                                                            <span class="destination-status <?= $dest_status_class ?>">
                                                                <i class="fas <?= $dest_status_icon ?>"></i>
                                                                <?= strtoupper($surat_jalan->$dest_status_field) ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="destination-status dest-pending">
                                                                <i class="fas fa-circle"></i> PENDING
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-6 text-right">
                                                        <?php if (!empty($surat_jalan->$dest_time_field)): ?>
                                                            <small class="text-muted">
                                                                <i class="fas fa-clock"></i>
                                                                <?= date('d/m/Y H:i', strtotime($surat_jalan->$dest_time_field)) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php if (!empty($surat_jalan->$dest_catatan_field)): ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-comment"></i>
                                                            <?= htmlspecialchars($surat_jalan->$dest_catatan_field) ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Biaya Detail -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-money-bill-wave"></i> Detail Biaya
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="biaya-row">
                                        <div class="row">
                                            <div class="col-8">
                                                <strong>Biaya Sewa</strong>
                                            </div>
                                            <div class="col-4 text-right nominal-display">
                                                <strong class="text-primary">
                                                    Rp <?= number_format($surat_jalan->biaya_sewa ?? 0, 0, ',', '.') ?>
                                                </strong>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    $biaya_items = [
                                        'biaya_solar' => 'Solar',
                                        'biaya_tol' => 'Tol',
                                        'biaya_parkir' => 'Parkir',
                                        'biaya_makan' => 'Makan',
                                        'biaya_lainnya' => 'Lainnya'
                                    ];

                                    foreach ($biaya_items as $field => $label):
                                        if (!empty($surat_jalan->$field) && $surat_jalan->$field > 0):
                                    ?>
                                            <div class="biaya-row">
                                                <div class="row">
                                                    <div class="col-8">
                                                        <?= $label ?>
                                                    </div>
                                                    <div class="col-4 text-right nominal-display">
                                                        Rp <?= number_format($surat_jalan->$field, 0, ',', '.') ?>
                                                    </div>
                                                </div>
                                            </div>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>

                                    <?php if (!empty($surat_jalan->biaya_details)): ?>
                                        <hr>
                                        <h6 class="mb-3">Biaya Tambahan:</h6>
                                        <?php foreach ($surat_jalan->biaya_details as $biaya): ?>
                                            <div class="biaya-row">
                                                <div class="row align-items-center">
                                                    <div class="col-md-5">
                                                        <strong><?= htmlspecialchars($biaya->jenis_biaya) ?></strong>
                                                        <?php if (!empty($biaya->keterangan)): ?>
                                                            <br><small class="text-muted">
                                                                <?= htmlspecialchars($biaya->keterangan) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-3 text-center">
                                                        <small class="text-muted">
                                                            <?= date('d/m/Y', strtotime($biaya->tanggal)) ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-md-3 text-right nominal-display">
                                                        Rp <?= number_format($biaya->nominal, 0, ',', '.') ?>
                                                    </div>
                                                    <div class="col-md-1 text-right">
                                                        <?php if ($user_level == 'superadmin'): ?>
                                                            <a href="<?= base_url('surat_jalan/delete_biaya/' . $biaya->id . '/' . $surat_jalan->id) ?>"
                                                                class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Hapus biaya ini?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <hr>
                                    <div class="biaya-row">
                                        <div class="row">
                                            <div class="col-8">
                                                <h5 class="mb-0"><strong>TOTAL BIAYA</strong></h5>
                                            </div>
                                            <div class="col-4 text-right">
                                                <h5 class="mb-0 nominal-display">
                                                    <strong class="text-danger">
                                                        Rp <?= number_format($surat_jalan->total_biaya ?? $surat_jalan->biaya_sewa, 0, ',', '.') ?>
                                                    </strong>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    $profit = ($surat_jalan->biaya_sewa ?? 0) - ($surat_jalan->total_biaya ?? $surat_jalan->biaya_sewa);
                                    if ($profit != 0):
                                    ?>
                                        <div class="biaya-row">
                                            <div class="row">
                                                <div class="col-8">
                                                    <strong>Profit/Loss</strong>
                                                </div>
                                                <div class="col-4 text-right">
                                                    <strong class="<?= $profit >= 0 ? 'text-success' : 'text-danger' ?> nominal-display">
                                                        Rp <?= number_format(abs($profit), 0, ',', '.') ?>
                                                        <?= $profit >= 0 ? '(Profit)' : '(Loss)' ?>
                                                    </strong>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Tracking History -->
                            <?php if (!empty($surat_jalan->tracking_history)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-history"></i> Riwayat Tracking
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="timeline">
                                            <?php foreach ($surat_jalan->tracking_history as $track): ?>
                                                <div class="timeline-item">
                                                    <?php
                                                    $track_icon_bg = '#4e73df';
                                                    $track_icon = 'fa-circle';

                                                    switch ($track->status) {
                                                        case 'draft':
                                                            $track_icon = 'fa-file';
                                                            $track_icon_bg = '#858796';
                                                            break;
                                                        case 'scheduled':
                                                            $track_icon = 'fa-calendar-check';
                                                            $track_icon_bg = '#4e73df';
                                                            break;
                                                        case 'on_trip':
                                                            $track_icon = 'fa-truck';
                                                            $track_icon_bg = '#f6c23e';
                                                            break;
                                                        case 'completed':
                                                            $track_icon = 'fa-check-circle';
                                                            $track_icon_bg = '#1cc88a';
                                                            break;
                                                        case 'cancelled':
                                                            $track_icon = 'fa-times-circle';
                                                            $track_icon_bg = '#e74a3b';
                                                            break;
                                                    }
                                                    ?>
                                                    <div class="timeline-icon" style="background: <?= $track_icon_bg ?>;">
                                                        <i class="fas <?= $track_icon ?>"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?= strtoupper($track->status) ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock"></i>
                                                            <?= date('d/m/Y H:i:s', strtotime($track->created_at)) ?>
                                                            <?php if (!empty($track->created_by)): ?>
                                                                | <i class="fas fa-user"></i> <?= htmlspecialchars($track->created_by) ?>
                                                            <?php endif; ?>
                                                        </small>
                                                        <?php if (!empty($track->lokasi)): ?>
                                                            <br><small><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($track->lokasi) ?></small>
                                                        <?php endif; ?>
                                                        <?php if (!empty($track->keterangan)): ?>
                                                            <br><small><?= htmlspecialchars($track->keterangan) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Photos -->
                            <?php if (!empty($surat_jalan->foto_surat_jalan) || !empty($surat_jalan->foto_bukti_kirim)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-images"></i> Foto
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php if (!empty($surat_jalan->foto_surat_jalan)): ?>
                                                <div class="col-md-6 mb-3">
                                                    <h6>Foto Surat Jalan:</h6>
                                                    <img src="<?= base_url('uploads/surat_jalan/' . $surat_jalan->foto_surat_jalan) ?>"
                                                        class="photo-preview"
                                                        alt="Foto Surat Jalan"
                                                        onclick="window.open(this.src, '_blank')">
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($surat_jalan->foto_bukti_kirim)): ?>
                                                <div class="col-md-6 mb-3">
                                                    <h6>Foto Bukti Kirim:</h6>
                                                    <img src="<?= base_url('uploads/surat_jalan/' . $surat_jalan->foto_bukti_kirim) ?>"
                                                        class="photo-preview"
                                                        alt="Foto Bukti Kirim"
                                                        onclick="window.open(this.src, '_blank')">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>

                        <!-- Right Column - Actions -->
                        <div class="col-lg-4">

                            <!-- Quick Actions -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-gradient-primary text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-tools"></i> Quick Actions
                                    </h6>
                                </div>
                                <div class="card-body action-btn-group">

                                    <!-- Update Status Form -->
                                    <?php if (in_array($surat_jalan->status, ['draft', 'scheduled'])): ?>
                                        <div class="status-update-form">
                                            <h6 class="text-center">
                                                <i class="fas fa-sync-alt"></i> Update Status
                                            </h6>
                                            <form method="post"
                                                action="<?= base_url('surat_jalan/update_status/' . $surat_jalan->id) ?>"
                                                id="updateStatusForm">
                                                <div class="form-group">
                                                    <label class="font-weight-bold small">Status:</label>
                                                    <select name="status" class="form-control" required id="statusSelect">
                                                        <option value="draft" <?= $surat_jalan->status == 'draft' ? 'selected' : '' ?>>
                                                            Draft
                                                        </option>
                                                        <option value="scheduled" <?= $surat_jalan->status == 'scheduled' ? 'selected' : '' ?>>
                                                            Scheduled
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="form-group" id="jamBerangkatGroup"
                                                    style="<?= $surat_jalan->status == 'scheduled' ? '' : 'display:none;' ?>">
                                                    <label class="font-weight-bold small">Jam Berangkat:</label>
                                                    <input type="time"
                                                        name="jam_berangkat"
                                                        id="jamBerangkat"
                                                        class="form-control"
                                                        value="<?= !empty($surat_jalan->jam_berangkat) ? date('H:i', strtotime($surat_jalan->jam_berangkat)) : '20:00' ?>">
                                                    <small class="text-muted">Wajib diisi untuk Scheduled</small>
                                                </div>

                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="fas fa-save"></i> Update Status
                                                </button>
                                            </form>
                                        </div>
                                        <hr>
                                    <?php endif; ?>

                                    <!-- Edit Button -->
                                    <?php if (in_array($surat_jalan->status, ['draft', 'scheduled'])): ?>
                                        <a href="<?= base_url('surat_jalan/ubah/' . $surat_jalan->id) ?>"
                                            class="btn btn-warning btn-block">
                                            <i class="fas fa-edit"></i> Edit Surat Jalan
                                        </a>
                                    <?php endif; ?>

                                    <!-- Start Trip -->
                                    <?php if ($surat_jalan->status == 'scheduled'): ?>
                                        <a href="<?= base_url('surat_jalan/start_trip/' . $surat_jalan->id) ?>"
                                            class="btn btn-success btn-block"
                                            onclick="return confirm('Mulai trip sekarang?')">
                                            <i class="fas fa-play"></i> Mulai Trip
                                        </a>
                                    <?php endif; ?>

                                    <!-- Complete Trip -->
                                    <?php if ($surat_jalan->status == 'on_trip'): ?>
                                        <a href="<?= base_url('surat_jalan/complete_trip/' . $surat_jalan->id) ?>"
                                            class="btn btn-success btn-block"
                                            onclick="return confirm('Selesaikan trip?')">
                                            <i class="fas fa-check-circle"></i> Selesaikan Trip
                                        </a>
                                    <?php endif; ?>

                                    <!-- Cancel Trip -->
                                    <?php if (!in_array($surat_jalan->status, ['completed', 'cancelled'])): ?>
                                        <button type="button"
                                            class="btn btn-danger btn-block"
                                            data-toggle="modal"
                                            data-target="#cancelModal">
                                            <i class="fas fa-times-circle"></i> Batalkan Trip
                                        </button>
                                    <?php endif; ?>

                                    <hr>

                                    <!-- Export PDF -->
                                    <a href="<?= base_url('surat_jalan/export_pdf/' . $surat_jalan->id) ?>"
                                        class="btn btn-danger btn-block"
                                        target="_blank">
                                        <i class="fas fa-file-pdf"></i> Export PDF
                                    </a>

                                    <!-- Back to List -->
                                    <a href="<?= base_url('surat_jalan') ?>"
                                        class="btn btn-secondary btn-block">
                                        <i class="fas fa-arrow-left"></i> Kembali ke List
                                    </a>

                                    <!-- Delete (Superadmin Only) -->
                                    <?php if ($user_level == 'superadmin' && in_array($surat_jalan->status, ['draft', 'cancelled'])): ?>
                                        <hr>
                                        <a href="<?= base_url('surat_jalan/hapus/' . $surat_jalan->id) ?>"
                                            class="btn btn-danger btn-block"
                                            onclick="return confirm('PERHATIAN!\n\nAnda akan menghapus surat jalan:\n<?= $surat_jalan->no_surat_jalan ?>\n\nData yang dihapus tidak dapat dikembalikan!\n\nLanjutkan?')">
                                            <i class="fas fa-trash"></i> Hapus Surat Jalan
                                        </a>
                                    <?php endif; ?>

                                </div>
                            </div>

                            <!-- Summary Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-info-circle"></i> Ringkasan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">Status:</small>
                                        <h5>
                                            <span class="badge <?= $status_badge ?> badge-lg">
                                                <?= strtoupper($surat_jalan->status) ?>
                                            </span>
                                        </h5>
                                    </div>

                                    <?php if ($surat_jalan->status == 'completed' && $surat_jalan->sla_status): ?>
                                        <div class="mb-3">
                                            <small class="text-muted">SLA Status:</small>
                                            <h5>
                                                <span class="badge <?= $sla_badge ?>">
                                                    <?= $sla_text ?>
                                                </span>
                                            </h5>
                                        </div>
                                    <?php endif; ?>

                                    <hr>

                                    <div class="mb-2">
                                        <small class="text-muted">Biaya Sewa:</small><br>
                                        <strong class="text-primary nominal-display">
                                            Rp <?= number_format($surat_jalan->biaya_sewa ?? 0, 0, ',', '.') ?>
                                        </strong>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Total Biaya:</small><br>
                                        <strong class="text-danger nominal-display">
                                            Rp <?= number_format($surat_jalan->total_biaya ?? $surat_jalan->biaya_sewa, 0, ',', '.') ?>
                                        </strong>
                                    </div>

                                    <?php if ($profit != 0): ?>
                                        <hr>
                                        <div>
                                            <small class="text-muted">Profit/Loss:</small><br>
                                            <strong class="<?= $profit >= 0 ? 'text-success' : 'text-danger' ?> nominal-display">
                                                Rp <?= number_format(abs($profit), 0, ',', '.') ?>
                                                <br><small><?= $profit >= 0 ? '(Profit)' : '(Loss)' ?></small>
                                            </strong>
                                        </div>
                                    <?php endif; ?>

                                    <hr>

                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> Dibuat: <?= htmlspecialchars($surat_jalan->created_by ?? 'System') ?>
                                        <br><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($surat_jalan->created_at)) ?>
                                        <?php if (!empty($surat_jalan->updated_at)): ?>
                                            <br><i class="fas fa-edit"></i> Update: <?= date('d/m/Y H:i', strtotime($surat_jalan->updated_at)) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle"></i> Batalkan Trip
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="post" action="<?= base_url('surat_jalan/cancel_trip/' . $surat_jalan->id) ?>">
                    <div class="modal-body">
                        <p><strong>Anda akan membatalkan trip:</strong></p>
                        <p class="text-primary"><?= htmlspecialchars($surat_jalan->no_surat_jalan) ?></p>

                        <div class="form-group">
                            <label class="font-weight-bold">Alasan Pembatalan: <span class="text-danger">*</span></label>
                            <textarea name="keterangan_cancel"
                                class="form-control"
                                rows="4"
                                required
                                placeholder="Masukkan alasan pembatalan..."></textarea>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Perhatian:</strong> Trip yang dibatalkan tidak dapat diubah kembali ke status aktif.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-check"></i> Ya, Batalkan Trip
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        $(document).ready(function() {
            // Toggle Jam Berangkat visibility
            function toggleJamBerangkat() {
                var status = $('#statusSelect').val();
                if (status === 'scheduled') {
                    $('#jamBerangkatGroup').slideDown(300);
                    $('#jamBerangkat').attr('required', true);
                } else {
                    $('#jamBerangkatGroup').slideUp(300);
                    $('#jamBerangkat').attr('required', false);
                }
            }

            $('#statusSelect').on('change', toggleJamBerangkat);
            toggleJamBerangkat(); // Initial check

            // Form validation
            $('#updateStatusForm').on('submit', function(e) {
                var status = $('#statusSelect').val();
                var jam = $('#jamBerangkat').val();

                if (status === 'scheduled' && !jam) {
                    e.preventDefault();
                    alert('⚠️ Jam Berangkat wajib diisi untuk status Scheduled!');
                    $('#jamBerangkat').focus();
                    return false;
                }

                var msg = '🔄 UPDATE STATUS?\n\n';
                msg += 'Status Baru: ' + status.toUpperCase() + '\n';
                if (status === 'scheduled' && jam) {
                    msg += 'Jam Berangkat: ' + jam + '\n';
                }
                msg += '\nLanjutkan?';

                return confirm(msg);
            });

            // Auto-hide flash messages
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Photo preview click handler
            $('.photo-preview').on('click', function() {
                var src = $(this).attr('src');
                window.open(src, '_blank');
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Console logging
            console.log('%c📋 Surat Jalan Detail Loaded', 'background: #4e73df; color: white; padding: 5px 10px; font-size: 14px; font-weight: bold;');
            console.log('No. SJ:', '<?= $surat_jalan->no_surat_jalan ?>');
            console.log('Status:', '<?= $surat_jalan->status ?>');
            console.log('Customer:', '<?= $surat_jalan->customer ?>');
            console.log('Driver:', '<?= $surat_jalan->nama_driver ?>');
            console.log('Unit:', '<?= $surat_jalan->no_polisi ?>');

            <?php if ($is_overdue): ?>
                console.warn('⚠️ TRIP OVERDUE!');
            <?php endif; ?>
        });

        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // Esc = Close modals
            if (e.keyCode === 27) {
                $('.modal').modal('hide');
            }

            // Ctrl + E = Edit (if available)
            if (e.ctrlKey && e.keyCode === 69) {
                e.preventDefault();
                <?php if (in_array($surat_jalan->status, ['draft', 'scheduled'])): ?>
                    window.location.href = '<?= base_url('surat_jalan/ubah/' . $surat_jalan->id) ?>';
                <?php endif; ?>
            }

            // Ctrl + P = Print PDF
            if (e.ctrlKey && e.keyCode === 80) {
                e.preventDefault();
                window.open('<?= base_url('surat_jalan/export_pdf/' . $surat_jalan->id) ?>', '_blank');
            }

            // Ctrl + B = Back to list
            if (e.ctrlKey && e.keyCode === 66) {
                e.preventDefault();
                window.location.href = '<?= base_url('surat_jalan') ?>';
            }
        });
    </script>
</body>

</html>