<!-- index.php - FIXED: Due date calculation using start_date -->
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
    <style>

        .periode-filter .form-control {
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            border: none;
        }
        .ocas-card {
            border-left: 5px solid;
            transition: all 0.3s;
            cursor: pointer;
        }
        .ocas-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.2)!important;
        }
        .ocas-pph23 {
            border-left-color: #e74a3b;
        }
        .ocas-pph42 {
            border-left-color: #f6c23e;
        }
        .ocas-ppn {
            border-left-color: #1cc88a;
        }
        .balance-amount {
            font-size: 2rem;
            font-weight: 700;
        }
        .periode-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .info-box {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .payment-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 11px;
        }
        .badge-pph23 {
            background: #e74a3b;
            color: white;
        }
        .badge-pph42 {
            background: #f6c23e;
            color: white;
        }
        .badge-ppn {
            background: #1cc88a;
            color: white;
        }
        
        /* 🔥 ALERT ANIMATIONS */
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .alert-animated {
            animation: slideInDown 0.5s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
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
                    
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-hand-holding-usd text-success"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('pembayaran_pajak/bayar?periode=' . $current_periode) ?>" 
                               class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-money-bill-wave"></i> Bayar Pajak
                            </a>
                            <button onclick="exportExcel()" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- 🔥 PERIODE FILTER -->
                    <div class="periode-filter shadow-lg">
                        <form method="get" action="<?= base_url('pembayaran_pajak') ?>" id="periodeForm">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center text-white mb-3 mb-md-0">
                                    <i class="fas fa-calendar-alt fa-3x mb-2"></i>
                                    <h5 class="mb-0 font-weight-bold">Filter Periode</h5>
                                    <small>Pajak per bulan</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-white font-weight-bold mb-2">
                                        <i class="fas fa-filter"></i> Pilih Periode (Bulan & Tahun):
                                    </label>
                                    <input type="month" name="periode" class="form-control form-control-lg" 
                                           value="<?= $current_periode ?>" 
                                           onchange="this.form.submit()">
                                    <small class="text-white-50">
                                        <i class="fas fa-info-circle"></i> 
                                        Menampilkan pajak yang dipotong/dipungut & dibayar dalam periode ini
                                    </small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <h6 class="text-white mb-2">Periode Aktif:</h6>
                                    <h4 class="text-white font-weight-bold mb-2">
                                        <?= $periode_label ?>
                                    </h4>
                                    <button type="button" onclick="resetToCurrentMonth()" 
                                            class="btn btn-light btn-sm">
                                        <i class="fas fa-sync"></i> Bulan Ini
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Info Box -->
                    <?php
                    // 🔥 FIX: Calculate due date - 15th of NEXT month using start_date
                    $due_date_info = date('Y-m-15', strtotime($start_date . ' +1 month'));
                    ?>
                    <div class="info-box shadow-lg">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <i class="fas fa-lightbulb fa-3x"></i>
                            </div>
                            <div class="col-md-11">
                                <h5 class="mb-2"><i class="fas fa-info-circle"></i> Cara Kerja Sistem Pajak</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <strong>PPH (dari Vendor):</strong> Pajak dipotong saat bayar vendor (<?= $periode_label ?>), 
                                            tercatat sebagai hutang ke negara.
                                        </p>
                                        <p class="mb-0">
                                            <small><i class="fas fa-calendar-alt"></i> Batas bayar: <strong class="text-warning"><?= date('d F Y', strtotime($due_date_info)) ?></strong></small>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <strong>PPN (dari Customer):</strong> Pajak dipungut saat invoice customer, 
                                            tercatat sebagai hutang ke negara.
                                        </p>
                                        <p class="mb-0">
                                            <small><i class="fas fa-calendar-alt"></i> Batas bayar: <strong class="text-warning"><?= date('d F Y', strtotime($due_date_info)) ?></strong></small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 🔥 DUE DATE ALERT BANNER -->
                    <?php
                    $total_hutang = round($pph23_balance, 0) + round($pph42_balance, 0) + round($ppn_balance, 0);
                    
                    // 🔥 FIX: Calculate due date - 15th of NEXT month using start_date
                    $due_date = date('Y-m-15', strtotime($start_date . ' +1 month'));
                    
                    $today = date('Y-m-d');
                    
                    // Check if overdue
                    $pph_overdue = ($today > $due_date) && (round($pph23_balance, 0) > 0 || round($pph42_balance, 0) > 0);
                    $ppn_overdue = ($today > $due_date) && round($ppn_balance, 0) > 0;
                    
                    // Check if approaching due date (1-5 days before)
                    $days_left = (strtotime($due_date) - strtotime($today)) / 86400;
                    
                    $pph_approaching = ($days_left > 0 && $days_left <= 5) && (round($pph23_balance, 0) > 0 || round($pph42_balance, 0) > 0);
                    $ppn_approaching = ($days_left > 0 && $days_left <= 5) && round($ppn_balance, 0) > 0;
                    
                    $show_critical_alert = $pph_overdue || $ppn_overdue;
                    $show_warning_alert = !$show_critical_alert && ($pph_approaching || $ppn_approaching);
                    ?>
                    
                    <!-- 🚨 CRITICAL ALERT: OVERDUE -->
                    <?php if ($show_critical_alert): ?>
                        <div class="alert alert-danger border-left-danger shadow-lg alert-animated" style="border-left-width: 5px !important;">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    <i class="fas fa-exclamation-circle fa-4x text-danger" style="animation: pulse 1.5s infinite;"></i>
                                </div>
                                <div class="col-md-11">
                                    <h4 class="text-danger mb-3">
                                        <i class="fas fa-alarm-clock"></i> <strong>PERINGATAN: PAJAK SUDAH JATUH TEMPO!</strong>
                                    </h4>
                                    
                                    <?php if ($pph_overdue): ?>
                                        <?php 
                                        $pph_total = round($pph23_balance, 0) + round($pph42_balance, 0);
                                        $days_late = abs(floor($days_left));
                                        ?>
                                        <div class="mb-3 p-3" style="background: rgba(231, 74, 59, 0.1); border-radius: 8px; border-left: 4px solid #e74a3b;">
                                            <h6 class="text-danger mb-2">
                                                <i class="fas fa-file-invoice"></i> <strong>PPH Periode <?= $periode_label ?></strong>
                                            </h6>
                                            <p class="mb-2">
                                                💰 Total Hutang: <strong class="text-danger">Rp <?= number_format($pph_total, 0, ',', '.') ?></strong>
                                            </p>
                                            <p class="mb-1">
                                                📅 Jatuh Tempo: <strong><?= date('d F Y', strtotime($due_date)) ?></strong>
                                            </p>
                                            <p class="mb-0">
                                                ⏰ Status: <span class="badge badge-danger badge-pill px-3">TERLAMBAT <?= $days_late ?> HARI</span>
                                            </p>
                                        </div>
                                    <?php endif ?>
                                    
                                    <?php if ($ppn_overdue): ?>
                                        <?php 
                                        $days_late = abs(floor($days_left));
                                        ?>
                                        <div class="mb-3 p-3" style="background: rgba(231, 74, 59, 0.1); border-radius: 8px; border-left: 4px solid #e74a3b;">
                                            <h6 class="text-danger mb-2">
                                                <i class="fas fa-file-invoice-dollar"></i> <strong>PPN Periode <?= $periode_label ?></strong>
                                            </h6>
                                            <p class="mb-2">
                                                💰 Total Hutang: <strong class="text-danger">Rp <?= number_format(round($ppn_balance, 0), 0, ',', '.') ?></strong>
                                            </p>
                                            <p class="mb-1">
                                                📅 Jatuh Tempo: <strong><?= date('d F Y', strtotime($due_date)) ?></strong>
                                            </p>
                                            <p class="mb-0">
                                                ⏰ Status: <span class="badge badge-danger badge-pill px-3">TERLAMBAT <?= $days_late ?> HARI</span>
                                            </p>
                                        </div>
                                    <?php endif ?>
                                    
                                    <div class="mt-3">
                                        <a href="<?= base_url('pembayaran_pajak/bayar?periode=' . $current_periode) ?>" 
                                           class="btn btn-danger btn-lg shadow-lg">
                                            <i class="fas fa-money-bill-wave"></i> <strong>BAYAR SEKARANG!</strong>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                    <!-- ⚠️ WARNING ALERT: APPROACHING DUE DATE -->
                    <?php elseif ($show_warning_alert): ?>
                        <div class="alert alert-warning border-left-warning shadow alert-animated" style="border-left-width: 5px !important;">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                                </div>
                                <div class="col-md-11">
                                    <h5 class="text-warning mb-3">
                                        <i class="fas fa-bell"></i> <strong>PENGINGAT: Mendekati Jatuh Tempo Pembayaran</strong>
                                    </h5>
                                    
                                    <?php if ($pph_approaching): ?>
                                        <?php 
                                        $pph_total = round($pph23_balance, 0) + round($pph42_balance, 0);
                                        $days = ceil($days_left);
                                        ?>
                                        <div class="mb-3 p-3" style="background: rgba(246, 194, 62, 0.1); border-radius: 8px; border-left: 4px solid #f6c23e;">
                                            <h6 class="text-dark mb-2">
                                                <i class="fas fa-file-invoice"></i> <strong>PPH Periode <?= $periode_label ?></strong>
                                            </h6>
                                            <p class="mb-2">
                                                💰 Total Hutang: <strong>Rp <?= number_format($pph_total, 0, ',', '.') ?></strong>
                                            </p>
                                            <p class="mb-1">
                                                📅 Jatuh Tempo: <strong><?= date('d F Y', strtotime($due_date)) ?></strong>
                                            </p>
                                            <p class="mb-0">
                                                ⏰ Sisa Waktu: <span class="badge badge-warning badge-pill px-3"><?= $days ?> HARI LAGI</span>
                                            </p>
                                        </div>
                                    <?php endif ?>
                                    
                                    <?php if ($ppn_approaching): ?>
                                        <?php 
                                        $days = ceil($days_left);
                                        ?>
                                        <div class="mb-3 p-3" style="background: rgba(246, 194, 62, 0.1); border-radius: 8px; border-left: 4px solid #f6c23e;">
                                            <h6 class="text-dark mb-2">
                                                <i class="fas fa-file-invoice-dollar"></i> <strong>PPN Periode <?= $periode_label ?></strong>
                                            </h6>
                                            <p class="mb-2">
                                                💰 Total Hutang: <strong>Rp <?= number_format(round($ppn_balance, 0), 0, ',', '.') ?></strong>
                                            </p>
                                            <p class="mb-1">
                                                📅 Jatuh Tempo: <strong><?= date('d F Y', strtotime($due_date)) ?></strong>
                                            </p>
                                            <p class="mb-0">
                                                ⏰ Sisa Waktu: <span class="badge badge-warning badge-pill px-3"><?= $days ?> HARI LAGI</span>
                                            </p>
                                        </div>
                                    <?php endif ?>
                                    
                                    <div class="mt-2">
                                        <a href="<?= base_url('pembayaran_pajak/bayar?periode=' . $current_periode) ?>" 
                                           class="btn btn-warning shadow">
                                            <i class="fas fa-money-bill-wave"></i> Bayar Sebelum Jatuh Tempo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                    <!-- 📊 STATUS ALERT: NORMAL -->
                    <?php elseif ($total_hutang > 0): ?>
                        <div class="alert alert-info border-left-info shadow">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    <i class="fas fa-info-circle fa-3x"></i>
                                </div>
                                <div class="col-md-11">
                                    <h5 class="text-info mb-2">
                                        <i class="fas fa-clipboard-list"></i> Ada Pajak Periode <?= $periode_label ?> yang Belum Dibayar
                                    </h5>
                                    <p class="mb-2">
                                        💰 Total Hutang: <strong class="text-primary">Rp <?= number_format($total_hutang, 0, ',', '.') ?></strong>
                                    </p>
                                    <p class="mb-0">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-check"></i> Batas pembayaran: 
                                            <strong><?= date('d F Y', strtotime($due_date)) ?></strong>
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    
                    <!-- ✅ SUCCESS: PAID -->
                    <?php else: ?>
                        <div class="alert alert-success border-left-success shadow">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    <i class="fas fa-check-circle fa-3x"></i>
                                </div>
                                <div class="col-md-11">
                                    <h5 class="text-success mb-2">
                                        <i class="fas fa-thumbs-up"></i> Semua Pajak Periode <?= $periode_label ?> Sudah Dibayar!
                                    </h5>
                                    <p class="mb-0">
                                        Tidak ada hutang pajak untuk periode ini. Saldo: <strong class="text-success">Rp 0</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                    <!-- Tax Balance Cards FOR SELECTED PERIOD -->
                    <div class="row mb-4">
                        <!-- PPH 23 Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card ocas-card ocas-pph23 shadow h-100 py-3">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="text-xs font-weight-bold text-danger text-uppercase">
                                                    <i class="fas fa-receipt"></i> PPH Pasal 23
                                                </div>
                                                <?php if (round($pph23_balance, 0) > 0): ?>
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-exclamation-circle"></i> Belum Bayar
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> Lunas
                                                    </span>
                                                <?php endif ?>
                                            </div>
                                            <div class="balance-amount text-danger mb-2">
                                                Rp <?= number_format(round($pph23_balance, 0), 0, ',', '.') ?>
                                            </div>
                                            <div class="text-xs text-muted mb-2">
                                                <i class="fas fa-book"></i> Akun: <?= $pph23_akun ? $pph23_akun->kode_perkiraan . ' - ' . $pph23_akun->nama : 'N/A' ?>
                                            </div>
                                            <div class="text-xs text-primary">
                                                <i class="fas fa-calendar"></i> Periode: <strong><?= $periode_label ?></strong>
                                            </div>
                                            <?php if (round($pph23_balance, 0) > 0): ?>
                                                <div class="mt-3">
                                                    <a href="<?= base_url('pembayaran_pajak/bayar?jenis=pph23&periode=' . $current_periode) ?>" 
                                                       class="btn btn-danger btn-sm btn-block">
                                                        <i class="fas fa-money-bill-wave"></i> Bayar Sekarang
                                                    </a>
                                                </div>
                                            <?php endif ?>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-invoice-dollar fa-3x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PPH 4(2) Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card ocas-card ocas-pph42 shadow h-100 py-3">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase">
                                                    <i class="fas fa-building"></i> PPH Pasal 4(2)
                                                </div>
                                                <?php if (round($pph42_balance, 0) > 0): ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-exclamation-circle"></i> Belum Bayar
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> Lunas
                                                    </span>
                                                <?php endif ?>
                                            </div>
                                            <div class="balance-amount text-warning mb-2">
                                                Rp <?= number_format(round($pph42_balance, 0), 0, ',', '.') ?>
                                            </div>
                                            <div class="text-xs text-muted mb-2">
                                                <i class="fas fa-book"></i> Akun: <?= $pph42_akun ? $pph42_akun->kode_perkiraan . ' - ' . $pph42_akun->nama : 'N/A' ?>
                                            </div>
                                            <div class="text-xs text-primary">
                                                <i class="fas fa-calendar"></i> Periode: <strong><?= $periode_label ?></strong>
                                            </div>
                                            <?php if (round($pph42_balance, 0) > 0): ?>
                                                <div class="mt-3">
                                                    <a href="<?= base_url('pembayaran_pajak/bayar?jenis=pph42&periode=' . $current_periode) ?>" 
                                                       class="btn btn-warning btn-sm btn-block">
                                                        <i class="fas fa-money-bill-wave"></i> Bayar Sekarang
                                                    </a>
                                                </div>
                                            <?php endif ?>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-warehouse fa-3x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 🔥 PPN KELUARAN Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card ocas-card ocas-ppn shadow h-100 py-3">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase">
                                                    <i class="fas fa-file-invoice-dollar"></i> PPN Keluaran
                                                </div>
                                                <?php if (round($ppn_balance, 0) > 0): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-exclamation-circle"></i> Belum Bayar
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-check-circle"></i> Lunas
                                                    </span>
                                                <?php endif ?>
                                            </div>
                                            <div class="balance-amount text-success mb-2">
                                                Rp <?= number_format(round($ppn_balance, 0), 0, ',', '.') ?>
                                            </div>
                                            <div class="text-xs text-muted mb-2">
                                                <i class="fas fa-book"></i> Akun: <?= $ppn_akun ? $ppn_akun->kode_perkiraan . ' - ' . $ppn_akun->nama : 'N/A' ?>
                                            </div>
                                            <div class="text-xs text-primary">
                                                <i class="fas fa-calendar"></i> Periode: <strong><?= $periode_label ?></strong>
                                            </div>
                                            <?php if (round($ppn_balance, 0) > 0): ?>
                                                <div class="mt-3">
                                                    <a href="<?= base_url('pembayaran_pajak/bayar?jenis=ppn_keluaran&periode=' . $current_periode) ?>" 
                                                       class="btn btn-success btn-sm btn-block">
                                                        <i class="fas fa-money-bill-wave"></i> Bayar Sekarang
                                                    </a>
                                                </div>
                                            <?php endif ?>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-receipt fa-3x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-gradient-primary text-white">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-history"></i> Riwayat Pembayaran - <?= $periode_label ?>
                                    </h6>
                                </div>
                                <div class="col-md-6 text-right">
                                    <span class="badge badge-light">
                                        Total: <?= count($payments) ?> transaksi
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($payments)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-4x text-gray-300 mb-3"></i>
                                    <h5 class="text-muted">Belum Ada Pembayaran Pajak di Periode Ini</h5>
                                    <p class="text-muted">
                                        Periode: <?= $periode_label ?><br>
                                        <?php if ($total_hutang > 0): ?>
                                            Silakan bayar pajak jika ada saldo yang belum dibayar.
                                        <?php endif ?>
                                    </p>
                                    <?php if ($total_hutang > 0): ?>
                                        <a href="<?= base_url('pembayaran_pajak/bayar?periode=' . $current_periode) ?>" 
                                           class="btn btn-success">
                                            <i class="fas fa-plus"></i> Bayar Pajak Periode Ini
                                        </a>
                                    <?php endif ?>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="5%" class="text-center">No</th>
                                                <th width="10%">Reff No</th>
                                                <th width="10%">Tanggal Bayar</th>
                                                <th width="12%">Jenis Pajak</th>
                                                <th width="13%">Masa Pajak</th>
                                                <th width="12%">Bukti Potong</th>
                                                <th width="13%" class="text-right">Nominal</th>
                                                <th width="13%">Bank</th>
                                                <th width="12%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1;
                                            $total_paid = 0;
                                            foreach ($payments as $payment): 
                                                $badge_class = 'badge-secondary';
                                                $badge_icon = 'fa-file';
                                                $jenis_display = strtoupper($payment->jenis_pajak);
                                                
                                                switch(strtoupper($payment->jenis_pajak)) {
                                                    case 'PPH23':
                                                        $badge_class = 'badge-pph23';
                                                        $badge_icon = 'fa-receipt';
                                                        $jenis_display = 'PPH 23';
                                                        break;
                                                    case 'PPH42':
                                                        $badge_class = 'badge-pph42';
                                                        $badge_icon = 'fa-building';
                                                        $jenis_display = 'PPH 4(2)';
                                                        break;
                                                    case 'PPN_KELUARAN':
                                                        $badge_class = 'badge-ppn';
                                                        $badge_icon = 'fa-file-invoice-dollar';
                                                        $jenis_display = 'PPN Keluaran';
                                                        break;
                                                }
                                                
                                                $total_paid += round($payment->nominal, 0);
                                            ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <strong class="text-primary">
                                                            <?= htmlspecialchars($payment->reff_no) ?>
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-calendar"></i>
                                                        <?= date('d/m/Y', strtotime($payment->tanggal_bayar)) ?>
                                                    </td>
                                                    <td>
                                                        <span class="payment-badge <?= $badge_class ?>">
                                                            <i class="fas <?= $badge_icon ?>"></i> <?= $jenis_display ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar-check"></i>
                                                            <?= htmlspecialchars($payment->masa_pajak ?: '-') ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <i class="fas fa-file-alt"></i>
                                                            <?= htmlspecialchars($payment->no_bukti_potong ?: '-') ?>
                                                        </small>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-success">
                                                            Rp <?= number_format(round($payment->nominal, 0), 0, ',', '.') ?>
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <i class="fas fa-university"></i>
                                                            <?= htmlspecialchars($payment->nama_bank) ?>
                                                        </small>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button type="button" 
                                                                    class="btn btn-info" 
                                                                    title="Detail"
                                                                    onclick="showDetail(<?= htmlspecialchars(json_encode($payment)) ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <a href="<?= base_url('pembayaran_pajak/hapus/' . $payment->id . '?periode=' . $current_periode) ?>"
                                                               onclick="return confirmDelete('<?= htmlspecialchars($payment->reff_no) ?>', '<?= $jenis_display ?>', <?= round($payment->nominal, 0) ?>)"
                                                               class="btn btn-danger" 
                                                               title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <th colspan="6" class="text-right">TOTAL DIBAYAR (<?= $periode_label ?>):</th>
                                                <th class="text-right text-success">
                                                    Rp <?= number_format($total_paid, 0, ',', '.') ?>
                                                </th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice-dollar"></i> Detail Pembayaran Pajak
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body" id="detailContent">
                    <!-- Content filled by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    
    <script>
        function resetToCurrentMonth() {
            const currentMonth = '<?= date('Y-m') ?>';
            window.location.href = '<?= base_url('pembayaran_pajak') ?>?periode=' + currentMonth;
        }

        function exportExcel() {
            const periode = '<?= $current_periode ?>';
            window.location.href = '<?= base_url('pembayaran_pajak/export_excel') ?>?periode=' + periode;
        }

        function confirmDelete(reffNo, jenisPajak, nominal) {
            return confirm(
                '⚠️ PERHATIAN!\n\n' +
                'Yakin hapus pembayaran ' + reffNo + '?\n' +
                'Jenis: ' + jenisPajak + '\n' +
                'Nominal: Rp ' + formatNumber(nominal) + '\n\n' +
                '❌ Journal entry akan dihapus\n' +
                '🔄 Saldo akan kembali (hutang bertambah)\n\n' +
                'Lanjutkan?'
            );
        }

        function showDetail(payment) {
            let jenis = payment.jenis_pajak;
            let badgeClass = 'badge-secondary';
            
            switch(payment.jenis_pajak.toUpperCase()) {
                case 'PPH23':
                    jenis = 'PPH Pasal 23';
                    badgeClass = 'badge-danger';
                    break;
                case 'PPH42':
                    jenis = 'PPH Pasal 4 Ayat 2';
                    badgeClass = 'badge-warning';
                    break;
                case 'PPN_KELUARAN':
                    jenis = 'PPN Keluaran';
                    badgeClass = 'badge-success';
                    break;
            }
            
            const nominal = Math.round(parseFloat(payment.nominal)) || 0;
            
            const html = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Reff No</label>
                        <p class="font-weight-bold">${payment.reff_no}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Jenis Pajak</label>
                        <p><span class="badge ${badgeClass}">${jenis}</span></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Tanggal Pembayaran</label>
                        <p class="font-weight-bold">${formatDate(payment.tanggal_bayar)}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Masa Pajak</label>
                        <p class="font-weight-bold">${payment.masa_pajak || '-'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Bukti Potong</label>
                        <p class="font-weight-bold">${payment.no_bukti_potong || '-'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Nominal</label>
                        <p class="font-weight-bold text-success">Rp ${formatNumber(nominal)}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Bank</label>
                        <p class="font-weight-bold">${payment.nama_bank}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Dibuat Oleh</label>
                        <p class="font-weight-bold">${payment.created_by} - ${formatDate(payment.created_at)}</p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="text-muted small">Keterangan</label>
                        <p>${payment.keterangan || '-'}</p>
                    </div>
                </div>
            `;
            
            $('#detailContent').html(html);
            $('#detailModal').modal('show');
        }

        function formatNumber(num) {
            const intNum = Math.round(parseFloat(num)) || 0;
            return intNum.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }
    </script>
</body>
</html>