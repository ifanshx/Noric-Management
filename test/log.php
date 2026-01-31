<?php 
// --- DEBUGGING (HAPUS JIKA SUDAH BERHASIL) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- 1. KONEKSI (PATH DISESUAIKAN UNTUK FOLDER /test/) ---
// Menggunakan ../ karena folder test sejajar dengan config
if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
} else {
    die("Error: File database.php tidak ditemukan di '../config/database.php'. Cek struktur folder Anda.");
}

// Set Timezone
date_default_timezone_set('Asia/Jakarta');

// Cek Login (Pastikan fungsi ini ada di database.php atau file global lainnya)
if (function_exists('cek_login')) {
    cek_login(); 
} else {
    // Fallback manual jika fungsi tidak ditemukan
    session_start();
    if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
        header("Location: ../index.php");
        exit;
    }
}

if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

// --- 2. MAPPING USER PIN TO NAME ---
$user_map = [];
$q_user = mysqli_query($conn, "SELECT pin, fullname, group_id, is_mandor, status_karyawan FROM users");
if ($q_user) {
    while($u = mysqli_fetch_assoc($q_user)) {
        $user_map[$u['pin']] = [
            'nama' => $u['fullname'],
            'tipe' => $u['status_karyawan']
        ];
    }
}

// --- 3. PAGINATION LOGIC ---
$limit = 20; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$total_result = mysqli_query($conn, "SELECT COUNT(id) as total FROM t_log");
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $limit);

// --- 4. FETCH DATA LOG ---
$sql_log = "SELECT * FROM t_log ORDER BY created_at DESC LIMIT $start, $limit";
$q_log = mysqli_query($conn, $sql_log);

// Helper Verify Mode
function getVerifyMode($mode) {
    switch($mode) {
        case 1: return '<span class="badge badge-finger">Sidik Jari</span>';
        case 4: return '<span class="badge badge-face">Wajah</span>';
        case 15: return '<span class="badge badge-face">Wajah</span>';
        case 3: return '<span class="badge badge-pass">Password</span>';
        default: return '<span class="badge badge-card">Lainnya ('.$mode.')</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../layout/header.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <style>
        :root { --primary: #4338ca; --bg-body: #f8fafc; --text-dark: #1e293b; --text-muted: #64748b; --border-color: #e2e8f0; }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-dark); }
        .content-wrapper { padding: 30px; }
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }
        .card-header { padding: 20px 25px; border-bottom: 1px solid var(--border-color); background: #fff; display: flex; justify-content: space-between; align-items: center; }
        .table-responsive { overflow-x: auto; }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { background: #f8fafc; padding: 15px; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid var(--border-color); text-align: left; }
        .table-custom td { padding: 15px; border-bottom: 1px solid var(--border-color); font-size: 14px; vertical-align: middle; }
        .badge { padding: 4px 10px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .badge-finger { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
        .badge-face { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-pass { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
        .badge-card { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
        .log-photo { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; cursor: pointer; transition: transform 0.2s; }
        .log-photo:hover { transform: scale(1.1); border-color: var(--primary); }
        .no-photo { width: 40px; height: 40px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 18px; }
        .pagination { display: flex; list-style: none; padding: 0; margin: 20px 0 0; justify-content: flex-end; gap: 5px; }
        .pagination a { padding: 8px 12px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 500; transition: 0.2s; }
        .pagination a:hover { background: #f1f5f9; }
        .pagination a.active { background: var(--primary); color: #fff; border-color: var(--primary); }
        .pagination a.disabled { opacity: 0.5; pointer-events: none; }
        .btn-raw { background: none; border: none; color: #6366f1; font-size: 12px; cursor: pointer; text-decoration: underline; }
        .raw-box { display: none; background: #1e293b; color: #bef264; padding: 10px; border-radius: 8px; font-family: monospace; font-size: 11px; margin-top: 5px; max-width: 300px; word-break: break-all; }
    </style>
</head>
<body>
    <?php include '../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        
        <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                <h2 style="font-weight:800; color:#1e293b; margin:0;">Log Aktivitas Mesin</h2>
                <p style="margin:5px 0 0; color:#64748b;">Data mentah (Raw Data) yang diterima langsung dari mesin absensi Cloud.</p>
            </div>
            <button onclick="location.reload()" class="btn-refresh" style="padding:10px 20px; background:#fff; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer; font-weight:600; color:#475569;">
                <i class="fa fa-refresh"></i> Refresh Data
            </button>
        </div>

        <div class="modern-card">
            <div class="card-header">
                <h4 style="margin:0; font-size:16px; font-weight:700;"><i class="fa fa-list-alt text-primary"></i> Data Log Terbaru</h4>
                <span style="font-size:12px; color:#64748b;">Total Record: <b><?= number_format($total_row['total']) ?></b></span>
            </div>
            
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="15%">Waktu Terima</th>
                            <th width="20%">Karyawan (PIN)</th>
                            <th width="15%">Waktu Scan</th>
                            <th width="10%">Metode</th>
                            <th width="10%" class="text-center">Foto</th>
                            <th>Original Data (JSON)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($q_log) > 0):
                            while($row = mysqli_fetch_assoc($q_log)):
                                // JSON DECODE
                                $json_data = json_decode($row['original_data'], true);
                                $data_detail = $json_data['data'] ?? [];
                                
                                // Ambil Detail
                                $pin_mesin = $data_detail['pin'] ?? '-';
                                $scan_time = $data_detail['scan'] ?? '-';
                                $verify    = $data_detail['verify'] ?? 0;
                                $photo_url = $data_detail['photo_url'] ?? '-';
                                
                                // Match dengan Database User
                                $nama_karyawan = $user_map[$pin_mesin]['nama'] ?? "<span style='color:#ef4444; font-style:italic;'>Unknown</span>";
                        ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td style="color:#64748b; font-size:12px;"><?= $row['created_at'] ?></td>
                            <td>
                                <div><b><?= $nama_karyawan ?></b></div>
                                <div style="font-size:11px; color:#64748b;">PIN: <span style="font-family:monospace; font-weight:bold;"><?= $pin_mesin ?></span></div>
                            </td>
                            <td>
                                <div style="font-weight:700; color:#1e293b;"><?= ($scan_time != '-') ? date('H:i:s', strtotime($scan_time)) : '-' ?></div>
                                <div style="font-size:11px; color:#64748b;"><?= ($scan_time != '-') ? date('d M Y', strtotime($scan_time)) : '-' ?></div>
                            </td>
                            <td><?= getVerifyMode($verify) ?></td>
                            <td class="text-center">
                                <?php if($photo_url != '-' && filter_var($photo_url, FILTER_VALIDATE_URL)): ?>
                                    <a href="<?= $photo_url ?>" target="_blank">
                                        <img src="<?= $photo_url ?>" class="log-photo" alt="Foto">
                                    </a>
                                <?php else: ?>
                                    <div class="no-photo" style="margin:0 auto;"><i class="fa fa-user"></i></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-raw" onclick="toggleRaw(<?= $row['id'] ?>)">Lihat JSON</button>
                                <div id="raw_<?= $row['id'] ?>" class="raw-box">
                                    <?= htmlspecialchars($row['original_data']) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="7" class="text-center" style="padding:40px; color:#94a3b8;">Belum ada data log mesin.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="padding: 20px; border-top:1px solid #f1f5f9;">
                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=<?= $page-1 ?>"><i class="fa fa-chevron-left"></i> Prev</a>
                    <?php else: ?>
                        <a class="disabled"><i class="fa fa-chevron-left"></i> Prev</a>
                    <?php endif; ?>

                    <?php 
                    $start_loop = max(1, $page - 2);
                    $end_loop   = min($total_pages, $page + 2);
                    for($i = $start_loop; $i <= $end_loop; $i++): 
                    ?>
                        <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?= $page+1 ?>">Next <i class="fa fa-chevron-right"></i></a>
                    <?php else: ?>
                        <a class="disabled">Next <i class="fa fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <?php include '../layout/footer.php'; ?>
    <script>
        function toggleRaw(id) {
            var x = document.getElementById("raw_" + id);
            x.style.display = (x.style.display === "block") ? "none" : "block";
        }
    </script>
</body>
</html>