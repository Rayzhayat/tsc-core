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
                    
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-money-bill-wave text-success"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('transaksi_order') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <div class="row">
                        <!-- Order Info -->
                        <div class="col-lg-4 mb-4">
                            <div class="card shadow">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="m-0 font-weight-bold">Informasi Order</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="45%">Kode Order</th>
                                            <td><strong><?= $order->kode_order ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Customer</th>
                                            <td><?= $order->nama_customer ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Order</th>
                                            <td><?= date('d/m/Y', strtotime($order->tanggal_order)) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Bulan Shipment</th>
                                            <td><?= $order->bulan_shipment ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nominal Payment</th>
                                            <td><strong>Rp <?= number_format($order->nominal_payment, 0, ',', '.') ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Form Bayar -->
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header bg-success text-white">
                                    <h6 class="m-0 font-weight-bold">Form Pembayaran ke Vendor</h6>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="<?= base_url('transaksi_order/proses_bayar_vendor') ?>" id="formBayar">
                                        <input type="hidden" name="order_id" value="<?= $order->id ?>">

                                        <div class="form-group">
                                            <label>Vendor <span class="text-danger">*</span></label>
                                            <select name="vendor_id" id="vendor_id" class="form-control select2" required>
                                                <option value="">- Pilih Vendor -</option>
                                                <?php foreach($vendors as $v): ?>
                                                    <option value="<?= $v->kode ?>"><?= $v->nama_vendor ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Tagihan Vendor <span class="text-danger">*</span></label>
                                            <select name="tagihan_id" id="tagihan_id" class="form-control select2" required disabled>
                                                <option value="">- Pilih vendor dulu -</option>
                                            </select>
                                            <small class="text-muted">Hanya menampilkan tagihan yang belum dibayar</small>
                                        </div>

                                        <div class="form-group">
                                            <label>Postingan Biaya <span class="text-danger">*</span></label>
                                            <select name="postingan_biaya" class="form-control" required>
                                                <option value="">- Pilih Postingan -</option>
                                                <option value="BBM">BBM</option>
                                                <option value="Tol">Tol</option>
                                                <option value="Parkir">Parkir</option>
                                                <option value="Lain-lain">Lain-lain</option>
                                            </select>
                                        </div>

                                        <!-- Pilih Akun Bank/Kas -->
                                        <div class="form-group">
                                            <label>Bayar dari Akun <span class="text-danger">*</span></label>
                                            <select name="akun_bank_id" id="akun_bank_id" class="form-control select2" required>
                                                <option value="">- Pilih Akun Bank/Kas -</option>
                                                <?php foreach($akun_bank as $akun): ?>
                                                    <option value="<?= $akun->id ?>">
                                                        <?= $akun->nama ?> (Saldo: Rp <?= number_format($akun->saldo_awal, 0, ',', '.') ?>)
                                                    </option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>

                                        <hr>

                                        <div class="form-group">
                                            <label>Nominal <span class="text-danger">*</span></label>
                                            <input type="text" name="nominal" id="nominal" class="form-control currency" required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>PPN <small class="text-muted">(auto dari master vendor)</small></label>
                                                    <input type="text" name="ppn" id="ppn" class="form-control currency" value="0">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>PPH <small class="text-muted">(auto dari master vendor)</small></label>
                                                    <input type="text" name="pph" id="pph" class="form-control currency" value="0">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Total Bayar <strong class="text-success">(Nominal + PPN - PPH)</strong></label>
                                            <input type="text" id="total_bayar" class="form-control form-control-lg font-weight-bold text-success" readonly>
                                        </div>

                                        <hr>

                                        <div class="text-right">
                                            <a href="<?= base_url('transaksi_order') ?>" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Batal
                                            </a>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save"></i> Proses Pembayaran
                                            </button>
                                        </div>
                                    </form>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Init Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Format currency input
            $('.currency').on('input', function() {
                let val = $(this).val().replace(/\./g, '');
                if (!isNaN(val) && val !== '') {
                    $(this).val(parseInt(val).toLocaleString('id-ID'));
                }
            });

            // Auto-load tagihan & PPN/PPH saat pilih vendor
            $('#vendor_id').on('change', function() {
                const vendorId = $(this).val();
                
                if (vendorId) {
                    // Load tagihan unpaid by vendor
                    $.ajax({
                        url: '<?= base_url('transaksi_order/ajax_get_tagihan_by_vendor') ?>',
                        type: 'POST',
                        data: { vendor_id: vendorId },
                        dataType: 'json',
                        success: function(res) {
                            $('#tagihan_id').prop('disabled', false).html('<option value="">- Pilih Tagihan -</option>');
                            
                            if (res.success && res.data.length > 0) {
                                $.each(res.data, function(i, item) {
                                    let nominal = parseInt(item.nominal).toLocaleString('id-ID');
                                    $('#tagihan_id').append(`<option value="${item.id}">${item.no_invoice} - Rp ${nominal}</option>`);
                                });
                            } else {
                                $('#tagihan_id').html('<option value="">Tidak ada tagihan unpaid</option>');
                            }
                        }
                    });

                    // Load PPN & PPH default dari master vendor
                    $.ajax({
                        url: '<?= base_url('transaksi_order/ajax_get_vendor_ppn_pph') ?>',
                        type: 'POST',
                        data: { vendor_id: vendorId },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                let ppn = parseInt(res.data.ppn) || 0;
                                let pph = parseInt(res.data.pph) || 0;
                                
                                $('#ppn').val(ppn.toLocaleString('id-ID'));
                                $('#pph').val(pph.toLocaleString('id-ID'));
                                
                                hitungTotal();
                            }
                        }
                    });
                } else {
                    $('#tagihan_id').prop('disabled', true).html('<option value="">- Pilih vendor dulu -</option>');
                    $('#ppn').val('0');
                    $('#pph').val('0');
                    hitungTotal();
                }
            });

            // Auto calculate total
            function hitungTotal() {
                let nominal = parseFloat($('#nominal').val().replace(/\./g, '')) || 0;
                let ppn = parseFloat($('#ppn').val().replace(/\./g, '')) || 0;
                let pph = parseFloat($('#pph').val().replace(/\./g, '')) || 0;
                
                let total = nominal + ppn - pph;
                
                $('#total_bayar').val('Rp ' + total.toLocaleString('id-ID'));
            }
            
            $('#nominal, #ppn, #pph').on('input', hitungTotal);

            // Validation before submit
            $('#formBayar').on('submit', function(e) {
                let nominal = parseFloat($('#nominal').val().replace(/\./g, '')) || 0;
                
                if (nominal <= 0) {
                    e.preventDefault();
                    alert('Nominal harus lebih dari 0!');
                    return false;
                }
                
                return confirm('Yakin proses pembayaran ini?');
            });

            // Auto hide alert
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>
</html>