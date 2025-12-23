<?php
// =================================================================================
// === CODE XỬ LÝ XÓA -> GÁN SESSION -> QUAY VỀ SANPHAM.PHP ========================
// =================================================================================

// 1. BẮT BUỘC KHỞI ĐỘNG SESSION ĐỂ GỬI THÔNG BÁO
session_start();

require_once '../connect.php'; 

// Kết nối DB (Check kỹ như các file trước)
if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    else if (isset($db)) $conn = $db;
}

// 2. KIỂM TRA ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // (Tùy chọn) Lấy tên sản phẩm để hiện thông báo cho đẹp
        $stmt_check = $conn->prepare("SELECT ten_sanpham FROM sanpham WHERE id = :id");
        $stmt_check->execute([':id' => $id]);
        $sp = $stmt_check->fetch(PDO::FETCH_ASSOC);
        $ten_sp = $sp ? $sp['ten_sanpham'] : 'Sản phẩm cũ';

        // --- THỰC HIỆN XÓA ---
        // Lưu ý: Nếu database có ràng buộc khóa ngoại (Foreign Key)
        // thì cần xóa ở bảng bienthe_sanpham trước, hoặc để CASCADE tự làm.
        // Code này giả định xóa trực tiếp hoặc CASCADE đã bật.
        
        $stmt = $conn->prepare("DELETE FROM sanpham WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // 3. QUAN TRỌNG: GÁN SESSION THÔNG BÁO THÀNH CÔNG
        $_SESSION['success_msg'] = "Đã xóa sản phẩm <b>$ten_sp</b> thành công!";

    } catch (PDOException $e) {
        // Nếu lỗi thì (tùy chọn) gán thông báo lỗi hoặc bỏ qua
        // $_SESSION['error_msg'] = "Lỗi: " . $e->getMessage();
    }
}

// 4. CHUYỂN HƯỚNG VỀ TRANG DANH SÁCH (SẼ TỰ HIỆN THÔNG BÁO)
header("Location: sanpham.php");
exit;
?>