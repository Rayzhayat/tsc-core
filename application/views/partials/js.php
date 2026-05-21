<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Tabler JS -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Notification JS -->
<script src="<?= base_url('assets/js/notification.js') ?>"></script>

<script>
    const BASE_URL = '<?= base_url() ?>';

    $(document).ready(function () {

        // ─── LOGOUT ───
        $('#logoutBtn').on('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Logout',
                html: '<p>Apakah Anda yakin ingin keluar?</p>',
                icon: 'question', showCancelButton: true,
                confirmButtonColor: '#1e3a5f', cancelButtonColor: '#858796',
                confirmButtonText: '<i class="fas fa-sign-out-alt"></i> Ya, Logout',
                cancelButtonText: 'Batal', reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('logoutOverlay').classList.add('show');
                    setTimeout(() => { window.location.href = BASE_URL + 'login/logout'; }, 3000);
                }
            });
        });

        $('#logoutForgetBtn').on('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Lupakan Perangkat?',
                html: '<p class="text-danger">Menghapus semua data login dari perangkat ini.</p>',
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#e74a3b', cancelButtonColor: '#858796',
                confirmButtonText: '<i class="fas fa-user-slash"></i> Ya, Hapus',
                cancelButtonText: 'Batal', reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('logoutOverlay').classList.add('show');
                    setTimeout(() => { window.location.href = BASE_URL + 'login/logout_forget'; }, 3000);
                }
            });
        });

        // ─── UBAH FOTO PROFIL ───
        $('#form-change-profile').on('submit', function (e) {
            e.preventDefault();
            const selectedPhoto = $('input[name="foto_profil"]:checked').val();
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({
                url: BASE_URL + 'pengguna/ubah_foto_profil',
                type: 'POST',
                data: { foto_profil: selectedPhoto },
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('changeProfileModal')).hide();
                        Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 2000, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: res.message });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan pada server' });
                }
            });
        });

    });
</script>