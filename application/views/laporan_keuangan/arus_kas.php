<!-- arus_kas.php with Charts -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
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
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }

        .section-body {
            padding: 20px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid #e3e6f0;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-date {
            color: #858796;
            font-size: 13px;
            margin-right: 15px;
            min-width: 100px;
        }

        .item-desc {
            flex: 1;
            font-weight: 500;
        }

        .item-amount {
            font-weight: 600;
            min-width: 150px;
            text-align: right;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 20px;
            background-color: #f8f9fc;
            font-weight: bold;
            font-size: 16px;
            border-top: 3px solid #36b9cc;
        }

        .saldo-card {
            padding: 20px;
            border-radius: 10px;
            color: white;
            text-align: center;
            margin-bottom: 20px;
        }

        .saldo-card.awal {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .saldo-card.akhir {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .saldo-card.akhir.negative {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
        }

        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 30px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e3e6f0;
        }

        .chart-title {
            font-size: 18px;
            font-weight: bold;
            color: #5a5c69;
        }

        .chart-subtitle {
            font-size: 13px;
            color: #858796;
            margin-top: 5px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .report-section {
                page-break-inside: avoid;
            }

            .chart-container {
                height: 300px;
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
                            <i class="fas fa-money-bill-wave text-warning"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('laporan_keuangan?start_date=' . $tanggal_awal . '&end_date=' . $tanggal_akhir) ?>"
                                class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button onclick="window.print()" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <a href="<?= base_url('laporan_keuangan/arus_kas_pdf?tanggal_awal=' . $tanggal_awal . '&tanggal_akhir=' . $tanggal_akhir) ?>"
                                target="_blank" class="btn btn-danger btn-sm shadow-sm">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                            <a href="<?= base_url('laporan_keuangan/arus_kas_excel?tanggal_awal=' . $tanggal_awal . '&tanggal_akhir=' . $tanggal_akhir) ?>"
                                class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        </div>
                    </div>

                    <!-- Filter Periode -->
                    <div class="card shadow mb-4 no-print">
                        <div class="card-header py-3 bg-warning text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-filter"></i> Filter Periode
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('laporan_keuangan/arus_kas') ?>"
                                class="form-inline">
                                <label class="mr-2">Dari:</label>
                                <input type="date" name="tanggal_awal" class="form-control mr-3"
                                    value="<?= $tanggal_awal ?>" required>

                                <label class="mr-2">Sampai:</label>
                                <input type="date" name="tanggal_akhir" class="form-control mr-3"
                                    value="<?= $tanggal_akhir ?>" required>

                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-search"></i> Tampilkan
                                </button>

                                <a href="<?= base_url('laporan_keuangan/arus_kas') ?>" class="btn btn-secondary ml-2">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </form>
                        </div>
                    </div>

                    <!-- Report Header -->
                    <div class="report-header">
                        <h2 class="mb-1">LAPORAN ARUS KAS</h2>
                        <h4 class="mb-0">Periode: <?= date('d M Y', strtotime($tanggal_awal)) ?> -
                            <?= date('d M Y', strtotime($tanggal_akhir)) ?>
                        </h4>
                    </div>

                    <!-- Saldo Awal & Akhir -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="saldo-card awal">
                                <h5>Saldo Kas/Bank Awal Periode</h5>
                                <h2>Rp <?= number_format($saldo_awal_kas, 0, ',', '.') ?></h2>
                                <small><?= date('d M Y', strtotime($tanggal_awal)) ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="saldo-card akhir <?= $saldo_akhir_kas < 0 ? 'negative' : '' ?>">
                                <h5>Saldo Kas/Bank Akhir Periode</h5>
                                <h2>Rp <?= number_format($saldo_akhir_kas, 0, ',', '.') ?></h2>
                                <small><?= date('d M Y', strtotime($tanggal_akhir)) ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- GRAFIK VISUALISASI -->
                    <div class="row mb-4">
                        <!-- Bar Chart - Kas Masuk vs Keluar -->
                        <div class="col-lg-6 mb-4">
                            <div class="chart-card">
                                <div class="chart-header">
                                    <div>
                                        <div class="chart-title">
                                            <i class="fas fa-exchange-alt text-primary"></i> Kas Masuk vs Keluar
                                        </div>
                                        <div class="chart-subtitle">Perbandingan arus kas masuk dan keluar</div>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="cashflowBarChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Donut Chart - Breakdown Kas -->
                        <div class="col-lg-6 mb-4">
                            <div class="chart-card">
                                <div class="chart-header">
                                    <div>
                                        <div class="chart-title">
                                            <i class="fas fa-chart-pie text-warning"></i> Komposisi Arus Kas
                                        </div>
                                        <div class="chart-subtitle">Distribusi kas masuk, keluar, dan saldo</div>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="cashflowDonutChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Line Chart - Trend Saldo Kas -->
                    <div class="row mb-4">
                        <div class="col-lg-12 mb-4">
                            <div class="chart-card">
                                <div class="chart-header">
                                    <div>
                                        <div class="chart-title">
                                            <i class="fas fa-chart-line text-info"></i> Trend Saldo Kas
                                        </div>
                                        <div class="chart-subtitle">Pergerakan saldo kas dari awal hingga akhir periode
                                        </div>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="cashTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Horizontal Bar - Top Kas Masuk/Keluar -->
                    <?php
                    // Group mutasi by description for top sources/uses
                    $kas_masuk_grouped = [];
                    $kas_keluar_grouped = [];

                    foreach ($mutasi_kas as $item) {
                        $desc = $item['keterangan'] ?: 'Lainnya';

                        if ($item['kas_masuk'] > 0) {
                            if (!isset($kas_masuk_grouped[$desc])) {
                                $kas_masuk_grouped[$desc] = 0;
                            }
                            $kas_masuk_grouped[$desc] += $item['kas_masuk'];
                        }

                        if ($item['kas_keluar'] > 0) {
                            if (!isset($kas_keluar_grouped[$desc])) {
                                $kas_keluar_grouped[$desc] = 0;
                            }
                            $kas_keluar_grouped[$desc] += $item['kas_keluar'];
                        }
                    }

                    // Sort and get top 5
                    arsort($kas_masuk_grouped);
                    arsort($kas_keluar_grouped);
                    $top_kas_masuk = array_slice($kas_masuk_grouped, 0, 5, true);
                    $top_kas_keluar = array_slice($kas_keluar_grouped, 0, 5, true);
                    ?>

                    <div class="row mb-4">
                        <div class="col-lg-6 mb-4">
                            <div class="chart-card">
                                <div class="chart-header">
                                    <div>
                                        <div class="chart-title">
                                            <i class="fas fa-arrow-down text-success"></i> Top 5 Sumber Kas Masuk
                                        </div>
                                        <div class="chart-subtitle">Transaksi kas masuk terbesar</div>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="topKasMasukChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="chart-card">
                                <div class="chart-header">
                                    <div>
                                        <div class="chart-title">
                                            <i class="fas fa-arrow-up text-danger"></i> Top 5 Penggunaan Kas Keluar
                                        </div>
                                        <div class="chart-subtitle">Transaksi kas keluar terbesar</div>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="topKasKeluarChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mutasi Kas/Bank -->
                    <div class="report-section">
                        <div class="section-header">
                            <i class="fas fa-exchange-alt mr-2"></i> MUTASI KAS & BANK
                        </div>
                        <div class="section-body">
                            <?php if (empty($mutasi_kas)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Tidak ada mutasi kas/bank untuk periode ini</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($mutasi_kas as $item): ?>
                                    <div class="item-row">
                                        <div class="item-date">
                                            <?= date('d/m/Y', strtotime($item['tanggal'])) ?>
                                        </div>
                                        <div class="item-desc">
                                            <?= htmlspecialchars($item['keterangan']) ?>
                                            <?php if (!empty($item['referensi_tipe'])): ?>
                                                <br><small class="text-muted">
                                                    <i class="fas fa-tag"></i> <?= $item['referensi_tipe'] ?>
                                                </small>
                                            <?php endif ?>
                                        </div>
                                        <div class="item-amount">
                                            <?php if ($item['kas_masuk'] > 0): ?>
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-down"></i>
                                                    Rp <?= number_format($item['kas_masuk'], 0, ',', '.') ?>
                                                </span>
                                            <?php endif ?>
                                        </div>
                                        <div class="item-amount">
                                            <?php if ($item['kas_keluar'] > 0): ?>
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-up"></i>
                                                    Rp <?= number_format($item['kas_keluar'], 0, ',', '.') ?>
                                                </span>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            <?php endif ?>

                            <div class="total-row">
                                <div>TOTAL</div>
                                <div class="text-success">
                                    <i class="fas fa-arrow-down"></i>
                                    Rp <?= number_format($total_kas_masuk, 0, ',', '.') ?>
                                </div>
                                <div class="text-danger">
                                    <i class="fas fa-arrow-up"></i>
                                    Rp <?= number_format($total_kas_keluar, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-warning text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-calculator"></i> Ringkasan Arus Kas
                            </h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <td class="font-weight-bold" width="60%">Saldo Kas/Bank Awal Periode</td>
                                    <td class="text-right font-weight-bold" width="40%">
                                        Rp <?= number_format($saldo_awal_kas, 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Total Kas Masuk</td>
                                    <td class="text-right text-success font-weight-bold">
                                        Rp <?= number_format($total_kas_masuk, 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Total Kas Keluar</td>
                                    <td class="text-right text-danger font-weight-bold">
                                        ( Rp <?= number_format($total_kas_keluar, 0, ',', '.') ?> )
                                    </td>
                                </tr>
                                <tr class="bg-light">
                                    <td class="font-weight-bold">Arus Kas Bersih</td>
                                    <td
                                        class="text-right font-weight-bold <?= $arus_kas_bersih >= 0 ? 'text-success' : 'text-danger' ?>">
                                        Rp <?= number_format($arus_kas_bersih, 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <tr class="<?= $saldo_akhir_kas >= 0 ? 'bg-success' : 'bg-danger' ?> text-white"
                                    style="font-size: 18px;">
                                    <td class="font-weight-bold">Saldo Kas/Bank Akhir Periode</td>
                                    <td class="text-right font-weight-bold">
                                        Rp <?= number_format($saldo_akhir_kas, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            </table>

                            <!-- Verification -->
                            <?php
                            $calculated_saldo = $saldo_awal_kas + $arus_kas_bersih;
                            $is_match = abs($calculated_saldo - $saldo_akhir_kas) < 1;
                            ?>
                            <div class="alert <?= $is_match ? 'alert-success' : 'alert-warning' ?> mt-3">
                                <i class="fas fa-<?= $is_match ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                                <strong>Verifikasi:</strong>
                                Saldo Awal (Rp <?= number_format($saldo_awal_kas, 0, ',', '.') ?>)
                                + Arus Kas Bersih (Rp <?= number_format($arus_kas_bersih, 0, ',', '.') ?>)
                                = Rp <?= number_format($calculated_saldo, 0, ',', '.') ?>
                                <?php if ($is_match): ?>
                                    ✓ Sesuai dengan Saldo Akhir
                                <?php else: ?>
                                    ⚠ Selisih Rp
                                    <?= number_format(abs($calculated_saldo - $saldo_akhir_kas), 0, ',', '.') ?>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Rata-rata Kas Masuk/Hari
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                <?php
                                                $days = (strtotime($tanggal_akhir) - strtotime($tanggal_awal)) / 86400 + 1;
                                                $avg_in = $days > 0 ? $total_kas_masuk / $days : 0;
                                                ?>
                                                Rp <?= number_format($avg_in, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Rata-rata Kas Keluar/Hari
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                <?php
                                                $avg_out = $days > 0 ? $total_kas_keluar / $days : 0;
                                                ?>
                                                Rp <?= number_format($avg_out, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Burn Rate (Hari)
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                <?php
                                                $burn_rate = $avg_out > 0 ? $saldo_akhir_kas / $avg_out : 0;
                                                ?>
                                                <?= number_format($burn_rate, 0) ?> hari
                                            </div>
                                            <small class="text-muted">Kas habis dalam berapa hari</small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-fire fa-2x text-gray-300"></i>
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

    <?php $this->load->view('partials/js') ?>

    <script>
        // Data dari PHP
        const saldoAwal = <?= $saldo_awal_kas ?>;
        const saldoAkhir = <?= $saldo_akhir_kas ?>;
        const totalKasMasuk = <?= $total_kas_masuk ?>;
        const totalKasKeluar = <?= $total_kas_keluar ?>;
        const arusKasBersih = <?= $arus_kas_bersih ?>;

        // Mutasi kas data for trend
        const mutasiKas = <?= json_encode($mutasi_kas) ?>;

        // Top kas masuk/keluar
        const topKasMasuk = <?= json_encode($top_kas_masuk) ?>;
        const topKasKeluar = <?= json_encode($top_kas_keluar) ?>;

        // Format currency helper
        function formatRupiah(number) {
            return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // 1. BAR CHART - Kas Masuk vs Keluar
        const cashflowBarCtx = document.getElementById('cashflowBarChart').getContext('2d');
        new Chart(cashflowBarCtx, {
            type: 'bar',
            data: {
                labels: ['Kas Masuk', 'Kas Keluar', 'Arus Kas Bersih'],
                datasets: [{
                    label: 'Jumlah (Rp)',
                    data: [totalKasMasuk, totalKasKeluar, arusKasBersih],
                    backgroundColor: [
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        arusKasBersih >= 0 ? 'rgba(54, 185, 204, 0.8)' : 'rgba(231, 74, 59, 0.8)'
                    ],
                    borderColor: [
                        'rgb(28, 200, 138)',
                        'rgb(231, 74, 59)',
                        arusKasBersih >= 0 ? 'rgb(54, 185, 204)' : 'rgb(231, 74, 59)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return formatRupiah(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return formatRupiah(value);
                            }
                        }
                    }
                }
            }
        });

        // 2. DONUT CHART - Komposisi Arus Kas
        const cashflowDonutCtx = document.getElementById('cashflowDonutChart').getContext('2d');
        new Chart(cashflowDonutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Kas Masuk', 'Kas Keluar', 'Saldo Akhir'],
                datasets: [{
                    data: [totalKasMasuk, totalKasKeluar, Math.max(0, saldoAkhir)],
                    backgroundColor: [
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(78, 115, 223, 0.8)'
                    ],
                    borderColor: [
                        'rgb(28, 200, 138)',
                        'rgb(231, 74, 59)',
                        'rgb(78, 115, 223)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + formatRupiah(value) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // 3. LINE CHART - Trend Saldo Kas
        const cashTrendCtx = document.getElementById('cashTrendChart').getContext('2d');

        // Build trend data
        let trendLabels = ['Saldo Awal'];
        let trendData = [saldoAwal];
        let runningSaldo = saldoAwal;

        mutasiKas.forEach((item, index) => {
            runningSaldo = runningSaldo + parseFloat(item.kas_masuk) - parseFloat(item.kas_keluar);
            if (index % 3 === 0 || index === mutasiKas.length - 1) { // Sample every 3rd transaction
                trendLabels.push(item.tanggal);
                trendData.push(runningSaldo);
            }
        });

        trendLabels.push('Saldo Akhir');
        trendData.push(saldoAkhir);

        new Chart(cashTrendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Saldo Kas',
                    data: trendData,
                    borderColor: 'rgb(54, 185, 204)',
                    backgroundColor: 'rgba(54, 185, 204, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgb(54, 185, 204)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'Saldo: ' + formatRupiah(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return formatRupiah(value);
                            }
                        }
                    }
                }
            }
        });

        // 4. HORIZONTAL BAR - Top Kas Masuk
        const topKasMasukCtx = document.getElementById('topKasMasukChart').getContext('2d');
        new Chart(topKasMasukCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(topKasMasuk).map(key => key.length > 30 ? key.substring(0, 30) + '...' :
                    key),
                datasets: [{
                    label: 'Kas Masuk',
                    data: Object.values(topKasMasuk),
                    backgroundColor: 'rgba(28, 200, 138, 0.8)',
                    borderColor: 'rgb(28, 200, 138)',
                    borderWidth: 2
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return formatRupiah(context.parsed.x);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return formatRupiah(value);
                            }
                        }
                    }
                }
            }
        });

        // 5. HORIZONTAL BAR - Top Kas Keluar
        const topKasKeluarCtx = document.getElementById('topKasKeluarChart').getContext('2d');
        new Chart(topKasKeluarCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(topKasKeluar).map(key => key.length > 30 ? key.substring(0, 30) + '...' :
                    key),
                datasets: [{
                    label: 'Kas Keluar',
                    data: Object.values(topKasKeluar),
                    backgroundColor: 'rgba(231, 74, 59, 0.8)',
                    borderColor: 'rgb(231, 74, 59)',
                    borderWidth: 2
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return formatRupiah(context.parsed.x);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return formatRupiah(value);
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>