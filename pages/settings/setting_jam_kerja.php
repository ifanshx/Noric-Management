<?php 
require_once '../../config/database.php';
require_once '../../config/function.php';
cek_login(); 

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

// --- 1. PROSES SIMPAN DATA ---
if (isset($_POST['simpan_aturan'])) {
    // A. Sanitasi Input Waktu Utama
    $masuk   = $_POST['jam_masuk'] . ':00';
    $pulang  = $_POST['jam_pulang'] . ':00';
    
    // B. Sanitasi Range Scan (Masuk & Pulang)
    $r_in_start  = $_POST['range_masuk_start'] . ':00';
    $r_in_end    = $_POST['range_masuk_end'] . ':00';
    $r_plg_start = $_POST['range_pulang_start'] . ':00';
    $r_plg_end   = $_POST['range_pulang_end'] . ':00';

    // C. Sanitasi Range Scan (Istirahat - BARU DITAMBAHKAN)
    $r_ist_out_start = $_POST['range_ist_out_start'] . ':00';
    $r_ist_out_end   = $_POST['range_ist_out_end'] . ':00';
    $r_ist_in_start  = $_POST['range_ist_in_start'] . ':00';
    $r_ist_in_end    = $_POST['range_ist_in_end'] . ':00';

    // D. Sanitasi Angka (Target & Sanksi)
    $tol_telat   = abs((int)$_POST['toleransi_telat']);
    $tol_pulang  = abs((int)$_POST['toleransi_pulang_awal']);
    $denda_menit = preg_replace('/[^0-9]/', '', $_POST['denda_per_menit']); 
    
    $target_full  = abs((int)$_POST['target_menit_full']); 
    $target_half  = abs((int)$_POST['target_menit_half']); 
    $tol_full_day = abs((int)$_POST['toleransi_full_day']); 
    $min_makan    = abs((int)$_POST['min_menit_makan']); 

    $lembur_min  = abs((int)$_POST['lembur_min']);
    $lembur_max  = abs((int)$_POST['lembur_max']);
    $lembur_pot  = abs((int)$_POST['lembur_pengurang']);

    // Update Query Lengkap
    $sql = "UPDATE settings_jam_kerja SET
            jam_masuk = '$masuk', 
            jam_pulang = '$pulang',
            
            range_masuk_start = '$r_in_start', 
            range_masuk_end = '$r_in_end',
            
            range_ist_out_start = '$r_ist_out_start',
            range_ist_out_end = '$r_ist_out_end',
            range_ist_in_start = '$r_ist_in_start',
            range_ist_in_end = '$r_ist_in_end',

            range_pulang_start = '$r_plg_start', 
            range_pulang_end = '$r_plg_end',

            toleransi_telat = '$tol_telat', 
            toleransi_pulang_awal = '$tol_pulang', 
            denda_per_menit = '$denda_menit',
            target_menit_full = '$target_full', 
            target_menit_half = '$target_half',
            toleransi_full_day = '$tol_full_day', 
            min_menit_makan = '$min_makan',
            lembur_min = '$lembur_min', 
            lembur_max = '$lembur_max', 
            lembur_pengurang = '$lembur_pot'
            WHERE id = 1";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Pengaturan Logic, Waktu & Range Istirahat berhasil diperbarui!'); window.location='setting_jam_kerja.php';</script>";
    } else {
        echo "<script>alert('Error: ".mysqli_error($conn)."');</script>";
    }
}

// --- 2. AMBIL DATA ---
$q_set = mysqli_query($conn, "SELECT * FROM settings_jam_kerja WHERE id = 1");
$d = mysqli_fetch_assoc($q_set);

// Default Value Safety (Jika DB masih NULL)
if(!isset($d['range_masuk_start'])) $d['range_masuk_start'] = '04:00:00';
if(!isset($d['range_ist_out_start'])) $d['range_ist_out_start'] = '11:00:00';
if(!isset($d['range_ist_in_start'])) $d['range_ist_in_start'] = '12:30:00';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <style>
        body { background-color: #f4f6f9; font-family: 'Poppins', sans-serif; }
        .content-wrapper { padding: 30px; }
        .modern-card { background: #fff; border-radius: 16px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.03); margin-bottom: 25px; }
        .card-header-white { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; }
        .card-title { font-weight: 700; color: #1e293b; margin: 0; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px; }
        .card-body { padding: 25px; }
        .form-group label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px; display: block; }
        .form-control-custom { height: 45px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; padding-left: 15px; width: 100%; transition: 0.3s; }
        .form-control-custom:focus { border-color: #4f46e5; outline: none; }
        .section-sep { border-top: 1px dashed #cbd5e1; margin: 25px 0; position: relative; }
        .section-label { position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #fff; padding: 0 10px; color: #94a3b8; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .alert-info-custom { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 15px; border-radius: 10px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .input-group { display: flex; align-items: center; }
        .input-group-addon { padding: 0 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-right: none; height: 45px; display: flex; align-items: center; border-radius: 8px 0 0 8px; font-weight: bold; color: #64748b; }
        .bg-focus { background-color: #f0fdf4; border-color: #86efac; font-weight: 700; color: #166534; }
        .bg-warning-light { background-color: #fffbeb; border-color: #fcd34d; font-weight: 700; color: #92400e; }
        .range-box { background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 15px; }
        .range-title { font-size: 12px; font-weight: 800; color: #334155; margin-bottom: 10px; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; display:block; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <h2 style="font-weight:800; color:#1e293b; margin-bottom: 10px;">Konfigurasi Sistem</h2>
        <div class="alert-info-custom">
            <i class="fa fa-info-circle" style="font-size: 18px;"></i>
            <span>Sistem menggunakan <b>Time-Based Bucketing</b>. Pastikan range jam tidak saling tumpang tindih.</span>
        </div>

        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="modern-card">
                        <div class="card-header-white"><h5 class="card-title"><i class="fa fa-clock-o text-primary"></i> Jadwal & Logika Scan</h5></div>
                        <div class="card-body">
                            
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Jam Masuk (Pagi)</label>
                                    <input type="time" name="jam_masuk" class="form-control-custom" value="<?=substr($d['jam_masuk'],0,5)?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Jam Pulang (Sore)</label>
                                    <input type="time" name="jam_pulang" class="form-control-custom" value="<?=substr($d['jam_pulang'],0,5)?>">
                                </div>
                            </div>

                            <div class="section-sep"><span class="section-label">Batasan Waktu Scan (Auto Detect)</span></div>
                            
                            <div class="range-box">
                                <span class="range-title text-success">1. SCAN MASUK</span>
                                <div class="row">
                                    <div class="col-6">
                                        <label>Mulai Scan</label>
                                        <input type="time" name="range_masuk_start" class="form-control-custom" value="<?=substr($d['range_masuk_start'],0,5)?>">
                                    </div>
                                    <div class="col-6">
                                        <label>Batas Akhir</label>
                                        <input type="time" name="range_masuk_end" class="form-control-custom" value="<?=substr($d['range_masuk_end'],0,5)?>">
                                    </div>
                                </div>
                            </div>

                            <div class="range-box" style="background:#fff7ed; border-color:#ffedd5;">
                                <span class="range-title text-warning" style="border-color:#ffedd5;">2. SCAN ISTIRAHAT KELUAR (OUT)</span>
                                <div class="row">
                                    <div class="col-6">
                                        <label>Mulai Scan</label>
                                        <input type="time" name="range_ist_out_start" class="form-control-custom" value="<?=substr($d['range_ist_out_start'],0,5)?>">
                                    </div>
                                    <div class="col-6">
                                        <label>Batas Akhir</label>
                                        <input type="time" name="range_ist_out_end" class="form-control-custom" value="<?=substr($d['range_ist_out_end'],0,5)?>">
                                    </div>
                                </div>
                            </div>

                            <div class="range-box" style="background:#fff7ed; border-color:#ffedd5;">
                                <span class="range-title text-warning" style="border-color:#ffedd5;">3. SCAN ISTIRAHAT MASUK (IN)</span>
                                <div class="row">
                                    <div class="col-6">
                                        <label>Mulai Scan</label>
                                        <input type="time" name="range_ist_in_start" class="form-control-custom" value="<?=substr($d['range_ist_in_start'],0,5)?>">
                                    </div>
                                    <div class="col-6">
                                        <label>Batas Akhir</label>
                                        <input type="time" name="range_ist_in_end" class="form-control-custom" value="<?=substr($d['range_ist_in_end'],0,5)?>">
                                    </div>
                                </div>
                            </div>

                            <div class="range-box">
                                <span class="range-title text-danger">4. SCAN PULANG</span>
                                <div class="row">
                                    <div class="col-6">
                                        <label>Mulai Scan</label>
                                        <input type="time" name="range_pulang_start" class="form-control-custom" value="<?=substr($d['range_pulang_start'],0,5)?>">
                                    </div>
                                    <div class="col-6">
                                        <label>Batas Akhir</label>
                                        <input type="time" name="range_pulang_end" class="form-control-custom" value="<?=substr($d['range_pulang_end'],0,5)?>">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="modern-card">
                        <div class="card-header-white"><h5 class="card-title"><i class="fa fa-gavel text-danger"></i> Target & Sanksi</h5></div>
                        <div class="card-body">
                            
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="text-success">Target Full Day (Menit)</label>
                                    <input type="number" name="target_menit_full" class="form-control-custom bg-focus" value="<?=$d['target_menit_full']?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Toleransi Full Day (Menit)</label>
                                    <input type="number" name="toleransi_full_day" class="form-control-custom" value="<?=$d['toleransi_full_day']?>">
                                </div>
                            </div>

                            <div class="row" style="margin-top:10px;">
                                <div class="col-md-6 form-group">
                                    <label>Target Half Day (Menit)</label>
                                    <input type="number" name="target_menit_half" class="form-control-custom" value="<?=$d['target_menit_half']?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="text-warning" style="color:#d97706;">Syarat Makan (Menit)</label>
                                    <input type="number" name="min_menit_makan" class="form-control-custom bg-warning-light" value="<?=$d['min_menit_makan']?>">
                                </div>
                            </div>

                            <div class="section-sep"><span class="section-label">Denda & Lembur</span></div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Tol. Terlambat (Mnt)</label>
                                    <input type="number" name="toleransi_telat" class="form-control-custom" value="<?=$d['toleransi_telat']?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Tol. Pulang Awal (Mnt)</label>
                                    <input type="number" name="toleransi_pulang_awal" class="form-control-custom" value="<?=$d['toleransi_pulang_awal']?>">
                                </div>
                            </div>

                            <div class="form-group" style="margin-top:15px;">
                                <label class="text-danger">Denda Per Menit (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input type="text" name="denda_per_menit" class="form-control-custom" value="<?=$d['denda_per_menit']?>">
                                </div>
                            </div>

                            <div class="row" style="margin-top:15px;">
                                <div class="col-md-4 form-group"><label>Min. Lembur</label><input type="number" name="lembur_min" class="form-control-custom" value="<?=$d['lembur_min']?>"></div>
                                <div class="col-md-4 form-group"><label>Max. Lembur</label><input type="number" name="lembur_max" class="form-control-custom" value="<?=$d['lembur_max']?>"></div>
                                <div class="col-md-4 form-group"><label>Pot. Maghrib</label><input type="number" name="lembur_pengurang" class="form-control-custom" value="<?=$d['lembur_pengurang']?>"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button type="submit" name="simpan_aturan" class="btn btn-primary btn-lg btn-block" style="width:100%; border-radius:12px; font-weight:800; padding:15px; box-shadow:0 4px 15px rgba(59, 130, 246, 0.4); border:none; transition: all 0.3s;">
                        <i class="fa fa-save"></i> SIMPAN PERUBAHAN SISTEM
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <?php include '../../layout/footer.php'; ?>
</body>
</html>