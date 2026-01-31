<?php 
require_once '../../config/database.php';

// 1. SET TIMEZONE REALTIME (WIB)
date_default_timezone_set('Asia/Jakarta');

cek_login(); 

// --- KONFIGURASI KEAMANAN ---
$today = date('Y-m-d');
$tgl_filter = isset($_GET['tgl']) ? $_GET['tgl'] : $today;

// Cek apakah tanggal yang dibuka adalah masa lalu?
$is_locked = ($tgl_filter < $today);

$edit_mode = false;
$data_edit = null;

// --- 1. PROSES HAPUS ---
if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Validasi: Cek tanggal data sebelum hapus
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT tanggal FROM transaksi_kas WHERE id='$id'"));
    
    if($cek['tanggal'] < $today) {
        echo "<script>alert('PELANGGARAN: Data masa lalu tidak dapat dihapus demi keamanan!'); window.location='kas.php?tgl=$tgl_filter';</script>";
    } else {
        $del = mysqli_query($conn, "DELETE FROM transaksi_kas WHERE id='$id'");
        if($del) {
            echo "<script>
                setTimeout(function() {
                    Swal.fire({title: 'Terhapus!', text: 'Data berhasil dihapus.', icon: 'success', timer: 1000, showConfirmButton: false})
                    .then(() => { window.location='kas.php?tgl=$tgl_filter'; });
                }, 100);
            </script>";
        }
    }
}

// --- 2. PROSES AMBIL DATA EDIT ---
if(isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $q_edit = mysqli_query($conn, "SELECT * FROM transaksi_kas WHERE id='$id_edit'");
    if(mysqli_num_rows($q_edit) > 0) {
        $data_edit = mysqli_fetch_assoc($q_edit);
        
        if($data_edit['tanggal'] < $today) {
             echo "<script>alert('Data ini sudah terkunci dan tidak bisa diedit!'); window.location='kas.php?tgl=$tgl_filter';</script>";
        } else {
            $edit_mode = true;
            $tgl_filter = $data_edit['tanggal']; 
        }
    }
}

// --- 3. PROSES SIMPAN (INSERT / UPDATE) ---
if(isset($_POST['simpan_transaksi'])) {
    $uid = $_SESSION['user_id'];
    $tgl = $_POST['tanggal'];
    $jenis = $_POST['jenis'];
    $metode = $_POST['metode']; 
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $nom = str_replace('.', '', $_POST['nominal']);
    
    // VALIDASI KEAMANAN
    if($tgl < $today) {
        echo "<script>
            setTimeout(function() {
                Swal.fire({title: 'Ditolak!', text: 'Tidak dapat mencatat transaksi mundur (Backdate).', icon: 'error'});
            }, 100);
        </script>";
    } 
    elseif($nom > 0) {
        if(isset($_POST['id_transaksi']) && !empty($_POST['id_transaksi'])) {
            // UPDATE
            $id_trx = $_POST['id_transaksi'];
            $cek_asal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT tanggal FROM transaksi_kas WHERE id='$id_trx'"));
            if($cek_asal['tanggal'] < $today) {
                die("Akses Ilegal: Mencoba mengedit data terkunci.");
            }

            $sql = "UPDATE transaksi_kas SET tanggal='$tgl', jenis='$jenis', metode='$metode', keterangan='$ket', nominal='$nom' WHERE id='$id_trx'";
            $msg = "Data berhasil diperbarui!";
        } else {
            // INSERT
            $sql = "INSERT INTO transaksi_kas (user_id, tanggal, jenis, metode, keterangan, nominal) VALUES ('$uid', '$tgl', '$jenis', '$metode', '$ket', '$nom')";
            $msg = "Data berhasil ditambahkan!";
        }

        if(mysqli_query($conn, $sql)) {
            echo "<script>
                setTimeout(function() {
                    Swal.fire({title: 'Berhasil!', text: '$msg', icon: 'success', timer: 1000, showConfirmButton: false})
                    .then(() => { window.location='kas.php?tgl=$tgl'; });
                }, 100);
            </script>";
        }
    }
}

// --- LOGIK PERHITUNGAN SALDO ---

// 1. Saldo Awal Global
$q_awal = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN jenis='Masuk' THEN nominal ELSE 0 END) as tot_masuk,
    SUM(CASE WHEN jenis='Keluar' THEN nominal ELSE 0 END) as tot_keluar
    FROM transaksi_kas WHERE tanggal < '$tgl_filter'");
$d_awal = mysqli_fetch_assoc($q_awal);
$saldo_awal = $d_awal['tot_masuk'] - $d_awal['tot_keluar'];

// 2. Transaksi Hari Ini Global
$q_today = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN jenis='Masuk' THEN nominal ELSE 0 END) as tot_masuk,
    SUM(CASE WHEN jenis='Keluar' THEN nominal ELSE 0 END) as tot_keluar
    FROM transaksi_kas WHERE tanggal = '$tgl_filter'");
$d_today = mysqli_fetch_assoc($q_today);
$masuk_hari_ini = $d_today['tot_masuk'];
$keluar_hari_ini = $d_today['tot_keluar'];

// 3. Saldo Akhir Global
$saldo_akhir = $saldo_awal + $masuk_hari_ini - $keluar_hari_ini;

// 4. Rincian Saldo (Cash vs ATM) s/d Hari Ini
$q_rincian = mysqli_query($conn, "SELECT 
    (SUM(CASE WHEN jenis='Masuk' AND metode='Cash' THEN nominal ELSE 0 END) - 
     SUM(CASE WHEN jenis='Keluar' AND metode='Cash' THEN nominal ELSE 0 END)) as sisa_cash,
    (SUM(CASE WHEN jenis='Masuk' AND metode='ATM' THEN nominal ELSE 0 END) - 
     SUM(CASE WHEN jenis='Keluar' AND metode='ATM' THEN nominal ELSE 0 END)) as sisa_atm
    FROM transaksi_kas WHERE tanggal <= '$tgl_filter'");
$d_rincian = mysqli_fetch_assoc($q_rincian);
$sisa_cash = $d_rincian['sisa_cash'] ?? 0;
$sisa_atm = $d_rincian['sisa_atm'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">

    <style>
        /* --- UI VARIABLES & GLOBAL --- */
        :root { 
            --accent-green: #10b981; --accent-red: #ef4444; 
            --accent-blue: #3b82f6; --accent-orange: #f97316; 
            --accent-purple: #8b5cf6; --accent-dark: #1e293b;
        }
        body { background-color: #f3f4f6; font-family: 'Inter', sans-serif; color:#1f2937; }
        .content-wrapper { padding: 30px; }

        /* --- HEADER & CLOCK --- */
        .header-wrapper { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        .page-title h3 { margin: 0; font-weight: 800; color: #111827; font-size: 24px; }
        .page-title p { margin: 5px 0 0; color: #6b7280; font-size: 14px; }
        
        .live-clock-widget { text-align: right; }
        .live-clock-time { font-size: 28px; font-weight: 800; color: var(--accent-blue); letter-spacing: -1px; line-height: 1; font-family: 'Courier Prime', monospace; }
        .live-clock-date { font-size: 12px; color: #64748b; font-weight: 600; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px; }

        .date-filter-box { background: #fff; padding: 5px 15px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: inline-flex; align-items: center; border: 1px solid #e5e7eb; margin-top: 10px; }
        .date-filter-box input { border: none; background: transparent; font-weight: 700; color: var(--accent-blue); font-size: 14px; outline: none; cursor: pointer; }

        /* --- STATS GRID --- */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; border-radius: 16px; padding: 20px; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #f3f4f6; }
        
        /* Variants */
        .stat-card.dark { background: linear-gradient(145deg, #1e293b, #0f172a); color: white; }
        .stat-card.green { border-left: 4px solid var(--accent-green); }
        .stat-card.red { border-left: 4px solid var(--accent-red); }
        .stat-card.blue { background: linear-gradient(145deg, #3b82f6, #2563eb); color: white; }
        .stat-card.locked { background: #e2e8f0; color: #64748b; opacity: 0.8; }

        .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; opacity: 0.9; margin-bottom: 5px; }
        .stat-value { font-size: 22px; font-weight: 700; font-family: 'Courier Prime', monospace; margin: 0; }
        .stat-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 32px; opacity: 0.15; }

        /* Detail Saldo (Cash/ATM) */
        .asset-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .asset-card { padding: 15px 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .asset-card.cash { background: linear-gradient(135deg, #f97316, #ea580c); }
        .asset-card.atm { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .asset-info h5 { margin: 0; font-size: 11px; text-transform: uppercase; font-weight: 700; opacity: 0.9; }
        .asset-info h3 { margin: 5px 0 0; font-size: 20px; font-family: 'Courier Prime', monospace; }

        /* --- FORM & TABLE --- */
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: 20px; }
        .card-header { padding: 15px 20px; border-bottom: 1px solid #f3f4f6; font-weight: 700; color: #374151; display: flex; align-items: center; justify-content: space-between; background: #f9fafb; }
        .card-body { padding: 20px; }

        /* Form Elements */
        .form-group label { font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; display: block; margin-bottom: 5px; }
        .form-input { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; outline: none; transition: 0.2s; }
        .form-input:focus { border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .form-input-money { font-family: 'Courier Prime', monospace; font-weight: 700; font-size: 16px; color: #111827; }

        /* Radio Buttons Custom */
        .radio-wrapper { display: flex; gap: 10px; }
        .radio-label { flex: 1; position: relative; cursor: pointer; }
        .radio-label input { position: absolute; opacity: 0; }
        .radio-box { display: flex; align-items: center; justify-content: center; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; font-weight: 600; color: #6b7280; transition: 0.2s; gap: 5px; }
        
        /* Logic Warna Radio */
        .radio-label input:checked + .radio-box.in { background: #ecfdf5; border-color: var(--accent-green); color: var(--accent-green); }
        .radio-label input:checked + .radio-box.out { background: #fef2f2; border-color: var(--accent-red); color: var(--accent-red); }
        .radio-label input:checked + .radio-box.cash { background: #fff7ed; border-color: var(--accent-orange); color: var(--accent-orange); }
        .radio-label input:checked + .radio-box.atm { background: #f5f3ff; border-color: var(--accent-purple); color: var(--accent-purple); }

        /* Table Style */
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { background: #f9fafb; text-align: left; padding: 15px; font-size: 11px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .table-custom td { padding: 15px; border-bottom: 1px solid #f3f4f6; font-size: 13px; color: #374151; vertical-align: middle; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        /* Badges & Colors */
        .font-mono { font-family: 'Courier Prime', monospace; font-weight: 600; }
        .text-green { color: var(--accent-green); }
        .text-red { color: var(--accent-red); }
        
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; border: 1px solid transparent; }
        .badge.cash { background: #fff7ed; color: #9a3412; border-color: #ffedd5; }
        .badge.atm { background: #f5f3ff; color: #5b21b6; border-color: #ede9fe; }
        .badge.sys { background: #f1f5f9; color: #475569; }

        /* Locked State */
        .locked-placeholder { text-align: center; padding: 40px 20px; color: #94a3b8; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; }

        /* Responsive */
        @media (max-width: 992px) { .stats-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 768px) { 
            .stats-grid { grid-template-columns: 1fr; }
            .asset-grid { grid-template-columns: 1fr; }
            .header-wrapper { flex-direction: column; align-items: flex-start; gap: 15px; }
            .live-clock-widget { text-align: left; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        
        <div class="header-wrapper">
            <div class="page-title">
                <h3>Buku Kas Harian</h3>
                <p>
                    <?php if($is_locked): ?>
                        <span class="badge" style="background:#e2e8f0; color:#475569;"><i class="fa fa-lock"></i> ARSIP (READ ONLY)</span>
                    <?php else: ?>
                        <span class="badge" style="background:#dcfce7; color:#166534;"><i class="fa fa-check-circle"></i> AKTIF</span>
                    <?php endif; ?>
                    &nbsp; Kelola arus kas masuk dan keluar.
                </p>
            </div>
            <div class="live-clock-widget">
                <div id="live-clock" class="live-clock-time"><?php echo date('H:i:s'); ?></div>
                <div id="live-date" class="live-clock-date"><?php echo date('l, d F Y'); ?></div>
                
                <form method="GET" style="display:inline-block;">
                    <div class="date-filter-box">
                        <span style="font-size:11px; color:#9ca3af; margin-right:5px; font-weight:700;">TANGGAL:</span>
                        <input type="date" name="tgl" value="<?php echo $tgl_filter; ?>" onchange="this.form.submit()">
                    </div>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card <?php echo $is_locked ? 'locked' : 'dark'; ?>">
                <div class="stat-label">Saldo Awal Hari Ini</div>
                <div class="stat-value">Rp <?php echo number_format($saldo_awal); ?></div>
                <i class="fa fa-history stat-icon"></i>
            </div>
            <div class="stat-card <?php echo $is_locked ? 'locked' : 'green'; ?>">
                <div class="stat-label" style="color:<?php echo $is_locked ? '' : 'var(--accent-green)'; ?>">Pemasukan Global</div>
                <div class="stat-value" style="color:<?php echo $is_locked ? '' : 'var(--accent-green)'; ?>">+ <?php echo number_format($masuk_hari_ini); ?></div>
                <i class="fa fa-arrow-down stat-icon"></i>
            </div>
            <div class="stat-card <?php echo $is_locked ? 'locked' : 'red'; ?>">
                <div class="stat-label" style="color:<?php echo $is_locked ? '' : 'var(--accent-red)'; ?>">Pengeluaran Global</div>
                <div class="stat-value" style="color:<?php echo $is_locked ? '' : 'var(--accent-red)'; ?>">- <?php echo number_format($keluar_hari_ini); ?></div>
                <i class="fa fa-arrow-up stat-icon"></i>
            </div>
            <div class="stat-card <?php echo $is_locked ? 'locked' : 'blue'; ?>">
                <div class="stat-label" style="color: white;">Total Saldo Akhir</div>
                <div class="stat-value" style="color: white;">Rp <?php echo number_format($saldo_akhir); ?></div>
                <i class="fa fa-wallet stat-icon" style="color:white; opacity:0.2;"></i>
            </div>
        </div>

        <div class="asset-grid">
            <div class="asset-card cash">
                <div class="asset-info">
                    <h5>DOMPET TUNAI (CASH)</h5>
                    <h3>Rp <?php echo number_format($sisa_cash); ?></h3>
                </div>
                <i class="fa fa-money" style="font-size:35px; opacity:0.3;"></i>
            </div>
            <div class="asset-card atm">
                <div class="asset-info">
                    <h5>REKENING BANK (ATM)</h5>
                    <h3>Rp <?php echo number_format($sisa_atm); ?></h3>
                </div>
                <i class="fa fa-credit-card" style="font-size:35px; opacity:0.3;"></i>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?php if($is_locked): ?>
                    <div class="locked-placeholder">
                        <i class="fa fa-lock fa-3x" style="margin-bottom:15px; color:#cbd5e1;"></i>
                        <h4 style="margin:0; font-weight:700; color:#64748b;">Buku Terkunci</h4>
                        <p style="font-size:13px; margin-top:5px;">Anda sedang melihat arsip lama.<br>Data tidak dapat diubah.</p>
                        <a href="kas.php" class="btn btn-primary btn-sm" style="margin-top:15px;">Ke Hari Ini</a>
                    </div>
                <?php else: ?>
                    <div class="modern-card">
                        <div class="card-header">
                            <span><i class="fa <?php echo $edit_mode ? 'fa-pencil' : 'fa-plus-circle'; ?>"></i> &nbsp; <?php echo $edit_mode ? 'Edit Transaksi' : 'Input Baru'; ?></span>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="id_transaksi" value="<?php echo $edit_mode ? $data_edit['id'] : ''; ?>">
                                
                                <div class="form-group" style="margin-bottom:15px;">
                                    <label>Tanggal</label>
                                    <input type="date" name="tanggal" class="form-input" style="background:#f9fafb;" 
                                           value="<?php echo $edit_mode ? $data_edit['tanggal'] : $tgl_filter; ?>" readonly>
                                </div>

                                <div class="form-group" style="margin-bottom:15px;">
                                    <label>Arus Dana</label>
                                    <div class="radio-wrapper">
                                        <label class="radio-label">
                                            <input type="radio" name="jenis" value="Masuk" <?php echo ($edit_mode && $data_edit['jenis']=='Masuk') ? 'checked' : ''; ?> required>
                                            <div class="radio-box in"><i class="fa fa-arrow-down"></i> Masuk</div>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="jenis" value="Keluar" <?php echo ($edit_mode && $data_edit['jenis']=='Keluar') ? 'checked' : ''; ?> required>
                                            <div class="radio-box out"><i class="fa fa-arrow-up"></i> Keluar</div>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom:15px;">
                                    <label>Sumber Dana</label>
                                    <div class="radio-wrapper">
                                        <label class="radio-label">
                                            <input type="radio" name="metode" value="Cash" <?php echo ($edit_mode && $data_edit['metode']=='Cash') ? 'checked' : ''; ?> required>
                                            <div class="radio-box cash"><i class="fa fa-money"></i> Cash</div>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="metode" value="ATM" <?php echo ($edit_mode && $data_edit['metode']=='ATM') ? 'checked' : ''; ?> required>
                                            <div class="radio-box atm"><i class="fa fa-credit-card"></i> ATM</div>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom:15px;">
                                    <label>Nominal (Rp)</label>
                                    <input type="text" name="nominal" id="nominal_input" class="form-input form-input-money" placeholder="0" 
                                           value="<?php echo $edit_mode ? number_format($data_edit['nominal'],0,',','.') : ''; ?>" 
                                           required autocomplete="off">
                                </div>

                                <div class="form-group" style="margin-bottom:25px;">
                                    <label>Keterangan</label>
                                    <textarea name="keterangan" class="form-input" rows="3" placeholder="Contoh: Beli pulsa, Tarik tunai..." required><?php echo $edit_mode ? $data_edit['keterangan'] : ''; ?></textarea>
                                </div>

                                <?php if($edit_mode): ?>
                                    <div style="display:flex; gap:10px;">
                                        <a href="kas.php" class="btn btn-default" style="flex:1; padding:10px; text-align:center; border:1px solid #d1d5db; border-radius:8px; color:#374151;">Batal</a>
                                        <button type="submit" name="simpan_transaksi" class="btn btn-warning" style="flex:1; padding:10px; border-radius:8px; font-weight:600; border:none; color:white; background:#f59e0b;">Update</button>
                                    </div>
                                <?php else: ?>
                                    <button type="submit" name="simpan_transaksi" class="btn btn-primary" style="width:100%; padding:12px; border-radius:8px; font-weight:700; border:none; background:var(--accent-blue); color:white;">
                                        <i class="fa fa-save"></i> SIMPAN TRANSAKSI
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-8">
                <div class="modern-card">
                    <div class="card-header">
                        <span><i class="fa fa-list-alt text-muted"></i> Mutasi: <?php echo date('d F Y', strtotime($tgl_filter)); ?></span>
                    </div>
                    <div class="card-body p-0" style="padding:0;">
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Keterangan</th>
                                        <th class="text-center" width="10%">Metode</th>
                                        <th class="text-right" width="20%">Nominal</th>
                                        <th class="text-right" width="20%">Saldo Global</th>
                                        <th class="text-center" width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background:#fdfdfe;">
                                        <td style="border-left: 3px solid #1e293b;"><b><i class="fa fa-circle" style="font-size:8px; vertical-align:middle;"></i> SALDO AWAL</b></td>
                                        <td class="text-center"><span class="badge sys">SYSTEM</span></td>
                                        <td class="text-right text-muted font-mono">-</td>
                                        <td class="text-right font-mono"><b><?php echo number_format($saldo_awal); ?></b></td>
                                        <td></td>
                                    </tr>

                                    <?php
                                    $q_kas = mysqli_query($conn, "SELECT * FROM transaksi_kas WHERE tanggal = '$tgl_filter' ORDER BY created_at ASC");
                                    $running_balance = $saldo_awal;
                                    
                                    if(mysqli_num_rows($q_kas) == 0) {
                                        echo "<tr><td colspan='5' class='text-center text-muted' style='padding:30px;'>Belum ada transaksi hari ini.</td></tr>";
                                    }

                                    while($d = mysqli_fetch_assoc($q_kas)) {
                                        $badge_metode = ($d['metode'] == 'ATM') ? 'badge atm' : 'badge cash';
                                        
                                        if($d['jenis'] == 'Masuk') {
                                            $nom_disp = "+ ".number_format($d['nominal']);
                                            $cls_nom = "text-green";
                                            $running_balance += $d['nominal'];
                                        } else {
                                            $nom_disp = "- ".number_format($d['nominal']);
                                            $cls_nom = "text-red";
                                            $running_balance -= $d['nominal'];
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <span style="font-weight:600; display:block;"><?php echo $d['keterangan']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="<?php echo $badge_metode; ?>"><?php echo $d['metode']; ?></span>
                                        </td>
                                        <td class="text-right font-mono <?php echo $cls_nom; ?>">
                                            <?php echo $nom_disp; ?>
                                        </td>
                                        <td class="text-right font-mono" style="color:var(--accent-blue);">
                                            <?php echo number_format($running_balance); ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if(!$is_locked): ?>
                                                <div class="btn-group">
                                                    <a href="kas.php?edit=<?php echo $d['id']; ?>" class="btn btn-xs btn-default" title="Edit"><i class="fa fa-pencil text-warning"></i></a>
                                                    <button onclick="confirmDelete(<?php echo $d['id']; ?>, '<?php echo $tgl_filter; ?>')" class="btn btn-xs btn-default" title="Hapus"><i class="fa fa-trash text-danger"></i></button>
                                                </div>
                                            <?php else: ?>
                                                <i class="fa fa-lock text-muted" title="Terkunci"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot style="background:#f9fafb; border-top:2px solid #e5e7eb;">
                                    <tr>
                                        <td colspan="3" class="text-right" style="font-weight:700; color:#64748b;">TOTAL SALDO (CASH + ATM)</td>
                                        <td class="text-right font-mono" style="font-size:16px; font-weight:800; color:#1e293b;">
                                            Rp <?php echo number_format($saldo_akhir); ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../../layout/footer.php'; ?>
    
    <script>
    // 1. Script Jam Realtime
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
        const dateString = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        document.getElementById('live-clock').textContent = timeString;
        document.getElementById('live-date').textContent = dateString;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // 2. Format Rupiah Input
    const nominalInput = document.getElementById('nominal_input');
    if(nominalInput){
        nominalInput.addEventListener('keyup', function(e) {
            let value = this.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            this.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        });
    }

    // 3. Konfirmasi Hapus
    function confirmDelete(id, tgl) {
        Swal.fire({
            title: 'Hapus data?',
            text: "Saldo akan dikalkulasi ulang.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#cbd5e1',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `kas.php?hapus=${id}&tgl=${tgl}`;
            }
        })
    }
    </script>
</body>
</html>