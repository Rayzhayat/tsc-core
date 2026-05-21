<!DOCTYPE html>
<html>

<head>
    <title><?= $title ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h2 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px 4px;
            vertical-align: middle;
        }

        th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-secondary {
            background: #e2e3e5;
            color: #383d41;
        }

        .nama-driver {
            font-weight: bold;
            font-size: 10px;
        }

        .star {
            color: #f6c23e;
            font-size: 10px;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }

        .summary {
            margin-top: 15px;
            padding: 10px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .summary-item {
            display: inline-block;
            margin-right: 20px;
            font-size: 10px;
        }

        .summary-item strong {
            color: #333;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2><?= $title ?></h2>
        <p>Dicetak pada: <?= date('d F Y H:i:s') ?> WIB</p>
    </div>

    <!-- SUMMARY STATISTICS -->
    <div class="summary">
        <?php
        $drivers = $drivers ?? [];
        $total = count($drivers);
        $aktif = count(array_filter($drivers, fn($d) => ($d->status_driver ?? 'aktif') == 'aktif'));
        $cuti = count(array_filter($drivers, fn($d) => ($d->status_driver ?? '') == 'cuti'));
        $nonaktif = count(array_filter($drivers, fn($d) => in_array(($d->status_driver ?? ''), ['resign', 'nonaktif'])));

        // Count SIM expired
        $sim_expired = 0;
        foreach ($drivers as $d) {
            if (!empty($d->masa_berlaku_sim) && strtotime($d->masa_berlaku_sim) < time()) $sim_expired++;
        }

        // Average rating
        $total_rating = 0;
        $count_rating = 0;
        foreach ($drivers as $d) {
            if (!empty($d->rating) && $d->rating > 0) {
                $total_rating += $d->rating;
                $count_rating++;
            }
        }
        $avg_rating = $count_rating > 0 ? $total_rating / $count_rating : 0;
        ?>
        <div class="summary-item"><strong>Total Driver:</strong> <?= $total ?></div>
        <div class="summary-item"><strong>Aktif:</strong> <?= $aktif ?></div>
        <div class="summary-item"><strong>Cuti:</strong> <?= $cuti ?></div>
        <div class="summary-item"><strong>Non-Aktif:</strong> <?= $nonaktif ?></div>
        <div class="summary-item"><strong>SIM Expired:</strong> <?= $sim_expired ?></div>
        <div class="summary-item"><strong>Avg Rating:</strong> <?= number_format($avg_rating, 1) ?>/5.0</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 15%;">Nama Driver</th>
                <th style="width: 10%;">NIK</th>
                <th style="width: 10%;">No. SIM / Tipe</th>
                <th style="width: 8%;">Masa Berlaku</th>
                <th style="width: 8%;">Status SIM</th>
                <th style="width: 10%;">Kontak</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 8%;">Join Date</th>
                <th style="width: 8%;">Rating</th>
                <th style="width: 7%;">Total Trip</th>
                <th style="width: 5%;">Foto</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($drivers as $d):
                // Status
                $status = $d->status_driver ?? 'aktif';
                $status_badges = [
                    'aktif' => 'badge-success',
                    'cuti' => 'badge-warning',
                    'resign' => 'badge-danger',
                    'nonaktif' => 'badge-secondary'
                ];
                $status_class = $status_badges[$status] ?? 'badge-secondary';

                // SIM Check
                $sim_expired = !empty($d->masa_berlaku_sim) && strtotime($d->masa_berlaku_sim) < time();
                $sim_soon = !empty($d->masa_berlaku_sim) && (strtotime($d->masa_berlaku_sim) - time()) / 86400 < 30 && !$sim_expired;

                if ($sim_expired) {
                    $sim_status = 'EXPIRED';
                    $sim_class = 'badge-danger';
                } elseif ($sim_soon) {
                    $diff = ceil((strtotime($d->masa_berlaku_sim) - time()) / 86400);
                    $sim_status = $diff . ' hari';
                    $sim_class = 'badge-warning';
                } else {
                    $sim_status = 'Aktif';
                    $sim_class = 'badge-success';
                }

                // Rating stars
                $rating = $d->rating ?? 0;
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    $stars .= ($i <= round($rating)) ? '★' : '☆';
                }
            ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td>
                        <span class="nama-driver"><?= htmlspecialchars($d->nama_driver) ?></span>
                    </td>
                    <td class="text-center" style="font-size: 8px;"><?= htmlspecialchars($d->nik ?? '-') ?></td>
                    <td class="text-center">
                        <strong><?= htmlspecialchars($d->sim ?? '-') ?></strong>
                        <?php if (!empty($d->tipe_sim)): ?>
                            <br><span class="badge badge-info">SIM <?= $d->tipe_sim ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?= !empty($d->masa_berlaku_sim) ? date('d/m/Y', strtotime($d->masa_berlaku_sim)) : '-' ?>
                    </td>
                    <td class="text-center">
                        <span class="badge <?= $sim_class ?>"><?= $sim_status ?></span>
                    </td>
                    <td style="font-size: 8px;">
                        <?php if (!empty($d->no_hp)): ?>
                            <?= htmlspecialchars($d->no_hp) ?>
                        <?php endif; ?>
                        <?php if (!empty($d->email)): ?>
                            <br><?= htmlspecialchars($d->email) ?>
                        <?php endif; ?>
                        <?php if (empty($d->no_hp) && empty($d->email)): ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge <?= $status_class ?>"><?= strtoupper($status) ?></span>
                    </td>
                    <td class="text-center">
                        <?= !empty($d->tanggal_bergabung) ? date('d/m/Y', strtotime($d->tanggal_bergabung)) : '-' ?>
                    </td>
                    <td class="text-center">
                        <span class="star"><?= $stars ?></span>
                        <br><small style="font-size: 7px;"><?= number_format($rating, 1) ?>/5.0</small>
                    </td>
                    <td class="text-center">
                        <?= number_format($d->total_trip ?? 0) ?>
                    </td>
                    <td class="text-center" style="font-size: 8px;">
                        <?= $d->foto_sim ? '✓' : '✗' ?> SIM<br>
                        <?= $d->foto_driver ? '✓' : '✗' ?> Driver
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Total Driver: <strong><?= $total ?></strong> | Laporan ini digenerate otomatis oleh sistem</p>
    </div>
</body>

</html>