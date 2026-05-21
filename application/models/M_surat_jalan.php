<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model: M_surat_jalan
 * Purpose: Manage Surat Jalan (Delivery Order) Operations
 * Tables: tb_surat_jalan, tb_surat_jalan_biaya, tb_surat_jalan_tracking
 */
class M_surat_jalan extends CI_Model
{

    private $table = 'tb_surat_jalan';
    private $table_biaya = 'tb_surat_jalan_biaya';
    private $table_tracking = 'tb_surat_jalan_tracking';

    public function __construct()
    {
        parent::__construct();
    }

    // ==================== AUTO GENERATE NO SURAT JALAN ====================

    /**
     * Generate nomor surat jalan otomatis
     * Format: SJ/YYYYMM/0001
     */
    public function generate_no_surat_jalan()
    {
        $prefix = 'SJ';
        $ym = date('Ym'); // YYYYMM (contoh: 202512)

        // Get last number for current month
        $this->db->select('no_surat_jalan');
        $this->db->from($this->table);
        $this->db->like('no_surat_jalan', $prefix . '/' . $ym . '/', 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);

        $last = $this->db->get()->row();

        if ($last) {
            // Extract number from last: SJ/202512/0005 → 5
            $parts = explode('/', $last->no_surat_jalan);
            $last_number = isset($parts[2]) ? intval($parts[2]) : 0;
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }

        // Format: SJ/202512/0001
        return $prefix . '/' . $ym . '/' . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    }

    // ==================== CREATE ====================

    /**
     * Create new surat jalan
     */
    public function create($data)
    {
        // Auto generate no_surat_jalan if not provided
        if (empty($data['no_surat_jalan'])) {
            $data['no_surat_jalan'] = $this->generate_no_surat_jalan();
        }

        // Auto calculate total_biaya
        $data['total_biaya'] =
            ($data['biaya_sewa'] ?? 0) +
            ($data['biaya_solar'] ?? 0) +
            ($data['biaya_tol'] ?? 0) +
            ($data['biaya_parkir'] ?? 0) +
            ($data['biaya_makan'] ?? 0) +
            ($data['biaya_lainnya'] ?? 0);

        // Calculate SLA target if jam_berangkat provided
        if (!empty($data['jam_berangkat']) && !empty($data['sla'])) {
            $data['target_tiba'] = $this->calculate_target_tiba($data['jam_berangkat'], $data['sla']);
        }

        // Set audit trail
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $this->db->insert($this->table, $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Log tracking
            $this->add_tracking($insert_id, 'draft', 'Surat jalan dibuat', 'Draft created');
        }

        return $insert_id;
    }

    // ==================== READ ====================

    /**
     * Get all surat jalan with filters
     */
    public function get_all($filters = [])
    {
        $this->db->select('
            sj.*,
            d.nama_driver,
            d.nik as driver_nik,
            u.no_polisi,
            u.tipe_unit as unit_tipe,
            r.service as rute_service
        ');
        $this->db->from($this->table . ' sj');
        $this->db->join('drivers d', 'd.id = sj.driver_id', 'left');
        $this->db->join('units u', 'u.id = sj.unit_id', 'left');
        $this->db->join('tb_rute r', 'r.kode_rute = sj.kode_rute', 'left');

        // Apply filters
        if (!empty($filters['tanggal_dari'])) {
            $this->db->where('sj.tanggal >=', $filters['tanggal_dari']);
        }

        if (!empty($filters['tanggal_sampai'])) {
            $this->db->where('sj.tanggal <=', $filters['tanggal_sampai']);
        }

        if (!empty($filters['status'])) {
            $this->db->where('sj.status', $filters['status']);
        }

        if (!empty($filters['customer'])) {
            $this->db->like('sj.customer', $filters['customer']);
        }

        if (!empty($filters['driver_id'])) {
            $this->db->where('sj.driver_id', $filters['driver_id']);
        }

        if (!empty($filters['unit_id'])) {
            $this->db->where('sj.unit_id', $filters['unit_id']);
        }

        if (!empty($filters['sla_status'])) {
            $this->db->where('sj.sla_status', $filters['sla_status']);
        }

        if (!empty($filters['keyword'])) {
            $this->db->group_start();
            $this->db->like('sj.no_surat_jalan', $filters['keyword']);
            $this->db->or_like('sj.customer', $filters['keyword']);
            $this->db->or_like('sj.origin', $filters['keyword']);
            $this->db->or_like('d.nama_driver', $filters['keyword']);
            $this->db->or_like('u.no_polisi', $filters['keyword']);
            $this->db->group_end();
        }

        $this->db->order_by('sj.id', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Get surat jalan by ID
     */
    public function get_by_id($id)
    {
        $this->db->select('
            sj.*,
            d.nama_driver,
            d.nik as driver_nik,
            d.sim as driver_sim,
            u.no_polisi,
            u.tipe_unit as unit_tipe,
            u.tipe_box,
            u.tonase as unit_tonase,
            r.service as rute_service,
            r.harga as rute_harga
        ');
        $this->db->from($this->table . ' sj');
        $this->db->join('drivers d', 'd.id = sj.driver_id', 'left');
        $this->db->join('units u', 'u.id = sj.unit_id', 'left');
        $this->db->join('tb_rute r', 'r.kode_rute = sj.kode_rute', 'left');
        $this->db->where('sj.id', $id);

        return $this->db->get()->row();
    }

    /**
     * Get surat jalan with all related data (for detail view)
     */
    public function get_with_relations($id)
    {
        $sj = $this->get_by_id($id);

        if ($sj) {
            // Get biaya details
            $sj->biaya_details = $this->get_biaya($id);

            // Get tracking history
            $sj->tracking_history = $this->get_tracking($id);
        }

        return $sj;
    }

    // ==================== UPDATE ====================

    /**
     * Update surat jalan
     */
    /**
     * Update surat jalan
     * 🔥 FIXED: Cast ID to integer to ensure WHERE clause works
     */
    public function update($id, $data)
    {
        // 🔥 FIX: Cast ID to integer
        $id = (int) $id;

        // Auto calculate total_biaya if any biaya field changed
        if (
            isset($data['biaya_sewa']) || isset($data['biaya_solar']) ||
            isset($data['biaya_tol']) || isset($data['biaya_parkir']) ||
            isset($data['biaya_makan']) || isset($data['biaya_lainnya'])
        ) {

            // Get current data
            $current = $this->get_by_id($id);

            $data['total_biaya'] =
                ($data['biaya_sewa'] ?? $current->biaya_sewa) +
                ($data['biaya_solar'] ?? $current->biaya_solar) +
                ($data['biaya_tol'] ?? $current->biaya_tol) +
                ($data['biaya_parkir'] ?? $current->biaya_parkir) +
                ($data['biaya_makan'] ?? $current->biaya_makan) +
                ($data['biaya_lainnya'] ?? $current->biaya_lainnya);
        }

        // Recalculate SLA if jam_berangkat changed
        if (!empty($data['jam_berangkat'])) {
            $current = $this->get_by_id($id);
            $sla = $data['sla'] ?? $current->sla;
            $data['target_tiba'] = $this->calculate_target_tiba($data['jam_berangkat'], $sla);
        }

        // Check SLA status if jam_tiba provided
        if (!empty($data['jam_tiba'])) {
            $current = $this->get_by_id($id);
            $target_tiba = $data['target_tiba'] ?? $current->target_tiba;

            if ($target_tiba) {
                $delay_minutes = (strtotime($data['jam_tiba']) - strtotime($target_tiba)) / 60;
                $data['keterlambatan'] = max(0, $delay_minutes);

                if ($delay_minutes <= 0) {
                    $data['sla_status'] = 'on_time';
                } elseif ($delay_minutes <= 60) {
                    $data['sla_status'] = 'late';
                } else {
                    $data['sla_status'] = 'very_late';
                }
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        // 🔥 FIX: Explicit WHERE with integer ID
        $this->db->where('id', $id);
        $result = $this->db->update($this->table, $data);

        // 🔥 DEBUG: Log if update failed
        if (!$result) {
            log_message('error', "❌ UPDATE FAILED for ID: {$id}");
            log_message('error', "Data: " . print_r($data, true));
            log_message('error', "DB Error: " . $this->db->error()['message']);
        } else {
            log_message('info', "✅ UPDATE SUCCESS for ID: {$id}, Rows affected: " . $this->db->affected_rows());
        }

        return $result;
    }

    /**
     * Update status
     */
    /**
     * Update status
     * 🔥 FIXED: Ensure status is actually updated in tb_surat_jalan
     */
    public function update_status($id, $status, $keterangan = '')
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Auto-fill timestamps based on status
        switch ($status) {
            case 'on_trip':
                $current = $this->get_by_id($id);
                if (empty($current->jam_berangkat)) {
                    $data['jam_berangkat'] = date('Y-m-d H:i:s');

                    // Calculate target_tiba
                    if ($current->sla) {
                        $sla_hours = $this->get_sla_hours($current->sla);
                        if ($sla_hours) {
                            $jam_berangkat_timestamp = strtotime($data['jam_berangkat']);
                            $target_tiba_timestamp = $jam_berangkat_timestamp + ($sla_hours * 3600);
                            $data['target_tiba'] = date('Y-m-d H:i:s', $target_tiba_timestamp);
                        }
                    }
                }
                break;

            case 'completed':
                $current = $this->get_by_id($id);
                if (empty($current->jam_tiba)) {
                    $data['jam_tiba'] = date('Y-m-d H:i:s');

                    // Calculate SLA status
                    if ($current->target_tiba) {
                        $delay_minutes = (strtotime($data['jam_tiba']) - strtotime($current->target_tiba)) / 60;
                        $data['keterlambatan'] = max(0, $delay_minutes);

                        if ($delay_minutes <= 0) {
                            $data['sla_status'] = 'on_time';
                        } elseif ($delay_minutes <= 60) {
                            $data['sla_status'] = 'late';
                        } else {
                            $data['sla_status'] = 'very_late';
                        }
                    }
                }
                break;
        }

        // 🔥 FIX: Update main table FIRST
        $this->db->where('id', $id);
        $result = $this->db->update($this->table, $data);

        // Then add tracking
        if ($result) {
            $this->add_tracking($id, $status, '', $keterangan);
        }

        return $result;
    }

    /**
     * Helper: Get SLA hours from SLA string
     */
    private function get_sla_hours($sla)
    {
        // Parse SLA like "Express (12 Jam)", "Non Express (24 Jam)", etc
        if (preg_match('/(\d+)\s*jam/i', $sla, $matches)) {
            return (int) $matches[1];
        }

        // Default SLA based on keywords
        $sla_lower = strtolower($sla);
        if (strpos($sla_lower, 'express') !== false) {
            return 12; // Express = 12 hours
        } elseif (strpos($sla_lower, 'regular') !== false) {
            return 24; // Regular = 24 hours
        } elseif (strpos($sla_lower, 'ekonomi') !== false) {
            return 48; // Ekonomi = 48 hours
        }

        return 24; // Default 24 hours
    }

    // ==================== DELETE ====================

    /**
     * Delete surat jalan
     */
    public function delete($id)
    {
        // Check if can delete (only draft/cancelled can be deleted)
        $sj = $this->get_by_id($id);

        if (!$sj) {
            return false;
        }

        if (!in_array($sj->status, ['draft', 'cancelled'])) {
            return false; // Cannot delete on_trip or completed
        }

        // Delete related records (CASCADE will handle this automatically)
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    // ==================== BIAYA OPERATIONS ====================

    /**
     * Add biaya detail
     */
    public function add_biaya($surat_jalan_id, $data)
    {
        $data['surat_jalan_id'] = $surat_jalan_id;
        $data['created_at'] = date('Y-m-d H:i:s');

        $result = $this->db->insert($this->table_biaya, $data);

        if ($result) {
            // Update total_biaya in main table
            $this->recalculate_total_biaya($surat_jalan_id);
        }

        return $result;
    }

    /**
     * Get all biaya for surat jalan
     */
    public function get_biaya($surat_jalan_id)
    {
        $this->db->where('surat_jalan_id', $surat_jalan_id);
        $this->db->order_by('tanggal', 'DESC');
        return $this->db->get($this->table_biaya)->result();
    }

    /**
     * Delete biaya
     */
    public function delete_biaya($id)
    {
        // Get surat_jalan_id before delete
        $biaya = $this->db->where('id', $id)->get($this->table_biaya)->row();

        if (!$biaya) {
            return false;
        }

        $result = $this->db->where('id', $id)->delete($this->table_biaya);

        if ($result) {
            $this->recalculate_total_biaya($biaya->surat_jalan_id);
        }

        return $result;
    }

    /**
     * Recalculate total biaya
     */
    private function recalculate_total_biaya($surat_jalan_id)
    {
        $sj = $this->get_by_id($surat_jalan_id);
        $biaya_details = $this->get_biaya($surat_jalan_id);

        $total_biaya_tambahan = 0;
        foreach ($biaya_details as $biaya) {
            $total_biaya_tambahan += $biaya->nominal;
        }

        $total = $sj->biaya_sewa + $total_biaya_tambahan;

        $this->db->where('id', $surat_jalan_id);
        $this->db->update($this->table, ['total_biaya' => $total]);
    }

    // ==================== TRACKING OPERATIONS ====================
    /**
     * Add tracking entry
     */
    public function add_tracking($surat_jalan_id, $status, $lokasi = '', $keterangan = '', $lat = null, $lng = null, $created_by = 'system')
    {
        $data = [
            'surat_jalan_id' => $surat_jalan_id,
            'status' => $status,
            'lokasi' => $lokasi,
            'keterangan' => $keterangan,
            'lat' => $lat,
            'lng' => $lng,
            'created_by' => $created_by,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert($this->table_tracking, $data);
    }

    /**
     * Get tracking history
     */
    public function get_tracking($surat_jalan_id)
    {
        $this->db->where('surat_jalan_id', $surat_jalan_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table_tracking)->result();
    }

    // ==================== DESTINATION TRACKING ====================

    /**
     * Update destination status
     */
    public function update_destination($id, $dest_number, $status, $time = null, $catatan = '')
    {
        if ($dest_number < 1 || $dest_number > 4) {
            return false;
        }

        $data = [
            'dest' . $dest_number . '_status' => $status,
            'dest' . $dest_number . '_time' => $time ?: date('Y-m-d H:i:s'),
            'dest' . $dest_number . '_catatan' => $catatan,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $id);
        $result = $this->db->update($this->table, $data);

        if ($result) {
            $sj = $this->get_by_id($id);
            $dest_name = $sj->{'dest' . $dest_number};
            $this->add_tracking($id, 'destination_' . $status, $dest_name, "Dest {$dest_number}: {$status} - {$catatan}");
        }

        return $result;
    }

    // ==================== HELPER FUNCTIONS ====================

    /**
     * Calculate target arrival time based on SLA
     */
    private function calculate_target_tiba($jam_berangkat, $sla)
    {
        $sla_hours_map = [
            'Express' => 8,       // 8 jam
            'Non Express' => 24,  // 24 jam
            'Same Day' => 12,     // 12 jam
            'Next Day' => 24,     // 24 jam
            'H+2' => 48,          // 48 jam
            'Regular' => 72       // 72 jam
        ];

        $hours = $sla_hours_map[$sla] ?? 24; // Default 24 hours

        return date('Y-m-d H:i:s', strtotime($jam_berangkat . " +{$hours} hours"));
    }

    /**
     * Get rute data by kode_rute
     */
    public function get_rute_data($kode_rute)
    {
        $this->db->where('kode_rute', $kode_rute);
        return $this->db->get('tb_rute')->row();
    }

    /**
     * Get driver data
     */
    public function get_driver($driver_id)
    {
        $this->db->where('id', $driver_id);
        return $this->db->get('drivers')->row();
    }

    /**
     * Get unit data
     */
    public function get_unit($unit_id)
    {
        $this->db->where('id', $unit_id);
        return $this->db->get('units')->row();
    }

    // ==================== DROPDOWN DATA ====================

    /**
     * Get all rute for dropdown
     */
    public function get_all_rute()
    {
        $this->db->select('kode_rute, customer, origin, dest1, service, sla, harga');
        $this->db->order_by('customer', 'ASC');
        $this->db->order_by('origin', 'ASC');
        return $this->db->get('tb_rute')->result();
    }

    /**
     * Get all drivers for dropdown
     */
    public function get_all_drivers()
    {
        $this->db->select('id, nama_driver, nik, sim');
        $this->db->order_by('nama_driver', 'ASC');
        return $this->db->get('drivers')->result();
    }

    /**
     * Get all units for dropdown
     */
    public function get_all_units()
    {
        $this->db->select('id, no_polisi, tipe_unit, tipe_box, tonase');
        $this->db->order_by('no_polisi', 'ASC');
        return $this->db->get('units')->result();
    }

    /**
     * Get available drivers (not currently on trip)
     */
    public function get_available_drivers()
    {
        // Drivers not in active trips
        $this->db->select('d.*');
        $this->db->from('drivers d');
        $this->db->where("d.id NOT IN (
            SELECT driver_id FROM tb_surat_jalan 
            WHERE status IN ('scheduled', 'on_trip') 
            AND driver_id IS NOT NULL
        )", NULL, FALSE);
        $this->db->order_by('d.nama_driver', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get available units (not currently on trip)
     */
    public function get_available_units()
    {
        // Units not in active trips
        $this->db->select('u.*');
        $this->db->from('units u');
        $this->db->where("u.id NOT IN (
            SELECT unit_id FROM tb_surat_jalan 
            WHERE status IN ('scheduled', 'on_trip') 
            AND unit_id IS NOT NULL
        )", NULL, FALSE);
        $this->db->order_by('u.no_polisi', 'ASC');

        return $this->db->get()->result();
    }

    // ==================== STATISTICS & REPORTS ====================

    /**
     * Get summary statistics
     */
    public function get_summary($filters = [])
    {
        $this->db->select('
            COUNT(*) as total_surat_jalan,
            SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as total_draft,
            SUM(CASE WHEN status = "scheduled" THEN 1 ELSE 0 END) as total_scheduled,
            SUM(CASE WHEN status = "on_trip" THEN 1 ELSE 0 END) as total_on_trip,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as total_completed,
            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as total_cancelled,
            SUM(biaya_sewa) as total_revenue,
            SUM(total_biaya) as total_cost,
            SUM(CASE WHEN sla_status = "on_time" THEN 1 ELSE 0 END) as total_on_time,
            SUM(CASE WHEN sla_status = "late" THEN 1 ELSE 0 END) as total_late,
            SUM(CASE WHEN sla_status = "very_late" THEN 1 ELSE 0 END) as total_very_late
        ');

        // Apply date filters
        if (!empty($filters['tanggal_dari'])) {
            $this->db->where('tanggal >=', $filters['tanggal_dari']);
        }

        if (!empty($filters['tanggal_sampai'])) {
            $this->db->where('tanggal <=', $filters['tanggal_sampai']);
        }

        return $this->db->get($this->table)->row();
    }

    /**
     * Get performance by driver
     */
    public function get_driver_performance($date_from = null, $date_to = null)
    {
        $this->db->select('
            d.id,
            d.nama_driver,
            COUNT(sj.id) as total_trips,
            SUM(CASE WHEN sj.status = "completed" THEN 1 ELSE 0 END) as completed_trips,
            SUM(CASE WHEN sj.sla_status = "on_time" THEN 1 ELSE 0 END) as on_time_trips,
            ROUND(SUM(CASE WHEN sj.sla_status = "on_time" THEN 1 ELSE 0 END) * 100.0 / COUNT(sj.id), 2) as on_time_percentage,
            AVG(sj.keterlambatan) as avg_delay_minutes
        ');
        $this->db->from('drivers d');
        $this->db->join('tb_surat_jalan sj', 'sj.driver_id = d.id', 'left');

        if ($date_from) {
            $this->db->where('sj.tanggal >=', $date_from);
        }

        if ($date_to) {
            $this->db->where('sj.tanggal <=', $date_to);
        }

        $this->db->group_by('d.id');
        $this->db->order_by('on_time_percentage', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Get performance by unit
     */
    public function get_unit_performance($date_from = null, $date_to = null)
    {
        $this->db->select('
            u.id,
            u.no_polisi,
            u.tipe_unit,
            COUNT(sj.id) as total_trips,
            SUM(sj.biaya_sewa) as total_revenue,
            SUM(sj.total_biaya) as total_cost,
            (SUM(sj.biaya_sewa) - SUM(sj.total_biaya)) as profit
        ');
        $this->db->from('units u');
        $this->db->join('tb_surat_jalan sj', 'sj.unit_id = u.id', 'left');

        if ($date_from) {
            $this->db->where('sj.tanggal >=', $date_from);
        }

        if ($date_to) {
            $this->db->where('sj.tanggal <=', $date_to);
        }

        $this->db->group_by('u.id');
        $this->db->order_by('total_trips', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Get performance by customer
     */
    public function get_customer_performance($date_from = null, $date_to = null)
    {
        $this->db->select('
            customer,
            COUNT(*) as total_trips,
            SUM(biaya_sewa) as total_revenue,
            AVG(biaya_sewa) as avg_revenue_per_trip,
            SUM(CASE WHEN sla_status = "on_time" THEN 1 ELSE 0 END) as on_time_trips,
            ROUND(SUM(CASE WHEN sla_status = "on_time" THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as on_time_percentage
        ');

        if ($date_from) {
            $this->db->where('tanggal >=', $date_from);
        }

        if ($date_to) {
            $this->db->where('tanggal <=', $date_to);
        }

        $this->db->group_by('customer');
        $this->db->order_by('total_revenue', 'DESC');

        return $this->db->get($this->table)->result();
    }

    /**
     * Get overdue/late deliveries
     */
    public function get_overdue_deliveries()
    {
        $this->db->select('
            sj.*,
            d.nama_driver,
            u.no_polisi,
            TIMESTAMPDIFF(MINUTE, sj.target_tiba, NOW()) as minutes_overdue
        ');
        $this->db->from($this->table . ' sj');
        $this->db->join('drivers d', 'd.id = sj.driver_id', 'left');
        $this->db->join('units u', 'u.id = sj.unit_id', 'left');
        $this->db->where('sj.status', 'on_trip');
        $this->db->where('sj.target_tiba <', date('Y-m-d H:i:s'));
        $this->db->order_by('sj.target_tiba', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get upcoming departures (scheduled for today/tomorrow)
     */
    public function get_upcoming_departures($days = 1)
    {
        $this->db->select('sj.*, d.nama_driver, u.no_polisi');
        $this->db->from($this->table . ' sj');
        $this->db->join('drivers d', 'd.id = sj.driver_id', 'left');
        $this->db->join('units u', 'u.id = sj.unit_id', 'left');
        $this->db->where('sj.status', 'scheduled');
        $this->db->where('sj.tanggal >=', date('Y-m-d'));
        $this->db->where('sj.tanggal <=', date('Y-m-d', strtotime("+{$days} days")));
        $this->db->order_by('sj.tanggal', 'ASC');
        $this->db->order_by('sj.jam_berangkat', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get monthly statistics
     */
    public function get_monthly_stats($year, $month)
    {
        $this->db->select('
            COUNT(*) as total_trips,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
            SUM(biaya_sewa) as revenue,
            SUM(total_biaya) as cost,
            (SUM(biaya_sewa) - SUM(total_biaya)) as profit,
            ROUND(SUM(CASE WHEN sla_status = "on_time" THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as sla_compliance
        ');
        $this->db->where('YEAR(tanggal)', $year);
        $this->db->where('MONTH(tanggal)', $month);

        return $this->db->get($this->table)->row();
    }

    // ==================== VALIDATION ====================

    /**
     * Check if no_surat_jalan exists (for duplicate check)
     */
    public function check_duplicate_no($no_surat_jalan, $exclude_id = null)
    {
        $this->db->where('no_surat_jalan', $no_surat_jalan);

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        return $this->db->get($this->table)->row();
    }

    /**
     * Check if driver is available
     */
    public function is_driver_available($driver_id, $tanggal, $exclude_id = null)
    {
        $this->db->where('driver_id', $driver_id);
        $this->db->where('tanggal', $tanggal);
        $this->db->where_in('status', ['scheduled', 'on_trip']);

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        $existing = $this->db->get($this->table)->row();

        return !$existing; // Return true if no conflict
    }

    /**
     * Check if unit is available
     */
    public function is_unit_available($unit_id, $tanggal, $exclude_id = null)
    {
        $this->db->where('unit_id', $unit_id);
        $this->db->where('tanggal', $tanggal);
        $this->db->where_in('status', ['scheduled', 'on_trip']);

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        $existing = $this->db->get($this->table)->row();

        return !$existing; // Return true if no conflict
    }

    // ==================== EXPORT ====================

    /**
     * Get data for Excel export
     */
    public function get_export_data($filters = [])
    {
        $this->db->select('
            sj.no_surat_jalan,
            sj.tanggal,
            sj.customer,
            sj.service,
            sj.sla,
            sj.origin,
            sj.dest1,
            sj.dest2,
            sj.dest3,
            sj.dest4,
            d.nama_driver,
            u.no_polisi,
            u.tipe_unit,
            sj.muatan,
            sj.tonase_aktual,
            sj.biaya_sewa,
            sj.total_biaya,
            sj.status,
            sj.jam_berangkat,
            sj.jam_tiba,
            sj.target_tiba,
            sj.keterlambatan,
            sj.sla_status
        ');
        $this->db->from($this->table . ' sj');
        $this->db->join('drivers d', 'd.id = sj.driver_id', 'left');
        $this->db->join('units u', 'u.id = sj.unit_id', 'left');

        // Apply same filters as get_all
        if (!empty($filters['tanggal_dari'])) {
            $this->db->where('sj.tanggal >=', $filters['tanggal_dari']);
        }

        if (!empty($filters['tanggal_sampai'])) {
            $this->db->where('sj.tanggal <=', $filters['tanggal_sampai']);
        }

        if (!empty($filters['status'])) {
            $this->db->where('sj.status', $filters['status']);
        }

        if (!empty($filters['customer'])) {
            $this->db->like('sj.customer', $filters['customer']);
        }

        $this->db->order_by('sj.tanggal', 'DESC');

        return $this->db->get()->result();
    }

} // End of M_surat_jalan class