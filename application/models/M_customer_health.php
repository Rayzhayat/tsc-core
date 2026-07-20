<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_customer_health extends CI_Model
{
    // ════════════════════════════════════════════════════════════
    // HEALTH SCORING LOGIC
    // Sehat          : margin% > 10  AND unfulfill% < 10  AND pending_pct < 20
    // Perlu Perhatian: 1 metrik bermasalah
    // Kritis         : margin negatif OR 2+ metrik bermasalah
    // ════════════════════════════════════════════════════════════
    public function calc_health($margin_pct, $unfulfill_pct, $pending_real_pct)
    {
        $issues = 0;
        if ($margin_pct < 0)
            return 'kritis';          // langsung kritis kalau margin negatif
        if ($margin_pct <= 10)
            $issues++;
        if ($unfulfill_pct >= 10)
            $issues++;
        if ($pending_real_pct >= 20)
            $issues++;

        if ($issues === 0)
            return 'sehat';
        if ($issues === 1)
            return 'perlu_perhatian';
        return 'kritis';
    }

    // ── Semua customer dengan health score ──
    public function get_all_customer_health($filters = [])
    {
        $this->db->select('
        customer,
        sheet_type,
        COUNT(*) as total_shipment,
        SUM(trip_cost_from_user) as total_revenue,
        SUM(margin) as total_margin,
        ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user), 0) * 100, 2) as margin_pct,
        SUM(CASE
            WHEN LOWER(status) LIKE "%unfulfill%"
              OR LOWER(status) LIKE "%cancel%"
              OR LOWER(status) LIKE "%not support%"
              OR LOWER(status) LIKE "%standby%"
              OR LOWER(status) = "belum jalan"
              OR LOWER(status) = "off"
            THEN 1 ELSE 0 END) as total_unfulfill,
        ROUND(
            SUM(CASE
                WHEN LOWER(status) LIKE "%unfulfill%"
                  OR LOWER(status) LIKE "%cancel%"
                  OR LOWER(status) LIKE "%not support%"
                  OR LOWER(status) LIKE "%standby%"
                  OR LOWER(status) = "belum jalan"
                  OR LOWER(status) = "off"
                THEN 1 ELSE 0 END)
            / NULLIF(COUNT(*), 0) * 100, 1
        ) as unfulfill_pct,

        SUM(CASE
            WHEN status_payment_user != ""
             AND (LOWER(status_payment_user) LIKE "%waiting%"
                  OR LOWER(status_payment_user) LIKE "%pending%")
            THEN 1 ELSE 0 END) as pending_real,
        ROUND(
            SUM(CASE
                WHEN status_payment_user != ""
                 AND (LOWER(status_payment_user) LIKE "%waiting%"
                      OR LOWER(status_payment_user) LIKE "%pending%")
                THEN 1 ELSE 0 END)
            / NULLIF(COUNT(*), 0) * 100, 1
        ) as pending_real_pct,

        SUM(CASE WHEN status_payment_user = "" THEN 1 ELSE 0 END) as belum_diisi,
        ROUND(
            SUM(CASE WHEN status_payment_user = "" THEN 1 ELSE 0 END)
            / NULLIF(COUNT(*), 0) * 100, 1
        ) as belum_diisi_pct,

        -- tetap simpan total pending lama (real + belum diisi) buat referensi/export kalau perlu
        SUM(CASE
            WHEN LOWER(status_payment_user) LIKE "%waiting%"
              OR LOWER(status_payment_user) LIKE "%pending%"
              OR status_payment_user = ""
            THEN 1 ELSE 0 END) as pending_payment,
        ROUND(
            SUM(CASE
                WHEN LOWER(status_payment_user) LIKE "%waiting%"
                  OR LOWER(status_payment_user) LIKE "%pending%"
                  OR status_payment_user = ""
                THEN 1 ELSE 0 END)
            / NULLIF(COUNT(*), 0) * 100, 1
        ) as pending_pct,

        MAX(start_date) as last_shipment
    ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('customer !=', '');

        if (!empty($filters['sheet_type']))
            $this->db->where('sheet_type', $filters['sheet_type']);
        if (!empty($filters['search']))
            $this->db->like('customer', $filters['search']);

        $this->db->group_by('customer, sheet_type');
        $this->db->order_by('total_revenue', 'DESC');

        $rows = $this->db->get()->result();

        foreach ($rows as &$r) {
            $r->health_status = $this->calc_health(
                (float) $r->margin_pct,
                (float) $r->unfulfill_pct,
                (float) $r->pending_real_pct
            );
        }

        if (!empty($filters['health'])) {
            $rows = array_values(array_filter($rows, fn($r) => $r->health_status === $filters['health']));
        }

        return $rows;
    }

    // ── Summary counts untuk header cards ──
    public function get_health_summary($filters = [])
    {
        $all = $this->get_all_customer_health($filters);

        $summary = [
            'total' => count($all),
            'sehat' => 0,
            'perlu_perhatian' => 0,
            'kritis' => 0,
            'total_revenue' => 0,
            'total_margin' => 0,
        ];

        foreach ($all as $r) {
            $summary[$r->health_status]++;
            $summary['total_revenue'] += $r->total_revenue;
            $summary['total_margin'] += $r->total_margin;
        }

        return $summary;
    }

    // ── Scorecard untuk 1 customer (aggregate semua sheet atau 1 sheet) ──
    public function get_customer_scorecard($customer, $sheet_type = '')
    {
        $this->db->select('
        customer,
        COUNT(*) as total_shipment,
        SUM(trip_cost_from_user) as total_revenue,
        SUM(margin) as total_margin,
        ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user), 0) * 100, 2) as margin_pct,
        AVG(margin) as avg_margin_per_trip,
        SUM(CASE
            WHEN LOWER(status) LIKE "%unfulfill%"
              OR LOWER(status) LIKE "%cancel%"
              OR LOWER(status) LIKE "%not support%"
              OR LOWER(status) LIKE "%standby%"
              OR LOWER(status) = "belum jalan"
              OR LOWER(status) = "off"
            THEN 1 ELSE 0 END) as total_unfulfill,
        ROUND(
            SUM(CASE
                WHEN LOWER(status) LIKE "%unfulfill%"
                  OR LOWER(status) LIKE "%cancel%"
                  OR LOWER(status) LIKE "%not support%"
                  OR LOWER(status) LIKE "%standby%"
                  OR LOWER(status) = "belum jalan"
                  OR LOWER(status) = "off"
                THEN 1 ELSE 0 END)
            / NULLIF(COUNT(*), 0) * 100, 1
        ) as unfulfill_pct,

        SUM(CASE
            WHEN status_payment_user != ""
             AND (LOWER(status_payment_user) LIKE "%waiting%"
                  OR LOWER(status_payment_user) LIKE "%pending%")
            THEN 1 ELSE 0 END) as pending_real,
        ROUND(
            SUM(CASE
                WHEN status_payment_user != ""
                 AND (LOWER(status_payment_user) LIKE "%waiting%"
                      OR LOWER(status_payment_user) LIKE "%pending%")
                THEN 1 ELSE 0 END)
            / NULLIF(COUNT(*), 0) * 100, 1
        ) as pending_real_pct,

        SUM(CASE WHEN status_payment_user = "" THEN 1 ELSE 0 END) as belum_diisi,
        ROUND(
            SUM(CASE WHEN status_payment_user = "" THEN 1 ELSE 0 END)
            / NULLIF(COUNT(*), 0) * 100, 1
        ) as belum_diisi_pct,

        SUM(CASE
            WHEN LOWER(status_payment_user) LIKE "%waiting%"
              OR LOWER(status_payment_user) LIKE "%pending%"
              OR status_payment_user = ""
            THEN 1 ELSE 0 END) as pending_payment,
        ROUND(
            SUM(CASE
                WHEN LOWER(status_payment_user) LIKE "%waiting%"
                  OR LOWER(status_payment_user) LIKE "%pending%"
                  OR status_payment_user = ""
                THEN 1 ELSE 0 END)
            / NULLIF(COUNT(*), 0) * 100, 1
        ) as pending_pct,

        SUM(CASE WHEN LOWER(status_payment_user) LIKE "%paid%" OR LOWER(status_payment_user) LIKE "%lunas%" THEN 1 ELSE 0 END) as paid_count,
        MAX(start_date) as last_shipment,
        MIN(start_date) as first_shipment,
        COUNT(DISTINCT vendor) as total_vendor_used,
        COUNT(DISTINCT CONCAT(origin, "->", dest_1)) as total_rute
    ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('customer', $customer);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);

        $row = $this->db->get()->row();
        if (!$row || !$row->total_shipment)
            return null;

        $row->health_status = $this->calc_health(
            (float) $row->margin_pct,
            (float) $row->unfulfill_pct,
            (float) $row->pending_real_pct
        );

        return $row;
    }

    // ── Trend margin per bulan untuk chart ──
    public function get_margin_trend($customer, $sheet_type = '')
    {
        $bulan_order = "FIELD(UPPER(periode),
            'JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI',
            'JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER')";

        $this->db->select('
            periode,
            sheet_type,
            COUNT(*) as total_shipment,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(margin) as total_margin,
            ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user), 0) * 100, 2) as margin_pct
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('customer', $customer);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_by('periode, sheet_type');
        $this->db->order_by($bulan_order);
        return $this->db->get()->result();
    }

    // ── Breakdown per rute (origin → dest) ──
    public function get_rute_breakdown($customer, $sheet_type = '')
    {
        $this->db->select('
            origin, dest_1, sheet_type,
            COUNT(*) as total_trip,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(margin) as total_margin,
            ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user), 0) * 100, 2) as margin_pct,
            SUM(CASE
                WHEN LOWER(status) LIKE "%unfulfill%"
                  OR LOWER(status) LIKE "%cancel%"
                  OR LOWER(status) LIKE "%not support%"
                  OR LOWER(status) LIKE "%standby%"
                  OR LOWER(status) = "belum jalan"
                  OR LOWER(status) = "off"
                THEN 1 ELSE 0 END) as unfulfill_count
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('customer', $customer);
        $this->db->where('origin !=', '');
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_by('origin, dest_1, sheet_type');
        $this->db->order_by('total_revenue', 'DESC');
        $this->db->limit(20);
        return $this->db->get()->result();
    }

    // ── Shipment history dengan filter tanggal ──
    public function get_recent_shipments($customer, $sheet_type = '', $date_from = '', $date_to = '', $limit = 100)
    {
        $this->db->select('
            id, sheet_type, start_date, origin, dest_1, dest_2,
            truck_type, vendor, driver, status,
            trip_cost_from_user, margin, margin,
            status_payment_user, no_invoice_user
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('customer', $customer);
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

    // ── Payment stats breakdown ──
    public function get_payment_stats($customer, $sheet_type = '')
    {
        $this->db->select('
            status_payment_user,
            COUNT(*) as total,
            SUM(trip_cost_from_user) as total_revenue
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('customer', $customer);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_by('status_payment_user');
        $this->db->order_by('total', 'DESC');
        return $this->db->get()->result();
    }

    // ── Sheet types yang ada untuk 1 customer ──
    public function get_sheet_types_for_customer($customer)
    {
        return $this->db->query(
            "SELECT DISTINCT sheet_type FROM tb_monitoring_shipment WHERE customer = ? ORDER BY sheet_type",
            [$customer]
        )->result();
    }

    // ── Semua sheet types ──
    public function get_sheet_types()
    {
        return $this->db->query("SELECT DISTINCT sheet_type FROM tb_monitoring_shipment ORDER BY sheet_type")->result();
    }
}