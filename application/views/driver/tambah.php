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

        .field-hint {
            font-size: 0.78rem;
            color: #b7b9cc;
            margin-top: 4px;
            display: block;
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
                            <i class="fas fa-user-tie text-primary"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('driver') ?>" class="btn btn-secondary btn-sm shadow-sm">
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
                                <i class="fas fa-plus-circle"></i> Form Tambah Driver
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('driver/proses_tambah') ?>" method="POST"
                                enctype="multipart/form-data" id="formDriver">

                                <!-- SECTION 1: IDENTITAS DRIVER -->
                                <div class="section-header">
                                    <i class="fas fa-id-card"></i> Identitas Driver
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nama_driver">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_driver" id="nama_driver"
                                                class="form-control" required
                                                placeholder="Nama lengkap driver" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nik">NIK <span class="text-danger">*</span></label>
                                            <input type="text" name="nik" id="nik" class="form-control" required
                                                placeholder="Masukkan 16 digit NIK"
                                                maxlength="16" pattern="[0-9]{16}" autocomplete="off">
                                            <span class="field-hint">16 digit angka tanpa spasi</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="no_hp">No. HP</label>
                                            <input type="text" name="no_hp" id="no_hp" class="form-control"
                                                placeholder="08xxxxxxxxxx" maxlength="15">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" name="email" id="email" class="form-control"
                                                placeholder="email@example.com">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tanggal_bergabung">Tanggal Bergabung</label>
                                            <input type="date" name="tanggal_bergabung" id="tanggal_bergabung"
                                                class="form-control" value="<?= date('Y-m-d') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="alamat">Alamat Lengkap</label>
                                            <textarea name="alamat" id="alamat" class="form-control" rows="2"
                                                placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION 2: DATA SIM -->
                                <div class="section-header">
                                    <i class="fas fa-id-badge"></i> Data SIM
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="sim">No. SIM <span class="text-danger">*</span></label>
                                            <input type="text" name="sim" id="sim" class="form-control" required
                                                placeholder="Nomor SIM" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tipe_sim">Tipe SIM <span class="text-danger">*</span></label>
                                            <select name="tipe_sim" id="tipe_sim" class="form-control" required>
                                                <option value="">— Pilih Tipe SIM —</option>
                                                <option value="A">SIM A (Mobil)</option>
                                                <option value="B1">SIM B1 (Mobil / Truk Ringan)</option>
                                                <option value="B2" selected>SIM B2 (Truk / Bus)</option>
                                                <option value="C">SIM C (Motor)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="masa_berlaku_sim">Masa Berlaku SIM <span class="text-danger">*</span></label>
                                            <input type="date" name="masa_berlaku_sim" id="masa_berlaku_sim"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Foto SIM <span class="text-danger">*</span></label>
                                            <div class="file-upload-box">
                                                <input type="file" name="foto_sim" id="foto_sim"
                                                    class="form-control-file" accept="image/*" required>
                                            </div>
                                            <span class="field-hint">Format JPG / PNG, maksimal 2MB</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Foto Driver <span class="text-danger">*</span></label>
                                            <div class="file-upload-box">
                                                <input type="file" name="foto_driver" id="foto_driver"
                                                    class="form-control-file" accept="image/*" required>
                                            </div>
                                            <span class="field-hint">Format JPG / PNG, maksimal 2MB</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION 3: STATUS & PERFORMANCE -->
                                <div class="section-header">
                                    <i class="fas fa-chart-line"></i> Status &amp; Performance
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="status_driver">Status Driver</label>
                                            <select name="status_driver" id="status_driver" class="form-control">
                                                <option value="aktif" selected>Aktif</option>
                                                <option value="cuti">Cuti</option>
                                                <option value="resign">Resign</option>
                                                <option value="nonaktif">Non-Aktif</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="rating">Rating (0 – 5)</label>
                                            <input type="number" name="rating" id="rating" class="form-control"
                                                placeholder="0.00" min="0" max="5" step="0.01" value="0">
                                            <span class="field-hint">Rating awal performance driver</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="total_trip">Total Trip</label>
                                            <input type="number" name="total_trip" id="total_trip" class="form-control"
                                                placeholder="0" min="0" step="1" value="0" readonly>
                                            <span class="field-hint">Otomatis diupdate dari sistem</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION 4: KETERANGAN -->
                                <div class="section-header">
                                    <i class="fas fa-clipboard"></i> Keterangan
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="keterangan">Catatan Tambahan</label>
                                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3"
                                                placeholder="Catatan atau informasi tambahan untuk driver ini..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <hr class="form-divider">
                                <div class="form-actions">
                                    <a href="<?= base_url('driver') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="reset" class="btn btn-warning">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Driver
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
            $('#nik').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            $('#no_hp').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>

</html>