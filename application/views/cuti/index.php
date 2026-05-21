<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .cuti-hero {
            background: linear-gradient(135deg, #1a1f3a 0%, #2d3561 60%, #1e3a5f 100%);
            border-radius: 16px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .cuti-hero::before {
            content: '';
            position: absolute;
            right: -60px;
            top: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .cuti-hero::after {
            content: '';
            position: absolute;
            right: 60px;
            bottom: -80px;
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }

        .sisa-angka {
            font-size: 3.5rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -2px;
        }

        .sisa-label {
            font-size: 0.78rem;
            opacity: 0.6;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 4px;
        }

        .cuti-stat {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 12px 16px;
            text-align: center;
        }

        .cuti-stat .val {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
        }

        .cuti-stat .lbl {
            font-size: 0.68rem;
            opacity: 0.6;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 2px;
        }

        .form-cuti-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
        }

        .form-cuti-card .card-header {
            border-radius: 14px 14px 0 0;
            border-bottom: 1px solid #e3e6f0;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-pending {
            background: #fff8e1;
            color: #f59e0b;
            border: 1px solid #fcd34d;
        }

        .status-disetujui {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #86efac;
        }

        .status-ditolak {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        .riwayat-table th {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #b7b9cc;
            background: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            padding: 10px 14px;
        }

        .riwayat-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f0f2f8;
            vertical-align: middle;
            font-size: 0.88rem;
        }

        .riwayat-table tr:last-child td {
            border-bottom: none;
        }

        .riwayat-table tr:hover td {
            background: #f8f9fc;
        }

        .date-range {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
        }

        .date-range .arrow {
            color: #b7b9cc;
            font-size: 0.75rem;
        }

        .hari-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 26px;
            padding: 0 8px;
            border-radius: 6px;
            background: #f0f2f8;
            color: #5a5c69;
            font-weight: 700;
            font-size: 0.78rem;
        }

        .admin-panel {
            background: linear-gradient(135deg, #fff8e1, #fffbeb);
            border: 1.5px solid #fcd34d;
            border-radius: 14px;
            margin-bottom: 20px;
        }

        .admin-panel .panel-header {
            padding: 14px 20px;
            border-bottom: 1px solid #fde68a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .empty-cuti {
            text-align: center;
            padding: 48px 24px;
            color: #b7b9cc;
        }

        .empty-cuti i {
            font-size: 3rem;
            margin-bottom: 12px;
            display: block;
            opacity: 0.4;
        }

        .cuti-progress {
            height: 6px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.15);
            overflow: hidden;
            margin-top: 10px;
        }

        .cuti-progress-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, #1cc88a, #36b9cc);
            transition: width 0.6s ease;
        }

        .approve-actions {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .btn-approve {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-approve.ok {
            background: #dcfce7;
            color: #16a34a;
        }

        .btn-approve.ok:hover {
            background: #16a34a;
            color: #fff;
        }

        .btn-approve.tolak {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-approve.tolak:hover {
            background: #dc2626;
            color: #fff;
        }

        .btn-approve:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .processed-label {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            color: #b7b9cc;
            font-style: italic;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <?php
                    $sisa = (int) ($pengguna->sisa_cuti ?? 0);
                    $jatah = (int) ($pengguna->jatah_cuti ?? 12);
                    $terpakai = $jatah - $sisa;
                    $pct_sisa = $jatah > 0 ? round(($sisa / $jatah) * 100) : 0;
                    $pending_ct = count(array_filter($cuti_list, fn($c) => $c->status === 'Pending'));
                    $disetujui_ct = count(array_filter($cuti_list, fn($c) => $c->status === 'Disetujui'));
                    $ditolak_ct = count(array_filter($cuti_list, fn($c) => $c->status === 'Ditolak'));
                    ?>

                    <!-- Flash -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-3">
                            <i class="fas fa-check-circle me-1"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-3">
                            <i class="fas fa-exclamation-triangle me-1"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <!-- ── Hero: sisa cuti ── -->
                    <div class="cuti-hero">
                        <div class="row align-items-center g-3">
                            <div class="col-md-5">
                                <div class="d-flex align-items-center gap-3 mb-1">
                                    <img src="<?= base_url('uploads/profil/' . ($pengguna->foto_profil ?? 'default-1.png')) ?>"
                                        style="width:44px;height:44px;border-radius:50%;border:2px solid rgba(255,255,255,0.3);object-fit:cover;"
                                        alt="">
                                    <div>
                                        <div
                                            style="font-size:0.78rem;opacity:0.6;text-transform:uppercase;letter-spacing:0.1em;">
                                            Hak Cuti Tahunan</div>
                                        <div style="font-size:1rem;font-weight:700;"><?= $pengguna->nama ?></div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end gap-3 mt-3">
                                    <div>
                                        <div class="sisa-angka <?= $sisa <= 3 ? 'text-warning' : '' ?>"><?= $sisa ?>
                                        </div>
                                        <div class="sisa-label">hari tersisa</div>
                                    </div>
                                    <div style="padding-bottom:8px;opacity:0.4;font-size:1.5rem;">/</div>
                                    <div>
                                        <div style="font-size:1.8rem;font-weight:700;opacity:0.7;"><?= $jatah ?></div>
                                        <div class="sisa-label">jatah cuti</div>
                                    </div>
                                </div>
                                <div class="cuti-progress mt-2">
                                    <div class="cuti-progress-fill" style="width:<?= $pct_sisa ?>%;"></div>
                                </div>
                                <div style="font-size:0.72rem;opacity:0.5;margin-top:5px;"><?= $terpakai ?> hari telah
                                    digunakan tahun ini</div>
                            </div>
                            <div class="col-md-7">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <div class="cuti-stat">
                                            <div class="val text-warning"><?= $pending_ct ?></div>
                                            <div class="lbl">Pending</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="cuti-stat">
                                            <div class="val text-success"><?= $disetujui_ct ?></div>
                                            <div class="lbl">Disetujui</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="cuti-stat">
                                            <div class="val text-danger"><?= $ditolak_ct ?></div>
                                            <div class="lbl">Ditolak</div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($sisa <= 3 && $sisa > 0): ?>
                                    <div class="mt-2 px-3 py-2 rounded"
                                        style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);font-size:0.8rem;">
                                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                        Sisa cuti Anda tinggal <strong><?= $sisa ?> hari</strong>. Gunakan dengan bijak!
                                    </div>
                                <?php elseif ($sisa == 0): ?>
                                    <div class="mt-2 px-3 py-2 rounded"
                                        style="background:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.3);font-size:0.8rem;">
                                        <i class="fas fa-times-circle text-danger me-1"></i>
                                        Jatah cuti Anda sudah habis untuk tahun ini.
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>

                    <!-- ── Admin Panel: semua cuti PENDING saja ── -->
                    <?php if ($is_admin): ?>
                        <div class="admin-panel">
                            <div class="panel-header">
                                <i class="fas fa-shield-alt text-warning"></i>
                                <span class="fw-bold">Panel Superadmin — Cuti Menunggu Persetujuan</span>
                                <span class="badge bg-warning text-dark ms-auto" id="pendingCount">Loading...</span>
                            </div>
                            <div class="p-3" id="pendingList">
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-spinner fa-spin me-2"></i> Memuat...
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                    <div class="row g-4">

                        <!-- FORM AJUKAN CUTI -->
                        <div class="col-lg-4">
                            <div class="card form-cuti-card h-100">
                                <div class="card-header bg-primary py-3">
                                    <h6 class="m-0 text-white fw-bold">
                                        <i class="fas fa-paper-plane me-2"></i> Ajukan Cuti Baru
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($sisa <= 0): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-calendar-times fa-3x text-danger mb-3 d-block"
                                                style="opacity:0.4;"></i>
                                            <p class="text-muted mb-0">Jatah cuti Anda sudah habis.<br>Tidak dapat
                                                mengajukan cuti baru.</p>
                                        </div>
                                    <?php else: ?>
                                        <form action="<?= base_url('cuti/proses_ajukan') ?>" method="POST" id="form-cuti">
                                            <div class="form-group mb-3">
                                                <label class="fw-bold small">Tanggal Mulai <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                                    class="form-control" min="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="fw-bold small">Tanggal Selesai <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                                    class="form-control" min="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <!-- Preview jumlah hari -->
                                            <div id="preview-hari" class="mb-3 p-3 rounded"
                                                style="background:#f0f4ff;border:1.5px solid #d0d8f5;display:none;">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="small text-muted">Durasi cuti:</span>
                                                    <span class="fw-bold text-primary" id="jumlah-hari-text">— hari</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-1">
                                                    <span class="small text-muted">Sisa setelah cuti:</span>
                                                    <span class="fw-bold" id="sisa-setelah-text">—</span>
                                                </div>
                                            </div>
                                            <div class="form-group mb-4">
                                                <label class="fw-bold small">Alasan / Keterangan <span
                                                        class="text-danger">*</span></label>
                                                <textarea name="alasan" class="form-control" rows="4"
                                                    placeholder="Tuliskan alasan pengajuan cuti..." required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100" id="btn-submit-cuti">
                                                <i class="fas fa-paper-plane me-2"></i> Kirim Pengajuan
                                            </button>
                                        </form>
                                    <?php endif ?>
                                </div>
                                <div class="card-footer bg-light border-top-0 rounded-bottom">
                                    <ul class="mb-0 ps-3 small text-muted">
                                        <li>Pengajuan akan diproses oleh Superadmin</li>
                                        <li>Cuti berstatus <strong>Pending</strong> bisa dibatalkan</li>
                                        <li>Sisa cuti berkurang otomatis saat disetujui</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- RIWAYAT CUTI -->
                        <div class="col-lg-8">
                            <div class="card form-cuti-card">
                                <div
                                    class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                                    <h6 class="m-0 fw-bold">
                                        <i class="fas fa-history text-muted me-2"></i> Riwayat Pengajuan Cuti
                                    </h6>
                                    <span class="badge bg-secondary fw-normal"><?= count($cuti_list) ?> pengajuan</span>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (empty($cuti_list)): ?>
                                        <div class="empty-cuti">
                                            <i class="fas fa-umbrella-beach"></i>
                                            <p class="mb-0 fw-bold" style="color:#6c757d;">Belum ada pengajuan cuti</p>
                                            <small>Ajukan cuti pertama Anda menggunakan form di sebelah kiri</small>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="riwayat-table w-100">
                                                <thead>
                                                    <tr>
                                                        <th>Periode</th>
                                                        <th class="text-center">Durasi</th>
                                                        <th>Alasan</th>
                                                        <th>Status</th>
                                                        <th>Catatan Admin</th>
                                                        <th class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($cuti_list as $c): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="date-range">
                                                                    <span><?= date('d M Y', strtotime($c->tanggal_mulai)) ?></span>
                                                                    <span class="arrow"><i
                                                                            class="fas fa-arrow-right"></i></span>
                                                                    <span><?= date('d M Y', strtotime($c->tanggal_selesai)) ?></span>
                                                                </div>
                                                                <div style="font-size:0.7rem;color:#b7b9cc;margin-top:3px;">
                                                                    Diajukan <?= date('d M Y', strtotime($c->created_at)) ?>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="hari-badge"><?= $c->jumlah_hari ?> hr</span>
                                                            </td>
                                                            <td>
                                                                <span style="font-size:0.85rem;">
                                                                    <?= nl2br(htmlspecialchars(mb_strimwidth($c->alasan, 0, 60, '...'))) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php if ($c->status === 'Pending'): ?>
                                                                    <span class="status-pill status-pending"><i
                                                                            class="fas fa-clock"></i> Pending</span>
                                                                <?php elseif ($c->status === 'Disetujui'): ?>
                                                                    <span class="status-pill status-disetujui"><i
                                                                            class="fas fa-check"></i> Disetujui</span>
                                                                <?php else: ?>
                                                                    <span class="status-pill status-ditolak"><i
                                                                            class="fas fa-times"></i> Ditolak</span>
                                                                <?php endif ?>
                                                            </td>
                                                            <td>
                                                                <span style="font-size:0.8rem;color:#6c757d;">
                                                                    <?= $c->catatan_admin ? htmlspecialchars($c->catatan_admin) : '<em style="color:#b7b9cc;">—</em>' ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if ($c->status === 'Pending'): ?>
                                                                    <a href="<?= base_url('cuti/hapus/' . $c->id) ?>"
                                                                        class="btn btn-sm btn-outline-danger btn-batal"
                                                                        style="font-size:0.72rem;padding:3px 10px;">
                                                                        <i class="fas fa-times"></i> Batalkan
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-muted small">—</span>
                                                                <?php endif ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>

                    </div><!-- /row -->

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {

            // Matikan semua cache AJAX jQuery
            $.ajaxSetup({ cache: false });

            const sisaCuti = <?= $sisa ?>;

            // ── Preview durasi ──
            function hitungHari() {
                const mulai = $('#tanggal_mulai').val();
                const selesai = $('#tanggal_selesai').val();
                if (!mulai || !selesai) { $('#preview-hari').hide(); return; }
                const d1 = new Date(mulai), d2 = new Date(selesai);
                if (d2 < d1) { $('#preview-hari').hide(); return; }
                const jumlah = Math.round((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
                const sisaSetelah = sisaCuti - jumlah;
                $('#jumlah-hari-text').text(jumlah + ' hari');
                $('#sisa-setelah-text')
                    .text(sisaSetelah + ' hari')
                    .removeClass('text-danger text-success text-warning')
                    .addClass(sisaSetelah < 0 ? 'text-danger' : (sisaSetelah <= 3 ? 'text-warning' : 'text-success'));
                $('#preview-hari').show();
            }

            $('#tanggal_mulai, #tanggal_selesai').on('change', function () {
                const mulai = $('#tanggal_mulai').val();
                if (mulai) $('#tanggal_selesai').attr('min', mulai);
                hitungHari();
            });

            // ── Submit form ──
            $('#form-cuti').on('submit', function (e) {
                e.preventDefault();
                const mulai = $('#tanggal_mulai').val();
                const selesai = $('#tanggal_selesai').val();
                const alasan = $('textarea[name=alasan]').val().trim();

                if (!mulai || !selesai || !alasan) {
                    Swal.fire({ icon: 'warning', title: 'Lengkapi Form', text: 'Semua field wajib diisi!' });
                    return;
                }

                const jumlah = Math.round((new Date(selesai) - new Date(mulai)) / (1000 * 60 * 60 * 24)) + 1;
                if (jumlah > sisaCuti) {
                    Swal.fire({
                        icon: 'error', title: 'Sisa Cuti Tidak Cukup',
                        html: `Sisa cuti Anda <strong>${sisaCuti} hari</strong>, diajukan <strong>${jumlah} hari</strong>.`
                    });
                    return;
                }

                const fmt = d => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                Swal.fire({
                    title: 'Kirim Pengajuan Cuti?',
                    html: `<div class="text-start">
                        <p class="mb-1"><i class="fas fa-calendar me-2 text-primary"></i>${fmt(mulai)} — ${fmt(selesai)}</p>
                        <p class="mb-1"><i class="fas fa-sun me-2 text-warning"></i><strong>${jumlah} hari</strong></p>
                        <p class="mb-0 text-muted small">${alasan.substring(0, 80)}${alasan.length > 80 ? '...' : ''}</p>
                    </div>`,
                    icon: 'question', showCancelButton: true,
                    confirmButtonColor: '#4e73df',
                    confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Kirim',
                    cancelButtonText: 'Batal', reverseButtons: true
                }).then(r => {
                    if (r.isConfirmed) {
                        $('#btn-submit-cuti').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin me-2"></i> Mengirim...');
                        document.getElementById('form-cuti').submit();
                    }
                });
            });

            // ── Batalkan cuti ──
            $(document).on('click', '.btn-batal', function (e) {
                e.preventDefault();
                const href = $(this).attr('href');
                Swal.fire({
                    title: 'Batalkan Pengajuan?', text: 'Pengajuan cuti ini akan dihapus.',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d33', confirmButtonText: 'Ya, Batalkan',
                    cancelButtonText: 'Tidak', reverseButtons: true
                }).then(r => { if (r.isConfirmed) window.location.href = href; });
            });

            // ════════════════════════════════════════
            // ADMIN PANEL — superadmin only
            // ════════════════════════════════════════
            <?php if ($is_admin): ?>

                // Guard: cegah double klik approve
                let isProcessing = false;

                function loadPending() {
                    // Cache buster timestamp supaya browser tidak cache response
                    const url = '<?= base_url('cuti/get_pending') ?>?_=' + new Date().getTime();

                    $.getJSON(url, function (data) {
                        const count = data.length;
                        $('#pendingCount').text(count + ' pending');

                        if (count === 0) {
                            $('#pendingList').html(
                                '<div class="text-center text-muted py-3 small">' +
                                '<i class="fas fa-check-circle text-success me-1"></i> Tidak ada pengajuan yang menunggu</div>'
                            );
                            return;
                        }

                        let html = '<div class="table-responsive"><table class="riwayat-table w-100"><thead><tr>' +
                            '<th>Karyawan</th><th>Periode</th><th class="text-center">Hari</th><th>Alasan</th><th>Aksi</th>' +
                            '</tr></thead><tbody>';

                        data.forEach(c => {
                            const fmt = s => new Date(s).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

                            const aksiHtml = (c.status === 'Pending')
                                ? `<div class="approve-actions">
                                <button class="btn-approve ok btn-do-approve"
                                    data-id="${c.id}" data-status="Disetujui"
                                    data-nama="${c.karyawan_nama}" data-hari="${c.jumlah_hari}">
                                    <i class="fas fa-check"></i> Setujui
                                </button>
                                <button class="btn-approve tolak btn-do-approve"
                                    data-id="${c.id}" data-status="Ditolak"
                                    data-nama="${c.karyawan_nama}" data-hari="${c.jumlah_hari}">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                               </div>`
                                : `<span class="processed-label"><i class="fas fa-check-circle text-success"></i> Sudah diproses</span>`;

                            html += `<tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= base_url('uploads/profil/') ?>${c.foto_profil || 'default-1.png'}"
                                         style="width:30px;height:30px;border-radius:50%;object-fit:cover;" alt="">
                                    <div>
                                        <div class="fw-bold small">${c.karyawan_nama}</div>
                                        <div class="text-muted" style="font-size:0.7rem;">${c.nik}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="small">${fmt(c.tanggal_mulai)} — ${fmt(c.tanggal_selesai)}</td>
                            <td class="text-center"><span class="hari-badge">${c.jumlah_hari} hr</span></td>
                            <td class="small text-muted">${c.alasan.substring(0, 50)}${c.alasan.length > 50 ? '...' : ''}</td>
                            <td>${aksiHtml}</td>
                        </tr>`;
                        });

                        html += '</tbody></table></div>';
                        $('#pendingList').html(html);

                    }).fail(function (xhr, status, error) {
                        console.error('loadPending gagal:', status, error);
                        $('#pendingList').html(
                            '<div class="text-center text-danger py-3 small">' +
                            '<i class="fas fa-exclamation-triangle me-1"></i> Gagal memuat data pending</div>'
                        );
                    });
                }

                loadPending();

                // Approve / tolak
                $(document).on('click', '.btn-do-approve', function () {
                    if (isProcessing) return;

                    const $btn = $(this);
                    const id = $btn.data('id');
                    const status = $btn.data('status');
                    const nama = $btn.data('nama');
                    const hari = $btn.data('hari');
                    const isOk = (status === 'Disetujui');

                    Swal.fire({
                        title: isOk ? 'Setujui Cuti?' : 'Tolak Cuti?',
                        html: `<p>Cuti <strong>${nama}</strong> selama <strong>${hari} hari</strong></p>
                           <input type="text" id="catatan_admin" class="swal2-input" placeholder="Catatan admin (opsional)">`,
                        icon: isOk ? 'question' : 'warning',
                        showCancelButton: true,
                        confirmButtonColor: isOk ? '#1cc88a' : '#d33',
                        confirmButtonText: isOk ? '<i class="fas fa-check"></i> Setujui' : '<i class="fas fa-times"></i> Tolak',
                        cancelButtonText: 'Batal', reverseButtons: true,
                        preConfirm: () => ({ catatan: document.getElementById('catatan_admin').value })
                    }).then(r => {
                        if (!r.isConfirmed) return;

                        isProcessing = true;
                        $btn.closest('tr').find('.btn-do-approve').prop('disabled', true);

                        $.ajax({
                            url: '<?= base_url('cuti/approve/') ?>' + id + '/' + status,
                            method: 'POST',
                            dataType: 'json',
                            data: { catatan_admin: r.value.catatan },
                            success: function (res) {
                                if (res && res.success) {
                                    Swal.fire({
                                        icon: isOk ? 'success' : 'info',
                                        title: isOk ? 'Cuti Disetujui!' : 'Cuti Ditolak',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        isProcessing = false;
                                        window.location.reload();
                                    });
                                } else {
                                    isProcessing = false;
                                    $btn.closest('tr').find('.btn-do-approve').prop('disabled', false);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: (res && res.msg) ? res.msg : 'Terjadi kesalahan.'
                                    });
                                }
                            },
                            error: function (xhr) {
                                isProcessing = false;
                                $btn.closest('tr').find('.btn-do-approve').prop('disabled', false);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'HTTP ' + xhr.status + ': ' + xhr.statusText
                                });
                            }
                        });
                    });
                });

            <?php endif ?>

            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
        });
    </script>
</body>

</html>