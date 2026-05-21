<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_tms_dashboard extends CI_Model
{
    // ========================================
    // FLEET STATISTICS
    // ========================================

    public function get_fleet_stats()
    {
        // Units statistics
        $units = $this->db->get('units')->result();

        $unit_stats = [
            'total' => count($units),
            'aktif' => 0,
            'maintenance' => 0,
            'rusak' => 0,
            'dijual' => 0,
            'nonaktif' => 0
        ];

        foreach ($units as $unit) {
            $status = $unit->status_unit ?? 'aktif';
            if (isset($unit_stats[$status])) {
                $unit_stats[$status]++;
            }
        }

        // Drivers statistics
        $drivers = $this->db->get('drivers')->result();

        $driver_stats = [
            'total' => count($drivers),
            'aktif' => 0,
            'cuti' => 0,
            'resign' => 0,
            'nonaktif' => 0
        ];

        foreach ($drivers as $driver) {
            $status = $driver->status_driver ?? 'aktif';
            if (isset($driver_stats[$status])) {
                $driver_stats[$status]++;
            }
        }

        return [
            'units' => $unit_stats,
            'drivers' => $driver_stats
        ];
    }

    // ========================================
    // EXPIRY STATISTICS
    // ========================================

    public function get_expiry_stats()
    {
        $stats = [
            'stnk_expired' => 0,
            'stnk_expiring_soon' => 0,
            'kir_expired' => 0,
            'kir_expiring_soon' => 0,
            'sim_expired' => 0,
            'sim_expiring_soon' => 0
        ];

        $today = time();
        $thirty_days = 30 * 86400;

        // Check STNK
        $units = $this->db->select('stnk_expired, kir_expired')->get('units')->result();
        foreach ($units as $unit) {
            if (!empty($unit->stnk_expired)) {
                $diff = strtotime($unit->stnk_expired) - $today;
                if ($diff <= 0) {
                    $stats['stnk_expired']++;
                } elseif ($diff <= $thirty_days) {
                    $stats['stnk_expiring_soon']++;
                }
            }

            if (!empty($unit->kir_expired)) {
                $diff = strtotime($unit->kir_expired) - $today;
                if ($diff <= 0) {
                    $stats['kir_expired']++;
                } elseif ($diff <= $thirty_days) {
                    $stats['kir_expiring_soon']++;
                }
            }
        }

        // Check SIM
        $drivers = $this->db->select('masa_berlaku_sim')->get('drivers')->result();
        foreach ($drivers as $driver) {
            if (!empty($driver->masa_berlaku_sim)) {
                $diff = strtotime($driver->masa_berlaku_sim) - $today;
                if ($diff <= 0) {
                    $stats['sim_expired']++;
                } elseif ($diff <= $thirty_days) {
                    $stats['sim_expiring_soon']++;
                }
            }
        }

        return $stats;
    }

    // ========================================
    // SERVICE DUE
    // ========================================

    public function get_units_need_service()
    {
        $this->db->select('id, no_polisi, current_km, next_service_km');
        $this->db->where('next_service_km IS NOT NULL');
        $this->db->where('current_km IS NOT NULL');
        $this->db->where('next_service_km > 0');
        $units = $this->db->get('units')->result();

        $need_service = [];
        foreach ($units as $unit) {
            $km_left = $unit->next_service_km - $unit->current_km;
            if ($km_left <= 1000) { // Service due within 1000 km
                $need_service[] = [
                    'id' => $unit->id,
                    'no_polisi' => $unit->no_polisi,
                    'current_km' => $unit->current_km,
                    'next_service_km' => $unit->next_service_km,
                    'km_left' => $km_left
                ];
            }
        }

        return $need_service;
    }

    // ========================================
    // AVERAGE FUEL EFFICIENCY
    // ========================================

    public function get_avg_fuel_efficiency()
    {
        $this->db->select_avg('konsumsi_bbm', 'avg_konsumsi');
        $this->db->where('konsumsi_bbm IS NOT NULL');
        $this->db->where('konsumsi_bbm > 0');
        $result = $this->db->get('units')->row();

        return $result->avg_konsumsi ?? 0;
    }

    // ========================================
    // AVERAGE DRIVER RATING
    // ========================================

    public function get_avg_driver_rating()
    {
        $this->db->select_avg('rating', 'avg_rating');
        $this->db->where('rating IS NOT NULL');
        $this->db->where('rating > 0');
        $result = $this->db->get('drivers')->row();

        return $result->avg_rating ?? 0;
    }

    // ========================================
    // MONTHLY TRENDS (Last 6 months)
    // ========================================

    public function get_monthly_trends()
    {
        $trends = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $month_name = date('M Y', strtotime("-$i months"));

            // Use 'tanggal' column
            $this->db->where("DATE_FORMAT(tanggal, '%Y-%m') =", $month);
            $surat_jalan_count = $this->db->count_all_results('tb_surat_jalan');

            $trends[] = [
                'month' => $month_name,
                'surat_jalan' => $surat_jalan_count
            ];
        }

        return $trends;
    }

    // ========================================
    // EXPIRY TIMELINE (Next 90 days)
    // ========================================

    public function get_expiry_timeline()
    {
        $timeline = [
            '0-7' => ['stnk' => 0, 'kir' => 0, 'sim' => 0],
            '8-30' => ['stnk' => 0, 'kir' => 0, 'sim' => 0],
            '31-60' => ['stnk' => 0, 'kir' => 0, 'sim' => 0],
            '61-90' => ['stnk' => 0, 'kir' => 0, 'sim' => 0]
        ];

        $today = time();

        // STNK & KIR
        $units = $this->db->select('stnk_expired, kir_expired')->get('units')->result();
        foreach ($units as $unit) {
            if (!empty($unit->stnk_expired)) {
                $days = ceil((strtotime($unit->stnk_expired) - $today) / 86400);
                if ($days >= 0 && $days <= 7) $timeline['0-7']['stnk']++;
                elseif ($days >= 8 && $days <= 30) $timeline['8-30']['stnk']++;
                elseif ($days >= 31 && $days <= 60) $timeline['31-60']['stnk']++;
                elseif ($days >= 61 && $days <= 90) $timeline['61-90']['stnk']++;
            }

            if (!empty($unit->kir_expired)) {
                $days = ceil((strtotime($unit->kir_expired) - $today) / 86400);
                if ($days >= 0 && $days <= 7) $timeline['0-7']['kir']++;
                elseif ($days >= 8 && $days <= 30) $timeline['8-30']['kir']++;
                elseif ($days >= 31 && $days <= 60) $timeline['31-60']['kir']++;
                elseif ($days >= 61 && $days <= 90) $timeline['61-90']['kir']++;
            }
        }

        // SIM
        $drivers = $this->db->select('masa_berlaku_sim')->get('drivers')->result();
        foreach ($drivers as $driver) {
            if (!empty($driver->masa_berlaku_sim)) {
                $days = ceil((strtotime($driver->masa_berlaku_sim) - $today) / 86400);
                if ($days >= 0 && $days <= 7) $timeline['0-7']['sim']++;
                elseif ($days >= 8 && $days <= 30) $timeline['8-30']['sim']++;
                elseif ($days >= 31 && $days <= 60) $timeline['31-60']['sim']++;
                elseif ($days >= 61 && $days <= 90) $timeline['61-90']['sim']++;
            }
        }

        return $timeline;
    }

    // ========================================
    // TOP PERFORMING DRIVERS
    // ========================================

    public function get_top_drivers($limit = 5)
    {
        $this->db->select('id, nama_driver, rating, total_trip');
        $this->db->where('rating > 0');
        $this->db->order_by('rating', 'DESC');
        $this->db->order_by('total_trip', 'DESC');
        $this->db->limit($limit);

        return $this->db->get('drivers')->result();
    }

    // ========================================
    // RECENT ACTIVITIES
    // ========================================

    public function get_recent_activities($limit = 10)
    {
        $this->db->select('
            tb_surat_jalan.id,
            tb_surat_jalan.no_surat_jalan,
            tb_surat_jalan.tanggal,
            tb_surat_jalan.customer,
            tb_surat_jalan.origin,
            tb_surat_jalan.dest1,
            tb_surat_jalan.muatan,
            tb_surat_jalan.tonase_aktual,
            units.no_polisi,
            drivers.nama_driver
        ');
        $this->db->from('tb_surat_jalan');
        $this->db->join('units', 'tb_surat_jalan.unit_id = units.id', 'left');
        $this->db->join('drivers', 'tb_surat_jalan.driver_id = drivers.id', 'left');
        $this->db->order_by('tb_surat_jalan.tanggal', 'DESC');
        $this->db->order_by('tb_surat_jalan.id', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result_array();
    }
}
