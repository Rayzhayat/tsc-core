<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tms_alert_generator
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('M_tms_alerts', 'alerts');
        $this->CI->load->database();
    }

    // ========================================
    // GENERATE ALL ALERTS
    // ========================================

    public function generate_all_alerts()
    {
        $results = [
            'stnk' => $this->generate_stnk_alerts(),
            'kir' => $this->generate_kir_alerts(),
            'sim' => $this->generate_sim_alerts(),
            'service' => $this->generate_service_alerts()
        ];

        return $results;
    }

    // ========================================
    // GENERATE STNK ALERTS
    // ========================================

    public function generate_stnk_alerts()
    {
        $units = $this->CI->db->select('id, no_polisi, stnk_expired')
            ->where('stnk_expired IS NOT NULL')
            ->get('units')
            ->result();

        $count = 0;
        $today = time();

        foreach ($units as $unit) {
            $days_left = ceil((strtotime($unit->stnk_expired) - $today) / 86400);

            $priority = null;
            $title = null;
            $message = null;

            if ($days_left < 0) {
                // CRITICAL: Already expired
                $priority = 'critical';
                $title = 'STNK Kadaluarsa - ' . $unit->no_polisi;
                $message = 'STNK unit ' . $unit->no_polisi . ' sudah kadaluarsa sejak ' . abs($days_left) . ' hari yang lalu. Segera perpanjang!';
            } elseif ($days_left <= 7) {
                // HIGH: Expire in 7 days
                $priority = 'high';
                $title = 'STNK Akan Kadaluarsa - ' . $unit->no_polisi;
                $message = 'STNK unit ' . $unit->no_polisi . ' akan kadaluarsa dalam ' . $days_left . ' hari. Mohon segera diproses!';
            } elseif ($days_left <= 30) {
                // MEDIUM: Expire in 30 days
                $priority = 'medium';
                $title = 'Reminder STNK - ' . $unit->no_polisi;
                $message = 'STNK unit ' . $unit->no_polisi . ' akan kadaluarsa dalam ' . $days_left . ' hari.';
            }

            if ($priority) {
                $data = [
                    'alert_type' => 'stnk_expired',
                    'reference_type' => 'unit',
                    'reference_id' => $unit->id,
                    'title' => $title,
                    'message' => $message,
                    'alert_date' => date('Y-m-d'),
                    'expired_date' => $unit->stnk_expired,
                    'status' => 'pending',
                    'priority' => $priority
                ];

                if ($this->CI->alerts->create_alert($data)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    // ========================================
    // GENERATE KIR ALERTS
    // ========================================

    public function generate_kir_alerts()
    {
        $units = $this->CI->db->select('id, no_polisi, kir_expired')
            ->where('kir_expired IS NOT NULL')
            ->get('units')
            ->result();

        $count = 0;
        $today = time();

        foreach ($units as $unit) {
            $days_left = ceil((strtotime($unit->kir_expired) - $today) / 86400);

            $priority = null;
            $title = null;
            $message = null;

            if ($days_left < 0) {
                $priority = 'critical';
                $title = 'KIR Kadaluarsa - ' . $unit->no_polisi;
                $message = 'KIR unit ' . $unit->no_polisi . ' sudah kadaluarsa sejak ' . abs($days_left) . ' hari yang lalu. Unit tidak boleh beroperasi!';
            } elseif ($days_left <= 7) {
                $priority = 'high';
                $title = 'KIR Akan Kadaluarsa - ' . $unit->no_polisi;
                $message = 'KIR unit ' . $unit->no_polisi . ' akan kadaluarsa dalam ' . $days_left . ' hari. Segera lakukan uji KIR!';
            } elseif ($days_left <= 30) {
                $priority = 'medium';
                $title = 'Reminder KIR - ' . $unit->no_polisi;
                $message = 'KIR unit ' . $unit->no_polisi . ' akan kadaluarsa dalam ' . $days_left . ' hari.';
            }

            if ($priority) {
                $data = [
                    'alert_type' => 'kir_expired',
                    'reference_type' => 'unit',
                    'reference_id' => $unit->id,
                    'title' => $title,
                    'message' => $message,
                    'alert_date' => date('Y-m-d'),
                    'expired_date' => $unit->kir_expired,
                    'status' => 'pending',
                    'priority' => $priority
                ];

                if ($this->CI->alerts->create_alert($data)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    // ========================================
    // GENERATE SIM ALERTS
    // ========================================

    public function generate_sim_alerts()
    {
        $drivers = $this->CI->db->select('id, nama_driver, masa_berlaku_sim')
            ->where('masa_berlaku_sim IS NOT NULL')
            ->get('drivers')
            ->result();

        $count = 0;
        $today = time();

        foreach ($drivers as $driver) {
            $days_left = ceil((strtotime($driver->masa_berlaku_sim) - $today) / 86400);

            $priority = null;
            $title = null;
            $message = null;

            if ($days_left < 0) {
                $priority = 'critical';
                $title = 'SIM Kadaluarsa - ' . $driver->nama_driver;
                $message = 'SIM driver ' . $driver->nama_driver . ' sudah kadaluarsa sejak ' . abs($days_left) . ' hari yang lalu. Driver tidak boleh beroperasi!';
            } elseif ($days_left <= 7) {
                $priority = 'high';
                $title = 'SIM Akan Kadaluarsa - ' . $driver->nama_driver;
                $message = 'SIM driver ' . $driver->nama_driver . ' akan kadaluarsa dalam ' . $days_left . ' hari. Segera perpanjang!';
            } elseif ($days_left <= 30) {
                $priority = 'medium';
                $title = 'Reminder SIM - ' . $driver->nama_driver;
                $message = 'SIM driver ' . $driver->nama_driver . ' akan kadaluarsa dalam ' . $days_left . ' hari.';
            }

            if ($priority) {
                $data = [
                    'alert_type' => 'sim_expired',
                    'reference_type' => 'driver',
                    'reference_id' => $driver->id,
                    'title' => $title,
                    'message' => $message,
                    'alert_date' => date('Y-m-d'),
                    'expired_date' => $driver->masa_berlaku_sim,
                    'status' => 'pending',
                    'priority' => $priority
                ];

                if ($this->CI->alerts->create_alert($data)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    // ========================================
    // GENERATE SERVICE ALERTS
    // ========================================

    public function generate_service_alerts()
    {
        $units = $this->CI->db->select('id, no_polisi, current_km, next_service_km')
            ->where('next_service_km IS NOT NULL')
            ->where('current_km IS NOT NULL')
            ->where('next_service_km > 0')
            ->get('units')
            ->result();

        $count = 0;

        foreach ($units as $unit) {
            $km_left = $unit->next_service_km - $unit->current_km;

            $priority = null;
            $title = null;
            $message = null;

            if ($km_left <= 0) {
                $priority = 'critical';
                $title = 'Service Overdue - ' . $unit->no_polisi;
                $message = 'Unit ' . $unit->no_polisi . ' sudah melewati target service (' . number_format($unit->next_service_km) . ' km). Current: ' . number_format($unit->current_km) . ' km. Segera lakukan service!';
            } elseif ($km_left <= 500) {
                $priority = 'high';
                $title = 'Service Due Soon - ' . $unit->no_polisi;
                $message = 'Unit ' . $unit->no_polisi . ' akan service dalam ' . number_format($km_left) . ' km lagi. Target: ' . number_format($unit->next_service_km) . ' km.';
            } elseif ($km_left <= 1000) {
                $priority = 'medium';
                $title = 'Service Reminder - ' . $unit->no_polisi;
                $message = 'Unit ' . $unit->no_polisi . ' akan service dalam ' . number_format($km_left) . ' km. Target: ' . number_format($unit->next_service_km) . ' km.';
            }

            if ($priority) {
                $data = [
                    'alert_type' => 'service_due',
                    'reference_type' => 'unit',
                    'reference_id' => $unit->id,
                    'title' => $title,
                    'message' => $message,
                    'alert_date' => date('Y-m-d'),
                    'expired_date' => null,
                    'status' => 'pending',
                    'priority' => $priority
                ];

                if ($this->CI->alerts->create_alert($data)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    // ========================================
    // CLEANUP OLD ALERTS
    // ========================================

    public function cleanup_old_alerts($days = 30)
    {
        return $this->CI->alerts->delete_old_resolved_alerts($days);
    }
}
