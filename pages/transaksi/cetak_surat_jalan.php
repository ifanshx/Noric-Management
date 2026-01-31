<?php
require_once '../../config/database.php';
// Gunakan pengecekan login manual jika fungsi cek_login() tidak standar
session_start();
if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
    header("location:../../index.php?pesan=belum_login");
    exit;
}

if(!isset($_GET['id'])) die("ID Pengiriman tidak ditemukan.");
$id_pengiriman = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil Header Pengiriman & Info Order
$q_header = mysqli_query($conn, "
    SELECT p.*, o.nama_pelanggan, o.keterangan as ket_order 
    FROM pengiriman p 
    JOIN orderan o ON p.order_id = o.id 
    WHERE p.id = '$id_pengiriman'
");
$header = mysqli_fetch_assoc($q_header);

if(!$header) die("Data tidak ditemukan.");

// Ambil Item Barang
$q_items = mysqli_query($conn, "
    SELECT pi.qty_kirim, oi.nama_barang 
    FROM pengiriman_items pi
    JOIN order_items oi ON pi.order_item_id = oi.id
    WHERE pi.pengiriman_id = '$id_pengiriman'
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - <?php echo $header['nama_pelanggan']; ?></title>
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
            width: 70px;
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
            text-transform: uppercase;
        }

        /* Info Pengiriman */
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
            width: 90px;
        }

        /* Tabel Barang */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table-data th, .table-data td {
            border: 1px solid #000;
            padding: 8px;
        }
        .table-data th {
            background-color: #f2f2f2;
            text-align: center;
            text-transform: uppercase;
        }

        /* Tanda Tangan - Disamakan dengan SPK */
        .signature-wrapper {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            text-align: center;
            width: 30%; /* Dibagi 3 kolom */
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

    <div class="doc-title">SURAT JALAN</div>

    <div class="info-container">
        <table class="info-table">
            <tr>
                <td class="label">KEPADA YTH.</td>
                <td>: <?php echo strtoupper($header['nama_pelanggan']); ?></td>
            </tr>
            <tr>
                <td class="label">CATATAN</td>
                <td>: <?php echo $header['ket_order'] ?: '-'; ?></td>
            </tr>
        </table>
        <table class="info-table">
            <tr>
                <td class="label">TGL. KIRIM</td>
                <td>: <?php echo date('d/m/Y', strtotime($header['tanggal'])); ?></td>
            </tr>
            <tr>
                <td class="label">NO. SJ</td>
                <td>: <b>SJ/<?php echo date('Y/m/', strtotime($header['tanggal'])) . $header['id']; ?></b></td>
            </tr>
        </table>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Barang / Item</th>
                <th width="15%">Qty Kirim</th>
                <th width="20%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no=1; $tot=0;
            while($d = mysqli_fetch_assoc($q_items)): 
                $tot += $d['qty_kirim'];
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td style="font-weight: bold;"><?php echo $d['nama_barang']; ?></td>
                <td class="text-center"><b><?php echo $d['qty_kirim']; ?></b> Pcs</td>
                <td></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right" style="font-weight:bold;">TOTAL BARANG</td>
                <td class="text-center" style="font-weight:bold;"><?php echo $tot; ?> Pcs</td>
                <td style="background:#eee;"></td>
            </tr>
        </tfoot>
    </table>

    <div class="signature-wrapper">
        <div class="sig-box">
            <p>Penerima / Customer</p>
            <div class="sig-space"></div>
            <div class="sig-name">( ............................ )</div>
        </div>
        
        <div class="sig-box">
            <p>Supir / Ekspedisi</p>
            <div class="sig-space"></div>
            <div class="sig-name">( ............................ )</div>
        </div>

        <div class="sig-box">
            <p>Hormat Kami,</p>
            <div class="sig-space"></div>
            <div class="sig-name">( Admin Gudang )</div>
        </div>
    </div>

    <div class="no-print" style="margin-top:50px; text-align:center;">
        <button onclick="window.history.back()" style="padding:10px 20px; cursor:pointer; background:#333; color:#fff; border:none; border-radius:5px;">Kembali</button>
        <button onclick="window.print()" style="padding:10px 20px; cursor:pointer; background:#28a745; color:#fff; border:none; border-radius:5px; margin-left:10px;">Cetak Surat Jalan</button>
    </div>

</body>
</html>