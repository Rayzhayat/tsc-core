<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
            color: white;
            border-radius: 10px;
        }

        .report-section {
            margin-bottom: 30px;
            border: 2px solid #e3e6f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .section-header {
            padding: 15px 20px;
            font-weight: bold;
            font-size: 16px;
            color: white;
        }

        .section-header.aset {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .section-header.liabilitas {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
        }

        .section-header.ekuitas {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        }

        .section-body {
            padding: 20px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 15px;
            border-bottom: 1px solid #e3e6f0;
            transition: all 0.2s;
        }

        .item-row:hover {
            background-color: #f8f9fc;
            padding-left: 25px;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-code {
            color: #858796;
            font-size: 13px;
            margin-right: 10px;
        }

        .item-name {
            flex: 1;
            font-weight: 500;
        }

        .item-amount {
            font-weight: 600;
            color: #5a5c69;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 20px;
            background-color: #f8f9fc;
            font-weight: bold;
            font-size: 16px;
            border-top: 3px solid #4e73df;
        }

        .balance-card {
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .balance-card.balanced {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
        }

        .balance-card.unbalanced {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
            color: white;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .report-section {
                page-break-inside: avoid;
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 no-print">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-balance-scale text-info"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('laporan_keuangan?start_date=' . date('Y-m-01', strtotime($tanggal)) . '&end_date=' . $tanggal) ?>"
                                class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button onclick="window.print()" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <a href="<?= base_url('laporan_keuangan/neraca_pdf?tanggal=' . $tanggal) ?>" target="_blank"
                                class="btn btn-danger btn-sm shadow-sm">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                            <button onclick="alert('Export Excel coming soon')"
                                class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>

                    <!-- Filter Tanggal -->
                    <div class="card shadow mb-4 no-print">
                        <div class="card-header py-3 bg-info text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-calendar"></i> Pilih Tanggal
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('laporan_keuangan/neraca') ?>" class="form-inline">
                                <label class="mr-2">Per Tanggal:</label>
                                <input type="date" name="tanggal" class="form-control mr-3" value="<?= $tanggal ?>"
                                    required>

                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-search"></i> Tampilkan
                                </button>

                                <a href="<?= base_url('laporan_keuangan/neraca') ?>" class="btn btn-secondary ml-2">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </form>
                        </div>
                    </div>

                    <!-- Report Header -->
                    <div class="report-header">
                        <h2 class="mb-1">NERACA (BALANCE SHEET)</h2>
                        <h4 class="mb-0">Per Tanggal: <?= date('d M Y', strtotime($tanggal)) ?></h4>
                    </div>

                    <div class="row">
                        <!-- LEFT SIDE: ASET -->
                        <div class="col-md-6">
                            <!-- ASET -->
                            <div class="report-section">
                                <div class="section-header aset">
                                    <i class="fas fa-building mr-2"></i> ASET (ASSETS)
                                </div>
                                <div class="section-body">
                                    <?php if (empty($aset)): ?>
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>Tidak ada data aset</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($aset as $item): ?>
                                            <div class="item-row">
                                                <div class="item-name">
                                                    <span class="item-code"><?= $item['kode_perkiraan'] ?></span>
                                                    <?= $item['nama'] ?>
                                                </div>
                                                <div class="item-amount text-primary">
                                                    Rp <?= number_format($item['saldo'], 0, ',', '.') ?>
                                                </div>
                                            </div>
                                        <?php endforeach ?>
                                    <?php endif ?>

                                    <div class="total-row">
                                        <div>TOTAL ASET</div>
                                        <div class="text-primary">Rp <?= number_format($total_aset, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT SIDE: PASSIVA -->
                        <div class="col-md-6">
                            <!-- LIABILITAS -->
                            <div class="report-section">
                                <div class="section-header liabilitas">
                                    <i class="fas fa-hand-holding-usd mr-2"></i> LIABILITAS (LIABILITIES)
                                </div>
                                <div class="section-body">
                                    <?php if (empty($liabilitas)): ?>
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>Tidak ada data liabilitas</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($liabilitas as $item): ?>
                                            <div class="item-row">
                                                <div class="item-name">
                                                    <span class="item-code"><?= $item['kode_perkiraan'] ?></span>
                                                    <?= $item['nama'] ?>
                                                </div>
                                                <div class="item-amount text-danger">
                                                    Rp <?= number_format($item['saldo'], 0, ',', '.') ?>
                                                </div>
                                            </div>
                                        <?php endforeach ?>
                                    <?php endif ?>

                                    <div class="total-row">
                                        <div>TOTAL LIABILITAS</div>
                                        <div class="text-danger">Rp <?= number_format($total_liabilitas, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- EKUITAS -->
                            <div class="report-section">
                                <div class="section-header ekuitas">
                                    <i class="fas fa-coins mr-2"></i> EKUITAS (EQUITY)
                                </div>
                                <div class="section-body">
                                    <?php if (empty($ekuitas)): ?>
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>Tidak ada data ekuitas</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($ekuitas as $item): ?>
                                            <div class="item-row">
                                                <div class="item-name">
                                                    <span class="item-code"><?= $item['kode_perkiraan'] ?></span>
                                                    <?= $item['nama'] ?>
                                                </div>
                                                <div class="item-amount text-warning">
                                                    Rp <?= number_format($item['saldo'], 0, ',', '.') ?>
                                                </div>
                                            </div>
                                        <?php endforeach ?>
                                    <?php endif ?>

                                    <div class="total-row">
                                        <div>TOTAL EKUITAS</div>
                                        <div class="text-warning">Rp <?= number_format($total_ekuitas, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TOTAL PASSIVA -->
                            <div class="card shadow">
                                <div class="card-body bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 font-weight-bold">TOTAL PASSIVA</h5>
                                        <h5 class="mb-0 font-weight-bold text-primary">
                                            Rp <?= number_format($total_passiva, 0, ',', '.') ?>
                                        </h5>
                                    </div>
                                    <small class="text-muted">Liabilitas + Ekuitas</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Check -->
                    <div class="row mt-4 mb-5">
                        <div class="col-12">
                            <?php
                            $is_balanced = abs($total_aset - $total_passiva) < 1; // Allow 1 rupiah difference
                            ?>
                            <div class="balance-card <?= $is_balanced ? 'balanced' : 'unbalanced' ?>">
                                <h3 class="mb-3">
                                    <i class="fas fa-<?= $is_balanced ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                                    <?= $is_balanced ? 'BALANCE!' : 'UNBALANCED!' ?>
                                </h3>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Total Aset</h5>
                                        <h2>Rp <?= number_format($total_aset, 0, ',', '.') ?></h2>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Total Passiva</h5>
                                        <h2>Rp <?= number_format($total_passiva, 0, ',', '.') ?></h2>
                                    </div>
                                </div>
                                <?php if (!$is_balanced): ?>
                                    <div class="mt-3">
                                        <strong>Selisih:</strong> Rp
                                        <?= number_format(abs($total_aset - $total_passiva), 0, ',', '.') ?>
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-gradient-info text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-table"></i> Ringkasan Neraca
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">ASET</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Total Aset</td>
                                            <td class="text-right font-weight-bold">
                                                Rp <?= number_format($total_aset, 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="text-danger mb-3">PASSIVA</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Total Liabilitas</td>
                                            <td class="text-right">
                                                Rp <?= number_format($total_liabilitas, 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Total Ekuitas</td>
                                            <td class="text-right">
                                                Rp <?= number_format($total_ekuitas, 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                        <tr class="font-weight-bold bg-light">
                                            <td>Total Passiva</td>
                                            <td class="text-right">
                                                Rp <?= number_format($total_passiva, 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    </table>
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