<!-- detail -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* ── Header Unit Card ── */
        .unit-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border-radius: 12px;
            color: white;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
        }

        .unit-header .nopol {
            font-size: 1.8rem;
            font-weight: 900;
            letter-spacing: 2px;
            font-family: monospace;
        }

        .unit-header .meta {
            opacity: 0.85;
            font-size: 0.88rem;
        }

        .info-pill {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.8rem;
            display: inline-block;
            margin: 2px;
        }

        /* ── Status badges ── */
        .s-aktif {
            background: #1cc88a;
            color: white;
        }

        .s-maintenance {
            background: #f6c23e;
            color: #333;
        }

        .s-rusak {
            background: #e74a3b;
            color: white;
        }

        /* ── Tabs ── */
        .fleet-tabs .nav-link {
            border-radius: 8px 8px 0 0;
            font-weight: 600;
            color: #858796;
            border: none;
            padding: 10px 20px;
        }

        .fleet-tabs .nav-link.active {
            background: white;
            color: #4e73df;
            border-bottom: 3px solid #4e73df;
        }

        .tab-content {
            background: white;
            border-radius: 0 0 10px 10px;
        }

        /* ── Tables ── */
        .detail-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #f8f9fc;
            font-weight: 700;
            border-bottom: 2px solid #e3e6f0;
        }

        .detail-table td {
            vertical-align: middle;
            font-size: 0.85rem;
        }

        /* ── Status doc badges ── */
        .bd-aktif {
            background: #1cc88a;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .bd-expired {
            background: #e74a3b;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .bd-diproses {
            background: #4e73df;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .bd-soon {
            background: #f6c23e;
            color: #333;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* ── Summary mini cards ── */
        .mini-card {
            border-radius: 8px;
            padding: 12px 16px;
            text-align: center;
            border: 1px solid #e3e6f0;
        }

        .mini-val {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
        }

        .mini-label {
            font-size: 0.7rem;
            color: #858796;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* ── Modals ── */
        .modal-header-dok {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
        }

        .modal-header-service {
            background: linear-gradient(135deg, #f6c23e, #dda20a);
            color: #333;
        }

        .modal-header-bbm {
            background: linear-gradient(135deg, #1cc88a, #13855c);
            color: white;
        }

        .row-expired {
            background: #fff5f5 !important;
        }

        .row-soon {
            background: #fffbf0 !important;
        }

        .auto-field {
            background: #f8f9fc;
            color: #6c757d;
        }

        /* ── Empty state ── */
        .empty-tab {
            padding: 40px 20px;
            text-align: center;
            color: #b7b9cc;
        }

        .empty-tab i {
            font-size: 2.4rem;
            display: block;
            margin-bottom: 8px;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- BREADCRUMB -->
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb bg-transparent p-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('unit') ?>">Master Unit</a></li>
                            <li class="breadcrumb-item active">Detail Unit</li>
                        </ol>
                    </nav>

                    <!-- FLASH -->
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

                    <!-- ══ UNIT HEADER ══ -->
                    <?php
                    $u = $unit;
                    $level = $this->session->userdata('login')['user_level'] ?? '';
                    $can_edit = in_array($level, ['superadmin', 'admin_operational', 'fleet_staff']);
                    $can_delete = in_array($level, ['superadmin', 'admin_operational']);
                    $status = $u->status_unit ?? 'aktif';
                    $sc = ['aktif' => 's-aktif', 'maintenance' => 's-maintenance', 'rusak' => 's-rusak'];
                    ?>
                    <div class="unit-header d-flex justify-content-between align-items-start flex-wrap">
                        <div>
                            <div class="nopol mb-1">🚛 <?= strtoupper($u->no_polisi ?? '') ?></div>
                            <div class="meta mb-2">
                                <span class="info-pill"><?= $u->tipe_unit ?? '-' ?></span>
                                <span class="info-pill"><?= $u->tipe_box ?? '-' ?></span>
                                <span class="info-pill">Tahun <?= $u->tahun_unit ?? '-' ?></span>
                                <span class="info-pill"><?= $u->tonase ?? '-' ?> Ton</span>
                                <?php if ($u->panjang && $u->lebar && $u->tinggi): ?>
                                    <span class="info-pill"><?= $u->panjang ?>×<?= $u->lebar ?>×<?= $u->tinggi ?> m</span>
                                <?php endif ?>
                            </div>
                            <div>
                                <span class="badge badge-pill badge-light <?= $sc[$status] ?? '' ?>"
                                    style="font-size:0.85rem;padding:6px 14px;">
                                    <?= strtoupper($status) ?>
                                </span>
                                <?php if ($u->bahan_bakar): ?>
                                    <span class="info-pill ml-1">
                                        <i class="fas fa-gas-pump"></i> <?= strtoupper($u->bahan_bakar) ?>
                                        <?= $u->konsumsi_bbm ? ' · ' . $u->konsumsi_bbm . ' km/L' : '' ?>
                                    </span>
                                <?php endif ?>
                                <?php if ($u->current_km): ?>
                                    <span class="info-pill ml-1">
                                        <i class="fas fa-tachometer-alt"></i> <?= number_format($u->current_km) ?> km
                                    </span>
                                <?php endif ?>
                            </div>
                        </div>
                        <div class="mt-2">
                            <?php if ($can_edit): ?>
                                <a href="<?= base_url('unit/ubah/' . $u->id) ?>" class="btn btn-light btn-sm">
                                    <i class="fas fa-edit"></i> Edit Unit
                                </a>
                            <?php endif ?>
                            <a href="<?= base_url('unit') ?>" class="btn btn-outline-light btn-sm ml-1">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <!-- ══ SUMMARY MINI CARDS ══ -->
                    <div class="row mb-4">
                        <div class="col-md-2 col-6 mb-2">
                            <div class="mini-card">
                                <div class="mini-val text-primary"><?= count($dokumens) ?></div>
                                <div class="mini-label">Total Dokumen</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <div class="mini-card">
                                <div class="mini-val text-danger">
                                    <?= count(array_filter($dokumens, fn($d) => $d->status == 'expired')) ?>
                                </div>
                                <div class="mini-label">Dok Expired</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <div class="mini-card">
                                <div class="mini-val text-success"><?= count($maintenances) ?></div>
                                <div class="mini-label">Total Service</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <div class="mini-card">
                                <?php $total_biaya_service = array_sum(array_column((array) $maintenances, 'biaya')); ?>
                                <div class="mini-val text-warning" style="font-size:1rem;">
                                    Rp <?= number_format($total_biaya_service / 1000000, 1) ?>jt
                                </div>
                                <div class="mini-label">Total Biaya Service</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <div class="mini-card">
                                <div class="mini-val text-info"><?= count($fuels) ?></div>
                                <div class="mini-label">Pengisian BBM</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <div class="mini-card">
                                <?php $total_liter = array_sum(array_column((array) $fuels, 'liter')); ?>
                                <div class="mini-val text-success"><?= number_format($total_liter, 0) ?></div>
                                <div class="mini-label">Total Liter BBM</div>
                            </div>
                        </div>
                    </div>

                    <!-- ══ TABS ══ -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-white border-bottom-0 pb-0">
                            <ul class="nav fleet-tabs" id="unitTabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tabDokumen">
                                        <i class="fas fa-file-alt text-primary"></i> Dokumen
                                        <?php $dok_alert = count(array_filter($dokumens, fn($d) => $d->status == 'expired' || (strtotime($d->tanggal_expired ?? '2099-01-01') - time()) / 86400 <= 30)); ?>
                                        <?php if ($dok_alert): ?>
                                            <span class="badge badge-danger ml-1"><?= $dok_alert ?></span>
                                        <?php endif ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tabService">
                                        <i class="fas fa-wrench text-warning"></i> Histori Service
                                        <span class="badge badge-secondary ml-1"><?= count($maintenances) ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tabBBM">
                                        <i class="fas fa-gas-pump text-success"></i> Histori BBM
                                        <span class="badge badge-secondary ml-1"><?= count($fuels) ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content p-3">

                            <!-- ════ TAB DOKUMEN ════ -->
                            <div class="tab-pane fade show active" id="tabDokumen">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold mb-0">
                                        <i class="fas fa-file-alt text-primary"></i> Dokumen Unit
                                    </h6>
                                    <?php if ($can_edit): ?>
                                        <button class="btn btn-primary btn-sm" id="btnTambahDok">
                                            <i class="fas fa-plus"></i> Tambah Dokumen
                                        </button>
                                    <?php endif ?>
                                </div>

                                <?php if (empty($dokumens)): ?>
                                    <!-- Empty state DIPISAH dari tabel — mencegah DataTables error tn/18 -->
                                    <div class="empty-tab">
                                        <i class="fas fa-file text-muted"></i>
                                        <em>Belum ada dokumen untuk unit ini.</em>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover detail-table mb-0" id="tblDok">
                                            <thead>
                                                <tr>
                                                    <th>Jenis</th>
                                                    <th>No Dokumen</th>
                                                    <th>Tgl Terbit</th>
                                                    <th>Tgl Expired</th>
                                                    <th>Sisa Hari</th>
                                                    <th>Biaya</th>
                                                    <th>Status</th>
                                                    <th>File</th>
                                                    <?php if ($can_edit): ?>
                                                        <th>Aksi</th><?php endif ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($dokumens as $d):
                                                    $diff = !empty($d->tanggal_expired) ? (strtotime($d->tanggal_expired) - time()) / 86400 : 999;
                                                    $row_class = ($d->status == 'expired' || $diff <= 0) ? 'row-expired' : ($diff <= 30 ? 'row-soon' : '');
                                                    ?>
                                                    <tr class="<?= $row_class ?>">
                                                        <td><span
                                                                class="badge badge-secondary text-uppercase"><?= $d->jenis_dokumen ?></span>
                                                        </td>
                                                        <td><?= htmlspecialchars($d->nomor_dokumen ?? '-') ?></td>
                                                        <td><?= !empty($d->tanggal_terbit) ? date('d/m/Y', strtotime($d->tanggal_terbit)) : '-' ?>
                                                        </td>
                                                        <td class="font-weight-bold">
                                                            <?= !empty($d->tanggal_expired) ? date('d/m/Y', strtotime($d->tanggal_expired)) : '-' ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($diff <= 0): ?>
                                                                <span class="bd-expired">❌ Expired</span>
                                                            <?php elseif ($diff <= 7): ?>
                                                                <span class="bd-soon">🔥 <?= ceil($diff) ?> hari</span>
                                                            <?php elseif ($diff <= 30): ?>
                                                                <span class="bd-soon">⚠️ <?= ceil($diff) ?> hari</span>
                                                            <?php elseif ($diff < 999): ?>
                                                                <span style="color:#1cc88a;font-weight:600;">✓ <?= ceil($diff) ?>
                                                                    hari</span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif ?>
                                                        </td>
                                                        <td><?= $d->biaya > 0 ? 'Rp ' . number_format($d->biaya, 0, ',', '.') : '-' ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($d->status == 'aktif'): ?>
                                                                <span class="bd-aktif">AKTIF</span>
                                                            <?php elseif ($d->status == 'expired'): ?>
                                                                <span class="bd-expired">EXPIRED</span>
                                                            <?php else: ?>
                                                                <span class="bd-diproses">DIPROSES</span>
                                                            <?php endif ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($d->file_dokumen)): ?>
                                                                <a href="<?= base_url('uploads/unit_documents/' . $d->file_dokumen) ?>"
                                                                    target="_blank" class="btn btn-info btn-sm">
                                                                    <i
                                                                        class="fas fa-<?= pathinfo($d->file_dokumen, PATHINFO_EXTENSION) == 'pdf' ? 'file-pdf' : 'image' ?>"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif ?>
                                                        </td>
                                                        <?php if ($can_edit): ?>
                                                            <td>
                                                                <button class="btn btn-success btn-sm btn-edit-dok"
                                                                    data-id="<?= $d->id ?>"
                                                                    data-jenis="<?= htmlspecialchars($d->jenis_dokumen) ?>"
                                                                    data-nomor="<?= htmlspecialchars($d->nomor_dokumen ?? '') ?>"
                                                                    data-terbit="<?= $d->tanggal_terbit ?? '' ?>"
                                                                    data-expired="<?= $d->tanggal_expired ?? '' ?>"
                                                                    data-biaya="<?= $d->biaya ?? 0 ?>"
                                                                    data-status="<?= $d->status ?>"
                                                                    data-reminder="<?= $d->reminder_days ?? 30 ?>"
                                                                    data-keterangan="<?= htmlspecialchars($d->keterangan ?? '') ?>"
                                                                    data-file="<?= $d->file_dokumen ?? '' ?>" title="Ubah">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <?php if ($can_delete): ?>
                                                                    <button class="btn btn-danger btn-sm btn-hapus-dok"
                                                                        data-id="<?= $d->id ?>"
                                                                        data-jenis="<?= strtoupper($d->jenis_dokumen) ?>" title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                <?php endif ?>
                                                            </td>
                                                        <?php endif ?>
                                                    </tr>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif ?>
                            </div>

                            <!-- ════ TAB SERVICE ════ -->
                            <div class="tab-pane fade" id="tabService">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold mb-0">
                                        <i class="fas fa-wrench text-warning"></i> Histori Service
                                    </h6>
                                    <?php if ($can_edit): ?>
                                        <button class="btn btn-warning btn-sm" id="btnTambahService">
                                            <i class="fas fa-plus"></i> Tambah Service
                                        </button>
                                    <?php endif ?>
                                </div>

                                <?php if (empty($maintenances)): ?>
                                    <!-- Empty state DIPISAH dari tabel — mencegah DataTables error tn/18 -->
                                    <div class="empty-tab">
                                        <i class="fas fa-wrench text-muted"></i>
                                        <em>Belum ada histori service untuk unit ini.</em>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover detail-table mb-0" id="tblService">
                                            <thead>
                                                <tr>
                                                    <th>Tgl Service</th>
                                                    <th>Jenis Service</th>
                                                    <th>KM Saat Service</th>
                                                    <th>Next Service KM</th>
                                                    <th>Bengkel</th>
                                                    <th>Biaya</th>
                                                    <th>Parts</th>
                                                    <th>Bukti</th>
                                                    <?php if ($can_edit): ?>
                                                        <th>Aksi</th><?php endif ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($maintenances as $m): ?>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <?= date('d/m/Y', strtotime($m->tanggal_service)) ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $jenis_labels = [
                                                                'service_rutin' => '🔧 Service Rutin',
                                                                'perbaikan' => '🛠️ Perbaikan',
                                                                'ganti_oli' => '🛢️ Ganti Oli',
                                                                'ganti_ban' => '🔄 Ganti Ban',
                                                                'ganti_aki' => '⚡ Ganti Aki',
                                                                'tune_up' => '⚙️ Tune Up',
                                                                'lainnya' => '📋 Lainnya'
                                                            ];
                                                            echo $jenis_labels[$m->jenis_service] ?? $m->jenis_service;
                                                            ?>
                                                        </td>
                                                        <td><?= !empty($m->km_saat_service) ? number_format($m->km_saat_service) . ' km' : '-' ?>
                                                        </td>
                                                        <td><?= !empty($m->next_service_km) ? number_format($m->next_service_km) . ' km' : '-' ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($m->bengkel ?? '-') ?></td>
                                                        <td><?= $m->biaya > 0 ? 'Rp ' . number_format($m->biaya, 0, ',', '.') : '-' ?>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">
                                                                <?= $m->parts_diganti ? htmlspecialchars(substr($m->parts_diganti, 0, 40)) . (strlen($m->parts_diganti) > 40 ? '...' : '') : '-' ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($m->bukti_nota)): ?>
                                                                <a href="<?= base_url('uploads/unit_service/' . $m->bukti_nota) ?>"
                                                                    target="_blank" class="btn btn-info btn-sm">
                                                                    <i class="fas fa-image"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif ?>
                                                        </td>
                                                        <?php if ($can_edit): ?>
                                                            <td>
                                                                <button class="btn btn-success btn-sm btn-edit-service"
                                                                    data-id="<?= $m->id ?>"
                                                                    data-tanggal="<?= $m->tanggal_service ?>"
                                                                    data-jenis="<?= $m->jenis_service ?>"
                                                                    data-km="<?= $m->km_saat_service ?? '' ?>"
                                                                    data-next-km="<?= $m->next_service_km ?? '' ?>"
                                                                    data-bengkel="<?= htmlspecialchars($m->bengkel ?? '') ?>"
                                                                    data-teknisi="<?= htmlspecialchars($m->teknisi ?? '') ?>"
                                                                    data-biaya="<?= $m->biaya ?? 0 ?>"
                                                                    data-parts="<?= htmlspecialchars($m->parts_diganti ?? '') ?>"
                                                                    data-keterangan="<?= htmlspecialchars($m->keterangan ?? '') ?>"
                                                                    title="Ubah">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <?php if ($can_delete): ?>
                                                                    <button class="btn btn-danger btn-sm btn-hapus-service"
                                                                        data-id="<?= $m->id ?>" title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                <?php endif ?>
                                                            </td>
                                                        <?php endif ?>
                                                    </tr>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif ?>
                            </div>

                            <!-- ════ TAB BBM ════ -->
                            <div class="tab-pane fade" id="tabBBM">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold mb-0">
                                        <i class="fas fa-gas-pump text-success"></i> Histori BBM
                                    </h6>
                                    <?php if ($can_edit): ?>
                                        <button class="btn btn-success btn-sm" id="btnTambahBBM">
                                            <i class="fas fa-plus"></i> Catat Pengisian BBM
                                        </button>
                                    <?php endif ?>
                                </div>

                                <?php if (!empty($fuels)):
                                    $avg_konsumsi = array_filter(array_column((array) $fuels, 'konsumsi'), fn($k) => $k > 0);
                                    $avg_konsumsi = !empty($avg_konsumsi) ? array_sum($avg_konsumsi) / count($avg_konsumsi) : 0;
                                    $total_biaya_bbm = array_sum(array_column((array) $fuels, 'total_biaya'));
                                    ?>
                                    <div class="row mb-3">
                                        <div class="col-md-3 col-6">
                                            <div class="mini-card">
                                                <div class="mini-val text-success"><?= number_format($total_liter, 1) ?>L
                                                </div>
                                                <div class="mini-label">Total Liter</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <div class="mini-card">
                                                <div class="mini-val text-info" style="font-size:1rem;">
                                                    Rp <?= number_format($total_biaya_bbm / 1000, 0) ?>rb
                                                </div>
                                                <div class="mini-label">Total Biaya</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <div class="mini-card">
                                                <div class="mini-val text-warning"><?= number_format($avg_konsumsi, 1) ?>
                                                </div>
                                                <div class="mini-label">Rata² km/L</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <div class="mini-card">
                                                <div class="mini-val text-primary"><?= count($fuels) ?></div>
                                                <div class="mini-label">Total Pengisian</div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif ?>

                                <?php if (empty($fuels)): ?>
                                    <!-- Empty state DIPISAH dari tabel — mencegah DataTables error tn/18 -->
                                    <div class="empty-tab">
                                        <i class="fas fa-gas-pump text-muted"></i>
                                        <em>Belum ada histori pengisian BBM untuk unit ini.</em>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover detail-table mb-0" id="tblBBM">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Jenis BBM</th>
                                                    <th>Liter</th>
                                                    <th>Harga/L</th>
                                                    <th>Total</th>
                                                    <th>KM Saat Isi</th>
                                                    <th>Konsumsi</th>
                                                    <th>SPBU</th>
                                                    <th>Driver</th>
                                                    <th>Struk</th>
                                                    <?php if ($can_edit): ?>
                                                        <th>Aksi</th><?php endif ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($fuels as $f): ?>
                                                    <tr>
                                                        <td class="font-weight-bold">
                                                            <?= date('d/m/Y', strtotime($f->tanggal_isi)) ?>
                                                        </td>
                                                        <td><span
                                                                class="badge badge-dark"><?= strtoupper($f->jenis_bbm) ?></span>
                                                        </td>
                                                        <td><strong class="text-success"><?= number_format($f->liter, 2) ?>
                                                                L</strong></td>
                                                        <td>Rp <?= number_format($f->harga_per_liter, 0, ',', '.') ?></td>
                                                        <td class="font-weight-bold">Rp
                                                            <?= number_format($f->total_biaya, 0, ',', '.') ?></td>
                                                        <td><?= !empty($f->km_saat_isi) ? number_format($f->km_saat_isi) . ' km' : '-' ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($f->konsumsi)):
                                                                $color = $f->konsumsi >= 8 ? 'text-success' : ($f->konsumsi >= 5 ? 'text-warning' : 'text-danger');
                                                                ?>
                                                                <span
                                                                    class="font-weight-bold <?= $color ?>"><?= number_format($f->konsumsi, 2) ?>
                                                                    km/L</span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif ?>
                                                        </td>
                                                        <td><small><?= htmlspecialchars($f->spbu ?? '-') ?></small></td>
                                                        <td><small><?= htmlspecialchars($f->driver_nama ?? '-') ?></small></td>
                                                        <td>
                                                            <?php if (!empty($f->bukti_struk)): ?>
                                                                <a href="<?= base_url('uploads/unit_bbm/' . $f->bukti_struk) ?>"
                                                                    target="_blank" class="btn btn-info btn-sm">
                                                                    <i class="fas fa-image"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif ?>
                                                        </td>
                                                        <?php if ($can_edit): ?>
                                                            <td>
                                                                <button class="btn btn-success btn-sm btn-edit-bbm"
                                                                    data-id="<?= $f->id ?>" data-tanggal="<?= $f->tanggal_isi ?>"
                                                                    data-waktu="<?= $f->waktu_isi ?? '' ?>"
                                                                    data-jenis="<?= $f->jenis_bbm ?>" data-liter="<?= $f->liter ?>"
                                                                    data-harga="<?= $f->harga_per_liter ?>"
                                                                    data-total="<?= $f->total_biaya ?>"
                                                                    data-km="<?= $f->km_saat_isi ?? '' ?>"
                                                                    data-kmterakhir="<?= $f->km_terakhir ?? '' ?>"
                                                                    data-konsumsi="<?= $f->konsumsi ?? '' ?>"
                                                                    data-driver="<?= htmlspecialchars($f->driver_nama ?? '') ?>"
                                                                    data-spbu="<?= htmlspecialchars($f->spbu ?? '') ?>"
                                                                    data-lokasi="<?= htmlspecialchars($f->lokasi ?? '') ?>"
                                                                    data-keterangan="<?= htmlspecialchars($f->keterangan ?? '') ?>"
                                                                    title="Ubah">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <?php if ($can_delete): ?>
                                                                    <button class="btn btn-danger btn-sm btn-hapus-bbm"
                                                                        data-id="<?= $f->id ?>" title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                <?php endif ?>
                                                            </td>
                                                        <?php endif ?>
                                                    </tr>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif ?>
                            </div>

                        </div><!-- end tab-content -->
                    </div><!-- end card -->

                    <!-- ════════════ MODALS ════════════ -->

                    <!-- Modal Tambah/Ubah Dokumen -->
                    <div class="modal fade" id="modalTambahDok" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header modal-header-dok">
                                    <h5 class="modal-title" id="modalDokTitle"><i class="fas fa-file-alt"></i> Tambah
                                        Dokumen</h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal">×</button>
                                </div>
                                <form id="formDok" action="<?= base_url('unit_document/proses_tambah') ?>" method="POST"
                                    enctype="multipart/form-data">
                                    <input type="hidden" name="unit_id" value="<?= $u->id ?>">
                                    <input type="hidden" name="redirect_to" value="unit/detail/<?= $u->id ?>">
                                    <input type="hidden" name="dok_id" id="dok_id" value="">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Jenis Dokumen <span
                                                                class="text-danger">*</span></strong></label>
                                                    <select name="jenis_dokumen" id="dok_jenis" class="form-control"
                                                        required>
                                                        <option value="">-- Pilih --</option>
                                                        <option value="stnk">STNK</option>
                                                        <option value="kir">KIR</option>
                                                        <option value="asuransi">Asuransi</option>
                                                        <option value="pajak">Pajak</option>
                                                        <option value="keur">KEUR</option>
                                                        <option value="lainnya">Lainnya</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Nomor Dokumen</strong></label>
                                                    <input type="text" name="nomor_dokumen" id="dok_nomor"
                                                        class="form-control" placeholder="B-12345-2024">
                                                </div>
                                            </div>
                                            <div class="col-md-4" id="dok_status_wrap" style="display:none">
                                                <div class="form-group">
                                                    <label><strong>Status</strong></label>
                                                    <select name="status" id="dok_status" class="form-control">
                                                        <option value="aktif">Aktif</option>
                                                        <option value="diproses">Diproses</option>
                                                        <option value="expired">Expired</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Tanggal Terbit</strong></label>
                                                    <input type="date" name="tanggal_terbit" id="dok_terbit"
                                                        class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Tanggal Expired <span
                                                                class="text-danger">*</span></strong></label>
                                                    <input type="date" name="tanggal_expired" id="dok_expired"
                                                        class="form-control" required>
                                                    <small id="dok_sisa"></small>
                                                    <small id="dok_expired_hint" class="mt-1 d-block"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Biaya (Rp)</strong></label>
                                                    <input type="number" name="biaya" id="dok_biaya"
                                                        class="form-control" placeholder="0" min="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Reminder (hari)</strong></label>
                                                    <input type="number" name="reminder_days" id="dok_reminder"
                                                        class="form-control" value="30">
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label><strong>Upload File (PDF/Foto)</strong></label>
                                                    <div id="dok_file_current" class="mb-1" style="display:none">
                                                        <small>File saat ini: <a id="dok_file_link" href="#"
                                                                target="_blank">Lihat</a> (kosongkan jika tidak ingin
                                                            ubah)</small>
                                                    </div>
                                                    <input type="file" name="file_dokumen" class="form-control-file"
                                                        accept=".jpg,.jpeg,.png,.pdf">
                                                    <small class="text-muted">PDF/JPG/PNG, maks 5MB</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Keterangan</strong></label>
                                            <textarea name="keterangan" id="dok_keterangan" class="form-control"
                                                rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan
                                            Dokumen</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Tambah/Ubah Service -->
                    <div class="modal fade" id="modalTambahService" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header modal-header-service">
                                    <h5 class="modal-title" id="modalServiceTitle"><i class="fas fa-wrench"></i> Tambah
                                        Histori Service</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
                                </div>
                                <form id="formService" action="<?= base_url('unit_maintenance/proses_tambah') ?>"
                                    method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="unit_id" value="<?= $u->id ?>">
                                    <input type="hidden" name="redirect_to" value="unit/detail/<?= $u->id ?>">
                                    <input type="hidden" name="service_id" id="service_id" value="">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Tanggal Service <span
                                                                class="text-danger">*</span></strong></label>
                                                    <input type="date" name="tanggal_service" id="svc_tanggal"
                                                        class="form-control" required value="<?= date('Y-m-d') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Jenis Service <span
                                                                class="text-danger">*</span></strong></label>
                                                    <select name="jenis_service" id="svc_jenis" class="form-control"
                                                        required>
                                                        <option value="">-- Pilih --</option>
                                                        <option value="service_rutin">🔧 Service Rutin</option>
                                                        <option value="perbaikan">🛠️ Perbaikan</option>
                                                        <option value="ganti_oli">🛢️ Ganti Oli</option>
                                                        <option value="ganti_ban">🔄 Ganti Ban</option>
                                                        <option value="ganti_aki">⚡ Ganti Aki</option>
                                                        <option value="tune_up">⚙️ Tune Up</option>
                                                        <option value="lainnya">📋 Lainnya</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>KM Saat Service</strong></label>
                                                    <input type="number" name="km_saat_service" id="svc_km"
                                                        class="form-control" placeholder="<?= $u->current_km ?? '' ?>"
                                                        value="<?= $u->current_km ?? '' ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Bengkel</strong></label>
                                                    <input type="text" name="bengkel" id="svc_bengkel"
                                                        class="form-control" placeholder="Nama bengkel">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Teknisi</strong></label>
                                                    <input type="text" name="teknisi" id="svc_teknisi"
                                                        class="form-control" placeholder="Nama teknisi">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Biaya (Rp)</strong></label>
                                                    <input type="number" name="biaya" id="svc_biaya"
                                                        class="form-control" placeholder="0" min="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Next Service KM</strong></label>
                                                    <input type="number" name="next_service_km" id="svc_next_km"
                                                        class="form-control" placeholder="Contoh: 150000">
                                                    <small class="text-muted">Otomatis update di master unit</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>Bukti Nota</strong></label>
                                                    <div id="svc_bukti_current" style="display:none" class="mb-1">
                                                        <small>File saat ini: <a id="svc_bukti_link" href="#"
                                                                target="_blank">Lihat</a></small>
                                                    </div>
                                                    <input type="file" name="bukti_nota" class="form-control-file"
                                                        accept=".jpg,.jpeg,.png,.pdf">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Parts yang Diganti</strong></label>
                                            <textarea name="parts_diganti" id="svc_parts" class="form-control" rows="2"
                                                placeholder="Contoh: Oli mesin, filter oli, busi..."></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Keterangan</strong></label>
                                            <textarea name="keterangan" id="svc_keterangan" class="form-control"
                                                rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Simpan
                                            Service</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Tambah/Ubah BBM -->
                    <div class="modal fade" id="modalTambahBBM" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header modal-header-bbm">
                                    <h5 class="modal-title" id="modalBBMTitle"><i class="fas fa-gas-pump"></i> Catat
                                        Pengisian BBM</h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal">×</button>
                                </div>
                                <form id="formBBM" action="<?= base_url('unit_fuel/proses_tambah') ?>" method="POST"
                                    enctype="multipart/form-data">
                                    <input type="hidden" name="unit_id" value="<?= $u->id ?>">
                                    <input type="hidden" name="redirect_to" value="unit/detail/<?= $u->id ?>">
                                    <input type="hidden" name="fuel_id" id="fuel_id" value="">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label><strong>Tanggal <span
                                                                class="text-danger">*</span></strong></label>
                                                    <input type="date" name="tanggal_isi" id="bbm_tanggal"
                                                        class="form-control" required value="<?= date('Y-m-d') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label><strong>Waktu</strong></label>
                                                    <input type="time" name="waktu_isi" id="bbm_waktu"
                                                        class="form-control" value="<?= date('H:i') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label><strong>Jenis BBM <span
                                                                class="text-danger">*</span></strong></label>
                                                    <select name="jenis_bbm" id="bbm_jenis" class="form-control"
                                                        required>
                                                        <option value="solar" <?= ($u->bahan_bakar == 'solar') ? 'selected' : '' ?>>Solar</option>
                                                        <option value="bensin" <?= ($u->bahan_bakar == 'bensin') ? 'selected' : '' ?>>Bensin</option>
                                                        <option value="pertamax">Pertamax</option>
                                                        <option value="pertalite">Pertalite</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label><strong>KM Saat Isi</strong></label>
                                                    <input type="number" name="km_saat_isi" id="bbm_km"
                                                        class="form-control" placeholder="<?= $u->current_km ?? '' ?>"
                                                        value="<?= $u->current_km ?? '' ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label><strong>Jumlah Liter <span
                                                                class="text-danger">*</span></strong></label>
                                                    <input type="number" name="liter" id="bbm_liter"
                                                        class="form-control" step="0.01" required placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label><strong>Harga/Liter (Rp)</strong></label>
                                                    <input type="number" name="harga_per_liter" id="bbm_harga"
                                                        class="form-control" placeholder="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label><strong>Total Biaya (Rp)</strong></label>
                                                    <input type="number" name="total_biaya" id="bbm_total"
                                                        class="form-control auto-field" placeholder="0">
                                                    <small class="text-muted">Otomatis terhitung</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label><strong>Konsumsi (km/L)</strong></label>
                                                    <input type="number" name="konsumsi" id="bbm_konsumsi"
                                                        class="form-control auto-field" step="0.01" placeholder="Auto">
                                                    <small class="text-muted" id="bbm_km_hint"></small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>SPBU</strong></label>
                                                    <input type="text" name="spbu" id="bbm_spbu" class="form-control"
                                                        placeholder="Nama/No SPBU">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Lokasi</strong></label>
                                                    <input type="text" name="lokasi" id="bbm_lokasi"
                                                        class="form-control" placeholder="Kota/Jalan">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>Driver</strong></label>
                                                    <input type="text" name="driver_nama" id="bbm_driver"
                                                        class="form-control" placeholder="Nama driver">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><strong>Bukti Struk</strong></label>
                                                    <div id="bbm_struk_current" style="display:none" class="mb-1">
                                                        <small>File saat ini: <a id="bbm_struk_link" href="#"
                                                                target="_blank">Lihat</a></small>
                                                    </div>
                                                    <input type="file" name="bukti_struk" class="form-control-file"
                                                        accept=".jpg,.jpeg,.png">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><strong>Keterangan</strong></label>
                                            <textarea name="keterangan" id="bbm_keterangan" class="form-control"
                                                rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan
                                            BBM</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Konfirmasi Hapus -->
                    <div class="modal fade" id="modalHapus" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title"><i class="fas fa-trash"></i> Konfirmasi Hapus</h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal">×</button>
                                </div>
                                <div class="modal-body text-center">
                                    <p id="hapus_msg">Yakin ingin menghapus data ini?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        data-bs-dismiss="modal">Batal</button>
                                    <a id="hapus_url" href="#" class="btn btn-danger btn-sm"><i
                                            class="fas fa-trash"></i> Hapus</a>
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
    <script>
        $(document).ready(function () {

            // ── DataTables — hanya init jika tabel ada di DOM ──
            if ($('#tblDok').length) {
                try {
                    $('#tblDok').DataTable({
                        pageLength: 10,
                        order: [[3, 'asc']],
                        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                        responsive: false,
                        destroy: true,
                        columnDefs: [{ orderable: false, targets: -1 }]
                    });
                } catch (e) { console.warn('tblDok:', e); }
            }

            if ($('#tblService').length) {
                try {
                    $('#tblService').DataTable({
                        pageLength: 10,
                        order: [[0, 'desc']],
                        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                        responsive: false,
                        destroy: true,
                        columnDefs: [{ orderable: false, targets: -1 }]
                    });
                } catch (e) { console.warn('tblService:', e); }
            }

            if ($('#tblBBM').length) {
                try {
                    $('#tblBBM').DataTable({
                        pageLength: 10,
                        order: [[0, 'desc']],
                        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                        responsive: false,
                        destroy: true,
                        columnDefs: [{ orderable: false, targets: -1 }]
                    });
                } catch (e) { console.warn('tblBBM:', e); }
            }

            // Re-adjust on tab switch
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            });

            // ── Data expired terakhir per jenis dokumen ──
            const lastExpired = {
                stnk: '<?= $stnk_expired_last ?? '' ?>',
                kir: '<?= $kir_expired_last ?? '' ?>'
            };

            // ── Sisa hari realtime ──
            function hitungSisa(val, el) {
                if (!val) { $(el).html(''); return; }
                const diff = Math.ceil((new Date(val) - new Date()) / 86400000);
                if (diff < 0)
                    $(el).html('<span class="text-danger font-weight-bold">❌ Expired ' + Math.abs(diff) + ' hari lalu</span>');
                else if (diff <= 7)
                    $(el).html('<span class="text-danger font-weight-bold">🔥 Sisa ' + diff + ' hari!</span>');
                else if (diff <= 30)
                    $(el).html('<span class="text-warning font-weight-bold">⚠️ Sisa ' + diff + ' hari</span>');
                else
                    $(el).html('<span class="text-success">✓ Sisa ' + diff + ' hari</span>');
            }

            $('#dok_expired').on('change', function () { hitungSisa(this.value, '#dok_sisa'); });

            $('#dok_jenis').on('change', function () {
                const jenis = $(this).val();
                const isEdit = $('#dok_id').val() !== '';
                if (!isEdit && lastExpired[jenis]) {
                    $('#dok_expired').val(lastExpired[jenis]);
                    hitungSisa(lastExpired[jenis], '#dok_sisa');
                    $('#dok_expired_hint').html('<span class="text-info"><i class="fas fa-info-circle"></i> Pre-filled dari data ' + jenis.toUpperCase() + ' terakhir. Ubah jika ini perpanjangan baru.</span>');
                } else {
                    $('#dok_expired_hint').html('');
                    if (!isEdit) { $('#dok_expired').val(''); $('#dok_sisa').html(''); }
                }
            });

            // ── Auto hitung total BBM ──
            function hitungTotal() {
                const liter = parseFloat($('#bbm_liter').val()) || 0;
                const harga = parseFloat($('#bbm_harga').val()) || 0;
                if (liter > 0 && harga > 0) $('#bbm_total').val(Math.round(liter * harga));
            }
            $('#bbm_liter, #bbm_harga').on('input keyup change', hitungTotal);

            // ── Buka modal Tambah ──
            $('#btnTambahDok').on('click', function () {
                $('#modalDokTitle').html('<i class="fas fa-plus-circle"></i> Tambah Dokumen');
                $('#formDok')[0].reset();
                $('#dok_id').val('');
                $('#formDok').attr('action', '<?= base_url('unit_document/proses_tambah') ?>');
                $('#dok_status_wrap').hide();
                $('#dok_file_current').hide();
                $('#dok_sisa, #dok_expired_hint').html('');
                $('#modalTambahDok').modal('show');
            });

            $('#btnTambahService').on('click', function () {
                $('#modalServiceTitle').html('<i class="fas fa-plus"></i> Tambah Histori Service');
                $('#formService')[0].reset();
                $('#service_id').val('');
                $('#formService').attr('action', '<?= base_url('unit_maintenance/proses_tambah') ?>');
                $('#svc_km').val('<?= $u->current_km ?? '' ?>');
                $('#svc_bukti_current').hide();
                $('#svc_tanggal').val('<?= date('Y-m-d') ?>');
                $('#modalTambahService').modal('show');
            });

            $('#btnTambahBBM').on('click', function () {
                $('#modalBBMTitle').html('<i class="fas fa-plus"></i> Catat Pengisian BBM');
                $('#formBBM')[0].reset();
                $('#fuel_id').val('');
                $('#formBBM').attr('action', '<?= base_url('unit_fuel/proses_tambah') ?>');
                $('#bbm_km').val('<?= $u->current_km ?? '' ?>');
                $('#bbm_struk_current').hide();
                $('#bbm_tanggal').val('<?= date('Y-m-d') ?>');
                $('#bbm_waktu').val('<?= date('H:i') ?>');
                $('#bbm_total, #bbm_konsumsi').val('');
                $('#bbm_km_hint').html('');
                $('#modalTambahBBM').modal('show');
            });

            // ── Edit Dokumen ──
            $(document).on('click', '.btn-edit-dok', function () {
                const d = $(this).data();
                $('#modalDokTitle').html('<i class="fas fa-edit"></i> Ubah Dokumen');
                $('#dok_id').val(d.id);
                $('#dok_jenis').val(d.jenis);
                $('#dok_nomor').val(d.nomor);
                $('#dok_terbit').val(d.terbit);
                $('#dok_expired').val(d.expired);
                $('#dok_biaya').val(d.biaya);
                $('#dok_status').val(d.status);
                $('#dok_reminder').val(d.reminder);
                $('#dok_keterangan').val(d.keterangan);
                $('#dok_status_wrap').show();
                $('#dok_expired_hint').html('');
                if (d.file) {
                    $('#dok_file_current').show();
                    $('#dok_file_link').attr('href', '<?= base_url('uploads/unit_documents/') ?>' + d.file);
                } else { $('#dok_file_current').hide(); }
                hitungSisa(d.expired, '#dok_sisa');
                $('#formDok').attr('action', '<?= base_url('unit_document/proses_ubah/') ?>' + d.id);
                $('#modalTambahDok').modal('show');
            });

            // ── Edit Service ──
            $(document).on('click', '.btn-edit-service', function () {
                const d = $(this).data();
                $('#modalServiceTitle').html('<i class="fas fa-edit"></i> Ubah Histori Service');
                $('#service_id').val(d.id);
                $('#svc_tanggal').val(d.tanggal);
                $('#svc_jenis').val(d.jenis);
                $('#svc_km').val(d.km);
                $('#svc_next_km').val(d['next-km']);
                $('#svc_bengkel').val(d.bengkel);
                $('#svc_teknisi').val(d.teknisi);
                $('#svc_biaya').val(d.biaya);
                $('#svc_parts').val(d.parts);
                $('#svc_keterangan').val(d.keterangan);
                if (d.bukti) {
                    $('#svc_bukti_current').show();
                    $('#svc_bukti_link').attr('href', '<?= base_url('uploads/unit_service/') ?>' + d.bukti);
                }
                $('#formService').attr('action', '<?= base_url('unit_maintenance/proses_ubah/') ?>' + d.id);
                $('#modalTambahService').modal('show');
            });

            // ── Edit BBM ──
            $(document).on('click', '.btn-edit-bbm', function () {
                const d = $(this).data();
                $('#modalBBMTitle').html('<i class="fas fa-edit"></i> Ubah Data BBM');
                $('#fuel_id').val(d.id);
                $('#bbm_tanggal').val(d.tanggal);
                $('#bbm_waktu').val(d.waktu);
                $('#bbm_jenis').val(d.jenis);
                $('#bbm_liter').val(d.liter);
                $('#bbm_harga').val(d.harga);
                $('#bbm_total').val(d.total);
                $('#bbm_km').val(d.km);
                $('#bbm_konsumsi').val(d.konsumsi || '');
                $('#bbm_driver').val(d.driver);
                $('#bbm_spbu').val(d.spbu);
                $('#bbm_lokasi').val(d.lokasi);
                $('#bbm_keterangan').val(d.keterangan);
                if (d.kmterakhir)
                    $('#bbm_km_hint').html('📍 KM terakhir tercatat: <strong>' + d.kmterakhir + '</strong> km');
                else
                    $('#bbm_km_hint').html('<span class="text-warning">⚠️ KM terakhir tidak ada, isi konsumsi manual jika perlu</span>');
                if (d.struk) {
                    $('#bbm_struk_current').show();
                    $('#bbm_struk_link').attr('href', '<?= base_url('uploads/unit_bbm/') ?>' + d.struk);
                } else { $('#bbm_struk_current').hide(); }
                $('#formBBM').attr('action', '<?= base_url('unit_fuel/proses_ubah/') ?>' + d.id);
                $('#modalTambahBBM').modal('show');
            });

            // ── Hapus ──
            $(document).on('click', '.btn-hapus-dok', function () {
                $('#hapus_msg').text('Yakin hapus dokumen ' + $(this).data('jenis') + ' ini?');
                $('#hapus_url').attr('href', '<?= base_url('unit_document/hapus/') ?>' + $(this).data('id'));
                $('#modalHapus').modal('show');
            });

            $(document).on('click', '.btn-hapus-service', function () {
                $('#hapus_msg').text('Yakin hapus data service ini?');
                $('#hapus_url').attr('href', '<?= base_url('unit_maintenance/hapus/') ?>' + $(this).data('id'));
                $('#modalHapus').modal('show');
            });

            $(document).on('click', '.btn-hapus-bbm', function () {
                $('#hapus_msg').text('Yakin hapus data pengisian BBM ini?');
                $('#hapus_url').attr('href', '<?= base_url('unit_fuel/hapus/') ?>' + $(this).data('id'));
                $('#modalHapus').modal('show');
            });

            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
        });
    </script>
</body>

</html>