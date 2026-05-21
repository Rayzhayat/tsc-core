<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ════════════════════════════════════════════════════════════
// _render_manage_rows — tidak lagi butuh _get_depth().
// Depth sudah di-pre-compute di controller → $depth_map.
// ════════════════════════════════════════════════════════════

function _render_manage_rows($nodes_flat, $parent_id, $depth)
{
    $children = array_filter($nodes_flat, function ($n) use ($parent_id) {
        if ($parent_id === null)
            return $n->parent_id === null;
        return (int) $n->parent_id === (int) $parent_id;
    });
    usort($children, fn($a, $b) => $a->urutan <=> $b->urutan ?: $a->id <=> $b->id);

    foreach ($children as $node):
        $depth_cls = 'depth-' . min($depth, 3);
        $safe_jabatan = htmlspecialchars($node->jabatan, ENT_QUOTES, 'UTF-8');
        $safe_departemen = htmlspecialchars((string) $node->departemen, ENT_QUOTES, 'UTF-8');
        $safe_nama = htmlspecialchars((string) $node->pengguna_nama, ENT_QUOTES, 'UTF-8');
        $parent_val = $node->parent_id !== null ? (int) $node->parent_id : '';
        $pengguna_val = $node->pengguna_id ? (int) $node->pengguna_id : '';
        ?>
        <div class="node-row <?= $depth_cls ?>" id="node-<?= $node->id ?>">

            <?php for ($i = 0; $i < $depth; $i++): ?>
                <span class="indent"></span>
                <span class="text-muted" style="font-size:.7rem;opacity:.4">└</span>
            <?php endfor ?>

            <div class="jabatan-label"><?= $safe_jabatan ?></div>

            <?php if ($node->departemen): ?>
                <span class="dept-tag"><?= $safe_departemen ?></span>
            <?php endif ?>

            <?php if ($node->pengguna_id): ?>
                <span class="nama-tag">
                    <i class="fas fa-user fa-xs me-1"></i><?= $safe_nama ?>
                </span>
            <?php else: ?>
                <span class="empty-tag">kosong</span>
            <?php endif ?>

            <div class="ms-auto d-flex" style="gap:4px">
                <button class="btn btn-warning btn-sm py-0 px-2 btn-ubah" data-id="<?= (int) $node->id ?>"
                    data-parent="<?= $parent_val ?>" data-departemen="<?= $safe_departemen ?>"
                    data-jabatan="<?= $safe_jabatan ?>" data-pengguna="<?= $pengguna_val ?>"
                    data-urutan="<?= (int) $node->urutan ?>" title="Ubah">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm py-0 px-2 btn-hapus" data-id="<?= (int) $node->id ?>"
                    data-jabatan="<?= $safe_jabatan ?>" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <?php
        _render_manage_rows($nodes_flat, $node->id, $depth + 1);
    endforeach;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .node-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 0.5px solid #e3e6f0;
            border-radius: 8px;
            margin-bottom: 6px;
            background: #fff;
            transition: background .15s;
        }

        .node-row:hover {
            background: #f8f9fc;
        }

        .node-row .indent {
            width: 20px;
            height: 1px;
            display: inline-block;
        }

        .node-row .jabatan-label {
            font-size: .82rem;
            font-weight: 600;
            flex: 1;
        }

        .node-row .dept-tag {
            font-size: .68rem;
            background: #e8f0fe;
            color: #1565c0;
            border-radius: 10px;
            padding: 2px 8px;
            white-space: nowrap;
        }

        .node-row .nama-tag {
            font-size: .72rem;
            color: #6c757d;
            white-space: nowrap;
        }

        .node-row .empty-tag {
            font-size: .7rem;
            color: #bbb;
            font-style: italic;
            white-space: nowrap;
        }

        .depth-0 {
            border-left: 4px solid #1a237e;
        }

        .depth-1 {
            border-left: 4px solid #1976d2;
        }

        .depth-2 {
            border-left: 4px solid #388e3c;
        }

        .depth-3 {
            border-left: 4px solid #9e9e9e;
        }

        .visibility-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            border: 1.5px solid #d1d3e2;
            background: #f8f9fc;
            color: #6c757d;
            cursor: pointer;
            transition: all .15s;
            user-select: none;
        }

        .visibility-chip input {
            display: none;
        }

        .visibility-chip.checked {
            background: #eef0fd;
            border-color: #4e73df;
            color: #4e73df;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-sitemap text-primary"></i> Kelola Struktur Organisasi
                            </h2>
                            <small class="text-muted">Tambah, ubah, dan atur tampilan bagan</small>
                        </div>
                        <a href="<?= base_url('org_chart') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-eye me-1"></i> Lihat Chart
                        </a>
                    </div>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-1"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-times-circle me-1"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <div class="row g-3">

                        <!-- ══ KOLOM KIRI: Daftar Node ══ -->
                        <div class="col-lg-7">
                            <div class="card shadow-sm">
                                <div class="card-header d-flex align-items-center justify-content-between py-2">
                                    <h6 class="mb-0 fw-bold text-primary">
                                        <i class="fas fa-list me-1"></i> Daftar Posisi
                                    </h6>
                                    <button class="btn btn-primary btn-sm" id="btnTambahPosisi">
                                        <i class="fas fa-plus me-1"></i> Tambah Posisi
                                    </button>
                                </div>
                                <div class="card-body p-3">
                                    <?php if (empty($nodes_flat)): ?>
                                        <div class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            <p class="mb-0">Belum ada data. Mulai tambah posisi!</p>
                                        </div>
                                    <?php else: ?>
                                        <?php _render_manage_rows($nodes_flat, null, 0); ?>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>

                        <!-- ══ KOLOM KANAN: Visibility + Info ══ -->
                        <div class="col-lg-5">
                            <?php if ($is_superadmin): ?>
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0 fw-bold text-info">
                                            <i class="fas fa-eye me-1"></i> Kontrol Visibilitas
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-3">
                                            Pilih role yang <strong>bisa melihat</strong> org chart.
                                            Superadmin selalu bisa melihat.
                                        </p>
                                        <form action="<?= base_url('org_chart/set_visibility') ?>" method="POST">
                                            <div class="d-flex flex-wrap mb-3" style="gap:8px">
                                                <?php
                                                $all_levels = [
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
                                                foreach ($all_levels as $lvl => $label):
                                                    $checked = in_array($lvl, $visibility);
                                                    ?>
                                                    <label class="visibility-chip <?= $checked ? 'checked' : '' ?>"
                                                        id="chip_<?= $lvl ?>">
                                                        <input type="checkbox" name="user_level[]" value="<?= $lvl ?>"
                                                            <?= $checked ? 'checked' : '' ?> onchange="toggleChip(this)">
                                                        <i class="fas fa-<?= $checked ? 'eye' : 'eye-slash' ?>"
                                                            id="icon_<?= $lvl ?>"></i>
                                                        <?= $label ?>
                                                    </label>
                                                <?php endforeach ?>
                                            </div>
                                            <button type="submit" class="btn btn-info btn-sm w-100">
                                                <i class="fas fa-save me-1"></i> Simpan Pengaturan Visibilitas
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif ?>

                            <div class="card shadow-sm border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2">
                                        <i class="fas fa-info-circle text-primary me-1"></i> Panduan
                                    </h6>
                                    <ul class="small text-muted mb-0 ps-3">
                                        <li>Klik <strong>Edit</strong> untuk mengubah posisi &amp; assign orang</li>
                                        <li>Hapus node hanya bisa kalau tidak punya anak</li>
                                        <li>Kolom <em>Parent</em> menentukan hierarki di bagan</li>
                                        <li>Biarkan <em>Nama</em> kosong jika posisi belum ada orangnya</li>
                                        <?php if (!$is_superadmin): ?>
                                            <li class="text-warning fw-bold">
                                                Anda hanya bisa edit posisi di departemen Anda sendiri
                                            </li>
                                        <?php endif ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div><!-- /row -->
                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- ══ MODAL TAMBAH NODE ══ -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= base_url('org_chart/tambah') ?>" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahLabel">
                            <i class="fas fa-plus me-1"></i> Tambah Posisi
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Parent (Di bawah siapa?)
                                <span class="text-muted fw-normal small">— kosongkan untuk root</span>
                            </label>
                            <select name="parent_id" class="form-select">
                                <option value="">— Root (level teratas) —</option>
                                <?php foreach ($nodes_flat as $n):
                                    $d = isset($depth_map[$n->id]) ? (int) $depth_map[$n->id] : 0;
                                    $prefix = str_repeat('&nbsp;&nbsp;', $d);
                                    ?>
                                    <option value="<?= $n->id ?>">
                                        <?= $prefix ?>
                                        <?= htmlspecialchars($n->jabatan) ?>
                                        <?= $n->departemen ? ' (' . htmlspecialchars($n->departemen) . ')' : '' ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Tim / Departemen
                                <span class="text-muted fw-normal small">— opsional</span>
                            </label>
                            <input type="text" name="departemen" class="form-control"
                                placeholder="e.g. Finance, Accounting &amp; Tax Tim">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Jabatan / Posisi <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="jabatan" class="form-control" placeholder="e.g. Head of Finance"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Assign ke Karyawan
                                <span class="text-muted fw-normal small">— opsional</span>
                            </label>
                            <select name="pengguna_id" class="form-select">
                                <option value="">— Belum diisi —</option>
                                <?php foreach ($pengguna as $p): ?>
                                    <option value="<?= $p->id ?>">
                                        <?= htmlspecialchars($p->nama) ?> (<?= $p->user_level ?>)
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">
                                Urutan <span class="text-muted fw-normal small">— kecil = kiri</span>
                            </label>
                            <input type="number" name="urutan" class="form-control" value="0" min="0" max="99">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ══ MODAL UBAH NODE ══ -->
    <div class="modal fade" id="modalUbah" tabindex="-1" aria-labelledby="modalUbahLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formUbah" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalUbahLabel">
                            <i class="fas fa-edit me-1"></i> Ubah Posisi
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($is_superadmin): ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Parent</label>
                                <select name="parent_id" id="ubah_parent" class="form-select">
                                    <option value="">— Root (level teratas) —</option>
                                    <?php foreach ($nodes_flat as $n):
                                        $d = isset($depth_map[$n->id]) ? (int) $depth_map[$n->id] : 0;
                                        $prefix = str_repeat('&nbsp;&nbsp;', $d);
                                        ?>
                                        <option value="<?= $n->id ?>">
                                            <?= $prefix ?>
                                            <?= htmlspecialchars($n->jabatan) ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        <?php endif ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Tim / Departemen</label>
                            <input type="text" name="departemen" id="ubah_departemen" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Jabatan <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="jabatan" id="ubah_jabatan" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Assign ke Karyawan</label>
                            <select name="pengguna_id" id="ubah_pengguna" class="form-select">
                                <option value="">— Belum diisi —</option>
                                <?php foreach ($pengguna as $p): ?>
                                    <option value="<?= $p->id ?>">
                                        <?= htmlspecialchars($p->nama) ?> (<?= $p->user_level ?>)
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Urutan</label>
                            <input type="number" name="urutan" id="ubah_urutan" class="form-control" min="0" max="99">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ── Pastikan window.bootstrap tersedia (load hanya jika belum ada) ──
        (function () {
            if (typeof bootstrap !== 'undefined') return;
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js';
            s.onload = function () { initPage(); };
            document.head.appendChild(s);
        })();

        // Jika bootstrap sudah ada, langsung init
        if (typeof bootstrap !== 'undefined') {
            document.addEventListener('DOMContentLoaded', initPage);
        }

        function initPage() {
            // ── Helper: ambil instance Bootstrap Modal ──
            function getModal(id) {
                return bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
            }

            // ── Tombol Tambah Posisi ──
            var btnTambah = document.getElementById('btnTambahPosisi');
            if (btnTambah) {
                btnTambah.addEventListener('click', function () {
                    getModal('modalTambah').show();
                });
            }

            // ── Event delegation: Ubah & Hapus ──
            document.addEventListener('click', function (e) {

                // ── Tombol Ubah ──
                var btnUbah = e.target.closest('.btn-ubah');
                if (btnUbah) {
                    var form = document.getElementById('formUbah');
                    form.action = '<?= base_url('org_chart/ubah/') ?>' + btnUbah.dataset.id;

                    var parentEl = document.getElementById('ubah_parent');
                    if (parentEl) parentEl.value = btnUbah.dataset.parent || '';

                    document.getElementById('ubah_departemen').value = btnUbah.dataset.departemen || '';
                    document.getElementById('ubah_jabatan').value = btnUbah.dataset.jabatan || '';
                    document.getElementById('ubah_pengguna').value = btnUbah.dataset.pengguna || '';
                    document.getElementById('ubah_urutan').value = btnUbah.dataset.urutan || 0;

                    getModal('modalUbah').show();
                    return;
                }

                // ── Tombol Hapus ──
                var btnHapus = e.target.closest('.btn-hapus');
                if (btnHapus) {
                    var id = btnHapus.dataset.id;
                    var jabatan = btnHapus.dataset.jabatan;

                    if (typeof Swal === 'undefined') {
                        if (confirm('Hapus posisi "' + jabatan + '"?')) {
                            window.location.href = '<?= base_url('org_chart/hapus/') ?>' + id;
                        }
                        return;
                    }

                    Swal.fire({
                        title: 'Hapus Posisi?',
                        html: 'Posisi <strong>"' + jabatan + '"</strong> akan dihapus dari struktur.<br>' +
                            '<small class="text-muted">Node yang punya anak tidak bisa dihapus.</small>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(function (r) {
                        if (r.isConfirmed) {
                            window.location.href = '<?= base_url('org_chart/hapus/') ?>' + id;
                        }
                    });
                }
            });
        }

        // ── Toggle visibility chip ──
        function toggleChip(el) {
            var chip = el.closest('.visibility-chip');
            var icon = document.getElementById('icon_' + el.value);
            if (!chip || !icon) return;
            if (el.checked) {
                chip.classList.add('checked');
                icon.className = 'fas fa-eye';
            } else {
                chip.classList.remove('checked');
                icon.className = 'fas fa-eye-slash';
            }
        }

        // ── Auto-fade alert ──
        setTimeout(function () {
            document.querySelectorAll('.alert').forEach(function (el) {
                el.classList.remove('show');
                setTimeout(function () { el.remove(); }, 500);
            });
        }, 5000);
    </script>
</body>

</html>