<!-- lihat.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        .stats-card {
            border-left: 4px solid;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .15) !important;
        }

        .badge-draft {
            background: #858796;
            color: #fff;
        }

        .badge-sent {
            background: #4e73df;
            color: #fff;
        }

        .badge-unsent {
            background: #f6c23e;
            color: #333;
        }

        .badge-paid {
            background: #1cc88a;
            color: #fff;
        }

        .badge-cancelled {
            background: #e74a3b;
            color: #fff;
        }

        .badge-overdue {
            background: #e74a3b;
            color: #fff;
        }

        .bg-orange {
            background-color: #fd7e14 !important;
        }

        tr.row-overdue td {
            background-color: #fff5f5 !important;
        }

        tr.row-overdue:hover td {
            background-color: #ffe8e8 !important;
        }

        /* Tab styling */
        #invoiceTabs .nav-link {
            color: #6c757d;
            border-radius: 0;
        }

        #invoiceTabs .nav-link.active {
            color: #1cc88a;
            border-bottom-color: #fff;
            font-weight: 600;
        }

        #invoiceTabs .nav-link:hover:not(.active) {
            color: #333;
            background: #f8f9fa;
        }

        /* Bulk action bar */
        #bulkActionBar {
            position: sticky;
            top: 0;
            z-index: 10;
            border-radius: 0;
            background: #e8f8f2 !important;
            border-bottom: 2px solid #1cc88a !important;
            animation: slideDown .15s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Checkbox column */
        th.col-check,
        td.col-check {
            width: 40px;
            text-align: center;
        }

        /* Row selected highlight */
        tr.row-selected td {
            background-color: #f0fff8 !important;
        }

        tr.row-selected.row-overdue td {
            background-color: #ffe8e8 !important;
        }

        /* Select2 custom styling */
        .select2-container--bootstrap-5 .select2-selection--multiple {
            min-height: 31px;
            font-size: .84rem;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
            padding: 0 4px;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #1cc88a;
            border-color: #19b07d;
            color: #fff;
            font-size: .72rem;
            padding: 0 6px;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255, 255, 255, .7);
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #fff;
        }

        .select2-container {
            width: 100% !important;
        }

        /* Badge active filter */
        .active-filter-badge {
            font-size: .72rem;
            padding: 3px 8px;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <!-- FLASH -->
                    <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                        <?php if ($this->session->flashdata($key)): ?>
                            <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                                <?= $this->session->flashdata($key) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif ?>
                    <?php endforeach ?>

                    <!-- PAGE HEADER -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-file-invoice text-success me-2"></i><?= $title ?>
                            </h2>
                            <small class="text-muted">Kelola invoice penagihan ke customer</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('invoice_tsc/tambah') ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-1"></i> Buat Invoice
                            </a>
                            <a href="<?= base_url('invoice_tsc/export_excel?' . http_build_query($filters)) ?>"
                                class="btn btn-primary btn-sm">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </a>
                        </div>
                    </div>

                    <!-- SUMMARY CARDS -->
                    <div class="row g-3 mb-3">
                        <?php
                        $stat_cards = [
                            [
                                'label' => 'Total Invoice',
                                'val' => number_format($summary->total_invoice ?? 0),
                                'color' => '#4e73df',
                                'icon' => 'fas fa-file-invoice',
                                'link' => base_url('invoice_tsc'),
                                'link_label' => 'Semua'
                            ],
                            [
                                'label' => 'Total Amount',
                                'val' => 'Rp ' . number_format($summary->total_amount ?? 0, 0, ',', '.'),
                                'color' => '#1cc88a',
                                'icon' => 'fas fa-money-bill-wave',
                                'link' => null,
                                'link_label' => null
                            ],
                            [
                                'label' => 'Outstanding',
                                'val' => 'Rp ' . number_format($summary->outstanding_amount ?? 0, 0, ',', '.'),
                                'color' => '#f6c23e',
                                'icon' => 'fas fa-clock',
                                'link' => base_url('invoice_tsc?status=sent'),
                                'link_label' => 'Lihat'
                            ],
                            [
                                'label' => 'Overdue',
                                'val' => number_format($count_overdue ?? 0),
                                'color' => '#e74a3b',
                                'icon' => 'fas fa-exclamation-triangle',
                                'link' => base_url('invoice_tsc?status=overdue'),
                                'link_label' => 'Filter Overdue'
                            ],
                        ];
                        foreach ($stat_cards as $sc): ?>
                            <div class="col-xl-3 col-md-6">
                                <div class="card stats-card shadow-sm h-100" style="border-left-color:<?= $sc['color'] ?>">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="font-size:1.6rem;color:<?= $sc['color'] ?>;opacity:.4">
                                                <i class="<?= $sc['icon'] ?>"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold fs-5 lh-1"><?= $sc['val'] ?></div>
                                                <div class="text-muted"
                                                    style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">
                                                    <?= $sc['label'] ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($sc['link']): ?>
                                        <div class="card-footer bg-white border-top-0 py-2">
                                            <a href="<?= $sc['link'] ?>" class="small text-decoration-none"
                                                style="color:<?= $sc['color'] ?>">
                                                <i class="fas fa-filter me-1"></i><?= $sc['link_label'] ?>
                                            </a>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <!-- OVERDUE ALERT BANNER -->
                    <?php if (($count_overdue ?? 0) > 0 && ($filters['status'] ?? '') !== 'overdue'): ?>
                        <div
                            class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-3 mb-3 py-2">
                            <i class="fas fa-exclamation-circle fa-lg flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <strong><?= $count_overdue ?> invoice overdue</strong> — sudah lewat jatuh tempo dan belum
                                dibayar.
                            </div>
                            <a href="<?= base_url('invoice_tsc?status=overdue') ?>"
                                class="btn btn-sm btn-danger flex-shrink-0">
                                <i class="fas fa-eye me-1"></i> Lihat Semua Overdue
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <!-- FILTER CARD -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-header py-2" style="border-left:4px solid #1cc88a;">
                            <h6 class="mb-0 fw-bold text-success">
                                <i class="fas fa-filter me-1"></i> Filter &amp; Pencarian
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <form method="get" action="<?= base_url('invoice_tsc') ?>" id="filterForm">
                                <div class="row g-2 align-items-end">

                                    <!-- ══ CUSTOMER MULTI-SELECT ══ -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold mb-1 small">
                                            Customer
                                            <?php if (!empty($filters['customer_id'])): ?>
                                                <span class="badge bg-success active-filter-badge ms-1">
                                                    <?= count((array) $filters['customer_id']) ?> dipilih
                                                </span>
                                            <?php endif ?>
                                        </label>
                                        <?php
                                        // Normalize: always array for view logic
                                        $selected_customers = [];
                                        if (!empty($filters['customer_id'])) {
                                            $selected_customers = is_array($filters['customer_id'])
                                                ? $filters['customer_id']
                                                : [$filters['customer_id']];
                                        }
                                        ?>
                                        <select name="customer_id[]" id="customerMultiSelect"
                                            class="form-select form-select-sm" multiple="multiple">
                                            <?php foreach ($customers as $cust): ?>
                                                <option value="<?= $cust->kode ?>" <?= in_array($cust->kode, $selected_customers) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cust->nama) ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                        <div class="mt-1 d-flex gap-1">
                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                style="font-size:.68rem;padding:1px 7px"
                                                onclick="$('#customerMultiSelect').val(null).trigger('change')">
                                                <i class="fas fa-times me-1"></i>Clear
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm"
                                                style="font-size:.68rem;padding:1px 7px"
                                                onclick="$('#customerMultiSelect').find('option').prop('selected', true).end().trigger('change')">
                                                <i class="fas fa-check-double me-1"></i>Semua
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Tgl Invoice -->
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold mb-1 small">Tgl Invoice — Dari</label>
                                        <input type="date" name="date_from" class="form-control form-control-sm"
                                            value="<?= $filters['date_from'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold mb-1 small">Tgl Invoice — Sampai</label>
                                        <input type="date" name="date_to" class="form-control form-control-sm"
                                            value="<?= $filters['date_to'] ?? '' ?>">
                                    </div>

                                    <!-- Periode Shipment -->
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold mb-1 small">Periode Shipment</label>
                                        <select name="periode_shipment" class="form-select form-select-sm">
                                            <option value="">Semua Periode</option>
                                            <?php foreach ($bulan_options as $bulan): ?>
                                                <option value="<?= $bulan ?>" <?= ($filters['periode_shipment'] ?? '') == $bulan ? 'selected' : '' ?>>
                                                    <?= $bulan ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-1">
                                        <label class="form-label fw-semibold mb-1 small">Status</label>
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="">Semua</option>
                                            <option value="draft" <?= ($filters['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Draft</option>
                                            <option value="sent" <?= ($filters['status'] ?? '') == 'sent' ? 'selected' : '' ?>>Sent</option>
                                            <option value="unsent" <?= ($filters['status'] ?? '') == 'unsent' ? 'selected' : '' ?>>Unsent</option>
                                            <option value="paid" <?= ($filters['status'] ?? '') == 'paid' ? 'selected' : '' ?>>Paid</option>
                                            <option value="cancelled" <?= ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            <option value="overdue" <?= ($filters['status'] ?? '') == 'overdue' ? 'selected' : '' ?>>⚠️ Overdue</option>
                                        </select>
                                    </div>

                                    <!-- Keyword -->
                                    <div class="col-md-1">
                                        <label class="form-label fw-semibold mb-1 small">Keyword</label>
                                        <input type="text" name="keyword" class="form-control form-control-sm"
                                            placeholder="No. Invoice / Faktur" value="<?= $filters['keyword'] ?? '' ?>">
                                    </div>
                                </div>

                                <!-- Active customer filter chips -->
                                <?php if (!empty($selected_customers)): ?>
                                    <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                                        <small class="text-muted me-1">Customer aktif:</small>
                                        <?php foreach ($customers as $cust): ?>
                                            <?php if (in_array($cust->kode, $selected_customers)): ?>
                                                <span class="badge bg-success" style="font-size:.7rem">
                                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($cust->nama) ?>
                                                </span>
                                            <?php endif ?>
                                        <?php endforeach ?>
                                    </div>
                                <?php endif ?>

                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-search me-1"></i> Cari
                                    </button>
                                    <a href="<?= base_url('invoice_tsc') ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-redo me-1"></i> Reset
                                    </a>
                                    <a href="<?= base_url('invoice_tsc?status=overdue') ?>"
                                        class="btn btn-sm <?= ($filters['status'] ?? '') == 'overdue' ? 'btn-danger' : 'btn-outline-danger' ?>">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Overdue
                                        <?php if (($count_overdue ?? 0) > 0): ?>
                                            <span class="badge bg-white text-danger ms-1"><?= $count_overdue ?></span>
                                        <?php endif ?>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ACTIVE FILTER INDICATOR -->
                    <?php if (($filters['status'] ?? '') === 'overdue'): ?>
                        <div class="alert alert-danger py-2 mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Menampilkan <strong><?= $pagination['total_records'] ?> invoice overdue</strong> — sudah
                                lewat jatuh tempo, belum dibayar.</span>
                            <a href="<?= base_url('invoice_tsc') ?>" class="btn btn-sm btn-outline-danger ms-auto">
                                <i class="fas fa-times me-1"></i> Hapus Filter
                            </a>
                        </div>
                    <?php endif ?>

                    <!-- ══════════════════════════════════════════
                         TAB NAVIGATION
                         ══════════════════════════════════════════ -->
                    <ul class="nav nav-tabs mb-0" id="invoiceTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="tab-invoice" data-bs-toggle="tab"
                                data-bs-target="#pane-invoice" type="button">
                                <i class="fas fa-file-invoice me-1 text-success"></i> Daftar Invoice
                                <span
                                    class="badge bg-secondary ms-1"><?= number_format($pagination['total_records']) ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="tab-aging" data-bs-toggle="tab"
                                data-bs-target="#pane-aging" type="button">
                                <i class="fas fa-hourglass-half me-1 text-danger"></i> Aging Report
                                <?php if (($aging_summary->bucket_30plus ?? 0) > 0): ?>
                                    <span class="badge bg-danger ms-1">!</span>
                                <?php endif ?>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content border border-top-0 rounded-bottom shadow-sm mb-4">

                        <!-- ══════════════════════════════════════════
                             TAB 1: DAFTAR INVOICE
                             ══════════════════════════════════════════ -->
                        <div class="tab-pane fade show active" id="pane-invoice" role="tabpanel">
                            <div class="card border-0 rounded-0 rounded-bottom mb-0">

                                <!-- Card Header -->
                                <div class="card-header py-2 d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fw-bold text-success">
                                        <i class="fas fa-list me-1"></i> Daftar Invoice TSC
                                    </h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (array_filter((array) $filters)): ?>
                                            <span class="badge bg-info">Filtered</span>
                                        <?php endif ?>
                                        <small class="text-muted text-nowrap">
                                            <?php
                                            $showing_from = $pagination['total_records'] > 0 ? $pagination['offset'] + 1 : 0;
                                            $showing_to = min($pagination['offset'] + $pagination['per_page'], $pagination['total_records']);
                                            ?>
                                            <?= number_format($showing_from) ?>–<?= number_format($showing_to) ?>
                                            dari <?= number_format($pagination['total_records']) ?> entri
                                        </small>
                                        <form method="get" id="perPageForm" class="d-flex align-items-center gap-1">
                                            <?php
                                            // Re-emit all filters including multi-select customer_id[]
                                            foreach ($filters as $key => $value):
                                                if ($key === 'customer_id')
                                                    continue; // handled separately below
                                                if (!empty($value)):
                                                    ?>
                                                    <input type="hidden" name="<?= $key ?>"
                                                        value="<?= htmlspecialchars($value) ?>">
                                                    <?php
                                                endif;
                                            endforeach;
                                            // Re-emit customer_id[] array
                                            if (!empty($selected_customers)):
                                                foreach ($selected_customers as $cid):
                                                    ?>
                                                    <input type="hidden" name="customer_id[]"
                                                        value="<?= htmlspecialchars($cid) ?>">
                                                    <?php
                                                endforeach;
                                            endif;
                                            ?>
                                            <span class="text-muted small">Show</span>
                                            <select name="per_page" class="form-select form-select-sm"
                                                style="width:65px" onchange="this.form.submit()">
                                                <?php foreach ([10, 25, 50, 100] as $n): ?>
                                                    <option value="<?= $n ?>" <?= ($pagination['per_page'] ?? 10) == $n ? 'selected' : '' ?>><?= $n ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        </form>
                                    </div>
                                </div>

                                <!-- ── BULK ACTION BAR (hidden by default) ── -->
                                <div id="bulkActionBar" class="d-none px-3 py-2 align-items-center gap-2">
                                    <i class="fas fa-check-square text-success"></i>
                                    <span class="fw-semibold text-success small">
                                        <span id="selectedCount">0</span> invoice dipilih
                                    </span>
                                    <div class="ms-auto d-flex gap-2 flex-wrap">
                                        <select id="bulkStatusSelect" class="form-select form-select-sm"
                                            style="width:160px">
                                            <option value="">Ganti status ke...</option>
                                            <option value="sent">✈️ Sent</option>
                                            <option value="unsent">↩️ Unsent</option>
                                            <option value="paid">✅ Paid</option>
                                            <option value="cancelled">🚫 Cancelled</option>
                                            <option value="draft">📝 Draft</option>
                                        </select>
                                        <button class="btn btn-success btn-sm" onclick="applyBulkStatus()">
                                            <i class="fas fa-check me-1"></i> Terapkan
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">
                                            <i class="fas fa-times me-1"></i> Batal
                                        </button>
                                    </div>
                                </div>

                                <!-- Table -->
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover align-middle mb-0"
                                            style="font-size:.84rem;">
                                            <thead class="table-success">
                                                <tr>
                                                    <th class="col-check">
                                                        <input type="checkbox" id="checkAll" class="form-check-input"
                                                            title="Pilih semua">
                                                    </th>
                                                    <th class="text-center" style="width:40px">No</th>
                                                    <th style="width:130px">No. Invoice</th>
                                                    <th style="width:85px">Tgl Invoice</th>
                                                    <th style="width:110px">Periode Shipment</th>
                                                    <th>Customer</th>
                                                    <th style="width:100px">No. Faktur</th>
                                                    <th style="width:90px">No. PO</th>
                                                    <th class="text-end" style="width:130px">Grand Total</th>
                                                    <th class="text-center" style="width:90px">Status</th>
                                                    <th class="text-center" style="width:80px">Jatuh Tempo</th>
                                                    <th class="text-center" style="width:160px">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($invoices)): ?>
                                                    <tr>
                                                        <td colspan="12" class="text-center text-muted py-5">
                                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                            <?= ($filters['status'] ?? '') === 'overdue'
                                                                ? 'Tidak ada invoice overdue'
                                                                : 'Belum ada data invoice' ?>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php
                                                    $today = date('Y-m-d');
                                                    $no = $pagination['offset'] + 1;
                                                    foreach ($invoices as $inv):
                                                        $is_overdue = in_array($inv->status, ['sent', 'draft', 'unsent'])
                                                            && $inv->due_date < $today;
                                                        $days_overdue = $is_overdue
                                                            ? (int) floor((strtotime($today) - strtotime($inv->due_date)) / 86400)
                                                            : 0;
                                                        $can_bulk = !in_array($inv->status, ['paid', 'cancelled']);
                                                        ?>
                                                        <tr class="<?= $is_overdue ? 'row-overdue' : '' ?>"
                                                            data-id="<?= $inv->id ?>">

                                                            <!-- Checkbox -->
                                                            <td class="col-check">
                                                                <?php if ($can_bulk): ?>
                                                                    <input type="checkbox" class="form-check-input row-check"
                                                                        value="<?= $inv->id ?>" data-status="<?= $inv->status ?>">
                                                                <?php else: ?>
                                                                    <span class="text-muted"
                                                                        title="<?= ucfirst($inv->status) ?> — tidak bisa diubah">
                                                                        <i class="fas fa-lock"
                                                                            style="font-size:.7rem;opacity:.4"></i>
                                                                    </span>
                                                                <?php endif ?>
                                                            </td>

                                                            <td class="text-center text-muted"><?= $no++ ?></td>

                                                            <td>
                                                                <strong><?= htmlspecialchars($inv->no_invoice) ?></strong>
                                                                <?php if ($is_overdue): ?>
                                                                    <div style="font-size:.7rem">
                                                                        <span class="badge bg-danger">
                                                                            <i class="fas fa-exclamation-triangle me-1"></i>Overdue
                                                                            <?= $days_overdue ?>h
                                                                        </span>
                                                                    </div>
                                                                <?php endif ?>
                                                            </td>

                                                            <td><?= date('d/m/Y', strtotime($inv->invoice_date)) ?></td>

                                                            <td>
                                                                <?php if (!empty($inv->periode_shipment)): ?>
                                                                    <span class="badge bg-info text-white" style="font-size:.78rem">
                                                                        <i
                                                                            class="fas fa-calendar-alt me-1"></i><?= htmlspecialchars($inv->periode_shipment) ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">—</span>
                                                                <?php endif ?>
                                                            </td>

                                                            <td><?= htmlspecialchars($inv->customer_nama) ?></td>

                                                            <td class="text-muted small">
                                                                <?= htmlspecialchars($inv->no_faktur ?? '—') ?>
                                                            </td>

                                                            <td>
                                                                <?php if (!empty($inv->no_po)): ?>
                                                                    <span class="badge bg-primary" style="font-size:.75rem">
                                                                        <?= htmlspecialchars($inv->no_po) ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">—</span>
                                                                <?php endif ?>
                                                            </td>

                                                            <td class="text-end">
                                                                <strong>Rp
                                                                    <?= number_format($inv->grand_total, 0, ',', '.') ?></strong>
                                                            </td>

                                                            <td class="text-center">
                                                                <?php
                                                                $bc = 'badge-draft';
                                                                if ($inv->status == 'sent')
                                                                    $bc = 'badge-sent';
                                                                if ($inv->status == 'unsent')
                                                                    $bc = 'badge-unsent';
                                                                if ($inv->status == 'paid')
                                                                    $bc = 'badge-paid';
                                                                if ($inv->status == 'cancelled')
                                                                    $bc = 'badge-cancelled';
                                                                ?>
                                                                <span class="badge <?= $bc ?>" style="font-size:.75rem">
                                                                    <?= strtoupper($inv->status) ?>
                                                                </span>
                                                            </td>

                                                            <td class="text-center">
                                                                <?php if ($is_overdue): ?>
                                                                    <span class="text-danger fw-bold" style="font-size:.78rem">
                                                                        <?= date('d/m/Y', strtotime($inv->due_date)) ?>
                                                                        <div style="font-size:.68rem" class="text-danger">
                                                                            +<?= $days_overdue ?> hari</div>
                                                                    </span>
                                                                <?php elseif ($inv->status !== 'paid' && $inv->status !== 'cancelled'): ?>
                                                                    <?php $days_left = (int) floor((strtotime($inv->due_date) - strtotime($today)) / 86400); ?>
                                                                    <span
                                                                        class="<?= $days_left <= 3 ? 'text-warning fw-bold' : 'text-muted' ?>"
                                                                        style="font-size:.78rem">
                                                                        <?= date('d/m/Y', strtotime($inv->due_date)) ?>
                                                                        <?php if ($days_left <= 3 && $days_left >= 0): ?>
                                                                            <div style="font-size:.68rem" class="text-warning">
                                                                                <?= $days_left == 0 ? 'Hari ini!' : $days_left . 'h lagi' ?>
                                                                            </div>
                                                                        <?php endif ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="text-muted" style="font-size:.78rem">
                                                                        <?= date('d/m/Y', strtotime($inv->due_date)) ?>
                                                                    </span>
                                                                <?php endif ?>
                                                            </td>

                                                            <!-- Aksi -->
                                                            <td class="text-center">
                                                                <a href="<?= base_url('invoice_tsc/detail/' . $inv->id) ?>"
                                                                    class="btn btn-info btn-sm" title="Detail">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="<?= base_url('invoice_tsc/export_pdf/' . $inv->id) ?>"
                                                                    class="btn btn-danger btn-sm" title="Export PDF">
                                                                    <i class="fas fa-file-pdf"></i>
                                                                </a>
                                                                <?php if ($inv->status != 'paid' && $inv->status != 'cancelled'): ?>
                                                                    <a href="<?= base_url('invoice_tsc/ubah/' . $inv->id) ?>"
                                                                        class="btn btn-warning btn-sm" title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <?php if ($inv->status == 'draft' || $inv->status == 'unsent'): ?>
                                                                        <button class="btn btn-primary btn-sm"
                                                                            onclick="updateStatus(<?= $inv->id ?>, 'sent')"
                                                                            title="Mark as Sent">
                                                                            <i class="fas fa-paper-plane"></i>
                                                                        </button>
                                                                    <?php endif ?>
                                                                    <?php if ($inv->status == 'sent'): ?>
                                                                        <button class="btn btn-warning btn-sm"
                                                                            onclick="updateStatus(<?= $inv->id ?>, 'unsent')"
                                                                            title="Mark as Unsent">
                                                                            <i class="fas fa-undo"></i>
                                                                        </button>
                                                                        <button class="btn btn-success btn-sm"
                                                                            onclick="updateStatus(<?= $inv->id ?>, 'paid')"
                                                                            title="Mark as Paid">
                                                                            <i class="fas fa-check-circle"></i>
                                                                        </button>
                                                                    <?php endif ?>
                                                                    <button class="btn btn-secondary btn-sm"
                                                                        onclick="confirmCancel(<?= $inv->id ?>)"
                                                                        title="Cancel Invoice">
                                                                        <i class="fas fa-ban"></i>
                                                                    </button>
                                                                    <button class="btn btn-danger btn-sm"
                                                                        onclick="confirmDelete(<?= $inv->id ?>)" title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                <?php elseif ($inv->status == 'cancelled'): ?>
                                                                    <span class="badge bg-secondary">
                                                                        <i class="fas fa-ban me-1"></i>Cancelled
                                                                    </span>
                                                                <?php else: ?>
                                                                    <?php if (($user_level ?? '') === 'superadmin'): ?>
                                                                        <a href="<?= base_url('invoice_tsc/ubah_paid/' . $inv->id) ?>"
                                                                            class="btn btn-warning btn-sm" title="Edit (Superadmin)">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                        <button class="btn btn-danger btn-sm"
                                                                            onclick="confirmDeletePaid(<?= $inv->id ?>)"
                                                                            title="Hapus (Superadmin)">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-secondary">
                                                                            <i class="fas fa-lock me-1"></i>Paid
                                                                        </span>
                                                                    <?php endif ?>
                                                                <?php endif ?>
                                                            </td>

                                                        </tr>
                                                    <?php endforeach ?>
                                                <?php endif ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- PAGINATION -->
                                <?php if ($pagination['total_pages'] > 1): ?>
                                    <div class="card-footer py-2">
                                        <nav>
                                            <ul class="pagination pagination-sm justify-content-center mb-0">

                                                <?php
                                                // Build base query string yang support customer_id[] array
                                                $base_filters = $filters;
                                                // Unset customer_id karena akan di-handle manual
                                                unset($base_filters['customer_id']);
                                                $base_qs = http_build_query(array_merge($base_filters, ['per_page' => $pagination['per_page']]));
                                                // Append customer_id[] manually
                                                $customer_qs = '';
                                                if (!empty($selected_customers)) {
                                                    foreach ($selected_customers as $cid) {
                                                        $customer_qs .= '&customer_id[]=' . urlencode($cid);
                                                    }
                                                }
                                                $build_page_url = function ($pg) use ($base_qs, $customer_qs) {
                                                    return base_url('invoice_tsc?' . $base_qs . $customer_qs . '&page=' . $pg);
                                                };
                                                ?>

                                                <li
                                                    class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                                                    <a class="page-link"
                                                        href="<?= $pagination['current_page'] > 1 ? $build_page_url($pagination['current_page'] - 1) : '#' ?>">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>

                                                <?php
                                                $sp = max(1, $pagination['current_page'] - 3);
                                                $ep = min($pagination['total_pages'], $pagination['current_page'] + 3);
                                                if ($sp > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="<?= $build_page_url(1) ?>">1</a>
                                                    </li>
                                                    <?php if ($sp > 2): ?>
                                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                                    <?php endif ?>
                                                <?php endif ?>

                                                <?php for ($i = $sp; $i <= $ep; $i++): ?>
                                                    <li
                                                        class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                                        <a class="page-link" href="<?= $build_page_url($i) ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor ?>

                                                <?php if ($ep < $pagination['total_pages']): ?>
                                                    <?php if ($ep < $pagination['total_pages'] - 1): ?>
                                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                                    <?php endif ?>
                                                    <li class="page-item">
                                                        <a class="page-link"
                                                            href="<?= $build_page_url($pagination['total_pages']) ?>"><?= $pagination['total_pages'] ?></a>
                                                    </li>
                                                <?php endif ?>

                                                <li
                                                    class="page-item <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                                                    <a class="page-link"
                                                        href="<?= $pagination['current_page'] < $pagination['total_pages'] ? $build_page_url($pagination['current_page'] + 1) : '#' ?>">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                <?php endif ?>

                            </div>
                        </div><!-- /pane-invoice -->


                        <!-- ══════════════════════════════════════════
                             TAB 2: AGING REPORT
                             ══════════════════════════════════════════ -->
                        <div class="tab-pane fade" id="pane-aging" role="tabpanel">
                            <div class="p-3">

                                <?php $ag = $aging_summary; ?>

                                <!-- Aging Summary Cards -->
                                <div class="row g-3 mb-4">
                                    <div class="col-xl col-md-4 col-6">
                                        <div class="card border-0 shadow-sm h-100"
                                            style="border-top:3px solid #1cc88a !important">
                                            <div class="card-body py-2 px-3">
                                                <div class="text-muted fw-semibold text-uppercase mb-1"
                                                    style="font-size:.65rem;letter-spacing:.05em">
                                                    Current (belum jatuh tempo)
                                                </div>
                                                <div class="fw-bold text-success" style="font-size:.9rem">
                                                    Rp <?= number_format($ag->current_amount ?? 0, 0, ',', '.') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl col-md-4 col-6">
                                        <div class="card border-0 shadow-sm h-100"
                                            style="border-top:3px solid #f6c23e !important">
                                            <div class="card-body py-2 px-3">
                                                <div class="text-muted fw-semibold text-uppercase mb-1"
                                                    style="font-size:.65rem;letter-spacing:.05em">
                                                    Overdue 1–14 hari
                                                </div>
                                                <div class="fw-bold text-warning" style="font-size:.9rem">
                                                    Rp <?= number_format($ag->bucket_1_14 ?? 0, 0, ',', '.') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl col-md-4 col-6">
                                        <div class="card border-0 shadow-sm h-100"
                                            style="border-top:3px solid #fd7e14 !important">
                                            <div class="card-body py-2 px-3">
                                                <div class="text-muted fw-semibold text-uppercase mb-1"
                                                    style="font-size:.65rem;letter-spacing:.05em">
                                                    Overdue 15–30 hari
                                                </div>
                                                <div class="fw-bold" style="color:#fd7e14;font-size:.9rem">
                                                    Rp <?= number_format($ag->bucket_15_30 ?? 0, 0, ',', '.') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl col-md-4 col-6">
                                        <div class="card border-0 shadow-sm h-100"
                                            style="border-top:3px solid #e74a3b !important">
                                            <div class="card-body py-2 px-3">
                                                <div class="text-muted fw-semibold text-uppercase mb-1"
                                                    style="font-size:.65rem;letter-spacing:.05em">
                                                    Overdue &gt;30 hari
                                                </div>
                                                <div class="fw-bold text-danger" style="font-size:.9rem">
                                                    Rp <?= number_format($ag->bucket_30plus ?? 0, 0, ',', '.') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl col-md-4 col-6">
                                        <div class="card border-0 shadow-sm h-100"
                                            style="border-top:3px solid #4e73df !important">
                                            <div class="card-body py-2 px-3">
                                                <div class="text-muted fw-semibold text-uppercase mb-1"
                                                    style="font-size:.65rem;letter-spacing:.05em">
                                                    Total Outstanding
                                                </div>
                                                <div class="fw-bold text-primary" style="font-size:.9rem">
                                                    Rp <?= number_format($ag->total_outstanding ?? 0, 0, ',', '.') ?>
                                                </div>
                                                <div class="text-muted" style="font-size:.7rem">
                                                    <?= $ag->total_invoice ?? 0 ?> invoice ·
                                                    <?= $ag->total_customer ?? 0 ?> customer
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Per Customer Table -->
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="fas fa-users me-2 text-primary"></i> Aging per Customer
                                        </h6>
                                        <a href="<?= base_url('invoice_tsc/export_aging') ?>"
                                            class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-file-csv me-1"></i> Export CSV
                                        </a>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-primary">
                                                    <tr>
                                                        <th>Customer</th>
                                                        <th class="text-center">Invoice</th>
                                                        <th class="text-end">Current</th>
                                                        <th class="text-end">1–14 hari</th>
                                                        <th class="text-end">15–30 hari</th>
                                                        <th class="text-end">&gt;30 hari</th>
                                                        <th class="text-end fw-bold">Total Outstanding</th>
                                                        <th class="text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($aging_per_customer)): ?>
                                                        <tr>
                                                            <td colspan="8" class="text-center py-4 text-muted">
                                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                                Semua piutang sudah lunas!
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($aging_per_customer as $ac):
                                                            $worst = 'current';
                                                            if ($ac->bucket_1_14 > 0)
                                                                $worst = '1-14';
                                                            if ($ac->bucket_15_30 > 0)
                                                                $worst = '15-30';
                                                            if ($ac->bucket_30plus > 0)
                                                                $worst = '30+';
                                                            $status_cfg = [
                                                                'current' => ['badge' => 'bg-success', 'label' => 'On Time'],
                                                                '1-14' => ['badge' => 'bg-warning text-dark', 'label' => 'Terlambat'],
                                                                '15-30' => ['badge' => 'bg-orange text-white', 'label' => 'Mendesak'],
                                                                '30+' => ['badge' => 'bg-danger', 'label' => 'Kritis'],
                                                            ];
                                                            $sc = $status_cfg[$worst];
                                                            ?>
                                                            <tr>
                                                                <td class="fw-semibold">
                                                                    <?= htmlspecialchars($ac->customer_nama ?? $ac->customer_id) ?>
                                                                </td>
                                                                <td class="text-center"><?= $ac->invoice_count ?></td>
                                                                <td
                                                                    class="text-end small <?= $ac->current_amount > 0 ? 'text-success' : 'text-muted' ?>">
                                                                    <?= $ac->current_amount > 0 ? 'Rp ' . number_format($ac->current_amount, 0, ',', '.') : '—' ?>
                                                                </td>
                                                                <td
                                                                    class="text-end small <?= $ac->bucket_1_14 > 0 ? 'text-warning fw-bold' : 'text-muted' ?>">
                                                                    <?= $ac->bucket_1_14 > 0 ? 'Rp ' . number_format($ac->bucket_1_14, 0, ',', '.') : '—' ?>
                                                                </td>
                                                                <td class="text-end small <?= $ac->bucket_15_30 > 0 ? 'fw-bold' : 'text-muted' ?>"
                                                                    style="<?= $ac->bucket_15_30 > 0 ? 'color:#fd7e14' : '' ?>">
                                                                    <?= $ac->bucket_15_30 > 0 ? 'Rp ' . number_format($ac->bucket_15_30, 0, ',', '.') : '—' ?>
                                                                </td>
                                                                <td
                                                                    class="text-end small <?= $ac->bucket_30plus > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                                                    <?= $ac->bucket_30plus > 0 ? 'Rp ' . number_format($ac->bucket_30plus, 0, ',', '.') : '—' ?>
                                                                </td>
                                                                <td class="text-end fw-bold">
                                                                    Rp <?= number_format($ac->total_outstanding, 0, ',', '.') ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge <?= $sc['badge'] ?>"
                                                                        style="font-size:.7rem">
                                                                        <?= $sc['label'] ?>
                                                                        <?php if ($ac->max_overdue_days > 0): ?>
                                                                            (<?= $ac->max_overdue_days ?>h)
                                                                        <?php endif ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach ?>
                                                    <?php endif ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detail Table -->
                                <div class="card shadow-sm">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="fas fa-list me-2 text-warning"></i> Detail Semua Piutang
                                            Outstanding
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-warning sticky-top">
                                                    <tr>
                                                        <th>Customer</th>
                                                        <th>No. Invoice</th>
                                                        <th>Jatuh Tempo</th>
                                                        <th class="text-center">Overdue</th>
                                                        <th class="text-center">Bucket</th>
                                                        <th class="text-end">Outstanding</th>
                                                        <th class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($aging_detail)): ?>
                                                        <tr>
                                                            <td colspan="7" class="text-center py-4 text-muted">
                                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                                Semua piutang sudah lunas!
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($aging_detail as $ad):
                                                            $bucket_cfg = [
                                                                'current' => ['class' => 'bg-success', 'label' => 'Current'],
                                                                '1-14' => ['class' => 'bg-warning text-dark', 'label' => '1–14 hari'],
                                                                '15-30' => ['class' => 'bg-orange text-white', 'label' => '15–30 hari'],
                                                                '30+' => ['class' => 'bg-danger', 'label' => '>30 hari'],
                                                            ];
                                                            $bc = $bucket_cfg[$ad->aging_bucket] ?? $bucket_cfg['current'];
                                                            ?>
                                                            <tr
                                                                class="<?= in_array($ad->aging_bucket, ['15-30', '30+']) ? 'table-danger' : ($ad->aging_bucket === '1-14' ? 'table-warning' : '') ?>">
                                                                <td class="fw-semibold small">
                                                                    <?= htmlspecialchars($ad->customer_nama ?? $ad->customer_id) ?>
                                                                </td>
                                                                <td class="small">
                                                                    <?= htmlspecialchars($ad->no_invoice ?? '—') ?></td>
                                                                <td class="small"><?= date('d/m/Y', strtotime($ad->due_date)) ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php if ($ad->overdue_days > 0): ?>
                                                                        <span class="fw-bold text-danger">+<?= $ad->overdue_days ?>
                                                                            hari</span>
                                                                    <?php else: ?>
                                                                        <span
                                                                            class="text-success small"><?= abs($ad->overdue_days) ?>
                                                                            hari lagi</span>
                                                                    <?php endif ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge <?= $bc['class'] ?>"
                                                                        style="font-size:.65rem">
                                                                        <?= $bc['label'] ?>
                                                                    </span>
                                                                </td>
                                                                <td class="text-end fw-bold">
                                                                    Rp <?= number_format($ad->outstanding, 0, ',', '.') ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php if ($ad->invoice_id): ?>
                                                                        <a href="<?= base_url('invoice_tsc/detail/' . $ad->invoice_id) ?>"
                                                                            class="btn btn-xs btn-outline-primary"
                                                                            style="font-size:.7rem;padding:2px 8px">
                                                                            <i class="fas fa-eye me-1"></i> Invoice
                                                                        </a>
                                                                    <?php endif ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach ?>
                                                    <?php endif ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div><!-- /pane-aging -->

                    </div><!-- /tab-content -->

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {

            // ══════════════════════════════════════════
            // Init Select2 multi-select customer
            // ══════════════════════════════════════════
            $('#customerMultiSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Semua Customer',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function () { return 'Customer tidak ditemukan'; },
                    searching: function () { return 'Mencari...'; },
                    inputTooShort: function () { return 'Ketik untuk mencari customer'; },
                    maximumSelected: function (args) {
                        return 'Maksimal ' + args.maximum + ' customer';
                    },
                    removeAllItems: function () { return 'Hapus semua'; }
                }
            });

            // Sync count badge real-time saat selection berubah
            $('#customerMultiSelect').on('change', function () {
                var count = $(this).val() ? $(this).val().length : 0;
                var $label = $(this).closest('.col-md-4').find('label .badge');
                if (count > 0) {
                    if ($label.length) {
                        $label.text(count + ' dipilih');
                    } else {
                        $(this).closest('.col-md-4').find('label').append(
                            '<span class="badge bg-success active-filter-badge ms-1">' + count + ' dipilih</span>'
                        );
                    }
                } else {
                    $label.remove();
                }
            });

        }); // end document ready

        // ══════════════════════════════════════════
        // Single status update (tombol per baris)
        // ══════════════════════════════════════════
        function updateStatus(id, status) {
            const msgs = {
                paid: 'Tandai invoice sebagai PAID?\n\nIni akan membuat jurnal pembayaran dan mengurangi piutang usaha.',
                sent: 'Tandai invoice sebagai SENT?\n\nInvoice akan ditandai sudah terkirim ke customer.',
                unsent: 'Tarik kembali invoice (UNSENT)?\n\nStatus invoice akan dikembalikan dan perlu dikirim ulang.'
            };
            if (confirm(msgs[status] || 'Lanjutkan?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('invoice_tsc/update_status/') ?>' + id;
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'status'; input.value = status;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function confirmCancel(id) {
            if (confirm('⚠️ CANCEL INVOICE\n\nInvoice akan dibatalkan dan tidak bisa diubah lagi.\n\nYakin?')) {
                updateStatus(id, 'cancelled');
            }
        }

        function confirmDelete(id) {
            if (confirm('Yakin ingin menghapus invoice ini?\n\nData akan dihapus permanent!')) {
                window.location.href = '<?= base_url('invoice_tsc/hapus/') ?>' + id;
            }
        }

        function confirmDeletePaid(id) {
            if (confirm('⚠️ PERINGATAN SUPERADMIN\n\nAnda akan menghapus invoice yang sudah PAID!\nIni akan menghapus jurnal akuntansi dan tidak bisa dikembalikan.\n\nYakin?')) {
                if (confirm('Konfirmasi sekali lagi — Hapus invoice PAID ID: ' + id + '?')) {
                    window.location.href = '<?= base_url('invoice_tsc/hapus/') ?>' + id;
                }
            }
        }

        // ══════════════════════════════════════════
        // Bulk select logic
        // ══════════════════════════════════════════
        const checkAll = document.getElementById('checkAll');
        const bulkBar = document.getElementById('bulkActionBar');
        const countEl = document.getElementById('selectedCount');

        function getChecked() {
            return [...document.querySelectorAll('.row-check:checked')];
        }

        function updateBulkBar() {
            const checked = getChecked();
            countEl.textContent = checked.length;

            document.querySelectorAll('.row-check').forEach(cb => {
                const row = cb.closest('tr');
                cb.checked ? row.classList.add('row-selected') : row.classList.remove('row-selected');
            });

            if (checked.length > 0) {
                bulkBar.classList.remove('d-none');
                bulkBar.classList.add('d-flex');
            } else {
                bulkBar.classList.add('d-none');
                bulkBar.classList.remove('d-flex');
            }
        }

        checkAll?.addEventListener('change', function () {
            document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
            updateBulkBar();
        });

        document.addEventListener('change', function (e) {
            if (!e.target.classList.contains('row-check')) return;
            updateBulkBar();

            const allChecks = document.querySelectorAll('.row-check');
            const checkedCount = getChecked().length;
            if (checkAll) {
                checkAll.indeterminate = checkedCount > 0 && checkedCount < allChecks.length;
                checkAll.checked = checkedCount > 0 && checkedCount === allChecks.length;
            }
        });

        function clearSelection() {
            document.querySelectorAll('.row-check').forEach(cb => cb.checked = false);
            if (checkAll) { checkAll.checked = false; checkAll.indeterminate = false; }
            updateBulkBar();
        }

        // ══════════════════════════════════════════
        // Apply bulk status
        // ══════════════════════════════════════════
        function applyBulkStatus() {
            const checked = getChecked();
            const status = document.getElementById('bulkStatusSelect').value;

            if (!checked.length) {
                Swal.fire('Pilih dulu', 'Belum ada invoice yang dipilih.', 'warning');
                return;
            }
            if (!status) {
                Swal.fire('Pilih status', 'Tentukan status yang ingin diterapkan.', 'warning');
                return;
            }

            const statusLabel = {
                sent: 'SENT', unsent: 'UNSENT', paid: 'PAID', cancelled: 'CANCELLED', draft: 'DRAFT'
            };

            const extraWarning = status === 'paid'
                ? '<br><small class="text-danger mt-1 d-block">⚠️ Akan membuat jurnal pembayaran untuk setiap invoice!</small>'
                : status === 'cancelled'
                    ? '<br><small class="text-danger mt-1 d-block">⚠️ Invoice yang dicancel tidak bisa diubah lagi!</small>'
                    : '';

            Swal.fire({
                title: `Ganti ${checked.length} invoice ke <span class="text-success">${statusLabel[status]}</span>?`,
                html: `Aksi ini akan diterapkan ke <strong>${checked.length}</strong> invoice yang dipilih.${extraWarning}`,
                icon: (status === 'cancelled') ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check me-1"></i> Ya, terapkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: (status === 'cancelled') ? '#e74a3b' : '#1cc88a',
            }).then(result => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Memproses...',
                    html: `Mengupdate ${checked.length} invoice...`,
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const ids = checked.map(cb => cb.value);
                const formData = new FormData();
                ids.forEach(id => formData.append('ids[]', id));
                formData.append('status', status);

                fetch('<?= base_url('invoice_tsc/bulk_update_status') ?>', {
                    method: 'POST',
                    body: formData
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                html: `<strong>${data.updated}</strong> invoice berhasil diupdate ke <strong>${statusLabel[status]}</strong>.`,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false,
                                timerProgressBar: true
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', data.message || 'Terjadi kesalahan saat update.', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Koneksi gagal. Coba lagi.', 'error');
                    });
            });
        }

        // ══════════════════════════════════════════
        // Misc
        // ══════════════════════════════════════════
        if (window.location.hash === '#aging') {
            document.getElementById('tab-aging')?.click();
        }

        setTimeout(() => document.querySelectorAll('.alert').forEach(el => $(el).fadeOut('slow')), 6000);
    </script>

    <?php if ($this->session->flashdata('download_pdf')): ?>
        <script>
            $(document).ready(function () {
                const pdfUrl = '<?= $this->session->flashdata('download_pdf') ?>';
                const invoiceNo = '<?= $this->session->flashdata('invoice_no') ?>';
                Swal.fire({
                    title: 'Invoice Berhasil Dibuat!',
                    html: 'Invoice <strong>' + invoiceNo + '</strong> tersimpan.<br>PDF sedang didownload...',
                    icon: 'success', timer: 3000, showConfirmButton: false, timerProgressBar: true
                });
                setTimeout(() => {
                    const link = document.createElement('a');
                    link.href = pdfUrl;
                    link.download = 'Invoice_' + invoiceNo.replace(/\//g, '-') + '.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }, 500);
            });
        </script>
    <?php endif ?>

</body>

</html>