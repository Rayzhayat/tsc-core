<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_data_quality extends CI_Model
{
    // ════════════════════════════════════════════════════════════
    // QUALITY SCORE LOGIC
    // Quality Score = 100 - rata-rata(% revenue kosong, % margin kosong,
    //                                  % vendor kosong, % customer kosong,
    //                                  % status kosong, % payment status kosong)
    // Baik  : score >= 90
    // Cukup : score >= 70
    // Buruk : score <  70
    // ════════════════════════════════════════════════════════════
    public function calc_quality_score($pct_revenue, $pct_margin, $pct_vendor, $pct_customer, $pct_status, $pct_payment)
    {
        $avg_bolong = ($pct_revenue + $pct_margin + $pct_vendor + $pct_customer + $pct_status + $pct_payment) / 6;
        return round(100 - $avg_bolong, 1);
    }

    public function quality_label($score)
    {
        if ($score >= 90)
            return 'baik';
        if ($score >= 70)
            return 'cukup';
        return 'buruk';
    }

    // ── Helper filter (dipakai semua query di model ini) ──
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

    // ── Base SELECT yang dipakai berulang (per group_by apapun) ──
    private function _select_quality_metrics()
    {
        $this->db->select('
            COUNT(*) as total_rows,

            SUM(CASE WHEN trip_cost_from_user = 0 OR trip_cost_from_user IS NULL THEN 1 ELSE 0 END) as bolong_revenue,
            ROUND(SUM(CASE WHEN trip_cost_from_user = 0 OR trip_cost_from_user IS NULL THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as pct_revenue,

            SUM(CASE WHEN margin = 0 OR margin IS NULL THEN 1 ELSE 0 END) as bolong_margin,
            ROUND(SUM(CASE WHEN margin = 0 OR margin IS NULL THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as pct_margin,

            SUM(CASE WHEN vendor = "" OR vendor IS NULL THEN 1 ELSE 0 END) as bolong_vendor,
            ROUND(SUM(CASE WHEN vendor = "" OR vendor IS NULL THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as pct_vendor,

            SUM(CASE WHEN customer = "" OR customer IS NULL THEN 1 ELSE 0 END) as bolong_customer,
            ROUND(SUM(CASE WHEN customer = "" OR customer IS NULL THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as pct_customer,

            SUM(CASE WHEN status = "" OR status IS NULL THEN 1 ELSE 0 END) as bolong_status,
            ROUND(SUM(CASE WHEN status = "" OR status IS NULL THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as pct_status,

            SUM(CASE WHEN status_payment_user = "" OR status_payment_user IS NULL THEN 1 ELSE 0 END) as bolong_payment,
            ROUND(SUM(CASE WHEN status_payment_user = "" OR status_payment_user IS NULL THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0) * 100, 1) as pct_payment
        ');
    }

    // ── Trend kualitas data per Periode + Sheet Type (buat chart) ──
    public function get_quality_trend($filters = [])
    {
        $this->_select_quality_metrics();
        $this->db->select('periode, sheet_type', false);
        $this->db->from('tb_monitoring_shipment');
        $this->_apply_filters($filters);
        $this->db->group_by('periode, sheet_type');
        $this->db->order_by('sheet_type');
        $this->db->order_by("FIELD(UPPER(periode),
            'JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI',
            'JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER'
        )");
        $rows = $this->db->get()->result();

        foreach ($rows as &$r) {
            $r->quality_score = $this->calc_quality_score(
                (float) $r->pct_revenue,
                (float) $r->pct_margin,
                (float) $r->pct_vendor,
                (float) $r->pct_customer,
                (float) $r->pct_status,
                (float) $r->pct_payment
            );
            $r->quality_label = $this->quality_label($r->quality_score);
        }

        return $rows;
    }

    // ── Breakdown kualitas per Sheet Type (agregat semua periode dalam filter) ──
    public function get_quality_by_sheet($filters = [])
    {
        $this->_select_quality_metrics();
        $this->db->select('sheet_type', false);
        $this->db->from('tb_monitoring_shipment');
        $this->_apply_filters($filters);
        $this->db->group_by('sheet_type');
        $this->db->order_by('total_rows', 'DESC');
        $rows = $this->db->get()->result();

        foreach ($rows as &$r) {
            $r->quality_score = $this->calc_quality_score(
                (float) $r->pct_revenue,
                (float) $r->pct_margin,
                (float) $r->pct_vendor,
                (float) $r->pct_customer,
                (float) $r->pct_status,
                (float) $r->pct_payment
            );
            $r->quality_label = $this->quality_label($r->quality_score);
        }

        return $rows;
    }

    // ── Summary keseluruhan (header cards) ──
    public function get_quality_summary($filters = [])
    {
        $this->_select_quality_metrics();
        $this->db->from('tb_monitoring_shipment');
        $this->_apply_filters($filters);
        $row = $this->db->get()->row();

        if (!$row || !$row->total_rows) {
            return (object) [
                'total_rows' => 0, 'pct_revenue' => 0, 'pct_margin' => 0, 'pct_vendor' => 0,
                'pct_customer' => 0, 'pct_status' => 0, 'pct_payment' => 0,
                'quality_score' => 100, 'quality_label' => 'baik',
            ];
        }

        $row->quality_score = $this->calc_quality_score(
            (float) $row->pct_revenue,
            (float) $row->pct_margin,
            (float) $row->pct_vendor,
            (float) $row->pct_customer,
            (float) $row->pct_status,
            (float) $row->pct_payment
        );
        $row->quality_label = $this->quality_label($row->quality_score);

        return $row;
    }

    // ── Daftar baris bermasalah (buat tabel detail + export) ──
    // $issue: revenue | margin | vendor | customer | status | payment | all
    public function get_problem_rows($filters = [], $issue = 'all', $limit = 300)
    {
        $this->db->select('id, sheet_type, periode, start_date, customer, origin, dest_1,
            vendor, status, trip_cost_from_user, margin, status_payment_user');
        $this->db->from('tb_monitoring_shipment');
        $this->_apply_filters($filters);

        switch ($issue) {
            case 'revenue':
                $this->db->group_start();
                $this->db->where('trip_cost_from_user', 0);
                $this->db->or_where('trip_cost_from_user IS NULL');
                $this->db->group_end();
                break;
            case 'margin':
                $this->db->group_start();
                $this->db->where('margin', 0);
                $this->db->or_where('margin IS NULL');
                $this->db->group_end();
                break;
            case 'vendor':
                $this->db->group_start();
                $this->db->where('vendor', '');
                $this->db->or_where('vendor IS NULL');
                $this->db->group_end();
                break;
            case 'customer':
                $this->db->group_start();
                $this->db->where('customer', '');
                $this->db->or_where('customer IS NULL');
                $this->db->group_end();
                break;
            case 'status':
                $this->db->group_start();
                $this->db->where('status', '');
                $this->db->or_where('status IS NULL');
                $this->db->group_end();
                break;
            case 'payment':
                $this->db->group_start();
                $this->db->where('status_payment_user', '');
                $this->db->or_where('status_payment_user IS NULL');
                $this->db->group_end();
                break;
            default: // 'all' → baris yang punya minimal 1 masalah
                $this->db->group_start();
                $this->db->where('trip_cost_from_user', 0);
                $this->db->or_where('trip_cost_from_user IS NULL');
                $this->db->or_where('margin', 0);
                $this->db->or_where('margin IS NULL');
                $this->db->or_where('vendor', '');
                $this->db->or_where('vendor IS NULL');
                $this->db->or_where('customer', '');
                $this->db->or_where('customer IS NULL');
                $this->db->or_where('status', '');
                $this->db->or_where('status IS NULL');
                $this->db->or_where('status_payment_user', '');
                $this->db->or_where('status_payment_user IS NULL');
                $this->db->group_end();
        }

        $this->db->order_by('start_date', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function count_problem_rows($filters = [], $issue = 'all')
    {
        // Pakai query yang sama tapi tanpa limit & cuma count
        $this->db->select('id');
        $this->db->from('tb_monitoring_shipment');
        $this->_apply_filters($filters);

        switch ($issue) {
            case 'revenue':
                $this->db->group_start()->where('trip_cost_from_user', 0)->or_where('trip_cost_from_user IS NULL')->group_end();
                break;
            case 'margin':
                $this->db->group_start()->where('margin', 0)->or_where('margin IS NULL')->group_end();
                break;
            case 'vendor':
                $this->db->group_start()->where('vendor', '')->or_where('vendor IS NULL')->group_end();
                break;
            case 'customer':
                $this->db->group_start()->where('customer', '')->or_where('customer IS NULL')->group_end();
                break;
            case 'status':
                $this->db->group_start()->where('status', '')->or_where('status IS NULL')->group_end();
                break;
            case 'payment':
                $this->db->group_start()->where('status_payment_user', '')->or_where('status_payment_user IS NULL')->group_end();
                break;
            default:
                $this->db->group_start()
                    ->where('trip_cost_from_user', 0)->or_where('trip_cost_from_user IS NULL')
                    ->or_where('margin', 0)->or_where('margin IS NULL')
                    ->or_where('vendor', '')->or_where('vendor IS NULL')
                    ->or_where('customer', '')->or_where('customer IS NULL')
                    ->or_where('status', '')->or_where('status IS NULL')
                    ->or_where('status_payment_user', '')->or_where('status_payment_user IS NULL')
                    ->group_end();
        }

        return $this->db->count_all_results();
    }

    // ════════════════════════════════════════════════════════════
    // DROPDOWN LISTS (reuse pattern dari M_analytics)
    // ════════════════════════════════════════════════════════════
    public function get_sheet_type_list()
    {
        return $this->db->query("SELECT DISTINCT sheet_type FROM tb_monitoring_shipment ORDER BY sheet_type")->result();
    }

    public function get_periode_list($sheet_type = '')
    {
        $where = "WHERE periode != ''";
        if (!empty($sheet_type)) {
            $where .= " AND sheet_type = " . $this->db->escape($sheet_type);
        }

        $rows = $this->db->query("SELECT DISTINCT periode FROM tb_monitoring_shipment $where")->result_array();

        $bulan_order = [
            'JANUARI' => 1, 'FEBRUARI' => 2, 'MARET' => 3, 'APRIL' => 4, 'MEI' => 5, 'JUNI' => 6,
            'JULI' => 7, 'AGUSTUS' => 8, 'SEPTEMBER' => 9, 'OKTOBER' => 10, 'NOVEMBER' => 11, 'DESEMBER' => 12,
        ];

        $set = [];
        foreach ($rows as $row) {
            $set[strtoupper(trim($row['periode']))] = true;
        }

        $list = array_keys($set);
        usort($list, fn($a, $b) => ($bulan_order[$a] ?? 99) - ($bulan_order[$b] ?? 99));

        return array_map(fn($p) => (object) ['periode' => $p], $list);
    }
}