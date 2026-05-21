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

                    <!-- HEADER -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-edit text-success"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('ftl_non_spx') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- ALERT ERROR -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif; ?>

                    <?php $s = $shipment; // alias pendek ?>

                    <form action="<?= base_url('ftl_non_spx/proses_ubah/' . $s->id) ?>" method="post">

                        <div class="row">

                            <!-- KOLOM KIRI -->
                            <div class="col-lg-6">

                                <!-- INFORMASI SHIPMENT -->
                                <div class="card shadow mb-4">
                                    <div class="card-header bg-success text-white py-2">
                                        <h6 class="m-0 font-weight-bold"><i class="fas fa-file-alt"></i> Informasi
                                            Shipment</h6>
                                    </div>
                                    <div class="card-body">

                                        <!-- No Shipment (readonly) -->
                                        <div class="form-group">
                                            <label>No Shipment</label>
                                            <input type="text" name="no_shipment" class="form-control"
                                                value="<?= htmlspecialchars($s->no_shipment ?? '') ?>" readonly
                                                style="background:#f8f9fc; font-weight:700; font-family:monospace; font-size:1.1rem; color:#4e73df;">
                                            <small class="text-muted">No shipment tidak bisa diubah.</small>
                                        </div>

                                        <!-- Customer -->
                                        <div class="form-group">
                                            <label>Customer <span class="text-danger">*</span></label>
                                            <select name="customer_id" class="form-control" required>
                                                <option value="">-- Pilih Customer --</option>
                                                <?php foreach ($customers ?? [] as $c): ?>
                                                    <option value="<?= $c->id ?>" <?= ($s->customer_id == $c->id) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($c->nama) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Origin 1 & Origin 2 -->
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Origin 1</label>
                                                <input type="text" name="origin" class="form-control"
                                                    value="<?= htmlspecialchars($s->origin ?? '') ?>"
                                                    placeholder="Kota asal utama">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Origin 2 <span class="text-muted" style="font-size:0.78rem;">(opsional)</span></label>
                                                <input type="text" name="origin2" class="form-control"
                                                    value="<?= htmlspecialchars($s->origin2 ?? '') ?>"
                                                    placeholder="Lokasi asal tambahan">
                                            </div>
                                        </div>

                                        <!-- Dest 1 & Dest 2 -->
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Dest 1 <span class="text-danger">*</span></label>
                                                <input type="text" name="dest1" class="form-control"
                                                    value="<?= htmlspecialchars($s->dest1 ?? '') ?>"
                                                    placeholder="Tujuan utama" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Dest 2 <span class="text-muted" style="font-size:0.78rem;">(opsional)</span></label>
                                                <input type="text" name="dest2" class="form-control"
                                                    value="<?= htmlspecialchars($s->dest2 ?? '') ?>"
                                                    placeholder="Tujuan tambahan">
                                            </div>
                                        </div>

                                        <!-- Truck Type -->
                                        <div class="form-group">
                                            <label>Truck Type</label>
                                            <select name="truck_type" class="form-control">
                                                <option value="">-- Pilih Tipe Truck --</option>
                                                <?php
                                                $truck_types = ['Blindvan', 'L300', 'CDE', 'CDE Long', 'CDD', 'CDD Long', 'Fuso', 'Tronton Wingbox', 'Tronton Box', 'WB', 'Wingbox', 'Flatbed', 'Reefer', 'Tronton', 'Trailer'];
                                                foreach ($truck_types as $tt):
                                                    ?>
                                                    <option value="<?= $tt ?>" <?= ($s->truck_type == $tt) ? 'selected' : '' ?>>
                                                        <?= $tt ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Status -->
                                        <div class="form-group">
                                            <label>Status Shipment</label>
                                            <select name="status_shipment" class="form-control">
                                                <?php
                                                $all_statuses = [
                                                    'Scheduled',
                                                    'Sourcing Vendor',
                                                    'Loading',
                                                    'On Trip',
                                                    'Tiba di Lokasi Muat',
                                                    'Tiba di Lokasi Bongkar',
                                                    'Completed',
                                                    'Cancelled'
                                                ];
                                                foreach ($all_statuses as $st):
                                                    ?>
                                                    <option value="<?= $st ?>" <?= ($s->status_shipment == $st) ? 'selected' : '' ?>>
                                                        <?= $st ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Notes -->
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <textarea name="notes" class="form-control"
                                                rows="2"><?= htmlspecialchars($s->notes ?? '') ?></textarea>
                                        </div>

                                    </div>
                                </div>

                            </div>

                            <!-- KOLOM KANAN -->
                            <div class="col-lg-6">

                                <!-- VENDOR & KENDARAAN -->
                                <div class="card shadow mb-4">
                                    <div class="card-header bg-primary text-white py-2">
                                        <h6 class="m-0 font-weight-bold"><i class="fas fa-truck"></i> Vendor & Kendaraan
                                        </h6>
                                    </div>
                                    <div class="card-body">

                                        <!-- Vendor -->
                                        <div class="form-group">
                                            <label>Vendor</label>
                                            <select name="vendor_id" class="form-control">
                                                <option value="">-- Pilih Vendor / OWN UNIT --</option>
                                                <?php foreach ($vendors ?? [] as $v): ?>
                                                    <option value="<?= $v->id ?>" <?= ($s->vendor_id == $v->id) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($v->nama_vendor) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Nopol -->
                                        <div class="form-group">
                                            <label>Nopol Kendaraan</label>
                                            <input type="text" name="nopol" class="form-control text-uppercase"
                                                value="<?= htmlspecialchars($s->nopol ?? '') ?>"
                                                style="font-family:monospace; font-weight:600; letter-spacing:1px;">
                                        </div>

                                        <!-- Driver & No HP -->
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Nama Driver</label>
                                                <input type="text" name="driver" class="form-control"
                                                    value="<?= htmlspecialchars($s->driver ?? '') ?>">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>No HP Driver</label>
                                                <input type="text" name="no_hp" class="form-control"
                                                    value="<?= htmlspecialchars($s->no_hp ?? '') ?>">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- TARGET WAKTU -->
                                <div class="card shadow mb-4">
                                    <div class="card-header bg-warning text-white py-2">
                                        <h6 class="m-0 font-weight-bold"><i class="fas fa-calendar-alt"></i> Target
                                            Waktu</h6>
                                    </div>
                                    <div class="card-body">

                                        <!-- Target Standby -->
                                        <div class="form-group">
                                            <label>Target Standby</label>
                                            <div class="form-row">
                                                <div class="col-7">
                                                    <input type="date" name="target_standby_date" class="form-control"
                                                        value="<?= $s->target_standby_date ?? '' ?>">
                                                </div>
                                                <div class="col-5">
                                                    <input type="time" name="target_standby_time" class="form-control"
                                                        value="<?= $s->target_standby_time ?? '' ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Target Arrival -->
                                        <div class="form-group">
                                            <label>Target Arrival</label>
                                            <div class="form-row">
                                                <div class="col-7">
                                                    <input type="date" name="target_arrival_date" class="form-control"
                                                        value="<?= $s->target_arrival_date ?? '' ?>">
                                                </div>
                                                <div class="col-5">
                                                    <input type="time" name="target_arrival_time" class="form-control"
                                                        value="<?= $s->target_arrival_time ?? '' ?>">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- TOMBOL SUBMIT -->
                        <div class="card shadow mb-4">
                            <div class="card-body text-right">
                                <a href="<?= base_url('ftl_non_spx') ?>" class="btn btn-secondary mr-2">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {
            $('input[name="nopol"]').on('input', function () {
                $(this).val($(this).val().toUpperCase());
            });
        });
    </script>
</body>

</html>
