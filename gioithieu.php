<?php
session_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới thiệu - HBG Shop</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/gioithieu.css">
    <link rel="stylesheet" href="assets/css/menu.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Trang chủ</a> &nbsp; <i class="fa-solid fa-angle-right" style="font-size: 12px;"></i> &nbsp; <span>Giới thiệu</span>
        </div>
    </div>

    <div class="intro-container">
        
        <aside class="sidebar">
            <h3>CÓ THỂ BẠN SẼ THÍCH</h3>
            
            <div class="news-item">
                <div class="news-thumb"><i class="fa-solid fa-image"></i></div>
                <div class="news-info">
                    <a href="#">Hướng dẫn mua hàng trực tuyến tại HBG Shop</a>
                    <div class="news-date">28-10-2025 08:32</div>
                </div>
            </div>

            <div class="news-item">
                <div class="news-thumb"><i class="fa-solid fa-gift"></i></div>
                <div class="news-info">
                    <a href="#">Các chương trình khuyến mãi tháng 12</a>
                    <div class="news-date">02-01-2025 09:15</div>
                </div>
            </div>

            <div class="news-item">
                <div class="news-thumb"><i class="fa-solid fa-truck"></i></div>
                <div class="news-info">
                    <a href="#">Chính sách vận chuyển và đổi trả</a>
                    <div class="news-date">15-11-2025 14:20</div>
                </div>
            </div>
            
             <div class="news-item">
                <div class="news-thumb"><i class="fa-solid fa-credit-card"></i></div>
                <div class="news-info">
                    <a href="#">Hướng dẫn thanh toán qua ngân hàng</a>
                    <div class="news-date">20-11-2025 10:00</div>
                </div>
            </div>

        </aside>

        <main class="main-content">
            <h1>GIỚI THIỆU</h1>
            
            <div class="meta-info">
                <span><i class="fa-regular fa-clock"></i> 17-12-2025 18:30</span>
                <span><i class="fa-solid fa-user"></i> HBG Admin</span>
            </div>

            <div class="content-text">
                <p>Chào mừng bạn đến với <strong>HBG Shop</strong> - Hệ thống cửa hàng cầu lông uy tín hàng đầu, nơi đam mê và chất lượng gặp gỡ.</p>

                <p>Được thành lập với sứ mệnh phục vụ cộng đồng yêu cầu lông, <span class="highlight">HBG Shop</span> không chỉ là nơi cung cấp dụng cụ thể thao mà còn là người bạn đồng hành tin cậy của các lông thủ trên mọi miền đất nước.</p>

                <p>Chúng tôi tự hào cung cấp đa dạng các sản phẩm chính hãng từ các thương hiệu nổi tiếng thế giới như <strong>Yonex, Lining, Victor, Mizuno</strong>... đến các thương hiệu tầm trung chất lượng cao như Kumpoo, Apacs. Tại đây, bạn có thể tìm thấy mọi thứ mình cần: từ vợt cầu lông, giày, quần áo thi đấu cho đến các phụ kiện nhỏ nhất.</p>

                <p>Với phương châm <em>"Khách hàng là trọng tâm"</em>, đội ngũ nhân viên tư vấn chuyên nghiệp của HBG Shop luôn sẵn sàng hỗ trợ bạn chọn được cây vợt ưng ý nhất, phù hợp với lối đánh và trình độ của từng người.</p>

                <p>Hãy đến với <strong>HBG Shop</strong> để trải nghiệm dịch vụ chuyên nghiệp và sở hữu những sản phẩm chất lượng nhất!</p>
            </div>
        </main>

    </div>
<div class="founders-section">
    <h2>Đội ngũ sáng lập</h2>
    
    <div class="founders-grid">
        
      <div class="founder-card">
            <img src="assets/images/huongdan/lb.png" alt="Nguyễn Gia Bảo" class="founder-img">
            <h3 class="founder-name">Nguyễn Gia Bảo</h3>
            <p class="founder-role">CEO & Founder</p>
        </div>

        <div class="founder-card">
            <img src="assets/images/huongdan/vnh.png" alt="Vũ Nhật Hoàng" class="founder-img">
            <h3 class="founder-name">Vũ Nhật Hoàng</h3>
            <p class="founder-role">Co-Founder</p>
        </div>

        <div class="founder-card">
            <img src="assets/images/huongdan/lg.png" alt="Lê Trường Giang" class="founder-img">
            <h3 class="founder-name">Lê Trường Giang</h3>
            <p class="founder-role">Chuyên gia tư vấn</p>
        </div>

    </div>
</div>
    <?php include 'includes/footer.php'; ?>

</body>
</html>