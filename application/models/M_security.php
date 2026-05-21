<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_security extends CI_Model {

    // ==================== STATISTICS ====================
    
    public function count_total_visitors() {
        return $this->db->select('COUNT(DISTINCT ip_address) as total')
                        ->from('tb_access_log')
                        ->get()->row()->total ?? 0;
    }

    public function count_today_visitors() {
        return $this->db->select('COUNT(DISTINCT ip_address) as total')
                        ->from('tb_access_log')
                        ->where('DATE(timestamp)', date('Y-m-d'))
                        ->get()->row()->total ?? 0;
    }

    public function count_blocked_ips() {
        return $this->db->where('is_active', 1)
                        ->count_all_results('tb_ip_blacklist');
    }

    public function count_threats_today() {
        return $this->db->where('DATE(detected_at)', date('Y-m-d'))
                        ->count_all_results('tb_security_threats');
    }

    public function count_failed_logins_today() {
        return $this->db->where('DATE(timestamp)', date('Y-m-d'))
                        ->where('status', 'failed')
                        ->count_all_results('tb_login_attempts');
    }

    // ==================== ACCESS LOGS ====================
    
    public function log_access($data) {
        return $this->db->insert('tb_access_log', $data);
    }

    public function get_access_logs($filters = []) {
        $this->db->select('*')
                 ->from('tb_access_log')
                 ->order_by('timestamp', 'DESC')
                 ->limit(1000);
        
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(timestamp) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(timestamp) <=', $filters['date_to']);
        }
        if (!empty($filters['ip'])) {
            $this->db->like('ip_address', $filters['ip']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        
        return $this->db->get()->result();
    }

    public function get_top_visitors($limit = 10) {
        return $this->db->select('ip_address, COUNT(*) as visits, MAX(timestamp) as last_visit')
                        ->from('tb_access_log')
                        ->where('DATE(timestamp) >=', date('Y-m-d', strtotime('-7 days')))
                        ->group_by('ip_address')
                        ->order_by('visits', 'DESC')
                        ->limit($limit)
                        ->get()->result();
    }

    // ==================== THREATS ====================
    
    public function log_threat($data) {
        return $this->db->insert('tb_security_threats', $data);
    }

    public function get_recent_threats($limit = 10) {
        return $this->db->select('*')
                        ->from('tb_security_threats')
                        ->order_by('detected_at', 'DESC')
                        ->limit($limit)
                        ->get()->result();
    }

    public function get_all_threats() {
        return $this->db->select('*')
                        ->from('tb_security_threats')
                        ->order_by('detected_at', 'DESC')
                        ->get()->result();
    }

    public function get_suspicious_ips($limit = 10) {
        return $this->db->select('ip_address, COUNT(*) as threat_count, MAX(detected_at) as last_threat')
                        ->from('tb_security_threats')
                        ->where('DATE(detected_at) >=', date('Y-m-d', strtotime('-7 days')))
                        ->group_by('ip_address')
                        ->order_by('threat_count', 'DESC')
                        ->limit($limit)
                        ->get()->result();
    }

    // ==================== LOGIN ATTEMPTS ====================
    
    public function log_login_attempt($data) {
        return $this->db->insert('tb_login_attempts', $data);
    }

    public function get_failed_logins($limit = 10) {
        return $this->db->select('*')
                        ->from('tb_login_attempts')
                        ->where('status', 'failed')
                        ->order_by('timestamp', 'DESC')
                        ->limit($limit)
                        ->get()->result();
    }

    public function count_failed_attempts($ip, $minutes = 15) {
        return $this->db->where('ip_address', $ip)
                        ->where('status', 'failed')
                        ->where('timestamp >', date('Y-m-d H:i:s', strtotime("-{$minutes} minutes")))
                        ->count_all_results('tb_login_attempts');
    }

    // ==================== IP BLACKLIST ====================
    
    public function block_ip($ip, $reason = 'Manual block') {
        // Check if already blocked
        $existing = $this->db->where('ip_address', $ip)
                             ->get('tb_ip_blacklist')->row();
        
        if ($existing) {
            // Update
            return $this->db->where('ip_address', $ip)
                            ->update('tb_ip_blacklist', [
                                'reason' => $reason,
                                'is_active' => 1,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
        } else {
            // Insert
            return $this->db->insert('tb_ip_blacklist', [
                'ip_address' => $ip,
                'reason' => $reason,
                'blocked_at' => date('Y-m-d H:i:s'),
                'is_active' => 1
            ]);
        }
    }

    public function unblock_ip($ip) {
        return $this->db->where('ip_address', $ip)
                        ->update('tb_ip_blacklist', [
                            'is_active' => 0,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
    }

    public function is_ip_blocked($ip) {
        $result = $this->db->where('ip_address', $ip)
                           ->where('is_active', 1)
                           ->get('tb_ip_blacklist')->row();
        
        return $result ? true : false;
    }

    public function get_blocked_ips() {
        return $this->db->select('*')
                        ->from('tb_ip_blacklist')
                        ->where('is_active', 1)
                        ->order_by('blocked_at', 'DESC')
                        ->get()->result();
    }

    // ==================== GEOGRAPHIC ====================
    
    public function get_visitor_by_country() {
        return $this->db->select('country, COUNT(DISTINCT ip_address) as visitors')
                        ->from('tb_access_log')
                        ->where('DATE(timestamp) >=', date('Y-m-d', strtotime('-30 days')))
                        ->where('country IS NOT NULL')
                        ->group_by('country')
                        ->order_by('visitors', 'DESC')
                        ->limit(10)
                        ->get()->result();
    }

    // ==================== CLEANUP ====================
    
    public function clear_old_logs($days = 30) {
        $date = date('Y-m-d', strtotime("-{$days} days"));
        
        $this->db->where('DATE(timestamp) <', $date)->delete('tb_access_log');
        $affected1 = $this->db->affected_rows();
        
        $this->db->where('DATE(detected_at) <', $date)->delete('tb_security_threats');
        $affected2 = $this->db->affected_rows();
        
        $this->db->where('DATE(timestamp) <', $date)->delete('tb_login_attempts');
        $affected3 = $this->db->affected_rows();
        
        return $affected1 + $affected2 + $affected3;
    }
}