<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container--bootstrap4 .select2-selection {
            height: calc(1.5em + .75rem + 2px) !important;
            padding: .375rem .75rem;
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

                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h6 class="m-0 font-weight-bold"><i class="fas fa-plus-circle"></i> Form Tambah Tagihan Vendor</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('tagihan_vendor/proses_tambah') ?>" method="post" id="formTagihan">

                                <!-- Tagihan Vendor -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tagihan Vendor <span class="text-danger">*</span></label>
                                            <select name="vendor_id" id="vendor_id" class="form-control select2" required>
                                                <option value="">- Pilih Vendor -</option>
                                                <?php foreach ($vendors as $v): ?>
                                                    <option value="<?= $v->kode ?>">
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
                                            <input type="text" id="nama_vendor_display" class="form-control bg-light" readonly placeholder="(auto fill jika sudah dibayar)">
                                            <small class="text-muted"><em>Nama Vendor</em></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- No Invoice & Invoice Date -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>No Invoice <span class="text-danger">*</span></label>
                                            <input type="text" name="no_invoice" class="form-control" required placeholder="INV-001">
                                            <small class="text-muted"><em>No Invoice</em></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Invoice Date</label>
                                            <input type="date" name="invoice_date" class="form-control">
                                            <small class="text-muted"><em>Invoice Date</em></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Invoice Recieve Date & Bulan Shipment -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Invoice Recieve Date</label>
                                            <input type="date" name="invoice_recieve_date" class="form-control">
                                            <small class="text-muted"><em>Invoice Recieve Date</em></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Bulan Shipment</label>
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
                                            <small class="text-muted"><em>Bulan Shipment</em></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nominal -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nominal <span class="text-danger">*</span></label>
                                            <input type="text" name="nominal" id="nominal" class="form-control text-right" required placeholder="0">
                                            <small class="text-muted"><em>Nominal (auto fill jika sudah dibayar)</em></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Status Payment</label>
                                            <input type="text" class="form-control bg-warning font-weight-bold" readonly value="Waiting Payment (Relasi dari Pengeluaran)">
                                            <small class="text-muted"><em>Status Payment (auto fill jika sudah dibayar)</em></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kode Payment -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Kode Payment</label>
                                            <input type="text" class="form-control bg-light" readonly placeholder="(auto fill jika sudah dibayar)">
                                            <small class="text-muted"><em>Relasi ke Reff No Pengeluaran</em></small>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-save"></i> Simpan Tagihan
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
            return true;
        });
    </script>
</body>

</html>