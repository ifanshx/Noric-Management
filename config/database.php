<?php
// config/database.php

// --- 1. GLOBAL SETTINGS (TIMEZONE & ERROR) ---

// Wajib set Timezone ke Jakarta agar tidak selisih 7 jam
date_default_timezone_set('Asia/Jakarta');

$env = 'production'; // Ubah ke 'development' jika sedang debugging
if ($env === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
}

if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

// --- 2. KONEKSI DATABASE ---
$db_host = "localhost"; 
$db_user = "uwjqdfka_noric";
$db_pass = "Noric1710";
$db_name = "uwjqdfka_noric_management";

try {
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
    if (mysqli_connect_errno()) {
        throw new Exception("Koneksi Database Gagal: " . mysqli_connect_error());
    }
    
    // Set Charset
    mysqli_set_charset($conn, "utf8mb4");
    
    // Set Timezone MySQL ke WIB (UTC+7) agar sinkron dengan PHP
    mysqli_query($conn, "SET time_zone = '+07:00'");

} catch (Exception $e) {
    if ($env === 'development') {
        die("Database Error: " . $e->getMessage());
    } else {
        // Tampilan error yang ramah user
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
                <h2>Maaf, Sedang Ada Gangguan Sistem</h2>
                <p>Kami tidak dapat terhubung ke database saat ini. Silakan coba beberapa saat lagi.</p>
             </div>");
    }
}

// --- 3. KONFIGURASI URL ---
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$server_host = $_SERVER['HTTP_HOST'];

// Sesuaikan folder jika di localhost/folder atau langsung domain
$folder_project = "/"; 

define('BASE_URL', $protocol . $server_host . $folder_project);

// Variabel Bridging untuk kompatibilitas kode lama
$base_url = BASE_URL; 

// --- 4. HELPER FUNCTIONS ---

if (!function_exists('cek_login')) {
    function cek_login() {
        // Cek sesi login
        if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login") {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }
    }
}

if (!function_exists('get_user_data')) {
    function get_user_data($conn, $user_id) {
        // Ambil data user + info gaji (Left Join)
        $sql = "SELECT u.*, g.gaji_pokok, g.uang_makan, g.gaji_lembur
                FROM users u
                LEFT JOIN gaji_karyawan g ON u.id = g.user_id
                WHERE u.id = ?";
                
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_assoc($result);
    }
}
?>