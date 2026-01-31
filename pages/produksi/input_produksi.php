<?php 
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

$user_id = $_SESSION['user_id'];
$today   = date('Y-m-d');

// --- 1. AMBIL DATA USER & TENTUKAN KATEGORI ---
$q_user = mysqli_query($conn, "SELECT pin, fullname, is_mandor, group_id, status_karyawan FROM users WHERE id='$user_id'");
$d_user = mysqli_fetch_assoc($q_user);
$my_pin = trim($d_user['pin']);
$nama_user = explode(' ', $d_user['fullname'])[0];

// Tentukan Kategori Pekerjaan yang boleh dilihat
$kategori_filter = "";
$label_mode = "";
$badge_color = "";

if ($d_user['is_mandor'] == 1) {
    // Jika Mandor -> Lihat Kategori 'Team'
    $kategori_filter = "Team";
    $label_mode = "MODE MANDOR (TEAM)";
    $badge_color = "background: #dbeafe; color: #1e40af;"; // Biru
} elseif ($d_user['group_id'] == NULL) {
    // Jika Tidak punya grup -> Lihat Kategori 'Perorangan'
    $kategori_filter = "Perorangan";
    $label_mode = "MODE PERORANGAN";
    $badge_color = "background: #fce7f3; color: #9d174d;"; // Pink
} else {
    // Anggota Team (Nanti diblokir di validasi, tapi defaultnya Team)
    $kategori_filter = "Team"; 
}

// --- 2. CEK ABSENSI ---
$sudah_masuk  = false;
$sudah_pulang = false;

$q_absen = mysqli_query($conn, "SELECT status_scan FROM absensi WHERE trim(pin)='$my_pin' AND scan_date LIKE '$today%'");
while($row = mysqli_fetch_assoc($q_absen)) {
    if(in_array($row['status_scan'], [0, 4, 8])) { $sudah_masuk = true; } 
    if(in_array($row['status_scan'], [1, 5, 9])) { $sudah_pulang = true; } 
}

// --- 3. VALIDASI IZIN INPUT ---
$bisa_input = false; 
$pesan_blokir = "";
$warna_alert = "grad-warning";
$icon_blokir = "fa-exclamation-triangle";

if ($d_user['status_karyawan'] != 'Borongan') {
    $pesan_blokir = "Halaman ini khusus untuk karyawan <b>BORONGAN</b>.";
    $warna_alert = "grad-danger";
    $icon_blokir = "fa-ban";
} elseif ($d_user['group_id'] != NULL && $d_user['is_mandor'] == 0) {
    $pesan_blokir = "Anda adalah <b>Anggota Team</b>. Input produksi dilakukan oleh Mandor.";
    $warna_alert = "grad-info";
    $icon_blokir = "fa-users";
} elseif (!$sudah_masuk) {
    $pesan_blokir = "Halo <b>$nama_user</b>, Anda belum <b>SCAN MASUK</b> hari ini ($today).";
    $warna_alert = "grad-warning";
    $icon_blokir = "fa-fingerprint";
} elseif ($sudah_pulang) {
    $pesan_blokir = "Anda sudah <b>ABSEN PULANG</b>. Form input ditutup.";
    $warna_alert = "grad-danger";
    $icon_blokir = "fa-lock";
} else {
    $bisa_input = true;
}

// --- 4. PROSES SIMPAN DATA ---
$swal_script = "";
if(isset($_POST['lapor_kerja']) && $bisa_input) {
    $tgl_input = $_POST['tanggal']; 
    $id_master = mysqli_real_escape_string($conn, $_POST['job_id']); 
    $jumlah    = (int)$_POST['jumlah'];
    
    // Ambil data dan PASTIKAN KATEGORINYA SESUAI (Security Check)
    $q_job = mysqli_query($conn, "SELECT * FROM master_pekerjaan WHERE id='$id_master' AND kategori='$kategori_filter'");
    $d_job = mysqli_fetch_assoc($q_job);
    
    if($d_job && $jumlah > 0) {
        $nama_lengkap = $d_job['jenis_pekerjaan'] . " - " . $d_job['nama_motor'];
        $total_upah   = $jumlah * $d_job['harga'];
        
        $sql_ins = "INSERT INTO produksi_borongan (user_id, tanggal, jenis_pekerjaan, jumlah, total_upah, status) 
                    VALUES ('$user_id', '$tgl_input', '$nama_lengkap', '$jumlah', '$total_upah', 'Pending')";
        
        if(mysqli_query($conn, $sql_ins)) {
            $swal_script = "Swal.fire({
                icon: 'success', 
                title: 'Berhasil!', 
                text: 'Laporan produksi tersimpan.', 
                confirmButtonColor: '#4f46e5'
            });";
        } else {
            $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan database.'});";
        }
    } else {
        $swal_script = "Swal.fire({icon: 'warning', title: 'Data Invalid', text: 'Pekerjaan tidak ditemukan atau tidak sesuai kategori Anda.'});";
    }
}

// --- 5. SIAPKAN DATA JS (FILTERED BY CATEGORY) ---
$list_dropdown_jenis = []; 
$json_data_motor     = []; 

// FILTER QUERY: Hanya ambil yang sesuai kategori user
$q_m = mysqli_query($conn, "SELECT * FROM master_pekerjaan WHERE kategori='$kategori_filter' ORDER BY jenis_pekerjaan ASC, nama_motor ASC");

while($row = mysqli_fetch_assoc($q_m)) {
    $jenis_asli = $row['jenis_pekerjaan'];
    // Safe Key
    $safe_key = md5(trim(strtolower($jenis_asli))); 
    
    $list_dropdown_jenis[$safe_key] = $jenis_asli; 
    
    $json_data_motor[$safe_key][] = [
        'id'    => $row['id'],
        'motor' => $row['nama_motor'],
        'harga' => (int)$row['harga']
    ];
}

// Statistik Harian
$q_stats = mysqli_query($conn, "SELECT SUM(total_upah) as upah, SUM(jumlah) as qty FROM produksi_borongan WHERE user_id='$user_id' AND tanggal='$today'");
$d_stats = mysqli_fetch_assoc($q_stats);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        :root { --primary: #4f46e5; --bg-body: #f1f5f9; --card-shadow: 0 10px 40px -10px rgba(0,0,0,0.08); }
        body { background-color: var(--bg-body); font-family: 'Poppins', sans-serif; color: #334155; }
        .content-wrapper { padding: 30px 20px; }

        .alert-modern { border-radius: 16px; padding: 25px; color: #fff; display: flex; align-items: center; gap: 20px; margin-bottom: 30px; border: none; position: relative; overflow: hidden; box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1); }
        .grad-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .grad-danger { background: linear-gradient(135deg, #ef4444, #b91c1c); }
        .grad-info { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }

        .glass-card { background: #fff; border-radius: 24px; border: 1px solid #fff; box-shadow: var(--card-shadow); overflow: hidden; height: 100%; transition: transform 0.3s ease; }
        .form-header { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); padding: 30px; color: #fff; text-align: center; position: relative; }
        .mode-badge { position: absolute; top: 15px; right: 15px; font-size: 10px; font-weight: 800; padding: 5px 10px; border-radius: 20px; letter-spacing: 0.5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-transform: uppercase; }

        .input-group-modern { margin-bottom: 25px; }
        .input-label { display: block; font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 8px; text-transform: uppercase; }
        .form-control-modern { width: 100%; height: 55px; padding: 0 20px; border-radius: 12px; border: 2px solid #f1f5f9; background: #f8fafc; font-size: 15px; font-weight: 600; color: #334155; transition: all 0.3s; }
        .form-control-modern:focus { border-color: var(--primary); background: #fff; outline: none; }
        .form-control-modern:disabled { background: #e2e8f0; cursor: not-allowed; }

        .btn-modern-submit { width: 100%; height: 60px; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); border: none; border-radius: 16px; color: #fff; font-size: 16px; font-weight: 800; text-transform: uppercase; transition: all 0.3s; box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4); display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-modern-submit:hover { transform: translateY(-2px); }
        .btn-modern-submit:disabled { background: #cbd5e1; box-shadow: none; cursor: not-allowed; }

        .history-card { background: #fff; padding: 20px; border-radius: 16px; margin-bottom: 15px; border: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .st-Pending { background: #fff7ed; color: #c2410c; padding: 3px 8px; border-radius: 5px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .st-Approved { background: #f0fdf4; color: #15803d; padding: 3px 8px; border-radius: 5px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        
        .empty-state { text-align: center; padding: 50px 20px; color: #94a3b8; }
        .empty-state i { font-size: 40px; margin-bottom: 10px; opacity: 0.5; }

        @media (max-width: 768px) { .col-md-5, .col-md-7 { width: 100%; margin-bottom: 20px; } }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <div>
                <h2 style="margin:0; font-weight:800; color:#1e293b;">Input Produksi</h2>
                <p style="margin:5px 0 0; font-size:14px; color:#64748b;">Halo <b><?= $nama_user ?></b>, selamat bekerja!</p>
            </div>
            <div style="text-align:right;">
                <span style="font-size:12px; font-weight:600; color:#64748b;"><?= date('l, d M Y') ?></span>
            </div>
        </div>

        <?php if(!$bisa_input): ?>
            <div class="alert-modern <?php echo $warna_alert; ?>">
                <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fa <?php echo $icon_blokir; ?> fa-2x"></i>
                </div>
                <div>
                    <h4 style="margin: 0 0 5px; font-weight: 800; font-size: 18px;">AKSES DITUTUP</h4>
                    <p style="margin: 0; opacity: 0.9; font-size: 14px; line-height: 1.4;"><?php echo $pesan_blokir; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-5">
                <div class="glass-card" style="<?php if(!$bisa_input) echo 'opacity: 0.6; pointer-events: none; filter: grayscale(100%);'; ?>">
                    <div class="form-header">
                        <div class="mode-badge" style="<?= $badge_color ?>">
                            <?= $label_mode ?>
                        </div>
                        
                        <i class="fa fa-layer-group fa-3x" style="opacity: 0.8; margin-bottom: 15px;"></i>
                        <h3 style="margin: 0; font-weight: 800;">Lapor Pekerjaan</h3>
                    </div>
                    <div class="form-body">
                        <form method="POST">
                            <input type="hidden" name="tanggal" value="<?= date('Y-m-d') ?>">
                            
                            <div class="input-group-modern">
                                <label class="input-label"><i class="fa fa-tags"></i> Jenis Pekerjaan</label>
                                <select id="pilih_jenis" class="form-control-modern" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <?php foreach($list_dropdown_jenis as $key => $label): ?>
                                        <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="input-group-modern">
                                <label class="input-label"><i class="fa fa-motorcycle"></i> Tipe Motor / Item</label>
                                <select name="job_id" id="pilih_motor" class="form-control-modern" required disabled style="background-color: #e2e8f0;">
                                    <option value="">-- Pilih Jenis Dulu --</option>
                                </select>
                            </div>

                            <div class="input-group-modern">
                                <label class="input-label"><i class="fa fa-cubes"></i> Jumlah (Pcs)</label>
                                <input type="number" name="jumlah" class="form-control-modern" min="1" placeholder="Contoh: 10" required>
                            </div>

                            <button type="submit" name="lapor_kerja" class="btn-modern-submit">
                                <i class="fa fa-paper-plane"></i> Kirim Laporan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div style="display:flex; gap:15px; margin-bottom:20px;">
                    <div style="flex:1; background:#fff; padding:15px; border-radius:16px; border:1px solid #f1f5f9; display:flex; align-items:center; gap:15px;">
                        <div style="width:40px; height:40px; background:#eff6ff; color:#2563eb; border-radius:10px; display:flex; align-items:center; justify-content:center;"><i class="fa fa-cube"></i></div>
                        <div>
                            <h4 style="margin:0; font-weight:800; color:#1e293b;"><?= number_format($d_stats['qty'] ?? 0) ?></h4>
                            <span style="font-size:11px; color:#64748b; font-weight:600;">Unit Hari Ini</span>
                        </div>
                    </div>
                    <div style="flex:1; background:#fff; padding:15px; border-radius:16px; border:1px solid #f1f5f9; display:flex; align-items:center; gap:15px;">
                        <div style="width:40px; height:40px; background:#f0fdf4; color:#16a34a; border-radius:10px; display:flex; align-items:center; justify-content:center;"><i class="fa fa-wallet"></i></div>
                        <div>
                            <h4 style="margin:0; font-weight:800; color:#1e293b;">Rp <?= number_format($d_stats['upah'] ?? 0) ?></h4>
                            <span style="font-size:11px; color:#64748b; font-weight:600;">Estimasi Upah</span>
                        </div>
                    </div>
                </div>

                <div class="glass-card">
                    <div style="padding: 20px; border-bottom: 1px solid #f1f5f9;">
                        <h5 style="margin: 0; font-weight: 800; color: #1e293b;">Riwayat Hari Ini</h5>
                    </div>
                    <div style="padding: 20px; background: #f8fafc; min-height: 400px; max-height:500px; overflow-y:auto;">
                        <?php
                        $q_riwayat = mysqli_query($conn, "SELECT * FROM produksi_borongan WHERE user_id='$user_id' AND tanggal='$today' ORDER BY id DESC");
                        
                        if(mysqli_num_rows($q_riwayat) == 0) {
                            echo '<div class="empty-state"><i class="fa fa-clipboard"></i><p>Belum ada data input hari ini.</p></div>';
                        }

                        while($d = mysqli_fetch_assoc($q_riwayat)) {
                            $parts = explode(' - ', $d['jenis_pekerjaan']);
                            $nm_jenis = $parts[0];
                            $nm_motor = isset($parts[1]) ? $parts[1] : '-';
                        ?>
                        <div class="history-card">
                            <div>
                                <div style="font-weight:800; color:#334155; font-size:14px;"><?= $nm_jenis ?></div>
                                <div style="font-size:12px; color:#64748b; margin-top:3px;">
                                    <i class="fa fa-wrench"></i> <?= $nm_motor ?> &bull; <b><?= $d['jumlah'] ?> Pcs</b>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight:800; color:#10b981;">Rp <?= number_format($d['total_upah']) ?></div>
                                <span class="st-<?= $d['status'] ?>"><?= $d['status'] ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        <?php if($swal_script) echo $swal_script; ?>

        // DATA DARI PHP (SUDAH DI-HASH KEY-NYA)
        var dataMotor = <?php echo json_encode($json_data_motor); ?>;

        $(document).ready(function() {
            // Animasi Input Focus
            $('.form-control-modern').on('focus', function(){
                $(this).prev('.input-label').css('color', '#4f46e5');
            }).on('blur', function(){
                $(this).prev('.input-label').css('color', '#64748b');
            });

            // Logic Dropdown
            $('#pilih_jenis').change(function() {
                var selectedKey = $(this).val();
                var motorSelect = $('#pilih_motor');

                // Efek visual loading
                motorSelect.css('opacity', '0.5');
                
                setTimeout(function(){
                    motorSelect.empty().append('<option value="">-- Pilih Tipe Motor --</option>');

                    if (selectedKey && dataMotor[selectedKey]) {
                        motorSelect.prop('disabled', false).css('background-color', '#fff');
                        
                        var list = dataMotor[selectedKey];
                        $.each(list, function(index, item) {
                            var rp = new Intl.NumberFormat('id-ID').format(item.harga);
                            motorSelect.append(`<option value="${item.id}">${item.motor} (Rp ${rp})</option>`);
                        });
                    } else {
                        motorSelect.prop('disabled', true).css('background-color', '#e2e8f0');
                    }
                    motorSelect.css('opacity', '1');
                }, 150);
            });
        });
    </script>
</body>
</html>