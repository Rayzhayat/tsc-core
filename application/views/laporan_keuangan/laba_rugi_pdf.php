<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi</title>
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
            border-bottom: 3px solid #4e73df;
        }

        .header h1 {
            margin: 0 0 5px 0;
            font-size: 20px;
            color: #4e73df;
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
        }

        .section-header.pendapatan {
            background-color: #1cc88a;
        }

        .section-header.cogs {
            background-color: #f6c23e;
        }

        .section-header.exps {
            background-color: #e74a3b;
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
            padding: 8px 10px;
        }

        table td.code {
            width: 80px;
            color: #858796;
            font-size: 10px;
        }

        table td.name {
            font-weight: 500;
        }

        table td.amount {
            width: 140px;
            text-align: right;
            font-weight: 600;
        }

        .total-row {
            background-color: #f8f9fc;
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #4e73df !important;
        }

        .total-row td {
            padding: 12px 15px !important;
        }

        .summary-box {
            padding: 20px;
            border-radius: 5px;
            color: white;
            text-align: center;
            margin-bottom: 15px;
        }

        .summary-box.kotor {
            background-color: #36b9cc;
        }

        .summary-box.bersih {
            background-color: #1cc88a;
        }

        .summary-box.negative {
            background-color: #e74a3b;
        }

        .summary-label {
            font-size: 11px;
            margin-bottom: 8px;
        }

        .summary-amount {
            font-size: 22px;
            font-weight: bold;
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

        .summary-table tr.final {
            background-color: #1cc88a;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }

        .summary-table tr.final.negative {
            background-color: #e74a3b;
        }

        .footer {
            margin-top: 40px;
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
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <h1>LAPORAN LABA RUGI</h1>
        <h2>(Income Statement)</h2>
    </div>

    <div class="periode">
        <strong>Periode:</strong>
        <?= date('d F Y', strtotime($tanggal_awal)) ?> - <?= date('d F Y', strtotime($tanggal_akhir)) ?>
    </div>

    <!-- A. PENDAPATAN -->
    <div class="section">
        <div class="section-header pendapatan">
            A. PENDAPATAN (REVENUE)
        </div>
        <div class="section-body">
            <?php if (empty($pendapatan)): ?>
                <div class="empty-data">Tidak ada data pendapatan</div>
            <?php else: ?>
                <table>
                    <?php foreach ($pendapatan as $item): ?>
                        <tr>
                            <td class="code"><?= $item['kode_perkiraan'] ?></td>
                            <td class="name"><?= $item['nama'] ?></td>
                            <td class="amount">Rp <?= number_format($item['nominal'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach ?>
                </table>
            <?php endif ?>

            <table>
                <tr class="total-row">
                    <td colspan="2"><strong>TOTAL PENDAPATAN</strong></td>
                    <td class="amount">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- B. COGS -->
    <div class="section">
        <div class="section-header cogs">
            B. BEBAN POKOK PENJUALAN (COGS)
        </div>
        <div class="section-body">
            <?php if (empty($cogs)): ?>
                <div class="empty-data">Tidak ada data COGS</div>
            <?php else: ?>
                <table>
                    <?php foreach ($cogs as $item): ?>
                        <tr>
                            <td class="code"><?= $item['kode_perkiraan'] ?></td>
                            <td class="name"><?= $item['nama'] ?></td>
                            <td class="amount">Rp <?= number_format($item['nominal'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach ?>
                </table>
            <?php endif ?>

            <table>
                <tr class="total-row">
                    <td colspan="2"><strong>TOTAL COGS</strong></td>
                    <td class="amount">Rp <?= number_format($total_cogs, 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- LABA KOTOR -->
    <div class="summary-box <?= $laba_kotor >= 0 ? 'kotor' : 'negative' ?>">
        <div class="summary-label">LABA KOTOR (GROSS PROFIT)</div>
        <div class="summary-amount">Rp <?= number_format($laba_kotor, 0, ',', '.') ?></div>
    </div>

    <!-- C. BEBAN OPERASIONAL -->
    <div class="section">
        <div class="section-header exps">
            C. BEBAN OPERASIONAL (EXPENSES)
        </div>
        <div class="section-body">
            <?php if (empty($exps)): ?>
                <div class="empty-data">Tidak ada data beban operasional</div>
            <?php else: ?>
                <table>
                    <?php foreach ($exps as $item): ?>
                        <tr>
                            <td class="code"><?= $item['kode_perkiraan'] ?></td>
                            <td class="name"><?= $item['nama'] ?></td>
                            <td class="amount">Rp <?= number_format($item['nominal'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach ?>
                </table>
            <?php endif ?>

            <table>
                <tr class="total-row">
                    <td colspan="2"><strong>TOTAL BEBAN OPERASIONAL</strong></td>
                    <td class="amount">Rp <?= number_format($total_exps, 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- LABA BERSIH -->
    <div class="summary-box <?= $laba_bersih >= 0 ? 'bersih' : 'negative' ?>">
        <div class="summary-label">LABA BERSIH (NET PROFIT)</div>
        <div class="summary-amount">Rp <?= number_format($laba_bersih, 0, ',', '.') ?></div>
    </div>

    <!-- SUMMARY TABLE -->
    <!-- SUMMARY TABLE - Format Klien -->
    <table class="summary-table">
        <tr>
            <td width="8%"><strong>A</strong></td>
            <td width="62%">Revenue Before Tax (Total Pendapatan)</td>
            <td width="30%" style="text-align:right;"><strong><?= number_format($total_pendapatan, 0, ',', '.') ?></strong>
            </td>
        </tr>
        <tr>
            <td><strong>B</strong></td>
            <td>PPH 2% Di Potong Cust</td>
            <td style="text-align:right;"><?= number_format($total_pph_customer, 0, ',', '.') ?></td>
        </tr>
        <tr style="background-color:#e8f0fe;">
            <td><strong>A-B</strong></td>
            <td><strong>Nett Revenue After Tax</strong></td>
            <td style="text-align:right;"><strong><?= number_format($nett_revenue, 0, ',', '.') ?></strong></td>
        </tr>
        <tr>
            <td><strong>C</strong></td>
            <td>COGS Before Tax (Total Beban Pokok Penjualan)</td>
            <td style="text-align:right;"><?= number_format($total_cogs, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td><strong>D</strong></td>
            <td>PPH Memotong dari Vendor</td>
            <td style="text-align:right;"><?= number_format($total_pph_vendor, 0, ',', '.') ?></td>
        </tr>
        <tr style="background-color:#fff3cd;">
            <td><strong>C-D</strong></td>
            <td><strong>Nett COGS After Tax</strong></td>
            <td style="text-align:right;"><strong><?= number_format($nett_cogs, 0, ',', '.') ?></strong></td>
        </tr>
        <tr style="background-color:#d1ecf1;">
            <td><strong>AB-CD</strong></td>
            <td><strong>Laba Kotor (Nett Revenue - Nett COGS)</strong></td>
            <td style="text-align:right;"><strong><?= number_format($laba_kotor, 0, ',', '.') ?></strong></td>
        </tr>
        <tr>
            <td><strong>—</strong></td>
            <td>Beban Operasional (Expenses)</td>
            <td style="text-align:right;"><?= number_format($total_exps, 0, ',', '.') ?></td>
        </tr>
        <tr class="final <?= $laba_bersih < 0 ? 'negative' : '' ?>">
            <td><strong>✓</strong></td>
            <td><strong>Nett Profit After Tax (Laba Bersih)</strong></td>
            <td style="text-align:right;"><strong><?= number_format($laba_bersih, 0, ',', '.') ?></strong></td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        Dicetak pada: <?= date('d F Y H:i:s') ?> | Generated by TSC Core System
    </div>

</body>

</html>