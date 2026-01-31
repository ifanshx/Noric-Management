<?php
require_once '../../config/database.php';
// Cek Login
session_start();
if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
    header("location:../../index.php?pesan=belum_login");
    exit;
}

if(!isset($_GET['id'])) {
    echo "ID Order tidak ditemukan.";
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil Data Header Order
$q_order = mysqli_query($conn, "SELECT * FROM orderan WHERE id='$id'");
$order = mysqli_fetch_assoc($q_order);

if(!$order) {
    echo "Data order tidak ditemukan.";
    exit;
}

// Ambil Detail Item
$q_items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id='$id'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK - <?php echo $order['nama_pelanggan']; ?></title>
    <style>
        /* Reset & Base */
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 12px; 
            color: #000; 
            margin: 0; 
            padding: 10px; 
        }

        /* Pengaturan Ukuran Kertas Portrait */
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }

        /* Kop Surat */
        .kop-header {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo {
            width: 70px; /* Sedikit dikecilkan untuk portrait */
            height: auto;
            margin-right: 20px;
        }
        .kop-text {
            flex-grow: 1;
            text-align: center;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .kop-text p {
            margin: 2px 0;
            font-size: 10px;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 20px;
        }

        /* Info Order - Menggunakan 2 kolom layout */
        .info-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .info-table {
            width: 48%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 12px;
        }
        .label {
            font-weight: bold;
            width: 80px;
        }

        /* Tabel Barang */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 8px;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-size: 11px;
            text-align: center;
        }
        .data-table td {
            font-size: 12px;
        }

        /* Tanda Tangan */
        .signature-wrapper {
            margin-top: 30px;
            display: flex;
            justify-content: space-around;
        }
        .sig-box {
            text-align: center;
            width: 200px;
        }
        .sig-space {
            height: 60px;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Helper */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="kop-header">
        <img src="../../assets/image/logo-noric.png" class="logo" alt="Logo">
        <div class="kop-text">
            <h2>NORIC RACING EXHAUST</h2>
            <p>JL. Ketuhu, Wirasana, Kec. Purbalingga, Kabupaten Purbalingga, Jawa Tengah 53318</p>
            <p>Telp: (087) 817903710 | Email: produksi@noric-exhaust.com</p>
        </div>
    </div>

    <div class="doc-title">SURAT PERINTAH KERJA (SPK)</div>

    <div class="info-container">
        <table class="info-table">
            <tr>
                <td class="label">NO. ORDER</td>
                <td>: <b>#<?php echo $order['id']; ?></b></td>
            </tr>
            <tr>
                <td class="label">TANGGAL</td>
                <td>: <?php echo date('d/m/Y', strtotime($order['tanggal'])); ?></td>
            </tr>
        </table>
        <table class="info-table">
            <tr>
                <td class="label">PEMESAN</td>
                <td>: <?php echo strtoupper($order['nama_pelanggan']); ?></td>
            </tr>
            <tr>
                <td class="label">STATUS</td>
                <td>: <?php echo strtoupper($order['status']); ?></td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 15px;">
        <strong>CATATAN:</strong><br>
        <div style="border: 1px solid #ccc; padding: 5px; min-height: 40px; margin-top: 5px;">
            <?php echo !empty($order['keterangan']) ? nl2br($order['keterangan']) : '-'; ?>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="55%">Nama Barang / Pekerjaan</th>
                <th width="15%">Qty</th>
                <th width="25%">Ceklis / Ket</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $total_qty = 0;
            while($item = mysqli_fetch_assoc($q_items)): 
                $total_qty += $item['qty'];
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td style="font-weight:bold;"><?php echo $item['nama_barang']; ?></td>
                <td class="text-center"><b><?php echo $item['qty']; ?></b> Pcs</td>
                <td></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right" style="font-weight:bold;">TOTAL BARANG</td>
                <td class="text-center" style="font-weight:bold;"><?php echo $total_qty; ?></td>
                <td style="background:#eee;"></td>
            </tr>
        </tfoot>
    </table>

    <div class="signature-wrapper">
        <div class="sig-box">
            <p>Admin / Pembuat</p>
            <div class="sig-space"></div>
            <div class="sig-name">( ..................................... )</div>
        </div>
        
        <div class="sig-box">
            <p>Kepala Produksi</p>
            <div class="sig-space"></div>
            <div class="sig-name">( ..................................... )</div>
        </div>
    </div>

    <div class="no-print" style="margin-top:50px; text-align:center;">
        <button onclick="window.history.back()" style="padding:10px 20px; cursor:pointer; background:#333; color:#fff; border:none; border-radius:5px;">Kembali</button>
        <button onclick="window.print()" style="padding:10px 20px; cursor:pointer; background:#28a745; color:#fff; border:none; border-radius:5px; margin-left:10px;">Cetak SPK</button>
    </div>

</body>
</html>