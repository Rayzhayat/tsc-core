<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .detail-card {
            border-left: 4px solid #4e73df;
            transition: all 0.3s;
        }
        .info-row {
            padding: 12px 0;
            border-bottom: 1px solid #e3e6f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 15px;
            color: #3a3b45;
        }
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .payment-timeline {
            position: relative;
            padding-left: 30px;
        }
        .payment-timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e3e6f0;
        }
        .payment-item {
            position: relative;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fc;
            border-radius: 8px;
            border-left: 3px solid #1cc88a;
        }
        .payment-item::before {
            content: '';
            position: absolute;
            left: -33px;
            top: 20px;
            width: 16px;
            height: 16px;
            background: #1cc88a;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 0 0 2px #e3e6f0;
        }
        .print-section {
            display: none;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-section {
                display: block !important;
            }
            .card {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 no-print">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-file-invoice text-success"></i> <?= $title ?>
                        </h1>
                        <div>
                            <button onclick="window.print()" class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <?php if ($tagihan->status_payment != 'Paid'): ?>
                                <a href="<?= base_url('tagihan_customer/ubah/' . $tagihan->id) ?>" class="btn btn-warning btn-sm shadow-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            <?php endif ?>
                            <a href="<?= base_url('tagihan_customer') ?>" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <!-- Print Header -->
                    <div class="print-section text-center mb-4">
                        <h2 class="font-weight-bold">DETAIL TAGIHAN CUSTOMER</h2>
                        <p class="text-muted">No Invoice: <?= htmlspecialchars($tagihan->no_invoice) ?></p>
                    </div>

                    <?php 
                    // Tentukan status badge
                    $status_badge = '';
                    $status_text = '';
                    $status_icon = '';
                    
                    if ($tagihan->status_payment == 'Paid') {
                        $status_badge = 'success';
                        $status_text = 'Lunas';
                        $status_icon = 'check-circle';
                    } elseif ($tagihan->status_payment == 'Waiting Payment') {
                        $status_badge = 'danger';
                        $status_text = 'Belum Bayar';
                        $status_icon = 'clock';
                    } elseif ($tagihan->status_payment == 'Partial Payment') {
                        $status_badge = 'warning';
                        $status_text = 'Pembayaran Sebagian';
                        $status_icon = 'exclamation-triangle';
                    } else {
                        $status_badge = 'secondary';
                        $status_text = $tagihan->status_payment;
                        $status_icon = 'info-circle';
                    }
                    ?>

                    <div class="row">
                        <!-- Informasi Tagihan -->
                        <div class="col-lg-8 mb-4">
                            <div class="card shadow detail-card">
                                <div class="card-body">
                                    <div class="section-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-file-invoice"></i> Informasi Tagihan
                                        </h5>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">
                                                    <i class="fas fa-calendar text-primary"></i> Tanggal
                                                </div>
                                                <div class="info-value">
                                                    <?= date('d F Y', strtotime($tagihan->tanggal)) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">
                                                    <i class="fas fa-user text-primary"></i> Customer
                                                </div>
                                                <div class="info-value">
                                                    <?= htmlspecialchars($tagihan->customer_nama ?? $tagihan->nama_customer) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">
                                                    <i class="fas fa-ship text-primary"></i> Bulan Shipment
                                                </div>
                                                <div class="info-value">
                                                    <?= !empty($tagihan->bulan_shipment) ? htmlspecialchars($tagihan->bulan_shipment) : '-' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fas fa-align-left text-primary"></i> Deskripsi
                                        </div>
                                        <div class="info-value">
                                            <?= !empty($tagihan->deskripsi) ? nl2br(htmlspecialchars($tagihan->deskripsi)) : '<em class="text-muted">Tidak ada deskripsi</em>' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Perhitungan -->
                            <div class="card shadow detail-card mt-4">
                                <div class="card-body">
                                    <div class="section-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-calculator"></i> Perhitungan
                                        </h5>
                                    </div>

                                    <div class="info-row">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="info-label">Nominal</div>
                                            <div class="info-value">
                                                Rp <?= number_format($tagihan->nominal, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-row">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="info-label">
                                                PPN <small class="text-success">(+)</small>
                                            </div>
                                            <div class="info-value text-success">
                                                Rp <?= number_format($tagihan->ppn, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-row">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="info-label">
                                                PPH <small class="text-danger">(-)</small>
                                            </div>
                                            <div class="info-value text-danger">
                                                Rp <?= number_format($tagihan->pph, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-row bg-light" style="padding: 20px; border-radius: 8px;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0 text-primary">Total Tagihan</h5>
                                                <small class="text-muted">Nominal + PPN - PPH</small>
                                            </div>
                                            <div>
                                                <h4 class="mb-0 text-primary font-weight-bold">
                                                    Rp <?= number_format($tagihan->total_tagihan, 0, ',', '.') ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Riwayat Pembayaran -->
                            <?php if (!empty($payments)): ?>
                                <div class="card shadow detail-card mt-4">
                                    <div class="card-body">
                                        <div class="section-header">
                                            <h5 class="mb-0">
                                                <i class="fas fa-history"></i> Riwayat Pembayaran
                                            </h5>
                                        </div>

                                        <div class="payment-timeline">
                                            <?php foreach ($payments as $pay): ?>
                                                <div class="payment-item">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="font-weight-bold text-success mb-1">
                                                                Rp <?= number_format($pay->jumlah_bayar, 0, ',', '.') ?>
                                                            </h6>
                                                            <p class="mb-1 text-muted">
                                                                <i class="fas fa-calendar"></i> 
                                                                <?= date('d F Y', strtotime($pay->tanggal_bayar)) ?>
                                                            </p>
                                                            <?php if (!empty($pay->keterangan)): ?>
                                                                <p class="mb-0 text-muted small">
                                                                    <i class="fas fa-comment"></i> <?= htmlspecialchars($pay->keterangan) ?>
                                                                </p>
                                                            <?php endif ?>
                                                        </div>
                                                        <span class="badge badge-success">Paid</span>
                                                    </div>
                                                </div>
                                            <?php endforeach ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- Status Card -->
                            <div class="card shadow mb-4 border-left-<?= $status_badge ?>" style="border-left-width: 4px;">
                                <div class="card-body text-center py-4">
                                    <i class="fas fa-<?= $status_icon ?> fa-3x text-<?= $status_badge ?> mb-3"></i>
                                    <h5 class="font-weight-bold text-<?= $status_badge ?> mb-2">
                                        <?= $status_text ?>
                                    </h5>
                                    <p class="text-muted mb-0 small">Status Pembayaran</p>
                                </div>
                            </div>

                            <!-- Summary Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header bg-gradient-primary text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-info-circle"></i> Ringkasan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="info-row">
                                        <div class="info-label small">Total Tagihan</div>
                                        <div class="info-value">
                                            <strong>Rp <?= number_format($tagihan->total_tagihan, 0, ',', '.') ?></strong>
                                        </div>
                                    </div>

                                    <?php if (!empty($payments)): 
                                        $total_terbayar = array_sum(array_column($payments, 'jumlah_bayar'));
                                        $sisa_tagihan = $tagihan->total_tagihan - $total_terbayar;
                                    ?>
                                        <div class="info-row">
                                            <div class="info-label small">Terbayar</div>
                                            <div class="info-value text-success">
                                                Rp <?= number_format($total_terbayar, 0, ',', '.') ?>
                                            </div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-label small">Sisa Tagihan</div>
                                            <div class="info-value text-danger">
                                                <strong>Rp <?= number_format($sisa_tagihan, 0, ',', '.') ?></strong>
                                            </div>
                                        </div>
                                    <?php endif ?>

                                    <?php if (!empty($tagihan->kode_payment)): ?>
                                        <div class="info-row">
                                            <div class="info-label small">Kode Payment</div>
                                            <div class="info-value">
                                                <code><?= htmlspecialchars($tagihan->kode_payment) ?></code>
                                            </div>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>

                            <!-- Actions Card -->
                            <div class="card shadow no-print">
                                <div class="card-header bg-gradient-info text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-cogs"></i> Aksi
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($tagihan->status_payment != 'Paid'): ?>
                                        <a href="<?= base_url('pembayaran/customer/' . $tagihan->id) ?>" class="btn btn-success btn-block mb-2">
                                            <i class="fas fa-money-bill-wave"></i> Bayar Tagihan
                                        </a>
                                        <a href="<?= base_url('tagihan_customer/ubah/' . $tagihan->id) ?>" class="btn btn-warning btn-block mb-2">
                                            <i class="fas fa-edit"></i> Edit Tagihan
                                        </a>
                                    <?php endif ?>
                                    <button onclick="window.print()" class="btn btn-secondary btn-block mb-2">
                                        <i class="fas fa-print"></i> Print Invoice
                                    </button>
                                    <a href="<?= base_url('tagihan_customer') ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
</body>
</html>