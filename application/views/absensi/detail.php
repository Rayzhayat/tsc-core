<!DOCTYPE html>
<html lang="id">

<head>
    <?php $this->load->view('partials/head') ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .photo-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .photo-main {
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }

        .photo-main:hover {
            transform: scale(1.02);
        }

        #map {
            height: 400px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e6e7e9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .875rem;
        }

        .info-value {
            font-weight: 600;
            color: #1a1a2e;
            text-align: right;
            font-size: .875rem;
        }

        .coordinate-box {
            background: #1e2530;
            color: #00e676;
            padding: 14px 18px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: .9rem;
            margin-top: 10px;
        }

        .timeline-item {
            position: relative;
            padding-left: 32px;
            padding-bottom: 20px;
            border-left: 2px solid #e6e7e9;
        }

        .timeline-item:last-child {
            border-left: none;
            padding-bottom: 0;
        }

        .timeline-icon {
            position: absolute;
            left: -11px;
            top: 0;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #206bc4;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        .fullscreen-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
        }

        .fullscreen-btn:hover {
            background: white;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <!-- Page Heading -->
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div>
                            <h2 class="mb-0"><i class="fas fa-eye text-primary me-2"></i><?= $title ?></h2>
                            <small class="text-muted">Detail rekaman absensi karyawan</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('absensi/laporan') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <a href="<?= base_url('absensi/edit/' . $record->id) ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <button type="button" class="btn btn-danger" id="btn-delete">
                                <i class="fas fa-trash me-1"></i> Hapus
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- LEFT: Foto & Info Karyawan -->
                        <div class="col-lg-5">

                            <!-- Info Karyawan -->
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-user me-2"></i>Informasi Karyawan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <img src="<?= base_url('uploads/profil/' . ($record->user_foto_profil ?? 'default-1.png')) ?>"
                                            alt="Profile" class="rounded-circle mb-2"
                                            style="width:90px;height:90px;object-fit:cover;border:3px solid #206bc4;">
                                        <h5 class="mb-1 text-primary fw-bold">
                                            <?= htmlspecialchars($record->user_nama) ?></h5>
                                        <span class="badge bg-purple-lt text-purple fw-semibold px-3 py-1"
                                            style="border-radius:20px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff!important">
                                            <?= ucwords(str_replace('_', ' ', $record->user_level)) ?>
                                        </span>
                                    </div>

                                    <div class="info-row">
                                        <div class="info-label"><i class="fas fa-id-card text-primary"></i> NIK</div>
                                        <div class="info-value"><?= htmlspecialchars($record->user_nik) ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label"><i class="fas fa-calendar text-success"></i> Tanggal
                                            Absensi</div>
                                        <div class="info-value"><?= date('d F Y', strtotime($record->tanggal)) ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label"><i class="fas fa-clock text-warning"></i> Waktu Absensi
                                        </div>
                                        <div class="info-value">
                                            <span class="badge bg-success fs-6 px-3 py-2"
                                                style="border-radius:20px"><?= $record->waktu ?> WIB</span>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label"><i class="fas fa-history text-info"></i> Dicatat Pada
                                        </div>
                                        <div class="info-value">
                                            <small><?= date('d/m/Y H:i:s', strtotime($record->created_at)) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Foto Absensi -->
                            <div class="card shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-camera me-2"></i>Foto Absensi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="photo-container">
                                        <button class="fullscreen-btn" onclick="showPhotoFullscreen()"
                                            title="Fullscreen">
                                            <i class="fas fa-expand-alt"></i>
                                        </button>
                                        <img src="<?= base_url('uploads/absensi/' . $record->foto) ?>"
                                            alt="Foto Absensi" class="photo-main" id="photo-main">
                                    </div>
                                    <div class="text-center mt-3">
                                        <a href="<?= base_url('uploads/absensi/' . $record->foto) ?>"
                                            download="Absensi_<?= $record->user_nama ?>_<?= $record->tanggal ?>.jpg"
                                            class="btn btn-primary">
                                            <i class="fas fa-download me-1"></i> Download Foto
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT: Lokasi & Map -->
                        <div class="col-lg-7">

                            <!-- Informasi Lokasi -->
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-map-marker-alt me-2"></i>Informasi Lokasi
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <h6 class="text-primary fw-bold mb-1"><i class="fas fa-map-marked-alt me-1"></i>
                                        Alamat</h6>
                                    <p class="mb-3"><?= htmlspecialchars($record->alamat) ?></p>

                                    <hr class="my-2">

                                    <h6 class="text-success fw-bold mb-1"><i class="fas fa-globe me-1"></i> Koordinat
                                        GPS</h6>
                                    <div class="coordinate-box">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <strong>LATITUDE:</strong><br>
                                                <?= $record->latitude ?>
                                            </div>
                                            <div class="col-6">
                                                <strong>LONGITUDE:</strong><br>
                                                <?= $record->longitude ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 d-grid gap-2">
                                        <a href="https://www.google.com/maps?q=<?= $record->latitude ?>,<?= $record->longitude ?>"
                                            target="_blank" class="btn btn-primary">
                                            <i class="fas fa-external-link-alt me-1"></i> Buka di Google Maps
                                        </a>
                                        <button type="button" class="btn btn-info text-white"
                                            onclick="copyCoordinates()">
                                            <i class="fas fa-copy me-1"></i> Copy Koordinat
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Peta -->
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-map me-2"></i>Peta Lokasi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>Lokasi Absensi:</strong> Marker menunjukkan titik koordinat GPS saat
                                        karyawan melakukan absensi.
                                    </div>
                                    <div id="map"></div>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="card shadow-sm">
                                <div class="card-header bg-warning text-white">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Timeline</h6>
                                </div>
                                <div class="card-body">
                                    <div class="timeline-item">
                                        <div class="timeline-icon"><i class="fas fa-user-check"></i></div>
                                        <h6 class="text-primary mb-1 fw-bold">Absensi Berhasil</h6>
                                        <small class="text-muted"><?= date('d F Y', strtotime($record->tanggal)) ?>
                                            &bull; <?= $record->waktu ?> WIB</small>
                                        <p class="mt-1 mb-0 small">
                                            <strong><?= htmlspecialchars($record->user_nama) ?></strong> melakukan
                                            absensi dari:<br>
                                            <span
                                                class="text-muted"><?= htmlspecialchars(substr($record->alamat, 0, 100)) ?>...</span>
                                        </p>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-icon" style="background:#2fb344"><i
                                                class="fas fa-camera"></i></div>
                                        <h6 class="text-success mb-1 fw-bold">Foto Tersimpan</h6>
                                        <small class="text-muted">Foto absensi berhasil diambil dan disimpan</small>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-icon" style="background:#4dabf7"><i
                                                class="fas fa-map-marker-alt"></i></div>
                                        <h6 class="text-info mb-1 fw-bold">Lokasi Terdeteksi</h6>
                                        <small class="text-muted">GPS: <?= $record->latitude ?>,
                                            <?= $record->longitude ?></small>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-icon" style="background:#868e96"><i
                                                class="fas fa-database"></i></div>
                                        <h6 class="text-secondary mb-1 fw-bold">Data Tersimpan</h6>
                                        <small
                                            class="text-muted"><?= date('d F Y \&bull; H:i:s', strtotime($record->created_at)) ?>
                                            WIB</small>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- Photo Fullscreen Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center p-0 position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal" style="z-index:9999;transform:scale(1.5)"></button>
                    <img src="<?= base_url('uploads/absensi/' . $record->foto) ?>" alt="Foto Absensi"
                        style="max-width:100%;max-height:90vh;border-radius:10px;">
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // ── MAP ──────────────────────────────────────────────────────────────
        const lat = <?= (float) $record->latitude ?>;
        const lng = <?= (float) $record->longitude ?>;

        const map = L.map('map').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);

        const customIcon = L.divIcon({
            className: '',
            html: '<div style="background:#e74a3b;width:28px;height:28px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 3px 10px rgba(0,0,0,0.3)"></div>',
            iconSize: [28, 28],
            iconAnchor: [14, 28]
        });

        const marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
        marker.bindPopup(`
            <div style="text-align:center;min-width:200px">
                <strong><?= htmlspecialchars($record->user_nama) ?></strong><br>
                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($record->tanggal . ' ' . $record->waktu)) ?> WIB</small>
                <hr style="margin:8px 0">
                <small><?= htmlspecialchars(substr($record->alamat, 0, 80)) ?>...</small>
            </div>
        `).openPopup();

        L.circle([lat, lng], {
            color: '#e74a3b', fillColor: '#e74a3b', fillOpacity: 0.1, radius: 50
        }).addTo(map);

        // ── DELETE ───────────────────────────────────────────────────────────
        document.getElementById('btn-delete').addEventListener('click', function () {
            Swal.fire({
                title: 'Hapus Data Absensi?',
                html: `<p>Data absensi berikut akan dihapus <strong>permanen</strong>:</p>
                       <hr>
                       <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($record->user_nama) ?></p>
                       <p class="mb-1"><strong>Tanggal:</strong> <?= date('d F Y', strtotime($record->tanggal)) ?></p>
                       <p class="mb-1"><strong>Waktu:</strong> <?= $record->waktu ?> WIB</p>
                       <hr>
                       <p class="text-danger mb-0"><small><i class="fas fa-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan!</small></p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(result => {
                if (result.isConfirmed)
                    window.location.href = '<?= base_url('absensi/delete/' . $record->id) ?>';
            });
        });

        // ── FULLSCREEN PHOTO ─────────────────────────────────────────────────
        function showPhotoFullscreen() {
            new bootstrap.Modal(document.getElementById('photoModal')).show();
        }

        // ── COPY COORDINATES ─────────────────────────────────────────────────
        function copyCoordinates() {
            const coords = `${lat},${lng}`;
            navigator.clipboard.writeText(coords).catch(() => {
                const el = document.createElement('textarea');
                el.value = coords;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
            }).finally ? null : void 0;
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'Koordinat berhasil dicopy!',
                showConfirmButton: false, timer: 2000, timerProgressBar: true
            });
        }
    </script>
</body>

</html>