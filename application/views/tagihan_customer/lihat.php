<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .summary-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }
        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15)!important;
        }
        .filter-card {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
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
                            <i class="fas fa-file-invoice text-success"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('tagihan_customer/tambah') ?>" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-plus"></i> Tambah Tagihan
                            </a>
                            <button onclick="exportExcel()" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button onclick="exportPDF()" class="btn btn-danger btn-sm shadow-sm">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </div>
                    </div>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif ?>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card summary-card border-left-primary shadow h-100 py-2" style="border-left-color: #4e73df;">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Tagihan
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($summary['total'] ?? 0, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card summary-card border-left-warning shadow h-100 py-2" style="border-left-color: #f6c23e;">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Belum Bayar
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($summary['belum_bayar'] ?? 0, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card summary-card border-left-success shadow h-100 py-2" style="border-left-color: #1cc88a;">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Sudah Bayar
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($summary['sudah_bayar'] ?? 0, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card summary-card border-left-info shadow h-100 py-2" style="border-left-color: #36b9cc;">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Jumlah Invoice
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                <?= number_format($summary['count'] ?? 0, 0, ',', '.') ?> invoice
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 filter-card">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-filter"></i> Filter & Pencarian
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('tagihan_customer') ?>" id="filterForm">
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
                                    <div class="col-md-3 mb-3">
                                        <label class="small font-weight-bold">Status Pembayaran</label>
                                        <select name="status" class="form-control">
                                            <option value="">Semua Status</option>
                                            <option value="belum_bayar" <?= $this->input->get('status') == 'belum_bayar' ? 'selected' : '' ?>>
                                                Belum Bayar
                                            </option>
                                            <option value="partial" <?= $this->input->get('status') == 'partial' ? 'selected' : '' ?>>
                                                Pembayaran Sebagian
                                            </option>
                                            <option value="lunas" <?= $this->input->get('status') == 'lunas' ? 'selected' : '' ?>>
                                                Lunas
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="small font-weight-bold">Cari Invoice/Customer</label>
                                        <input type="text" name="keyword" class="form-control" 
                                               placeholder="No Invoice atau Nama Customer"
                                               value="<?= $this->input->get('keyword') ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Cari
                                        </button>
                                        <a href="<?= base_url('tagihan_vendor') ?>" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i> Reset
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
                                Daftar Tagihan Customer
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="12%">Tanggal</th>
                                            <th width="15%">No Invoice</th>
                                            <th width="25%">Customer</th>
                                            <th width="13%">Nominal</th>
                                            <th width="13%">Total</th>
                                            <th width="12%">Status</th>
                                            <th width="5%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($tagihan)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>Tidak ada data tagihan customer</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php 
                                            $no = 1;
                                            foreach ($tagihan as $item): 
                                                $status_badge = '';
                                                $status_text = '';
                                                
                                                if ($item->status_payment == 'Paid') {
                                                    $status_badge = 'success';
                                                    $status_text = 'Lunas';
                                                } elseif ($item->status_payment == 'Waiting Payment') {
                                                    $status_badge = 'danger';
                                                    $status_text = 'Belum Bayar';
                                                } elseif ($item->status_payment == 'Partial Payment') {
                                                    $status_badge = 'warning';
                                                    $status_text = 'Sebagian';
                                                } else {
                                                    $status_badge = 'secondary';
                                                    $status_text = $item->status_payment;
                                                }
                                            ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= date('d/m/Y', strtotime($item->tanggal)) ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($item->no_invoice) ?></strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($item->customer_nama ?? $item->nama_customer) ?></td>
                                                    <td class="text-right">
                                                        Rp <?= number_format($item->nominal, 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong>Rp <?= number_format($item->total_tagihan, 0, ',', '.') ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= $status_badge ?>"><?= $status_text ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('tagihan_customer/detail/' . $item->id) ?>" 
                                                           class="btn btn-info btn-sm" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($item->status_payment != 'Paid'): ?>
                                                            <a href="<?= base_url('tagihan_customer/ubah/' . $item->id) ?>" 
                                                               class="btn btn-warning btn-sm" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif ?>
                                                        <a href="<?= base_url('tagihan_customer/hapus/' . $item->id) ?>" 
                                                           onclick="return confirm('Yakin hapus tagihan ini?')"
                                                           class="btn btn-danger btn-sm" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
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
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    
    <script>
        function exportExcel() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = '<?= base_url('tagihan_customer/export_excel') ?>?' + params.toString();
        }
        
        function exportPDF() {
            const params = new URLSearchParams(window.location.search);
            window.open('<?= base_url('tagihan_customer/export_pdf') ?>?' + params.toString(), '_blank');
        }
    </script>
</body>
</html>