<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_ftl_non_spx extends CI_Model
{
    private $table = 'ftl_non_spx';

    // ============================================
    // GET ALL (exclude soft deleted)
    // ============================================
    public function lihat()
    {
        $this->db->select('f.*, c.nama AS nama_customer, v.nama_vendor');
        $this->db->from($this->table . ' f');
        $this->db->join('customer c', 'c.id = f.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = f.vendor_id', 'left');
        $this->db->where('f.deleted_at IS NULL', null, false);
        $this->db->order_by('f.target_standby_date', 'ASC');  // ← ganti dari created_at DESC
        $this->db->order_by('f.target_standby_time', 'ASC');
        $this->db->order_by('f.created_at', 'DESC');
        return $this->db->get()->result();
    }

    // ============================================
    // AUTO SYNC DRIVER KE MASTER DRIVER
    // ============================================
    public function cek_atau_tambah_driver($nama_driver, $no_hp = null)
    {
        $nama_driver = trim($nama_driver);
        if (empty($nama_driver)) {
            return ['is_new' => false, 'driver_id' => null];
        }

        $existing = $this->db
            ->where("LOWER(nama_driver) = LOWER('" . $this->db->escape_str($nama_driver) . "')", null, false)
            ->get('drivers')
            ->row();

        if ($existing) {
            return ['is_new' => false, 'driver_id' => (int) $existing->id];
        }

        $fields = $this->db->list_fields('drivers');
        $data = ['nama_driver' => $nama_driver];

        if (in_array('no_hp', $fields))
            $data['no_hp'] = $no_hp ?: null;
        if (in_array('status_driver', $fields))
            $data['status_driver'] = 'aktif';
        if (in_array('created_at', $fields))
            $data['created_at'] = date('Y-m-d H:i:s');
        if (in_array('rating', $fields))
            $data['rating'] = 0;
        if (in_array('total_trip', $fields))
            $data['total_trip'] = 0;

        $this->db->insert('drivers', $data);
        $new_id = $this->db->insert_id();

        return ['is_new' => true, 'driver_id' => (int) $new_id];
    }

    public function get_sla_summary($date_from = null, $date_to = null)
    {
        $where = 'deleted_at IS NULL
        AND actual_tiba_bongkar_date IS NOT NULL
        AND target_arrival_date IS NOT NULL';

        if (!empty($date_from) && !empty($date_to)) {
            $df = $this->db->escape_str($date_from);
            $dt = $this->db->escape_str($date_to);
            $where .= " AND actual_tiba_bongkar_date BETWEEN '{$df}' AND '{$dt}'";
        }

        $this->db->select("
        COUNT(*) as total_completed,
        SUM(CASE
            WHEN actual_tiba_bongkar_date <= target_arrival_date
            AND (actual_tiba_bongkar_time <= target_arrival_time OR target_arrival_time IS NULL)
            THEN 1 ELSE 0 END) as ontime,
        SUM(CASE
            WHEN actual_tiba_bongkar_date > target_arrival_date
            OR (actual_tiba_bongkar_date = target_arrival_date AND actual_tiba_bongkar_time > target_arrival_time)
            THEN 1 ELSE 0 END) as late,
        AVG(TIMESTAMPDIFF(MINUTE,
            CONCAT(actual_depart_date, ' ', IFNULL(actual_depart_time, '00:00')),
            CONCAT(actual_tiba_bongkar_date, ' ', IFNULL(actual_tiba_bongkar_time, '00:00'))
        )) as avg_transit_minutes
    ", false);
        $this->db->from($this->table);
        $this->db->where($where, null, false);
        return $this->db->get()->row();
    }

    public function get_overdue()
    {
        $this->db->select('f.*, c.nama AS nama_customer, v.nama_vendor');
        $this->db->from($this->table . ' f');
        $this->db->join('customer c', 'c.id = f.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = f.vendor_id', 'left');
        $this->db->where('f.deleted_at IS NULL', null, false);
        $this->db->where('f.target_arrival_date <', date('Y-m-d'));
        $this->db->where_not_in('f.status_shipment', ['Completed', 'Cancelled']);
        $this->db->where('f.actual_tiba_bongkar_date IS NULL', null, false);
        $this->db->order_by('f.target_arrival_date', 'ASC');
        return $this->db->get()->result();
    }

    // ============================================
    // GET BY ID
    // ============================================
    public function lihat_id($id)
    {
        $this->db->select('f.*, c.nama AS nama_customer, v.nama_vendor');
        $this->db->from($this->table . ' f');
        $this->db->join('customer c', 'c.id = f.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = f.vendor_id', 'left');
        $this->db->where('f.id', $id);
        $this->db->where('f.deleted_at IS NULL', null, false);
        return $this->db->get()->row();
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
        return $this->db->update($this->table, ['deleted_at' => date('Y-m-d H:i:s')]);
    }

    public function restore($id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, ['deleted_at' => null]);
    }

    public function hapus_permanen($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    public function lihat_terhapus()
    {
        $this->db->select('f.*, c.nama AS nama_customer, v.nama_vendor');
        $this->db->from($this->table . ' f');
        $this->db->join('customer c', 'c.id = f.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = f.vendor_id', 'left');
        $this->db->where('f.deleted_at IS NOT NULL', null, false);
        $this->db->order_by('f.deleted_at', 'DESC');
        return $this->db->get()->result();
    }

    // ============================================
    // SEARCH / CARI (AJAX) — origin2 ikut dicari
    // ============================================
    public function cari($keyword)
    {
        $this->db->select('f.*, c.nama AS nama_customer, v.nama_vendor');
        $this->db->from($this->table . ' f');
        $this->db->join('customer c', 'c.id = f.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = f.vendor_id', 'left');
        $this->db->where('f.deleted_at IS NULL', null, false);

        $kw = $this->db->escape_str(trim($keyword));

        if (strlen(trim($keyword)) >= 3) {
            $this->db->group_start();
            $this->db->where(
                "MATCH(f.no_shipment, f.origin, f.origin2, f.dest1, f.dest2,
                   f.truck_type, f.nopol, f.driver, f.no_hp)
             AGAINST ('{$kw}' IN BOOLEAN MODE)",
                null,
                false
            );
            $this->db->or_like('c.nama', $keyword);
            $this->db->or_like('v.nama_vendor', $keyword);
            // status tetap pakai LIKE karena ENUM
            $this->db->or_like('f.status_shipment', $keyword);
            $this->db->group_end();
        } else {
            $this->db->group_start();
            $this->db->like('f.no_shipment', $keyword);
            $this->db->or_like('c.nama', $keyword);
            $this->db->or_like('v.nama_vendor', $keyword);
            $this->db->or_like('f.origin', $keyword);
            $this->db->or_like('f.origin2', $keyword);
            $this->db->or_like('f.dest1', $keyword);
            $this->db->or_like('f.dest2', $keyword);
            $this->db->or_like('f.truck_type', $keyword);
            $this->db->or_like('f.nopol', $keyword);
            $this->db->or_like('f.driver', $keyword);
            $this->db->or_like('f.no_hp', $keyword);
            $this->db->or_like('f.status_shipment', $keyword);
            $this->db->group_end();
        }

        $this->db->order_by('f.target_standby_date', 'ASC');
        $this->db->order_by('f.target_standby_time', 'ASC');
        $this->db->order_by('f.created_at', 'DESC');
        $this->db->limit(100);
        return $this->db->get()->result();
    }

    // ============================================
    // AUTO GENERATE NO SHIPMENT
    // ============================================
    public function generate_no_shipment()
    {
        $total = $this->db->count_all($this->table);
        if ($total === 0)
            return 'F001';

        $query = $this->db->query(
            "SELECT MAX(CAST(SUBSTRING(no_shipment, 2) AS UNSIGNED)) AS max_num FROM {$this->table}"
        );
        $row = $query->row();
        $next = ($row && $row->max_num) ? (int) $row->max_num + 1 : 1;

        return 'F' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function update_status($id, $status)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, [
            'status_shipment' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function get_customers()
    {
        return $this->db->order_by('nama', 'ASC')->get('customer')->result();
    }

    public function get_vendors()
    {
        return $this->db->where('deleted_at IS NULL', null, false)
            ->order_by('nama_vendor', 'ASC')
            ->get('vendor_operasional')->result();
    }

    public function get_overdue_standby()
    {
        $this->db->select('f.*, c.nama AS nama_customer, v.nama_vendor');
        $this->db->from($this->table . ' f');
        $this->db->join('customer c', 'c.id = f.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = f.vendor_id', 'left');
        $this->db->where('f.deleted_at IS NULL', null, false);
        $this->db->where('f.target_standby_date <', date('Y-m-d'));
        $this->db->where_in('f.status_shipment', ['Sourcing Vendor', 'Scheduled']);
        $this->db->order_by('f.target_standby_date', 'ASC');
        return $this->db->get()->result();
    }

    // ============================================
    // STATISTICS DASHBOARD
    // ============================================
    public function get_statistics($date_from = null, $date_to = null)
    {
        // Status aktif: TIDAK difilter periode
        // Completed & Cancelled: difilter by updated_at/actual_done_at
        $period_cond = '';
        if (!empty($date_from) && !empty($date_to)) {
            $df = $this->db->escape_str($date_from);
            $dt = $this->db->escape_str($date_to);
            $period_cond = "AND (
            (status_shipment NOT IN ('Completed','Cancelled'))
            OR (status_shipment = 'Completed'  AND DATE(actual_done_at) BETWEEN '{$df}' AND '{$dt}')
            OR (status_shipment = 'Cancelled'  AND DATE(updated_at)     BETWEEN '{$df}' AND '{$dt}')
        )";
        }

        $query = $this->db->query("
        SELECT
            COUNT(*) AS total,
            SUM(deleted_at IS NULL AND status_shipment = 'Scheduled')              AS scheduled,
            SUM(deleted_at IS NULL AND status_shipment = 'Sourcing Vendor')        AS sourcing,
            SUM(deleted_at IS NULL AND status_shipment = 'Loading')                AS loading,
            SUM(deleted_at IS NULL AND status_shipment = 'On Trip')                AS on_trip,
            SUM(deleted_at IS NULL AND status_shipment = 'Tiba di Lokasi Muat')    AS tiba_muat,
            SUM(deleted_at IS NULL AND status_shipment = 'Tiba di Lokasi Bongkar') AS tiba_bongkar,
            SUM(deleted_at IS NULL AND status_shipment = 'Completed')              AS completed,
            SUM(deleted_at IS NULL AND status_shipment = 'Cancelled')              AS cancelled
        FROM ftl_non_spx
        WHERE deleted_at IS NULL
        {$period_cond}
    ");

        $row = $query->row_array();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'scheduled' => (int) ($row['scheduled'] ?? 0),
            'sourcing' => (int) ($row['sourcing'] ?? 0),
            'loading' => (int) ($row['loading'] ?? 0),
            'on_trip' => (int) ($row['on_trip'] ?? 0),
            'tiba_muat' => (int) ($row['tiba_muat'] ?? 0),
            'tiba_bongkar' => (int) ($row['tiba_bongkar'] ?? 0),
            'completed' => (int) ($row['completed'] ?? 0),
            'cancelled' => (int) ($row['cancelled'] ?? 0),
        ];
    }

    public function get_by_status($status)
    {
        $this->db->select('f.*, c.nama AS nama_customer, v.nama_vendor');
        $this->db->from($this->table . ' f');
        $this->db->join('customer c', 'c.id = f.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = f.vendor_id', 'left');
        $this->db->where('f.deleted_at IS NULL', null, false);
        $this->db->where('f.status_shipment', $status);
        $this->db->order_by('f.target_standby_date', 'ASC');
        $this->db->order_by('f.target_standby_time', 'ASC');
        return $this->db->get()->result();
    }

    // ============================================
    // APPLY FILTER — origin2 ikut di-filter keyword
    // ============================================
    private function _apply_filter($params)
    {
        $this->db->where('f.deleted_at IS NULL', null, false);

        if (!empty($params['keyword'])) {
            $kw = $this->db->escape_str(trim($params['keyword']));

            // Coba FULLTEXT dulu (cepat), fallback ke LIKE kalau keyword < 3 karakter
            if (strlen(trim($params['keyword'])) >= 3) {
                $this->db->group_start();
                $this->db->where(
                    "MATCH(f.no_shipment, f.origin, f.origin2, f.dest1, f.dest2,
                       f.truck_type, f.nopol, f.driver, f.no_hp, f.status_shipment)
                 AGAINST ('{$kw}' IN BOOLEAN MODE)",
                    null,
                    false
                );
                $this->db->or_like('c.nama', $params['keyword']);
                $this->db->or_like('v.nama_vendor', $params['keyword']);
                $this->db->group_end();
            } else {
                $this->db->group_start();
                $this->db->like('f.no_shipment', $params['keyword']);
                $this->db->or_like('c.nama', $params['keyword']);
                $this->db->or_like('v.nama_vendor', $params['keyword']);
                $this->db->or_like('f.origin', $params['keyword']);
                $this->db->or_like('f.origin2', $params['keyword']);
                $this->db->or_like('f.dest1', $params['keyword']);
                $this->db->or_like('f.nopol', $params['keyword']);
                $this->db->or_like('f.driver', $params['keyword']);
                $this->db->or_like('f.status_shipment', $params['keyword']);
                $this->db->group_end();
            }
        }

        if (!empty($params['status']))
            $this->db->where('f.status_shipment', $params['status']);
        if (!empty($params['customer_id']))
            $this->db->where('f.customer_id', $params['customer_id']);
        if (!empty($params['vendor_id']))
            $this->db->where('f.vendor_id', $params['vendor_id']);
        if (!empty($params['truck_type']))
            $this->db->where('f.truck_type', $params['truck_type']);
        if (!empty($params['date_from']))
            $this->db->where('f.target_standby_date >=', $params['date_from']);
        if (!empty($params['date_to']))
            $this->db->where('f.target_standby_date <=', $params['date_to']);
    }

    public function count_filter($params)
    {
        $this->db->from($this->table . ' f');
        $this->db->join('customer c', 'c.id = f.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = f.vendor_id', 'left');
        $this->_apply_filter($params);
        return $this->db->count_all_results();
    }

    public function get_filter($params, $limit, $offset)
    {
        $this->db->select('f.*, c.nama AS nama_customer, v.nama_vendor');
        $this->db->from($this->table . ' f');
        $this->db->join('customer c', 'c.id = f.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = f.vendor_id', 'left');
        $this->_apply_filter($params);
        $this->db->order_by('f.target_standby_date', 'ASC');
        $this->db->order_by('f.target_standby_time', 'ASC');
        $this->db->order_by('f.created_at', 'DESC'); // ← kalau standby sama, yang terbaru duluan
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }
}