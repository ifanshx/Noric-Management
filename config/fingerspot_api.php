<?php
/**
 * Fingerspot SDK Online — API Client Library
 * Modul ini menyediakan fungsi wrapper untuk berkomunikasi dengan
 * Fingerspot Developer API secara aman dan terstruktur.
 * * @author Noric Management System
 * @version 1.2 (Fixed Auth & SSL)
 * @license MIT
 */

// Pastikan file konfigurasi Fingerspot tersedia
if (!defined('FINGERSPOT_API_URL')) {
    require_once __DIR__ . '/fingerspot.php';
}

/**
 * Kirim permintaan ke API Fingerspot menggunakan cURL
 */
function fingerspot_request($endpoint, $data, $timeout = 30) {
    $url = rtrim(FINGERSPOT_API_URL, '/') . '/' . ltrim($endpoint, '/');
    
    // 1. Validasi & Fix Token (Auto-add Bearer)
    $token = defined('FINGERSPOT_API_TOKEN') ? FINGERSPOT_API_TOKEN : '';
    if (empty($token) || strpos($token, 'MASUKKAN_TOKEN') !== false) {
        error_log("FINGERSPOT ERROR: Token API belum dikonfigurasi.");
        return false;
    }
    
    if (stripos($token, 'Bearer ') === false) {
        $token = 'Bearer ' . $token;
    }

    // Validasi Cloud ID
    if (!defined('FINGERSPOT_CLOUD_ID') || empty(FINGERSPOT_CLOUD_ID)) {
        error_log("FINGERSPOT ERROR: Cloud ID belum dikonfigurasi.");
        return false;
    }

    $headers = [
        'Authorization: ' . $token,
        'Content-Type: application/json',
        'User-Agent: Noric-Management/1.0'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data, JSON_UNESCAPED_SLASHES),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        // Fix SSL Issue (Penting untuk Localhost/Shared Hosting)
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_FAILONERROR    => false
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Error Handling
    if ($curl_error) {
        error_log("FINGERSPOT cURL ERROR ({$url}): {$curl_error}");
        return false;
    }

    if ($http_code !== 200) {
        error_log("FINGERSPOT HTTP ERROR ({$url}): Status {$http_code}, Response: " . substr($response, 0, 200));
        return false;
    }

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("FINGERSPOT JSON DECODE ERROR ({$url}): " . json_last_error_msg());
        return false;
    }

    return $decoded;
}

/**
 * Sinkronisasi data pengguna ke mesin Fingerspot
 */
function fingerspot_sync_user($pin, $name, $privilege = '1', $password = '') {
    $trans_id = (string)time(); 
    
    $data = [
        'trans_id' => $trans_id,
        'cloud_id' => FINGERSPOT_CLOUD_ID,
        'data' => [
            'pin'       => (string)$pin,
            'name'      => substr(trim($name), 0, 18), // Limit nama max 18 chars
            'privilege' => (string)$privilege, 
            'password'  => (string)$password,
            'rfid'      => '', 
            'template'  => ''  // Kosongkan agar template jari tidak terhapus
        ]
    ];

    $result = fingerspot_request('set_userinfo', $data);
    
    if ($result && isset($result['success']) && $result['success'] === true) {
        return true;
    }
    
    if (isset($result['message'])) {
        error_log("FINGERSPOT SYNC FAIL (PIN $pin): " . $result['message']);
    }
    
    return false;
}

/**
 * Hapus pengguna dari mesin Fingerspot
 */
function fingerspot_delete_user($pin) {
    $data = [
        'trans_id' => (string)time(),
        'cloud_id' => FINGERSPOT_CLOUD_ID,
        'pin'      => (string)$pin
    ];

    $result = fingerspot_request('delete_userinfo', $data);
    return ($result && isset($result['success']) && $result['success'] === true);
}

/**
 * Ambil log absensi historis (Recovery)
 */
function fingerspot_get_attlog_history($start_date, $end_date) {
    $data = [
        'trans_id'   => (string)time(),
        'cloud_id'   => FINGERSPOT_CLOUD_ID,
        'start_date' => $start_date,
        'end_date'   => $end_date
    ];

    $result = fingerspot_request('get_attlog', $data);
    if ($result && isset($result['success']) && $result['success'] === true) {
        return $result['data'] ?? [];
    }
    return false;
}

/**
 * Restart perangkat Fingerspot
 */
function fingerspot_restart_device() {
    $data = [
        'trans_id' => (string)time(),
        'cloud_id' => FINGERSPOT_CLOUD_ID
    ];

    $result = fingerspot_request('restart_device', $data);
    return ($result && isset($result['success']) && $result['success'] === true);
}

/**
 * Sinkronkan waktu mesin
 */
function fingerspot_set_time() {
    $data = [
        'trans_id' => (string)time(),
        'cloud_id' => FINGERSPOT_CLOUD_ID,
        'timezone' => 'Asia/Jakarta'
    ];

    $result = fingerspot_request('set_time', $data);
    return ($result && isset($result['success']) && $result['success'] === true);
}
?>