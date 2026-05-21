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
                            <i class="fas fa-hand-holding-usd text-success"></i> <?= $title ?>
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

                    <!-- Info Order Card -->
                    <div class="card shadow mb-4 border-left-success">
                        <div class="card-header py-3 bg-gradient-success text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-info-circle"></i> Informasi Transaksi Order
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="35%">Kode Order</th>
                                            <td><strong class="text-primary"><?= $order->kode_order ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Customer</th>
                                            <td><?= $order->nama_customer ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Order</th>
                                            <td><?= date('d/m/Y', strtotime($order->tanggal_order)) ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="35%">No Invoice</th>
                                            <td><?= $order->no_invoice_customer ?></td>
                                        </tr>
                                        <tr>
                                            <th>Bulan Shipment</th>
                                            <td><?= $order->bulan_shipment ?: '-' ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nominal Payment</th>
                                            <td><strong class="text-success">Rp <?= number_format($order->nominal_payment, 0, ',', '.') ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Terima Pembayaran -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-success text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-money-check-alt"></i> Form Terima Pembayaran
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('transaksi_order/proses_terima_pembayaran') ?>" method="POST" id="formPembayaran">
                                <input type="hidden" name="order_id" value="<?= $order->id ?>">

                                <div class="row">
                                    <!-- Akun Bank/Kas -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-university text-primary"></i> Akun Bank / Kas <span class="text-danger">*</span></label>
                                            <select name="akun_id" id="akun_id" class="form-control select2" required>
                                                <option value="">- Pilih Akun Bank/Kas -</option>
                                                <?php foreach ($akun_bank as $akun): ?>
                                                    <option value="<?= $akun->id ?>">
                                                        <?= $akun->kode_perkiraan ?> - <?= $akun->nama ?>
                                                    </option>
                                                <?php endforeach ?>
                                            </select>
                                            <small class="text-muted">Pilih akun bank/kas yang menerima pembayaran</small>
                                        </div>
                                    </div>

                                    <!-- Nominal -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-money-bill-wave text-primary"></i> Nominal <span class="text-danger">*</span></label>
                                            <input type="text" name="nominal" id="nominal" class="form-control" 
                                                   value="<?= number_format($order->nominal_payment, 0, ',', '.') ?>" 
                                                   placeholder="0" required>
                                            <small class="text-muted">Default sesuai nominal order, bisa diubah jika berbeda</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info Box -->
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Informasi:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>No Transaksi akan digenerate otomatis dengan format: <strong>IN-YYYYMMDD-XXXX</strong></li>
                                        <li>Status payment customer akan otomatis berubah jadi <strong>Paid</strong></li>
                                        <li>Transaksi akan tercatat di <strong>Laporan Keuangan</strong></li>
                                        <li>Pastikan nominal dan akun bank sudah benar sebelum menyimpan</li>
                                    </ul>
                                </div>

                                <!-- Buttons -->
                                <div class="text-right">
                                    <a href="<?= base_url('transaksi_order') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check-circle"></i> Konfirmasi Pembayaran
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
            // Select2
            $('#akun_id').select2({
                theme: 'bootstrap4',
                placeholder: '- Pilih Akun Bank/Kas -'
            });

            // Format nominal
            $('#nominal').on('keyup', function() {
                let val = $(this).val().replace(/\D/g, '');
                $(this).val(val.replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
            });

            // Validasi sebelum submit
            $('#formPembayaran').on('submit', function(e) {
                const akun = $('#akun_id').val();
                const nominal = $('#nominal').val();
                
                if (!akun) {
                    e.preventDefault();
                    alert('Pilih akun bank/kas terlebih dahulu!');
                    return false;
                }
                
                if (!nominal || nominal == '0') {
                    e.preventDefault();
                    alert('Nominal tidak boleh kosong!');
                    return false;
                }

                return confirm('Yakin ingin konfirmasi pembayaran sebesar Rp ' + nominal + '?');
            });

            // Auto focus akun
            setTimeout(function() {
                $('#akun_id').select2('open');
            }, 500);
        });
    </script>
</body>
</html>