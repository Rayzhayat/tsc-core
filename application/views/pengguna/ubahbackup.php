<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .preview-ktp {
            max-width: 150px;
            max-height: 100px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .preview-ktp-new {
            display: none;
        }
    </style>
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
                        <a href="<?= base_url('pengguna') ?>" class="btn btn-secondary">
                            Kembali
                        </a>
                    </div>

                    <!-- ALERT -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow">
                                <div class="card-header bg-warning text-dark">
                                    <strong>Form Ubah Karyawan</strong>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('pengguna/proses_ubah/' . $pengguna->id) ?>" method="POST" enctype="multipart/form-data" id="form-ubah">
                                        <div class="row">
                                            <!-- KOLOM KIRI -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>NIK <span class="text-danger">*</span></strong></label>
                                                    <input type="text" name="nik" class="form-control" value="<?= $pengguna->nik ?>" maxlength="16" required>
                                                    <small class="text-muted">16 digit NIK</small>
                                                </div>

                                                <div class="form-group">
                                                    <label><strong>Nama Lengkap <span class="text-danger">*</span></strong></label>
                                                    <input type="text" name="nama" id="nama" class="form-control" value="<?= $pengguna->nama ?>" required>
                                                </div>

                                                <div class="form-group">
                                                    <label><strong>Tanggal Lahir <span class="text-danger">*</span></strong></label>
                                                    <input type="date" name="tanggal_lahir" class="form-control" value="<?= $pengguna->tanggal_lahir ?>" required>
                                                    <small class="text-muted">Pilih tanggal lahir karyawan</small>
                                                </div>

                                                <div class="form-group">
                                                    <label><strong>Username</strong></label>
                                                    <input type="text" name="username" id="username" class="form-control" value="<?= $pengguna->username ?>" readonly>
                                                    <small class="text-muted">Otomatis dari nama</small>
                                                </div>

                                                <div class="form-group">
                                                    <label><strong>Password Baru</strong></label>
                                                    <input type="password" name="password" class="form-control" placeholder="Kosongkan = tidak diubah">
                                                    <small class="text-muted">Biarkan kosong jika tidak ingin ubah password</small>
                                                </div>
                                            </div>

                                            <!-- KOLOM KANAN -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Level Akses <span class="text-danger">*</span></strong></label>
                                                    <select name="user_level" class="form-control" required>
                                                        <option value="">Pilih Level</option>
                                                        <option value="superadmin" <?= $pengguna->user_level == 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
                                                        <option value="admin_operational" <?= $pengguna->user_level == 'admin_operational' ? 'selected' : '' ?>>Admin Operational</option>
                                                        <option value="operational_staff" <?= $pengguna->user_level == 'operational_staff' ? 'selected' : '' ?>>Operational Staff</option>
                                                        <option value="finance_staff" <?= $pengguna->user_level == 'finance_staff' ? 'selected' : '' ?>>Finance Staff</option>
                                                        <option value="fleet_staff" <?= $pengguna->user_level == 'fleet_staff' ? 'selected' : '' ?>>Fleet Staff</option>
                                                        <option value="viewer" <?= $pengguna->user_level == 'viewer' ? 'selected' : '' ?>>Viewer / Manajemen</option>
                                                        <option value="admin_document" <?= $pengguna->user_level == 'admin_document' ? 'selected' : '' ?>>Admin Document</option>
                                                    </select>
                                                </div>

                                                <!-- FOTO KTP LAMA -->
                                                <div class="form-group">
                                                    <label><strong>Foto KTP Saat Ini</strong></label><br>
                                                    <?php if ($pengguna->foto_ktp): ?>
                                                        <a href="<?= base_url('uploads/ktp/' . $pengguna->foto_ktp) ?>" target="_blank">
                                                            <img src="<?= base_url('uploads/ktp/' . $pengguna->foto_ktp) ?>" class="preview-ktp img-thumbnail" alt="KTP">
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif ?>
                                                </div>

                                                <!-- GANTI FOTO KTP -->
                                                <div class="form-group">
                                                    <label><strong>Ganti Foto KTP</strong></label>
                                                    <div class="custom-file">
                                                        <input type="file" name="foto_ktp" class="custom-file-input" id="foto_ktp" accept="image/*">
                                                        <label class="custom-file-label" for="foto_ktp">Pilih file baru...</label>
                                                    </div>
                                                    <img src="" alt="Preview Baru" class="preview-ktp img-thumbnail preview-ktp-new" id="preview-ktp">
                                                    <small class="text-muted">Kosongkan jika tidak ingin ganti</small>
                                                </div>
                                            </div>
                                        </div>

                                        <hr>
                                        <div class="text-right">
                                            <button type="submit" class="btn btn-warning btn-lg">
                                                Ubah
                                            </button>
                                            <a href="<?= base_url('pengguna') ?>" class="btn btn-secondary btn-lg">
                                                Batal
                                            </a>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {

            // Auto update username from nama - FIXED
            $('#nama').on('keyup', function() {
                let nama = $(this).val().trim();
                // Remove all non-alphanumeric, convert to lowercase
                let username = nama.toLowerCase().replace(/[^a-z0-9]/g, '');
                $('#username').val(username || '');
            });

            // Preview KTP baru
            $('#foto_ktp').on('change', function(e) {
                const file = e.target.files[0];

                if (file) {
                    // Validate file size (max 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Terlalu Besar',
                            text: 'Ukuran file maksimal 2MB!',
                            confirmButtonText: 'OK'
                        });
                        $(this).val('');
                        $('.custom-file-label').text('Pilih file baru...');
                        $('#preview-ktp').hide();
                        return;
                    }

                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    if (!validTypes.includes(file.type)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Format File Salah',
                            text: 'Hanya file JPG, JPEG, atau PNG yang diperbolehkan!',
                            confirmButtonText: 'OK'
                        });
                        $(this).val('');
                        $('.custom-file-label').text('Pilih file baru...');
                        $('#preview-ktp').hide();
                        return;
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview-ktp').attr('src', e.target.result).show();
                    };
                    reader.readAsDataURL(file);

                    // Update label
                    $('.custom-file-label').text(file.name);
                }
            });

            // Validate tanggal lahir - no future dates
            $('input[name="tanggal_lahir"]').on('change', function() {
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Reset time to compare dates only

                const selected = new Date($(this).val());

                if (selected > today) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Tanggal lahir tidak boleh di masa depan!',
                        confirmButtonText: 'OK'
                    });
                    $(this).val('<?= $pengguna->tanggal_lahir ?>'); // Reset to original
                }
            });

            // Password strength indicator (optional)
            $('input[name="password"]').on('keyup', function() {
                const password = $(this).val();
                if (password.length > 0) {
                    let strength = 'Lemah';
                    let color = 'text-danger';

                    if (password.length >= 8) {
                        strength = 'Sedang';
                        color = 'text-warning';
                    }

                    if (password.length >= 12 && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
                        strength = 'Kuat';
                        color = 'text-success';
                    }

                    // Show strength indicator (if element exists)
                    if ($('#password-strength').length === 0) {
                        $(this).after('<small id="password-strength" class="d-block mt-1"></small>');
                    }
                    $('#password-strength').html(`<span class="${color}">Password: ${strength}</span>`);
                } else {
                    $('#password-strength').remove();
                }
            });

            // Form submit with confirmation
            $('#form-ubah').on('submit', function(e) {
                e.preventDefault();

                // Validate required fields
                const nik = $('input[name="nik"]').val().trim();
                const nama = $('#nama').val().trim();
                const tanggalLahir = $('input[name="tanggal_lahir"]').val();
                const userLevel = $('select[name="user_level"]').val();

                if (!nik || !nama || !tanggalLahir || !userLevel) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Tidak Lengkap',
                        text: 'Mohon lengkapi semua field yang bertanda *',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Validate NIK length
                if (nik.length !== 16) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'NIK Tidak Valid',
                        text: 'NIK harus 16 digit!',
                        confirmButtonText: 'OK'
                    });
                    $('input[name="nik"]').focus();
                    return;
                }

                // Check if password is being changed
                const newPassword = $('input[name="password"]').val();
                const passwordInfo = newPassword ?
                    '<br><small class="text-info">⚠️ Password akan diubah</small>' :
                    '<br><small class="text-muted">Password tidak diubah</small>';

                // Check if KTP is being changed
                const newKtp = $('#foto_ktp')[0].files[0];
                const ktpInfo = newKtp ?
                    '<br><small class="text-info">⚠️ Foto KTP akan diubah</small>' :
                    '<br><small class="text-muted">Foto KTP tidak diubah</small>';

                // Confirmation
                Swal.fire({
                    title: 'Simpan Perubahan?',
                    html: `<div class="text-left">
                <strong>Nama:</strong> ${nama}<br>
                <strong>NIK:</strong> ${nik}<br>
                <strong>Level:</strong> ${$('select[name="user_level"] option:selected').text()}
                ${passwordInfo}
                ${ktpInfo}
                <hr>
                <small class="text-muted">Pastikan semua perubahan sudah benar!</small>
            </div>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Menyimpan...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit form
                        this.submit();
                    }
                });
            });

            // Cancel button with confirmation
            $('a.btn-secondary[href*="pengguna"]').on('click', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');

                Swal.fire({
                    title: 'Batalkan Perubahan?',
                    text: 'Semua perubahan yang belum disimpan akan hilang!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Batalkan',
                    cancelButtonText: 'Lanjut Edit'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });

            // Auto hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Console log
            console.log('✅ Form Ubah Karyawan - Ready');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Features:');
            console.log('- Auto Update Username');
            console.log('- KTP Preview (New)');
            console.log('- File Validation (2MB, JPG/PNG)');
            console.log('- Date Validation');
            console.log('- Password Strength Indicator');
            console.log('- Form Validation');
            console.log('- ✅ Admin Operational option added');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });
    </script>
</body>

</html>