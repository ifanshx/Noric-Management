<?php 
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

if($_SESSION['role'] != 'kepala_bengkel' && $_SESSION['role'] != 'admin') { 
    echo "<script>window.location='../dashboard.php';</script>";
    exit; 
}

$swal_script = "";

// --- PROSES SIMPAN PENGIRIMAN ---
if(isset($_POST['simpan_pengiriman'])) {
    $id_order  = $_POST['id_order_kirim'];
    $tgl_kirim = $_POST['tgl_kirim'];
    $item_ids  = $_POST['item_id']; 
    $kirim_now = $_POST['qty_kirim_sekarang']; 
    
    // 1. Header Pengiriman
    mysqli_query($conn, "INSERT INTO pengiriman (order_id, tanggal) VALUES ('$id_order', '$tgl_kirim')");
    $id_pengiriman = mysqli_insert_id($conn);

    $total_item = 0;
    foreach($item_ids as $index => $iid) {
        $qty_input = (int)$kirim_now[$index];
        if($qty_input > 0) {
            mysqli_query($conn, "INSERT INTO pengiriman_items (pengiriman_id, order_item_id, qty_kirim) VALUES ('$id_pengiriman', '$iid', '$qty_input')");
            mysqli_query($conn, "UPDATE order_items SET qty_sent = qty_sent + $qty_input WHERE id='$iid'");
            $total_item++;
        }
    }

    if($total_item > 0) {
        // Cek Selesai
        $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(qty) as tp, SUM(qty_sent) as tk FROM order_items WHERE order_id='$id_order'"));
        if($cek['tk'] >= $cek['tp']) mysqli_query($conn, "UPDATE orderan SET status='Selesai' WHERE id='$id_order'");

        $swal_script = "Swal.fire({icon: 'success', title: 'Terkirim!', text: 'Silakan cetak surat jalan.', showCancelButton: true, confirmButtonText: 'Cetak Surat Jalan'}).then((result) => { if (result.isConfirmed) { window.open('cetak_surat_jalan.php?id=$id_pengiriman', '_blank'); window.location='pengiriman.php'; } else { window.location='pengiriman.php'; } });";
    } else {
        mysqli_query($conn, "DELETE FROM pengiriman WHERE id='$id_pengiriman'");
        $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'Qty tidak boleh kosong.'});";
    }
}

// Stats Dashboard
$q_stat = mysqli_query($conn, "SELECT COUNT(*) as cnt, status FROM orderan WHERE status IN ('Proses', 'Selesai') GROUP BY status");
$stat_data = ['Proses'=>0, 'Selesai'=>0];
while($r=mysqli_fetch_assoc($q_stat)) $stat_data[$r['status']] = $r['cnt'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- TEMA BIRU/INDIGO --- */
        :root { 
            --primary: #4f46e5;      
            --primary-dark: #3730a3; 
            --accent: #0ea5e9;       
            --bg-body: #f8fafc;      
            --text-main: #1e293b;    
            --card-border: #e2e8f0;
        }
        
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-main); }
        .content-wrapper { padding: 30px; }
        
        /* HEADER */
        .page-header { margin-bottom: 30px; }
        .page-title h3 { margin: 0; font-weight: 800; color: #1e293b; font-size: 24px; letter-spacing: -0.5px; }
        .page-title p { margin: 5px 0 0; color: #64748b; font-size: 14px; }

        /* STATS CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 16px; border: 1px solid var(--card-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); position: relative; overflow: hidden; }
        
        .stat-card.blue { border-left: 4px solid #3b82f6; }
        .stat-card.green { border-left: 4px solid #10b981; }
        .stat-card.dark { border-left: 4px solid #1e293b; background: linear-gradient(145deg, #ffffff, #f8fafc); }
        
        .stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 5px; letter-spacing: 0.5px; }
        .stat-value { font-size: 28px; font-weight: 800; color: #0f172a; margin: 0; }
        .stat-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 36px; opacity: 0.1; color: var(--primary); }

        /* TABLE MODERN */
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid var(--card-border); overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.03); }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { background: #f8fafc; font-size: 11px; text-transform: uppercase; padding: 18px 20px; color: #64748b; text-align: left; border-bottom: 2px solid #e2e8f0; font-weight: 700; letter-spacing: 0.5px; }
        .table-custom td { padding: 20px; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: top; color: #334155; }
        .table-custom tr:last-child td { border-bottom: none; }

        /* Detail Item Styles */
        .order-meta { margin-bottom: 15px; }
        .customer-name { font-weight: 700; font-size: 15px; color: #1e293b; display: block; margin-bottom: 4px; }
        .order-date { font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 6px; font-family: 'Courier Prime', monospace; }
        
        .item-list-compact { list-style: none; padding: 0; margin: 0; }
        .item-list-compact li { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9; font-size: 12px; }
        .item-list-compact li:last-child { border-bottom: none; }
        
        .qty-badge { background: #eff6ff; color: #4f46e5; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 11px; }
        
        /* Progress Bar Modern */
        .progress-container { width: 100%; background: #f1f5f9; height: 8px; border-radius: 10px; margin-top: 8px; overflow: hidden; }
        .progress-bar-fill { height: 100%; border-radius: 10px; transition: width 0.6s ease; }
        .progress-info { display: flex; justify-content: space-between; font-size: 11px; font-weight: 600; color: #64748b; margin-top: 4px; }

        /* Buttons & Badges */
        .btn-action { padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; border:none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; text-decoration: none; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        
        .btn-kirim { background: var(--primary); color: white; }
        .btn-kirim:hover { background: var(--primary-dark); }
        
        .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; display: inline-block; letter-spacing: 0.5px; }
        .bg-siap { background: #eff6ff; color: #4f46e5; border: 1px solid #c7d2fe; }
        .bg-selesai { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

        /* MODAL */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); }
        .modal-content { background-color: #fff; margin: 5% auto; padding: 0; border-radius: 16px; width: 600px; max-width: 95%; animation: slideDown 0.3s ease-out; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        @keyframes slideDown { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        
        @media(max-width:768px){ 
            .stats-grid { grid-template-columns: 1fr; } 
            .modal-content { width: 95%; margin: 20% auto; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="page-header">
            <div class="page-title">
                <h3>Jadwal Pengiriman</h3>
                <p>Kelola pengiriman barang mingguan dan cetak surat jalan.</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-label">Siap Kirim</div>
                <div class="stat-value"><?php echo $stat_data['Proses']; ?></div>
                <i class="fa fa-truck stat-icon"></i>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Selesai (Lengkap)</div>
                <div class="stat-value"><?php echo $stat_data['Selesai']; ?></div>
                <i class="fa fa-check-circle stat-icon"></i>
            </div>
            <div class="stat-card dark">
                <div class="stat-label">Total Jadwal</div>
                <div class="stat-value"><?php echo $stat_data['Proses'] + $stat_data['Selesai']; ?></div>
                <i class="fa fa-list stat-icon"></i>
            </div>
        </div>

        <div class="modern-card">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="25%">Informasi Pelanggan</th>
                            <th width="40%">Rincian Progress Pengiriman</th>
                            <th class="text-center" width="15%">Status</th>
                            <th class="text-center" width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($conn, "SELECT * FROM orderan WHERE status IN ('Proses', 'Selesai') ORDER BY CASE WHEN status='Proses' THEN 1 ELSE 2 END, tanggal ASC");
                        
                        if(mysqli_num_rows($q) == 0) {
                            echo "<tr><td colspan='4' style='text-align:center; padding:40px; color:#94a3b8;'>Tidak ada jadwal pengiriman saat ini.</td></tr>";
                        }

                        while($d = mysqli_fetch_assoc($q)):
                            $oid = $d['id'];
                            
                            // Hitung Statistik Item
                            $q_item = mysqli_query($conn, "SELECT nama_barang, qty, qty_sent FROM order_items WHERE order_id='$oid'");
                            $items = [];
                            $tp = 0; $tk = 0;
                            while($it = mysqli_fetch_assoc($q_item)) {
                                $tp += $it['qty'];
                                $tk += $it['qty_sent'];
                                $items[] = $it;
                            }
                            
                            $tp = ($tp > 0) ? $tp : 1; // Hindari division by zero
                            $persen = round(($tk / $tp) * 100);
                            $prog_color = ($persen < 100) ? '#f59e0b' : '#10b981'; // Orange -> Green
                        ?>
                        <tr>
                            <td>
                                <div class="order-meta">
                                    <span class="customer-name"><?php echo htmlspecialchars($d['nama_pelanggan']); ?></span>
                                    <span class="order-date"><i class="fa fa-calendar"></i> <?php echo date('d M Y', strtotime($d['tanggal'])); ?></span>
                                </div>
                                <?php if(!empty($d['keterangan'])): ?>
                                    <div style="font-size:11px; background:#fff7ed; padding:4px 8px; border-radius:6px; color:#c2410c; display:inline-block; border:1px solid #ffedd5;">
                                        <i class="fa fa-info-circle"></i> <?php echo htmlspecialchars($d['keterangan']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <div style="margin-bottom:15px;">
                                    <div class="progress-info">
                                        <span>Progress Kirim</span>
                                        <span style="color:<?php echo $prog_color; ?>;"><?php echo $persen; ?>%</span>
                                    </div>
                                    <div class="progress-container">
                                        <div class="progress-bar-fill" style="width: <?php echo $persen; ?>%; background-color: <?php echo $prog_color; ?>;"></div>
                                    </div>
                                    <div class="progress-info" style="margin-top:2px; font-weight:400;">
                                        <span>Total: <b style="color:#1e293b;"><?php echo $tk; ?></b> dari <?php echo $tp; ?> unit</span>
                                    </div>
                                </div>

                                <ul class="item-list-compact">
                                    <?php 
                                    $limit = 0;
                                    foreach($items as $it): 
                                        if($limit >= 3) break; 
                                        $limit++;
                                    ?>
                                    <li>
                                        <span style="color:#475569; font-weight:500;"><?php echo $it['nama_barang']; ?></span>
                                        <?php if($it['qty_sent'] >= $it['qty']): ?>
                                            <span style="color:#10b981; font-weight:700; font-size:10px;"><i class="fa fa-check"></i> Selesai</span>
                                        <?php else: ?>
                                            <span style="color:#f59e0b; font-weight:600; font-size:10px;">Sisa: <?php echo ($it['qty'] - $it['qty_sent']); ?></span>
                                        <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                    <?php if(count($items) > 3): ?>
                                        <li style="color:#94a3b8; font-style:italic;">+ <?php echo count($items)-3; ?> item lainnya...</li>
                                    <?php endif; ?>
                                </ul>
                            </td>

                            <td class="text-center">
                                <?php if($d['status'] == 'Proses'): ?>
                                    <span class="badge-status bg-siap">SIAP KIRIM</span>
                                <?php else: ?>
                                    <span class="badge-status bg-selesai">SELESAI</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?php if($d['status'] == 'Proses'): ?>
                                    <button onclick="bukaModalKirim(<?php echo $d['id']; ?>, '<?php echo htmlspecialchars($d['nama_pelanggan']); ?>')" class="btn-action btn-kirim">
                                        <i class="fa fa-truck"></i> <span>Input Kirim</span>
                                    </button>
                                <?php else: ?>
                                    <div style="color:#10b981; font-weight:700; font-size:12px;">
                                        <i class="fa fa-check-circle" style="font-size:24px; display:block; margin-bottom:5px;"></i>
                                        LENGKAP
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalKirim" class="modal">
        <div class="modal-content">
            <form method="POST">
                <div style="padding:20px 25px; border-bottom:1px solid #e2e8f0; background:#f8fafc; display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; color:#1e293b; font-weight:700; font-size:16px;">Form Pengiriman Barang</h4>
                    <span onclick="document.getElementById('modalKirim').style.display='none'" style="cursor:pointer; font-size:20px; color:#64748b;">&times;</span>
                </div>
                <div style="padding:25px;">
                    <input type="hidden" name="id_order_kirim" id="id_order_kirim">
                    
                    <div style="display:flex; gap:15px; margin-bottom:20px;">
                        <div style="flex:1; padding:15px; border-radius:10px; background:#eff6ff; border:1px solid #bfdbfe;">
                            <div style="font-size:10px; color:#6366f1; font-weight:700; text-transform:uppercase; margin-bottom:5px;">Pelanggan</div>
                            <div id="nama_plg_modal" style="font-size:14px; font-weight:800; color:#1e293b;">-</div>
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:11px; color:#64748b; font-weight:700; display:block; margin-bottom:5px;">TANGGAL SURAT JALAN</label>
                            <input type="date" name="tgl_kirim" value="<?php echo date('Y-m-d', strtotime('next saturday')); ?>" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-weight:600; color:#334155; height:52px;">
                        </div>
                    </div>
                    
                    <div style="border-bottom:1px solid #e2e8f0; padding-bottom:10px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:12px; font-weight:700; color:#1e293b;">RINCIAN BARANG (SISA)</span>
                        <span style="font-size:10px; color:#64748b;">Isi Qty yang dikirim hari ini</span>
                    </div>
                    
                    <div id="list_item_kirim" style="border:1px solid #e2e8f0; border-radius:8px; max-height:280px; overflow-y:auto; background:#fff;"></div>
                </div>
                <div style="padding:15px 25px; background:#f8fafc; text-align:right; border-top:1px solid #e2e8f0;">
                    <button type="button" onclick="document.getElementById('modalKirim').style.display='none'" class="btn-action" style="background:#fff; border:1px solid #cbd5e1; color:#334155; margin-right:10px;">Batal</button>
                    <button type="submit" name="simpan_pengiriman" class="btn-action btn-kirim">Simpan & Cetak SJ</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if(!empty($swal_script)) echo $swal_script; ?>
        function bukaModalKirim(id, nama) {
            document.getElementById('id_order_kirim').value = id;
            document.getElementById('nama_plg_modal').innerText = nama;
            document.getElementById('modalKirim').style.display = 'block';
            document.getElementById('list_item_kirim').innerHTML = '<div style="text-align:center; padding:30px; color:#94a3b8;"><i class="fa fa-spinner fa-spin"></i> Memuat data...</div>';
            fetch(`get_items_for_shipping.php?id=${id}`).then(r => r.text()).then(html => { document.getElementById('list_item_kirim').innerHTML = html; });
        }
    </script>
</body>
</html>