<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Session Helper
 * Parse device/browser/OS dari User-Agent
 * Ambil geolocation dari ip-api.com
 */

// ─── DEVICE DETECTION ────────────────────────────────────────────────────────

function detect_device_info($user_agent = null)
{
    $ua = $user_agent ?: ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');

    return [
        'user_agent' => $ua,
        'device_type' => _detect_device_type($ua),
        'os' => _detect_os($ua),
        'browser' => _detect_browser($ua),
    ];
}

function _detect_device_type($ua)
{
    $ua = strtolower($ua);
    if (preg_match('/tablet|ipad|kindle|playbook|silk|(android(?!.*mobile))/i', $ua))
        return 'tablet';
    if (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile|wpdesktop/i', $ua))
        return 'mobile';
    return 'desktop';
}

function _detect_os($ua)
{
    $map = [
        '/windows nt 11/i' => 'Windows 11',
        '/windows nt 10/i' => 'Windows 10',
        '/windows nt 6\.3/i' => 'Windows 8.1',
        '/windows nt 6\.2/i' => 'Windows 8',
        '/windows nt 6\.1/i' => 'Windows 7',
        '/windows/i' => 'Windows',
        '/macintosh|mac os x/i' => 'macOS',
        '/iphone/i' => 'iOS (iPhone)',
        '/ipad/i' => 'iOS (iPad)',
        '/android/i' => 'Android',
        '/linux/i' => 'Linux',
        '/ubuntu/i' => 'Ubuntu',
        '/chromeos/i' => 'Chrome OS',
    ];
    foreach ($map as $pattern => $name) {
        if (preg_match($pattern, $ua))
            return $name;
    }
    return 'Unknown OS';
}

function _detect_browser($ua)
{
    // Order matters — check specific browsers before generic ones
    $map = [
        '/edg\//i' => 'Microsoft Edge',
        '/opr\//i' => 'Opera',
        '/opera/i' => 'Opera',
        '/samsungbrowser/i' => 'Samsung Browser',
        '/ucbrowser/i' => 'UC Browser',
        '/yabrowser/i' => 'Yandex Browser',
        '/firefox/i' => 'Firefox',
        '/chrome/i' => 'Chrome',
        '/safari/i' => 'Safari',
        '/msie|trident/i' => 'Internet Explorer',
    ];
    foreach ($map as $pattern => $name) {
        if (preg_match($pattern, $ua))
            return $name;
    }
    return 'Unknown Browser';
}

// ─── GEOLOCATION ─────────────────────────────────────────────────────────────

function get_ip_geolocation($ip)
{
    // Skip private/local IPs
    if (_is_private_ip($ip)) {
        return [
            'country' => 'Local Network',
            'city' => 'Localhost',
            'country_code' => 'LO',
            'isp' => 'Internal',
        ];
    }

    // Cache key (simpan di CI cache jika memungkinkan)
    $cache_file = APPPATH . 'cache/geo_' . md5($ip) . '.json';
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 86400) {
        $cached = json_decode(file_get_contents($cache_file), true);
        if ($cached)
            return $cached;
    }

    // Hit ip-api.com (free, 45 req/menit tanpa API key)
    $url = "http://ip-api.com/json/{$ip}?fields=status,country,countryCode,city,isp";
    $ctx = stream_context_create(['http' => ['timeout' => 3]]);

    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw)
        return _empty_geo();

    $data = json_decode($raw, true);
    if (!$data || $data['status'] !== 'success')
        return _empty_geo();

    $result = [
        'country' => $data['country'] ?? null,
        'city' => $data['city'] ?? null,
        'country_code' => strtolower($data['countryCode'] ?? ''),
        'isp' => $data['isp'] ?? null,
    ];

    // Cache hasil
    @file_put_contents($cache_file, json_encode($result));

    return $result;
}

function _is_private_ip($ip)
{
    return in_array($ip, ['127.0.0.1', '::1'])
        || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
}

function _empty_geo()
{
    return ['country' => null, 'city' => null, 'country_code' => null, 'isp' => null];
}

// ─── DEVICE ICON HELPER ───────────────────────────────────────────────────────

function device_icon($type)
{
    switch ($type) {
        case 'mobile':
            return 'fa-mobile-alt';
        case 'tablet':
            return 'fa-tablet-alt';
        case 'desktop':
            return 'fa-desktop';
        default:
            return 'fa-question-circle';
    }
}

function browser_icon($browser)
{
    $b = strtolower($browser);
    if (strpos($b, 'chrome') !== false)
        return 'fa-chrome';
    if (strpos($b, 'firefox') !== false)
        return 'fa-firefox';
    if (strpos($b, 'safari') !== false)
        return 'fa-safari';
    if (strpos($b, 'edge') !== false)
        return 'fa-edge';
    if (strpos($b, 'opera') !== false)
        return 'fa-opera';
    return 'fa-globe';
}

function time_ago($datetime)
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)
        return 'Baru saja';
    if ($diff < 3600)
        return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400)
        return floor($diff / 3600) . ' jam lalu';
    return floor($diff / 86400) . ' hari lalu';
}