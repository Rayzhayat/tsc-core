<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Authentication & Authorization Helper Functions
 */

/**
 * Check if user has specific role(s)
 * @param string|array $role_or_roles Single role or array of roles
 * @return bool
 */
if (!function_exists('has_role')) {
    function has_role($role_or_roles)
    {
        $CI =& get_instance();

        // Get user's roles from session
        $user_roles = $CI->session->userdata('login')['user_level'] ?? '';

        if (empty($user_roles)) {
            return false;
        }

        // Convert to array (support comma-separated)
        $user_roles_array = explode(',', $user_roles);
        $user_roles_array = array_map('trim', $user_roles_array);

        // Check single role or array of roles
        if (is_array($role_or_roles)) {
            // User needs ANY of these roles
            foreach ($role_or_roles as $role) {
                if (in_array(trim($role), $user_roles_array)) {
                    return true;
                }
            }
            return false;
        } else {
            // Check single role
            return in_array(trim($role_or_roles), $user_roles_array);
        }
    }
}

/**
 * Check if user has ALL specified roles
 * @param array $roles
 * @return bool
 */
if (!function_exists('has_all_roles')) {
    function has_all_roles($roles)
    {
        $CI =& get_instance();

        $user_roles = $CI->session->userdata('login')['user_level'] ?? '';

        if (empty($user_roles)) {
            return false;
        }

        $user_roles_array = explode(',', $user_roles);
        $user_roles_array = array_map('trim', $user_roles_array);

        foreach ($roles as $role) {
            if (!in_array(trim($role), $user_roles_array)) {
                return false;
            }
        }

        return true;
    }
}

/**
 * Get user's primary role (first in list)
 * @return string|null
 */
if (!function_exists('get_primary_role')) {
    function get_primary_role()
    {
        $CI =& get_instance();

        $user_roles = $CI->session->userdata('login')['user_level'] ?? '';

        if (empty($user_roles)) {
            return null;
        }

        $roles_array = explode(',', $user_roles);
        return trim($roles_array[0]);
    }
}

/**
 * Get all user's roles as array
 * @return array
 */
if (!function_exists('get_user_roles')) {
    function get_user_roles()
    {
        $CI =& get_instance();

        $user_roles = $CI->session->userdata('login')['user_level'] ?? '';

        if (empty($user_roles)) {
            return [];
        }

        $roles_array = explode(',', $user_roles);
        return array_map('trim', $roles_array);
    }
}

/**
 * Check if user is superadmin
 * @return bool
 */
if (!function_exists('is_superadmin')) {
    function is_superadmin()
    {
        return has_role('superadmin');
    }
}

/**
 * Format role badge HTML
 * @param string $role
 * @return string
 */
if (!function_exists('format_role_badge')) {
    function format_role_badge($role)
    {
        $badges = [
            'superadmin' => '<span class="badge badge-danger"><i class="fas fa-crown"></i> Superadmin</span>',
            'admin_operational' => '<span class="badge badge-primary"><i class="fas fa-user-shield"></i> Admin Operational</span>',
            'operational_staff' => '<span class="badge badge-info"><i class="fas fa-tasks"></i> Operational Staff</span>',
            'finance_staff' => '<span class="badge badge-success"><i class="fas fa-dollar-sign"></i> Finance Staff</span>',
            'fleet_staff' => '<span class="badge badge-warning"><i class="fas fa-truck"></i> Fleet Staff</span>',
            'viewer' => '<span class="badge badge-secondary"><i class="fas fa-eye"></i> Viewer</span>',
            'admin_document' => '<span class="badge badge-dark"><i class="fas fa-file-alt"></i> Admin Document</span>',
        ];

        return $badges[strtolower(trim($role))] ?? '<span class="badge badge-light">' . htmlspecialchars($role) . '</span>';
    }
}

/**
 * Get role display name
 * @param string $role
 * @return string
 */
if (!function_exists('get_role_name')) {
    function get_role_name($role)
    {
        $names = [
            'superadmin' => 'Superadmin',
            'admin_operational' => 'Admin Operational',
            'operational_staff' => 'Operational Staff',
            'finance_staff' => 'Finance Staff',
            'fleet_staff' => 'Fleet Staff',
            'viewer' => 'Viewer / Manajemen',
            'admin_document' => 'Admin Document'
        ];

        return $names[strtolower(trim($role))] ?? ucfirst($role);
    }
}