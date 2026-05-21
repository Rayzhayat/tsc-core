<!-- ubah -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        body {
            background-color: #f0f2f5;
        }

        /* ===== CARD ===== */
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            padding: 14px 20px;
        }

        .card-body {
            padding: 28px 30px;
        }

        /* ===== SECTION HEADER ===== */
        .section-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            margin-top: 8px;
            margin-bottom: 18px;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.02em;
            box-shadow: 0 2px 6px rgba(78, 115, 223, 0.3);
        }

        .section-header i {
            margin-right: 6px;
        }

        /* ===== FORM ELEMENTS ===== */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #4e4f5e;
            margin-bottom: 6px;
        }

        .form-control {
            height: 40px;
            border-radius: 6px;
            border: 1.5px solid #d1d3e2;
            font-size: 0.875rem;
            padding: 8px 12px;
            color: #3a3b45;
            background-color: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.12);
            outline: none;
        }

        .form-control::placeholder {
            color: #b7b9cc;
            font-size: 0.85rem;
        }

        textarea.form-control {
            height: auto;
            resize: vertical;
        }

        select.form-control {
            height: 40px;
            cursor: pointer;
        }

        /* File input */
        .file-upload-box {
            border: 1.5px dashed #c5c7d8;
            border-radius: 6px;
            padding: 10px 14px;
            background: #fafbff;
            transition: border-color 0.2s;
        }

        .file-upload-box:hover {
            border-color: #4e73df;
        }

        .form-control-file {
            display: block;
            width: 100%;
            font-size: 0.85rem;
            color: #6e707e;
        }

        .field-hint {
            font-size: 0.78rem;
            color: #b7b9cc;
            margin-top: 4px;
            display: block;
        }

        /* ===== FOTO CURRENT (edit mode) ===== */
        .foto-current {
            border: 1.5px solid #e3e6f0;
            border-radius: 8px;
            padding: 8px 10px;
            background: #f8f9fc;
            margin-bottom: 8px;
            display: inline-block;
        }

        .foto-current img {
            border-radius: 5px;
            display: block;
        }

        .foto-current .foto-label {
            font-size: 0.75rem;
            color: #858796;
            margin-top: 5px;
        }

        /* ===== READONLY INFO CARDS ===== */
        .readonly-card {
            border-radius: 8px;
            border: 1px solid #e3e6f0;
            padding: 14px 16px;
            background: #f8f9fc;
            height: 100%;
        }

        .readonly-card .rc-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #858796;
            margin-bottom: 5px;
        }

        .readonly-card .rc-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #3a3b45;
            line-height: 1.2;
        }

        .readonly-card .rc-note {
            font-size: 0.72rem;
            color: #b7b9cc;
            margin-top: 4px;
        }

        /* ===== INFO BOXES ===== */
        .info-managed {
            background: #f0f7ff;
            border: 1.5px dashed #4e73df;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.85rem;
            color: #4e73df;
        }

        .info-managed i {
            margin-right: 5px;
        }

        /* ===== BUTTONS ===== */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            padding-top: 4px;
        }

        .btn {
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 9px 20px;
            transition: all 0.2s;
        }

        .btn i {
            margin-right: 6px;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f6c23e, #e0a800);
            border: none;
            color: #fff;
            box-shadow: 0 2px 6px rgba(246, 194, 62, 0.35);
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #e0a800, #c69500);
            color: #fff;
        }

        .btn-info {
            background: linear-gradient(135deg, #36b9cc, #1a8fa3);
            border: none;
            color: #fff;
        }

        .btn-secondary {
            background: #858796;
            border: none;
            color: #fff;
        }

        .form-divider {
            border: none;
            border-top: 1.5px solid #e8eaf0;
            margin: 24px 0 20px;
        }

        .alert {
            border-radius: 8px;
            font-size: 0.875rem;
            border: none;
            padding: 12px 16px;
        }

        .alert-danger {
            background: #fde8e8;
            color: #c0392b;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- JUDUL + TOMBOL -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-truck text-warning"></i> <?= $title ?>
                        </h1>
                        <div class="d-flex gap-2" style="gap:8px;">
                            <a href="<?= base_url('unit/detail/' . $unit->id) ?>" class="btn btn-info btn-sm shadow-sm">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </a>
                            <a href="<?= base_url('unit') ?>" class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <!-- ALERT -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- ── INFO CARDS READONLY ── -->
                    <div class="row mb-3">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="readonly-card">
                                <div class="rc-label">
                                    <i class="fas fa-tachometer-alt text-primary"></i> KM Terkini
                                </div>
                                <div class="rc-value text-primary">
                                    <?= $unit->current_km ? number_format($unit->current_km) . ' km' : '—' ?>
                                </div>
                                <div class="rc-note">Diupdate otomatis dari pencatatan BBM</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="readonly-card">
                                <div class="rc-label">
                                    <i class="fas fa-gas-pump text-success"></i> Konsumsi BBM
                                </div>
                                <div class="rc-value text-success">
                                    <?= $unit->konsumsi_bbm ? $unit->konsumsi_bbm . ' km/L' : '—' ?>
                                </div>
                                <div class="rc-note">Rata-rata dari histori BBM</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="readonly-card">
                                <div class="rc-label">
                                    <i class="fas fa-wrench text-warning"></i> Next Service KM
                                </div>
                                <div class="rc-value text-warning">
                                    <?= $unit->next_service_km ? number_format($unit->next_service_km) . ' km' : '—' ?>
                                </div>
                                <div class="rc-note">Diupdate dari histori service</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="readonly-card">
                                <div class="rc-label">
                                    <i class="fas fa-calendar text-info"></i> Service Terakhir
                                </div>
                                <div class="rc-value text-info">
                                    <?= $unit->last_service_date ? date('d/m/Y', strtotime($unit->last_service_date)) : '—' ?>
                                </div>
                                <div class="rc-note">Diupdate dari histori service</div>
                            </div>
                        </div>
                    </div>

                    <div class="info-managed mb-4">
                        <i class="fas fa-lock"></i>
                        <strong>Data di atas tidak bisa diubah dari sini</strong> — dikelola otomatis melalui pencatatan
                        di halaman
                        <a href="<?= base_url('unit/detail/' . $unit->id) ?>" class="font-weight-bold"
                            style="color:#224abe;">
                            Detail Unit <i class="fas fa-arrow-right fa-xs"></i>
                        </a>
                    </div>

                    <!-- CARD FORM -->
                    <div class="card shadow mb-5">
                        <div class="card-header py-3"
                            style="background: linear-gradient(135deg, #f6c23e 0%, #dda520 100%);">
                            <h6 class="m-0 font-weight-bold text-white">
                                <i class="fas fa-edit"></i> Form Ubah Unit —
                                <?= htmlspecialchars(strtoupper($unit->no_polisi ?? '')) ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('unit/proses_ubah/' . $unit->id) ?>" method="POST"
                                enctype="multipart/form-data" id="formUnit">

                                <!-- ── SECTION 1: IDENTITAS UNIT ── -->
                                <div class="section-header">
                                    <i class="fas fa-id-card"></i> Identitas Unit
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="no_polisi">No. Polisi <span class="text-danger">*</span></label>
                                            <input type="text" name="no_polisi" id="no_polisi"
                                                class="form-control text-uppercase" required maxlength="15"
                                                value="<?= htmlspecialchars($unit->no_polisi ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tipe_unit">Tipe Unit <span class="text-danger">*</span></label>
                                            <select name="tipe_unit" id="tipe_unit" class="form-control" required>
                                                <option value="">— Pilih Tipe —</option>
                                                <?php foreach (['Blindvan', 'L300', 'CDE', 'CDE Long', 'CDD', 'CDD Long', 'Fuso', 'Tronton Wingbox', 'Tronton Box'] as $tipe): ?>
                                                    <option value="<?= $tipe ?>" <?= ($unit->tipe_unit ?? '') == $tipe ? 'selected' : '' ?>>
                                                        <?= $tipe ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tahun_unit">Tahun Unit <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="tahun_unit" id="tahun_unit" class="form-control"
                                                required min="1990" max="<?= date('Y') + 1 ?>"
                                                value="<?= $unit->tahun_unit ?? '' ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tipe_box">Tipe Box <span class="text-danger">*</span></label>
                                            <select name="tipe_box" id="tipe_box" class="form-control" required>
                                                <option value="">— Pilih Tipe Box —</option>
                                                <?php foreach (['Bak Kayu', 'Bak Besi', 'Box Besi', 'Box Alumunium', 'Refrigerator', 'Wingbox', 'Lowbed', 'Trailer'] as $box): ?>
                                                    <option value="<?= $box ?>" <?= ($unit->tipe_box ?? '') == $box ? 'selected' : '' ?>>
                                                        <?= $box ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tonase">Tonase (Ton) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.1" name="tonase" id="tonase"
                                                class="form-control" required value="<?= $unit->tonase ?? '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="kapasitas_kg">Kapasitas (KG)</label>
                                            <input type="number" name="kapasitas_kg" id="kapasitas_kg"
                                                class="form-control" min="0" value="<?= $unit->kapasitas_kg ?? '' ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- ── SECTION 2: DIMENSI BOX ── -->
                                <div class="section-header">
                                    <i class="fas fa-ruler-combined"></i> Dimensi Box
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="panjang">Panjang (m) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="panjang" id="panjang"
                                                class="form-control" required value="<?= $unit->panjang ?? '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="lebar">Lebar (m) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="lebar" id="lebar"
                                                class="form-control" required value="<?= $unit->lebar ?? '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tinggi">Tinggi (m) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="tinggi" id="tinggi"
                                                class="form-control" required value="<?= $unit->tinggi ?? '' ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- ── SECTION 3: SPESIFIKASI OPERASIONAL ── -->
                                <div class="section-header">
                                    <i class="fas fa-cogs"></i> Spesifikasi Operasional
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status_unit">Status Unit</label>
                                            <select name="status_unit" id="status_unit" class="form-control">
                                                <?php foreach (['aktif' => 'Aktif', 'maintenance' => 'Maintenance', 'rusak' => 'Rusak', 'dijual' => 'Dijual', 'nonaktif' => 'Non-Aktif'] as $val => $label): ?>
                                                    <option value="<?= $val ?>" <?= ($unit->status_unit ?? 'aktif') == $val ? 'selected' : '' ?>>
                                                        <?= $label ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bahan_bakar">Jenis BBM</label>
                                            <select name="bahan_bakar" id="bahan_bakar" class="form-control">
                                                <option value="">— Pilih BBM —</option>
                                                <?php foreach (['bensin' => 'Bensin', 'solar' => 'Solar', 'pertamax' => 'Pertamax', 'pertalite' => 'Pertalite'] as $val => $label): ?>
                                                    <option value="<?= $val ?>" <?= ($unit->bahan_bakar ?? '') == $val ? 'selected' : '' ?>>
                                                        <?= $label ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- ── SECTION 4: FOTO DOKUMEN ── -->
                                <div class="section-header">
                                    <i class="fas fa-images"></i> Foto Dokumen
                                </div>

                                <div class="alert alert-info mb-3" style="font-size:0.85rem; border-radius:8px;">
                                    <i class="fas fa-info-circle"></i>
                                    Tanggal expired STNK, KIR, dan dokumen lainnya dikelola di
                                    <a href="<?= base_url('unit/detail/' . $unit->id) ?>"
                                        class="alert-link font-weight-bold">
                                        Detail Unit → Tab Dokumen
                                    </a>.
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Foto STNK</label>
                                            <?php if (!empty($unit->foto_stnk)): ?>
                                                <div class="foto-current">
                                                    <a href="<?= base_url('uploads/stnk/' . $unit->foto_stnk) ?>"
                                                        target="_blank">
                                                        <img src="<?= base_url('uploads/stnk/' . $unit->foto_stnk) ?>"
                                                            width="80" alt="STNK">
                                                    </a>
                                                    <div class="foto-label">✓ Klik untuk lihat ukuran penuh</div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="file-upload-box">
                                                <input type="file" name="foto_stnk" class="form-control-file"
                                                    accept="image/*">
                                            </div>
                                            <span class="field-hint">JPG / PNG, maks 2MB. Kosongkan jika tidak ingin
                                                mengganti</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Foto KIR</label>
                                            <?php if (!empty($unit->foto_kir)): ?>
                                                <div class="foto-current">
                                                    <a href="<?= base_url('uploads/kir/' . $unit->foto_kir) ?>"
                                                        target="_blank">
                                                        <img src="<?= base_url('uploads/kir/' . $unit->foto_kir) ?>"
                                                            width="80" alt="KIR">
                                                    </a>
                                                    <div class="foto-label">✓ Klik untuk lihat ukuran penuh</div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="file-upload-box">
                                                <input type="file" name="foto_kir" class="form-control-file"
                                                    accept="image/*">
                                            </div>
                                            <span class="field-hint">JPG / PNG, maks 2MB. Kosongkan jika tidak ingin
                                                mengganti</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Foto Barcode Solar</label>
                                            <?php if (!empty($unit->foto_barcode_solar)): ?>
                                                <div class="foto-current">
                                                    <a href="<?= base_url('uploads/barcode_solar/' . $unit->foto_barcode_solar) ?>"
                                                        target="_blank">
                                                        <img src="<?= base_url('uploads/barcode_solar/' . $unit->foto_barcode_solar) ?>"
                                                            width="80" alt="Barcode Solar">
                                                    </a>
                                                    <div class="foto-label">✓ Klik untuk lihat ukuran penuh</div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="file-upload-box">
                                                <input type="file" name="foto_barcode_solar" id="foto_barcode_solar"
                                                    class="form-control-file" accept="image/*">
                                            </div>
                                            <div id="preview_barcode_wrap" style="display:none" class="mt-2">
                                                <img id="preview_barcode" src="" alt="Preview" width="80"
                                                    class="img-thumbnail" style="border-radius:6px;">
                                            </div>
                                            <span class="field-hint">JPG / PNG, maks 2MB. Kosongkan jika tidak ingin
                                                mengganti</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- ── SECTION 5: KETERANGAN ── -->
                                <div class="section-header">
                                    <i class="fas fa-clipboard"></i> Keterangan
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="keterangan">Catatan Tambahan</label>
                                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3"
                                                placeholder="Catatan atau informasi tambahan..."><?= htmlspecialchars($unit->keterangan ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <hr class="form-divider">
                                <div class="form-actions">
                                    <a href="<?= base_url('unit/detail/' . $unit->id) ?>" class="btn btn-info">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                    <a href="<?= base_url('unit') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save"></i> Simpan Perubahan
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
        $(document).ready(function () {
            // Auto format no polisi
            $('#no_polisi').on('input', function () {
                let value = this.value.replace(/\s+/g, '').replace(/[^A-Z0-9]/gi, '').toUpperCase();
                let formatted = '', prefix = '', i = 0;
                if (value.length > 0) {
                    while (i < value.length && isNaN(value[i])) { prefix += value[i]; i++; }
                    if (prefix) formatted += prefix + ' ';
                    let numberPart = '';
                    while (i < value.length && !isNaN(value[i]) && numberPart.length < 4) { numberPart += value[i]; i++; }
                    if (numberPart) formatted += numberPart + ' ';
                    let suffix = value.substring(i);
                    if (suffix) formatted += suffix;
                    formatted = formatted.trimEnd();
                }
                if (formatted.length > 15) formatted = formatted.substring(0, 15);
                this.value = formatted;
            });

            // Preview barcode baru
            $('#foto_barcode_solar').on('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        $('#preview_barcode').attr('src', e.target.result);
                        $('#preview_barcode_wrap').show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#preview_barcode_wrap').hide();
                }
            });

            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
        });
    </script>
</body>

</html>