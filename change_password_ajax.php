<?php
session_start();
require_once 'connect.php'; 

header('Content-Type: application/json');

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập!']);
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Xử lý dữ liệu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $current_pass = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_pass     = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_pass = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // --- KIỂM TRA INPUT TRỐNG ---
    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        exit();
    }

    // --- KIỂM TRA MẬT KHẨU MỚI TRÙNG MẬT KHẨU CŨ ---
    if ($current_pass === $new_pass) {
        echo json_encode(['status' => 'error', 'message' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại!']);
        exit();
    }

    // --- KIỂM TRA XÁC NHẬN MẬT KHẨU ---
    if ($new_pass !== $confirm_pass) {
        echo json_encode(['status' => 'error', 'message' => 'Mật khẩu xác nhận không khớp!']);
        exit();
    }

    // --- KIỂM TRA ĐỘ MẠNH MẬT KHẨU (REGEX) ---
    // Yêu cầu: Ít nhất 1 chữ hoa, 1 số, 1 ký tự đặc biệt
    if (!preg_match('/[A-Z]/', $new_pass) ||       // Phải có chữ hoa
        !preg_match('/[0-9]/', $new_pass) ||       // Phải có số
        !preg_match('/[\W_]/', $new_pass)) {       // Phải có ký tự đặc biệt (!@#$%^&...)
        
        echo json_encode([
            'status' => 'error', 
            'message' => 'Mật khẩu mới phải có ít nhất: 1 chữ in hoa, 1 số và 1 ký tự đặc biệt!'
        ]);
        exit();
    }

    try {
        // Lấy mật khẩu cũ trong DB
        $sql = "SELECT password FROM user WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra mật khẩu cũ có đúng không
        if (md5($current_pass) !== $user['password']) {
            echo json_encode(['status' => 'error', 'message' => 'Mật khẩu hiện tại không chính xác!']);
            exit();
        }

        // Cập nhật mật khẩu mới (Mã hóa MD5)
        $new_pass_hash = md5($new_pass);
        $updateSql = "UPDATE user SET password = :password WHERE id = :id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([
            ':password' => $new_pass_hash,
            ':id'       => $user_id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Đổi mật khẩu thành công!']);
        exit();

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        exit();
    }
}
?>