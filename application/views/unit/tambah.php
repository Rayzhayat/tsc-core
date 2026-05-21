<!-- tambah -->
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

        .form-control[readonly] {
            background-color: #f4f5f9;
            color: #9a9bb0;
            cursor: not-allowed;
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

        /* Preview image */
        .preview-img {
            width: 100%;
            max-width: 100px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #e3e6f0;
            display: none;
            margin-top: 8px;
        }

        /* ===== FIELD HINTS ===== */
        .field-hint {
            font-size: 0.78rem;
            color: #b7b9cc;
            margin-top: 4px;
            display: block;
        }

        /* ===== OPTIONAL BADGE ===== */
        .optional-badge {
            font-size: 0.68rem;
            background: #e8eaf0;
            color: #858796;
            padding: 2px 7px;
            border-radius: 10px;
            font-weight: 500;
            vertical-align: middle;
            margin-left: 4px;
        }

        /* ===== INFO BOX ===== */
        .info-managed {
            background: #f0f7ff;
            border: 1.5px dashed #4e73df;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.85rem;
            color: #4e73df;
        }

        .info-managed i {
            margin-right: 6px;
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

        .btn-primary {
            background: linear-gradient(135deg, #4e73df, #2e59d9);
            border: none;
            box-shadow: 0 2px 6px rgba(78, 115, 223, 0.35);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2e59d9, #1a3fbb);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f6c23e, #e0a800);
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
                            <i class="fas fa-truck text-primary"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('unit') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- ALERT -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- CARD FORM -->
                    <div class="card shadow mb-5">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">
                                <i class="fas fa-plus-circle"></i> Form Tambah Unit Kendaraan
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('unit/proses_tambah') ?>" method="POST"
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
                                                class="form-control text-uppercase" required
                                                placeholder="Contoh: B 1234 XYZ" maxlength="15" autocomplete="off">
                                            <span class="field-hint">Otomatis diformat kapital</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tipe_unit">Tipe Unit <span class="text-danger">*</span></label>
                                            <select name="tipe_unit" id="tipe_unit" class="form-control" required>
                                                <option value="">— Pilih Tipe —</option>
                                                <option value="Blindvan">Blindvan</option>
                                                <option value="L300">L300</option>
                                                <option value="CDE">CDE</option>
                                                <option value="CDE Long">CDE Long</option>
                                                <option value="CDD">CDD</option>
                                                <option value="CDD Long">CDD Long</option>
                                                <option value="Fuso">Fuso</option>
                                                <option value="Tronton Wingbox">Tronton Wingbox</option>
                                                <option value="Tronton Box">Tronton Box</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tahun_unit">Tahun Unit <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="tahun_unit" id="tahun_unit" class="form-control"
                                                required placeholder="<?= date('Y') ?>" min="1990"
                                                max="<?= date('Y') + 1 ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tipe_box">Tipe Box <span class="text-danger">*</span></label>
                                            <select name="tipe_box" id="tipe_box" class="form-control" required>
                                                <option value="">— Pilih Tipe Box —</option>
                                                <option value="Bak Kayu">Bak Kayu</option>
                                                <option value="Bak Besi">Bak Besi</option>
                                                <option value="Box Besi">Box Besi</option>
                                                <option value="Box Alumunium">Box Alumunium</option>
                                                <option value="Refrigerator">Refrigerator</option>
                                                <option value="Wingbox">Wingbox</option>
                                                <option value="Lowbed">Lowbed</option>
                                                <option value="Trailer">Trailer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tonase">Tonase (Ton) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.1" name="tonase" id="tonase"
                                                class="form-control" required placeholder="8.5">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="kapasitas_kg">Kapasitas (KG)</label>
                                            <input type="number" name="kapasitas_kg" id="kapasitas_kg"
                                                class="form-control" placeholder="5000" min="0">
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
                                                class="form-control" placeholder="6.0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="lebar">Lebar (m) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="lebar" id="lebar"
                                                class="form-control" placeholder="2.4" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tinggi">Tinggi (m) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="tinggi" id="tinggi"
                                                class="form-control" placeholder="2.4" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- ── SECTION 3: SPESIFIKASI OPERASIONAL ── -->
                                <div class="section-header">
                                    <i class="fas fa-cogs"></i> Spesifikasi Operasional
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="status_unit">Status Unit</label>
                                            <select name="status_unit" id="status_unit" class="form-control">
                                                <option value="aktif" selected>Aktif</option>
                                                <option value="maintenance">Maintenance</option>
                                                <option value="rusak">Rusak</option>
                                                <option value="dijual">Dijual</option>
                                                <option value="nonaktif">Non-Aktif</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="bahan_bakar">Jenis BBM</label>
                                            <select name="bahan_bakar" id="bahan_bakar" class="form-control">
                                                <option value="">— Pilih BBM —</option>
                                                <option value="bensin">Bensin</option>
                                                <option value="solar" selected>Solar</option>
                                                <option value="pertamax">Pertamax</option>
                                                <option value="pertalite">Pertalite</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="current_km">KM Awal (Odometer)</label>
                                            <input type="number" name="current_km" id="current_km" class="form-control"
                                                placeholder="0" min="0" value="0">
                                            <span class="field-hint">KM saat unit pertama didaftarkan</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- ── SECTION 4: DOKUMEN & FOTO ── -->
                                <div class="section-header">
                                    <i class="fas fa-file-alt"></i> Dokumen &amp; Foto
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="stnk_expired">STNK Expired</label>
                                            <input type="date" name="stnk_expired" id="stnk_expired"
                                                class="form-control">
                                            <span class="field-hint">Tanggal expired STNK</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="kir_expired">KIR Expired</label>
                                            <input type="date" name="kir_expired" id="kir_expired" class="form-control">
                                            <span class="field-hint">Tanggal expired KIR</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Foto STNK <span class="optional-badge">Opsional</span></label>
                                            <div class="file-upload-box">
                                                <input type="file" name="foto_stnk" id="foto_stnk"
                                                    class="form-control-file" accept="image/*">
                                            </div>
                                            <img id="preview_stnk" class="preview-img" src="" alt="Preview STNK">
                                            <span class="field-hint">JPG / PNG, maks 2MB</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Foto KIR <span class="optional-badge">Opsional</span></label>
                                            <div class="file-upload-box">
                                                <input type="file" name="foto_kir" id="foto_kir"
                                                    class="form-control-file" accept="image/*">
                                            </div>
                                            <img id="preview_kir" class="preview-img" src="" alt="Preview KIR">
                                            <span class="field-hint">JPG / PNG, maks 2MB</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Foto Barcode Solar <span
                                                    class="optional-badge">Opsional</span></label>
                                            <div class="file-upload-box">
                                                <input type="file" name="foto_barcode_solar" id="foto_barcode_solar"
                                                    class="form-control-file" accept="image/*">
                                            </div>
                                            <img id="preview_barcode" class="preview-img" src="" alt="Preview Barcode">
                                            <span class="field-hint">JPG / PNG, maks 2MB</span>
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
                                                placeholder="Catatan atau informasi tambahan untuk unit ini..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info data dinamis -->
                                <div class="info-managed mb-4">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Data berikut dikelola otomatis setelah unit tersimpan:</strong>
                                    Histori BBM, Histori Service, Konsumsi rata-rata, KM terkini, dan Dokumen expired
                                    — semua dicatat &amp; diupdate melalui halaman <strong>Detail Unit</strong>.
                                </div>

                                <hr class="form-divider">
                                <div class="form-actions">
                                    <a href="<?= base_url('unit') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="reset" class="btn btn-warning" id="btnReset">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Unit
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

            // Preview foto
            function previewFoto(inputId, previewId) {
                $('#' + inputId).on('change', function () {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = e => $('#' + previewId).attr('src', e.target.result).show();
                        reader.readAsDataURL(file);
                    } else {
                        $('#' + previewId).hide();
                    }
                });
            }

            previewFoto('foto_stnk', 'preview_stnk');
            previewFoto('foto_kir', 'preview_kir');
            previewFoto('foto_barcode_solar', 'preview_barcode');

            $('#btnReset').on('click', function () { $('.preview-img').hide(); });
            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
        });
    </script>
</body>

</html>