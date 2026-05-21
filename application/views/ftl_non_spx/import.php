<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-file-excel text-success"></i> Import Shipment FTL Non SPX
                        </h1>
                        <a href="<?= base_url('ftl_non_spx') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif; ?>

                    <div class="row">

                        <!-- UPLOAD FORM -->
                        <div class="col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header bg-success text-white py-2">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-upload"></i> Upload File Excel
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('ftl_non_spx/proses_import') ?>" method="post"
                                        enctype="multipart/form-data">

                                        <div class="form-group">
                                            <label>Pilih File Excel <span class="text-danger">*</span></label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="excel_file"
                                                    name="excel_file" accept=".xlsx,.xls" required>
                                                <label class="custom-file-label" for="excel_file">Pilih file .xlsx atau
                                                    .xls</label>
                                            </div>
                                            <small class="text-muted">Maksimal 5MB. Format: .xlsx atau .xls</small>
                                        </div>

                                        <div class="alert alert-info py-2">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Info:</strong> Data yang diimport akan otomatis terisi:
                                            Customer, Origin 1, Origin 2, Dest 1, Dest 2, Truck Type, Target Standby,
                                            Target Arrival.
                                            <br>Vendor, Nopol, Driver, No HP dilengkapi manual di TMS setelah import.
                                        </div>

                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-upload"></i> Upload & Preview
                                        </button>

                                    </form>

                                    <hr>

                                    <a href="<?= base_url('ftl_non_spx/download_template') ?>"
                                        class="btn btn-outline-primary btn-block">
                                        <i class="fas fa-download"></i> Download Template Excel
                                    </a>

                                </div>
                            </div>
                        </div>

                        <!-- PANDUAN -->
                        <div class="col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header bg-primary text-white py-2">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-book"></i> Panduan Import</h6>
                                </div>
                                <div class="card-body">
                                    <p class="font-weight-bold text-primary mb-2">Kolom di Excel:</p>
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Kolom</th>
                                                <th>Keterangan</th>
                                                <th>Wajib</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>A</td>
                                                <td>Customer</td>
                                                <td><span class="badge badge-danger">Ya</span></td>
                                            </tr>
                                            <tr>
                                                <td>B</td>
                                                <td>Origin 1</td>
                                                <td><span class="badge badge-danger">Ya</span></td>
                                            </tr>
                                            <tr>
                                                <td>C</td>
                                                <td>Origin 2</td>
                                                <td><span class="badge badge-secondary">Tidak</span></td>
                                            </tr>
                                            <tr>
                                                <td>D</td>
                                                <td>Dest 1</td>
                                                <td><span class="badge badge-danger">Ya</span></td>
                                            </tr>
                                            <tr>
                                                <td>E</td>
                                                <td>Dest 2</td>
                                                <td><span class="badge badge-secondary">Tidak</span></td>
                                            </tr>
                                            <tr>
                                                <td>F</td>
                                                <td>Truck Type</td>
                                                <td><span class="badge badge-danger">Ya</span></td>
                                            </tr>
                                            <tr>
                                                <td>G</td>
                                                <td>Target Standby Date</td>
                                                <td><span class="badge badge-danger">Ya</span></td>
                                            </tr>
                                            <tr>
                                                <td>H</td>
                                                <td>Target Standby Time</td>
                                                <td><span class="badge badge-secondary">Tidak</span></td>
                                            </tr>
                                            <tr>
                                                <td>I</td>
                                                <td>Target Arrival Date</td>
                                                <td><span class="badge badge-danger">Ya</span></td>
                                            </tr>
                                            <tr>
                                                <td>J</td>
                                                <td>Target Arrival Time</td>
                                                <td><span class="badge badge-secondary">Tidak</span></td>
                                            </tr>
                                            <tr>
                                                <td>K</td>
                                                <td>Notes Order</td>
                                                <td><span class="badge badge-secondary">Tidak</span></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <p class="font-weight-bold text-warning mb-1 mt-3">
                                        <i class="fas fa-exclamation-triangle"></i> Tips:
                                    </p>
                                    <ul class="small text-muted mb-0">
                                        <li>Format tanggal: <code>DD/MM/YYYY</code> atau <code>YYYY-MM-DD</code></li>
                                        <li>Format waktu: <code>HH:MM</code> (contoh: 08:00)</li>
                                        <li>Truck Type: CDD, CDE, Fuso, Tronton, Trailer, WB, Wingbox</li>
                                        <li>Nama Customer harus sesuai master data</li>
                                        <li>Kolom Origin 2 & Dest 2 boleh dikosongkan</li>
                                        <li>Baris 1 adalah header, data mulai baris 2</li>
                                        <li>Maksimal <strong>500 baris</strong> per upload</li>
                                    </ul>
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
        $(document).ready(function () {
            $('#excel_file').on('change', function () {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').text(fileName || 'Pilih file .xlsx atau .xls');
            });
        });
    </script>
</body>

</html>