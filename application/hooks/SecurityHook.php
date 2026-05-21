<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Security Monitor Hook
 * Automatically detect and block threats
 * 
 * Enable in config/hooks.php:
 * $hook['post_controller_constructor'] = [
 *     'class' => 'SecurityHook',
 *     'function' => 'monitor',
 *     'filename' => 'SecurityHook.php',
 *     'filepath' => 'hooks'
 * ];
 */

class SecurityHook {
    
    private $CI;
    
    public function monitor() {
        $this->CI =& get_instance();
        $this->CI->load->model('M_security');
        $this->CI->load->helper('security_helper');
        
        // Get request info
        $ip = $this->CI->input->ip_address();
        $request_uri = $this->CI->input->server('REQUEST_URI');
        $method = $this->CI->input->server('REQUEST_METHOD');
        $user_agent = $this->CI->input->user_agent();
        
        // Check if IP is blocked
        if ($this->CI->M_security->is_ip_blocked($ip)) {
            $this->block_access('Your IP has been blocked due to suspicious activity');
            return;
        }
        
        // Check for threats
        $threat_detected = $this->detect_threats($request_uri, $ip);
        
        if ($threat_detected) {
            // Auto-block after 3 threats
            $recent_threats = $this->CI->db->where('ip_address', $ip)
                                          ->where('detected_at >', date('Y-m-d H:i:s', strtotime('-1 hour')))
                                          ->count_all_results('tb_security_threats');
            
            if ($recent_threats >= 3) {
                $this->CI->M_security->block_ip($ip, 'Auto-blocked: Multiple threats detected');
                $this->block_access('Access denied due to suspicious activity');
                return;
            }
        }
        
        // Log access (for non-static files)
        if (!preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|woff|woff2|ttf)$/i', $request_uri)) {
            $this->log_access($ip, $request_uri, $method, $user_agent);
        }
    }
    
    private function detect_threats($request_uri, $ip) {
        $threat_patterns = [
            // SQL Injection
            [
                'type' => 'SQL Injection',
                'pattern' => '/(union|select|insert|update|delete|drop|create|alter|exec|script|into outfile|load_file|benchmark)/i',
                'severity' => 'high'
            ],
            
            // XSS
            [
                'type' => 'XSS Attempt',
                'pattern' => '/<script|javascript:|onerror=|onload=|<iframe|eval\(|alert\(/i',
                'severity' => 'high'
            ],
            
            // Path Traversal
            [
                'type' => 'Path Traversal',
                'pattern' => '/\.\.\/|\.\.\\\/i',
                'severity' => 'medium'
            ],
            
            // Command Injection
            [
                'type' => 'Command Injection',
                'pattern' => '/;|\|\||&&|`|\$\(|>\||<\|/i',
                'severity' => 'high'
            ],
            
            // File Inclusion
            [
                'type' => 'File Inclusion',
                'pattern' => '/(etc\/passwd|boot\.ini|win\.ini|php:\/\/|file:\/\/|data:\/\/)/i',
                'severity' => 'high'
            ],
            
            // XXE
            [
                'type' => 'XXE Attempt',
                'pattern' => '/<!ENTITY|<!DOCTYPE|SYSTEM|PUBLIC/i',
                'severity' => 'medium'
            ]
        ];
        
        $threat_detected = false;
        
        foreach ($threat_patterns as $threat) {
            if (preg_match($threat['pattern'], $request_uri)) {
                // Log threat
                $this->CI->M_security->log_threat([
                    'ip_address' => $ip,
                    'threat_type' => $threat['type'],
                    'severity' => $threat['severity'],
                    'request_uri' => $request_uri,
                    'user_agent' => $this->CI->input->user_agent(),
                    'detected_at' => date('Y-m-d H:i:s')
                ]);
                
                $threat_detected = true;
                
                // Block immediately for high severity
                if ($threat['severity'] === 'high') {
                    $this->block_access('Malicious request detected: ' . $threat['type']);
                    return true;
                }
            }
        }
        
        return $threat_detected;
    }
    
    private function log_access($ip, $uri, $method, $user_agent) {
        // Get IP info (cached)
        static $ip_cache = [];
        
        if (!isset($ip_cache[$ip])) {
            $ip_info = get_ip_details_cached($ip);
            $ip_cache[$ip] = $ip_info;
        } else {
            $ip_info = $ip_cache[$ip];
        }
        
        // Get user info
        $user_id = null;
        $username = null;
        
        if ($this->CI->session->userdata('login')) {
            $user_id = $this->CI->session->userdata('login')['user_id'] ?? null;
            $username = $this->CI->session->userdata('login')['username'] ?? null;
        }
        
        $this->CI->M_security->log_access([
            'ip_address' => $ip,
            'user_id' => $user_id,
            'username' => $username,
            'page_url' => $uri,
            'method' => $method,
            'user_agent' => $user_agent,
            'country' => $ip_info['location']['country'] ?? null,
            'city' => $ip_info['location']['city'] ?? null,
            'isp' => $ip_info['network']['isp'] ?? null,
            'status' => 'success',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function block_access($message = 'Access Denied') {
        header('HTTP/1.1 403 Forbidden');
        
        // Simple HTML response
        echo '<!DOCTYPE html>
<html>
<head>
    <title>403 Forbidden</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        h1 { color: #e74a3b; margin-bottom: 20px; }
        p { color: #666; line-height: 1.6; }
        .icon { font-size: 64px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>Access Denied</h1>
        <p>' . htmlspecialchars($message) . '</p>
        <p style="font-size: 12px; color: #999; margin-top: 20px;">
            Your IP: ' . htmlspecialchars($this->CI->input->ip_address()) . '<br>
            Incident ID: ' . date('YmdHis') . rand(1000, 9999) . '
        </p>
    </div>
</body>
</html>';
        
        exit;
    }
}