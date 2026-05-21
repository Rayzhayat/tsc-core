<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - <?= $surat_jalan->no_surat_jalan ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 10mm 10mm 10mm 10mm;
            size: A4 portrait;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 100%;
            padding: 10px;
            overflow: hidden;
        }

        /* Header - Compact */
        .header {
            border-bottom: 2px solid #4e73df;
            margin-right: 20px;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            width: 75%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 25%;
            vertical-align: top;
            text-align: right;
        }

        .company-name {
            font-size: 13px;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 2px;
        }

        .company-info {
            font-size: 7px;
            color: #666;
            line-height: 1.3;
        }

        /* Document Title - Compact */
        .doc-title {
            text-align: center;
            margin: 6px 0;
            padding: 5px;
            background: #4e73df;
            color: white;
        }

        .doc-title h1 {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .doc-number {
            font-size: 10px;
            font-weight: bold;
        }

        /* Info Table - Compact */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            table-layout: fixed;
        }

        .info-table td {
            padding: 1px 3px;
            vertical-align: top;
            font-size: 8px;
            word-wrap: break-word;
        }

        .info-table td:first-child {
            width: 20%;
            font-weight: bold;
        }

        .info-table td:nth-child(2) {
            width: 2%;
        }

        .info-table td:nth-child(3) {
            width: 28%;
        }

        .info-table td:nth-child(4) {
            width: 18%;
            font-weight: bold;
        }

        .info-table td:nth-child(5) {
            width: 2%;
        }

        .info-table td:nth-child(6) {
            width: 30%;
        }

        /* Route Visual - Compact */
        .route-visual {
            text-align: center;
            padding: 5px;
            border: 1px solid #4e73df;
            margin: 5px 0;
            background: #f0f4ff;
            font-size: 8px;
        }

        .route-item {
            display: inline-block;
            vertical-align: middle;
            margin: 0 4px;
        }

        .route-location {
            font-weight: bold;
            font-size: 9px;
            color: #4e73df;
        }

        .route-arrow {
            font-size: 12px;
            color: #666;
        }

        /* Three Column Layout - Fixed widths */
        .three-column {
            display: table;
            width: 100%;
            margin-bottom: 5px;
            table-layout: fixed;
        }

        .column {
            display: table-cell;
            vertical-align: top;
            padding: 0 2px;
        }

        .column:nth-child(1) {
            width: 26%;
            /* Driver column */
        }

        .column:nth-child(2) {
            width: 42%;
            /* Muatan column */
        }

        .column:nth-child(3) {
            width: 32%;
            /* Biaya column */
        }

        /* Info Box - Compact */
        .info-box {
            border: 1px solid #ddd;
            margin-bottom: 5px;
            overflow: hidden;
        }

        .info-box-header {
            background: #4e73df;
            color: white;
            padding: 2px 5px;
            font-weight: bold;
            font-size: 8px;
        }

        .info-box-content {
            padding: 3px 5px;
            background: #f8f9fc;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Muatan Box - Compact */
        .muatan-box {
            border: 1px solid #ddd;
            padding: 5px;
            min-height: 35px;
            background: #fff;
            font-size: 7px;
            line-height: 1.3;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Biaya Table - Fixed layout with word wrap */
        .biaya-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            table-layout: fixed;
        }

        .biaya-table th,
        .biaya-table td {
            border: 1px solid #ddd;
            padding: 2px 3px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        .biaya-table th {
            background: #f8f9fc;
            font-weight: bold;
            font-size: 7px;
        }

        .biaya-table td:first-child {
            width: 50%;
            font-size: 7px;
        }

        .biaya-table td:last-child {
            width: 50%;
            text-align: right;
            font-size: 7px;
        }

        .biaya-table .total-row {
            background: #4e73df;
            color: white;
            font-weight: bold;
        }

        /* Signature Section - Compact */
        .signature-section {
            margin-top: 600px;
            page-break-inside: avoid;
        }

        .signature-boxes {
            display: table;
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .signature-box {
            display: table-cell;
            width: 33.33%;
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
            vertical-align: top;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 25px;
            font-size: 8px;
        }

        .signature-name {
            border-top: 1px solid #333;
            padding-top: 2px;
            margin-top: 3px;
            font-weight: bold;
            font-size: 7px;
            word-wrap: break-word;
        }

        .signature-date {
            font-size: 6px;
            color: #666;
        }

        /* Important Notice - Compact */
        .important-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-left: 3px solid #ffc107;
            padding: 4px;
            margin: 5px 0;
            font-size: 7px;
            line-height: 1.3;
            word-wrap: break-word;
        }

        .important-notice strong {
            color: #856404;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7px;
            white-space: nowrap;
        }

        .status-draft {
            background: #858796;
            color: white;
        }

        .status-scheduled {
            background: #4e73df;
            color: white;
        }

        .status-on_trip {
            background: #f6c23e;
            color: #333;
        }

        .status-completed {
            background: #1cc88a;
            color: white;
        }

        .status-cancelled {
            background: #e74a3b;
            color: white;
        }

        /* Notes - Compact */
        .notes-section {
            border: 1px solid #ddd;
            padding: 4px;
            margin: 5px 0;
            background: #fffbea;
            font-size: 7px;
            line-height: 1.3;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 2px;
            color: #856404;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Header - Compact -->
        <div class="header">
            <div class="header-content" style="margin-top: 40px;">
                <div class="header-left">
                    <div class="company-name">PT. NAMA PERUSAHAAN ANDA</div>
                    <div class="company-info">
                        Jl. Contoh Alamat No. 123, Jakarta | Telp: (021) 1234-5678 | Email: info@perusahaan.com
                    </div>
                </div>
                <div class="header-right" style="margin-right: 20px;">
                    <span class="status-badge status-<?= $surat_jalan->status ?>" style="margin-right: 20px;">
                        <?= strtoupper($surat_jalan->status) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Document Title -->
        <div class="doc-title" style="margin-right: 20px;">
            <h1>SURAT JALAN</h1>
            <div class="doc-number"><?= htmlspecialchars($surat_jalan->no_surat_jalan) ?></div>
        </div>

        <!-- Basic Info - Single Row -->
        <table class="info-table">
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td><strong><?= date('d/m/Y', strtotime($surat_jalan->tanggal)) ?></strong></td>
                <td width="18%">Customer</td>
                <td width="3%">:</td>
                <td><strong><?= htmlspecialchars($surat_jalan->customer) ?></strong></td>
            </tr>
            <tr>
                <td>Service</td>
                <td>:</td>
                <td><?= htmlspecialchars($surat_jalan->service) ?> - <?= htmlspecialchars($surat_jalan->sla) ?></td>
                <td>No. Polisi</td>
                <td>:</td>
                <td><strong><?= htmlspecialchars($surat_jalan->no_polisi) ?></strong> (<?= htmlspecialchars($surat_jalan->unit_tipe) ?>)</td>
            </tr>
        </table>

        <!-- Route Visual - Compact -->
        <div class="route-visual" style="margin-right: 20px;">
            <div class="route-item">
                <div class="route-location"><?= htmlspecialchars($surat_jalan->origin) ?></div>
            </div>
            <div class="route-item">
                <span class="route-arrow">→</span>
            </div>
            <div class="route-item">
                <div class="route-location"><?= htmlspecialchars($surat_jalan->dest1) ?></div>
            </div>
            <?php if ($surat_jalan->dest2): ?>
                <div class="route-item">
                    <span class="route-arrow">→</span>
                </div>
                <div class="route-item">
                    <div class="route-location"><?= htmlspecialchars($surat_jalan->dest2) ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Three Column Layout: Driver, Muatan, Biaya -->
        <div class="three-column">
            <!-- Driver -->
            <div class="column">
                <div class="info-box">
                    <div class="info-box-header">DRIVER</div>
                    <div class="info-box-content">
                        <strong><?= htmlspecialchars($surat_jalan->nama_driver) ?></strong><br>
                        NIK: <?= htmlspecialchars($surat_jalan->driver_nik) ?>
                    </div>
                </div>

                <!-- Waktu Info (if completed) -->
                <?php if ($surat_jalan->status == 'completed'): ?>
                    <div style="font-size: 7px; margin-top: 3px;">
                        <strong>Waktu:</strong><br>
                        Berangkat: <?= date('H:i', strtotime($surat_jalan->jam_berangkat)) ?><br>
                        Tiba: <?= date('H:i', strtotime($surat_jalan->jam_tiba)) ?><br>
                        <strong style="color: <?= $surat_jalan->sla_status == 'on_time' ? '#1cc88a' : '#e74a3b' ?>">
                            SLA: <?= strtoupper(str_replace('_', ' ', $surat_jalan->sla_status)) ?>
                        </strong>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Muatan -->
            <div class="column">
                <div class="info-box">
                    <div class="info-box-header">MUATAN</div>
                    <div class="info-box-content">
                        <div class="muatan-box">
                            <?= nl2br(htmlspecialchars($surat_jalan->muatan)) ?>
                        </div>
                        <?php if ($surat_jalan->tonase_aktual || $surat_jalan->kubikasi_aktual): ?>
                            <div style="font-size: 7px; margin-top: 3px;">
                                <?php if ($surat_jalan->tonase_aktual): ?>
                                    Tonase: <strong><?= number_format($surat_jalan->tonase_aktual, 2) ?> Ton</strong><br>
                                <?php endif; ?>
                                <?php if ($surat_jalan->kubikasi_aktual): ?>
                                    Kubikasi: <strong><?= number_format($surat_jalan->kubikasi_aktual, 2) ?> m³</strong>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Biaya -->
            <div class="column">
                <div class="info-box" style="margin-right: 20px;">
                    <div class="info-box-header">RINCIAN BIAYA</div>
                    <div class="info-box-content" style="padding: 0;">
                        <table class="biaya-table">
                            <tr>
                                <td>Biaya Sewa</td>
                                <td class="text-right"><?= number_format($surat_jalan->biaya_sewa, 0, ',', '.') ?></td>
                            </tr>
                            <?php if ($surat_jalan->biaya_solar > 0): ?>
                                <tr>
                                    <td>Solar</td>
                                    <td class="text-right"><?= number_format($surat_jalan->biaya_solar, 0, ',', '.') ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($surat_jalan->biaya_tol > 0): ?>
                                <tr>
                                    <td>Tol</td>
                                    <td class="text-right"><?= number_format($surat_jalan->biaya_tol, 0, ',', '.') ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($surat_jalan->biaya_parkir > 0): ?>
                                <tr>
                                    <td>Parkir</td>
                                    <td class="text-right"><?= number_format($surat_jalan->biaya_parkir, 0, ',', '.') ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($surat_jalan->biaya_makan > 0): ?>
                                <tr>
                                    <td>Makan</td>
                                    <td class="text-right"><?= number_format($surat_jalan->biaya_makan, 0, ',', '.') ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($surat_jalan->biaya_lainnya > 0): ?>
                                <tr>
                                    <td>Lainnya</td>
                                    <td class="text-right"><?= number_format($surat_jalan->biaya_lainnya, 0, ',', '.') ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="total-row">
                                <td><strong>TOTAL</strong></td>
                                <td class="text-right"><strong><?= number_format($surat_jalan->total_biaya, 0, ',', '.') ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan (if exists) -->
        <?php if ($surat_jalan->catatan): ?>
            <div class="notes-section" style="margin-right: 20px;">
                <span class="notes-title">CATATAN:</span> <?= htmlspecialchars($surat_jalan->catatan) ?>
            </div>
        <?php endif; ?>

        <!-- Important Notice -->
        <div class="important-notice" style="margin-right: 20px;">
            <strong>PENTING:</strong> Surat jalan ini merupakan bukti sah pengiriman barang. Periksa dengan teliti sebelum ditandatangani. Kerusakan/kehilangan setelah TT menjadi tanggung jawab penerima.
        </div>

        <!-- Signature Section - Compact -->
        <div class="signature-section" style="margin-right: 20px;">
            <div class="signature-boxes">
                <div class="signature-box">
                    <div class="signature-title">PENGIRIM</div>
                    <div style="height: 30px;"></div>
                    <div class="signature-name">( _____________ )</div>
                    <div class="signature-date">Tgl: <?= date('d/m/Y') ?></div>
                </div>

                <div class="signature-box">
                    <div class="signature-title">DRIVER</div>
                    <div style="height: 30px;"></div>
                    <div class="signature-name"><?= htmlspecialchars($surat_jalan->nama_driver) ?></div>
                    <div class="signature-date">Tgl: <?= date('d/m/Y') ?></div>
                </div>

                <div class="signature-box" >
                    <div class="signature-title">PENERIMA</div>
                    <div style="height: 30px;"></div>
                    <div class="signature-name">( _____________ )</div>
                    <div class="signature-date">Tgl: __________</div>
                </div>
            </div>
        </div>

        <!-- Footer - Inline -->
        <div style="margin-top: 8px; text-align: center; font-size: 7px; color: #666; border-top: 1px solid #ddd; padding-top: 5px;">
            Dicetak: <?= date('d/m/Y H:i') ?> | <?= htmlspecialchars($surat_jalan->no_surat_jalan) ?>
        </div>

    </div>
</body>

</html>