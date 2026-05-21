<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
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
                            <i class="fas fa-plus-circle text-primary"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('transaksi_order') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Form Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-edit"></i> Form Tambah Transaksi Order
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('transaksi_order/proses_tambah') ?>" method="POST" id="formTambah">
                                
                                <div class="row">
                                    <!-- Kode Order (Auto Generate) -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-barcode text-primary"></i> Kode Order <span class="text-danger">*</span></label>
                                            <input type="text" name="kode_order" id="kode_order" class="form-control bg-light" readonly value="Auto Generate">
                                            <small class="text-muted">Kode akan digenerate otomatis saat disimpan</small>
                                        </div>
                                    </div>

                                    <!-- Tanggal Order -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-calendar text-primary"></i> Tanggal Order <span class="text-danger">*</span></label>
                                            <input type="date" name="tanggal_order" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Customer -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-user text-primary"></i> Customer <span class="text-danger">*</span></label>
                                            <select name="customer_id" id="customer_id" class="form-control select2" required>
                                                <option value="">- Pilih Customer -</option>
                                                <?php foreach ($customers as $customer): ?>
                                                    <option value="<?= $customer->id ?>">
                                                        <?= htmlspecialchars($customer->nama) ?>
                                                    </option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- No Invoice Customer -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-file-invoice text-primary"></i> No Invoice Customer <span class="text-danger">*</span></label>
                                            <input type="text" name="no_invoice_customer" class="form-control" placeholder="Contoh: INV-CUST-001" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Bulan Shipment -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-calendar-alt text-primary"></i> Bulan Shipment</label>
                                            <select name="bulan_shipment" class="form-control">
                                                <option value="">- Pilih Bulan -</option>
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

                                    <!-- Nominal Payment -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-money-bill-wave text-primary"></i> Nominal Payment</label>
                                            <input type="text" name="nominal_payment" id="nominal_payment" class="form-control" placeholder="0">
                                            <small class="text-muted">Masukkan nominal tanpa titik/koma. Contoh: 5000000</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info Box -->
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Catatan:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Kode Order akan digenerate otomatis dengan format: <strong>ORD-YYYYMMDD-XXXX</strong></li>
                                        <li>Status payment customer dan vendor akan otomatis diset <strong>Waiting Payment</strong></li>
                                        <li>Untuk bayar ke vendor, bisa dilakukan setelah order disimpan</li>
                                    </ul>
                                </div>

                                <!-- Buttons -->
                                <div class="text-right">
                                    <a href="<?= base_url('transaksi_order') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Transaksi
                                    </button>
                                </div>
                            </form>
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
            // Select2 untuk customer
            $('#customer_id').select2({
                theme: 'bootstrap4',
                placeholder: '- Pilih Customer -',
                allowClear: true
            });

            // Format nominal payment
            $('#nominal_payment').on('keyup', function() {
                let val = $(this).val().replace(/\D/g, '');
                $(this).val(val.replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
            });

            // Auto focus customer setelah load
            setTimeout(function() {
                $('#customer_id').select2('open');
            }, 500);
        });
    </script>
</body>
</html>