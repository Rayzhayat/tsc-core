<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?= $title ?></title>
	<style>
		body { 
			font-family: Arial, sans-serif; 
			font-size: 12px;
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
		th, td { 
			border: 1px solid #000; 
			padding: 8px; 
			text-align: left;
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
			font-size: 10px; 
			color: #666;
		}
	</style>
</head>
<body>
	<h3><?= $title ?></h3>
	<p class="small">Tanggal Cetak: <?= date('d F Y H:i') ?></p>
	
	<table>
		<thead>
			<tr>
				<th width="5%" class="text-center">No</th>
				<th width="12%">Kode</th>
				<th width="20%">Nama Customer</th>
				<th width="12%">Telepon</th>
				<th width="15%">PIC</th>
				<th width="15%">NPWP</th>
				<th width="8%" class="text-center">PPH</th>
				<th width="8%" class="text-center">PPN</th>
			</tr>
		</thead>
		<tbody>
		<?php if (!empty($all_customer)): ?>
			<?php foreach ($all_customer as $customer): ?>
				<tr>
					<td class="text-center"><?= $no++ ?></td>
					<td><?= htmlspecialchars($customer->kode) ?></td>
					<td><strong><?= htmlspecialchars($customer->nama) ?></strong></td>
					<td><?= htmlspecialchars($customer->telepon ?: '-') ?></td>
					<td><?= htmlspecialchars($customer->pic ?: '-') ?></td>
					<td class="small"><?= htmlspecialchars($customer->npwp ?: '-') ?></td>
					<td class="text-center"><?= htmlspecialchars($customer->pph ?: '-') ?></td>
					<td class="text-center"><?= htmlspecialchars($customer->ppn ?: '-') ?></td>
				</tr>	
			<?php endforeach ?>
		<?php else: ?>
			<tr>
				<td colspan="8" class="text-center">Tidak ada data customer</td>
			</tr>
		<?php endif ?>
		</tbody>
	</table>
	
	<p class="small" style="margin-top: 20px;">
		Total Customer: <strong><?= count($all_customer) ?></strong>
	</p>
</body>
</html>