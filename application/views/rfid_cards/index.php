<?php $this->load->view('partials/head') ?>
<style>
    .uid-badge {
        font-family: monospace;
        font-size: 0.95rem;
        letter-spacing: 2px;
        background: #2d3748;
        color: #68d391;
        padding: 4px 10px;
        border-radius: 6px;
    }
    .card-status-active   { border-left: 4px solid #1cc88a; }
    .card-status-inactive { border-left: 4px solid #e74a3b; opacity: 0.7; }

    /* Pending card pulse animation */
    .pending-card {
        border-left: 4px solid #f6c23e;
        animation: pulse-border 1.5s infinite;
    }
    @keyframes pulse-border {
        0%, 100% { border-left-color: #f6c23e; }
        50%       { border-left-color: #e74a3b; }
    }
    .pending-uid {
        font-family: monospace;
        font-size: 1.1rem;
        font-weight: bold;
        color: #f6c23e;
        background: #2d3748;
        padding: 6px 14px;
        border-radius: 8px;
        letter-spacing: 3px;
    }

    /* Print ID Card button */
    .btn-print-card {
        background: linear-gradient(135deg, #17a2b8, #138496);
        border: none;
        color: #fff;
        transition: all 0.2s ease;
    }
    .btn-print-card:hover {
        background: linear-gradient(135deg, #138496, #0f6674);
        color: #fff;
        transform: scale(1.05);
    }
    .btn-print-card:active {
        transform: scale(0.97);
    }

    /* Download toast */
    #download-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        min-width: 280px;
        border-left: 4px solid #1cc88a;
    }
</style>

<body class="antialiased">
<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">

                <!-- Header -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-id-card"></i> <?= $title ?>
                    </h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="fas fa-plus"></i> Daftarkan Kartu Manual
                    </button>
                </div>

                <!-- Flash Messages -->
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif ?>
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif ?>

                <!-- 🔥 PENDING CARDS SECTION -->
                <div id="pending-section" class="<?= empty($pending) ? 'd-none' : '' ?>">
                    <div class="alert alert-warning d-flex align-items-center mb-3">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                        <div>
                            <strong>Ada kartu RFID baru terdeteksi!</strong>
                            <span class="ms-1">Tap kartu tadi belum terdaftar. Assign ke karyawan mana?</span>
                        </div>
                    </div>

                    <div id="pending-list" class="row mb-4">
                        <?php foreach ($pending as $p): ?>
                            <div class="col-md-4 mb-3 pending-item" data-uid="<?= $p->uid ?>">
                                <div class="card pending-card shadow">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <i class="fas fa-credit-card fa-3x text-warning mb-2"></i>
                                            <div class="pending-uid"><?= $p->uid ?></div>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i>
                                                Tap: <?= date('H:i:s', strtotime($p->scanned_at)) ?>
                                            </small>
                                        </div>
                                        <div class="mb-2">
                                            <select class="form-select select-assign" data-uid="<?= $p->uid ?>">
                                                <option value="">-- Pilih Karyawan --</option>
                                                <?php foreach ($users as $u): ?>
                                                    <option value="<?= $u->id ?>"><?= $u->nama ?> (<?= $u->nik ?>)</option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-success btn-sm flex-grow-1 btn-assign" data-uid="<?= $p->uid ?>">
                                                <i class="fas fa-check"></i> Assign
                                            </button>
                                            <a href="<?= base_url('rfid_cards/hapus_pending/' . $p->uid) ?>"
                                                class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('Hapus kartu pending ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <!-- Registered Cards Table -->
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list"></i> Daftar Kartu RFID Terdaftar
                        </h6>
                        <small class="text-muted">
                            <i class="fas fa-sync fa-spin d-none" id="polling-spinner"></i>
                            <span id="polling-status">Auto-detect aktif</span>
                        </small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>UID Kartu</th>
                                        <th>Nama Karyawan</th>
                                        <th>NIK</th>
                                        <th>Group</th>
                                        <th>Status</th>
                                        <th>Terdaftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cards-table">
                                    <?php if (empty($cards)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                                <p class="text-muted">Belum ada kartu RFID terdaftar</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($cards as $i => $card): ?>
                                            <tr class="<?= $card->is_active ? 'card-status-active' : 'card-status-inactive' ?>">
                                                <td><?= $i + 1 ?></td>
                                                <td><span class="uid-badge"><?= $card->uid ?></span></td>
                                                <td><?= $card->nama ?></td>
                                                <td><?= $card->nik ?></td>
                                                <td><span class="badge bg-secondary"><?= $card->group_karyawan ?></span></td>
                                                <td>
                                                    <?php if ($card->is_active): ?>
                                                        <span class="badge bg-success"><i class="fas fa-check"></i> Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger"><i class="fas fa-times"></i> Nonaktif</span>
                                                    <?php endif ?>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($card->created_at)) ?></td>
                                                <td>
                                                    <!-- Edit -->
                                                    <button class="btn btn-sm btn-warning btn-edit"
                                                        data-id="<?= $card->id ?>"
                                                        data-uid="<?= $card->uid ?>"
                                                        data-user="<?= $card->user_id ?>"
                                                        data-bs-toggle="modal" data-bs-target="#modalEdit"
                                                        title="Edit Kartu">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <!-- Toggle Aktif/Nonaktif -->
                                                    <a href="<?= base_url('rfid_cards/toggle/' . $card->id) ?>"
                                                        class="btn btn-sm <?= $card->is_active ? 'btn-secondary' : 'btn-success' ?>"
                                                        title="<?= $card->is_active ? 'Nonaktifkan' : 'Aktifkan' ?>"
                                                        onclick="return confirm('<?= $card->is_active ? 'Nonaktifkan' : 'Aktifkan' ?> kartu ini?')">
                                                        <i class="fas fa-<?= $card->is_active ? 'ban' : 'check' ?>"></i>
                                                    </a>

                                                    <!-- Download Barcode PNG -->
                                                    <button class="btn btn-sm btn-print-card"
                                                        title="Download Barcode PNG"
                                                        data-uid="<?= $card->uid ?>">
                                                        <i class="fas fa-barcode"></i>
                                                    </button>

                                                    <!-- Hapus -->
                                                    <a href="<?= base_url('rfid_cards/hapus/' . $card->id) ?>"
                                                        class="btn btn-sm btn-danger"
                                                        title="Hapus Kartu"
                                                        onclick="return confirm('Hapus kartu RFID ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php $this->load->view('partials/footer') ?>
    </div>
</div>

<!-- ===================== MODAL TAMBAH MANUAL ===================== -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Daftarkan Kartu Manual</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('rfid_cards/tambah') ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>UID Kartu <span class="text-danger">*</span></label>
                        <input type="text" name="uid" class="form-control uid-input"
                            placeholder="Contoh: A1B2C3D4"
                            required maxlength="20"
                            style="font-family:monospace; text-transform:uppercase; letter-spacing:2px">
                        <small class="text-muted">UID dari Serial Monitor Arduino saat kartu di-tap</small>
                    </div>
                    <div class="form-group">
                        <label>Karyawan <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Pilih Karyawan --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u->id ?>"><?= $u->nama ?> (<?= $u->nik ?>)</option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== MODAL EDIT ===================== -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Kartu RFID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" action="" method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>UID Kartu</label>
                        <input type="text" name="uid" id="edit_uid" class="form-control uid-input"
                            required style="font-family:monospace; text-transform:uppercase; letter-spacing:2px">
                    </div>
                    <div class="form-group">
                        <label>Karyawan</label>
                        <select name="user_id" id="edit_user_id" class="form-select" required>
                            <option value="">-- Pilih Karyawan --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u->id ?>"><?= $u->nama ?> (<?= $u->nik ?>)</option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== DOWNLOAD TOAST ===================== -->
<div id="download-toast" class="card shadow d-none">
    <div class="card-body py-2 px-3 d-flex align-items-center gap-3">
        <i class="fas fa-check-circle text-success fa-lg"></i>
        <div>
            <div class="fw-bold" style="font-size:0.9rem">ID Card didownload!</div>
            <div class="text-muted" id="toast-filename" style="font-size:0.78rem"></div>
        </div>
    </div>
</div>

<!-- Canvas tersembunyi untuk konversi SVG barcode → PNG -->
<!-- position:fixed off-screen, bukan display:none, supaya dimensi canvas tetap valid -->
<canvas id="barcode-canvas" style="position:fixed; top:-9999px; left:-9999px; opacity:0; pointer-events:none;"></canvas>

<?php $this->load->view('partials/js') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

<script>
$(document).ready(function () {

    // ====================================================
    // AUTO UPPERCASE UID INPUT
    // ====================================================
    $(document).on('input', '.uid-input', function () {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });

    // ====================================================
    // EDIT MODAL POPULATE
    // ====================================================
    $('.btn-edit').on('click', function () {
        $('#edit_uid').val($(this).data('uid'));
        $('#edit_user_id').val($(this).data('user'));
        $('#formEdit').attr('action', '<?= base_url('rfid_cards/edit/') ?>' + $(this).data('id'));
    });

    // ====================================================
    // ASSIGN PENDING CARD
    // ====================================================
    $(document).on('click', '.btn-assign', function () {
        const uid     = $(this).data('uid');
        const user_id = $(`.select-assign[data-uid="${uid}"]`).val();

        if (!user_id) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih karyawan dulu!',
                timer: 1500,
                showConfirmButton: false
            });
            return;
        }

        Swal.fire({
            title: 'Assign Kartu?',
            html: `UID: <code style="color:#f6c23e">${uid}</code>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Assign!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '<?= base_url('rfid_cards/assign') ?>',
                type: 'POST',
                data: { uid, user_id },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: `Kartu didaftarkan ke ${res.nama}`,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: res.message });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Gagal menghubungi server.' });
                }
            });
        });
    });

    // ====================================================
    // POLLING PENDING CARDS (setiap 3 detik)
    // ====================================================
    let lastCount = <?= count($pending) ?>;

    function pollPending() {
        $('#polling-spinner').removeClass('d-none');

        $.ajax({
            url: '<?= base_url('rfid_cards/check_pending') ?>',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                $('#polling-spinner').addClass('d-none');

                if (res.count > 0) {
                    $('#pending-section').removeClass('d-none');

                    // Tambah kartu baru yang belum ada di DOM
                    res.data.forEach(function(p) {
                        if (!document.querySelector(`.pending-item[data-uid="${p.uid}"]`)) {

                            // Notif toast kalau ada kartu baru
                            if (lastCount < res.count) {
                                Swal.fire({
                                    icon: 'info',
                                    title: '🎴 Kartu Baru Terdeteksi!',
                                    html: `UID: <strong style="font-family:monospace;color:#f6c23e">${p.uid}</strong><br>Assign ke karyawan mana?`,
                                    timer: 4000,
                                    showConfirmButton: false,
                                    toast: true,
                                    position: 'top-end'
                                });
                            }

                            const userOptions = `<?php foreach($users as $u): ?><option value="<?= $u->id ?>"><?= htmlspecialchars($u->nama) ?> (<?= $u->nik ?>)</option><?php endforeach ?>`;
                            const tapTime = p.scanned_at ? p.scanned_at.substring(11, 19) : 'Baru saja';

                            const html = `
                                <div class="col-md-4 mb-3 pending-item" data-uid="${p.uid}">
                                    <div class="card pending-card shadow">
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <i class="fas fa-credit-card fa-3x text-warning mb-2"></i>
                                                <div class="pending-uid">${p.uid}</div>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> Tap: ${tapTime}
                                                </small>
                                            </div>
                                            <div class="mb-2">
                                                <select class="form-select select-assign" data-uid="${p.uid}">
                                                    <option value="">-- Pilih Karyawan --</option>
                                                    ${userOptions}
                                                </select>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-success btn-sm flex-grow-1 btn-assign" data-uid="${p.uid}">
                                                    <i class="fas fa-check"></i> Assign
                                                </button>
                                                <a href="<?= base_url('rfid_cards/hapus_pending/') ?>${p.uid}"
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('Hapus kartu pending ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                            $('#pending-list').append(html);
                        }
                    });

                    // Hapus dari DOM kartu yang sudah tidak ada di DB
                    const activeUIDs = res.data.map(p => p.uid);
                    document.querySelectorAll('.pending-item').forEach(function(el) {
                        if (!activeUIDs.includes(el.dataset.uid)) el.remove();
                    });

                    lastCount = res.count;

                } else {
                    const domItems = document.querySelectorAll('.pending-item').length;
                    if (domItems === 0) $('#pending-section').addClass('d-none');
                    lastCount = 0;
                }
            },
            error: function () {
                $('#polling-spinner').addClass('d-none');
            }
        });
    }

    setInterval(pollPending, 3000);

    // ====================================================
    // AUTO HIDE FLASH ALERTS
    // ====================================================
    setTimeout(function() {
        $('.alert-success, .alert-danger').fadeOut('slow');
    }, 5000);

    // ====================================================
    // 🔥 DOWNLOAD BARCODE PNG (bersih, siap Canva/Photoshop)
    // ====================================================

    /**
     * Generate barcode CODE128 dari UID → download langsung sebagai PNG.
     * Background putih, garis hitam, ukuran besar supaya resolusi cetak oke.
     * Nama file: Barcode_<UID>.png
     */
    function downloadBarcode(uid) {
        // Pakai SVG element sementara — lebih reliable cross-browser vs canvas hidden
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        document.body.appendChild(svg); // harus ada di DOM dulu biar JsBarcode bisa render

        try {
            JsBarcode(svg, uid, {
                format      : 'CODE128',
                width       : 3,
                height      : 120,
                displayValue: true,
                background  : 'transparent',
                lineColor   : '#000000',
                margin      : 10
            });
        } catch (e) {
            document.body.removeChild(svg);
            Swal.fire({
                icon : 'error',
                title: 'Gagal generate barcode',
                text : 'UID tidak valid untuk format CODE128: ' + uid
            });
            return;
        }

        // Konversi SVG → Canvas → PNG blob → download
        const svgData   = new XMLSerializer().serializeToString(svg);
        const svgBlob   = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
        const svgUrl    = URL.createObjectURL(svgBlob);

        const img = new Image();
        img.onload = function () {
            const canvas  = document.getElementById('barcode-canvas');
            canvas.width  = img.width;
            canvas.height = img.height;

            const ctx = canvas.getContext('2d');
            // Bersihkan canvas (transparan)
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0);

            // Download
            const filename = 'Barcode_' + uid + '.png';
            const link     = document.createElement('a');
            link.download  = filename;
            link.href      = canvas.toDataURL('image/png');
            link.click();

            // Cleanup
            URL.revokeObjectURL(svgUrl);
            document.body.removeChild(svg);

            // Toast notif
            $('#toast-filename').text(filename);
            $('#download-toast').removeClass('d-none');
            setTimeout(function () {
                $('#download-toast').addClass('d-none');
            }, 3500);
        };

        img.onerror = function () {
            URL.revokeObjectURL(svgUrl);
            document.body.removeChild(svg);
            Swal.fire({ icon: 'error', title: 'Gagal render barcode ke PNG.' });
        };

        img.src = svgUrl;
    }

    // Handler tombol Download Barcode
    $(document).on('click', '.btn-print-card', function () {
        const uid = $(this).data('uid');
        if (!uid) {
            Swal.fire({ icon: 'error', title: 'UID tidak ditemukan!' });
            return;
        }
        downloadBarcode(uid);
    });

});
</script>
</body>
</html>