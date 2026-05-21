<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_notification extends CI_Model {

    /**
     * Create notification
     */
    public function create($data) {
        $notification = [
            'user_target' => $data['user_target'] ?? null,
            'user_level_target' => $data['user_level_target'] ?? 'all',
            'title' => $data['title'],
            'message' => $data['message'],
            'url' => $data['url'] ?? null,
            'type' => $data['type'] ?? 'info',
            'category' => $data['category'] ?? 'other',
            'icon' => $data['icon'] ?? 'fa-bell',
            'ref_module' => $data['ref_module'] ?? null,
            'ref_id' => $data['ref_id'] ?? null,
            'created_by' => $data['created_by']
        ];
        
        return $this->db->insert('tb_notifications', $notification);
    }

    /**
     * Get notifications for specific user
     */
    public function get_for_user($username, $user_level, $limit = 10, $unread_only = false) {
        $this->db->select('*');
        $this->db->from('tb_notifications');
        
        // Filter by user or user level
        $this->db->group_start();
            $this->db->where('user_target', $username);
            $this->db->or_where('user_level_target', $user_level);
            $this->db->or_where('user_level_target', 'all');
        $this->db->group_end();
        
        if ($unread_only) {
            $this->db->where('is_read', 0);
        }
        
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }

    /**
     * Get unread count
     */
    public function get_unread_count($username, $user_level) {
        $this->db->from('tb_notifications');
        $this->db->where('is_read', 0);
        
        $this->db->group_start();
            $this->db->where('user_target', $username);
            $this->db->or_where('user_level_target', $user_level);
            $this->db->or_where('user_level_target', 'all');
        $this->db->group_end();
        
        return $this->db->count_all_results();
    }

    /**
     * Mark as read
     */
    public function mark_as_read($id) {
        return $this->db->update('tb_notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
    }

    /**
     * Mark all as read for user
     */
    public function mark_all_as_read($username, $user_level) {
        $this->db->where('is_read', 0);
        
        $this->db->group_start();
            $this->db->where('user_target', $username);
            $this->db->or_where('user_level_target', $user_level);
            $this->db->or_where('user_level_target', 'all');
        $this->db->group_end();
        
        return $this->db->update('tb_notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Delete notification
     */
    public function delete($id) {
        return $this->db->delete('tb_notifications', ['id' => $id]);
    }

    /**
     * Delete all read notifications for user
     */
    public function delete_all_read($username, $user_level) {
        $this->db->where('is_read', 1);
        
        $this->db->group_start();
            $this->db->where('user_target', $username);
            $this->db->or_where('user_level_target', $user_level);
            $this->db->or_where('user_level_target', 'all');
        $this->db->group_end();
        
        return $this->db->delete('tb_notifications');
    }

}