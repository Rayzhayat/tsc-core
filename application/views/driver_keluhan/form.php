<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keluhan Driver &mdash; TSC</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/TSC_page-0001.png') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js?render=6LdE1IgsAAAAAPlxbr43hpEijurL_HxHVcQhg_Rx"></script>
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 24px 16px 40px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url('<?= base_url("assets/img/TSC_page-0001.png") ?>');
            background-repeat: repeat;
            background-size: 200px auto;
            opacity: 0.06;
            pointer-events: none;
            z-index: 0;
        }

        .form-card {
            position: relative;
            z-index: 1;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .3);
            width: 100%;
            max-width: 560px;
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
            color: #fff;
            padding: 28px 32px 20px;
            text-align: center;
        }

        .form-header .logo {
            font-size: 2.5rem;
            margin-bottom: 8px;
        }

        .form-header h4 {
            margin: 0;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .form-header p {
            margin: 4px 0 0;
            opacity: .8;
            font-size: .88rem;
        }

        .form-body {
            padding: 28px 32px 32px;
        }

        .section-label {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #6c757d;
            margin: 20px 0 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e9ecef;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border-color: #dee2e6;
            padding: 10px 14px;
            font-size: .92rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2d6a9f;
            box-shadow: 0 0 0 3px rgba(45, 106, 159, .15);
        }

        .form-label {
            font-weight: 600;
            font-size: .85rem;
            color: #343a40;
            margin-bottom: 5px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-size: 1rem;
            font-weight: 700;
            width: 100%;
            margin-top: 8px;
            transition: opacity .2s;
        }

        .btn-submit:hover {
            opacity: .9;
            color: #fff;
        }

        .foto-preview {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
            border: 2px solid #dee2e6;
        }

        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            position: relative;
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: #2d6a9f;
            background: #f0f6ff;
        }

        .upload-area input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .upload-area i {
            font-size: 2rem;
            color: #adb5bd;
            margin-bottom: 6px;
            display: block;
        }

        .upload-area p {
            margin: 0;
            font-size: .85rem;
            color: #6c757d;
        }

        .tsc-footer {
            text-align: center;
            padding: 14px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            font-size: .78rem;
            color: #adb5bd;
        }

        .required-star {
            color: #dc3545;
            margin-left: 2px;
        }

        .lokasi-box {
            border-radius: 8px;
            padding: 9px 13px;
            font-size: .82rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lokasi-box.loading {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }

        .lokasi-box.success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        .lokasi-box.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        @media (max-width: 576px) {
            .form-body {
                padding: 20px;
            }

            .form-header {
                padding: 22px 20px 16px;
            }
        }
    </style>
</head>

<body>

    <div class="form-card">
        <div class="form-header">
            <div class="logo">🚛</div>
            <h4>Form Laporan Driver</h4>
            <p>PT Tata Sanjaya Cakrawala &mdash; TSC Logistics</p>
        </div>

        <?php if (!empty($error)): ?>
            <div
                style="margin:16px 20px 0;padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#dc2626;font-size:.85rem">
                <i class="fas fa-exclamation-circle me-1"></i> <?= $error ?>
            </div>
        <?php endif ?>

        <div class="form-body">
            <?= form_open_multipart('driver_keluhan/submit') ?>

            <!-- Honeypot anti-bot: field ini harus kosong, disembunyikan dari user -->
            <div style="display:none;position:absolute;left:-9999px;" aria-hidden="true">
                <label for="website">Website (jangan diisi)</label>
                <input type="text" name="website" id="website" value="" tabindex="-1" autocomplete="off">
            </div>

            <!-- Hidden GPS -->
            <input type="hidden" name="gps_lat" id="gpsLat">
            <input type="hidden" name="gps_lng" id="gpsLng">
            <input type="hidden" name="gps_lokasi" id="gpsLokasi">
            <input type="hidden" name="gps_coords" id="gpsCoords">

            <!-- Keterangan GPS -->
            <div class="d-flex align-items-start gap-2 mb-2 py-2 px-3"
                style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;font-size:.78rem;color:#7c5c00">
                <i class="fas fa-info-circle mt-1" style="color:#f59e0b;flex-shrink:0"></i>
                <span>
                    <strong>Mohon aktifkan GPS/Lokasi di HP Anda</strong> sebelum mengisi form ini.<br>
                    Buka <em>Pengaturan → Lokasi → Aktifkan</em>, lalu izinkan browser mengakses lokasi saat ada
                    permintaan.
                    Data lokasi akan otomatis tercatat sebagai bukti laporan.
                </span>
            </div>

            <!-- Status lokasi -->
            <div class="lokasi-box loading mb-3" id="lokasiBox">
                <i class="fas fa-spinner fa-spin" id="lokasiIcon"></i>
                <span id="lokasiText">Mendeteksi lokasi Anda...</span>
            </div>

            <div class="section-label"><i class="fas fa-id-card"></i> Data Driver</div>

            <div class="mb-3">
                <label class="form-label">Nama Driver <span class="required-star">*</span></label>
                <input type="text" name="nama_driver" class="form-control" placeholder="Masukkan nama lengkap"
                    value="<?= set_value('nama_driver') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">No Polisi / Plat No <span class="required-star">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-car"></i></span>
                    <input type="text" name="no_polisi" id="noPolisi" class="form-control" placeholder="B 1234 TES"
                        value="<?= set_value('no_polisi') ?>" required autocomplete="off"
                        style="text-transform:uppercase;letter-spacing:.05em">
                </div>
            </div>

            <div class="section-label"><i class="fas fa-route"></i> Detail Pengiriman</div>

            <div class="mb-3">
                <label class="form-label">Vendor <span class="required-star">*</span></label>
                <input type="text" name="vendor" class="form-control" placeholder="Nama vendor / perusahaan"
                    value="<?= set_value('vendor') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">No LT <span class="required-star">*</span></label>
                <input type="text" name="no_lt" class="form-control" placeholder="Contoh : LT12345ABCD"
                    value="<?= set_value('no_lt') ?>" required>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label">Origin <span class="required-star">*</span></label>
                    <input type="text" name="origin" class="form-control" placeholder="Lokasi Muat"
                        value="<?= set_value('origin') ?>" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Destinasi <span class="required-star">*</span></label>
                    <input type="text" name="destinasi" class="form-control" placeholder="Lokasi Bongkar"
                        value="<?= set_value('destinasi') ?>" required>
                </div>
            </div>

            <div class="section-label"><i class="fas fa-comment-dots"></i> Laporan Keluhan</div>

            <div class="mb-3">
                <label class="form-label">Keterangan Keluhan <span class="required-star">*</span></label>
                <textarea name="keluhan" class="form-control" rows="4"
                    placeholder="Ceritakan kendala yang dialami secara detail..."
                    required><?= set_value('keluhan') ?></textarea>
                <div class="form-text">Jelaskan masalah, penyebab, dan kondisi saat ini.</div>
            </div>

            <div class="mb-4">
                <label class="form-label">Foto Bukti <span class="required-star">*</span>
                    <small class="text-muted fw-normal">(maks 5MB)</small>
                </label>
                <div class="upload-area" id="uploadArea">
                    <input type="file" name="foto" id="fotoInput" accept="image/*,application/pdf" required>
                    <i class="fas fa-camera"></i>
                    <p><strong>Tap untuk ambil foto</strong> atau pilih dari galeri</p>
                    <p style="font-size:.75rem;margin-top:4px">JPG, PNG, WEBP, PDF</p>
                </div>
                <img id="fotoPreview" class="foto-preview" src="" alt="Preview">
            </div>

            <!-- reCAPTCHA v3 - invisible, token dikirim saat submit -->
            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

            <button type="submit" class="btn-submit" id="btnSubmit">
                <i class="fas fa-paper-plane me-2"></i> Kirim Laporan
            </button>

            <?= form_close() ?>
        </div>

        <div class="tsc-footer">
            &copy; <?= date('Y') ?> PT Tata Sanjaya Cakrawala &mdash; All rights reserved
        </div>
    </div>

    <script>
        // ── reCAPTCHA v3 — jalankan saat submit ──────────────────────────────────────
        document.querySelector('form').addEventListener('submit', function (e) {
            e.preventDefault();
            var form = this;
            var btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Memverifikasi...';

            grecaptcha.ready(function () {
                grecaptcha.execute('6LdE1IgsAAAAAPlxbr43hpEijurL_HxHVcQhg_Rx', { action: 'submit_keluhan' })
                    .then(function (token) {
                        document.getElementById('g-recaptcha-response').value = token;
                        form.submit();
                    })
                    .catch(function () {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Kirim Laporan';
                        alert('Verifikasi gagal, silakan coba lagi.');
                    });
            });
        });

        // ── GPS + Nominatim ──────────────────────────────────────────────────────────
        const lokasiBox = document.getElementById('lokasiBox');
        const lokasiText = document.getElementById('lokasiText');
        const lokasiIcon = document.getElementById('lokasiIcon');

        function setLokasi(type, iconClass, msg) {
            lokasiBox.className = 'lokasi-box ' + type + ' mb-3';
            lokasiIcon.className = 'fas ' + iconClass;
            lokasiText.textContent = msg;
        }

        function getLocation() {
            if (!navigator.geolocation) {
                setLokasi('error', 'fa-exclamation-triangle', 'Browser tidak mendukung GPS.');
                return;
            }
            navigator.geolocation.getCurrentPosition(onGPSSuccess, onGPSError, {
                enableHighAccuracy: true, timeout: 12000, maximumAge: 0
            });
        }

        function onGPSSuccess(pos) {
            const lat = pos.coords.latitude.toFixed(6);
            const lng = pos.coords.longitude.toFixed(6);
            document.getElementById('gpsLat').value = lat;
            document.getElementById('gpsLng').value = lng;
            document.getElementById('gpsCoords').value = lat + ', ' + lng;

            setLokasi('loading', 'fa-spinner fa-spin', 'Mendapatkan nama lokasi...');

            fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json&accept-language=id', {
                headers: { 'User-Agent': 'TSC-Logistics-App/1.0' }
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var lokasi = lat + ', ' + lng;
                    if (data && data.display_name) {
                        // Pakai display_name langsung, potong 4 segmen pertama supaya cukup detail
                        lokasi = data.display_name.split(',').slice(0, 4).join(',').trim();
                    }
                    document.getElementById('gpsLokasi').value = lokasi;
                    setLokasi('success', 'fa-map-marker-alt', '📍 ' + lokasi + ' (' + lat + ', ' + lng + ')');
                })
                .catch(function () {
                    var fallback = lat + ', ' + lng;
                    document.getElementById('gpsLokasi').value = fallback;
                    setLokasi('success', 'fa-map-marker-alt', '📍 ' + fallback);
                });
        }

        function onGPSError(err) {
            var msgs = {
                1: 'Izin lokasi ditolak. Mohon izinkan akses lokasi di browser.',
                2: 'Lokasi tidak dapat ditentukan.',
                3: 'Timeout GPS, coba refresh halaman.'
            };
            setLokasi('error', 'fa-exclamation-triangle', msgs[err.code] || 'Gagal mendapatkan lokasi.');
        }

        window.addEventListener('load', getLocation);

        // ── Auto format No Polisi: B1234TES → B 1234 TES ────────────────────
        document.getElementById('noPolisi').addEventListener('input', function () {
            var raw = this.value.replace(/\s+/g, '').toUpperCase();
            var pos = this.selectionStart;
            var diff = 0;
            // Regex: 1-2 huruf | 1-4 angka | 0-3 huruf
            var m = raw.match(/^([A-Z]{1,2})(\d{1,4})([A-Z]{0,3})$/);
            if (m) {
                var parts = [m[1], m[2]];
                if (m[3]) parts.push(m[3]);
                var formatted = parts.join(' ');
                diff = formatted.length - this.value.length;
                this.value = formatted;
            } else {
                this.value = raw;
            }
            // Jaga posisi cursor
            var newPos = Math.max(0, pos + diff);
            this.setSelectionRange(newPos, newPos);
        });

        // ── Foto Preview ─────────────────────────────────────────────────────────────
        var fotoInput = document.getElementById('fotoInput');
        var fotoPreview = document.getElementById('fotoPreview');
        var uploadArea = document.getElementById('uploadArea');

        fotoInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    fotoPreview.src = e.target.result;
                    fotoPreview.style.display = 'block';
                    uploadArea.style.borderColor = '#2d6a9f';
                    uploadArea.querySelector('p').textContent = file.name;
                };
                reader.readAsDataURL(file);
            } else {
                fotoPreview.style.display = 'none';
                uploadArea.querySelector('p').textContent = file.name;
            }
        });

        uploadArea.addEventListener('dragover', function (e) { e.preventDefault(); uploadArea.classList.add('dragover'); });
        uploadArea.addEventListener('dragleave', function () { uploadArea.classList.remove('dragover'); });
        uploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fotoInput.files = e.dataTransfer.files;
                fotoInput.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>

</html>