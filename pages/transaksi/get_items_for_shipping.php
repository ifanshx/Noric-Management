<?php
require_once '../../config/database.php';

if(isset($_GET['id'])) {
    $oid = mysqli_real_escape_string($conn, $_GET['id']);
    $q = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id='$oid'");
    
    echo '<table style="width:100%; border-collapse:collapse; font-family:\'Inter\', sans-serif;">';
    echo '<tr style="background:#f3f4f6; color:#6b7280; font-size:10px; font-weight:700; text-transform:uppercase;">
            <td style="padding:10px 15px;">Nama Barang</td>
            <td style="text-align:center; width:50px;">Pesan</td>
            <td style="text-align:center; width:50px;">Sdh</td>
            <td style="width:90px; padding-right:15px; text-align:right;">Kirim Qty</td>
          </tr>';
    
    $count = 0;
    while($d = mysqli_fetch_assoc($q)) {
        $sisa = $d['qty'] - $d['qty_sent'];
        $is_full = ($sisa <= 0);
        
        $bg_row = $is_full ? '#f0fdf4' : '#fff'; 
        $color_name = $is_full ? '#166534' : '#1f2937';
        $icon = $is_full ? '<i class="fa fa-check-circle" style="color:#166534; font-size:12px; margin-left:5px;"></i>' : '';
        $val_input = $is_full ? 0 : $sisa;

        echo "<tr style='background:{$bg_row}; border-bottom:1px solid #f3f4f6;'>";
        
        // Nama
        echo "<td style='padding:10px 15px; font-size:13px; font-weight:600; color:{$color_name};'>";
        echo htmlspecialchars($d['nama_barang']) . $icon;
        echo "<input type='hidden' name='item_id[]' value='{$d['id']}'>";
        echo "</td>";
        
        // Angka
        echo "<td style='text-align:center; font-size:12px; color:#6b7280;'>{$d['qty']}</td>";
        echo "<td style='text-align:center; font-size:12px; font-weight:700; color:#f97316;'>{$d['qty_sent']}</td>";
        
        // Input
        echo "<td style='padding:8px 15px 8px 0; text-align:right;'>";
        if(!$is_full) {
            echo "<input type='number' name='qty_kirim_sekarang[]' value='{$val_input}' max='{$sisa}' min='0' 
                  style='width:100%; text-align:center; padding:6px; border:1px solid #d1d5db; border-radius:6px; font-weight:bold; outline:none; font-size:13px; color:#1f2937;' required>";
        } else {
            echo "<span style='font-size:10px; color:#166534; font-weight:700; background:#dcfce7; padding:2px 8px; border-radius:4px;'>LUNAS</span>";
            echo "<input type='hidden' name='qty_kirim_sekarang[]' value='0'>";
        }
        echo "</td></tr>";
        $count++;
    }
    
    if($count == 0) {
        echo "<tr><td colspan='4' style='padding:20px; text-align:center; font-size:12px; color:#9ca3af;'>Tidak ada item dalam order ini.</td></tr>";
    }
    
    echo '</table>';
}
?>