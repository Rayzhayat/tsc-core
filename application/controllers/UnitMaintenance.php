<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UnitMaintenance extends CI_Controller
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
        $this->load->model('M_unit_maintenance', 'maintenance');
        $this->load->library('upload');
    }

    public function proses_tambah()
    {
        $unit_id = $this->input->post('unit_id');
        $redirect = $this->input->post('redirect_to') ?: 'unit/detail/' . $unit_id;

        $bukti_nota = null;
        if (!empty($_FILES['bukti_nota']['name'])) {
            $bukti_nota = $this->_upload('bukti_nota', 'unit_service');
            if (!$bukti_nota)
                return redirect($redirect);
        }

        $km_saat = $this->input->post('km_saat_service') ?: null;
        $next_km = $this->input->post('next_service_km') ?: null;

        $data = [
            'unit_id' => $unit_id,
            'tanggal_service' => $this->input->post('tanggal_service'),
            'km_saat_service' => $km_saat,
            'jenis_service' => $this->input->post('jenis_service'),
            'bengkel' => $this->input->post('bengkel') ?: null,
            'teknisi' => $this->input->post('teknisi') ?: null,
            'biaya' => $this->input->post('biaya') ?: 0,
            'parts_diganti' => $this->input->post('parts_diganti') ?: null,
            'next_service_km' => $next_km,
            'keterangan' => $this->input->post('keterangan') ?: null,
            'bukti_nota' => $bukti_nota,
            'created_by' => $this->session->userdata('login')['username'] ?? '',
        ];

        if ($this->maintenance->tambah($data)) {
            // Update current_km & next_service_km di tabel units
            $update_unit = [];
            if ($km_saat)
                $update_unit['current_km'] = $km_saat;
            if ($next_km)
                $update_unit['next_service_km'] = $next_km;
            if ($km_saat)
                $update_unit['last_service_km'] = $km_saat;
            $update_unit['last_service_date'] = $this->input->post('tanggal_service');
            if (!empty($update_unit)) {
                $this->db->where('id', $unit_id)->update('units', $update_unit);
            }
            $this->session->set_flashdata('success', 'Histori service berhasil disimpan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan service!');
        }
        redirect($redirect);
    }

    public function proses_ubah($id)
    {
        $lama = $this->maintenance->lihat_id($id);
        $redirect = $this->input->post('redirect_to') ?: 'unit/detail/' . ($lama->unit_id ?? '');
        if (!$lama) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect($redirect);
        }

        $bukti_nota = $lama->bukti_nota;
        if (!empty($_FILES['bukti_nota']['name'])) {
            $baru = $this->_upload('bukti_nota', 'unit_service');
            if ($baru) {
                if ($lama->bukti_nota)
                    @unlink(FCPATH . 'uploads/unit_service/' . $lama->bukti_nota);
                $bukti_nota = $baru;
            }
        }

        $km_saat = $this->input->post('km_saat_service') ?: null;
        $next_km = $this->input->post('next_service_km') ?: null;

        $data = [
            'tanggal_service' => $this->input->post('tanggal_service'),
            'km_saat_service' => $km_saat,
            'jenis_service' => $this->input->post('jenis_service'),
            'bengkel' => $this->input->post('bengkel') ?: null,
            'teknisi' => $this->input->post('teknisi') ?: null,
            'biaya' => $this->input->post('biaya') ?: 0,
            'parts_diganti' => $this->input->post('parts_diganti') ?: null,
            'next_service_km' => $next_km,
            'keterangan' => $this->input->post('keterangan') ?: null,
            'bukti_nota' => $bukti_nota,
        ];

        if ($this->maintenance->ubah($data, $id)) {
            // Update units table jika ada perubahan KM
            $update_unit = [];
            if ($km_saat)
                $update_unit['current_km'] = $km_saat;
            if ($next_km)
                $update_unit['next_service_km'] = $next_km;
            if (!empty($update_unit))
                $this->db->where('id', $lama->unit_id)->update('units', $update_unit);
            $this->session->set_flashdata('success', 'Histori service berhasil diubah!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah service!');
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
        $data = $this->maintenance->lihat_id($id);
        $redirect = 'unit/detail/' . ($data->unit_id ?? '');
        if ($data) {
            if (!empty($data->bukti_nota))
                @unlink(FCPATH . 'uploads/unit_service/' . $data->bukti_nota);
            if ($this->maintenance->hapus($id)) {
                $this->session->set_flashdata('success', 'Data service berhasil dihapus!');
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
        $config = ['upload_path' => $path, 'allowed_types' => 'jpg|jpeg|png|pdf', 'max_size' => 5120, 'file_name' => $folder . '_' . time() . '_' . rand(100, 999), 'file_ext_tolower' => true];
        $this->upload->initialize($config);
        if ($this->upload->do_upload($field))
            return $this->upload->data('file_name');
        $this->session->set_flashdata('error', 'Upload gagal: ' . strip_tags($this->upload->display_errors()));
        return false;
    }
}