<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar') ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Import Master Rute</h1>
                        <a href="<?= base_url('rute') ?>" class="btn btn-secondary btn-sm">
                            Kembali
                        </a>
                    </div>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <div class="card shadow">
                        <div class="card-body">
                            <form action="<?= base_url('rute/proses_import') ?>" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label>Pilih File Excel (.xlsx)</label>
                                    <input type="file" name="file_excel" class="form-control-file" accept=".xlsx" required>
                                    <small class="text-muted mt-2 d-block">
                                        Kolom wajib: Customer | Service | Tipe Unit | SLA | Origin | Dest 1 | Harga<br>
                                        Kolom opsional: Dest 2 | Dest 3 | Dest 4
                                    </small>
                                </div>
                                <button type="submit" class="btn btn-success btn-lg">
                                    IMPORT SEKARANG
                                </button>
                            </form>

                            <hr>
                            <p><strong>Download Template Excel:</strong></p>
                            <a href="<?= base_url('assets/template/master_rute_template.xlsx') ?>" class="btn btn-info">
                                Download Template.xlsx
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </  </div>
    <?php $this->load->view('partials/js') ?>
</body>
</html>