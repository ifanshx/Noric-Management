<?php 
require_once '../../config/database.php';
cek_login(); 

if($_SESSION['role'] != 'admin') { header("Location: ../dashboard.php"); exit; }

$swal_script = "";

// --- 1. LOGIKA ACC / TOLAK (MULTI TABEL) ---
if(isset($_POST['aksi'])) {
    $id = $_POST['id'];
    $tipe = $_POST['tipe']; // 'kasbon' atau 'makan'
    $aksi = $_POST['aksi']; // Approved / Rejected
    
    $table_target = ($tipe == 'makan') ? 'uang_makan' : 'kasbon';
    
    $update = mysqli_query($conn, "UPDATE $table_target SET status='$aksi' WHERE id='$id'");
    
    if($update) {
        $status_msg = ($aksi == 'Approved') ? 'Disetujui' : 'Ditolak';
        $icon_type  = ($aksi == 'Approved') ? 'success' : 'info';
        $swal_script = "Swal.fire({icon: '$icon_type', title: '$status_msg', text: 'Status berhasil diperbarui.', timer: 1000, showConfirmButton: false});";
    }
}

// --- 2. LOGIKA BATALKAN / UNDO (MULTI TABEL) ---
if(isset($_POST['batalkan'])) {
    $id = $_POST['id'];
    $tipe = $_POST['tipe'];
    $table_target = ($tipe == 'makan') ? 'uang_makan' : 'kasbon';
    
    $reset = mysqli_query($conn, "UPDATE $table_target SET status='Pending' WHERE id='$id'");
    
    if($reset) {
        $swal_script = "Swal.fire({icon: 'warning', title: 'Dibatalkan', text: 'Data dikembalikan ke antrian pending.', timer: 1000, showConfirmButton: false});";
    }
}

// --- 3. FILTER SEARCH ---
$search_query = "";
$search_val = "";
if(isset($_GET['q']) && !empty($_GET['q'])) {
    $s = mysqli_real_escape_string($conn, $_GET['q']);
    $search_query = " AND fullname LIKE '%$s%'";
    $search_val = $_GET['q'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { background-color: #f4f6f9; font-family: 'Inter', sans-serif; color:#1e293b; }
        .content-wrapper { padding: 30px; }

        /* Card Styles */
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; }
        
        .card-header-gradient { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); padding: 25px; color: #fff; }
        .card-header-gray { background: #f8fafc; border-bottom:1px solid #e2e8f0; padding: 20px 25px; color: #334155; }
        
        .card-header-gradient h4, .card-header-gray h4 { margin: 0; font-weight: 700; display: flex; align-items: center; font-size: 16px; }

        /* Search */
        .search-container { position: relative; max-width: 350px; }
        .search-input { width: 100%; padding: 10px 15px 10px 40px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; font-size: 13px; transition: all 0.3s; }
        .search-input:focus { border-color: #ef4444; outline: none; }
        .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size:14px; }

        /* Table */
        .table-approval thead th { background: #fef2f2; color: #991b1b; font-size: 11px; text-transform: uppercase; font-weight: 700; padding: 15px; border-bottom: 1px solid #fecaca; }
        .table-history thead th { background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; font-weight: 700; padding: 15px; }
        
        .table tbody td { vertical-align: middle !important; padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        
        /* Type Badge */
        .badge-tipe { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing:0.5px; }
        .tipe-makan { background: #ecfdf5; color: #047857; border:1px solid #a7f3d0; }
        .tipe-kasbon { background: #fff7ed; color: #c2410c; border:1px solid #fed7aa; }

        /* Buttons */
        .btn-action { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; cursor: pointer; color: #fff; font-size:14px; }
        .btn-accept { background: #22c55e; } .btn-accept:hover { background: #16a34a; transform:scale(1.05); }
        .btn-reject { background: #ef4444; } .btn-reject:hover { background: #dc2626; transform:scale(1.05); }
        .btn-undo { background: #f59e0b; } .btn-undo:hover { background: #d97706; }

        .badge-status { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .st-Approved { background: #dcfce7; color: #166534; }
        .st-Rejected { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        
        <div class="row mb-20" style="margin-bottom: 30px; display: flex; align-items: flex-end;">
            <div class="col-md-6">
                <h2 style="margin:0; font-weight:800; color:#1e293b;">Verifikasi Pengajuan</h2>
                <p class="text-muted" style="margin:5px 0 0; font-size:14px;">Persetujuan pinjaman tunai & uang makan harian.</p>
            </div>
            <div class="col-md-6" style="display:flex; justify-content:flex-end;">
                <form method="GET" class="search-container">
                    <i class="fa fa-search search-icon"></i>
                    <input type="text" name="q" class="search-input" placeholder="Cari nama karyawan..." value="<?php echo htmlspecialchars($search_val); ?>" autocomplete="off" onchange="this.form.submit()">
                </form>
            </div>
        </div>

        <div class="modern-card">
            <div class="card-header-gradient">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h4><i class="fa fa-clock-o" style="margin-right:10px;"></i> Antrian Approval (Pending)</h4>
                    <?php 
                        // Hitung Total Pending Gabungan
                        $q_count = mysqli_query($conn, "SELECT (SELECT COUNT(*) FROM kasbon WHERE status='Pending') + (SELECT COUNT(*) FROM uang_makan WHERE status='Pending') as total");
                        $d_count = mysqli_fetch_assoc($q_count);
                        if($d_count['total'] > 0): 
                    ?>
                        <span style="background:#fff; color:#b91c1c; padding:2px 10px; font-size:12px; font-weight:800; border-radius:20px;"><?php echo $d_count['total']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-body" style="padding:0;">
                <div class="table-responsive">
                    <table class="table table-approval mb-0">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Jenis</th>
                                <th>Nominal</th>
                                <th>Keterangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // QUERY GABUNGAN: Kasbon & Uang Makan (Status Pending)
                            $sql_pending = "
                                SELECT * FROM (
                                    (SELECT k.id, k.user_id, k.nominal, k.keterangan, k.created_at, 'kasbon' as tipe, u.fullname 
                                     FROM kasbon k JOIN users u ON k.user_id = u.id WHERE k.status='Pending')
                                    UNION
                                    (SELECT m.id, m.user_id, m.nominal, 'Uang Makan Harian' as keterangan, m.created_at, 'makan' as tipe, u.fullname 
                                     FROM uang_makan m JOIN users u ON m.user_id = u.id WHERE m.status='Pending')
                                ) AS gabungan 
                                WHERE 1=1 $search_query 
                                ORDER BY created_at ASC
                            ";
                            
                            $q = mysqli_query($conn, $sql_pending);
                            
                            if(mysqli_num_rows($q) > 0) {
                                while($d = mysqli_fetch_assoc($q)) {
                                    $fid = "form_vr_".$d['tipe']."_".$d['id'];
                                    $badge_class = ($d['tipe'] == 'makan') ? 'tipe-makan' : 'tipe-kasbon';
                                    $label_tipe  = ($d['tipe'] == 'makan') ? 'UANG MAKAN' : 'PINJAMAN';
                            ?>
                            <tr>
                                <td>
                                    <div style="font-weight:700; color:#334155;"><?php echo $d['fullname']; ?></div>
                                    <div style="font-size:11px; color:#64748b;"><?php echo date('d M Y H:i', strtotime($d['created_at'])); ?></div>
                                </td>
                                <td><span class="badge-tipe <?php echo $badge_class; ?>"><?php echo $label_tipe; ?></span></td>
                                <td><span style="font-weight:800; color:#dc2626;">Rp <?php echo number_format($d['nominal']); ?></span></td>
                                <td><div style="font-style:italic; color:#64748b; max-width:250px;"><?php echo $d['keterangan']; ?></div></td>
                                <td class="text-center">
                                    <form id="<?php echo $fid; ?>" method="POST" style="display:inline-flex; gap:8px;">
                                        <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                        <input type="hidden" name="tipe" value="<?php echo $d['tipe']; ?>">
                                        <input type="hidden" name="aksi" id="aksi_<?php echo $fid; ?>" value="">
                                        
                                        <button type="button" onclick="confirmAction('Rejected', '<?php echo $fid; ?>')" class="btn-action btn-reject" title="Tolak">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        <button type="button" onclick="confirmAction('Approved', '<?php echo $fid; ?>')" class="btn-action btn-accept" title="Setujui">
                                            <i class="fa fa-check"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php } } else { echo '<tr><td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">Tidak ada antrian pending.</td></tr>'; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modern-card">
            <div class="card-header-gray">
                <h4><i class="fa fa-history" style="margin-right:10px;"></i> Riwayat Konfirmasi Hari Ini</h4>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="table-responsive">
                    <table class="table table-history mb-0">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Jenis</th>
                                <th>Nominal</th>
                                <th>Status</th>
                                <th class="text-center">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $today = date('Y-m-d');
                            // QUERY GABUNGAN RIWAYAT
                            $sql_history = "
                                SELECT * FROM (
                                    (SELECT k.id, k.nominal, k.status, k.created_at, 'kasbon' as tipe, u.fullname 
                                     FROM kasbon k JOIN users u ON k.user_id = u.id WHERE k.status != 'Pending' AND DATE(k.created_at)='$today')
                                    UNION
                                    (SELECT m.id, m.nominal, m.status, m.created_at, 'makan' as tipe, u.fullname 
                                     FROM uang_makan m JOIN users u ON m.user_id = u.id WHERE m.status != 'Pending' AND DATE(m.created_at)='$today')
                                ) AS histori 
                                WHERE 1=1 $search_query 
                                ORDER BY created_at DESC
                            ";
                            
                            $q_hist = mysqli_query($conn, $sql_history);
                            
                            if(mysqli_num_rows($q_hist) > 0) {
                                while($h = mysqli_fetch_assoc($q_hist)) {
                                    $st_class = 'st-'.$h['status'];
                                    $badge_class = ($h['tipe'] == 'makan') ? 'tipe-makan' : 'tipe-kasbon';
                                    $label_tipe  = ($h['tipe'] == 'makan') ? 'UANG MAKAN' : 'PINJAMAN';
                            ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:#334155; font-size:13px;"><?php echo $h['fullname']; ?></div>
                                    <div style="font-size:11px; color:#94a3b8;"><?php echo date('H:i', strtotime($h['created_at'])); ?></div>
                                </td>
                                <td><span class="badge-tipe <?php echo $badge_class; ?>"><?php echo $label_tipe; ?></span></td>
                                <td>Rp <?php echo number_format($h['nominal']); ?></td>
                                <td><span class="badge-status <?php echo $st_class; ?>"><?php echo $h['status']; ?></span></td>
                                <td class="text-center">
                                    <form method="POST" onsubmit="return confirm('Batalkan status dan kembalikan ke Pending?')">
                                        <input type="hidden" name="id" value="<?php echo $h['id']; ?>">
                                        <input type="hidden" name="tipe" value="<?php echo $h['tipe']; ?>">
                                        <button type="submit" name="batalkan" class="btn-action btn-undo" title="Batalkan Keputusan">
                                            <i class="fa fa-undo"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php } } else { echo '<tr><td colspan="5" style="text-align:center; padding:20px; color:#cbd5e1; font-style:italic;">Belum ada data yang diproses hari ini.</td></tr>'; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    
    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if(!empty($swal_script)) echo $swal_script; ?>

        function confirmAction(type, formId) {
            let titleText = (type === 'Approved') ? 'Setujui Pengajuan?' : 'Tolak Pengajuan?';
            let btnColor  = (type === 'Approved') ? '#16a34a' : '#ef4444';
            
            Swal.fire({
                title: titleText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: btnColor,
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya, Proses',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('aksi_' + formId).value = type;
                    document.getElementById(formId).submit();
                }
            })
        }
    </script>
</body>
</html>