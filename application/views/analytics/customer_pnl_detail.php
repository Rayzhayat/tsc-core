<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title mb-0">
                            <i class="fas fa-file-invoice-dollar text-primary me-2"></i> P&L per Customer
                        </h1>
                        <small class="text-muted">Laba-rugi per customer — rute mana yang nyumbang, rute mana yang
                            ngerugiin</small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('analytics') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chart-bar me-1"></i> Analytics Dashboard
                        </a>
                        <a href="<?= base_url('customer_pnl/export?' . http_build_query($filters)) ?>"
                            class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-csv me-1"></i> Export CSV
                        </a>
                    </div>
                </div>

                <!-- Summary Cards -->
                <?php $s = $summary_stats; ?>
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #6c757d !important">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(108,117,125,.15)">
                                    <i class="fas fa-users text-secondary fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Total Customer</div>
                                    <div class="h4 mb-0 fw-bold"><?= number_format($s['total']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #1cc88a !important">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(28,200,138,.15)">
                                    <i class="fas fa-check-circle text-success fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Profitable</div>
                                    <div class="h4 mb-0 fw-bold text-success"><?= $s['profitable'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f6c23e !important">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(246,194,62,.15)">
                                    <i class="fas fa-exclamation-circle text-warning fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Margin Tipis</div>
                                    <div class="h4 mb-0 fw-bold text-warning"><?= $s['tipis'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #e74a3b !important">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(231,74,59,.15)">
                                    <i class="fas fa-times-circle text-danger fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Rugi</div>
                                    <div class="h4 mb-0 fw-bold text-danger"><?= $s['rugi'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body py-2">
                        <form method="GET" action="<?= base_url('customer_pnl') ?>" class="row g-2 align-items-end">
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">Sheet Type</label>
                                <select name="sheet_type" class="form-select form-select-sm">
                                    <option value="">Semua Sheet</option>
                                    <?php foreach ($sheet_types as $st): ?>
                                        <option value="<?= $st->sheet_type ?>" <?= $filters['sheet_type'] == $st->sheet_type ? 'selected' : '' ?>>
                                            <?= $st->sheet_type ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">Periode</label>
                                <select name="periode" class="form-select form-select-sm">
                                    <option value="">Semua Periode</option>
                                    <?php foreach ($periode_list as $p): ?>
                                        <option value="<?= $p->periode ?>" <?= $filters['periode'] == $p->periode ? 'selected' : '' ?>>
                                            <?= $p->periode ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="form-label small mb-1 fw-semibold">Status P&L</label>
                                <select name="status_pnl" class="form-select form-select-sm">
                                    <option value="">Semua Status</option>
                                    <option value="profitable" <?= $filters['status_pnl'] == 'profitable' ? 'selected' : '' ?>>🟢 Profitable</option>
                                    <option value="tipis" <?= $filters['status_pnl'] == 'tipis' ? 'selected' : '' ?>>🟡
                                        Margin Tipis</option>
                                    <option value="rugi" <?= $filters['status_pnl'] == 'rugi' ? 'selected' : '' ?>>🔴 Rugi
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 col-8">
                                <label class="form-label small mb-1 fw-semibold">Cari Customer</label>
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Nama customer..." value="<?= htmlspecialchars($filters['search']) ?>">
                            </div>
                            <div class="col-md-2 col-4">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="<?= base_url('customer_pnl') ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold">
                            <i class="fas fa-table me-2 text-primary"></i>
                            Daftar Customer
                            <span class="badge bg-secondary ms-2"><?= $s['total'] ?></span>
                        </h6>
                        <small class="text-muted">Klik customer untuk lihat breakdown rute/vendor</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0 w-100" id="customerPnlTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:36px">#</th>
                                        <th>Customer</th>
                                        <th class="text-center">Trip</th>
                                        <th class="text-center">Rute</th>
                                        <th class="text-center">Vendor</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Margin</th>
                                        <th class="text-center">Margin%</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Rows di-render via AJAX (server-side DataTables), lihat script di bawah -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-3 p-3 bg-light rounded small text-muted">
                    <strong class="text-dark">Cara baca Status P&L:</strong>
                    <span class="mx-3">🟢 <strong>Profitable</strong> — Margin % &gt; 10%</span>
                    <span class="mx-3">🟡 <strong>Tipis</strong> — Margin % 0–10%</span>
                    <span class="mx-3">🔴 <strong>Rugi</strong> — Margin % negatif</span>
                </div>

            </div>
        </div>
    </div>
</div>

<!--
    jQuery + DataTables JS/CSS sudah di-load global (partials/head untuk CSS, partials/js untuk JS
    yang posisinya di bawah view ini). Script dibungkus DOMContentLoaded + retry check agar
    aman terhadap urutan load, sama seperti pattern di Rute Profitability.
-->
<script>
document.addEventListener('DOMContentLoaded', function () {
    (function initCustomerPnlTable() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
            return setTimeout(initCustomerPnlTable, 50);
        }

        jQuery(function ($) {
            var filterParams = {
                sheet_type: <?= json_encode($filters['sheet_type']) ?>,
                periode: <?= json_encode($filters['periode']) ?>,
                status_pnl: <?= json_encode($filters['status_pnl']) ?>,
                search: <?= json_encode($filters['search']) ?>
            };

            $('#customerPnlTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= base_url('customer_pnl/ajax_list') ?>',
                    type: 'POST',
                    data: function (d) {
                        d.sheet_type  = filterParams.sheet_type;
                        d.periode     = filterParams.periode;
                        d.status_pnl  = filterParams.status_pnl;
                        d.search_bar  = filterParams.search;
                    },
                    error: function (xhr) {
                        console.error('DataTables ajax error:', xhr.status, xhr.responseText);
                    }
                },
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                columns: [
                    { data: 0, orderable: false },
                    { data: 1 },
                    { data: 2, className: 'text-center' },
                    { data: 3, className: 'text-center' },
                    { data: 4, className: 'text-center' },
                    { data: 5, className: 'text-end' },
                    { data: 6, className: 'text-end' },
                    { data: 7, className: 'text-center' },
                    { data: 8, className: 'text-center' },
                    { data: 9, orderable: false, className: 'text-center' }
                ],
                order: [[6, 'desc']],
                language: {
                    processing: "Memuat data...",
                    lengthMenu: "Tampil _MENU_ entri",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ customer",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(difilter dari _MAX_ total customer)",
                    paginate: { first: "Awal", last: "Akhir", next: "Next", previous: "Prev" },
                    zeroRecords: "Customer tidak ditemukan",
                    emptyTable: "Belum ada data customer"
                }
            });
        });
    })();
});
</script>