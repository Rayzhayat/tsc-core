<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
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
        .table-ftl {
            font-size: 0.78rem;
        }

        .table-ftl thead th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: .4px;
            white-space: nowrap;
            padding: 8px 6px;
            vertical-align: middle;
        }

        .table-ftl tbody td {
            font-size: 0.78rem;
            vertical-align: middle;
            padding: 6px 6px;
            line-height: 1.3;
        }

        .no-shipment {
            font-family: monospace;
            font-weight: 700;
            font-size: 0.9rem;
            color: #4e73df;
            white-space: nowrap;
        }

        /* Aksi: icon-only buttons */
        .btn-aksi-wrap {
            display: flex;
            flex-direction: column;
            gap: 3px;
            align-items: flex-start;
            min-width: 90px;
        }

        .btn-aksi-wrap .btn-flow {
            font-size: 0.68rem;
            padding: 3px 7px;
            white-space: nowrap;
            width: 100%;
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

        /* SLA badges */
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

        .sla-na {
            background: transparent;
            color: #aaa;
        }

        .sla-sourcing {
            background: transparent;
            color: #aaa;
        }

        /* Filter bar */
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

        /* Status chips */
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

        .status-chip:hover {
            opacity: .85;
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

        .status-chip.chip-Scheduled {
            background: #e2e3e5;
            color: #383d41;
        }

        .status-chip.chip-Scheduled.active {
            background: #6c757d;
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

        .status-chip.chip-Loading {
            background: #bee5eb;
            color: #0c5460;
        }

        .status-chip.chip-Loading.active {
            background: #17a2b8;
            color: #fff;
        }

        .status-chip.chip-OnTrip {
            background: #b8d4f9;
            color: #004085;
        }

        .status-chip.chip-OnTrip.active {
            background: #4e73df;
            color: #fff;
        }

        .status-chip.chip-TibaMuat {
            background: #ffeeba;
            color: #856404;
        }

        .status-chip.chip-TibaMuat.active {
            background: #ffc107;
            color: #212529;
        }

        .status-chip.chip-TibaBongkar {
            background: #e0cffc;
            color: #4a1d6e;
        }

        .status-chip.chip-TibaBongkar.active {
            background: #6f42c1;
            color: #fff;
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

        /* Pagination */
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
                            <i class="fas fa-truck text-primary"></i> <?= $title ?>
                        </h1>
                        <div class="d-flex flex-wrap" style="gap:6px;">
                            <?php $level = $this->session->userdata('login')['user_level'] ?? ''; ?>
                            <?php if ($level === 'superadmin'): ?>
                                <a href="<?= base_url('ftl_non_spx/terhapus') ?>" class="btn btn-warning btn-sm shadow-sm">
                                    <i class="fas fa-trash-restore"></i> Data Terhapus
                                </a>
                            <?php endif ?>
                            <a href="<?= base_url('ftl_non_spx/import') ?>" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-file-excel"></i> Import Excel
                            </a>
                            <a href="#" id="btnExportExcel" class="btn btn-sm shadow-sm"
                                style="background:#1d6f42; border-color:#1d6f42; color:#fff;">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                            <a href="<?= base_url('ftl_non_spx/export') ?>" class="btn btn-danger btn-sm shadow-sm">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                            <a href="<?= base_url('ftl_non_spx/tambah') ?>" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-plus"></i> Tambah Shipment
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

                    <!-- STATISTICS CARDS -->
                    <?php $stats = $stats ?? []; ?>
                    <div class="row mb-3">
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card status-card border-left-primary shadow h-100 py-2" data-status-filter="">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
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
                            <div class="card status-card border-left-warning shadow h-100 py-2"
                                data-status-filter="Tiba di Lokasi Muat">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Tiba Muat
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['tiba_muat'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card status-card border-left-info shadow h-100 py-2"
                                data-status-filter="Loading">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Loading</div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['loading'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card status-card border-left-primary shadow h-100 py-2"
                                data-status-filter="On Trip">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">On Trip</div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['on_trip'] ?? 0 ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl col-md-4 col-6 mb-3">
                            <div class="card status-card border-left-purple shadow h-100 py-2"
                                data-status-filter="Tiba di Lokasi Bongkar">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#6f42c1;">
                                        Tiba Bongkar</div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['tiba_bongkar'] ?? 0 ?></div>
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
                                        placeholder="No shipment, driver, nopol...">
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
                                <label class="small text-muted mb-1">Truck Type</label>
                                <select id="filterTruck" class="form-control">
                                    <option value="">Semua Tipe</option>
                                    <option>Blindvan</option>
                                    <option>L300</option>
                                    <option>CDE</option>
                                    <option>CDE Long</option>
                                    <option>CDD</option>
                                    <option>CDD Long</option>
                                    <option>Fuso</option>
                                    <option>Tronton Wingbox</option>
                                    <option>Tronton Box</option>
                                    <option>WB</option>
                                    <option>Wingbox</option>
                                    <option>Flatbed</option>
                                    <option>Reefer</option>
                                    <option>Tronton</option>
                                    <option>Trailer</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 mb-2">
                                <label class="small text-muted mb-1">Standby Date Dari</label>
                                <input type="date" id="filterDateFrom" class="form-control">
                            </div>
                            <div class="col-lg-1 col-md-4 mb-2">
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
                                <span class="status-chip chip-TibaMuat" data-status="Tiba di Lokasi Muat">Tiba
                                    Muat</span>
                                <span class="status-chip chip-Loading" data-status="Loading">Loading</span>
                                <span class="status-chip chip-OnTrip" data-status="On Trip">On Trip</span>
                                <span class="status-chip chip-TibaBongkar" data-status="Tiba di Lokasi Bongkar">Tiba
                                    Bongkar</span>
                                <span class="status-chip chip-Completed" data-status="Completed">Completed</span>
                                <span class="status-chip chip-Cancelled" data-status="Cancelled">Cancelled</span>
                            </div>
                        </div>
                    </div>

                    <!-- TABLE CARD -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-white">
                                <i class="fas fa-list"></i> Daftar Shipment FTL Non SPX
                            </h6>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-database"></i> <span id="countNumber">0</span> Shipment
                            </span>
                        </div>
                        <div class="table-wrapper">
                            <div id="loadingOverlay" style="display:none; position:absolute; top:0; left:0; right:0; bottom:0;
                                       background:rgba(255,255,255,.65); z-index:10;
                                       align-items:center; justify-content:center;">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="small text-muted mt-1">Memuat data...</div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-ftl mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="3%" class="text-center">No</th>
                                                <th width="7%" class="text-center">No Shipment</th>
                                                <th width="9%">Customer</th>
                                                <th width="10%">Origin 1 / 2</th>
                                                <th width="10%">Destination</th>
                                                <th width="5%" class="text-center">Truck</th>
                                                <th width="8%">Vendor</th>
                                                <th width="9%">Nopol / Driver</th>
                                                <th width="8%" class="text-center">Standby</th>
                                                <th width="8%" class="text-center">Arrival</th>
                                                <th width="6%" class="text-center">SLA</th>
                                                <th width="7%" class="text-center">Status</th>
                                                <th width="5%">Notes</th>
                                                <th width="5%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                            <tr>
                                                <td colspan="14" class="text-center py-4">
                                                    <div class="spinner-border spinner-border-sm text-primary"></div>
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
                                    dari <strong id="showTotal">0</strong> shipment
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

    <!-- MODAL: ASSIGN VENDOR -->
    <div class="modal fade" id="modalAssignVendor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-truck"></i> Assign Vendor — <span id="avShipmentNo"></span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small fw-bold">Vendor <span class="text-danger">*</span></label>
                        <select id="avVendorId" class="form-control">
                            <option value="">-- Pilih Vendor --</option>
                            <?php foreach ($vendors ?? [] as $v): ?>
                                <option value="<?= $v->id ?>"><?= htmlspecialchars($v->nama_vendor) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">Nopol Kendaraan <span class="text-danger">*</span></label>
                            <input type="text" id="avNopol" class="form-control text-uppercase" placeholder="B 1234 XYZ"
                                style="font-family:monospace; font-weight:600; letter-spacing:1px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">Nama Driver <span class="text-danger">*</span></label>
                            <input type="text" id="avDriver" class="form-control" placeholder="Nama lengkap driver">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">No HP Driver</label>
                        <input type="text" id="avNoHp" class="form-control" placeholder="08xxxxxxxxxx">
                    </div>
                    <div id="avDuplikasiAlert" class="alert alert-danger py-2 d-none mt-3 mb-0"></div>
                    <div class="alert alert-info py-2 small mt-3 mb-0">
                        <i class="fas fa-info-circle"></i> Status akan otomatis berubah ke <strong>Scheduled</strong>
                        setelah disimpan.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="avShipmentId">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveAssignVendor">
                        <i class="fas fa-save"></i> Simpan & Assign
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: TIMESTAMP -->
    <div class="modal fade" id="modalTimestamp" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2 text-white" id="tsModalHeader" style="background:#4e73df;">
                    <h6 class="modal-title" id="tsModalTitle"><i class="fas fa-clock"></i> Catat Waktu</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Shipment: <strong id="tsShipmentNo"></strong></p>
                    <div class="mb-2">
                        <label class="small fw-bold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" id="tsDate" class="form-control form-control-sm">
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">Jam</label>
                        <input type="time" id="tsTime" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <input type="hidden" id="tsShipmentId">
                    <input type="hidden" id="tsAksi">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm text-white" id="btnSaveTimestamp"
                        style="background:#4e73df;">
                        <i class="fas fa-check"></i> Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: UPDATE STATUS MANUAL -->
    <div class="modal fade" id="modalStatus" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-exchange-alt"></i> Ubah Status Manual</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-1">Shipment: <strong id="msShipmentNo"></strong></p>
                    <select class="form-control" id="selectStatus">
                        <option value="Sourcing Vendor">Sourcing Vendor</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Tiba di Lokasi Muat">Tiba di Lokasi Muat</option>
                        <option value="Loading">Loading</option>
                        <option value="On Trip">On Trip</option>
                        <option value="Tiba di Lokasi Bongkar">Tiba di Lokasi Bongkar</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <input type="hidden" id="msShipmentId">
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-dark btn-sm" id="btnSaveStatus">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: CANCEL -->
    <div class="modal fade" id="modalCancel" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"
                style="border-radius:10px; overflow:hidden; border:none; box-shadow:0 10px 40px rgba(0,0,0,.2);">
                <div class="modal-header py-3" style="background:linear-gradient(135deg,#dc3545,#b02a37); border:none;">
                    <div>
                        <h6 class="modal-title text-white fw-bold mb-0">
                            <i class="fas fa-ban me-2"></i>Batalkan Shipment
                        </h6>
                        <small class="text-white-50" id="cancelShipmentNoLabel"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="d-flex align-items-start p-3 mb-4"
                        style="background:#fff5f5; border-radius:8px; border-left:4px solid #dc3545;">
                        <i class="fas fa-exclamation-triangle text-danger mt-1 me-3"
                            style="font-size:1.1rem; flex-shrink:0;"></i>
                        <div>
                            <div class="fw-bold text-danger" style="font-size:0.88rem;">Tindakan ini tidak dapat
                                dibatalkan</div>
                            <div class="text-muted" style="font-size:0.82rem; margin-top:3px;">
                                Shipment akan berpindah ke status <strong>Cancelled</strong> dan alasan akan tersimpan
                                di sistem.
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-uppercase text-muted mb-2">
                            <i class="fas fa-list-ul"></i> Pilih Alasan Cepat
                        </label>
                        <div class="d-flex flex-wrap" style="gap:6px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Customer request pembatalan">Customer request</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Tidak ada vendor/unit tersedia">Tidak ada unit</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Rute berubah / Dipindah ke shipment lain">Rute berubah</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Barang belum siap / Jadwal ditunda">Barang belum siap</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Double booking / Duplikat order">Double booking</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary reason-chip"
                                data-reason="Force majeure (bencana alam, dll)">Force majeure</button>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">
                            Alasan Cancel <span class="text-danger">*</span>
                            <span class="text-muted fw-normal">(wajib diisi, min. 10 karakter)</span>
                        </label>
                        <textarea id="cancelReason" class="form-control" rows="3"
                            placeholder="Tuliskan alasan pembatalan shipment ini..." maxlength="500"
                            style="border-radius:6px; resize:none;"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-danger d-none" id="cancelReasonError">
                                <i class="fas fa-exclamation-circle"></i> Alasan cancel wajib diisi (min. 10 karakter)
                            </small>
                            <small class="text-muted ms-auto" id="cancelReasonCount">0/500</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2" style="border-top:1px solid #f0f2f5;">
                    <input type="hidden" id="cancelShipmentId">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnConfirmCancel">
                        <i class="fas fa-ban"></i> Ya, Batalkan Shipment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: DONE -->
    <div class="modal fade" id="modalDone" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"
                style="border-radius:10px; overflow:hidden; border:none; box-shadow:0 10px 40px rgba(0,0,0,.2);">
                <div class="modal-header py-3" style="background:linear-gradient(135deg,#1cc88a,#17a673); border:none;">
                    <div>
                        <h6 class="modal-title text-white fw-bold mb-0">
                            <i class="fas fa-check-circle me-2"></i>Selesaikan Shipment
                        </h6>
                        <small class="text-white-50" id="doneShipmentNoLabel"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="d-flex align-items-start p-3 mb-4"
                        style="background:#f0fdf4; border-radius:8px; border-left:4px solid #1cc88a;">
                        <i class="fas fa-truck-loading text-success mt-1 me-3"
                            style="font-size:1.1rem; flex-shrink:0;"></i>
                        <div>
                            <div class="fw-bold text-success" style="font-size:0.88rem;">Konfirmasi Shipment Selesai
                            </div>
                            <div class="text-muted" style="font-size:0.82rem; margin-top:3px;">
                                Status akan berubah ke <strong>Completed</strong>. Pastikan barang sudah diterima di
                                tujuan.
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold">Catatan Selesai <span
                                class="text-muted fw-normal">(opsional)</span></label>
                        <textarea id="doneNotes" class="form-control" rows="3"
                            placeholder="Contoh: Barang diterima lengkap, tanda terima sudah ditandatangani..."
                            maxlength="500" style="border-radius:6px; resize:none;"></textarea>
                        <small class="text-muted d-flex justify-content-end mt-1" id="doneNotesCount">0/500</small>
                    </div>
                </div>
                <div class="modal-footer py-2" style="border-top:1px solid #f0f2f5;">
                    <input type="hidden" id="doneShipmentId">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="btnConfirmDone">
                        <i class="fas fa-check-circle"></i> Ya, Shipment Selesai
                    </button>
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
            const CAN_MANUAL = ['superadmin', 'admin_operational'].includes(LEVEL);

            const CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
            let CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';

            $(document).ajaxSend(function (e, xhr, settings) {
                if (settings.type && settings.type.toUpperCase() === 'POST') {
                    if (typeof settings.data === 'string') {
                        settings.data += '&' + CSRF_NAME + '=' + CSRF_HASH;
                    } else if (typeof settings.data === 'object' && settings.data !== null) {
                        settings.data[CSRF_NAME] = CSRF_HASH;
                    } else {
                        settings.data = CSRF_NAME + '=' + CSRF_HASH;
                    }
                }
            });

            $(document).ajaxSuccess(function (e, xhr) {
                try { let r = JSON.parse(xhr.responseText); if (r && r.csrf_hash) CSRF_HASH = r.csrf_hash; } catch (err) { }
            });

            let currentPage = 1, perPage = 25;
            let filters = { keyword: '', status: '', customer_id: '', vendor_id: '', truck_type: '', date_from: '', date_to: '' };
            let searchTimeout = null;

            const STATUS_COLORS = {
                'Scheduled': 'secondary', 'Sourcing Vendor': 'dark', 'Loading': 'info',
                'On Trip': 'primary', 'Tiba di Lokasi Muat': 'warning',
                'Tiba di Lokasi Bongkar': 'purple', 'Completed': 'success', 'Cancelled': 'danger'
            };

            const FLOW_BTN = {
                'Sourcing Vendor': { label: '<i class="fas fa-truck"></i> Assign Vendor', cls: 'btn-primary', aksi: 'assign_vendor' },
                'Scheduled': { label: '<i class="fas fa-map-marker-alt"></i> Tiba Muat', cls: 'btn-warning', aksi: 'tiba_muat' },
                'Tiba di Lokasi Muat': { label: '<i class="fas fa-boxes"></i> Loading', cls: 'btn-info', aksi: 'loading' },
                'Loading': { label: '<i class="fas fa-truck-moving"></i> Depart', cls: 'btn-primary', aksi: 'depart' },
                'On Trip': { label: '<i class="fas fa-warehouse"></i> Tiba Bongkar', cls: 'btn-purple', aksi: 'tiba_bongkar' },
                'Tiba di Lokasi Bongkar': { label: '<i class="fas fa-check-circle"></i> Done', cls: 'btn-success', aksi: 'done' },
            };

            const TS_CONFIG = {
                'tiba_muat': { title: 'Tiba di Lokasi Muat', color: '#ffc107' },
                'loading': { title: 'Loading', color: '#17a2b8' },
                'depart': { title: 'Depart / On Trip', color: '#4e73df' },
                'tiba_bongkar': { title: 'Tiba di Lokasi Bongkar', color: '#6f42c1' },
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

            function buildSla(s) {
                const status = s.status_shipment;
                if (status === 'Cancelled') return '<span class="sla-badge sla-na">-</span>';
                if (status === 'Sourcing Vendor') return '<span class="sla-badge sla-sourcing text-muted">Belum assign</span>';
                if (!s.target_arrival_date) return '<span class="sla-badge sla-na">-</span>';
                const today = new Date(); today.setHours(0, 0, 0, 0);
                if (status === 'Completed') {
                    if (!s.actual_tiba_bongkar_date) return '<span class="sla-badge sla-ontime">✅ Done</span>';
                    const actual = new Date(s.actual_tiba_bongkar_date); actual.setHours(0, 0, 0, 0);
                    const target = new Date(s.target_arrival_date); target.setHours(0, 0, 0, 0);
                    if (actual <= target) return '<span class="sla-badge sla-ontime">✅ On Time</span>';
                    const diff = Math.round((actual - target) / 86400000);
                    return `<span class="sla-badge sla-late">❌ Late +${diff}h</span>`;
                }
                const target = new Date(s.target_arrival_date); target.setHours(0, 0, 0, 0);
                const diff = Math.round((target - today) / 86400000);
                if (diff < 0) return `<span class="sla-badge sla-overdue">🔴 Overdue ${Math.abs(diff)}h</span>`;
                if (diff === 0) return '<span class="sla-badge sla-today">🕐 Hari ini</span>';
                if (diff <= 2) return `<span class="sla-badge sla-close">⏳ ${diff}h lagi</span>`;
                return `<span class="sla-badge sla-ok">📅 ${diff}h lagi</span>`;
            }

            function loadData() {
                $('#loadingOverlay').css('display', 'flex');
                $.ajax({
                    url: BASE + 'ftl_non_spx/filter_ajax',
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
                        $('#tableBody').html('<tr><td colspan="14" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle"></i> Gagal memuat data! (' + xhr.status + ')</td></tr>');
                    }
                });
            }

            function renderTable(rows, offset) {
                if (!rows || rows.length === 0) {
                    $('#tableBody').html('<tr><td colspan="14" class="text-center text-muted py-5"><i class="fas fa-inbox fa-3x mb-3 d-block"></i><em>Tidak ada data shipment yang sesuai filter.</em></td></tr>');
                    return;
                }
                let html = '';
                rows.forEach(function (s, idx) {
                    let status = s.status_shipment || 'Scheduled';
                    let color = STATUS_COLORS[status] || 'secondary';
                    let no = offset + idx + 1;

                    // Origin 1 + Origin 2
                    let origin = esc(s.origin || '-') + (s.origin2 ? '<br><small class="text-muted">+ ' + esc(s.origin2) + '</small>' : '');

                    let dest = esc(s.dest1 || '-') + (s.dest2 ? '<br><small class="text-muted">→ ' + esc(s.dest2) + '</small>' : '');
                    let driver = (s.nopol ? '<strong>' + esc(s.nopol) + '</strong>' : '-')
                        + (s.driver ? '<br><small class="text-muted">' + esc(s.driver) + '</small>' : '')
                        + (s.no_hp ? '<br><small class="text-muted"><i class="fas fa-phone" style="font-size:0.65rem"></i> ' + esc(s.no_hp) + '</small>' : '');

                    html += `<tr>
                        <td class="text-center">${no}</td>
                        <td class="text-center"><span class="no-shipment">${esc(s.no_shipment || '')}</span></td>
                        <td><small>${esc(s.nama_customer || '-')}</small></td>
                        <td><small>${origin}</small></td>
                        <td><small>${dest}</small></td>
                        <td class="text-center"><span class="badge bg-dark text-white" style="font-size:0.65rem;">${esc(s.truck_type || '-')}</span></td>
                        <td><small>${esc(s.nama_vendor || '-')}</small></td>
                        <td><small>${driver}</small></td>
                        <td class="text-center">
                            <small>${fmtDate(s.target_standby_date)}</small>
                            ${s.target_standby_time ? '<br><small class="text-muted">' + s.target_standby_time.substr(0, 5) + '</small>' : ''}
                        </td>
                        <td class="text-center">
                            <small>${fmtDate(s.target_arrival_date)}</small>
                            ${s.target_arrival_time ? '<br><small class="text-muted">' + s.target_arrival_time.substr(0, 5) + '</small>' : ''}
                        </td>
                        <td class="text-center">${buildSla(s)}</td>
                        <td class="text-center"><span class="badge badge-status badge-${color}">${esc(status)}</span></td>
                        <td><small class="text-muted">${esc(s.notes || '-')}</small></td>
                        <td>${buildAksi(s, status)}</td>
                    </tr>`;
                });
                $('#tableBody').html(html);
            }

            function buildAksi(s, status) {
                if (!CAN_EDIT) return '<span class="text-muted">-</span>';
                let id = s.id, no = esc(s.no_shipment || '');
                let vid = esc(s.vendor_id || ''), npl = esc(s.nopol || ''), drv = esc(s.driver || ''), nhp = esc(s.no_hp || '');

                let html = '<div class="btn-aksi-wrap">';

                if (FLOW_BTN[status]) {
                    let btn = FLOW_BTN[status];
                    html += `<button class="btn ${btn.cls} btn-sm btn-flow"
                        data-id="${id}" data-no="${no}" data-aksi="${btn.aksi}"
                        data-vendor="${vid}" data-nopol="${npl}" data-driver="${drv}" data-nohp="${nhp}">
                        ${btn.label}</button>`;
                }

                html += '<div class="btn-icon-row">';
                html += `<a href="${BASE}ftl_non_spx/detail/${id}" class="btn btn-outline-info btn-sm" title="Detail" data-bs-toggle="tooltip"><i class="fas fa-eye"></i></a>`;
                html += `<a href="${BASE}ftl_non_spx/ubah/${id}" class="btn btn-outline-success btn-sm" title="Edit" data-bs-toggle="tooltip"><i class="fas fa-edit"></i></a>`;

                if (CAN_MANUAL && !['Completed', 'Cancelled'].includes(status)) {
                    html += `<button class="btn btn-outline-secondary btn-sm btn-manual-status"
                        data-id="${id}" data-no="${no}" data-status="${esc(status)}"
                        title="Ubah Status" data-bs-toggle="tooltip">
                        <i class="fas fa-exchange-alt"></i></button>`;
                }
                if (!['Completed', 'Cancelled'].includes(status)) {
                    html += `<button class="btn btn-outline-danger btn-sm btn-cancel"
                        data-id="${id}" data-no="${no}"
                        title="Cancel" data-bs-toggle="tooltip">
                        <i class="fas fa-ban"></i></button>`;
                }
                if (CAN_DELETE) {
                    html += `<a href="${BASE}ftl_non_spx/hapus/${id}" class="btn btn-danger btn-sm"
                        onclick="return confirm('Hapus shipment ${no}?')"
                        title="Hapus" data-bs-toggle="tooltip">
                        <i class="fas fa-trash"></i></a>`;
                }
                html += '</div></div>';
                return html;
            }

            $(document).on('mouseenter', '[data-bs-toggle="tooltip"]:not(.tooltip-init)', function () {
                $(this).addClass('tooltip-init');
                new bootstrap.Tooltip(this, { trigger: 'hover' });
                $(this).tooltip('show');
            });

            $(document).on('click', '.btn-flow', function () {
                let id = $(this).data('id');
                let no = $(this).data('no');
                let aksi = $(this).data('aksi');

                if (aksi === 'assign_vendor') {
                    $('#avShipmentId').val(id);
                    $('#avShipmentNo').text(no);
                    $('#avVendorId').val($(this).data('vendor') || '');
                    $('#avNopol').val($(this).data('nopol') || '');
                    $('#avDriver').val($(this).data('driver') || '');
                    $('#avNoHp').val($(this).data('nohp') || '');
                    new bootstrap.Modal(document.getElementById('modalAssignVendor')).show();

                } else if (aksi === 'done') {
                    $('#doneShipmentId').val(id);
                    $('#doneShipmentNoLabel').text('Shipment: ' + no);
                    $('#doneNotes').val('');
                    $('#doneNotesCount').text('0/500');
                    new bootstrap.Modal(document.getElementById('modalDone')).show();

                } else {
                    let cfg = TS_CONFIG[aksi];
                    if (!cfg) return;
                    $('#tsShipmentId').val(id);
                    $('#tsShipmentNo').text(no);
                    $('#tsAksi').val(aksi);
                    $('#tsModalTitle').html('<i class="fas fa-clock"></i> ' + cfg.title);
                    $('#tsModalHeader').css('background', cfg.color);
                    $('#btnSaveTimestamp').css('background', cfg.color);
                    let now = new Date();
                    $('#tsDate').val(now.toISOString().split('T')[0]);
                    $('#tsTime').val(now.toTimeString().substring(0, 5));
                    new bootstrap.Modal(document.getElementById('modalTimestamp')).show();
                }
            });

            $('#btnSaveAssignVendor').on('click', function () {
                if (!$('#avVendorId').val()) { alert('Vendor wajib dipilih!'); return; }
                if (!$('#avNopol').val()) { alert('Nopol Kendaraan wajib diisi!'); return; }
                if (!$('#avDriver').val()) { alert('Nama Driver wajib diisi!'); return; }

                const id = $('#avShipmentId').val(), nopol = $('#avNopol').val(), driver = $('#avDriver').val();
                $('#btnSaveAssignVendor').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengecek...');

                $.ajax({
                    url: BASE + 'ftl_non_spx/cek_duplikasi',
                    method: 'POST',
                    data: { id, nopol, driver },
                    dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        $('#btnSaveAssignVendor').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan & Assign');
                        if (res.ada_duplikasi) {
                            let pesan = '<div style="font-size:0.88rem;"><p class="fw-bold text-danger mb-2"><i class="fas fa-exclamation-triangle"></i> Duplikasi Terdeteksi!</p>';
                            if (res.pesan.nopol) pesan += '<p class="mb-1">🚛 ' + res.pesan.nopol + '</p>';
                            if (res.pesan.driver) pesan += '<p class="mb-1">👤 ' + res.pesan.driver + '</p>';
                            pesan += '<hr class="my-2"><p class="text-muted mb-0">Driver/kendaraan yang masih aktif di shipment lain tidak bisa di-assign ulang.</p></div>';
                            $('#avDuplikasiAlert').html(pesan).removeClass('d-none');
                            return;
                        }
                        $('#avDuplikasiAlert').addClass('d-none');
                        $('#btnSaveAssignVendor').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                        $.ajax({
                            url: BASE + 'ftl_non_spx/aksi_assign_vendor',
                            method: 'POST',
                            data: { id, vendor_id: $('#avVendorId').val(), nopol, driver, no_hp: $('#avNoHp').val() },
                            dataType: 'json',
                            success: function (res) {
                                if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                                $('#btnSaveAssignVendor').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan & Assign');
                                if (res.success) {
                                    bootstrap.Modal.getInstance(document.getElementById('modalAssignVendor')).hide();
                                    loadData();
                                } else { alert(res.message || 'Gagal!'); }
                            },
                            error: function (xhr) {
                                $('#btnSaveAssignVendor').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan & Assign');
                                alert('Gagal assign vendor! (' + xhr.status + ')');
                            }
                        });
                    },
                    error: function (xhr) {
                        $('#btnSaveAssignVendor').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan & Assign');
                        alert('Gagal cek duplikasi! (' + xhr.status + ')');
                    }
                });
            });

            $('#modalAssignVendor').on('show.bs.modal hidden.bs.modal', function () {
                $('#avDuplikasiAlert').addClass('d-none').html('');
            });

            $('#btnSaveTimestamp').on('click', function () {
                if (!$('#tsDate').val()) { alert('Tanggal wajib diisi!'); return; }
                doAjax(BASE + 'ftl_non_spx/aksi_timestamp', {
                    id: $('#tsShipmentId').val(), aksi: $('#tsAksi').val(),
                    date: $('#tsDate').val(), time: $('#tsTime').val(),
                }, 'modalTimestamp');
            });

            $(document).on('click', '.btn-cancel', function () {
                $('#cancelShipmentId').val($(this).data('id'));
                $('#cancelShipmentNoLabel').text('Shipment: ' + $(this).data('no'));
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
                var id = $('#cancelShipmentId').val();
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Membatalkan...');
                $.ajax({
                    url: BASE + 'ftl_non_spx/aksi_cancel',
                    method: 'POST',
                    data: { id, cancel_reason: reason },
                    dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        $('#btnConfirmCancel').prop('disabled', false).html('<i class="fas fa-ban"></i> Ya, Batalkan Shipment');
                        if (res.success) { bootstrap.Modal.getInstance(document.getElementById('modalCancel')).hide(); loadData(); }
                        else { alert(res.message || 'Gagal cancel!'); }
                    },
                    error: function (xhr) {
                        $('#btnConfirmCancel').prop('disabled', false).html('<i class="fas fa-ban"></i> Ya, Batalkan Shipment');
                        alert('Gagal koneksi ke server! (' + xhr.status + ')');
                    }
                });
            });

            $('#doneNotes').on('input', function () { $('#doneNotesCount').text($(this).val().length + '/500'); });

            $('#btnConfirmDone').on('click', function () {
                var id = $('#doneShipmentId').val();
                var notes = $('#doneNotes').val().trim();
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                $.ajax({
                    url: BASE + 'ftl_non_spx/aksi_done',
                    method: 'POST',
                    data: { id, done_notes: notes },
                    dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        $('#btnConfirmDone').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Ya, Shipment Selesai');
                        if (res.success) { bootstrap.Modal.getInstance(document.getElementById('modalDone')).hide(); loadData(); }
                        else { alert(res.message || 'Gagal!'); }
                    },
                    error: function (xhr) {
                        $('#btnConfirmDone').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Ya, Shipment Selesai');
                        alert('Gagal koneksi ke server! (' + xhr.status + ')');
                    }
                });
            });

            $(document).on('click', '.btn-manual-status', function () {
                $('#msShipmentId').val($(this).data('id'));
                $('#msShipmentNo').text($(this).data('no'));
                $('#selectStatus').val($(this).data('status'));
                new bootstrap.Modal(document.getElementById('modalStatus')).show();
            });

            $('#btnSaveStatus').on('click', function () {
                doAjax(BASE + 'ftl_non_spx/update_status', {
                    id: $('#msShipmentId').val(), status: $('#selectStatus').val(),
                }, 'modalStatus');
            });

            function doAjax(url, data, modalId) {
                $.ajax({
                    url, method: 'POST', data, dataType: 'json',
                    success: function (res) {
                        if (res.csrf_hash) CSRF_HASH = res.csrf_hash;
                        if (modalId) {
                            let el = document.getElementById(modalId);
                            if (el) bootstrap.Modal.getInstance(el)?.hide();
                        }
                        if (res.success) { loadData(); }
                        else { alert(res.message || 'Gagal!'); }
                    },
                    error: function (xhr) { alert('Gagal koneksi ke server! (' + xhr.status + ')'); }
                });
            }

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
                let pg = $(this).data('page');
                if (!pg || pg < 1) return;
                currentPage = pg; loadData();
                $('html,body').animate({ scrollTop: $('.card.shadow').offset().top - 20 }, 200);
            });

            $('#perPageSelect').on('change', function () { perPage = parseInt($(this).val()); currentPage = 1; loadData(); });

            $('#filterKeyword').on('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    filters.keyword = $('#filterKeyword').val().trim(); currentPage = 1; loadData();
                }, 500);
            });
            $('#filterCustomer').on('change', function () { filters.customer_id = $(this).val(); currentPage = 1; loadData(); });
            $('#filterVendor').on('change', function () { filters.vendor_id = $(this).val(); currentPage = 1; loadData(); });
            $('#filterTruck').on('change', function () { filters.truck_type = $(this).val(); currentPage = 1; loadData(); });
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
                filters = { keyword: '', status: '', customer_id: '', vendor_id: '', truck_type: '', date_from: '', date_to: '' };
                $('#filterKeyword,#filterCustomer,#filterVendor,#filterTruck,#filterDateFrom,#filterDateTo').val('');
                $('.status-chip').removeClass('active'); $('.status-chip.chip-all').addClass('active');
                currentPage = 1; loadData();
            });

            function updateActiveFilterCount() {
                let count = Object.values(filters).filter(v => v !== '').length;
                if (count > 0) { $('#activeFilterCount').text(count + ' aktif').show(); }
                else { $('#activeFilterCount').hide(); }
            }

            $('#avNopol').on('input', function () { $(this).val($(this).val().toUpperCase()); });

            $('#btnExportExcel').on('click', function (e) {
                e.preventDefault();
                const params = new URLSearchParams({
                    keyword: filters.keyword || '', status: filters.status || '',
                    customer_id: filters.customer_id || '', vendor_id: filters.vendor_id || '',
                    truck_type: filters.truck_type || '', date_from: filters.date_from || '', date_to: filters.date_to || '',
                });
                window.open(BASE + 'ftl_non_spx/export_excel?' + params.toString(), '_blank');
            });

            setTimeout(function () { $('.alert').fadeOut('slow'); }, 5000);
            loadData();
        });
    </script>
</body>

</html>