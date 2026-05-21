<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UnitFuel extends CI_Controller
{
    private $allowed = ['superadmin', 'admin_operational', 'fleet_staff'];
    private $can_delete = ['superadmin', 'admin_operational'];

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login'))
            redirect('login');
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->allowed))
            show_error('Access Denied', 403);
        $this->load->model('M_unit_fuel', 'fuel');
        $this->load->library('upload');
    }

    public function proses_tambah()
    {
        $unit_id = $this->input->post('unit_id');
        $redirect = $this->input->post('redirect_to') ?: 'unit/detail/' . $unit_id;

        $bukti_struk = null;
        if (!empty($_FILES['bukti_struk']['name'])) {
            $bukti_struk = $this->_upload('bukti_struk', 'unit_bbm');
            if (!$bukti_struk)
                return redirect($redirect);
        }

        $liter = $this->input->post('liter');
        $harga = $this->input->post('harga_per_liter') ?: 0;
        $total = $this->input->post('total_biaya') ?: ($liter * $harga);
        $km_saat = $this->input->post('km_saat_isi') ?: null;

        // Hitung konsumsi: butuh km_terakhir dari unit
        $unit = $this->db->where('id', $unit_id)->get('units')->row();
        $km_terakhir = $unit->current_km ?? null;
        $jarak_tempuh = ($km_saat && $km_terakhir && $km_saat > $km_terakhir) ? ($km_saat - $km_terakhir) : null;
        $konsumsi = ($jarak_tempuh && $liter > 0) ? round($jarak_tempuh / $liter, 2) : null;

        $data = [
            'unit_id' => $unit_id,
            'driver_nama' => $this->input->post('driver_nama') ?: null,
            'tanggal_isi' => $this->input->post('tanggal_isi'),
            'waktu_isi' => $this->input->post('waktu_isi') ?: null,
            'liter' => $liter,
            'harga_per_liter' => $harga,
            'total_biaya' => $total,
            'km_saat_isi' => $km_saat,
            'km_terakhir' => $km_terakhir,
            'jarak_tempuh' => $jarak_tempuh,
            'konsumsi' => $konsumsi,
            'jenis_bbm' => $this->input->post('jenis_bbm'),
            'spbu' => $this->input->post('spbu') ?: null,
            'lokasi' => $this->input->post('lokasi') ?: null,
            'bukti_struk' => $bukti_struk,
            'keterangan' => $this->input->post('keterangan') ?: null,
            'created_by' => $this->session->userdata('login')['username'] ?? '',
        ];

        if ($this->fuel->tambah($data)) {
            // Update current_km di unit
            if ($km_saat)
                $this->db->where('id', $unit_id)->update('units', ['current_km' => $km_saat]);
            $this->session->set_flashdata('success', 'Pengisian BBM berhasil dicatat!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data BBM!');
        }
        redirect($redirect);
    }

    public function proses_ubah($id)
    {
        $lama = $this->fuel->lihat_id($id);
        $redirect = $this->input->post('redirect_to') ?: 'unit/detail/' . ($lama->unit_id ?? '');
        if (!$lama) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect($redirect);
        }

        $bukti_struk = $lama->bukti_struk;
        if (!empty($_FILES['bukti_struk']['name'])) {
            $baru = $this->_upload('bukti_struk', 'unit_bbm');
            if ($baru) {
                if ($lama->bukti_struk)
                    @unlink(FCPATH . 'uploads/unit_bbm/' . $lama->bukti_struk);
                $bukti_struk = $baru;
            }
        }

        $liter = $this->input->post('liter');
        $harga = $this->input->post('harga_per_liter') ?: 0;
        $total = $this->input->post('total_biaya') ?: ($liter * $harga);
        $km_saat = $this->input->post('km_saat_isi') ?: null;

        // Hitung konsumsi: pakai km_terakhir dari record lama
        $km_terakhir = $lama->km_terakhir ?? null;
        $jarak_tempuh = ($km_saat && $km_terakhir && $km_saat > $km_terakhir)
            ? ($km_saat - $km_terakhir)
            : null;
        $konsumsi_auto = ($jarak_tempuh && $liter > 0)
            ? round($jarak_tempuh / $liter, 2)
            : null;

        // ✅ Fallback: jika auto-hitung gagal (km_terakhir NULL), pakai input manual dari form
        $konsumsi_input = $this->input->post('konsumsi') ?: null;
        $konsumsi = $konsumsi_auto ?? $konsumsi_input;

        $data = [
            'tanggal_isi' => $this->input->post('tanggal_isi'),
            'driver_nama' => $this->input->post('driver_nama') ?: null,
            'waktu_isi' => $this->input->post('waktu_isi') ?: null,
            'liter' => $liter,
            'harga_per_liter' => $harga,
            'total_biaya' => $total,
            'km_saat_isi' => $km_saat,
            'konsumsi' => $konsumsi,
            'km_terakhir' => $km_terakhir,
            'jarak_tempuh' => $jarak_tempuh,
            'jenis_bbm' => $this->input->post('jenis_bbm'),
            'spbu' => $this->input->post('spbu') ?: null,
            'lokasi' => $this->input->post('lokasi') ?: null,
            'bukti_struk' => $bukti_struk,
            'keterangan' => $this->input->post('keterangan') ?: null,
        ];

        if ($this->fuel->ubah($data, $id)) {
            if ($km_saat)
                $this->db->where('id', $lama->unit_id)->update('units', ['current_km' => $km_saat]);
            $this->session->set_flashdata('success', 'Data BBM berhasil diubah!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah data BBM!');
        }
        redirect($redirect);
    }

    public function hapus($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->can_delete)) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect('unit');
        }
        $data = $this->fuel->lihat_id($id);
        $redirect = 'unit/detail/' . ($data->unit_id ?? '');
        if ($data) {
            if (!empty($data->bukti_struk))
                @unlink(FCPATH . 'uploads/unit_bbm/' . $data->bukti_struk);
            if ($this->fuel->hapus($id)) {
                $this->session->set_flashdata('success', 'Data BBM berhasil dihapus!');
            } else {
                $this->session->set_flashdata('error', 'Gagal menghapus!');
            }
        }
        redirect($redirect);
    }

    private function _upload($field, $folder)
    {
        $path = FCPATH . 'uploads/' . $folder . '/';
        if (!is_dir($path))
            mkdir($path, 0755, true);
        $config = ['upload_path' => $path, 'allowed_types' => 'jpg|jpeg|png', 'max_size' => 5120, 'file_name' => $folder . '_' . time() . '_' . rand(100, 999), 'file_ext_tolower' => true];
        $this->upload->initialize($config);
        if ($this->upload->do_upload($field))
            return $this->upload->data('file_name');
        $this->session->set_flashdata('error', 'Upload gagal: ' . strip_tags($this->upload->display_errors()));
        return false;
    }
}