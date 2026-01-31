<?php
require_once __DIR__ . '/database.php'; 

// --- FUNGSI LOG (Lama) ---
function getLog() {
    global $conn;
    $sql    = 'SELECT * FROM t_log ORDER BY created_at DESC';
    $result = mysqli_query($conn, $sql);
    $arr    = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $arr[] = $row;
    }
    return $arr;
}

// --- FUNGSI KEAMANAN CSRF (BARU) ---
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("Akses Ditolak: Validasi Security Token (CSRF) Gagal. Silakan refresh halaman.");
    }
}
?>