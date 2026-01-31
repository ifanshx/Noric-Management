<style>
    /* --- SIDEBAR STYLE MODERN --- */
    :root {
        --sidebar-w: 260px;
    }

    .main-sidebar {
        width: var(--sidebar-w);
        background: #1e293b; /* Warna Dasar Gelap */
        position: fixed;
        top: 0; bottom: 0; left: 0;
        z-index: 1002;
        overflow-y: auto; /* Scroll jika menu panjang */
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-right: 1px solid rgba(255,255,255,0.05);
        display: flex;
        flex-direction: column; /* Agar bisa atur layout atas-bawah */
    }
    
    /* Scrollbar Halus */
    .main-sidebar::-webkit-scrollbar { width: 4px; }
    .main-sidebar::-webkit-scrollbar-track { background: transparent; }
    .main-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

    /* 1. LOGO AREA (DIPERBAIKI) */
    .sb-logo-area {
        height: 70px;
        min-height: 70px; /* Fix tinggi */
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0,0,0,0.2);
    }
    .sb-logo-img {
        max-height: 70px; /* Ukuran logo */
        width: auto;
        transition: 0.2s;
    }
    .sb-logo-img:hover {
        transform: scale(1.05);
    }

    /* MENU LIST */
    .sidebar-menu { 
        padding: 15px 10px; 
        list-style: none; 
        margin: 0;
        flex-grow: 1; /* Isi ruang kosong agar logout bisa didorong ke bawah jika perlu */
    }
    
    /* Header Section */
    .sidebar-menu .header {
        color: #94a3b8; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        margin: 20px 0 10px 15px; 
        opacity: 0.7;
    }

    /* Link Item */
    .sidebar-menu li a {
        display: flex; align-items: center;
        padding: 12px 15px;
        color: #cbd5e1; text-decoration: none;
        font-size: 14px; font-weight: 500;
        border-radius: 10px; margin-bottom: 4px;
        transition: all 0.2s ease;
    }
    .sidebar-menu li a i { 
        width: 24px; text-align: center; margin-right: 12px; font-size: 17px; 
        transition: 0.2s; color: #94a3b8; 
    }

    /* Hover & Active State */
    .sidebar-menu li a:hover { 
        background: rgba(255,255,255,0.08); 
        color: #fff; 
        transform: translateX(4px);
    }
    .sidebar-menu li a:hover i { color: #fff; }

    .sidebar-menu li a.active {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: #fff; 
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); 
        font-weight: 600;
    }
    .sidebar-menu li a.active i { color: #fff; }

    /* TOMBOL LOGOUT KHUSUS */
    .menu-logout {
        margin-top: 30px; /* Jarak dari menu atas */
        background: rgba(239, 68, 68, 0.1) !important;
        color: #ef4444 !important;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .menu-logout i { color: #ef4444 !important; }
    
    .menu-logout:hover {
        background: #ef4444 !important;
        color: #fff !important;
        border-color: #ef4444;
    }
    .menu-logout:hover i { color: #fff !important; }

    /* OVERLAY MOBILE */
    #sidebar-overlay {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5); z-index: 1000; backdrop-filter: blur(2px);
    }
    
    /* MOBILE RESPONSIVE LOGIC */
    @media (max-width: 768px) { 
        .main-sidebar { transform: translateX(-100%); } 
        body.sidebar-open .main-sidebar { transform: translateX(0); }
        body.sidebar-open #sidebar-overlay { display: block; }
    }
</style>

<div id="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside class="main-sidebar">
    <div class="sb-logo-area">
        <img src="<?php echo $base_url; ?>assets/image/logo-noric.png" alt="NORIC SYSTEM" class="sb-logo-img">
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo $base_url; ?>pages/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fa fa-th-large"></i> <span>Dashboard</span>
            </a>
        </li>

        <?php if($_SESSION['role'] == 'user' || $_SESSION['role'] == 'kepala_bengkel'): ?>
            <li class="header">MENU PEGAWAI</li>
            
            <?php 
            // Cek Status Borongan (Jika Kepala Bengkel juga ikut borongan, dia bisa akses ini)
            $my_id = $_SESSION['user_id'];
            $q_cek = mysqli_query($conn, "SELECT status_karyawan FROM users WHERE id='$my_id'");
            $cek = mysqli_fetch_assoc($q_cek);
            
            if($cek && $cek['status_karyawan'] == 'Borongan'): 
            ?>
            <li>
                <a href="<?php echo $base_url; ?>pages/produksi/input_produksi.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'input_produksi.php' ? 'active' : ''; ?>">
                    <i class="fa fa-cube"></i> <span>Input Produksi</span>
                </a>
            </li>
            <?php endif; ?>

            <li>
                <a href="<?php echo $base_url; ?>pages/users/data_absen.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'data_absen.php' ? 'active' : ''; ?>">
                    <i class="fa fa-clock-o"></i> <span>Riwayat Absen</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/users/slip_gaji.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'slip_gaji.php' ? 'active' : ''; ?>">
                    <i class="fa fa-file-text-o"></i> <span>Slip Gaji</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/bon.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bon.php' ? 'active' : ''; ?>">
                    <i class="fa fa-money"></i> <span>Uang Makan & Kasbon</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if($_SESSION['role'] == 'kepala_bengkel'): ?>
             <li class="header">MENU OPERASIONAL</li>
             <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/pengiriman.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pengiriman.php' ? 'active' : ''; ?>">
                    <i class="fa fa-truck"></i> <span>Jadwal Pengiriman</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if($_SESSION['role'] == 'admin'): ?>
            <li class="header">MASTER DATA</li>
            <li>
                <a href="<?php echo $base_url; ?>pages/master/karyawan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'karyawan.php' ? 'active' : ''; ?>">
                    <i class="fa fa-users"></i> <span>Data Karyawan</span>
                </a>
            </li>
            
            <li class="header">TRANSAKSI</li>
            <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/orderan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orderan.php' ? 'active' : ''; ?>">
                    <i class="fa fa-shopping-cart"></i> <span>Orderan Masuk</span>
                </a>
            </li>
             <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/pengiriman.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pengiriman.php' ? 'active' : ''; ?>">
                    <i class="fa fa-truck"></i> <span>Jadwal Pengiriman</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/produksi/konfirmasi_produksi.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'konfirmasi_produksi.php' ? 'active' : ''; ?>">
                    <i class="fa fa-check-square-o"></i> <span>Verif. Produksi</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/konfirmasi_bon.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'konfirmasi_bon.php' ? 'active' : ''; ?>">
                    <i class="fa fa-handshake-o"></i> <span>Verif. Kasbon & Uang Makan</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>pages/transaksi/kas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kas.php' ? 'active' : ''; ?>">
                    <i class="fa fa-bank"></i> <span>Kas Operasional</span>
                </a>
            </li>
            
            <li class="header">LAPORAN</li>
            <li><a href="<?php echo $base_url; ?>pages/laporan/laporan_absensi.php"><i class="fa fa-list-alt"></i> <span>Lap. Absensi</span></a></li>
            <li><a href="<?php echo $base_url; ?>pages/laporan/laporan_gaji.php"><i class="fa fa-file-excel-o"></i> <span>Lap. Gaji</span></a></li>
            <li><a href="<?php echo $base_url; ?>pages/laporan/laporan_kasbon.php"><i class="fa fa-file-pdf-o"></i> <span>Lap. Kasbon</span></a></li>
            <li><a href="<?php echo $base_url; ?>pages/laporan/laporan_produksi.php"><i class="fa fa-bar-chart"></i> <span>Lap. Produksi</span></a></li>
            <li><a href="<?php echo $base_url; ?>pages/laporan/laporan_kas.php"><i class="fa fa-line-chart"></i> <span>Lap. Arus Kas</span></a></li>
            <li><a href="<?php echo $base_url; ?>pages/laporan/laporan_orderan.php"><i class="fa fa-cart-arrow-down"></i> <span>Lap. Orderan</span></a></li>

            <li class="header">PENGATURAN</li>
            <li><a href="<?php echo $base_url; ?>pages/settings/setting_gaji.php"><i class="fa fa-sliders"></i> <span>Aturan Gaji</span></a></li>
            <li><a href="<?php echo $base_url; ?>pages/settings/setting_jam_kerja.php"><i class="fa fa-clock-o"></i> <span>Jam & Sanksi</span></a></li>
            <li><a href="<?php echo $base_url; ?>pages/master/users.php"><i class="fa fa-cog"></i> <span>Admin System</span></a></li>
            <li><a href="<?php echo $base_url; ?>pages/master/tools_mesin.php"><i class="fa fa-wrench"></i> <span>Tools Mesin</span></a></li>
        <?php endif; ?>

        <li>
            <a href="<?php echo $base_url; ?>logout.php" onclick="return confirm('Yakin ingin keluar?')" class="menu-logout">
                <i class="fa fa-sign-out"></i> <span>KELUAR / LOGOUT</span>
            </a>
        </li>
        
        <li style="height: 30px;"></li>
    </ul>
</aside>