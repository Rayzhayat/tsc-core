<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .unit-row {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 10px;
            position: relative;
            transition: box-shadow .15s;
        }

        .unit-row:hover {
            box-shadow: 0 .15rem .75rem rgba(0, 0, 0, .08);
        }

        .unit-row .unit-num {
            position: absolute;
            top: -10px;
            left: 14px;
            background: #4e73df;
            color: #fff;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .btn-remove-unit {
            position: absolute;
            top: 8px;
            right: 10px;
            font-size: 0.7rem;
        }

        #unitContainer .unit-row:first-child .btn-remove-unit {
            display: none;
        }

        /* ── LOCK OVERLAY ── */
        .field-locked {
            position: relative;
            pointer-events: none;
            opacity: .55;
        }

        .locked-badge {
            display: inline-block;
            font-size: 0.68rem;
            padding: 2px 8px;
            background: #e3e6f0;
            color: #858796;
            border-radius: 4px;
            font-weight: 600;
        }

        /* ── STEP INDICATOR ── */
        .step-indicator {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 24px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .step-active .step-num {
            background: #4e73df;
            color: #fff;
        }

        .step-next .step-num {
            background: #e3e6f0;
            color: #858796;
        }

        .step-active .step-label {
            color: #4e73df;
        }

        .step-next .step-label {
            color: #858796;
        }

        .step-arrow {
            width: 40px;
            height: 2px;
            background: #e3e6f0;
            margin: 0 4px;
            flex-shrink: 0;
        }

        /* ── UNIT BADGE ── */
        .unit-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #4e73df;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            margin-left: 6px;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- HEADER -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-3">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-plus-circle text-info"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('daily_rent') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- STEP INDICATOR -->
                    <div class="step-indicator mb-4">
                        <div class="step-item step-active">
                            <div class="step-num">1</div>
                            <div class="step-label">Isi Data Order</div>
                        </div>
                        <div class="step-arrow"></div>
                        <div class="step-item step-next">
                            <div class="step-num">2</div>
                            <div class="step-label">Tambah / Assign Unit</div>
                        </div>
                        <div class="step-arrow"></div>
                        <div class="step-item step-next">
                            <div class="step-num">3</div>
                            <div class="step-label">Aktifkan & Monitor</div>
                        </div>
                    </div>

                    <!-- ALERT INFO FLOW -->
                    <div class="alert alert-info border-left-info py-2 mb-4">
                        <div class="d-flex align-items-center">
                            <div class="mr-3" style="font-size:1.5rem;">📋</div>
                            <div class="small">
                                <strong>Alur Order Daily Rent:</strong>
                                Isi data order di sini →
                                Tambah unit kendaraan (bisa langsung atau nanti di halaman detail) →
                                Activate saat kendaraan mulai jalan →
                                Return saat kendaraan kembali.
                            </div>
                        </div>
                    </div>

                    <!-- ALERT ERROR -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <form action="<?= base_url('daily_rent/proses_tambah') ?>" method="post" id="formTambah">
                        <div class="row">

                            <!-- ── KOLOM KIRI ── -->
                            <div class="col-lg-6">

                                <!-- INFO ORDER -->
                                <div class="card shadow mb-4">
                                    <div class="card-header bg-info text-white py-2">
                                        <h6 class="m-0 font-weight-bold"><i class="fas fa-file-alt"></i> Informasi Order
                                        </h6>
                                    </div>
                                    <div class="card-body">

                                        <!-- No Rent -->
                                        <div class="form-group">
                                            <label>No Rent</label>
                                            <div class="input-group">
                                                <input type="text" name="no_rent" class="form-control"
                                                    value="<?= htmlspecialchars($no_rent ?? '') ?>" required readonly
                                                    style="background:#f8f9fc;font-weight:700;font-family:monospace;font-size:1.1rem;color:#4e73df;">
                                                <div class="input-group-append">
                                                    <span class="input-group-text bg-info text-white">
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
                                                <?php endforeach ?>
                                            </select>
                                        </div>

                                        <!-- PIC Customer -->
                                        <div class="form-row">
                                            <div class="form-group col-md-7">
                                                <label>PIC Customer</label>
                                                <input type="text" name="pic_customer" class="form-control"
                                                    placeholder="Nama PIC di sisi customer">
                                            </div>
                                            <div class="form-group col-md-5">
                                                <label>No HP PIC</label>
                                                <input type="text" name="pic_customer_phone" class="form-control"
                                                    placeholder="08xxxxxxxxxx">
                                            </div>
                                        </div>

                                        <!-- Lokasi Operasional -->
                                        <div class="form-group">
                                            <label>Lokasi Operasional</label>
                                            <input type="text" name="location" class="form-control"
                                                placeholder="Contoh: Area Cikarang, Pabrik Legok, dll">
                                            <small class="text-muted">Lokasi default, bisa diperbarui per unit di
                                                halaman detail.</small>
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

                            <!-- ── KOLOM KANAN ── -->
                            <div class="col-lg-6">

                                <!-- PERIODE SEWA -->
                                <div class="card shadow mb-4">
                                    <div class="card-header bg-warning text-white py-2">
                                        <h6 class="m-0 font-weight-bold"><i class="fas fa-calendar-alt"></i> Periode
                                            Sewa</h6>
                                    </div>
                                    <div class="card-body">

                                        <!-- Vendor Default -->
                                        <div class="form-group">
                                            <label>Vendor (Default)</label>
                                            <select name="vendor_id" class="form-control">
                                                <option value="">-- Pilih Vendor (opsional) --</option>
                                                <?php foreach ($vendors ?? [] as $v): ?>
                                                    <option value="<?= $v->id ?>"><?= htmlspecialchars($v->nama_vendor) ?>
                                                    </option>
                                                <?php endforeach ?>
                                            </select>
                                            <small class="text-muted">Vendor utama order. Tiap unit bisa dioverride
                                                vendor-nya.</small>
                                        </div>

                                        <!-- Tanggal Mulai -->
                                        <div class="form-group">
                                            <label>Tanggal & Jam Mulai <span class="text-danger">*</span></label>
                                            <div class="form-row">
                                                <div class="col-7">
                                                    <input type="date" name="rent_start_date" id="rentStartDate"
                                                        class="form-control" required>
                                                </div>
                                                <div class="col-5">
                                                    <input type="time" name="rent_start_time" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tanggal Selesai -->
                                        <div class="form-group mb-0">
                                            <label>Tanggal & Jam Selesai <span class="text-danger">*</span></label>
                                            <div class="form-row">
                                                <div class="col-7">
                                                    <input type="date" name="rent_end_date" id="rentEndDate"
                                                        class="form-control" required>
                                                </div>
                                                <div class="col-5">
                                                    <input type="time" name="rent_end_time" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Duration preview -->
                                        <div id="durationPreview" class="mt-2" style="display:none;">
                                            <div class="alert alert-info py-2 mb-0 small">
                                                <i class="fas fa-clock"></i> Durasi sewa: <strong
                                                    id="durationText">-</strong>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- UNIT KENDARAAN (Opsional di Step 1) -->
                                <div class="card shadow mb-4">
                                    <div
                                        class="card-header bg-secondary text-white py-2 d-flex justify-content-between align-items-center">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-truck"></i> Unit Kendaraan
                                            <span class="unit-count-badge" id="unitCountBadge">0</span>
                                        </h6>
                                        <button type="button" class="btn btn-light btn-sm" id="btnAddUnit">
                                            <i class="fas fa-plus"></i> Tambah Unit
                                        </button>
                                    </div>
                                    <div class="card-body pb-2">
                                        <div class="alert alert-light border py-2 small mb-3">
                                            <i class="fas fa-info-circle text-info"></i>
                                            Unit bisa diisi sekarang (langsung Assigned) atau nanti setelah order
                                            tersimpan (Pending Assign).
                                            Kalau tidak diisi, status unit akan jadi <strong>Pending Assign</strong>.
                                        </div>
                                        <div id="unitContainer">
                                            <!-- Unit row pertama (default) -->
                                            <div class="unit-row" data-unit-idx="0">
                                                <div class="unit-num">Unit #1</div>
                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm btn-remove-unit"><i
                                                        class="fas fa-times"></i></button>
                                                <div class="form-row mt-2">
                                                    <div class="form-group col-md-4 mb-2">
                                                        <label class="small">Truck Type</label>
                                                        <select name="unit_truck_type[]"
                                                            class="form-control form-control-sm">
                                                            <option value="">-- Tipe --</option>
                                                            <option>Blindvan</option>
                                                            <option>L300</option>
                                                            <option>CDE</option>
                                                            <option>CDE Long</option>
                                                            <option>CDD</option>
                                                            <option>CDD Long</option>
                                                            <option>Fuso</option>
                                                            <option>Tronton Wingbox</option>
                                                            <option>Tronton Box</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-4 mb-2">
                                                        <label class="small">Nopol</label>
                                                        <input type="text" name="unit_nopol[]"
                                                            class="form-control form-control-sm text-uppercase nopol-input"
                                                            placeholder="B 1234 XYZ"
                                                            style="font-family:monospace;font-weight:600;">
                                                    </div>
                                                    <div class="form-group col-md-4 mb-2">
                                                        <label class="small">Vendor Unit</label>
                                                        <select name="unit_vendor_id[]"
                                                            class="form-control form-control-sm">
                                                            <option value="">-- Vendor --</option>
                                                            <?php foreach ($vendors ?? [] as $v): ?>
                                                                <option value="<?= $v->id ?>">
                                                                    <?= htmlspecialchars($v->nama_vendor) ?></option>
                                                            <?php endforeach ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-md-6 mb-0">
                                                        <label class="small">Nama Driver</label>
                                                        <input type="text" name="unit_driver[]"
                                                            class="form-control form-control-sm"
                                                            placeholder="Nama driver (opsional)">
                                                    </div>
                                                    <div class="form-group col-md-6 mb-0">
                                                        <label class="small">No HP Driver</label>
                                                        <input type="text" name="unit_no_hp[]"
                                                            class="form-control form-control-sm"
                                                            placeholder="08xx (opsional)">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- SUBMIT -->
                        <div class="card shadow mb-4">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Setelah simpan, lo akan diarahkan ke halaman detail untuk monitor & assign unit.
                                </small>
                                <div>
                                    <a href="<?= base_url('daily_rent') ?>" class="btn btn-secondary mr-2">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-info text-white">
                                        <i class="fas fa-save"></i> Simpan & Lanjut ke Detail
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

            // ── Unit row template ──
            const VENDOR_OPTIONS = `
        <option value="">-- Vendor --</option>
        <?php foreach ($vendors ?? [] as $v): ?>
            <option value="<?= $v->id ?>"><?= htmlspecialchars($v->nama_vendor) ?></option>
        <?php endforeach ?>
    `;
            const TRUCK_OPTIONS = `
        <option value="">-- Tipe --</option>
        <?php foreach (['Blindvan', 'L300', 'CDE', 'CDE Long', 'CDD', 'CDD Long', 'Fuso', 'Tronton Wingbox', 'Tronton Box'] as $t): ?>
            <option><?= $t ?></option>
        <?php endforeach ?>
    `;

            let unitCount = 1;
            function updateUnitBadge() {
                let visible = $('#unitContainer .unit-row').length;
                $('#unitCountBadge').text(visible);
                // Show/hide remove button on first row
                $('#unitContainer .unit-row').each(function (i) {
                    $(this).find('.unit-num').text('Unit #' + (i + 1));
                    if (i === 0 && visible === 1) $(this).find('.btn-remove-unit').hide();
                    else $(this).find('.btn-remove-unit').show();
                });
            }

            // ── Tambah unit row ──
            $('#btnAddUnit').on('click', function () {
                unitCount++;
                let html = `
        <div class="unit-row" data-unit-idx="${unitCount}">
            <div class="unit-num">Unit #${$('#unitContainer .unit-row').length + 1}</div>
            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-unit"><i class="fas fa-times"></i></button>
            <div class="form-row mt-2">
                <div class="form-group col-md-4 mb-2">
                    <label class="small">Truck Type</label>
                    <select name="unit_truck_type[]" class="form-control form-control-sm">${TRUCK_OPTIONS}</select>
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="small">Nopol</label>
                    <input type="text" name="unit_nopol[]" class="form-control form-control-sm text-uppercase nopol-input" placeholder="B 1234 XYZ" style="font-family:monospace;font-weight:600;">
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="small">Vendor Unit</label>
                    <select name="unit_vendor_id[]" class="form-control form-control-sm">${VENDOR_OPTIONS}</select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6 mb-0">
                    <label class="small">Nama Driver</label>
                    <input type="text" name="unit_driver[]" class="form-control form-control-sm" placeholder="Nama driver (opsional)">
                </div>
                <div class="form-group col-md-6 mb-0">
                    <label class="small">No HP Driver</label>
                    <input type="text" name="unit_no_hp[]" class="form-control form-control-sm" placeholder="08xx (opsional)">
                </div>
            </div>
        </div>`;
                $('#unitContainer').append(html);
                updateUnitBadge();
            });

            // ── Hapus unit row ──
            $(document).on('click', '.btn-remove-unit', function () {
                $(this).closest('.unit-row').remove();
                updateUnitBadge();
            });

            // ── Auto uppercase nopol ──
            $(document).on('input', '.nopol-input', function () {
                $(this).val($(this).val().toUpperCase());
            });

            // ── Duration Preview ──
            function updateDuration() {
                let start = $('#rentStartDate').val();
                let end = $('#rentEndDate').val();
                if (!start || !end) { $('#durationPreview').hide(); return; }
                let diff = Math.round((new Date(end) - new Date(start)) / 86400000);
                if (diff < 0) {
                    $('#durationPreview').show();
                    $('#durationText').html('<span class="text-danger">⚠️ Tanggal selesai sebelum tanggal mulai!</span>');
                } else {
                    $('#durationPreview').show();
                    $('#durationText').html(diff + ' hari');
                }
            }
            $('#rentStartDate, #rentEndDate').on('change', updateDuration);

            // ── Form Validation ──
            $('#formTambah').on('submit', function (e) {
                let start = $('[name=rent_start_date]').val();
                let end = $('[name=rent_end_date]').val();

                if (start && end && end < start) {
                    e.preventDefault();
                    alert('Tanggal selesai tidak boleh sebelum tanggal mulai!');
                    $('[name=rent_end_date]').focus();
                    return false;
                }

                // Cek nopol duplikat antar unit dalam form yang sama
                let nopols = [];
                let dupFound = false;
                $('[name="unit_nopol[]"]').each(function () {
                    let val = $(this).val().trim().toUpperCase();
                    if (!val) return;
                    if (nopols.includes(val)) { dupFound = true; return false; }
                    nopols.push(val);
                });
                if (dupFound) {
                    e.preventDefault();
                    alert('Terdapat nopol yang sama di beberapa unit! Harap periksa kembali.');
                    return false;
                }
            });

            updateUnitBadge();
        });
    </script>
</body>

</html>