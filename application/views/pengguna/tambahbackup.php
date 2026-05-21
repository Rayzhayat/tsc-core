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
                        <a href="<?= base_url('pengguna') ?>" class="btn btn-secondary">Kembali</a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow">
                                <div class="card-header bg-primary text-white">
                                    <strong>Form Tambah Karyawan</strong>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('pengguna/proses_tambah') ?>" method="POST" enctype="multipart/form-data" id="form-tambah">
                                        <div class="row">
                                            <!-- KOLOM KIRI -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>NIK <span class="text-danger">*</span></strong></label>
                                                    <input type="text" name="nik" class="form-control" placeholder="16 Digit NIK" maxlength="16" required>
                                                    <small class="text-muted">Contoh: 1234567890123456</small>
                                                </div>

                                                <div class="form-group">
                                                    <label><strong>Nama Lengkap <span class="text-danger">*</span></strong></label>
                                                    <input type="text" name="nama" id="nama" class="form-control" placeholder="Masukkan Nama" required>
                                                </div>

                                                <div class="form-group">
                                                    <label><strong>Tanggal Lahir <span class="text-danger">*</span></strong></label>
                                                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" required>
                                                    <small class="text-muted">Pilih tanggal lahir karyawan</small>
                                                </div>

                                                <div class="form-group">
                                                    <label><strong>Username</strong></label>
                                                    <input type="text" name="username" id="username" class="form-control" placeholder="Otomatis dari nama" readonly>
                                                    <small class="text-muted">Hanya huruf kecil & angka</small>
                                                </div>

                                                <div class="form-group">
                                                    <label><strong>Password</strong></label>
                                                    <input type="password" name="password" class="form-control" placeholder="Kosongkan = 123456">
                                                    <small class="text-muted">Default: <code>123456</code></small>
                                                </div>
                                            </div>

                                            <!-- KOLOM KANAN -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Level Akses <span class="text-danger">*</span></strong></label>
                                                    <select name="user_level" class="form-control" required>
                                                        <option value="">Pilih Level</option>
                                                        <option value="superadmin">Superadmin</option>
                                                        <option value="admin_operational">Admin Operational</option>
                                                        <option value="operational_staff">Operational Staff</option>
                                                        <option value="finance_staff">Finance Staff</option>
                                                        <option value="fleet_staff">Fleet Staff</option>
                                                        <option value="viewer">Viewer / Manajemen</option>
                                                        <option value="admin_document">Admin Document</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label><strong>Foto KTP <span class="text-danger">*</span></strong></label>
                                                    <div class="custom-file">
                                                        <input type="file" name="foto_ktp" class="custom-file-input" id="foto_ktp" accept="image/*" required>
                                                        <label class="custom-file-label" for="foto_ktp">Pilih file...</label>
                                                    </div>
                                                    <img src="" alt="Preview" class="preview-ktp img-thumbnail" id="preview-ktp">
                                                    <small class="text-muted">Max 2MB, JPG/PNG</small>
                                                </div>
                                            </div>
                                        </div>

                                        <hr>
                                        <div class="text-right">
                                            <button type="submit" class="btn btn-primary btn-lg">Simpan</button>
                                            <button type="reset" class="btn btn-danger btn-lg" id="btn-reset">Batal</button>
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

            // Auto generate username from nama - FIXED
            $('#nama').on('keyup', function() {
                let nama = $(this).val().trim();
                // Remove all non-alphanumeric, convert to lowercase
                let username = nama.toLowerCase().replace(/[^a-z0-9]/g, '');
                $('#username').val(username || '');
            });

            // Validate tanggal lahir - no future dates
            $('#tanggal_lahir').on('change', function() {
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
                    $(this).val('');
                }
            });

            // Preview foto KTP
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
                        $('.custom-file-label').text('Pilih file...');
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
                        $('.custom-file-label').text('Pilih file...');
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

            // Reset button handler
            $('#btn-reset').on('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Reset Form?',
                    text: 'Semua data yang diisi akan hilang!',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Reset',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#form-tambah')[0].reset();
                        $('#preview-ktp').hide();
                        $('.custom-file-label').text('Pilih file...');
                        $('#username').val('');
                    }
                });
            });

            // Form submit with confirmation
            $('#form-tambah').on('submit', function(e) {
                e.preventDefault();

                // Validate required fields
                const nik = $('input[name="nik"]').val().trim();
                const nama = $('#nama').val().trim();
                const tanggalLahir = $('#tanggal_lahir').val();
                const userLevel = $('select[name="user_level"]').val();
                const fotoKtp = $('#foto_ktp')[0].files[0];

                if (!nik || !nama || !tanggalLahir || !userLevel || !fotoKtp) {
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

                // Confirmation
                Swal.fire({
                    title: 'Simpan Data Karyawan?',
                    html: `<div class="text-left">
                <strong>Nama:</strong> ${nama}<br>
                <strong>NIK:</strong> ${nik}<br>
                <strong>Level:</strong> ${$('select[name="user_level"] option:selected').text()}<br>
                <small class="text-muted">Pastikan semua data sudah benar!</small>
            </div>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
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

            // Auto hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Console log
            console.log('✅ Form Tambah Karyawan - Ready');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Features:');
            console.log('- Auto Generate Username');
            console.log('- KTP Preview');
            console.log('- File Validation (2MB, JPG/PNG)');
            console.log('- Date Validation');
            console.log('- Form Validation');
            console.log('- ✅ Admin Operational option added');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });
    </script>
</body>

</html>