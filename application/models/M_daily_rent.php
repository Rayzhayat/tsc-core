<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_daily_rent extends CI_Model
{
    private $table = 'daily_rent';
    private $table_units = 'daily_rent_units';
    private $table_locs = 'daily_rent_unit_locations';
    private $table_ext = 'daily_rent_extensions';
    private $table_dlog = 'daily_rent_driver_logs';

    // ============================================================
    // ░░░░░░░░░░░░░░  DAILY RENT — HEADER  ░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    // ------------------------------------------------------------
    // GET ALL (exclude soft deleted)
    // ------------------------------------------------------------
    public function lihat()
    {
        return $this->db
            ->select('dr.*, c.nama AS nama_customer, v.nama_vendor')
            ->from($this->table . ' dr')
            ->join('customer c', 'c.id = dr.customer_id', 'left')
            ->join('vendor_operasional v', 'v.id = dr.vendor_id', 'left')
            ->where('dr.deleted_at IS NULL', null, false)
            ->order_by('dr.created_at', 'DESC')
            ->get()->result();
    }

    // ------------------------------------------------------------
    // GET BY ID (header only)
    // ------------------------------------------------------------
    public function lihat_id($id)
    {
        return $this->db
            ->select('dr.*, c.nama AS nama_customer, v.nama_vendor')
            ->from($this->table . ' dr')
            ->join('customer c', 'c.id = dr.customer_id', 'left')
            ->join('vendor_operasional v', 'v.id = dr.vendor_id', 'left')
            ->where('dr.id', $id)
            ->where('dr.deleted_at IS NULL', null, false)
            ->get()->row();
    }

    // ------------------------------------------------------------
    // GET DETAIL LENGKAP (header + units dalam satu shot)
    // Dipakai di halaman detail
    // ------------------------------------------------------------
    public function lihat_detail($id)
    {
        $rent = $this->lihat_id($id);
        if (!$rent)
            return null;

        $rent->units = $this->get_units($id);
        $rent->extensions = $this->get_extensions_by_rent($id);

        return $rent;
    }

    // ------------------------------------------------------------
    // TAMBAH
    // ------------------------------------------------------------
    public function tambah($data)
    {
        return $this->db->insert($this->table, $data);
    }

    // ------------------------------------------------------------
    // UBAH
    // ------------------------------------------------------------
    public function ubah($data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    // ------------------------------------------------------------
    // SOFT DELETE
    // ------------------------------------------------------------
    public function hapus($id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, [
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ------------------------------------------------------------
    // RESTORE
    // ------------------------------------------------------------
    public function restore($id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, ['deleted_at' => null]);
    }

    // ------------------------------------------------------------
    // HARD DELETE
    // ------------------------------------------------------------
    public function hapus_permanen($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    // ------------------------------------------------------------
    // GET TERHAPUS (soft deleted only)
    // ------------------------------------------------------------
    public function lihat_terhapus()
    {
        return $this->db
            ->select('dr.*, c.nama AS nama_customer, v.nama_vendor')
            ->from($this->table . ' dr')
            ->join('customer c', 'c.id = dr.customer_id', 'left')
            ->join('vendor_operasional v', 'v.id = dr.vendor_id', 'left')
            ->where('dr.deleted_at IS NOT NULL', null, false)
            ->order_by('dr.deleted_at', 'DESC')
            ->get()->result();
    }

    // ------------------------------------------------------------
    // UPDATE STATUS HEADER
    // ------------------------------------------------------------
    public function update_status($id, $status)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, [
            'status_rent' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ------------------------------------------------------------
    // AUTO GENERATE NO RENT (DR001, DR002, ...)
    // ------------------------------------------------------------
    public function generate_no_rent()
    {
        $row = $this->db->query(
            "SELECT MAX(CAST(SUBSTRING(no_rent, 3) AS UNSIGNED)) AS max_num
         FROM {$this->table}
         WHERE no_rent REGEXP '^DR[0-9]+$'"
        )->row();

        $next = ($row && $row->max_num) ? (int) $row->max_num + 1 : 1;
        return 'DR' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    // ------------------------------------------------------------
    // STATISTICS DASHBOARD
    // ------------------------------------------------------------
    public function get_statistics()
    {
        $stats['total'] = $this->db
            ->where('deleted_at IS NULL', null, false)
            ->count_all_results($this->table);

        $statuses = [
            'sourcing' => 'Sourcing Vendor',
            'scheduled' => 'Scheduled',
            'active' => 'Active',
            'partially_returned' => 'Partially Returned',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        foreach ($statuses as $key => $status) {
            $this->db->where('deleted_at IS NULL', null, false);
            $this->db->where('status_rent', $status);
            $stats[$key] = $this->db->count_all_results($this->table);
        }

        // Unit stats (lintas order)
        $unit_row = $this->db->query("
            SELECT
                COUNT(*)                                    AS total_units,
                SUM(status_unit = 'Active')                 AS units_active,
                SUM(status_unit = 'Extended')               AS units_extended,
                SUM(status_unit = 'Pending Assign')         AS units_pending
            FROM {$this->table_units}
            WHERE deleted_at IS NULL
        ")->row();

        $stats['total_units'] = (int) ($unit_row->total_units ?? 0);
        $stats['units_active'] = (int) ($unit_row->units_active ?? 0);
        $stats['units_extended'] = (int) ($unit_row->units_extended ?? 0);
        $stats['units_pending'] = (int) ($unit_row->units_pending ?? 0);

        return $stats;
    }

    // ------------------------------------------------------------
    // GET OVERDUE ORDERS
    // Order yang rent_end_date sudah lewat tapi belum Completed/Cancelled
    // ------------------------------------------------------------
    public function get_overdue()
    {
        return $this->db
            ->select('dr.*, c.nama AS nama_customer, v.nama_vendor')
            ->from($this->table . ' dr')
            ->join('customer c', 'c.id = dr.customer_id', 'left')
            ->join('vendor_operasional v', 'v.id = dr.vendor_id', 'left')
            ->where('dr.deleted_at IS NULL', null, false)
            ->where('dr.rent_end_date <', date('Y-m-d'))
            ->where_not_in('dr.status_rent', ['Completed', 'Cancelled'])
            ->order_by('dr.rent_end_date', 'ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------
    // APPLY FILTER (shared — dipakai count_filter & get_filter)
    // ------------------------------------------------------------
    private function _apply_filter($params)
    {
        $this->db->where('dr.deleted_at IS NULL', null, false);

        if (!empty($params['keyword'])) {
            $kw = $params['keyword'];
            $this->db->group_start();
            $this->db->like('dr.no_rent', $kw);
            $this->db->or_like('c.nama', $kw);
            $this->db->or_like('v.nama_vendor', $kw);
            $this->db->or_like('dr.pic_customer', $kw);
            $this->db->or_like('dr.location', $kw);
            $this->db->or_like('dr.status_rent', $kw);
            $this->db->group_end();
        }

        if (!empty($params['status'])) {
            $this->db->where('dr.status_rent', $params['status']);
        }
        if (!empty($params['customer_id'])) {
            $this->db->where('dr.customer_id', $params['customer_id']);
        }
        if (!empty($params['vendor_id'])) {
            $this->db->where('dr.vendor_id', $params['vendor_id']);
        }
        if (!empty($params['date_from'])) {
            $this->db->where('dr.rent_start_date >=', $params['date_from']);
        }
        if (!empty($params['date_to'])) {
            $this->db->where('dr.rent_start_date <=', $params['date_to']);
        }
    }

    // ------------------------------------------------------------
    // COUNT FILTER (untuk total pagination)
    // ------------------------------------------------------------
    public function count_filter($params)
    {
        $this->db->from($this->table . ' dr');
        $this->db->join('customer c', 'c.id = dr.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = dr.vendor_id', 'left');
        $this->_apply_filter($params);
        return $this->db->count_all_results();
    }

    // ------------------------------------------------------------
    // GET FILTER + PAGINATION
    // ------------------------------------------------------------
    public function get_filter($params, $limit, $offset)
    {
        $this->db->select('dr.*, c.nama AS nama_customer, v.nama_vendor');
        $this->db->from($this->table . ' dr');
        $this->db->join('customer c', 'c.id = dr.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = dr.vendor_id', 'left');
        $this->_apply_filter($params);
        $this->db->order_by('dr.rent_start_date', 'ASC');
        $this->db->order_by('dr.rent_start_time', 'ASC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    // ------------------------------------------------------------
    // SEARCH (legacy AJAX — keyword only)
    // ------------------------------------------------------------
    public function cari($keyword)
    {
        return $this->db
            ->select('dr.*, c.nama AS nama_customer, v.nama_vendor')
            ->from($this->table . ' dr')
            ->join('customer c', 'c.id = dr.customer_id', 'left')
            ->join('vendor_operasional v', 'v.id = dr.vendor_id', 'left')
            ->where('dr.deleted_at IS NULL', null, false)
            ->group_start()
            ->like('dr.no_rent', $keyword)
            ->or_like('c.nama', $keyword)
            ->or_like('v.nama_vendor', $keyword)
            ->or_like('dr.pic_customer', $keyword)
            ->or_like('dr.location', $keyword)
            ->or_like('dr.status_rent', $keyword)
            ->group_end()
            ->order_by('dr.created_at', 'DESC')
            ->get()->result();
    }

    // ------------------------------------------------------------
    // GET CUSTOMERS (dropdown)
    // ------------------------------------------------------------
    public function get_customers()
    {
        return $this->db->order_by('nama', 'ASC')->get('customer')->result();
    }

    // ------------------------------------------------------------
    // GET VENDORS (dropdown)
    // ------------------------------------------------------------
    public function get_vendors()
    {
        return $this->db
            ->where('deleted_at IS NULL', null, false)
            ->order_by('nama_vendor', 'ASC')
            ->get('vendor_operasional')->result();
    }


    // ============================================================
    // ░░░░░░░░░░░░░░  UNITS  ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    // ------------------------------------------------------------
    // GET UNITS BY RENT ID
    // ------------------------------------------------------------
    public function get_units($rent_id)
    {
        return $this->db
            ->select('dru.*, v.nama_vendor')
            ->from($this->table_units . ' dru')
            ->join('vendor_operasional v', 'v.id = dru.vendor_id', 'left')
            ->where('dru.rent_id', $rent_id)
            ->where('dru.deleted_at IS NULL', null, false)
            ->order_by('dru.id', 'ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------
    // GET UNIT BY ID
    // ------------------------------------------------------------
    public function get_unit_by_id($unit_id)
    {
        return $this->db
            ->select('dru.*, v.nama_vendor, dr.no_rent, dr.customer_id, c.nama AS nama_customer')
            ->from($this->table_units . ' dru')
            ->join('vendor_operasional v', 'v.id = dru.vendor_id', 'left')
            ->join('daily_rent dr', 'dr.id = dru.rent_id', 'left')
            ->join('customer c', 'c.id = dr.customer_id', 'left')
            ->where('dru.id', $unit_id)
            ->where('dru.deleted_at IS NULL', null, false)
            ->get()->row();
    }

    // ------------------------------------------------------------
    // TAMBAH UNIT
    // ------------------------------------------------------------
    public function tambah_unit($data)
    {
        return $this->db->insert($this->table_units, $data);
    }

    // ------------------------------------------------------------
    // UBAH UNIT
    // ------------------------------------------------------------
    public function ubah_unit($data, $unit_id)
    {
        $this->db->where('id', $unit_id);
        return $this->db->update($this->table_units, $data);
    }

    // ------------------------------------------------------------
    // HAPUS UNIT (soft delete)
    // ------------------------------------------------------------
    public function hapus_unit($unit_id)
    {
        $this->db->where('id', $unit_id);
        return $this->db->update($this->table_units, [
            'deleted_at' => date('Y-m-d H:i:s'),
            'status_unit' => 'Cancelled',
        ]);
    }

    // ------------------------------------------------------------
    // UPDATE STATUS UNIT
    // ------------------------------------------------------------
    public function update_status_unit($unit_id, $status, $extra_data = [])
    {
        $data = array_merge($extra_data, [
            'status_unit' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->where('id', $unit_id);
        return $this->db->update($this->table_units, $data);
    }

    // ------------------------------------------------------------
    // CEK DUPLIKASI NOPOL / DRIVER
    // Cek apakah nopol/driver masih aktif di rent lain
    // Return array ['nopol' => ..., 'driver' => ...]
    // ------------------------------------------------------------
    public function cek_duplikasi_unit($nopol, $driver, $exclude_unit_id = 0)
    {
        $exclude_status = ['Returned', 'Cancelled'];
        $result = ['nopol' => null, 'driver' => null];

        if (!empty($nopol)) {
            $row = $this->db
                ->select('dr.no_rent, dru.status_unit, dr.customer_id, c.nama AS nama_customer')
                ->from($this->table_units . ' dru')
                ->join('daily_rent dr', 'dr.id = dru.rent_id', 'left')
                ->join('customer c', 'c.id = dr.customer_id', 'left')
                ->where('dru.deleted_at IS NULL', null, false)
                ->where('dr.deleted_at IS NULL', null, false)
                ->where('dru.nopol', strtoupper(trim($nopol)))
                ->where('dru.id !=', (int) $exclude_unit_id)
                ->where_not_in('dru.status_unit', $exclude_status)
                ->get()->row();

            if ($row) {
                $result['nopol'] = "Nopol <strong>" . htmlspecialchars(strtoupper(trim($nopol))) . "</strong>"
                    . " sedang aktif di order <strong>{$row->no_rent}</strong>"
                    . " (Customer: {$row->nama_customer}, Status: {$row->status_unit})";
            }
        }

        if (!empty($driver)) {
            $row = $this->db
                ->select('dr.no_rent, dru.status_unit, dr.customer_id, c.nama AS nama_customer')
                ->from($this->table_units . ' dru')
                ->join('daily_rent dr', 'dr.id = dru.rent_id', 'left')
                ->join('customer c', 'c.id = dr.customer_id', 'left')
                ->where('dru.deleted_at IS NULL', null, false)
                ->where('dr.deleted_at IS NULL', null, false)
                ->where('dru.driver', trim($driver))
                ->where('dru.id !=', (int) $exclude_unit_id)
                ->where_not_in('dru.status_unit', $exclude_status)
                ->get()->row();

            if ($row) {
                $result['driver'] = "Driver <strong>" . htmlspecialchars(trim($driver)) . "</strong>"
                    . " sedang aktif di order <strong>{$row->no_rent}</strong>"
                    . " (Customer: {$row->nama_customer}, Status: {$row->status_unit})";
            }
        }

        return $result;
    }

    // ------------------------------------------------------------
    // RECALCULATE STATUS HEADER berdasarkan status units
    // Dipanggil setiap kali ada perubahan status unit
    // ------------------------------------------------------------
    public function recalculate_rent_status($rent_id)
    {
        $units = $this->db
            ->select('status_unit')
            ->from($this->table_units)
            ->where('rent_id', $rent_id)
            ->where('deleted_at IS NULL', null, false)
            ->get()->result();

        if (empty($units))
            return; // Tidak ada unit, skip

        $statuses = array_column($units, 'status_unit');
        $total = count($statuses);

        $returned = count(array_filter($statuses, fn($s) => $s === 'Returned'));
        $cancelled = count(array_filter($statuses, fn($s) => $s === 'Cancelled'));
        $active = count(array_filter($statuses, fn($s) => in_array($s, ['Active', 'Extended'])));
        $assigned = count(array_filter($statuses, fn($s) => $s === 'Assigned'));
        $pending = count(array_filter($statuses, fn($s) => $s === 'Pending Assign'));

        $non_cancelled = $total - $cancelled;

        if ($non_cancelled === 0) {
            // Semua di-cancel
            $new_status = 'Cancelled';
        } elseif ($returned === $non_cancelled) {
            // Semua unit sudah returned
            $new_status = 'Completed';
        } elseif ($returned > 0 && $returned < $non_cancelled) {
            // Sebagian returned
            $new_status = 'Partially Returned';
        } elseif ($active > 0) {
            $new_status = 'Active';
        } elseif ($assigned > 0) {
            $new_status = 'Scheduled';
        } else {
            // Semua masih Pending Assign
            $new_status = 'Sourcing Vendor';
        }

        $this->db->where('id', $rent_id);
        $this->db->update($this->table, [
            'status_rent' => $new_status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $new_status;
    }

    // ------------------------------------------------------------
    // COUNT UNITS BY RENT ID
    // ------------------------------------------------------------
    public function count_units($rent_id)
    {
        return $this->db
            ->where('rent_id', $rent_id)
            ->where('deleted_at IS NULL', null, false)
            ->count_all_results($this->table_units);
    }


    // ============================================================
    // ░░░░░░░░░░░░░░  LOCATIONS  ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    // ------------------------------------------------------------
    // GET LOCATION HISTORY BY UNIT
    // ------------------------------------------------------------
    public function get_location_history($unit_id)
    {
        return $this->db
            ->where('unit_id', $unit_id)
            ->order_by('moved_at', 'DESC')
            ->get($this->table_locs)->result();
    }

    // ------------------------------------------------------------
    // GET LOCATION HISTORY BY RENT (semua unit)
    // ------------------------------------------------------------
    public function get_location_history_by_rent($rent_id)
    {
        return $this->db
            ->select('drl.*, dru.nopol, dru.driver, dru.truck_type')
            ->from($this->table_locs . ' drl')
            ->join($this->table_units . ' dru', 'dru.id = drl.unit_id', 'left')
            ->where('drl.rent_id', $rent_id)
            ->order_by('drl.moved_at', 'DESC')
            ->get()->result();
    }

    // ------------------------------------------------------------
    // TAMBAH LOCATION LOG & UPDATE current_location di unit
    // ------------------------------------------------------------
    public function catat_lokasi($unit_id, $rent_id, $location, $moved_by, $notes = null)
    {
        // Insert log
        $this->db->insert($this->table_locs, [
            'unit_id' => $unit_id,
            'rent_id' => $rent_id,
            'location' => $location,
            'moved_at' => date('Y-m-d H:i:s'),
            'moved_by' => $moved_by,
            'notes' => $notes,
        ]);

        // Update current_location di unit
        $this->db->where('id', $unit_id);
        $this->db->update($this->table_units, [
            'current_location' => $location,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insert_id();
    }


    // ============================================================
    // ░░░░░░░░░░░░░░  EXTENSIONS  ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    // ------------------------------------------------------------
    // GET EXTENSIONS BY RENT
    // ------------------------------------------------------------
    public function get_extensions_by_rent($rent_id)
    {
        return $this->db
            ->select('dre.*, dru.nopol, dru.truck_type, dru.driver')
            ->from($this->table_ext . ' dre')
            ->join($this->table_units . ' dru', 'dru.id = dre.unit_id', 'left')
            ->where('dre.rent_id', $rent_id)
            ->order_by('dre.extended_at', 'DESC')
            ->get()->result();
    }

    // ------------------------------------------------------------
    // GET EXTENSIONS BY UNIT
    // ------------------------------------------------------------
    public function get_extensions_by_unit($unit_id)
    {
        return $this->db
            ->where('unit_id', $unit_id)
            ->order_by('extended_at', 'DESC')
            ->get($this->table_ext)->result();
    }

    // ------------------------------------------------------------
    // EXTEND ORDER (semua unit sekaligus — unit_id NULL)
    // Update rent_end_date di header + semua unit yang masih aktif
    // ------------------------------------------------------------
    public function extend_order($rent_id, $new_end_date, $new_end_time = null, $reason = null, $extended_by = null)
    {
        // Ambil old_end dari header
        $rent = $this->lihat_id($rent_id);
        if (!$rent)
            return false;

        $old_end_date = $rent->rent_end_date;
        $old_end_time = $rent->rent_end_time;

        // Hitung extension_days
        $diff = (strtotime($new_end_date) - strtotime($old_end_date)) / 86400;

        // Insert extension log (level order)
        $this->db->insert($this->table_ext, [
            'rent_id' => $rent_id,
            'unit_id' => null,
            'old_end_date' => $old_end_date,
            'old_end_time' => $old_end_time,
            'new_end_date' => $new_end_date,
            'new_end_time' => $new_end_time,
            'extension_days' => $diff,
            'reason' => $reason,
            'extended_by' => $extended_by,
            'extended_at' => date('Y-m-d H:i:s'),
        ]);

        // Update header
        $this->db->where('id', $rent_id);
        $this->db->update($this->table, [
            'rent_end_date' => $new_end_date,
            'rent_end_time' => $new_end_time,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Update semua unit yang belum returned/cancelled
        $this->db
            ->where('rent_id', $rent_id)
            ->where('deleted_at IS NULL', null, false)
            ->where_not_in('status_unit', ['Returned', 'Cancelled'])
            ->update($this->table_units, [
                'rent_end_date' => $new_end_date,
                'rent_end_time' => $new_end_time,
                'status_unit' => 'Extended',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return true;
    }

    // ------------------------------------------------------------
    // EXTEND UNIT (per unit spesifik)
    // ------------------------------------------------------------
    public function extend_unit($unit_id, $new_end_date, $new_end_time = null, $reason = null, $extended_by = null)
    {
        $unit = $this->get_unit_by_id($unit_id);
        if (!$unit)
            return false;

        $old_end_date = $unit->rent_end_date;
        $old_end_time = $unit->rent_end_time;
        $diff = (strtotime($new_end_date) - strtotime($old_end_date)) / 86400;

        // Insert extension log
        $this->db->insert($this->table_ext, [
            'rent_id' => $unit->rent_id,
            'unit_id' => $unit_id,
            'old_end_date' => $old_end_date,
            'old_end_time' => $old_end_time,
            'new_end_date' => $new_end_date,
            'new_end_time' => $new_end_time,
            'extension_days' => $diff,
            'reason' => $reason,
            'extended_by' => $extended_by,
            'extended_at' => date('Y-m-d H:i:s'),
        ]);

        // Update unit
        $this->db->where('id', $unit_id);
        $this->db->update($this->table_units, [
            'rent_end_date' => $new_end_date,
            'rent_end_time' => $new_end_time,
            'status_unit' => 'Extended',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Recalculate header status
        $this->recalculate_rent_status($unit->rent_id);

        return true;
    }


    // ============================================================
    // ░░░░░░░░░░░░░░  DRIVER LOGS  ░░░░░░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    // ------------------------------------------------------------
    // GET DRIVER LOGS BY UNIT
    // ------------------------------------------------------------
    public function get_driver_logs($unit_id)
    {
        return $this->db
            ->where('unit_id', $unit_id)
            ->order_by('changed_at', 'DESC')
            ->get($this->table_dlog)->result();
    }

    // ------------------------------------------------------------
    // GET DRIVER LOGS BY RENT (semua unit)
    // ------------------------------------------------------------
    public function get_driver_logs_by_rent($rent_id)
    {
        return $this->db
            ->select('drdl.*, dru.nopol, dru.truck_type')
            ->from($this->table_dlog . ' drdl')
            ->join($this->table_units . ' dru', 'dru.id = drdl.unit_id', 'left')
            ->where('drdl.rent_id', $rent_id)
            ->order_by('drdl.changed_at', 'DESC')
            ->get()->result();
    }

    // ------------------------------------------------------------
    // GANTI DRIVER
    // Otomatis catat log + update driver di unit
    // ------------------------------------------------------------
    public function ganti_driver($unit_id, $new_driver, $new_no_hp = null, $reason = null, $changed_by = null)
    {
        $unit = $this->get_unit_by_id($unit_id);
        if (!$unit)
            return false;

        // Insert log
        $this->db->insert($this->table_dlog, [
            'unit_id' => $unit_id,
            'rent_id' => $unit->rent_id,
            'old_driver' => $unit->driver,
            'old_no_hp' => $unit->no_hp,
            'new_driver' => $new_driver,
            'new_no_hp' => $new_no_hp,
            'changed_at' => date('Y-m-d H:i:s'),
            'changed_by' => $changed_by,
            'reason' => $reason,
        ]);

        // Update unit
        $this->db->where('id', $unit_id);
        return $this->db->update($this->table_units, [
            'driver' => $new_driver,
            'no_hp' => $new_no_hp,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }


    // ============================================================
    // ░░░░░░░░░░░░░░  RETURN / DONE  ░░░░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    // ------------------------------------------------------------
    // RETURN UNIT
    // Catat actual_return + hitung overrun + recalculate header
    // ------------------------------------------------------------
    public function return_unit($unit_id, $return_date, $return_time = null, $overrun_notes = null)
    {
        $unit = $this->get_unit_by_id($unit_id);
        if (!$unit)
            return false;

        // Hitung overrun days
        $overrun = 0;
        if (!empty($unit->rent_end_date) && !empty($return_date)) {
            $diff = (strtotime($return_date) - strtotime($unit->rent_end_date)) / 86400;
            $overrun = max(0, round($diff, 1));
        }

        $this->db->where('id', $unit_id);
        $this->db->update($this->table_units, [
            'actual_return_date' => $return_date,
            'actual_return_time' => $return_time ?: null,
            'status_unit' => 'Returned',
            'overrun_days' => $overrun,
            'overrun_notes' => $overrun_notes ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Recalculate header status
        $new_header_status = $this->recalculate_rent_status($unit->rent_id);

        return [
            'overrun_days' => $overrun,
            'new_header_status' => $new_header_status,
        ];
    }

    // ------------------------------------------------------------
    // ACTIVATE UNIT (Assigned → Active, catat actual_start)
    // ------------------------------------------------------------
    public function activate_unit($unit_id, $start_date, $start_time = null)
    {
        $unit = $this->get_unit_by_id($unit_id);
        if (!$unit)
            return false;

        $this->db->where('id', $unit_id);
        $this->db->update($this->table_units, [
            'actual_start_date' => $start_date,
            'actual_start_time' => $start_time ?: null,
            'status_unit' => 'Active',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Jika header masih Scheduled, naik ke Active
        $this->recalculate_rent_status($unit->rent_id);

        return true;
    }

    // ------------------------------------------------------------
    // CANCEL UNIT
    // ------------------------------------------------------------
    public function cancel_unit($unit_id, $reason = null, $cancelled_by = null)
    {
        $unit = $this->get_unit_by_id($unit_id);
        if (!$unit)
            return false;

        $this->db->where('id', $unit_id);
        $this->db->update($this->table_units, [
            'status_unit' => 'Cancelled',
            'notes' => $reason ? ('CANCELLED: ' . $reason) : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->recalculate_rent_status($unit->rent_id);
        return true;
    }

    // ------------------------------------------------------------
    // CANCEL ORDER (semua unit + header)
    // ------------------------------------------------------------
    public function cancel_order($rent_id, $reason, $cancelled_by = null)
    {
        $fields = $this->db->list_fields($this->table);

        $data = [
            'status_rent' => 'Cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (in_array('cancel_reason', $fields))
            $data['cancel_reason'] = $reason;
        if (in_array('cancelled_at', $fields))
            $data['cancelled_at'] = date('Y-m-d H:i:s');
        if (in_array('cancelled_by', $fields))
            $data['cancelled_by'] = $cancelled_by;

        $this->db->where('id', $rent_id);
        $this->db->update($this->table, $data);

        // Cancel semua unit yang belum returned
        $this->db
            ->where('rent_id', $rent_id)
            ->where('deleted_at IS NULL', null, false)
            ->where_not_in('status_unit', ['Returned'])
            ->update($this->table_units, [
                'status_unit' => 'Cancelled',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return true;
    }

    // ============================================================
    // ░░░░░░░░░░░░░░  BULK OPERATIONS  ░░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    // ------------------------------------------------------------
    // BULK RESTORE (superadmin)
    // ------------------------------------------------------------
    public function bulk_restore($ids)
    {
        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);
        if (empty($ids))
            return false;
        $this->db->where_in('id', $ids);
        return $this->db->update($this->table, ['deleted_at' => null]);
    }

    // ------------------------------------------------------------
    // BULK HAPUS PERMANEN (superadmin)
    // ------------------------------------------------------------
    public function bulk_hapus_permanen($ids)
    {
        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);
        if (empty($ids))
            return false;
        $this->db->where_in('id', $ids);
        return $this->db->delete($this->table);
    }
}