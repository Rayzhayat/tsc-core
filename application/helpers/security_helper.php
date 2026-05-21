<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Security Helper Functions
 * Save as: application/helpers/security_helper.php
 */

if (!function_exists('get_ip_details')) {
    function get_ip_details($ip = null) {
        $CI =& get_instance();
        
        if ($ip === null) {
            $ip = $CI->input->ip_address();
        }
        
        $url = "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,query";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if (!$response) {
            return [
                'success' => false,
                'error' => 'Failed to fetch IP info'
            ];
        }
        
        $data = json_decode($response, true);
        
        if (!$data || $data['status'] === 'fail') {
            return [
                'success' => false,
                'error' => $data['message'] ?? 'Unknown error'
            ];
        }
        
        return [
            'success' => true,
            'ip' => $data['query'],
            'location' => [
                'city' => $data['city'] ?? 'Unknown',
                'region' => $data['regionName'] ?? 'Unknown',
                'country' => $data['country'] ?? 'Unknown',
                'country_code' => $data['countryCode'] ?? '',
                'full' => sprintf('%s, %s', 
                    $data['city'] ?? 'Unknown',
                    $data['country'] ?? 'Unknown'
                )
            ],
            'network' => [
                'isp' => $data['isp'] ?? 'Unknown',
                'org' => $data['org'] ?? 'Unknown',
                'as' => $data['as'] ?? 'Unknown'
            ],
            'coordinates' => [
                'lat' => $data['lat'] ?? 0,
                'lon' => $data['lon'] ?? 0
            ]
        ];
    }
}

if (!function_exists('get_ip_details_cached')) {
    function get_ip_details_cached($ip) {
        $CI =& get_instance();
        $CI->load->driver('cache', ['adapter' => 'file']);
        
        $cache_key = 'ip_info_' . str_replace('.', '_', $ip);
        $cached = $CI->cache->get($cache_key);
        
        if ($cached !== FALSE) {
            return $cached;
        }
        
        $ip_info = get_ip_details($ip);
        
        // Cache for 24 hours
        $CI->cache->save($cache_key, $ip_info, 86400);
        
        return $ip_info;
    }
}

if (!function_exists('detect_threat_level')) {
    function detect_threat_level($threat_count) {
        if ($threat_count >= 10) {
            return ['level' => 'critical', 'color' => 'danger', 'icon' => '🔴'];
        } elseif ($threat_count >= 5) {
            return ['level' => 'high', 'color' => 'warning', 'icon' => '🟠'];
        } elseif ($threat_count >= 1) {
            return ['level' => 'medium', 'color' => 'info', 'icon' => '🟡'];
        } else {
            return ['level' => 'safe', 'color' => 'success', 'icon' => '🟢'];
        }
    }
}

if (!function_exists('format_threat_badge')) {
    function format_threat_badge($severity) {
        $badges = [
            'high' => '<span class="badge badge-danger">HIGH</span>',
            'medium' => '<span class="badge badge-warning">MEDIUM</span>',
            'low' => '<span class="badge badge-info">LOW</span>'
        ];
        
        return $badges[$severity] ?? '<span class="badge badge-secondary">UNKNOWN</span>';
    }
}

if (!function_exists('time_ago')) {
    function time_ago($timestamp) {
        $time = strtotime($timestamp);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return $diff . ' detik yang lalu';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' menit yang lalu';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' jam yang lalu';
        } else {
            return floor($diff / 86400) . ' hari yang lalu';
        }
    }
}