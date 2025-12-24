<?php
// admin/save_phieu_ajax.php
require_once '../connect.php';

// Chỉ xử lý khi có request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    try {
        // 1. Lấy thông tin đơn hàng hiện tại
        $stmt = $conn->prepare("SELECT * FROM donhang WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $don = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($don) {
            // 2. Chuẩn bị thông tin lưu
            $ten = $don['ten_nguoi_nhan'];
            $dc  = $don['dia_chi_giao'];
            $tien = $don['tong_tien'];
            $note = "Xuất từ đơn hàng #" . $id;

            // 3. Insert vào bảng phieu_xuat
            $sql = "INSERT INTO phieu_xuat (ten_khach_hang, dia_chi, tong_tien, ghi_chu) 
                    VALUES (:ten, :dc, :tien, :note)";
            $stmt_insert = $conn->prepare($sql);
            $stmt_insert->execute([
                ':ten' => $ten,
                ':dc'  => $dc,
                ':tien'=> $tien,
                ':note'=> $note
            ]);

            echo "success"; // Trả về tín hiệu thành công
        } else {
            echo "error_not_found";
        }
    } catch (Exception $e) {
        echo "error_db";
    }
}
?>