<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice <?= $invoice->no_invoice ?></title>
    <style>
        @page {
            margin: 10mm 15mm;
            size: A4 portrait;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            line-height: 1.15;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-bottom: 45px;
        }

        .company-info {
            float: left;
            width: 65%;
        }

        .company-info h2 {
            margin: 0 0 1px 0;
            font-size: 12pt;
            font-weight: bold;
            color: #000;
        }

        .company-info p {
            margin: 2px 0;
            font-size: 7pt;
            margin-bottom: 6px;
            line-height: 1.15;
        }

        .logo {
            float: right;
            width: 30%;
            text-align: right;
        }

        .logo img {
            max-width: 100px;
            max-height: 60px;
            height: auto;
        }

        .clear {
            clear: both;
        }

        .invoice-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 2px 0 3px 0;
            text-transform: uppercase;
        }

        .top-section {
            margin-bottom: 15px;
        }

        .bill-to {
            float: left;
            width: 58%;
        }

        .bill-to-header {
            font-weight: bold;
            font-size: 8pt;
            margin-bottom: 2px;
        }

        .bill-to-content {
            font-size: 7pt;
            line-height: 1.25;
        }

        .invoice-details {
            float: right;
            width: 38%;
            font-size: 7pt;
        }

        .invoice-details table {
            width: 100%;
        }

        .invoice-details td {
            padding: 0.5px 0;
        }

        .invoice-details td:first-child {
            width: 42%;
        }

        .invoice-details td:nth-child(2) {
            width: 5%;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 3px 0;
            font-size: 7pt;
        }

        .items-table th {
            background-color: #e8e8e8;
            border: 1px solid #666;
            padding: 3px;
            text-align: center;
            font-weight: bold;
        }

        .items-table td {
            border: 1px solid #666;
            padding: 3px;
        }

        .items-table td.no {
            text-align: center;
            width: 5%;
        }

        .items-table td.desc {
            text-align: left;
        }

        .items-table td.amount {
            text-align: right;
            width: 22%;
        }

        .deduction-row {
            background-color: #fff9e6;
        }

        .summary-section {
            margin-top: 3px;
        }

        .summary-box {
            float: right;
            width: 42%;
        }

        .summary-table {
            width: 100%;
            margin-top: 20px;
            margin-bottom: 50px;
            font-size: 7pt;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 2px 5px;
            border: 1px solid #666;
        }

        .summary-table td:first-child {
            text-align: left;
            font-weight: bold;
            width: 50%;
        }

        .summary-table td:last-child {
            text-align: right;
            width: 50%;
        }

        .summary-table .total-row td {
            font-weight: bold;
            font-size: 8pt;
            background-color: #e8e8e8;
        }

        .bottom-section {
            clear: both;
            margin-top: 4px;
            margin-bottom: 90px;
            font-size: 7pt;
        }

        .terbilang {
            font-style: italic;
            margin-bottom: 2px;
        }

        .notes {
            margin-bottom: 2px;
        }

        .bank-details {
            line-height: 1.35;
            margin-bottom: 4px;
        }

        .signature-section {
            text-align: right;
            margin-top: 8px;
        }

        .signature-box {
            display: inline-block;
            text-align: center;
        }

        .signature-box .name {
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding: 3px 15px;
            margin-top: 25px;
            margin-bottom: 1px;
        }

        .signature-box .title {
            font-size: 7pt;
            font-style: italic;
        }

        .footer-note {
            margin-top: 4px;
            font-size: 6pt;
            font-style: italic;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 2px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <h2>PT Tata Sanjaya Cakrawala</h2>
            <p>Jl Bulak Perwira 2 RT 06 RW 07 No. 26 Perwira, Bekasi Utara, Kota Bekasi</p>
            <p>NPWP: 90.585.862.8-407.000</p>
        </div>
        <div class="logo">
            <?php
            $logo_paths = [
                FCPATH . 'assets/img/TSC_page-0001.jpg',
                FCPATH . 'assets/img/TSC_page-0001.png',
                FCPATH . 'assets/img/TSC_page-0001.jpeg',
                FCPATH . 'assets/images/TSC_page-0001.jpg',
                FCPATH . 'assets/images/TSC_page-0001.png',
            ];

            $logo_found = false;
            foreach ($logo_paths as $path) {
                if (file_exists($path)) {
                    $logo_data = base64_encode(file_get_contents($path));
                    $extension = pathinfo($path, PATHINFO_EXTENSION);
                    $mime_type = 'image/' . ($extension == 'jpg' ? 'jpeg' : $extension);
                    echo '<img src="data:' . $mime_type . ';base64,' . $logo_data . '" alt="TSC Logo">';
                    $logo_found = true;
                    break;
                }
            }

            if (!$logo_found) {
                echo '<div style="font-size: 18pt; font-weight: bold; color: #0066cc;">TSC</div>';
            }
            ?>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Invoice Title -->
    <div class="invoice-title">INVOICE</div>

    <!-- Top Section -->
    <div class="top-section">
        <!-- Bill To -->
        <div class="bill-to">
            <div class="bill-to-header" style="margin-top: 45px;">
                <?= htmlspecialchars($invoice->customer_nama_npwp ?: $invoice->customer_nama) ?>
            </div>
            <div class="bill-to-content" style="margin-top: 2px;">
                <?= htmlspecialchars($invoice->customer_alamat) ?>
            </div>
            <div class="bill-to-content" style="margin-top: 2px;">
                <strong>Bag. Finance</strong>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details" style="margin-top: 45px;">
            <table>
                <tr>
                    <td>No. Invoice</td>
                    <td>:</td>
                    <td><strong><?= htmlspecialchars($invoice->no_invoice) ?></strong></td>
                </tr>
                <tr>
                    <td>Tgl. Invoice</td>
                    <td>:</td>
                    <td><?= date('d F Y', strtotime($invoice->invoice_date)) ?></td>
                </tr>
                <tr>
                    <td>Tgl. Jatuh Tempo</td>
                    <td>:</td>
                    <td><?= date('d F Y', strtotime($invoice->due_date)) ?></td>
                </tr>
                <tr>
                    <td>No. Faktur</td>
                    <td>:</td>
                    <td><?= !empty($invoice->no_faktur) ? htmlspecialchars($invoice->no_faktur) : '-' ?></td>
                </tr>
                <!-- ✅ TAMBAH: No. PO (DI BAWAH NO. FAKTUR) -->
                <?php if (!empty($invoice->no_po)): ?>
                    <tr>
                        <td>No. PO</td>
                        <td>:</td>
                        <td><strong><?= htmlspecialchars($invoice->no_po) ?></strong></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="clear"></div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="desc">Deskripsi</th>
                <th class="amount">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $total_items = 0;
            $total_deductions = 0;

            foreach ($invoice->items as $item):
                $is_deduction = $item->item_type == 'deduction';
                $row_class = $is_deduction ? 'deduction-row' : '';

                if ($is_deduction) {
                    $total_deductions += abs($item->jumlah);
                } else {
                    $total_items += $item->jumlah;
                }
                ?>
                <tr class="<?= $row_class ?>">
                    <td class="no"><?= $no++ ?></td>
                    <td class="desc"><?= htmlspecialchars($item->deskripsi) ?></td>
                    <td class="amount">
                        <?php if ($is_deduction): ?>
                            -Rp<?= number_format(abs($item->jumlah), 0, ',', '.') ?>
                        <?php else: ?>
                            Rp<?= number_format($item->jumlah, 0, ',', '.') ?>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <?php
    // Array customer yang TIDAK perlu tampil PERSENTASE PPN & PPH
    $hide_percent_customers = ['CUST-0018'];
    $show_percent = !in_array($invoice->customer_kode, $hide_percent_customers);
    ?>

    <!-- Summary -->
    <div class="summary-section">
        <div class="summary-box">
            <table class="summary-table">
                <tr>
                    <td>Subtotal</td>
                    <td>Rp<?= number_format($total_items - $total_deductions, 0, ',', '.') ?></td>
                </tr>
                <?php if ($invoice->ppn_amount > 0): ?>
                    <tr>
                        <td>
                            PPN<?php if ($show_percent): ?> (<?= number_format($invoice->ppn_percent, 1) ?>%)<?php endif ?>
                        </td>
                        <td>Rp<?= number_format($invoice->ppn_amount, 0, ',', '.') ?></td>
                    </tr>
                <?php endif ?>
                <!-- <?php if ($total_deductions > 0): ?>
                <tr>
                    <td>Deduction</td>
                    <td>-Rp<?= number_format($total_deductions, 0, ',', '.') ?></td>
                </tr>
                <?php endif ?> -->
                <tr>
                    <td>PPH (<?= number_format($invoice->pph_percent, 1) ?>%)</td>
                    <td>-Rp<?= number_format($invoice->pph_amount, 0, ',', '.') ?></td>
                </tr>
                <tr class="total-row">
                    <td>Grand Total</td>
                    <td>Rp<?= number_format($invoice->grand_total, 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Bottom Section -->
    <div class="bottom-section">

        <!-- Terbilang -->
        <div class="terbilang">
            <strong>Terbilang:</strong> <?= ucwords(strtolower($invoice->terbilang)) ?>
        </div>

        <?php if (!empty($invoice->keterangan)): ?>
            <div class="notes">
                <strong>Keterangan:</strong> <?= htmlspecialchars($invoice->keterangan) ?>
            </div>
        <?php endif ?>

        <!-- Bank Details -->
        <div class="bank-details">
            <strong>Transfer ke:</strong><br>
            <strong>Nama Bank:</strong> BANK CENTRAL ASIA<br>
            <strong>Nama Akun:</strong> PT. TATA SANJAYA CAKRAWALA<br>
            <strong>No. Account:</strong> 538-031-6414
        </div>

        <!-- Signature -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="name">Shintya Ayu</div>
                <div class="title">Finance Manager</div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="footer-note">
            *) Harap mencantumkan nomor invoice ini pada saat pembayaran.
            Pembayaran dengan cek atau giro diasumsikan sah apabila sudah masuk ke rekening kami.
        </div>
    </div>

</body>

</html>