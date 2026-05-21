<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>
        .badge-waiting { background-color: #ffc107; color: #000; }
        .badge-paid { background-color: #28a745; color: #fff; }
        .table-hover tbody tr:hover { background-color: #f8f9fc; }
        .select2-container { width: 100% !important; }
        .info-card { border-left: 4px solid; }
        .info-card.primary { border-color: #4e73df; }
        .info-card.warning { border-color: #f6c23e; }
        .info-card.info { border-color: #36b9cc; }
        .info-card.success { border-color: #1cc88a; }
    </style>
</head>
<body id="page-top" class="fixed-nav">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar') ?>
                <div class="container-fluid">
                    
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-truck-moving text-primary"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('transaksi_order/tambah') ?>" class="btn btn-primary btn-sm shadow-sm">
                            <i class="fas fa-plus-circle"></i> Tambah Transaksi Order
                        </a>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Info Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card info-card primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Transaksi
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= count($orders) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card info-card warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Waiting Customer
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= count(array_filter($orders, function($o) { 
                                                    return $o->status_payment_customer == 'Waiting Payment'; 
                                                })) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card info-card info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Waiting Vendor
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= count(array_filter($orders, function($o) { 
                                                    return $o->status_payment_vendor == 'Waiting Payment'; 
                                                })) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card info-card success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Transaksi Selesai
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= count(array_filter($orders, function($o) { 
                                                    return $o->status_payment_customer == 'Paid' && $o->status_payment_vendor == 'Paid'; 
                                                })) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter & Search Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-filter"></i> Filter & Pencarian
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="filterForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><i class="fas fa-search"></i> Cari Kode Order / Customer</label>
                                            <input type="text" id="keyword" class="form-control" placeholder="Ketik kode order atau nama customer...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><i class="fas fa-calendar"></i> Bulan Shipment</label>
                                            <select id="bulan_shipment" class="form-control">
                                                <option value="">- Semua Bulan -</option>
                                                <option value="Januari">Januari</option>
                                                <option value="Februari">Februari</option>
                                                <option value="Maret">Maret</option>
                                                <option value="April">April</option>
                                                <option value="Mei">Mei</option>
                                                <option value="Juni">Juni</option>
                                                <option value="Juli">Juli</option>
                                                <option value="Agustus">Agustus</option>
                                                <option value="September">September</option>
                                                <option value="Oktober">Oktober</option>
                                                <option value="November">November</option>
                                                <option value="Desember">Desember</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><i class="fas fa-info-circle"></i> Status Payment</label>
                                            <select id="status_payment" class="form-control">
                                                <option value="">- Semua Status -</option>
                                                <option value="Waiting Payment">Waiting Payment</option>
                                                <option value="Paid">Paid</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><i class="fas fa-list"></i> Tampilkan</label>
                                            <select id="limit" class="form-control">
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="all">Semua</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button type="button" id="btnFilter" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <button type="button" id="btnReset" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Data Table Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-white">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-table"></i> Data Transaksi Order / Ritase
                                    </h6>
                                </div>
                                <div class="col-md-6 text-right">
                                    <span class="text-muted" id="info-data">
                                        Menampilkan <strong id="start"><?= count($orders) > 0 ? 1 : 0 ?></strong> - 
                                        <strong id="end"><?= count($orders) ?></strong> dari 
                                        <strong id="total"><?= count($orders) ?></strong> data
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="3%" class="text-center">No</th>
                                            <th width="10%">Kode Order</th>
                                            <th width="12%">Customer</th>
                                            <th width="8%">Tanggal</th>
                                            <th width="8%">Bulan Ship</th>
                                            <th width="10%">No Invoice</th>
                                            <th width="10%" class="text-right">Nominal</th>
                                            <th width="9%" class="text-center">Status Customer</th>
                                            <th width="9%" class="text-center">Status Vendor</th>
                                            <th width="9%">Reff Vendor</th>
                                            <th width="12%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-body">
                                        <?php if (empty($orders)): ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-muted">
                                                    <em><i class="fas fa-inbox"></i> Tidak ada data transaksi order.</em>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; foreach ($orders as $order): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><strong class="text-primary"><?= htmlspecialchars($order->kode_order) ?></strong></td>
                                                    <td><?= htmlspecialchars($order->nama_customer ?? '-') ?></td>
                                                    <td><?= date('d/m/Y', strtotime($order->tanggal_order)) ?></td>
                                                    <td><?= $order->bulan_shipment ?: '<em class="text-muted">—</em>' ?></td>
                                                    <td><?= htmlspecialchars($order->no_invoice_customer ?? '-') ?></td>
                                                    <td class="text-right"><strong>Rp <?= number_format($order->nominal_payment, 0, ',', '.') ?></strong></td>
                                                    <td class="text-center">
                                                        <?php if ($order->status_payment_customer == 'Waiting Payment'): ?>
                                                            <span class="badge badge-waiting">
                                                                <i class="fas fa-clock"></i> Waiting
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-paid">
                                                                <i class="fas fa-check"></i> Paid
                                                            </span>
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($order->status_payment_vendor == 'Waiting Payment'): ?>
                                                            <span class="badge badge-warning">
                                                                <i class="fas fa-hourglass-half"></i> Waiting
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-paid">
                                                                <i class="fas fa-check-circle"></i> Paid
                                                            </span>
                                                        <?php endif ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($order->reff_payment_vendor): ?>
                                                            <span class="badge badge-info"><?= htmlspecialchars($order->reff_payment_vendor) ?></span>
                                                        <?php else: ?>
                                                            <em class="text-muted">—</em>
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            <!-- Detail Modal -->
                                                            <button type="button" class="btn btn-info btn-sm" 
                                                                    data-toggle="modal" 
                                                                    data-target="#detailModal<?= $order->id ?>"
                                                                    title="Detail">
                                                                <i class="fas fa-eye"></i>
                                                            </button>

                                                            <!-- Terima pembayaran Customer -->
                                                            <?php if ($order->status_payment_customer == 'Waiting Payment'): ?>
                                                                <a href="<?= base_url('transaksi_order/terima_pembayaran/' . $order->id) ?>" 
                                                                class="btn btn-success btn-sm"
                                                                title="Terima Pembayaran Customer">
                                                                    <i class="fas fa-hand-holding-usd"></i>
                                                                </a>
                                                            <?php endif ?>

                                                            <!-- Bayar ke Vendor (jika belum paid) -->
                                                            <?php if ($order->status_payment_vendor == 'Waiting Payment'): ?>
                                                                <a href="<?= base_url('transaksi_order/bayar_vendor/' . $order->id) ?>" 
                                                                   class="btn btn-success btn-sm"
                                                                   title="Bayar ke Vendor">
                                                                    <i class="fas fa-money-bill-wave"></i>
                                                                </a>
                                                            <?php endif ?>

                                                            <!-- Edit -->
                                                            <a href="<?= base_url('transaksi_order/ubah/' . $order->id) ?>" 
                                                               class="btn btn-warning btn-sm" 
                                                               title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>

                                                            <!-- Delete -->
                                                            <a onclick="return confirm('Yakin hapus order <?= htmlspecialchars($order->kode_order) ?>?')" 
                                                               href="<?= base_url('transaksi_order/hapus/' . $order->id) ?>" 
                                                               class="btn btn-danger btn-sm" 
                                                               title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Modal Detail -->
                                                <div class="modal fade" id="detailModal<?= $order->id ?>" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">
                                                                    <i class="fas fa-info-circle"></i> Detail Transaksi Order
                                                                </h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal">
                                                                    <span>&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <table class="table table-sm table-borderless">
                                                                            <tr>
                                                                                <th width="45%">Kode Order</th>
                                                                                <td><strong class="text-primary"><?= htmlspecialchars($order->kode_order) ?></strong></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Customer</th>
                                                                                <td><?= htmlspecialchars($order->nama_customer ?? '-') ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Tanggal Order</th>
                                                                                <td><?= date('d/m/Y', strtotime($order->tanggal_order)) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Bulan Shipment</th>
                                                                                <td><?= $order->bulan_shipment ?: '-' ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>No Invoice Customer</th>
                                                                                <td><?= htmlspecialchars($order->no_invoice_customer ?? '-') ?></td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <table class="table table-sm table-borderless">
                                                                            <tr>
                                                                                <th width="45%">Nominal Payment</th>
                                                                                <td><strong>Rp <?= number_format($order->nominal_payment, 0, ',', '.') ?></strong></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Status Customer</th>
                                                                                <td>
                                                                                    <?php if ($order->status_payment_customer == 'Paid'): ?>
                                                                                        <span class="badge badge-paid">Paid</span>
                                                                                    <?php else: ?>
                                                                                        <span class="badge badge-waiting">Waiting Payment</span>
                                                                                    <?php endif ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Status Vendor</th>
                                                                                <td>
                                                                                    <?php if ($order->status_payment_vendor == 'Paid'): ?>
                                                                                        <span class="badge badge-paid">Paid</span>
                                                                                    <?php else: ?>
                                                                                        <span class="badge badge-warning">Waiting Payment</span>
                                                                                    <?php endif ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Reff Payment Vendor</th>
                                                                                <td>
                                                                                    <?php if ($order->reff_payment_vendor): ?>
                                                                                        <span class="badge badge-info"><?= htmlspecialchars($order->reff_payment_vendor) ?></span>
                                                                                    <?php else: ?>
                                                                                        <span class="text-muted">Belum ada pembayaran</span>
                                                                                    <?php endif ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Created By</th>
                                                                                <td><?= $order->created_by ?? '-' ?></td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                </div>

                                                                <?php if (isset($order->tagihan_no_invoice) && $order->tagihan_no_invoice): ?>
                                                                    <hr>
                                                                    <div class="alert alert-info mb-0">
                                                                        <strong><i class="fas fa-link"></i> Relasi Tagihan Vendor:</strong><br>
                                                                        No Invoice: <strong><?= htmlspecialchars($order->tagihan_no_invoice) ?></strong>
                                                                    </div>
                                                                <?php endif ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </tbody>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Auto hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Filter button (optional - untuk nanti kalo mau pake AJAX)
            $('#btnFilter').on('click', function() {
                alert('Filter akan diimplementasi dengan AJAX jika diperlukan');
            });

            // Reset button
            $('#btnReset').on('click', function() {
                $('#filterForm')[0].reset();
            });

            // Enter di keyword
            $('#keyword').on('keypress', function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    $('#btnFilter').click();
                }
            });
        });
    </script>
</body>
</html>