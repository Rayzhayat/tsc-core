<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_vendor_performance extends CI_Model
{
    // ════════════════════════════════════════════════════════════
    // RELIABILITY SCORE LOGIC
    // Reliability Score = 100 - unfulfill_pct
    // Andal      : score >= 90  (unfulfill < 10%)
    // Cukup      : score >= 70  (unfulfill 10-30%)
    // Bermasalah : score <  70  (unfulfill > 30%)
    // ════════════════════════════════════════════════════════════
    public function calc_reliability($unfulfill_pct)
    {
        return round(100 - $unfulfill_pct, 1);
    }

    public function reliability_label($score)
    {
        if ($score >= 90)
            return 'andal';
        if ($score >= 70)
            return 'cukup';
        return 'bermasalah';
    }

    private function _apply_filters($filters = [])
    {
        if (!empty($filters['sheet_type']))
            $this->db->where('sheet_type', $filters['sheet_type']);
        if (!empty($filters['periode']))
            $this->db->where('periode', $filters['periode']);
        if (!empty($filters['start_date_from']))
            $this->db->where('start_date >=', $filters['start_date_from']);
        if (!empty($filters['start_date_to']))
            $this->db->where('start_date <=', $filters['start_date_to']);
    }

    private function _unfulfill_case_sql()
    {
        return '
            LOWER(status) LIKE "%unfulfill%"
            OR LOWER(status) LIKE "%cancel%"
            OR LOWER(status) LIKE "%not support%"
            OR LOWER(status) LIKE "%standby%"
            OR LOWER(status) = "belum jalan"
            OR LOWER(status) = "off"
        ';
    }

    // ── Semua vendor + reliability score ──
    public function get_all_vendor_performance($filters = [])
    {
        $unfulfill_sql = $this->_unfulfill_case_sql();

        $this->db->select("
            vendor,
            COUNT(*) as total_trip,
            COUNT(DISTINCT customer) as total_customer,
            COUNT(DISTINCT CONCAT(origin, '->', dest_1)) as total_rute,
            COUNT(DISTINCT sheet_type) as total_sheet,
            SUM(trip_cost_to_vendor) as total_cost,
            SUM(margin) as total_margin_terkait,
            SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) as total_unfulfill,
            ROUND(SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as unfulfill_pct,
            MAX(start_date) as last_trip,
            MIN(start_date) as first_trip
        ", false);
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('vendor !=', '');
        $this->db->where('UPPER(vendor) !=', 'OWN UNIT'); // unit internal sudah ada modul sendiri
        if (!empty($filters['search']))
            $this->db->like('vendor', $filters['search']);
        $this->_apply_filters($filters);
        $this->db->group_by('vendor');
        $this->db->order_by('total_trip', 'DESC');

        $rows = $this->db->get()->result();

        foreach ($rows as &$r) {
            $r->reliability_score = $this->calc_reliability((float) $r->unfulfill_pct);
            $r->reliability_label = $this->reliability_label($r->reliability_score);
        }

        if (!empty($filters['reliability'])) {
            $rows = array_values(array_filter($rows, fn($r) => $r->reliability_label === $filters['reliability']));
        }

        return $rows;
    }

    // ── Summary cards ──
    public function get_performance_summary($filters = [])
    {
        $all = $this->get_all_vendor_performance($filters);

        $summary = [
            'total' => count($all),
            'andal' => 0,
            'cukup' => 0,
            'bermasalah' => 0,
            'total_trip' => 0,
            'total_cost' => 0,
        ];

        foreach ($all as $r) {
            $summary[$r->reliability_label]++;
            $summary['total_trip'] += $r->total_trip;
            $summary['total_cost'] += $r->total_cost;
        }

        return $summary;
    }

    // ── Scorecard 1 vendor ──
    public function get_vendor_scorecard($vendor, $sheet_type = '')
    {
        $unfulfill_sql = $this->_unfulfill_case_sql();

        $this->db->select("
            vendor,
            COUNT(*) as total_trip,
            COUNT(DISTINCT customer) as total_customer,
            COUNT(DISTINCT CONCAT(origin, '->', dest_1)) as total_rute,
            SUM(trip_cost_to_vendor) as total_cost,
            SUM(margin) as total_margin_terkait,
            AVG(margin) as avg_margin_per_trip,
            SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) as total_unfulfill,
            ROUND(SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as unfulfill_pct,
            MAX(start_date) as last_trip,
            MIN(start_date) as first_trip
        ", false);
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('vendor', $vendor);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);

        $row = $this->db->get()->row();
        if (!$row || !$row->total_trip)
            return null;

        $row->reliability_score = $this->calc_reliability((float) $row->unfulfill_pct);
        $row->reliability_label = $this->reliability_label($row->reliability_score);

        return $row;
    }

    // ── Trend jumlah trip & unfulfill per bulan ──
    public function get_trend($vendor, $sheet_type = '')
    {
        $unfulfill_sql = $this->_unfulfill_case_sql();
        $bulan_order = "FIELD(UPPER(periode),
            'JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI',
            'JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER')";

        $this->db->select("
            periode,
            sheet_type,
            COUNT(*) as total_trip,
            SUM(trip_cost_to_vendor) as total_cost,
            SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) as total_unfulfill,
            ROUND(SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as unfulfill_pct
        ", false);
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('vendor', $vendor);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_by('periode, sheet_type');
        $this->db->order_by($bulan_order);
        return $this->db->get()->result();
    }

    // ── Breakdown customer yang dilayani vendor ini ──
    public function get_customer_breakdown($vendor, $sheet_type = '')
    {
        $unfulfill_sql = $this->_unfulfill_case_sql();

        $this->db->select("
            customer,
            COUNT(*) as total_trip,
            SUM(trip_cost_to_vendor) as total_cost,
            SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) as total_unfulfill
        ", false);
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('vendor', $vendor);
        $this->db->where('customer !=', '');
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_by('customer');
        $this->db->order_by('total_trip', 'DESC');
        $this->db->limit(20);
        return $this->db->get()->result();
    }

    // ── Breakdown rute yang dilayani vendor ini ──
    public function get_rute_breakdown($vendor, $sheet_type = '')
    {
        $unfulfill_sql = $this->_unfulfill_case_sql();

        $this->db->select("
            origin, dest_1, sheet_type,
            COUNT(*) as total_trip,
            SUM(trip_cost_to_vendor) as total_cost,
            SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) as total_unfulfill
        ", false);
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('vendor', $vendor);
        $this->db->where('origin !=', '');
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_by('origin, dest_1, sheet_type');
        $this->db->order_by('total_trip', 'DESC');
        $this->db->limit(20);
        return $this->db->get()->result();
    }

    // ── Riwayat trip terbaru ──
    public function get_recent_trips($vendor, $sheet_type = '', $date_from = '', $date_to = '', $limit = 100)
    {
        $this->db->select('
            id, sheet_type, start_date, customer, origin, dest_1, dest_2,
            truck_type, nopol, driver, status, trip_cost_to_vendor, margin
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('vendor', $vendor);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        if (!empty($date_from))
            $this->db->where('start_date >=', $date_from);
        if (!empty($date_to))
            $this->db->where('start_date <=', $date_to);
        $this->db->order_by('start_date', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    // ════════════════════════════════════════════════════════════
    // DROPDOWN LISTS
    // ════════════════════════════════════════════════════════════
    public function get_sheet_types()
    {
        return $this->db->query("SELECT DISTINCT sheet_type FROM tb_monitoring_shipment ORDER BY sheet_type")->result();
    }

    public function get_sheet_types_for_vendor($vendor)
    {
        return $this->db->query(
            "SELECT DISTINCT sheet_type FROM tb_monitoring_shipment WHERE vendor = ? ORDER BY sheet_type",
            [$vendor]
        )->result();
    }
}