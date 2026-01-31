<?php 
require_once '../../config/database.php';
// Set Timezone
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

if($_SESSION['role'] != 'admin') { 
    header("Location: ../dashboard.php"); 
    exit; 
}

// --- 1. CONFIG JAM KERJA ---
$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1");
$aturan = mysqli_fetch_assoc($q_set);

if (!$aturan) {
    echo "<script>alert('Harap simpan pengaturan jam kerja!'); window.location='../settings/setting_jam_kerja.php';</script>";
    exit;
}

// Parameter Dasar
$TARGET_FULL = 420; // 7 Jam Kerja Bersih
$TARGET_HALF = 210;
$TOL_TELAT   = 5;   
$LEM_MIN     = 30;
$LEM_MAX     = 720;

// BATAS WAKTU
$JAM_MASUK_NORMAL   = "08:00:00";
$JAM_MASUK_SIANG    = "12:30:00"; 
$JAM_IST_OUT        = "11:30:00";
$JAM_IST_IN         = "12:30:00";
$JAM_PULANG_NORMAL  = "16:00:00";

// --- 2. FILTER PERIODE ---
$default_awal  = date('Y-m-d', strtotime('monday this week'));
$default_akhir = date('Y-m-d', strtotime('sunday this week'));
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;
$filter_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

// --- 3. DATA USER ---
$sql_user = "SELECT id, fullname, pin FROM users WHERE role IN ('user', 'kepala_toko', 'kepala_bengkel')";
if(!empty($filter_id)) { $sql_user .= " AND id='$filter_id'"; }
$sql_user .= " ORDER BY fullname ASC";
$q_users = mysqli_query($conn, $sql_user);
$list_users = []; while($r = mysqli_fetch_assoc($q_users)) $list_users[] = $r;

// --- 4. DATA ABSENSI ---
// Perbaikan: Ambil data sampai H+1 pukul 06:00 pagi untuk menangkap absen pulang lewat tengah malam
$tgl_akhir_plus = date('Y-m-d H:i:s', strtotime($tgl_akhir . ' +1 day 06:00:00'));
$sql_absen = "SELECT pin, scan_date FROM absensi 
              WHERE scan_date BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir_plus'
              ORDER BY scan_date ASC";
$q_raw = mysqli_query($conn, $sql_absen);
$absen_map = []; 

while($r = mysqli_fetch_assoc($q_raw)) {
    $scan_ts = strtotime($r['scan_date']);
    $tgl_aktual = date('Y-m-d', $scan_ts);
    $jam = date('H:i:s', $scan_ts);
    
    // Jika scan antara jam 00:00 - 06:00 pagi, anggap itu absen pulang hari sebelumnya
    if ($jam >= "00:00:00" && $jam <= "06:00:00") {
        $tgl_idx = date('Y-m-d', strtotime($tgl_aktual . ' -1 day'));
    } else {
        $tgl_idx = $tgl_aktual;
    }
    
    $absen_map[$r['pin']][$tgl_idx][] = $jam;
}

// --- 5. PROCESSING LOGIC ---
$report_data = [];
$stats = ['count_hadir' => 0, 'count_telat' => 0, 'count_plg_awal' => 0, 'count_lembur' => 0];

$period = new DatePeriod(new DateTime($tgl_awal), new DateInterval('P1D'), new DateTime($tgl_akhir . ' +1 day'));

foreach($period as $dt) {
    $curr_date = $dt->format('Y-m-d');
    $is_sunday = ($dt->format('N') == 7); 
    
    foreach($list_users as $u) {
        $pin = $u['pin'];
        $d = [
            'tanggal' => $curr_date, 'nama' => $u['fullname'], 'pin' => $pin,
            'in' => null, 'ist_out' => null, 'ist_in' => null, 'out' => null,
            'telat' => 0, 'plg_awal' => 0, 'durasi' => 0, 'lembur' => 0,
            'status' => $is_sunday ? 'Libur' : 'Alpha',
            'badge' => $is_sunday ? 'badge-libur' : 'badge-alpha',
            'is_shift_siang' => false, 'potongan_istirahat' => 0,
            'is_maghrib' => false, 'warning_list' => []
        ];

        if(isset($absen_map[$pin][$curr_date])) {
            $scans = $absen_map[$pin][$curr_date];
            
            foreach($scans as $jam) {
                // Masuk: 06:00 - 11:00
                if ($jam >= "06:00:00" && $jam <= "11:00:00") {
                    if(!$d['in']) $d['in'] = $jam;
                }
                // Istirahat Keluar: 11:01 - 12:00
                elseif ($jam >= "11:01:00" && $jam <= "12:00:00") {
                    $d['ist_out'] = $jam;
                }
                // Istirahat Masuk / Masuk Siang: 12:01 - 13:00
                elseif ($jam >= "12:01:00" && $jam <= "13:30:00") {
                    if(!$d['in']) {
                        $d['in'] = $jam;
                        $d['is_shift_siang'] = true;
                    } else {
                        $d['ist_in'] = $jam;
                    }
                }
                // Pulang: 13:31 ke atas ATAU 00:00 - 06:00 (Subuh)
                elseif ($jam >= "13:31:00" || ($jam >= "00:00:00" && $jam <= "06:00:00")) {
                    $d['out'] = $jam;
                }
            }
            
            if($d['in']) {
                $d['status'] = 'HADIR'; $d['badge'] = 'badge-full';
                $stats['count_hadir']++;

                $ts_in = strtotime("$curr_date ".$d['in']);
                $jam_target = $d['is_shift_siang'] ? $JAM_MASUK_SIANG : $JAM_MASUK_NORMAL;
                $ts_target_masuk = strtotime("$curr_date $jam_target");

                if($ts_in > ($ts_target_masuk + ($TOL_TELAT * 60))) {
                    $d['telat'] = floor(($ts_in - $ts_target_masuk) / 60);
                    $stats['count_telat']++;
                }

                if ($d['out']) {
                    $ts_out = strtotime("$curr_date ".$d['out']);
                    if($ts_out < $ts_in) $ts_out += 86400; // Logika Cross-day

                    $ts_target_pulang = strtotime("$curr_date $JAM_PULANG_NORMAL");
                    if ($ts_out < $ts_target_pulang) {
                        $d['plg_awal'] = floor(($ts_target_pulang - $ts_out) / 60);
                        if($d['plg_awal'] > 0) $stats['count_plg_awal']++;
                    }

                    // Durasi & Potongan
                    $dur_kotor = floor(($ts_out - $ts_in)/60);
                    $potongan = 0;
                    if (!$d['is_shift_siang']) {
                        if ($ts_in < strtotime("$curr_date $JAM_IST_OUT") && $ts_out > strtotime("$curr_date $JAM_IST_IN")) {
                            $potongan = 60; 
                            if(!$d['ist_out'] && !$d['ist_in']) $d['warning_list'][] = "Pot. Istirahat (Auto)";
                        }
                    }

                    $d['potongan_istirahat'] = $potongan;
                    $d['durasi'] = max(0, $dur_kotor - $potongan);

                    // Lembur
                    if ($d['durasi'] > $TARGET_FULL) {
                        $raw_lembur = $d['durasi'] - $TARGET_FULL;
                        
                        // Potongan Maghrib (Jika kerja melewati jam 18:00)
                        $ts_maghrib = strtotime("$curr_date 18:00:00");
                        if ($ts_out > $ts_maghrib) {
                            $raw_lembur -= 30; 
                            $d['is_maghrib'] = true;
                        }

                        if ($raw_lembur >= $LEM_MIN) {
                            $d['lembur'] = min($raw_lembur, $LEM_MAX);
                            $stats['count_lembur'] += $d['lembur'];
                        }
                    }
                    
                    if ($d['durasi'] < $TARGET_HALF) $d['badge'] = 'badge-low';
                    elseif ($d['durasi'] < $TARGET_FULL) $d['badge'] = 'badge-half';
                } else {
                    $d['badge'] = 'badge-low';
                    $d['warning_list'][] = "Tidak Absen Pulang";
                }
            }
        }
        $report_data[] = $d;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <style>
        .laporan-wrapper { font-family: 'Inter', sans-serif; color: #1f2937; padding: 20px; }
        :root { 
            --accent-green: #10b981; --accent-red: #ef4444; --accent-blue: #3b82f6; 
            --accent-orange: #f97316; --accent-teal: #14b8a6;
        }
        .page-header-custom { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
        .page-header-custom h1 { font-size: 24px; font-weight: 800; color: #111827; margin: 0; }
        .filter-bar { background: #fff; padding: 15px 20px; border-radius: 12px; border: 1px solid #e5e7eb; display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 25px; align-items:center;}
        .date-input, .select-input { border: 1px solid #e5e7eb; background: #f9fafb; font-weight: 600; padding: 8px 12px; border-radius: 8px; font-size:13px; }
        .btn-apply { background: #1f2937; color: #fff; padding: 8px 20px; border-radius: 8px; font-weight: 600; border: none; font-size:13px; cursor: pointer;}
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; border-radius: 16px; padding: 20px; position: relative; border: 1px solid #f3f4f6; border-left: 4px solid #ddd; }
        .stat-card.blue { border-left-color: var(--accent-blue); }
        .stat-card.red { border-left-color: var(--accent-red); }
        .stat-card.orange { border-left-color: var(--accent-orange); }
        .stat-card.teal { border-left-color: var(--accent-teal); }
        .stat-title { font-size: 11px; text-transform: uppercase; font-weight: 600; color:#64748b; margin-bottom: 5px;}
        .stat-number { font-size: 24px; font-weight: 800; color:#1e293b;}
        .report-card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden; }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { background: #f9fafb; padding: 12px 8px; font-size: 10px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .table-custom td { padding: 10px 8px; border-bottom: 1px solid #f3f4f6; font-size: 12px; text-align:center; }
        .font-mono { font-family: 'Courier Prime', monospace; font-weight: 600; }
        .badge-pill { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; display: inline-block; }
        .badge-full { background: #dcfce7; color: #166534; } 
        .badge-alpha { background: #fee2e2; color: #991b1b; } 
        .badge-libur { background: #f1f5f9; color: #94a3b8; }
        .warning-tag { display:block; font-size:9px; color:#ef4444; font-weight:600; }
        .shift-tag { font-size: 9px; background: #e0f2fe; color: #0369a1; padding: 1px 4px; border-radius: 3px; font-weight: 700; }
        @media print {
            .no-print { display: none !important; }
            .print-header { display: block !important; }
            .table-custom th, .table-custom td { border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="laporan-wrapper">
            <div class="print-header" style="display:none; text-align:center; border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:20px;">
                <h2 style="margin:0;">NORIC RACING EXHAUST</h2>
                <p style="margin:0;">LAPORAN ABSENSI KARYAWAN</p>
                <p style="margin:0; font-size:12px;">Periode: <?= $tgl_awal ?> s/d <?= $tgl_akhir ?></p>
            </div>

            <div class="page-header-custom no-print">
                <div>
                    <h1>Rekapitulasi Absensi</h1>
                    <p style="color:#64748b;">Periode: <b><?= date('d M Y', strtotime($tgl_awal)) ?></b> - <b><?= date('d M Y', strtotime($tgl_akhir)) ?></b></p>
                </div>
                <button onclick="window.print()" class="btn-apply" style="background:white; color:#333; border:1px solid #ccc;">Cetak PDF</button>
            </div>

            <div class="filter-bar no-print">
                <form method="GET" style="display:flex; gap:10px;">
                    <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="date-input">
                    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="date-input">
                    <select name="user_id" class="select-input">
                        <option value="">-- Semua Karyawan --</option>
                        <?php foreach($list_users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($filter_id==$u['id'])?'selected':'' ?>><?= $u['fullname'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-apply">Filter</button>
                </form>
            </div>

            <div class="stats-grid no-print">
                <div class="stat-card blue">
                    <div class="stat-title">Total Hadir</div>
                    <div class="stat-number"><?= $stats['count_hadir'] ?></div>
                </div>
                <div class="stat-card red">
                    <div class="stat-title">Terlambat</div>
                    <div class="stat-number"><?= $stats['count_telat'] ?></div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-title">Pulang Awal</div>
                    <div class="stat-number"><?= $stats['count_plg_awal'] ?></div>
                </div>
                <div class="stat-card teal">
                    <div class="stat-title">Lembur (Menit)</div>
                    <div class="stat-number"><?= $stats['count_lembur'] ?></div>
                </div>
            </div>

            <div class="report-card">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Tgl</th>
                            <th style="text-align:left;">Nama</th>
                            <th>Masuk</th>
                            <th>Ist.Kel</th>
                            <th>Ist.Msk</th>
                            <th>Pulang</th>
                            <th>Telat</th>
                            <th>Durasi</th>
                            <th>Lembur</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($report_data as $row): ?>
                        <tr>
                            <td class="font-mono"><?= date('d/m', strtotime($row['tanggal'])) ?></td>
                            <td style="text-align:left; font-weight:600;"><?= $row['nama'] ?></td>
                            <td class="font-mono">
                                <?= $row['in'] ? date('H:i', strtotime($row['in'])) : '-' ?>
                                <?= $row['is_shift_siang'] ? '<span class="shift-tag">SIANG</span>' : '' ?>
                            </td>
                            <td><?= $row['ist_out'] ? date('H:i', strtotime($row['ist_out'])) : '-' ?></td>
                            <td><?= $row['ist_in'] ? date('H:i', strtotime($row['ist_in'])) : '-' ?></td>
                            <td class="font-mono" style="color:red;"><?= $row['out'] ? date('H:i', strtotime($row['out'])) : '-' ?></td>
                            <td style="color:red;"><?= $row['telat'] > 0 ? $row['telat'].'m' : '-' ?></td>
                            <td style="font-weight:700;">
                                <?php 
                                    $h = floor($row['durasi']/60); $m = $row['durasi']%60;
                                    echo $row['durasi'] > 0 ? "{$h}j {$m}m" : "-";
                                ?>
                            </td>
                            <td style="color:blue; font-weight:700;">
                                <?php 
                                    $lh = floor($row['lembur']/60); $lm = $row['lembur']%60;
                                    echo $row['lembur'] > 0 ? "+{$lh}j {$lm}m" : "-";
                                ?>
                                <?= $row['is_maghrib'] ? '<span class="warning-tag">Pot. Maghrib</span>' : '' ?>
                            </td>
                            <td>
                                <span class="badge-pill <?= $row['badge'] ?>"><?= $row['status'] ?></span>
                                <?php foreach($row['warning_list'] as $w) echo "<span class='warning-tag'>$w</span>"; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include '../../layout/footer.php'; ?>
</body>
</html>