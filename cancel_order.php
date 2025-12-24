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
        $conn->beginTransaction();

        // 2. Kiểm tra đơn hàng chính chủ
        $checkSql = "SELECT trang_thai FROM donhang WHERE id = :id AND user_id = :uid FOR UPDATE";
        $stmtCheck = $conn->prepare($checkSql);
        $stmtCheck->execute([':id' => $order_id, ':uid' => $user_id]);
        $order = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // Logic: Dù đang ở 0, 1, 2 hay 3, khi ấn Hủy đều chuyển về 4
            // (Chưa cộng kho, đợi Admin duyệt)
            if (in_array($order['trang_thai'], [0, 1, 2, 3])) {
                
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
            $conn->commit();
        } else {
            $conn->rollBack();
        }
    } catch (Exception $e) {
        $conn->rollBack();
    }
}

// Quay lại trang Profile
header("Location: profile.php");
exit();
?>