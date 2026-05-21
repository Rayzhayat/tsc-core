<!DOCTYPE html>
<html lang="id">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .page-hero {
            background: linear-gradient(135deg, #0f3460 0%, #533483 100%);
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .page-hero::after {
            content: '';
            position: absolute;
            right: -30px;
            top: -30px;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, .05);
            border-radius: 50%;
            pointer-events: none; /* ← tambah ini */
        }

        .page-hero h1 {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .page-hero p {
            color: rgba(255, 255, 255, .5);
            margin: 0;
            font-size: .88rem;
        }

        .bc-row {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #eee;
            padding: 14px 18px;
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            transition: box-shadow .2s, border-color .2s;
        }

        .bc-row:hover {
            box-shadow: 0 4px 18px rgba(0, 0, 0, .08);
        }

        .bc-row.inactive {
            opacity: .5;
        }

        .bc-row.pinned {
            border-left: 4px solid #f6c23e;
        }

        .bc-dot {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .bc-dot.info {
            background: #e3f2fd;
            color: #1565c0;
        }

        .bc-dot.warning {
            background: #fff8e1;
            color: #f57f17;
        }

        .bc-dot.success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .bc-dot.danger {
            background: #ffebee;
            color: #c62828;
        }

        .bc-title {
            font-weight: 700;
            font-size: .95rem;
            color: #1a1a2e;
        }

        .bc-meta {
            font-size: .73rem;
            color: #999;
            margin-top: 3px;
        }

        .bc-actions {
            margin-left: auto;
            display: flex;
            gap: 6px;
            flex-shrink: 0;
            align-items: center;
        }

        .stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f5f5f5;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: .72rem;
            color: #777;
        }

        .attachment-file-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f4ff;
            color: #1565c0;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: .78rem;
            text-decoration: none;
            margin-top: 6px;
        }

        .attachment-file-chip:hover {
            background: #dce8ff;
            color: #0d47a1;
        }

        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            background: #fafafa;
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: #0f3460;
            background: #f0f4ff;
        }

        .upload-area i {
            font-size: 2rem;
            color: #aaa;
            margin-bottom: 8px;
            display: block;
        }

        .upload-area small {
            color: #999;
            font-size: .78rem;
        }

        #uploadPreviewWrap img {
            max-height: 120px;
            border-radius: 8px;
            margin-top: 8px;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- Banner -->
                    <?php $this->load->view('broadcast/banner', ['broadcasts_banner' => $broadcasts_banner]) ?>

                    <!-- Hero -->
                    <div class="page-hero d-flex align-items-center justify-content-between">
                        <div>
                            <h1><i class="fas fa-bullhorn me-2" style="color:#f6c23e;"></i> Kelola Pengumuman</h1>
                            <p>Buat, edit, dan atur banner pengumuman yang tampil di dashboard karyawan</p>
                        </div>
                        <button class="btn btn-warning fw-semibold" id="btnTambah">
                            <i class="fas fa-plus me-1"></i> Tambah Baru
                        </button>
                    </div>

                    <!-- List -->
                    <?php if (empty($broadcasts)): ?>
                        <div class="card text-center py-5"
                            style="border-radius:14px;border:none;box-shadow:0 2px 12px rgba(0,0,0,.06);">
                            <i class="fas fa-bullhorn fa-3x text-muted mb-3 d-block opacity-25"></i>
                            <h5 class="text-muted">Belum ada pengumuman</h5>
                            <p class="text-muted small">Klik "Tambah Baru" untuk membuat pengumuman pertama</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($broadcasts as $b):
                            $icon_map = ['info' => 'fa-info-circle', 'warning' => 'fa-exclamation-triangle', 'success' => 'fa-check-circle', 'danger' => 'fa-fire'];
                            $icon = $icon_map[$b->tipe] ?? 'fa-bell';
                            $ext = $b->attachment ? strtolower(pathinfo($b->attachment, PATHINFO_EXTENSION)) : null;
                            $is_img = $ext && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $target_label = 'Semua Karyawan';
                            if ($b->target_type !== 'all' && $b->target_value) {
                                $vals = explode(',', $b->target_value);
                                $target_label = count($vals) . ' target';
                            }
                            ?>
                            <div class="bc-row <?= !$b->is_active ? 'inactive' : '' ?> <?= $b->is_pinned ? 'pinned' : '' ?>">
                                <div class="bc-dot <?= $b->tipe ?>">
                                    <i class="fas <?= $icon ?>"></i>
                                </div>
                                <div class="flex-grow-1" style="min-width:0;">
                                    <div class="bc-title">
                                        <?php if ($b->is_pinned): ?>
                                            <i class="fas fa-thumbtack text-warning me-1" title="Disematkan"></i>
                                        <?php endif ?>
                                        <?= htmlspecialchars($b->judul) ?>
                                        <?php if (!$b->is_active): ?>
                                            <span class="badge bg-secondary ms-1" style="font-size:.68rem;">Nonaktif</span>
                                        <?php endif ?>
                                    </div>
                                    <div class="bc-meta">
                                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($b->dibuat_oleh_nama) ?>
                                        &nbsp;·&nbsp; <?= date('d M Y H:i', strtotime($b->created_at)) ?>
                                        &nbsp;·&nbsp; <i class="fas fa-users me-1"></i><?= $target_label ?>
                                        <?php if ($b->start_date || $b->end_date): ?>
                                            &nbsp;·&nbsp;<i class="fas fa-calendar me-1"></i>
                                            <?= ($b->start_date ? date('d/m/Y', strtotime($b->start_date)) : '...') ?> –
                                            <?= ($b->end_date ? date('d/m/Y', strtotime($b->end_date)) : '∞') ?>
                                        <?php endif ?>
                                    </div>
                                    <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                                        <span class="stat-chip">
                                            <i class="fas fa-eye-slash"></i> <?= $b->dismiss_count ?> dismiss
                                        </span>
                                        <?php if ($b->attachment): ?>
                                            <span class="stat-chip">
                                                <i class="fas fa-paperclip"></i>
                                                <?= $is_img ? 'Gambar' : strtoupper($ext) ?>
                                            </span>
                                        <?php endif ?>
                                    </div>
                                </div>
                                <div class="bc-actions">
                                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="<?= $b->id ?>"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        class="btn btn-sm <?= $b->is_active ? 'btn-outline-warning' : 'btn-outline-success' ?> btn-toggle"
                                        data-id="<?= $b->id ?>" title="<?= $b->is_active ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                        <i class="fas <?= $b->is_active ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $b->id ?>"
                                        data-judul="<?= htmlspecialchars($b->judul) ?>" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- ══ MODAL FORM ══ -->
    <div class="modal fade" id="modalForm" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
                <div class="modal-header border-0 px-4 pt-4"
                    style="background:linear-gradient(135deg,#0f3460,#533483);">
                    <h5 class="modal-title text-white fw-bold" id="modalTitle">
                        <i class="fas fa-bullhorn me-2" style="color:#f6c23e;"></i> Tambah Pengumuman
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="editId">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Judul <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fJudul" maxlength="255"
                                placeholder="Judul pengumuman...">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tipe</label>
                            <select class="form-select" id="fTipe">
                                <option value="info">ℹ️ Informasi</option>
                                <option value="warning">⚠️ Perhatian</option>
                                <option value="success">✅ Penting</option>
                                <option value="danger">🔥 Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="fStartDate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="fEndDate">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Isi Pengumuman <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="fIsi" rows="4"
                                placeholder="Tulis isi pengumuman..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Attachment <span class="text-muted">(opsional —
                                    gambar/PDF/doc, maks 5MB)</span></label>
                            <div class="upload-area" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div>Klik atau drag file ke sini</div>
                                <small>JPG, PNG, GIF, WEBP, PDF, DOC, DOCX, XLS, XLSX</small>
                            </div>
                            <input type="file" id="fAttachment" name="attachment"
                                accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx" style="display:none;">
                            <div id="uploadPreviewWrap" style="display:none;">
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <span id="uploadFileName" class="small text-muted"></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveUpload">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div id="uploadImgPreview"></div>
                            </div>
                            <div id="existingAttachWrap" style="display:none;" class="mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted">Attachment saat ini:</span>
                                    <a href="#" id="existingAttachLink" target="_blank" class="attachment-file-chip">
                                        <i class="fas fa-paperclip"></i> <span id="existingAttachName"></span>
                                    </a>
                                    <div class="form-check ms-2 mb-0">
                                        <input class="form-check-input" type="checkbox" id="fRemoveAttachment">
                                        <label class="form-check-label small text-danger"
                                            for="fRemoveAttachment">Hapus</label>
                                    </div>
                                </div>
                                <div id="existingImgPreview"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Target Penerima</label>
                            <div class="d-flex gap-3 mb-2 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="fTargetType" id="tAll"
                                        value="all" checked>
                                    <label class="form-check-label small" for="tAll">Semua Karyawan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="fTargetType" id="tGroup"
                                        value="group">
                                    <label class="form-check-label small" for="tGroup">Per Group</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="fTargetType" id="tLevel"
                                        value="level">
                                    <label class="form-check-label small" for="tLevel">Per Level</label>
                                </div>
                            </div>
                            <div id="targetGroupBox" class="p-3 bg-light rounded" style="display:none;">
                                <div class="row g-2">
                                    <?php foreach (['Yamazaki Staff', 'Admin TSC', 'Operasional TSC', 'TSF Staff', 'Sinar Boga Staff', 'Rorotan Staff'] as $g): ?>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input target-group-cb" type="checkbox"
                                                    value="<?= $g ?>" id="grp_<?= md5($g) ?>">
                                                <label class="form-check-label small"
                                                    for="grp_<?= md5($g) ?>"><?= $g ?></label>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            </div>
                            <div id="targetLevelBox" class="p-3 bg-light rounded" style="display:none;">
                                <div class="row g-2">
                                    <?php
                                    $lvls = [
                                        'viewer' => 'Viewer / Manajemen',
                                        'head_of_departemen' => 'Head of Departemen',
                                        'operational_lead' => 'Operational Lead',
                                        'administration_lead' => 'Administration Lead',
                                        'hr_staff' => 'HR Staff',
                                        'admin_operational' => 'Admin Operational',
                                        'operational_staff' => 'Operational Staff',
                                        'finance_staff' => 'Finance Staff',
                                        'fleet_staff' => 'Fleet Staff',
                                        'admin_document' => 'Admin Document',
                                        'yamazaki' => 'Yamazaki',
                                        'tsf' => 'TSF',
                                        'sinar_boga' => 'Sinar Boga',
                                        'rorotan' => 'Rorotan',
                                    ];
                                    foreach ($lvls as $lv => $ln): ?>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input target-level-cb" type="checkbox"
                                                    value="<?= $lv ?>" id="lv_<?= $lv ?>">
                                                <label class="form-check-label small" for="lv_<?= $lv ?>"><?= $ln ?></label>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex gap-4 flex-wrap">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="fPinned" role="switch">
                                    <label class="form-check-label small" for="fPinned">
                                        <i class="fas fa-thumbtack me-1 text-warning"></i> Sematkan (tampil paling atas)
                                    </label>
                                </div>
                                <div class="form-check form-switch" id="wrapActive" style="display:none;">
                                    <input class="form-check-input" type="checkbox" id="fActive" role="switch" checked>
                                    <label class="form-check-label small" for="fActive">Aktif</label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary px-4 fw-semibold" id="btnSave">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const BASE = '<?= base_url() ?>';

        function dismissBanner(id) {
            const el = document.getElementById('banner-' + id);
            if (!el) return;
            el.classList.add('dismissing');
            setTimeout(function () {
                el.remove();
                const stack = document.getElementById('broadcastBannerStack');
                if (stack && !stack.querySelector('.broadcast-banner')) stack.style.display = 'none';
            }, 400);
            fetch(BASE + 'broadcast/dismiss/' + id, { method: 'POST' }).catch(function () { });
        }

        document.addEventListener('DOMContentLoaded', function () {

            console.group('=== BROADCAST DEBUG ===');
            console.log('1. Bootstrap tersedia:', typeof bootstrap !== 'undefined' ? 'YA ✅' : 'TIDAK ❌');
            console.log('2. Bootstrap.Modal tersedia:', (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') ? 'YA ✅' : 'TIDAK ❌');
            console.log('3. modalForm:', document.getElementById('modalForm') ? 'ADA ✅' : 'TIDAK ADA ❌');
            console.log('4. btnTambah:', document.getElementById('btnTambah') ? 'ADA ✅' : 'TIDAK ADA ❌');
            console.log('5. uploadArea:', document.getElementById('uploadArea') ? 'ADA ✅' : 'TIDAK ADA ❌');
            console.log('6. btnSave:', document.getElementById('btnSave') ? 'ADA ✅' : 'TIDAK ADA ❌');
            console.log('7. BASE URL:', BASE);

            // Cek apakah ada script error sebelumnya
            window.onerror = function (msg, src, line, col, err) {
                console.error('❌ GLOBAL JS ERROR:', msg, '| File:', src, '| Line:', line, '| Error:', err);
            };

            var modalEl = document.getElementById('modalForm');
            var btnEl = document.getElementById('btnTambah');

            if (!modalEl) { console.error('STOP: modalForm tidak ditemukan!'); console.groupEnd(); return; }
            if (!btnEl) { console.error('STOP: btnTambah tidak ditemukan!'); console.groupEnd(); return; }
            if (typeof bootstrap === 'undefined') { console.error('STOP: Bootstrap belum load!'); console.groupEnd(); return; }
            if (typeof bootstrap.Modal === 'undefined') { console.error('STOP: bootstrap.Modal tidak ada — versi Bootstrap salah?'); console.groupEnd(); return; }

            console.log('✅ Semua elemen OK, pasang event listeners...');
            console.groupEnd();

            // ── Fix aria-hidden ──
            modalEl.addEventListener('hide.bs.modal', function () {
                if (document.activeElement) document.activeElement.blur();
            });

            // ── Target radio toggle ──
            document.querySelectorAll('input[name="fTargetType"]').forEach(r => {
                r.addEventListener('change', function () {
                    document.getElementById('targetGroupBox').style.display = this.value === 'group' ? '' : 'none';
                    document.getElementById('targetLevelBox').style.display = this.value === 'level' ? '' : 'none';
                });
            });

            // ── Upload area ──
            const uploadArea = document.getElementById('uploadArea');
            const fAttachment = document.getElementById('fAttachment');

            uploadArea.addEventListener('click', () => fAttachment.click());
            uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('dragover'); });
            uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
            uploadArea.addEventListener('drop', e => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    fAttachment.files = e.dataTransfer.files;
                    handleFileSelect(e.dataTransfer.files[0]);
                }
            });
            fAttachment.addEventListener('change', function () {
                if (this.files.length) handleFileSelect(this.files[0]);
            });

            function handleFileSelect(file) {
                document.getElementById('uploadFileName').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                document.getElementById('uploadPreviewWrap').style.display = '';
                uploadArea.style.display = 'none';
                const imgWrap = document.getElementById('uploadImgPreview');
                imgWrap.innerHTML = '';
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.style.maxHeight = '120px';
                    img.style.borderRadius = '8px';
                    img.style.marginTop = '8px';
                    imgWrap.appendChild(img);
                }
            }

            document.getElementById('btnRemoveUpload').addEventListener('click', function () {
                fAttachment.value = '';
                document.getElementById('uploadPreviewWrap').style.display = 'none';
                document.getElementById('uploadImgPreview').innerHTML = '';
                uploadArea.style.display = '';
            });

            // ── Reset form ──
            function resetForm() {
                console.log('🔄 resetForm() dipanggil');
                document.getElementById('editId').value = '';
                document.getElementById('fJudul').value = '';
                document.getElementById('fIsi').value = '';
                document.getElementById('fTipe').value = 'info';
                document.getElementById('fStartDate').value = '';
                document.getElementById('fEndDate').value = '';
                document.getElementById('fPinned').checked = false;
                document.getElementById('fActive').checked = true;
                document.getElementById('tAll').checked = true;
                document.getElementById('targetGroupBox').style.display = 'none';
                document.getElementById('targetLevelBox').style.display = 'none';
                document.querySelectorAll('.target-group-cb,.target-level-cb').forEach(c => c.checked = false);
                document.getElementById('wrapActive').style.display = 'none';
                fAttachment.value = '';
                document.getElementById('uploadPreviewWrap').style.display = 'none';
                document.getElementById('uploadImgPreview').innerHTML = '';
                uploadArea.style.display = '';
                document.getElementById('existingAttachWrap').style.display = 'none';
                document.getElementById('fRemoveAttachment').checked = false;
                document.getElementById('modalTitle').innerHTML =
                    '<i class="fas fa-bullhorn me-2" style="color:#f6c23e;"></i> Tambah Pengumuman';
            }

            // ── Tambah Baru ──
            btnEl.addEventListener('click', function () {
                console.group('🟢 btnTambah DIKLIK');
                try {
                    resetForm();
                    console.log('resetForm() selesai ✅');
                    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                    console.log('modalInstance:', modalInstance);
                    modalInstance.show();
                    console.log('modal.show() dipanggil ✅');
                } catch (e) {
                    console.error('❌ ERROR saat buka modal:', e);
                }
                console.groupEnd();
            });

            // ── Edit ──
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function () {
                    console.group('✏️ btn-edit DIKLIK, id:', this.dataset.id);
                    const id = this.dataset.id;
                    fetch(BASE + 'broadcast/get_json/' + id)
                        .then(r => {
                            console.log('fetch get_json status:', r.status, r.statusText);
                            if (!r.ok) throw new Error('HTTP ' + r.status);
                            return r.json();
                        })
                        .then(d => {
                            console.log('Response get_json:', d);
                            if (!d.success) { console.warn('success=false:', d); return; }
                            const b = d.data;
                            resetForm();
                            document.getElementById('editId').value = b.id;
                            document.getElementById('fJudul').value = b.judul;
                            document.getElementById('fIsi').value = b.isi;
                            document.getElementById('fTipe').value = b.tipe;
                            document.getElementById('fStartDate').value = b.start_date || '';
                            document.getElementById('fEndDate').value = b.end_date || '';
                            document.getElementById('fPinned').checked = b.is_pinned == 1;
                            document.getElementById('fActive').checked = b.is_active == 1;
                            document.getElementById('wrapActive').style.display = '';

                            if (b.attachment) {
                                document.getElementById('existingAttachWrap').style.display = '';
                                document.getElementById('existingAttachLink').href = BASE + 'uploads/broadcast/' + b.attachment;
                                document.getElementById('existingAttachName').textContent = b.attachment;
                                const imgPrev = document.getElementById('existingImgPreview');
                                imgPrev.innerHTML = '';
                                const ext = b.attachment.split('.').pop().toLowerCase();
                                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                                    imgPrev.innerHTML = `<img src="${BASE}uploads/broadcast/${b.attachment}" style="max-height:100px;border-radius:8px;margin-top:6px;">`;
                                }
                            }

                            if (b.target_type === 'group') {
                                document.getElementById('tGroup').checked = true;
                                document.getElementById('targetGroupBox').style.display = '';
                                (b.target_value || '').split(',').forEach(v => {
                                    const cb = document.querySelector(`.target-group-cb[value="${v.trim()}"]`);
                                    if (cb) cb.checked = true;
                                });
                            } else if (b.target_type === 'level') {
                                document.getElementById('tLevel').checked = true;
                                document.getElementById('targetLevelBox').style.display = '';
                                (b.target_value || '').split(',').forEach(v => {
                                    const cb = document.querySelector(`.target-level-cb[value="${v.trim()}"]`);
                                    if (cb) cb.checked = true;
                                });
                            }

                            document.getElementById('modalTitle').innerHTML =
                                '<i class="fas fa-edit me-2" style="color:#f6c23e;"></i> Edit Pengumuman';
                            bootstrap.Modal.getOrCreateInstance(modalEl).show();
                            console.log('modal edit dibuka ✅');
                        })
                        .catch(e => console.error('❌ ERROR fetch get_json:', e));
                    console.groupEnd();
                });
            });

            // ── Save ──
            document.getElementById('btnSave').addEventListener('click', function () {
                console.group('💾 btnSave DIKLIK');
                const id = document.getElementById('editId').value;
                console.log('editId:', id || '(tambah baru)');

                const fd = new FormData();
                fd.append('judul', document.getElementById('fJudul').value.trim());
                fd.append('isi', document.getElementById('fIsi').value.trim());
                fd.append('tipe', document.getElementById('fTipe').value);
                fd.append('start_date', document.getElementById('fStartDate').value);
                fd.append('end_date', document.getElementById('fEndDate').value);
                fd.append('is_pinned', document.getElementById('fPinned').checked ? '1' : '');
                fd.append('is_active', document.getElementById('fActive').checked ? '1' : '');
                fd.append('remove_attachment', document.getElementById('fRemoveAttachment').checked ? '1' : '');

                const targetType = document.querySelector('input[name="fTargetType"]:checked').value;
                fd.append('target_type', targetType);
                document.querySelectorAll('.target-group-cb:checked').forEach(c => fd.append('target_groups[]', c.value));
                document.querySelectorAll('.target-level-cb:checked').forEach(c => fd.append('target_levels[]', c.value));
                if (fAttachment.files.length) fd.append('attachment', fAttachment.files[0]);

                console.log('FormData judul:', fd.get('judul'));
                console.log('FormData isi:', fd.get('isi'));
                console.log('FormData tipe:', fd.get('tipe'));
                console.log('FormData target_type:', fd.get('target_type'));

                if (!fd.get('judul') || !fd.get('isi')) {
                    console.warn('⚠️ Validasi gagal: judul/isi kosong');
                    Swal.fire({ icon: 'warning', title: 'Oops', text: 'Judul dan isi wajib diisi!' });
                    console.groupEnd();
                    return;
                }

                const url = id ? BASE + 'broadcast/update/' + id : BASE + 'broadcast/store';
                console.log('Fetch URL:', url);

                const btn = document.getElementById('btnSave');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

                fetch(url, { method: 'POST', body: fd })
                    .then(r => {
                        console.log('fetch store/update status:', r.status, r.statusText);
                        if (!r.ok) throw new Error('HTTP ' + r.status + ' ' + r.statusText);
                        return r.text(); // pakai .text() dulu biar bisa lihat raw response kalau JSON error
                    })
                    .then(raw => {
                        console.log('Raw response:', raw);
                        let d;
                        try {
                            d = JSON.parse(raw);
                        } catch (e) {
                            console.error('❌ Response bukan JSON valid! Raw:', raw);
                            Swal.fire({ icon: 'error', title: 'Server Error', text: 'Response bukan JSON. Cek console untuk detail.' });
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan';
                            return;
                        }
                        console.log('Parsed response:', d);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan';
                        if (d.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: d.message, timer: 1500, showConfirmButton: false })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: d.message });
                        }
                    })
                    .catch(e => {
                        console.error('❌ ERROR fetch store/update:', e);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan';
                        Swal.fire({ icon: 'error', title: 'Network Error', text: e.message });
                    });

                console.groupEnd();
            });

            // ── Toggle ──
            document.querySelectorAll('.btn-toggle').forEach(btn => {
                btn.addEventListener('click', function () {
                    console.log('🔄 btn-toggle diklik, id:', this.dataset.id);
                    fetch(BASE + 'broadcast/toggle/' + this.dataset.id, { method: 'POST' })
                        .then(r => r.json())
                        .then(d => { if (d.success) location.reload(); });
                });
            });

            // ── Delete ──
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function () {
                    console.log('🗑️ btn-delete diklik, id:', this.dataset.id);
                    Swal.fire({
                        title: 'Hapus Pengumuman?',
                        html: `<strong>${this.dataset.judul}</strong><br><small class="text-muted">Aksi ini tidak bisa dibatalkan.</small>`,
                        icon: 'warning', showCancelButton: true,
                        confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', reverseButtons: true,
                    }).then(r => {
                        if (r.isConfirmed) {
                            fetch(BASE + 'broadcast/delete/' + this.dataset.id, { method: 'POST' })
                                .then(res => res.json())
                                .then(d => {
                                    if (d.success) {
                                        Swal.fire({ icon: 'success', title: 'Dihapus!', timer: 1200, showConfirmButton: false })
                                            .then(() => location.reload());
                                    }
                                });
                        }
                    });
                });
            });

            console.log('✅ Semua event listener terpasang');

        }); // end DOMContentLoaded
    </script>
</body>

</html>