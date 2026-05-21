<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* ── Typography & Base ── */
        body {
            font-family: 'Nunito', sans-serif;
        }

        /* ── Badge styles (consistent with lihat.php) ── */
        .badge-status-approved {
            background-color: #1cc88a;
            color: white;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-status-paid {
            background-color: #4e73df;
            color: white;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-status-pending {
            background-color: #f6c23e;
            color: white;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-status-rejected {
            background-color: #e74a3b;
            color: white;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-vendor {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-non-vendor {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        /* ── Hero / Header Section ── */
        .detail-hero {
            background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);
            border-radius: 12px;
            padding: 30px 35px;
            color: white;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(231, 74, 59, 0.35);
        }

        .detail-hero::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.07);
        }

        .detail-hero::after {
            content: '';
            position: absolute;
            bottom: -60px;
            right: 80px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
        }

        .detail-hero .reff-badge {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 30px;
            padding: 6px 20px;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 2px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .detail-hero .hero-total {
            font-size: 38px;
            font-weight: 900;
            letter-spacing: -1px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .detail-hero .hero-meta {
            font-size: 13px;
            opacity: 0.85;
            margin-top: 5px;
        }

        /* ── Info Cards ── */
        .info-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .info-card .card-header-custom {
            padding: 14px 20px;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card .card-header-custom i {
            font-size: 15px;
        }

        .info-card .card-body-custom {
            padding: 20px;
        }

        /* Color coded card headers */
        .header-primary {
            background: #4e73df;
            color: white;
        }

        .header-danger {
            background: #e74a3b;
            color: white;
        }

        .header-success {
            background: #1cc88a;
            color: white;
        }

        .header-warning {
            background: #f6c23e;
            color: #333;
        }

        .header-info {
            background: #36b9cc;
            color: white;
        }

        .header-secondary {
            background: #858796;
            color: white;
        }

        .header-purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        /* ── Detail Row ── */
        .detail-row {
            display: flex;
            border-bottom: 1px solid #f0f0f0;
            padding: 11px 0;
            align-items: flex-start;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            width: 200px;
            min-width: 200px;
            font-size: 12px;
            font-weight: 700;
            color: #858796;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding-top: 2px;
        }

        .detail-value {
            flex: 1;
            font-size: 14px;
            color: #2d3748;
            font-weight: 500;
        }

        /* ── Financial Breakdown ── */
        .financial-box {
            background: linear-gradient(135deg, #f8f9fc 0%, #eef0f8 100%);
            border-radius: 10px;
            padding: 20px 25px;
        }

        .financial-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px dashed #dee2e6;
            font-size: 14px;
        }

        .financial-line:last-child {
            border-bottom: none;
        }

        .financial-line .fl-label {
            color: #6c757d;
            font-weight: 600;
        }

        .financial-line .fl-value {
            font-weight: 700;
        }

        .financial-total-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0 2px;
            border-top: 2px solid #e74a3b;
            margin-top: 5px;
        }

        .financial-total-line .fl-label {
            font-size: 15px;
            font-weight: 800;
            color: #333;
        }

        .financial-total-line .fl-value {
            font-size: 22px;
            font-weight: 900;
            color: #e74a3b;
        }

        /* ── OCAS / Journal Box ── */
        .journal-table th {
            background: #2c3e50;
            color: white;
            font-size: 12px;
        }

        .journal-table td {
            font-size: 13px;
            vertical-align: middle;
        }

        .journal-table .debit {
            color: #e74a3b;
            font-weight: 700;
        }

        .journal-table .kredit {
            color: #1cc88a;
            font-weight: 700;
        }

        .journal-table .balanced-row {
            background: #f8fff8;
        }

        .journal-table tfoot td {
            background: #f8f9fc;
            font-weight: 800;
            font-size: 13px;
        }

        /* ── Timeline / Audit Trail ── */
        .timeline {
            padding: 10px 0;
        }

        .timeline-item {
            display: flex;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            position: relative;
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-dot {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
        }

        .dot-created {
            background: #4e73df;
        }

        .dot-approved {
            background: #1cc88a;
        }

        .dot-paid {
            background: #36b9cc;
        }

        .dot-rejected {
            background: #e74a3b;
        }

        .dot-updated {
            background: #f6c23e;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-content .t-title {
            font-weight: 700;
            font-size: 14px;
            color: #333;
        }

        .timeline-content .t-meta {
            font-size: 12px;
            color: #858796;
            margin-top: 2px;
        }

        /* ── Action Buttons ── */
        .action-section {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .btn-action {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }

        .btn-edit {
            background: #1cc88a;
            color: white;
        }

        .btn-approve {
            background: #4e73df;
            color: white;
        }

        .btn-reject {
            background: #e74a3b;
            color: white;
        }

        .btn-delete {
            background: #858796;
            color: white;
        }

        .btn-back {
            background: #f8f9fc;
            color: #333;
            border: 2px solid #dee2e6;
        }

        .btn-print {
            background: #36b9cc;
            color: white;
        }

        .btn-export {
            background: #f6c23e;
            color: #333;
        }

        /* ── Tagihan Link Card ── */
        .linked-tagihan {
            background: linear-gradient(135deg, #667eea15, #764ba215);
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 18px 22px;
        }

        /* ── Watermark for status ── */
        .status-watermark {
            position: absolute;
            top: 50%;
            right: 30px;
            transform: translateY(-50%) rotate(-15deg);
            font-size: 56px;
            font-weight: 900;
            text-transform: uppercase;
            opacity: 0.12;
            letter-spacing: 5px;
            pointer-events: none;
            z-index: 1;
        }

        .wm-approved {
            color: #1cc88a;
        }

        .wm-paid {
            color: #4e73df;
        }

        .wm-pending {
            color: #f6c23e;
        }

        .wm-rejected {
            color: #e74a3b;
        }

        /* ── Print ── */
        @media print {
            .no-print {
                display: none !important;
            }

            .sidebar,
            #topbar,
            .topbar {
                display: none !important;
            }

            #content-wrapper {
                margin-left: 0 !important;
            }

            .detail-hero {
                box-shadow: none;
            }
        }

        /* ── Tooltip popover ── */
        .info-tooltip {
            display: inline-block;
            width: 18px;
            height: 18px;
            background: #dee2e6;
            border-radius: 50%;
            text-align: center;
            line-height: 18px;
            font-size: 11px;
            color: #495057;
            cursor: help;
            margin-left: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .detail-label {
                width: 140px;
                min-width: 140px;
                font-size: 11px;
            }

            .detail-hero .hero-total {
                font-size: 26px;
            }

            .financial-total-line .fl-value {
                font-size: 18px;
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
                    <?php
                    /* ───────────────────────────────────────────────
                     * Helper Variables
                     * ─────────────────────────────────────────────── */
                    $p = $pengeluaran;
                    $tipe = substr($p->reff_no, 0, 1);
                    $is_vendor = ($tipe === 'V');
                    $status_text = $p->status ?? 'Tidak Ada';
                    $status_lower = strtolower($status_text);

                    // Status badge
                    $status_badges = [
                        'approved' => '<span class="badge-status-approved"><i class="fas fa-check-circle mr-1"></i>Approved</span>',
                        'paid' => '<span class="badge-status-paid"><i class="fas fa-money-bill-wave mr-1"></i>Paid</span>',
                        'pending' => '<span class="badge-status-pending"><i class="fas fa-clock mr-1"></i>Pending</span>',
                        'rejected' => '<span class="badge-status-rejected"><i class="fas fa-times-circle mr-1"></i>Rejected</span>',
                    ];
                    $status_badge = $status_badges[$status_lower]
                        ?? '<span class="badge badge-secondary px-3 py-2">' . htmlspecialchars($status_text) . '</span>';

                    // Tipe badge
                    $tipe_badge = $is_vendor
                        ? '<span class="badge-vendor"><i class="fas fa-truck mr-1"></i>VENDOR</span>'
                        : '<span class="badge-non-vendor"><i class="fas fa-receipt mr-1"></i>NON-VENDOR</span>';

                    // Financial
                    $nominal = (float) $p->nominal;
                    $ppn = (float) $p->ppn;
                    $pph = (float) $p->pph;
                    $total_biaya = $nominal + $ppn;
                    $total_bayar = $total_biaya - $pph;

                    // PPH Rate auto-detect
                    $pph_rate = ($nominal > 0) ? ($pph / $nominal * 100) : 0;
                    $pph_type = '';
                    if ($pph_rate >= 1.5 && $pph_rate <= 2.5)
                        $pph_type = 'PPH 23';
                    elseif ($pph_rate >= 0.3 && $pph_rate <= 0.7)
                        $pph_type = 'PPH 4(2)';
                    elseif ($pph > 0)
                        $pph_type = 'PPH Custom';
                    ?>

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 no-print">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-money-bill-wave text-danger"></i> Detail Pengeluaran
                        </h1>
                        <div class="d-flex gap-2 flex-wrap" style="gap:8px;">
                            <a href="<?= base_url('pengeluaran') ?>" class="btn-action btn-back">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button onclick="window.print()" class="btn-action btn-print">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <?php if ($status_lower === 'approved' || $status_lower === 'paid'): ?>
                                <a href="<?= base_url('pengeluaran/export_pdf?detail=' . $p->id) ?>" target="_blank"
                                    class="btn-action btn-export" style="background:#e74a3b;color:white;">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </a>
                            <?php endif ?>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- ═══════════════════════════════════
                     HERO SECTION
                ═══════════════════════════════════ -->
                    <div class="detail-hero">
                        <!-- Status Watermark -->
                        <div class="status-watermark wm-<?= $status_lower ?>">
                            <?= $status_lower === 'approved' ? 'APPROVED' : ($status_lower === 'paid' ? 'PAID' : ($status_lower === 'pending' ? 'PENDING' : ($status_lower === 'rejected' ? 'REJECTED' : ''))) ?>
                        </div>

                        <div class="row align-items-center position-relative" style="z-index:2;">
                            <div class="col-md-7">
                                <div class="reff-badge">
                                    <?= htmlspecialchars($p->reff_no) ?>
                                </div>
                                <div style="margin: 8px 0;">
                                    <?= $tipe_badge ?>
                                    &nbsp;
                                    <?= $status_badge ?>
                                </div>
                                <div class="hero-total">
                                    Rp <?= number_format($total_bayar, 0, ',', '.') ?>
                                </div>
                                <div class="hero-meta">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    <?= date('d F Y', strtotime($p->tanggal)) ?>
                                    &nbsp;&bull;&nbsp;
                                    <i class="fas fa-ship mr-1"></i>
                                    Bulan: <?= htmlspecialchars($p->bulan_shipment ?: '-') ?>
                                    <?php if (!empty($p->postingan_biaya)): ?>
                                        &nbsp;&bull;&nbsp;
                                        <i class="fas fa-tag mr-1"></i>
                                        Biaya: <?= htmlspecialchars($p->postingan_biaya) ?>
                                    <?php endif ?>
                                </div>
                            </div>
                            <div class="col-md-5 text-md-right mt-3 mt-md-0">
                                <div style="font-size:13px; opacity:0.85;">
                                    <div><i class="fas fa-file-invoice mr-1"></i> Total Biaya: <strong>Rp
                                            <?= number_format($total_biaya, 0, ',', '.') ?></strong></div>
                                    <div style="margin-top:5px;">
                                        <?php if ($ppn > 0): ?>
                                            <span
                                                style="background:rgba(255,255,255,0.2);border-radius:8px;padding:3px 10px;margin-right:5px;font-size:12px;">
                                                PPN +Rp <?= number_format($ppn, 0, ',', '.') ?>
                                            </span>
                                        <?php endif ?>
                                        <?php if ($pph > 0): ?>
                                            <span
                                                style="background:rgba(255,0,0,0.25);border-radius:8px;padding:3px 10px;font-size:12px;">
                                                <?= $pph_type ?> -Rp <?= number_format($pph, 0, ',', '.') ?>
                                            </span>
                                        <?php endif ?>
                                    </div>
                                </div>
                                <!-- Action Buttons inside Hero -->
                                <div class="action-section justify-content-md-end mt-3 no-print">
                                    <?php if ($status_lower === 'pending'): ?>
                                        <a href="<?= base_url('pengeluaran/approve/' . $p->id) ?>"
                                            onclick="return confirm('Approve & posting journal untuk <?= htmlspecialchars($p->reff_no) ?>?')"
                                            class="btn-action btn-approve">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                        <a href="<?= base_url('pengeluaran/reject/' . $p->id) ?>"
                                            onclick="return confirm('Reject <?= htmlspecialchars($p->reff_no) ?>?')"
                                            class="btn-action btn-reject">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                        <a href="<?= base_url('pengeluaran/ubah/' . $p->id) ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    <?php elseif ($status_lower === 'rejected'): ?>
                                        <a href="<?= base_url('pengeluaran/hapus/' . $p->id) ?>"
                                            onclick="return confirm('Hapus pengeluaran ini?')"
                                            class="btn-action btn-delete">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= base_url('pengeluaran/ubah/' . $p->id) ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="<?= base_url('pengeluaran/hapus/' . $p->id) ?>"
                                            onclick="return confirmDelete('<?= htmlspecialchars($p->reff_no) ?>', <?= $p->tagihan_id ? 'true' : 'false' ?>)"
                                            class="btn-action btn-delete">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══════════════════════════════════
                     MAIN CONTENT GRID
                ═══════════════════════════════════ -->
                    <div class="row">

                        <!-- LEFT COLUMN -->
                        <div class="col-lg-7">

                            <!-- ── Informasi Dasar ── -->
                            <div class="info-card">
                                <div class="card-header-custom header-danger">
                                    <i class="fas fa-info-circle"></i> Informasi Dasar
                                </div>
                                <div class="card-body-custom">
                                    <div class="detail-row">
                                        <span class="detail-label">Reff No</span>
                                        <span class="detail-value">
                                            <strong class="text-danger"
                                                style="font-size:16px;"><?= htmlspecialchars($p->reff_no) ?></strong>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Tipe</span>
                                        <span class="detail-value"><?= $tipe_badge ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Status</span>
                                        <span class="detail-value"><?= $status_badge ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Tanggal</span>
                                        <span class="detail-value">
                                            <i class="fas fa-calendar text-danger mr-1"></i>
                                            <?= date('l, d F Y', strtotime($p->tanggal)) ?>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Bulan Shipment</span>
                                        <span class="detail-value">
                                            <i class="fas fa-ship text-primary mr-1"></i>
                                            <?= htmlspecialchars($p->bulan_shipment ?: '-') ?>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Postingan Biaya</span>
                                        <span class="detail-value">
                                            <span class="badge badge-light border px-3 py-2" style="font-size:13px;">
                                                <i class="fas fa-tag text-warning mr-1"></i>
                                                <?= htmlspecialchars($p->postingan_biaya) ?>
                                            </span>
                                        </span>
                                    </div>
                                    <?php if (!empty($p->deskripsi_rincian)): ?>
                                        <div class="detail-row">
                                            <span class="detail-label">Deskripsi</span>
                                            <span class="detail-value text-muted" style="font-style:italic;">
                                                "<?= htmlspecialchars($p->deskripsi_rincian) ?>"
                                            </span>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>

                            <!-- ── Informasi Vendor ── -->
                            <?php if ($is_vendor || !empty($p->nama_vendor)): ?>
                                <div class="info-card">
                                    <div class="card-header-custom header-purple">
                                        <i class="fas fa-building"></i> Informasi Vendor
                                    </div>
                                    <div class="card-body-custom">
                                        <?php if (!empty($p->nama_vendor)): ?>
                                            <div class="detail-row">
                                                <span class="detail-label">Nama Vendor</span>
                                                <span class="detail-value">
                                                    <strong><?= htmlspecialchars($p->nama_vendor) ?></strong>
                                                </span>
                                            </div>
                                        <?php endif ?>
                                        <?php if (!empty($p->vendor_id)): ?>
                                            <div class="detail-row">
                                                <span class="detail-label">Kode Vendor</span>
                                                <span class="detail-value">
                                                    <code><?= htmlspecialchars($p->vendor_id) ?></code>
                                                </span>
                                            </div>
                                        <?php endif ?>
                                        <?php if (!empty($p->no_invoice_vendor)): ?>
                                            <div class="detail-row">
                                                <span class="detail-label">No Invoice</span>
                                                <span class="detail-value">
                                                    <i class="fas fa-file-invoice text-primary mr-1"></i>
                                                    <strong><?= htmlspecialchars($p->no_invoice_vendor) ?></strong>
                                                </span>
                                            </div>
                                        <?php endif ?>

                                        <!-- Tagihan Terkait -->
                                        <?php if (!empty($p->tagihan_id) && !empty($tagihan_terkait)): ?>
                                            <div class="detail-row">
                                                <span class="detail-label">Tagihan Terkait</span>
                                                <span class="detail-value">
                                                    <div class="linked-tagihan">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <i class="fas fa-link text-primary mr-1"></i>
                                                                <strong><?= htmlspecialchars($tagihan_terkait->no_invoice ?? '-') ?></strong>
                                                                <span class="badge badge-success ml-2 px-2 py-1"
                                                                    style="font-size:10px;">Linked</span>
                                                            </div>
                                                            <?php if (!empty($tagihan_terkait->nominal)): ?>
                                                                <div class="text-muted small mt-1">
                                                                    Total Tagihan: Rp
                                                                    <?= number_format($tagihan_terkait->nominal, 0, ',', '.') ?>
                                                                </div>
                                                            <?php endif ?>
                                                        </div>
                                                        <?php if (!empty($tagihan_terkait->invoice_date)): ?>
                                                            <div class="text-muted small mt-1">
                                                                <i class="fas fa-calendar mr-1"></i>
                                                                Invoice Date:
                                                                <?= date('d/m/Y', strtotime($tagihan_terkait->invoice_date)) ?>
                                                            </div>
                                                        <?php endif ?>
                                                    </div>
                                                </span>
                                            </div>
                                        <?php elseif (!empty($p->tagihan_id)): ?>
                                            <div class="detail-row">
                                                <span class="detail-label">Tagihan ID</span>
                                                <span class="detail-value">
                                                    <code>#<?= $p->tagihan_id ?></code>
                                                    <span class="badge badge-secondary ml-1"
                                                        style="font-size:10px;">Linked</span>
                                                </span>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                </div>
                            <?php endif ?>

                            <!-- ── Informasi Bank/Kas ── -->
                            <div class="info-card">
                                <div class="card-header-custom header-success">
                                    <i class="fas fa-university"></i> Akun Pembayaran
                                </div>
                                <div class="card-body-custom">
                                    <?php
                                    // Try to load bank account info
                                    $akun_bank_info = null;
                                    if (!empty($p->akun_bank_id)) {
                                        $this->load->model('M_akunbiaya');
                                        $akun_bank_info = $this->M_akunbiaya->get_by_id($p->akun_bank_id);
                                    }
                                    ?>
                                    <?php if ($akun_bank_info): ?>
                                        <div class="detail-row">
                                            <span class="detail-label">Bank / Kas</span>
                                            <span class="detail-value">
                                                <i class="fas fa-landmark text-success mr-1"></i>
                                                <strong><?= htmlspecialchars($akun_bank_info->nama) ?></strong>
                                            </span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Kode Akun</span>
                                            <span class="detail-value">
                                                <code><?= htmlspecialchars($akun_bank_info->kode_perkiraan ?? $p->akun_bank_id) ?></code>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <div class="detail-row">
                                            <span class="detail-label">Akun Bank ID</span>
                                            <span
                                                class="detail-value"><code><?= htmlspecialchars($p->akun_bank_id ?? '-') ?></code></span>
                                        </div>
                                    <?php endif ?>
                                    <div class="detail-row">
                                        <span
                                            class="detail-label"><?= $is_vendor ? 'Jumlah Dibayar' : 'Total Pengeluaran' ?></span>
                                        <span class="detail-value">
                                            <strong class="text-success" style="font-size:16px;">
                                                Rp <?= number_format($total_bayar, 0, ',', '.') ?>
                                            </strong>
                                            <small class="text-muted ml-2">
                                                <?= $is_vendor ? '(yang diterima vendor)' : '(jumlah yang dibayarkan)' ?>
                                            </small>
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /LEFT COLUMN -->

                        <!-- RIGHT COLUMN -->
                        <div class="col-lg-5">

                            <!-- ── Rincian Keuangan ── -->
                            <div class="info-card">
                                <div class="card-header-custom header-warning">
                                    <i class="fas fa-calculator"></i> Rincian Keuangan
                                </div>
                                <div class="card-body-custom">
                                    <div class="financial-box">
                                        <!-- Nominal -->
                                        <div class="financial-line">
                                            <span class="fl-label">
                                                <i class="fas fa-tag mr-1 text-muted"></i> Nominal
                                            </span>
                                            <span class="fl-value">Rp <?= number_format($nominal, 0, ',', '.') ?></span>
                                        </div>

                                        <!-- PPN -->
                                        <?php if ($ppn > 0): ?>
                                            <div class="financial-line">
                                                <span class="fl-label">
                                                    <i class="fas fa-plus mr-1 text-success"></i> PPN
                                                    <?php
                                                    $ppn_rate_pct = ($nominal > 0) ? round($ppn / $nominal * 100, 2) : 0;
                                                    if ($ppn_rate_pct > 0): ?>
                                                        <small class="text-muted">(<?= $ppn_rate_pct ?>%)</small>
                                                    <?php endif ?>
                                                </span>
                                                <span class="fl-value text-success">+Rp
                                                    <?= number_format($ppn, 0, ',', '.') ?></span>
                                            </div>
                                        <?php endif ?>

                                        <!-- Divider total biaya -->
                                        <?php if ($ppn > 0): ?>
                                            <div class="financial-line"
                                                style="background:#e8f4fd; border-radius:6px; padding:8px 10px; border:none; margin: 4px 0;">
                                                <span class="fl-label" style="color:#2c7be5;">
                                                    <i class="fas fa-equals mr-1"></i> Total Biaya
                                                </span>
                                                <span class="fl-value" style="color:#2c7be5;">Rp
                                                    <?= number_format($total_biaya, 0, ',', '.') ?></span>
                                            </div>
                                        <?php endif ?>

                                        <!-- PPH -->
                                        <?php if ($pph > 0): ?>
                                            <div class="financial-line">
                                                <span class="fl-label">
                                                    <i class="fas fa-minus mr-1 text-danger"></i>
                                                    <?= $pph_type ?: 'PPH' ?>
                                                    <?php if ($pph_rate > 0): ?>
                                                        <small class="text-muted">(<?= round($pph_rate, 2) ?>%)</small>
                                                    <?php endif ?>
                                                    <span class="info-tooltip" data-bs-toggle="tooltip"
                                                        title="PPH ditahan dan dicatat ke OCAS (hutang ke negara)">?</span>
                                                </span>
                                                <span class="fl-value text-danger">-Rp
                                                    <?= number_format($pph, 0, ',', '.') ?></span>
                                            </div>
                                        <?php endif ?>

                                        <!-- Total Bayar -->
                                        <div class="financial-total-line">
                                            <span class="fl-label">TOTAL BAYAR</span>
                                            <span class="fl-value">Rp
                                                <?= number_format($total_bayar, 0, ',', '.') ?></span>
                                        </div>
                                    </div>

                                    <?php if ($pph > 0): ?>
                                        <div class="alert alert-warning mt-3 mb-0 py-2 px-3" style="font-size:12px;">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            <strong><?= $pph_type ?>:</strong>
                                            Rp <?= number_format($pph, 0, ',', '.') ?> telah dipotong &amp; dicatat ke OCAS
                                            (harus disetor ke negara).
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>

                            <!-- ── Journal Entry ── -->
                            <?php
                            // Load related journal/transactions
                            $journals = [];
                            if (!empty($p->tagihan_id)) {
                                $journals = $this->M_transaksi_keuangan->get_by_referensi('Pembayaran_Tagihan', $p->tagihan_id);
                            }
                            if (empty($journals)) {
                                $journals = $this->M_transaksi_keuangan->get_by_referensi('Pengeluaran', $p->id);
                            }
                            ?>
                            <?php if (!empty($journals)): ?>
                                <div class="info-card">
                                    <div class="card-header-custom header-info">
                                        <i class="fas fa-book"></i> Journal Entry
                                    </div>
                                    <div class="card-body-custom p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0 journal-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width:35%;">Akun</th>
                                                        <th class="text-right">Debit</th>
                                                        <th class="text-right">Kredit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $jTotal_debit = 0;
                                                    $jTotal_kredit = 0;
                                                    $j_no_transaksi = '';
                                                    foreach ($journals as $j):
                                                        $jTotal_debit += (float) $j->debit;
                                                        $jTotal_kredit += (float) $j->kredit;
                                                        if (empty($j_no_transaksi) && !empty($j->no_transaksi))
                                                            $j_no_transaksi = $j->no_transaksi;
                                                        ?>
                                                        <tr class="<?= $j->debit > 0 ? '' : 'balanced-row' ?>">
                                                            <td>
                                                                <?php
                                                                $jAkun = $this->M_akunbiaya->get_by_id($j->akun_id);
                                                                ?>
                                                                <?php if ($jAkun): ?>
                                                                    <span class="text-xs font-weight-bold"
                                                                        style="font-size:11px;color:#858796;">
                                                                        <?= htmlspecialchars($jAkun->kode_perkiraan ?? '') ?>
                                                                    </span><br>
                                                                    <span
                                                                        style="font-size:12px;"><?= htmlspecialchars($jAkun->nama ?? '-') ?></span>
                                                                <?php else: ?>
                                                                    <span style="font-size:12px;">Akun #<?= $j->akun_id ?></span>
                                                                <?php endif ?>
                                                            </td>
                                                            <td class="text-right debit" style="font-size:12px;">
                                                                <?= $j->debit > 0 ? 'Rp ' . number_format($j->debit, 0, ',', '.') : '-' ?>
                                                            </td>
                                                            <td class="text-right kredit" style="font-size:12px;">
                                                                <?= $j->kredit > 0 ? 'Rp ' . number_format($j->kredit, 0, ',', '.') : '-' ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td class="text-right text-muted" style="font-size:11px;">TOTAL</td>
                                                        <td class="text-right debit">Rp
                                                            <?= number_format($jTotal_debit, 0, ',', '.') ?>
                                                        </td>
                                                        <td class="text-right kredit">Rp
                                                            <?= number_format($jTotal_kredit, 0, ',', '.') ?>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <?php if (!empty($j_no_transaksi)): ?>
                                            <div class="px-3 pb-2 pt-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-hashtag mr-1"></i>
                                                    No Transaksi: <code><?= htmlspecialchars($j_no_transaksi) ?></code>
                                                    &nbsp;
                                                    <?php if (abs($jTotal_debit - $jTotal_kredit) < 0.01): ?>
                                                        <span class="text-success font-weight-bold">
                                                            <i class="fas fa-check-circle"></i> Balanced
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-danger font-weight-bold">
                                                            <i class="fas fa-exclamation-triangle"></i> NOT Balanced!
                                                        </span>
                                                    <?php endif ?>
                                                </small>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                </div>
                            <?php elseif ($status_lower === 'pending'): ?>
                                <div class="info-card">
                                    <div class="card-header-custom header-secondary">
                                        <i class="fas fa-book"></i> Journal Entry
                                    </div>
                                    <div class="card-body-custom">
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-clock fa-2x mb-2 d-block" style="color:#f6c23e;"></i>
                                            <strong>Journal Belum Diposting</strong>
                                            <p class="mb-0 mt-1" style="font-size:12px;">
                                                Status <strong>Pending</strong> — Journal akan diposting
                                                setelah di-<strong>Approve</strong>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif ?>

                            <!-- ── Audit Trail ── -->
                            <div class="info-card">
                                <div class="card-header-custom header-secondary">
                                    <i class="fas fa-history"></i> Audit Trail
                                </div>
                                <div class="card-body-custom">
                                    <div class="timeline">
                                        <!-- Created -->
                                        <div class="timeline-item">
                                            <div class="timeline-dot dot-created">
                                                <i class="fas fa-plus"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="t-title">Dibuat</div>
                                                <div class="t-meta">
                                                    <?= !empty($p->created_at) ? date('d/m/Y H:i', strtotime($p->created_at)) : '-' ?>
                                                    <?php if (!empty($p->created_by)): ?>
                                                        &bull; oleh <strong><?= htmlspecialchars($p->created_by) ?></strong>
                                                    <?php endif ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Updated (if exists and different from created) -->
                                        <?php if (!empty($p->updated_at) && $p->updated_at !== $p->created_at): ?>
                                            <div class="timeline-item">
                                                <div class="timeline-dot dot-updated">
                                                    <i class="fas fa-edit"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="t-title">Diperbarui</div>
                                                    <div class="t-meta">
                                                        <?= date('d/m/Y H:i', strtotime($p->updated_at)) ?>
                                                        <?php if (!empty($p->updated_by)): ?>
                                                            &bull; oleh <strong><?= htmlspecialchars($p->updated_by) ?></strong>
                                                        <?php endif ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif ?>

                                        <!-- Status-based trail -->
                                        <?php if ($status_lower === 'approved'): ?>
                                            <div class="timeline-item">
                                                <div class="timeline-dot dot-approved">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="t-title">Approved — Journal Diposting</div>
                                                    <div class="t-meta">
                                                        <?= !empty($p->updated_at) ? date('d/m/Y H:i', strtotime($p->updated_at)) : '-' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif ($status_lower === 'paid'): ?>
                                            <div class="timeline-item">
                                                <div class="timeline-dot dot-paid">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="t-title">Paid — Pembayaran Selesai</div>
                                                    <div class="t-meta">
                                                        <?= !empty($p->updated_at) ? date('d/m/Y H:i', strtotime($p->updated_at)) : '-' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif ($status_lower === 'rejected'): ?>
                                            <div class="timeline-item">
                                                <div class="timeline-dot dot-rejected">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="t-title">Ditolak / Rejected</div>
                                                    <div class="t-meta">
                                                        <?= !empty($p->updated_at) ? date('d/m/Y H:i', strtotime($p->updated_at)) : '-' ?>
                                                        <?php if (!empty($p->updated_by)): ?>
                                                            &bull; oleh <strong><?= htmlspecialchars($p->updated_by) ?></strong>
                                                        <?php endif ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif ($status_lower === 'pending'): ?>
                                            <div class="timeline-item">
                                                <div class="timeline-dot dot-updated" style="background:#f6c23e;">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="t-title">Menunggu Persetujuan</div>
                                                    <div class="t-meta text-warning">Pending — Belum di-approve</div>
                                                </div>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /RIGHT COLUMN -->

                    </div><!-- /.row -->

                    <!-- ═══════════════════════════════════
                     OCAS DETAIL (if PPH exists)
                ═══════════════════════════════════ -->
                    <?php if ($pph > 0): ?>
                        <div class="info-card mb-4">
                            <div class="card-header-custom"
                                style="background:linear-gradient(135deg,#2c3e50 0%,#34495e 100%);color:white;">
                                <i class="fas fa-balance-scale"></i> Detail OCAS (Other Current Account Summary)
                            </div>
                            <div class="card-body-custom">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center p-3"
                                            style="background:#fff8e1;border-radius:10px;border:2px solid #f6c23e;">
                                            <i class="fas fa-hand-holding-usd fa-2x text-warning mb-2"></i>
                                            <div class="font-weight-bold">Total Biaya Perusahaan</div>
                                            <div style="font-size:20px;font-weight:900;color:#333;">Rp
                                                <?= number_format($total_biaya, 0, ',', '.') ?>
                                            </div>
                                            <small class="text-muted">Nominal + PPN = Total Biaya</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center my-auto">
                                        <div class="p-3">
                                            <i class="fas fa-equals fa-2x text-secondary mb-2"></i>
                                            <div class="text-muted small">DEBIT Biaya: <strong>Rp
                                                    <?= number_format($total_biaya, 0, ',', '.') ?></strong></div>
                                            <div class="text-muted small mt-1">KREDIT Bank: <strong>Rp
                                                    <?= number_format($total_bayar, 0, ',', '.') ?></strong></div>
                                            <div class="text-danger small mt-1">KREDIT OCAS: <strong>Rp
                                                    <?= number_format($pph, 0, ',', '.') ?></strong></div>
                                            <hr class="my-2">
                                            <div
                                                class="small font-weight-bold <?= abs($total_biaya - $total_bayar - $pph) < 0.01 ? 'text-success' : 'text-danger' ?>">
                                                <?= abs($total_biaya - $total_bayar - $pph) < 0.01 ? '✅ Journal Balanced' : '❌ Tidak Balance!' ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3"
                                            style="background:#fdecea;border-radius:10px;border:2px solid #e74a3b;">
                                            <div class="text-center mb-2">
                                                <i class="fas fa-university fa-2x text-danger"></i>
                                            </div>
                                            <div class="text-center font-weight-bold"><?= $pph_type ?: 'PPH' ?> Ditahan
                                            </div>
                                            <div class="text-center" style="font-size:20px;font-weight:900;color:#e74a3b;">
                                                Rp <?= number_format($pph, 0, ',', '.') ?>
                                            </div>
                                            <div class="text-center small text-muted">
                                                Harus disetor ke negara<br>Rate: <?= round($pph_rate, 2) ?>%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                </div><!-- /.container-fluid -->
            </div><!-- /#content -->
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {
            // Tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        function confirmDelete(reffNo, hasTagihan) {
            let msg = 'Yakin hapus pengeluaran Reff: ' + reffNo + '?';
            if (hasTagihan) {
                msg += '\n\n⚠️ Terhubung dengan tagihan. Status tagihan akan kembali ke "Waiting Payment".';
            }
            return confirm(msg);
        }
    </script>
</body>

</html>