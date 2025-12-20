<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng dẫn mua hàng - HBG Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
      <link rel="stylesheet" href="assets/css/footer.css">
    <style>
        .guide-container { display: flex; gap: 30px; max-width: 1200px; margin: 30px auto; padding: 0 15px; }
        .sidebar-guide { width: 25%; border-right: 1px solid #eee; }
        .sidebar-guide h3 { font-size: 18px; margin-bottom: 20px; border-left: 4px solid #e65100; padding-left: 10px; text-transform: uppercase; }
        .sidebar-guide ul { list-style: none; padding: 0; }
        .sidebar-guide ul li { margin-bottom: 12px; }
        .sidebar-guide ul li a { color: #555; text-decoration: none; font-weight: 500; display: block; padding: 8px; border-radius: 4px; transition: 0.2s; }
        .sidebar-guide ul li a:hover, .sidebar-guide ul li a.active { background: #fff0e6; color: #e65100; }
        
        .main-guide { width: 75%; }
        .main-guide h1 { font-size: 24px; color: #333; margin-bottom: 20px; text-transform: uppercase; }
        .step-box { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #e65100; }
        .step-title { font-weight: bold; font-size: 16px; color: #e65100; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="guide-container">
        <aside class="sidebar-guide">
            <h3>Danh mục hướng dẫn</h3>
            <ul>
                <li><a href="hdmh.php" class="active"><i class="fa-solid fa-cart-shopping"></i> Hướng dẫn mua hàng</a></li>
                <li><a href="hdtt.php"><i class="fa-solid fa-money-bill"></i> Hướng dẫn thanh toán</a></li>
                <li><a href="hdlcvot.php"><i class="fa-solid fa-table-tennis-paddle-ball"></i> Hướng dẫn chọn vợt</a></li>
            </ul>
        </aside>

        <main class="main-guide">
            <h1>Quy trình mua hàng tại HBG Shop</h1>
            
            <p>Việc mua hàng tại HBG Shop rất đơn giản, bạn chỉ cần làm theo 4 bước sau:</p>
            <br>

            <div class="step-box">
                <span class="step-title">BƯỚC 1: TÌM KIẾM SẢN PHẨM</span>
                <p>Bạn có thể tìm kiếm sản phẩm theo 2 cách:</p>
                <ul>
                    <li>Gõ tên sản phẩm vào thanh tìm kiếm ở đầu trang (Ví dụ: "Vợt Yonex 88D").</li>
                    <li>Tìm theo danh mục sản phẩm trên thanh Menu (Vợt cầu lông, Giày, Quần áo...).</li>
                </ul>
            </div>

            <div class="step-box">
                <span class="step-title">BƯỚC 2: THÊM VÀO GIỎ HÀNG</span>
                <p>Sau khi chọn được sản phẩm ưng ý:</p>
                <ul>
                    <li>Chọn <strong>Size</strong> (đối với giày/quần áo) hoặc <strong>Thông số U/G</strong> (đối với vợt).</li>
                    <li>Bấm nút <strong>"THÊM VÀO GIỎ"</strong> nếu muốn mua thêm món khác.</li>
                    <li>Hoặc bấm <strong>"MUA NGAY"</strong> để chuyển sang bước thanh toán.</li>
                </ul>
            </div>

            <div class="step-box">
                <span class="step-title">BƯỚC 3: KIỂM TRA GIỎ HÀNG & ĐĂNG NHẬP</span>
                <p>Tại trang giỏ hàng, bạn kiểm tra lại số lượng và giá tiền. Nếu chưa có tài khoản, hệ thống sẽ yêu cầu bạn <strong>Đăng nhập</strong> hoặc <strong>Đăng ký</strong> để tích điểm thành viên.</p>
            </div>

            <div class="step-box">
                <span class="step-title">BƯỚC 4: THANH TOÁN & NHẬN HÀNG</span>
                <p>Điền đầy đủ thông tin giao hàng (Họ tên, SĐT, Địa chỉ). Chọn phương thức thanh toán (COD hoặc Chuyển khoản) và bấm <strong>"ĐẶT HÀNG"</strong>. Nhân viên sẽ gọi điện xác nhận đơn hàng sau 15 phút.</p>
            </div>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>