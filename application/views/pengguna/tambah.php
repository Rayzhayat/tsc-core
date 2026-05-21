<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .section-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 24px 0 16px;
        }

        .section-divider .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6c757d;
            white-space: nowrap;
        }

        .section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e3e6f0;
        }

        .profile-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 8px;
        }

        .profile-option {
            position: relative;
            cursor: pointer;
        }

        .profile-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .profile-option img {
            width: 100%;
            height: 72px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e3e6f0;
            transition: all 0.2s ease;
        }

        .profile-option input[type="radio"]:checked+img {
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.2);
            transform: scale(1.06);
        }

        .profile-option:hover img {
            border-color: #4e73df;
        }

        .profile-option .check-icon {
            position: absolute;
            top: -4px;
            right: 4px;
            background: #4e73df;
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        .profile-option input[type="radio"]:checked~.check-icon {
            display: flex;
        }

        .preview-ktp {
            max-width: 160px;
            max-height: 110px;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
        }

        .golongan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(62px, 1fr));
            gap: 8px;
            margin-top: 8px;
        }

        .golongan-option {
            position: relative;
        }

        .golongan-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .golongan-option label {
            display: block;
            text-align: center;
            padding: 8px 4px;
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
        }

        .golongan-option input[type="radio"]:checked+label {
            border-color: #4e73df;
            background: #eef0fd;
            color: #4e73df;
        }

        .golongan-option label:hover {
            border-color: #b7c0ee;
            color: #4e73df;
        }

        .cuti-counter {
            display: flex;
            align-items: center;
            gap: 0;
            width: fit-content;
        }

        .cuti-counter button {
            width: 36px;
            height: 36px;
            border: 1px solid #d1d3e2;
            background: #f8f9fc;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s;
            color: #4e73df;
        }

        .cuti-counter button:hover {
            background: #eef0fd;
        }

        .cuti-counter button:first-child {
            border-radius: 6px 0 0 6px;
        }

        .cuti-counter button:last-child {
            border-radius: 0 6px 6px 0;
        }

        .cuti-counter input {
            width: 56px;
            height: 36px;
            border: 1px solid #d1d3e2;
            border-left: none;
            border-right: none;
            text-align: center;
            font-weight: 700;
            font-size: 1rem;
            color: #333;
        }

        .info-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f4ff;
            border: 1px solid #d0d8f5;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.78rem;
            color: #4e73df;
            font-weight: 600;
        }

        .group-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 8px;
        }

        .group-option {
            position: relative;
        }

        .group-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .group-option label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border: 2px solid #e3e6f0;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
            height: 100%;
        }

        .group-option label .group-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .group-option input[type="radio"]:checked+label {
            border-color: #4e73df;
            background: #eef0fd;
            color: #4e73df;
        }

        .group-option label:hover {
            border-color: #b7c0ee;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-plus"></i> <?= $title ?></h1>
                        <a href="<?= base_url('pengguna') ?>" class="btn btn-secondary btn-sm"><i
                                class="fas fa-arrow-left"></i> Kembali</a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <form action="<?= base_url('pengguna/proses_tambah') ?>" method="POST" enctype="multipart/form-data"
                        id="form-tambah">
                        <div class="row">

                            <!-- KOLOM KIRI -->
                            <div class="col-lg-6">
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-primary py-2">
                                        <h6 class="m-0 text-white fw-bold"><i class="fas fa-key me-1"></i> Akun & Akses
                                            Sistem</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold">NIK <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="nik" class="form-control"
                                                placeholder="16 digit NIK" maxlength="16" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold">Nama Lengkap <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="nama" id="nama" class="form-control"
                                                placeholder="Masukkan nama lengkap" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold">Tanggal Lahir <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                                                class="form-control" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-at"></i></span>
                                                <input type="text" name="username" id="username"
                                                    class="form-control bg-light" placeholder="Otomatis dari nama"
                                                    readonly>
                                            </div>
                                            <small class="text-muted"><i class="fas fa-magic"></i> Generate otomatis
                                                dari nama</small>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold">Password</label>
                                            <input type="password" name="password" class="form-control"
                                                placeholder="Kosongkan untuk default (123456)">
                                            <small class="text-muted"><i class="fas fa-key"></i> Default:
                                                <code>123456</code></small>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Level Akses <span
                                                    class="text-danger">*</span></label>
                                            <select name="user_level" class="form-control" required>
                                                <option value="">-- Pilih Level --</option>
                                                <optgroup label="── Management ──">
                                                    <option value="superadmin">🔴 Superadmin</option>
                                                    <option value="viewer">🟣 Viewer / Manajemen</option>
                                                    <option value="head_of_departemen">🔴 Head of Departemen</option>
                                                    <option value="operational_lead">🟠 Operational Lead</option>
                                                    <option value="administration_lead">🟠 Administration Lead</option>
                                                    <option value="hr_staff">🟢 HR Staff</option>
                                                </optgroup>
                                                <optgroup label="── Staff ──">
                                                    <option value="admin_operational">🟠 Admin Operational</option>
                                                    <option value="operational_staff">🟡 Operational Staff</option>
                                                    <option value="finance_staff">🟢 Finance Staff</option>
                                                    <option value="fleet_staff">🔵 Fleet Staff</option>
                                                    <option value="admin_document">🟤 Admin Document</option>
                                                </optgroup>
                                                <optgroup label="── Operasional ──">
                                                    <option value="yamazaki">🟡 Yamazaki</option>
                                                    <option value="tsf">🟠 TSF</option>
                                                    <option value="sinar_boga">🟢 Sinar Boga</option>
                                                    <option value="rorotan">🔵 Rorotan</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-info py-2">
                                        <h6 class="m-0 text-white fw-bold"><i class="fas fa-user-circle me-1"></i> Foto
                                            Profil & KTP</h6>
                                    </div>
                                    <div class="card-body">
                                        <label class="font-weight-bold">Pilih Foto Profil <span
                                                class="text-danger">*</span></label>
                                        <div class="profile-selector">
                                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                                <label class="profile-option">
                                                    <input type="radio" name="foto_profil" value="default-<?= $i ?>.png"
                                                        <?= $i == 1 ? 'checked' : '' ?>>
                                                    <img src="<?= base_url('uploads/profil/default-' . $i . '.png') ?>"
                                                        alt="Avatar <?= $i ?>">
                                                    <span class="check-icon"><i class="fas fa-check"></i></span>
                                                </label>
                                            <?php endfor ?>
                                        </div>
                                        <div class="section-divider mt-4"><span class="section-label">Foto KTP</span>
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" name="foto_ktp" class="custom-file-input" id="foto_ktp"
                                                accept="image/*" required>
                                            <label class="custom-file-label" for="foto_ktp">Pilih file KTP...</label>
                                        </div>
                                        <img src="" alt="Preview KTP" class="preview-ktp img-thumbnail"
                                            id="preview-ktp">
                                        <small class="text-muted d-block mt-1"><i class="fas fa-image"></i> Max 2MB ·
                                            JPG, JPEG, PNG</small>
                                    </div>
                                </div>
                            </div>

                            <!-- KOLOM KANAN -->
                            <div class="col-lg-6">
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-success py-2">
                                        <h6 class="m-0 text-white fw-bold"><i class="fas fa-id-badge me-1"></i> Data
                                            Kepegawaian</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold">Tanggal Bergabung</label>
                                            <input type="date" name="tanggal_join" class="form-control">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold">Status Kepegawaian</label>
                                            <div class="d-flex gap-2 flex-wrap mt-1">
                                                <?php foreach ([['Tetap', 'success', 'fa-user-check'], ['Kontrak', 'warning', 'fa-file-contract'], ['Magang', 'info', 'fa-user-graduate']] as [$val, $color, $icon]): ?>
                                                    <label
                                                        class="d-flex align-items-center gap-2 px-3 py-2 border rounded status-card"
                                                        data-val="<?= $val ?>" style="cursor:pointer; transition:all 0.2s;">
                                                        <input type="radio" name="status_kepegawaian" value="<?= $val ?>"
                                                            class="status-radio" style="display:none;">
                                                        <i class="fas <?= $icon ?> text-<?= $color ?>"></i>
                                                        <span class="font-weight-bold small"><?= $val ?></span>
                                                    </label>
                                                <?php endforeach ?>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold">Group Karyawan <span
                                                    class="text-muted small fw-normal ms-1">— Pilih
                                                    divisi/perusahaan</span></label>
                                            <?php $groups = [['Yamazaki Staff', '#c2185b'], ['Admin TSC', '#1565c0'], ['Operasional TSC', '#2e7d32'], ['TSF Staff', '#f57f17'], ['Sinar Boga Staff', '#6a1b9a'], ['Rorotan Staff', '#bf360c']]; ?>
                                            <div class="group-grid">
                                                <?php foreach ($groups as [$grp, $clr]): ?>
                                                    <div class="group-option">
                                                        <input type="radio" name="group_karyawan"
                                                            id="grp_<?= str_replace(' ', '_', $grp) ?>" value="<?= $grp ?>">
                                                        <label for="grp_<?= str_replace(' ', '_', $grp) ?>">
                                                            <span class="group-dot" style="background:<?= $clr ?>;"></span>
                                                            <?= $grp ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach ?>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold">Golongan</label>
                                            <div class="golongan-grid">
                                                <?php foreach (['1A', '1B', '1C', '2A', '2B', '2C', '2D', '3A', '3B', '3C', '3D', '4A', '4B', '4C', '4D'] as $g): ?>
                                                    <div class="golongan-option">
                                                        <input type="radio" name="golongan" id="gol_<?= $g ?>"
                                                            value="<?= $g ?>">
                                                        <label for="gol_<?= $g ?>"><?= $g ?></label>
                                                    </div>
                                                <?php endforeach ?>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Jatah Cuti (hari/tahun)</label>
                                            <div class="d-flex align-items-center gap-3 mt-1">
                                                <div class="cuti-counter">
                                                    <button type="button" id="btn-cuti-minus">−</button>
                                                    <input type="number" name="jatah_cuti" id="jatah_cuti" value="12"
                                                        min="0" max="30">
                                                    <button type="button" id="btn-cuti-plus">+</button>
                                                </div>
                                                <span class="info-chip"><i class="fas fa-umbrella-beach"></i><span
                                                        id="label-cuti">12 hari / tahun</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-light border">
                                    <h6 class="fw-bold mb-2"><i class="fas fa-lightbulb text-warning"></i> Catatan</h6>
                                    <ul class="mb-0 ps-3 small text-muted">
                                        <li>Dokumen karyawan bisa diupload setelah data disimpan</li>
                                        <li>Sisa cuti awal = jatah cuti yang diset sekarang</li>
                                        <li>Group menentukan pengelompokan di laporan & dashboard</li>
                                        <li>Username dibuat otomatis dari nama</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-danger" id="btn-reset"><i class="fas fa-times"></i>
                                Reset</button>
                            <a href="<?= base_url('pengguna') ?>" class="btn btn-secondary"><i
                                    class="fas fa-arrow-left"></i> Batal</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save"></i> Simpan
                                Karyawan</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>
    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            $('#nama').on('keyup', function () { $('#username').val($(this).val().trim().toLowerCase().replace(/[^a-z0-9]/g, '')); });
            $('#tanggal_lahir').on('change', function () {
                if (new Date($(this).val()) > new Date()) { Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Tanggal lahir tidak boleh di masa depan!' }); $(this).val(''); }
            });
            $('#foto_ktp').on('change', function (e) {
                const file = e.target.files[0]; if (!file) return;
                if (file.size > 2 * 1024 * 1024) { Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Maksimal 2MB!' }); $(this).val(''); $('.custom-file-label').text('Pilih file KTP...'); return; }
                const reader = new FileReader(); reader.onload = e => $('#preview-ktp').attr('src', e.target.result).show(); reader.readAsDataURL(file); $('.custom-file-label').text(file.name);
            });
            $('.status-card').on('click', function () { $('.status-card').css({ background: '', borderColor: '#dee2e6' }); $(this).css({ background: '#eef0fd', borderColor: '#4e73df' }); $(this).find('.status-radio').prop('checked', true); });
            function updateCutiLabel() { $('#label-cuti').text((parseInt($('#jatah_cuti').val()) || 0) + ' hari / tahun'); }
            $('#btn-cuti-minus').on('click', function () { let v = parseInt($('#jatah_cuti').val()) || 0; if (v > 0) { $('#jatah_cuti').val(v - 1); updateCutiLabel(); } });
            $('#btn-cuti-plus').on('click', function () { let v = parseInt($('#jatah_cuti').val()) || 0; if (v < 30) { $('#jatah_cuti').val(v + 1); updateCutiLabel(); } });
            $('#jatah_cuti').on('input', updateCutiLabel);
            $('#btn-reset').on('click', function () {
                Swal.fire({ title: 'Reset Form?', text: 'Semua data akan dihapus!', icon: 'question', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Reset', cancelButtonText: 'Batal' })
                    .then(r => { if (r.isConfirmed) { $('#form-tambah')[0].reset(); $('#preview-ktp').hide(); $('.custom-file-label').text('Pilih file KTP...'); $('#username').val(''); $('#jatah_cuti').val(12); updateCutiLabel(); $('.status-card').css({ background: '', borderColor: '#dee2e6' }); } });
            });
            $('#form-tambah').on('submit', function (e) {
                e.preventDefault();
                const nik = $('input[name="nik"]').val().trim(); const nama = $('#nama').val().trim();
                if (!nik || !nama || !$('#tanggal_lahir').val() || !$('select[name="user_level"]').val() || !$('#foto_ktp')[0].files[0]) { Swal.fire({ icon: 'warning', title: 'Data Tidak Lengkap', text: 'Mohon lengkapi semua field wajib (*)' }); return; }
                if (nik.length !== 16) { Swal.fire({ icon: 'warning', title: 'NIK Tidak Valid', text: 'NIK harus 16 digit!' }); return; }
                Swal.fire({ title: 'Simpan Karyawan?', html: `<b>${nama}</b><br><small class="text-muted">NIK: ${nik}</small>`, icon: 'question', showCancelButton: true, confirmButtonColor: '#4e73df', confirmButtonText: '<i class="fas fa-save"></i> Simpan', cancelButtonText: 'Batal', reverseButtons: true })
                    .then(r => { if (r.isConfirmed) { Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() }); this.submit(); } });
            });
            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
        });
    </script>
</body>

</html>