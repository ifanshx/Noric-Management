<?php 
require_once '../../config/database.php';
// Set Timezone
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

if($_SESSION['role'] != 'admin') { 
    header("Location: ../dashboard.php"); 
    exit; 
}

// --- 1. FILTER TANGGAL & SEARCH ---
$default_awal  = date('Y-m-01');
$default_akhir = date('Y-m-d');

$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal;
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;
$keyword   = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';

// --- 2. QUERY DATA ---
$sql = "SELECT p.*, u.fullname FROM produksi_borongan p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.status = 'Approved' 
        AND p.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";

if(!empty($keyword)) {
    $sql .= " AND (u.fullname LIKE '%$keyword%' OR p.jenis_pekerjaan LIKE '%$keyword%')";
}

$sql .= " ORDER BY p.tanggal DESC, p.created_at DESC";
$q_prod = mysqli_query($conn, $sql);

// --- 3. PRE-CALCULATION ---
$list_data = [];
$stats = [
    'tot_qty' => 0,
    'tot_upah' => 0,
    'karyawan_aktif' => [],
    'avg_qty' => 0
];

if($q_prod) {
    while($row = mysqli_fetch_assoc($q_prod)) {
        $stats['tot_qty'] += $row['jumlah'];
        $stats['tot_upah'] += $row['total_upah'];
        
        // Track unique employee
        if(!in_array($row['user_id'], $stats['karyawan_aktif'])) {
            $stats['karyawan_aktif'][] = $row['user_id'];
        }
        
        $list_data[] = $row;
    }
}

// Hitung durasi hari untuk rata-rata
$diff = strtotime($tgl_akhir) - strtotime($tgl_awal);
$days = max(1, round($diff / (60 * 60 * 24)) + 1); 
$stats['avg_qty'] = ($days > 0) ? round($stats['tot_qty'] / $days) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">

    <style>
        .laporan-wrapper { font-family: 'Inter', sans-serif; color: #1f2937; padding: 20px; }
        :root { 
            --accent-green: #10b981; 
            --accent-red: #ef4444; 
            --accent-blue: #3b82f6; 
            --accent-orange: #f97316; 
            --accent-purple: #8b5cf6;
        }

        /* HEADER & FILTER (Hidden in Print) */
        .page-header-custom { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
        .page-header-custom h1 { font-size: 24px; font-weight: 800; color: #111827; margin: 0; }
        
        .filter-bar { background: #fff; padding: 15px 20px; border-radius: 12px; border: 1px solid #e5e7eb; display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 25px; align-items:center;}
        .date-input { border: 1px solid #e5e7eb; background: #f9fafb; font-weight: 600; padding: 8px 12px; border-radius: 8px; font-size:13px; }
        .search-input { border: 1px solid #e5e7eb; background: #f9fafb; padding: 8px 12px; border-radius: 8px; font-size:13px; min-width: 250px; }
        .btn-apply { background: #1f2937; color: #fff; padding: 8px 20px; border-radius: 8px; font-weight: 600; border: none; font-size:13px;}
        
        /* Period Chips */
        .period-chips { display: flex; gap: 8px; margin-left: auto; }
        .chip { background: #f3f4f6; border: 1px solid #e5e7eb; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #6b7280; cursor: pointer; transition: 0.2s; }
        .chip:hover { border-color: var(--accent-blue); color: var(--accent-blue); background: #eff6ff; }

        /* STATS */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; border-radius: 16px; padding: 20px; position: relative; border: 1px solid #f3f4f6; overflow:hidden;}
        .stat-card.blue { border-left: 4px solid var(--accent-blue); }
        .stat-card.green { border-left: 4px solid var(--accent-green); }
        .stat-card.purple { border-left: 4px solid var(--accent-purple); }
        .stat-card.orange { border-left: 4px solid var(--accent-orange); }
        
        .stat-title { font-size: 11px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 5px; color:#64748b;}
        .stat-number { font-size: 24px; font-weight: 800; font-family: 'Inter', sans-serif; color:#1e293b;}
        .stat-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 32px; opacity: 0.1; }

        /* TABLE */
        .report-card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden; margin-bottom: 30px; }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { background: #f9fafb; text-align: left; padding: 12px 15px; font-size: 10px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; vertical-align:middle;}
        .table-custom td { padding: 10px 15px; border-bottom: 1px solid #f3f4f6; font-size: 12px; color: #1f2937; vertical-align:middle;}
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        
        /* Fonts & Colors */
        .font-mono { font-family: 'Courier Prime', monospace; font-weight: 600; font-size:12px; }
        .val-money { color: var(--accent-green); font-weight:700; }
        
        /* Badges */
        .badge-motor { background: #eff6ff; color: #1d4ed8; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; border: 1px solid #dbeafe; display: inline-block; margin-left: 5px; }

        /* PRINT SETTINGS */
        .print-header { display: none; }
        
        @media print {
            @page { margin: 0.5cm; size: auto; }
            body { background: white !important; margin: 0 !important; padding: 0 !important; -webkit-print-color-adjust: exact; }
            .no-print, .main-sidebar, .content-header, .navbar, .main-footer { display: none !important; }
            .content-wrapper { margin: 0 !important; padding: 0 !important; background: white !important; border: none !important; }
            .laporan-wrapper { padding: 0 !important; width: 100%; }

            /* KOP SURAT */
            .print-header { display: block !important; width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; position: relative; }
            .kop-container { display: flex; align-items: center; justify-content: center; position: relative; min-height: 100px; }
            .kop-logo { position: absolute; left: 0; top: 5px; width: 80px; height: auto; }
            .kop-text { text-align: center; width: 100%; padding: 0 90px; }
            .kop-text h2 { margin: 0; font-size: 24px; font-weight: 800; text-transform: uppercase; color: #000; line-height: 1.2; }
            .kop-text p { margin: 3px 0; font-size: 11px; color: #000; }

            /* Table Adjustment */
            .report-card { border: none !important; box-shadow: none !important; margin: 0 !important; }
            .table-custom th { background: #eee !important; color: #000 !important; border: 1px solid #000; }
            .table-custom td { border: 1px solid #000; color: #000; }
            
            /* Tanda Tangan */
            .signature-section { display: flex !important; margin-top: 50px; justify-content: space-between; page-break-inside: avoid; }
        }
        
        /* Mobile */
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .filter-bar { flex-direction:column; align-items:stretch;}
            .period-chips { margin-left: 0; overflow-x: auto; padding-bottom: 5px; width: 100%;}
            .chip { white-space: nowrap; }
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
                        <p>Telp: (087) 817903710 | Email: finance@noric-exhaust.com</p>
                    </div>
                </div>
                <div style="text-align:center; margin-top:10px;">
                    <h3 style="margin:0; text-decoration:underline;">LAPORAN HASIL PRODUKSI</h3>
                    <p style="margin:0; font-size:12px;">Periode: <?php echo date('d/m/Y', strtotime($tgl_awal)); ?> s/d <?php echo date('d/m/Y', strtotime($tgl_akhir)); ?></p>
                </div>
            </div>

            <div class="page-header-custom no-print">
                <div class="page-title">
                    <h1>Laporan Produksi</h1>
                    <p style="margin-top:5px; color:#64748b;">Monitoring borongan periode <b><?php echo date('d M Y', strtotime($tgl_awal)); ?></b> s/d <b><?php echo date('d M Y', strtotime($tgl_akhir)); ?></b></p>
                </div>
                <button onclick="window.print()" class="btn-apply" style="background: white; color: #374151; border: 1px solid #d1d5db;">
                    <i class="fa fa-print"></i> Cetak PDF
                </button>
            </div>

            <div class="filter-bar no-print">
                <form method="GET" id="filterForm" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; width:100%;">
                    <input type="date" name="tgl_awal" id="tgl_awal" value="<?php echo $tgl_awal; ?>" class="date-input">
                    <span style="font-size:12px; font-weight:bold; color:#94a3b8;">s/d</span>
                    <input type="date" name="tgl_akhir" id="tgl_akhir" value="<?php echo $tgl_akhir; ?>" class="date-input">
                    
                    <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" class="search-input" placeholder="Cari Nama / Pekerjaan...">
                    <button type="submit" class="btn-apply">Filter Data</button>
                </form>

             
            </div>

            <div class="stats-grid no-print">
                <div class="stat-card blue">
                    <div class="stat-title">Total Produksi</div>
                    <div class="stat-number" style="color:#2563eb;"><?php echo number_format($stats['tot_qty']); ?> <span style="font-size:12px; color:#6b7280; font-weight:normal;">Pcs</span></div>
                    <i class="fa fa-cubes stat-icon"></i>
                </div>
                <div class="stat-card green">
                    <div class="stat-title">Total Upah (Cost)</div>
                    <div class="stat-number" style="color:#10b981;">Rp <?php echo number_format($stats['tot_upah']); ?></div>
                    <i class="fa fa-money stat-icon"></i>
                </div>
                <div class="stat-card purple">
                    <div class="stat-title">Karyawan Aktif</div>
                    <div class="stat-number" style="color:#8b5cf6;"><?php echo count($stats['karyawan_aktif']); ?> <span style="font-size:12px; color:#6b7280; font-weight:normal;">Orang</span></div>
                    <i class="fa fa-users stat-icon"></i>
                </div>
                <div class="stat-card orange">
                    <div class="stat-title">Rata-rata / Hari</div>
                    <div class="stat-number" style="color:#f97316;">~<?php echo number_format($stats['avg_qty']); ?> <span style="font-size:12px; color:#6b7280; font-weight:normal;">Pcs</span></div>
                    <i class="fa fa-bar-chart stat-icon"></i>
                </div>
            </div>

            <div class="report-card">
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th width="12%" class="text-center">Tanggal</th>
                                <th width="20%">Nama Karyawan</th>
                                <th>Jenis Pekerjaan</th>
                                <th width="10%" class="text-center">Qty</th>
                                <th width="15%" class="text-right">Total Upah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($list_data)): ?>
                                <tr><td colspan="5" class="text-center p-5" style="color:#9ca3af;">Tidak ada data produksi untuk periode ini.</td></tr>
                            <?php endif; ?>
                            
                            <?php foreach($list_data as $d): 
                                $parts = explode(' - ', $d['jenis_pekerjaan']);
                                $job_name = $parts[0] ?? $d['jenis_pekerjaan'];
                                $motor_type = $parts[1] ?? null;
                            ?>
                            <tr>
                                <td class="text-center font-mono" style="color:#6b7280;"><?php echo date('d/m/Y', strtotime($d['tanggal'])); ?></td>
                                <td style="font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($d['fullname']); ?></td>
                                <td>
                                    <span style="font-weight:500; color:#374151;"><?php echo htmlspecialchars($job_name); ?></span>
                                    <?php if($motor_type): ?><span class="badge-motor"><?php echo $motor_type; ?></span><?php endif; ?>
                                </td>
                                <td class="text-center font-mono" style="font-weight:700;"><?php echo number_format($d['jumlah']); ?></td>
                                <td class="text-right font-mono val-money">Rp <?php echo number_format($d['total_upah']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background:#f9fafb; border-top:2px solid #e5e7eb;">
                            <tr>
                                <td colspan="3" class="text-right" style="font-weight:800; font-size:13px; color:#374151; padding-right:20px;">TOTAL PERIODE INI</td>
                                <td class="text-center font-mono" style="font-size:14px; font-weight:800; color:#2563eb;"><?php echo number_format($stats['tot_qty']); ?></td>
                                <td class="text-right font-mono" style="font-size:14px; font-weight:800; color:#10b981;">Rp <?php echo number_format($stats['tot_upah']); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="print-only signature-section" style="display:none;">
                <div style="text-align:center; width:200px;">
                    <p>Dibuat Oleh,</p>
                    <br><br><br>
                    <p style="border-top:1px solid #000; padding-top:5px;">Admin Produksi</p>
                </div>
                <div style="text-align:center; width:200px;">
                    <p>Diketahui Oleh,</p>
                    <br><br><br>
                    <p style="border-top:1px solid #000; padding-top:5px;">Kepala Produksi</p>
                </div>
            </div>

        </div> 
    </div> 
    
    <?php include '../../layout/footer.php'; ?>
    
    <script>
       
    </script>
</body>
</html>