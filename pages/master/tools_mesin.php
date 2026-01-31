<?php 
require_once '../../config/database.php';
require_once '../../config/fingerspot_api.php';
cek_login(); 

// PROTEKSI: Hanya Admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

$msg = "";

// --- 1. LOGIC: INPUT ABSEN MANUAL (BARU) ---
if (isset($_POST['input_manual'])) {
    $pin_karyawan = $_POST['pin_karyawan'];
    $tgl_waktu    = $_POST['tgl_waktu']; // Format: Y-m-dTH:i
    $status_scan  = $_POST['status_scan'];
    
    // Konversi format datetime-local HTML ke MySQL
    $scan_date = date('Y-m-d H:i:s', strtotime($tgl_waktu));
    
    // Cek Duplikat (Agar tidak double input di detik yang sama)
    $cek = mysqli_query($conn, "SELECT id FROM absensi WHERE pin='$pin_karyawan' AND scan_date='$scan_date'");
    
    if (mysqli_num_rows($cek) > 0) {
        $msg = "<div class='alert alert-warning'><i class='fa fa-exclamation-circle'></i> Data absensi untuk waktu tersebut sudah ada.</div>";
    } else {
        // Insert Manual (verify_mode = 1 menandakan input manual/password)
        $stmt = mysqli_prepare($conn, "INSERT INTO absensi (pin, scan_date, status_scan, verify_mode) VALUES (?, ?, ?, 1)");
        mysqli_stmt_bind_param($stmt, "ssi", $pin_karyawan, $scan_date, $status_scan);
        
        if (mysqli_stmt_execute($stmt)) {
            $msg = "<div class='alert alert-success'><i class='fa fa-check-circle'></i> Absensi manual berhasil disimpan.</div>";
        } else {
            $msg = "<div class='alert alert-danger'><i class='fa fa-times'></i> Gagal menyimpan: " . mysqli_error($conn) . "</div>";
        }
    }
}

// --- 2. LOGIC: TARIK LOG CLOUD (RECOVERY) ---
if (isset($_POST['tarik_log'])) {
    $tgl_awal = $_POST['tgl_awal'];
    $tgl_akhir = $_POST['tgl_akhir'];
    
    $logs = fingerspot_get_attlog_history($tgl_awal, $tgl_akhir);
    
    if ($logs !== false && is_array($logs)) {
        $count_new = 0;
        mysqli_begin_transaction($conn);
        try {
            foreach ($logs as $log) {
                $pin = $log['pin'];
                $scan = $log['scan_date'];
                $status = isset($log['status_scan']) ? $log['status_scan'] : 0; 
                $verify = isset($log['verify']) ? $log['verify'] : 1; 

                $cek_user = mysqli_query($conn, "SELECT id FROM users WHERE pin = '$pin'");
                if (mysqli_num_rows($cek_user) > 0) {
                    $cek_dup = mysqli_query($conn, "SELECT id FROM absensi WHERE pin='$pin' AND scan_date='$scan'");
                    if (mysqli_num_rows($cek_dup) == 0) {
                        $stmt = mysqli_prepare($conn, "INSERT INTO absensi (pin, scan_date, status_scan, verify_mode) VALUES (?, ?, ?, ?)");
                        mysqli_stmt_bind_param($stmt, "ssii", $pin, $scan, $status, $verify);
                        mysqli_stmt_execute($stmt);
                        $count_new++;
                    }
                }
            }
            mysqli_commit($conn);
            $msg = "<div class='alert alert-success'><i class='fa fa-check-circle'></i> Recovery Sukses! <b>$count_new data absen</b> berhasil dipulihkan.</div>";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $msg = "<div class='alert alert-danger'><i class='fa fa-exclamation-triangle'></i> Error Database: " . $e->getMessage() . "</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning'><i class='fa fa-info-circle'></i> Tidak ditemukan data log baru di cloud.</div>";
    }
}

// --- 3. LOGIC: UTILITIES ---
if (isset($_POST['restart_mesin'])) {
    if (fingerspot_restart_device()) $msg = "<div class='alert alert-success'><i class='fa fa-check'></i> Perintah Restart dikirim.</div>";
    else $msg = "<div class='alert alert-danger'><i class='fa fa-times'></i> Gagal restart.</div>";
}
if (isset($_POST['sync_time'])) {
    if (fingerspot_set_time()) $msg = "<div class='alert alert-success'><i class='fa fa-check'></i> Waktu mesin disinkronkan.</div>";
    else $msg = "<div class='alert alert-danger'><i class='fa fa-times'></i> Gagal sinkron waktu.</div>";
}

// --- 4. LOGIC: PUSH ALL USERS (Update: Ditambahkan role kepala_bengkel) ---
if (isset($_POST['push_all_users'])) {
    $q = mysqli_query($conn, "SELECT * FROM users WHERE role IN ('user', 'kepala_bengkel')");
    $sukses = 0;
    while($u = mysqli_fetch_assoc($q)) {
        if(fingerspot_sync_user($u['pin'], $u['fullname'], '1')) { 
            $sukses++;
            mysqli_query($conn, "UPDATE users SET sync_status='synced' WHERE id={$u['id']}");
        }
    }
    $msg = "<div class='alert alert-info'><i class='fa fa-upload'></i> Sinkronisasi Massal Selesai. <b>$sukses personel</b> diproses.</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <style>
        body { background-color: #f4f6f9; font-family: 'Poppins', sans-serif; }
        .content-wrapper { padding: 30px; }
        .modern-card { background: #fff; border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 25px; overflow: hidden; height: 100%; transition: transform 0.3s; }
        .modern-card:hover { transform: translateY(-3px); }
        .card-header-gradient { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 25px; position: relative; color: #fff; }
        .card-header-gradient.orange { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); }
        .card-header-gradient.blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .card-header-gradient.green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .card-title { margin: 0; font-weight: 800; font-size: 18px; display: flex; align-items: center; gap: 10px; }
        .card-body { padding: 30px; }
        .form-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 0.5px; }
        .form-control-custom { height: 45px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 14px; padding-left: 15px; transition: 0.3s; background: #f8fafc; }
        .form-control-custom:focus { border-color: #3b82f6; background: #fff; }
        .btn-action { width: 100%; padding: 15px; border-radius: 12px; font-weight: 700; font-size: 14px; border: none; color: #fff; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
        .btn-orange { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3); }
        .btn-red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); }
        .btn-teal { background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); box-shadow: 0 4px 15px rgba(20, 184, 166, 0.3); }
        .btn-green { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3); }
        .btn-action:hover { transform: translateY(-2px); filter: brightness(110%); }
        .util-icon { font-size: 40px; margin-bottom: 15px; opacity: 0.2; position: absolute; right: 20px; top: 20px; }
        .text-desc { font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 20px; min-height: 40px; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="row mb-20" style="margin-bottom: 30px;">
            <div class="col-md-12">
                <h3 style="margin:0; font-weight:800; color:#1e293b;">Maintenance Mesin</h3>
                <p class="text-muted" style="margin:5px 0 0;">Alat bantu pemulihan data, input manual, dan manajemen perangkat.</p>
            </div>
        </div>

        <?php echo $msg; ?>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="modern-card">
                    <div class="card-header-gradient green">
                        <h4 class="card-title"><i class="fa fa-keyboard-o"></i> Input Absensi Manual</h4>
                        <p style="margin:5px 0 0; font-size:12px; opacity:0.8;">Gunakan jika mesin rusak, mati listrik, atau karyawan lupa scan.</p>
                        <i class="fa fa-edit util-icon"></i>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Pilih Karyawan</label>
                                        <select name="pin_karyawan" class="form-control form-control-custom" required>
                                            <option value="">-- Cari Nama --</option>
                                            <?php 
                                            // Update: Ditambahkan role kepala_bengkel
                                            $q_u = mysqli_query($conn, "SELECT pin, fullname FROM users WHERE role IN ('user', 'kepala_bengkel') ORDER BY fullname ASC");
                                            while($u = mysqli_fetch_assoc($q_u)){
                                                echo "<option value='{$u['pin']}'>{$u['fullname']} (PIN: {$u['pin']})</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Tanggal & Jam</label>
                                        <input type="datetime-local" name="tgl_waktu" class="form-control form-control-custom" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Status Scan</label>
                                        <select name="status_scan" class="form-control form-control-custom">
                                            <option value="0">Scan Masuk (Check In)</option>
                                            <option value="1">Scan Pulang (Check Out)</option>
                                            <option value="2">Istirahat Keluar (Break Out)</option>
                                            <option value="3">Istirahat Masuk (Break In)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="input_manual" class="btn-action btn-green mt-3" style="margin-top:15px;">
                                <i class="fa fa-save"></i> SIMPAN DATA MANUAL
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="modern-card">
                    <div class="card-header-gradient blue">
                        <h4 class="card-title"><i class="fa fa-cloud-download"></i> Recovery Absensi</h4>
                        <p style="margin:5px 0 0; font-size:12px; opacity:0.8;">Tarik ulang data dari Cloud jika Webhook macet.</p>
                        <i class="fa fa-database util-icon"></i>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Dari Tanggal</label>
                                        <input type="date" name="tgl_awal" class="form-control form-control-custom" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Sampai Tanggal</label>
                                        <input type="date" name="tgl_akhir" class="form-control form-control-custom" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="tarik_log" class="btn-action btn-blue mt-3" style="margin-top:15px;">
                                <i class="fa fa-refresh"></i> TARIK DATA CLOUD
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="modern-card" style="min-height:220px;">
                            <div class="card-body text-center">
                                <div style="width:60px; height:60px; background:#fff7ed; color:#ea580c; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 15px;">
                                    <i class="fa fa-clock-o"></i>
                                </div>
                                <h5 style="font-weight:700; color:#1e293b;">Sinkron Waktu</h5>
                                <p class="text-desc">Cocokkan jam mesin dengan server (WIB) agar akurat.</p>
                                <form method="POST">
                                    <button type="submit" name="sync_time" class="btn-action btn-orange py-2" style="padding:10px;">
                                        SYNC CLOCK
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="modern-card" style="min-height:220px;">
                            <div class="card-body text-center">
                                <div style="width:60px; height:60px; background:#fef2f2; color:#dc2626; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 15px;">
                                    <i class="fa fa-power-off"></i>
                                </div>
                                <h5 style="font-weight:700; color:#1e293b;">Reboot Mesin</h5>
                                <p class="text-desc">Restart perangkat jarak jauh jika mesin lag/error.</p>
                                <form method="POST">
                                    <button type="submit" name="restart_mesin" class="btn-action btn-red py-2" style="padding:10px;" onclick="return confirm('Yakin restart mesin?')">
                                        RESTART
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="modern-card">
                    <div class="card-body" style="background: linear-gradient(to right, #f0fdfa, #fff);">
                        <div class="row align-items-center" style="display:flex; align-items:center; flex-wrap:wrap;">
                            <div class="col-md-8">
                                <h5 style="font-weight:800; color:#0f766e; margin-bottom:5px;">
                                    <i class="fa fa-users"></i> Push Data Karyawan
                                </h5>
                                <p style="color:#64748b; font-size:13px; margin:0;">
                                    Kirim ulang <b>semua nama & PIN karyawan</b> dari database lokal ke mesin absensi. Gunakan fitur ini hanya saat ganti mesin baru atau reset pabrik.
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <form method="POST">
                                    <button type="submit" name="push_all_users" class="btn-action btn-teal" onclick="return confirm('Proses ini akan menimpa data nama di mesin. Lanjutkan?')">
                                        <i class="fa fa-upload"></i> EKSEKUSI PUSH
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <?php include '../../layout/footer.php'; ?>
</body>
</html>