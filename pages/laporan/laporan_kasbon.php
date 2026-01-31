<?php 
require_once '../../config/database.php';
// Set Timezone
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

if($_SESSION['role'] != 'admin') { 
    header("Location: ../dashboard.php"); 
    exit; 
}

// --- 1. FILTER DATA ---
$default_awal  = date('Y-m-01');
$default_akhir = date('Y-m-d');
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : $default_awal; 
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : $default_akhir;

// --- 2. QUERY DATA ---
$sql = "SELECT k.*, u.fullname, u.pin FROM kasbon k 
        JOIN users u ON k.user_id = u.id 
        WHERE (k.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir') 
        AND k.status != 'Pending' 
        ORDER BY k.tanggal DESC, k.created_at DESC";

$q_bon = mysqli_query($conn, $sql);

// --- 3. HITUNG SUMMARY ---
$total_pinjaman = 0;
$total_sisa     = 0;
$count_lunas    = 0;
$count_aktif    = 0;
$data_list      = [];

while($d = mysqli_fetch_assoc($q_bon)) {
    if($d['status'] == 'Approved') {
        $total_pinjaman += $d['nominal'];
        $sisa = $d['nominal'] - $d['terbayar'];
        $total_sisa += $sisa;
        
        if($sisa <= 0) $count_lunas++;
        else $count_aktif++;
    }
    $data_list[] = $d;
}
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
        .btn-apply { background: #1f2937; color: #fff; padding: 8px 20px; border-radius: 8px; font-weight: 600; border: none; font-size:13px;}
        
        /* Period Chips */
        .period-chips { display: flex; gap: 8px; margin-left: auto; }
        .chip { background: #f3f4f6; border: 1px solid #e5e7eb; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #6b7280; cursor: pointer; transition: 0.2s; }
        .chip:hover { border-color: var(--accent-blue); color: var(--accent-blue); background: #eff6ff; }

        /* STATS */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; border-radius: 16px; padding: 20px; position: relative; border: 1px solid #f3f4f6; overflow:hidden;}
        .stat-card.red { border-left: 4px solid var(--accent-red); }
        .stat-card.orange { border-left: 4px solid var(--accent-orange); }
        .stat-card.blue { border-left: 4px solid var(--accent-blue); }
        .stat-card.green { border-left: 4px solid var(--accent-green); }
        
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
        .val-danger { color: var(--accent-red); font-weight:700; }
        .val-success { color: var(--accent-green); font-weight:700; }
        
        /* Badges */
        .badge-pill { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; display: inline-block; min-width: 70px; text-align: center; }
        .bg-LUNAS { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .bg-AKTIF { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .bg-DITOLAK { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* PRINT SETTINGS */
        .print-header { display: none; }
        .info-print { display: none; }
        
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
            .badge-pill { border: 1px solid #000; background: none !important; color: #000 !important; }
            
            /* Tanda Tangan */
            .signature-section { display: flex !important; margin-top: 50px; justify-content: space-between; page-break-inside: avoid; }
            
            /* Info Tambahan */
            .info-print { display: block !important; margin-top: 20px; font-size: 10px; color: #000; border-top: 1px dashed #000; padding-top: 10px; }
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
                    <h3 style="margin:0; text-decoration:underline;">LAPORAN KASBON KARYAWAN</h3>
                    <p style="margin:0; font-size:12px;">Periode: <?php echo date('d/m/Y', strtotime($tgl_awal)); ?> s/d <?php echo date('d/m/Y', strtotime($tgl_akhir)); ?></p>
                </div>
            </div>

            <div class="page-header-custom no-print">
                <div class="page-title">
                    <h1>Laporan Kasbon</h1>
                    <p style="margin-top:5px; color:#64748b;">Monitoring pinjaman dan status pelunasan periode <b><?php echo date('d M Y', strtotime($tgl_awal)); ?></b> s/d <b><?php echo date('d M Y', strtotime($tgl_akhir)); ?></b></p>
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
                    <button type="submit" class="btn-apply">Filter Data</button>
                </form>

            </div>

            <div class="stats-grid no-print">
                <div class="stat-card red">
                    <div class="stat-title">Pinjaman Keluar</div>
                    <div class="stat-number" style="color:#ef4444;">Rp <?php echo number_format($total_pinjaman); ?></div>
                    <i class="fa fa-arrow-circle-up stat-icon"></i>
                </div>
                <div class="stat-card orange">
                    <div class="stat-title">Total Outstanding</div>
                    <div class="stat-number" style="color:#f97316;">Rp <?php echo number_format($total_sisa); ?></div>
                    <i class="fa fa-exclamation-circle stat-icon"></i>
                </div>
                <div class="stat-card blue">
                    <div class="stat-title">Hutang Aktif</div>
                    <div class="stat-number" style="color:#3b82f6;"><?php echo $count_aktif; ?> <span style="font-size:12px; color:#6b7280; font-weight:normal;">Transaksi</span></div>
                    <i class="fa fa-hourglass-half stat-icon"></i>
                </div>
                <div class="stat-card green">
                    <div class="stat-title">Sudah Lunas</div>
                    <div class="stat-number" style="color:#10b981;"><?php echo $count_lunas; ?> <span style="font-size:12px; color:#6b7280; font-weight:normal;">Transaksi</span></div>
                    <i class="fa fa-check-circle stat-icon"></i>
                </div>
            </div>

            <div class="report-card">
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th width="12%">Tanggal</th>
                                <th width="20%">Nama Karyawan</th>
                                <th>Keperluan</th>
                                <th width="15%" class="text-right">Nominal</th>
                                <th width="15%" class="text-right">Sisa Hutang</th>
                                <th width="10%" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($data_list)): ?>
                                <tr><td colspan="6" class="text-center p-5" style="color:#9ca3af;">Tidak ada data kasbon pada periode ini.</td></tr>
                            <?php endif; ?>
                            
                            <?php foreach($data_list as $d): 
                                $sisa = $d['nominal'] - $d['terbayar'];
                                $label = 'DITOLAK';
                                $bg_class = 'bg-DITOLAK';

                                if($d['status'] == 'Approved') {
                                    if($sisa <= 0) {
                                        $label = 'LUNAS'; $bg_class = 'bg-LUNAS';
                                    } else {
                                        $label = 'AKTIF'; $bg_class = 'bg-AKTIF';
                                    }
                                }
                            ?>
                            <tr>
                                <td class="font-mono" style="color:#6b7280;"><?php echo date('d/m/Y', strtotime($d['tanggal'])); ?></td>
                                <td>
                                    <div style="font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($d['fullname']); ?></div>
                                    <div style="font-size:10px; color:#94a3b8;">PIN: <?php echo $d['pin']; ?></div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($d['keterangan']); ?>
                                    <div style="font-size:10px; color:#64748b; margin-top:2px;">Tenor: <?php echo $d['tenor']; ?> Minggu</div>
                                </td>
                                <td class="text-right font-mono" style="font-weight:600;">Rp <?php echo number_format($d['nominal']); ?></td>
                                <td class="text-right font-mono">
                                    <?php if($d['status'] == 'Approved'): ?>
                                        <span class="<?php echo ($sisa>0)?'val-danger':'val-success'; ?>">
                                            Rp <?php echo number_format($sisa); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge-pill <?php echo $bg_class; ?>"><?php echo $label; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background:#f9fafb; border-top:2px solid #e5e7eb;">
                            <tr>
                                <td colspan="4" class="text-right" style="font-weight:800; font-size:13px; color:#374151; padding-right:20px;">TOTAL SISA PIUTANG (APPROVED)</td>
                                <td class="text-right font-mono" style="font-size:14px; font-weight:800; color:#ef4444;">Rp <?php echo number_format($total_sisa); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="info-print">
                * Laporan ini menampilkan data kasbon yang statusnya <b>Approved</b> (Uang Keluar) dan <b>Ditolak</b> (Arsip).<br>
                * Total Sisa Piutang hanya menghitung kasbon yang statusnya Approved.
            </div>

            <div class="print-only signature-section" style="display:none;">
                <div style="text-align:center; width:200px;">
                    <p>Dibuat Oleh,</p>
                    <br><br><br>
                    <p style="border-top:1px solid #000; padding-top:5px;">Admin Keuangan</p>
                </div>
                <div style="text-align:center; width:200px;">
                    <p>Diketahui Oleh,</p>
                    <br><br><br>
                    <p style="border-top:1px solid #000; padding-top:5px;">Pimpinan</p>
                </div>
            </div>

        </div> 
    </div> 
    
    <?php include '../../layout/footer.php'; ?>
    
    
</body>
</html>