<!-- ubah.php -->
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
                                <div class="card-header bg-warning text-dark">
                                    <strong><i class="fa fa-edit"></i> Ubah Data Vendor</strong>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('vendorr/proses_ubah/' . $vendor->kode) ?>" method="POST"
                                        enctype="multipart/form-data">

                                        <!-- KODE READONLY -->
                                        <div class="mb-3">
                                            <label for="kode_vendor" class="form-label">Kode Vendor</label>
                                            <input type="text" class="form-control" id="kode_vendor"
                                                value="<?= htmlspecialchars($vendor->kode) ?>" readonly>
                                            <small class="text-muted">Kode tidak bisa diubah.</small>
                                        </div>

                                        <!-- NAMA & NPWP -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="nama_vendor" class="form-label">Nama Vendor <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="nama_vendor" id="nama_vendor"
                                                    class="form-control"
                                                    value="<?= htmlspecialchars($vendor->nama_vendor) ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="npwp_vendor" class="form-label">NPWP</label>
                                                <input type="text" name="npwp_vendor" id="npwp_vendor"
                                                    class="form-control"
                                                    value="<?= htmlspecialchars($vendor->npwp_vendor) ?>">
                                            </div>
                                        </div>

                                        <!-- ALAMAT -->
                                        <div class="mb-3">
                                            <label for="alamat_vendor" class="form-label">Alamat</label>
                                            <textarea name="alamat_vendor" id="alamat_vendor" class="form-control"
                                                rows="3"><?= htmlspecialchars($vendor->alamat_vendor) ?></textarea>
                                        </div>

                                        <!-- PIC & NO TELP -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="pic_vendor" class="form-label">PIC (Person In
                                                    Charge)</label>
                                                <input type="text" name="pic_vendor" id="pic_vendor"
                                                    class="form-control"
                                                    value="<?= htmlspecialchars($vendor->pic_vendor) ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="no_telp_vendor" class="form-label">No. Telepon</label>
                                                <input type="text" name="no_telp_vendor" id="no_telp_vendor"
                                                    class="form-control"
                                                    value="<?= htmlspecialchars($vendor->no_telp_vendor) ?>">
                                            </div>
                                        </div>

                                        <!-- PPN & PPH -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="ppn_vendor" class="form-label">Status PPN <span
                                                        class="text-danger">*</span></label>
                                                <select name="ppn_vendor" id="ppn_vendor" class="form-control" required>
                                                    <option value="Belum PPN" <?= $vendor->ppn_vendor == 'Belum PPN' ? 'selected' : '' ?>>Belum PPN</option>
                                                    <option value="11%" <?= $vendor->ppn_vendor == '11%' ? 'selected' : '' ?>>11%</option>
                                                    <option value="1.1%" <?= $vendor->ppn_vendor == '1.1%' ? 'selected' : '' ?>>1.1%</option>
                                                    <option value="0.1%" <?= $vendor->ppn_vendor == '0.1%' ? 'selected' : '' ?>>0.1%</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="pph_vendor" class="form-label">Status PPH <span
                                                        class="text-danger">*</span></label>
                                                <select name="pph_vendor" id="pph_vendor" class="form-control" required>
                                                    <option value="Belum PPH" <?= $vendor->pph_vendor == 'Belum PPH' ? 'selected' : '' ?>>Belum PPH</option>
                                                    <option value="2%" <?= $vendor->pph_vendor == '2%' ? 'selected' : '' ?>>2%</option>
                                                    <option value="2.5%" <?= $vendor->pph_vendor == '2.5%' ? 'selected' : '' ?>>2.5%</option>
                                                    <option value="0.5%" <?= $vendor->pph_vendor == '0.5%' ? 'selected' : '' ?>>0.5%</option>
                                                </select>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <!-- FILE UPLOADS SECTION -->
                                        <h5 class="text-warning mb-3">
                                            <i class="fa fa-file-pdf"></i> Upload Dokumen
                                        </h5>

                                        <!-- FILE NPWP -->
                                        <div class="mb-3">
                                            <label for="file_npwp" class="form-label">File NPWP</label>
                                            <?php if (!empty($vendor->file_npwp)): ?>
                                                <div class="alert alert-info mb-2">
                                                    <i class="fa fa-file-pdf"></i>
                                                    File saat ini:
                                                    <strong><?= htmlspecialchars($vendor->file_npwp) ?></strong>
                                                    <a href="<?= base_url('assets/uploads/vendor/' . $vendor->file_npwp) ?>"
                                                        target="_blank" class="btn btn-info btn-sm ms-2">
                                                        <i class="fa fa-eye"></i> Lihat
                                                    </a>
                                                </div>
                                            <?php endif ?>
                                            <input type="file" class="form-control" id="file_npwp" name="file_npwp"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <small class="text-muted">Format: PDF, JPG, PNG (Max 2MB) - Kosongkan jika
                                                tidak ingin mengubah</small>
                                        </div>

                                        <!-- FILE SKB -->
                                        <div class="mb-3">
                                            <label for="file_skb" class="form-label">File SKB <span
                                                    class="text-muted">(Optional)</span></label>
                                            <?php if (!empty($vendor->file_skb)): ?>
                                                <div class="alert alert-info mb-2">
                                                    <i class="fa fa-file-pdf"></i>
                                                    File saat ini:
                                                    <strong><?= htmlspecialchars($vendor->file_skb) ?></strong>
                                                    <a href="<?= base_url('assets/uploads/vendor/' . $vendor->file_skb) ?>"
                                                        target="_blank" class="btn btn-info btn-sm ms-2">
                                                        <i class="fa fa-eye"></i> Lihat
                                                    </a>
                                                </div>
                                            <?php endif ?>
                                            <input type="file" class="form-control" id="file_skb" name="file_skb"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <small class="text-muted">Format: PDF, JPG, PNG (Max 2MB) - Kosongkan jika
                                                tidak ingin mengubah</small>
                                        </div>

                                        <!-- FILE SPPKP -->
                                        <div class="mb-3">
                                            <label for="file_sppkp" class="form-label">File SPPKP <span
                                                    class="text-muted">(Optional)</span></label>
                                            <?php if (!empty($vendor->file_sppkp)): ?>
                                                <div class="alert alert-info mb-2">
                                                    <i class="fa fa-file-pdf"></i>
                                                    File saat ini:
                                                    <strong><?= htmlspecialchars($vendor->file_sppkp) ?></strong>
                                                    <a href="<?= base_url('assets/uploads/vendor/' . $vendor->file_sppkp) ?>"
                                                        target="_blank" class="btn btn-info btn-sm ms-2">
                                                        <i class="fa fa-eye"></i> Lihat
                                                    </a>
                                                </div>
                                            <?php endif ?>
                                            <input type="file" class="form-control" id="file_sppkp" name="file_sppkp"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <small class="text-muted">Format: PDF, JPG, PNG (Max 2MB) - Kosongkan jika
                                                tidak ingin mengubah</small>
                                        </div>

                                        <hr>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fa fa-save"></i> Simpan Perubahan
                                            </button>
                                            <a href="<?= base_url('vendorr') ?>" class="btn btn-secondary">
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