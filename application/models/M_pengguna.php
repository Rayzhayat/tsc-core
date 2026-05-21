<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_pengguna extends CI_Model
{

    public function lihat()
    {
        $this->db->order_by('id', 'ASC');
        return $this->db->get('pengguna')->result();
    }

    public function lihat_id($id)
    {
        return $this->db->get_where('pengguna', ['id' => $id])->row();
    }

    public function tambah($data)
    {
        return $this->db->insert('pengguna', $data);
    }

    public function ubah($data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update('pengguna', $data);
    }

    public function hapus($id)
    {
        return $this->db->delete('pengguna', ['id' => $id]);
    }

    // ── Filter & pagination ──

    public function hitung_filter($keyword = '', $level = '', $group = '')
    {
        $this->_build_filter_query($keyword, $level, $group);
        return $this->db->count_all_results('pengguna');
    }

    public function filter($keyword = '', $level = '', $group = '', $limit = 25, $offset = 0)
    {
        $this->db->select('*');
        $this->_build_filter_query($keyword, $level, $group);
        $this->db->limit($limit, $offset);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('pengguna')->result();
    }

    private function _build_filter_query($keyword, $level, $group = '')
    {
        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db->like('nama', $keyword);
            $this->db->or_like('nik', $keyword);
            $this->db->or_like('golongan', $keyword);
            $this->db->group_end();
        }
        if (!empty($level)) {
            $this->db->where('user_level', $level);
        }
        if (!empty($group)) {
            $this->db->where('group_karyawan', $group);
        }
    }

    // ── Cek duplikat ──

    public function cek_nik($nik, $id = null)
    {
        $this->db->where('nik', $nik);
        if ($id)
            $this->db->where('id !=', $id);
        return $this->db->get('pengguna')->num_rows() > 0;
    }

    public function cek_username($username, $id = null)
    {
        $this->db->where('username', $username);
        if ($id)
            $this->db->where('id !=', $id);
        return $this->db->get('pengguna')->num_rows() > 0;
    }

    // ── Ulang tahun ──

    public function ulang_tahun_hari_ini()
    {
        $today_md = date('m-d');
        $this->db->select('id, nama, tanggal_lahir, foto_profil');
        $this->db->where("DATE_FORMAT(tanggal_lahir, '%m-%d') =", $today_md);
        return $this->db->get('pengguna')->result();
    }

    // ════════════════════════════════════════
    // MULTI GROUP AKSES (BARU)
    // ════════════════════════════════════════

    /**
     * Ambil semua group yang boleh dilihat laporannya oleh user ini
     * Return: array of string, e.g. ['TSF Staff', 'Sinar Boga Staff']
     */
    public function get_group_akses($user_id)
    {
        $rows = $this->db->get_where('pengguna_group_akses', ['user_id' => $user_id])->result();
        return array_map(fn($r) => $r->group_karyawan, $rows);
    }

    /**
     * Set ulang semua group akses untuk satu user (replace all)
     * $groups = array of string group names
     */
    public function set_group_akses($user_id, array $groups)
    {
        $this->db->trans_start();

        // Hapus semua akses lama
        $this->db->delete('pengguna_group_akses', ['user_id' => $user_id]);

        // Insert akses baru (hanya yang valid & unik)
        $groups = array_unique(array_filter($groups));
        foreach ($groups as $group) {
            $this->db->insert('pengguna_group_akses', [
                'user_id' => $user_id,
                'group_karyawan' => $group,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Hapus semua group akses milik satu user
     */
    public function hapus_group_akses($user_id)
    {
        return $this->db->delete('pengguna_group_akses', ['user_id' => $user_id]);
    }

    // ════════════════════════════════════════
    // CUTI
    // ════════════════════════════════════════

    public function get_cuti($user_id, $status = null)
    {
        $this->db->where('user_id', $user_id);
        if ($status)
            $this->db->where('status', $status);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('karyawan_cuti')->result();
    }

    public function get_cuti_id($id)
    {
        return $this->db->get_where('karyawan_cuti', ['id' => $id])->row();
    }

    public function tambah_cuti($data)
    {
        $start = new DateTime($data['tanggal_mulai']);
        $end = new DateTime($data['tanggal_selesai']);
        $jumlah = $start->diff($end)->days + 1;

        $data['jumlah_hari'] = $jumlah;
        $data['status'] = 'Pending';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->insert('karyawan_cuti', $data);
    }

    public function update_status_cuti($id, $status, $approved_by, $catatan = '')
    {
        $cuti = $this->get_cuti_id($id);
        if (!$cuti)
            return false;

        $this->db->trans_start();

        $this->db->where('id', $id);
        $this->db->update('karyawan_cuti', [
            'status' => $status,
            'approved_by' => $approved_by,
            'catatan_admin' => $catatan,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($status === 'Disetujui' && $cuti->status === 'Pending') {
            $this->db->query(
                "UPDATE pengguna SET sisa_cuti = GREATEST(sisa_cuti - ?, 0) WHERE id = ?",
                [$cuti->jumlah_hari, $cuti->user_id]
            );
        }

        if ($status === 'Ditolak' && $cuti->status === 'Disetujui') {
            $this->db->query(
                "UPDATE pengguna SET sisa_cuti = LEAST(sisa_cuti + ?, jatah_cuti) WHERE id = ?",
                [$cuti->jumlah_hari, $cuti->user_id]
            );
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function hapus_cuti($id)
    {
        $cuti = $this->get_cuti_id($id);
        if (!$cuti || $cuti->status !== 'Pending')
            return false;
        return $this->db->delete('karyawan_cuti', ['id' => $id]);
    }

    public function hitung_cuti_terpakai($user_id, $tahun = null)
    {
        $tahun = $tahun ?: date('Y');
        $this->db->select_sum('jumlah_hari');
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'Disetujui');
        $this->db->where("YEAR(tanggal_mulai)", $tahun);
        $result = $this->db->get('karyawan_cuti')->row();
        return (int) ($result->jumlah_hari ?? 0);
    }

    public function reset_jatah_cuti_tahunan()
    {
        return $this->db->query("UPDATE pengguna SET sisa_cuti = jatah_cuti");
    }

    // ════════════════════════════════════════
    // DOKUMEN KARYAWAN
    // ════════════════════════════════════════

    public function get_dokumen($user_id, $jenis = null)
    {
        $this->db->select('karyawan_dokumen.*, pengguna.nama as uploader_nama');
        $this->db->from('karyawan_dokumen');
        $this->db->join('pengguna', 'pengguna.id = karyawan_dokumen.uploaded_by', 'left');
        $this->db->where('karyawan_dokumen.user_id', $user_id);
        if ($jenis)
            $this->db->where('jenis_dokumen', $jenis);
        $this->db->order_by('karyawan_dokumen.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_dokumen_id($id)
    {
        return $this->db->get_where('karyawan_dokumen', ['id' => $id])->row();
    }

    public function tambah_dokumen($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('karyawan_dokumen', $data);
    }

    public function hapus_dokumen($id)
    {
        return $this->db->delete('karyawan_dokumen', ['id' => $id]);
    }

    public function hitung_sp($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where_in('jenis_dokumen', ['SP1', 'SP2', 'SP3', 'Surat Peringatan Lainnya']);
        return $this->db->count_all_results('karyawan_dokumen');
    }

    public function get_dokumen_hampir_expired($hari = 30)
    {
        $this->db->select('karyawan_dokumen.*, pengguna.nama as karyawan_nama, pengguna.nik');
        $this->db->from('karyawan_dokumen');
        $this->db->join('pengguna', 'pengguna.id = karyawan_dokumen.user_id');
        $this->db->where('karyawan_dokumen.tanggal_expired IS NOT NULL');
        $this->db->where("karyawan_dokumen.tanggal_expired BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL {$hari} DAY)");
        $this->db->order_by('karyawan_dokumen.tanggal_expired', 'ASC');
        return $this->db->get()->result();
    }

    // ════════════════════════════════════════
    // PERFORMA
    // ════════════════════════════════════════

    public function get_performa($user_id = null, $group = null)
    {
        if ($user_id) {
            return $this->db->get_where('v_performa_karyawan', ['user_id' => $user_id])->row();
        }
        $this->db->select('v.*, p.group_karyawan, p.status_kepegawaian, p.golongan, p.tanggal_join, p.foto_profil');
        $this->db->from('v_performa_karyawan v');
        $this->db->join('pengguna p', 'p.id = v.user_id');
        if (!empty($group)) {
            $this->db->where('p.group_karyawan', $group);
        }
        $this->db->order_by('v.persen_kehadiran', 'DESC');
        return $this->db->get()->result();
    }

    public function get_cuti_pending_semua()
    {
        $this->db->select('karyawan_cuti.*, pengguna.nama as karyawan_nama, pengguna.nik, pengguna.foto_profil');
        $this->db->from('karyawan_cuti');
        $this->db->join('pengguna', 'pengguna.id = karyawan_cuti.user_id');
        $this->db->where('karyawan_cuti.status', 'Pending');
        $this->db->order_by('karyawan_cuti.created_at', 'ASC');
        return $this->db->get()->result();
    }
}