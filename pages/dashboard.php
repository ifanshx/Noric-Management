<?php 
// --- 1. SETTING ERROR HANDLER ---
ini_set('display_errors', 0); 
error_reporting(E_ALL);

// --- 2. KONEKSI & SESSION ---
require_once '../config/database.php';
date_default_timezone_set('Asia/Jakarta');
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../../index.php?pesan=belum_login");
    exit;
}

$my_id    = $_SESSION['user_id'];
$role     = $_SESSION['role'] ?? 'user';
$fullname = isset($_SESSION['fullname']) ? explode(' ', $_SESSION['fullname'])[0] : 'User';

// --- 3. HELPER FUNCTIONS ---
function get_safe_single_val($conn, $query) {
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['val'] ?? 0;
    }
    return 0;
}

function get_greeting() {
    $h = date('H');
    if ($h < 11) return "Selamat Pagi";
    if ($h < 15) return "Selamat Siang";
    if ($h < 19) return "Selamat Sore";
    return "Selamat Malam";
}

// --- 4. CONFIG JAM KERJA ---
$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1");
$aturan = ($q_set && mysqli_num_rows($q_set) > 0) ? mysqli_fetch_assoc($q_set) : [];

// Default Values
$jam_masuk = $aturan['jam_masuk'] ?? "08:00";
$jam_pulang = $aturan['jam_pulang'] ?? "16:00";
$target_full = $aturan['target_menit_full'] ?? 420;
$toleransi = $aturan['toleransi_telat'] ?? 5;

// Range Scan Absen
$range_masuk_start    = $aturan['range_masuk_start'] ?? "04:00:00";
$range_masuk_end      = $aturan['range_masuk_end'] ?? "10:30:00";
$range_pulang_start   = $aturan['range_pulang_start'] ?? "14:00:01";
$range_pulang_end     = $aturan['range_pulang_end'] ?? "23:59:59";

// JS Variables Default
$js_jam_masuk = 0;
$js_jam_pulang = 0;
$js_status_kerja = "belum"; 

// ============================================================
// LOGIKA 1: ADMIN (KPI STATISTIK)
// ============================================================
if ($role == 'admin') {
    $today = date('Y-m-d');
    $cnt_hadir = get_safe_single_val($conn, "SELECT COUNT(DISTINCT pin) as val FROM absensi WHERE DATE(scan_date) = '$today'");
    $cnt_pending = get_safe_single_val($conn, "SELECT COUNT(*) as val FROM orderan WHERE status='Pending'");
    $cnt_proses = get_safe_single_val($conn, "SELECT COUNT(*) as val FROM orderan WHERE status='Proses'");
    $prod_today = get_safe_single_val($conn, "SELECT SUM(jumlah) as val FROM produksi_borongan WHERE tanggal='$today'");

    $kpi_list = [
        ['val' => $cnt_hadir, 'label' => 'Hadir Hari Ini', 'icon' => 'fa-users', 'color' => 'blue'],
        ['val' => $cnt_pending, 'label' => 'Order Pending', 'icon' => 'fa-clock', 'color' => 'orange'],
        ['val' => $cnt_proses, 'label' => 'Order Proses', 'icon' => 'fa-cogs', 'color' => 'teal'],
        ['val' => number_format($prod_today), 'label' => 'Output Produksi', 'icon' => 'fa-box-open', 'color' => 'green']
    ];

    $q_list_query = "SELECT a.*, u.fullname FROM absensi a LEFT JOIN users u ON a.pin = u.pin WHERE DATE(a.scan_date) = CURDATE() ORDER BY a.scan_date DESC LIMIT 8";
} 
// ============================================================
// LOGIKA 2: USER (PENGUMUMAN ATURAN & LIVE STATUS)
// ============================================================
else {
    $q_u = mysqli_query($conn, "SELECT pin FROM users WHERE id='$my_id'");
    $u_data = mysqli_fetch_assoc($q_u);
    $pin = $u_data['pin'];
    $today = date('Y-m-d');

    // Cek Status Absen Hari Ini
    $q_absen = mysqli_query($conn, "SELECT scan_date FROM absensi WHERE pin='$pin' AND DATE(scan_date)='$today' ORDER BY scan_date ASC");
    $jam_in_db = null; $jam_out_db = null;

    if ($q_absen) {
        while ($r = mysqli_fetch_assoc($q_absen)) {
            $j = date('H:i:s', strtotime($r['scan_date']));
            if (!$jam_in_db && $j >= $range_masuk_start && $j <= $range_masuk_end) { $jam_in_db = $r['scan_date']; }
            if ($j >= $range_pulang_start && $j <= $range_pulang_end) { $jam_out_db = $r['scan_date']; }
        }
    }

    $durasi_display = "Belum Absen Masuk";
    if ($jam_in_db) {
        $dt_in = new DateTime($jam_in_db);
        $js_jam_masuk = $dt_in->getTimestamp() * 1000;
        
        if ($jam_out_db) {
            $dt_out = new DateTime($jam_out_db);
            $diff = $dt_in->diff($dt_out);
            $durasi_display = sprintf("%02d Jam %02d Menit", ($diff->days * 24) + $diff->h, $diff->i);
            $js_status_kerja = "selesai";
        } else {
            $durasi_display = "Sedang Bekerja...";
            $js_status_kerja = "kerja";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4f46e5; 
            --dark: #1e293b;
            --light: #f8fafc;
            --gray-border: #e2e8f0;
        }
        body { background-color: var(--light); font-family: 'Inter', sans-serif; color: #334155; }
        .content-wrapper { padding: 30px; }
        
        /* Welcome Banner */
        .welcome-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            padding: 30px;
            color: #fff;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px -10px rgba(30, 41, 59, 0.5);
            position: relative;
            overflow: hidden;
        }
        .welcome-text h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; }
        .welcome-text p { margin: 5px 0 0; opacity: 0.8; font-size: 14px; }
        .live-clock { font-family: 'Courier Prime', monospace; font-size: 32px; color: #818cf8; text-align: right; }
        .live-date { font-size: 13px; opacity: 0.8; text-align: right; margin-top: 5px; }

        /* KPI Grid (Admin) */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card {
            background: #fff; border-radius: 16px; padding: 20px;
            border: 1px solid var(--gray-border);
            display: flex; align-items: center; gap: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.05); }
        .icon-box {
            width: 50px; height: 50px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        /* Icon Colors */
        .bg-blue { background: #eff6ff; color: #2563eb; }
        .bg-green { background: #f0fdf4; color: #16a34a; }
        .bg-orange { background: #fff7ed; color: #ea580c; }
        .bg-teal { background: #f0f9ff; color: #0891b2; }
        .stat-val { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.2; }
        .stat-label { font-size: 12px; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }

        /* Announcement Cards (User) */
        .info-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .info-card {
            background: #fff; border-radius: 16px; border: 1px solid var(--gray-border);
            overflow: hidden; height: 100%;
        }
        .card-header {
            padding: 20px 25px; border-bottom: 1px solid var(--gray-border);
            background: #f8fafc; display: flex; align-items: center; gap: 10px;
        }
        .card-header h3 { margin: 0; font-size: 16px; font-weight: 700; color: #334155; }
        .card-body { padding: 25px; }

        /* Rules List */
        .rule-item {
            display: flex; align-items: flex-start; gap: 15px;
            margin-bottom: 15px; padding-bottom: 15px;
            border-bottom: 1px dashed var(--gray-border);
        }
        .rule-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .rule-icon {
            width: 36px; height: 36px; border-radius: 50%;
            background: #eef2ff; color: #4f46e5;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0;
        }
        .rule-content h4 { margin: 0 0 4px; font-size: 14px; font-weight: 700; color: #1e293b; }
        .rule-content p { margin: 0; font-size: 13px; color: #64748b; line-height: 1.5; }

        /* Live Status Box */
        .status-box { text-align: center; padding: 10px; }
        .timer-display {
            font-family: 'Courier Prime', monospace;
            font-size: 28px; font-weight: 700;
            color: #2563eb; margin: 10px 0;
        }
        .status-badge {
            display: inline-block; padding: 6px 12px;
            border-radius: 20px; font-size: 12px; font-weight: 700;
            text-transform: uppercase;
        }
        .badge-working { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-idle { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .welcome-card { flex-direction: column; text-align: center; gap: 15px; }
            .live-clock, .live-date { text-align: center; }
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include '../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="welcome-card">
            <div class="welcome-text">
                <h1>Halo, <?=$fullname?>!</h1>
                <p>Selamat datang di Dashboard Sistem Manajemen Karyawan.</p>
            </div>
            <div>
                <div class="live-clock" id="rt-clock">00:00:00</div>
                <div class="live-date" id="rt-date">...</div>
            </div>
        </div>

        <?php if($role == 'admin'): ?>
            <div class="stats-grid">
                <?php foreach($kpi_list as $k): ?>
                <div class="stat-card">
                    <div class="icon-box bg-<?=$k['color']?>"><i class="fa <?=$k['icon']?>"></i></div>
                    <div>
                        <div class="stat-val"><?=$k['val']?></div>
                        <div class="stat-label"><?=$k['label']?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="info-card">
                <div class="card-header"><i class="fa fa-list text-primary"></i> <h3>Aktivitas Absensi Terbaru</h3></div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0" style="font-size:13px;">
                        <thead class="bg-light"><tr><th class="pl-4">Nama</th><th>Waktu</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php 
                            $res = mysqli_query($conn, $q_list_query);
                            while($row = mysqli_fetch_assoc($res)): 
                                $j = date('H:i', strtotime($row['scan_date']));
                                $s = ($j >= $range_masuk_start && $j <= $range_masuk_end) ? 'MASUK' : 'PULANG';
                            ?>
                            <tr>
                                <td class="pl-4"><b><?=$row['fullname']?></b></td>
                                <td style="font-family:monospace;"><?=$j?></td>
                                <td><span class="badge badge-<?=($s=='MASUK'?'success':'danger')?>"><?=$s?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <div class="info-grid">
                
                <div class="info-card">
                    <div class="card-header">
                        <i class="fa fa-book text-primary"></i> <h3>Aturan & Kebijakan Penggajian</h3>
                    </div>
                    <div class="card-body">
                        <div class="rule-item">
                            <div class="rule-icon"><i class="fa fa-clock"></i></div>
                            <div class="rule-content">
                                <h4>Jam Kerja & Keterlambatan</h4>
                                <p>Jam Masuk: <b><?=$jam_masuk?></b>. Toleransi terlambat <b><?=$toleransi?> menit</b>. Keterlambatan akan mengurangi durasi kerja riil.</p>
                            </div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-icon"><i class="fa fa-money-bill-wave"></i></div>
                            <div class="rule-content">
                                <h4>Gaji Pokok (Karyawan Tetap)</h4>
                                <p>Target Full Day adalah <b><?=$target_full?> menit</b> (7 jam). Jika kurang, gaji pokok dihitung <b>Pro-Rata</b>. Uang makan cair jika kerja minimal <b>5 Jam</b>.</p>
                            </div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-icon"><i class="fa fa-bolt"></i></div>
                            <div class="rule-content">
                                <h4>Lembur & Shift</h4>
                                <p>Lembur dihitung dari kelebihan menit kerja setelah mencapai target. Pulang di atas <b>18:30</b> dikenakan potongan istirahat Maghrib 30 menit.</p>
                            </div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-icon"><i class="fa fa-cubes"></i></div>
                            <div class="rule-content">
                                <h4>Kebijakan Karyawan Borongan</h4>
                                <p>Gaji dihitung berdasarkan <b>Total Output Produksi</b> yang disetujui (Approved). Absensi tetap wajib dilakukan sebagai bukti kehadiran, namun tidak berlaku aturan pro-rata menit.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="card-header">
                        <i class="fa fa-stopwatch text-danger"></i> <h3>Status Kerja Hari Ini</h3>
                    </div>
                    <div class="card-body">
                        <div class="status-box">
                            <div style="font-size:12px; color:#64748b; margin-bottom:5px;">Durasi Kerja Real-Time</div>
                            <div id="live-timer" class="timer-display">
                                <?php echo ($js_status_kerja == 'kerja') ? '00:00:00' : $durasi_display; ?>
                            </div>
                            <span class="status-badge <?= ($js_status_kerja == 'kerja') ? 'badge-working pulse' : 'badge-idle' ?>">
                                <?= ($js_status_kerja == 'kerja') ? 'SEDANG BEKERJA' : strtoupper($durasi_display) ?>
                            </span>
                            
                            <hr style="margin:20px 0; border-top:1px dashed #e2e8f0;">
                            
                            <div style="display:flex; justify-content:space-between; font-size:12px;">
                                <div style="text-align:left;">
                                    <div style="color:#64748b;">Masuk</div>
                                    <div style="font-weight:700; color:#1e293b;"><?= $jam_in_db ? date('H:i', strtotime($jam_in_db)) : '-' ?></div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="color:#64748b;">Pulang</div>
                                    <div style="font-weight:700; color:#1e293b;"><?= $jam_out_db ? date('H:i', strtotime($jam_out_db)) : '-' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div>

    <?php include '../layout/footer.php'; ?>
    
    <script>
        // Jam Digital Header
        function updateClock() {
            const now = new Date();
            document.getElementById('rt-clock').textContent = now.toLocaleTimeString('id-ID', {hour12:false});
            document.getElementById('rt-date').textContent = now.toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long', year:'numeric'});
        }
        setInterval(updateClock, 1000); updateClock();

        // Timer Durasi Kerja (Hanya untuk User yang sedang bekerja)
        <?php if($role != 'admin' && $js_status_kerja == 'kerja'): ?>
            const jamMasuk = <?=$js_jam_masuk?>;
            
            function updateTimer() {
                const now = new Date().getTime();
                const diff = now - jamMasuk;
                
                if(diff > 0) {
                    const h = Math.floor(diff / (1000 * 60 * 60));
                    const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const s = Math.floor((diff % (1000 * 60)) / 1000);
                    
                    const str = [h, m, s].map(v => v < 10 ? "0" + v : v).join(":");
                    document.getElementById('live-timer').innerText = str;
                }
            }
            setInterval(updateTimer, 1000);
            updateTimer();
        <?php endif; ?>
    </script>
</body>
</html>