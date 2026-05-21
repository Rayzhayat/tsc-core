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
                            <i class="fas fa-edit text-warning"></i> <?= $title ?>
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
                        <div class="card-header py-3 bg-warning text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-edit"></i> Form Ubah Transaksi Order
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('transaksi_order/proses_ubah') ?>" method="POST">
                                <input type="hidden" name="id" value="<?= $order->id ?>">
                                
                                <div class="row">
                                    <!-- Kode Order (Read Only) -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-barcode text-primary"></i> Kode Order</label>
                                            <input type="text" class="form-control bg-light" value="<?= $order->kode_order ?>" readonly>
                                        </div>
                                    </div>

                                    <!-- Tanggal Order -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-calendar text-primary"></i> Tanggal Order <span class="text-danger">*</span></label>
                                            <input type="date" name="tanggal_order" class="form-control" value="<?= $order->tanggal_order ?>" required>
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
                                                    <option value="<?= $customer->id ?>" <?= $customer->id == $order->customer_id ? 'selected' : '' ?>>
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
                                            <input type="text" name="no_invoice_customer" class="form-control" value="<?= $order->no_invoice_customer ?>" required>
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
                                                <?php 
                                                $bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                                                              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                                foreach ($bulan_list as $bulan): 
                                                ?>
                                                    <option value="<?= $bulan ?>" <?= $order->bulan_shipment == $bulan ? 'selected' : '' ?>>
                                                        <?= $bulan ?>
                                                    </option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Nominal Payment -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-money-bill-wave text-primary"></i> Nominal Payment</label>
                                            <input type="text" name="nominal_payment" id="nominal_payment" class="form-control" 
                                                   value="<?= number_format($order->nominal_payment, 0, ',', '.') ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Info -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-info-circle text-info"></i> Status Payment Customer</label>
                                            <input type="text" class="form-control bg-light" value="<?= $order->status_payment_customer ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-info-circle text-info"></i> Status Payment Vendor</label>
                                            <input type="text" class="form-control bg-light" value="<?= $order->status_payment_vendor ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($order->status_payment_vendor == 'Paid'): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        <strong>Perhatian!</strong> Order ini sudah dibayar ke vendor dengan Reff: 
                                        <strong><?= $order->reff_payment_vendor ?></strong>
                                    </div>
                                <?php endif ?>

                                <!-- Buttons -->
                                <div class="text-right">
                                    <a href="<?= base_url('transaksi_order') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-warning text-white">
                                        <i class="fas fa-save"></i> Update Transaksi
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
            $('#customer_id').select2({
                theme: 'bootstrap4',
                placeholder: '- Pilih Customer -'
            });

            // Format nominal
            $('#nominal_payment').on('keyup', function() {
                let val = $(this).val().replace(/\D/g, '');
                $(this).val(val.replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
            });
        });
    </script>
</body>
</html>