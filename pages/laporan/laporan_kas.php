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

// --- 2. HITUNG SALDO AWAL ---
$q_awal = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN jenis='Masuk' THEN nominal ELSE 0 END) as tot_masuk,
    SUM(CASE WHEN jenis='Keluar' THEN nominal ELSE 0 END) as tot_keluar,
    (SUM(CASE WHEN jenis='Masuk' AND metode='Cash' THEN nominal ELSE 0 END) - 
     SUM(CASE WHEN jenis='Keluar' AND metode='Cash' THEN nominal ELSE 0 END)) as awal_cash,
    (SUM(CASE WHEN jenis='Masuk' AND metode='ATM' THEN nominal ELSE 0 END) - 
     SUM(CASE WHEN jenis='Keluar' AND metode='ATM' THEN nominal ELSE 0 END)) as awal_atm
    FROM transaksi_kas WHERE tanggal < '$tgl_awal'");

$d_awal = mysqli_fetch_assoc($q_awal);
$saldo_awal_global = $d_awal['tot_masuk'] - $d_awal['tot_keluar'];
$saldo_awal_cash   = $d_awal['awal_cash'] ?? 0;
$saldo_awal_atm    = $d_awal['awal_atm'] ?? 0;

// --- 3. AMBIL DATA TRANSAKSI ---
$sql = "SELECT * FROM transaksi_kas 
        WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' 
        ORDER BY tanggal ASC, created_at ASC";
$q_kas = mysqli_query($conn, $sql);

// --- 4. PRE-CALCULATION & LOOPING ---
$total_masuk = 0;
$total_keluar = 0;
$mutasi_cash_masuk = 0;
$mutasi_cash_keluar = 0;
$mutasi_atm_masuk = 0;
$mutasi_atm_keluar = 0;

$list_data = [];
$running_saldo = $saldo_awal_global;

while($d = mysqli_fetch_assoc($q_kas)) {
    if($d['jenis'] == 'Masuk') {
        $total_masuk += $d['nominal'];
        $running_saldo += $d['nominal'];
        if($d['metode'] == 'Cash') $mutasi_cash_masuk += $d['nominal'];
        else $mutasi_atm_masuk += $d['nominal'];
    } else {
        $total_keluar += $d['nominal'];
        $running_saldo -= $d['nominal'];
        if($d['metode'] == 'Cash') $mutasi_cash_keluar += $d['nominal'];
        else $mutasi_atm_keluar += $d['nominal'];
    }
    $d['saldo_row'] = $running_saldo;
    $list_data[] = $d;
}

// --- 5. HITUNG SALDO AKHIR ---
$saldo_akhir_global = $saldo_awal_global + $total_masuk - $total_keluar;
$saldo_akhir_cash   = $saldo_awal_cash + $mutasi_cash_masuk - $mutasi_cash_keluar;
$saldo_akhir_atm    = $saldo_awal_atm + $mutasi_atm_masuk - $mutasi_atm_keluar;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">

    <style>
        .laporan-wrapper { font-family: 'Inter', sans-serif; color: #1f2937; padding: 20px; }
        :root { --accent-green: #10b981; --accent-red: #ef4444; --accent-blue: #3b82f6; --accent-orange: #f97316; --accent-purple: #8b5cf6; }

        /* HEADER & FILTER (Hidden in Print) */
        .page-header-custom { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
        .page-header-custom h1 { font-size: 24px; font-weight: 800; color: #111827; margin: 0; }
        .filter-bar { background: #fff; padding: 15px 20px; border-radius: 12px; border: 1px solid #e5e7eb; display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 25px; }
        .date-input { border: none; background: #f9fafb; font-weight: 600; padding: 5px; width: 130px; }
        .btn-apply { background: #1f2937; color: #fff; padding: 8px 20px; border-radius: 8px; font-weight: 600; border: none; }
        
        /* STATS (Hidden in Print) */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; border-radius: 16px; padding: 20px; position: relative; border: 1px solid #f3f4f6; }
        .stat-card.primary { background: linear-gradient(145deg, #2563eb, #1d4ed8); color: white; }
        .stat-card.cash { border-left: 4px solid var(--accent-orange); }
        .stat-card.atm { border-left: 4px solid var(--accent-purple); }
        .stat-title { font-size: 11px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 5px; }
        .stat-number { font-size: 22px; font-weight: 700; font-family: 'Courier Prime', monospace; }
        .stat-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 32px; opacity: 0.1; }

        /* TABLE */
        .report-card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden; margin-bottom: 30px; }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { background: #f9fafb; text-align: left; padding: 12px 15px; font-size: 11px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .table-custom td { padding: 10px 15px; border-bottom: 1px solid #f3f4f6; font-size: 12px; color: #1f2937; }
        .font-mono { font-family: 'Courier Prime', monospace; font-weight: 600; }
        
        /* Badges */
        .badge-custom { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; display: inline-block; }
        .badge-cash { background: #fff7ed; color: #9a3412; border: 1px solid #ffedd5; }
        .badge-atm { background: #f5f3ff; color: #5b21b6; border: 1px solid #ede9fe; }
        .badge-in { color: var(--accent-green); background: #ecfdf5; border: 1px solid #d1fae5; }
        .badge-out { color: var(--accent-red); background: #fef2f2; border: 1px solid #fee2e2; }

        /* PRINT SETTINGS (CRUCIAL FOR KOP SURAT) */
        .print-header { display: none; }
        
        @media print {
            @page {
                margin: 0.5cm; /* Margin kertas */
                size: auto;
            }
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact;
            }
            .no-print, .main-sidebar, .content-header, .navbar, .main-footer { 
                display: none !important; 
            }
            .content-wrapper { 
                margin: 0 !important; 
                padding: 0 !important; 
                background: white !important; 
                border: none !important;
            }
            .laporan-wrapper { 
                padding: 0 !important; 
                width: 100%;
            }

            /* STYLE KHUSUS KOP SURAT */
            .print-header {
                display: block !important;
                width: 100%;
                border-bottom: 3px double #000;
                margin-bottom: 20px;
                padding-bottom: 10px;
                position: relative; /* Agar child absolute bisa refer ke sini */
            }
            .kop-container {
                display: flex;
                align-items: center;
                justify-content: center; /* Center Teks */
                position: relative;
                min-height: 100px; /* Sesuaikan tinggi logo */
            }
            .kop-logo {
                position: absolute;
                left: 0;
                top: 5px;
                width: 80px; /* Ukuran Logo */
                height: auto;
            }
            .kop-text {
                text-align: center;
                width: 100%;
                padding: 0 90px; /* Padding kiri kanan agar teks tidak nabrak logo */
            }
            .kop-text h2 {
                margin: 0;
                font-size: 24px;
                font-weight: 800;
                text-transform: uppercase;
                color: #000;
                line-height: 1.2;
            }
            .kop-text p {
                margin: 3px 0;
                font-size: 11px;
                color: #000;
            }

            /* Table Adjustment for Print */
            .report-card { border: none !important; box-shadow: none !important; margin: 0 !important; }
            .table-custom th { background: #eee !important; color: #000 !important; border: 1px solid #000; }
            .table-custom td { border: 1px solid #000; color: #000; }
            
            /* Tanda Tangan */
            .signature-section { display: flex !important; margin-top: 50px; justify-content: space-between; page-break-inside: avoid; }
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
            </div>

            <div class="page-header-custom no-print">
                <div class="page-title">
                    <h1>Laporan Keuangan</h1>
                    <p>Monitoring arus kas periode <b><?php echo date('d M Y', strtotime($tgl_awal)); ?></b> s/d <b><?php echo date('d M Y', strtotime($tgl_akhir)); ?></b></p>
                </div>
                <button onclick="window.print()" class="btn-apply" style="background: white; color: #374151; border: 1px solid #d1d5db;">
                    <i class="fa fa-print"></i> Cetak PDF
                </button>
            </div>

            <div class="filter-bar no-print">
                <form method="GET" id="filterForm" style="display:flex; align-items:center; gap:10px; flex-grow:1;">
                    <i class="fa fa-calendar" style="color:#9ca3af; font-size:12px;"></i>
                    <input type="date" name="tgl_awal" id="tgl_awal" value="<?php echo $tgl_awal; ?>" class="date-input">
                    <span style="color:#9ca3af; font-size:12px;">s/d</span>
                    <input type="date" name="tgl_akhir" id="tgl_akhir" value="<?php echo $tgl_akhir; ?>" class="date-input">
                    <button type="submit" class="btn-apply">Filter</button>
                </form>
            </div>

            <div class="stats-grid no-print">
                <div class="stat-card">
                    <div class="stat-title">Saldo Awal</div>
                    <div class="stat-number" style="color: #4b5563;">Rp <?php echo number_format($saldo_awal_global); ?></div>
                    <i class="fa fa-history stat-icon"></i>
                </div>
                <div class="stat-card">
                    <div class="stat-title text-green">Total Pemasukan</div>
                    <div class="stat-number text-green">+ <?php echo number_format($total_masuk); ?></div>
                    <i class="fa fa-line-chart stat-icon"></i>
                </div>
                <div class="stat-card">
                    <div class="stat-title text-red">Total Pengeluaran</div>
                    <div class="stat-number text-red">- <?php echo number_format($total_keluar); ?></div>
                    <i class="fa fa-shopping-cart stat-icon"></i>
                </div>
                <div class="stat-card primary">
                    <div class="stat-title" style="color: rgba(255,255,255,0.8);">Saldo Akhir</div>
                    <div class="stat-number">Rp <?php echo number_format($saldo_akhir_global); ?></div>
                    <i class="fa fa-wallet stat-icon" style="opacity:0.2; color:white;"></i>
                </div>
            </div>

            <div class="row no-print" style="margin-bottom: 25px;">
                <div class="col-md-6 col-sm-6">
                    <div class="stat-card cash" style="flex-direction: row; justify-content: space-between; align-items: center; padding: 15px 25px; margin-bottom:10px;">
                        <div>
                            <div class="stat-title" style="color:#9a3412;">Dompet (Cash)</div>
                            <div class="stat-number" style="font-size:18px; color:#333;">Rp <?php echo number_format($saldo_akhir_cash); ?></div>
                        </div>
                        <i class="fa fa-money" style="font-size:24px; color:#fb923c;"></i>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="stat-card atm" style="flex-direction: row; justify-content: space-between; align-items: center; padding: 15px 25px; margin-bottom:10px;">
                        <div>
                            <div class="stat-title" style="color:#5b21b6;">Rekening (ATM)</div>
                            <div class="stat-number" style="font-size:18px; color:#333;">Rp <?php echo number_format($saldo_akhir_atm); ?></div>
                        </div>
                        <i class="fa fa-credit-card" style="font-size:24px; color:#a78bfa;"></i>
                    </div>
                </div>
            </div>

            <div class="report-card">
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th width="12%">Tanggal</th>
                                <th width="8%" class="text-center">Metode</th>
                                <th>Keterangan</th>
                                <th width="8%" class="text-center">Tipe</th>
                                <th width="15%" class="text-right">Masuk (+)</th>
                                <th width="15%" class="text-right">Keluar (-)</th>
                                <th width="15%" class="text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background:#f9fafb;">
                                <td colspan="6" style="text-align:right; font-weight:600; color:#4b5563;">SALDO AWAL PER <?php echo date('d/m/Y', strtotime($tgl_awal)); ?></td>
                                <td class="text-right font-mono" style="color:#111827;">Rp <?php echo number_format($saldo_awal_global); ?></td>
                            </tr>

                            <?php if(empty($list_data)): ?>
                                <tr><td colspan="7" class="text-center" style="padding:40px; color:#9ca3af;">Tidak ada transaksi pada periode ini.</td></tr>
                            <?php else: ?>
                                <?php foreach($list_data as $d): 
                                    $is_masuk = ($d['jenis'] == 'Masuk');
                                ?>
                                <tr>
                                    <td style="color:#6b7280; font-weight:500;"><?php echo date('d M Y', strtotime($d['tanggal'])); ?></td>
                                    <td class="text-center">
                                        <span class="badge-custom <?php echo ($d['metode'] == 'ATM') ? 'badge-atm' : 'badge-cash'; ?>">
                                            <?php echo $d['metode']; ?>
                                        </span>
                                    </td>
                                    <td style="font-weight:500; color:#374151;"><?php echo htmlspecialchars($d['keterangan']); ?></td>
                                    <td class="text-center">
                                        <span class="badge-custom <?php echo $is_masuk ? 'badge-in' : 'badge-out'; ?>">
                                            <?php echo strtoupper($d['jenis']); ?>
                                        </span>
                                    </td>
                                    <td class="text-right font-mono" style="color:var(--accent-green);">
                                        <?php echo $is_masuk ? number_format($d['nominal']) : '-'; ?>
                                    </td>
                                    <td class="text-right font-mono" style="color:var(--accent-red);">
                                        <?php echo !$is_masuk ? number_format($d['nominal']) : '-'; ?>
                                    </td>
                                    <td class="text-right font-mono" style="color:var(--accent-blue);">
                                        <?php echo number_format($d['saldo_row']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot style="background:#f9fafb; border-top:2px solid #e5e7eb;">
                            <tr>
                                <td colspan="4" style="text-align:right; font-weight:normal; color:#6b7280; text-transform:uppercase; font-size:11px; letter-spacing:1px; padding:15px;">Total Mutasi</td>
                                <td class="text-right font-mono text-green" style="padding:15px;">+ <?php echo number_format($total_masuk); ?></td>
                                <td class="text-right font-mono text-red" style="padding:15px;">- <?php echo number_format($total_keluar); ?></td>
                                <td></td>
                            </tr>
                            <tr style="background:#fff;">
                                <td colspan="6" style="text-align:right; font-size:14px; color:#111827; padding:20px;">SALDO AKHIR (Cash + ATM)</td>
                                <td class="text-right font-mono" style="font-size:16px; color:#2563eb; padding:20px;">Rp <?php echo number_format($saldo_akhir_global); ?></td>
                            </tr>
                            <tr class="print-only" style="border:none;">
                               <td colspan="7" style="text-align:right; font-size:20px; padding-top:5px; border:none;">
                                   Rincian: Cash (RP. <?php echo number_format($saldo_akhir_cash); ?>) | ATM (RP. <?php echo number_format($saldo_akhir_atm); ?>)
                               </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
                    <p style="border-top:1px solid #000; padding-top:5px;">Manager</p>
                </div>
            </div>

        </div> 
    </div> 
    
    <?php include '../../layout/footer.php'; ?>
</body>
</html>