<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* ─── Camera & Selfie ─────────────────────────────────────────── */
        #camera-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        #camera-preview {
            width: 100%;
            border-radius: 10px;
            background: #000;
            aspect-ratio: 4/3;
            transform: scaleX(-1);
        }

        #captured-photo {
            width: 100%;
            border-radius: 10px;
            display: none;
        }

        .camera-controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }

        /* ─── User Info ───────────────────────────────────────────────── */
        .user-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
        }

        .location-info {
            background: #f8f9fc;
            border-left: 4px solid #4e73df;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }

        /* ─── History Cards ───────────────────────────────────────────── */
        .history-card {
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .history-card.tipe-in {
            border-left: 4px solid #1cc88a;
        }

        .history-card.tipe-out {
            border-left: 4px solid #e74a3b;
        }

        .history-card.tipe-out.is-auto {
            border-left: 4px solid #f6c23e;
            background-color: #fffdf0;
        }

        .history-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .history-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .camera-disabled {
            opacity: 0.5;
            pointer-events: none;
            filter: grayscale(100%);
        }

        .badge-in {
            background: #1cc88a;
            color: white;
        }

        .badge-out {
            background: #e74a3b;
            color: white;
        }

        .badge-auto-out {
            background: #f6c23e;
            color: #333;
        }

        .auto-out-banner {
            background: linear-gradient(135deg, #f6c23e, #f8a225);
            color: #333;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 10px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ─── Realtime Dashboard ──────────────────────────────────────── */
        @keyframes rtPulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .4;
                transform: scale(1.5);
            }
        }

        #rt-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #1cc88a;
            display: inline-block;
            animation: rtPulse 1.5s infinite;
            margin-right: 6px;
        }

        #rt-filter-btns .btn.active {
            background-color: #4e73df;
            border-color: #4e73df;
            color: #fff;
        }

        #rt-table tbody tr {
            transition: background .15s;
        }

        .rt-badge-in {
            background: #1cc88a;
            color: #fff;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: .75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .rt-badge-complete {
            background: #4e73df;
            color: #fff;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: .75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .rt-badge-none {
            background: #858796;
            color: #fff;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: .75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .rt-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #e8eeff;
            color: #4e73df;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        #rt-search {
            max-width: 220px;
        }

        .rt-spin {
            display: inline-block;
            animation: spin .8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ─── Sidebar left border cards ──────────────────────────────── */
        .border-left-secondary {
            border-left: 4px solid #858796 !important;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- Page Header -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-camera"></i> <?= $title ?>
                        </h1>
                        <?php if ($is_admin || $can_see_laporan): ?>
                            <a href="<?= base_url('absensi/laporan') ?>" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> Lihat Laporan
                            </a>
                        <?php endif ?>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php
                    $already_in = !empty($today_in);
                    $already_out = !empty($today_out);
                    $already_complete = $already_in && $already_out;
                    $current_tipe = $already_in ? 'out' : 'in';
                    ?>

                    <!-- Status Alert -->
                    <?php if ($already_complete): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <h5 class="alert-heading"><i class="fas fa-check-circle"></i> Absensi Hari Ini Sudah Lengkap!
                            </h5>
                            <hr>
                            <p class="mb-0">
                                <i class="fas fa-sign-in-alt text-success"></i>
                                <strong>IN:</strong> <?= date('H:i:s', strtotime($today_in->waktu)) ?> WIB
                                &nbsp;&nbsp;
                                <i class="fas fa-sign-out-alt text-danger"></i>
                                <strong>OUT:</strong> <?= date('H:i:s', strtotime($today_out->waktu)) ?> WIB
                                <?php if (($today_out->metode ?? '') === 'auto'): ?>
                                    &nbsp;<span class="badge" style="background:#f6c23e;color:#333;font-size:0.75rem;">
                                        <i class="fas fa-robot"></i> Auto
                                    </span>
                                <?php endif ?>
                            </p>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php elseif ($already_in): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <h5 class="alert-heading"><i class="fas fa-sign-in-alt"></i> Sudah Absen IN — Jangan Lupa Absen
                                OUT!</h5>
                            <hr>
                            <p class="mb-0">
                                <strong>Waktu IN:</strong> <?= date('H:i:s', strtotime($today_in->waktu)) ?> WIB
                                &nbsp;|&nbsp;
                                <strong>Lokasi:</strong> <?= $today_in->alamat ?>
                            </p>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- ═══════════════════════════════════════════════════
                         BARIS 1 : Selfie kiri | Riwayat hari ini kanan
                    ═══════════════════════════════════════════════════ -->
                    <div class="row">

                        <!-- LEFT: Form Absensi Selfie -->
                        <div class="col-lg-6">

                            <!-- User Info Card -->
                            <div class="user-info-card">
                                <div class="d-flex align-items-center">
                                    <img src="<?= base_url('uploads/profil/' . $user_foto_profil) ?>" alt="Profile"
                                        class="user-avatar mr-3">
                                    <div>
                                        <h4 class="mb-1"><?= $user_name ?></h4>
                                        <p class="mb-0"><i class="fas fa-id-card"></i> NIK: <?= $user_nik ?></p>
                                        <p class="mb-0"><i class="fas fa-user-tag"></i> Level:
                                            <span class="badge badge-light">
                                                <?= ucwords(str_replace('_', ' ', $user_level)) ?>
                                            </span>
                                        </p>
                                        <div class="mt-2">
                                            <?php if ($already_complete): ?>
                                                <span class="badge"
                                                    style="background:#1cc88a;color:white;font-size:0.85rem;">
                                                    <i class="fas fa-check-circle"></i> IN & OUT Lengkap
                                                </span>
                                            <?php elseif ($already_in): ?>
                                                <span class="badge"
                                                    style="background:#f6c23e;color:#333;font-size:0.85rem;">
                                                    <i class="fas fa-sign-in-alt"></i> Sudah IN — Belum OUT
                                                </span>
                                            <?php else: ?>
                                                <span class="badge"
                                                    style="background:#858796;color:white;font-size:0.85rem;">
                                                    <i class="fas fa-clock"></i> Belum Absen
                                                </span>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Camera Card -->
                            <div class="card shadow mb-4 <?= $already_complete ? 'camera-disabled' : '' ?>">
                                <div
                                    class="card-header py-3 <?= $current_tipe === 'in' ? 'bg-success' : 'bg-danger' ?>">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <?php if ($already_complete): ?>
                                            <i class="fas fa-check-circle"></i> Absensi Sudah Lengkap
                                        <?php elseif ($current_tipe === 'out'): ?>
                                            <i class="fas fa-sign-out-alt"></i> Absen OUT — Selamat Pulang!
                                        <?php else: ?>
                                            <i class="fas fa-sign-in-alt"></i> Absen IN — Selamat Datang!
                                        <?php endif ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($already_complete): ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                            <h5 class="text-muted">Absensi Hari Ini Sudah Selesai</h5>
                                            <p class="text-muted mb-1">
                                                <i class="fas fa-sign-in-alt text-success"></i>
                                                IN: <?= date('H:i:s', strtotime($today_in->waktu)) ?> WIB
                                            </p>
                                            <p class="text-muted">
                                                <i class="fas fa-sign-out-alt text-danger"></i>
                                                OUT: <?= date('H:i:s', strtotime($today_out->waktu)) ?> WIB
                                                <?php if (($today_out->metode ?? '') === 'auto'): ?>
                                                    <span class="badge"
                                                        style="background:#f6c23e;color:#333;font-size:0.75rem;">
                                                        <i class="fas fa-robot"></i> Auto
                                                    </span>
                                                <?php endif ?>
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <div id="camera-container">
                                            <video id="camera-preview" autoplay playsinline></video>
                                            <img id="captured-photo" alt="Captured Photo">
                                        </div>

                                        <div class="camera-controls">
                                            <button type="button" class="btn btn-success btn-lg" id="btn-capture">
                                                <i class="fas fa-camera"></i> Ambil Foto
                                            </button>
                                            <button type="button" class="btn btn-warning btn-lg" id="btn-retake"
                                                style="display:none;">
                                                <i class="fas fa-redo"></i> Foto Ulang
                                            </button>
                                            <button type="button"
                                                class="btn btn-lg <?= $current_tipe === 'in' ? 'btn-success' : 'btn-danger' ?>"
                                                id="btn-submit" style="display:none;">
                                                <?php if ($current_tipe === 'in'): ?>
                                                    <i class="fas fa-sign-in-alt"></i> Submit Absen IN
                                                <?php else: ?>
                                                    <i class="fas fa-sign-out-alt"></i> Submit Absen OUT
                                                <?php endif ?>
                                            </button>
                                        </div>

                                        <div class="location-info mt-3" id="location-info" style="display:none;">
                                            <h6 class="font-weight-bold">
                                                <i class="fas fa-map-marker-alt text-danger"></i> Lokasi Anda
                                            </h6>
                                            <p class="mb-1" id="address-text">Sedang mengambil lokasi...</p>
                                            <small class="text-muted">
                                                Lat: <span id="lat-text">-</span>,
                                                Long: <span id="lng-text">-</span>
                                            </small>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT: Riwayat Hari Ini -->
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-info">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-history"></i> Riwayat Absensi Hari Ini
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($today_attendance)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Belum ada absensi hari ini</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($today_attendance as $record): ?>
                                            <?php $is_auto = ($record->metode ?? '') === 'auto'; ?>
                                            <div
                                                class="history-card card mb-3 tipe-<?= $record->tipe ?><?= $is_auto ? ' is-auto' : '' ?>">
                                                <div class="card-body">
                                                    <?php if ($is_auto): ?>
                                                        <div class="auto-out-banner mb-2">
                                                            <i class="fas fa-robot"></i>
                                                            <span>OUT ini diproses <strong>otomatis oleh sistem</strong>
                                                                karena melewati batas 16 jam kerja.</span>
                                                        </div>
                                                    <?php endif ?>
                                                    <div class="row align-items-center">
                                                        <div class="col-md-3 text-center">
                                                            <img src="<?= base_url('uploads/absensi/' . $record->foto) ?>"
                                                                alt="Foto" class="history-photo img-thumbnail">
                                                        </div>
                                                        <div class="col-md-9">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <?php if ($is_auto): ?>
                                                                    <span class="badge badge-auto-out mr-2"
                                                                        style="font-size:.9rem;">
                                                                        <i class="fas fa-robot"></i> AUTO OUT
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-<?= $record->tipe ?> mr-2"
                                                                        style="font-size:.9rem;">
                                                                        <?= strtoupper($record->tipe) ?>
                                                                    </span>
                                                                <?php endif ?>
                                                                <h6
                                                                    class="font-weight-bold mb-0 <?= $record->tipe === 'in' ? 'text-success' : ($is_auto ? 'text-warning' : 'text-danger') ?>">
                                                                    <i class="fas fa-clock"></i>
                                                                    <?= date('H:i:s', strtotime($record->waktu)) ?> WIB
                                                                </h6>
                                                            </div>
                                                            <p class="mb-1 small">
                                                                <i class="fas fa-calendar"></i>
                                                                <?= date('d M Y', strtotime($record->tanggal)) ?>
                                                            </p>
                                                            <p class="mb-2 small">
                                                                <i class="fas fa-map-marker-alt text-danger"></i>
                                                                <?= $record->alamat ?>
                                                            </p>
                                                            <?php if (!$is_auto): ?>
                                                                <a href="https://www.google.com/maps?q=<?= $record->latitude ?>,<?= $record->longitude ?>"
                                                                    target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-map"></i> Lihat di Maps
                                                                </a>
                                                            <?php endif ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach ?>

                                        <!-- Summary IN/OUT -->
                                        <div class="card bg-light mt-3">
                                            <div class="card-body py-2">
                                                <div class="row text-center">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Absen IN</small>
                                                        <strong class="text-success">
                                                            <?= $today_in ? date('H:i', strtotime($today_in->waktu)) : '-' ?>
                                                        </strong>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Absen OUT</small>
                                                        <strong
                                                            class="<?= ($today_out && ($today_out->metode ?? '') === 'auto') ? 'text-warning' : 'text-danger' ?>">
                                                            <?php if ($today_out): ?>
                                                                <?= date('H:i', strtotime($today_out->waktu)) ?>
                                                                <?php if (($today_out->metode ?? '') === 'auto'): ?>
                                                                    <small><i class="fas fa-robot" title="Auto OUT"></i></small>
                                                                <?php endif ?>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif ?>
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>

                    </div><!-- end row selfie+riwayat -->


                    <!-- ═══════════════════════════════════════════════════
                         BARIS 2 : Realtime Dashboard (SUPERADMIN ONLY)
                    ═══════════════════════════════════════════════════ -->
                    <?php if ($user_level === 'superadmin'): ?>
                        <div class="row mt-2" id="rt-section">
                            <div class="col-12">
                                <div class="card shadow mb-4">

                                    <!-- Card Header -->
                                    <div
                                        class="card-header py-3 bg-dark d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <h6 class="m-0 font-weight-bold text-white d-flex align-items-center">
                                            <span id="rt-pulse"></span>
                                            Kehadiran Realtime Hari Ini
                                        </h6>
                                        <div class="d-flex align-items-center gap-3">
                                            <small class="text-white-50">
                                                Update: <span id="rt-updated">—</span>
                                            </small>
                                            <button class="btn btn-sm btn-outline-light py-0 px-2" id="rt-refresh-btn"
                                                title="Refresh sekarang">
                                                <i class="fas fa-sync-alt fa-xs" id="rt-refresh-icon"></i>
                                            </button>
                                            <!-- Countdown bar -->
                                            <div
                                                style="width:80px;height:4px;background:rgba(255,255,255,.15);border-radius:2px;overflow:hidden;">
                                                <div id="rt-countdown-bar"
                                                    style="height:100%;background:#1cc88a;width:100%;transition:width 1s linear;">
                                                </div>
                                            </div>
                                            <small class="text-white-50" id="rt-countdown-text">30s</small>
                                        </div>
                                    </div>

                                    <div class="card-body">

                                        <!-- ── Stat Cards ─────────────────────────────── -->
                                        <div class="row mb-4">
                                            <div class="col-6 col-xl-3 mb-3">
                                                <div class="card border-left-secondary shadow h-100 py-2">
                                                    <div class="card-body py-2">
                                                        <div
                                                            class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                                            <i class="fas fa-users fa-fw"></i> Total Karyawan
                                                        </div>
                                                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="rt-total">
                                                            <?= (int) $rt_total ?>
                                                        </div>
                                                        <small class="text-muted">aktif terdaftar</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-xl-3 mb-3">
                                                <div class="card border-left-success shadow h-100 py-2">
                                                    <div class="card-body py-2">
                                                        <div
                                                            class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                            <i class="fas fa-sign-in-alt fa-fw"></i> Sudah Masuk
                                                        </div>
                                                        <div class="h4 mb-0 font-weight-bold text-success" id="rt-in">
                                                            <?= (int) $rt_in ?>
                                                        </div>
                                                        <small class="text-muted">sudah absen IN</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-xl-3 mb-3">
                                                <div class="card border-left-primary shadow h-100 py-2">
                                                    <div class="card-body py-2">
                                                        <div
                                                            class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                            <i class="fas fa-check-double fa-fw"></i> Lengkap IN+OUT
                                                        </div>
                                                        <div class="h4 mb-0 font-weight-bold text-primary" id="rt-complete">
                                                            <?= (int) $rt_complete ?>
                                                        </div>
                                                        <small class="text-muted">sudah pulang</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-xl-3 mb-3">
                                                <div class="card border-left-danger shadow h-100 py-2">
                                                    <div class="card-body py-2">
                                                        <div
                                                            class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                            <i class="fas fa-user-times fa-fw"></i> Belum Absen
                                                        </div>
                                                        <div class="h4 mb-0 font-weight-bold text-danger" id="rt-absent">
                                                            <?= (int) $rt_absent ?>
                                                        </div>
                                                        <small class="text-muted">belum IN sama sekali</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ── Progress Bar Kehadiran ─────────────────── -->
                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted font-weight-bold">Progress Kehadiran Hari
                                                    Ini</small>
                                                <small class="text-muted" id="rt-pct-label">
                                                    <?= $rt_total > 0 ? round(($rt_in / $rt_total) * 100) : 0 ?>%
                                                </small>
                                            </div>
                                            <div class="progress" style="height:12px;border-radius:6px;">
                                                <div class="progress-bar bg-success" id="rt-bar-complete" role="progressbar"
                                                    style="width:<?= $rt_total > 0 ? round(($rt_complete / $rt_total) * 100) : 0 ?>%;transition:width .5s ease;"
                                                    title="Lengkap IN+OUT">
                                                </div>
                                                <div class="progress-bar bg-warning" id="rt-bar-in" role="progressbar"
                                                    style="width:<?= $rt_total > 0 ? round((($rt_in - $rt_complete) / $rt_total) * 100) : 0 ?>%;transition:width .5s ease;"
                                                    title="Sudah IN, belum OUT">
                                                </div>
                                            </div>
                                            <div class="d-flex gap-3 mt-1">
                                                <small><span
                                                        style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#1cc88a;margin-right:3px;"></span>Lengkap</small>
                                                <small><span
                                                        style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#f6c23e;margin-right:3px;"></span>Sudah
                                                    IN</small>
                                                <small><span
                                                        style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#e9ecef;border:1px solid #dee2e6;margin-right:3px;"></span>Belum</small>
                                            </div>
                                        </div>

                                        <!-- ── Filter + Search ────────────────────────── -->
                                        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                                            <div class="input-group" style="max-width:240px;">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0">
                                                        <i class="fas fa-search fa-xs text-muted"></i>
                                                    </span>
                                                </div>
                                                <input type="text" id="rt-search"
                                                    class="form-control form-control-sm border-left-0"
                                                    placeholder="Cari nama / NIK...">
                                            </div>

                                            <div class="btn-group btn-group-sm" id="rt-filter-btns">
                                                <button class="btn btn-secondary active" data-filter="all">
                                                    Semua
                                                </button>
                                                <button class="btn btn-secondary" data-filter="in">
                                                    <i class="fas fa-sign-in-alt"></i> Masuk
                                                </button>
                                                <button class="btn btn-secondary" data-filter="complete">
                                                    <i class="fas fa-check-double"></i> Lengkap
                                                </button>
                                                <button class="btn btn-secondary" data-filter="none">
                                                    <i class="fas fa-user-times"></i> Belum
                                                </button>
                                            </div>

                                            <!-- Filter Group -->
                                            <select id="rt-filter-group" class="form-control form-control-sm"
                                                style="max-width:180px;">
                                                <option value="">— Semua Group —</option>
                                                <option>Yamazaki Staff</option>
                                                <option>Admin TSC</option>
                                                <option>Operasional TSC</option>
                                                <option>TSF Staff</option>
                                                <option>Sinar Boga Staff</option>
                                                <option>Rorotan Staff</option>
                                            </select>

                                            <small class="text-muted ml-auto" id="rt-count-label"></small>
                                        </div>

                                        <!-- ── Table ──────────────────────────────────── -->
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover table-bordered align-middle mb-0"
                                                id="rt-table">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th style="width:40px" class="text-center">No</th>
                                                        <th>Karyawan</th>
                                                        <th style="width:110px">NIK</th>
                                                        <th style="width:150px">Group</th>
                                                        <th style="width:130px" class="text-center">Status</th>
                                                        <th style="width:80px" class="text-center">Jam IN</th>
                                                        <th style="width:80px" class="text-center">Jam OUT</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="rt-body">
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-3">
                                                            <i class="fas fa-spinner fa-spin mr-2"></i> Memuat data...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Paging sederhana -->
                                        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                                            <small class="text-muted" id="rt-page-label"></small>
                                            <div class="btn-group btn-group-sm" id="rt-paging">
                                                <button class="btn btn-outline-secondary" id="rt-prev" disabled>
                                                    <i class="fas fa-chevron-left fa-xs"></i>
                                                </button>
                                                <span class="btn btn-outline-secondary disabled" id="rt-page-num">1</span>
                                                <button class="btn btn-outline-secondary" id="rt-next" disabled>
                                                    <i class="fas fa-chevron-right fa-xs"></i>
                                                </button>
                                            </div>
                                        </div>

                                    </div><!-- /card-body -->
                                </div><!-- /card -->
                            </div>
                        </div><!-- /row rt-section -->
                    <?php endif ?>

                </div><!-- /container-xl -->
            </div>
        </div>
        <?php $this->load->view('partials/footer') ?>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ═══════════════════════════════════════════════════════════
         SELFIE / KAMERA SCRIPT
    ═══════════════════════════════════════════════════════════ -->
    <script>
        $(document).ready(function () {
            const alreadyComplete = <?= $already_complete ? 'true' : 'false' ?>;
            const currentTipe = '<?= $current_tipe ?>';
            const lastOutIsAuto = <?= !empty($last_out_is_auto) ? 'true' : 'false' ?>;

            let videoStream = null;
            let capturedPhoto = null;
            let latitude = null;
            let longitude = null;
            let address = 'Lokasi tidak ditemukan';

            /* ── Kamera ─────────────────────────────────────────────── */
            async function initCamera() {
                if (alreadyComplete) return;
                try {
                    videoStream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } }
                    });
                    $('#camera-preview')[0].srcObject = videoStream;
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Kamera Error', text: 'Tidak dapat mengakses kamera!' });
                }
            }

            /* ── GPS ────────────────────────────────────────────────── */
            function getLocation() {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition(
                    async function (pos) {
                        latitude = pos.coords.latitude;
                        longitude = pos.coords.longitude;
                        $('#lat-text').text(latitude.toFixed(6));
                        $('#lng-text').text(longitude.toFixed(6));
                        try {
                            const r = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
                            const data = await r.json();
                            address = data.display_name || 'Alamat tidak ditemukan';
                            $('#address-text').text(address);
                        } catch (e) {
                            address = `Lat: ${latitude}, Long: ${longitude}`;
                            $('#address-text').text(address);
                        }
                        $('#location-info').slideDown();
                    },
                    function () {
                        Swal.fire({ icon: 'warning', title: 'GPS Error', text: 'Tidak dapat mengambil lokasi!' });
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }

            /* ── Capture ────────────────────────────────────────────── */
            $('#btn-capture').on('click', function () {
                const video = $('#camera-preview')[0];
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0);
                capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
                $('#captured-photo').attr('src', capturedPhoto).show();
                $('#camera-preview').hide();
                $('#btn-capture').hide();
                $('#btn-retake, #btn-submit').show();
                getLocation();
            });

            $('#btn-retake').on('click', function () {
                capturedPhoto = null;
                $('#captured-photo').hide();
                $('#camera-preview').show();
                $('#location-info').slideUp();
                $('#btn-retake, #btn-submit').hide();
                $('#btn-capture').show();
            });

            $('#btn-submit').on('click', function () {
                if (!capturedPhoto) {
                    Swal.fire({ icon: 'warning', title: 'Foto Belum Diambil', text: 'Silakan ambil foto terlebih dahulu!' });
                    return;
                }
                if (!latitude || !longitude) {
                    Swal.fire({ icon: 'warning', title: 'Lokasi Belum Didapat', text: 'Menunggu GPS...' });
                    return;
                }
                const tipeLabel = currentTipe === 'in' ? 'Absen IN' : 'Absen OUT';
                const tipeIcon = currentTipe === 'in' ? '🟢' : '🔴';
                Swal.fire({
                    title: `Konfirmasi ${tipeLabel}`,
                    html: `<div class="text-left">
                    <p>${tipeIcon} <strong>Tipe:</strong> ${tipeLabel}</p>
                    <p><strong>Nama:</strong> <?= $user_name ?></p>
                    <p><strong>NIK:</strong> <?= $user_nik ?></p>
                    <p><strong>Waktu:</strong> ${new Date().toLocaleString('id-ID')}</p>
                    <p><strong>Lokasi:</strong> ${address}</p>
                    <hr><small class="text-muted">Pastikan data sudah benar!</small>
                </div>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: currentTipe === 'in' ? '#1cc88a' : '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: `<i class="fas fa-check"></i> Ya, ${tipeLabel}`,
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    reverseButtons: true
                }).then(result => { if (result.isConfirmed) submitAbsensi(); });
            });

            function submitAbsensi() {
                Swal.fire({
                    title: 'Menyimpan...',
                    html: 'Mohon tunggu <i class="fas fa-spinner fa-spin"></i>',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });
                $.ajax({
                    url: '<?= base_url('absensi/submit') ?>',
                    type: 'POST',
                    data: { photo: capturedPhoto, latitude, longitude, address },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            const label = resp.tipe === 'in' ? 'Absen IN' : 'Absen OUT';
                            Swal.fire({
                                icon: 'success', title: `${label} Berhasil!`,
                                text: resp.message, timer: 2000, showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            if (resp.already_complete) {
                                Swal.fire({
                                    icon: 'info', title: 'Absensi Sudah Lengkap!',
                                    text: resp.message, confirmButtonText: 'OK'
                                }).then(() => location.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal!', text: resp.message });
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan: ' + error });
                    }
                });
            }

            if (!alreadyComplete) initCamera();
            setTimeout(() => $('.alert').fadeOut('slow'), 5000);

            /* Auto-out info modal */
            if (lastOutIsAuto) {
                Swal.fire({
                    icon: 'info',
                    title: '⏰ Informasi Absensi Otomatis',
                    html: `<div style="text-align:left;">
                    <p>Absensi <strong>OUT</strong> Anda sebelumnya telah diproses
                    <strong>secara otomatis oleh sistem</strong> karena melewati
                    batas waktu jam kerja (16 jam sejak absen IN).</p>
                    <hr>
                    <p class="mb-0" style="color:#6c757d;">
                        <i class="fas fa-info-circle" style="color:#36b9cc;"></i>
                        Silakan lakukan <strong>Absen IN</strong> seperti biasa saat Anda mulai bekerja kembali.
                    </p></div>`,
                    confirmButtonText: '<i class="fas fa-check"></i> Mengerti',
                    confirmButtonColor: '#4e73df',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
            }
        });
    </script>


    <!-- ═══════════════════════════════════════════════════════════
         REALTIME DASHBOARD SCRIPT  (superadmin only)
    ═══════════════════════════════════════════════════════════ -->
    <?php if ($user_level === 'superadmin'): ?>
        <script>
            (function () {
                'use strict';

                /* ── Konfigurasi ──────────────────────────────────────────── */
                var RT_URL = '<?= base_url('absensi/rt_data') ?>';
                var INTERVAL_MS = 30000;   /* auto-refresh tiap 30 detik   */
                var PER_PAGE = 20;      /* baris per halaman tabel       */

                /* ── State ────────────────────────────────────────────────── */
                var allRows = <?= json_encode(array_map(function ($k) {
                    return [
                        'nama' => $k->nama,
                        'nik' => $k->nik,
                        'group' => $k->group_karyawan ?? '-',
                        'foto' => $k->foto_profil ?? 'default-1.png',
                        'status' => $k->rt_status,
                        'jam_in' => $k->jam_in ? date('H:i', strtotime($k->jam_in)) : null,
                        'jam_out' => $k->jam_out ? date('H:i', strtotime($k->jam_out)) : null,
                    ];
                }, $rt_karyawan)) ?>;

                var curFilter = 'all';
                var curSearch = '';
                var curGroup = '';
                var curPage = 1;
                var totalFiltered = 0;
                var refreshTimer = null;
                var countdownVal = INTERVAL_MS / 1000;
                var countdownTimer = null;

                /* ── Helpers ──────────────────────────────────────────────── */
                function initials(name) {
                    return (name || '').split(' ').slice(0, 2).map(function (w) { return w[0] || ''; }).join('').toUpperCase();
                }

                function badge(status) {
                    if (status === 'complete')
                        return '<span class="rt-badge-complete"><i class="fas fa-check-double fa-xs"></i> Lengkap</span>';
                    if (status === 'in')
                        return '<span class="rt-badge-in"><i class="fas fa-sign-in-alt fa-xs"></i> Sudah IN</span>';
                    return '<span class="rt-badge-none"><i class="fas fa-clock fa-xs"></i> Belum</span>';
                }

                function avatarColor(name) {
                    var colors = ['#4e73df', '#1cc88a', '#e74a3b', '#f6c23e', '#36b9cc', '#858796'];
                    var idx = (name.charCodeAt(0) || 0) % colors.length;
                    return colors[idx];
                }

                function fmtTime(t) {
                    return t ? '<code style="font-size:.8rem;">' + t + '</code>' : '<span class="text-muted">—</span>';
                }

                /* ── Filter & Render ──────────────────────────────────────── */
                function getFiltered() {
                    return allRows.filter(function (r) {
                        var mF = (curFilter === 'all') || (r.status === curFilter);
                        var mS = !curSearch ||
                            r.nama.toLowerCase().indexOf(curSearch) > -1 ||
                            r.nik.toLowerCase().indexOf(curSearch) > -1;
                        var mG = !curGroup || r.group === curGroup;
                        return mF && mS && mG;
                    });
                }

                function render() {
                    var filtered = getFiltered();
                    totalFiltered = filtered.length;
                    var totalPages = Math.max(1, Math.ceil(totalFiltered / PER_PAGE));
                    if (curPage > totalPages) curPage = totalPages;

                    /* label count */
                    document.getElementById('rt-count-label').textContent =
                        'Menampilkan ' + totalFiltered + ' dari ' + allRows.length + ' karyawan';

                    /* paging info */
                    var startIdx = (curPage - 1) * PER_PAGE;
                    var endIdx = Math.min(startIdx + PER_PAGE, totalFiltered);
                    document.getElementById('rt-page-label').textContent =
                        totalFiltered > 0
                            ? 'Baris ' + (startIdx + 1) + '–' + endIdx + ' dari ' + totalFiltered
                            : '';
                    document.getElementById('rt-page-num').textContent = curPage + ' / ' + totalPages;
                    document.getElementById('rt-prev').disabled = (curPage <= 1);
                    document.getElementById('rt-next').disabled = (curPage >= totalPages);

                    /* table body */
                    var page = filtered.slice(startIdx, endIdx);
                    var tbody = document.getElementById('rt-body');

                    if (!page.length) {
                        tbody.innerHTML =
                            '<tr><td colspan="7" class="text-center text-muted py-4">' +
                            '<i class="fas fa-search mr-1"></i> Data tidak ditemukan</td></tr>';
                        return;
                    }

                    tbody.innerHTML = page.map(function (r, i) {
                        var no = startIdx + i + 1;
                        var rowClass = r.status === 'none' ? 'table-light' : '';
                        var color = avatarColor(r.nama);
                        return '<tr class="' + rowClass + '">' +
                            '<td class="text-center">' + no + '</td>' +
                            '<td>' +
                            '<div class="d-flex align-items-center" style="gap:8px;">' +
                            '<div class="rt-avatar" style="background:' + color + '20;color:' + color + ';">' +
                            initials(r.nama) +
                            '</div>' +
                            '<span style="font-weight:600;font-size:.875rem;">' + r.nama + '</span>' +
                            '</div>' +
                            '</td>' +
                            '<td><small class="text-muted">' + r.nik + '</small></td>' +
                            '<td><small>' + r.group + '</small></td>' +
                            '<td class="text-center">' + badge(r.status) + '</td>' +
                            '<td class="text-center">' + fmtTime(r.jam_in) + '</td>' +
                            '<td class="text-center">' + fmtTime(r.jam_out) + '</td>' +
                            '</tr>';
                    }).join('');
                }

                /* ── Update stat cards + progress bar ─────────────────────── */
                function updateStats(data) {
                    document.getElementById('rt-total').textContent = data.total;
                    document.getElementById('rt-in').textContent = data.in;
                    document.getElementById('rt-complete').textContent = data.complete;
                    document.getElementById('rt-absent').textContent = data.absent;
                    document.getElementById('rt-updated').textContent = data.updated;

                    var total = parseInt(data.total, 10) || 1;
                    var complete = parseInt(data.complete, 10);
                    var inOnly = parseInt(data.in, 10) - complete;

                    var pctComplete = Math.round((complete / total) * 100);
                    var pctInOnly = Math.round((Math.max(inOnly, 0) / total) * 100);
                    var pctTotal = Math.round((parseInt(data.in, 10) / total) * 100);

                    document.getElementById('rt-bar-complete').style.width = pctComplete + '%';
                    document.getElementById('rt-bar-in').style.width = pctInOnly + '%';
                    document.getElementById('rt-pct-label').textContent = pctTotal + '% hadir';
                }

                /* ── Fetch dari server ────────────────────────────────────── */
                function setRefreshIcon(spinning) {
                    var icon = document.getElementById('rt-refresh-icon');
                    if (spinning) {
                        icon.classList.add('rt-spin');
                    } else {
                        icon.classList.remove('rt-spin');
                    }
                }

                function fetchRT(silent) {
                    if (!silent) setRefreshIcon(true);

                    fetch(RT_URL)
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (!data.success) return;
                            allRows = data.rows;
                            updateStats(data);
                            render();

                            /* Badge navbar juga diupdate */
                            var nb = document.getElementById('navAbsensiBadge');
                            if (nb) {
                                var absent = parseInt(data.absent, 10);
                                if (absent > 0) {
                                    nb.textContent = absent > 99 ? '99+' : absent;
                                    nb.style.display = '';
                                } else {
                                    nb.style.display = 'none';
                                }
                            }
                        })
                        .catch(function () { })
                        .finally(function () {
                            setRefreshIcon(false);
                            resetCountdown();
                        });
                }

                /* ── Countdown bar ────────────────────────────────────────── */
                function resetCountdown() {
                    clearInterval(countdownTimer);
                    countdownVal = INTERVAL_MS / 1000;
                    updateCountdownUI();
                    countdownTimer = setInterval(function () {
                        countdownVal--;
                        if (countdownVal < 0) countdownVal = 0;
                        updateCountdownUI();
                    }, 1000);
                }

                function updateCountdownUI() {
                    var pct = (countdownVal / (INTERVAL_MS / 1000)) * 100;
                    document.getElementById('rt-countdown-bar').style.width = pct + '%';
                    document.getElementById('rt-countdown-text').textContent = countdownVal + 's';
                }

                /* ── Event: filter buttons ────────────────────────────────── */
                document.getElementById('rt-filter-btns').addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-filter]');
                    if (!btn) return;
                    document.querySelectorAll('#rt-filter-btns .btn')
                        .forEach(function (b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                    curFilter = btn.getAttribute('data-filter');
                    curPage = 1;
                    render();
                });

                /* ── Event: search ────────────────────────────────────────── */
                document.getElementById('rt-search').addEventListener('input', function () {
                    curSearch = this.value.toLowerCase().trim();
                    curPage = 1;
                    render();
                });

                /* ── Event: filter group ──────────────────────────────────── */
                document.getElementById('rt-filter-group').addEventListener('change', function () {
                    curGroup = this.value;
                    curPage = 1;
                    render();
                });

                /* ── Event: paging ────────────────────────────────────────── */
                document.getElementById('rt-prev').addEventListener('click', function () {
                    if (curPage > 1) { curPage--; render(); }
                });
                document.getElementById('rt-next').addEventListener('click', function () {
                    var totalPages = Math.ceil(totalFiltered / PER_PAGE);
                    if (curPage < totalPages) { curPage++; render(); }
                });

                /* ── Event: manual refresh ────────────────────────────────── */
                document.getElementById('rt-refresh-btn').addEventListener('click', function () {
                    clearInterval(refreshTimer);
                    fetchRT(false);
                    refreshTimer = setInterval(function () { fetchRT(true); }, INTERVAL_MS);
                });

                /* ── Auto refresh ─────────────────────────────────────────── */
                refreshTimer = setInterval(function () { fetchRT(true); }, INTERVAL_MS);

                /* ── Init ─────────────────────────────────────────────────── */
                document.getElementById('rt-updated').textContent =
                    new Date().toLocaleTimeString('id-ID');
                render();
                resetCountdown();

            })();
        </script>
    <?php endif ?>

</body>

</html>