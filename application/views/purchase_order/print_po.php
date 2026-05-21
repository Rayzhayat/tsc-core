<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Purchase Order - <?= $po->no_po ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
        }

        .container {
            width: 100%;
            padding: 20px;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #4e73df;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-info {
            width: 60%;
            float: left;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 9pt;
            color: #666;
        }

        .po-title {
            width: 40%;
            float: right;
            text-align: right;
        }

        .po-title h1 {
            font-size: 24pt;
            color: #4e73df;
            margin-bottom: 5px;
        }

        .po-number {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Info Boxes */
        .info-section {
            margin-bottom: 20px;
        }

        .info-box {
            width: 48%;
            float: left;
            border: 1px solid #e3e6f0;
            padding: 10px;
            background: #f8f9fc;
        }

        .info-box:first-child {
            margin-right: 4%;
        }

        .info-box h3 {
            font-size: 11pt;
            color: #4e73df;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .info-row {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            width: 120px;
            display: inline-block;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .items-table th {
            background: #4e73df;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9pt;
            border: 1px solid #4e73df;
        }

        .items-table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 9pt;
            vertical-align: top;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f8f9fc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .item-name {
            font-weight: bold;
        }

        .item-spec {
            font-size: 8pt;
            color: #666;
            margin-top: 3px;
        }

        /* Calculation Summary */
        .calculation-box {
            width: 50%;
            float: right;
            margin-top: 10px;
        }

        .calc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .calc-table td {
            padding: 5px 10px;
            border-bottom: 1px solid #e3e6f0;
        }

        .calc-label {
            text-align: left;
            width: 60%;
        }

        .calc-value {
            text-align: right;
            width: 40%;
            font-weight: bold;
        }

        .calc-total {
            background: #4e73df;
            color: white;
            font-size: 12pt;
            font-weight: bold;
        }

        .calc-total td {
            border: none;
            padding: 10px;
        }

        /* Terms & Conditions */
        .terms-section {
            margin-top: 30px;
            clear: both;
            border-top: 2px solid #e3e6f0;
            padding-top: 15px;
        }

        .terms-section h3 {
            font-size: 11pt;
            color: #4e73df;
            margin-bottom: 10px;
        }

        .terms-list {
            padding-left: 20px;
            font-size: 9pt;
        }

        .terms-list li {
            margin-bottom: 5px;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 30%;
            float: left;
            text-align: center;
            margin-right: 5%;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
            font-size: 9pt;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #4e73df;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10pt;
        }

        .status-draft {
            background: #858796;
            color: white;
        }

        .status-pending {
            background: #36b9cc;
            color: white;
        }

        .status-approved {
            background: #1cc88a;
            color: white;
        }

        .status-rejected {
            background: #e74a3b;
            color: white;
        }

        .status-completed {
            background: #4e73df;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Header -->
        <div class="header clearfix">
            <div class="company-info">
                <div class="company-name">PT. YOUR COMPANY NAME</div>
                <div class="company-details">
                    Jl. Contoh Alamat No. 123, Jakarta 12345<br>
                    Telp: (021) 1234-5678 | Email: info@yourcompany.com<br>
                    NPWP: 01.234.567.8-901.000
                </div>
            </div>
            <div class="po-title">
                <h1>PURCHASE ORDER</h1>
                <div class="po-number"><?= $po->no_po ?></div>
                <?php
                $status_class = [
                    'draft' => 'status-draft',
                    'pending' => 'status-pending',
                    'approved' => 'status-approved',
                    'rejected' => 'status-rejected',
                    'completed' => 'status-completed'
                ];
                $class = $status_class[$po->status] ?? 'status-draft';
                ?>
                <div style="margin-top: 10px;">
                    <span class="status-badge <?= $class ?>">
                        <?= strtoupper(str_replace('_', ' ', $po->status)) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- PO Info & Vendor Info -->
        <div class="info-section clearfix">
            <div class="info-box">
                <h3>PO INFORMATION</h3>
                <div class="info-row">
                    <span class="info-label">PO Date:</span>
                    <?= date('d F Y', strtotime($po->tanggal_po)) ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Category:</span>
                    <?= ucfirst($po->kategori) ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Jenis Pembelian:</span>
                    <?= ucfirst(str_replace('_', ' ', $po->jenis_pembelian)) ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Terms:</span>
                    <?= $po->payment_terms ?: '-' ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Expected Delivery:</span>
                    <?= $po->expected_delivery ? date('d F Y', strtotime($po->expected_delivery)) : '-' ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Requested By:</span>
                    <?= htmlspecialchars($po->request_by) ?>
                </div>
            </div>

            <div class="info-box">
                <h3>VENDOR INFORMATION</h3>
                <div class="info-row">
                    <span class="info-label">Vendor:</span>
                    <strong><?= htmlspecialchars($po->vendor_nama) ?></strong>
                </div>
                <div class="info-row">
                    <span class="info-label">Kode:</span>
                    <?= htmlspecialchars($po->vendor_kode) ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <?= nl2br(htmlspecialchars($po->vendor_alamat)) ?: '-' ?>
                </div>
                <div class="info-row">
                    <span class="info-label">NPWP:</span>
                    <?= htmlspecialchars($po->vendor_npwp) ?: '-' ?>
                </div>
                <div class="info-row">
                    <span class="info-label">PIC:</span>
                    <?= htmlspecialchars($po->vendor_pic) ?: '-' ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <?= htmlspecialchars($po->vendor_telp) ?: '-' ?>
                </div>
            </div>
        </div>

        <?php if (!empty($po->delivery_address)): ?>
            <!-- Delivery Address -->
            <div style="clear: both; margin-bottom: 15px; padding: 10px; border: 1px solid #e3e6f0; background: #fffbf0;">
                <strong>Delivery Address:</strong><br>
                <?= nl2br(htmlspecialchars($po->delivery_address)) ?>
            </div>
        <?php endif; ?>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="3%" class="text-center">No</th>
                    <th width="25%">Item Description</th>
                    <th width="10%" class="text-center">Code</th>
                    <th width="8%" class="text-center">Unit</th>
                    <th width="8%" class="text-right">Qty</th>
                    <th width="12%" class="text-right">Unit Price</th>
                    <th width="8%" class="text-center">Disc %</th>
                    <th width="13%" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($details as $item):
                ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <div class="item-name"><?= htmlspecialchars($item->item_nama) ?></div>
                            <?php if (!empty($item->item_spesifikasi)): ?>
                                <div class="item-spec"><?= htmlspecialchars($item->item_spesifikasi) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item->keterangan)): ?>
                                <div class="item-spec"><em>Note: <?= htmlspecialchars($item->keterangan) ?></em></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= htmlspecialchars($item->item_kode) ?: '-' ?></td>
                        <td class="text-center"><?= htmlspecialchars($item->item_satuan) ?></td>
                        <td class="text-right"><?= number_format($item->qty_order, 2) ?></td>
                        <td class="text-right">Rp <?= number_format($item->harga_satuan, 0, ',', '.') ?></td>
                        <td class="text-center">
                            <?php if ($item->diskon_persen > 0): ?>
                                <?= number_format($item->diskon_persen, 2) ?>%
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><strong>Rp <?= number_format($item->subtotal, 0, ',', '.') ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Calculation Summary -->
        <div class="calculation-box">
            <table class="calc-table">
                <tr>
                    <td class="calc-label">Subtotal:</td>
                    <td class="calc-value">Rp <?= number_format($po->subtotal, 0, ',', '.') ?></td>
                </tr>

                <?php if ($po->diskon_nominal > 0): ?>
                    <tr>
                        <td class="calc-label">Discount (<?= $po->diskon_persen ?>%):</td>
                        <td class="calc-value">- Rp <?= number_format($po->diskon_nominal, 0, ',', '.') ?></td>
                    </tr>
                <?php endif; ?>

                <?php if ($po->ongkir > 0): ?>
                    <tr>
                        <td class="calc-label">Shipping:</td>
                        <td class="calc-value">Rp <?= number_format($po->ongkir, 0, ',', '.') ?></td>
                    </tr>
                <?php endif; ?>

                <?php if ($po->biaya_lain > 0): ?>
                    <tr>
                        <td class="calc-label">Other Costs:</td>
                        <td class="calc-value">Rp <?= number_format($po->biaya_lain, 0, ',', '.') ?></td>
                    </tr>
                <?php endif; ?>

                <?php if ($po->ppn_nominal > 0): ?>
                    <tr>
                        <td class="calc-label">PPN (<?= $po->ppn_persen ?>%):</td>
                        <td class="calc-value">Rp <?= number_format($po->ppn_nominal, 0, ',', '.') ?></td>
                    </tr>
                <?php endif; ?>

                <?php if ($po->pph_nominal > 0): ?>
                    <tr>
                        <td class="calc-label">PPh (<?= $po->pph_persen ?>%):</td>
                        <td class="calc-value">- Rp <?= number_format($po->pph_nominal, 0, ',', '.') ?></td>
                    </tr>
                <?php endif; ?>

                <tr class="calc-total">
                    <td>TOTAL:</td>
                    <td>Rp <?= number_format($po->total_po, 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <?php if (!empty($po->keterangan)): ?>
            <!-- Notes -->
            <div style="margin-top: 20px; padding: 10px; border: 1px solid #e3e6f0; background: #f8f9fc;">
                <strong>Notes:</strong><br>
                <?= nl2br(htmlspecialchars($po->keterangan)) ?>
            </div>
        <?php endif; ?>

        <!-- Terms & Conditions -->
        <div class="terms-section">
            <h3>Terms & Conditions:</h3>
            <ol class="terms-list">
                <li>Pembayaran sesuai dengan payment terms yang telah disepakati.</li>
                <li>Barang yang dikirim harus sesuai dengan spesifikasi yang tercantum dalam PO ini.</li>
                <li>Vendor bertanggung jawab atas kualitas barang yang dikirimkan.</li>
                <li>Pengiriman dilakukan sesuai dengan jadwal yang telah ditentukan.</li>
                <li>Jika terjadi keterlambatan pengiriman, vendor wajib memberitahukan kepada pembeli.</li>
                <li>Barang yang tidak sesuai spesifikasi akan dikembalikan kepada vendor.</li>
                <li>PO ini berlaku sebagai kontrak pembelian yang sah antara kedua belah pihak.</li>
            </ol>
        </div>

        <!-- Signature Section -->
        <div class="signature-section clearfix">
            <div class="signature-box">
                <div class="signature-title">Prepared By</div>
                <div class="signature-line">
                    <?= htmlspecialchars($po->created_by) ?><br>
                    <small><?= date('d/m/Y', strtotime($po->created_at)) ?></small>
                </div>
            </div>

            <?php if (!empty($po->approved_by)): ?>
                <div class="signature-box">
                    <div class="signature-title">Approved By</div>
                    <div class="signature-line">
                        <?= htmlspecialchars($po->approved_by) ?><br>
                        <small><?= date('d/m/Y', strtotime($po->approved_at)) ?></small>
                    </div>
                </div>
            <?php else: ?>
                <div class="signature-box">
                    <div class="signature-title">Approved By</div>
                    <div class="signature-line">
                        _______________________
                    </div>
                </div>
            <?php endif; ?>

            <div class="signature-box">
                <div class="signature-title">Vendor</div>
                <div class="signature-line">
                    _______________________
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer" style="clear: both;">
            <p>This is a computer-generated document. No signature required.</p>
            <p>Purchase Order No: <?= $po->no_po ?> | Generated: <?= date('d F Y H:i') ?></p>
        </div>

    </div>
</body>

</html>