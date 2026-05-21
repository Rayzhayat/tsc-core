<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Arus Kas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #f6c23e;
        }

        .header h1 {
            margin: 0 0 5px 0;
            font-size: 20px;
            color: #f6c23e;
        }

        .header h2 {
            margin: 0;
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }

        .periode {
            text-align: center;
            margin-bottom: 25px;
            font-size: 12px;
            color: #666;
        }

        .saldo-box {
            width: 48%;
            display: inline-block;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            color: white;
            margin-bottom: 20px;
        }

        .saldo-box.awal {
            background-color: #4e73df;
            margin-right: 2%;
        }

        .saldo-box.akhir {
            background-color: #1cc88a;
        }

        .saldo-box.akhir.negative {
            background-color: #e74a3b;
        }

        .saldo-box h4 {
            font-size: 11px;
            margin-bottom: 8px;
            opacity: 0.9;
        }

        .saldo-box h2 {
            font-size: 18px;
            margin: 0;
        }

        .saldo-box small {
            font-size: 9px;
            opacity: 0.8;
        }

        .section {
            margin-bottom: 25px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .section-header {
            padding: 10px 15px;
            font-weight: bold;
            font-size: 12px;
            color: white;
            background-color: #36b9cc;
        }

        .section-body {
            padding: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table tr {
            border-bottom: 1px solid #e3e6f0;
        }

        table tr:last-child {
            border-bottom: none;
        }

        table td {
            padding: 6px 8px;
            vertical-align: top;
        }

        table td.date {
            width: 80px;
            color: #858796;
            font-size: 10px;
        }

        table td.desc {
            font-weight: 500;
        }

        table td.amount-in {
            width: 120px;
            text-align: right;
            font-weight: 600;
            color: #1cc88a;
        }

        table td.amount-out {
            width: 120px;
            text-align: right;
            font-weight: 600;
            color: #e74a3b;
        }

        .total-row {
            background-color: #f8f9fc;
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #36b9cc !important;
        }

        .total-row td {
            padding: 12px 8px !important;
        }

        .summary-table {
            width: 100%;
            margin-top: 20px;
            border: 1px solid #ddd;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #e3e6f0;
        }

        .summary-table tr.highlight {
            background-color: #f8f9fc;
        }

        .summary-table tr.final {
            background-color: #1cc88a;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }

        .summary-table tr.final.negative {
            background-color: #e74a3b;
        }

        .verification {
            margin-top: 15px;
            padding: 12px;
            border-radius: 5px;
            font-size: 10px;
        }

        .verification.match {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .verification.nomatch {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .empty-data {
            text-align: center;
            color: #999;
            padding: 20px;
            font-style: italic;
        }

        .stats-box {
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }

        .stats-header {
            background-color: #36b9cc;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11px;
        }

        .stats-body {
            padding: 12px;
        }

        .stats-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .stats-row:last-child {
            margin-bottom: 0;
        }

        .stats-col {
            display: table-cell;
            width: 33.33%;
            padding: 8px;
            text-align: center;
            border-right: 1px solid #e3e6f0;
        }

        .stats-col:last-child {
            border-right: none;
        }

        .stats-col h5 {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
        }

        .stats-col h3 {
            font-size: 12px;
            margin: 0;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <h1>LAPORAN ARUS KAS</h1>
        <h2>(Cashflow Statement)</h2>
    </div>

    <div class="periode">
        <strong>Periode:</strong>
        <?= date('d F Y', strtotime($tanggal_awal)) ?> - <?= date('d F Y', strtotime($tanggal_akhir)) ?>
    </div>

    <!-- Saldo Boxes -->
    <div class="saldo-box awal">
        <h4>Saldo Kas/Bank Awal Periode</h4>
        <h2>Rp <?= number_format($saldo_awal_kas, 0, ',', '.') ?></h2>
        <small><?= date('d M Y', strtotime($tanggal_awal)) ?></small>
    </div>
    <div class="saldo-box akhir <?= $saldo_akhir_kas < 0 ? 'negative' : '' ?>">
        <h4>Saldo Kas/Bank Akhir Periode</h4>
        <h2>Rp <?= number_format($saldo_akhir_kas, 0, ',', '.') ?></h2>
        <small><?= date('d M Y', strtotime($tanggal_akhir)) ?></small>
    </div>

    <!-- Mutasi Kas -->
    <div class="section">
        <div class="section-header">
            MUTASI KAS & BANK
        </div>
        <div class="section-body">
            <?php if (empty($mutasi_kas)): ?>
                <div class="empty-data">Tidak ada mutasi kas/bank</div>
            <?php else: ?>
                <table>
                    <?php foreach ($mutasi_kas as $item): ?>
                        <tr>
                            <td class="date"><?= date('d/m/Y', strtotime($item['tanggal'])) ?></td>
                            <td class="desc">
                                <?= htmlspecialchars($item['keterangan']) ?>
                                <?php if (!empty($item['referensi_tipe'])): ?>
                                    <br><small style="color: #999; font-size: 9px;"><?= $item['referensi_tipe'] ?></small>
                                <?php endif ?>
                            </td>
                            <td class="amount-in">
                                <?php if ($item['kas_masuk'] > 0): ?>
                                    ↓ Rp <?= number_format($item['kas_masuk'], 0, ',', '.') ?>
                                <?php endif ?>
                            </td>
                            <td class="amount-out">
                                <?php if ($item['kas_keluar'] > 0): ?>
                                    ↑ Rp <?= number_format($item['kas_keluar'], 0, ',', '.') ?>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </table>
            <?php endif ?>

            <table>
                <tr class="total-row">
                    <td colspan="2"><strong>TOTAL</strong></td>
                    <td class="amount-in">Rp <?= number_format($total_kas_masuk, 0, ',', '.') ?></td>
                    <td class="amount-out">Rp <?= number_format($total_kas_keluar, 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Summary Table -->
    <table class="summary-table">
        <tr>
            <td width="70%"><strong>Saldo Kas/Bank Awal Periode</strong></td>
            <td width="30%" style="text-align: right;">
                <strong>Rp <?= number_format($saldo_awal_kas, 0, ',', '.') ?></strong>
            </td>
        </tr>
        <tr>
            <td><strong>Total Kas Masuk</strong></td>
            <td style="text-align: right; color: #1cc88a;">
                <strong>Rp <?= number_format($total_kas_masuk, 0, ',', '.') ?></strong>
            </td>
        </tr>
        <tr>
            <td><strong>Total Kas Keluar</strong></td>
            <td style="text-align: right; color: #e74a3b;">
                <strong>( Rp <?= number_format($total_kas_keluar, 0, ',', '.') ?> )</strong>
            </td>
        </tr>
        <tr class="highlight">
            <td><strong>Arus Kas Bersih</strong></td>
            <td style="text-align: right;">
                <strong>Rp <?= number_format($arus_kas_bersih, 0, ',', '.') ?></strong>
            </td>
        </tr>
        <tr class="final <?= $saldo_akhir_kas < 0 ? 'negative' : '' ?>">
            <td><strong>SALDO KAS/BANK AKHIR PERIODE</strong></td>
            <td style="text-align: right;">
                <strong>Rp <?= number_format($saldo_akhir_kas, 0, ',', '.') ?></strong>
            </td>
        </tr>
    </table>

    <!-- Verification -->
    <?php
    $calculated_saldo = $saldo_awal_kas + $arus_kas_bersih;
    $is_match = abs($calculated_saldo - $saldo_akhir_kas) < 1;
    ?>
    <div class="verification <?= $is_match ? 'match' : 'nomatch' ?>">
        <strong>Verifikasi:</strong>
        Saldo Awal (Rp <?= number_format($saldo_awal_kas, 0, ',', '.') ?>)
        + Arus Kas Bersih (Rp <?= number_format($arus_kas_bersih, 0, ',', '.') ?>)
        = Rp <?= number_format($calculated_saldo, 0, ',', '.') ?>
        <?php if ($is_match): ?>
            ✓ Sesuai dengan Saldo Akhir
        <?php else: ?>
            ⚠ Selisih Rp <?= number_format(abs($calculated_saldo - $saldo_akhir_kas), 0, ',', '.') ?>
        <?php endif ?>
    </div>

    <!-- Statistics -->
    <div class="stats-box">
        <div class="stats-header">
            STATISTIK ARUS KAS
        </div>
        <div class="stats-body">
            <div class="stats-row">
                <div class="stats-col">
                    <h5>Rata-rata Kas Masuk/Hari</h5>
                    <h3>
                        <?php
                        $days = (strtotime($tanggal_akhir) - strtotime($tanggal_awal)) / 86400 + 1;
                        $avg_in = $days > 0 ? $total_kas_masuk / $days : 0;
                        ?>
                        Rp <?= number_format($avg_in, 0, ',', '.') ?>
                    </h3>
                </div>
                <div class="stats-col">
                    <h5>Rata-rata Kas Keluar/Hari</h5>
                    <h3>
                        <?php $avg_out = $days > 0 ? $total_kas_keluar / $days : 0; ?>
                        Rp <?= number_format($avg_out, 0, ',', '.') ?>
                    </h3>
                </div>
                <div class="stats-col">
                    <h5>Burn Rate (Hari)</h5>
                    <h3>
                        <?php $burn_rate = $avg_out > 0 ? $saldo_akhir_kas / $avg_out : 0; ?>
                        <?= number_format($burn_rate, 0) ?> hari
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Dicetak pada: <?= date('d F Y H:i:s') ?> | Generated by TSC Core System
    </div>

</body>

</html>