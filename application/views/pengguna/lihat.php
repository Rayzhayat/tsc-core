<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* ── Level badges ── */
        .badge-superadmin {
            background-color: #dc3545 !important;
            color: #fff;
        }

        .badge-admin_operational {
            background-color: #f6c23e !important;
            color: #212529;
        }

        .badge-operational_staff {
            background-color: #4e73df !important;
            color: #fff;
        }

        .badge-finance_staff {
            background-color: #1cc88a !important;
            color: #fff;
        }

        .badge-fleet_staff {
            background-color: #36b9cc !important;
            color: #fff;
        }

        .badge-viewer {
            background-color: #858796 !important;
            color: #fff;
        }

        .badge-admin_document {
            background-color: #343a40 !important;
            color: #fff;
        }

        .badge-dispatcher {
            background-color: #6f42c1 !important;
            color: #fff;
        }

        .badge-admin_staff {
            background-color: #fd7e14 !important;
            color: #fff;
        }

        .badge-checker {
            background-color: #20c997 !important;
            color: #fff;
        }

        .badge-distributor {
            background-color: #0dcaf0 !important;
            color: #212529;
        }

        .badge-rorotan {
            background-color: #6c757d !important;
            color: #fff;
        }

        /* ── Group badges ── */
        .gbadge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 600;
            border: 1px solid;
        }

        .gbadge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        .gbadge-yamazaki {
            background: #fff0f6;
            color: #c2185b;
            border-color: #f48fb1;
        }

        .gbadge-admin-tsc {
            background: #e8f4fd;
            color: #1565c0;
            border-color: #90caf9;
        }

        .gbadge-ops-tsc {
            background: #e8f5e9;
            color: #2e7d32;
            border-color: #a5d6a7;
        }

        .gbadge-tsf {
            background: #fff8e1;
            color: #f57f17;
            border-color: #ffe082;
        }

        .gbadge-sinar-boga {
            background: #f3e5f5;
            color: #6a1b9a;
            border-color: #ce93d8;
        }

        .gbadge-rorotan {
            background: #fbe9e7;
            color: #bf360c;
            border-color: #ffab91;
        }

        /* ── Status badges ── */
        .badge-tetap {
            background-color: #1cc88a !important;
            color: #fff;
        }

        .badge-kontrak {
            background-color: #f6c23e !important;
            color: #212529;
        }

        .badge-magang {
            background-color: #36b9cc !important;
            color: #fff;
        }

        /* Table tweaks */
        #dataTable th,
        #dataTable td {
            vertical-align: middle;
            font-size: 0.82rem;
        }

        #dataTable thead th {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #4e73df;
        }

        .avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
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
                            <h2 class="mb-0"><?= $title ?></h2>
                            <small class="text-muted">Kelola data karyawan &amp; hak akses sistem</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pengguna/performa') ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-chart-line"></i> Dashboard Performa
                            </a>
                            <a href="<?= base_url('pengguna/tambah') ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Tambah Karyawan
                            </a>
                        </div>
                    </div>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-1"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-1"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <?php if (!$can_delete): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Anda login sebagai Admin Operational.</strong> Anda dapat menambah dan mengubah
                            karyawan, tetapi tidak dapat menghapus.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <div class="card shadow-sm">
                        <div class="card-header py-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-auto me-md-auto">
                                    <h6 class="mb-0 fw-bold text-primary">
                                        <i class="fas fa-users me-1"></i> Daftar Karyawan
                                        <span class="badge bg-info ms-2 fw-normal" id="badgeCounter">0 Karyawan</span>
                                    </h6>
                                </div>
                                <!-- Search -->
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" id="searchInput" class="form-control"
                                            placeholder="Cari nama, NIK..." autocomplete="off">
                                    </div>
                                </div>
                                <!-- Filter level -->
                                <!-- Filter level -->
                                <div class="col-6 col-sm-3 col-md-2">
                                    <select id="filterLevel" class="form-select form-select-sm">
                                        <option value="">Semua Level</option>
                                        <optgroup label="── Management ──">
                                            <option value="superadmin">Superadmin</option>
                                            <option value="viewer">Viewer / Manajemen</option>
                                            <option value="head_of_departemen">Head of Departemen</option>
                                            <option value="operational_lead">Operational Lead</option>
                                            <option value="administration_lead">Administration Lead</option>
                                            <option value="hr_staff">HR Staff</option>
                                        </optgroup>
                                        <optgroup label="── Staff ──">
                                            <option value="admin_operational">Admin Operational</option>
                                            <option value="operational_staff">Operational Staff</option>
                                            <option value="finance_staff">Finance Staff</option>
                                            <option value="fleet_staff">Fleet Staff</option>
                                            <option value="admin_document">Admin Document</option>
                                        </optgroup>
                                        <optgroup label="── Operasional ──">
                                            <option value="yamazaki">Yamazaki</option>
                                            <option value="tsf">TSF</option>
                                            <option value="sinar_boga">Sinar Boga</option>
                                            <option value="rorotan">Rorotan</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <!-- Filter group -->
                                <div class="col-6 col-sm-3 col-md-2">
                                    <select id="filterGroup" class="form-select form-select-sm">
                                        <option value="">Semua Group</option>
                                        <option value="Yamazaki Staff">Yamazaki Staff</option>
                                        <option value="Admin TSC">Admin TSC</option>
                                        <option value="Operasional TSC">Operasional TSC</option>
                                        <option value="TSF Staff">TSF Staff</option>
                                        <option value="Sinar Boga Staff">Sinar Boga Staff</option>
                                        <option value="Rorotan Staff">Rorotan Staff</option>
                                    </select>
                                </div>
                                <!-- Show entries -->
                                <div class="col-6 col-sm-3 col-md-auto d-flex align-items-center gap-2">
                                    <span class="text-muted small text-nowrap">Show</span>
                                    <select id="limitEntries" class="form-select form-select-sm" style="width:70px">
                                        <option value="10">10</option>
                                        <option value="25" selected>25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <span class="text-muted small">entries</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle mb-0" id="dataTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width:45px">No</th>
                                            <th class="text-center" style="width:55px">Foto</th>
                                            <th style="min-width:150px">Nama</th>
                                            <th style="min-width:130px">NIK</th>
                                            <th style="min-width:120px">Username</th>
                                            <th style="min-width:140px">Group</th>
                                            <th style="min-width:130px">Level</th>
                                            <th style="min-width:100px">Status</th>
                                            <th class="text-center" style="min-width:80px">Golongan</th>
                                            <th style="min-width:120px">Tgl Bergabung</th>
                                            <th class="text-center" style="min-width:90px">Jatah Cuti</th>
                                            <th class="text-center" style="width:150px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <tr>
                                            <td colspan="12" class="text-center py-4 text-muted">
                                                <i class="fas fa-spinner fa-spin me-2"></i> Memuat data...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer py-2">
                            <small class="text-muted" id="paginationInfo">—</small>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            let searchTimeout, currentPage = 1;
            let canDelete = <?= $can_delete ? 'true' : 'false' ?>;

            // Group badge helper
            const groupBadgeMap = {
                'Yamazaki Staff': { cls: 'gbadge-yamazaki', dot: '#c2185b' },
                'Admin TSC': { cls: 'gbadge-admin-tsc', dot: '#1565c0' },
                'Operasional TSC': { cls: 'gbadge-ops-tsc', dot: '#2e7d32' },
                'TSF Staff': { cls: 'gbadge-tsf', dot: '#f57f17' },
                'Sinar Boga Staff': { cls: 'gbadge-sinar-boga', dot: '#6a1b9a' },
                'Rorotan Staff': { cls: 'gbadge-rorotan', dot: '#bf360c' },
            };

            function groupBadge(grp) {
                if (!grp) return '<span class="text-muted small">—</span>';
                const g = groupBadgeMap[grp] || { cls: '', dot: '#aaa' };
                return `<span class="gbadge ${g.cls}"><span class="gbadge-dot" style="background:${g.dot}"></span>${escapeHtml(grp)}</span>`;
            }

            function statusBadge(status) {
                if (!status) return '<span class="text-muted">—</span>';
                const map = {
                    'Tetap': 'success',
                    'Kontrak': 'warning text-dark',
                    'Magang': 'info'
                };
                const cls = map[status] || 'secondary';
                return `<span class="badge bg-${cls} px-2 py-1" style="font-size:0.73rem;">${escapeHtml(status)}</span>`;
            }

            function formatTanggal(tgl) {
                if (!tgl || tgl === '0000-00-00') return '<span class="text-muted">—</span>';
                const d = new Date(tgl);
                if (isNaN(d)) return '<span class="text-muted">—</span>';
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            }

            function loadData() {
                const keyword = $('#searchInput').val().trim();
                const level = $('#filterLevel').val();
                const group = $('#filterGroup').val();
                const limit = $('#limitEntries').val();
                const offset = (currentPage - 1) * limit;

                $.ajax({
                    url: '<?= base_url('pengguna/filter') ?>',
                    method: 'POST',
                    data: { keyword, level, group, limit, offset },
                    success: function (response) {
                        const data = JSON.parse(response);
                        let html = '';

                        if (data.pengguna && data.pengguna.length > 0) {
                            let no = data.start;
                            data.pengguna.forEach(p => {
                                const levelClass = 'badge-' + p.user_level;
                                const levelLabel = ucwords(p.user_level.replace(/_/g, ' '));

                                const deleteBtn = canDelete
                                    ? `<a href="<?= base_url('pengguna/hapus/') ?>${p.id}" class="btn btn-danger btn-sm btn-hapus" data-nama="${escapeHtml(p.nama)}" title="Hapus"><i class="fas fa-trash"></i></a>`
                                    : `<button class="btn btn-secondary btn-sm" disabled title="Hanya Superadmin"><i class="fas fa-lock"></i></button>`;

                                html += `<tr>
                                    <td class="text-center">${no++}</td>
                                    <td class="text-center">
                                        <img src="<?= base_url('uploads/profil/') ?>${escapeHtml(p.foto_profil || 'default-1.png')}"
                                            class="avatar-circle" alt="${escapeHtml(p.nama)}">
                                    </td>
                                    <td>
                                        <strong>${escapeHtml(p.nama)}</strong>
                                    </td>
                                    <td class="text-muted small">${escapeHtml(p.nik)}</td>
                                    <td class="text-muted small">${escapeHtml(p.username) || '<em class="text-muted">-</em>'}</td>
                                    <td>${groupBadge(p.group_karyawan)}</td>
                                    <td><span class="badge ${levelClass} px-2 py-1" style="font-size:0.73rem;">${levelLabel}</span></td>
                                    <td>${statusBadge(p.status_kepegawaian)}</td>
                                    <td class="text-center">
                                        ${p.golongan
                                        ? `<span class="badge bg-secondary px-2 py-1" style="font-size:0.73rem;">${escapeHtml(p.golongan)}</span>`
                                        : '<span class="text-muted">—</span>'}
                                    </td>
                                    <td class="small">${formatTanggal(p.tanggal_join)}</td>
                                    <td class="text-center">
                                        ${p.jatah_cuti != null && p.jatah_cuti !== ''
                                        ? `<span class="badge bg-info text-dark px-2 py-1" style="font-size:0.73rem;"><i class="fas fa-umbrella-beach me-1"></i>${p.jatah_cuti} hari</span>`
                                        : '<span class="text-muted">—</span>'}
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('pengguna/detail/') ?>${p.id}" class="btn btn-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>
                                        <a href="<?= base_url('pengguna/ubah/') ?>${p.id}" class="btn btn-warning btn-sm" title="Ubah"><i class="fas fa-edit"></i></a>
                                        <a href="<?= base_url('pengguna/dokumen/') ?>${p.id}" class="btn btn-secondary btn-sm" title="Dokumen Karyawan"><i class="fas fa-folder-open"></i></a>
                                        ${deleteBtn}
                                    </td>
                                </tr>`;
                            });
                        } else {
                            html = `<tr><td colspan="12" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                <em>Tidak ada data karyawan.</em>
                            </td></tr>`;
                        }

                        $('#tableBody').html(html);
                        $('#badgeCounter').text(data.total + ' Karyawan');
                        $('#paginationInfo').html(data.total > 0
                            ? `Menampilkan ${data.start}–${data.end} dari ${data.total} entri`
                            : 'Menampilkan 0 entri');
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Gagal Memuat Data', text: 'Terjadi kesalahan saat memuat data.' });
                    }
                });
            }

            $('#searchInput').on('keyup', function () {
                currentPage = 1;
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(loadData, 400);
            });

            $('#filterLevel, #filterGroup, #limitEntries').on('change', function () {
                currentPage = 1;
                loadData();
            });

            $(document).on('keyup', function (e) {
                if (e.key === 'Escape') {
                    $('#searchInput').val('');
                    $('#filterLevel').val('');
                    $('#filterGroup').val('');
                    $('#limitEntries').val('25');
                    currentPage = 1;
                    loadData();
                }
            });

            $(document).on('click', '.btn-hapus', function (e) {
                e.preventDefault();
                if (!canDelete) {
                    Swal.fire({ icon: 'error', title: 'Akses Ditolak', text: 'Hanya Superadmin yang dapat menghapus karyawan!' });
                    return;
                }
                const href = $(this).attr('href');
                const nama = $(this).data('nama');
                Swal.fire({
                    title: 'Yakin hapus?',
                    html: `Data karyawan <strong>"${nama}"</strong> akan dihapus permanen!`,
                    icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    reverseButtons: true
                }).then(result => { if (result.isConfirmed) window.location.href = href; });
            });

            function escapeHtml(text) {
                if (!text) return '';
                const d = document.createElement('div');
                d.textContent = text;
                return d.innerHTML;
            }

            function ucwords(str) {
                return str ? str.replace(/\b\w/g, l => l.toUpperCase()) : '';
            }

            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
            loadData();
        });
    </script>
</body>

</html>