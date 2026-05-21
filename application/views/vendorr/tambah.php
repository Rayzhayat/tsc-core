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
                    <!-- JUDUL + TOMBOL KEMBALI -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0"><?= $title ?></h1>
                        <a href="<?= base_url('vendorr') ?>" class="btn btn-secondary btn-sm">
                            <i class="fa fa-reply"></i> Kembali
                        </a>
                    </div>

                    <!-- ALERT -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <!-- CARD FORM -->
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="card shadow">
                                <div class="card-header bg-primary text-white">
                                    <strong><i class="fa fa-plus-circle"></i> Isi Form Tambah Vendor</strong>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('vendorr/proses_tambah') ?>" method="POST"
                                        enctype="multipart/form-data">

                                        <!-- NAMA & NPWP -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="nama_vendor" class="form-label">Nama Vendor <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="nama_vendor" id="nama_vendor"
                                                    class="form-control" placeholder="Masukkan Nama Vendor" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="npwp_vendor" class="form-label">NPWP <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="npwp_vendor" id="npwp_vendor"
                                                    class="form-control" placeholder="Contoh: 01.234.567.8-901.000"
                                                    required>
                                            </div>
                                        </div>

                                        <!-- ALAMAT -->
                                        <div class="mb-3">
                                            <label for="alamat_vendor" class="form-label">Alamat</label>
                                            <textarea name="alamat_vendor" id="alamat_vendor" class="form-control"
                                                rows="3" placeholder="Masukkan alamat lengkap vendor"></textarea>
                                        </div>

                                        <!-- PIC & NO TELP -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="pic_vendor" class="form-label">PIC (Person In
                                                    Charge)</label>
                                                <input type="text" name="pic_vendor" id="pic_vendor"
                                                    class="form-control" placeholder="Nama PIC">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="no_telp_vendor" class="form-label">No. Telepon</label>
                                                <input type="text" name="no_telp_vendor" id="no_telp_vendor"
                                                    class="form-control" placeholder="Contoh: 081234567890">
                                            </div>
                                        </div>

                                        <!-- PPN & PPH -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="ppn_vendor" class="form-label">Status PPN <span
                                                        class="text-danger">*</span></label>
                                                <select name="ppn_vendor" id="ppn_vendor" class="form-control" required>
                                                    <option value="">-- Pilih Status PPN --</option>
                                                    <option value="Belum PPN">Belum PPN</option>
                                                    <option value="11%">11%</option>
                                                    <option value="1.1%">1.1%</option>
                                                    <option value="0.1%">0.1%</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="pph_vendor" class="form-label">Status PPH <span
                                                        class="text-danger">*</span></label>
                                                <select name="pph_vendor" id="pph_vendor" class="form-control" required>
                                                    <option value="">-- Pilih Status PPH --</option>
                                                    <option value="Belum PPH">Belum PPH</option>
                                                    <option value="2%">2%</option>
                                                    <option value="2.5%">2.5%</option>
                                                    <option value="0.5%">0.5%</option>
                                                </select>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <!-- FILE UPLOADS SECTION -->
                                        <h5 class="text-primary mb-3">
                                            <i class="fa fa-file-pdf"></i> Upload Dokumen
                                        </h5>

                                        <!-- FILE NPWP (WAJIB) -->
                                        <div class="mb-3">
                                            <label for="file_npwp" class="form-label">
                                                File NPWP <span class="text-danger">*</span>
                                            </label>
                                            <input type="file" class="form-control" id="file_npwp" name="file_npwp"
                                                accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted">
                                                Format: PDF, JPG, PNG (Max 2MB) - <span class="text-danger">Wajib
                                                    diisi</span>
                                            </small>
                                        </div>

                                        <!-- FILE SKB (OPTIONAL) -->
                                        <div class="mb-3">
                                            <label for="file_skb" class="form-label">
                                                File SKB <span class="text-muted">(Optional)</span>
                                            </label>
                                            <input type="file" class="form-control" id="file_skb" name="file_skb"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <small class="text-muted">Format: PDF, JPG, PNG (Max 2MB)</small>
                                        </div>

                                        <!-- FILE SPPKP (OPTIONAL) -->
                                        <div class="mb-3">
                                            <label for="file_sppkp" class="form-label">
                                                File SPPKP <span class="text-muted">(Optional)</span>
                                            </label>
                                            <input type="file" class="form-control" id="file_sppkp" name="file_sppkp"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <small class="text-muted">Format: PDF, JPG, PNG (Max 2MB)</small>
                                        </div>

                                        <hr>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-save"></i> Simpan
                                            </button>
                                            <a href="<?= base_url('vendorr') ?>" class="btn btn-danger">
                                                <i class="fa fa-times"></i> Batal
                                            </a>
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
</body>

</html>