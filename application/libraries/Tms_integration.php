<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * TMS Integration Library
 * 
 * Handles automatic updates to TMS system when Surat Jalan is created/updated
 * - Auto-update Unit KM and fuel consumption
 * - Auto-update Driver trips and performance
 * - Auto-check maintenance alerts
 * - Auto-generate alerts if needed
 */
class Tms_integration
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('M_unit', 'unit');
        $this->CI->load->model('M_driver', 'driver');
        $this->CI->load->model('M_tms_alerts', 'alerts');
        $this->CI->load->library('Tms_alert_generator');
    }

    // ========================================
    // MAIN INTEGRATION FUNCTION
    // Called after Surat Jalan saved
    // ========================================

    public function process_surat_jalan($surat_jalan_data)
    {
        $results = [
            'unit_updated' => false,
            'driver_updated' => false,
            'fuel_logged' => false,
            'alerts_generated' => 0,
            'errors' => []
        ];

        try {
            // 1. Update Unit data
            if (!empty($surat_jalan_data['unit_id'])) {
                $results['unit_updated'] = $this->update_unit_after_trip($surat_jalan_data);
            }

            // 2. Update Driver data
            if (!empty($surat_jalan_data['driver_id'])) {
                $results['driver_updated'] = $this->update_driver_after_trip($surat_jalan_data);
            }

            // 3. Log fuel consumption (if BBM data available)
            if (!empty($surat_jalan_data['unit_id']) && isset($surat_jalan_data['biaya_solar'])) {
                $results['fuel_logged'] = $this->log_fuel_consumption($surat_jalan_data);
            }

            // 4. Check and generate alerts
            $alert_count = $this->check_and_generate_alerts($surat_jalan_data);
            $results['alerts_generated'] = $alert_count;

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            log_message('error', 'TMS Integration Error: ' . $e->getMessage());
        }

        return $results;
    }

    // ========================================
    // UPDATE UNIT AFTER TRIP
    // ========================================

    private function update_unit_after_trip($surat_jalan_data)
    {
        $unit_id = $surat_jalan_data['unit_id'];

        // Get current unit data
        $unit = $this->CI->unit->get_by_id($unit_id);
        if (!$unit) {
            return false;
        }

        // Prepare update data
        $update_data = [];

        // 1. Update current_km (table uses current_km, not km_terakhir)
        if (isset($surat_jalan_data['km_akhir']) && $surat_jalan_data['km_akhir'] > 0) {
            $update_data['current_km'] = $surat_jalan_data['km_akhir'];
        }
        // OR calculate from km_awal + jarak (if available)
        elseif (isset($surat_jalan_data['km_awal']) && isset($surat_jalan_data['jarak'])) {
            $update_data['current_km'] = $surat_jalan_data['km_awal'] + $surat_jalan_data['jarak'];
        }

        // 2. Update tanggal terakhir digunakan
        if (isset($surat_jalan_data['tanggal'])) {
            $update_data['last_used_date'] = $surat_jalan_data['tanggal'];
        }

        // 3. Calculate fuel efficiency (if BBM and distance available)
        if (
            isset($surat_jalan_data['biaya_solar']) &&
            isset($surat_jalan_data['jarak']) &&
            $surat_jalan_data['jarak'] > 0
        ) {

            // Assume fuel price (or get from settings)
            $fuel_price_per_liter = 6500; // Default Rp 6,500/liter

            $fuel_liters = $surat_jalan_data['biaya_solar'] / $fuel_price_per_liter;
            $fuel_efficiency = $surat_jalan_data['jarak'] / $fuel_liters;

            // Update konsumsi BBM (km/liter)
            $update_data['konsumsi_bbm'] = round($fuel_efficiency, 2);
        }

        // 4. Update status_unit to 'aktif' (table uses status_unit, not status)
        if (isset($unit->status_unit) && $unit->status_unit != 'aktif') {
            $update_data['status_unit'] = 'aktif';
        }

        // Perform update if there's data to update
        if (!empty($update_data)) {
            $this->CI->db->where('id', $unit_id);
            return $this->CI->db->update('units', $update_data);
        }

        return true;
    }

    // ========================================
    // UPDATE DRIVER AFTER TRIP
    // ========================================

    private function update_driver_after_trip($surat_jalan_data)
    {
        $driver_id = $surat_jalan_data['driver_id'];

        // Get current driver data
        $driver = $this->CI->driver->get_by_id($driver_id);
        if (!$driver) {
            return false;
        }

        // Prepare update data
        $update_data = [];

        // 1. Increment total trip
        $current_trips = $driver->total_trip ?? 0;
        $update_data['total_trip'] = $current_trips + 1;

        // 2. Update last trip date
        if (isset($surat_jalan_data['tanggal'])) {
            $update_data['last_trip_date'] = $surat_jalan_data['tanggal'];
        }

        // 3. Update status_driver to 'aktif' (table uses status_driver, not status)
        if (isset($driver->status_driver) && $driver->status_driver != 'aktif') {
            $update_data['status_driver'] = 'aktif';
        }

        // 4. Calculate performance score (simple: based on trip count and violations)
        // Get violation count
        $this->CI->db->where('driver_id', $driver_id);
        $this->CI->db->where('status', 'pending');
        $violation_count = $this->CI->db->count_all_results('driver_violations');

        // Simple scoring: Start with 5.0, minus 0.5 per violation
        $base_score = 5.0;
        $penalty = $violation_count * 0.5;
        $performance_score = max(1.0, $base_score - $penalty);

        $update_data['rating'] = round($performance_score, 1);

        // Perform update
        $this->CI->db->where('id', $driver_id);
        return $this->CI->db->update('drivers', $update_data);
    }

    // ========================================
    // LOG FUEL CONSUMPTION
    // ========================================

    private function log_fuel_consumption($surat_jalan_data)
    {
        // Check if unit_fuel table exists
        if (!$this->CI->db->table_exists('unit_fuel')) {
            return false;
        }

        $unit_id = $surat_jalan_data['unit_id'];

        // Calculate fuel data
        $fuel_price_per_liter = 6500; // Default
        $fuel_cost = $surat_jalan_data['biaya_solar'] ?? 0;

        if ($fuel_cost <= 0) {
            return false;
        }

        $fuel_liters = $fuel_cost / $fuel_price_per_liter;
        $distance = $surat_jalan_data['jarak'] ?? 0;
        $efficiency = $distance > 0 ? ($distance / $fuel_liters) : 0;

        // Prepare fuel log data
        $fuel_data = [
            'unit_id' => $unit_id,
            'tanggal_isi' => $surat_jalan_data['tanggal'] ?? date('Y-m-d'),
            'jumlah_liter' => round($fuel_liters, 2),
            'harga_per_liter' => $fuel_price_per_liter,
            'total_biaya' => $fuel_cost,
            'km_saat_isi' => $surat_jalan_data['km_akhir'] ?? $surat_jalan_data['km_awal'] ?? 0,
            'jarak_tempuh' => $distance,
            'konsumsi_per_km' => $efficiency > 0 ? round($efficiency, 2) : 0,
            'no_surat_jalan' => $surat_jalan_data['no_surat_jalan'] ?? null,
            'keterangan' => 'Auto-logged from Surat Jalan',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Insert fuel log
        return $this->CI->db->insert('unit_fuel', $fuel_data);
    }

    // ========================================
    // CHECK AND GENERATE ALERTS
    // ========================================

    private function check_and_generate_alerts($surat_jalan_data)
    {
        $alert_count = 0;

        // 1. Check unit maintenance due (based on KM)
        if (!empty($surat_jalan_data['unit_id'])) {
            $unit = $this->CI->unit->get_by_id($surat_jalan_data['unit_id']);

            if ($unit && isset($unit->current_km) && isset($unit->next_service_km)) {
                $km_left = $unit->next_service_km - $unit->current_km;

                // Generate alert if service due soon
                if ($km_left <= 1000 && $km_left >= 0) {
                    // Check if alert already exists
                    $this->CI->db->where('alert_type', 'service_due');
                    $this->CI->db->where('reference_type', 'unit');
                    $this->CI->db->where('reference_id', $unit->id);
                    $this->CI->db->where('status', 'pending');
                    $existing = $this->CI->db->count_all_results('tms_alerts');

                    if ($existing == 0) {
                        $priority = $km_left <= 0 ? 'critical' : ($km_left <= 500 ? 'high' : 'medium');

                        $alert_data = [
                            'alert_type' => 'service_due',
                            'priority' => $priority,
                            'reference_type' => 'unit',
                            'reference_id' => $unit->id,
                            'title' => 'Service Due: ' . $unit->no_polisi,
                            'message' => $km_left <= 0
                                ? 'Unit sudah melewati jadwal service! Segera lakukan service.'
                                : 'Unit akan jatuh tempo service dalam ' . $km_left . ' km lagi.',
                            'status' => 'pending',
                            'created_at' => date('Y-m-d H:i:s')
                        ];

                        if ($this->CI->db->insert('tms_alerts', $alert_data)) {
                            $alert_count++;
                        }
                    }
                }
            }
        }

        // 2. Check document expiry (STNK, KIR) after trip
        if (!empty($surat_jalan_data['unit_id'])) {
            // Check if tms_alert_generator exists before calling
            if (isset($this->CI->tms_alert_generator)) {
                // Run alert generator for this specific unit
                $generator_results = $this->CI->tms_alert_generator->generate_stnk_alerts();
                $alert_count += $generator_results;

                $generator_results = $this->CI->tms_alert_generator->generate_kir_alerts();
                $alert_count += $generator_results;
            }
        }

        // 3. Check driver SIM expiry
        if (!empty($surat_jalan_data['driver_id'])) {
            // Check if tms_alert_generator exists before calling
            if (isset($this->CI->tms_alert_generator)) {
                $generator_results = $this->CI->tms_alert_generator->generate_sim_alerts();
                $alert_count += $generator_results;
            }
        }

        return $alert_count;
    }

    // ========================================
    // CALCULATE TRIP STATISTICS (Optional)
    // ========================================

    public function calculate_trip_stats($unit_id = null, $driver_id = null)
    {
        $stats = [];

        // Get trip statistics from surat jalan
        $this->CI->db->select('
            COUNT(*) as total_trips,
            SUM(tonase_aktual) as total_tonase,
            AVG(tonase_aktual) as avg_tonase,
            SUM(biaya_solar) as total_fuel_cost
        ');

        if ($unit_id) {
            $this->CI->db->where('unit_id', $unit_id);
        }

        if ($driver_id) {
            $this->CI->db->where('driver_id', $driver_id);
        }

        // Last 30 days
        $this->CI->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')));

        $query = $this->CI->db->get('tb_surat_jalan');
        $stats = $query->row_array();

        return $stats;
    }

    // ========================================
    // GET FUEL EFFICIENCY TREND
    // ========================================

    public function get_fuel_efficiency_trend($unit_id, $months = 6)
    {
        if (!$this->CI->db->table_exists('unit_fuel')) {
            return [];
        }

        $this->CI->db->select("
            DATE_FORMAT(tanggal_isi, '%Y-%m') as month,
            AVG(konsumsi_per_km) as avg_efficiency,
            SUM(total_biaya) as total_cost,
            SUM(jumlah_liter) as total_liters
        ");
        $this->CI->db->where('unit_id', $unit_id);
        $this->CI->db->where('tanggal_isi >=', date('Y-m-d', strtotime("-$months months")));
        $this->CI->db->group_by("DATE_FORMAT(tanggal_isi, '%Y-%m')");
        $this->CI->db->order_by('month', 'ASC');

        return $this->CI->db->get('unit_fuel')->result_array();
    }
}