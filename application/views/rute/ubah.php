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
                        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
                        <a href="<?= base_url('rute') ?>" class="btn btn-secondary btn-sm">
                            Kembali
                        </a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Ubah Rute: <strong><?= $rute->kode_rute ?></strong>
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('rute/proses_ubah/' . $rute->id) ?>" method="post">
                                <div class="row">
                                    <!-- KIRI -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Customer</strong> <span class="text-danger">*</span></label>
                                            <input type="text" name="customer" class="form-control" 
                                                   value="<?= set_value('customer', $rute->customer) ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Service</strong> <span class="text-danger">*</span></label>
                                            <select name="service" class="form-control" required>
                                                <option value="">Pilih Service</option>
                                                <option value="FTL" <?= set_select('service', 'FTL', $rute->service == 'FTL') ?>>FTL</option>
                                                <option value="Daily" <?= set_select('service', 'Daily', $rute->service == 'Daily') ?>>Daily</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Tipe Unit</strong> <span class="text-danger">*</span></label>
                                            <select name="tipe_unit" class="form-control" required>
                                                <option value="">Pilih Tipe Unit</option>
                                                <?php 
                                                $tipe_options = ['WB', 'CDDL', 'CDE', 'L300', 'FUSO', 'Tronton', 'CDD'];
                                                foreach ($tipe_options as $opt): ?>
                                                    <option value="<?= $opt ?>" <?= set_select('tipe_unit', $opt, $rute->tipe_unit == $opt) ?>><?= $opt ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>SLA</strong> <span class="text-danger">*</span></label>
                                            <select name="sla" class="form-control" required>
                                                <option value="">Pilih SLA</option>
                                                <option value="Express" <?= set_select('sla', 'Express', $rute->sla == 'Express') ?>>Express</option>
                                                <option value="Regular" <?= set_select('sla', 'Regular', $rute->sla == 'Regular') ?>>Regular</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- KANAN -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Origin (DC Asal)</strong> <span class="text-danger">*</span></label>
                                            <input type="text" name="origin" class="form-control" 
                                                   value="<?= set_value('origin', $rute->origin) ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Destination 1</strong> <span class="text-danger">*</span></label>
                                            <input type="text" name="dest1" class="form-control" 
                                                   value="<?= set_value('dest1', $rute->dest1) ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Destination 2 <small class="text-muted">(Opsional)</small></label>
                                            <input type="text" name="dest2" class="form-control" 
                                                   value="<?= set_value('dest2', $rute->dest2) ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Destination 3 <small class="text-muted">(Opsional)</small></label>
                                            <input type="text" name="dest3" class="form-control" 
                                                   value="<?= set_value('dest3', $rute->dest3) ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Destination 4 <small class="text-muted">(Opsional)</small></label>
                                            <input type="text" name="dest4" class="form-control" 
                                                   value="<?= set_value('dest4', $rute->dest4) ?>">
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Harga</strong> <span class="text-danger">*</span></label>
                                            <input type="text" name="harga" class="form-control text-right" 
                                                   value="<?= set_value('harga', $rute->harga) ?>" required>
                                            <small class="text-muted">Tanpa titik atau koma</small>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        Update Rute
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
    <script>
    $(document).on('keyup', function(e) {
        if (e.key === "Escape") {
            window.location.href = '<?= base_url('rute') ?>';
        }
    });
    </script>
</body>
</html>