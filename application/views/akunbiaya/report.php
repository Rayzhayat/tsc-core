<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?= $title ?></title>
	<link href="<?= base_url('sb-admin') ?>/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body>
	<div class="row">
		<div class="col text-center">
			<h3 class="h3 text-dark"><?= $title ?></h3>
		</div>
	</div>
	<hr>
	<div class="row">
		<table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
			<thead>
				<tr>
					<td width="40px">No</td>
					<td>Tipe Akun</td>
					<td>Kode Perkiraan</td>
					<td>Nama Akun</td>
					<td>Akun Induk</td>
					<!-- <td>Tanggal Dibuat</td> -->
				</tr>
			</thead>
			<tbody>
			<?php foreach ($akunbiaya as $akun): ?>
				<tr>
					<td><?= $no++ ?></td>
					<td><?= htmlspecialchars($akun->tipe_akun, ENT_QUOTES, 'UTF-8') ?></td>
					<td><?= htmlspecialchars($akun->kode_perkiraan, ENT_QUOTES, 'UTF-8') ?></td>
					<td><?= htmlspecialchars($akun->nama, ENT_QUOTES, 'UTF-8') ?></td>
					<td><?= htmlspecialchars($akun->akun_induk, ENT_QUOTES, 'UTF-8') ?></td>
					<!-- <td><= isset($akun->created_at) ? date('d-m-Y', strtotime($akun->created_at)) : '-' ?></td> -->
				</tr>	
			<?php endforeach ?>
			</tbody>
		</table>
	</div>
</body>
</html>
