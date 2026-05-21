<?php defined('BASEPATH') OR exit('No direct script access allowed');

$open_rows = array_filter($results, fn($r) => strtolower($r['status']) === 'open');
usort($open_rows, function ($a, $b) {
    $wa = empty($a['vendor_tsc']) ? 0 : 1;
    $wb = empty($b['vendor_tsc']) ? 0 : 1;
    if ($wa !== $wb)
        return $wa - $wb;
    return strcmp($a['vendor_tsc'], $b['vendor_tsc']);
});
$not_found_rows = array_filter($open_rows, fn($r) => empty($r['vendor_tsc']));

$rows_by_vendor = [];
foreach ($open_rows as $r) {
    if (!empty($r['vendor_tsc'])) {
        $rows_by_vendor[$r['vendor_tsc']][] = $r;
    }
}
ksort($rows_by_vendor);
?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title mb-0">
                            <i class="fas fa-search text-warning"></i> Hasil Lookup —
                            <?= htmlspecialchars($sheet_name) ?>
                        </h1>
                        <small class="text-muted">
                            Masterdata: <strong><?= number_format($total_lt_csv) ?> LT Number</strong> total
                            <?php if (!empty($master_infos) && count($master_infos) > 1): ?>
                                dari <strong><?= count($master_infos) ?> file</strong>
                                (<?= implode(' + ', array_map(fn($m) => htmlspecialchars($m['name']), $master_infos)) ?>)
                            <?php elseif (!empty($master_infos)): ?>
                                dari <strong><?= htmlspecialchars($master_infos[0]['name']) ?></strong>
                            <?php endif ?>
                        </small>
                        <?php if (!empty($master_infos) && count($master_infos) > 1): ?>
                            <div class="mt-1">
                                <?php foreach ($master_infos as $info): ?>
                                    <span class="badge bg-primary me-1">
                                        <i class="fas fa-file-excel me-1"></i>
                                        <?= htmlspecialchars($info['name']) ?>
                                        <span class="ms-1 opacity-75">(<?= number_format($info['total_lt']) ?> LT)</span>
                                    </span>
                                <?php endforeach ?>
                            </div>
                        <?php endif ?>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('feedback') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <a href="<?= base_url('feedback/export') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-1"></i> Download Semua (Excel)
                        </a>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #4e73df">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total OPEN</div>
                                <div class="h4 mb-0 fw-bold"><?= number_format($total_open) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #1cc88a">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Vendor Ditemukan</div>
                                <div class="h4 mb-0 fw-bold text-success"><?= number_format($total_matched) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #e74a3b">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Tidak Ditemukan di
                                    Masterdata</div>
                                <div class="h4 mb-0 fw-bold text-danger"><?= number_format($total_not_found) ?></div>
                                <div class="text-xs text-muted">Upload masterdata bulan terkait</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #36b9cc">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">Match Rate</div>
                                <div class="h4 mb-0 fw-bold">
                                    <?= $total_open > 0 ? round($total_matched / $total_open * 100, 1) : 0 ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Legenda match -->
                <div class="alert alert-light border mb-3 py-2">
                    <small class="fw-semibold me-3">Keterangan warna:</small>
                    <span class="badge bg-success me-2">✅ via LT</span> match dari LT Number &nbsp;|&nbsp;
                    <span class="badge bg-warning text-dark me-2">🔶 via Nopol</span> LT miss, match dari Nopol
                    &nbsp;|&nbsp;
                    <span class="badge bg-danger me-2">❌ N/A</span> tidak ditemukan di masterdata manapun
                </div>

                <!-- Row: Summary Vendor + Detail Tidak Ditemukan -->
                <div class="row g-3 mb-4">

                    <!-- Summary per Vendor -->
                    <div class="col-lg-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3" style="background:#ed7d31;">
                                <h6 class="m-0 fw-bold text-white">
                                    <i class="fas fa-truck me-2"></i> Open Feedback per Vendor
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead style="background:#f9f0e8;">
                                            <tr>
                                                <th>#</th>
                                                <th>Vendor</th>
                                                <th class="text-center">Open</th>
                                                <th class="text-center">%</th>
                                                <th class="text-center">Download</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1;
                                            foreach ($vendor_summary as $vendor => $count): ?>
                                                <?php
                                                $is_warn = strpos($vendor, '⚠') !== false;
                                                $pct = $total_open > 0 ? round($count / $total_open * 100, 1) : 0;
                                                ?>
                                                <tr class="<?= $is_warn ? 'table-danger' : '' ?>">
                                                    <td><span class="badge bg-secondary"><?= $i++ ?></span></td>
                                                    <td class="fw-semibold small <?= $is_warn ? 'text-danger' : '' ?>">
                                                        <?= htmlspecialchars($vendor) ?>
                                                    </td>
                                                    <td class="text-center fw-bold"><?= $count ?></td>
                                                    <td class="text-center">
                                                        <span class="badge <?= $is_warn ? 'bg-danger' : 'bg-primary' ?>">
                                                            <?= $pct ?>%
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (!$is_warn): ?>
                                                            <a href="<?= base_url('feedback/export_vendor?vendor=' . urlencode($vendor)) ?>"
                                                                class="btn btn-xs btn-outline-success py-0 px-1"
                                                                title="Download Excel vendor ini">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        <?php endif ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($vendor_summary)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-3">Tidak ada data</td>
                                                </tr>
                                            <?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LT Tidak Ditemukan -->
                    <div class="col-lg-8">
                        <div class="card shadow">
                            <div class="card-header py-3 bg-danger text-white">
                                <h6 class="m-0 fw-bold">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Tidak Ditemukan di Masterdata (<?= count($not_found_rows) ?> rows)
                                    <?php if (count($not_found_rows) > 0): ?>
                                        <small class="fw-normal ms-2">— Upload masterdata bulan terkait untuk
                                            melengkapi</small>
                                    <?php endif ?>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:280px;overflow-y:auto">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-danger sticky-top">
                                            <tr>
                                                <th>LT Number</th>
                                                <th>Week</th>
                                                <th>Origin</th>
                                                <th>Destination</th>
                                                <th>Unit Type</th>
                                                <th>Nopol (SPX)</th>
                                                <th>Driver Name</th>
                                                <th>Follow Up</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($not_found_rows as $r): ?>
                                                <tr class="table-warning">
                                                    <td class="small fw-semibold font-monospace">
                                                        <?= htmlspecialchars($r['lt_number']) ?>
                                                    </td>
                                                    <td class="small"><?= htmlspecialchars($r['week']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['origin']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['destination']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['unit_type']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['nopol_spx']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['driver_spx']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['follow_up']) ?></td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($not_found_rows)): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-3">
                                                        <i class="fas fa-check-circle text-success me-1"></i>
                                                        Semua LT Number ditemukan di masterdata!
                                                    </td>
                                                </tr>
                                            <?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Tabel Detail All OPEN Feedback -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-table me-2"></i>
                            Detail OPEN Feedback + Vendor (<?= count($open_rows) ?> rows)
                        </h6>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="text" id="searchInput" class="form-control form-control-sm"
                                placeholder="Cari vendor, LT, origin..." style="width:220px;">
                            <a href="<?= base_url('feedback/export') ?>" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download me-1"></i> Export Excel
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:500px;overflow-y:auto">
                            <table class="table table-sm table-hover mb-0" id="resultTable">
                                <thead class="table-primary sticky-top">
                                    <tr>
                                        <th>Status</th>
                                        <th>LT Number</th>
                                        <th>Vendor (TSC)</th>
                                        <th>Nopol (TSC)</th>
                                        <th>Driver (TSC)</th>
                                        <th>Division</th>
                                        <th>Match Via</th>
                                        <th>Week</th>
                                        <th>Date</th>
                                        <th>Origin</th>
                                        <th>Destination</th>
                                        <th>Unit Type</th>
                                        <th>Follow Up</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($open_rows as $r): ?>
                                        <?php
                                        $is_warn = empty($r['vendor_tsc']);
                                        $via = $r['match_via'] ?? '';
                                        ?>
                                        <tr
                                            class="<?= $is_warn ? 'table-danger' : ($via === 'nopol' ? 'table-warning' : '') ?>">
                                            <td>
                                                <span class="badge bg-danger">OPEN</span>
                                            </td>
                                            <td class="small font-monospace"><?= htmlspecialchars($r['lt_number']) ?></td>
                                            <td class="fw-semibold small <?= $is_warn ? 'text-danger' : 'text-success' ?>">
                                                <?= $is_warn
                                                    ? '<i class="fas fa-times-circle me-1"></i>Tidak Ditemukan'
                                                    : htmlspecialchars($r['vendor_tsc']) ?>
                                            </td>
                                            <td class="small"><?= htmlspecialchars($r['nopol_tsc']) ?></td>
                                            <td class="small"><?= htmlspecialchars($r['driver_tsc']) ?></td>
                                            <td class="small"><?= htmlspecialchars($r['division']) ?></td>
                                            <td class="small text-center">
                                                <?php if ($via === 'lt'): ?>
                                                    <span class="badge bg-success">✅ LT</span>
                                                <?php elseif ($via === 'nopol'): ?>
                                                    <span class="badge bg-warning text-dark">🔶 Nopol</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">❌ N/A</span>
                                                <?php endif ?>
                                            </td>
                                            <td class="small text-center"><?= htmlspecialchars($r['week']) ?></td>
                                            <td class="small text-center"><?= htmlspecialchars($r['date']) ?></td>
                                            <td class="small"><?= htmlspecialchars($r['origin']) ?></td>
                                            <td class="small"><?= htmlspecialchars($r['destination']) ?></td>
                                            <td class="small"><?= htmlspecialchars($r['unit_type']) ?></td>
                                            <td class="small"><?= htmlspecialchars($r['follow_up']) ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SECTION PER VENDOR -->
                <?php if (!empty($rows_by_vendor)): ?>
                    <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-layer-group text-primary me-2"></i>
                            Detail per Vendor
                        </h5>
                        <span class="badge bg-primary"><?= count($rows_by_vendor) ?> vendor</span>
                        <small class="text-muted">— Klik tombol download di pojok kanan tiap section untuk export per
                            vendor</small>
                    </div>

                    <?php
                    $vendor_accent_colors = ['#2E75B6', '#70AD47', '#C00000', '#7030A0', '#C55A11', '#0070C0', '#375623', '#BF8F00', '#833C00'];
                    $vi = 0;
                    foreach ($rows_by_vendor as $vendor_name => $vendor_rows):
                        $accent = $vendor_accent_colors[$vi % count($vendor_accent_colors)];
                        $vi++;
                        $safe_id = 'vendor_' . preg_replace('/[^a-zA-Z0-9]/', '_', $vendor_name);
                        ?>
                        <div class="card shadow mb-3" id="section_<?= $safe_id ?>">
                            <div class="card-header py-2 d-flex justify-content-between align-items-center"
                                style="background:<?= $accent ?>;">
                                <h6 class="m-0 fw-bold text-white">
                                    <i class="fas fa-truck me-2"></i>
                                    <?= htmlspecialchars($vendor_name) ?>
                                    <span class="badge bg-white ms-2" style="color:<?= $accent ?>">
                                        <?= count($vendor_rows) ?> rows
                                    </span>
                                </h6>
                                <a href="<?= base_url('feedback/export_vendor?vendor=' . urlencode($vendor_name)) ?>"
                                    class="btn btn-sm btn-light fw-semibold" style="color:<?= $accent ?>">
                                    <i class="fas fa-download me-1"></i> Download Excel
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                                    <table class="table table-sm table-hover mb-0" id="tbl_<?= $safe_id ?>">
                                        <thead class="sticky-top"
                                            style="background:<?= $accent ?>20; border-bottom:2px solid <?= $accent ?>;">
                                            <tr>
                                                <th class="small">Date</th>
                                                <th class="small">LT Number</th>
                                                <th class="small">Vendor</th>
                                                <th class="small">Match</th>
                                                <th class="small">Origin Station</th>
                                                <th class="small">Destination</th>
                                                <th class="small">Sequence Trip</th>
                                                <th class="small">Unit Type</th>
                                                <th class="small">Data Nopol</th>
                                                <th class="small">Driver ID</th>
                                                <th class="small">Driver Name</th>
                                                <th class="small">Service Type Fulfillment</th>
                                                <th class="small">ATD Origin</th>
                                                <th class="small">STA Destination</th>
                                                <th class="small">ATA Destination</th>
                                                <th class="small">ETA Destination</th>
                                                <th class="small">Travel Time</th>
                                                <th class="small">SLA Target</th>
                                                <th class="small">Duration Late Lead Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($vendor_rows as $idx => $r): ?>
                                                <tr
                                                    class="<?= ($r['match_via'] ?? '') === 'nopol' ? 'table-warning' : ($idx % 2 === 1 ? 'table-light' : '') ?>">
                                                    <td class="small"><?= htmlspecialchars($r['date']) ?></td>
                                                    <td class="small font-monospace fw-semibold">
                                                        <?= htmlspecialchars($r['lt_number']) ?></td>
                                                    <td class="small fw-semibold text-success">
                                                        <?= htmlspecialchars($r['vendor_tsc']) ?></td>
                                                    <td class="small text-center">
                                                        <?= ($r['match_via'] ?? '') === 'nopol'
                                                            ? '<span class="badge bg-warning text-dark">🔶 Nopol</span>'
                                                            : '<span class="badge bg-success">✅ LT</span>' ?>
                                                    </td>
                                                    <td class="small"><?= htmlspecialchars($r['origin']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['destination']) ?></td>
                                                    <td class="small text-center"><?= htmlspecialchars($r['sequence']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['unit_type']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['nopol_spx']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['driver_id']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['driver_spx']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['service_type']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['atd_origin']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['sta_dest']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['ata_dest']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['eta_dest']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['travel_time']) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r['sla_target']) ?></td>
                                                    <td class="small fw-semibold text-danger">
                                                        <?= htmlspecialchars($r['duration_late']) ?></td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                <?php endif ?>

            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#resultTable tbody tr').forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>