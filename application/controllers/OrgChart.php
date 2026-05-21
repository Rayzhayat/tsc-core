<?php
defined('BASEPATH') or exit('No direct script access allowed');

class OrgChart extends CI_Controller
{
    private string $user_level;
    private int $user_id;
    private string $user_nama;

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $login = $this->session->userdata('login');
        $this->user_level = $login['user_level'];
        $this->user_id = (int) $login['id'];
        $this->user_nama = $login['nama'];

        $this->load->model('M_org_chart');
    }

    // ════════════════════════════════════════
    // PUBLIC: Lihat chart (semua role yg diizinkan)
    // ════════════════════════════════════════

    public function index()
    {
        if (!$this->M_org_chart->can_view($this->user_level)) {
            show_error('Akses ditolak. Org chart tidak tersedia untuk level Anda.', 403);
        }

        $nodes = $this->M_org_chart->get_all_nodes();
        $tree = $this->M_org_chart->build_tree($nodes);

        $data = [
            'title' => 'Struktur Organisasi',
            'aktif' => 'orgchart',
            'tree' => $tree,
            'can_manage' => in_array($this->user_level, ['superadmin', 'head_of_departemen']),
            'is_superadmin' => ($this->user_level === 'superadmin'),
        ];

        $this->load->view('org_chart/index', $data);
    }

    // ════════════════════════════════════════
    // MANAGE: Halaman kelola node
    // ════════════════════════════════════════

    public function manage()
    {
        $this->_require_manage_access();

        if ($this->user_level === 'superadmin') {
            $nodes = $this->M_org_chart->get_all_nodes();
        } else {
            $dept = $this->M_org_chart->get_dept_of_user($this->user_id);
            if (!$dept) {
                show_error('Anda belum terdaftar di struktur organisasi. Hubungi Superadmin.', 403);
            }
            $nodes = $this->M_org_chart->get_nodes_by_dept($dept);
        }

        // ── Pre-compute depth map (O(n), bukan O(n²)) ──
        $depth_map = $this->_build_depth_map($nodes);

        $tree = $this->M_org_chart->build_tree($nodes);
        $visibility = $this->M_org_chart->get_visibility();
        $pengguna = $this->M_org_chart->get_pengguna_list();

        $data = [
            'title' => 'Kelola Struktur Organisasi',
            'aktif' => 'orgchart',
            'tree' => $tree,
            'nodes_flat' => $nodes,
            'depth_map' => $depth_map,   // <-- pass ke view
            'visibility' => $visibility,
            'pengguna' => $pengguna,
            'is_superadmin' => ($this->user_level === 'superadmin'),
        ];

        $this->load->view('org_chart/manage', $data);
    }

    // ════════════════════════════════════════
    // TAMBAH NODE
    // ════════════════════════════════════════

    public function tambah()
    {
        $this->_require_manage_access();

        $parent_id = $this->input->post('parent_id') ?: null;
        $departemen = trim($this->input->post('departemen') ?? '');
        $jabatan = trim($this->input->post('jabatan') ?? '');
        $pengguna_id = $this->input->post('pengguna_id') ?: null;
        $urutan = (int) ($this->input->post('urutan') ?? 0);

        if (!$jabatan) {
            $this->session->set_flashdata('error', 'Jabatan tidak boleh kosong!');
            redirect('org_chart/manage');
        }

        if ($this->user_level === 'head_of_departemen') {
            $dept_saya = $this->M_org_chart->get_dept_of_user($this->user_id);
            if ($departemen && $departemen !== $dept_saya) {
                show_error('Anda hanya bisa menambah node di departemen sendiri.', 403);
            }
            $departemen = $dept_saya;
        }

        $insert = [
            'parent_id' => $parent_id,
            'departemen' => $departemen ?: null,
            'jabatan' => $jabatan,
            'pengguna_id' => $pengguna_id,
            'urutan' => $urutan,
            'is_aktif' => 1,
            'created_by' => $this->user_id,
            'updated_by' => $this->user_id,
        ];

        if ($this->M_org_chart->tambah_node($insert)) {
            $this->session->set_flashdata('success', "Posisi <strong>$jabatan</strong> berhasil ditambahkan!");
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan node!');
        }

        redirect('org_chart/manage');
    }

    // ════════════════════════════════════════
    // UBAH NODE
    // ════════════════════════════════════════

    public function ubah($id)
    {
        $this->_require_manage_access();

        $node = $this->M_org_chart->get_node($id);
        if (!$node)
            show_404();

        if ($this->user_level === 'head_of_departemen') {
            $dept_saya = $this->M_org_chart->get_dept_of_user($this->user_id);
            if ($node->departemen !== $dept_saya) {
                show_error('Akses ditolak — node di luar departemen Anda.', 403);
            }
        }

        $jabatan = trim($this->input->post('jabatan') ?? $node->jabatan);
        $departemen = trim($this->input->post('departemen') ?? $node->departemen);
        $pengguna_id = $this->input->post('pengguna_id') ?: null;
        $urutan = (int) ($this->input->post('urutan') ?? $node->urutan);
        $parent_id = $this->input->post('parent_id') ?: null;

        $update = [
            'jabatan' => $jabatan,
            'departemen' => $departemen ?: null,
            'pengguna_id' => $pengguna_id,
            'urutan' => $urutan,
            'updated_by' => $this->user_id,
        ];

        if ($this->user_level === 'superadmin') {
            $update['parent_id'] = $parent_id;
        }

        if ($this->M_org_chart->ubah_node((int) $id, $update)) {
            $this->session->set_flashdata('success', "Posisi <strong>$jabatan</strong> berhasil diubah!");
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah node!');
        }

        redirect('org_chart/manage');
    }

    // ════════════════════════════════════════
    // HAPUS NODE (soft delete)
    // ════════════════════════════════════════

    public function hapus($id)
    {
        if ($this->user_level !== 'superadmin') {
            show_error('Hanya Superadmin yang dapat menghapus node.', 403);
        }

        $node = $this->M_org_chart->get_node($id);
        if (!$node)
            show_404();

        if ($this->M_org_chart->punya_anak((int) $id)) {
            $this->session->set_flashdata('error', 'Tidak bisa hapus! Node ini masih punya posisi di bawahnya. Hapus dulu anak-anaknya.');
            redirect('org_chart/manage');
        }

        if ($this->M_org_chart->hapus_node((int) $id)) {
            $this->session->set_flashdata('success', "Posisi <strong>{$node->jabatan}</strong> berhasil dihapus.");
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus node!');
        }

        redirect('org_chart/manage');
    }

    // ════════════════════════════════════════
    // SET VISIBILITY
    // ════════════════════════════════════════

    public function set_visibility()
    {
        if ($this->user_level !== 'superadmin') {
            show_error('Akses ditolak.', 403);
        }

        $levels = $this->input->post('user_level') ?: [];

        if ($this->M_org_chart->set_visibility($levels)) {
            $this->session->set_flashdata('success', 'Pengaturan visibilitas berhasil disimpan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan pengaturan!');
        }

        redirect('org_chart/manage');
    }

    // ════════════════════════════════════════
    // AJAX: get_tree
    // ════════════════════════════════════════

    public function get_tree()
    {
        if (!$this->M_org_chart->can_view($this->user_level)) {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
            return;
        }

        $nodes = $this->M_org_chart->get_all_nodes();
        $tree = $this->M_org_chart->build_tree($nodes);

        echo json_encode(['success' => true, 'tree' => $tree]);
    }

    // ════════════════════════════════════════
    // PRIVATE HELPERS
    // ════════════════════════════════════════

    private function _require_manage_access()
    {
        if (!in_array($this->user_level, ['superadmin', 'head_of_departemen'])) {
            show_error('Akses ditolak! Hanya Superadmin dan Head of Departemen yang dapat mengelola org chart.', 403);
        }
    }

    /**
     * Build depth map O(n) — hitung depth sekali untuk semua node.
     * Jauh lebih efisien daripada rekursi per-node di view.
     *
     * @param  array $nodes  array of stdClass dari model
     * @return array         [ node_id => depth ]
     */
    private function _build_depth_map(array $nodes): array
    {
        // Buat lookup id => parent_id (integer atau null)
        $parent_map = [];
        foreach ($nodes as $n) {
            $parent_map[(int) $n->id] = $n->parent_id !== null ? (int) $n->parent_id : null;
        }

        $depth_map = [];

        foreach (array_keys($parent_map) as $start_id) {
            if (isset($depth_map[$start_id]))
                continue;

            // Iteratif — tidak rekursif, tidak bisa stack overflow / memory loop
            $depth = 0;
            $current = $start_id;
            $visited = []; // guard circular reference

            while (true) {
                $visited[$current] = true;

                $parent = $parent_map[$current] ?? null;

                // Sudah sampai root, atau parent tidak ada di map
                if ($parent === null || !isset($parent_map[$parent])) {
                    $depth_map[$current] = 0;
                    break;
                }

                // Circular reference terdeteksi — putus loop
                if (isset($visited[$parent])) {
                    $depth_map[$current] = 0;
                    break;
                }

                // Parent sudah punya depth — gunakan langsung
                if (isset($depth_map[$parent])) {
                    $depth_map[$current] = $depth_map[$parent] + 1;
                    break;
                }

                $current = $parent;
                $depth++;
            }

            // Back-fill: jalan dari start_id ke atas, isi depth_map
            // berdasarkan nilai yang sudah kita temukan
            $current = $start_id;
            $chain = [];
            $visited2 = [];
            while (true) {
                if (isset($depth_map[$current]) || isset($visited2[$current]))
                    break;
                $visited2[$current] = true;
                $chain[] = $current;
                $parent = $parent_map[$current] ?? null;
                if ($parent === null || !isset($parent_map[$parent]))
                    break;
                $current = $parent;
            }

            // Isi depth_map untuk semua node di chain
            foreach (array_reverse($chain) as $node_id) {
                $p = $parent_map[$node_id] ?? null;
                if ($p === null || !isset($depth_map[$p])) {
                    $depth_map[$node_id] = 0;
                } else {
                    $depth_map[$node_id] = $depth_map[$p] + 1;
                }
            }
        }

        return $depth_map;
    }
}