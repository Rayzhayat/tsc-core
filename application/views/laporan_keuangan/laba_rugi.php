<!-- laba_rugi.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .report-header {
            text-align: center;
            padding: 18px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .report-section {
            margin-bottom: 20px;
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-header {
            padding: 10px 16px;
            font-weight: 700;
            font-size: .92rem;
            color: #fff;
        }

        .section-header.pendapatan {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .section-header.cogs {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        }

        .section-header.exps {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 9px 14px;
            border-bottom: 1px solid #e3e6f0;
            transition: all .15s;
        }

        .item-row:hover {
            background: #f8f9fc;
            padding-left: 22px;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-code {
            color: #858796;
            font-size: .8rem;
            margin-right: 8px;
        }

        .item-name {
            flex: 1;
            font-weight: 500;
            font-size: .9rem;
        }

        .item-amount {
            font-weight: 600;
            font-size: .9rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 16px;
            background: #f8f9fc;
            font-weight: 700;
            font-size: .95rem;
            border-top: 3px solid #4e73df;
        }

        .summary-card {
            padding: 20px;
            border-radius: 10px;
            color: #fff;
            text-align: center;
            margin-bottom: 16px;
        }

        .summary-card.kotor {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }

        .summary-card.bersih {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .summary-card.negative {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
        }

        .summary-label {
            font-size: .85rem;
            opacity: .9;
            margin-bottom: 6px;
        }

        .summary-amount {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .chart-card {
            background: #fff;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 .1rem 1rem rgba(58, 59, 69, .12);
            margin-bottom: 20px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e3e6f0;
        }

        .chart-title {
            font-size: 1rem;
            font-weight: 700;
            color: #5a5c69;
        }

        .chart-subtitle {
            font-size: .78rem;
            color: #858796;
            margin-top: 2px;
        }

        .chart-container {
            position: relative;
            height: 320px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .report-section {
                page-break-inside: avoid;
            }

            .chart-container {
                height: 260px;
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
                <div class="container-xl mt-3">

                    <!-- PAGE HEADER -->
                    <div class="d-flex align-items-center justify-content-between mb-3 no-print">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-chart-line text-success me-2"></i><?= $title ?>
                            </h2>
                            <small class="text-muted">Laporan laba rugi periode terpilih</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="<?= base_url('laporan_keuangan?start_date=' . $tanggal_awal . '&end_date=' . $tanggal_akhir) ?>"
                                class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button onclick="window.print()" class="btn btn-primary btn-sm">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                            <a href="<?= base_url('laporan_keuangan/laba_rugi_pdf?tanggal_awal=' . $tanggal_awal . '&tanggal_akhir=' . $tanggal_akhir) ?>"
                                target="_blank" class="btn btn-danger btn-sm">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a>
                            <a href="<?= base_url('laporan_keuangan/laba_rugi_excel?tanggal_awal=' . $tanggal_awal . '&tanggal_akhir=' . $tanggal_akhir) ?>"
                                class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </a>
                        </div>
                    </div>

                    <!-- FILTER -->
                    <div class="card shadow-sm mb-3 no-print">
                        <div class="card-header py-2 bg-primary text-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-filter me-1"></i> Filter Periode
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <form method="get" action="<?= base_url('laporan_keuangan/laba_rugi') ?>">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold mb-1 small">Dari</label>
                                        <input type="date" name="tanggal_awal" class="form-control form-control-sm"
                                            value="<?= $tanggal_awal ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold mb-1 small">Sampai</label>
                                        <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                                            value="<?= $tanggal_akhir ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-search me-1"></i> Tampilkan
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="<?= base_url('laporan_keuangan/laba_rugi') ?>"
                                            class="btn btn-secondary btn-sm w-100">
                                            <i class="fas fa-redo me-1"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- REPORT HEADER -->
                    <div class="report-header">
                        <h3 class="mb-1 fw-bold">LAPORAN LABA RUGI</h3>
                        <h5 class="mb-0 opacity-90">
                            Periode: <?= date('d M Y', strtotime($tanggal_awal)) ?> –
                            <?= date('d M Y', strtotime($tanggal_akhir)) ?>
                        </h5>
                    </div>

                    <!-- CHARTS -->
                    <div class="row g-3 mb-3">
                        <div class="col-lg-8">
                            <div class="chart-card">
                                <div class="chart-header">
                                    <div>
                                        <div class="chart-title"><i class="fas fa-water text-primary me-1"></i>
                                            Waterfall Laba Rugi</div>
                                        <div class="chart-subtitle">Alur perhitungan dari Pendapatan ke Laba Bersih
                                        </div>
                                    </div>
                                </div>
                                <div class="chart-container"><canvas id="waterfallChart"></canvas></div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="chart-card">
                                <div class="chart-header">
                                    <div>
                                        <div class="chart-title"><i class="fas fa-chart-pie text-warning me-1"></i>
                                            Komposisi Biaya</div>
                                        <div class="chart-subtitle">COGS vs Beban Operasional</div>
                                    </div>
                                </div>
                                <div class="chart-container"><canvas id="expenseDonutChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-lg-6">
                            <div class="chart-card">
                                <div class="chart-header">
                                    <div>
                                        <div class="chart-title"><i class="fas fa-chart-bar text-success me-1"></i>
                                            Perbandingan Komponen</div>
                                        <div class="chart-subtitle">Pendapatan, COGS, Beban, dan Laba</div>
                                    </div>
                                </div>
                                <div class="chart-container"><canvas id="componentBarChart"></canvas></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="chart-card">
                                <div class="chart-header">
                                    <div>
                                        <div class="chart-title"><i class="fas fa-percentage text-info me-1"></i>
                                            Analisis Margin</div>
                                        <div class="chart-subtitle">Gross Margin vs Net Margin</div>
                                    </div>
                                </div>
                                <div class="chart-container"><canvas id="marginChart"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <!-- A. PENDAPATAN -->
                    <div class="report-section">
                        <div class="section-header pendapatan">
                            <i class="fas fa-arrow-up me-2"></i> A. PENDAPATAN (REVENUE)
                        </div>
                        <div class="section-body">
                            <?php if (empty($pendapatan)): ?>
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i> Tidak ada data pendapatan untuk periode
                                    ini
                                </div>
                            <?php else: ?>
                                <?php foreach ($pendapatan as $item): ?>
                                    <div class="item-row">
                                        <div class="item-name">
                                            <span class="item-code"><?= $item['kode_perkiraan'] ?></span>
                                            <?= $item['nama'] ?>
                                        </div>
                                        <div class="item-amount text-success">Rp
                                            <?= number_format($item['nominal'], 0, ',', '.') ?></div>
                                    </div>
                                <?php endforeach ?>
                            <?php endif ?>
                            <div class="total-row">
                                <div>TOTAL PENDAPATAN</div>
                                <div class="text-success">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- B. COGS -->
                    <div class="report-section">
                        <div class="section-header cogs">
                            <i class="fas fa-box me-2"></i> B. BEBAN POKOK PENJUALAN (COGS)
                        </div>
                        <div class="section-body">
                            <?php if (empty($cogs)): ?>
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i> Tidak ada data COGS untuk periode ini
                                </div>
                            <?php else: ?>
                                <?php foreach ($cogs as $item): ?>
                                    <div class="item-row">
                                        <div class="item-name">
                                            <span class="item-code"><?= $item['kode_perkiraan'] ?></span>
                                            <?= $item['nama'] ?>
                                        </div>
                                        <div class="item-amount text-warning">Rp
                                            <?= number_format($item['nominal'], 0, ',', '.') ?></div>
                                    </div>
                                <?php endforeach ?>
                            <?php endif ?>
                            <div class="total-row">
                                <div>TOTAL COGS</div>
                                <div class="text-warning">Rp <?= number_format($total_cogs, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- LABA KOTOR -->
                    <div class="row mb-3">
                        <div class="col-md-6 offset-md-6">
                            <div class="summary-card <?= $laba_kotor >= 0 ? 'kotor' : 'negative' ?>">
                                <div class="summary-label">LABA KOTOR (GROSS PROFIT)</div>
                                <div class="summary-amount">Rp <?= number_format($laba_kotor, 0, ',', '.') ?></div>
                                <small style="opacity:.8">Pendapatan - COGS</small>
                            </div>
                        </div>
                    </div>

                    <!-- C. BEBAN OPERASIONAL -->
                    <div class="report-section">
                        <div class="section-header exps">
                            <i class="fas fa-receipt me-2"></i> C. BEBAN OPERASIONAL (EXPENSES)
                        </div>
                        <div class="section-body">
                            <?php if (empty($exps)): ?>
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i> Tidak ada data beban operasional untuk
                                    periode ini
                                </div>
                            <?php else: ?>
                                <?php foreach ($exps as $item): ?>
                                    <div class="item-row">
                                        <div class="item-name">
                                            <span class="item-code"><?= $item['kode_perkiraan'] ?></span>
                                            <?= $item['nama'] ?>
                                        </div>
                                        <div class="item-amount text-danger">Rp
                                            <?= number_format($item['nominal'], 0, ',', '.') ?></div>
                                    </div>
                                <?php endforeach ?>
                            <?php endif ?>
                            <div class="total-row">
                                <div>TOTAL BEBAN OPERASIONAL</div>
                                <div class="text-danger">Rp <?= number_format($total_exps, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- LABA BERSIH -->
                    <div class="row mb-4">
                        <div class="col-md-8 offset-md-4">
                            <div class="summary-card <?= $laba_bersih >= 0 ? 'bersih' : 'negative' ?>">
                                <div class="summary-label">LABA BERSIH (NET PROFIT)</div>
                                <div class="summary-amount">Rp <?= number_format($laba_bersih, 0, ',', '.') ?></div>
                                <small style="opacity:.8">Laba Kotor - Beban Operasional</small>
                            </div>
                        </div>
                    </div>

                    <!-- RINGKASAN -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-header py-2 bg-primary text-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-calculator me-1"></i> Ringkasan Perhitungan
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered mb-0" style="font-size:.88rem;">
                                <tbody>
                                    <tr>
                                        <td width="5%" class="text-center fw-bold text-muted">A</td>
                                        <td width="55%">Revenue Before Tax <small class="text-muted">(Total
                                                Pendapatan)</small></td>
                                        <td width="40%" class="text-end text-success fw-bold">Rp
                                            <?= number_format($total_pendapatan, 0, ',', '.') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center fw-bold text-muted">B</td>
                                        <td>PPH 2% Dipotong Customer <small class="text-muted">(OCAS PPH 23)</small>
                                        </td>
                                        <td class="text-end text-danger">Rp
                                            <?= number_format($total_pph_customer, 0, ',', '.') ?></td>
                                    </tr>
                                    <tr class="table-light">
                                        <td class="text-center fw-bold text-primary">A-B</td>
                                        <td class="fw-bold">Nett Revenue After Tax</td>
                                        <td class="text-end fw-bold text-primary" style="font-size:.95rem;">Rp
                                            <?= number_format($nett_revenue, 0, ',', '.') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center fw-bold text-muted">C</td>
                                        <td>COGS Before Tax <small class="text-muted">(Total Beban Pokok
                                                Penjualan)</small></td>
                                        <td class="text-end text-warning">Rp
                                            <?= number_format($total_cogs, 0, ',', '.') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center fw-bold text-muted">D</td>
                                        <td>PPH Memotong dari Vendor <small class="text-muted">(OCAS PPH Vendor)</small>
                                        </td>
                                        <td class="text-end text-success">Rp
                                            <?= number_format($total_pph_vendor, 0, ',', '.') ?></td>
                                    </tr>
                                    <tr class="table-light">
                                        <td class="text-center fw-bold text-warning">C-D</td>
                                        <td class="fw-bold">Nett COGS After Tax</td>
                                        <td class="text-end fw-bold text-warning" style="font-size:.95rem;">Rp
                                            <?= number_format($nett_cogs, 0, ',', '.') ?></td>
                                    </tr>
                                    <tr style="background-color:#e8f4fd;">
                                        <td class="text-center fw-bold" style="color:#36b9cc;">AB-CD</td>
                                        <td class="fw-bold" style="color:#36b9cc;">
                                            Laba Kotor <small class="text-muted">(Nett Revenue - Nett COGS)</small>
                                        </td>
                                        <td class="text-end fw-bold" style="font-size:1rem;color:#36b9cc;">Rp
                                            <?= number_format($laba_kotor, 0, ',', '.') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center fw-bold text-muted">—</td>
                                        <td>Beban Operasional <small class="text-muted">(Expenses)</small></td>
                                        <td class="text-end text-danger">( Rp
                                            <?= number_format($total_exps, 0, ',', '.') ?> )</td>
                                    </tr>
                                    <tr class="<?= $laba_bersih >= 0 ? 'bg-success' : 'bg-danger' ?> text-white fw-bold"
                                        style="font-size:1rem;">
                                        <td class="text-center">✓</td>
                                        <td>Nett Profit After Tax <small style="opacity:.8">(Laba Bersih)</small></td>
                                        <td class="text-end">Rp <?= number_format($laba_bersih, 0, ',', '.') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- RASIO -->
                    <?php if ($total_pendapatan > 0): ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-header py-2 bg-info text-white">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-percentage me-1"></i> Analisis Rasio
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php
                                    $rasios = [
                                        ['label' => 'Gross Profit Margin', 'val' => ($laba_kotor / $total_pendapatan) * 100, 'color' => 'info', 'desc' => 'Laba Kotor / Pendapatan'],
                                        ['label' => 'Operating Profit Margin', 'val' => ($laba_bersih / $total_pendapatan) * 100, 'color' => 'success', 'desc' => 'Laba Bersih / Pendapatan'],
                                        ['label' => 'Operating Expense Ratio', 'val' => ($total_exps / $total_pendapatan) * 100, 'color' => 'warning', 'desc' => 'Beban Operasional / Pendapatan'],
                                    ];
                                    foreach ($rasios as $r): ?>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded">
                                                <h6 class="text-muted small"><?= $r['label'] ?></h6>
                                                <h4 class="text-<?= $r['color'] ?>"><?= number_format($r['val'], 2) ?>%</h4>
                                                <small class="text-muted"><?= $r['desc'] ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        const totalPendapatan = <?= $total_pendapatan ?>;
        const totalCOGS = <?= $total_cogs ?>;
        const totalExps = <?= $total_exps ?>;
        const labaKotor = <?= $laba_kotor ?>;
        const labaBersih = <?= $laba_bersih ?>;

        function formatRupiah(n) {
            return 'Rp ' + Math.abs(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // 1. WATERFALL
        new Chart(document.getElementById('waterfallChart'), {
            type: 'bar',
            data: {
                labels: ['Pendapatan', 'COGS', 'Laba Kotor', 'Beban Ops', 'Laba Bersih'],
                datasets: [{
                    data: [totalPendapatan, -totalCOGS, labaKotor, -totalExps, labaBersih],
                    backgroundColor: ['rgba(28,200,138,.8)', 'rgba(246,194,62,.8)', 'rgba(54,185,204,.8)', 'rgba(231,74,59,.8)', labaBersih >= 0 ? 'rgba(28,200,138,.9)' : 'rgba(231,74,59,.9)'],
                    borderColor: ['rgb(28,200,138)', 'rgb(246,194,62)', 'rgb(54,185,204)', 'rgb(231,74,59)', labaBersih >= 0 ? 'rgb(28,200,138)' : 'rgb(231,74,59)'],
                    borderWidth: 2
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => formatRupiah(c.parsed.y) } } }, scales: { y: { beginAtZero: true, ticks: { callback: v => formatRupiah(v) } } } }
        });

        // 2. DONUT
        new Chart(document.getElementById('expenseDonutChart'), {
            type: 'doughnut',
            data: {
                labels: ['COGS', 'Beban Operasional', 'Laba Bersih'],
                datasets: [{ data: [totalCOGS, totalExps, Math.max(0, labaBersih)], backgroundColor: ['rgba(246,194,62,.8)', 'rgba(231,74,59,.8)', 'rgba(28,200,138,.8)'], borderColor: ['rgb(246,194,62)', 'rgb(231,74,59)', 'rgb(28,200,138)'], borderWidth: 2 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } }, tooltip: { callbacks: { label: c => { const t = c.dataset.data.reduce((a, b) => a + b, 0); return c.label + ': ' + formatRupiah(c.parsed) + ' (' + ((c.parsed / t) * 100).toFixed(1) + '%)' } } } } }
        });

        // 3. BAR
        new Chart(document.getElementById('componentBarChart'), {
            type: 'bar',
            data: {
                labels: ['Pendapatan', 'COGS', 'Beban Ops', 'Laba Kotor', 'Laba Bersih'],
                datasets: [{ label: 'Jumlah (Rp)', data: [totalPendapatan, totalCOGS, totalExps, labaKotor, labaBersih], backgroundColor: ['rgba(28,200,138,.7)', 'rgba(246,194,62,.7)', 'rgba(231,74,59,.7)', 'rgba(54,185,204,.7)', labaBersih >= 0 ? 'rgba(28,200,138,.9)' : 'rgba(231,74,59,.9)'], borderColor: ['rgb(28,200,138)', 'rgb(246,194,62)', 'rgb(231,74,59)', 'rgb(54,185,204)', labaBersih >= 0 ? 'rgb(28,200,138)' : 'rgb(231,74,59)'], borderWidth: 2 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => formatRupiah(c.parsed.y) } } }, scales: { y: { beginAtZero: true, ticks: { callback: v => formatRupiah(v) } } } }
        });

        // 4. MARGIN
        const gm = totalPendapatan > 0 ? (labaKotor / totalPendapatan * 100) : 0;
        const nm = totalPendapatan > 0 ? (labaBersih / totalPendapatan * 100) : 0;
        new Chart(document.getElementById('marginChart'), {
            type: 'bar',
            data: {
                labels: ['Gross Profit Margin', 'Net Profit Margin'],
                datasets: [{ label: 'Margin (%)', data: [gm, nm], backgroundColor: ['rgba(54,185,204,.8)', nm >= 0 ? 'rgba(28,200,138,.8)' : 'rgba(231,74,59,.8)'], borderColor: ['rgb(54,185,204)', nm >= 0 ? 'rgb(28,200,138)' : 'rgb(231,74,59)'], borderWidth: 2 }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => c.parsed.x.toFixed(2) + '%' } } }, scales: { x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } } }
        });
    </script>
</body>

</html>