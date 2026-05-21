<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        #camera-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        #camera-preview {
            width: 100%;
            border-radius: 10px;
            background: #000;
            aspect-ratio: 4/3;
            transform: scaleX(-1);
        }

        #captured-photo {
            width: 100%;
            border-radius: 10px;
            display: none;
        }

        .camera-controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }

        .user-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
        }

        .location-info {
            background: #f8f9fc;
            border-left: 4px solid #4e73df;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .history-card {
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .history-card.tipe-in {
            border-left: 4px solid #1cc88a;
        }

        .history-card.tipe-out {
            border-left: 4px solid #e74a3b;
        }

        /* ← TAMBAHAN: style khusus auto OUT di history card */
        .history-card.tipe-out.is-auto {
            border-left: 4px solid #f6c23e;
            background-color: #fffdf0;
        }

        .history-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .history-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .camera-disabled {
            opacity: 0.5;
            pointer-events: none;
            filter: grayscale(100%);
        }

        .badge-in {
            background: #1cc88a;
            color: white;
        }

        .badge-out {
            background: #e74a3b;
            color: white;
        }

        /* ← TAMBAHAN: badge khusus auto OUT */
        .badge-auto-out {
            background: #f6c23e;
            color: #333;
        }

        .absensi-status-card {
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            text-align: center;
        }

        .status-in-only {
            background: linear-gradient(135deg, #f6c23e, #f8a225);
            color: white;
        }

        .status-complete {
            background: linear-gradient(135deg, #1cc88a, #17a673);
            color: white;
        }

        .status-none {
            background: linear-gradient(135deg, #858796, #6c757d);
            color: white;
        }

        /* ← TAMBAHAN: banner info auto OUT */
        .auto-out-banner {
            background: linear-gradient(135deg, #f6c23e, #f8a225);
            color: #333;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 10px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
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
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-camera"></i> <?= $title ?>
                        </h1>
                        <?php if ($is_admin || $can_see_laporan): ?>
                            <a href="<?= base_url('absensi/laporan') ?>" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> Lihat Laporan
                            </a>
                        <?php endif ?>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php
                    $already_in = !empty($today_in);
                    $already_out = !empty($today_out);
                    $already_complete = $already_in && $already_out;
                    $current_tipe = $already_in ? 'out' : 'in';
                    ?>

                    <!-- Status Alert -->
                    <?php if ($already_complete): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <h5 class="alert-heading"><i class="fas fa-check-circle"></i> Absensi Hari Ini Sudah Lengkap!
                            </h5>
                            <hr>
                            <p class="mb-0">
                                <i class="fas fa-sign-in-alt text-success"></i>
                                <strong>IN:</strong> <?= date('H:i:s', strtotime($today_in->waktu)) ?> WIB
                                &nbsp;&nbsp;
                                <i class="fas fa-sign-out-alt text-danger"></i>
                                <strong>OUT:</strong> <?= date('H:i:s', strtotime($today_out->waktu)) ?> WIB
                                <?php if (($today_out->metode ?? '') === 'auto'): ?>
                                    &nbsp;<span class="badge" style="background:#f6c23e;color:#333;font-size:0.75rem;">
                                        <i class="fas fa-robot"></i> Auto
                                    </span>
                                <?php endif ?>
                            </p>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php elseif ($already_in): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <h5 class="alert-heading"><i class="fas fa-sign-in-alt"></i> Sudah Absen IN — Jangan Lupa Absen
                                OUT!</h5>
                            <hr>
                            <p class="mb-0">
                                <strong>Waktu IN:</strong> <?= date('H:i:s', strtotime($today_in->waktu)) ?> WIB
                                &nbsp;|&nbsp;
                                <strong>Lokasi:</strong> <?= $today_in->alamat ?>
                            </p>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <div class="row">
                        <!-- LEFT: Form Absensi -->
                        <div class="col-lg-6">

                            <!-- User Info -->
                            <div class="user-info-card">
                                <div class="d-flex align-items-center">
                                    <img src="<?= base_url('uploads/profil/' . $user_foto_profil) ?>" alt="Profile"
                                        class="user-avatar mr-3">
                                    <div>
                                        <h4 class="mb-1"><?= $user_name ?></h4>
                                        <p class="mb-0"><i class="fas fa-id-card"></i> NIK: <?= $user_nik ?></p>
                                        <p class="mb-0"><i class="fas fa-user-tag"></i> Level:
                                            <span class="badge badge-light">
                                                <?= ucwords(str_replace('_', ' ', $user_level)) ?>
                                            </span>
                                        </p>
                                        <!-- Status absensi hari ini -->
                                        <div class="mt-2">
                                            <?php if ($already_complete): ?>
                                                <span class="badge"
                                                    style="background:#1cc88a;color:white;font-size:0.85rem;">
                                                    <i class="fas fa-check-circle"></i> IN & OUT Lengkap
                                                </span>
                                            <?php elseif ($already_in): ?>
                                                <span class="badge"
                                                    style="background:#f6c23e;color:#333;font-size:0.85rem;">
                                                    <i class="fas fa-sign-in-alt"></i> Sudah IN — Belum OUT
                                                </span>
                                            <?php else: ?>
                                                <span class="badge"
                                                    style="background:#858796;color:white;font-size:0.85rem;">
                                                    <i class="fas fa-clock"></i> Belum Absen
                                                </span>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Camera Card -->
                            <div class="card shadow mb-4 <?= $already_complete ? 'camera-disabled' : '' ?>">
                                <div
                                    class="card-header py-3 <?= $current_tipe === 'in' ? 'bg-success' : 'bg-danger' ?>">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <?php if ($already_complete): ?>
                                            <i class="fas fa-check-circle"></i> Absensi Sudah Lengkap
                                        <?php elseif ($current_tipe === 'out'): ?>
                                            <i class="fas fa-sign-out-alt"></i> Absen OUT — Selamat Pulang!
                                        <?php else: ?>
                                            <i class="fas fa-sign-in-alt"></i> Absen IN — Selamat Datang!
                                        <?php endif ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($already_complete): ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                            <h5 class="text-muted">Absensi Hari Ini Sudah Selesai</h5>
                                            <p class="text-muted mb-1">
                                                <i class="fas fa-sign-in-alt text-success"></i>
                                                IN: <?= date('H:i:s', strtotime($today_in->waktu)) ?> WIB
                                            </p>
                                            <p class="text-muted">
                                                <i class="fas fa-sign-out-alt text-danger"></i>
                                                OUT: <?= date('H:i:s', strtotime($today_out->waktu)) ?> WIB
                                                <?php if (($today_out->metode ?? '') === 'auto'): ?>
                                                    <span class="badge"
                                                        style="background:#f6c23e;color:#333;font-size:0.75rem;">
                                                        <i class="fas fa-robot"></i> Auto
                                                    </span>
                                                <?php endif ?>
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <div id="camera-container">
                                            <video id="camera-preview" autoplay playsinline></video>
                                            <img id="captured-photo" alt="Captured Photo">
                                        </div>

                                        <div class="camera-controls">
                                            <button type="button" class="btn btn-success btn-lg" id="btn-capture">
                                                <i class="fas fa-camera"></i> Ambil Foto
                                            </button>
                                            <button type="button" class="btn btn-warning btn-lg" id="btn-retake"
                                                style="display:none;">
                                                <i class="fas fa-redo"></i> Foto Ulang
                                            </button>
                                            <button type="button"
                                                class="btn btn-lg <?= $current_tipe === 'in' ? 'btn-success' : 'btn-danger' ?>"
                                                id="btn-submit" style="display:none;">
                                                <?php if ($current_tipe === 'in'): ?>
                                                    <i class="fas fa-sign-in-alt"></i> Submit Absen IN
                                                <?php else: ?>
                                                    <i class="fas fa-sign-out-alt"></i> Submit Absen OUT
                                                <?php endif ?>
                                            </button>
                                        </div>

                                        <div class="location-info mt-3" id="location-info" style="display:none;">
                                            <h6 class="font-weight-bold">
                                                <i class="fas fa-map-marker-alt text-danger"></i> Lokasi Anda
                                            </h6>
                                            <p class="mb-1" id="address-text">Sedang mengambil lokasi...</p>
                                            <small class="text-muted">
                                                Lat: <span id="lat-text">-</span>,
                                                Long: <span id="lng-text">-</span>
                                            </small>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT: Riwayat Hari Ini -->
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-info">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-history"></i> Riwayat Absensi Hari Ini
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($today_attendance)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Belum ada absensi hari ini</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($today_attendance as $record): ?>
                                            <?php $is_auto = ($record->metode ?? '') === 'auto'; ?>
                                            <div
                                                class="history-card card mb-3 tipe-<?= $record->tipe ?><?= $is_auto ? ' is-auto' : '' ?>">
                                                <div class="card-body">
                                                    <?php if ($is_auto): ?>
                                                        <!-- ← TAMBAHAN: banner kecil di dalam card kalau auto OUT -->
                                                        <div class="auto-out-banner mb-2">
                                                            <i class="fas fa-robot"></i>
                                                            <span>OUT ini diproses <strong>otomatis oleh sistem</strong> karena
                                                                melewati batas 16 jam kerja.</span>
                                                        </div>
                                                    <?php endif ?>
                                                    <div class="row align-items-center">
                                                        <div class="col-md-3 text-center">
                                                            <img src="<?= base_url('uploads/absensi/' . $record->foto) ?>"
                                                                alt="Foto" class="history-photo img-thumbnail">
                                                        </div>
                                                        <div class="col-md-9">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <?php if ($is_auto): ?>
                                                                    <span class="badge badge-auto-out mr-2"
                                                                        style="font-size:0.9rem;">
                                                                        <i class="fas fa-robot"></i> AUTO OUT
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-<?= $record->tipe ?> mr-2"
                                                                        style="font-size:0.9rem;">
                                                                        <?= strtoupper($record->tipe) ?>
                                                                    </span>
                                                                <?php endif ?>
                                                                <h6
                                                                    class="font-weight-bold mb-0 <?= $record->tipe === 'in' ? 'text-success' : ($is_auto ? 'text-warning' : 'text-danger') ?>">
                                                                    <i class="fas fa-clock"></i>
                                                                    <?= date('H:i:s', strtotime($record->waktu)) ?> WIB
                                                                </h6>
                                                            </div>
                                                            <p class="mb-1 small">
                                                                <i class="fas fa-calendar"></i>
                                                                <?= date('d M Y', strtotime($record->tanggal)) ?>
                                                            </p>
                                                            <p class="mb-2 small">
                                                                <i class="fas fa-map-marker-alt text-danger"></i>
                                                                <?= $record->alamat ?>
                                                            </p>
                                                            <?php if (!$is_auto): ?>
                                                                <a href="https://www.google.com/maps?q=<?= $record->latitude ?>,<?= $record->longitude ?>"
                                                                    target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-map"></i> Lihat di Maps
                                                                </a>
                                                            <?php endif ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach ?>

                                        <!-- Summary IN/OUT -->
                                        <div class="card bg-light mt-3">
                                            <div class="card-body py-2">
                                                <div class="row text-center">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Absen IN</small>
                                                        <strong class="text-success">
                                                            <?= $today_in ? date('H:i', strtotime($today_in->waktu)) : '-' ?>
                                                        </strong>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Absen OUT</small>
                                                        <strong
                                                            class="<?= ($today_out && ($today_out->metode ?? '') === 'auto') ? 'text-warning' : 'text-danger' ?>">
                                                            <?php if ($today_out): ?>
                                                                <?= date('H:i', strtotime($today_out->waktu)) ?>
                                                                <?php if (($today_out->metode ?? '') === 'auto'): ?>
                                                                    <small><i class="fas fa-robot" title="Auto OUT"></i></small>
                                                                <?php endif ?>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif ?>
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif ?>
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
        $(document).ready(function () {
            const alreadyComplete = <?= $already_complete ? 'true' : 'false' ?>;
            const currentTipe = '<?= $current_tipe ?>';

            // ← TAMBAHAN: flag dari controller apakah OUT sebelumnya adalah auto
            const lastOutIsAuto = <?= !empty($last_out_is_auto) ? 'true' : 'false' ?>;

            let videoStream = null;
            let capturedPhoto = null;
            let latitude = null;
            let longitude = null;
            let address = 'Lokasi tidak ditemukan';

            async function initCamera() {
                if (alreadyComplete) return;
                try {
                    videoStream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } }
                    });
                    $('#camera-preview')[0].srcObject = videoStream;
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Kamera Error', text: 'Tidak dapat mengakses kamera!' });
                }
            }

            function getLocation() {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition(
                    async function (position) {
                        latitude = position.coords.latitude;
                        longitude = position.coords.longitude;
                        $('#lat-text').text(latitude.toFixed(6));
                        $('#lng-text').text(longitude.toFixed(6));
                        try {
                            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
                            const data = await res.json();
                            address = data.display_name || 'Alamat tidak ditemukan';
                            $('#address-text').text(address);
                        } catch (e) {
                            address = `Lat: ${latitude}, Long: ${longitude}`;
                            $('#address-text').text(address);
                        }
                        $('#location-info').slideDown();
                    },
                    function () {
                        Swal.fire({ icon: 'warning', title: 'GPS Error', text: 'Tidak dapat mengambil lokasi!' });
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }

            $('#btn-capture').on('click', function () {
                const video = $('#camera-preview')[0];
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0);
                capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
                $('#captured-photo').attr('src', capturedPhoto).show();
                $('#camera-preview').hide();
                $('#btn-capture').hide();
                $('#btn-retake, #btn-submit').show();
                getLocation();
            });

            $('#btn-retake').on('click', function () {
                capturedPhoto = null;
                $('#captured-photo').hide();
                $('#camera-preview').show();
                $('#location-info').slideUp();
                $('#btn-retake, #btn-submit').hide();
                $('#btn-capture').show();
            });

            $('#btn-submit').on('click', function () {
                if (!capturedPhoto) {
                    Swal.fire({ icon: 'warning', title: 'Foto Belum Diambil', text: 'Silakan ambil foto terlebih dahulu!' });
                    return;
                }
                if (!latitude || !longitude) {
                    Swal.fire({ icon: 'warning', title: 'Lokasi Belum Didapat', text: 'Menunggu GPS...' });
                    return;
                }

                const tipeLabel = currentTipe === 'in' ? 'Absen IN' : 'Absen OUT';
                const tipeIcon = currentTipe === 'in' ? '🟢' : '🔴';

                Swal.fire({
                    title: `Konfirmasi ${tipeLabel}`,
                    html: `
                        <div class="text-left">
                            <p>${tipeIcon} <strong>Tipe:</strong> ${tipeLabel}</p>
                            <p><strong>Nama:</strong> <?= $user_name ?></p>
                            <p><strong>NIK:</strong> <?= $user_nik ?></p>
                            <p><strong>Waktu:</strong> ${new Date().toLocaleString('id-ID')}</p>
                            <p><strong>Lokasi:</strong> ${address}</p>
                            <hr>
                            <small class="text-muted">Pastikan data sudah benar!</small>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: currentTipe === 'in' ? '#1cc88a' : '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: `<i class="fas fa-check"></i> Ya, ${tipeLabel}`,
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    reverseButtons: true
                }).then(result => {
                    if (result.isConfirmed) submitAbsensi();
                });
            });

            function submitAbsensi() {
                Swal.fire({
                    title: 'Menyimpan...',
                    html: 'Mohon tunggu <i class="fas fa-spinner fa-spin"></i>',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '<?= base_url('absensi/submit') ?>',
                    type: 'POST',
                    data: { photo: capturedPhoto, latitude, longitude, address },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            const tipeLabel = response.tipe === 'in' ? 'Absen IN' : 'Absen OUT';
                            Swal.fire({
                                icon: 'success',
                                title: `${tipeLabel} Berhasil!`,
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            if (response.already_complete) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Absensi Sudah Lengkap!',
                                    text: response.message,
                                    confirmButtonText: 'OK'
                                }).then(() => location.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message });
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan: ' + error });
                    }
                });
            }

            if (!alreadyComplete) initCamera();

            setTimeout(() => $('.alert').fadeOut('slow'), 5000);

            // ─────────────────────────────────────────────────────────────
            // ← TAMBAHAN: Modal info kalau OUT sebelumnya adalah auto
            // Muncul setelah halaman load, wajib klik "Mengerti" dulu
            // ─────────────────────────────────────────────────────────────
            if (lastOutIsAuto) {
                Swal.fire({
                    icon: 'info',
                    title: '⏰ Informasi Absensi Otomatis',
                    html: `
                        <div style="text-align:left;">
                            <p>Absensi <strong>OUT</strong> Anda sebelumnya telah diproses
                            <strong>secara otomatis oleh sistem</strong> karena melewati
                            batas waktu jam kerja (16 jam sejak absen IN).</p>
                            <hr>
                            <p class="mb-0" style="color:#6c757d;">
                                <i class="fas fa-info-circle" style="color:#36b9cc;"></i>
                                Silakan lakukan <strong>Absen IN</strong> seperti biasa
                                saat Anda mulai bekerja kembali.
                            </p>
                        </div>
                    `,
                    confirmButtonText: '<i class="fas fa-check"></i> Mengerti',
                    confirmButtonColor: '#4e73df',
                    allowOutsideClick: false,  // wajib klik tombol dulu
                    allowEscapeKey: false
                });
            }

        }); // end ready
    </script>
</body>

</html>