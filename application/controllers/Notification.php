<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller: Notification
 * Purpose: Handle notification AJAX requests
 * 🔥 FIXED: Proper unread count logic
 */
class Notification extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Check login
        if (!$this->session->userdata('login')) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }

        $this->load->model('M_notification');
    }

    /**
     * Get notifications (AJAX)
     * Called by notification.js every 10 seconds
     */
    public function get_notifications()
    {
        $limit = $this->input->get('limit') ?: 10;
        $unread_only = $this->input->get('unread_only') == 'true';

        // Get current user
        $username = $this->session->userdata('login')['username'] ?? '';
        $user_level = $this->session->userdata('login')['user_level'] ?? '';

        // 🔥 FIXED - Get proper unread count
        $unread_count = $this->M_notification->get_unread_count($username, $user_level);

        // Get notifications
        $notifications = $this->M_notification->get_for_user($username, $user_level, $limit, $unread_only);

        echo json_encode([
            'success' => true,
            'unread_count' => (int) $unread_count,  // ✅ CORRECT COUNT!
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark notification as read (AJAX)
     */
    public function mark_as_read($id)
    {
        $result = $this->M_notification->mark_as_read($id);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Marked as read' : 'Failed'
        ]);
    }

    /**
     * Mark all as read (AJAX)
     */
    public function mark_all_as_read()
    {
        $username = $this->session->userdata('login')['username'] ?? '';
        $user_level = $this->session->userdata('login')['user_level'] ?? '';

        $result = $this->M_notification->mark_all_as_read($username, $user_level);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'All marked as read' : 'Failed'
        ]);
    }

    /**
     * Get unread count only (AJAX - faster)
     */
    public function get_unread_count()
    {
        $username = $this->session->userdata('login')['username'] ?? '';
        $user_level = $this->session->userdata('login')['user_level'] ?? '';

        $count = $this->M_notification->get_unread_count($username, $user_level);

        echo json_encode([
            'success' => true,
            'count' => (int) $count
        ]);
    }

    /**
     * Delete notification (AJAX)
     */
    public function delete($id)
    {
        $result = $this->M_notification->delete($id);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Deleted' : 'Failed'
        ]);
    }
}
