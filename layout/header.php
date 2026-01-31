<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>NORIC SYSTEM</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/image/favicon.ico" type="image/x-icon">

    <style>
        :root { --primary: #3b82f6; --sidebar-w: 260px; --header-h: 70px; }
        body { font-family: 'Poppins', sans-serif; background-color: #f1f5f9; color: #334155; overflow-x: hidden; }

        /* HEADER STYLE */
        .top-navbar {
            background: #fff; height: var(--header-h);
            position: fixed; top: 0; left: 0; right: 0; z-index: 1001;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex; align-items: center; padding: 0 20px;
            margin-left: var(--sidebar-w); /* Default Desktop */
            transition: margin-left 0.3s ease-in-out;
        }

        /* TOMBOL BURGER */
        #sidebar-toggle {
            background: transparent; border: none; font-size: 22px; color: #334155;
            cursor: pointer; padding: 5px; margin-right: 15px;
        }

        .brand-text { font-weight: 800; font-size: 20px; color: #1e293b; display: flex; align-items: center; }
        .brand-text i { color: var(--primary); margin-right: 10px; }

        .top-right { margin-left: auto; display: flex; align-items: center; gap: 10px; }
        .profile-img { width: 35px; height: 35px; border-radius: 50%; border: 2px solid #e2e8f0; }
        .profile-info { text-align: right; line-height: 1.2; display: block; }
        
        /* CONTENT WRAPPER */
        .content-wrapper {
            margin-top: var(--header-h);
            margin-left: var(--sidebar-w);
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease-in-out;
        }

        /* RESPONSIVE CSS */
        @media (min-width: 769px) {
            body.sidebar-collapsed .top-navbar { margin-left: 0; }
            body.sidebar-collapsed .content-wrapper { margin-left: 0; }
        }

        @media (max-width: 768px) {
            .top-navbar { margin-left: 0; padding: 0 15px; }
            .content-wrapper { margin-left: 0; padding: 15px; }
            .brand-text span { display: none; } /* Hide text logo on mobile */
            .profile-info { display: none; }
        }

        @media print {
            .top-navbar, .main-sidebar, .sb-footer { display: none !important; }
            .content-wrapper { margin: 0 !important; padding: 0 !important; width: 100%; }
        }
    </style>
</head>
<body>

<nav class="top-navbar">
    <button id="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fa fa-bars"></i>
    </button>
    
    <div class="brand-text">
        <i class="fa fa-cube"></i> <span>NORIC SYSTEM</span>
    </div>

    <div class="top-right">
        <div class="profile-info">
            <div style="font-weight:700; font-size:13px;"><?php echo $_SESSION['fullname'] ?? 'User'; ?></div>
            <div style="font-size:10px; color:#94a3b8;"><?php echo ucfirst($_SESSION['role'] ?? 'Guest'); ?></div>
        </div>
        <img class="profile-img" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['fullname'] ?? 'U'); ?>&background=3b82f6&color=fff&bold=true">
    </div>
</nav>