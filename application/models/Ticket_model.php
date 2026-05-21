<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket_model extends CI_Model
{

    const TABLE = 'tickets';
    const TABLE_LOG = 'ticket_logs';

    // ── Generate kode unik TSC-TICKET-YYYYMMDD-XXXX ──────────────
    public function generate_kode()
    {
        $prefix = 'TSC-TICKET-' . date('Ymd') . '-';
        $last = $this->db
            ->like('kode', $prefix, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get(self::TABLE)->row();

        $seq = $last ? ((int) substr($last->kode, -4) + 1) : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ── Submit ticket baru ────────────────────────────────────────
    public function create($data)
    {
        $data['kode'] = $this->generate_kode();
        $data['status'] = 'open';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->insert(self::TABLE, $data);
        $id = $this->db->insert_id();

        $this->add_log($id, 'Ticket dibuat', null, 'open', null, $data['submitted_by']);
        return $id;
    }

    // ── Get semua ticket (admin: semua | user: milik sendiri) ─────
    public function get_all($filter = [])
    {
        $this->db->select('t.*, 
            u.nama AS submitter_nama,
            a.nama AS handler_nama')
            ->from(self::TABLE . ' t')
            ->join('pengguna u', 'u.id = t.submitted_by', 'left')
            ->join('pengguna a', 'a.id = t.handled_by', 'left');

        if (!empty($filter['submitted_by'])) {
            $this->db->where('t.submitted_by', $filter['submitted_by']);
        }
        if (!empty($filter['status'])) {
            $this->db->where('t.status', $filter['status']);
        }
        if (!empty($filter['priority'])) {
            $this->db->where('t.priority', $filter['priority']);
        }

        return $this->db->order_by('t.created_at', 'DESC')->get()->result();
    }

    // ── Get single ticket by ID ───────────────────────────────────
    public function get_by_id($id)
    {
        return $this->db->select('t.*, 
            u.nama AS submitter_nama,
            a.nama AS handler_nama')
            ->from(self::TABLE . ' t')
            ->join('pengguna u', 'u.id = t.submitted_by', 'left')
            ->join('pengguna a', 'a.id = t.handled_by', 'left')
            ->where('t.id', $id)
            ->get()->row();
    }

    // ── Update status ticket ──────────────────────────────────────
    public function update_status($id, $new_status, $catatan_admin, $handler_id)
    {
        $ticket = $this->get_by_id($id);
        if (!$ticket)
            return false;

        $update = [
            'status' => $new_status,
            'catatan_admin' => $catatan_admin,
            'handled_by' => $handler_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($new_status === 'resolved' || $new_status === 'closed') {
            $update['resolved_at'] = date('Y-m-d H:i:s');
        }

        $this->db->where('id', $id)->update(self::TABLE, $update);

        $this->add_log(
            $id,
            'Status diubah ke ' . strtoupper($new_status),
            $ticket->status,
            $new_status,
            $catatan_admin,
            $handler_id
        );

        return $ticket; // return ticket lama (untuk notif email)
    }

    // ── Log history ───────────────────────────────────────────────
    public function add_log($ticket_id, $action, $old_status, $new_status, $catatan, $by_user_id)
    {
        $this->db->insert(self::TABLE_LOG, [
            'ticket_id' => $ticket_id,
            'action' => $action,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'catatan' => $catatan,
            'by_user_id' => $by_user_id,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Get logs by ticket ID ─────────────────────────────────────
    public function get_logs($ticket_id)
    {
        return $this->db->select('tl.*, p.nama AS by_nama')
            ->from(self::TABLE_LOG . ' tl')
            ->join('pengguna p', 'p.id = tl.by_user_id', 'left')
            ->where('tl.ticket_id', $ticket_id)
            ->order_by('tl.created_at', 'ASC')
            ->get()->result();
    }

    // ── Summary counts untuk dashboard ───────────────────────────
    public function get_summary($submitted_by = null)
    {
        $statuses = ['open', 'in_progress', 'resolved', 'closed'];
        $summary = [];

        foreach ($statuses as $s) {
            $q = $this->db->where('status', $s);
            if ($submitted_by)
                $q = $this->db->where('submitted_by', $submitted_by);
            $summary[$s] = $this->db->count_all_results(self::TABLE);
        }

        return $summary;
    }

    // ── Get admin emails untuk notifikasi ────────────────────────
    // Email superadmin di-hardcode di config — tidak butuh kolom email di tabel pengguna
    public function get_admin_emails()
    {
        $email = $this->config->item('superadmin_email');

        // Support multiple email (pisah koma di config)
        $emails = array_filter(array_map('trim', explode(',', $email)));

        $result = [];
        foreach ($emails as $e) {
            $obj = new stdClass();
            $obj->email = $e;
            $obj->nama = 'Superadmin TSC';
            $result[] = $obj;
        }
        return $result;
    }
}