<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

class Driver extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        // 🔥 UPDATED: Allow all operational levels to access
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            show_error('Access Denied - You do not have permission to access this module', 403);
        }

        $this->load->model('M_driver');
        $this->load->library('permission_lib');
        $this->load->helper('file');
        $this->data['aktif'] = 'driver';
    }

    // LIHAT SEMUA
    public function index()
    {
        $this->data['title'] = 'Master Driver';
        $this->data['drivers'] = $this->M_driver->lihat(); // ✅ FIXED: M_driver
        $this->data['no'] = 1;
        $this->load->view('driver/lihat', $this->data);
    }

    // 🔥 ENHANCED: SEARCH AJAX WITH GRANULAR PERMISSIONS
    public function search()
    {
        $keyword = $this->input->post('keyword') ?? '';
        $drivers = $this->M_driver->cari($keyword); // ✅ FIXED: M_driver

        // 🔥 UPDATED: Check if user can edit/delete
        $level = $this->session->userdata('login')['user_level'] ?? '';
        $can_edit = in_array($level, ['superadmin', 'admin_operational', 'operational_staff']);
        $can_delete = in_array($level, ['superadmin', 'admin_operational']); // Only superadmin & admin_ops

        $no = 1;

        if (empty($drivers)) {
            echo '<tr><td colspan="' . ($can_edit ? '11' : '10') . '" class="text-center text-muted py-4"><i class="fas fa-inbox fa-3x mb-3 d-block"></i><em>Tidak ada data driver.</em></td></tr>';
            return;
        }

        foreach ($drivers as $driver):
            $status = $driver->status_driver ?? 'aktif';
            $status_class = 'status-' . $status;
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center">
                    <?php if (!empty($driver->foto_driver)): ?>
                        <a href="<?= base_url('uploads/driver/' . $driver->foto_driver) ?>" target="_blank">
                            <img src="<?= base_url('uploads/driver/' . $driver->foto_driver) ?>" class="driver-photo" alt="Driver">
                        </a>
                    <?php else: ?>
                        <div class="driver-photo bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-user text-muted"></i>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <strong class="text-primary"><?= htmlspecialchars($driver->nama_driver ?? '') ?></strong>
                    <?php if (!empty($driver->tanggal_bergabung)): ?>
                        <br><small class="text-muted">
                            <i class="fas fa-calendar"></i> Sejak <?= date('d/m/Y', strtotime($driver->tanggal_bergabung)) ?>
                        </small>
                    <?php endif; ?>
                </td>
                <td><small><?= htmlspecialchars($driver->nik ?? '-') ?></small></td>
                <td>
                    <strong><?= htmlspecialchars($driver->sim ?? '-') ?></strong>
                    <?php if (!empty($driver->tipe_sim)): ?>
                        <br><span class="badge badge-info">SIM <?= $driver->tipe_sim ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    if (!empty($driver->masa_berlaku_sim)):
                        $diff = (strtotime($driver->masa_berlaku_sim) - time()) / 86400;
                        if ($diff <= 0):
                            $class = 'expiry-expired';
                            $text = '❌ Expired';
                        elseif ($diff < 30):
                            $class = 'expiry-soon';
                            $text = '⚠️ ' . ceil($diff) . ' hari';
                        else:
                            $class = 'expiry-ok';
                            $text = '✓ ' . date('d/m/Y', strtotime($driver->masa_berlaku_sim));
                        endif;
                        ?>
                        <small class="expiry-warning <?= $class ?>"><?= $text ?></small>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($driver->no_hp)): ?>
                        <small><i class="fas fa-phone"></i> <?= htmlspecialchars($driver->no_hp) ?></small>
                    <?php endif; ?>
                    <?php if (!empty($driver->email)): ?>
                        <br><small><i class="fas fa-envelope"></i> <?= htmlspecialchars($driver->email) ?></small>
                    <?php endif; ?>
                    <?php if (empty($driver->no_hp) && empty($driver->email)): ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <span class="badge badge-status <?= $status_class ?>">
                        <?= strtoupper($status) ?>
                    </span>
                </td>
                <td>
                    <?php
                    $rating = $driver->rating ?? 0;
                    $total_trip = $driver->total_trip ?? 0;
                    ?>
                    <div class="rating-stars">
                        <?php
                        $stars = round($rating * 2) / 2;
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $stars)
                                echo '<i class="fas fa-star"></i>';
                            elseif ($i - 0.5 == $stars)
                                echo '<i class="fas fa-star-half-alt"></i>';
                            else
                                echo '<i class="far fa-star"></i>';
                        }
                        ?>
                        <br><small class="text-muted"><?= number_format($rating, 1) ?> / 5.0</small>
                    </div>
                    <small class="text-info d-block mt-1">
                        <i class="fas fa-road"></i> <?= number_format($total_trip) ?> trip
                    </small>
                </td>
                <td class="text-center">
                    <?php if (!empty($driver->foto_sim)): ?>
                        <a href="<?= base_url('uploads/sim/' . $driver->foto_sim) ?>" target="_blank">
                            <img src="<?= base_url('uploads/sim/' . $driver->foto_sim) ?>" width="40" class="img-thumbnail" alt="SIM">
                        </a>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>

                <!-- 🔥 UPDATED: Action buttons with granular permissions -->
                <?php if ($can_edit): ?>
                    <td class="text-center">
                        <!-- Edit Button - All operational levels can edit -->
                        <a href="<?= base_url('driver/ubah/' . $driver->id) ?>" class="btn btn-success btn-sm" title="Ubah">
                            <i class="fas fa-edit"></i>
                        </a>

                        <!-- Delete Button - Only superadmin & admin_operational -->
                        <?php if ($can_delete): ?>
                            <a onclick="return confirm('Yakin hapus driver <?= htmlspecialchars($driver->nama_driver ?? '') ?>?')"
                                href="<?= base_url('driver/hapus/' . $driver->id) ?>" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled title="Anda tidak memiliki akses untuk menghapus">
                                <i class="fas fa-lock"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                <?php endif ?>
            </tr>
            <?php
        endforeach;
    }

    // TAMBAH - All operational levels can add
    public function tambah()
    {
        $this->data['title'] = 'Tambah Driver';
        $this->load->view('driver/tambah', $this->data);
    }

    // 🔥 ENHANCED: PROSES TAMBAH WITH NEW FIELDS
    public function proses_tambah()
    {
        $this->form_validation->set_rules('nama_driver', 'Nama Driver', 'required|trim');
        $this->form_validation->set_rules('nik', 'NIK', 'required|exact_length[16]|is_unique[drivers.nik]|trim');
        $this->form_validation->set_rules('sim', 'SIM', 'required|trim');
        $this->form_validation->set_rules('masa_berlaku_sim', 'Masa Berlaku SIM', 'required');
        $this->form_validation->set_rules('tipe_sim', 'Tipe SIM', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('driver/tambah');
        }

        // UPLOAD FOTO SIM
        $foto_sim = $this->upload_foto('foto_sim', 'sim');
        if ($foto_sim === FALSE) {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('driver/tambah');
        }

        // UPLOAD FOTO DRIVER
        $foto_driver = $this->upload_foto('foto_driver', 'driver');
        if ($foto_driver === FALSE) {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('driver/tambah');
        }

        // 🔥 UPDATED: Include ALL new TMS fields
        $data = [
            // Existing fields
            'nama_driver' => $this->input->post('nama_driver'),
            'nik' => $this->input->post('nik'),
            'sim' => $this->input->post('sim'),
            'masa_berlaku_sim' => $this->input->post('masa_berlaku_sim'),
            'foto_sim' => $foto_sim,
            'foto_driver' => $foto_driver,

            // 🔥 NEW TMS FIELDS
            'no_hp' => $this->input->post('no_hp') ?: null,
            'email' => $this->input->post('email') ?: null,
            'alamat' => $this->input->post('alamat') ?: null,
            'tipe_sim' => $this->input->post('tipe_sim') ?: null,
            'status_driver' => $this->input->post('status_driver') ?: 'aktif',
            'tanggal_bergabung' => $this->input->post('tanggal_bergabung') ?: null,
            'rating' => $this->input->post('rating') ?: 0,
            'total_trip' => 0, // Default 0 for new driver
            'keterangan' => $this->input->post('keterangan') ?: null
        ];

        if ($this->M_driver->tambah($data)) { // ✅ FIXED: M_driver
            $this->session->set_flashdata('success', 'Driver <strong>' . $data['nama_driver'] . '</strong> berhasil ditambahkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan driver!');
        }
        redirect('driver');
    }

    // UBAH - All operational levels can edit
    public function ubah($id)
    {
        $this->data['title'] = 'Ubah Driver';
        $this->data['driver'] = $this->M_driver->lihat_id($id); // ✅ FIXED: M_driver
        if (!$this->data['driver']) {
            $this->session->set_flashdata('error', 'Driver tidak ditemukan!');
            redirect('driver');
        }
        $this->load->view('driver/ubah', $this->data);
    }

    // 🔥 ENHANCED: PROSES UBAH WITH NEW FIELDS
    public function proses_ubah($id)
    {
        $this->form_validation->set_rules('nama_driver', 'Nama Driver', 'required|trim');
        $this->form_validation->set_rules('sim', 'SIM', 'required|trim');
        $this->form_validation->set_rules('masa_berlaku_sim', 'Masa Berlaku SIM', 'required');
        $this->form_validation->set_rules('tipe_sim', 'Tipe SIM', 'required');

        $driver_lama = $this->M_driver->lihat_id($id); // ✅ FIXED: M_driver
        $old_nik = $driver_lama->nik;
        $new_nik = $this->input->post('nik');

        if ($new_nik != $old_nik) {
            $this->form_validation->set_rules('nik', 'NIK', 'required|exact_length[16]|is_unique[drivers.nik]|trim');
        } else {
            $this->form_validation->set_rules('nik', 'NIK', 'required|exact_length[16]|trim');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('driver/ubah/' . $id);
        }

        // Handle Foto SIM
        $foto_sim = $driver_lama->foto_sim;
        if (!empty($_FILES['foto_sim']['name'])) {
            $foto_sim_baru = $this->upload_foto('foto_sim', 'sim');
            if ($foto_sim_baru === FALSE) {
                $this->session->set_flashdata('error', 'Gagal upload foto SIM: ' . $this->upload->display_errors());
                redirect('driver/ubah/' . $id);
            }
            // Hapus foto lama
            if (!empty($driver_lama->foto_sim) && file_exists(FCPATH . 'uploads/sim/' . $driver_lama->foto_sim)) {
                @unlink(FCPATH . 'uploads/sim/' . $driver_lama->foto_sim);
            }
            $foto_sim = $foto_sim_baru;
        }

        // Handle Foto Driver
        $foto_driver = $driver_lama->foto_driver;
        if (!empty($_FILES['foto_driver']['name'])) {
            $foto_driver_baru = $this->upload_foto('foto_driver', 'driver');
            if ($foto_driver_baru === FALSE) {
                $this->session->set_flashdata('error', 'Gagal upload foto driver: ' . $this->upload->display_errors());
                redirect('driver/ubah/' . $id);
            }
            // Hapus foto lama
            if (!empty($driver_lama->foto_driver) && file_exists(FCPATH . 'uploads/driver/' . $driver_lama->foto_driver)) {
                @unlink(FCPATH . 'uploads/driver/' . $driver_lama->foto_driver);
            }
            $foto_driver = $foto_driver_baru;
        }

        // 🔥 UPDATED: Include ALL new TMS fields
        $data = [
            // Existing fields
            'nama_driver' => $this->input->post('nama_driver'),
            'nik' => $new_nik,
            'sim' => $this->input->post('sim'),
            'masa_berlaku_sim' => $this->input->post('masa_berlaku_sim'),
            'foto_sim' => $foto_sim,
            'foto_driver' => $foto_driver,

            // 🔥 NEW TMS FIELDS
            'no_hp' => $this->input->post('no_hp') ?: null,
            'email' => $this->input->post('email') ?: null,
            'alamat' => $this->input->post('alamat') ?: null,
            'tipe_sim' => $this->input->post('tipe_sim') ?: null,
            'status_driver' => $this->input->post('status_driver') ?: 'aktif',
            'tanggal_bergabung' => $this->input->post('tanggal_bergabung') ?: null,
            'rating' => $this->input->post('rating') ?: 0,
            'total_trip' => $this->input->post('total_trip') ?: 0,
            'keterangan' => $this->input->post('keterangan') ?: null
        ];

        if ($this->M_driver->ubah($data, $id)) { // ✅ FIXED: M_driver
            $this->session->set_flashdata('success', 'Driver <strong>' . $data['nama_driver'] . '</strong> berhasil diubah!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah driver!');
        }
        redirect('driver');
    }

    // 🔥 HAPUS - ONLY SUPERADMIN & ADMIN_OPERATIONAL
    public function hapus($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational'])) {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('driver');
        }

        $driver = $this->M_driver->lihat_id($id);
        if (!$driver) {
            $this->session->set_flashdata('error', 'Driver tidak ditemukan!');
            redirect('driver');
        }

        // Soft delete - tidak hapus foto, data tetap ada
        if ($this->M_driver->hapus($id)) {
            $this->session->set_flashdata('success', 'Driver <strong>' . $driver->nama_driver . '</strong> berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus driver!');
        }
        redirect('driver');
    }

    // EXPORT PDF
    public function export()
    {
        $dompdf = new Dompdf();
        $this->data['title'] = 'Laporan Data Driver';
        $this->data['drivers'] = $this->M_driver->lihat(); // ✅ FIXED: M_driver
        $this->data['no'] = 1;
        $html = $this->load->view('driver/report', $this->data, true);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('Laporan_Driver_' . date('d-m-Y') . '.pdf', ['Attachment' => false]);
    }

    public function terhapus()
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin yang dapat mengakses halaman ini.');
            redirect('driver');
        }

        $this->data['title'] = 'Driver Terhapus';
        $this->data['drivers'] = $this->M_driver->lihat_semua_termasuk_terhapus();
        // Filter hanya yang deleted
        $this->data['drivers'] = array_filter($this->data['drivers'], function ($d) {
            return !empty($d->deleted_at);
        });
        $this->data['no'] = 1;
        $this->load->view('driver/terhapus', $this->data);
    }

    public function restore($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('driver');
        }

        // Ambil termasuk yang deleted
        $this->db->where('id', $id);
        $driver = $this->db->get('drivers')->row();

        if (!$driver) {
            $this->session->set_flashdata('error', 'Driver tidak ditemukan!');
            redirect('driver/terhapus');
        }

        if ($this->M_driver->restore($id)) {
            $this->session->set_flashdata('success', 'Driver <strong>' . $driver->nama_driver . '</strong> berhasil dipulihkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan driver!');
        }
        redirect('driver/terhapus');
    }

    public function hapus_permanen($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('driver');
        }

        $this->db->where('id', $id);
        $driver = $this->db->get('drivers')->row();

        if ($driver) {
            // Hapus foto fisik
            if (!empty($driver->foto_sim) && file_exists(FCPATH . 'uploads/sim/' . $driver->foto_sim)) {
                @unlink(FCPATH . 'uploads/sim/' . $driver->foto_sim);
            }
            if (!empty($driver->foto_driver) && file_exists(FCPATH . 'uploads/driver/' . $driver->foto_driver)) {
                @unlink(FCPATH . 'uploads/driver/' . $driver->foto_driver);
            }
        }

        $this->db->where('id', $id);
        if ($this->db->delete('drivers')) {
            $this->session->set_flashdata('success', 'Driver <strong>' . $driver->nama_driver . '</strong> dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus permanen!');
        }
        redirect('driver/terhapus');
    }

    // HELPER UPLOAD FOTO
    private function upload_foto($field, $folder)
    {
        $config['upload_path'] = FCPATH . 'uploads/' . $folder . '/';
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = TRUE;

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);

        if (!empty($_FILES[$field]['name'])) {
            if ($this->upload->do_upload($field)) {
                return $this->upload->data('file_name');
            } else {
                return FALSE;
            }
        }
        return NULL;
    }
}