<!-- ============================================================
     FILE: report.php  (template PDF Dompdf)
     PATH: application/views/daily_rent/report.php
     ============================================================ -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            margin: 0;
        }

        h2 {
            text-align: center;
            font-size: 13px;
            margin-bottom: 4px;
            color: #4e73df;
        }

        .sub {
            text-align: center;
            font-size: 8px;
            color: #888;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #4e73df;
            color: #fff;
            padding: 5px 4px;
            font-size: 8px;
            text-align: center;
        }

        td {
            padding: 4px 4px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }

        tr:nth-child(even) td {
            background: #f8f9fc;
        }

        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .b-dark {
            background: #343a40;
            color: #fff;
        }

        .b-sec {
            background: #6c757d;
            color: #fff;
        }

        .b-primary {
            background: #4e73df;
            color: #fff;
        }

        .b-warning {
            background: #ffc107;
            color: #212529;
        }

        .b-success {
            background: #28a745;
            color: #fff;
        }

        .b-danger {
            background: #dc3545;
            color: #fff;
        }

        .footer {
            font-size: 7px;
            color: #888;
            text-align: right;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <h2>Laporan Daily Rent — PT Tata Sanjaya Cakrawala</h2>
    <div class="sub">Dicetak: <?= date('d/m/Y H:i') ?> | Total: <?= count($rents ?? []) ?> Order</div>
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="9%">No Rent</th>
                <th width="14%">Customer</th>
                <th width="12%">Vendor</th>
                <th width="10%">PIC</th>
                <th width="12%">Lokasi</th>
                <th width="8%">Mulai</th>
                <th width="8%">Selesai</th>
                <th width="5%">Dur.</th>
                <th width="9%">Status</th>
                <th width="10%">Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $color_map = [
                'Sourcing Vendor' => 'b-dark',
                'Scheduled' => 'b-sec',
                'Active' => 'b-primary',
                'Partially Returned' => 'b-warning',
                'Completed' => 'b-success',
                'Cancelled' => 'b-danger'
            ];
            $no = 1;
            foreach ($rents ?? [] as $r):
                $dur = (!empty($r->rent_start_date) && !empty($r->rent_end_date))
                    ? round((strtotime($r->rent_end_date) - strtotime($r->rent_start_date)) / 86400) . 'h' : '-';
                $bc = $color_map[$r->status_rent ?? ''] ?? 'b-sec';
                ?>
                <tr>
                    <td style="text-align:center;"><?= $no++ ?></td>
                    <td style="font-family:monospace;font-weight:bold;color:#4e73df;">
                        <?= htmlspecialchars($r->no_rent ?? '') ?></td>
                    <td><?= htmlspecialchars($r->nama_customer ?? '-') ?></td>
                    <td><?= htmlspecialchars($r->nama_vendor ?? '-') ?></td>
                    <td><?= htmlspecialchars($r->pic_customer ?? '-') ?></td>
                    <td><?= htmlspecialchars($r->location ?? '-') ?></td>
                    <td style="text-align:center;">
                        <?= !empty($r->rent_start_date) ? date('d/m/Y', strtotime($r->rent_start_date)) : '-' ?></td>
                    <td style="text-align:center;">
                        <?= !empty($r->rent_end_date) ? date('d/m/Y', strtotime($r->rent_end_date)) : '-' ?></td>
                    <td style="text-align:center;"><?= $dur ?></td>
                    <td style="text-align:center;"><span
                            class="badge <?= $bc ?>"><?= htmlspecialchars($r->status_rent ?? '-') ?></span></td>
                    <td><?= htmlspecialchars($r->notes ?? '-') ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
    <div class="footer">TSC Core System — Laporan Daily Rent — <?= date('d/m/Y H:i:s') ?></div>
</body>

</html>