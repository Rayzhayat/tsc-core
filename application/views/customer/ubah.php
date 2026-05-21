<!DOCTYPE html>
<html lang="en">

<head>
	<?php $this->load->view('partials/head') ?>
</head>

<body class="antialiased">
	<div class="wrapper">
		<?php $this->load->view('partials/navbar') ?>
		<div class="page-wrapper">
			<div class="page-body">
				<div class="container-xl">
					<div class="d-sm-flex align-items-center justify-content-between mb-4">
						<h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
						<a href="<?= base_url('customer') ?>" class="btn btn-secondary btn-sm">
							<i class="fa fa-arrow-left"></i> Kembali
						</a>
					</div>

					<div class="card shadow">
						<div class="card-header">
							<strong>Form Ubah Customer</strong>
						</div>
						<div class="card-body">
							<form action="<?= base_url('customer/proses_ubah/' . $customer->id) ?>" method="POST"
								id="form-ubah">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label>Kode Customer</label>
											<input type="text" class="form-control" value="<?= $customer->kode ?>"
												readonly>
										</div>
										<div class="form-group">
											<label>Nama Customer <span class="text-danger">*</span></label>
											<input type="text" name="nama" class="form-control"
												value="<?= $customer->nama ?>" required
												placeholder="Nama perusahaan/customer">
										</div>
										<div class="form-group">
											<label>Telepon</label>
											<input type="text" name="telepon" class="form-control"
												value="<?= $customer->telepon ?>" placeholder="Contoh: 081234567890">
										</div>
										<div class="form-group">
											<label>PIC (Person In Charge)</label>
											<input type="text" name="pic" class="form-control"
												value="<?= $customer->pic ?>" placeholder="Nama PIC">
										</div>
										<div class="form-group">
											<label>NPWP</label>
											<input type="text" name="npwp" class="form-control"
												value="<?= $customer->npwp ?>" placeholder="00.000.000.0-000.000"
												maxlength="20">
										</div>
										<div class="form-group">
											<label>Nama NPWP</label>
											<input type="text" name="nama_npwp" class="form-control"
												value="<?= $customer->nama_npwp ?>" placeholder="Nama sesuai NPWP">
											<small class="form-text text-muted">Nama yang tertera di NPWP</small>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>PPH</label>
											<select name="pph" class="form-control">
												<option value="">-- Tidak Kena PPH --</option>
												<?php foreach ($pph_options as $opt): ?>
													<option value="<?= $opt ?>" <?= $customer->pph == $opt ? 'selected' : '' ?>><?= $opt ?></option>
												<?php endforeach; ?>
											</select>
											<small class="form-text text-muted">Pilih persentase PPH yang
												berlaku</small>
										</div>
										<div class="form-group">
											<label>PPN</label>
											<select name="ppn" class="form-control">
												<option value="">-- Tidak Kena PPN --</option>
												<?php foreach ($ppn_options as $opt): ?>
													<option value="<?= $opt ?>" <?= $customer->ppn == $opt ? 'selected' : '' ?>><?= $opt ?></option>
												<?php endforeach; ?>
											</select>
											<small class="form-text text-muted">Pilih persentase PPN yang
												berlaku</small>
										</div>
										<div class="form-group">
											<label>Alamat</label>
											<textarea name="alamat" class="form-control" rows="7"
												placeholder="Alamat lengkap customer"><?= $customer->alamat ?></textarea>
										</div>
									</div>
								</div>
								<hr>
								<div class="form-group mb-0">
									<button type="submit" class="btn btn-success">
										<i class="fa fa-save"></i> Update
									</button>
									<a href="<?= base_url('customer') ?>" class="btn btn-secondary">
										<i class="fa fa-times"></i> Batal
									</a>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<?php $this->load->view('partials/footer') ?>
		</div>
	</div>

	<?php $this->load->view('partials/js') ?>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		$(document).ready(function () {
			// Submit dengan konfirmasi
			$('#form-ubah').on('submit', function (e) {
				e.preventDefault();

				const nama = $('input[name="nama"]').val().trim();
				if (!nama) {
					Swal.fire('Perhatian!', 'Nama Customer harus diisi!', 'warning');
					return false;
				}

				Swal.fire({
					title: 'Update Customer?',
					text: 'Pastikan perubahan data sudah benar!',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ya, Update!',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						this.submit();
					}
				});
			});

			// Format NPWP
			$('input[name="npwp"]').on('input', function () {
				let val = $(this).val().replace(/\D/g, '');
				if (val.length > 15) val = val.substr(0, 15);

				if (val.length > 2) val = val.substr(0, 2) + '.' + val.substr(2);
				if (val.length > 6) val = val.substr(0, 6) + '.' + val.substr(6);
				if (val.length > 10) val = val.substr(0, 10) + '.' + val.substr(10);
				if (val.length > 12) val = val.substr(0, 12) + '-' + val.substr(12);
				if (val.length > 16) val = val.substr(0, 16) + '.' + val.substr(16);

				$(this).val(val);
			});
		});
	</script>
</body>

</html>