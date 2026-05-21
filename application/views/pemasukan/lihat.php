<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <link href="<?= base_url('sb-admin/vendor/datatables/dataTables.bootstrap4.min.css') ?>" rel="stylesheet">
    <style>
        .badge-tagihan-linked {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 11px;
        }

        .badge-tagihan-none {
            background-color: #6c757d;
            color: white;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 11px;
        }

        .badge-customer {
            background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-revenue {
            background: linear-gradient(135deg, #36b9cc 0%, #2c9faf 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fc;
        }

        .filter-section {
            background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .filter-btn {
            border: 2px solid white;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: bold;
            transition: all 0.3s;
            margin: 5px;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .filter-btn.active {
            background: white !important;
            color: #1cc88a !important;
        }

        .summary-card {
            transition: transform 0.3s;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .date-filter-card {
            border-left: 4px solid #1cc88a;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-hand-holding-usd text-success"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('pemasukan/tambah') ?>" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-plus-circle"></i> Tambah Pemasukan
                            </a>
                            <button onclick="exportToExcel()" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button onclick="exportToPDF()" class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
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

                    <!-- ✅ DATE FILTER SECTION -->
                    <div class="card shadow mb-4 date-filter-card">
                        <div class="card-header py-3 bg-success text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-calendar-alt"></i> Filter Tanggal & Pencarian
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('pemasukan') ?>" id="filterForm">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="small font-weight-bold">Tanggal Mulai</label>
                                        <input type="date" name="tanggal_mulai" class="form-control"
                                            value="<?= $this->input->get('tanggal_mulai') ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="small font-weight-bold">Tanggal Akhir</label>
                                        <input type="date" name="tanggal_akhir" class="form-control"
                                            value="<?= $this->input->get('tanggal_akhir') ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="small font-weight-bold">Cari (Reff/Customer/Invoice/Jenis)</label>
                                        <input type="text" name="keyword" class="form-control"
                                            placeholder="Cari Reff No, Customer, Invoice, Jenis..."
                                            value="<?= $this->input->get('keyword') ?>">
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="small font-weight-bold">&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-success btn-block">
                                                <i class="fas fa-search"></i> Cari
                                            </button>
                                            <a href="<?= base_url('pemasukan') ?>"
                                                class="btn btn-secondary btn-block mt-2">
                                                <i class="fas fa-redo"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- CLIENT-SIDE FILTER SECTION -->
                    <div class="filter-section shadow-lg">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center text-white mb-3 mb-md-0">
                                <i class="fas fa-filter fa-3x mb-2"></i>
                                <h5 class="mb-0 font-weight-bold">Filter Tipe</h5>
                                <small>(Client-side)</small>
                            </div>
                            <div class="col-md-9 text-center">
                                <button class="btn btn-light filter-btn active" data-filter="all">
                                    <i class="fas fa-list"></i> Semua
                                </button>
                                <button class="btn btn-light filter-btn" data-filter="customer">
                                    <i class="fas fa-user"></i> Customer (C)
                                </button>
                                <button class="btn btn-light filter-btn" data-filter="lainnya">
                                    <i class="fas fa-hand-holding-usd"></i> Lainnya (R)
                                </button>
                                <button class="btn btn-light filter-btn" data-filter="with-tagihan">
                                    <i class="fas fa-link"></i> Dengan Tagihan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <?php
                        $total_all = 0;
                        $total_customer = 0;
                        $total_lainnya = 0;
                        $count_customer = 0;
                        $count_lainnya = 0;
                        $linked = 0;

                        foreach ($pemasukan as $p) {
                            $total_all += $p->total_diterima;
                            if ($p->tagihan_id)
                                $linked++;

                            if (substr($p->reff_no, 0, 1) === 'C') {
                                $total_customer += $p->total_diterima;
                                $count_customer++;
                            } else if (substr($p->reff_no, 0, 1) === 'R') {
                                $total_lainnya += $p->total_diterima;
                                $count_lainnya++;
                            }
                        }
                        ?>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 summary-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total Pemasukan
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_all, 0, ',', '.') ?>
                                            </div>
                                            <div class="text-xs text-muted mt-1">
                                                <?= count($pemasukan) ?> transaksi
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 summary-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Customer (C)
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_customer, 0, ',', '.') ?>
                                            </div>
                                            <div class="text-xs text-muted mt-1">
                                                <?= $count_customer ?> transaksi
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2 summary-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Lainnya (R)
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_lainnya, 0, ',', '.') ?>
                                            </div>
                                            <div class="text-xs text-muted mt-1">
                                                <?= $count_lainnya ?> transaksi
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 summary-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Dari Tagihan
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $linked ?> transaksi
                                            </div>
                                            <div class="text-xs text-muted mt-1">
                                                dari <?= count($pemasukan) ?> total
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-link fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-gradient-success">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-table"></i> Daftar Pemasukan
                                        <span id="filterLabel" class="badge badge-light ml-2 text-success">Semua</span>
                                    </h6>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button class="btn btn-sm btn-light" onclick="window.print()">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%"
                                    cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="3%" class="text-center">No</th>
                                            <th width="6%">Tipe</th>
                                            <th width="7%">Reff No</th>
                                            <th width="8%">Tanggal</th>
                                            <th width="10%">Jenis</th>
                                            <th width="12%">Customer</th>
                                            <th width="10%">No Invoice</th>
                                            <th width="10%" class="text-right">Nominal</th>
                                            <th width="7%" class="text-right">PPN</th>
                                            <th width="7%" class="text-right">PPH</th>
                                            <th width="10%" class="text-right">Total Diterima</th>
                                            <th width="5%" class="text-center">Status</th>
                                            <th width="5%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pemasukan)): ?>
                                            <tr>
                                                <td colspan="13" class="text-center text-muted">
                                                    <em><i class="fas fa-inbox fa-2x mb-2"></i><br>Tidak ada data
                                                        pemasukan.</em>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $no = 1;
                                            foreach ($pemasukan as $p):
                                                $tipe = substr($p->reff_no, 0, 1);
                                                $tipe_label = '';
                                                $tipe_class = '';

                                                if ($tipe === 'C') {
                                                    $tipe_label = '<span class="badge-customer"><i class="fas fa-user"></i> CUSTOMER</span>';
                                                    $tipe_class = 'data-customer';
                                                } else if ($tipe === 'R') {
                                                    $tipe_label = '<span class="badge-revenue"><i class="fas fa-hand-holding-usd"></i> LAINNYA</span>';
                                                    $tipe_class = 'data-lainnya';
                                                }

                                                $tagihan_badge = '<span class="badge-tagihan-none"><i class="fas fa-minus-circle"></i></span>';
                                                $tagihan_class = '';
                                                if ($p->tagihan_id) {
                                                    $tagihan_badge = '<span class="badge-tagihan-linked" title="Terhubung dengan tagihan"><i class="fas fa-check-circle"></i></span>';
                                                    $tagihan_class = 'data-with-tagihan';
                                                }
                                                ?>
                                                <tr class="data-row <?= $tipe_class ?> <?= $tagihan_class ?>">
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><?= $tipe_label ?></td>
                                                    <td><strong
                                                            class="text-success"><?= htmlspecialchars($p->reff_no) ?></strong>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($p->tanggal)) ?></td>
                                                    <td><small><?= htmlspecialchars($p->jenis_penerimaan) ?></small></td>
                                                    <td><?= htmlspecialchars($p->nama_customer ?: '-') ?></td>
                                                    <td><small><?= htmlspecialchars($p->no_invoice_cust ?: '-') ?></small></td>
                                                    <td class="text-right"><?= number_format($p->nominal, 0, ',', '.') ?></td>
                                                    <td class="text-right text-success">
                                                        <small>+<?= number_format($p->ppn, 0, ',', '.') ?></small></td>
                                                    <td class="text-right text-danger">
                                                        <small>-<?= number_format($p->pph, 0, ',', '.') ?></small></td>
                                                    <td class="text-right"><strong class="text-success">Rp
                                                            <?= number_format($p->total_diterima, 0, ',', '.') ?></strong></td>
                                                    <td class="text-center"><?= $tagihan_badge ?></td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="<?= base_url('pemasukan/ubah/' . $p->id) ?>"
                                                                class="btn btn-warning" title="Ubah">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a onclick="return confirmDelete('<?= htmlspecialchars($p->reff_no) ?>', <?= $p->tagihan_id ? 'true' : 'false' ?>)"
                                                                href="<?= base_url('pemasukan/hapus/' . $p->id) ?>"
                                                                class="btn btn-danger" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="10" class="text-right">GRAND TOTAL (<span
                                                    id="totalLabel">Semua</span>):</th>
                                            <th class="text-right text-success">
                                                <span id="grandTotal">Rp
                                                    <?= number_format($total_all, 0, ',', '.') ?></span>
                                            </th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="<?= base_url('sb-admin/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('sb-admin/vendor/datatables/dataTables.bootstrap4.min.js') ?>"></script>

    <script>
        $(document).ready(function () {
            // CLIENT-SIDE FILTER
            $('.filter-btn').on('click', function () {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');

                const filter = $(this).data('filter');
                let visibleRows = [];
                let filterLabelText = 'Semua';
                let totalLabelText = 'Semua';

                $('.data-row').hide();

                if (filter === 'all') {
                    $('.data-row').show();
                    visibleRows = $('.data-row');
                    filterLabelText = 'Semua';
                    totalLabelText = 'Semua';
                } else if (filter === 'customer') {
                    $('.data-customer').show();
                    visibleRows = $('.data-customer');
                    filterLabelText = 'Customer (C)';
                    totalLabelText = 'Customer';
                } else if (filter === 'lainnya') {
                    $('.data-lainnya').show();
                    visibleRows = $('.data-lainnya');
                    filterLabelText = 'Lainnya (R)';
                    totalLabelText = 'Lainnya';
                } else if (filter === 'with-tagihan') {
                    $('.data-with-tagihan').show();
                    visibleRows = $('.data-with-tagihan');
                    filterLabelText = 'Dengan Tagihan';
                    totalLabelText = 'Dengan Tagihan';
                }

                $('#filterLabel').text(filterLabelText);
                $('#totalLabel').text(totalLabelText);

                let no = 1;
                visibleRows.each(function () {
                    $(this).find('td:first').text(no++);
                });

                let filteredTotal = 0;
                visibleRows.each(function () {
                    const totalText = $(this).find('td:nth-last-child(3)').text();
                    const cleanTotal = totalText.replace(/[^\d]/g, '');
                    filteredTotal += parseInt(cleanTotal) || 0;
                });

                $('#grandTotal').text('Rp ' + formatNumber(filteredTotal));

                if (visibleRows.length === 0) {
                    const tbody = $('#dataTable tbody');
                    tbody.append('<tr class="no-data-row"><td colspan="13" class="text-center text-muted"><em><i class="fas fa-search fa-2x mb-2"></i><br>Tidak ada data untuk filter ini.</em></td></tr>');
                } else {
                    $('.no-data-row').remove();
                }
            });

            // Auto hide alert
            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 5000);
        });

        function confirmDelete(reffNo, hasTagihan) {
            let message = 'Yakin hapus pemasukan dengan Reff: ' + reffNo + '?';

            if (hasTagihan) {
                message += '\n\n⚠️ PERHATIAN: Pemasukan ini terhubung dengan tagihan.\nJika dihapus, status tagihan akan kembali menjadi "Waiting Payment".';
            }

            return confirm(message);
        }

        // ✅ REAL EXPORT FUNCTIONS
        function exportToExcel() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = '<?= base_url('pemasukan/export_excel') ?>?' + params.toString();
        }

        function exportToPDF() {
            const params = new URLSearchParams(window.location.search);
            window.open('<?= base_url('pemasukan/export_pdf') ?>?' + params.toString(), '_blank');
        }

        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>
</body>

</html>