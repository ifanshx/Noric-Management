<?php 
require_once '../../config/database.php';
// Set Timezone
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

if($_SESSION['role'] != 'admin') { 
    header("Location: ../dashboard.php"); 
    exit; 
}

// --- 1. FILTER PERIODE & STATUS ---
$default_awal  = date('Y-m-d');
$default_akhir = date('Y-m-d');

$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;
$status    = isset($_GET['status']) ? $_GET['status'] : '';

// --- 2. QUERY DATA ---
$sql = "SELECT * FROM orderan WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";

if(!empty($status)) {
    $sql .= " AND status = '$status'";
}

$sql .= " ORDER BY tanggal DESC, created_at DESC";
$q_data = mysqli_query($conn, $sql) or die(mysqli_error($conn));

// --- 3. PROSES DATA ---
$list_order = [];
$stats = [
    'total_trx' => 0,
    'total_qty_pesan' => 0,
    'total_qty_kirim' => 0,
    'cnt_pending' => 0,
    'cnt_selesai' => 0
];

while($d = mysqli_fetch_assoc($q_data)) {
    $oid = $d['id'];
    
    // Ambil Rincian Item + Hitung Akumulasi Kirim
    $q_items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id='$oid'");
    $items = [];
    $sub_qty_pesan = 0;
    $sub_qty_kirim = 0;

    while($it = mysqli_fetch_assoc($q_items)) {
        $sub_qty_pesan += $it['qty'];
        $sub_qty_kirim += $it['qty_sent'];
        $items[] = $it;
    }
    
    $d['items'] = $items; 
    $d['real_qty_pesan'] = $sub_qty_pesan;
    $d['real_qty_kirim'] = $sub_qty_kirim;
    
    // Hitung Persentase Selesai
    $d['progress'] = ($sub_qty_pesan > 0) ? round(($sub_qty_kirim / $sub_qty_pesan) * 100) : 0;

    // Statistik Global
    $stats['total_trx']++;
    if($d['status'] != 'Batal') {
        $stats['total_qty_pesan'] += $sub_qty_pesan;
        $stats['total_qty_kirim'] += $sub_qty_kirim;
    }
    
    if($d['status'] == 'Pending') $stats['cnt_pending']++;
    if($d['status'] == 'Selesai') $stats['cnt_selesai']++;

    $list_order[] = $d;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .laporan-wrapper { font-family: 'Inter', sans-serif; color: #1f2937; padding: 20px; }
        :root { 
            --accent-green: #10b981; 
            --accent-red: #ef4444; 
            --accent-blue: #3b82f6; 
            --accent-orange: #f97316; 
            --bg-light: #f9fafb;
        }

        /* HEADER & FILTER */
        .page-header-custom { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
        .page-header-custom h1 { font-size: 24px; font-weight: 800; color: #111827; margin: 0; }
        
        .filter-bar { background: #fff; padding: 15px 20px; border-radius: 12px; border: 1px solid #e5e7eb; display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 25px; align-items:center;}
        .date-input { border: 1px solid #e5e7eb; background: var(--bg-light); font-weight: 600; padding: 8px 12px; border-radius: 8px; font-size:13px; }
        .select-input { border: 1px solid #e5e7eb; background: var(--bg-light); padding: 8px 12px; border-radius: 8px; font-size:13px; min-width: 150px; }
        .btn-apply { background: #1f2937; color: #fff; padding: 8px 20px; border-radius: 8px; font-weight: 600; border: none; font-size:13px;}
        
        /* STATS */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; border-radius: 16px; padding: 20px; border: 1px solid #f3f4f6; position:relative; overflow:hidden;}
        .stat-card.blue { border-left: 4px solid var(--accent-blue); }
        .stat-card.orange { border-left: 4px solid var(--accent-orange); }
        .stat-card.green { border-left: 4px solid var(--accent-green); }
        
        .stat-title { font-size: 11px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 5px; color:#64748b;}
        .stat-number { font-size: 24px; font-weight: 800; color:#1e293b;}
        .stat-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 32px; opacity: 0.1; }

        /* TABLE MODERN */
        .report-card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden; margin-bottom: 30px; }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { background: #f9fafb; text-align: left; padding: 15px; font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 700; border-bottom: 1px solid #e5e7eb; letter-spacing: 0.5px; }
        .table-custom td { padding: 15px; border-bottom: 1px solid #f3f4f6; font-size: 13px; color: #374151; vertical-align: top; }
        
        /* Item List Clean */
        .item-list { list-style: none; padding: 0; margin: 0; }
        .item-list li { display: flex; justify-content: space-between; align-items:center; padding: 6px 0; border-bottom: 1px dashed #f3f4f6; font-size: 12px; }
        .item-list li:last-child { border-bottom: none; }
        
        .item-name { font-weight: 600; color: #4b5563; flex: 1; padding-right: 10px; }
        .item-stats { display: flex; gap: 8px; font-size: 11px; font-family: 'Inter', sans-serif; font-weight: 500; }
        
        .stat-pill { padding: 2px 6px; border-radius: 4px; display: inline-block; }
        .pill-pesan { color: #6b7280; background: #f3f4f6; }
        .pill-kirim { color: var(--accent-orange); background: #fff7ed; font-weight: 700; }
        .pill-done { color: var(--accent-green); background: #f0fdf4; font-weight: 700; }

        /* Progress Bar */
        .progress-wrapper { width: 100%; background: #f3f4f6; height: 6px; border-radius: 10px; margin-top: 6px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 10px; transition: width 0.5s ease; }
        .progress-label { font-size: 11px; font-weight: 700; display: block; margin-bottom: 2px; }

        /* Badges */
        .badge-soft { padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; display: inline-block; min-width: 70px; text-align: center; letter-spacing: 0.5px; }
        .bg-pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .bg-proses { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .bg-selesai { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .bg-batal { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

        /* PRINT SETTINGS */
        .print-header { display: none; }
        
        @media print {
            @page { margin: 0.5cm; size: auto; }
            body { background: white !important; -webkit-print-color-adjust: exact; }
            .no-print, .main-sidebar, .content-header { display: none !important; }
            .content-wrapper { padding: 0 !important; margin: 0 !important; }
            
            .print-header { display: block !important; width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; position: relative; }
            .kop-container { display: flex; align-items: center; justify-content: center; position: relative; min-height: 100px; }
            .kop-logo { position: absolute; left: 0; top: 5px; width: 80px; height: auto; }
            .kop-text { text-align: center; width: 100%; padding: 0 90px; }
            .kop-text h2 { margin: 0; font-size: 24px; font-weight: 800; text-transform: uppercase; color: #000; }
            
            .report-card { border: none !important; box-shadow: none !important; }
            .table-custom th { background: #eee !important; color: #000 !important; border: 1px solid #000; }
            .table-custom td { border: 1px solid #000; color: #000; }
            .badge-soft { border: 1px solid #000; background: none !important; color: #000 !important; }
            .stat-pill { border: none !important; background: none !important; color: #000 !important; }
            .progress-wrapper { border: 1px solid #000; background: none !important; }
            .progress-fill { background: #000 !important; }

            .signature-section { display: flex !important; margin-top: 50px; justify-content: space-between; page-break-inside: avoid; }
        }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .filter-bar { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="laporan-wrapper">
            
            <div class="print-header">
                <div class="kop-container">
                    <img src="../../assets/image/logo-noric.png" class="kop-logo" alt="Logo">
                    <div class="kop-text">
                        <h2>NORIC RACING EXHAUST</h2>
                        <p>JL. Ketuhu, Wirasana, Kec. Purbalingga, Kabupaten Purbalingga, Jawa Tengah 53318</p>
                        <p>Telp: (087) 817903710 | Email: marketing@noric-exhaust.com</p>
                    </div>
                </div>
                <div style="text-align:center; margin-top:10px;">
                    <h3 style="margin:0; text-decoration:underline;">LAPORAN PROGRESS ORDER</h3>
                    <p style="margin:0; font-size:12px;">Periode: <?php echo date('d/m/Y', strtotime($tgl_awal)); ?> s/d <?php echo date('d/m/Y', strtotime($tgl_akhir)); ?></p>
                </div>
            </div>

            <div class="page-header-custom no-print">
                <div>
                    <h1>Laporan Order & Pengiriman</h1>
                    <p style="margin-top:5px; color:#6b7280;">Monitoring detail pesanan dan status pengiriman barang.</p>
                </div>
                <button onclick="window.print()" class="btn-apply" style="background: white; color: #374151; border: 1px solid #d1d5db; display:flex; align-items:center; gap:8px;">
                    <i class="fa fa-print"></i> Cetak PDF
                </button>
            </div>

            <div class="filter-bar no-print">
                <form method="GET" id="filterForm" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; width:100%;">
                    <input type="date" name="tgl_awal" value="<?php echo $tgl_awal; ?>" class="date-input">
                    <span style="font-size:12px; font-weight:bold; color:#94a3b8;">s/d</span>
                    <input type="date" name="tgl_akhir" value="<?php echo $tgl_akhir; ?>" class="date-input">
                    
                    <select name="status" class="select-input">
                        <option value="">-- Semua Status --</option>
                        <option value="Pending" <?php if($status=='Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Proses" <?php if($status=='Proses') echo 'selected'; ?>>Proses</option>
                        <option value="Selesai" <?php if($status=='Selesai') echo 'selected'; ?>>Selesai</option>
                        <option value="Batal" <?php if($status=='Batal') echo 'selected'; ?>>Batal</option>
                    </select>

                    <button type="submit" class="btn-apply">Filter Data</button>
                </form>
            </div>

            <div class="stats-grid no-print">
                <div class="stat-card blue">
                    <div class="stat-title">Total Transaksi</div>
                    <div class="stat-number" style="color:#2563eb;"><?php echo number_format($stats['total_trx']); ?></div>
                    <i class="fa fa-shopping-cart stat-icon"></i>
                </div>
                <div class="stat-card orange">
                    <div class="stat-title">Item Dipesan</div>
                    <div class="stat-number" style="color:#f97316;"><?php echo number_format($stats['total_qty_pesan']); ?></div>
                    <i class="fa fa-cubes stat-icon"></i>
                </div>
                <div class="stat-card green">
                    <div class="stat-title">Item Terkirim</div>
                    <div class="stat-number" style="color:#10b981;"><?php echo number_format($stats['total_qty_kirim']); ?></div>
                    <i class="fa fa-truck stat-icon"></i>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Selesai / Pending</div>
                    <div class="stat-number"><?php echo $stats['cnt_selesai']; ?> / <?php echo $stats['cnt_pending']; ?></div>
                    <i class="fa fa-pie-chart stat-icon"></i>
                </div>
            </div>

            <div class="report-card">
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th width="12%">Tanggal</th>
                                <th width="20%">Pelanggan</th>
                                <th width="38%">Rincian Barang & Pengiriman</th>
                                <th width="20%" class="text-center">Progress Order</th>
                                <th width="10%" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($list_order)): ?>
                                <tr><td colspan="5" class="text-center p-5" style="color:#9ca3af;">Tidak ada data order pada periode ini.</td></tr>
                            <?php endif; ?>
                            
                            <?php foreach($list_order as $row): 
                                $status_cls = 'bg-pending';
                                $prog_color = '#f97316'; // Default Orange
                                if($row['status'] == 'Proses') $status_cls = 'bg-proses';
                                if($row['status'] == 'Selesai') { 
                                    $status_cls = 'bg-selesai'; 
                                    $prog_color = '#10b981'; // Green
                                }
                                if($row['status'] == 'Batal') $status_cls = 'bg-batal';
                            ?>
                            <tr>
                                <td style="font-family:'Courier Prime'; color:#6b7280; font-weight:600;"><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                
                                <td>
                                    <div style="font-weight:700; color:#1f2937; font-size:14px;"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></div>
                                    <?php if(!empty($row['keterangan'])): ?>
                                        <div style="font-size:11px; color:#c2410c; margin-top:4px; font-style:italic;">
                                            Note: <?php echo htmlspecialchars($row['keterangan']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <ul class="item-list">
                                        <?php foreach($row['items'] as $item): 
                                            $sisa = $item['qty'] - $item['qty_sent'];
                                            $pill_class = ($sisa <= 0) ? 'pill-done' : 'pill-kirim';
                                        ?>
                                            <li>
                                                <span class="item-name"><?php echo htmlspecialchars($item['nama_barang']); ?></span>
                                                <span class="item-stats">
                                                    <span class="stat-pill pill-pesan">Order: <?php echo $item['qty']; ?></span>
                                                    <span class="stat-pill <?php echo $pill_class; ?>">Kirim: <?php echo $item['qty_sent']; ?></span>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                                
                                <td class="text-center" style="padding:15px;">
                                    <span class="progress-label" style="color:<?php echo $prog_color; ?>;">
                                        <?php echo $row['real_qty_kirim']; ?> / <?php echo $row['real_qty_pesan']; ?> Unit
                                    </span>
                                    <div class="progress-wrapper">
                                        <div class="progress-fill" style="width: <?php echo $row['progress']; ?>%; background-color: <?php echo $prog_color; ?>;"></div>
                                    </div>
                                    <span style="font-size:10px; color:#6b7280; display:block; margin-top:3px;"><?php echo $row['progress']; ?>% Terkirim</span>
                                </td>
                                
                                <td class="text-center">
                                    <span class="badge-soft <?php echo $status_cls; ?>">
                                        <?php echo strtoupper($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background:#f9fafb; border-top:2px solid #e5e7eb;">
                            <tr>
                                <td colspan="3" class="text-right" style="font-weight:800; font-size:13px; color:#374151; padding-right:20px;">GRAND TOTAL PERIODE INI</td>
                                <td class="text-center" style="font-size:14px; font-weight:800; color:#10b981; font-family:'Courier Prime';">
                                    <?php echo number_format($stats['total_qty_kirim']); ?> / <?php echo number_format($stats['total_qty_pesan']); ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="print-only signature-section" style="display:none;">
                <div style="text-align:center; width:200px;">
                    <p>Dibuat Oleh,</p>
                    <br><br><br>
                    <p style="border-top:1px solid #000; padding-top:5px;">Admin Gudang</p>
                </div>
                <div style="text-align:center; width:200px;">
                    <p>Diketahui Oleh,</p>
                    <br><br><br>
                    <p style="border-top:1px solid #000; padding-top:5px;">Manager Operasional</p>
                </div>
            </div>

        </div> 
    </div> 
    
    <?php include '../../layout/footer.php'; ?>
</body>
</html>