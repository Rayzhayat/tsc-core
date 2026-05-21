<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_tms_alerts extends CI_Model
{
    private $table = 'tms_alerts';
    
    // ========================================
    // GET ALL ALERTS
    // ========================================
    
    public function get_all_alerts($status = null, $priority = null)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        if ($priority) {
            $this->db->where('priority', $priority);
        }
        
        $this->db->order_by('priority', 'DESC'); // critical first
        $this->db->order_by('alert_date', 'ASC');
        
        return $this->db->get()->result();
    }
    
    // ========================================
    // GET ALERTS BY TYPE
    // ========================================
    
    public function get_alerts_by_type($type, $status = 'pending')
    {
        $this->db->where('alert_type', $type);
        $this->db->where('status', $status);
        $this->db->order_by('priority', 'DESC');
        $this->db->order_by('alert_date', 'ASC');
        
        return $this->db->get($this->table)->result();
    }
    
    // ========================================
    // GET ALERT BY ID
    // ========================================
    
    public function get_alert_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }
    
    // ========================================
    // GET ALERTS COUNT BY STATUS
    // ========================================
    
    public function get_alerts_count($status = 'pending')
    {
        $this->db->where('status', $status);
        return $this->db->count_all_results($this->table);
    }
    
    // ========================================
    // GET ALERTS COUNT BY PRIORITY
    // ========================================
    
    public function get_alerts_count_by_priority()
    {
        $priorities = ['critical', 'high', 'medium', 'low'];
        $counts = [];
        
        foreach ($priorities as $priority) {
            $this->db->where('status', 'pending');
            $this->db->where('priority', $priority);
            $counts[$priority] = $this->db->count_all_results($this->table);
        }
        
        return $counts;
    }
    
    // ========================================
    // CREATE ALERT
    // ========================================
    
    public function create_alert($data)
    {
        // Check if similar alert already exists (avoid duplicates)
        $existing = $this->db->where([
            'alert_type' => $data['alert_type'],
            'reference_type' => $data['reference_type'],
            'reference_id' => $data['reference_id'],
            'status' => 'pending'
        ])->get($this->table)->row();
        
        if ($existing) {
            // Update existing alert instead of creating duplicate
            return $this->db->where('id', $existing->id)->update($this->table, [
                'title' => $data['title'],
                'message' => $data['message'],
                'priority' => $data['priority'],
                'alert_date' => $data['alert_date'],
                'expired_date' => $data['expired_date'] ?? null
            ]);
        }
        
        // Create new alert
        return $this->db->insert($this->table, $data);
    }
    
    // ========================================
    // ACKNOWLEDGE ALERT
    // ========================================
    
    public function acknowledge_alert($id, $user)
    {
        $data = [
            'status' => 'acknowledged',
            'acknowledged_by' => $user,
            'acknowledged_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->where('id', $id)->update($this->table, $data);
    }
    
    // ========================================
    // RESOLVE ALERT
    // ========================================
    
    public function resolve_alert($id)
    {
        return $this->db->where('id', $id)->update($this->table, ['status' => 'resolved']);
    }
    
    // ========================================
    // DELETE ALERT
    // ========================================
    
    public function delete_alert($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }
    
    // ========================================
    // DELETE OLD RESOLVED ALERTS (Cleanup)
    // ========================================
    
    public function delete_old_resolved_alerts($days = 30)
    {
        $date = date('Y-m-d', strtotime("-$days days"));
        
        $this->db->where('status', 'resolved');
        $this->db->where('acknowledged_at <', $date);
        
        return $this->db->delete($this->table);
    }
    
    // ========================================
    // GET ALERT DETAILS WITH REFERENCE DATA
    // ========================================
    
    public function get_alert_with_details($id)
    {
        $alert = $this->get_alert_by_id($id);
        
        if (!$alert) return null;
        
        // Get reference data based on type
        if ($alert->reference_type == 'unit') {
            $alert->reference_data = $this->db->get_where('units', ['id' => $alert->reference_id])->row();
        } elseif ($alert->reference_type == 'driver') {
            $alert->reference_data = $this->db->get_where('drivers', ['id' => $alert->reference_id])->row();
        }
        
        return $alert;
    }
    
    // ========================================
    // BULK ACKNOWLEDGE
    // ========================================
    
    public function bulk_acknowledge($ids, $user)
    {
        if (empty($ids)) return false;
        
        $data = [
            'status' => 'acknowledged',
            'acknowledged_by' => $user,
            'acknowledged_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where_in('id', $ids);
        return $this->db->update($this->table, $data);
    }
    
    // ========================================
    // BULK RESOLVE
    // ========================================
    
    public function bulk_resolve($ids)
    {
        if (empty($ids)) return false;
        
        $this->db->where_in('id', $ids);
        return $this->db->update($this->table, ['status' => 'resolved']);
    }
    
    // ========================================
    // BULK DELETE
    // ========================================
    
    public function bulk_delete($ids)
    {
        if (empty($ids)) return false;
        
        $this->db->where_in('id', $ids);
        return $this->db->delete($this->table);
    }
}