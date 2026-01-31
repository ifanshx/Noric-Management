<?php 
require_once '../../config/database.php';
cek_login(); 

if ($_SESSION['role'] !== 'admin') {
    echo "<script>window.location='../dashboard.php';</script>";
    exit;
}

$swal_script = "";

// 1. Tambah Tarif Borongan Baru
if (isset($_POST['tambah_pekerjaan'])) {
    $jenis = trim(mysqli_real_escape_string($conn, $_POST['jenis_baru']));
    $motor = trim(mysqli_real_escape_string($conn, $_POST['motor_baru']));
    $kat   = mysqli_real_escape_string($conn, $_POST['kategori_baru']);
    $harga = str_replace('.', '', $_POST['harga_baru']);
    
    if(!empty($jenis) && !empty($motor)) {
        $q_ins = "INSERT INTO master_pekerjaan (jenis_pekerjaan, nama_motor, kategori, harga) VALUES ('$jenis', '$motor', '$kat', '$harga')";
        if (mysqli_query($conn, $q_ins)) {
            $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: 'Tarif baru ditambahkan!', timer: 1500, showConfirmButton: false});";
        }
    }
}

// 2. Update Massal Tarif
if (isset($_POST['update_harga_borongan'])) {
    foreach ($_POST['harga_pekerjaan'] as $id => $nilai) {
        $nilai_fix = str_replace('.', '', $nilai);
        mysqli_query($conn, "UPDATE master_pekerjaan SET harga='$nilai_fix' WHERE id='$id'");
    }
    $swal_script = "Swal.fire({icon: 'success', title: 'Updated', text: 'Harga diperbarui!', timer: 1500, showConfirmButton: false});";
}

// 3. Hapus Tarif
if (isset($_GET['hapus_job'])) {
    $id = (int)$_GET['hapus_job'];
    mysqli_query($conn, "DELETE FROM master_pekerjaan WHERE id='$id'");
    $swal_script = "Swal.fire({icon: 'success', title: 'Terhapus', text: 'Item dihapus.', timer: 1000, showConfirmButton: false}).then(() => { window.location='setting_gaji.php'; });";
}

// 4. Update Gaji Tetap (Termasuk Kepala Bengkel)
if (isset($_POST['simpan_gaji_individu'])) {
    $stmt = mysqli_prepare($conn, "INSERT INTO gaji_karyawan (user_id, gaji_pokok, uang_makan, gaji_lembur) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE gaji_pokok = VALUES(gaji_pokok), uang_makan = VALUES(uang_makan), gaji_lembur = VALUES(gaji_lembur)");
    if ($stmt) {
        foreach ($_POST['user'] as $uid => $data) {
            $uid = (int)$uid;
            $gapok  = (int)str_replace('.', '', $data['gapok'] ?? '0');
            $makan  = (int)str_replace('.', '', $data['makan'] ?? '0');
            $lembur = (int)str_replace('.', '', $data['lembur'] ?? '0');
            mysqli_stmt_bind_param($stmt, "iiii", $uid, $gapok, $makan, $lembur);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
        $swal_script = "Swal.fire({icon: 'success', title: 'Tersimpan', text: 'Data gaji diperbarui!', timer: 1500, showConfirmButton: false});";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #4f46e5; --secondary: #64748b; --bg-body: #f8fafc; --card-shadow: 0 10px 30px -5px rgba(0,0,0,0.06); }
        body { background-color: var(--bg-body); font-family: 'Poppins', sans-serif; color: #334155; }
        .content-wrapper { padding: 30px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title h2 { margin: 0; font-weight: 800; color: #1e293b; font-size: 24px; }
        .page-title p { margin: 5px 0 0; color: #64748b; font-size: 14px; }
        .glass-card { background: #fff; border-radius: 20px; border: 1px solid #fff; box-shadow: var(--card-shadow); overflow: hidden; margin-bottom: 25px; transition: transform 0.2s; }
        .glass-card:hover { transform: translateY(-2px); }
        .card-header-gradient { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); padding: 20px 25px; color: #fff; display: flex; align-items: center; justify-content: space-between; }
        .card-header-white { background: #fff; padding: 20px 25px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
        .card-title { margin: 0; font-weight: 700; font-size: 16px; display: flex; align-items: center; gap: 10px; }
        .table-custom th { background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 800; padding: 15px 20px; border-bottom: 2px solid #e2e8f0; }
        .table-custom td { padding: 12px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .input-modern { width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 15px; font-size: 13px; transition: all 0.3s; background: #fff; }
        .input-bare { border: 1px solid transparent; background: transparent; width: 100%; padding: 8px; font-weight: 600; text-align: right; border-radius: 6px; color: #334155; }
        .input-bare:focus { background: #fff; border-color: #e2e8f0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); outline: none; }
        .badge-pill { padding: 4px 10px; border-radius: 50px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge-perorangan { background: #e0e7ff; color: #3730a3; }
        .badge-team { background: #ffedd5; color: #9a3412; }
        .badge-kepala { background: #e0f2fe; color: #0369a1; font-size: 9px; }
        .search-box { position: relative; width: 250px; }
        .search-box input { width: 100%; padding: 8px 15px 8px 35px; border-radius: 50px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 13px; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .btn-gradient { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #fff; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 700; font-size: 13px; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3); cursor:pointer; }
        .btn-gradient.green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .btn-icon-add { width: 35px; height: 35px; border-radius: 8px; background: var(--primary); color: #fff; border: none; display: flex; align-items: center; justify-content: center; cursor:pointer; }
        .action-icon { width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #fee2e2; color: #ef4444; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="page-header">
            <div class="page-title">
                <h2>Konfigurasi Gaji</h2>
                <p>Atur gaji pokok, tunjangan, dan tarif borongan di sini.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-7">
                <div class="glass-card">
                    <div class="card-header-gradient">
                        <h5 class="card-title"><i class="fa fa-user-tie"></i> Gaji Karyawan Tetap & Management</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="table-responsive">
                                <table class="table table-custom mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nama Karyawan</th>
                                            <th class="text-right">Gapok / Hari</th>
                                            <th class="text-right">Makan / Hari</th>
                                            <th class="text-right">Lembur / Jam</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Query diupdate untuk menyertakan role kepala_bengkel
                                        $q_tetap = mysqli_query($conn, "
                                            SELECT u.id, u.fullname, u.role, 
                                            COALESCE(g.gaji_pokok, 0) AS gaji_pokok, 
                                            COALESCE(g.uang_makan, 0) AS uang_makan, 
                                            COALESCE(g.gaji_lembur, 0) AS gaji_lembur 
                                            FROM users u 
                                            LEFT JOIN gaji_karyawan g ON u.id = g.user_id 
                                            WHERE (u.role = 'user' AND u.status_karyawan = 'Tetap') 
                                            OR u.role = 'kepala_bengkel' 
                                            ORDER BY u.fullname ASC
                                        ");
                                        if(mysqli_num_rows($q_tetap) > 0) {
                                            while ($u = mysqli_fetch_assoc($q_tetap)) { ?>
                                            <tr>
                                                <td>
                                                    <div style="font-weight:700; color:#334155;"><?php echo $u['fullname']; ?></div>
                                                    <?php if($u['role'] == 'kepala_bengkel'): ?>
                                                        <span class="badge-pill badge-kepala">Kepala Bengkel</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><input type="text" name="user[<?php echo $u['id']; ?>][gapok]" class="input-bare input-rupiah" value="<?php echo number_format($u['gaji_pokok'], 0, ',', '.'); ?>" placeholder="0"></td>
                                                <td><input type="text" name="user[<?php echo $u['id']; ?>][makan]" class="input-bare input-rupiah" value="<?php echo number_format($u['uang_makan'], 0, ',', '.'); ?>" placeholder="0"></td>
                                                <td><input type="text" name="user[<?php echo $u['id']; ?>][lembur]" class="input-bare input-rupiah" value="<?php echo number_format($u['gaji_lembur'], 0, ',', '.'); ?>" placeholder="0"></td>
                                            </tr>
                                            <?php } 
                                        } else { echo '<tr><td colspan="4" class="text-center text-muted" style="padding:30px;">Belum ada data karyawan.</td></tr>'; } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="padding: 20px; text-align:right; border-top:1px solid #f1f5f9;">
                                <button type="submit" name="simpan_gaji_individu" class="btn-gradient">
                                    <i class="fa fa-save" style="margin-right:5px;"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="glass-card" style="border-top: 4px solid #10b981;">
                    <div class="card-body" style="padding: 25px;">
                        <h6 style="font-weight:800; color:#1e293b; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                            <span style="width:30px; height:30px; background:#dcfce7; color:#10b981; border-radius:8px; display:flex; align-items:center; justify-content:center;"><i class="fa fa-plus"></i></span>
                            Tambah Tarif Baru
                        </h6>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <input type="text" name="jenis_baru" class="input-modern" placeholder="Jenis Pekerjaan (Contoh: Leher)" required>
                                </div>
                                <div class="col-md-6 mb-3" style="padding-right:5px;">
                                    <input type="text" name="motor_baru" class="input-modern" placeholder="Tipe Motor" required>
                                </div>
                                <div class="col-md-6 mb-3" style="padding-left:5px;">
                                    <select name="kategori_baru" class="input-modern">
                                        <option value="Perorangan">Perorangan</option>
                                        <option value="Team">Team</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display:flex; gap:10px;">
                                <div style="position:relative; flex-grow:1;">
                                    <span style="position:absolute; left:15px; top:50%; transform:translateY(-50%); font-weight:bold; color:#94a3b8;">Rp</span>
                                    <input type="text" name="harga_baru" class="input-modern input-rupiah" style="padding-left:40px; font-weight:700; color:#10b981;" placeholder="0" required>
                                </div>
                                <button type="submit" name="tambah_pekerjaan" class="btn-icon-add"><i class="fa fa-arrow-right"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="glass-card">
                    <div class="card-header-white">
                        <div class="card-title"><i class="fa fa-tags text-primary"></i> Master Tarif Borongan</div>
                        <div class="search-box">
                            <i class="fa fa-search"></i>
                            <input type="text" id="cariTarif" placeholder="Cari pekerjaan...">
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-custom mb-0" id="tabelTarif">
                                    <thead>
                                        <tr>
                                            <th>Item & Kategori</th>
                                            <th class="text-right">Harga (Rp)</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $q_job = mysqli_query($conn, "SELECT * FROM master_pekerjaan ORDER BY jenis_pekerjaan ASC");
                                        while ($job = mysqli_fetch_assoc($q_job)):
                                            $badge_cls = ($job['kategori'] == 'Team') ? 'badge-team' : 'badge-perorangan';
                                        ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight:700; font-size:14px; color:#1e293b;"><?php echo $job['jenis_pekerjaan']; ?></div>
                                                <div style="margin-top:4px;">
                                                    <span style="color:#64748b; font-size:12px; margin-right:5px;"><?php echo $job['nama_motor']; ?></span>
                                                    <span class="badge-pill <?php echo $badge_cls; ?>"><?php echo $job['kategori']; ?></span>
                                                </div>
                                            </td>
                                            <td><input type="text" name="harga_pekerjaan[<?php echo $job['id']; ?>]" class="input-bare input-rupiah" value="<?php echo number_format($job['harga'], 0, ',', '.'); ?>" style="color:#10b981;"></td>
                                            <td class="text-center">
                                                <a href="setting_gaji.php?hapus_job=<?php echo $job['id']; ?>" onclick="return confirm('Hapus item ini?')" class="action-icon"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="padding: 15px; border-top:1px solid #f1f5f9; text-align:center;">
                                <button type="submit" name="update_harga_borongan" class="btn-gradient green" style="width:100%;">
                                    <i class="fa fa-sync-alt" style="margin-right:5px;"></i> Update Semua Harga
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if (!empty($swal_script)) echo $swal_script; ?>
        document.getElementById('cariTarif').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('#tabelTarif tbody tr').forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
        document.querySelectorAll('.input-rupiah').forEach(input => {
            input.addEventListener('keyup', function(e) {
                let val = this.value.replace(/[^,\d]/g, '').toString(), split = val.split(','), sisa = split[0].length % 3, rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                if (ribuan) { rupiah += (sisa ? '.' : '') + ribuan.join('.'); }
                this.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            });
        });
    </script>
</body>
</html>