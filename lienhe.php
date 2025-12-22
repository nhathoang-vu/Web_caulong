<?php
// 1. Kết nối CSDL (PDO)
include 'connect.php'; 

// 2. Xử lý khi người dùng ấn nút Gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ho_ten = $_POST['hoten'];
    $email = $_POST['email'];
    $sdt = $_POST['sdt'];
    $noi_dung = $_POST['noidung'];

    try {
        $sql = "INSERT INTO lienhe (ho_ten, email, sdt, noi_dung) VALUES (:hoten, :email, :sdt, :noidung)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':hoten' => $ho_ten,
            ':email' => $email,
            ':sdt' => $sdt,
            ':noidung' => $noi_dung
        ]);
        echo "<script>alert('Gửi thành công! Chúng tôi sẽ sớm liên hệ lại.'); window.location.href='lienhe.php';</script>";
    } catch(PDOException $e) {
        echo "<script>alert('Lỗi: " . $e->getMessage() . "');</script>";
    }
}
?>

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
            <div class="info-box"> <i class="fa-solid fa-phone-volume"></i> <h3>Hotline</h3> <p>0904082576</p> </div>
            <div class="info-box"> <i class="fa-solid fa-envelope-open-text"></i> <h3>Email</h3> <p>baodcad73@gmail.com</p> </div>
            <div class="info-box"> <i class="fa-solid fa-location-dot"></i> <h3>Địa chỉ</h3> <p>Phường Lạch Tray, Hải Phòng</p> </div>
        </div>

        <div class="contact-content-wrapper">
            <div class="contact-form-container">
                <h3>GỬI TIN NHẮN</h3>
                <form action="" method="POST">
                    <div class="form-group"> <input type="text" name="hoten" class="form-control" placeholder="Họ và tên *" required> </div>
                    <div class="form-group"> <input type="email" name="email" class="form-control" placeholder="Email *" required> </div>
                    <div class="form-group"> <input type="tel" name="sdt" class="form-control" placeholder="Số điện thoại *" required> </div>
                    <div class="form-group"> <textarea name="noidung" class="form-control" placeholder="Nội dung *" required></textarea> </div>
                    <button type="submit" class="btn-submit">GỬI NGAY</button>
                </form>
            </div>
            <div class="map-container">
                <iframe class="map-frame" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d59648.65657807753!2d106.63223035659837!3d20.84673117961226!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x314a7af39e43692f%3A0xa6213791097e3c1a!2zTmjDoCBIw6F0IEzhu5duIEjhuqNpIFBow7JuZw!5e0!3m2!1svi!2s!4v1703070000000!5m2!1svi!2s" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div> 
    </div> 
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>