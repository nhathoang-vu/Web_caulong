<?php
session_start();

// Xử lý gửi form (Giả lập)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ở đây bạn có thể thêm code gửi email hoặc lưu vào CSDL sau này
    echo "<script>alert('Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.');</script>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - HBG Shop</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/lienhe.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Trang chủ</a> &nbsp; <i class="fa-solid fa-angle-right" style="font-size: 12px;"></i> &nbsp; <span>Liên hệ</span>
        </div>
    </div>

    <main class="contact-page">
        
        <div class="contact-info">
            <h2>NƠI GIẢI ĐÁP TOÀN BỘ MỌI THẮC MẮC CỦA BẠN?</h2>
            
            <p>
                <strong>Hotline:</strong> 
                <span class="highlight-text">0977508430 | 0338000308</span>
            </p>
            
            <p>
                <strong>Email:</strong> 
                <span class="highlight-text">info@shopvnb.com</span>
            </p>
        </div>

        <h3 class="contact-form-title">LIÊN HỆ VỚI CHÚNG TÔI</h3>

        <form action="" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="fullname" class="form-control" placeholder="Họ và tên" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <input type="text" name="phone" class="form-control" placeholder="Điện thoại">
            </div>

            <div class="form-group">
                <textarea name="content" class="form-control" placeholder="Nội dung" required></textarea>
            </div>

            <button type="submit" class="btn-submit">Gửi thông tin</button>
        </form>

    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>