<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .summary-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .card-primary {
            border-left-color: #4e73df;
        }

        .card-warning {
            border-left-color: #f6c23e;
        }

        .card-success {
            border-left-color: #1cc88a;
        }

        .card-danger {
            border-left-color: #e74a3b;
        }

        .card-info {
            border-left-color: #36b9cc;
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.3;
        }

        .status-badge {
            font-size: 11px;
            padding: 4px 10px;
            font-weight: 600;
        }

        .badge-draft {
            background: #858796;
        }

        .badge-pending {
            background: #4e73df;
        }

        .badge-approved {
            background: #1cc88a;
        }

        .badge-rejected {
            background: #e74a3b;
        }

        .badge-partial_received {
            background: #f6c23e;
            color: #000;
        }

        .badge-received {
            background: #36b9cc;
        }

        .badge-completed {
            background: #1cc88a;
        }

        .badge-cancelled {
            background: #6c757d;
        }

        .overdue-row {
            background: #fff3cd !important;
        }

        .filter-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .filter-card .form-control,
        .filter-card .form-select {
            border-radius: 5px;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar') ?>

                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-shopping-cart text-primary"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('purchase_order/dashboard') ?>" class="btn btn-info btn-sm shadow-sm">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                            <a href="<?= base_url('purchase_order/tambah') ?>" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-plus"></i> Buat PO Baru
                            </a>
                        </div>
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

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card summary-card card-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total PO
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= count($purchase_orders) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-invoice stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card summary-card card-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pending Approval
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= isset($summary['pending']) ? $summary['pending']['total'] : 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card summary-card card-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Outstanding PO
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $summary['outstanding'] ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-truck stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card summary-card card-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total Nilai PO
                                            </div>
                                            <?php
                                            $total_nilai = 0;
                                            foreach ($purchase_orders as $po) {
                                                $total_nilai += $po->total_po;
                                            }
                                            ?>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_nilai, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header filter-card py-3">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-filter"></i> Filter & Pencarian
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="<?= base_url('purchase_order') ?>" id="filterForm">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="small font-weight-bold">No. PO</label>
                                        <input type="text" name="no_po" class="form-control"
                                            placeholder="Cari No. PO..."
                                            value="<?= $filters['no_po'] ?? '' ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="small font-weight-bold">Vendor</label>
                                        <select name="vendor_kode" class="form-control">
                                            <option value="">-- Semua Vendor --</option>
                                            <?php foreach ($vendors as $vendor): ?>
                                                <option value="<?= $vendor->kode ?>"
                                                    <?= (isset($filters['vendor_kode']) && $filters['vendor_kode'] == $vendor->kode) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($vendor->nama_vendor) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="small font-weight-bold">Status</label>
                                        <select name="status" class="form-control">
                                            <option value="">-- Semua Status --</option>
                                            <option value="draft" <?= (isset($filters['status']) && $filters['status'] == 'draft') ? 'selected' : '' ?>>Draft</option>
                                            <option value="pending" <?= (isset($filters['status']) && $filters['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                            <option value="approved" <?= (isset($filters['status']) && $filters['status'] == 'approved') ? 'selected' : '' ?>>Approved</option>
                                            <option value="rejected" <?= (isset($filters['status']) && $filters['status'] == 'rejected') ? 'selected' : '' ?>>Rejected</option>
                                            <option value="partial_received" <?= (isset($filters['status']) && $filters['status'] == 'partial_received') ? 'selected' : '' ?>>Partial Received</option>
                                            <option value="received" <?= (isset($filters['status']) && $filters['status'] == 'received') ? 'selected' : '' ?>>Received</option>
                                            <option value="completed" <?= (isset($filters['status']) && $filters['status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                                            <option value="cancelled" <?= (isset($filters['status']) && $filters['status'] == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="small font-weight-bold">Kategori</label>
                                        <select name="kategori" class="form-control">
                                            <option value="">-- Semua Kategori --</option>
                                            <option value="barang" <?= (isset($filters['kategori']) && $filters['kategori'] == 'barang') ? 'selected' : '' ?>>Barang</option>
                                            <option value="jasa" <?= (isset($filters['kategori']) && $filters['kategori'] == 'jasa') ? 'selected' : '' ?>>Jasa</option>
                                            <option value="aset" <?= (isset($filters['kategori']) && $filters['kategori'] == 'aset') ? 'selected' : '' ?>>Aset</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="small font-weight-bold">Tanggal Dari</label>
                                        <input type="date" name="tanggal_dari" class="form-control"
                                            value="<?= $filters['tanggal_dari'] ?? '' ?>">
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="small font-weight-bold">Tanggal Sampai</label>
                                        <input type="date" name="tanggal_sampai" class="form-control"
                                            value="<?= $filters['tanggal_sampai'] ?? '' ?>">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="small font-weight-bold">Pencarian</label>
                                        <input type="text" name="search" class="form-control"
                                            placeholder="Cari vendor, keterangan..."
                                            value="<?= $filters['search'] ?? '' ?>">
                                    </div>

                                    <div class="col-md-6 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="<?= base_url('purchase_order') ?>" class="btn btn-secondary mr-2">
                                            <i class="fas fa-redo"></i> Reset
                                        </a>
                                        <a href="<?= base_url('purchase_order/export_excel?' . http_build_query($filters)) ?>"
                                            class="btn btn-success">
                                            <i class="fas fa-file-excel"></i> Export Excel
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-table"></i> Daftar Purchase Order
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="12%">No. PO</th>
                                            <th width="10%">Tanggal</th>
                                            <th width="18%">Vendor</th>
                                            <th width="10%">Kategori</th>
                                            <th width="10%">Status</th>
                                            <th width="12%" class="text-right">Total PO</th>
                                            <th width="8%">Items</th>
                                            <th width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($purchase_orders)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <p class="mb-0">Belum ada data Purchase Order</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $no = 1;
                                            foreach ($purchase_orders as $po):
                                                // Check if overdue
                                                $is_overdue = false;
                                                if (in_array($po->status, ['approved', 'partial_received']) && $po->expected_delivery) {
                                                    $is_overdue = (strtotime($po->expected_delivery) < time());
                                                }
                                            ?>
                                                <tr class="<?= $is_overdue ? 'overdue-row' : '' ?>">
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($po->no_po) ?></strong>
                                                        <?php if ($is_overdue): ?>
                                                            <br><small class="text-danger">
                                                                <i class="fas fa-exclamation-triangle"></i> Overdue
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($po->tanggal_po)) ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($po->vendor_nama) ?></strong>
                                                        <?php if ($po->vendor_pic): ?>
                                                            <br><small class="text-muted">PIC: <?= htmlspecialchars($po->vendor_pic) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">
                                                            <?= ucfirst($po->kategori) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge status-badge badge-<?= $po->status ?>">
                                                            <?= strtoupper(str_replace('_', ' ', $po->status)) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong>Rp <?= number_format($po->total_po, 0, ',', '.') ?></strong>
                                                        <?php if ($po->total_dibayar > 0): ?>
                                                            <br><small class="text-success">
                                                                Dibayar: Rp <?= number_format($po->total_dibayar, 0, ',', '.') ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-secondary">
                                                            <?= $po->total_items ?> items
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            <a href="<?= base_url('purchase_order/detail/' . $po->id) ?>"
                                                                class="btn btn-info btn-sm"
                                                                data-toggle="tooltip"
                                                                title="Detail">
                                                                <i class="fas fa-eye"></i>
                                                            </a>

                                                            <?php if (in_array($po->status, ['draft', 'rejected'])): ?>
                                                                <a href="<?= base_url('purchase_order/ubah/' . $po->id) ?>"
                                                                    class="btn btn-warning btn-sm"
                                                                    data-toggle="tooltip"
                                                                    title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            <?php endif; ?>

                                                            <a href="<?= base_url('purchase_order/print_po/' . $po->id) ?>"
                                                                class="btn btn-danger btn-sm"
                                                                data-toggle="tooltip"
                                                                title="Print PDF"
                                                                target="_blank">
                                                                <i class="fas fa-print"></i>
                                                            </a>

                                                            <?php if (in_array($po->status, ['draft', 'rejected']) && $user_level == 'superadmin'): ?>
                                                                <a href="<?= base_url('purchase_order/hapus/' . $po->id) ?>"
                                                                    class="btn btn-danger btn-sm"
                                                                    data-toggle="tooltip"
                                                                    title="Hapus"
                                                                    onclick="return confirm('Hapus PO <?= $po->no_po ?>?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card shadow mb-4">
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-primary mb-3">
                                        <i class="fas fa-info-circle"></i> Keterangan Status
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <p class="mb-2"><span class="badge badge-draft">DRAFT</span> - Draft</p>
                                            <p class="mb-2"><span class="badge badge-pending">PENDING</span> - Menunggu Approval</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-2"><span class="badge badge-approved">APPROVED</span> - Disetujui</p>
                                            <p class="mb-2"><span class="badge badge-rejected">REJECTED</span> - Ditolak</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-2"><span class="badge badge-partial_received">PARTIAL RECEIVED</span> - Diterima Sebagian</p>
                                            <p class="mb-2"><span class="badge badge-received">RECEIVED</span> - Diterima Lengkap</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-2"><span class="badge badge-completed">COMPLETED</span> - Selesai</p>
                                            <p class="mb-2"><span class="badge badge-cancelled">CANCELLED</span> - Dibatalkan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#dataTable').DataTable({
                "pageLength": 25,
                "ordering": true,
                "info": true,
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(difilter dari _MAX_ total data)",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });

            // Tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Auto hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Console logging
            console.log('📦 Purchase Order - Index');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Total PO: <?= count($purchase_orders) ?>');
            console.log('Pending: <?= isset($summary['pending']) ? $summary['pending']['total'] : 0 ?>');
            console.log('Outstanding: <?= $summary['outstanding'] ?? 0 ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });
    </script>
</body>

</html>