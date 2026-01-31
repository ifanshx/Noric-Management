<?php 
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

// --- 1. CONFIG JAM KERJA ---
$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1");
$aturan = mysqli_fetch_assoc($q_set);

if (!$aturan) {
    die("<div style='padding:20px; color:red; font-weight:bold;'>Error: Konfigurasi Jam Kerja tidak ditemukan.</div>");
}

// Aturan Tetap
$JAM_IST_OUT     = "11:30:00";
$JAM_IST_IN      = "12:30:00";
$TARGET_FULL     = 420; 
$MIN_JAM_MAKAN   = 300; 

// --- 2. DATA USER ---
$my_uid = $_SESSION['user_id'];
$q_user = mysqli_query($conn, "
    SELECT u.*, g.gaji_pokok, g.uang_makan, g.gaji_lembur
    FROM users u 
    LEFT JOIN gaji_karyawan g ON u.id = g.user_id 
    WHERE u.id = '$my_uid'
");
$user_data = mysqli_fetch_assoc($q_user);

// --- 3. FILTER PERIODE ---
$default_awal  = date('Y-m-d', strtotime('monday this week'));
$default_akhir = date('Y-m-d', strtotime('sunday this week'));

$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;

// --- 4. ENGINE HITUNG GAJI ---
$gaji = [
    'gapok_total' => 0, 'makan_hak' => 0, 'lembur_total_rp' => 0, 'borongan_total' => 0,
    'kasbon_total' => 0, 'um_diambil' => 0, 'pot_um_anggota' => 0,
    'thp' => 0, 'lembur_menit_total' => 0, 
    'detail_log' => [], 'detail_produksi' => [], 'detail_um_anggota' => []
];

// =========================================================
// A. LOGIKA PENDAPATAN (KARYAWAN TETAP)
// =========================================================
if ($user_data['status_karyawan'] === 'Tetap') {
    $pin = $user_data['pin'];
    $absen_raw = [];
    
    // FIX: Ambil data sampai H+1 subuh untuk menangkap scan pulang malam
    $tgl_akhir_plus = date('Y-m-d H:i:s', strtotime($tgl_akhir . ' +1 day 06:00:00'));
    $q_absen = mysqli_query($conn, "SELECT scan_date FROM absensi WHERE pin = '$pin' AND scan_date BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir_plus' ORDER BY scan_date ASC");
    
    while($row = mysqli_fetch_assoc($q_absen)) {
        $scan_ts = strtotime($row['scan_date']);
        $tgl_aktual = date('Y-m-d', $scan_ts);
        $jam = date('H:i:s', $scan_ts);
        
        // Pengelompokan scan subuh ke hari sebelumnya
        if ($jam >= "00:00:00" && $jam <= "06:00:00") {
            $tgl_idx = date('Y-m-d', strtotime($tgl_aktual . ' -1 day'));
        } else {
            $tgl_idx = $tgl_aktual;
        }
        $absen_raw[$tgl_idx][] = $jam;
    }

    $period = new DatePeriod(new DateTime($tgl_awal), new DateInterval('P1D'), new DateTime($tgl_akhir . ' +1 day'));
    
    foreach ($period as $dt_obj) {
        $curr = $dt_obj->format('Y-m-d');
        if ($dt_obj->format('N') == 7) continue; 

        $log = ['tgl' => $curr, 'status' => 'Alpha', 'ket' => '-'];

        if (isset($absen_raw[$curr])) {
            $scans = $absen_raw[$curr];
            $in = null; $out = null; $is_shift_siang = false;

            foreach($scans as $jam) {
                if ($jam >= "06:00:00" && $jam <= "11:00:00") { if(!$in) $in = $jam; }
                elseif ($jam >= "12:01:00" && $jam <= "13:30:00") { if(!$in) { $in = $jam; $is_shift_siang = true; } }
                if ($jam >= "13:31:00" || ($jam >= "00:00:00" && $jam <= "06:00:00")) $out = $jam;
            }

            if ($in && $out) {
                $ts_in = strtotime("$curr $in");
                $ts_out = strtotime("$curr $out");
                if ($ts_out < $ts_in) $ts_out += 86400; // FIX: Handle Cross-day

                $durasi_menit = ($ts_out - $ts_in) / 60;
                if (!$is_shift_siang) {
                    if ($ts_in < strtotime("$curr $JAM_IST_OUT") && $ts_out > strtotime("$curr $JAM_IST_IN")) {
                        $durasi_menit -= 60;
                    }
                }
                $durasi_bersih = max(0, floor($durasi_menit));

                // Hak Uang Makan
                if ($durasi_bersih >= $MIN_JAM_MAKAN) {
                    $gaji['makan_hak'] += $user_data['uang_makan'];
                }

                // Gaji Pokok & Lembur
                if ($durasi_bersih >= $TARGET_FULL) {
                    $gaji['gapok_total'] += $user_data['gaji_pokok'];
                    $log['status'] = "Full Day";
                    
                    $menit_lembur = $durasi_bersih - $TARGET_FULL;
                    if ($ts_out > strtotime("$curr 18:00:00")) $menit_lembur -= 30; // Pot. Maghrib
                    
                    if ($menit_lembur >= 30) {
                        $gaji['lembur_menit_total'] += $menit_lembur;
                        $gaji['lembur_total_rp'] += ($menit_lembur / 60) * $user_data['gaji_lembur'];
                        $log['status'] = "Full Day +Lembur";
                    }
                } else {
                    $upah_pro_rata = ($durasi_bersih / $TARGET_FULL) * $user_data['gaji_pokok'];
                    $gaji['gapok_total'] += $upah_pro_rata;
                    $log['status'] = ($durasi_bersih >= 210) ? "Half Day" : "Low Hour";
                }
                $log['ket'] = "Durasi: ".floor($durasi_bersih/60)."j ".($durasi_bersih%60)."m";
            } elseif ($in && !$out) {
                $log['status'] = "Invalid"; $log['ket'] = "Lupa Pulang";
            }
        }
        $gaji['detail_log'][] = $log;
    }
}

// =========================================================
// B. LOGIKA PENDAPATAN (KARYAWAN BORONGAN)
// =========================================================
if ($user_data['status_karyawan'] == 'Borongan') {
    $q_prod = mysqli_query($conn, "SELECT * FROM produksi_borongan WHERE user_id = '$my_uid' AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' ORDER BY tanggal ASC");
    while($prod = mysqli_fetch_assoc($q_prod)) {
        $gaji['detail_produksi'][] = $prod;
        $gaji['borongan_total'] += $prod['total_upah'];
    }
    if (!empty($user_data['group_id']) && $user_data['is_mandor'] == 0) {
        $gaji['borongan_total'] = 0; 
    }
}

// =========================================================
// C. LOGIKA POTONGAN
// =========================================================

// 1. Kasbon Mingguan
$jumlah_sabtu = 0;
foreach (new DatePeriod(new DateTime($tgl_awal), new DateInterval('P1D'), new DateTime($tgl_akhir . ' +1 day')) as $d) {
    if ($d->format('N') == 6) $jumlah_sabtu++;
}
if ($jumlah_sabtu > 0) {
    $q_bon = mysqli_query($conn, "SELECT nominal, tenor, terbayar FROM kasbon WHERE user_id = '$my_uid' AND status = 'Approved' AND (nominal - terbayar) > 0");
    while ($b = mysqli_fetch_assoc($q_bon)) {
        $cicilan = ceil($b['nominal'] / $b['tenor']);
        $sisa = $b['nominal'] - $b['terbayar'];
        $gaji['kasbon_total'] += min($cicilan * $jumlah_sabtu, $sisa);
    }
}

// 2. Potongan Uang Makan (Sendiri)
$q_um = mysqli_query($conn, "SELECT SUM(nominal) as total FROM uang_makan WHERE user_id = '$my_uid' AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
$d_um = mysqli_fetch_assoc($q_um);
$um_total_diambil = $d_um['total'] ?? 0;

// 3. Logika Khusus Mandor vs Anggota
$is_anggota_borongan = ($user_data['status_karyawan'] == 'Borongan' && !empty($user_data['group_id']) && $user_data['is_mandor'] == 0);
$is_mandor_borongan  = ($user_data['status_karyawan'] == 'Borongan' && !empty($user_data['group_id']) && $user_data['is_mandor'] == 1);

if ($is_anggota_borongan) {
    $gaji['um_diambil'] = 0; // Dibebankan ke Mandor
} else {
    $gaji['um_diambil'] = $um_total_diambil;
}

if ($is_mandor_borongan) {
    $group_id = $user_data['group_id'];
    $q_anggota = mysqli_query($conn, "SELECT id FROM users WHERE group_id='$group_id' AND is_mandor=0");
    $ids_anggota = [];
    while($agm = mysqli_fetch_assoc($q_anggota)) { $ids_anggota[] = $agm['id']; }

    if(!empty($ids_anggota)) {
        $ids_str = implode(',', $ids_anggota);
        $q_um_agg = mysqli_query($conn, "SELECT SUM(nominal) as total FROM uang_makan WHERE user_id IN ($ids_str) AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
        $d_agg = mysqli_fetch_assoc($q_um_agg);
        $gaji['pot_um_anggota'] = $d_agg['total'] ?? 0;

        if ($gaji['pot_um_anggota'] > 0) {
            $q_dt_um = mysqli_query($conn, "SELECT u.fullname, um.nominal, um.tanggal FROM uang_makan um JOIN users u ON um.user_id = u.id WHERE um.user_id IN ($ids_str) AND um.status = 'Approved' AND um.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' ORDER BY um.tanggal ASC");
            while($row_um = mysqli_fetch_assoc($q_dt_um)) { $gaji['detail_um_anggota'][] = $row_um; }
        }
    }
}

// FINAL THP
$pendapatan = $gaji['gapok_total'] + $gaji['makan_hak'] + $gaji['lembur_total_rp'] + $gaji['borongan_total'];
$potongan   = $gaji['kasbon_total'] + $gaji['um_diambil'] + $gaji['pot_um_anggota'];
$gaji['thp'] = max(0, $pendapatan - $potongan);

$label_periode = date('d M Y', strtotime($tgl_awal)) . " - " . date('d M Y', strtotime($tgl_akhir));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        :root { --primary: #4338ca; --success: #10b981; --danger: #ef4444; --border-color: #e2e8f0; }
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .content-wrapper { padding: 30px; }
        .slip-card { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid var(--border-color); overflow: hidden; }
        .slip-header { background: #1e293b; color: #fff; padding: 40px; text-align: center; }
        .slip-body { padding: 40px; }
        .filter-bar { display: flex; justify-content: center; gap: 10px; margin-bottom: 30px; }
        .date-input { border: 1px solid var(--border-color); padding: 8px; border-radius: 8px; }
        .btn-filter { background: var(--primary); color: white; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer; }
        .section-title { font-size: 12px; text-transform: uppercase; font-weight: 700; color: #64748b; margin-top: 25px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .row-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed #eee; font-size: 14px; }
        .row-item span:last-child { font-weight: 700; font-family: monospace; }
        .thp-box { background: #eff6ff; padding: 30px; border-radius: 16px; margin-top: 30px; text-align: center; border: 2px solid #dbeafe; }
        .thp-value { font-size: 36px; font-weight: 800; color: #1e293b; }
        .log-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .log-table th { background: #f8fafc; padding: 12px; text-align: left; font-size: 11px; color: #64748b; border-bottom: 1px solid #eee; }
        .log-table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 13px; }
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .btn-print { width: 100%; padding: 16px; background: #1e293b; color: white; border: none; border-radius: 12px; font-weight: 700; margin-top: 25px; cursor: pointer; }
        .detail-list { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; margin-top: 5px; font-size: 12px; }
        .detail-item { display: flex; justify-content: space-between; padding: 3px 0; color: #64748b; }
        @media print { .no-print { display: none !important; } .slip-card { border: 1px solid #000; } }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="filter-bar no-print">
            <form method="GET" style="display:flex; align-items:center; gap:10px;">
                <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="date-input">
                <span>s/d</span>
                <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="date-input">
                <button type="submit" class="btn-filter">Filter</button>
            </form>
        </div>

        <div class="slip-card">
            <div class="slip-header">
                <h2>SLIP GAJI KARYAWAN</h2>
                <p><strong><?= strtoupper($user_data['fullname']) ?></strong> | <?= $label_periode ?></p>
            </div>
            <div class="slip-body">
                <?php if ($is_anggota_borongan): ?>
                    <div style="text-align:center; padding:50px 0; color:#64748b;">
                        <i class="fa fa-users fa-4x" style="margin-bottom:20px; opacity:0.3;"></i>
                        <h3>SLIP GAJI ADA PADA MANDOR</h3>
                        <p>Rincian upah Anda dikelola melalui pembagian tim borongan.</p>
                    </div>
                <?php else: ?>
                    <div class="section-title">Penerimaan</div>
                    <?php if ($user_data['status_karyawan'] == 'Tetap'): ?>
                        <div class="row-item"><span>Gaji Pokok (Pro-Rata/Full)</span><span style="color:green;">Rp <?=number_format($gaji['gapok_total'])?></span></div>
                        <div class="row-item"><span>Uang Makan (Hak)</span><span style="color:green;">Rp <?=number_format($gaji['makan_hak'])?></span></div>
                        <div class="row-item"><span>Lembur</span><span style="color:green;">Rp <?=number_format($gaji['lembur_total_rp'])?></span></div>
                    <?php else: ?>
                        <div class="row-item"><span>Hasil Borongan</span><span style="color:green;">Rp <?=number_format($gaji['borongan_total'])?></span></div>
                        <?php if($gaji['detail_produksi']): ?>
                            <div class="detail-list">
                                <?php foreach($gaji['detail_produksi'] as $p): ?>
                                    <div class="detail-item"><span><?= $p['jenis_pekerjaan'] ?> (<?= $p['jumlah'] ?>)</span><span>Rp <?= number_format($p['total_upah']) ?></span></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="section-title">Potongan</div>
                    <?php if($gaji['um_diambil'] > 0): ?><div class="row-item"><span>Uang Makan (Cash)</span><span style="color:red;">Rp <?=number_format($gaji['um_diambil'])?></span></div><?php endif; ?>
                    <?php if($gaji['pot_um_anggota'] > 0): ?>
                        <div class="row-item"><span>Uang Makan Anggota Tim</span><span style="color:red;">Rp <?=number_format($gaji['pot_um_anggota'])?></span></div>
                        <div class="detail-list">
                            <?php foreach($gaji['detail_um_anggota'] as $da): ?>
                                <div class="detail-item"><span><?= $da['fullname'] ?></span><span>Rp <?= number_format($da['nominal']) ?></span></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="row-item"><span>Cicilan Kasbon</span><span style="color:red;">Rp <?=number_format($gaji['kasbon_total'])?></span></div>

                    <div class="thp-box">
                        <div style="font-size:12px; font-weight:700; color:#4338ca;">TOTAL DITERIMA (THP)</div>
                        <div class="thp-value">Rp <?=number_format($gaji['thp'])?></div>
                    </div>

                    <?php if ($user_data['status_karyawan'] == 'Tetap'): ?>
                        <div class="section-title no-print">Log Harian</div>
                        <table class="log-table no-print">
                            <thead><tr><th>Tanggal</th><th>Status</th><th>Keterangan</th></tr></thead>
                            <tbody>
                                <?php foreach($gaji['detail_log'] as $l): ?>
                                    <tr>
                                        <td><?= date('d/m/y', strtotime($l['tgl'])) ?></td>
                                        <td><span class="badge <?= ($l['status']=='Alpha'?'badge-danger':'badge-success') ?>"><?= $l['status'] ?></span></td>
                                        <td><?= $l['ket'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <button onclick="window.print()" class="btn-print no-print">CETAK SLIP GAJI</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>