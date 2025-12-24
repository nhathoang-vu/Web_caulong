<?php
session_start();
require_once 'connect.php'; 

// 1. XỬ LÝ DỮ LIỆU TỪ GIỎ HÀNG
$pay_items = [];
$total_money = 0;

if (isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
    foreach ($_POST['selected_items'] as $key) {
        if (isset($_SESSION['cart'][$key])) {
            $item = $_SESSION['cart'][$key];
            $pay_items[$key] = $item;
            $total_money += $item['price'] * $item['qty'];
        }
    }
}

// 2. MIỄN PHÍ VẬN CHUYỂN
$shipping_fee = 0; 
$grand_total = $total_money + $shipping_fee;

// 3. LẤY THÔNG TIN USER TỰ ĐỘNG (QUERY TRỰC TIẾP TỪ DATABASE)
$u_ten    = '';
$u_sdt    = '';
$u_email  = '';
$u_diachi = '';

// Kiểm tra xem người dùng đã đăng nhập chưa
// (Hỗ trợ cả 2 kiểu lưu session phổ biến là $_SESSION['user_id'] hoặc $_SESSION['user']['id'])
$user_id = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
    $user_id = $_SESSION['user']['id'];
}

// Nếu có ID người dùng, thực hiện truy vấn CSDL để lấy thông tin mới nhất
if ($user_id > 0) {
    try {
        $stmt = $conn->prepare("SELECT tendaydu, sdt, email, diachi FROM user WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $u_ten    = $user_data['tendaydu'];
            $u_sdt    = $user_data['sdt'];
            $u_email  = $user_data['email'];
            $u_diachi = $user_data['diachi'];
        }
    } catch (PDOException $e) {
        // Nếu lỗi kết nối thì bỏ qua, để form trống
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - HBG Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    
    <link rel="stylesheet" href="assets/css/thanhtoan.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main class="checkout-page">
        
        <div class="container-fluid-custom">
            
            <form action="xulythanhtoan.php" method="POST" class="checkout-layout">
                
                <div class="col-info">
                    <h3 class="checkout-title">Thông tin giao hàng</h3>
                    
                    <div class="form-group">
                        <label>Họ và tên (*)</label>
                        <input type="text" name="hoten" class="form-control" placeholder="Nhập họ tên" required 
                               value="<?php echo htmlspecialchars($u_ten); ?>">
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại (*)</label>
                        <input type="text" name="sdt" class="form-control" placeholder="Nhập số điện thoại" required
                               value="<?php echo htmlspecialchars($u_sdt); ?>">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Nhập email"
                               value="<?php echo htmlspecialchars($u_email); ?>">
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ nhận hàng (*)</label>
                        <textarea name="diachi" class="form-control" rows="3" placeholder="Địa chỉ chi tiết..." required><?php echo htmlspecialchars($u_diachi); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="ghichu" class="form-control" rows="2" placeholder="Lưu ý cho shop..."></textarea>
                    </div>
                </div>

                <div class="col-order">
                    <h3 class="checkout-title">Đơn hàng của bạn</h3>

                    <?php if (count($pay_items) > 0): ?>
                        <div class="order-list">
                            <?php foreach ($pay_items as $key => $item): 
                                $subtotal = $item['price'] * $item['qty'];
                                
                                // XỬ LÝ ẢNH TRONG PHP (Tránh lỗi nhấp nháy JS)
                                // Kiểm tra kỹ key lưu ảnh trong session là 'img' hay 'image'
                                $img_file = isset($item['img']) ? $item['img'] : (isset($item['image']) ? $item['image'] : '');
                                $img_path = 'admin/anh_sanpham/' . $img_file; 
                                
                                // Kiểm tra file có tồn tại trên host không
                                if (empty($img_file) || !file_exists($img_path)) {
                                    // Fallback: Nếu không tìm thấy file, thử đường dẫn khác hoặc dùng placeholder
                                    $final_img = 'https://via.placeholder.com/80x80.png?text=No+Image';
                                } else {
                                    $final_img = $img_path;
                                }
                            ?>
                            <div class="order-item">
                                <img src="<?php echo $final_img; ?>" class="order-img" alt="SP">
                                
                                <div class="order-info">
                                    <div class="order-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="order-meta">
                                        <?php if(!empty($item['color'])) echo "Màu: " . $item['color']; ?>
                                        <?php if(!empty($item['size'])) echo " | Size: " . $item['size']; ?>
                                    </div>
                                    <div class="order-meta">SL: <strong><?php echo $item['qty']; ?></strong></div>
                                </div>
                                <div class="order-price">
                                    <?php echo number_format($subtotal, 0, ',', '.'); ?>₫
                                </div>

                                <input type="hidden" name="products[<?php echo $key; ?>][id]" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="products[<?php echo $key; ?>][qty]" value="<?php echo $item['qty']; ?>">
                                <input type="hidden" name="products[<?php echo $key; ?>][price]" value="<?php echo $item['price']; ?>">
                                <input type="hidden" name="products[<?php echo $key; ?>][color]" value="<?php echo isset($item['color'])?$item['color']:''; ?>">
                                <input type="hidden" name="products[<?php echo $key; ?>][size]" value="<?php echo isset($item['size'])?$item['size']:''; ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Tạm tính:</span>
                                <span><?php echo number_format($total_money, 0, ',', '.'); ?>₫</span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Phí vận chuyển:</span>
                                <span style="color: green; font-weight: bold;">Miễn phí</span>
                            </div>

                            <div class="grand-total">
                                <span>Tổng thanh toán:</span>
                                <span><?php echo number_format($grand_total, 0, ',', '.'); ?>₫</span>
                            </div>
                            <input type="hidden" name="tong_tien" value="<?php echo $grand_total; ?>">
                        </div>

                        <div class="payment-methods">
                            <h4 style="margin-bottom:10px;">Thanh toán</h4>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="COD" checked>
                                <span><i class="fa-solid fa-truck-fast"></i> Thanh toán khi nhận hàng (COD)</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="BANK">
                                <span><i class="fa-solid fa-building-columns"></i> Chuyển khoản ngân hàng</span>
                            </label>
                        </div>

                        <button type="submit" name="btn_dathang" class="btn-confirm">ĐẶT HÀNG NGAY</button>
                    <?php else: ?>
                        <div style="text-align: center; padding: 50px;">
                            <p>Giỏ hàng trống.</p>
                            <a href="index.php" style="color: #ea580c; font-weight: bold;">Quay lại mua hàng</a>
                        </div>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>