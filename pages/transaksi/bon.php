<?php 
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

$uid = $_SESSION['user_id'];
$today = date('Y-m-d');

// --- 1. AMBIL DATA USER & GAJI ---
$q_user = mysqli_query($conn, "SELECT status_karyawan FROM users WHERE id='$uid'");
$d_user = mysqli_fetch_assoc($q_user);
$status_karyawan = $d_user['status_karyawan']; // 'Tetap' atau 'Borongan'

// Ambil nominal default uang makan (untuk referensi)
$q_gaji = mysqli_query($conn, "SELECT uang_makan FROM gaji_karyawan WHERE user_id='$uid'");
$d_gaji = mysqli_fetch_assoc($q_gaji);
$uang_makan_default = $d_gaji['uang_makan'] ?? 0;

// --- 2. CEK STATUS UANG MAKAN HARI INI ---
$q_cek = mysqli_query($conn, "SELECT id FROM uang_makan WHERE user_id='$uid' AND tanggal='$today' AND status IN ('Pending', 'Approved')");
$sudah_ambil_makan = (mysqli_num_rows($q_cek) > 0);

// --- 3. PROSES PENGAJUAN ---
$swal_script = "";

if(isset($_POST['ajukan'])) {
    $tgl = date('Y-m-d'); 
    $jenis = $_POST['jenis_pengajuan']; 
    
    // A. LOGIKA UANG MAKAN
    if ($jenis == 'makan') {
        if($sudah_ambil_makan) {
            $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'Jatah uang makan hari ini sudah diajukan!'});";
        } else {
            // Jika Borongan, ambil input manual. Jika Tetap, pakai default.
            if ($status_karyawan == 'Borongan') {
                $nom = str_replace('.', '', $_POST['nominal_makan']);
            } else {
                $nom = $uang_makan_default;
            }

            if ($nom > 0) {
                $sql = "INSERT INTO uang_makan (user_id, tanggal, nominal, status) 
                        VALUES ('$uid', '$tgl', '$nom', 'Pending')";
                
                if(mysqli_query($conn, $sql)) {
                    $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: 'Uang makan berhasil diajukan!', timer: 1500, showConfirmButton: false}).then(() => { window.location='bon.php'; });";
                }
            } else {
                $swal_script = "Swal.fire({icon: 'error', title: 'Error', text: 'Nominal tidak boleh kosong!'});";
            }
        }
    } 
    // B. LOGIKA KASBON MANUAL
    else if ($jenis == 'kasbon') {
        $nom = str_replace('.', '', $_POST['nominal']); 
        $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);
        $tenor = (int)$_POST['tenor']; 

        if($nom > 0 && !empty($ket)) {
            $cek_pending = mysqli_query($conn, "SELECT id FROM kasbon WHERE user_id='$uid' AND status='Pending'");
            if(mysqli_num_rows($cek_pending) > 0) {
                $swal_script = "Swal.fire({icon: 'warning', title: 'Tahan Dulu', text: 'Selesaikan pengajuan kasbon sebelumnya.'});";
            } else {
                $sql = "INSERT INTO kasbon (user_id, tanggal, nominal, keterangan, status, tenor, terbayar, status_lunas) 
                        VALUES ('$uid', '$tgl', '$nom', '$ket', 'Pending', '$tenor', 0, 'Belum')";
                if(mysqli_query($conn, $sql)) {
                    $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: 'Pengajuan kasbon terkirim!', timer: 1500, showConfirmButton: false}).then(() => { window.location='bon.php'; });";
                }
            }
        } else {
            $swal_script = "Swal.fire({icon: 'error', title: 'Error', text: 'Nominal dan Keterangan wajib diisi!'});";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* CSS SAMA PERSIS SEPERTI SEBELUMNYA */
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --success: #10b981;
            --danger: #ef4444;
            --bg-body: #f8fafc;
            --border-color: #e2e8f0;
        }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: #1e293b; }
        .content-wrapper { padding: 30px; }
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; height: 100%; }
        .card-header { padding: 20px 25px; border-bottom: 1px solid var(--border-color); background: #fff; display: flex; align-items: center; gap: 10px; }
        .card-body { padding: 25px; }
        .tabs-container { display: flex; background: #f1f5f9; padding: 5px; border-radius: 12px; margin-bottom: 25px; }
        .tab-btn { flex: 1; padding: 10px; border: none; background: transparent; border-radius: 8px; font-weight: 600; color: #64748b; cursor: pointer; transition: 0.2s; font-size: 14px; }
        .tab-btn.active { background: #fff; color: var(--primary); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .form-label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 6px; display: block; }
        .form-control-lg { height: 48px; border-radius: 10px; font-size: 15px; border: 1px solid var(--border-color); padding-left: 15px; width: 100%; transition: 0.2s; }
        .form-control-lg:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .textarea-custom { width: 100%; padding: 15px; border-radius: 10px; border: 1px solid var(--border-color); font-family: inherit; font-size: 14px; resize: vertical; min-height: 80px; }
        .info-box { background: #eff6ff; border: 1px solid #dbeafe; border-radius: 10px; padding: 15px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; }
        .info-icon { width: 40px; height: 40px; border-radius: 8px; background: #fff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .cicilan-preview { background: #fdf2f8; border: 1px dashed #fbcfe8; border-radius: 10px; padding: 15px; text-align: center; margin-bottom: 20px; color: #be185d; display: none; }
        .btn-submit { width: 100%; padding: 14px; background: var(--primary); color: #fff; border: none; border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-submit:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .btn-submit:disabled { background: #cbd5e1; cursor: not-allowed; transform: none; }
        .history-list { display: flex; flex-direction: column; gap: 0; }
        .history-item { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid var(--border-color); }
        .history-item:last-child { border-bottom: none; padding-bottom: 0; }
        .h-icon { width: 42px; height: 42px; border-radius: 10px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-right: 15px; }
        .h-info h5 { margin: 0; font-size: 14px; font-weight: 700; color: #1e293b; }
        .h-info p { margin: 3px 0 0; font-size: 12px; color: #64748b; }
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .bg-pending { background: #fff7ed; color: #c2410c; }
        .bg-approved { background: #dcfce7; color: #15803d; }
        .bg-rejected { background: #fef2f2; color: #991b1b; }
        .bg-lunas { background: #dbeafe; color: #1e40af; }
        .header-clock { position: absolute; right: 30px; top: 35px; font-family: 'Courier New', monospace; font-weight: 700; color: var(--primary); background: #eef2ff; padding: 5px 10px; border-radius: 6px; font-size: 14px; }
        @media (max-width: 768px) { .header-clock { display: none; } }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div style="position:relative; margin-bottom: 30px;">
            <h2 style="font-weight:800; color:#1e293b; margin:0;">Pengajuan Dana</h2>
            <p style="margin:5px 0 0; color:#64748b;">Kelola pinjaman dan uang makan harian Anda di sini.</p>
            <div class="header-clock" id="clock">00:00:00</div>
        </div>

        <div class="row">
            <div class="col-lg-5 col-md-12 mb-4">
                <div class="modern-card">
                    <div class="card-header">
                        <i class="fa fa-edit text-primary"></i> <h4 style="margin:0; font-size:16px; font-weight:700;">Buat Pengajuan Baru</h4>
                    </div>
                    <div class="card-body">
                        
                        <div class="tabs-container">
                            <button type="button" class="tab-btn active" onclick="switchTab('kasbon')" id="btn-kasbon">Kasbon Manual</button>
                            <button type="button" class="tab-btn" onclick="switchTab('makan')" id="btn-makan">Uang Makan</button>
                        </div>

                        <form method="POST" id="formPengajuan">
                            <input type="hidden" name="jenis_pengajuan" id="jenis_pengajuan" value="kasbon">

                            <div id="view-kasbon">
                                <div class="form-group mb-3">
                                    <label class="form-label">Nominal Pinjaman</label>
                                    <input type="text" name="nominal" id="nominal_kasbon" class="form-control-lg" placeholder="Contoh: 500.000" autocomplete="off">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Tenor (Lama Cicilan)</label>
                                    <select name="tenor" id="tenor" class="form-control-lg" onchange="hitungCicilan()">
                                        <option value="1">1 Minggu (Langsung Lunas)</option>
                                        <option value="2">2 Minggu</option>
                                        <option value="3">3 Minggu</option>
                                        <option value="4">4 Minggu (1 Bulan)</option>
                                    </select>
                                </div>
                                <div id="cicilan-info" class="cicilan-preview">
                                    <small style="font-weight:700; text-transform:uppercase;">Estimasi Potongan/Minggu:</small><br>
                                    <span style="font-size:20px; font-weight:800;" id="nilai-cicilan">Rp 0</span>
                                </div>
                                <div class="form-group mb-4">
                                    <label class="form-label">Keperluan</label>
                                    <textarea name="keterangan" id="ket_kasbon" class="textarea-custom" placeholder="Jelaskan alasan pinjaman..."></textarea>
                                </div>
                            </div>

                            <div id="view-makan" style="display:none;">
                                <?php if($sudah_ambil_makan): ?>
                                    <div class="info-box" style="background:#fef2f2; border-color:#fee2e2; color:#991b1b;">
                                        <div class="info-icon" style="background:#fecaca; color:#dc2626;"><i class="fa fa-times"></i></div>
                                        <div>
                                            <h5 style="margin:0; font-size:14px; font-weight:700;">Sudah Diambil</h5>
                                            <p style="margin:2px 0 0; font-size:12px;">Anda sudah mengajukan uang makan untuk hari ini.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="info-box">
                                        <div class="info-icon"><i class="fa fa-utensils"></i></div>
                                        <div>
                                            <h5 style="margin:0; font-size:14px; font-weight:700;">Ambil Jatah Harian</h5>
                                            <?php if($status_karyawan == 'Borongan'): ?>
                                                <p style="margin:2px 0 0; font-size:12px;">Silakan input nominal uang makan yang dibutuhkan.</p>
                                            <?php else: ?>
                                                <p style="margin:2px 0 0; font-size:12px;">Nominal otomatis: <b>Rp <?=number_format($uang_makan_default)?></b></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if($status_karyawan == 'Borongan'): ?>
                                        <div class="form-group mb-4">
                                            <label class="form-label">Nominal Uang Makan</label>
                                            <input type="text" name="nominal_makan" id="nominal_makan" class="form-control-lg" placeholder="Masukkan Nominal..." autocomplete="off">
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <button type="submit" name="ajukan" id="btn-submit" class="btn-submit" <?= ($sudah_ambil_makan) ? '' : '' ?>>
                                <i class="fa fa-paper-plane"></i> KIRIM PENGAJUAN
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 col-md-12">
                <div class="modern-card">
                    <div class="card-header">
                        <i class="fa fa-history text-secondary"></i> <h4 style="margin:0; font-size:16px; font-weight:700;">Riwayat Transaksi</h4>
                    </div>
                    <div class="card-body">
                        <div class="history-list">
                            <?php
                            // UNION QUERY (Kasbon + Uang Makan)
                            $sql_history = "
                                (SELECT 'kasbon' as tipe, nominal, keterangan, status, tanggal, created_at, tenor, terbayar 
                                 FROM kasbon WHERE user_id='$uid')
                                UNION
                                (SELECT 'makan' as tipe, nominal, 'Uang Makan Harian' as keterangan, status, tanggal, created_at, 1 as tenor, 0 as terbayar 
                                 FROM uang_makan WHERE user_id='$uid')
                                ORDER BY created_at DESC LIMIT 6
                            ";
                            $q_hist = mysqli_query($conn, $sql_history);
                            
                            if(mysqli_num_rows($q_hist) == 0) {
                                echo "<div style='text-align:center; padding:40px; color:#94a3b8;'><i class='fa fa-folder-open fa-2x mb-2'></i><br>Belum ada riwayat.</div>";
                            }
                            
                            while($h = mysqli_fetch_assoc($q_hist)) {
                                $st = $h['status'];
                                $is_makan = ($h['tipe'] == 'makan');
                                
                                if ($is_makan) {
                                    $cls = ($st=='Approved') ? 'bg-approved' : (($st=='Rejected')?'bg-rejected':'bg-pending');
                                    $txt = $st; 
                                } else {
                                    $sisa = $h['nominal'] - $h['terbayar'];
                                    $cls = ($st=='Approved') ? ($sisa<=0?'bg-lunas':'bg-approved') : (($st=='Rejected')?'bg-rejected':'bg-pending');
                                    $txt = ($st=='Approved') ? ($sisa<=0?'LUNAS':'AKTIF') : (($st=='Rejected')?'DITOLAK':'PENDING');
                                }

                                $icon = $is_makan ? 'fa-utensils' : 'fa-money-bill-wave';
                                $bg_icon = $is_makan ? '#ecfdf5' : '#f1f5f9';
                                $color_icon = $is_makan ? '#10b981' : '#64748b';
                            ?>
                            <div class="history-item">
                                <div style="display:flex; align-items:center;">
                                    <div class="h-icon" style="background:<?=$bg_icon?>; color:<?=$color_icon?>;"><i class="fa <?=$icon?>"></i></div>
                                    <div class="h-info">
                                        <h5>Rp <?=number_format($h['nominal'])?> 
                                            <?php if(!$is_makan): ?>
                                                <span style="font-weight:400; color:#94a3b8; font-size:12px;">(Cicil <?=$h['tenor']?>x)</span>
                                            <?php endif; ?>
                                        </h5>
                                        <p><?=$h['keterangan']?> &bull; <span style="font-family:monospace;"><?=date('d M', strtotime($h['tanggal']))?></span></p>
                                    </div>
                                </div>
                                <div><span class="badge <?=$cls?>"><?=$txt?></span></div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if(!empty($swal_script)) echo $swal_script; ?>

        setInterval(() => {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID');
        }, 1000);

        const btnKasbon = document.getElementById('btn-kasbon');
        const btnMakan = document.getElementById('btn-makan');
        const viewKasbon = document.getElementById('view-kasbon');
        const viewMakan = document.getElementById('view-makan');
        const inputJenis = document.getElementById('jenis_pengajuan');
        const btnSubmit = document.getElementById('btn-submit');
        const sudahAmbilMakan = <?= json_encode($sudah_ambil_makan) ?>;

        const inpNominal = document.getElementById('nominal_kasbon');
        const inpNominalMakan = document.getElementById('nominal_makan'); // Hanya ada jika borongan
        const inpKet = document.getElementById('ket_kasbon');

        function switchTab(type) {
            inputJenis.value = type;
            if(type === 'kasbon') {
                btnKasbon.classList.add('active');
                btnMakan.classList.remove('active');
                viewKasbon.style.display = 'block';
                viewMakan.style.display = 'none';
                
                inpNominal.required = true;
                inpKet.required = true;
                if(inpNominalMakan) inpNominalMakan.required = false;

                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fa fa-paper-plane"></i> AJUKAN KASBON';
            } else {
                btnMakan.classList.add('active');
                btnKasbon.classList.remove('active');
                viewKasbon.style.display = 'none';
                viewMakan.style.display = 'block';
                
                inpNominal.required = false;
                inpKet.required = false;
                if(inpNominalMakan) inpNominalMakan.required = true;

                if(sudahAmbilMakan) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = 'SUDAH DIAMBIL';
                } else {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fa fa-utensils"></i> AMBIL UANG MAKAN';
                }
            }
        }

        // Auto Format Rupiah Kasbon
        inpNominal.addEventListener('keyup', function(e) {
            let val = this.value.replace(/[^0-9]/g, '');
            this.value = new Intl.NumberFormat('id-ID').format(val);
            hitungCicilan();
        });

        // Auto Format Rupiah Makan (Jika Borongan)
        if(inpNominalMakan) {
            inpNominalMakan.addEventListener('keyup', function(e) {
                let val = this.value.replace(/[^0-9]/g, '');
                this.value = new Intl.NumberFormat('id-ID').format(val);
            });
        }

        function hitungCicilan() {
            let nominal = parseInt(inpNominal.value.replace(/\./g, '')) || 0;
            let tenor = parseInt(document.getElementById('tenor').value);
            let divInfo = document.getElementById('cicilan-info');
            
            if(nominal > 0) {
                let perMinggu = Math.ceil(nominal / tenor);
                document.getElementById('nilai-cicilan').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(perMinggu);
                divInfo.style.display = 'block';
            } else {
                divInfo.style.display = 'none';
            }
        }
    </script>
</body>
</html>