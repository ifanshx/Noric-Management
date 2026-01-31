<?php
// 1. Panggil Koneksi Database
require_once '../config/database.php';

// Set Timezone (Penting agar jam sesuai WIB)
date_default_timezone_set('Asia/Jakarta');

// 2. Ambil Data Body (Raw Input)
$body = file_get_contents('php://input');

// (Opsional) Debugging: Simpan log mentah ke file untuk cek jika ada masalah
// file_put_contents('debug_log.txt', $body . PHP_EOL, FILE_APPEND);

// 3. Parsing JSON ke Array PHP
$json = json_decode($body, true);

// 4. Validasi Data
// Pastikan tipe datanya adalah 'attlog' dan ada isinya
if (isset($json['type']) && $json['type'] == 'attlog' && isset($json['data'])) {
    
    $data = $json['data'];
    $cloud_id = $json['cloud_id']; // ID Mesin (Serial Number)

    // Ambil variabel penting
    $pin = $data['pin']; // User ID di mesin
    $scan_date = date('Y-m-d H:i:s', strtotime($data['scan'])); // Format datetime MySQL
    $verify = isset($data['verify']) ? $data['verify'] : 0; // Cara absen (Finger/Wajah/dll)
    
    // Status Scan (PENTING: Ambil sesuai dokumentasi)
    // 0: Masuk, 1: Pulang, 2: Break Out, 3: Break In, dst.
    // Jika mesin tidak kirim status, default 0 (Masuk)
    $status_scan = isset($data['status_scan']) ? intval($data['status_scan']) : 0; 

    // 5. Simpan ke Tabel Log Mentah (Opsional tapi direkomendasikan)
    // Berguna jika nanti ada dispute data
    $stmt_log = mysqli_prepare($conn, "INSERT INTO t_log (cloud_id, type, original_data, created_at) VALUES (?, 'attlog', ?, NOW())");
    mysqli_stmt_bind_param($stmt_log, "ss", $cloud_id, $body);
    mysqli_stmt_execute($stmt_log);

    // 6. Simpan ke Tabel Absensi Utama
    // Cek dulu apakah PIN ini ada di database user kita?
    $cek_user = mysqli_query($conn, "SELECT id FROM users WHERE pin = '$pin'");
    
    if (mysqli_num_rows($cek_user) > 0) {
        // Cek Duplikasi: Jangan simpan jika data sama persis sudah ada (cegah double post dari mesin)
        $cek_dup = mysqli_query($conn, "SELECT id FROM absensi WHERE pin = '$pin' AND scan_date = '$scan_date'");
        
        if (mysqli_num_rows($cek_dup) == 0) {
            // Lakukan Insert
            $stmt_absen = mysqli_prepare($conn, "INSERT INTO absensi (pin, scan_date, status_scan, verify_mode) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_absen, "ssii", $pin, $scan_date, $status_scan, $verify);
            
            if (mysqli_stmt_execute($stmt_absen)) {
                // Berhasil
            } else {
                // Gagal Insert (Bisa dicatat di error log)
                error_log("Gagal insert absensi: " . mysqli_error($conn));
            }
        }
    } else {
        // PIN tidak dikenali (Karyawan belum didaftarkan di web tapi sudah absen di mesin)
        // Opsional: Simpan ke tabel 'unknown_scan' atau biarkan saja di t_log
    }
}

// 7. Berikan Respon "OK" ke Mesin (Wajib)
// Jika tidak ada respon ini, mesin akan menganggap gagal dan mengirim ulang data terus menerus.
echo "OK";
?>