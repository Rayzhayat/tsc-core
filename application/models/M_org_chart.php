<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_org_chart extends CI_Model
{
    // ════════════════════════════════════════
    // NODE CRUD
    // ════════════════════════════════════════

    /**
     * Ambil semua node aktif + join nama & foto dari pengguna
     * Return array flat, disusun ke tree di controller
     */
    public function get_all_nodes()
    {
        $this->db->select('
            n.id, n.parent_id, n.departemen, n.jabatan,
            n.pengguna_id, n.urutan, n.is_aktif,
            p.nama        AS pengguna_nama,
            p.foto_profil AS pengguna_foto,
            p.user_level  AS pengguna_level,
            p.golongan    AS pengguna_golongan
        ');
        $this->db->from('tb_org_node n');
        $this->db->join('pengguna p', 'p.id = n.pengguna_id', 'left');
        $this->db->where('n.is_aktif', 1);
        $this->db->order_by('n.parent_id', 'ASC');
        $this->db->order_by('n.urutan',    'ASC');
        $this->db->order_by('n.id',        'ASC');
        return $this->db->get()->result();
    }

    /**
     * Ambil node milik satu departemen — untuk head_of_departemen
     * Cari semua node yang departemen-nya sama dengan departemen si user
     */
    public function get_nodes_by_dept(string $departemen)
    {
        $this->db->select('
            n.id, n.parent_id, n.departemen, n.jabatan,
            n.pengguna_id, n.urutan, n.is_aktif,
            p.nama        AS pengguna_nama,
            p.foto_profil AS pengguna_foto,
            p.user_level  AS pengguna_level,
            p.golongan    AS pengguna_golongan
        ');
        $this->db->from('tb_org_node n');
        $this->db->join('pengguna p', 'p.id = n.pengguna_id', 'left');
        $this->db->where('n.departemen', $departemen);
        $this->db->where('n.is_aktif', 1);
        $this->db->order_by('n.urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function get_node($id)
    {
        return $this->db->get_where('tb_org_node', ['id' => $id])->row();
    }

    public function tambah_node(array $data): bool
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('tb_org_node', $data);
    }

    public function ubah_node(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('tb_org_node', $data);
    }

    public function hapus_node(int $id): bool
    {
        // Soft delete — set is_aktif = 0
        // Anak-anak node tidak ikut terhapus, biarkan tampil tanpa parent (akan di-handle di view)
        return $this->ubah_node($id, ['is_aktif' => 0]);
    }

    /**
     * Cek apakah satu node punya anak yang masih aktif
     */
    public function punya_anak(int $id): bool
    {
        $this->db->where('parent_id', $id);
        $this->db->where('is_aktif', 1);
        return $this->db->count_all_results('tb_org_node') > 0;
    }

    // ════════════════════════════════════════
    // TREE BUILDER
    // ════════════════════════════════════════

    /**
     * Konversi flat array ke nested tree
     * Return array tree siap render
     */
    public function build_tree(array $nodes, $parent_id = null): array
    {
        $tree = [];
        foreach ($nodes as $node) {
            $pid = $node->parent_id === null ? null : (int)$node->parent_id;
            $target = $parent_id === null ? null : (int)$parent_id;
            if ($pid === $target) {
                $node->children = $this->build_tree($nodes, $node->id);
                $tree[] = $node;
            }
        }
        return $tree;
    }

    /**
     * Cari departemen dari node yang dipegang pengguna_id tertentu
     * Dipakai buat head_of_departemen agar tau departemennya sendiri
     */
    public function get_dept_of_user(int $pengguna_id): ?string
    {
        $row = $this->db->select('departemen')
            ->where('pengguna_id', $pengguna_id)
            ->where('is_aktif', 1)
            ->limit(1)
            ->get('tb_org_node')->row();
        return $row ? $row->departemen : null;
    }

    // ════════════════════════════════════════
    // VISIBILITY
    // ════════════════════════════════════════

    public function get_visibility(): array
    {
        return array_column(
            $this->db->get('tb_org_visibility')->result_array(),
            'user_level'
        );
    }

    public function set_visibility(array $levels): bool
    {
        $this->db->trans_start();
        $this->db->truncate('tb_org_visibility');
        foreach (array_unique(array_filter($levels)) as $lvl) {
            $this->db->insert('tb_org_visibility', [
                'user_level' => $lvl,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function can_view(string $user_level): bool
    {
        // Superadmin selalu bisa lihat
        if ($user_level === 'superadmin') return true;
        $this->db->where('user_level', $user_level);
        return $this->db->count_all_results('tb_org_visibility') > 0;
    }

    // ════════════════════════════════════════
    // HELPER: daftar pengguna untuk dropdown assign
    // ════════════════════════════════════════

    public function get_pengguna_list(): array
    {
        return $this->db->select('id, nama, user_level, golongan')
            ->order_by('nama', 'ASC')
            ->get('pengguna')
            ->result();
    }
}