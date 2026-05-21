<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>

    <!-- Signature Pad Library -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        /* Form Card */
        .pod-form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .pod-form-header {
            background: var(--primary-gradient);
            color: white;
            padding: 24px;
            border-bottom: 4px solid rgba(255, 255, 255, 0.2);
        }

        .form-section {
            background: #f8f9fc;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .form-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #5a5c69;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
        }

        .form-section-title i {
            margin-right: 10px;
            color: #667eea;
        }

        /* Signature Pad */
        .signature-container {
            border: 3px dashed #d1d3e2;
            border-radius: 12px;
            background: white;
            padding: 10px;
            text-align: center;
        }

        #signature-pad {
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            cursor: crosshair;
            touch-action: none;
        }

        .signature-actions {
            margin-top: 12px;
        }

        /* Photo Upload */
        .photo-upload-zone {
            border: 3px dashed #d1d3e2;
            border-radius: 12px;
            background: white;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .photo-upload-zone:hover {
            border-color: #667eea;
            background: #f8f9fc;
        }

        .photo-upload-zone.dragover {
            border-color: #11998e;
            background: #d4edda;
        }

        .photo-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
        }

        .photo-preview-item {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            border: 3px solid #e3e6f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .photo-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-preview-item .remove-photo {
            position: absolute;
            top: 4px;
            right: 4px;
            background: #e74a3b;
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .photo-preview-item .remove-photo:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        /* Time Input Enhancement */
        .time-input-group {
            position: relative;
        }

        .time-input-group .input-group-text {
            background: var(--primary-gradient);
            color: white;
            border: none;
            font-weight: 600;
        }

        .time-input-group input {
            border-left: none;
        }

        /* Condition Selector */
        .condition-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
        }

        .condition-option {
            position: relative;
        }

        .condition-option input[type="radio"] {
            display: none;
        }

        .condition-option label {
            display: block;
            padding: 16px;
            border: 3px solid #e3e6f0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .condition-option input[type="radio"]:checked+label {
            border-color: #667eea;
            background: var(--primary-gradient);
            color: white;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .condition-option label i {
            font-size: 2rem;
            display: block;
            margin-bottom: 8px;
        }

        .condition-option label span {
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Submit Button */
        .btn-submit-pod {
            background: var(--success-gradient);
            border: none;
            padding: 16px 48px;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 50px;
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
            transition: all 0.3s ease;
        }

        .btn-submit-pod:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
        }

        /* Trip Info Card */
        .trip-info-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .trip-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .trip-info-item i {
            width: 30px;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .trip-info-item strong {
            margin-right: 8px;
        }

        /* Quantity Counter */
        .qty-counter {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .qty-counter button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .qty-counter button:hover {
            background: #667eea;
            color: white;
            transform: scale(1.1);
        }

        .qty-counter input {
            width: 100px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            border: 2px solid #e3e6f0;
            border-radius: 8px;
        }

        /* Required Field Indicator */
        .required-field::after {
            content: " *";
            color: #e74a3b;
            font-weight: bold;
        }
    </style>
</head>

<body id="page-top" class="fixed-nav">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar') ?>

                <div class="container-fluid">
                    <!-- BACK BUTTON -->
                    <div class="mb-3">
                        <a href="<?= base_url('surat_jalan/pod_dashboard') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to POD Dashboard
                        </a>
                    </div>

                    <!-- TRIP INFO CARD -->
                    <div class="trip-info-card">
                        <h4 class="mb-3">
                            <i class="fas fa-truck"></i> Trip Information
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="trip-info-item">
                                    <i class="fas fa-file-alt"></i>
                                    <strong>No. SJ:</strong> <?= $sj->no_sj ?>
                                </div>
                                <div class="trip-info-item">
                                    <i class="fas fa-calendar"></i>
                                    <strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($sj->tanggal)) ?>
                                </div>
                                <div class="trip-info-item">
                                    <i class="fas fa-user-tie"></i>
                                    <strong>Driver:</strong> <?= $sj->nama_driver ?? '-' ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="trip-info-item">
                                    <i class="fas fa-truck"></i>
                                    <strong>Unit:</strong> <?= $sj->no_polisi ?? '-' ?>
                                </div>
                                <div class="trip-info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <strong>Tujuan:</strong> <?= $sj->tujuan ?? '-' ?>
                                </div>
                                <div class="trip-info-item">
                                    <i class="fas fa-boxes"></i>
                                    <strong>Muatan:</strong> <?= $sj->muatan ?? '-' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- POD FORM -->
                    <form action="<?= base_url('surat_jalan/pod_submit') ?>" method="POST" enctype="multipart/form-data" id="podForm">
                        <input type="hidden" name="sj_id" value="<?= $sj->id ?>">

                        <div class="pod-form-card">
                            <div class="pod-form-header">
                                <h3 class="mb-0">
                                    <i class="fas fa-clipboard-check"></i> Submit Proof of Delivery
                                </h3>
                                <p class="mb-0 mt-2" style="opacity: 0.9;">
                                    Lengkapi semua informasi di bawah ini untuk menyelesaikan pengiriman
                                </p>
                            </div>

                            <div class="card-body p-4">
                                <!-- SECTION 1: ARRIVAL TIME -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="fas fa-clock"></i> Waktu Kedatangan & Bongkar
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="font-weight-bold required-field">Waktu Tiba</label>
                                            <div class="time-input-group input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </span>
                                                </div>
                                                <input type="datetime-local"
                                                    name="arrival_time"
                                                    class="form-control"
                                                    required
                                                    value="<?= date('Y-m-d\TH:i') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="font-weight-bold">Mulai Bongkar</label>
                                            <div class="time-input-group input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-play"></i>
                                                    </span>
                                                </div>
                                                <input type="datetime-local"
                                                    name="unloading_start"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="font-weight-bold">Selesai Bongkar</label>
                                            <div class="time-input-group input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-stop"></i>
                                                    </span>
                                                </div>
                                                <input type="datetime-local"
                                                    name="unloading_finish"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION 2: DELIVERY QUANTITY -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="fas fa-boxes"></i> Jumlah Barang
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold required-field">Jumlah Diterima</label>
                                            <div class="qty-counter">
                                                <button type="button" class="qty-btn" data-action="decrease" data-target="qty_delivered">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number"
                                                    name="qty_delivered"
                                                    id="qty_delivered"
                                                    class="form-control"
                                                    min="0"
                                                    value="0"
                                                    required>
                                                <button type="button" class="qty-btn" data-action="increase" data-target="qty_delivered">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Jumlah Ditolak</label>
                                            <div class="qty-counter">
                                                <button type="button" class="qty-btn" data-action="decrease" data-target="qty_rejected">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number"
                                                    name="qty_rejected"
                                                    id="qty_rejected"
                                                    class="form-control"
                                                    min="0"
                                                    value="0">
                                                <button type="button" class="qty-btn" data-action="increase" data-target="qty_rejected">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION 3: RECEIVER INFO -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="fas fa-user-check"></i> Informasi Penerima
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold required-field">Nama Penerima</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                </div>
                                                <input type="text"
                                                    name="receiver_name"
                                                    class="form-control"
                                                    placeholder="Nama lengkap penerima"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">No. HP Penerima</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-phone"></i>
                                                    </span>
                                                </div>
                                                <input type="tel"
                                                    name="receiver_phone"
                                                    class="form-control"
                                                    placeholder="08xxxxxxxxxx">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION 4: CONDITION -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="fas fa-box-open"></i> Kondisi Barang
                                    </div>
                                    <div class="condition-selector">
                                        <div class="condition-option">
                                            <input type="radio"
                                                name="delivery_condition"
                                                value="baik"
                                                id="condition_baik"
                                                checked>
                                            <label for="condition_baik">
                                                <i class="fas fa-check-circle text-success"></i>
                                                <span>Baik</span>
                                            </label>
                                        </div>
                                        <div class="condition-option">
                                            <input type="radio"
                                                name="delivery_condition"
                                                value="rusak_sebagian"
                                                id="condition_partial">
                                            <label for="condition_partial">
                                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                                <span>Rusak Sebagian</span>
                                            </label>
                                        </div>
                                        <div class="condition-option">
                                            <input type="radio"
                                                name="delivery_condition"
                                                value="rusak"
                                                id="condition_rusak">
                                            <label for="condition_rusak">
                                                <i class="fas fa-times-circle text-danger"></i>
                                                <span>Rusak</span>
                                            </label>
                                        </div>
                                        <div class="condition-option">
                                            <input type="radio"
                                                name="delivery_condition"
                                                value="kurang"
                                                id="condition_kurang">
                                            <label for="condition_kurang">
                                                <i class="fas fa-minus-circle text-info"></i>
                                                <span>Kurang</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION 5: SIGNATURE -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="fas fa-signature"></i> Tanda Tangan Penerima
                                    </div>
                                    <div class="signature-container">
                                        <canvas id="signature-pad" width="600" height="200"></canvas>
                                        <div class="signature-actions">
                                            <button type="button" class="btn btn-warning btn-sm" id="clear-signature">
                                                <i class="fas fa-eraser"></i> Clear Signature
                                            </button>
                                        </div>
                                        <input type="hidden" name="receiver_signature" id="signature-data">
                                    </div>
                                </div>

                                <!-- SECTION 6: PHOTOS -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="fas fa-camera"></i> Foto Bukti Pengiriman
                                    </div>

                                    <!-- Main Photo -->
                                    <div class="mb-3">
                                        <label class="font-weight-bold">Foto Utama</label>
                                        <div class="photo-upload-zone" id="main-photo-zone">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                            <h5>Click or Drag Photo Here</h5>
                                            <p class="text-muted">JPG, PNG (Max 5MB)</p>
                                            <input type="file"
                                                name="photo_proof"
                                                id="photo_proof"
                                                accept="image/*"
                                                class="d-none">
                                        </div>
                                        <div id="main-photo-preview" class="photo-preview-container"></div>
                                    </div>

                                    <!-- Additional Photos -->
                                    <div class="mb-3">
                                        <label class="font-weight-bold">Foto Tambahan (Optional)</label>
                                        <div class="photo-upload-zone" id="additional-photos-zone">
                                            <i class="fas fa-images fa-3x text-success mb-3"></i>
                                            <h5>Upload Multiple Photos</h5>
                                            <p class="text-muted">Max 5 photos</p>
                                            <input type="file"
                                                name="pod_photos[]"
                                                id="pod_photos"
                                                accept="image/*"
                                                multiple
                                                class="d-none">
                                        </div>
                                        <div id="additional-photos-preview" class="photo-preview-container"></div>
                                    </div>
                                </div>

                                <!-- SECTION 7: NOTES -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="fas fa-sticky-note"></i> Catatan
                                    </div>
                                    <textarea name="delivery_notes"
                                        class="form-control"
                                        rows="4"
                                        placeholder="Catatan tambahan tentang pengiriman (optional)"></textarea>
                                </div>

                                <!-- SUBMIT BUTTON -->
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-submit-pod">
                                        <i class="fas fa-paper-plane"></i> Submit POD
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        $(document).ready(function() {
            // ============================================
            // SIGNATURE PAD
            // ============================================
            const canvas = document.getElementById('signature-pad');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });

            // Resize canvas
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear();
            }
            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            // Clear signature
            $('#clear-signature').on('click', function() {
                signaturePad.clear();
            });

            // ============================================
            // QUANTITY BUTTONS
            // ============================================
            $('.qty-btn').on('click', function() {
                const action = $(this).data('action');
                const target = $(this).data('target');
                const input = $('#' + target);
                let value = parseInt(input.val()) || 0;

                if (action === 'increase') {
                    value++;
                } else if (action === 'decrease' && value > 0) {
                    value--;
                }

                input.val(value);
            });

            // ============================================
            // PHOTO UPLOAD - MAIN
            // ============================================
            $('#main-photo-zone').on('click', function() {
                $('#photo_proof').click();
            });

            $('#photo_proof').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    previewPhoto(file, '#main-photo-preview', false);
                }
            });

            // ============================================
            // PHOTO UPLOAD - ADDITIONAL
            // ============================================
            $('#additional-photos-zone').on('click', function() {
                $('#pod_photos').click();
            });

            $('#pod_photos').on('change', function(e) {
                $('#additional-photos-preview').html('');
                Array.from(e.target.files).forEach((file, index) => {
                    if (index < 5) { // Max 5 photos
                        previewPhoto(file, '#additional-photos-preview', true);
                    }
                });
            });

            // Preview photo function
            function previewPhoto(file, container, multiple) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = `
                        <div class="photo-preview-item">
                            <img src="${e.target.result}" alt="Preview">
                            ${!multiple ? '<button type="button" class="remove-photo"><i class="fas fa-times"></i></button>' : ''}
                        </div>
                    `;

                    if (multiple) {
                        $(container).append(preview);
                    } else {
                        $(container).html(preview);
                    }
                }
                reader.readAsDataURL(file);
            }

            // Remove photo
            $(document).on('click', '.remove-photo', function() {
                $(this).closest('.photo-preview-item').remove();
                $('#photo_proof').val('');
            });

            // Drag & Drop
            $('.photo-upload-zone').on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            $('.photo-upload-zone').on('dragleave', function(e) {
                $(this).removeClass('dragover');
            });

            $('.photo-upload-zone').on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');

                const files = e.originalEvent.dataTransfer.files;
                const inputId = $(this).find('input[type="file"]').attr('id');

                if (inputId === 'photo_proof' && files.length > 0) {
                    previewPhoto(files[0], '#main-photo-preview', false);
                    $('#photo_proof')[0].files = files;
                } else if (inputId === 'pod_photos') {
                    $('#pod_photos')[0].files = files;
                    $('#pod_photos').trigger('change');
                }
            });

            // ============================================
            // FORM SUBMISSION
            // ============================================
            $('#podForm').on('submit', function(e) {
                // Save signature
                if (!signaturePad.isEmpty()) {
                    const signatureData = signaturePad.toDataURL('image/png');
                    $('#signature-data').val(signatureData);
                }

                // Validate
                if ($('#qty_delivered').val() == 0) {
                    e.preventDefault();
                    alert('Jumlah diterima harus lebih dari 0!');
                    return false;
                }

                if ($('input[name="receiver_name"]').val().trim() === '') {
                    e.preventDefault();
                    alert('Nama penerima harus diisi!');
                    return false;
                }

                // Show loading
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true);
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
            });

            console.log('📋 POD Form Ready - SJ: <?= $sj->no_sj ?>');
        });
    </script>
</body>

</html>