<!-- ftl non spx -->
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
                            <i class="fas fa-plus-circle text-primary"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('ftl_non_spx') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <div class="alert alert-info border-left-info py-2 mb-4">
                        <div class="d-flex align-items-center">
                            <div class="mr-3" style="font-size:1.6rem;">📋</div>
                            <div>
                                <strong>Alur Input Shipment:</strong>
                                <span class="badge badge-primary ml-1">Step 1</span> Isi data shipment di sini → Simpan
                                &nbsp;→&nbsp;
                                <span class="badge badge-success ml-1">Step 2</span> Assign Vendor, Nopol & Driver di
                                halaman detail
                            </div>
                        </div>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('ftl_non_spx/proses_tambah') ?>" method="post">
                        <div class="row">

                            <!-- KOLOM KIRI -->
                            <div class="col-lg-6">
                                <div class="card shadow mb-4">
                                    <div class="card-header bg-primary text-white py-2">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-file-alt"></i> Informasi Shipment
                                            <span class="badge badge-light ml-2" style="font-size:0.7rem;">Step 1</span>
                                        </h6>
                                    </div>
                                    <div class="card-body">

                                        <!-- No Shipment -->
                                        <div class="form-group">
                                            <label>No Shipment</label>
                                            <div class="input-group">
                                                <input type="text" name="no_shipment" class="form-control"
                                                    value="<?= htmlspecialchars($no_shipment ?? '') ?>" required
                                                    readonly
                                                    style="background:#f8f9fc; font-weight:700; font-family:monospace; font-size:1.1rem; color:#4e73df;">
                                                <div class="input-group-append">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-magic"></i> Auto
                                                    </span>
                                                </div>
                                            </div>
                                            <small class="text-muted">Generate otomatis, tidak bisa diubah
                                                manual.</small>
                                        </div>

                                        <!-- Customer -->
                                        <div class="form-group">
                                            <label>Customer <span class="text-danger">*</span></label>
                                            <select name="customer_id" class="form-control" required>
                                                <option value="">-- Pilih Customer --</option>
                                                <?php foreach ($customers ?? [] as $c): ?>
                                                    <option value="<?= $c->id ?>"><?= htmlspecialchars($c->nama) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Origin 1 & Origin 2 -->
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Origin 1 <span class="text-danger">*</span></label>
                                                <input type="text" name="origin" class="form-control"
                                                    placeholder="Lokasi asal utama" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Origin 2 <span class="text-muted"
                                                        style="font-size:0.78rem;">(opsional)</span></label>
                                                <input type="text" name="origin2" class="form-control"
                                                    placeholder="Lokasi asal tambahan">
                                            </div>
                                        </div>

                                        <!-- Dest 1 & Dest 2 -->
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Dest 1 <span class="text-danger">*</span></label>
                                                <input type="text" name="dest1" class="form-control"
                                                    placeholder="Tujuan utama" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Dest 2 <span class="text-muted"
                                                        style="font-size:0.78rem;">(opsional)</span></label>
                                                <input type="text" name="dest2" class="form-control"
                                                    placeholder="Tujuan tambahan">
                                            </div>
                                        </div>

                                        <!-- Truck Type -->
                                        <div class="form-group">
                                            <label>Truck Type <span class="text-danger">*</span></label>
                                            <select name="truck_type" class="form-control" required>
                                                <option value="">-- Pilih Tipe Truck --</option>
                                                <option>Blindvan</option>
                                                <option>L300</option>
                                                <option>CDE</option>
                                                <option>CDE Long</option>
                                                <option>CDD</option>
                                                <option>CDD Long</option>
                                                <option>Fuso</option>
                                                <option>Tronton Wingbox</option>
                                                <option>Tronton Box</option>
                                                <option>WB</option>
                                                <option>Wingbox</option>
                                                <option>Flatbed</option>
                                                <option>Reefer</option>
                                                <option>Tronton</option>
                                                <option>Trailer</option>
                                            </select>
                                        </div>

                                        <!-- Notes -->
                                        <div class="form-group mb-0">
                                            <label>Notes</label>
                                            <textarea name="notes" class="form-control" rows="2"
                                                placeholder="Catatan tambahan..."></textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- KOLOM KANAN -->
                            <div class="col-lg-6">

                                <!-- TARGET WAKTU -->
                                <div class="card shadow mb-4">
                                    <div class="card-header bg-warning text-white py-2">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-calendar-alt"></i> Target Waktu
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Target Standby <span class="text-danger">*</span></label>
                                            <div class="form-row">
                                                <div class="col-7">
                                                    <input type="date" name="target_standby_date" class="form-control"
                                                        required>
                                                </div>
                                                <div class="col-5">
                                                    <input type="time" name="target_standby_time" class="form-control"
                                                        required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Target Arrival <span class="text-danger">*</span></label>
                                            <div class="form-row">
                                                <div class="col-7">
                                                    <input type="date" name="target_arrival_date" class="form-control"
                                                        required>
                                                </div>
                                                <div class="col-5">
                                                    <input type="time" name="target_arrival_time" class="form-control"
                                                        required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- VENDOR LOCKED -->
                                <div class="card shadow mb-4 border-secondary">
                                    <div class="card-header bg-secondary text-white py-2">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-lock"></i> Vendor & Kendaraan
                                            <span class="badge badge-light ml-2" style="font-size:0.7rem;">Step 2</span>
                                        </h6>
                                    </div>
                                    <div class="card-body" style="position:relative; min-height:140px;">
                                        <div
                                            style="position:absolute; top:0; left:0; right:0; bottom:0;
                                        background:rgba(248,249,252,0.92); z-index:5; border-radius:0 0 4px 4px;
                                        display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;">
                                            <i class="fas fa-lock fa-2x text-secondary mb-2"></i>
                                            <p class="text-muted mb-1" style="font-size:0.88rem;">
                                                <strong>Vendor, Nopol & Driver</strong> diisi di Step 2
                                            </p>
                                            <p class="text-muted" style="font-size:0.78rem;">
                                                Simpan data shipment terlebih dahulu,<br>
                                                lalu klik tombol <strong>"Assign Vendor"</strong> di halaman detail.
                                            </p>
                                        </div>
                                        <div class="form-group">
                                            <label class="text-muted">Vendor</label>
                                            <input type="text" class="form-control" disabled
                                                placeholder="Diisi setelah simpan...">
                                        </div>
                                        <div class="form-group">
                                            <label class="text-muted">Nopol Kendaraan</label>
                                            <input type="text" class="form-control" disabled
                                                placeholder="Diisi setelah simpan...">
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label class="text-muted">Nama Driver</label>
                                                <input type="text" class="form-control" disabled
                                                    placeholder="Diisi setelah simpan...">
                                            </div>
                                            <div class="form-group col-md-6 mb-0">
                                                <label class="text-muted">No HP Driver</label>
                                                <input type="text" class="form-control" disabled
                                                    placeholder="Diisi setelah simpan...">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- TOMBOL SUBMIT -->
                        <div class="card shadow mb-4">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Setelah simpan, kamu akan diarahkan ke halaman detail untuk mengisi Vendor & Driver.
                                </small>
                                <div>
                                    <a href="<?= base_url('ftl_non_spx') ?>" class="btn btn-secondary mr-2">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan & Lanjut ke Step 2
                                    </button>
                                </div>
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
            $('form').on('submit', function (e) {
                const standbyDate = $('[name=target_standby_date]').val();
                const standbyTime = $('[name=target_standby_time]').val();
                const arrivalDate = $('[name=target_arrival_date]').val();
                const arrivalTime = $('[name=target_arrival_time]').val();
                if (standbyDate && arrivalDate) {
                    const standby = standbyDate + 'T' + (standbyTime || '00:00');
                    const arrival = arrivalDate + 'T' + (arrivalTime || '00:00');
                    if (arrival < standby) {
                        e.preventDefault();
                        alert('Target Arrival tidak boleh sebelum Target Standby!');
                        $('[name=target_arrival_date]').focus();
                        return false;
                    }
                }
            });
        });
    </script>
</body>

</html>