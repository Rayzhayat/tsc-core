<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title><?= $title ?></title>
	<style>
		body {
			font-family: Arial, sans-serif;
			font-size: 10px;
		}

		h3 {
			text-align: center;
			margin-bottom: 20px;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 10px;
		}

		th,
		td {
			border: 1px solid #000;
			padding: 6px;
			text-align: left;
			vertical-align: top;
		}

		th {
			background-color: #4e73df;
			color: white;
			font-weight: bold;
		}

		tr:nth-child(even) {
			background-color: #f8f9fc;
		}

		.text-center {
			text-align: center;
		}

		.small {
			font-size: 8px;
			color: #666;
		}

		.badge {
			display: inline-block;
			padding: 2px 6px;
			border-radius: 3px;
			font-size: 8px;
			margin: 1px 0;
		}

		.badge-success {
			background: #1cc88a;
			color: white;
		}

		.badge-warning {
			background: #f6c23e;
			color: #333;
		}

		.badge-info {
			background: #36b9cc;
			color: white;
		}
	</style>
</head>

<body>
	<h3><?= $title ?></h3>
	<p class="small">Tanggal Cetak: <?= date('d F Y H:i') ?></p>

	<table>
		<thead>
			<tr>
				<th width="4%" class="text-center">No</th>
				<th width="18%">Nama Vendor</th>
				<th width="23%">Alamat</th>
				<th width="13%">NPWP</th>
				<th width="12%">PIC / Telp</th>
				<th width="8%" class="text-center">PPN</th>
				<th width="8%" class="text-center">PPH</th>
				<th width="14%" class="text-center">Dokumen</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($all_vendorr)): ?>
				<?php foreach ($all_vendorr as $vendor): ?>
					<tr>
						<td class="text-center"><?= $no++ ?></td>
						<td><strong><?= htmlspecialchars($vendor->nama_vendor) ?></strong></td>
						<td class="small"><?= htmlspecialchars($vendor->alamat_vendor ?: '-') ?></td>
						<td class="small"><?= htmlspecialchars($vendor->npwp_vendor ?: '-') ?></td>
						<td class="small">
							<?= htmlspecialchars($vendor->pic_vendor ?: '-') ?><br>
							<?= htmlspecialchars($vendor->no_telp_vendor ?: '-') ?>
						</td>
						<td class="text-center small"><?= htmlspecialchars($vendor->ppn_vendor) ?></td>
						<td class="text-center small"><?= htmlspecialchars($vendor->pph_vendor) ?></td>
						<td class="text-center small">
							<?php if (!empty($vendor->file_npwp)): ?>
								<span class="badge badge-success">✓ NPWP</span><br>
							<?php endif; ?>
							<?php if (!empty($vendor->file_skb)): ?>
								<span class="badge badge-info">✓ SKB</span><br>
							<?php endif; ?>
							<?php if (!empty($vendor->file_sppkp)): ?>
								<span class="badge badge-warning">✓ SPPKP</span>
							<?php endif; ?>
							<?php if (empty($vendor->file_npwp) && empty($vendor->file_skb) && empty($vendor->file_sppkp)): ?>
								<span style="color: #999;">—</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach ?>
			<?php else: ?>
				<tr>
					<td colspan="8" class="text-center">Tidak ada data vendor</td>
				</tr>
			<?php endif ?>
		</tbody>
	</table>

	<p class="small" style="margin-top: 20px;">
		<strong>Total Vendor: <?= count($all_vendorr) ?></strong><br>
		<em>Catatan: Kolom "Dokumen" menunjukkan file yang sudah diupload (NPWP, SKB, SPPKP)</em>
	</p>
</body>

</html>