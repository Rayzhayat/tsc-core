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
                            <i class="fas fa-edit text-warning"></i> Edit Vendor Operasional
                        </h1>
                        <a href="<?= base_url('vendor_operasional') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif; ?>

                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow">
                                <div class="card-header bg-gradient-warning text-white py-3">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-building"></i> Edit Data Vendor
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('vendor_operasional/proses_ubah/' . $vendor->id) ?>"
                                        method="POST">
                                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                                            value="<?= $this->security->get_csrf_hash() ?>">

                                        <div class="form-group">
                                            <label class="font-weight-bold">Nama Vendor <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="nama_vendor" class="form-control form-control-lg"
                                                placeholder="Nama vendor"
                                                value="<?= set_value('nama_vendor', $vendor->nama_vendor) ?>" autofocus
                                                required>
                                            <small class="text-muted">Perubahan nama akan langsung berlaku di semua
                                                dropdown vendor.</small>
                                        </div>

                                        <div class="p-2 bg-light rounded mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> Ditambahkan:
                                                <?= !empty($vendor->created_at) ? date('d/m/Y H:i', strtotime($vendor->created_at)) : '-' ?><br>
                                                <i class="fas fa-sync"></i> Terakhir diubah:
                                                <?= !empty($vendor->updated_at) ? date('d/m/Y H:i', strtotime($vendor->updated_at)) : '-' ?>
                                            </small>
                                        </div>

                                        <hr>
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="<?= base_url('vendor_operasional') ?>" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Batal
                                            </a>
                                            <button type="submit" class="btn btn-warning text-white">
                                                <i class="fas fa-save"></i> Update Vendor
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
</body>

</html>