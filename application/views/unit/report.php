<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        th, td { 
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
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .badge { 
            display: inline-block;
            padding: 2px 6px; 
            border-radius: 3px; 
            font-size: 8px; 
            font-weight: bold;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
        .no-polisi { 
            font-weight: bold; 
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
        $units = $units ?? [];
        $total = count($units);
        $aktif = count(array_filter($units, fn($u) => ($u->status_unit ?? 'aktif') == 'aktif'));
        $maintenance = count(array_filter($units, fn($u) => ($u->status_unit ?? '') == 'maintenance'));
        $rusak = count(array_filter($units, fn($u) => ($u->status_unit ?? '') == 'rusak'));
        
        // Count STNK/KIR expired
        $stnk_expired = 0;
        $kir_expired = 0;
        foreach ($units as $u) {
            if (!empty($u->stnk_expired) && strtotime($u->stnk_expired) < time()) $stnk_expired++;
            if (!empty($u->kir_expired) && strtotime($u->kir_expired) < time()) $kir_expired++;
        }
        ?>
        <div class="summary-item"><strong>Total Unit:</strong> <?= $total ?></div>
        <div class="summary-item"><strong>Aktif:</strong> <?= $aktif ?></div>
        <div class="summary-item"><strong>Maintenance:</strong> <?= $maintenance ?></div>
        <div class="summary-item"><strong>Rusak:</strong> <?= $rusak ?></div>
        <div class="summary-item"><strong>STNK Expired:</strong> <?= $stnk_expired ?></div>
        <div class="summary-item"><strong>KIR Expired:</strong> <?= $kir_expired ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 10%;">No Polisi</th>
                <th style="width: 8%;">Tipe Unit</th>
                <th style="width: 8%;">Tipe Box</th>
                <th style="width: 5%;">Tahun</th>
                <th style="width: 10%;">Dimensi<br>(P×L×T)</th>
                <th style="width: 6%;">CBM</th>
                <th style="width: 6%;">Tonase</th>
                <th style="width: 8%;">Status Unit</th>
                <th style="width: 8%;">STNK Exp</th>
                <th style="width: 8%;">KIR Exp</th>
                <th style="width: 7%;">BBM</th>
                <th style="width: 8%;">Odometer</th>
                <th style="width: 5%;">Foto</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($units as $u): 
                $status = $u->status_unit ?? 'aktif';
                
                // Status badge
                $status_badges = [
                    'aktif' => 'badge-success',
                    'maintenance' => 'badge-warning',
                    'rusak' => 'badge-danger',
                    'dijual' => 'badge-secondary',
                    'nonaktif' => 'badge-secondary'
                ];
                $status_class = $status_badges[$status] ?? 'badge-secondary';
                
                // STNK Check
                $stnk_status = '-';
                $stnk_class = '';
                if (!empty($u->stnk_expired)) {
                    $diff = (strtotime($u->stnk_expired) - time()) / 86400;
                    if ($diff <= 0) {
                        $stnk_status = 'EXPIRED';
                        $stnk_class = 'badge-danger';
                    } elseif ($diff < 30) {
                        $stnk_status = ceil($diff) . ' hari';
                        $stnk_class = 'badge-warning';
                    } else {
                        $stnk_status = date('d/m/Y', strtotime($u->stnk_expired));
                        $stnk_class = 'badge-success';
                    }
                }
                
                // KIR Check
                $kir_status = '-';
                $kir_class = '';
                if (!empty($u->kir_expired)) {
                    $diff = (strtotime($u->kir_expired) - time()) / 86400;
                    if ($diff <= 0) {
                        $kir_status = 'EXPIRED';
                        $kir_class = 'badge-danger';
                    } elseif ($diff < 30) {
                        $kir_status = ceil($diff) . ' hari';
                        $kir_class = 'badge-warning';
                    } else {
                        $kir_status = date('d/m/Y', strtotime($u->kir_expired));
                        $kir_class = 'badge-success';
                    }
                }
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center">
                    <span class="no-polisi"><?= strtoupper($u->no_polisi) ?></span>
                    <?php if (!empty($u->kapasitas_kg)): ?>
                        <br><small style="font-size: 7px;">Cap: <?= number_format($u->kapasitas_kg) ?> kg</small>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?= $u->tipe_unit ?></td>
                <td class="text-center"><?= $u->tipe_box ?></td>
                <td class="text-center"><?= $u->tahun_unit ?></td>
                <td class="text-center">
                    <?php if ($u->panjang && $u->lebar && $u->tinggi): ?>
                        <?= $u->panjang ?>×<?= $u->lebar ?>×<?= $u->tinggi ?> m
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="text-right">
                    <?php 
                    if ($u->panjang && $u->lebar && $u->tinggi) {
                        echo number_format($u->panjang * $u->lebar * $u->tinggi, 2);
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td class="text-right"><?= $u->tonase ?> Ton</td>
                <td class="text-center">
                    <span class="badge <?= $status_class ?>"><?= strtoupper($status) ?></span>
                </td>
                <td class="text-center">
                    <?php if ($stnk_class): ?>
                        <span class="badge <?= $stnk_class ?>"><?= $stnk_status ?></span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php if ($kir_class): ?>
                        <span class="badge <?= $kir_class ?>"><?= $kir_status ?></span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php if (!empty($u->bahan_bakar)): ?>
                        <strong><?= strtoupper($u->bahan_bakar) ?></strong>
                        <?php if (!empty($u->konsumsi_bbm)): ?>
                            <br><small style="font-size: 7px;"><?= $u->konsumsi_bbm ?> km/L</small>
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="text-right">
                    <?php if (!empty($u->current_km)): ?>
                        <?= number_format($u->current_km) ?> km
                        <?php if (!empty($u->next_service_km)): ?>
                            <br><small style="font-size: 7px;">Srv: <?= number_format($u->next_service_km) ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="text-center" style="font-size: 8px;">
                    <?= $u->foto_stnk ? '✓' : '✗' ?> STNK<br>
                    <?= $u->foto_kir ? '✓' : '✗' ?> KIR
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Total Unit: <strong><?= $total ?></strong> | Laporan ini digenerate otomatis oleh sistem</p>
    </div>
</body>
</html>