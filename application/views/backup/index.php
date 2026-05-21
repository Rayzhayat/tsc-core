<!DOCTYPE html>
<html lang="id">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .backup-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: white;
            padding: 28px 32px;
            border-radius: 12px;
            margin-bottom: 28px;
        }

        .backup-header h2 {
            margin: 0 0 6px 0;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .backup-header p {
            margin: 0;
            opacity: 0.7;
            font-size: 0.9rem;
        }

        .method-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .method-badge.mysqldump {
            background: rgba(28, 200, 138, 0.2);
            border: 1px solid rgba(28, 200, 138, 0.4);
            color: #1cc88a;
        }

        .method-badge.dbutil {
            background: rgba(246, 194, 62, 0.2);
            border: 1px solid rgba(246, 194, 62, 0.4);
            color: #f6c23e;
        }

        .stat-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .action-card .btn {
            border-radius: 8px;
            padding: 14px 20px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .action-card .btn:hover {
            transform: translateY(-2px);
        }

        .action-card .btn small {
            display: block;
            font-weight: 400;
            opacity: 0.85;
            margin-top: 3px;
            font-size: 0.78rem;
        }

        .backup-table th {
            background: #f8f9fc;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #858796;
            border-top: none;
            padding: 12px 16px;
        }

        .backup-table td {
            padding: 12px 16px;
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .backup-table tr:hover td {
            background: #f8f9fc;
        }

        .filename-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .file-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #4e73df;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .file-icon.zip {
            background: #fff3cd;
            color: #e0a800;
        }

        .file-icon.sql {
            background: #d4edda;
            color: #28a745;
        }

        .tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .tag-new {
            background: #d4edda;
            color: #155724;
        }

        .tag-old {
            background: #f8d7da;
            color: #721c24;
        }

        .tag-mysqldump {
            background: #cce5ff;
            color: #004085;
        }

        .tag-dbutil {
            background: #fff3cd;
            color: #856404;
        }

        .empty-state {
            text-align: center;
            padding: 64px 20px;
        }

        .empty-state .empty-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #f0f1f7;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            color: #b7b9cc;
        }

        .notes-box {
            background: #fffbf0;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 16px 20px;
        }

        .notes-box li {
            margin-bottom: 6px;
            font-size: 0.875rem;
            color: #444;
        }

        .notes-box li:last-child {
            margin-bottom: 0;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- Header -->
                    <div class="backup-header shadow-sm">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <h2><i class="fas fa-database mr-2"></i>Database Backup Manager</h2>
                                <p>Export, download, dan kelola backup database TSC Core System</p>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-2">
                                <span class="method-badge <?= $method ?>">
                                    <i
                                        class="fas <?= $method === 'mysqldump' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                                    Metode: <?= strtoupper($method) ?>
                                </span>
                                <small style="opacity:0.6; font-size:0.75rem;">
                                    <?php if ($method === 'mysqldump'): ?>
                                        <i class="fas fa-shield-alt"></i> Full data backup aktif
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle"></i> shell_exec tidak tersedia, menggunakan
                                        fallback
                                    <?php endif ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 mb-4">
                            <i class="fas fa-check-circle mr-2"></i><?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 mb-4">
                            <i class="fas fa-exclamation-triangle mr-2"></i><?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <!-- Stat Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-xl-4 col-md-4">
                            <div class="card stat-card shadow-sm p-3 h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="background:#eef2ff; color:#4e73df;">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted"
                                            style="font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                                            Database</div>
                                        <div class="font-weight-bold text-dark" style="font-size:1rem;">
                                            <?= htmlspecialchars($db_name) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4">
                            <div class="card stat-card shadow-sm p-3 h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="background:#d4edda; color:#1cc88a;">
                                        <i class="fas fa-hdd"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted"
                                            style="font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                                            Ukuran DB</div>
                                        <div class="font-weight-bold text-dark" style="font-size:1rem;">
                                            <?= htmlspecialchars($db_size) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4">
                            <div class="card stat-card shadow-sm p-3 h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="background:#fff3cd; color:#f6c23e;">
                                        <i class="fas fa-table"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted"
                                            style="font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                                            Total Tabel</div>
                                        <div class="font-weight-bold text-dark" style="font-size:1rem;">
                                            <?= number_format($table_count) ?> tables</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card action-card shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="m-0 font-weight-bold text-dark">
                                <i class="fas fa-bolt text-warning mr-2"></i>Backup Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="<?= base_url('database_backup/create') ?>"
                                        class="btn btn-primary btn-block w-100">
                                        <i class="fas fa-save mr-2"></i> Create Backup
                                        <small>Simpan backup di server, bisa download nanti</small>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="<?= base_url('database_backup/quick_backup') ?>"
                                        class="btn btn-success btn-block w-100">
                                        <i class="fas fa-download mr-2"></i> Quick Backup & Download
                                        <small>Buat backup dan langsung download sekarang</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Backup History -->
                    <div class="card shadow-sm mb-4">
                        <div
                            class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-dark">
                                <i class="fas fa-history text-primary mr-2"></i>Backup History
                                <?php if (!empty($backups)): ?>
                                    <span
                                        class="badge badge-primary ml-1"><?= count($backups) ?>/<?= $this->max_backups ?? 10 ?></span>
                                <?php endif ?>
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>Maks 10 backup tersimpan, lama otomatis dihapus
                            </small>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($backups)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fas fa-box-open"></i></div>
                                    <h6 class="text-muted mb-2">Belum ada backup tersimpan</h6>
                                    <p class="text-muted mb-0" style="font-size:0.875rem;">
                                        Klik "Create Backup" atau "Quick Backup & Download" untuk mulai
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table backup-table mb-0">
                                        <thead>
                                            <tr>
                                                <th width="4%">#</th>
                                                <th>Filename</th>
                                                <th width="10%">Size</th>
                                                <th width="18%">Tanggal</th>
                                                <th width="8%">Umur</th>
                                                <th width="8%">Metode</th>
                                                <th width="10%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($backups as $b): ?>
                                                <tr>
                                                    <td class="text-center text-muted"><?= $no++ ?></td>
                                                    <td>
                                                        <div class="filename-cell">
                                                            <?php $ext = pathinfo($b['filename'], PATHINFO_EXTENSION); ?>
                                                            <div class="file-icon <?= $ext ?>">
                                                                <i
                                                                    class="fas <?= $ext === 'zip' ? 'fa-file-archive' : 'fa-file-code' ?>"></i>
                                                            </div>
                                                            <div>
                                                                <div class="font-weight-bold text-dark"
                                                                    style="font-size:0.85rem;">
                                                                    <?= htmlspecialchars($b['filename']) ?>
                                                                </div>
                                                                <div class="mt-1">
                                                                    <?php if ($b['age_days'] == 0): ?>
                                                                        <span class="tag tag-new">New</span>
                                                                    <?php elseif ($b['age_days'] > 14): ?>
                                                                        <span class="tag tag-old">Lama</span>
                                                                    <?php endif ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-muted"><?= $b['size_formatted'] ?></td>
                                                    <td>
                                                        <i class="far fa-calendar-alt text-muted mr-1"></i>
                                                        <small><?= $b['date'] ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if ($b['age_days'] == 0): ?>
                                                            <span class="text-success font-weight-bold">Hari ini</span>
                                                        <?php elseif ($b['age_days'] == 1): ?>
                                                            <span class="text-info">Kemarin</span>
                                                        <?php else: ?>
                                                            <span class="text-muted"><?= $b['age_days'] ?> hari lalu</span>
                                                        <?php endif ?>
                                                    </td>
                                                    <td>
                                                        <span class="tag tag-<?= $b['method'] ?>">
                                                            <?= $b['method'] === 'mysqldump' ? 'mysqldump' : 'dbutil' ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('database_backup/download/' . urlencode($b['filename'])) ?>"
                                                            class="btn btn-success btn-sm" title="Download"
                                                            data-bs-toggle="tooltip">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <a href="<?= base_url('database_backup/delete/' . urlencode($b['filename'])) ?>"
                                                            class="btn btn-danger btn-sm ml-1" title="Hapus"
                                                            data-bs-toggle="tooltip"
                                                            onclick="return confirm('Hapus backup \'<?= htmlspecialchars($b['filename']) ?>\'?\n\nFile ini tidak bisa dikembalikan.')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="notes-box mb-4">
                        <h6 class="font-weight-bold mb-3" style="color:#92400e;">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Catatan Penting
                        </h6>
                        <ul class="pl-3 mb-0">
                            <li>Backup berisi <strong>SEMUA DATA</strong> database termasuk password dan data sensitif —
                                simpan di tempat aman</li>
                            <li>Metode <strong>mysqldump</strong> lebih reliable — data dijamin lengkap vs CI3 dbutil
                                yang bisa partial</li>
                            <li>File backup disimpan di <strong>luar web root</strong> dan hanya bisa diakses via fitur
                                download ini</li>
                            <li>Maksimal <strong>10 backup</strong> tersimpan di server — backup terlama dihapus
                                otomatis saat backup baru dibuat</li>
                            <li>Lakukan download backup secara berkala sebagai cadangan offline</li>
                        </ul>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 6000);
        });
    </script>
</body>

</html>