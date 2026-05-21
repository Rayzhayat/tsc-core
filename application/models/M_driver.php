<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_driver extends CI_Model
{
    private $table = 'drivers';

    public function lihat()
    {
        $this->db->where('deleted_at IS NULL', null, false);
        return $this->db->order_by('created_at', 'DESC')->get($this->table)->result();
    }

    public function lihat_id($id)
    {
        $this->db->where('deleted_at IS NULL', null, false);
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }


    // 🔥 NEW: Alias for compatibility with Driver_violations controller
    public function get_by_id($id)
    {
        return $this->lihat_id($id);
    }

    // 🔥 NEW: Get all drivers (for dropdowns)
    public function get_all()
    {
        $this->db->where('deleted_at IS NULL', null, false);
        return $this->db->order_by('nama_driver', 'ASC')->get($this->table)->result();
    }

    public function tambah($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function ubah($data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function hapus($id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, [
            'deleted_at' => date('Y-m-d H:i:s'),
            'status_driver' => 'nonaktif'
        ]);
    }


    public function cek_nik($nik)
    {
        return $this->db->get_where($this->table, ['nik' => $nik])->num_rows() > 0;
    }

    // 🔥 NEW: Search function for AJAX
    public function cari($keyword)
    {
        $this->db->where('deleted_at IS NULL', null, false);
        $this->db->group_start();
        $this->db->like('nama_driver', $keyword);
        $this->db->or_like('nik', $keyword);
        $this->db->or_like('sim', $keyword);
        $this->db->or_like('tipe_sim', $keyword);
        $this->db->or_like('no_hp', $keyword);
        $this->db->or_like('email', $keyword);
        $this->db->or_like('status_driver', $keyword);
        $this->db->group_end();

        return $this->db->order_by('created_at', 'DESC')->get($this->table)->result();
    }

    public function restore($id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, [
            'deleted_at' => null,
            'status_driver' => 'aktif'
        ]);
    }

    // 🔥 NEW: Get active drivers only
    public function get_active()
    {
        $this->db->where('status_driver', 'aktif');
        return $this->db->order_by('nama_driver', 'ASC')->get($this->table)->result();
    }

    public function lihat_semua_termasuk_terhapus()
    {
        return $this->db->order_by('created_at', 'DESC')->get($this->table)->result();
    }

    // 🔥 NEW: Get driver with violations count
    public function get_with_violations($driver_id)
    {
        $driver = $this->lihat_id($driver_id);

        if ($driver) {
            // Get pending violations count
            $this->db->where('driver_id', $driver_id);
            $this->db->where('status', 'pending');
            $driver->pending_violations = $this->db->count_all_results('driver_violations');

            // Get total violations
            $this->db->where('driver_id', $driver_id);
            $driver->total_violations = $this->db->count_all_results('driver_violations');
        }

        return $driver;
    }

    // 🔥 NEW: Update driver rating
    public function update_rating($driver_id, $rating)
    {
        $this->db->where('id', $driver_id);
        return $this->db->update($this->table, ['rating' => $rating]);
    }

    // 🔥 NEW: Increment total trips
    public function increment_trips($driver_id)
    {
        $this->db->set('total_trip', 'total_trip + 1', FALSE);
        $this->db->where('id', $driver_id);
        return $this->db->update($this->table);
    }

    // 🔥 NEW: Update last trip date
    public function update_last_trip($driver_id, $date = null)
    {
        $date = $date ?: date('Y-m-d');
        $this->db->where('id', $driver_id);
        return $this->db->update($this->table, ['last_trip_date' => $date]);
    }

    // 🔥 NEW: Get top rated drivers
    public function get_top_rated($limit = 5)
    {
        $this->db->where('status_driver', 'aktif');
        $this->db->order_by('rating', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result();
    }

    // 🔥 NEW: Get drivers by status
    public function get_by_status($status = 'aktif')
    {
        $this->db->where('status_driver', $status);
        return $this->db->order_by('nama_driver', 'ASC')->get($this->table)->result();
    }

    // 🔥 NEW: Get driver statistics
    public function get_statistics()
    {
        $stats = [
            'total' => $this->db->count_all($this->table),
            'aktif' => $this->db->where('status_driver', 'aktif')->count_all_results($this->table),
            'cuti' => $this->db->where('status_driver', 'cuti')->count_all_results($this->table),
            'nonaktif' => $this->db->where_in('status_driver', ['nonaktif', 'resign'])->count_all_results($this->table)
        ];

        // Average rating
        $query = $this->db->select_avg('rating')->get($this->table);
        $stats['avg_rating'] = $query->row()->rating ?? 0;

        return $stats;
    }

    // 🔥 NEW: Check SIM expiry
    public function get_sim_expiring($days = 30)
    {
        $date_limit = date('Y-m-d', strtotime("+{$days} days"));

        $this->db->where('masa_berlaku_sim <=', $date_limit);
        $this->db->where('masa_berlaku_sim >=', date('Y-m-d'));
        $this->db->where('status_driver', 'aktif');

        return $this->db->order_by('masa_berlaku_sim', 'ASC')->get($this->table)->result();
    }

    // 🔥 NEW: Get drivers with low rating
    public function get_low_rated($threshold = 3.0)
    {
        $this->db->where('rating <', $threshold);
        $this->db->where('status_driver', 'aktif');
        return $this->db->order_by('rating', 'ASC')->get($this->table)->result();
    }
}