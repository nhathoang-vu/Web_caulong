<?php
session_start();
require_once '../connect.php'; // Trỏ ra thư mục gốc để lấy file kết nối

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Lấy hành động (add, delete, update_qty, clear...)
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// --- 1. THÊM SẢN PHẨM VÀO GIỎ (ADD) ---
if ($action == 'add') {
    $id = intval($_POST['product_id']);
    $qty = intval($_POST['quantity']);
    // Lấy màu và size nếu có (nếu không chọn thì là chuỗi rỗng)
    $color = isset($_POST['color']) ? $_POST['color'] : '';
    $size = isset($_POST['size']) ? $_POST['size'] : '';

    if ($id > 0 && $qty > 0) {
        // Truy vấn DB lấy thông tin sản phẩm
        $stmt = $conn->prepare("SELECT ten_sanpham, gia_ban, gia_khuyenmai, hinh_anh FROM sanpham WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Ưu tiên giá khuyến mãi
            $price = ($product['gia_khuyenmai'] > 0) ? $product['gia_khuyenmai'] : $product['gia_ban'];
            
            // Tạo KEY duy nhất: ID + Màu + Size
            // Ví dụ: 10_Do_40. Giúp phân biệt các biến thể khác nhau của cùng 1 sản phẩm
            $key = $id . '_' . $color . '_' . $size;

            if (isset($_SESSION['cart'][$key])) {
                // Đã có -> Cộng dồn số lượng
                $_SESSION['cart'][$key]['qty'] += $qty;
            } else {
                // Chưa có -> Thêm mới
                $_SESSION['cart'][$key] = [
                    'id'    => $id,
                    'name'  => $product['ten_sanpham'],
                    'price' => $price,
                    'qty'   => $qty,
                    'img'   => $product['hinh_anh'],
                    'color' => $color,
                    'size'  => $size
                ];
            }
        }
    }

    // Nếu bấm nút "Mua Ngay" -> Chuyển hướng sang giỏ hàng luôn
    if (isset($_POST['buy_now'])) {
        header('Location: ../giohang.php');
        exit();
    }
    
    // Nếu là Ajax (Nút Thêm vào giỏ) -> Trả về JSON để hiện Popup
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['status' => 'success', 'message' => 'Đã thêm vào giỏ!']);
        exit();
    } else {
        // Trường hợp không dùng JS -> Load lại trang cũ
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

// --- 2. XÓA 1 SẢN PHẨM (DELETE) ---
if ($action == 'delete') {
    $key = isset($_GET['key']) ? $_GET['key'] : '';
    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }
    header('Location: ../giohang.php');
    exit();
}

// --- 3. XÓA HẾT GIỎ HÀNG (CLEAR) ---
if ($action == 'clear') {
    unset($_SESSION['cart']);
    header('Location: ../giohang.php');
    exit();
}

// --- 4. CẬP NHẬT SỐ LƯỢNG (UPDATE - Dùng cho Ajax ở trang giỏ hàng) ---
if ($action == 'update_qty') {
    $key = isset($_POST['key']) ? $_POST['key'] : '';
    $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;

    if (isset($_SESSION['cart'][$key])) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$key]); // Nếu giảm về 0 thì xóa luôn
        } else {
            $_SESSION['cart'][$key]['qty'] = $qty;
        }
    }
    echo "ok"; // Phản hồi cho JS biết đã xong
    exit();
}
?>