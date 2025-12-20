<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng dẫn thanh toán - HBG Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
      <link rel="stylesheet" href="assets/css/footer.css">
    <style>
        /* Bạn copy y hệt đoạn style ở file huongdanmuahang.php dán vào đây */
        .guide-container { display: flex; gap: 30px; max-width: 1200px; margin: 30px auto; padding: 0 15px; }
        .sidebar-guide { width: 25%; border-right: 1px solid #eee; }
        .sidebar-guide h3 { font-size: 18px; margin-bottom: 20px; border-left: 4px solid #e65100; padding-left: 10px; text-transform: uppercase; }
        .sidebar-guide ul { list-style: none; padding: 0; }
        .sidebar-guide ul li { margin-bottom: 12px; }
        .sidebar-guide ul li a { color: #555; text-decoration: none; font-weight: 500; display: block; padding: 8px; border-radius: 4px; transition: 0.2s; }
        .sidebar-guide ul li a:hover, .sidebar-guide ul li a.active { background: #fff0e6; color: #e65100; }
        .main-guide { width: 75%; }
        .main-guide h1 { font-size: 24px; color: #333; margin-bottom: 20px; text-transform: uppercase; }
        
        /* CSS Bảng ngân hàng */
        .bank-info { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .bank-info th, .bank-info td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .bank-info th { background-color: #333; color: white; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="guide-container">
        <aside class="sidebar-guide">
            <h3>Danh mục hướng dẫn</h3>
            <ul>
                <li><a href="hdmh.php"><i class="fa-solid fa-cart-shopping"></i> Hướng dẫn mua hàng</a></li>
                <li><a href="hdtt.php" class="active"><i class="fa-solid fa-money-bill"></i> Hướng dẫn thanh toán</a></li>
                <li><a href="hdlcvot.php"><i class="fa-solid fa-table-tennis-paddle-ball"></i> Hướng dẫn chọn vợt</a></li>
            </ul>
        </aside>

        <main class="main-guide">
            <h1>Phương thức thanh toán</h1>
            <p>HBG Shop hỗ trợ 2 hình thức thanh toán chính:</p>
            
            <h3>1. Thanh toán khi nhận hàng (COD)</h3>
            <p>Quý khách hàng sẽ thanh toán tiền mặt trực tiếp cho nhân viên giao hàng (Shipper) khi nhận được sản phẩm.</p>
            
            <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

            <h3>2. Chuyển khoản ngân hàng</h3>
            <p>Quý khách vui lòng chuyển khoản qua các ngân hàng sau với nội dung: <strong>[Tên khách hàng] + [SĐT]</strong></p>
            
            <table class="bank-info">
                <thead>
                    <tr>
                        <th>Ngân hàng</th>
                        <th>Số tài khoản</th>
                        <th>Chủ tài khoản</th>
                        <th>Chi nhánh</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Vietcombank</strong></td>
                        <td>0071000XXXXXX</td>
                        <td>NGUYEN VAN A</td>
                        <td>Hải Phòng</td>
                    </tr>
                    <tr>
                        <td><strong>MB Bank</strong></td>
                        <td>999999XXXXXX</td>
                        <td>HBG SHOP</td>
                        <td>Hà Nội</td>
                    </tr>
                </tbody>
            </table>
            
            <p style="margin-top: 15px; color: red; font-style: italic;">* Lưu ý: Sau khi chuyển khoản xong, vui lòng chụp lại màn hình và gửi qua Zalo/Fanpage để shop xác nhận nhanh nhất.</p>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>