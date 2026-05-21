<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* ── STATUS BADGES ── */
        .badge-status {
            font-size: 0.72rem;
            padding: 4px 8px;
            font-weight: 600;
            border-radius: 4px;
            white-space: nowrap;
        }

        .badge-purple {
            background-color: #6f42c1;
            color: white;
        }

        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: #fff;
        }

        .btn-purple:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
            color: #fff;
        }

        /* ── STAT CARDS ── */
        .status-card {
            border-left: 4px solid;
            transition: transform .15s;
            cursor: pointer;
        }

        .border-left-dark {
            border-left-color: #343a40 !important;
        }

        .border-left-purple {
            border-left-color: #6f42c1 !important;
        }

        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }

        /* ── TABLE ── */
        .table-dr {
            font-size: 0.78rem;
        }

        .table-dr thead th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: .4px;
            white-space: nowrap;
            padding: 8px 6px;
            vertical-align: middle;
        }

        .table-dr tbody td {
            font-size: 0.78rem;
            vertical-align: middle;
            padding: 6px 6px;
            line-height: 1.3;
        }

        .no-rent {
            font-family: monospace;
            font-weight: 700;
            font-size: 0.9rem;
            color: #4e73df;
            white-space: nowrap;
        }

        /* ── UNIT PILLS ── */
        .unit-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 3px;
        }

        .unit-pill {
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .unit-pill-pending {
            background: #e2e3e5;
            color: #383d41;
        }

        .unit-pill-assigned {
            background: #bee5eb;
            color: #0c5460;
        }

        .unit-pill-active {
            background: #b8d4f9;
            color: #004085;
        }

        .unit-pill-extended {
            background: #ffeeba;
            color: #856404;
        }

        .unit-pill-returned {
            background: #c3e6cb;
            color: #155724;
        }

        .unit-pill-cancelled {
            background: #f5c6cb;
            color: #721c24;
        }

        /* ── UNIT SUMMARY BADGE ── */
        .unit-summary {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .unit-summary .us-total {
            background: #4e73df;
            color: #fff;
            padding: 2px 7px;
            border-radius: 10px;
        }

        .unit-summary .us-active {
            background: #1cc88a;
            color: #fff;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .unit-summary .us-ret {
            background: #858796;
            color: #fff;
            padding: 2px 6px;
            border-radius: 10px;
        }

        /* ── DURATION / SLA ── */
        .sla-badge {
            font-size: 0.68rem;
            padding: 3px 6px;
            border-radius: 4px;
            font-weight: 700;
            white-space: nowrap;
            display: inline-block;
        }

        .sla-ontime {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .sla-late {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .sla-overdue {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .sla-today {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .sla-close {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .sla-ok {
            background: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        .sla-sourcing {
            background: transparent;
            color: #aaa;
        }

        /* ── DURATION BOX ── */
        .dur-box {
            font-size: 0.72rem;
            color: #495057;
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1.2;
        }

        .dur-box .dur-days {
            font-size: 1rem;
            font-weight: 700;
            color: #4e73df;
        }

        /* ── AKSI ── */
        .btn-aksi-wrap {
            display: flex;
            flex-direction: column;
            gap: 3px;
            align-items: flex-start;
            min-width: 90px;
        }

        .btn-aksi-wrap .btn-icon-row {
            display: flex;
            gap: 2px;
            flex-wrap: wrap;
        }

        .btn-aksi-wrap .btn-icon-row .btn {
            font-size: 0.68rem;
            padding: 3px 6px;
            line-height: 1.2;
        }

        /* ── FILTER BAR ── */
        .filter-bar {
            background: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58, 59, 69, .15);
        }

        .filter-bar .filter-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #858796;
            margin-bottom: 10px;
        }

        .filter-bar .form-control {
            font-size: 0.82rem;
            height: 34px;
            border-radius: 6px;
            border-color: #d1d3e2;
        }

        .filter-bar select.form-control {
            height: 34px;
            padding: 0 8px;
        }

        .filter-active-badge {
            display: inline-block;
            background: #4e73df;
            color: #fff;
            font-size: 0.68rem;
            border-radius: 10px;
            padding: 2px 8px;
            margin-left: 6px;
            font-weight: 600;
        }

        /* ── STATUS CHIPS ── */
        .status-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .status-chip {
            cursor: pointer;
            font-size: 0.72rem;
            padding: 4px 12px;
            border-radius: 20px;
            border: 2px solid transparent;
            font-weight: 600;
            transition: all .15s;
            user-select: none;
        }

        .status-chip.chip-all {
            background: #e3e6f0;
            color: #5a5c69;
            border-color: #e3e6f0;
        }

        .status-chip.chip-all.active {
            background: #5a5c69;
            color: #fff;
        }

        .status-chip.chip-Sourcing {
            background: #d6d8d9;
            color: #1b1e21;
        }

        .status-chip.chip-Sourcing.active {
            background: #343a40;
            color: #fff;
        }

        .status-chip.chip-Scheduled {
            background: #e2e3e5;
            color: #383d41;
        }

        .status-chip.chip-Scheduled.active {
            background: #6c757d;
            color: #fff;
        }

        .status-chip.chip-Active {
            background: #b8d4f9;
            color: #004085;
        }

        .status-chip.chip-Active.active {
            background: #4e73df;
            color: #fff;
        }

        .status-chip.chip-Partial {
            background: #ffeeba;
            color: #856404;
        }

        .status-chip.chip-Partial.active {
            background: #ffc107;
            color: #212529;
        }

        .status-chip.chip-Completed {
            background: #c3e6cb;
            color: #155724;
        }

        .status-chip.chip-Completed.active {
            background: #28a745;
            color: #fff;
        }

        .status-chip.chip-Cancelled {
            background: #f5c6cb;
            color: #721c24;
        }

        .status-chip.chip-Cancelled.active {
            background: #dc3545;
            color: #fff;
        }

        /* ── PAGINATION ── */
        .pagination-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            border-top: 1px solid #e3e6f0;
            background: #f8f9fc;
            flex-wrap: wrap;
            gap: 8px;
        }

        .pagination-info {
            font-size: 0.82rem;
            color: #858796;
        }

        .pagination-info strong {
            color: #4e73df;
        }

        .pagination .page-link {
            font-size: 0.82rem;
            padding: 5px 11px;
            color: #4e73df;
            border-color: #d1d3e2;
        }

        .pagination .page-item.active .page-link {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        .per-page-select {
            font-size: 0.82rem;
            height: 30px;
            padding: 0 6px;
            border-radius: 4px;
            border-color: #d1d3e2;
            width: auto;
            display: inline-block;
        }

        .table-wrapper {
            position: relative;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- HEADER -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-calendar-alt text-info"></i> <?= $title ?>
                        </h1>
                        <div class="d-flex flex-wrap" style="gap:6px;">
                            <?php $level = $this->session->userdata('login')['user_level'] ?? ''; ?>
                            <?php if ($level === 'superadmin'): ?>
                                <a href="<?= base_url('daily_rent/terhapus') ?>" class="btn btn-warning btn-sm shadow-sm">
                                    <i class="fas fa-trash-restore"></i> Data Terhapus
                                </a>
                            <?php endif ?>
                            <a href="<?= base_url('daily_rent/import') ?>" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-file-import"></i> Import Excel
                            </a>
                            <a href="#" id="btnExportExcel" class="btn btn-sm shadow-sm"
                                style="background:#1d6f42;border-color:#1d6f42;color:#fff;">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                            <a href="<?= base_url('daily_rent/export') ?>" class="btn btn-danger btn-sm shadow-sm">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                            <a href="<?= base_url('daily_rent/tambah') ?>" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-plus"></i> Tambah Order
                            </a>
                        </div>
                    </div>

                    <!-- ALERTS -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('warning')): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('warning') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <!-- STAT CARDS -->
                    <?php $stats = $stats ?? []; ?>
                    <div class="row mb-3">
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card status-card border-left-primary shadow h-100 py-2" data-status-filter="">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Order
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['total'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card status-card border-left-dark shadow h-100 py-2"
                                data-status-filter="Sourcing Vendor">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Sourcing</div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['sourcing'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card status-card border-left-secondary shadow h-100 py-2"
                                data-status-filter="Scheduled">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Scheduled
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['scheduled'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card status-card border-left-primary shadow h-100 py-2"
                                data-status-filter="Active">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active</div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['active'] ?? 0 ?></div>
                                    <?php if (!empty($stats['units_active'])): ?>
                                        <div class="text-xs text-muted"><?= $stats['units_active'] ?> unit aktif</div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card status-card border-left-warning shadow h-100 py-2"
                                data-status-filter="Partially Returned">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Partial
                                        Return</div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['partially_returned'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card status-card border-left-success shadow h-100 py-2"
                                data-status-filter="Completed">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['completed'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card status-card border-left-danger shadow h-100 py-2"
                                data-status-filter="Cancelled">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Cancelled
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['cancelled'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <!-- Unit stat -->
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card border-left-info shadow h-100 py-2"
                                style="border-left:4px solid #36b9cc!important;">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Unit Pending
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['units_pending'] ?? 0 ?></div>
                                    <div class="text-xs text-muted">belum di-assign</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FILTER BAR -->
                    <div class="filter-bar">
                        <div class="filter-title">
                            <i class="fas fa-filter"></i> Filter & Pencarian
                            <span id="activeFilterCount" class="filter-active-badge" style="display:none;"></span>
                        </div>
                        <div class="row align-items-end">
                            <div class="col-lg-3 col-md-6 mb-2">
                                <label class="small text-muted mb-1">Cari</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="filterKeyword" class="form-control"
                                        placeholder="No rent, PIC, lokasi...">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6 mb-2">
                                <label class="small text-muted mb-1">Customer</label>
                                <select id="filterCustomer" class="form-control">
                                    <option value="">Semua Customer</option>
                                    <?php foreach ($customers ?? [] as $c): ?>
                                        <option value="<?= $c->id ?>"><?= htmlspecialchars($c->nama) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-6 mb-2">
                                <label class="small text-muted mb-1">Vendor</label>
                                <select id="filterVendor" class="form-control">
                                    <option value="">Semua Vendor</option>
                                    <?php foreach ($vendors ?? [] as $v): ?>
                                        <option value="<?= $v->id ?>"><?= htmlspecialchars($v->nama_vendor) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 mb-2">
                                <label class="small text-muted mb-1">Start Date Dari</label>
                                <input type="date" id="filterDateFrom" class="form-control">
                            </div>
                            <div class="col-lg-2 col-md-4 mb-2">
                                <label class="small text-muted mb-1">Sampai</label>
                                <input type="date" id="filterDateTo" class="form-control">
                            </div>
                            <div class="col-lg-1 col-md-2 mb-2">
                                <label class="small text-muted mb-1 d-block">&nbsp;</label>
                                <button id="btnResetFilter" class="btn btn-outline-secondary btn-sm w-100"
                                    title="Reset Filter">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="small text-muted mb-1 d-block">Status</label>
                            <div class="status-chips">
                                <span class="status-chip chip-all active" data-status="">Semua</span>
                                <span class="status-chip chip-Sourcing" data-status="Sourcing Vendor">Sourcing
                                    Vendor</span>
                                <span class="status-chip chip-Scheduled" data-status="Scheduled">Scheduled</span>
                                <span class="status-chip chip-Active" data-status="Active">Active</span>
                                <span class="status-chip chip-Partial" data-status="Partially Returned">Partially
                                    Returned</span>
                                <span class="status-chip chip-Completed" data-status="Completed">Completed</span>
                                <span class="status-chip chip-Cancelled" data-status="Cancelled">Cancelled</span>
                            </div>
                        </div>
                    </div>

                    <!-- TABLE CARD -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-info d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-white">
                                <i class="fas fa-calendar-alt"></i> Daftar Order Daily Rent
                            </h6>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-database"></i> <span id="countNumber">0</span> Order
                            </span>
                        </div>
                        <div class="table-wrapper">
                            <div id="loadingOverlay"
                                style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;
                                   background:rgba(255,255,255,.65);z-index:10;align-items:center;justify-content:center;">
                                <div class="text-center">
                                    <div class="spinner-border text-info" role="status"></div>
                                    <div class="small text-muted mt-1">Memuat data...</div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-dr mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="3%" class="text-center">No</th>
                                                <th width="7%" class="text-center">No Rent</th>
                                                <th width="10%">Customer</th>
                                                <th width="8%">Vendor</th>
                                                <th width="9%">PIC</th>
                                                <th width="8%">Lokasi</th>
                                                <th width="7%" class="text-center">Durasi</th>
                                                <th width="9%" class="text-center">Periode</th>
                                                <th width="9%" class="text-center">Unit</th>
                                                <th width="7%" class="text-center">SLA</th>
                                                <th width="8%" class="text-center">Status</th>
                                                <th width="5%">Notes</th>
                                                <th width="8%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                            <tr>
                                                <td colspan="13" class="text-center py-4">
                                                    <div class="spinner-border spinner-border-sm text-info"></div>
                                                    <span class="ms-2 text-muted">Memuat data...</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="pagination-wrapper">
                                <div class="pagination-info">
                                    Menampilkan <strong id="showFrom">0</strong>–<strong id="showTo">0</strong>
                                    dari <strong id="showTotal">0</strong> order
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted">Per halaman:</span>
                                    <select id="perPageSelect" class="form-control per-page-select">
                                        <option value="10">10</option>
                                        <option value="25" selected>25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0" id="paginationNav"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- MODAL: CANCEL ORDER -->
    <div class="modal fade" id="modalCancel" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"
                style="border-radius:10px;overflow:hidden;border:none;box-shadow:0 10px 40px rgba(0,0,0,.2);">
                <div class="modal-header py-3" style="background:linear-gradient(135deg,#dc3545,#b02a37);border:none;">
                    <div>
                        <h6 class="modal-title text-white fw-bold mb-0"><i class="fas fa-ban me-2"></i>Batalkan Order
                        </h6>
                        <small class="text-white-50" id="cancelRentNoLabel"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="d-flex align-items-start p-3 mb-4"
                        style="background:#fff5f5;border-radius:8px;border-left:4px solid #dc3545;">
                        <i class="fas fa-exclamation-triangle text-danger mt-1 me-3"
                            style="font-size:1.1rem;flex-shrink:0;"></i>
                        <div>
                            <div class="fw-bold text-danger" style="font-size:0.88rem;">Semua unit aktif akan ikut
                                dibatalkan</div>
                            <div class="text-muted" style="font-size:0.82rem;margin-top:3px;">Order dan seluruh unit
                                yang belum returned akan berpindah ke status Cancelled.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-uppercase text-muted mb-2"><i class="fas fa-list-ul"></i>
                            Alasan Cepat</label>
                        <div class="d-flex flex-wrap" style="gap:6px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Customer request pembatalan">Customer request</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Tidak ada unit tersedia">Tidak ada unit</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Kontrak berubah / dipindah">Kontrak berubah</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Double booking / Duplikat order">Double booking</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Force majeure (bencana alam, dll)">Force majeure</button>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">Alasan Cancel <span class="text-danger">*</span> <span
                                class="text-muted fw-normal">(min. 10 karakter)</span></label>
                        <textarea id="cancelReason" class="form-control" rows="3"
                            placeholder="Tuliskan alasan pembatalan..." maxlength="500"
                            style="border-radius:6px;resize:none;"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-danger d-none" id="cancelReasonError"><i
                                    class="fas fa-exclamation-circle"></i> Alasan wajib diisi (min. 10 karakter)</small>
                            <small class="text-muted ms-auto" id="cancelReasonCount">0/500</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="cancelRentId">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            class="fas fa-times"></i> Batal</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnConfirmCancel"><i class="fas fa-ban"></i>
                        Ya, Batalkan Order</button>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {
            const BASE = '<?= base_url() ?>';
            const LEVEL = '<?= $this->session->userdata('login')['user_level'] ?? '' ?>';
            const CAN_EDIT = ['superadmin', 'admin_operational', 'operational_staff'].includes(LEVEL);
            const CAN_DELETE = ['superadmin', 'admin_operational'].includes(LEVEL);

            const CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
            let CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';

            $(document).ajaxSend(function (e, xhr, settings) {
                if (settings.type && settings.type.toUpperCase() === 'POST') {
                    if (typeof settings.data === 'string') settings.data += '&' + CSRF_NAME + '=' + CSRF_HASH;
                    else if (typeof settings.data === 'object' && settings.data !== null) settings.data[CSRF_NAME] = CSRF_HASH;
                    else settings.data = CSRF_NAME + '=' + CSRF_HASH;
                }
            });
            $(document).ajaxSuccess(function (e, xhr) {
                try { let r = JSON.parse(xhr.responseText); if (r && r.csrf_hash) CSRF_HASH = r.csrf_hash; } catch (err) { }
            });

            let currentPage = 1, perPage = 25;
            let filters = { keyword: '', status: '', customer_id: '', vendor_id: '', date_from: '', date_to: '' };
            let searchTimeout = null;

            const STATUS_COLORS = {
                'Sourcing Vendor': 'dark', 'Scheduled': 'secondary', 'Active': 'primary',
                'Partially Returned': 'warning', 'Completed': 'success', 'Cancelled': 'danger'
            };
            const UNIT_STATUS_CLS = {
                'Pending Assign': 'pending', 'Assigned': 'assigned', 'Active': 'active',
                'Extended': 'extended', 'Returned': 'returned', 'Cancelled': 'cancelled'
            };

            function esc(str) {
                if (!str) return '';
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }
            function fmtDate(d) {
                if (!d) return '-';
                let p = d.split('-');
                return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : d;
            }
            function diffDays(d1, d2) {
                if (!d1 || !d2) return null;
                return Math.round((new Date(d2) - new Date(d1)) / 86400000);
            }

            function buildSla(r) {
                const status = r.status_rent;
                if (status === 'Cancelled') return '<span class="sla-badge sla-sourcing">-</span>';
                if (status === 'Sourcing Vendor') return '<span class="sla-badge sla-sourcing text-muted">Sourcing</span>';
                if (!r.rent_end_date) return '<span class="sla-badge sla-sourcing">-</span>';

                const today = new Date(); today.setHours(0, 0, 0, 0);
                if (status === 'Completed') return '<span class="sla-badge sla-ontime">✅ Selesai</span>';

                const end = new Date(r.rent_end_date); end.setHours(0, 0, 0, 0);
                const diff = Math.round((end - today) / 86400000);
                if (diff < 0) return `<span class="sla-badge sla-overdue">🔴 Overdue ${Math.abs(diff)}h</span>`;
                if (diff === 0) return '<span class="sla-badge sla-today">🕐 Berakhir hari ini</span>';
                if (diff <= 3) return `<span class="sla-badge sla-close">⏳ ${diff}h lagi</span>`;
                return `<span class="sla-badge sla-ok">📅 ${diff}h lagi</span>`;
            }

            function buildUnitSummary(r) {
                if (!r.total_units) return '<span class="text-muted small">Belum ada unit</span>';
                let html = '<div class="unit-summary">';
                html += `<span class="us-total">${r.total_units} unit</span>`;
                if (r.units_active > 0) html += `<span class="us-active">${r.units_active} aktif</span>`;
                if (r.units_returned > 0) html += `<span class="us-ret">${r.units_returned} returned</span>`;
                html += '</div>';

                // Unit pills (nopol list)
                if (r.units && r.units.length > 0) {
                    html += '<div class="unit-pills mt-1">';
                    r.units.forEach(function (u) {
                        let cls = UNIT_STATUS_CLS[u.status_unit] || 'pending';
                        html += `<span class="unit-pill unit-pill-${cls}" title="${esc(u.driver || '')} — ${esc(u.status_unit)}">
                    <i class="fas fa-truck" style="font-size:0.6rem;"></i> ${esc(u.nopol || '?')}
                </span>`;
                    });
                    html += '</div>';
                }
                return html;
            }

            function buildDurasi(r) {
                if (!r.rent_start_date || !r.rent_end_date) return '-';
                const days = diffDays(r.rent_start_date, r.rent_end_date);
                return `<div class="dur-box"><span class="dur-days">${days}</span><span>hari</span></div>`;
            }

            function buildPeriode(r) {
                let start = fmtDate(r.rent_start_date);
                let end = fmtDate(r.rent_end_date);
                let ts = r.rent_start_time ? '<br><small class="text-muted">' + r.rent_start_time.substr(0, 5) + '</small>' : '';
                let te = r.rent_end_time ? '<br><small class="text-muted">' + r.rent_end_time.substr(0, 5) + '</small>' : '';
                return `<div style="font-size:0.72rem; line-height:1.4;">
            <i class="fas fa-play-circle text-success" style="font-size:0.6rem;"></i> ${start}${ts}<br>
            <i class="fas fa-stop-circle text-danger"  style="font-size:0.6rem;"></i> ${end}${te}
        </div>`;
            }

            // ── LOAD DATA ──
            function loadData() {
                $('#loadingOverlay').css('display', 'flex');
                $.ajax({
                    url: BASE + 'daily_rent/filter_ajax',
                    method: 'POST',
                    data: { page: currentPage, per_page: perPage, ...filters },
                    dataType: 'json',
                    success: function (res) {
                        $('#loadingOverlay').hide();
                        renderTable(res.rows, res.offset);
                        renderPagination(res.total, res.page, res.per_page);
                        $('#countNumber').text(res.total);
                        updateActiveFilterCount();
                    },
                    error: function (xhr) {
                        $('#loadingOverlay').hide();
                        $('#tableBody').html('<tr><td colspan="13" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle"></i> Gagal memuat data! (' + xhr.status + ')</td></tr>');
                    }
                });
            }

            function renderTable(rows, offset) {
                if (!rows || rows.length === 0) {
                    $('#tableBody').html('<tr><td colspan="13" class="text-center text-muted py-5"><i class="fas fa-inbox fa-3x mb-3 d-block"></i><em>Tidak ada data order yang sesuai filter.</em></td></tr>');
                    return;
                }
                let html = '';
                rows.forEach(function (r, idx) {
                    let status = r.status_rent || 'Sourcing Vendor';
                    let color = STATUS_COLORS[status] || 'secondary';
                    let no = offset + idx + 1;

                    html += `<tr>
                <td class="text-center">${no}</td>
                <td class="text-center"><span class="no-rent">${esc(r.no_rent || '')}</span></td>
                <td><small>${esc(r.nama_customer || '-')}</small></td>
                <td><small>${esc(r.nama_vendor || '-')}</small></td>
                <td>
                    <small class="fw-bold">${esc(r.pic_customer || '-')}</small>
                    ${r.pic_customer_phone ? '<br><small class="text-muted"><i class="fas fa-phone" style="font-size:0.65rem;"></i> ' + esc(r.pic_customer_phone) + '</small>' : ''}
                </td>
                <td><small class="text-muted">${esc(r.location || '-')}</small></td>
                <td class="text-center">${buildDurasi(r)}</td>
                <td class="text-center">${buildPeriode(r)}</td>
                <td>${buildUnitSummary(r)}</td>
                <td class="text-center">${buildSla(r)}</td>
                <td class="text-center"><span class="badge badge-status badge-${color}">${esc(status)}</span></td>
                <td><small class="text-muted">${esc(r.notes || '-')}</small></td>
                <td>${CAN_EDIT ? buildAksi(r, status) : '<span class="text-muted">-</span>'}</td>
            </tr>`;
                });
                $('#tableBody').html(html);
            }

            function buildAksi(r, status) {
                let id = r.id, no = esc(r.no_rent || '');
                let html = '<div class="btn-aksi-wrap"><div class="btn-icon-row">';
                html += `<a href="${BASE}daily_rent/detail/${id}" class="btn btn-outline-info btn-sm" title="Detail" data-bs-toggle="tooltip"><i class="fas fa-eye"></i></a>`;
                html += `<a href="${BASE}daily_rent/ubah/${id}"   class="btn btn-outline-success btn-sm" title="Edit" data-bs-toggle="tooltip"><i class="fas fa-edit"></i></a>`;
                if (!['Completed', 'Cancelled'].includes(status)) {
                    html += `<button class="btn btn-outline-danger btn-sm btn-cancel-order"
                data-id="${id}" data-no="${no}" title="Cancel Order" data-bs-toggle="tooltip">
                <i class="fas fa-ban"></i></button>`;
                }
                if (CAN_DELETE) {
                    html += `<a href="${BASE}daily_rent/hapus/${id}" class="btn btn-danger btn-sm"
                onclick="return confirm('Hapus order ${no}?')" title="Hapus" data-bs-toggle="tooltip">
                <i class="fas fa-trash"></i></a>`;
                }
                html += '</div></div>';
                return html;
            }

            // ── TOOLTIP ──
            $(document).on('mouseenter', '[data-bs-toggle="tooltip"]:not(.tooltip-init)', function () {
                $(this).addClass('tooltip-init');
                new bootstrap.Tooltip(this, { trigger: 'hover' });
                $(this).tooltip('show');
            });

            // ── CANCEL ORDER ──
            $(document).on('click', '.btn-cancel-order', function () {
                $('#cancelRentId').val($(this).data('id'));
                $('#cancelRentNoLabel').text('Order: ' + $(this).data('no'));
                $('#cancelReason').val('');
                $('#cancelReasonCount').text('0/500');
                $('#cancelReasonError').addClass('d-none');
                $('.reason-chip').removeClass('active btn-secondary').addClass('btn-outline-secondary');
                new bootstrap.Modal(document.getElementById('modalCancel')).show();
            });

            $(document).on('click', '.reason-chip', function () {
                var txt = $(this).data('reason');
                $('#cancelReason').val(txt);
                $('#cancelReasonCount').text(txt.length + '/500');
                $('#cancelReasonError').addClass('d-none');
                $('.reason-chip').removeClass('active btn-secondary').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('btn-secondary active');
            });
            $('#cancelReason').on('input', function () {
                $('#cancelReasonCount').text($(this).val().length + '/500');
                if ($(this).val().length >= 10) $('#cancelReasonError').addClass('d-none');
                $('.reason-chip').removeClass('active btn-secondary').addClass('btn-outline-secondary');
            });

            $('#btnConfirmCancel').on('click', function () {
                var reason = $('#cancelReason').val().trim();
                if (reason.length < 10) { $('#cancelReasonError').removeClass('d-none'); $('#cancelReason').focus(); return; }
                var id = $('#cancelRentId').val();
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Membatalkan...');
                $.ajax({
                    url: BASE + 'daily_rent/aksi_cancel', method: 'POST',
                    data: { rent_id: id, cancel_reason: reason }, dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        $('#btnConfirmCancel').prop('disabled', false).html('<i class="fas fa-ban"></i> Ya, Batalkan Order');
                        if (res.success) { bootstrap.Modal.getInstance(document.getElementById('modalCancel')).hide(); loadData(); }
                        else { alert(res.message || 'Gagal cancel!'); }
                    },
                    error: function (xhr) {
                        $('#btnConfirmCancel').prop('disabled', false).html('<i class="fas fa-ban"></i> Ya, Batalkan Order');
                        alert('Gagal koneksi! (' + xhr.status + ')');
                    }
                });
            });

            // ── PAGINATION ──
            function renderPagination(total, page, per) {
                let totalPages = Math.ceil(total / per);
                let from = total === 0 ? 0 : (page - 1) * per + 1;
                let to = Math.min(page * per, total);
                $('#showFrom').text(from); $('#showTo').text(to); $('#showTotal').text(total);
                if (totalPages <= 1) { $('#paginationNav').html(''); return; }
                let html = `<li class="page-item ${page <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${page - 1}">‹</a></li>`;
                getPageNumbers(page, totalPages).forEach(function (p) {
                    if (p === '...') html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
                    else html += `<li class="page-item ${p === page ? 'active' : ''}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
                });
                html += `<li class="page-item ${page >= totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${page + 1}">›</a></li>`;
                $('#paginationNav').html(html);
            }
            function getPageNumbers(cur, tot) {
                if (tot <= 7) { let p = []; for (let i = 1; i <= tot; i++) p.push(i); return p; }
                let p = [1]; if (cur > 3) p.push('...');
                for (let i = Math.max(2, cur - 1); i <= Math.min(tot - 1, cur + 1); i++) p.push(i);
                if (cur < tot - 2) p.push('...'); p.push(tot); return p;
            }
            $(document).on('click', '#paginationNav .page-link', function (e) {
                e.preventDefault();
                let pg = $(this).data('page'); if (!pg || pg < 1) return;
                currentPage = pg; loadData();
                $('html,body').animate({ scrollTop: $('.card.shadow').offset().top - 20 }, 200);
            });
            $('#perPageSelect').on('change', function () { perPage = parseInt($(this).val()); currentPage = 1; loadData(); });

            // ── FILTER EVENTS ──
            $('#filterKeyword').on('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () { filters.keyword = $('#filterKeyword').val().trim(); currentPage = 1; loadData(); }, 500);
            });
            $('#filterCustomer').on('change', function () { filters.customer_id = $(this).val(); currentPage = 1; loadData(); });
            $('#filterVendor').on('change', function () { filters.vendor_id = $(this).val(); currentPage = 1; loadData(); });
            $('#filterDateFrom').on('change', function () { filters.date_from = $(this).val(); currentPage = 1; loadData(); });
            $('#filterDateTo').on('change', function () { filters.date_to = $(this).val(); currentPage = 1; loadData(); });

            $(document).on('click', '.status-chip', function () {
                $('.status-chip').removeClass('active'); $(this).addClass('active');
                filters.status = $(this).data('status'); currentPage = 1; loadData();
            });
            $(document).on('click', '.status-card[data-status-filter]', function () {
                let s = $(this).data('status-filter');
                filters.status = s; currentPage = 1;
                $('.status-chip').removeClass('active');
                $('.status-chip[data-status="' + s + '"]').addClass('active');
                if (s === '') $('.status-chip.chip-all').addClass('active');
                loadData();
            });

            $('#btnResetFilter').on('click', function () {
                filters = { keyword: '', status: '', customer_id: '', vendor_id: '', date_from: '', date_to: '' };
                $('#filterKeyword,#filterCustomer,#filterVendor,#filterDateFrom,#filterDateTo').val('');
                $('.status-chip').removeClass('active'); $('.status-chip.chip-all').addClass('active');
                currentPage = 1; loadData();
            });

            function updateActiveFilterCount() {
                let count = Object.values(filters).filter(v => v !== '').length;
                if (count > 0) $('#activeFilterCount').text(count + ' aktif').show();
                else $('#activeFilterCount').hide();
            }

            // ── EXPORT EXCEL ──
            $('#btnExportExcel').on('click', function (e) {
                e.preventDefault();
                const params = new URLSearchParams({
                    keyword: filters.keyword || '', status: filters.status || '',
                    customer_id: filters.customer_id || '', vendor_id: filters.vendor_id || '',
                    date_from: filters.date_from || '', date_to: filters.date_to || ''
                });
                window.open(BASE + 'daily_rent/export_excel?' + params.toString(), '_blank');
            });

            setTimeout(function () { $('.alert').fadeOut('slow'); }, 5000);
            loadData();
        });
    </script>
</body>

</html>