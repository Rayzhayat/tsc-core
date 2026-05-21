<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background: #e74a3b; color: white; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { background: #f8f9fc; padding: 10px; margin: 10px 0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PENGELUARAN</h2>
        <?php if (!empty($filters['tanggal_mulai']) || !empty($filters['tanggal_akhir'])): ?>
            <p>
                Periode: 
                <?= !empty($filters['tanggal_mulai']) ? date('d/m/Y', strtotime($filters['tanggal_mulai'])) : '...' ?> 
                s/d 
                <?= !empty($filters['tanggal_akhir']) ? date('d/m/Y', strtotime($filters['tanggal_akhir'])) : '...' ?>
            </p>
        <?php endif ?>
    </div>

    <div class="summary">
        <strong>Summary:</strong><br>
        Total Semua: Rp <?= number_format($total_all, 0, ',', '.') ?> | 
        Vendor (V): Rp <?= number_format($total_vendor, 0, ',', '.') ?> | 
        Non-Vendor (M): Rp <?= number_format($total_non_vendor, 0, ',', '.') ?>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="5%">Tipe</th>
                <th width="7%">Reff No</th>
                <th width="7%">Tanggal</th>
                <th width="12%">Postingan Biaya</th>
                <th width="12%">Vendor</th>
                <th width="10%">No Invoice</th>
                <th width="8%" class="text-right">Nominal</th>
                <th width="6%" class="text-right">PPN</th>
                <th width="6%" class="text-right">PPH</th>
                <th width="10%" class="text-right">Total</th>
                <th width="7%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($pengeluaran as $item): 
                $tipe = substr($item->reff_no, 0, 1);
                $status = $item->tagihan_id ? 'Tagihan' : 'Manual';
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $tipe === 'V' ? 'VENDOR' : 'MANUAL' ?></td>
                    <td><?= htmlspecialchars($item->reff_no) ?></td>
                    <td><?= date('d/m/Y', strtotime($item->tanggal)) ?></td>
                    <td><?= htmlspecialchars($item->postingan_biaya) ?></td>
                    <td><?= htmlspecialchars($item->nama_vendor ?: '-') ?></td>
                    <td><?= htmlspecialchars($item->no_invoice_vendor ?: '-') ?></td>
                    <td class="text-right"><?= number_format($item->nominal, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($item->ppn, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($item->pph, 0, ',', '.') ?></td>
                    <td class="text-right"><strong><?= number_format($item->total_bayar, 0, ',', '.') ?></strong></td>
                    <td class="text-center"><?= $status ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="10" class="text-right">GRAND TOTAL:</th>
                <th class="text-right">Rp <?= number_format($total_all, 0, ',', '.') ?></th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top: 20px; font-size: 9px; color: #888;">
        Dicetak pada: <?= date('d F Y, H:i:s') ?>
    </p>
</body>
</html>