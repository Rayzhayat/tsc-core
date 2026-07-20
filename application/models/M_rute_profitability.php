<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_rute_profitability extends CI_Model
{
    // ════════════════════════════════════════════════════════════
    // KLASIFIKASI RUTE
    // Profitable : margin% > 10
    // Tipis      : margin% 0 - 10
    // Rugi       : margin% < 0
    // ════════════════════════════════════════════════════════════
    public function classify_rute($margin_pct)
    {
        if ($margin_pct < 0)
            return 'rugi';
        if ($margin_pct <= 10)
            return 'tipis';
        return 'profitable';
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

    // ── Semua rute + klasifikasi ──
    public function get_all_rute($filters = [])
    {
        $unfulfill_sql = $this->_unfulfill_case_sql();

        $this->db->select("
            origin, dest_1,
            COUNT(*) as total_trip,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(margin) as total_margin,
            ROUND(AVG(margin), 0) as avg_margin,
            ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user),0) * 100, 2) as margin_pct,
            COUNT(DISTINCT vendor) as total_vendor,
            COUNT(DISTINCT customer) as total_customer,
            SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) as total_unfulfill,
            ROUND(SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as unfulfill_pct,
            MAX(start_date) as last_trip
        ", false);
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('origin !=', '');
        $this->db->where('dest_1 !=', '');
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('origin', $filters['search']);
            $this->db->or_like('dest_1', $filters['search']);
            $this->db->group_end();
        }
        $this->_apply_filters($filters);
        $this->db->group_by('origin, dest_1');
        $this->db->order_by('total_trip', 'DESC');

        $rows = $this->db->get()->result();

        foreach ($rows as &$r) {
            $r->status_rute = $this->classify_rute((float) $r->margin_pct);
        }

        if (!empty($filters['status_rute'])) {
            $rows = array_values(array_filter($rows, fn($r) => $r->status_rute === $filters['status_rute']));
        }

        return $rows;
    }

    // ── Query builder dipakai bareng buat data + count (server-side datatables) ──
    private function _build_rute_query($filters = [], $dt_search = '')
    {
        $unfulfill_sql = $this->_unfulfill_case_sql();

        $this->db->select("
        origin, dest_1,
        COUNT(*) as total_trip,
        SUM(trip_cost_from_user) as total_revenue,
        SUM(margin) as total_margin,
        ROUND(AVG(margin), 0) as avg_margin,
        ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user),0) * 100, 2) as margin_pct,
        COUNT(DISTINCT vendor) as total_vendor,
        COUNT(DISTINCT customer) as total_customer,
        SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) as total_unfulfill,
        ROUND(SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as unfulfill_pct,
        MAX(start_date) as last_trip
    ", false);
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('origin !=', '');
        $this->db->where('dest_1 !=', '');
        $this->_apply_filters($filters);

        $search_term = $dt_search !== '' ? $dt_search : ($filters['search'] ?? '');
        if (!empty($search_term)) {
            $this->db->group_start();
            $this->db->like('origin', $search_term);
            $this->db->or_like('dest_1', $search_term);
            $this->db->group_end();
        }

        $this->db->group_by('origin, dest_1');

        if (!empty($filters['status_rute'])) {
            switch ($filters['status_rute']) {
                case 'profitable':
                    $this->db->having('margin_pct >', 10);
                    break;
                case 'tipis':
                    $this->db->having('margin_pct >=', 0);
                    $this->db->having('margin_pct <=', 10);
                    break;
                case 'rugi':
                    $this->db->having('margin_pct <', 0);
                    break;
            }
        }
    }

    // ── Data untuk 1 page DataTables ──
    public function get_rute_datatables($filters = [], $start = 0, $length = 10, $order_col = 'total_trip', $order_dir = 'DESC', $dt_search = '')
    {
        $this->_build_rute_query($filters, $dt_search);
        $this->db->order_by($order_col, $order_dir);
        if ($length > 0) {
            $this->db->limit($length, $start);
        }
        $rows = $this->db->get()->result();

        foreach ($rows as &$r) {
            $r->status_rute = $this->classify_rute((float) $r->margin_pct);
        }
        return $rows;
    }

    // ── Total rute (belum kena search/status, cuma filter bar sheet_type/periode/date) ──
    public function count_rute_total($filters = [])
    {
        $base_filters = [
            'sheet_type' => $filters['sheet_type'] ?? '',
            'periode' => $filters['periode'] ?? '',
            'start_date_from' => $filters['start_date_from'] ?? '',
            'start_date_to' => $filters['start_date_to'] ?? '',
        ];
        $this->_build_rute_query($base_filters, '');
        $sql = $this->db->get_compiled_select();
        $row = $this->db->query("SELECT COUNT(*) as cnt FROM ($sql) as t")->row();
        return $row ? (int) $row->cnt : 0;
    }

    // ── Total rute setelah semua filter + search DataTables ──
    public function count_rute_filtered($filters = [], $dt_search = '')
    {
        $this->_build_rute_query($filters, $dt_search);
        $sql = $this->db->get_compiled_select();
        $row = $this->db->query("SELECT COUNT(*) as cnt FROM ($sql) as t")->row();
        return $row ? (int) $row->cnt : 0;
    }

    // ── Summary cards ──
    public function get_rute_summary($filters = [])
    {
        $all = $this->get_all_rute($filters);

        $summary = [
            'total' => count($all),
            'profitable' => 0,
            'tipis' => 0,
            'rugi' => 0,
            'total_revenue' => 0,
            'total_margin' => 0,
        ];

        foreach ($all as $r) {
            $summary[$r->status_rute]++;
            $summary['total_revenue'] += $r->total_revenue;
            $summary['total_margin'] += $r->total_margin;
        }

        return $summary;
    }

    // ── Scorecard 1 rute (aggregate semua sheet/vendor di rute ini) ──
    public function get_rute_scorecard($origin, $dest_1, $sheet_type = '')
    {
        $unfulfill_sql = $this->_unfulfill_case_sql();

        $this->db->select("
            origin, dest_1,
            COUNT(*) as total_trip,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(trip_cost_to_vendor) as total_cost,
            SUM(margin) as total_margin,
            ROUND(AVG(margin), 0) as avg_margin,
            ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user),0) * 100, 2) as margin_pct,
            COUNT(DISTINCT vendor) as total_vendor,
            COUNT(DISTINCT customer) as total_customer,
            COUNT(DISTINCT truck_type) as total_truck_type,
            SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) as total_unfulfill,
            ROUND(SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as unfulfill_pct,
            MAX(start_date) as last_trip,
            MIN(start_date) as first_trip
        ", false);
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('origin', $origin);
        $this->db->where('dest_1', $dest_1);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);

        $row = $this->db->get()->row();
        if (!$row || !$row->total_trip)
            return null;

        $row->status_rute = $this->classify_rute((float) $row->margin_pct);
        return $row;
    }

    // ── Breakdown per vendor di rute ini (kunci: vendor mana untung/rugi) ──
    public function get_vendor_breakdown($origin, $dest_1, $sheet_type = '')
    {
        $unfulfill_sql = $this->_unfulfill_case_sql();

        $this->db->select("
            vendor,
            COUNT(*) as total_trip,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(trip_cost_to_vendor) as total_cost,
            SUM(margin) as total_margin,
            ROUND(AVG(margin), 0) as avg_margin,
            ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user),0) * 100, 2) as margin_pct,
            SUM(CASE WHEN $unfulfill_sql THEN 1 ELSE 0 END) as total_unfulfill
        ", false);
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('origin', $origin);
        $this->db->where('dest_1', $dest_1);
        $this->db->where('vendor !=', '');
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_by('vendor');
        $this->db->order_by('total_margin', 'DESC');
        return $this->db->get()->result();
    }

    // ── Breakdown per customer di rute ini ──
    public function get_customer_breakdown($origin, $dest_1, $sheet_type = '')
    {
        $this->db->select('
            customer,
            COUNT(*) as total_trip,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(margin) as total_margin,
            ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user),0) * 100, 2) as margin_pct
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('origin', $origin);
        $this->db->where('dest_1', $dest_1);
        $this->db->where('customer !=', '');
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_by('customer');
        $this->db->order_by('total_trip', 'DESC');
        return $this->db->get()->result();
    }

    // ── Breakdown per truck type di rute ini ──
    public function get_truck_breakdown($origin, $dest_1, $sheet_type = '')
    {
        $this->db->select('
            truck_type,
            COUNT(*) as total_trip,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(margin) as total_margin,
            ROUND(AVG(margin), 0) as avg_margin
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('origin', $origin);
        $this->db->where('dest_1', $dest_1);
        $this->db->where('truck_type !=', '');
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_by('truck_type');
        $this->db->order_by('total_trip', 'DESC');
        return $this->db->get()->result();
    }

    // ── Trend margin per bulan di rute ini ──
    public function get_trend($origin, $dest_1, $sheet_type = '')
    {
        $bulan_order = "FIELD(UPPER(periode),
            'JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI',
            'JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER')";

        $this->db->select('
            periode, sheet_type,
            COUNT(*) as total_trip,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(margin) as total_margin,
            ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user),0) * 100, 2) as margin_pct
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('origin', $origin);
        $this->db->where('dest_1', $dest_1);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_by('periode, sheet_type');
        $this->db->order_by($bulan_order);
        return $this->db->get()->result();
    }

    // ── Riwayat trip di rute ini ──
    public function get_recent_trips($origin, $dest_1, $sheet_type = '', $date_from = '', $date_to = '', $limit = 100)
    {
        $this->db->select('
            id, sheet_type, start_date, customer, vendor, truck_type, nopol, driver,
            status, trip_cost_from_user, trip_cost_to_vendor, margin
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('origin', $origin);
        $this->db->where('dest_1', $dest_1);
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

    public function get_sheet_types_for_rute($origin, $dest_1)
    {
        return $this->db->query(
            "SELECT DISTINCT sheet_type FROM tb_monitoring_shipment WHERE origin = ? AND dest_1 = ? ORDER BY sheet_type",
            [$origin, $dest_1]
        )->result();
    }
}