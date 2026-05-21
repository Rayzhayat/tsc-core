<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .form-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }

        #map {
            height: 400px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .photo-preview {
            max-width: 300px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .info-card {
            border-left: 4px solid #4e73df;
            background: #f8f9fc;
            padding: 15px;
            border-radius: 5px;
        }

        .coordinate-input {
            font-family: 'Courier New', monospace;
            background: #f8f9fc;
        }

        .btn-map-click {
            background: #1cc88a;
            color: white;
            border: none;
            transition: all 0.3s;
        }

        .btn-map-click:hover {
            background: #17a673;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(28, 200, 138, 0.3);
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-edit"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('absensi/laporan') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Laporan
                        </a>
                    </div>

                    <div class="row">
                        <!-- LEFT COLUMN: Form -->
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header form-card py-3">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-edit"></i> Edit Data Absensi
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- Info Alert -->
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Info:</strong> Anda dapat mengubah tanggal, waktu, alamat, dan koordinat
                                        lokasi.
                                        Klik pada peta untuk memilih lokasi baru.
                                    </div>

                                    <form action="<?= base_url('absensi/update/' . $record->id) ?>" method="POST"
                                        id="form-edit">
                                        <div class="row">
                                            <!-- User Info (Read Only) -->
                                            <div class="col-md-12 mb-3">
                                                <div class="info-card">
                                                    <h6 class="font-weight-bold text-primary mb-2">
                                                        <i class="fas fa-user"></i> Informasi Karyawan
                                                    </h6>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong>Nama:</strong>
                                                                <?= $record->user_nama ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong>NIK:</strong>
                                                                <?= $record->user_nik ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tanggal -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tanggal">
                                                        <i class="fas fa-calendar"></i> Tanggal Absensi *
                                                    </label>
                                                    <input type="date" class="form-control" id="tanggal" name="tanggal"
                                                        value="<?= $record->tanggal ?>" required>
                                                </div>
                                            </div>

                                            <!-- Waktu -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="waktu">
                                                        <i class="fas fa-clock"></i> Waktu Absensi *
                                                    </label>
                                                    <input type="time" class="form-control" id="waktu" name="waktu"
                                                        value="<?= $record->waktu ?>" step="1" required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tipe">
                                                        <i class="fas fa-exchange-alt"></i> Tipe Absensi *
                                                    </label>
                                                    <select class="form-control" id="tipe" name="tipe" required>
                                                        <option value="in" <?= $record->tipe === 'in' ? 'selected' : '' ?>>
                                                            🟢 IN — Masuk
                                                        </option>
                                                        <option value="out" <?= $record->tipe === 'out' ? 'selected' : '' ?>>
                                                            🔴 OUT — Pulang
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Alamat -->
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="alamat">
                                                        <i class="fas fa-map-marker-alt"></i> Alamat Lokasi *
                                                    </label>
                                                    <textarea class="form-control" id="alamat" name="alamat" rows="3"
                                                        required><?= $record->alamat ?></textarea>
                                                    <small class="form-text text-muted">
                                                        Alamat akan otomatis terisi saat Anda mengklik peta
                                                    </small>
                                                </div>
                                            </div>

                                            <!-- Latitude -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="latitude">
                                                        <i class="fas fa-globe"></i> Latitude *
                                                    </label>
                                                    <input type="text" class="form-control coordinate-input"
                                                        id="latitude" name="latitude" value="<?= $record->latitude ?>"
                                                        readonly required>
                                                </div>
                                            </div>

                                            <!-- Longitude -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="longitude">
                                                        <i class="fas fa-globe"></i> Longitude *
                                                    </label>
                                                    <input type="text" class="form-control coordinate-input"
                                                        id="longitude" name="longitude"
                                                        value="<?= $record->longitude ?>" readonly required>
                                                </div>
                                            </div>

                                            <!-- Map -->
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>
                                                        <i class="fas fa-map"></i> Pilih Lokasi di Peta
                                                    </label>
                                                    <div class="alert alert-success">
                                                        <i class="fas fa-mouse-pointer"></i>
                                                        <strong>Klik pada peta</strong> untuk memilih lokasi baru.
                                                        Koordinat dan alamat akan diupdate otomatis.
                                                    </div>
                                                    <div id="map"></div>
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="col-md-12">
                                                <hr>
                                                <div class="form-group text-right">
                                                    <a href="<?= base_url('absensi/laporan') ?>"
                                                        class="btn btn-secondary">
                                                        <i class="fas fa-times"></i> Batal
                                                    </a>
                                                    <button type="submit" class="btn btn-primary" id="btn-submit">
                                                        <i class="fas fa-save"></i> Simpan Perubahan
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Photo Preview -->
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-gradient-success">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-image"></i> Foto Absensi
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <img src="<?= base_url('uploads/absensi/' . $record->foto) ?>" alt="Foto Absensi"
                                        class="photo-preview img-thumbnail">
                                    <hr>
                                    <p class="text-muted mb-0">
                                        <small>
                                            <i class="fas fa-info-circle"></i>
                                            Foto tidak dapat diubah
                                        </small>
                                    </p>
                                </div>
                            </div>

                            <!-- Original Data Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-gradient-info">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-history"></i> Data Asli
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><i class="fas fa-calendar text-primary"></i> Tanggal</td>
                                            <td><strong><?= date('d/m/Y', strtotime($record->tanggal)) ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-clock text-success"></i> Waktu</td>
                                            <td><strong><?= $record->waktu ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <hr class="my-2">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?= $record->alamat ?>
                                                </small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <a href="https://www.google.com/maps?q=<?= $record->latitude ?>,<?= $record->longitude ?>"
                                                    target="_blank" class="btn btn-sm btn-outline-primary btn-block">
                                                    <i class="fas fa-external-link-alt"></i> Lihat di Google Maps
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {

            // ========================================
            // INITIALIZE MAP
            // ========================================
            const initialLat = <?= $record->latitude ?>;
            const initialLng = <?= $record->longitude ?>;

            const map = L.map('map').setView([initialLat, initialLng], 15);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);

            // Add marker
            let marker = L.marker([initialLat, initialLng], {
                draggable: false
            }).addTo(map);

            marker.bindPopup('<strong>Lokasi Absensi</strong><br>Klik peta untuk mengubah lokasi').openPopup();

            // ========================================
            // MAP CLICK EVENT
            // ========================================
            map.on('click', function (e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                // Update marker position
                marker.setLatLng([lat, lng]);
                map.panTo([lat, lng]);

                // Update form fields
                $('#latitude').val(lat.toFixed(6));
                $('#longitude').val(lng.toFixed(6));

                // Reverse geocoding
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        const address = data.display_name || 'Alamat tidak ditemukan';
                        $('#alamat').val(address);

                        marker.setPopupContent(`
                            <strong>Lokasi Baru</strong><br>
                            <small>${address}</small>
                        `).openPopup();

                        // Show success toast
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Lokasi diperbarui!',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    })
                    .catch(error => {
                        console.error('Geocoding error:', error);
                        $('#alamat').val(`Lat: ${lat.toFixed(6)}, Long: ${lng.toFixed(6)}`);
                    });
            });

            // ========================================
            // FORM SUBMIT CONFIRMATION
            // ========================================
            $('#form-edit').on('submit', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Konfirmasi Perubahan',
                    html: `
                        <div class="text-left">
                            <p><strong>Apakah Anda yakin ingin menyimpan perubahan?</strong></p>
                            <hr>
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> Tanggal: <strong>${$('#tanggal').val()}</strong><br>
                                <i class="fas fa-clock"></i> Waktu: <strong>${$('#waktu').val()}</strong><br>
                                <i class="fas fa-map-marker-alt"></i> Lokasi: <strong>${$('#alamat').val().substring(0, 50)}...</strong>
                            </small>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4e73df',
                    cancelButtonColor: '#858796',
                    confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Menyimpan...',
                            html: 'Mohon tunggu <i class="fas fa-spinner fa-spin"></i>',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit form
                        this.submit();
                    }
                });
            });

            // ========================================
            // INITIALIZE
            // ========================================
            console.log('✅ Edit Absensi Page Ready');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Record ID: <?= $record->id ?>');
            console.log('User: <?= $record->user_nama ?>');
            console.log('Original Location: [<?= $record->latitude ?>, <?= $record->longitude ?>]');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });
    </script>
</body>

</html>