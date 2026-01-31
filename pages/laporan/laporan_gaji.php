<?php 
require_once '../../config/database.php';
// Set Timezone
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

// --- 1. CONFIG & SETTINGS ---
$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id=1");
$aturan = mysqli_fetch_assoc($q_set);

if (!$aturan) {
    echo "<script>alert('Harap isi pengaturan jam kerja dahulu!'); window.location='../dashboard.php';</script>";
    exit;
}

$TARGET_FULL     = 420; 
$MIN_JAM_MAKAN   = 300; 
$JAM_IST_OUT     = "11:30:00";
$JAM_IST_IN      = "12:30:00";

// --- 2. FILTER PERIODE ---
$default_awal  = date('Y-m-01'); 
$default_akhir = date('Y-m-d');
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;

$period_obj = new DatePeriod(new DateTime($tgl_awal), new DateInterval('P1D'), new DateTime($tgl_akhir . ' +1 day'));

$jumlah_sabtu = 0;
foreach ($period_obj as $dt) { if ($dt->format('N') == 6) $jumlah_sabtu++; }

// --- 3. PRE-FETCH DATA ABSENSI ---
$absen_map = [];
$tgl_akhir_scan = date('Y-m-d H:i:s', strtotime($tgl_akhir . ' +1 day 06:00:00'));
$sql_absen = "SELECT pin, scan_date FROM absensi WHERE scan_date BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir_scan' ORDER BY scan_date ASC";
$q_raw = mysqli_query($conn, $sql_absen);

while($r = mysqli_fetch_assoc($q_raw)) {
    $scan_ts = strtotime($r['scan_date']);
    $tgl_aktual = date('Y-m-d', $scan_ts);
    $jam = date('H:i:s', $scan_ts);
    
    if ($jam >= "00:00:00" && $jam <= "06:00:00") {
        $tgl_idx = date('Y-m-d', strtotime($tgl_aktual . ' -1 day'));
    } else {
        $tgl_idx = $tgl_aktual;
    }
    $absen_map[$r['pin']][$tgl_idx][] = $jam;
}

// --- 4. DATA PROCESSING ---
$laporan_tetap = [];
$laporan_borongan = [];

$stats_tetap = ['gapok'=>0, 'makan'=>0, 'lembur'=>0, 'denda'=>0, 'kasbon'=>0, 'thp'=>0];
$stats_borongan = ['borongan'=>0, 'kasbon'=>0, 'thp'=>0];

$q_users = mysqli_query($conn, "
    SELECT u.id, u.pin, u.fullname, u.status_karyawan, u.is_mandor, u.group_id, u.role,
    COALESCE(g.gaji_pokok, 0) as gaji_pokok, 
    COALESCE(g.uang_makan, 0) as uang_makan, 
    COALESCE(g.gaji_lembur, 0) as gaji_lembur 
    FROM users u 
    LEFT JOIN gaji_karyawan g ON u.id = g.user_id 
    WHERE u.role IN ('user', 'kepala_bengkel') 
    ORDER BY u.fullname ASC
");

while ($u = mysqli_fetch_assoc($q_users)) {
    $uid = $u['id'];
    $pin = $u['pin'];
    
    $row = [
        'nama' => $u['fullname'], 'jenis' => $u['status_karyawan'],
        'gapok' => 0, 'makan_hak' => 0, 'lembur_duit' => 0, 'borongan' => 0,
        'telat_menit_total' => 0, 'lembur_menit_total' => 0, 
        'kasbon_duit' => 0, 'thp' => 0, 'hari_kerja' => 0, 'info_extra' => '', 
        'pot_pro_rata' => 0, 'um_diambil' => 0, 'pot_um_anggota' => 0
    ];

    if($u['role'] == 'kepala_bengkel') $row['info_extra'] = 'Kepala Bengkel';
    elseif($u['status_karyawan'] == 'Borongan') {
        if($u['is_mandor']) $row['info_extra'] = 'Mandor';
        elseif($u['group_id']) $row['info_extra'] = 'Anggota';
        else $row['info_extra'] = 'Perorangan';
    }

    // A. LOGIKA TETAP
    if ($u['status_karyawan'] === 'Tetap' || $u['role'] === 'kepala_bengkel') {
        foreach ($period_obj as $dt) {
            $curr = $dt->format('Y-m-d');
            if ($dt->format('N') == 7) continue; 
            if (isset($absen_map[$pin][$curr])) {
                $scans = $absen_map[$pin][$curr];
                $in = null; $out = null; $is_shift_siang = false;
                foreach($scans as $jam) {
                    if ($jam >= "06:00:00" && $jam <= "11:00:00") { if(!$in) $in = $jam; }
                    elseif ($jam >= "12:01:00" && $jam <= "13:30:00") { if(!$in) { $in = $jam; $is_shift_siang = true; } }
                    if ($jam >= "13:31:00" || ($jam >= "00:00:00" && $jam <= "06:00:00")) $out = $jam;
                }
                if ($in && $out) {
                    $ts_in = strtotime("$curr $in");
                    $ts_out = strtotime("$curr $out");
                    if($ts_out < $ts_in) $ts_out += 86400; 
                    $durasi_menit = ($ts_out - $ts_in) / 60;
                    if (!$is_shift_siang && $ts_in < strtotime("$curr $JAM_IST_OUT") && $ts_out > strtotime("$curr $JAM_IST_IN")) $durasi_menit -= 60;
                    $durasi_bersih = max(0, floor($durasi_menit));
                    if ($durasi_bersih >= $MIN_JAM_MAKAN) $row['makan_hak'] += $u['uang_makan'];
                    if ($durasi_bersih >= $TARGET_FULL) {
                        $row['gapok'] += $u['gaji_pokok']; $row['hari_kerja'] += 1;
                        $menit_lembur = $durasi_bersih - $TARGET_FULL;
                        if ($ts_out > strtotime("$curr 18:00:00")) $menit_lembur -= 30; 
                        if ($menit_lembur >= 30) $row['lembur_menit_total'] += $menit_lembur;
                    } else {
                        $rasio = $durasi_bersih / $TARGET_FULL;
                        $row['gapok'] += $u['gaji_pokok'] * $rasio;
                        $row['pot_pro_rata'] += ($u['gaji_pokok'] - ($u['gaji_pokok'] * $rasio));
                        $row['hari_kerja'] += $rasio;
                    }
                }
            }
        }
        $row['lembur_duit'] = ($row['lembur_menit_total'] / 60) * $u['gaji_lembur'];
    } else {
        if (!($u['group_id'] != NULL && $u['is_mandor'] == 0)) {
            $res_prod = mysqli_query($conn, "SELECT SUM(total_upah) as upah FROM produksi_borongan WHERE user_id = '$uid' AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
            $data_prod = mysqli_fetch_assoc($res_prod);
            $row['borongan'] = $data_prod['upah'] ?? 0;
        }
    }

    $kasbon_murni = 0;
    if ($jumlah_sabtu > 0) {
        $res_bon = mysqli_query($conn, "SELECT nominal, tenor, terbayar FROM kasbon WHERE user_id = '$uid' AND status = 'Approved' AND (nominal - terbayar) > 0");
        while ($b = mysqli_fetch_assoc($res_bon)) {
            $kasbon_murni += min(ceil($b['nominal'] / $b['tenor']) * $jumlah_sabtu, ($b['nominal'] - $b['terbayar']));
        }
    }

    $res_um = mysqli_query($conn, "SELECT SUM(nominal) as total FROM uang_makan WHERE user_id = '$uid' AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
    $um_diambil = mysqli_fetch_assoc($res_um)['total'] ?? 0;

    $pot_mandor = 0;
    if ($u['is_mandor'] == 1 && !empty($u['group_id'])) {
        $q_agg = mysqli_query($conn, "SELECT id FROM users WHERE group_id = '{$u['group_id']}' AND is_mandor = 0");
        $ids = []; while($a = mysqli_fetch_assoc($q_agg)) $ids[] = $a['id'];
        if(!empty($ids)) {
            $q_um_agg = mysqli_query($conn, "SELECT SUM(nominal) as total FROM uang_makan WHERE user_id IN (".implode(',',$ids).") AND status = 'Approved' AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
            $pot_mandor = mysqli_fetch_assoc($q_um_agg)['total'] ?? 0;
        }
    }

    $is_anggota = ($u['status_karyawan']=='Borongan' && $u['group_id']!=NULL && $u['is_mandor'] == 0);
    $row['kasbon_duit'] = $kasbon_murni + ($is_anggota ? 0 : $um_diambil) + $pot_mandor;
    $row['thp'] = ($row['gapok'] + $row['makan_hak'] + $row['lembur_duit'] + $row['borongan']) - $row['kasbon_duit'];

    if ($u['status_karyawan'] === 'Tetap' || $u['role'] === 'kepala_bengkel') {
        $laporan_tetap[] = $row;
        $stats_tetap['gapok'] += $row['gapok']; $stats_tetap['makan'] += $row['makan_hak'];
        $stats_tetap['lembur'] += $row['lembur_duit']; $stats_tetap['denda'] += $row['pot_pro_rata'];
        $stats_tetap['kasbon'] += $row['kasbon_duit']; $stats_tetap['thp'] += $row['thp'];
    } else {
        $laporan_borongan[] = $row;
        $stats_borongan['borongan'] += $row['borongan'];
        $stats_borongan['kasbon'] += $row['kasbon_duit']; $stats_borongan['thp'] += $row['thp'];
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
        :root { --accent-green: #10b981; --accent-red: #ef4444; --accent-blue: #3b82f6; --accent-orange: #f97316; }
        .page-header-custom { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
        .page-header-custom h1 { font-size: 24px; font-weight: 800; margin: 0; }
        .filter-bar { background: #fff; padding: 15px 20px; border-radius: 12px; border: 1px solid #e5e7eb; display: flex; gap: 10px; margin-bottom: 25px; align-items:center;}
        .date-input { border: 1px solid #e5e7eb; padding: 8px; border-radius: 8px; font-size:13px; }
        .btn-apply { background: #1f2937; color: #fff; padding: 8px 20px; border-radius: 8px; cursor: pointer; border:none;}
        .table-custom { width: 100%; border-collapse: collapse; background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb; margin-bottom: 40px; }
        .table-custom th { background: #f9fafb; padding: 12px; font-size: 10px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .table-custom td { padding: 12px; border-bottom: 1px solid #f3f4f6; font-size: 12px; text-align:center; }
        .table-custom tfoot td { background: #f8fafc; font-weight: 800; border-top: 2px solid #e5e7eb; color: #374151; }
        .font-mono { font-family: 'Courier Prime', monospace; font-weight: 700; }
        .val-plus { color: #15803d; } .val-min { color: #b91c1c; } .val-thp { color: #2563eb; font-weight: 800; }
        .section-divider { border-left: 4px solid #1f2937; padding-left: 15px; margin-bottom: 15px; font-size: 16px; font-weight: 800; color: #1f2937; }
        @media print { .no-print { display: none !important; } .table-custom th, .table-custom td { border: 1px solid #000; } @page { size: landscape; } }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="laporan-wrapper">
            <div class="page-header-custom no-print">
                <div>
                    <h1>Laporan Penggajian</h1>
                    <p style="color:#64748b;">Periode: <b><?= date('d M Y', strtotime($tgl_awal)) ?></b> - <b><?= date('d M Y', strtotime($tgl_akhir)) ?></b></p>
                </div>
                <button onclick="window.print()" class="btn-apply">Cetak PDF</button>
            </div>

            <div class="filter-bar no-print">
                <form method="GET" style="display:flex; gap:10px;">
                    <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="date-input">
                    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="date-input">
                    <button type="submit" class="btn-apply">Filter</button>
                </form>
            </div>

            <div class="section-divider">KARYAWAN TETAP & HARIAN</div>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="text-align:left;">Nama Karyawan</th>
                        <th>Status</th>
                        <th>Gaji Pokok</th>
                        <th>Makan</th>
                        <th>Lembur</th>
                        <th>Pot. Pro-Rata</th>
                        <th>Kasbon/UM</th>
                        <th>THP FINAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($laporan_tetap as $r): ?>
                    <tr>
                        <td style="text-align:left; font-weight:700;"><?= $r['nama'] ?></td>
                        <td><small><?= $r['jenis'] ?><br><?= $r['info_extra'] ?></small></td>
                        <td class="font-mono">Rp <?= number_format($r['gapok']) ?></td>
                        <td class="font-mono">Rp <?= number_format($r['makan_hak']) ?></td>
                        <td class="font-mono val-plus">+<?= number_format($r['lembur_duit']) ?></td>
                        <td class="font-mono val-min">-<?= number_format($r['pot_pro_rata']) ?></td>
                        <td class="font-mono val-min">-<?= number_format($r['kasbon_duit']) ?></td>
                        <td class="font-mono val-thp">Rp <?= number_format($r['thp']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:right;">SUBTOTAL TETAP</td>
                        <td class="font-mono">Rp <?= number_format($stats_tetap['gapok']) ?></td>
                        <td class="font-mono">Rp <?= number_format($stats_tetap['makan']) ?></td>
                        <td class="font-mono val-plus">+<?= number_format($stats_tetap['lembur']) ?></td>
                        <td class="font-mono val-min">-<?= number_format($stats_tetap['denda']) ?></td>
                        <td class="font-mono val-min">-<?= number_format($stats_tetap['kasbon']) ?></td>
                        <td class="font-mono val-thp">Rp <?= number_format($stats_tetap['thp']) ?></td>
                    </tr>
                </tfoot>
            </table>

            <div class="section-divider">KARYAWAN BORONGAN</div>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="text-align:left;">Nama Karyawan</th>
                        <th>Status</th>
                        <th>Upah Borongan</th>
                        <th>Kasbon/UM</th>
                        <th>THP FINAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($laporan_borongan as $r): ?>
                    <tr>
                        <td style="text-align:left; font-weight:700;"><?= $r['nama'] ?></td>
                        <td><small><?= $r['jenis'] ?><br><?= $r['info_extra'] ?></small></td>
                        <td class="font-mono val-plus">Rp <?= number_format($r['borongan']) ?></td>
                        <td class="font-mono val-min">-<?= number_format($r['kasbon_duit']) ?></td>
                        <td class="font-mono val-thp">Rp <?= number_format($r['thp']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:right;">SUBTOTAL BORONGAN</td>
                        <td class="font-mono">Rp <?= number_format($stats_borongan['borongan']) ?></td>
                        <td class="font-mono val-min">-<?= number_format($stats_borongan['kasbon']) ?></td>
                        <td class="font-mono val-thp">Rp <?= number_format($stats_borongan['thp']) ?></td>
                    </tr>
                </tfoot>
            </table>
            
            <div style="background: #1f2937; color: #fff; padding: 20px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 800; font-size: 18px;">GRAND TOTAL PENGELUARAN GAJI</span>
                <span class="font-mono" style="font-size: 24px;">Rp <?= number_format($stats_tetap['thp'] + $stats_borongan['thp']) ?></span>
            </div>
        </div>
    </div>
    <?php include '../../layout/footer.php'; ?>
</body>
</html>