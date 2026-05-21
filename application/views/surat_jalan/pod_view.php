<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>

    <!-- Lightbox for photos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --timeline-color: #667eea;
        }

        /* Header Card */
        .pod-header-card {
            background: var(--primary-gradient);
            color: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.3);
        }

        .pod-header-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .pod-header-subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .pod-status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .pod-status-badge.completed {
            background: #11998e;
            color: white;
        }

        .pod-status-badge.pending {
            background: #f6c23e;
            color: #856404;
        }

        /* Info Grid */
        .info-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .info-card-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            color: #858796;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .info-card-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #5a5c69;
        }

        .info-card-icon {
            font-size: 2rem;
            color: #667eea;
            opacity: 0.2;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Section Cards */
        .section-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
            padding: 20px 24px;
            border-bottom: 3px solid #667eea;
        }

        .section-header h5 {
            margin: 0;
            font-weight: 700;
            color: #5a5c69;
            display: flex;
            align-items: center;
        }

        .section-header h5 i {
            margin-right: 12px;
            color: #667eea;
        }

        .section-body {
            padding: 24px;
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 60px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, #667eea, #764ba2);
            border-radius: 2px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .timeline-marker {
            position: absolute;
            left: -52px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: white;
            border: 4px solid #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            z-index: 2;
        }

        .timeline-marker i {
            color: #667eea;
            font-size: 1.2rem;
        }

        .timeline-marker.completed {
            background: var(--success-gradient);
            border-color: #11998e;
        }

        .timeline-marker.completed i {
            color: white;
        }

        .timeline-content {
            background: #f8f9fc;
            border-radius: 12px;
            padding: 16px 20px;
            border-left: 3px solid #667eea;
        }

        .timeline-time {
            font-size: 0.85rem;
            color: #858796;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .timeline-title {
            font-weight: 700;
            color: #5a5c69;
            margin-bottom: 4px;
        }

        .timeline-description {
            font-size: 0.9rem;
            color: #858796;
            margin: 0;
        }

        /* Receiver Info */
        .receiver-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }

        .receiver-card h6 {
            margin-bottom: 16px;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .receiver-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .receiver-info-item i {
            width: 30px;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Signature */
        .signature-container {
            background: white;
            border: 3px solid #e3e6f0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .signature-container img {
            max-width: 100%;
            max-height: 200px;
            border: 2px dashed #d1d3e2;
            border-radius: 8px;
            padding: 10px;
        }

        .signature-label {
            margin-top: 12px;
            font-weight: 600;
            color: #5a5c69;
            font-size: 0.9rem;
        }

        /* Photo Gallery */
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 16px;
        }

        .photo-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .photo-item:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .photo-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .photo-item-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            color: white;
            padding: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .photo-empty {
            text-align: center;
            padding: 40px;
            color: #858796;
        }

        .photo-empty i {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 12px;
        }

        /* Condition Badge */
        .condition-badge-large {
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .condition-badge-large.baik {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .condition-badge-large.rusak {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .condition-badge-large.rusak_sebagian,
        .condition-badge-large.kurang {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        /* Quantity Display */
        .qty-display {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 12px;
            color: white;
        }

        .qty-display .qty-number {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
        }

        .qty-display .qty-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-action.btn-print {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-action.btn-complete {
            background: var(--success-gradient);
            color: white;
        }

        /* Notes Display */
        .notes-display {
            background: #fff3cd;
            border-left: 4px solid #f6c23e;
            padding: 16px 20px;
            border-radius: 8px;
            margin-top: 16px;
        }

        .notes-display strong {
            display: block;
            color: #856404;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .notes-display p {
            color: #856404;
            margin: 0;
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
                        <a href="<?= base_url('surat_jalan/pod_dashboard?status=completed') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to POD Dashboard
                        </a>
                    </div>

                    <!-- POD HEADER -->
                    <div class="pod-header-card position-relative">
                        <span class="pod-status-badge <?= $pod->pod_status ?>">
                            <i class="fas fa-<?= $pod->pod_status == 'completed' ? 'check-circle' : 'clock' ?>"></i>
                            <?= strtoupper($pod->pod_status) ?>
                        </span>

                        <div class="pod-header-title">
                            <i class="fas fa-clipboard-check"></i> Proof of Delivery
                        </div>
                        <div class="pod-header-subtitle">
                            Surat Jalan: <strong><?= $pod->no_sj ?></strong>
                        </div>
                        <div class="mt-3">
                            <small style="opacity: 0.8;">Submitted by:</small>
                            <strong><?= $pod->pod_submitted_by ?? '-' ?></strong>
                            <span class="mx-2">•</span>
                            <small style="opacity: 0.8;">
                                <?= $pod->pod_submitted_at ? date('d/m/Y H:i', strtotime($pod->pod_submitted_at)) : '-' ?>
                            </small>
                        </div>
                    </div>

                    <!-- QUICK INFO GRID -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="info-card position-relative">
                                <div class="info-card-title">Driver</div>
                                <div class="info-card-value"><?= htmlspecialchars($pod->nama_driver ?? '-') ?></div>
                                <i class="fas fa-user-tie info-card-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="info-card position-relative">
                                <div class="info-card-title">No Polisi</div>
                                <div class="info-card-value"><?= $pod->no_polisi ?? '-' ?></div>
                                <i class="fas fa-truck info-card-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="info-card position-relative">
                                <div class="info-card-title">Tanggal Trip</div>
                                <div class="info-card-value" style="font-size: 1.2rem;">
                                    <?= date('d/m/Y', strtotime($pod->tanggal)) ?>
                                </div>
                                <i class="fas fa-calendar info-card-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="info-card position-relative">
                                <div class="info-card-title">Tujuan</div>
                                <div class="info-card-value" style="font-size: 1.1rem;">
                                    <?= htmlspecialchars($pod->tujuan ?? '-') ?>
                                </div>
                                <i class="fas fa-map-marker-alt info-card-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- LEFT COLUMN -->
                        <div class="col-lg-8">
                            <!-- DELIVERY DETAILS -->
                            <div class="section-card">
                                <div class="section-header">
                                    <h5>
                                        <i class="fas fa-box-open"></i>
                                        Delivery Details
                                    </h5>
                                </div>
                                <div class="section-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="qty-display">
                                                <div class="qty-number"><?= $pod->qty_delivered ?? 0 ?></div>
                                                <div class="qty-label">Quantity Delivered</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="qty-display" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                                                <div class="qty-number"><?= $pod->qty_rejected ?? 0 ?></div>
                                                <div class="qty-label">Quantity Rejected</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mb-4">
                                        <span class="condition-badge-large <?= $pod->delivery_condition ?? 'baik' ?>">
                                            <i class="fas fa-<?= ($pod->delivery_condition ?? 'baik') == 'baik' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                                            Kondisi: <?= ucfirst(str_replace('_', ' ', $pod->delivery_condition ?? 'baik')) ?>
                                        </span>
                                    </div>

                                    <?php if ($pod->delivery_notes): ?>
                                        <div class="notes-display">
                                            <strong><i class="fas fa-sticky-note"></i> Catatan Pengiriman:</strong>
                                            <p><?= nl2br(htmlspecialchars($pod->delivery_notes)) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- TIMELINE -->
                            <div class="section-card">
                                <div class="section-header">
                                    <h5>
                                        <i class="fas fa-history"></i>
                                        Trip Timeline
                                    </h5>
                                </div>
                                <div class="section-body">
                                    <div class="timeline">
                                        <?php if (!empty($pod->timeline)): ?>
                                            <?php foreach ($pod->timeline as $event): ?>
                                                <div class="timeline-item">
                                                    <div class="timeline-marker <?= in_array($event->event_type, ['completed', 'pod_submitted', 'arrival']) ? 'completed' : '' ?>">
                                                        <i class="fas fa-<?= $this->get_event_icon($event->event_type) ?>"></i>
                                                    </div>
                                                    <div class="timeline-content">
                                                        <div class="timeline-time">
                                                            <?= date('d/m/Y H:i', strtotime($event->event_time)) ?>
                                                        </div>
                                                        <div class="timeline-title">
                                                            <?= $this->get_event_title($event->event_type) ?>
                                                        </div>
                                                        <?php if ($event->notes): ?>
                                                            <p class="timeline-description"><?= htmlspecialchars($event->notes) ?></p>
                                                        <?php endif; ?>
                                                        <?php if ($event->location_name): ?>
                                                            <p class="timeline-description">
                                                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event->location_name) ?>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-muted text-center">No timeline data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- PHOTOS -->
                            <div class="section-card">
                                <div class="section-header">
                                    <h5>
                                        <i class="fas fa-images"></i>
                                        Photo Gallery (<?= count($pod->photos ?? []) ?>)
                                    </h5>
                                </div>
                                <div class="section-body">
                                    <?php if (!empty($pod->photos)): ?>
                                        <div class="photo-gallery">
                                            <?php foreach ($pod->photos as $photo): ?>
                                                <a href="<?= base_url('uploads/pod/photos/' . $photo->photo_path) ?>"
                                                    data-lightbox="pod-gallery"
                                                    data-title="<?= htmlspecialchars($photo->description ?? '') ?>"
                                                    class="photo-item">
                                                    <img src="<?= base_url('uploads/pod/photos/' . $photo->photo_path) ?>"
                                                        alt="Photo">
                                                    <div class="photo-item-label">
                                                        <?= ucfirst($photo->photo_type) ?>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($pod->photo_proof): ?>
                                        <div class="photo-gallery">
                                            <a href="<?= base_url('uploads/pod/proof/' . $pod->photo_proof) ?>"
                                                data-lightbox="pod-gallery"
                                                class="photo-item">
                                                <img src="<?= base_url('uploads/pod/proof/' . $pod->photo_proof) ?>"
                                                    alt="Proof Photo">
                                                <div class="photo-item-label">Main Photo</div>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="photo-empty">
                                            <i class="fas fa-image"></i>
                                            <p>No photos available</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-lg-4">
                            <!-- RECEIVER INFO -->
                            <div class="receiver-card">
                                <h6><i class="fas fa-user-check"></i> Receiver Information</h6>
                                <div class="receiver-info-item">
                                    <i class="fas fa-user"></i>
                                    <div>
                                        <strong><?= htmlspecialchars($pod->receiver_name ?? '-') ?></strong>
                                    </div>
                                </div>
                                <?php if ($pod->receiver_phone): ?>
                                    <div class="receiver-info-item">
                                        <i class="fas fa-phone"></i>
                                        <div><?= htmlspecialchars($pod->receiver_phone) ?></div>
                                    </div>
                                <?php endif; ?>
                                <div class="receiver-info-item">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <small style="opacity: 0.9;">Received at:</small><br>
                                        <strong>
                                            <?= $pod->arrival_time ? date('d/m/Y H:i', strtotime($pod->arrival_time)) : '-' ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <!-- SIGNATURE -->
                            <?php if ($pod->receiver_signature): ?>
                                <div class="section-card">
                                    <div class="section-header">
                                        <h5>
                                            <i class="fas fa-signature"></i>
                                            Signature
                                        </h5>
                                    </div>
                                    <div class="section-body">
                                        <div class="signature-container">
                                            <img src="<?= base_url('uploads/pod/signatures/' . $pod->receiver_signature) ?>"
                                                alt="Signature">
                                            <div class="signature-label">
                                                Signed by: <?= htmlspecialchars($pod->receiver_name ?? '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- UNLOADING TIMES -->
                            <?php if ($pod->unloading_start || $pod->unloading_finish): ?>
                                <div class="section-card">
                                    <div class="section-header">
                                        <h5>
                                            <i class="fas fa-hourglass-half"></i>
                                            Unloading Time
                                        </h5>
                                    </div>
                                    <div class="section-body">
                                        <?php if ($pod->unloading_start): ?>
                                            <p class="mb-2">
                                                <strong>Start:</strong>
                                                <?= date('H:i', strtotime($pod->unloading_start)) ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($pod->unloading_finish): ?>
                                            <p class="mb-2">
                                                <strong>Finish:</strong>
                                                <?= date('H:i', strtotime($pod->unloading_finish)) ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($pod->unloading_start && $pod->unloading_finish): ?>
                                            <?php
                                            $start = strtotime($pod->unloading_start);
                                            $finish = strtotime($pod->unloading_finish);
                                            $duration = round(($finish - $start) / 60);
                                            ?>
                                            <p class="mb-0">
                                                <strong>Duration:</strong>
                                                <span class="badge badge-primary"><?= $duration ?> minutes</span>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- ACTIONS -->
                            <div class="section-card">
                                <div class="section-header">
                                    <h5>
                                        <i class="fas fa-cog"></i>
                                        Actions
                                    </h5>
                                </div>
                                <div class="section-body">
                                    <div class="action-buttons">
                                        <a href="<?= base_url('surat_jalan/print_pod/' . $pod->id) ?>"
                                            target="_blank"
                                            class="btn btn-action btn-print btn-block">
                                            <i class="fas fa-print"></i> Print POD
                                        </a>

                                        <?php if ($pod->status == 'delivered' && $pod->pod_status == 'completed'): ?>
                                            <button type="button"
                                                class="btn btn-action btn-complete btn-block"
                                                onclick="markReturning(<?= $pod->id ?>)">
                                                <i class="fas fa-undo"></i> Mark as Returning
                                            </button>
                                        <?php endif; ?>

                                        <a href="<?= base_url('surat_jalan/detail/' . $pod->id) ?>"
                                            class="btn btn-action btn-secondary btn-block">
                                            <i class="fas fa-file-alt"></i> View Surat Jalan
                                        </a>
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

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

    <script>
        function markReturning(sjId) {
            if (confirm('Mark this trip as RETURNING to depot?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('surat_jalan/mark_returning/') ?>' + sjId;

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'return_time';
                input.value = new Date().toISOString().slice(0, 19).replace('T', ' ');

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Lightbox config
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': 'Photo %1 of %2'
        });

        console.log('📋 POD View loaded - SJ: <?= $pod->no_sj ?>');
    </script>
</body>

</html>