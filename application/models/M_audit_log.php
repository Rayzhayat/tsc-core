<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_audit_log extends CI_Model
{
    private $table = 'tb_audit_log';

    // ── DataTables server-side ──
    public function get_datatables($filters = [])
    {
        $this->_apply_filters($filters);

        $order_col = ['id', 'created_at', 'user_nama', 'modul', 'aksi', 'keterangan'];
        $order_idx = $this->input->post('order')[0]['column'] ?? 1;
        $order_dir = $this->input->post('order')[0]['dir'] ?? 'desc';
        $this->db->order_by($order_col[$order_idx] ?? 'created_at', $order_dir);

        $length = (int) ($this->input->post('length') ?? 25);
        $start = (int) ($this->input->post('start') ?? 0);
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        return $this->db->get($this->table)->result();
    }

    // ── Log per record (dipakai di halaman detail modul lain) ──
    public function get_by_record($modul, $record_id, $limit = 50)
    {
        return $this->db->where('modul', $modul)
            ->where('record_id', $record_id)
            ->order_by('created_at', 'desc')
            ->limit($limit)
            ->get($this->table)->result();
    }

    public function count_filtered($filters = [])
    {
        $this->_apply_filters($filters);
        return $this->db->count_all_results($this->table);
    }

    public function count_all()
    {
        return $this->db->count_all_results($this->table);
    }

    private function _apply_filters($filters)
    {
        // NOTE: no $this->db->from($this->table) here on purpose.
        // get_datatables()/count_filtered() already call
        // ->get($this->table) / ->count_all_results($this->table),
        // which append their own FROM. Calling from() here too
        // duplicated the table in the compiled query and threw:
        // "Error Number: 1066 - Not unique table/alias: 'tb_audit_log'"

        if (!empty($filters['modul'])) {
            $this->db->where('modul', $filters['modul']);
        }
        if (!empty($filters['aksi'])) {
            $this->db->where('aksi', $filters['aksi']);
        }
        if (!empty($filters['user_id'])) {
            $this->db->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                ->like('keterangan', $s)
                ->or_like('user_nama', $s)
                ->or_like('record_id', $s)
                ->group_end();
        }
    }

    // ── Dropdown filter options ──
    public function get_distinct_modul()
    {
        return $this->db->select('modul')->distinct()->order_by('modul')->get($this->table)->result();
    }

    public function get_distinct_users()
    {
        return $this->db->select('user_id, user_nama')->distinct()
            ->where('user_id IS NOT NULL')
            ->order_by('user_nama')
            ->get($this->table)->result();
    }

    // ── Summary cards ──
    public function get_summary($filters = [])
    {
        $today = date('Y-m-d');

        $total = $this->db->count_all_results($this->table);

        $today_count = $this->db->where('created_at >=', $today . ' 00:00:00')
            ->where('created_at <=', $today . ' 23:59:59')
            ->count_all_results($this->table);

        $delete_count = $this->db->where('aksi', 'delete')->count_all_results($this->table);

        $active_users = $this->db->select('user_id')->distinct()
            ->where('created_at >=', date('Y-m-d', strtotime('-7 days')) . ' 00:00:00')
            ->get($this->table)->num_rows();

        return (object) [
            'total' => $total,
            'today' => $today_count,
            'delete_count' => $delete_count,
            'active_users' => $active_users,
        ];
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }
}