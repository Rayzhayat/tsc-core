<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model: M_driver_violations
 * Purpose: Manage driver violations and penalties
 */
class M_driver_violations extends CI_Model
{
    private $table = 'driver_violations';
    
    // ========================================
    // GET ALL VIOLATIONS
    // ========================================
    
    public function get_all($filters = [])
    {
        $this->db->select('
            driver_violations.*,
            drivers.nama_driver,
            drivers.no_hp,
            drivers.rating
        ');
        $this->db->from($this->table);
        $this->db->join('drivers', 'driver_violations.driver_id = drivers.id', 'left');
        
        // Apply filters
        if (!empty($filters['driver_id'])) {
            $this->db->where('driver_violations.driver_id', $filters['driver_id']);
        }
        
        if (!empty($filters['violation_type'])) {
            $this->db->where('driver_violations.violation_type', $filters['violation_type']);
        }
        
        if (!empty($filters['status'])) {
            $this->db->where('driver_violations.status', $filters['status']);
        }
        
        if (!empty($filters['date_from'])) {
            $this->db->where('driver_violations.violation_date >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $this->db->where('driver_violations.violation_date <=', $filters['date_to']);
        }
        
        if (!empty($filters['keyword'])) {
            $this->db->group_start();
            $this->db->like('drivers.nama_driver', $filters['keyword']);
            $this->db->or_like('driver_violations.description', $filters['keyword']);
            $this->db->group_end();
        }
        
        $this->db->order_by('driver_violations.violation_date', 'DESC');
        $this->db->order_by('driver_violations.id', 'DESC');
        
        return $this->db->get()->result();
    }
    
    // ========================================
    // GET BY ID
    // ========================================
    
    public function get_by_id($id)
    {
        $this->db->select('
            driver_violations.*,
            drivers.nama_driver,
            drivers.no_hp,
            drivers.no_sim,
            drivers.rating
        ');
        $this->db->from($this->table);
        $this->db->join('drivers', 'driver_violations.driver_id = drivers.id', 'left');
        $this->db->where('driver_violations.id', $id);
        
        return $this->db->get()->row();
    }
    
    // ========================================
    // GET BY DRIVER
    // ========================================
    
    public function get_by_driver($driver_id, $status = null)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('driver_id', $driver_id);
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        $this->db->order_by('violation_date', 'DESC');
        
        return $this->db->get()->result();
    }
    
    // ========================================
    // GET SUMMARY
    // ========================================
    
    public function get_summary($filters = [])
    {
        // Total violations
        $this->db->from($this->table);
        if (!empty($filters['driver_id'])) {
            $this->db->where('driver_id', $filters['driver_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('violation_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('violation_date <=', $filters['date_to']);
        }
        $total = $this->db->count_all_results();
        
        // By status
        $this->db->select('status, COUNT(*) as count, SUM(penalty_amount) as total_penalty');
        $this->db->from($this->table);
        if (!empty($filters['driver_id'])) {
            $this->db->where('driver_id', $filters['driver_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('violation_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('violation_date <=', $filters['date_to']);
        }
        $this->db->group_by('status');
        $by_status = $this->db->get()->result_array();
        
        $status_summary = [
            'pending' => ['count' => 0, 'total_penalty' => 0],
            'paid' => ['count' => 0, 'total_penalty' => 0],
            'waived' => ['count' => 0, 'total_penalty' => 0]
        ];
        
        foreach ($by_status as $item) {
            $status_summary[$item['status']] = [
                'count' => $item['count'],
                'total_penalty' => $item['total_penalty']
            ];
        }
        
        // By type
        $this->db->select('violation_type, COUNT(*) as count');
        $this->db->from($this->table);
        if (!empty($filters['driver_id'])) {
            $this->db->where('driver_id', $filters['driver_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('violation_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('violation_date <=', $filters['date_to']);
        }
        $this->db->group_by('violation_type');
        $by_type = $this->db->get()->result_array();
        
        return [
            'total' => $total,
            'by_status' => $status_summary,
            'by_type' => $by_type
        ];
    }
    
    // ========================================
    // CREATE VIOLATION
    // ========================================
    
    public function create($data)
    {
        // Set defaults
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Insert
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        
        return false;
    }
    
    // ========================================
    // UPDATE VIOLATION
    // ========================================
    
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
    
    // ========================================
    // DELETE VIOLATION
    // ========================================
    
    public function delete($id)
    {
        // Only allow delete if pending
        $violation = $this->get_by_id($id);
        
        if (!$violation || $violation->status !== 'pending') {
            return false;
        }
        
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
    
    // ========================================
    // UPDATE STATUS
    // ========================================
    
    public function update_status($id, $status, $resolved_by = null)
    {
        $data = [
            'status' => $status
        ];
        
        if ($status !== 'pending') {
            $data['resolved_date'] = date('Y-m-d');
            $data['resolved_by'] = $resolved_by;
        } else {
            $data['resolved_date'] = null;
            $data['resolved_by'] = null;
        }
        
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
    
    // ========================================
    // GET VIOLATION COUNT BY DRIVER
    // ========================================
    
    public function get_violation_count_by_driver($driver_id, $status = null)
    {
        $this->db->from($this->table);
        $this->db->where('driver_id', $driver_id);
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        return $this->db->count_all_results();
    }
    
    // ========================================
    // GET TOTAL PENALTY BY DRIVER
    // ========================================
    
    public function get_total_penalty_by_driver($driver_id, $status = null)
    {
        $this->db->select('SUM(penalty_amount) as total');
        $this->db->from($this->table);
        $this->db->where('driver_id', $driver_id);
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        $result = $this->db->get()->row();
        
        return $result ? $result->total : 0;
    }
    
    // ========================================
    // GET DRIVER PERFORMANCE DATA
    // ========================================
    
    public function get_driver_performance_data($driver_id, $date_from = null, $date_to = null)
    {
        // Get driver
        $this->db->select('
            drivers.*,
            COUNT(DISTINCT tb_surat_jalan.id) as total_trips
        ');
        $this->db->from('drivers');
        $this->db->join('tb_surat_jalan', 'drivers.id = tb_surat_jalan.driver_id', 'left');
        $this->db->where('drivers.id', $driver_id);
        
        if ($date_from) {
            $this->db->where('tb_surat_jalan.tanggal >=', $date_from);
        }
        if ($date_to) {
            $this->db->where('tb_surat_jalan.tanggal <=', $date_to);
        }
        
        $this->db->group_by('drivers.id');
        $driver = $this->db->get()->row();
        
        if (!$driver) {
            return null;
        }
        
        // Get violations
        $violations = $this->get_by_driver($driver_id);
        $violation_count = count($violations);
        $pending_violations = array_filter($violations, function($v) { return $v->status === 'pending'; });
        $pending_count = count($pending_violations);
        
        // Get total penalties
        $total_penalties = $this->get_total_penalty_by_driver($driver_id);
        $pending_penalties = $this->get_total_penalty_by_driver($driver_id, 'pending');
        
        // Calculate performance score
        // Base: 5.0, minus 0.5 per pending violation
        $performance_score = max(1.0, 5.0 - ($pending_count * 0.5));
        
        return [
            'driver' => $driver,
            'violations' => $violations,
            'violation_count' => $violation_count,
            'pending_violations' => $pending_count,
            'total_penalties' => $total_penalties,
            'pending_penalties' => $pending_penalties,
            'performance_score' => round($performance_score, 1)
        ];
    }
    
    // ========================================
    // GET TOP VIOLATORS
    // ========================================
    
    public function get_top_violators($limit = 10, $date_from = null, $date_to = null)
    {
        $this->db->select('
            drivers.id,
            drivers.nama_driver,
            drivers.no_hp,
            drivers.rating,
            COUNT(driver_violations.id) as violation_count,
            SUM(driver_violations.penalty_amount) as total_penalty
        ');
        $this->db->from('drivers');
        $this->db->join('driver_violations', 'drivers.id = driver_violations.driver_id', 'inner');
        
        if ($date_from) {
            $this->db->where('driver_violations.violation_date >=', $date_from);
        }
        if ($date_to) {
            $this->db->where('driver_violations.violation_date <=', $date_to);
        }
        
        $this->db->group_by('drivers.id');
        $this->db->order_by('violation_count', 'DESC');
        $this->db->order_by('total_penalty', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }
    
    // ========================================
    // GET MONTHLY VIOLATIONS
    // ========================================
    
    public function get_monthly_violations($months = 6)
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $month_name = date('M Y', strtotime("-$i months"));
            
            $this->db->where("DATE_FORMAT(violation_date, '%Y-%m') =", $month);
            $count = $this->db->count_all_results($this->table);
            
            $data[] = [
                'month' => $month_name,
                'count' => $count
            ];
        }
        
        return $data;
    }
}