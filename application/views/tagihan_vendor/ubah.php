<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>
        .select2-container { width: 100% !important; }
        .select2-container--bootstrap4 .select2-selection {
            height: calc(1.5em + .75rem + 2px) !important;
            padding: .375rem .75rem;
        }
        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
                        <a href="<?= base_url('tagihan_vendor') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Info Box untuk Status Payment -->
                    <?php if ($tagihan->status_payment == 'Paid'): ?>
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i> <strong>Perhatian:</strong> 
                            Tagihan ini sudah berstatus <strong class="text-success">PAID</strong> dan terhubung dengan 
                            Reff No: <strong class="text-primary"><?= $tagihan->kode_payment ?></strong>. 
                            Perubahan data mungkin mempengaruhi pencatatan pengeluaran.
                        </div>
                    <?php endif ?>

                    <div class="card shadow">
                        <div class="card-header bg-warning text-white">
                            <h6 class="m-0 font-weight-bold"><i class="fas fa-edit"></i> Form Ubah Tagihan Vendor</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('tagihan_vendor/proses_ubah') ?>" method="post" id="formTagihan">
                                <input type="hidden" name="id" value="<?= $tagihan->id ?>">
                                
                                <!-- Tagihan Vendor -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tagihan Vendor <span class="text-danger">*</span></label>
                                            <select name="vendor_id" id="vendor_id" class="form-control select2" required>
                                                <option value="">- Pilih Vendor -</option>
                                                <?php foreach ($vendors as $v): ?>
                                                    <option value="<?= $v->kode ?>" <?= $v->kode == $tagihan->vendor_id ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($v->nama_vendor) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted"><em>Relasi Master Vendor</em></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nama Vendor</label>
                                            <input type="text" id="nama_vendor_display" class="form-control bg-light" readonly value="<?= htmlspecialchars($tagihan->nama_vendor) ?>">
                                            <small class="text-muted"><em>Nama Vendor</em></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- No Invoice & Invoice Date -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>No Invoice <span class="text-danger">*</span></label>
                                            <input type="text" name="no_invoice" class="form-control" required placeholder="INV-001" value="<?= htmlspecialchars($tagihan->no_invoice) ?>">
                                            <small class="text-muted"><em>No Invoice</em></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Invoice Date</label>
                                            <input type="date" name="invoice_date" class="form-control" value="<?= $tagihan->invoice_date ?>">
                                            <small class="text-muted"><em>Invoice Date</em></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Invoice Recieve Date & Bulan Shipment -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Invoice Recieve Date</label>
                                            <input type="date" name="invoice_recieve_date" class="form-control" value="<?= $tagihan->invoice_recieve_date ?>">
                                            <small class="text-muted"><em>Invoice Recieve Date</em></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Bulan Shipment</label>
                                            <select name="bulan_shipment" class="form-control">
                                                <option value="">- Pilih Bulan -</option>
                                                <?php 
                                                $bulan_list = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                                foreach ($bulan_list as $bln): 
                                                ?>
                                                    <option value="<?= $bln ?>" <?= $bln == $tagihan->bulan_shipment ? 'selected' : '' ?>><?= $bln ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted"><em>Bulan Shipment</em></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nominal -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nominal <span class="text-danger">*</span></label>
                                            <input type="text" name="nominal" id="nominal" class="form-control text-right" required placeholder="0" value="<?= number_format($tagihan->nominal, 0, ',', '.') ?>">
                                            <small class="text-muted"><em>Nominal (auto fill jika sudah dibayar)</em></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Status Payment</label>
                                            <input type="text" class="form-control font-weight-bold <?= $tagihan->status_payment == 'Paid' ? 'bg-success text-white' : 'bg-warning' ?>" readonly value="<?= $tagihan->status_payment ?> (Relasi dari Pengeluaran)">
                                            <small class="text-muted"><em>Status Payment (auto fill jika sudah dibayar)</em></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kode Payment -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Kode Payment</label>
                                            <input type="text" class="form-control bg-light font-weight-bold <?= $tagihan->kode_payment ? 'text-primary' : '' ?>" readonly value="<?= $tagihan->kode_payment ?: '(Belum dibayar)' ?>">
                                            <small class="text-muted"><em>Relasi ke Reff No Pengeluaran</em></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info Audit -->
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i> Dibuat oleh: <strong><?= $tagihan->created_by ?></strong><br>
                                                    <i class="fas fa-clock"></i> Pada: <strong><?= date('d/m/Y H:i', strtotime($tagihan->created_at)) ?></strong>
                                                </small>
                                            </div>
                                            <?php if ($tagihan->updated_at): ?>
                                                <div class="col-md-6">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user-edit"></i> Diubah oleh: <strong><?= $tagihan->updated_by ?></strong><br>
                                                        <i class="fas fa-clock"></i> Pada: <strong><?= date('d/m/Y H:i', strtotime($tagihan->updated_at)) ?></strong>
                                                    </small>
                                                </div>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-warning btn-lg px-5">
                                        <i class="fas fa-save"></i> Update Tagihan
                                    </button>
                                    <a href="<?= base_url('tagihan_vendor') ?>" class="btn btn-secondary btn-lg px-5">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
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
            // Init Select2
            $('.select2').select2({ 
                theme: 'bootstrap4',
                placeholder: '- Pilih Vendor -',
                allowClear: true
            });
            
            // Auto fill nama vendor saat vendor dipilih
            $('#vendor_id').on('select2:select', function(e) {
                var selected = $(this).find(':selected');
                var nama = selected.text();
                $('#nama_vendor_display').val(nama);
            });
            
            $('#vendor_id').on('select2:clear', function(e) {
                $('#nama_vendor_display').val('');
            });
            
            // Format rupiah
            $('#nominal').on('keyup', function() {
                formatRupiah(this);
            });
        });

        function formatRupiah(o) {
            let v = o.value.replace(/\D/g, '');
            v = v.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            o.value = v || '0';
        }

        $('#formTagihan').on('submit', function(e) {
            var nominal = parseInt($('#nominal').val().replace(/\./g, '') || 0);
            if (nominal <= 0) {
                e.preventDefault();
                alert('Nominal harus lebih dari 0!');
                $('#nominal').focus();
                return false;
            }

            // Konfirmasi jika status sudah Paid
            <?php if ($tagihan->status_payment == 'Paid'): ?>
                if (!confirm('Tagihan ini sudah berstatus PAID dan terhubung dengan Reff No: <?= $tagihan->kode_payment ?>.\n\nYakin ingin mengubah data ini?')) {
                    e.preventDefault();
                    return false;
                }
            <?php endif ?>

            return true;
        });
    </script>
</body>
</html>