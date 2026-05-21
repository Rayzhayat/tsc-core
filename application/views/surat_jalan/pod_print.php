<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POD - <?= $pod->no_surat_jalan ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header Section */
        .header {
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: middle;
        }

        .company-name {
            font-size: 20pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 9pt;
            color: #7f8c8d;
            font-style: italic;
        }

        .doc-title {
            font-size: 18pt;
            font-weight: bold;
            color: #e74c3c;
            letter-spacing: 1px;
        }

        .doc-subtitle {
            font-size: 10pt;
            color: #95a5a6;
            margin-top: 3px;
        }

        /* Info Grid */
        .info-section {
            margin-bottom: 20px;
        }

        .section-title {
            background: #34495e;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        .info-grid {
            display: table;
            width: 100%;
            border: 1px solid #ddd;
            border-collapse: collapse;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 8px 12px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .info-cell.label {
            width: 30%;
            background: #ecf0f1;
            font-weight: bold;
            color: #2c3e50;
        }

        .info-cell.value {
            width: 70%;
        }

        /* Two Column Layout */
        .two-column {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .column:first-child {
            padding-right: 10px;
        }

        .column:last-child {
            padding-left: 10px;
        }

        /* Quantity Display */
        .qty-box {
            text-align: center;
            padding: 15px;
            border: 2px solid #3498db;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .qty-box.delivered {
            background: #d5f4e6;
            border-color: #27ae60;
        }

        .qty-box.rejected {
            background: #fadbd8;
            border-color: #e74c3c;
        }

        .qty-number {
            font-size: 32pt;
            font-weight: bold;
            color: #2c3e50;
            line-height: 1;
        }

        .qty-label {
            font-size: 10pt;
            text-transform: uppercase;
            color: #7f8c8d;
            margin-top: 5px;
            font-weight: bold;
        }

        /* Condition Badge */
        .condition-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 11pt;
            text-transform: uppercase;
        }

        .condition-badge.baik {
            background: #27ae60;
            color: white;
        }

        .condition-badge.rusak {
            background: #e74c3c;
            color: white;
        }

        .condition-badge.rusak_sebagian,
        .condition-badge.kurang {
            background: #f39c12;
            color: white;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            padding: 15px;
            border: 2px dashed #bdc3c7;
            border-radius: 5px;
            min-height: 150px;
        }

        .signature-image {
            max-width: 250px;
            max-height: 120px;
            margin: 10px auto;
            display: block;
            border: 1px solid #ddd;
            padding: 5px;
            background: white;
        }

        .signature-name {
            font-weight: bold;
            margin-top: 10px;
            color: #2c3e50;
        }

        .signature-date {
            font-size: 9pt;
            color: #7f8c8d;
            margin-top: 3px;
        }

        /* Timeline */
        .timeline {
            margin: 15px 0;
        }

        .timeline-item {
            padding: 8px 0 8px 25px;
            border-left: 3px solid #3498db;
            margin-left: 10px;
            position: relative;
        }

        .timeline-item::before {
            content: '●';
            position: absolute;
            left: -9px;
            top: 8px;
            color: #3498db;
            font-size: 14pt;
        }

        .timeline-item.completed::before {
            color: #27ae60;
        }

        .timeline-time {
            font-size: 9pt;
            color: #7f8c8d;
            font-weight: bold;
        }

        .timeline-title {
            font-weight: bold;
            color: #2c3e50;
            margin-top: 2px;
        }

        .timeline-desc {
            font-size: 9pt;
            color: #7f8c8d;
            margin-top: 2px;
        }

        /* Photos Grid */
        .photos-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .photo-row {
            display: table-row;
        }

        .photo-cell {
            display: table-cell;
            width: 33.33%;
            padding: 5px;
            text-align: center;
            vertical-align: top;
        }

        .photo-item {
            border: 2px solid #ecf0f1;
            border-radius: 5px;
            overflow: hidden;
            background: white;
        }

        .photo-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }

        .photo-label {
            padding: 5px;
            background: #34495e;
            color: white;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Notes Box */
        .notes-box {
            background: #fff9e6;
            border-left: 4px solid #f39c12;
            padding: 12px;
            margin: 15px 0;
            border-radius: 3px;
        }

        .notes-title {
            font-weight: bold;
            color: #e67e22;
            margin-bottom: 5px;
            font-size: 10pt;
        }

        .notes-content {
            color: #7f8c8d;
            font-size: 10pt;
            line-height: 1.5;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            font-size: 9pt;
            color: #95a5a6;
        }

        .footer-note {
            font-style: italic;
            margin-bottom: 5px;
        }

        .footer-info {
            font-size: 8pt;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }

        /* Print Specific */
        @media print {
            body {
                padding: 0;
            }

            .page-break {
                page-break-after: always;
            }
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
        }

        .status-badge.completed {
            background: #d5f4e6;
            color: #27ae60;
            border: 2px solid #27ae60;
        }

        .status-badge.pending {
            background: #fef5e7;
            color: #f39c12;
            border: 2px solid #f39c12;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <div class="header-top">
                <div class="header-left">
                    <div class="company-name">YOUR COMPANY NAME</div>
                    <div class="company-tagline">Reliable Transportation & Logistics</div>
                </div>
                <div class="header-right">
                    <div class="doc-title">PROOF OF DELIVERY</div>
                    <div class="doc-subtitle">Bukti Penerimaan Barang</div>
                </div>
            </div>
        </div>

        <!-- DOCUMENT INFO -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell label">No. Surat Jalan</div>
                    <div class="info-cell value"><strong><?= $pod->no_surat_jalan ?></strong></div>
                </div>
                <div class="info-row">
                    <div class="info-cell label">Tanggal Pengiriman</div>
                    <div class="info-cell value"><?= date('d F Y', strtotime($pod->tanggal)) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-cell label">POD Status</div>
                    <div class="info-cell value">
                        <span class="status-badge <?= $pod->pod_status ?>">
                            <?= strtoupper($pod->pod_status) ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell label">Tanggal Submit POD</div>
                    <div class="info-cell value">
                        <?= $pod->pod_submitted_at ? date('d F Y H:i', strtotime($pod->pod_submitted_at)) : '-' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- TRIP INFORMATION -->
        <div class="info-section">
            <div class="section-title">📋 INFORMASI PENGIRIMAN</div>
            <div class="two-column">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell label">Driver</div>
                            <div class="info-cell value"><?= htmlspecialchars($pod->nama_driver ?? '-') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell label">No. Polisi</div>
                            <div class="info-cell value"><?= $pod->no_polisi ?? '-' ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell label">Muatan</div>
                            <div class="info-cell value"><?= htmlspecialchars($pod->muatan ?? '-') ?></div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell label">Customer</div>
                            <div class="info-cell value"><?= htmlspecialchars($pod->customer ?? '-') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell label">Tujuan</div>
                            <div class="info-cell value"><?= htmlspecialchars($pod->tujuan ?? '-') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell label">Service</div>
                            <div class="info-cell value"><?= htmlspecialchars($pod->service ?? '-') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DELIVERY DETAILS -->
        <div class="info-section">
            <div class="section-title">📦 DETAIL PENERIMAAN</div>

            <!-- Quantity Boxes -->
            <div class="two-column">
                <div class="column">
                    <div class="qty-box delivered">
                        <div class="qty-number"><?= $pod->qty_delivered ?? 0 ?></div>
                        <div class="qty-label">Jumlah Diterima</div>
                    </div>
                </div>
                <div class="column">
                    <div class="qty-box rejected">
                        <div class="qty-number"><?= $pod->qty_rejected ?? 0 ?></div>
                        <div class="qty-label">Jumlah Ditolak</div>
                    </div>
                </div>
            </div>

            <!-- Condition & Times -->
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell label">Kondisi Barang</div>
                    <div class="info-cell value">
                        <span class="condition-badge <?= $pod->delivery_condition ?? 'baik' ?>">
                            <?= ucfirst(str_replace('_', ' ', $pod->delivery_condition ?? 'baik')) ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell label">Waktu Tiba</div>
                    <div class="info-cell value">
                        <?= $pod->arrival_time ? date('d/m/Y H:i', strtotime($pod->arrival_time)) : '-' ?>
                    </div>
                </div>
                <?php if ($pod->unloading_start): ?>
                    <div class="info-row">
                        <div class="info-cell label">Waktu Bongkar</div>
                        <div class="info-cell value">
                            <?= date('H:i', strtotime($pod->unloading_start)) ?>
                            <?php if ($pod->unloading_finish): ?>
                                - <?= date('H:i', strtotime($pod->unloading_finish)) ?>
                                <?php
                                $start = strtotime($pod->unloading_start);
                                $finish = strtotime($pod->unloading_finish);
                                $duration = round(($finish - $start) / 60);
                                ?>
                                (<?= $duration ?> menit)
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($pod->delivery_notes): ?>
                <div class="notes-box">
                    <div class="notes-title">📝 Catatan Pengiriman:</div>
                    <div class="notes-content"><?= nl2br(htmlspecialchars($pod->delivery_notes)) ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- RECEIVER INFORMATION -->
        <div class="info-section">
            <div class="section-title">👤 INFORMASI PENERIMA</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell label">Nama Penerima</div>
                    <div class="info-cell value">
                        <strong><?= htmlspecialchars($pod->receiver_name ?? '-') ?></strong>
                    </div>
                </div>
                <?php if ($pod->receiver_phone): ?>
                    <div class="info-row">
                        <div class="info-cell label">No. Telepon</div>
                        <div class="info-cell value"><?= htmlspecialchars($pod->receiver_phone) ?></div>
                    </div>
                <?php endif; ?>
                <div class="info-row">
                    <div class="info-cell label">Waktu Penerimaan</div>
                    <div class="info-cell value">
                        <?= $pod->arrival_time ? date('d F Y, H:i', strtotime($pod->arrival_time)) : '-' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- TIMELINE -->
        <?php if (!empty($pod->timeline)): ?>
            <div class="info-section">
                <div class="section-title">🕒 TIMELINE PENGIRIMAN</div>
                <div class="timeline">
                    <?php foreach ($pod->timeline as $event): ?>
                        <?php
                        $completed = in_array($event->event_type, ['completed', 'pod_submitted', 'arrival', 'unloading_finish']);
                        ?>
                        <div class="timeline-item <?= $completed ? 'completed' : '' ?>">
                            <div class="timeline-time">
                                <?= date('d/m/Y H:i', strtotime($event->event_time)) ?>
                            </div>
                            <div class="timeline-title">
                                <?= $this->get_event_title($event->event_type) ?>
                            </div>
                            <?php if ($event->notes): ?>
                                <div class="timeline-desc"><?= htmlspecialchars($event->notes) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- SIGNATURE -->
        <?php if ($pod->receiver_signature): ?>
            <div class="signature-section">
                <div class="section-title">✍️ TANDA TANGAN PENERIMA</div>
                <div class="signature-box">
                    <img src="<?= base_url('uploads/pod/signatures/' . $pod->receiver_signature) ?>"
                        alt="Signature"
                        class="signature-image">
                    <div class="signature-name"><?= htmlspecialchars($pod->receiver_name ?? '-') ?></div>
                    <div class="signature-date">
                        <?= $pod->pod_submitted_at ? date('d F Y, H:i', strtotime($pod->pod_submitted_at)) : '' ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- PAGE BREAK FOR PHOTOS -->
        <?php if (!empty($pod->photos) || $pod->photo_proof): ?>
            <div class="page-break"></div>

            <!-- PHOTOS -->
            <div class="info-section">
                <div class="section-title">📸 DOKUMENTASI FOTO</div>
                <div class="photos-grid">
                    <?php
                    $photo_count = 0;
                    $photos_to_display = [];

                    // Add main photo
                    if ($pod->photo_proof) {
                        $photos_to_display[] = [
                            'path' => 'uploads/pod/proof/' . $pod->photo_proof,
                            'label' => 'Main Photo'
                        ];
                    }

                    // Add additional photos
                    if (!empty($pod->photos)) {
                        foreach ($pod->photos as $photo) {
                            $photos_to_display[] = [
                                'path' => 'uploads/pod/photos/' . $photo->photo_path,
                                'label' => ucfirst($photo->photo_type)
                            ];
                        }
                    }

                    // Display photos in rows of 3
                    foreach ($photos_to_display as $index => $photo):
                        if ($index % 3 == 0): ?>
                            <div class="photo-row">
                            <?php endif; ?>

                            <div class="photo-cell">
                                <div class="photo-item">
                                    <img src="<?= base_url($photo['path']) ?>" alt="Photo">
                                    <div class="photo-label"><?= $photo['label'] ?></div>
                                </div>
                            </div>

                            <?php if (($index + 1) % 3 == 0 || $index == count($photos_to_display) - 1): ?>
                            </div>
                    <?php endif;
                        endforeach;
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- FOOTER -->
        <div class="footer">
            <div class="footer-note">
                Dokumen ini dicetak secara otomatis dan sah tanpa tanda tangan basah
            </div>
            <div class="footer-info">
                Dicetak pada: <?= date('d F Y, H:i') ?> WIB |
                POD No: <?= $pod->no_surat_jalan ?> |
                Submitted by: <?= $pod->pod_submitted_by ?? 'System' ?>
            </div>
        </div>
    </div>
</body>

</html>