<?php 
require_once '../../config/database.php';
require_once '../../config/fingerspot_api.php';
cek_login(); 

// PROTEKSI: Hanya Admin
if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses Ditolak!'); window.location='dashboard.php';</script>";
    exit;
}

$my_user_id = $_SESSION['user_id'];
$swal_script = "";

// --- PROSES UBAH ROLE ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($id == $my_user_id) {
        $swal_script = "Swal.fire('Gagal', 'Tidak bisa mengubah role akun sendiri!', 'error');";
    } else {
        if ($action === 'make_admin') {
            $new_role = 'admin';
            $new_privilege = '1'; 
            $msg = "User berhasil dinaikkan menjadi ADMIN!";
        } elseif ($action === 'make_user') {
            $new_role = 'user';
            $new_privilege = '0'; 
            $msg = "User dikembalikan menjadi USER Biasa!";
        } else {
            die("Aksi tidak valid.");
        }

        // Ambil Data User
        $stmt = mysqli_prepare($conn, "SELECT pin, fullname FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($user_data) {
            // Update DB
            $stmt_upd = mysqli_prepare($conn, "UPDATE users SET role = ?, privilege = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_upd, "ssi", $new_role, $new_privilege, $id);
            
            if(mysqli_stmt_execute($stmt_upd)) {
                // Sync ke Mesin Finger
                fingerspot_sync_user($user_data['pin'], $user_data['fullname'], $new_privilege);
                
                $swal_script = "Swal.fire({
                    title: 'Berhasil!',
                    text: '$msg',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => { window.location='users.php'; });";
            }
        }
    }
}

// --- PROSES HAPUS ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    if ($id == $my_user_id) {
        $swal_script = "Swal.fire('Error', 'Tidak bisa menghapus akun sendiri!', 'error');";
    } else {
        // Ambil PIN
        $stmt = mysqli_prepare($conn, "SELECT pin FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($user) {
            // Hapus dari Mesin Finger
            if (!empty($user['pin'])) {
                fingerspot_delete_user($user['pin']);
            }
            // Hapus dari DB
            mysqli_query($conn, "DELETE FROM users WHERE id = $id");
            
            $swal_script = "Swal.fire({
                title: 'Terhapus!',
                text: 'Data pengguna dan data di mesin fingerprint telah dihapus.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => { window.location='users.php'; });";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Poppins', sans-serif; }
        .content-wrapper { padding: 30px; }

        /* Card Style */
        .modern-card { background: #fff; border-radius: 12px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.03); margin-bottom: 25px; overflow: hidden; }
        .card-header-gradient { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 25px; color: #fff; display: flex; justify-content: space-between; align-items: center; }
        
        /* Table */
        .table-custom th { background: #f8fafc; color: #64748b; font-size: 12px; text-transform: uppercase; font-weight: 700; border-bottom: 2px solid #e2e8f0; padding: 15px; }
        .table-custom td { vertical-align: middle; padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
        
        /* Badges */
        .badge-role { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .bg-admin { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .bg-user { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        
        /* User Info */
        .user-info-box { display: flex; align-items: center; gap: 10px; }
        .user-avatar { width: 40px; height: 40px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #64748b; font-weight: bold; font-size: 16px; }
        
        /* Action Buttons */
        .btn-action { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 5px; }
        .btn-promote { background: #eff6ff; color: #1d4ed8; }
        .btn-promote:hover { background: #1d4ed8; color: #fff; }
        
        .btn-demote { background: #fff7ed; color: #c2410c; }
        .btn-demote:hover { background: #c2410c; color: #fff; }
        
        .btn-delete { background: #fef2f2; color: #b91c1c; }
        .btn-delete:hover { background: #b91c1c; color: #fff; }

        /* Search Bar */
        .search-box { position: relative; max-width: 300px; }
        .search-box input { padding-left: 35px; height: 40px; border-radius: 20px; border: 1px solid #e2e8f0; background: rgba(255,255,255,0.1); color: #fff; width: 100%; font-size: 13px; }
        .search-box input::placeholder { color: rgba(255,255,255,0.7); }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.7); }
        .search-box input:focus { background: #fff; color: #333; outline: none; box-shadow: 0 0 0 3px rgba(255,255,255,0.2); }
        .search-box input:focus + i { color: #333; }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="row mb-20" style="margin-bottom: 25px;">
            <div class="col-md-12">
                <h3 style="margin:0; font-weight:800; color:#1e293b;">Manajemen Pengguna</h3>
                <p class="text-muted" style="margin:5px 0 0;">Kelola hak akses dan sinkronisasi mesin fingerprint.</p>
            </div>
        </div>

        <div class="modern-card">
            <div class="card-header-gradient">
                <div>
                    <h4 style="margin:0; font-weight:700;"><i class="fa fa-users"></i> Daftar Karyawan</h4>
                </div>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Cari nama atau PIN...">
                    <i class="fa fa-search"></i>
                </div>
            </div>
            
            <div class="card-body p-0" style="padding:0;">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0" id="userTable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Lengkap</th>
                                <th>PIN / Username</th>
                                <th>Status</th>
                                <th>Role</th>
                                <th width="20%" class="text-center">Ubah Akses</th>
                                <th width="10%" class="text-center">Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($conn, "SELECT id, fullname, username, pin, role, status_karyawan FROM users ORDER BY role ASC, fullname ASC");
                            $no = 1;
                            while ($u = mysqli_fetch_assoc($q)) {
                                $is_me = ($u['id'] == $my_user_id);
                                $badge_cls = ($u['role'] === 'admin') ? 'bg-admin' : 'bg-user';
                                $initial = strtoupper(substr($u['fullname'], 0, 1));
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td>
                                    <div class="user-info-box">
                                        <div class="user-avatar"><?php echo $initial; ?></div>
                                        <div>
                                            <div style="font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($u['fullname']); ?></div>
                                            <div style="font-size:11px; color:#64748b;"><?php echo $u['status_karyawan']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-family:monospace; color:#3b82f6; font-weight:bold;">PIN: <?php echo $u['pin']; ?></div>
                                    <div style="font-size:11px; color:#94a3b8;">@<?php echo $u['username']; ?></div>
                                </td>
                                <td>
                                    <span class="label label-success" style="border-radius:10px; padding:3px 8px; font-size:10px;">AKTIF</span>
                                </td>
                                <td>
                                    <span class="badge-role <?php echo $badge_cls; ?>"><?php echo strtoupper($u['role']); ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if (!$is_me): ?>
                                        <?php if ($u['role'] === 'user'): ?>
                                            <a href="users.php?action=make_admin&id=<?php echo $u['id']; ?>" class="btn-action btn-promote" title="Promosikan jadi Admin">
                                                <i class="fa fa-arrow-up"></i> Jadikan Admin
                                            </a>
                                        <?php else: ?>
                                            <a href="users.php?action=make_user&id=<?php echo $u['id']; ?>" class="btn-action btn-demote" title="Turunkan jadi User">
                                                <i class="fa fa-arrow-down"></i> Jadikan User
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:11px; font-style:italic;">Akun Saya</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!$is_me): ?>
                                        <button onclick="confirmDelete(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['fullname']); ?>')" class="btn-action btn-delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
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
        // 1. Tampilkan Alert dari PHP (jika ada)
        <?php if(!empty($swal_script)) echo $swal_script; ?>

        // 2. Fitur Pencarian Cepat (JS)
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#userTable tbody tr');

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });

        // 3. Konfirmasi Hapus dengan SweetAlert
        function confirmDelete(id, nama) {
            Swal.fire({
                title: 'Hapus Pengguna?',
                text: "Anda akan menghapus user: " + nama + ". Data di mesin fingerprint juga akan dihapus.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya, Hapus Permanen',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `users.php?hapus=${id}`;
                }
            })
        }
    </script>
</body>
</html>