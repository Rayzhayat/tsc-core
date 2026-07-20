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
                            <i class="fas fa-heartbeat text-danger me-2"></i> Customer Health Score
                        </h1>
                        <small class="text-muted">Monitor performa dan kesehatan setiap customer secara
                            real-time</small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('analytics') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chart-bar me-1"></i> Analytics Dashboard
                        </a>
                        <a href="<?= base_url('customer_health/export?' . http_build_query($filters)) ?>"
                            class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-csv me-1"></i> Export CSV
                        </a>
                    </div>
                </div>

                <!-- Summary Cards -->
                <?php $s = $summary_stats; ?>
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100"
                            style="border-left: 4px solid #6c757d !important; border-left-width: 4px !important;">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(108,117,125,.15)">
                                    <i class="fas fa-users text-secondary fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Total Customer</div>
                                    <div class="h4 mb-0 fw-bold"><?= number_format($s['total']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #1cc88a !important;">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(28,200,138,.15)">
                                    <i class="fas fa-check-circle text-success fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Sehat</div>
                                    <div class="h4 mb-0 fw-bold text-success"><?= $s['sehat'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f6c23e !important;">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(246,194,62,.15)">
                                    <i class="fas fa-exclamation-circle text-warning fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Perlu Perhatian</div>
                                    <div class="h4 mb-0 fw-bold text-warning"><?= $s['perlu_perhatian'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #e74a3b !important;">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:46px;height:46px;background:rgba(231,74,59,.15)">
                                    <i class="fas fa-times-circle text-danger fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase"
                                        style="font-size:.7rem;letter-spacing:.05em">Kritis</div>
                                    <div class="h4 mb-0 fw-bold text-danger"><?= $s['kritis'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body py-2">
                        <form method="GET" action="<?= base_url('customer_health') ?>" class="row g-2 align-items-end">
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
                                <label class="form-label small mb-1 fw-semibold">Health Status</label>
                                <select name="health" class="form-select form-select-sm">
                                    <option value="">Semua Status</option>
                                    <option value="sehat" <?= $filters['health'] == 'sehat' ? 'selected' : '' ?>>🟢 Sehat
                                    </option>
                                    <option value="perlu_perhatian" <?= $filters['health'] == 'perlu_perhatian' ? 'selected' : '' ?>>🟡 Perlu Perhatian</option>
                                    <option value="kritis" <?= $filters['health'] == 'kritis' ? 'selected' : '' ?>>🔴
                                        Kritis</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-8">
                                <label class="form-label small mb-1 fw-semibold">Cari Customer</label>
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Nama customer..." value="<?= htmlspecialchars($filters['search']) ?>">
                            </div>
                            <div class="col-md-2 col-4">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="<?= base_url('customer_health') ?>"
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
                            Daftar Customer
                            <span class="badge bg-secondary ms-2"><?= count($customers) ?></span>
                        </h6>
                        <small class="text-muted">Klik nama customer untuk detail</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0" id="customerTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:36px">#</th>
                                        <th>Customer</th>
                                        <th>Sheet</th>
                                        <th class="text-center">Shipment</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Margin</th>
                                        <th class="text-center">Margin%</th>
                                        <th class="text-center">Unfulfill%</th>
                                        <th class="text-center">Pending (Real)</th>
                                        <th class="text-center">Belum Diisi</th>
                                        <th class="text-center">Health</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($customers)): ?>
                                        <tr>
                                            <td colspan="12" class="text-center py-5 text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                                Belum ada data customer
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($customers as $i => $c): ?>
                                            <?php
                                            $health_cfg = [
                                                'sehat' => ['badge' => 'bg-success', 'icon' => 'check-circle', 'label' => 'Sehat'],
                                                'perlu_perhatian' => ['badge' => 'bg-warning text-dark', 'icon' => 'exclamation-circle', 'label' => 'Perlu Perhatian'],
                                                'kritis' => ['badge' => 'bg-danger', 'icon' => 'times-circle', 'label' => 'Kritis'],
                                            ];
                                            $hc = $health_cfg[$c->health_status] ?? $health_cfg['kritis'];
                                            ?>
                                            <tr>
                                                <td class="text-muted small"><?= $i + 1 ?></td>
                                                <td>
                                                    <a href="<?= base_url('customer_health/detail/' . rawurlencode($c->customer)) ?>"
                                                        class="fw-semibold text-decoration-none">
                                                        <?= htmlspecialchars($c->customer) ?>
                                                    </a>
                                                    <?php if ($c->last_shipment): ?>
                                                        <div class="text-muted" style="font-size:.7rem">
                                                            Terakhir: <?= date('d M Y', strtotime($c->last_shipment)) ?>
                                                        </div>
                                                    <?php endif ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary" style="font-size:.65rem">
                                                        <?= $c->sheet_type ?>
                                                    </span>
                                                </td>
                                                <td class="text-center fw-bold"><?= number_format($c->total_shipment) ?></td>
                                                <td class="text-end small">
                                                    Rp <?= number_format($c->total_revenue, 0, ',', '.') ?>
                                                </td>
                                                <td
                                                    class="text-end small fw-semibold <?= $c->total_margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                    Rp <?= number_format($c->total_margin, 0, ',', '.') ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                                        <span
                                                            class="fw-bold <?= $c->margin_pct >= 0 ? ($c->margin_pct > 10 ? 'text-success' : 'text-warning') : 'text-danger' ?>">
                                                            <?= $c->margin_pct ?>%
                                                        </span>
                                                    </div>
                                                    <div class="progress mt-1" style="height:3px;width:60px;margin:0 auto">
                                                        <div class="progress-bar <?= $c->margin_pct > 10 ? 'bg-success' : ($c->margin_pct > 0 ? 'bg-warning' : 'bg-danger') ?>"
                                                            style="width:<?= min(abs($c->margin_pct), 100) ?>%"></div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="<?= $c->unfulfill_pct >= 10 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                                        <?= $c->unfulfill_pct ?>%
                                                    </span>
                                                    <div class="text-muted" style="font-size:.65rem">
                                                        (<?= $c->total_unfulfill ?>)</div>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="<?= $c->pending_real_pct >= 20 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                                        <?= $c->pending_real_pct ?>%
                                                    </span>
                                                    <div class="text-muted" style="font-size:.65rem">
                                                        (<?= $c->pending_real ?>)</div>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="<?= $c->belum_diisi_pct >= 50 ? 'text-warning fw-semibold' : 'text-muted' ?>">
                                                        <?= $c->belum_diisi_pct ?>%
                                                    </span>
                                                    <div class="text-muted" style="font-size:.65rem">
                                                        (<?= $c->belum_diisi ?>)</div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge <?= $hc['badge'] ?> px-2 py-1">
                                                        <i class="fas fa-<?= $hc['icon'] ?> me-1"></i>
                                                        <?= $hc['label'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?= base_url('customer_health/detail/' . rawurlencode($c->customer) . '?sheet_type=' . urlencode($c->sheet_type)) ?>"
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
                    <strong class="text-dark">Cara baca Health Score:</strong>
                    <span class="mx-3">🟢 <strong>Sehat</strong> — Margin &gt;10%, Unfulfill &lt;10%, Pending Real
                        &lt;20%</span>
                    <span class="mx-3">🟡 <strong>Perlu Perhatian</strong> — 1 metrik di luar threshold</span>
                    <span class="mx-3">🔴 <strong>Kritis</strong> — Margin negatif atau 2+ metrik bermasalah</span>
                    <br class="d-none d-md-block">
                    <span class="mx-3 mt-1 d-inline-block">⚪ <strong>"Belum Diisi"</strong> = status pembayaran kosong
                        di data, <em>bukan</em> berarti pending — cuma indikator kerajinan tim finance update sheet.
                        Tidak ikut menghitung Health Score.</span>
                </div>

            </div>
        </div>
    </div>
</div>