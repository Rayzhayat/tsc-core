<!-- ============================================================
     FILE: import.php
     PATH: application/views/daily_rent/import.php
     ============================================================ -->
<!DOCTYPE html>
<html lang="en">

<head><?php $this->load->view('partials/head') ?></head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0"><i class="fas fa-file-excel text-success"></i> Import Order Daily Rent</h1>
                        <a href="<?= base_url('daily_rent') ?>" class="btn btn-secondary btn-sm"><i
                                class="fas fa-arrow-left"></i> Kembali</a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <div class="row">
                        <div class="col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header bg-success text-white py-2">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-upload"></i> Upload File Excel
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('daily_rent/proses_import') ?>" method="post"
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
                                            Customer, Lokasi, PIC, Periode Sewa.
                                            <br>Vendor, Nopol, Driver dilengkapi manual per unit setelah import.
                                        </div>

                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-upload"></i> Upload & Preview
                                        </button>
                                    </form>

                                    <hr>

                                    <a href="<?= base_url('daily_rent/download_template') ?>"
                                        class="btn btn-outline-primary btn-block">
                                        <i class="fas fa-download"></i> Download Template Excel
                                    </a>
                                </div>
                            </div>
                        </div>

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
                                                <td>Lokasi Operasional</td>
                                                <td><span class="badge badge-secondary">Tidak</span></td>
                                            </tr>
                                            <tr>
                                                <td>C</td>
                                                <td>PIC Customer</td>
                                                <td><span class="badge badge-secondary">Tidak</span></td>
                                            </tr>
                                            <tr>
                                                <td>D</td>
                                                <td>No HP PIC</td>
                                                <td><span class="badge badge-secondary">Tidak</span></td>
                                            </tr>
                                            <tr>
                                                <td>E</td>
                                                <td>Tanggal Mulai Sewa</td>
                                                <td><span class="badge badge-danger">Ya</span></td>
                                            </tr>
                                            <tr>
                                                <td>F</td>
                                                <td>Jam Mulai</td>
                                                <td><span class="badge badge-secondary">Tidak</span></td>
                                            </tr>
                                            <tr>
                                                <td>G</td>
                                                <td>Tanggal Selesai Sewa</td>
                                                <td><span class="badge badge-danger">Ya</span></td>
                                            </tr>
                                            <tr>
                                                <td>H</td>
                                                <td>Jam Selesai</td>
                                                <td><span class="badge badge-secondary">Tidak</span></td>
                                            </tr>
                                            <tr>
                                                <td>I</td>
                                                <td>Notes Order</td>
                                                <td><span class="badge badge-secondary">Tidak</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <p class="font-weight-bold text-warning mb-1 mt-3"><i
                                            class="fas fa-exclamation-triangle"></i> Tips:</p>
                                    <ul class="small text-muted mb-0">
                                        <li>Format tanggal: <code>DD/MM/YYYY</code> atau <code>YYYY-MM-DD</code></li>
                                        <li>Format jam: <code>HH:MM</code> (contoh: 08:00)</li>
                                        <li>Nama Customer harus sesuai master data</li>
                                        <li>Baris 1 adalah header, data mulai baris 2</li>
                                        <li>Maksimal <strong>500 baris</strong> per upload</li>
                                        <li>No Rent di-generate otomatis (DR001, DR002, ...)</li>
                                        <li>Unit kendaraan ditambahkan manual di halaman detail</li>
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
                let fn = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').text(fn || 'Pilih file .xlsx atau .xls');
            });
        });
    </script>
</body>

</html>