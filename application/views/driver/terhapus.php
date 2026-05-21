<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        body {
            background-color: #f0f2f5;
        }

        /* ===== CARD ===== */
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            padding: 14px 20px;
            background: linear-gradient(135deg, #f6c23e 0%, #dda520 100%);
        }

        /* ===== TABLE ===== */
        .table thead th {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #5a5c69;
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            vertical-align: middle;
            padding: 12px 10px;
            white-space: nowrap;
        }

        .table tbody td {
            vertical-align: middle;
            font-size: 0.875rem;
            color: #3a3b45;
            padding: 10px;
        }

        .table-warning > td {
            background-color: #fffbf0 !important;
        }

        .table-hover tbody tr.table-warning:hover > td {
            background-color: #fff3cd !important;
        }

        /* ===== DRIVER PHOTO ===== */
        .driver-photo {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e3e6f0;
            display: block;
            margin: 0 auto;
        }

        .driver-photo-placeholder {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid #e3e6f0;
            background: #f8f9fc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        /* ===== BADGES ===== */
        .badge {
            font-size: 0.74rem;
            padding: 4px 9px;
            font-weight: 600;
            border-radius: 20px;
        }

        /* ===== BUTTONS ===== */
        .btn {
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 6px 14px;
            transition: all 0.2s;
        }

        .btn i {
            margin-right: 4px;
        }

        .btn-success {
            background: linear-gradient(135deg, #1cc88a, #13855c);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74a3b, #be2617);
            border: none;
        }

        .btn-secondary {
            background: #858796;
            border: none;
            color: #fff;
        }

        /* ===== INFO BOX ===== */
        .info-box {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-left: 4px solid #f6c23e;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.875rem;
            color: #5a4a00;
            margin-bottom: 20px;
        }

        /* ===== ALERT ===== */
        .alert {
            border-radius: 8px;
            font-size: 0.875rem;
            border: none;
            padding: 12px 16px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #fde8e8;
            color: #c0392b;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            padding: 50px 20px;
            text-align: center;
            color: #b7b9cc;
        }

        .empty-state i {
            font-size: 2.8rem;
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- JUDUL -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-trash-restore text-warning"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('driver') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Driver
                        </a>
                    </div>

                    <!-- FLASH ALERT -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- INFO BOX -->
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informasi:</strong> Driver di bawah ini telah dihapus (soft delete).
                        Data tetap tersimpan dan bisa dipulihkan kapan saja.
                        <strong>Hapus Permanen</strong> akan menghapus data beserta foto secara permanen
                        dan <u>tidak bisa dikembalikan</u>.
                    </div>

                    <!-- CARD TABEL -->
                    <div class="card shadow mb-5">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-white">
                                <i class="fas fa-list"></i> Daftar Driver Terhapus
                                <span class="badge badge-light ml-2"><?= count($drivers) ?></span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <?php if (empty($drivers)): ?>
                                    <!-- 
                                        Jika kosong, tampilkan tanpa DataTables
                                        agar tidak terjadi error "Incorrect column count"
                                    -->
                                    <div class="empty-state">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <em>Tidak ada driver yang terhapus saat ini.</em>
                                    </div>
                                <?php else: ?>
                                    <table class="table table-bordered table-hover mb-0"
                                        id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th style="width:4%"  class="text-center">No</th>
                                                <th style="width:6%"  class="text-center">Foto</th>
                                                <th style="width:18%">Nama Driver</th>
                                                <th style="width:12%">NIK</th>
                                                <th style="width:13%">No. SIM / Tipe</th>
                                                <th style="width:10%">No. HP</th>
                                                <th style="width:8%"  class="text-center">Status</th>
                                                <th style="width:13%" class="text-center">Dihapus Pada</th>
                                                <th style="width:16%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($drivers as $driver): ?>
                                                <tr class="table-warning">
                                                    <td class="text-center"><?= $no++ ?></td>

                                                    <td class="text-center">
                                                        <?php if (!empty($driver->foto_driver)): ?>
                                                            <img src="<?= base_url('uploads/driver/' . $driver->foto_driver) ?>"
                                                                class="driver-photo" alt="Foto Driver">
                                                        <?php else: ?>
                                                            <div class="driver-photo-placeholder">
                                                                <i class="fas fa-user text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td>
                                                        <strong><?= htmlspecialchars($driver->nama_driver ?? '') ?></strong>
                                                        <?php if (!empty($driver->tanggal_bergabung)): ?>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar-alt"></i>
                                                                Bergabung: <?= date('d/m/Y', strtotime($driver->tanggal_bergabung)) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td>
                                                        <small><?= htmlspecialchars($driver->nik ?? '—') ?></small>
                                                    </td>

                                                    <td>
                                                        <strong><?= htmlspecialchars($driver->sim ?? '—') ?></strong>
                                                        <?php if (!empty($driver->tipe_sim)): ?>
                                                            <br>
                                                            <span class="badge badge-info">SIM <?= $driver->tipe_sim ?></span>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td>
                                                        <?php if (!empty($driver->no_hp)): ?>
                                                            <small>
                                                                <i class="fas fa-phone"></i>
                                                                <?= htmlspecialchars($driver->no_hp) ?>
                                                            </small>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td class="text-center">
                                                        <span class="badge badge-secondary">
                                                            <?= strtoupper($driver->status_driver ?? '—') ?>
                                                        </span>
                                                    </td>

                                                    <td class="text-center">
                                                        <small class="text-danger">
                                                            <i class="fas fa-clock"></i>
                                                            <?= !empty($driver->deleted_at)
                                                                ? date('d/m/Y H:i', strtotime($driver->deleted_at))
                                                                : '—' ?>
                                                        </small>
                                                    </td>

                                                    <td class="text-center">
                                                        <a href="<?= base_url('driver/restore/' . $driver->id) ?>"
                                                            class="btn btn-success btn-sm mb-1 d-block"
                                                            onclick="return confirm('Pulihkan driver <?= htmlspecialchars($driver->nama_driver ?? '') ?>?')">
                                                            <i class="fas fa-undo"></i> Pulihkan
                                                        </a>
                                                        <a href="<?= base_url('driver/hapus_permanen/' . $driver->id) ?>"
                                                            class="btn btn-danger btn-sm d-block"
                                                            onclick="return confirm('⚠️ HAPUS PERMANEN driver <?= htmlspecialchars($driver->nama_driver ?? '') ?>?\n\nData dan foto tidak bisa dikembalikan!')">
                                                            <i class="fas fa-times"></i> Permanen
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {
            // Hanya init DataTables jika tabel ada (data tidak kosong)
            if ($('#dataTable').length) {
                $('#dataTable').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                    },
                    "order": [[7, "desc"]],
                    "pageLength": 25,
                    "responsive": true,
                    // Nonaktifkan kolom Aksi dari sorting
                    "columnDefs": [
                        { "orderable": false, "targets": [1, 8] }
                    ]
                });
            }

            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>

</html>