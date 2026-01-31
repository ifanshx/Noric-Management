<?php
require_once '../../config/database.php';

if(isset($_GET['id'])) {
    $oid = $_GET['id'];
    $q = mysqli_query($conn, "SELECT * FROM pengiriman WHERE order_id='$oid' ORDER BY tanggal DESC, created_at DESC");
    
    if(mysqli_num_rows($q) > 0) {
        echo '<div style="display:flex; flex-direction:column; gap:12px;">';
        while($d = mysqli_fetch_assoc($q)) {
            $pid = $d['id'];
            $tgl = date('d F Y', strtotime($d['tanggal']));
            
            // Hitung total item
            $q_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(qty_kirim) as total FROM pengiriman_items WHERE pengiriman_id='$pid'"));
            $total = $q_count['total'];

            echo "
            <div style='border:1px solid #e5e7eb; padding:12px 15px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,0.03);'>
                <div style='display:flex; align-items:center; gap:12px;'>
                    <div style='background:#eff6ff; width:35px; height:35px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#3b82f6;'>
                        <i class='fa fa-truck'></i>
                    </div>
                    <div>
                        <div style='font-weight:700; font-size:13px; color:#1f2937;'>$tgl</div>
                        <div style='font-size:11px; color:#6b7280;'>Total Dikirim: <b style='color:#f97316;'>$total Pcs</b></div>
                    </div>
                </div>
                <a href='cetak_surat_jalan.php?id=$pid' target='_blank' style='background:#fff; border:1px solid #d1d5db; color:#374151; padding:6px 12px; border-radius:6px; font-size:11px; font-weight:bold; text-decoration:none; display:flex; align-items:center; gap:5px; transition:0.2s;' onmouseover='this.style.borderColor=\"#3b82f6\";this.style.color=\"#3b82f6\"' onmouseout='this.style.borderColor=\"#d1d5db\";this.style.color=\"#374151\"'>
                    <i class='fa fa-print'></i> Cetak SJ
                </a>
            </div>";
        }
        echo '</div>';
    } else {
        echo "<div style='text-align:center; padding:30px; color:#9ca3af;'>
                <i class='fa fa-box-open' style='font-size:30px; margin-bottom:10px; color:#e5e7eb;'></i><br>
                <span style='font-size:13px;'>Belum ada riwayat pengiriman.</span>
              </div>";
    }
}
?>