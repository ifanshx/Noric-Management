<?php 
require_once '../../config/database.php';
require_once '../../config/function.php'; 
require_once '../../config/fingerspot_api.php'; 

// 1. SET TIMEZONE
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

$swal_script = "";

// --- LOGIKA AUTO PIN ---
$q_max = mysqli_query($conn, "SELECT MAX(CAST(pin AS UNSIGNED)) as max_pin FROM users");
$d_max = mysqli_fetch_assoc($q_max);
$next_pin = ($d_max['max_pin'] && $d_max['max_pin'] >= 1000) ? $d_max['max_pin'] + 1 : 1001;

// --- PROSES EDIT PASSWORD ---
if (isset($_POST['update_password'])) {
    $uid = (int)$_POST['user_id'];
    $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $new_pass, $uid);
    
    if (mysqli_stmt_execute($stmt)) {
        $swal_script = "Swal.fire({icon: 'success', title: 'Berhasil', text: 'Password berhasil diperbarui!', timer: 1500, showConfirmButton: false});";
    } else {
        $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'Gagal memperbarui password.'});";
    }
}

// --- PROSES TAMBAH KARYAWAN ---
if (isset($_POST['simpan_karyawan'])) {
    verify_csrf_token($_POST['csrf_token']);

    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $pin      = mysqli_real_escape_string($conn, trim($_POST['pin']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $status_karyawan = $_POST['status_karyawan']; 
    $is_mandor = 0;
    $group_id = NULL;
    $role = 'user';

    if ($status_karyawan === 'kepala_bengkel') {
        $role = 'kepala_bengkel';
        $status_karyawan = 'Tetap'; 
    } elseif ($status_karyawan == 'Borongan') {
        $tipe_borongan = $_POST['tipe_borongan']; 
        if ($tipe_borongan == 'Mandor') {
            $is_mandor = 1;
        } elseif ($tipe_borongan == 'Anggota') {
            $mandor_id = (int)$_POST['mandor_id'];
            $q_m = mysqli_query($conn, "SELECT group_id FROM users WHERE id='$mandor_id'");
            $d_m = mysqli_fetch_assoc($q_m);
            $group_id = $d_m['group_id']; 
        }
    }

    $no_hp    = !empty($_POST['no_hp']) ? mysqli_real_escape_string($conn, $_POST['no_hp']) : NULL;
    $tgl_masuk = !empty($_POST['tgl_masuk']) ? $_POST['tgl_masuk'] : date('Y-m-d');

    if (!ctype_digit($pin)) {
        $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'PIN harus angka!'});";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE pin = ? OR username = ?");
        mysqli_stmt_bind_param($stmt, "ss", $pin, $username);
        mysqli_stmt_execute($stmt);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
            $swal_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'PIN atau Username sudah terpakai!'});";
        } else {
            mysqli_begin_transaction($conn);
            try {
                $privilege = '1'; 
                $sync_status = 'pending';

                $stmt_ins = mysqli_prepare($conn, "INSERT INTO users (pin, fullname, username, privilege, password, role, status_karyawan, no_hp, tgl_masuk, sync_status, is_mandor, group_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt_ins, "ssssssssssii", $pin, $fullname, $username, $privilege, $password, $role, $status_karyawan, $no_hp, $tgl_masuk, $sync_status, $is_mandor, $group_id);
                mysqli_stmt_execute($stmt_ins);
                $user_id = mysqli_insert_id($conn);

                if ($is_mandor == 1) {
                    mysqli_query($conn, "UPDATE users SET group_id = '$user_id' WHERE id = '$user_id'");
                }

                if ($status_karyawan === 'Tetap' || $role === 'kepala_bengkel') {
                    $gapok  = preg_replace('/[^0-9]/', '', $_POST['gaji_pokok']);
                    $makan  = preg_replace('/[^0-9]/', '', $_POST['uang_makan']);
                    $lembur = preg_replace('/[^0-9]/', '', $_POST['gaji_lembur']); 
                    
                    $stmt_gaji = mysqli_prepare($conn, "INSERT INTO gaji_karyawan (user_id, gaji_pokok, uang_makan, gaji_lembur) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt_gaji, "iiii", $user_id, $gapok, $makan, $lembur);
                    mysqli_stmt_execute($stmt_gaji);
                }

                $sync_success = fingerspot_sync_user($pin, $fullname, $privilege);
                if ($sync_success) {
                    mysqli_query($conn, "UPDATE users SET sync_status = 'synced' WHERE id = $user_id");
                    $msg_sync = "dan <b class='text-success'>Sukses Sinkron</b>.";
                    $icon = "success";
                } else {
                    mysqli_query($conn, "UPDATE users SET sync_status = 'failed' WHERE id = $user_id");
                    $icon = "warning";
                }

                mysqli_commit($conn);
                $swal_script = "Swal.fire({ icon: '$icon', title: 'Berhasil', html: 'Karyawan <b>$fullname</b> berhasil disimpan', timer: 3000, showConfirmButton: false }).then(() => { window.location='karyawan.php'; });";

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $swal_script = "Swal.fire({icon: 'error', title: 'Error', text: 'Database Error: " . $e->getMessage() . "'});";
            }
        }
    }
}

// --- PROSES HAPUS ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = mysqli_prepare($conn, "SELECT pin FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if ($user) {
        if (!empty($user['pin'])) fingerspot_delete_user($user['pin']);
        mysqli_query($conn, "DELETE FROM users WHERE id = $id");
        $swal_script = "Swal.fire({icon: 'success', title: 'Dihapus', text: 'Data karyawan dihapus.', timer: 1500, showConfirmButton: false}).then(() => { window.location='karyawan.php'; });";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; color: #1e293b; }
        .content-wrapper { padding: 40px 20px; }
        
        /* Typography */
        h3 { font-weight: 700; letter-spacing: -0.02em; }
        .text-muted { color: #64748b !important; font-size: 0.9rem; }

        /* Modern Card Decoration */
        .modern-card { 
            background: #fff; 
            border-radius: 20px; 
            border: 1px solid rgba(226, 232, 240, 0.8); 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); 
            margin-bottom: 25px; 
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card-header-gradient { 
            background: #1e293b; 
            padding: 20px 25px; 
            color: #fff; 
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .card-header-light { 
            background: #fff; 
            padding: 20px 25px; 
            border-bottom: 1px solid #f1f5f9; 
            font-weight: 600;
        }

        /* Form Styling */
        .card-body { padding: 25px; }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { font-size: 0.75rem; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; display: block; }
        
        .form-control-custom { 
            height: 48px; 
            border-radius: 12px; 
            border: 1px solid #e2e8f0; 
            font-size: 0.9rem; 
            padding: 0 16px; 
            background: #ffffff; 
            width: 100%;
            transition: all 0.2s ease;
            color: #1e293b;
        }
        
        .form-control-custom:focus { 
            outline: none;
            border-color: #3b82f6; 
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); 
            background: #fff;
        }

        .form-control-custom[readonly] { background: #f1f5f9; cursor: not-allowed; color: #64748b; }

        /* Table Styling */
        .table-elegant { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-elegant thead th { 
            background: #f8fafc; 
            color: #64748b; 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            font-weight: 700; 
            padding: 16px 20px; 
            border-bottom: 1px solid #e2e8f0; 
            letter-spacing: 0.05em;
        }
        
        .table-elegant tbody td { 
            vertical-align: middle; 
            padding: 18px 20px; 
            border-bottom: 1px solid #f1f5f9; 
            font-size: 0.875rem; 
        }

        .table-elegant tbody tr:hover { background-color: #fbfcfe; }

        /* Status & Badges */
        .avatar-initial { 
            width: 40px; 
            height: 40px; 
            border-radius: 12px; 
            background: #eff6ff; 
            color: #3b82f6; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: 700; 
            font-size: 1rem; 
            margin-right: 15px;
            border: 1px solid #dbeafe;
        }

        .badge-soft { 
            padding: 6px 12px; 
            border-radius: 8px; 
            font-size: 0.7rem; 
            font-weight: 700; 
            display: inline-block;
            letter-spacing: 0.02em;
        }
        
        .badge-kepala { background: #f0f9ff; color: #0369a1; border: 1px solid #e0f2fe; }
        .badge-tetap { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .badge-borongan { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }

        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .dot-green { background: #22c55e; box-shadow: 0 0 8px rgba(34, 197, 94, 0.4); } 
        .dot-red { background: #ef4444; box-shadow: 0 0 8px rgba(239, 68, 68, 0.4); }

        /* Buttons */
        .btn-save { 
            width: 100%; 
            padding: 16px; 
            border-radius: 14px; 
            font-weight: 700; 
            font-size: 0.95rem;
            background: #1e293b; 
            color: #fff; 
            border: none; 
            cursor: pointer; 
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .btn-save:hover { background: #0f172a; transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .btn-save:active { transform: translateY(0); }

        .action-btn {
            height: 36px;
            width: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
            background: #fff;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-edit { color: #3b82f6; }
        .btn-edit:hover { background: #eff6ff; border-color: #3b82f6; }
        .btn-delete { color: #ef4444; }
        .btn-delete:hover { background: #fef2f2; border-color: #ef4444; }

        .hidden-section { display: none; transition: all 0.3s ease; }
        
        /* Time UI */
        .time-badge {
            background: #fff;
            padding: 8px 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            font-family: 'Monaco', 'Consolas', monospace;
            font-weight: 700;
            color: #3b82f6;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        
        .swal2-popup {
    border-radius: 24px !important;
    padding: 2rem !important;
    font-family: 'Inter', sans-serif !important;
}
.swal2-title {
    font-weight: 800 !important;
    color: #1e293b !important;
    letter-spacing: -0.02em !important;
}
.swal2-confirm {
    border-radius: 12px !important;
    padding: 12px 30px !important;
    font-weight: 600 !important;
}
.swal2-cancel {
    border-radius: 12px !important;
    padding: 12px 30px !important;
    font-weight: 600 !important;
}
.swal2-input {
    border-radius: 12px !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: none !important;
    height: 50px !important;
    font-size: 14px !important;
    transition: all 0.2s !important;
}
.swal2-input:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
}
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="row" style="margin-bottom: 40px; display: flex; align-items: center; justify-content: space-between;">
            <div class="col-md-8">
                <h3>Data Karyawan</h3>
                <p class="text-muted">Manajemen database personel, hak akses, dan sinkronisasi mesin absen.</p>
            </div>
            <div class="col-md-4 text-right">
                <span class="time-badge"><?php echo date('H:i:s'); ?></span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="modern-card">
                    <div class="card-header-gradient">
                        <i class="fa fa-user-plus"></i>
                        <span style="font-weight: 600;">Registrasi Karyawan</span>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="fullname" class="form-control-custom" placeholder="Masukkan nama sesuai KTP" required>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">No. HP</label>
                                        <input type="text" name="no_hp" class="form-control-custom" placeholder="08...">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Tgl Masuk</label>
                                        <input type="date" name="tgl_masuk" class="form-control-custom" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control-custom" placeholder="Digunakan untuk login aplikasi" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">PIN Fingerprint (Otomatis)</label>
                                <input type="text" name="pin" class="form-control-custom" value="<?php echo $next_pin; ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tipe Karyawan</label>
                                <select name="status_karyawan" id="status_karyawan" class="form-control-custom" onchange="toggleForm()">
                                    <option value="Tetap">Karyawan Tetap (Bulanan/Harian)</option>
                                    <option value="kepala_bengkel">Kepala Bengkel</option>
                                    <option value="Borongan">Karyawan Borongan (Produksi)</option>
                                </select>
                            </div>

                            <div id="section_borongan" class="hidden-section" style="background:#fefce8; padding:20px; border-radius:12px; margin-bottom:1.5rem; border:1px solid #fef08a;">
                                <div class="form-group">
                                    <label class="form-label">Role Produksi</label>
                                    <select name="tipe_borongan" id="tipe_borongan" class="form-control-custom" onchange="toggleTeam()">
                                        <option value="Perorangan">Perorangan</option>
                                        <option value="Mandor">Mandor (Kepala Regu)</option>
                                        <option value="Anggota">Anggota Regu</option>
                                    </select>
                                </div>
                                <div id="section_pilih_mandor" class="hidden-section">
                                    <label class="form-label">Pilih Mandor</label>
                                    <select name="mandor_id" class="form-control-custom">
                                        <?php 
                                            $q_mandor = mysqli_query($conn, "SELECT id, fullname FROM users WHERE is_mandor = 1 ORDER BY fullname ASC");
                                            while($m = mysqli_fetch_assoc($q_mandor)) echo "<option value='{$m['id']}'>{$m['fullname']}</option>";
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div id="section_gaji" style="background:#f0fdf4; padding:20px; border-radius:12px; margin-bottom:1.5rem; border:1px solid #dcfce7;">
                                <div class="form-group">
                                    <label class="form-label">Gaji Pokok / Hari</label>
                                    <input type="text" name="gaji_pokok" class="form-control-custom input-rupiah" placeholder="0">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Uang Makan / Hari</label>
                                    <input type="text" name="uang_makan" class="form-control-custom input-rupiah" placeholder="0">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Rate Lembur / Jam</label>
                                    <input type="text" name="gaji_lembur" class="form-control-custom input-rupiah" placeholder="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Password Login</label>
                                <input type="password" name="password" class="form-control-custom" placeholder="Minimal 6 karakter" required>
                            </div>

                            <button type="submit" name="simpan_karyawan" class="btn-save">
                                <i class="fa fa-check-circle" style="margin-right: 8px;"></i> Simpan Database
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="modern-card">
                    <div class="card-header-light">
                        <i class="fa fa-list-ul" style="margin-right: 10px; color: #3b82f6;"></i>
                        Daftar Personel Aktif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table-elegant">
                                <thead>
                                    <tr>
                                        <th>Informasi Karyawan</th>
                                        <th class="text-center">Status Role</th>
                                        <th>Fingerprint</th>
                                        <th class="text-center">Manajemen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $q = mysqli_query($conn, "SELECT * FROM users WHERE role IN ('user', 'kepala_bengkel') ORDER BY fullname ASC");
                                    while ($k = mysqli_fetch_assoc($q)) {
                                        $sync = ($k['sync_status'] == 'synced') ? 'dot-green' : 'dot-red';
                                        $initials = strtoupper(substr($k['fullname'], 0, 1));
                                    ?>
                                    <tr>
                                        <td>
                                            <div style="display:flex; align-items:center;">
                                                <div class="avatar-initial"><?php echo $initials; ?></div>
                                                <div>
                                                    <div style="font-weight:600; color: #0f172a;"><?php echo $k['fullname']; ?></div>
                                                    <div style="font-size:0.75rem; color:#64748b;">ID PIN: #<?php echo $k['pin']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                                if($k['role'] == 'kepala_bengkel') echo '<span class="badge-soft badge-kepala">Kepala Bengkel</span>';
                                                elseif($k['status_karyawan'] == 'Tetap') echo '<span class="badge-soft badge-tetap">Tetap</span>';
                                                else echo '<span class="badge-soft badge-borongan">Borongan</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; font-size: 0.8rem; font-weight: 500;">
                                                <span class="status-dot <?php echo $sync; ?>"></span>
                                                <span style="color: <?php echo ($k['sync_status'] == 'synced') ? '#15803d' : '#ef4444'; ?>">
                                                    <?php echo strtoupper($k['sync_status']); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button onclick="editPassword('<?php echo $k['id']; ?>', '<?php echo addslashes($k['fullname']); ?>')" class="action-btn btn-edit" title="Ganti Password">
                                                <i class="fa fa-key"></i>
                                            </button>
                                            <button onclick="confirmDelete('<?php echo $k['id']; ?>', '<?php echo addslashes($k['fullname']); ?>')" class="action-btn btn-delete" title="Hapus Data" style="margin-left: 5px;">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="formUpdatePassword" method="POST" style="display:none;">
        <input type="hidden" name="user_id" id="pass_user_id">
        <input type="hidden" name="new_password" id="pass_new_val">
        <input type="hidden" name="update_password" value="1">
    </form>

    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    <?php if ($swal_script) echo $swal_script; ?>

    // 1. Modal Ganti Password yang Lebih Keren
    function editPassword(id, name) {
        Swal.fire({
            title: 'Keamanan Akun',
            html: `Setel ulang kata sandi untuk personel:<br><b style="color:#3b82f6;">${name}</b>`,
            icon: 'info',
            iconColor: '#3b82f6',
            input: 'password',
            inputPlaceholder: 'Masukkan password baru...',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Update Sekarang',
            cancelButtonText: 'Batalkan',
            confirmButtonColor: '#1e293b',
            cancelButtonColor: '#f1f5f9',
            buttonsStyling: true,
            reverseButtons: true,
            customClass: {
                cancelButton: 'text-muted-btn' // CSS tambahan jika perlu
            },
            preConfirm: (pass) => {
                if (!pass) {
                    Swal.showValidationMessage('Password tidak boleh kosong!');
                } else if (pass.length < 6) {
                    Swal.showValidationMessage('Minimal 6 karakter untuk keamanan.');
                }
                return pass;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Efek Loading sebelum submit
                Swal.fire({
                    title: 'Memproses...',
                    didOpen: () => { Swal.showLoading(); }
                });
                document.getElementById('pass_user_id').value = id;
                document.getElementById('pass_new_val').value = result.value;
                document.getElementById('formUpdatePassword').submit();
            }
        });
    }

    // 2. Modal Konfirmasi Hapus yang Lebih Tegas
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Personel?',
            html: `Anda akan menghapus data <b>${name}</b>.<br><small class="text-danger">Tindakan ini akan menghapus akses login & riwayat permanen.</small>`,
            icon: 'warning',
            iconColor: '#ef4444',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus Permanen',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#f1f5f9',
            reverseButtons: true,
            focusCancel: true,
            backdrop: `rgba(15, 23, 42, 0.4)` // Backdrop sedikit lebih gelap agar fokus
        }).then((result) => {
            if (result.isConfirmed) {
                // Beri feedback visual sebelum redirect
                Swal.fire({
                    title: 'Menghapus...',
                    timer: 2000,
                    didOpen: () => { Swal.showLoading(); }
                });
                window.location.href = `karyawan.php?hapus=${id}`;
            }
        });
    }

    // Logika UI Toggle Tetap Sama
    function toggleForm() {
        const status = document.getElementById('status_karyawan').value;
        const bSec = document.getElementById('section_borongan');
        const gSec = document.getElementById('section_gaji');
        
        if(status === 'Borongan'){
            bSec.style.display = 'block';
            gSec.style.display = 'none';
        } else {
            bSec.style.display = 'none';
            gSec.style.display = 'block';
        }
    }

    function toggleTeam() {
        const tipe = document.getElementById('tipe_borongan').value;
        document.getElementById('section_pilih_mandor').style.display = (tipe === 'Anggota') ? 'block' : 'none';
    }

    document.querySelectorAll('.input-rupiah').forEach(input => {
        input.addEventListener('keyup', function() {
            let val = this.value.replace(/[^0-9]/g, '');
            this.value = val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        });
    });

    toggleForm();
</script>
</body>
</html>