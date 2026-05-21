<?php
$foto_profil = $this->session->userdata('login')['foto_profil'] ?? 'default-1.png';
$nama        = $this->session->userdata('login')['nama'] ?? 'User';
$level       = $this->session->userdata('login')['user_level'] ?? '';
$level_text  = [
    'superadmin'        => 'Superadmin',
    'admin_operational' => 'Admin Operational',
    'operational_staff' => 'Operational Staff',
    'finance_staff'     => 'Finance Staff',
    'fleet_staff'       => 'Fleet Staff',
    'viewer'            => 'Viewer / Manajemen',
    'admin_document'    => 'Admin Document',
];
?>

<div class="header header-sticky mb-4 p-0" id="header">
    <div class="container-fluid px-4">
        <button class="header-toggler px-md-0 me-md-3" type="button" id="sidebarToggler">
            <i class="fas fa-bars"></i>
        </button>

        <ul class="header-nav ms-auto">

            <!-- NOTIFICATIONS -->
            <li class="nav-item dropdown">
                <a class="nav-link position-relative" href="#" role="button" data-coreui-toggle="dropdown">
                    <i class="fas fa-bell fa-fw fs-5"></i>
                    <span class="badge bg-danger position-absolute top-0 start-75 translate-middle badge-counter"
                          id="notifCount" style="display:none; font-size:0.6rem;">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end py-0" style="min-width:380px; max-height:500px; overflow-y:auto;">
                    <div class="dropdown-header bg-primary text-white py-2 px-3">
                        <i class="fas fa-bell me-2"></i> Notifikasi
                    </div>
                    <div id="notificationList">
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-spinner fa-spin me-2"></i> Loading...
                        </div>
                    </div>
                    <div class="dropdown-divider m-0"></div>
                    <a class="dropdown-item text-center small text-muted py-2" href="#" id="markAllRead">
                        <i class="fas fa-check me-1"></i> Tandai Semua Dibaca
                    </a>
                </div>
            </li>

            <!-- USER PROFILE -->
            <li class="nav-item dropdown">
                <a class="nav-link d-flex align-items-center gap-2 py-1" href="#" role="button" data-coreui-toggle="dropdown">
                    <img src="<?= base_url('uploads/profil/' . $foto_profil) ?>"
                         id="topbar-profile-img"
                         alt="<?= $nama ?>"
                         style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #e9ecef;">
                    <span class="d-none d-md-inline text-body fw-semibold small"><?= $nama ?></span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end py-0" style="min-width:220px;">
                    <!-- Profile Info -->
                    <div class="text-center p-3 bg-light border-bottom">
                        <img src="<?= base_url('uploads/profil/' . $foto_profil) ?>"
                             class="rounded-circle mb-2"
                             style="width:55px;height:55px;object-fit:cover;border:3px solid #dee2e6;">
                        <div class="fw-bold small"><?= $nama ?></div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            <?= $level_text[$level] ?? $level ?>
                        </div>
                    </div>

                    <a class="dropdown-item py-2" href="#" data-coreui-toggle="modal" data-coreui-target="#changeProfileModal">
                        <i class="fas fa-user-circle me-2 text-muted"></i> Ubah Foto Profil
                    </a>
                    <div class="dropdown-divider m-0"></div>
                    <a class="dropdown-item py-2" href="#" id="logoutBtn">
                        <i class="fas fa-sign-out-alt me-2 text-muted"></i> Logout
                    </a>
                    <a class="dropdown-item py-2 text-danger" href="#" id="logoutForgetBtn">
                        <i class="fas fa-user-slash me-2"></i> Logout & Lupakan Perangkat
                    </a>
                </div>
            </li>

        </ul>
    </div>
</div>

<!-- ── LOGOUT OVERLAY ── -->
<div class="logout-overlay" id="logoutOverlay">
    <div class="logout-content">
        <div class="logout-icon"><i class="fas fa-sign-out-alt"></i></div>
        <div class="logout-spinner">
            <div class="spinner-border" role="status"></div>
        </div>
        <div class="logout-text" id="logoutText">Mengakhiri Sesi...</div>
        <div class="logout-subtext" id="logoutSubtext">Terima kasih telah menggunakan sistem</div>
        <div class="logout-dots"><span></span><span></span><span></span></div>
    </div>
</div>

<!-- ── MODAL UBAH FOTO PROFIL ── -->
<div class="modal fade" id="changeProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i> Ubah Foto Profil</h5>
                <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal"></button>
            </div>
            <form id="form-change-profile">
                <div class="modal-body">
                    <p class="text-center text-muted mb-3">Pilih foto profil Anda:</p>
                    <div class="profile-selector-modal">
                        <?php foreach (['default-1.png','default-2.png','default-3.png','default-4.png'] as $i => $foto): ?>
                        <label class="profile-option-modal">
                            <input type="radio" name="foto_profil" value="<?= $foto ?>"
                                <?= $foto_profil == $foto ? 'checked' : '' ?>>
                            <img src="<?= base_url('uploads/profil/' . $foto) ?>" alt="Avatar <?= $i+1 ?>">
                            <span class="check-icon-modal"><i class="fas fa-check"></i></span>
                        </label>
                        <?php endforeach ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* LOGOUT OVERLAY */
.logout-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: linear-gradient(135deg, rgba(30,58,95,0.96) 0%, rgba(78,154,241,0.96) 100%);
    backdrop-filter: blur(10px);
    display: none; justify-content: center; align-items: center;
    z-index: 99999; opacity: 0; transition: opacity 0.4s ease;
}
.logout-overlay.show { display: flex; opacity: 1; }
.logout-content { text-align: center; color: white; animation: fadeInUp 0.6s ease; }
@keyframes fadeInUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
.logout-icon { font-size: 80px; margin-bottom: 20px; animation: iconPulse 2s ease-in-out infinite; }
@keyframes iconPulse { 0%,100% { transform:scale(1); } 50% { transform:scale(1.1); opacity:0.8; } }
.logout-spinner { margin: 20px auto; }
.logout-spinner .spinner-border { width:60px;height:60px;border-width:4px;border-color:rgba(255,255,255,0.3);border-top-color:white; }
.logout-text { font-size:24px;font-weight:600;margin-top:20px; }
.logout-subtext { font-size:16px;color:rgba(255,255,255,0.8);margin-top:8px; }
.logout-dots { display:inline-flex;gap:10px;margin-top:20px; }
.logout-dots span { width:12px;height:12px;background:white;border-radius:50%;animation:logoutBounce 1.4s infinite ease-in-out both; }
.logout-dots span:nth-child(1) { animation-delay:-0.32s; }
.logout-dots span:nth-child(2) { animation-delay:-0.16s; }
@keyframes logoutBounce { 0%,80%,100% { transform:scale(0);opacity:0.5; } 40% { transform:scale(1);opacity:1; } }

/* PROFILE SELECTOR */
.profile-selector-modal { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;padding:8px; }
.profile-option-modal { position:relative;cursor:pointer;transition:all 0.3s ease; }
.profile-option-modal input[type="radio"] { position:absolute;opacity:0; }
.profile-option-modal img { width:100%;height:80px;object-fit:cover;border-radius:50%;border:3px solid #dee2e6;transition:all 0.3s ease; }
.profile-option-modal input[type="radio"]:checked + img { border-color:#4e9af1;box-shadow:0 0 0 3px rgba(78,154,241,0.2);transform:scale(1.05); }
.profile-option-modal .check-icon-modal { position:absolute;top:-4px;right:4px;background:#4e9af1;color:white;border-radius:50%;width:24px;height:24px;display:none;align-items:center;justify-content:center;font-size:12px; }
.profile-option-modal input[type="radio"]:checked ~ .check-icon-modal { display:flex; }

/* Dropdown hover */
.dropdown-item.text-danger:hover { background:#fff5f5; }
</style>

<script>
$(document).ready(function () {

    // LOGOUT BIASA
    $('#logoutBtn').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Logout',
            html: '<p>Apakah Anda yakin ingin keluar?</p><small class="text-muted">Data login tersimpan untuk kemudahan akses berikutnya</small>',
            icon: 'question', showCancelButton: true,
            confirmButtonColor: '#1e3a5f', cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-sign-out-alt"></i> Ya, Logout',
            cancelButtonText: '<i class="fas fa-times"></i> Batal', reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) {
                const o = document.getElementById('logoutOverlay');
                const t = document.getElementById('logoutText');
                const s = document.getElementById('logoutSubtext');
                o.classList.add('show');
                setTimeout(() => { t.textContent = 'Menyimpan Data...'; s.textContent = 'Menyimpan aktivitas terakhir'; }, 1000);
                setTimeout(() => { t.textContent = 'Membersihkan Sesi...'; s.textContent = 'Auto-fill tetap aktif'; }, 2000);
                setTimeout(() => { t.textContent = 'Sampai Jumpa!'; }, 3000);
                setTimeout(() => { window.location.href = '<?= base_url("login/logout") ?>'; }, 3500);
            }
        });
    });

    // LOGOUT + FORGET
    $('#logoutForgetBtn').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Lupakan Perangkat Ini?',
            html: '<p><strong>⚠️ PERHATIAN:</strong> Menghapus semua data login dari perangkat ini.</p><p class="text-danger small">Gunakan ini jika logout dari komputer publik.</p>',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#e74a3b', cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-user-slash"></i> Ya, Hapus Semua',
            cancelButtonText: '<i class="fas fa-times"></i> Batal', reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) {
                const o = document.getElementById('logoutOverlay');
                const t = document.getElementById('logoutText');
                const s = document.getElementById('logoutSubtext');
                o.classList.add('show');
                setTimeout(() => { t.textContent = 'Menghapus Data Login...'; s.textContent = 'Membersihkan cookies dan session'; }, 1000);
                setTimeout(() => { t.textContent = 'Melupakan Perangkat...'; }, 2000);
                setTimeout(() => { t.textContent = 'Perangkat Dilupakan!'; }, 3000);
                setTimeout(() => { window.location.href = '<?= base_url("login/logout_forget") ?>'; }, 3500);
            }
        });
    });

    // UBAH FOTO PROFIL
    $('#form-change-profile').on('submit', function (e) {
        e.preventDefault();
        const selectedPhoto = $('input[name="foto_profil"]:checked').val();
        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.ajax({
            url: '<?= base_url("pengguna/ubah_foto_profil") ?>',
            type: 'POST',
            data: { foto_profil: selectedPhoto },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    const modal = coreui.Modal.getInstance(document.getElementById('changeProfileModal'));
                    if (modal) modal.hide();
                    const newUrl = '<?= base_url("uploads/profil/") ?>' + selectedPhoto;
                    $('#topbar-profile-img').attr('src', newUrl);
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Foto profil berhasil diubah', timer: 2000, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: res.message || 'Gagal mengubah foto profil' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan pada server' });
            }
        });
    });
});
</script>