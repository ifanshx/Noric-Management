<?php
// Mulai sesi untuk bisa mengaksesnya
session_start();

// 1. Hapus semua variabel sesi
$_SESSION = array();

// 2. Hapus Cookie Sesi (Penting agar browser lupa ID sesi lama)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan sesi di server
session_destroy();

// 4. Redirect kembali ke login dengan pesan (opsional via parameter URL)
header("location: login.php?msg=logout");
exit;
?>