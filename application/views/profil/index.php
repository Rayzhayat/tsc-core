<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title mb-0">
                            <i class="fas fa-user-circle text-primary me-2"></i> Profil Saya
                        </h1>
                        <small class="text-muted">Informasi akun, cuti, dan performa kehadiran Anda</small>
                    </div>
                </div>

                <div class="row g-3">

                    <!-- KARTU PROFIL -->
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <img src="<?= base_url('uploads/profil/' . $pengguna->foto_profil) ?>"
                                    class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;">
                                <h4 class="mb-0"><?= htmlspecialchars($pengguna->nama) ?></h4>
                                <div class="text-muted small mb-2">NIK: <?= htmlspecialchars($pengguna->nik) ?></div>
                                <span class="badge bg-primary"><?= htmlspecialchars($pengguna->user_level) ?></span>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text-muted">Golongan</span>
                                    <span><?= htmlspecialchars($pengguna->golongan ?? '-') ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text-muted">Status</span>
                                    <span><?= htmlspecialchars($pengguna->status_kepegawaian ?? '-') ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text-muted">Group</span>
                                    <span><?= htmlspecialchars($pengguna->group_karyawan ?? '-') ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text-muted">Tanggal Join</span>
                                    <span><?= $pengguna->tanggal_join ? date('d M Y', strtotime($pengguna->tanggal_join)) : '-' ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- SISA CUTI + PERFORMA -->
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-3">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i> Cuti Saya
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $jatah = (int) $pengguna->jatah_cuti;
                                $sisa = (int) $pengguna->sisa_cuti;
                                $terpakai = $jatah - $sisa;
                                $persen = $jatah > 0 ? round(($terpakai / $jatah) * 100) : 0;
                                ?>
                                <div class="row text-center mb-3">
                                    <div class="col">
                                        <div class="fs-2 fw-bold text-success"><?= $sisa ?></div>
                                        <div class="text-muted small">Sisa Cuti</div>
                                    </div>
                                    <div class="col">
                                        <div class="fs-2 fw-bold"><?= $terpakai ?></div>
                                        <div class="text-muted small">Terpakai</div>
                                    </div>
                                    <div class="col">
                                        <div class="fs-2 fw-bold text-muted"><?= $jatah ?></div>
                                        <div class="text-muted small">Jatah / Tahun</div>
                                    </div>
                                </div>
                                <div class="progress mb-3" style="height:8px;">
                                    <div class="progress-bar bg-success" style="width:<?= $persen ?>%"></div>
                                </div>

                                <h6 class="mt-4">Riwayat Pengajuan</h6>
                                <?php if (empty($cuti_list)): ?>
                                    <div class="text-muted small">Belum ada pengajuan cuti.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Jumlah</th>
                                                    <th>Alasan</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($cuti_list as $c): ?>
                                                    <tr>
                                                        <td><?= date('d M Y', strtotime($c->tanggal_mulai)) ?> -
                                                            <?= date('d M Y', strtotime($c->tanggal_selesai)) ?></td>
                                                        <td><?= $c->jumlah_hari ?> hari</td>
                                                        <td><?= htmlspecialchars($c->alasan) ?></td>
                                                        <td>
                                                            <?php
                                                            $color = $c->status == 'Disetujui' ? 'success' : ($c->status == 'Ditolak' ? 'danger' : 'warning');
                                                            ?>
                                                            <span class="badge bg-<?= $color ?>"><?= $c->status ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>

                        <?php if ($performa): ?>
                            <div class="card shadow-sm">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold">
                                        <i class="fas fa-chart-line me-2 text-primary"></i> Performa Kehadiran
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="fs-3 fw-bold"><?= $performa->persen_kehadiran ?? 0 ?>%</div>
                                    <div class="text-muted small">Persentase kehadiran</div>
                                </div>
                            </div>
                        <?php endif ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>