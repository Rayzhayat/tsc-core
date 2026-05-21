<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* Status badges */
        .badge-status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-status-approved {
            background: #1cc88a;
            color: #fff;
        }

        .badge-status-paid {
            background: #4e73df;
            color: #fff;
        }

        .badge-status-pending {
            background: #f6c23e;
            color: #fff;
        }

        .badge-status-rejected {
            background: #e74a3b;
            color: #fff;
        }

        .badge-status-default {
            background: #858796;
            color: #fff;
        }

        /* Tipe badges */
        .badge-vendor {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-non-vendor {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: #fff;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Filter section */
        .filter-section {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .filter-btn {
            border: 2px solid #fff;
            border-radius: 25px;
            padding: 6px 16px;
            font-weight: 600;
            transition: all .25s;
            margin: 3px;
            background: transparent !important;
            color: #fff !important;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, .3);
            background: rgba(255, 255, 255, .15) !important;
            color: #fff !important;
        }

        .filter-btn.active {
            background: #fff !important;
            color: #667eea !important;
        }

        /* Summary cards */
        .summary-card {
            transition: transform .25s;
        }

        .summary-card:hover {
            transform: translateY(-4px);
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
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <h2 class="mb-0">
                            <i class="fas fa-money-bill-wave text-danger me-2"></i><?= $title ?>
                        </h2>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="<?= base_url('pengeluaran/tambah') ?>" class="btn btn-danger btn-sm">
                                <i class="fas fa-plus-circle me-1"></i>Tambah Pengeluaran
                            </a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle"
                                    data-bs-toggle="dropdown">
                                    <i class="fas fa-file-import me-1"></i>Import Excel
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('pengeluaran/import') ?>">
                                            <i class="fas fa-upload text-primary me-1"></i>Upload File Excel
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="<?= base_url('pengeluaran/download_template') ?>">
                                            <i class="fas fa-download text-success me-1"></i>Download Template
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <button onclick="exportToExcel()" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel me-1"></i>Export Excel
                            </button>
                            <button onclick="exportToPDF()" class="btn btn-secondary btn-sm">
                                <i class="fas fa-file-pdf me-1"></i>Export PDF
                            </button>
                        </div>
                    </div>

                    <!-- FLASH MESSAGES -->
                    <?php foreach (['success' => 'alert-success', 'error' => 'alert-danger', 'warning' => 'alert-warning'] as $key => $cls): ?>
                        <?php if ($msg = $this->session->flashdata($key)): ?>
                            <div class="alert <?= $cls ?> alert-dismissible fade show" role="alert">
                                <?= $msg ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif ?>
                    <?php endforeach ?>

                    <!-- DATE FILTER CARD -->
                    <div class="card shadow-sm mb-4 border-start border-danger border-3">
                        <div class="card-header bg-danger text-white py-2">
                            <h6 class="mb-0 fw-semibold"><i class="fas fa-calendar-alt me-1"></i>Filter Tanggal &
                                Pencarian</h6>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('pengeluaran') ?>" id="filterForm">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label small fw-semibold mb-1">Tanggal Mulai</label>
                                        <input type="date" name="tanggal_mulai" class="form-control form-control-sm"
                                            value="<?= htmlspecialchars($filters['tanggal_mulai'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-semibold mb-1">Tanggal Akhir</label>
                                        <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                                            value="<?= htmlspecialchars($filters['tanggal_akhir'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold mb-1">Cari
                                            (Reff/Vendor/Invoice/Biaya)</label>
                                        <input type="text" name="keyword" class="form-control form-control-sm"
                                            placeholder="Cari Reff No, Vendor, Invoice..."
                                            value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex gap-2 align-items-center flex-wrap">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-search me-1"></i>Cari
                                            </button>
                                            <a href="<?= base_url('pengeluaran') ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-redo me-1"></i>Reset
                                            </a>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="text-muted small">Tampil</span>
                                                <select name="per_page" class="form-select form-select-sm"
                                                    style="width:70px" onchange="this.form.submit()">
                                                    <?php foreach ([10, 25, 50, 100] as $opt): ?>
                                                        <option value="<?= $opt ?>" <?= $per_page == $opt ? 'selected' : '' ?>>
                                                            <?= $opt ?></option>
                                                    <?php endforeach ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TIPE FILTER (client-side) -->
                    <div class="filter-section shadow">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center text-white mb-3 mb-md-0">
                                <i class="fas fa-filter fa-2x mb-1 d-block"></i>
                                <h6 class="mb-0 fw-bold">Filter Tipe</h6>
                                <small class="opacity-75">halaman ini</small>
                            </div>
                            <div class="col-md-9 text-center">
                                <button class="btn btn-outline-light filter-btn active" data-filter="all"><i
                                        class="fas fa-list me-1"></i>Semua</button>
                                <button class="btn btn-outline-light filter-btn" data-filter="vendor"><i
                                        class="fas fa-truck me-1"></i>Vendor (V)</button>
                                <button class="btn btn-outline-light filter-btn" data-filter="non-vendor"><i
                                        class="fas fa-receipt me-1"></i>Non-Vendor (M)</button>
                                <button class="btn btn-outline-light filter-btn" data-filter="with-tagihan"><i
                                        class="fas fa-link me-1"></i>Dengan Tagihan</button>
                            </div>
                        </div>
                    </div>

                    <!-- SUMMARY CARDS -->
                    <?php
                    $total_all = 0;
                    $total_vendor = 0;
                    $total_non_vendor = 0;
                    $count_vendor = 0;
                    $count_non_vendor = 0;
                    $linked = 0;
                    foreach ($all_pengeluaran as $p) {
                        $total_all += $p->total_bayar;
                        if ($p->tagihan_id)
                            $linked++;
                        if (substr($p->reff_no, 0, 1) === 'V') {
                            $total_vendor += $p->total_bayar;
                            $count_vendor++;
                        } elseif (substr($p->reff_no, 0, 1) === 'M') {
                            $total_non_vendor += $p->total_bayar;
                            $count_non_vendor++;
                        }
                    }
                    $summary = [
                        ['label' => 'Total Semua', 'color' => 'danger', 'icon' => 'money-bill-wave', 'value' => 'Rp ' . number_format($total_all, 0, ',', '.'), 'sub' => number_format($total_rows, 0, ',', '.') . ' transaksi'],
                        ['label' => 'Vendor (V)', 'color' => 'primary', 'icon' => 'truck', 'value' => 'Rp ' . number_format($total_vendor, 0, ',', '.'), 'sub' => "$count_vendor transaksi"],
                        ['label' => 'Non-Vendor (M)', 'color' => 'warning', 'icon' => 'receipt', 'value' => 'Rp ' . number_format($total_non_vendor, 0, ',', '.'), 'sub' => "$count_non_vendor transaksi"],
                        ['label' => 'Dengan Tagihan', 'color' => 'success', 'icon' => 'link', 'value' => "$linked transaksi", 'sub' => 'dari ' . number_format($total_rows, 0, ',', '.') . ' total'],
                    ];
                    ?>
                    <div class="row mb-4">
                        <?php foreach ($summary as $s): ?>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div
                                    class="card border-start border-<?= $s['color'] ?> border-3 shadow-sm h-100 summary-card">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div
                                                    class="text-xs fw-bold text-<?= $s['color'] ?> text-uppercase mb-1 small">
                                                    <?= $s['label'] ?></div>
                                                <div class="h6 mb-0 fw-bold"><?= $s['value'] ?></div>
                                                <div class="text-muted small mt-1"><?= $s['sub'] ?></div>
                                            </div>
                                            <i class="fas fa-<?= $s['icon'] ?> fa-2x text-secondary opacity-25"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <!-- DATA TABLE -->
                    <div class="card shadow-sm">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h6 class="mb-0 fw-semibold text-danger">
                                <i class="fas fa-table me-1"></i>Data Pengeluaran
                                <span id="filterLabel" class="badge bg-secondary ms-1">Semua</span>
                                <small class="text-muted fw-normal ms-2">
                                    Hal <?= $current_page ?> dari <?= $total_pages ?> &nbsp;|&nbsp;
                                    <?= number_format((($current_page - 1) * $per_page) + 1, 0, ',', '.') ?>–<?= number_format(min($current_page * $per_page, $total_rows), 0, ',', '.') ?>
                                    dari <?= number_format($total_rows, 0, ',', '.') ?> data
                                </small>
                            </h6>
                            <button class="btn btn-sm btn-outline-danger" onclick="window.print()">
                                <i class="fas fa-print me-1"></i>Print
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm mb-0" id="dataTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="3%" class="text-center">No</th>
                                            <th width="6%">Tipe</th>
                                            <th width="7%">Reff No</th>
                                            <th width="7%">Tanggal</th>
                                            <th width="11%">Postingan Biaya</th>
                                            <th width="10%">Vendor</th>
                                            <th width="9%">No Invoice</th>
                                            <th width="7%">Bulan</th>
                                            <th width="8%" class="text-end">Nominal</th>
                                            <th width="6%" class="text-end">PPN</th>
                                            <th width="6%" class="text-end">PPH</th>
                                            <th width="9%" class="text-end">Total Bayar</th>
                                            <th width="7%" class="text-center">Status</th>
                                            <th width="7%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pengeluaran)): ?>
                                            <tr>
                                                <td colspan="14" class="text-center text-muted py-5">
                                                    <i class="fas fa-inbox fa-3x d-block mb-2 opacity-25"></i>
                                                    Tidak ada data pengeluaran.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $no = (($current_page - 1) * $per_page) + 1;
                                            foreach ($pengeluaran as $p):
                                                $tipe = substr($p->reff_no, 0, 1);
                                                if ($tipe === 'V') {
                                                    $tipe_label = '<span class="badge-vendor"><i class="fas fa-truck me-1"></i>VENDOR</span>';
                                                    $tipe_class = 'data-vendor';
                                                } else {
                                                    $tipe_label = '<span class="badge-non-vendor"><i class="fas fa-receipt me-1"></i>NON-VENDOR</span>';
                                                    $tipe_class = 'data-non-vendor';
                                                }

                                                $status_text = $p->status ?? 'Tidak Ada';
                                                $status_map = [
                                                    'approved' => ['cls' => 'approved', 'icon' => 'check-circle', 'label' => 'Approved'],
                                                    'paid' => ['cls' => 'paid', 'icon' => 'money-bill-wave', 'label' => 'Paid'],
                                                    'pending' => ['cls' => 'pending', 'icon' => 'clock', 'label' => 'Pending'],
                                                    'rejected' => ['cls' => 'rejected', 'icon' => 'times-circle', 'label' => 'Rejected'],
                                                ];
                                                $s = $status_map[strtolower($status_text)] ?? ['cls' => 'default', 'icon' => 'question-circle', 'label' => htmlspecialchars($status_text)];
                                                $status_badge = '<span class="badge-status badge-status-' . $s['cls'] . '"><i class="fas fa-' . $s['icon'] . ' me-1"></i>' . $s['label'] . '</span>';

                                                $tagihan_class = $p->tagihan_id ? 'data-with-tagihan' : '';
                                                ?>
                                                <tr class="data-row <?= $tipe_class ?> <?= $tagihan_class ?>">
                                                    <td class="text-center row-no"><?= $no++ ?></td>
                                                    <td><?= $tipe_label ?></td>
                                                    <td><strong
                                                            class="text-danger"><?= htmlspecialchars($p->reff_no) ?></strong>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($p->tanggal)) ?></td>
                                                    <td><small><?= htmlspecialchars($p->postingan_biaya) ?></small></td>
                                                    <td><?= htmlspecialchars($p->nama_vendor ?: '-') ?></td>
                                                    <td><small><?= htmlspecialchars($p->no_invoice_vendor ?: '-') ?></small>
                                                    </td>
                                                    <td><small><?= htmlspecialchars($p->bulan_shipment ?: '-') ?></small></td>
                                                    <td class="text-end"><?= number_format($p->nominal, 0, ',', '.') ?></td>
                                                    <td class="text-end text-success">
                                                        <small>+<?= number_format($p->ppn, 0, ',', '.') ?></small></td>
                                                    <td class="text-end text-danger">
                                                        <small>-<?= number_format($p->pph, 0, ',', '.') ?></small></td>
                                                    <td class="text-end row-total"><strong class="text-primary">Rp
                                                            <?= number_format($p->total_bayar, 0, ',', '.') ?></strong></td>
                                                    <td class="text-center">
                                                        <?= $status_badge ?>
                                                        <?php if ($p->tagihan_id): ?>
                                                            <br><small class="text-muted">
                                                                <i class="fas fa-link me-1"></i>
                                                                <?php
                                                                $this->load->model('M_tagihan_vendor');
                                                                $tagihan = $this->M_tagihan_vendor->get_by_id($p->tagihan_id);
                                                                if ($tagihan)
                                                                    echo htmlspecialchars($tagihan->no_invoice);
                                                                ?>
                                                            </small>
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('pengeluaran/detail/' . $p->id) ?>"
                                                            class="btn btn-info btn-sm" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($p->status === 'Pending'): ?>
                                                            <a href="<?= base_url('pengeluaran/approve/' . $p->id) ?>"
                                                                onclick="return confirm('Approve & post journal untuk <?= htmlspecialchars($p->reff_no) ?>?')"
                                                                class="btn btn-success btn-sm" title="Approve"><i
                                                                    class="fas fa-check"></i></a>
                                                            <a href="<?= base_url('pengeluaran/reject/' . $p->id) ?>"
                                                                onclick="return confirm('Reject <?= htmlspecialchars($p->reff_no) ?>?')"
                                                                class="btn btn-danger btn-sm" title="Reject"><i
                                                                    class="fas fa-times"></i></a>
                                                            <a href="<?= base_url('pengeluaran/ubah/' . $p->id) ?>"
                                                                class="btn btn-warning btn-sm" title="Edit"><i
                                                                    class="fas fa-edit"></i></a>
                                                        <?php elseif ($p->status === 'Rejected'): ?>
                                                            <span class="badge bg-secondary">Rejected</span>
                                                            <a href="<?= base_url('pengeluaran/hapus/' . $p->id) ?>"
                                                                onclick="return confirm('Hapus <?= htmlspecialchars($p->reff_no) ?>?')"
                                                                class="btn btn-danger btn-sm" title="Hapus"><i
                                                                    class="fas fa-trash"></i></a>
                                                        <?php else: ?>
                                                            <a href="<?= base_url('pengeluaran/ubah/' . $p->id) ?>"
                                                                class="btn btn-success btn-sm" title="Ubah"><i
                                                                    class="fas fa-edit"></i></a>
                                                            <a href="<?= base_url('pengeluaran/hapus/' . $p->id) ?>"
                                                                onclick="return confirmDelete('<?= htmlspecialchars($p->reff_no) ?>', <?= $p->tagihan_id ? 'true' : 'false' ?>)"
                                                                class="btn btn-danger btn-sm" title="Hapus"><i
                                                                    class="fas fa-trash"></i></a>
                                                        <?php endif ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="11" class="text-end">
                                                TOTAL HALAMAN INI (<span id="filterLabel2">Semua</span>):
                                            </th>
                                            <th class="text-end text-danger">Rp <span id="pageTotal">0</span></th>
                                            <th colspan="2" class="text-end text-muted small">
                                                Grand Total: Rp <?= number_format($total_all, 0, ',', '.') ?>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- PAGINATION -->
                        <?php if ($total_pages > 1): ?>
                            <?php
                            $base_params = http_build_query(array_filter([
                                'tanggal_mulai' => $filters['tanggal_mulai'] ?? '',
                                'tanggal_akhir' => $filters['tanggal_akhir'] ?? '',
                                'keyword' => $filters['keyword'] ?? '',
                                'per_page' => $per_page,
                            ]));
                            function page_url($base, $page)
                            {
                                return base_url('pengeluaran') . '?' . $base . '&page=' . $page;
                            }
                            $range = 2;
                            $p_start = max(1, $current_page - $range);
                            $p_end = min($total_pages, $current_page + $range);
                            ?>
                            <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <small class="text-muted">
                                    Menampilkan
                                    <strong><?= number_format((($current_page - 1) * $per_page) + 1, 0, ',', '.') ?></strong>–<strong><?= number_format(min($current_page * $per_page, $total_rows), 0, ',', '.') ?></strong>
                                    dari <strong><?= number_format($total_rows, 0, ',', '.') ?></strong> data
                                </small>

                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link" href="<?= page_url($base_params, 1) ?>">&laquo;</a>
                                        </li>
                                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link"
                                                href="<?= page_url($base_params, $current_page - 1) ?>">&lsaquo;</a>
                                        </li>

                                        <?php if ($p_start > 1): ?>
                                            <li class="page-item disabled"><span class="page-link">…</span></li>
                                        <?php endif ?>

                                        <?php for ($p = $p_start; $p <= $p_end; $p++): ?>
                                            <li class="page-item <?= $p == $current_page ? 'active' : '' ?>">
                                                <a class="page-link" href="<?= page_url($base_params, $p) ?>"><?= $p ?></a>
                                            </li>
                                        <?php endfor ?>

                                        <?php if ($p_end < $total_pages): ?>
                                            <li class="page-item disabled"><span class="page-link">…</span></li>
                                        <?php endif ?>

                                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                            <a class="page-link"
                                                href="<?= page_url($base_params, $current_page + 1) ?>">&rsaquo;</a>
                                        </li>
                                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                            <a class="page-link"
                                                href="<?= page_url($base_params, $total_pages) ?>">&raquo;</a>
                                        </li>
                                    </ul>
                                </nav>

                                <form method="get" action="<?= base_url('pengeluaran') ?>"
                                    class="d-flex align-items-center gap-1">
                                    <input type="hidden" name="tanggal_mulai"
                                        value="<?= htmlspecialchars($filters['tanggal_mulai'] ?? '') ?>">
                                    <input type="hidden" name="tanggal_akhir"
                                        value="<?= htmlspecialchars($filters['tanggal_akhir'] ?? '') ?>">
                                    <input type="hidden" name="keyword"
                                        value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
                                    <input type="hidden" name="per_page" value="<?= $per_page ?>">
                                    <input type="number" name="page" class="form-control form-control-sm" style="width:65px"
                                        min="1" max="<?= $total_pages ?>" placeholder="Hal" value="<?= $current_page ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Go</button>
                                </form>
                            </div>
                        <?php endif ?>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {
            recalcTotal();

            $('.filter-btn').on('click', function () {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');

                const filter = $(this).data('filter');
                const labels = { all: 'Semua', vendor: 'Vendor (V)', 'non-vendor': 'Non-Vendor (M)', 'with-tagihan': 'Dengan Tagihan' };

                $('.data-row').hide();
                filter === 'all' ? $('.data-row').show() : $('.data-row.data-' + filter).show();

                const label = labels[filter] || 'Semua';
                $('#filterLabel, #filterLabel2').text(label);

                let no = <?= (($current_page - 1) * $per_page) + 1 ?>;
                $('.data-row:visible').each(function () { $(this).find('.row-no').text(no++); });

                recalcTotal();

                if ($('.data-row:visible').length === 0) {
                    if (!$('#noDataRow').length) {
                        $('#dataTable tbody').append('<tr id="noDataRow"><td colspan="14" class="text-center text-muted py-4"><em>Tidak ada data untuk filter ini.</em></td></tr>');
                    }
                } else {
                    $('#noDataRow').remove();
                }
            });

            function recalcTotal() {
                let total = 0;
                $('.data-row:visible').each(function () {
                    total += parseInt($(this).find('.row-total').text().replace(/[^\d]/g, '')) || 0;
                });
                $('#pageTotal').text(total.toLocaleString('id-ID'));
            }
        });

        function confirmDelete(reffNo, hasTagihan) {
            let msg = 'Yakin hapus pengeluaran Reff: ' + reffNo + '?';
            if (hasTagihan) msg += '\n\n⚠️ Terhubung dengan tagihan. Status tagihan akan kembali ke "Waiting Payment".';
            return confirm(msg);
        }

        function exportToExcel() {
            const params = new URLSearchParams(window.location.search);
            params.delete('page');
            window.location.href = '<?= base_url('pengeluaran/export_excel') ?>?' + params.toString();
        }

        function exportToPDF() {
            const params = new URLSearchParams(window.location.search);
            params.delete('page');
            window.open('<?= base_url('pengeluaran/export_pdf') ?>?' + params.toString(), '_blank');
        }
    </script>
</body>

</html>