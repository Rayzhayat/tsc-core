<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * M_register — Model untuk sistem pendaftaran karyawan baru
 * Menangani: simpan request, list pending, approve, reject
 */
class M_register extends CI_Model
{

    // ── Simpan pengajuan registrasi baru ──────────────────────

    public function simpan_request($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('register_requests', $data);
    }

    public function get_insert_id()
    {
        return $this->db->insert_id();
    }

    // ── Cek duplikat NIK (di pengguna + register_requests pending) ──

    public function nik_sudah_ada($nik)
    {
        // Cek di pengguna aktif
        $di_pengguna = $this->db->get_where('pengguna', ['nik' => $nik])->num_rows() > 0;
        if ($di_pengguna) return 'pengguna';

        // Cek di request yang masih pending
        $di_request = $this->db->get_where('register_requests', [
            'nik'    => $nik,
            'status' => 'pending',
        ])->num_rows() > 0;
        if ($di_request) return 'pending';

        return false;
    }

    // ── List requests dengan filter ──────────────────────────

    public function list_requests($status = 'pending', $keyword = '', $limit = 25, $offset = 0)
    {
        $this->_build_list_query($status, $keyword);
        $this->db->limit($limit, $offset);
        $this->db->order_by('created_at', 'ASC');
        return $this->db->get('register_requests')->result();
    }

    public function hitung_requests($status = 'pending', $keyword = '')
    {
        $this->_build_list_query($status, $keyword);
        return $this->db->count_all_results('register_requests');
    }

    private function _build_list_query($status, $keyword)
    {
        if ($status !== 'all') {
            $this->db->where('status', $status);
        }
        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db->like('nama', $keyword);
            $this->db->or_like('nik', $keyword);
            $this->db->group_end();
        }
    }

    // ── Ambil satu request ────────────────────────────────────

    public function get_by_id($id)
    {
        return $this->db->get_where('register_requests', ['id' => $id])->row();
    }

    // ── Hitung pending (untuk badge notifikasi) ───────────────

    public function hitung_pending()
    {
        return $this->db->get_where('register_requests', ['status' => 'pending'])->num_rows();
    }

    // ── Approve → pindahkan ke tabel pengguna ─────────────────

    public function approve($id, $reviewer_id, $catatan = '')
    {
        $req = $this->get_by_id($id);
        if (!$req || $req->status !== 'pending') return false;

        $this->db->trans_start();

        // Generate username unik dari nama
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $req->nama));
        $username = $base;
        $counter  = 1;
        while ($this->db->get_where('pengguna', ['username' => $username])->num_rows() > 0) {
            $username = $base . $counter++;
        }

        // Insert ke pengguna
        $pengguna_data = [
            'nik'               => $req->nik,
            'nama'              => $req->nama,
            'tanggal_lahir'     => $req->tanggal_lahir,
            'username'          => $username,
            'password'          => $req->password_hash,
            'user_level'        => $req->user_level,
            'status_akun'       => 'aktif',
            'foto_ktp'          => $req->foto_ktp,
            'foto_profil'       => $req->foto_profil,
            'golongan'          => $req->golongan,
            'status_kepegawaian'=> $req->status_kepegawaian,
            'group_karyawan'    => $req->group_karyawan,
            'tanggal_join'      => $req->tanggal_join ?: date('Y-m-d'),
            'jatah_cuti'        => 12,
            'sisa_cuti'         => 12,
        ];
        $this->db->insert('pengguna', $pengguna_data);

        // Update status request
        $this->db->where('id', $id);
        $this->db->update('register_requests', [
            'status'       => 'approved',
            'catatan_admin'=> $catatan,
            'reviewed_by'  => $reviewer_id,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ── Reject ────────────────────────────────────────────────

    public function reject($id, $reviewer_id, $catatan = '')
    {
        $req = $this->get_by_id($id);
        if (!$req || $req->status !== 'pending') return false;

        $this->db->where('id', $id);
        return $this->db->update('register_requests', [
            'status'       => 'rejected',
            'catatan_admin'=> $catatan,
            'reviewed_by'  => $reviewer_id,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Hapus request (opsional, hanya superadmin) ────────────

    public function hapus($id)
    {
        return $this->db->delete('register_requests', ['id' => $id]);
    }
}