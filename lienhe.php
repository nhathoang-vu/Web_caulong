<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - HBG Shop</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/lienhe.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="contact-container">
        <h1>LIÊN HỆ VỚI CHÚNG TÔI</h1>

        <div class="contact-info">
            <div class="info-box">
                <i class="fa-solid fa-phone-volume"></i>
                <h3>Hotline tư vấn</h3>
                <p>0904082576<br>0836959658</p>
            </div>
            <div class="info-box">
                <i class="fa-solid fa-envelope-open-text"></i>
                <h3>Email hỗ trợ</h3>
                <p>baodcad73@gmail.com<br>bao96219@st.vimaru.edu.vn</p>
            </div>
            <div class="info-box">
                <i class="fa-solid fa-location-dot"></i>
                <h3>Địa chỉ cửa hàng</h3>
                <p><br>Phường Lạch Tray, Hải Phòng</p>
            </div>
        </div>

        <div class="contact-content-wrapper">
            
            <div class="contact-form-container">
                <h3>GỬI TIN NHẮN CHO CHÚNG TÔI</h3>
                <form action="#" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Họ và tên của bạn *" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" placeholder="Email liên hệ *" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" class="form-control" placeholder="Số điện thoại *" required>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" placeholder="Nội dung tin nhắn *" required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">GỬI NGAY</button>
                </form>
            </div>
            
            <div class="map-container">
                <h3>ĐỊA ĐIỂM</h3>
                <iframe class="map-frame" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d59648.65657807753!2d106.63223035659837!3d20.84673117961226!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x314a7af39e43692f%3A0xa6213791097e3c1a!2zTmjDoCBIw6F0IEzhu5duIEjhuqNpIFBow7JuZw!5e0!3m2!1svi!2s!4v1703070000000!5m2!1svi!2s" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>

        </div> </div> <?php include 'includes/footer.php'; ?>

</body>
</html>