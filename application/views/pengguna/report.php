<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title><?= $title ?></title>
	<link href="<?= base_url('sb-admin') ?>/css/sb-admin-2.min.css" rel="stylesheet">
	<style>
		body {
			font-size: 12px;
		}

		.report-header {
			text-align: center;
			margin-bottom: 16px;
		}

		.report-header h3 {
			font-size: 16px;
			font-weight: 700;
			margin-bottom: 2px;
		}

		.report-header p {
			font-size: 11px;
			color: #555;
			margin: 0;
		}

		.report-meta {
			display: flex;
			justify-content: space-between;
			font-size: 11px;
			color: #555;
			margin-bottom: 10px;
			padding-bottom: 6px;
			border-bottom: 1px solid #ccc;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			font-size: 11px;
		}

		thead tr {
			background-color: #2c3e6b;
			color: #fff;
		}

		thead th {
			padding: 7px 8px;
			text-align: left;
			border: 1px solid #1a2a50;
			white-space: nowrap;
		}

		tbody tr:nth-child(even) {
			background-color: #f2f4f8;
		}

		tbody tr:nth-child(odd) {
			background-color: #ffffff;
		}

		tbody td {
			padding: 6px 8px;
			border: 1px solid #ddd;
			vertical-align: middle;
		}

		.badge-level {
			display: inline-block;
			padding: 2px 7px;
			border-radius: 10px;
			font-size: 10px;
			font-weight: 600;
		}

		.badge-superadmin {
			background: #2c3e6b;
			color: #fff;
		}

		.badge-admin_operational {
			background: #1cc88a;
			color: #fff;
		}

		.badge-operational_staff {
			background: #36b9cc;
			color: #fff;
		}

		.badge-finance_staff {
			background: #f6c23e;
			color: #333;
		}

		.badge-admin_document {
			background: #858796;
			color: #fff;
		}

		.badge-status {
			display: inline-block;
			padding: 2px 7px;
			border-radius: 10px;
			font-size: 10px;
			font-weight: 600;
		}

		.badge-tetap {
			background: #1cc88a;
			color: #fff;
		}

		.badge-kontrak {
			background: #f6c23e;
			color: #333;
		}

		.badge-magang {
			background: #858796;
			color: #fff;
		}

		.cuti-bar {
			display: flex;
			align-items: center;
			gap: 5px;
		}

		.cuti-track {
			flex: 1;
			height: 6px;
			background: #e0e0e0;
			border-radius: 4px;
			overflow: hidden;
			min-width: 40px;
		}

		.cuti-fill {
			height: 100%;
			border-radius: 4px;
			background: linear-gradient(90deg, #1cc88a, #36b9cc);
		}

		.text-center {
			text-align: center;
		}

		.text-right {
			text-align: right;
		}

		.summary-box {
			display: flex;
			gap: 16px;
			margin-bottom: 12px;
		}

		.summary-item {
			background: #f2f4f8;
			border: 1px solid #dde;
			border-radius: 6px;
			padding: 8px 14px;
			text-align: center;
			min-width: 80px;
		}

		.summary-item .val {
			font-size: 18px;
			font-weight: 800;
			line-height: 1;
			color: #2c3e6b;
		}

		.summary-item .lbl {
			font-size: 10px;
			color: #888;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			margin-top: 2px;
		}

		@media print {
			body {
				margin: 0;
			}

			.no-print {
				display: none !important;
			}
		}
	</style>
</head>

<body>

	<!-- Print Button -->
	<div class="no-print" style="margin-bottom:12px;">
		<button onclick="window.print()" class="btn btn-sm btn-primary">
			<i class="fas fa-print me-1"></i> Cetak / Print
		</button>
	</div>

	<!-- Header -->
	<div class="report-header">
		<h3><?= $title ?></h3>
		<p>Data Master Karyawan — Sistem Informasi Inventori</p>
	</div>

	<div class="report-meta">
		<span>Tanggal Cetak: <?= date('d F Y, H:i') ?> WIB</span>
		<span>Total Karyawan: <?= count($all_pengguna) ?> orang</span>
	</div>

	<?php
	// Hitung summary
	$total = count($all_pengguna);
	$tetap = count(array_filter($all_pengguna, fn($p) => ($p->status_kepegawaian ?? '') === 'Tetap'));
	$kontrak = count(array_filter($all_pengguna, fn($p) => ($p->status_kepegawaian ?? '') === 'Kontrak'));
	$magang = count(array_filter($all_pengguna, fn($p) => ($p->status_kepegawaian ?? '') === 'Magang'));
	?>

	<!-- Summary -->
	<div class="summary-box">
		<div class="summary-item">
			<div class="val"><?= $total ?></div>
			<div class="lbl">Total</div>
		</div>
		<div class="summary-item">
			<div class="val" style="color:#1cc88a;"><?= $tetap ?></div>
			<div class="lbl">Tetap</div>
		</div>
		<div class="summary-item">
			<div class="val" style="color:#f6c23e;"><?= $kontrak ?></div>
			<div class="lbl">Kontrak</div>
		</div>
		<div class="summary-item">
			<div class="val" style="color:#858796;"><?= $magang ?></div>
			<div class="lbl">Magang</div>
		</div>
	</div>

	<!-- Table -->
	<table>
		<thead>
			<tr>
				<th class="text-center" style="width:30px;">No</th>
				<th>NIK</th>
				<th>Nama Karyawan</th>
				<th>Username</th>
				<th>Tanggal Lahir</th>
				<th>Tanggal Join</th>
				<th>Golongan</th>
				<th>Status</th>
				<th>Level Akses</th>
				<th class="text-center">Jatah Cuti</th>
				<th class="text-center">Sisa Cuti</th>
				<th style="width:90px;">Progress Cuti</th>
			</tr>
		</thead>
		<tbody>
			<?php $no = 1;
			foreach ($all_pengguna as $p): ?>
				<?php
				$jatah = (int) ($p->jatah_cuti ?? 12);
				$sisa = (int) ($p->sisa_cuti ?? 0);
				$pct = $jatah > 0 ? round(($sisa / $jatah) * 100) : 0;

				$level_labels = [
					'superadmin' => ['Superadmin', 'superadmin'],
					'admin_operational' => ['Admin Operational', 'admin_operational'],
					'operational_staff' => ['Staff Operational', 'operational_staff'],
					'finance_staff' => ['Staff Finance', 'finance_staff'],
					'admin_document' => ['Admin Dokumen', 'admin_document'],
				];
				$level_info = $level_labels[$p->user_level] ?? [$p->user_level, 'superadmin'];

				$status_class = strtolower($p->status_kepegawaian ?? 'kontrak');
				?>
				<tr>
					<td class="text-center"><?= $no++ ?></td>
					<td><?= $p->nik ?? '—' ?></td>
					<td><strong><?= htmlspecialchars($p->nama) ?></strong></td>
					<td style="color:#888;"><?= htmlspecialchars($p->username) ?></td>
					<td><?= $p->tanggal_lahir ? date('d M Y', strtotime($p->tanggal_lahir)) : '—' ?></td>
					<td><?= $p->tanggal_join ? date('d M Y', strtotime($p->tanggal_join)) : '—' ?></td>
					<td class="text-center"><?= $p->golongan ?? '—' ?></td>
					<td>
						<?php if ($p->status_kepegawaian): ?>
							<span class="badge-status badge-<?= $status_class ?>">
								<?= $p->status_kepegawaian ?>
							</span>
						<?php else: ?>
							<span style="color:#ccc;">—</span>
						<?php endif ?>
					</td>
					<td>
						<span class="badge-level badge-<?= $level_info[1] ?>">
							<?= $level_info[0] ?>
						</span>
					</td>
					<td class="text-center"><?= $jatah ?> hr</td>
					<td class="text-center"><?= $sisa ?> hr</td>
					<td>
						<div class="cuti-bar">
							<div class="cuti-track">
								<div class="cuti-fill" style="width:<?= $pct ?>%;"></div>
							</div>
							<span style="font-size:10px;color:#888;white-space:nowrap;"><?= $pct ?>%</span>
						</div>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>

	<div style="margin-top:16px;font-size:10px;color:#aaa;text-align:right;">
		Dicetak oleh sistem pada <?= date('d/m/Y H:i:s') ?> WIB
	</div>

</body>

</html>