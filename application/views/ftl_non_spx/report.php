<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan FTL Non SPX</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }

        /* HEADER */
        .report-header {
            text-align: center;
            border-bottom: 3px solid #4e73df;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .report-header h2 {
            font-size: 16px;
            font-weight: bold;
            color: #4e73df;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .report-header p {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }

        /* SUMMARY STATS */
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary-box {
            display: table-cell;
            width: 12.5%;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }
        .summary-box .num {
            font-size: 16px;
            font-weight: bold;
            line-height: 1;
        }
        .summary-box .lbl {
            font-size: 7px;
            text-transform: uppercase;
            color: #666;
            margin-top: 2px;
        }
        .bg-primary   { background: #4e73df; color: white; }
        .bg-secondary { background: #858796; color: white; }
        .bg-dark      { background: #5a5c69; color: white; }
        .bg-info      { background: #36b9cc; color: white; }
        .bg-primary2  { background: #224abe; color: white; }
        .bg-warning   { background: #f6c23e; color: #333; }
        .bg-purple    { background: #6f42c1; color: white; }
        .bg-success   { background: #1cc88a; color: white; }
        .bg-danger    { background: #e74a3b; color: white; }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        thead tr {
            background: #4e73df;
            color: white;
        }
        thead th {
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #3a5cc9;
        }
        tbody tr {
            border-bottom: 1px solid #e3e6f0;
        }
        tbody tr:nth-child(even) {
            background: #f8f9fc;
        }
        tbody td {
            padding: 5px 4px;
            vertical-align: middle;
            border: 1px solid #e3e6f0;
            font-size: 8.5px;
        }
        .text-center { text-align: center; }

        /* STATUS BADGES */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-scheduled      { background: #858796; color: white; }
        .badge-sourcing       { background: #5a5c69; color: white; }
        .badge-loading        { background: #36b9cc; color: white; }
        .badge-on_trip        { background: #4e73df; color: white; }
        .badge-tiba_muat      { background: #f6c23e; color: #333; }
        .badge-tiba_bongkar   { background: #6f42c1; color: white; }
        .badge-completed      { background: #1cc88a; color: white; }
        .badge-cancelled      { background: #e74a3b; color: white; }

        /* FOOTER */
        .report-footer {
            margin-top: 16px;
            border-top: 1px solid #dee2e6;
            padding-top: 8px;
            font-size: 8px;
            color: #888;
        }
        .footer-left  { float: left; }
        .footer-right { float: right; text-align: right; }
        .clearfix::after { content: ''; display: table; clear: both; }

        /* NO DATA */
        .no-data {
            text-align: center;
            padding: 20px;
            color: #aaa;
            font-style: italic;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="report-header">
        <h2>Laporan FTL Non SPX</h2>
        <p>Dicetak pada: <?= date('d F Y, H:i') ?> WIB &nbsp;|&nbsp; Total: <?= count($shipments ?? []) ?> Shipment</p>
    </div>

    <!-- SUMMARY STATS -->
    <?php
    $shipments = $shipments ?? [];
    $total        = count($shipments);
    $scheduled    = 0; $sourcing = 0; $loading = 0; $on_trip = 0;
    $tiba_muat    = 0; $tiba_bongkar = 0; $completed = 0; $cancelled = 0;

    foreach ($shipments as $s) {
        switch ($s->status_shipment ?? '') {
            case 'Scheduled':              $scheduled++;    break;
            case 'Sourcing Vendor':        $sourcing++;     break;
            case 'Loading':                $loading++;      break;
            case 'On Trip':                $on_trip++;      break;
            case 'Tiba di Lokasi Muat':    $tiba_muat++;    break;
            case 'Tiba di Lokasi Bongkar': $tiba_bongkar++; break;
            case 'Completed':              $completed++;    break;
            case 'Cancelled':              $cancelled++;    break;
        }
    }
    ?>

    <div class="summary-row">
        <div class="summary-box bg-primary">
            <div class="num"><?= $total ?></div>
            <div class="lbl">Total</div>
        </div>
        <div class="summary-box bg-secondary">
            <div class="num"><?= $scheduled ?></div>
            <div class="lbl">Scheduled</div>
        </div>
        <div class="summary-box bg-dark">
            <div class="num"><?= $sourcing ?></div>
            <div class="lbl">Sourcing</div>
        </div>
        <div class="summary-box bg-info">
            <div class="num"><?= $loading ?></div>
            <div class="lbl">Loading</div>
        </div>
        <div class="summary-box bg-primary2">
            <div class="num"><?= $on_trip ?></div>
            <div class="lbl">On Trip</div>
        </div>
        <div class="summary-box bg-warning">
            <div class="num"><?= $tiba_muat ?></div>
            <div class="lbl">Tiba Muat</div>
        </div>
        <div class="summary-box bg-purple">
            <div class="num"><?= $tiba_bongkar ?></div>
            <div class="lbl">Tiba Bongkar</div>
        </div>
        <div class="summary-box bg-success">
            <div class="num"><?= $completed ?></div>
            <div class="lbl">Completed</div>
        </div>
    </div>

    <!-- TABLE -->
    <?php if (empty($shipments)): ?>
        <div class="no-data">Tidak ada data shipment.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="7%">No Shipment</th>
                <th width="12%">Customer</th>
                <th width="8%">Origin</th>
                <th width="10%">Dest 1</th>
                <th width="8%">Dest 2</th>
                <th width="6%">Truck</th>
                <th width="10%">Vendor</th>
                <th width="7%">Nopol</th>
                <th width="8%">Driver</th>
                <th width="9%">Target Standby</th>
                <th width="9%">Target Arrival</th>
                <th width="8%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($shipments as $s):
                $status = $s->status_shipment ?? 'Scheduled';
                $badge_map = [
                    'Scheduled'              => 'scheduled',
                    'Sourcing Vendor'        => 'sourcing',
                    'Loading'                => 'loading',
                    'On Trip'                => 'on_trip',
                    'Tiba di Lokasi Muat'    => 'tiba_muat',
                    'Tiba di Lokasi Bongkar' => 'tiba_bongkar',
                    'Completed'              => 'completed',
                    'Cancelled'              => 'cancelled',
                ];
                $badge_class = $badge_map[$status] ?? 'scheduled';
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center"><strong><?= htmlspecialchars($s->no_shipment ?? '') ?></strong></td>
                <td><?= htmlspecialchars($s->nama_customer ?? '-') ?></td>
                <td><?= htmlspecialchars($s->origin ?? '-') ?></td>
                <td><?= htmlspecialchars($s->dest1 ?? '-') ?></td>
                <td><?= htmlspecialchars($s->dest2 ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($s->truck_type ?? '-') ?></td>
                <td><?= htmlspecialchars($s->nama_vendor ?? 'OWN UNIT') ?></td>
                <td class="text-center"><strong><?= htmlspecialchars($s->nopol ?? '-') ?></strong></td>
                <td>
                    <?= htmlspecialchars($s->driver ?? '-') ?>
                    <?php if (!empty($s->no_hp)): ?>
                        <br><span style="color:#888; font-size:7.5px;"><?= htmlspecialchars($s->no_hp) ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php if (!empty($s->target_standby_date)): ?>
                        <?= date('d/m/Y', strtotime($s->target_standby_date)) ?>
                        <?php if (!empty($s->target_standby_time)): ?>
                            <br><?= substr($s->target_standby_time, 0, 5) ?>
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php if (!empty($s->target_arrival_date)): ?>
                        <?= date('d/m/Y', strtotime($s->target_arrival_date)) ?>
                        <?php if (!empty($s->target_arrival_time)): ?>
                            <br><?= substr($s->target_arrival_time, 0, 5) ?>
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <span class="badge badge-<?= $badge_class ?>">
                        <?= htmlspecialchars($status) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- FOOTER -->
    <div class="report-footer clearfix">
        <div class="footer-left">
            TSC — Laporan FTL Non SPX &nbsp;|&nbsp; <?= date('d/m/Y H:i') ?>
        </div>
        <div class="footer-right">
            Total Shipment: <strong><?= $total ?></strong> &nbsp;|&nbsp;
            Completed: <strong><?= $completed ?></strong> &nbsp;|&nbsp;
            On Trip: <strong><?= $on_trip ?></strong> &nbsp;|&nbsp;
            Cancelled: <strong><?= $cancelled ?></strong>
        </div>
    </div>

</body>
</html>