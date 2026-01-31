<?php 
require_once '../../config/database.php';
cek_login(); 

if($_SESSION['role'] != 'admin') {
    echo "<script>window.location='../dashboard.php';</script>"; exit;
}

// --- 1. LOGIKA ACC / TOLAK (DENGAN SWEETALERT) ---
$swal_script = "";

if(isset($_POST['aksi'])) {
    $id = $_POST['id'];
    $aksi = $_POST['aksi'];
    $jml_baru = (int)$_POST['jumlah_revisi'];
    
    if($aksi == 'terima') {
        // Ambil Data Lama (Untuk cek jenis pekerjaan)
        $q_log = mysqli_query($conn, "SELECT jenis_pekerjaan FROM produksi_borongan WHERE id='$id'");
        $d_log = mysqli_fetch_assoc($q_log);
        
        // Parsing Nama Pekerjaan untuk cari harga
        $parts = explode(' - ', $d_log['jenis_pekerjaan']);
        $jenis = isset($parts[0]) ? trim($parts[0]) : '';
        $motor = isset($parts[1]) ? trim($parts[1]) : '';

        // Cari Harga Terbaru di Master (Re-Calculate Price)
        // Jika nama motor kosong, cari berdasarkan jenis saja
        if (!empty($motor)) {
            $q_hrg = mysqli_query($conn, "SELECT harga FROM master_pekerjaan WHERE jenis_pekerjaan='$jenis' AND nama_motor='$motor'");
        } else {
            $q_hrg = mysqli_query($conn, "SELECT harga FROM master_pekerjaan WHERE jenis_pekerjaan='$jenis' LIMIT 1");
        }
        
        $d_hrg = mysqli_fetch_assoc($q_hrg);
        $harga = $d_hrg ? $d_hrg['harga'] : 0; 
        
        // Hitung Ulang Total
        $total = $jml_baru * $harga;

        $update = mysqli_query($conn, "UPDATE produksi_borongan SET jumlah='$jml_baru', total_upah='$total', status='Approved' WHERE id='$id'");
        
        if($update) {
            $swal_script = "Swal.fire({icon: 'success', title: 'Disetujui', text: 'Laporan produksi berhasil diverifikasi.', timer: 1500, showConfirmButton: false});";
        }
    } elseif ($aksi == 'tolak') {
        $update = mysqli_query($conn, "UPDATE produksi_borongan SET status='Rejected' WHERE id='$id'");
        
        if($update) {
            $swal_script = "Swal.fire({icon: 'error', title: 'Ditolak', text: 'Laporan produksi telah ditolak.', timer: 1500, showConfirmButton: false});";
        }
    }
}

// --- 2. LOGIKA PENCARIAN ---
$where_clause = "WHERE p.status = 'Pending'";
$search_val   = "";

if(isset($_GET['q']) && !empty($_GET['q'])) {
    $search = mysqli_real_escape_string($conn, $_GET['q']);
    $where_clause .= " AND (u.fullname LIKE '%$search%' OR p.jenis_pekerjaan LIKE '%$search%')";
    $search_val = $_GET['q'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        /* --- MODERN UI STYLES --- */
        body { background-color: #f4f6f9; font-family: 'Poppins', sans-serif; }
        .content-wrapper { padding: 30px; }

        /* Card Styles */
        .modern-card { background: #fff; border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 25px; overflow: hidden; }
        .card-header-gradient { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 25px; position: relative; color: #fff; }
        .card-header-gradient h4 { margin: 0; font-weight: 800; letter-spacing: 0.5px; display: flex; align-items: center; }
        .card-body { padding: 0; } /* Padding 0 untuk table full width */

        /* Search Bar */
        .search-container { position: relative; max-width: 350px; }
        .search-input { width: 100%; padding: 12px 20px 12px 45px; border-radius: 50px; border: 1px solid #e2e8f0; background: #fff; font-size: 14px; transition: all 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .search-input:focus { border-color: #f59e0b; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); outline: none; }
        .search-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        /* Table Styles */
        .table-approval thead th { background: #fffbeb; color: #92400e; font-size: 11px; text-transform: uppercase; font-weight: 800; padding: 20px !important; border-bottom: 2px solid #fcd34d; letter-spacing: 0.5px; }
        .table-approval tbody td { vertical-align: middle !important; padding: 20px !important; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
        .table-approval tr:hover { background-color: #fffbf0; }

        /* Input Koreksi */
        .input-koreksi-group { display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 2px; width: 100px; margin: 0 auto; box-shadow: inset 0 2px 4px rgba(0,0,0,0.03); }
        .input-koreksi { border: none; text-align: center; font-weight: 700; color: #1e293b; width: 50px; background: transparent; font-size: 16px; }
        .input-koreksi:focus { outline: none; }
        .input-label { font-size: 10px; color: #94a3b8; font-weight: 600; padding-right: 8px; }

        /* Badges */
        .badge-job { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 12px; }
        .badge-motor { background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; margin-left: 5px; border: 1px solid #cbd5e1; }
        
        /* Avatar */
        .avatar-circle { width: 45px; height: 45px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; margin-right: 15px; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

        /* Action Buttons */
        .btn-action { width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn-accept { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; }
        .btn-accept:hover { transform: translateY(-2px); box-shadow: 0 5px 10px rgba(34, 197, 94, 0.4); }
        .btn-reject { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff; }
        .btn-reject:hover { transform: translateY(-2px); box-shadow: 0 5px 10px rgba(239, 68, 68, 0.4); }

        /* Empty State */
        .empty-state { padding: 60px; text-align: center; }
        .empty-icon { font-size: 60px; color: #e2e8f0; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        
        <div class="row mb-20" style="margin-bottom: 30px; display: flex; align-items: flex-end;">
            <div class="col-md-6">
                <h3 style="margin:0; font-weight:800; color:#1e293b;">Verifikasi Produksi</h3>
                <p class="text-muted" style="margin:5px 0 0;">Validasi laporan kerja harian karyawan borongan.</p>
            </div>
            <div class="col-md-6" style="display:flex; justify-content:flex-end;">
                <form method="GET" class="search-container">
                    <i class="fa fa-search search-icon"></i>
                    <input type="text" name="q" class="search-input" placeholder="Cari karyawan atau pekerjaan..." value="<?php echo htmlspecialchars($search_val); ?>" autocomplete="off" onchange="this.form.submit()">
                </form>
            </div>
        </div>

        <div class="modern-card">
            <div class="card-header-gradient">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h4><i class="fa fa-clipboard-check" style="margin-right:10px; opacity:0.8;"></i> Antrian Approval</h4>
                    <?php 
                        $count_pending = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM produksi_borongan WHERE status='Pending'"));
                        if($count_pending > 0): 
                    ?>
                        <span class="badge" style="background:#fff; color:#d97706; padding:5px 10px; font-weight:800; border-radius:8px;"><?php echo $count_pending; ?> Pending</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-approval mb-0">
                        <thead>
                            <tr>
                                <th width="30%">Karyawan</th>
                                <th width="30%">Detail Pekerjaan</th>
                                <th width="15%" class="text-center">Koreksi Jumlah</th>
                                <th width="15%" class="text-right">Est. Upah (Awal)</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT p.*, u.fullname FROM produksi_borongan p JOIN users u ON p.user_id = u.id $where_clause ORDER BY p.tanggal ASC";
                            $q = mysqli_query($conn, $query);
                            
                            if(mysqli_num_rows($q) > 0) {
                                while($d = mysqli_fetch_assoc($q)) {
                                    $fid = "form_verif_".$d['id'];
                                    
                                    // Parse Pekerjaan
                                    $parts = explode(' - ', $d['jenis_pekerjaan']);
                                    $job = isset($parts[0])?$parts[0]:''; 
                                    $mtr = isset($parts[1])?$parts[1]:'';

                                    // Initials
                                    $initials = strtoupper(substr($d['fullname'], 0, 1));
                            ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center;">
                                        <div class="avatar-circle"><?php echo $initials; ?></div>
                                        <div>
                                            <div style="font-weight:700; color:#334155; font-size:15px;"><?php echo $d['fullname']; ?></div>
                                            <div style="font-size:12px; color:#64748b; margin-top:2px;">
                                                <i class="fa fa-clock-o"></i> <?php echo date('d M Y, H:i', strtotime($d['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-job"><?php echo $job; ?></span>
                                    <?php if($mtr): ?>
                                        <span class="badge-motor"><?php echo $mtr; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="input-koreksi-group">
                                        <input type="number" name="jumlah_revisi" value="<?php echo $d['jumlah']; ?>" form="<?php echo $fid; ?>" class="input-koreksi" min="1">
                                        <span class="input-label">Pcs</span>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <span style="font-weight:700; color:#94a3b8; font-size:13px;">Rp <?php echo number_format($d['total_upah']); ?></span>
                                </td>
                                <td class="text-center">
                                    <form id="<?php echo $fid; ?>" method="POST" style="display:inline-flex; gap:10px;">
                                        <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                        
                                        <button type="button" onclick="confirmAction('tolak', '<?php echo $fid; ?>')" class="btn-action btn-reject" title="Tolak Laporan">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        
                                        <button type="button" onclick="confirmAction('terima', '<?php echo $fid; ?>')" class="btn-action btn-accept" title="Setujui Laporan">
                                            <i class="fa fa-check"></i>
                                        </button>

                                        <input type="hidden" name="aksi" id="aksi_<?php echo $fid; ?>" value="">
                                    </form>
                                </td>
                            </tr>
                            <?php 
                                } 
                            } else {
                            ?>
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <i class="fa fa-folder-open-o empty-icon"></i>
                                        <h5 style="font-weight:700; color:#64748b;">Tidak Ada Antrian</h5>
                                        <p class="text-muted">Semua laporan produksi sudah diverifikasi.</p>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // 1. Tampilkan Notifikasi dari PHP (Jika ada)
        <?php if(!empty($swal_script)) echo $swal_script; ?>

        // 2. Fungsi Konfirmasi Sebelum Submit
        function confirmAction(type, formId) {
            let titleText = (type === 'terima') ? 'Setujui Laporan?' : 'Tolak Laporan?';
            let descText = (type === 'terima') ? 'Pastikan jumlah produksi (Pcs) sudah benar.' : 'Laporan ini tidak akan dihitung dalam gaji.';
            let iconType = (type === 'terima') ? 'question' : 'warning';
            let btnColor = (type === 'terima') ? '#16a34a' : '#ef4444';
            let btnText  = (type === 'terima') ? 'Ya, Setujui!' : 'Ya, Tolak!';

            Swal.fire({
                title: titleText,
                text: descText,
                icon: iconType,
                showCancelButton: true,
                confirmButtonColor: btnColor,
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: btnText,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Set value aksi ke hidden input
                    document.getElementById('aksi_' + formId).value = type;
                    // Submit form
                    document.getElementById(formId).submit();
                }
            })
        }
    </script>
</body>
</html>