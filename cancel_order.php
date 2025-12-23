<?php
session_start();
require_once 'connect.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['ly_do'])) {
    $order_id = $_POST['order_id'];
    $ly_do    = trim($_POST['ly_do']);
    $user_id  = $_SESSION['user_id'];

    try {
        // 2. Kiểm tra đơn hàng có tồn tại, đúng chủ sở hữu và trạng thái hợp lệ (0, 1, 2)
        $checkSql = "SELECT trang_thai FROM donhang WHERE id = :id AND user_id = :uid";
        $stmtCheck = $conn->prepare($checkSql);
        $stmtCheck->execute([':id' => $order_id, ':uid' => $user_id]);
        $order = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        // Chấp nhận hủy nếu trạng thái là: 0 (Mới), 1 (Đã xác nhận), 2 (Đang giao)
        if ($order && in_array($order['trang_thai'], [0, 1, 2])) {
            
            // 3. Cập nhật trạng thái = 4 và lưu lý do vào cột `ly_do_huy`
            $updateSql = "UPDATE donhang 
                          SET trang_thai = 4, 
                              ly_do_huy = :lydo 
                          WHERE id = :id";
            
            $stmtUpdate = $conn->prepare($updateSql);
            $stmtUpdate->execute([
                ':lydo' => $ly_do,
                ':id'   => $order_id
            ]);
        }
    } catch (Exception $e) {
        // Có thể log lỗi ở đây nếu cần
    }
}

// 4. Quay lại trang Profile
header("Location: profile.php");
exit();
?>