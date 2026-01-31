<?php
// index.php di root folder
require_once 'config/database.php';

if (isset($_SESSION['status']) && $_SESSION['status'] == "login") {
    // Jika sudah login, lempar ke folder pages/dashboard.php
    header("location: pages/dashboard.php");
} else {
    // Jika belum, lempar ke login.php
    header("location: login.php");
}
?>