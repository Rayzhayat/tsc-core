<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Permission Helper Library
 * Centralized permission management for the application
 */
class Permission_lib
{
    protected $CI;

    // User level constants
    const SUPERADMIN = 'superadmin';
    const ADMIN_OPERATIONAL = 'admin_operational';
    const OPERATIONAL_STAFF = 'operational_staff';
    const FINANCE_STAFF = 'finance_staff';
    const ADMIN_DOCUMENT = 'admin_document';

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    /**
     * Get current user level
     */
    public function get_user_level()
    {
        return $this->CI->session->userdata('login')['user_level'] ?? null;
    }

    /**
     * Get current username
     */
    public function get_username()
    {
        return $this->CI->session->userdata('login')['username'] ?? null;
    }

    /**
     * Check if user has specific level
     */
    public function is($level)
    {
        return $this->get_user_level() === $level;
    }

    /**
     * Check if user is superadmin
     */
    public function is_superadmin()
    {
        return $this->is(self::SUPERADMIN);
    }

    /**
     * Check if user is admin operational
     */
    public function is_admin_operational()
    {
        return $this->is(self::ADMIN_OPERATIONAL);
    }

    /**
     * Check if user is operational staff
     */
    public function is_operational_staff()
    {
        return $this->is(self::OPERATIONAL_STAFF);
    }

    /**
     * Check if user has one of the specified levels
     */
    public function has_level($levels)
    {
        if (!is_array($levels)) {
            $levels = [$levels];
        }

        return in_array($this->get_user_level(), $levels);
    }
    
    // ========================================
    // SPECIFIC PERMISSIONS
    // ========================================

    /**
     * Can approve Purchase Orders
     */
    public function can_approve_po()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL]);
    }

    /**
     * Can create Purchase Orders
     */
    public function can_create_po()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL, self::OPERATIONAL_STAFF]);
    }

    /**
     * Can cancel Purchase Orders
     */
    public function can_cancel_po()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL]);
    }

    /**
     * Can manage users (ONLY SUPERADMIN)
     */
    public function can_manage_users()
    {
        return $this->is_superadmin();
    }

    /**
     * Can edit superadmin users (ONLY SUPERADMIN)
     */
    public function can_edit_superadmin()
    {
        return $this->is_superadmin();
    }

    /**
     * Can manage TMS alerts
     */
    public function can_manage_alerts()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL]);
    }

    /**
     * Can add driver violations
     */
    public function can_add_violations()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL]);
    }

    /**
     * Can approve incentives
     */
    public function can_approve_incentive()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL]);
    }

    /**
     * Can manage drivers
     */
    public function can_manage_drivers()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL]);
    }

    /**
     * Can manage units/vehicles
     */
    public function can_manage_units()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL]);
    }

    /**
     * Can delete violation
     */
    public function can_delete_violation()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL]);
    }
    
    /**
     * Can delete surat jalan
     */
    public function can_delete_surat_jalan()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL]);
    }

    /**
     * Can export reports
     */
    public function can_export_reports()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL]);
    }

    /**
     * Can view full TMS dashboard
     */
    public function can_view_tms_dashboard()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL, self::OPERATIONAL_STAFF]);
    }

    /**
     * Can add payments
     */
    public function can_add_payment()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL, self::FINANCE_STAFF]);
    }

    /**
     * Can delete purchase order
     */
    public function can_delete_po()
    {
        return $this->has_level([self::SUPERADMIN, self::ADMIN_OPERATIONAL]);
    }
    
    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Require specific permission or redirect
     */
    public function require_permission($callback_method, $redirect_url = null, $error_message = null)
    {
        if (!method_exists($this, $callback_method)) {
            show_error('Invalid permission check method', 500);
        }

        if (!$this->$callback_method()) {
            $error_message = $error_message ?: 'Anda tidak memiliki akses untuk melakukan aksi ini!';
            $this->CI->session->set_flashdata('error', $error_message);

            if ($redirect_url) {
                redirect($redirect_url);
            } else {
                show_error($error_message, 403);
            }
        }

        return true;
    }

    /**
     * Check permission and return boolean
     */
    public function check($callback_method)
    {
        if (!method_exists($this, $callback_method)) {
            return false;
        }

        return $this->$callback_method();
    }

    /**
     * Get user level label
     */
    public function get_level_label($level = null)
    {
        $level = $level ?: $this->get_user_level();

        $labels = [
            self::SUPERADMIN => 'Super Administrator',
            self::ADMIN_OPERATIONAL => 'Admin Operational',
            self::OPERATIONAL_STAFF => 'Operational Staff',
            self::FINANCE_STAFF => 'Finance Staff',
            self::ADMIN_DOCUMENT => 'Admin Document'
        ];

        return $labels[$level] ?? $level;
    }

    /**
     * Get user level badge class
     */
    public function get_level_badge_class($level = null)
    {
        $level = $level ?: $this->get_user_level();

        $badges = [
            self::SUPERADMIN => 'badge-danger',
            self::ADMIN_OPERATIONAL => 'badge-primary',
            self::OPERATIONAL_STAFF => 'badge-info',
            self::FINANCE_STAFF => 'badge-success',
            self::ADMIN_DOCUMENT => 'badge-warning'
        ];

        return $badges[$level] ?? 'badge-secondary';
    }
}
