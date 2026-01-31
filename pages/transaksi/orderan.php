<?php 
require_once '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');
cek_login(); 

// PROTEKSI: Hanya Admin
if($_SESSION['role'] != 'admin') { 
    echo "<script>window.location='../dashboard.php';</script>";
    exit; 
}

$swal_script = "";

// --- 1. SIMPAN ORDER ---
if(isset($_POST['simpan_order'])) {
    $tgl   = $_POST['tanggal'];
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $ket   = mysqli_real_escape_string($conn, $_POST['keterangan']); 
    $stat  = 'Pending'; 
    
    $items = $_POST['item_nama']; 
    $qtys  = $_POST['item_qty'];    
    $total_qty = array_sum($qtys);

    mysqli_query($conn, "INSERT INTO orderan (tanggal, nama_pelanggan, keterangan, total_qty, status) VALUES ('$tgl', '$nama', '$ket', '$total_qty', '$stat')");
    $id_order = mysqli_insert_id($conn);

    if($id_order && !empty($items)) {
        foreach($items as $index => $nama_barang) {
            $qty_barang = (int)$qtys[$index];
            if(!empty($nama_barang) && $qty_barang > 0) {
                $nama_fix = mysqli_real_escape_string($conn, $nama_barang);
                mysqli_query($conn, "INSERT INTO order_items (order_id, nama_barang, qty, qty_sent) VALUES ('$id_order', '$nama_fix', '$qty_barang', 0)");
            }
        }
    }
    $swal_script = "Swal.fire({icon: 'success', title: 'Order Berhasil', text: 'Data tersimpan. Menunggu verifikasi.', timer: 2000, showConfirmButton: false});";
}

// --- 2. VERIFIKASI ---
if(isset($_GET['verifikasi_id'])) {
    $oid = $_GET['verifikasi_id'];
    mysqli_query($conn, "UPDATE orderan SET status='Proses' WHERE id='$oid'");
    echo "<script>window.location='orderan.php';</script>";
}

// --- 3. HAPUS ---
if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM order_items WHERE order_id='$id'"); 
    mysqli_query($conn, "DELETE FROM orderan WHERE id='$id'"); 
    echo "<script>window.location='orderan.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layout/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --accent: #4f46e5; --bg-body: #f3f4f6; --text-main: #1f2937; }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-main); }
        .content-wrapper { padding: 30px; }
        
        /* Cards */
        .modern-card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 25px;}
        .card-header-gradient { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); padding: 20px 25px; color: #fff; }
        .card-body { padding: 25px; }

        /* Form */
        .form-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block; }
        .form-control-lg { height: 42px; border-radius: 8px; border: 1px solid #d1d5db; width: 100%; padding: 8px 12px; font-size: 14px; }
        .form-control-lg:focus { border-color: var(--accent); outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

        /* Dynamic Item */
        .item-row { display: flex; gap: 10px; margin-bottom: 10px; }
        .btn-add-item { background: #eff6ff; color: var(--accent); border: 1px dashed var(--accent); width: 100%; padding: 10px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.2s; }
        .btn-add-item:hover { background: #e0e7ff; }

        /* Table */
        .table-custom th { background: #f9fafb; font-size: 11px; text-transform: uppercase; padding: 15px; color: #6b7280; text-align: left; border-bottom: 2px solid #e5e7eb; }
        .table-custom td { padding: 15px; border-bottom: 1px solid #f3f4f6; font-size: 13px; vertical-align: middle; }
        
        .badge-status { padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .bg-Pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .bg-Proses { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .bg-Selesai { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

        /* Buttons */
        .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: bold; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
        .btn-verif { background: #f59e0b; color: white; }
        .btn-print { background: #0ea5e9; color: white; }
        .btn-history { background: #fff; border: 1px solid #d1d5db; color: #374151; }
        
        /* Modal History */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 0; border-radius: 12px; width: 450px; max-width: 95%; animation: slideDown 0.3s ease-out; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        @keyframes slideDown { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }

        @media(max-width:768px){ .row { flex-direction: column; } }
    </style>
</head>
<body>
    <?php include '../../layout/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="row">
            
            <div class="col-md-5">
                <div class="modern-card">
                    <div class="card-header-gradient">
                        <h4 style="margin:0; font-weight:800;"><i class="fa fa-plus-circle"></i> &nbsp; Order Baru</h4>
                        <p style="margin:5px 0 0; opacity:0.8; font-size:13px;">Input pesanan pelanggan (Auto Pending).</p>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom:15px;">
                                        <label class="form-label">Tanggal</label>
                                        <input type="date" name="tanggal" class="form-control-lg" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom:15px;">
                                        <label class="form-label">Pelanggan</label>
                                        <input type="text" name="nama_pelanggan" class="form-control-lg" placeholder="Contoh: TOKO BERKAH" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:20px;">
                                <label class="form-label">Catatan Order</label>
                                <input type="text" name="keterangan" class="form-control-lg" placeholder="Opsional (Packing kayu, dll)...">
                            </div>

                            <div style="border-top:1px dashed #d1d5db; padding-top:15px; margin-bottom:20px;">
                                <label class="form-label" style="color:var(--accent); margin-bottom:10px;">Item Barang</label>
                                <div id="items_container">
                                    <div class="item-row">
                                        <input type="text" name="item_nama[]" class="form-control-lg" placeholder="Nama Barang" style="flex:3;" required>
                                        <input type="number" name="item_qty[]" class="form-control-lg" placeholder="Qty" style="flex:1; text-align:center;" required>
                                        <i class="fa fa-times-circle" style="color:#ef4444; font-size:18px; padding:10px; cursor:pointer;" onclick="this.parentNode.remove()"></i>
                                    </div>
                                </div>
                                <button type="button" class="btn-add-item" onclick="addItem()"><i class="fa fa-plus"></i> Tambah Baris Item</button>
                            </div>

                            <button type="submit" name="simpan_order" class="btn btn-primary" style="width:100%; padding:12px; font-weight:bold; border-radius:8px; background:var(--accent); border:none; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);">SIMPAN DATA</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="modern-card">
                    <div style="padding:20px; border-bottom:1px solid #f3f4f6; background:#fff;">
                        <h5 style="margin:0; font-weight:800; color:#1f2937;">Verifikasi & Monitoring</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Info Order</th>
                                    <th width="15%" class="text-center">Total Qty</th>
                                    <th width="15%" class="text-center">Status</th>
                                    <th width="20%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $q = mysqli_query($conn, "SELECT * FROM orderan ORDER BY CASE WHEN status='Pending' THEN 1 ELSE 2 END, tanggal DESC LIMIT 10");
                                while($d = mysqli_fetch_assoc($q)):
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:#1f2937;"><?php echo htmlspecialchars($d['nama_pelanggan']); ?></div>
                                        <div style="font-size:11px; color:#6b7280; font-family:'Courier Prime'; margin-top:2px;">
                                            <?php echo date('d M Y', strtotime($d['tanggal'])); ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div style="font-weight:700; color:#4f46e5;"><?php echo $d['total_qty']; ?></div>
                                        <small style="color:#9ca3af; font-size:10px;">Pcs</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-status bg-<?php echo $d['status']; ?>"><?php echo $d['status']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div style="display:flex; gap:10px; justify-content:center;">
                                            <?php if($d['status'] == 'Pending'): ?>
                                                <button onclick="verif(<?php echo $d['id']; ?>)" class="btn-action btn-verif" title="Verifikasi">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                                <button onclick="hapus(<?php echo $d['id']; ?>)" class="btn-action" style="background:#fee2e2; color:#ef4444; border:1px solid #fecaca;">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button onclick="printSPK(<?php echo $d['id']; ?>)" class="btn-action btn-print" title="Cetak SPK">
                                                    <i class="fa fa-print"></i>Cetak SPK
                                                </button>
                                                 <button onclick="lihatHistory(<?php echo $d['id']; ?>)" class="btn-action btn-history" title="Cetak Surat Jalan">
                                                    <i class="fa fa-print">Cetak SJ</i>
                                                </button>
                                          
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalHistory" class="modal">
        <div class="modal-content">
            <div style="padding:15px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                <h4 style="margin:0; font-size:16px; font-weight:700; color:#1f2937;">Riwayat Pengiriman Barang</h4>
                <span onclick="closeModal()" style="cursor:pointer; font-size:24px; color:#9ca3af;">&times;</span>
            </div>
            <div id="konten_history" style="padding:20px; max-height:400px; overflow-y:auto; background:#f9fafb;">
                </div>
        </div>
    </div>
    
    <?php include '../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if(!empty($swal_script)) echo $swal_script; ?>
        
        function addItem() {
            var div = document.createElement("div");
            div.className = "item-row";
            div.innerHTML = `<input type="text" name="item_nama[]" class="form-control-lg" placeholder="Nama Barang" style="flex:3;" required><input type="number" name="item_qty[]" class="form-control-lg" placeholder="Qty" style="flex:1; text-align:center;" required><i class="fa fa-times-circle" style="color:#ef4444; font-size:18px; padding:10px; cursor:pointer;" onclick="this.parentNode.remove()"></i>`;
            document.getElementById("items_container").appendChild(div);
        }

        function verif(id) {
            Swal.fire({title: 'Verifikasi Pembayaran?', text: "Order akan diteruskan ke produksi.", icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Verifikasi'}).then((result) => {
                if (result.isConfirmed) window.location.href = `orderan.php?verifikasi_id=${id}`;
            })
        }
        function hapus(id) {
            Swal.fire({title: 'Hapus Order?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus'}).then((result) => {
                if (result.isConfirmed) window.location.href = `orderan.php?hapus=${id}`;
            })
        }
        function printSPK(id) { window.open(`cetak_spk.php?id=${id}`, '_blank'); }
        
        function lihatHistory(id) {
            document.getElementById('modalHistory').style.display = 'block';
            document.getElementById('konten_history').innerHTML = '<div style="text-align:center; padding:20px;"><i class="fa fa-spinner fa-spin"></i> Memuat data...</div>';
            fetch(`get_shipping_history.php?id=${id}`).then(r => r.text()).then(html => { document.getElementById('konten_history').innerHTML = html; });
        }
        function closeModal() { document.getElementById('modalHistory').style.display = 'none'; }
    </script>
</body>
</html>