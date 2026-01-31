<?php
require_once 'config/database.php';

if (isset($_SESSION['status']) && $_SESSION['status'] == "login") {
    header("Location: pages/dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Gunakan prepared statement
    $stmt = mysqli_prepare($conn, "SELECT id, pin, username, password, fullname, role, status_karyawan FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        
        // Set session sesuai struktur baru
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['status']   = "login";

        header("Location: pages/dashboard.php");
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NORIC RACING EXHAUST</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/image/favicon.ico" type="image/x-icon">
    <link rel="icon" href="<?php echo $base_url; ?>assets/image/favicon.ico" type="image/x-icon">
    <style>
        body { 
            background: radial-gradient(circle at top left, #1e293b 0%, #0f172a 100%); 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-family: 'Inter', sans-serif; 
            margin: 0;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }
        .login-card { 
            background: #ffffff; 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); 
            border-radius: 16px; 
            overflow: hidden; 
            border: none;
        }
        .login-header { 
            background: #fff; 
            padding: 30px 30px 15px 30px; 
            text-align: center; 
        }
        /* Penyesuaian Logo */
        .login-logo {
            width: 300px;
            height: auto;
            margin-bottom: 15px;
        }
        .login-header h3 { 
            margin: 0; 
            font-weight: 700; 
            color: #1e293b;
            letter-spacing: -0.5px;
            font-size: 20px;
        }
        .login-header p {
            color: #64748b;
            font-size: 14px;
            margin-top: 5px;
        }
        .login-body { padding: 5px 35px 40px 35px; }
        
        .form-group { position: relative; margin-bottom: 20px; }
        .form-group i {
            position: absolute;
            left: 15px;
            top: 15px;
            color: #94a3b8;
            font-size: 16px;
        }
        .form-control { 
            height: 48px; 
            border-radius: 10px; 
            padding-left: 45px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        .form-control:focus {
            background: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .btn-login { 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
            color: #fff; 
            height: 48px; 
            border-radius: 10px; 
            font-weight: 600; 
            border: none; 
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            transition: all 0.3s;
        }
        .btn-login:hover { 
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
            color: #fff;
        }
        .alert-danger {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #b91c1c;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <img src="assets/image/logo-noric.png" alt="Noric Logo" class="login-logo">
            <h3>Silakan masuk ke akun Anda</h3>
        </div>
        <div class="login-body">
            <?php if($error): ?>
                <div class="alert alert-danger text-center">
                    <i class="fa fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <i class="fa fa-user"></i>
                    <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                </div>
                <div class="form-group">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" name="login" class="btn btn-login">
                    MASUK SEKARANG
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>