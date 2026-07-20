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
                            <i class="fas fa-truck-loading text-primary me-2"></i> Vendor Performance Score
                        </h1>
                        <small class="text-muted">Reliability dan coverage tiap vendor — sering cancel/unfulfill atau
                            andal</small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('analytics') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chart-bar me-1"></i> Analytics Dashboard
                        </a>
                        <a href="<?= base_url('vendor_performance/export?' . http_build_query($filters)) ?>"
                            class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-csv me-1"></i> Export CSV
                        </a>
                    </div>
                </div>

                <!-- Summary Cards -->
                <?php $s = $summary_stats; ?>
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #6c757d !important">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(108,117,125,.15)">
                                    <i class="fas fa-truck text-secondary fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Total Vendor</div>
                                    <div class="h4 mb-0 fw-bold"><?= number_format($s['total']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #1cc88a !important">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(28,200,138,.15)">
                                    <i class="fas fa-shield-alt text-success fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Andal</div>
                                    <div class="h4 mb-0 fw-bold text-success"><?= $s['andal'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f6c23e !important">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(246,194,62,.15)">
                                    <i class="fas fa-exclamation-circle text-warning fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Cukup</div>
                                    <div class="h4 mb-0 fw-bold text-warning"><?= $s['cukup'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #e74a3b !important">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(231,74,59,.15)">
                                    <i class="fas fa-times-circle text-danger fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Bermasalah</div>
                                    <div class="h4 mb-0 fw-bold text-danger"><?= $s['bermasalah'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body py-2">
                        <form method="GET" action="<?= base_url('vendor_performance') ?>"
                            class="row g-2 align-items-end">
                            <div class="col-md-3 col-6">
                                <label class="form-label small mb-1 fw-semibold">Sheet Type</label>
                                <select name="sheet_type" class="form-select form-select-sm">
                                    <option value="">Semua Sheet</option>
                                    <?php foreach ($sheet_types as $st): ?>
                                        <option value="<?= $st->sheet_type ?>" <?= $filters['sheet_type'] == $st->sheet_type ? 'selected' : '' ?>>
                                            <?= $st->sheet_type ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label small mb-1 fw-semibold">Reliability</label>
                                <select name="reliability" class="form-select form-select-sm">
                                    <option value="">Semua Status</option>
                                    <option value="andal" <?= $filters['reliability'] == 'andal' ? 'selected' : '' ?>>🟢
                                        Andal</option>
                                    <option value="cukup" <?= $filters['reliability'] == 'cukup' ? 'selected' : '' ?>>🟡
                                        Cukup</option>
                                    <option value="bermasalah" <?= $filters['reliability'] == 'bermasalah' ? 'selected' : '' ?>>🔴 Bermasalah</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-8">
                                <label class="form-label small mb-1 fw-semibold">Cari Vendor</label>
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Nama vendor..." value="<?= htmlspecialchars($filters['search']) ?>">
                            </div>
                            <div class="col-md-2 col-4">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="<?= base_url('vendor_performance') ?>"
                                        class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold">
                            <i class="fas fa-table me-2 text-primary"></i>
                            Daftar Vendor
                            <span class="badge bg-secondary ms-2"><?= count($vendors) ?></span>
                        </h6>
                        <small class="text-muted">Klik nama vendor untuk detail</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0" id="vendorTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:36px">#</th>
                                        <th>Vendor</th>
                                        <th class="text-center">Trip</th>
                                        <th class="text-center">Customer</th>
                                        <th class="text-center">Rute</th>
                                        <th class="text-end">Total Cost</th>
                                        <th class="text-center">Unfulfill%</th>
                                        <th class="text-center">Reliability</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($vendors)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center py-5 text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                                Belum ada data vendor
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($vendors as $i => $v): ?>
                                            <?php
                                            $rel_cfg = [
                                                'andal' => ['badge' => 'bg-success', 'icon' => 'shield-alt', 'label' => 'Andal'],
                                                'cukup' => ['badge' => 'bg-warning text-dark', 'icon' => 'exclamation-circle', 'label' => 'Cukup'],
                                                'bermasalah' => ['badge' => 'bg-danger', 'icon' => 'times-circle', 'label' => 'Bermasalah'],
                                            ];
                                            $rc = $rel_cfg[$v->reliability_label] ?? $rel_cfg['bermasalah'];
                                            ?>
                                            <tr>
                                                <td class="text-muted small"><?= $i + 1 ?></td>
                                                <td>
                                                    <a href="<?= base_url('vendor_performance/detail/' . rawurlencode($v->vendor)) ?>"
                                                        class="fw-semibold text-decoration-none">
                                                        <?= htmlspecialchars($v->vendor) ?>
                                                    </a>
                                                    <?php if ($v->last_trip): ?>
                                                        <div class="text-muted" style="font-size:.7rem">
                                                            Terakhir: <?= date('d M Y', strtotime($v->last_trip)) ?>
                                                        </div>
                                                    <?php endif ?>
                                                </td>
                                                <td class="text-center fw-bold"><?= number_format($v->total_trip) ?></td>
                                                <td class="text-center"><?= $v->total_customer ?></td>
                                                <td class="text-center"><?= $v->total_rute ?></td>
                                                <td class="text-end small">Rp <?= number_format($v->total_cost, 0, ',', '.') ?>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="<?= $v->unfulfill_pct >= 10 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                                        <?= $v->unfulfill_pct ?>%
                                                    </span>
                                                    <div class="text-muted" style="font-size:.65rem">
                                                        (<?= $v->total_unfulfill ?>)</div>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="fw-bold <?= $v->reliability_score >= 90 ? 'text-success' : ($v->reliability_score >= 70 ? 'text-warning' : 'text-danger') ?>">
                                                        <?= $v->reliability_score ?>
                                                    </span>
                                                    <div class="progress mt-1" style="height:3px;width:60px;margin:0 auto">
                                                        <div class="progress-bar <?= $v->reliability_score >= 90 ? 'bg-success' : ($v->reliability_score >= 70 ? 'bg-warning' : 'bg-danger') ?>"
                                                            style="width:<?= min($v->reliability_score, 100) ?>%"></div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge <?= $rc['badge'] ?> px-2 py-1">
                                                        <i class="fas fa-<?= $rc['icon'] ?> me-1"></i> <?= $rc['label'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?= base_url('vendor_performance/detail/' . rawurlencode($v->vendor)) ?>"
                                                        class="btn btn-xs btn-outline-primary"
                                                        style="font-size:.7rem;padding:2px 8px">
                                                        <i class="fas fa-eye me-1"></i> Detail
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

                <!-- Legend -->
                <div class="mt-3 p-3 bg-light rounded small text-muted">
                    <strong class="text-dark">Cara baca Reliability Score:</strong>
                    <span class="mx-3">🟢 <strong>Andal</strong> — Unfulfill &lt; 10% (score ≥ 90)</span>
                    <span class="mx-3">🟡 <strong>Cukup</strong> — Unfulfill 10–30% (score 70–89)</span>
                    <span class="mx-3">🔴 <strong>Bermasalah</strong> — Unfulfill &gt; 30% (score &lt; 70)</span>
                    <br>
                    <span class="mx-3 d-inline-block mt-1">Vendor "OWN UNIT" tidak ditampilkan di sini — itu unit
                        internal, sudah ada modulnya sendiri di menu Unit Internal.</span>
                </div>

            </div>
        </div>
    </div>
</div>