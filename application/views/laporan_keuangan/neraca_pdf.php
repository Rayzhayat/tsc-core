<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Neraca (Balance Sheet)</title>
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
            border-bottom: 3px solid #36b9cc;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 20px;
            color: #36b9cc;
        }
        .header h2 {
            margin: 0;
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }
        .tanggal {
            text-align: center;
            margin-bottom: 25px;
            font-size: 12px;
            color: #666;
        }
        .container {
            width: 100%;
        }
        .col-left {
            width: 48%;
            float: left;
            margin-right: 2%;
        }
        .col-right {
            width: 50%;
            float: left;
        }
        .section {
            margin-bottom: 20px;
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
        .section-header.aset { background-color: #4e73df; }
        .section-header.liabilitas { background-color: #e74a3b; }
        .section-header.ekuitas { background-color: #f6c23e; }
        
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
            width: 70px;
            color: #858796;
            font-size: 10px;
        }
        table td.name {
            font-weight: 500;
        }
        table td.amount {
            width: 130px;
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
            padding: 12px 10px !important;
        }
        .balance-check {
            clear: both;
            margin-top: 20px;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            color: white;
        }
        .balance-check.balanced { background-color: #1cc88a; }
        .balance-check.unbalanced { background-color: #e74a3b; }
        .balance-check h3 {
            margin-bottom: 15px;
            font-size: 16px;
        }
        .balance-amounts {
            display: table;
            width: 100%;
        }
        .balance-col {
            display: table-cell;
            width: 50%;
            padding: 10px;
        }
        .balance-col h4 {
            font-size: 11px;
            margin-bottom: 5px;
            opacity: 0.9;
        }
        .balance-col h2 {
            font-size: 18px;
            margin: 0;
        }
        .summary-table {
            width: 100%;
            margin-top: 20px;
            border: 1px solid #ddd;
            border-collapse: collapse;
            clear: both;
        }
        .summary-table th {
            background-color: #36b9cc;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        .summary-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e3e6f0;
        }
        .summary-table tr.total {
            background-color: #f8f9fc;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
            clear: both;
        }
        .empty-data {
            text-align: center;
            color: #999;
            padding: 15px;
            font-style: italic;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>NERACA (BALANCE SHEET)</h1>
        <h2>Laporan Posisi Keuangan</h2>
    </div>
    
    <div class="tanggal">
        <strong>Per Tanggal:</strong> 
        <?= date('d F Y', strtotime($tanggal)) ?>
    </div>

    <div class="container">
        <!-- LEFT COLUMN: ASET -->
        <div class="col-left">
            <div class="section">
                <div class="section-header aset">
                    ASET (ASSETS)
                </div>
                <div class="section-body">
                    <?php if (empty($aset)): ?>
                        <div class="empty-data">Tidak ada data aset</div>
                    <?php else: ?>
                        <table>
                            <?php foreach ($aset as $item): ?>
                            <tr>
                                <td class="code"><?= $item['kode_perkiraan'] ?></td>
                                <td class="name"><?= $item['nama'] ?></td>
                                <td class="amount">Rp <?= number_format($item['saldo'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach ?>
                        </table>
                    <?php endif ?>
                    
                    <table>
                        <tr class="total-row">
                            <td colspan="2"><strong>TOTAL ASET</strong></td>
                            <td class="amount">Rp <?= number_format($total_aset, 0, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: PASSIVA -->
        <div class="col-right">
            <!-- LIABILITAS -->
            <div class="section">
                <div class="section-header liabilitas">
                    LIABILITAS (LIABILITIES)
                </div>
                <div class="section-body">
                    <?php if (empty($liabilitas)): ?>
                        <div class="empty-data">Tidak ada data liabilitas</div>
                    <?php else: ?>
                        <table>
                            <?php foreach ($liabilitas as $item): ?>
                            <tr>
                                <td class="code"><?= $item['kode_perkiraan'] ?></td>
                                <td class="name"><?= $item['nama'] ?></td>
                                <td class="amount">Rp <?= number_format($item['saldo'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach ?>
                        </table>
                    <?php endif ?>
                    
                    <table>
                        <tr class="total-row">
                            <td colspan="2"><strong>TOTAL LIABILITAS</strong></td>
                            <td class="amount">Rp <?= number_format($total_liabilitas, 0, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- EKUITAS -->
            <div class="section">
                <div class="section-header ekuitas">
                    EKUITAS (EQUITY)
                </div>
                <div class="section-body">
                    <?php if (empty($ekuitas)): ?>
                        <div class="empty-data">Tidak ada data ekuitas</div>
                    <?php else: ?>
                        <table>
                            <?php foreach ($ekuitas as $item): ?>
                            <tr>
                                <td class="code"><?= $item['kode_perkiraan'] ?></td>
                                <td class="name"><?= $item['nama'] ?></td>
                                <td class="amount">Rp <?= number_format($item['saldo'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach ?>
                        </table>
                    <?php endif ?>
                    
                    <table>
                        <tr class="total-row">
                            <td colspan="2"><strong>TOTAL EKUITAS</strong></td>
                            <td class="amount">Rp <?= number_format($total_ekuitas, 0, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Check -->
    <?php 
    $is_balanced = abs($total_aset - $total_passiva) < 1; 
    ?>
    <div class="balance-check <?= $is_balanced ? 'balanced' : 'unbalanced' ?>">
        <h3>
            <?= $is_balanced ? '✓ BALANCE!' : '⚠ UNBALANCED!' ?>
        </h3>
        <div class="balance-amounts">
            <div class="balance-col">
                <h4>Total Aset</h4>
                <h2>Rp <?= number_format($total_aset, 0, ',', '.') ?></h2>
            </div>
            <div class="balance-col">
                <h4>Total Passiva</h4>
                <h2>Rp <?= number_format($total_passiva, 0, ',', '.') ?></h2>
            </div>
        </div>
        <?php if (!$is_balanced): ?>
            <div style="margin-top: 10px; font-size: 11px;">
                <strong>Selisih:</strong> Rp <?= number_format(abs($total_aset - $total_passiva), 0, ',', '.') ?>
            </div>
        <?php endif ?>
    </div>

    <!-- Summary Table -->
    <table class="summary-table">
        <thead>
            <tr>
                <th width="50%">ASET</th>
                <th width="50%">PASSIVA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Total Aset:</strong><br>
                    Rp <?= number_format($total_aset, 0, ',', '.') ?>
                </td>
                <td>
                    <strong>Liabilitas:</strong><br>
                    Rp <?= number_format($total_liabilitas, 0, ',', '.') ?>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <strong>Ekuitas:</strong><br>
                    Rp <?= number_format($total_ekuitas, 0, ',', '.') ?>
                </td>
            </tr>
            <tr class="total">
                <td>
                    <strong>TOTAL ASET</strong><br>
                    <span style="font-size: 13px;">Rp <?= number_format($total_aset, 0, ',', '.') ?></span>
                </td>
                <td>
                    <strong>TOTAL PASSIVA</strong><br>
                    <span style="font-size: 13px;">Rp <?= number_format($total_passiva, 0, ',', '.') ?></span>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Dicetak pada: <?= date('d F Y H:i:s') ?> | Generated by TSC Core System
    </div>

</body>
</html>