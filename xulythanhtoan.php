<?php
session_start();
require_once 'connect.php';

// --- CẤU HÌNH NGÂN HÀNG CỦA BẠN ---
$MY_BANK_ID   = 'TPB';           // Mã ngân hàng (Ví dụ: MB, VCB, ACB, TPB...)
$MY_ACCOUNT   = '88228102004';   // Số tài khoản của bạn
$TEMPLATE     = 'compact2';     // Giao diện QR
// ----------------------------------

if (!isset($_POST['btn_dathang'])) {
    header("Location: index.php");
    exit();
}

// 1. LẤY DỮ LIỆU TỪ FORM
$user_id    = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);
$hoten      = $_POST['hoten'];
$sdt        = $_POST['sdt'];
$email      = isset($_POST['email']) ? $_POST['email'] : '';
$diachi     = $_POST['diachi'];
$ghichu     = isset($_POST['ghichu']) ? $_POST['ghichu'] : '';
$tong_tien  = $_POST['tong_tien'];
$pt_thanhtoan = $_POST['payment_method']; 
$products   = isset($_POST['products']) ? $_POST['products'] : [];

$order_id = 0;
$success = false;

try {
    $conn->beginTransaction();

    // 2. LƯU ĐƠN HÀNG
    $sql = "INSERT INTO donhang (user_id, ten_nguoi_nhan, sdt_nguoi_nhan, email, dia_chi_giao, ghichu, tong_tien, pt_thanhtoan, ngay_dat, trang_thai) 
            VALUES (:uid, :ten, :sdt, :email, :diachi, :ghichu, :tong, :pt, NOW(), 0)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':uid' => $user_id,
        ':ten' => $hoten,
        ':sdt' => $sdt,
        ':email' => $email,
        ':diachi' => $diachi,
        ':ghichu' => $ghichu,
        ':tong' => $tong_tien,
        ':pt' => $pt_thanhtoan
    ]);

    $order_id = $conn->lastInsertId();

    // 3. LƯU CHI TIẾT ĐƠN HÀNG
    $sqlDetail = "INSERT INTO chitiet_donhang (donhang_id, sanpham_id, ten_sanpham, mau, size, so_luong, don_gia) 
                  VALUES (:dh_id, :sp_id, :sp_ten, :mau, :size, :sl, :gia)";
    $stmtDetail = $conn->prepare($sqlDetail);

    foreach ($products as $key => $prod) {
        $stmtDetail->execute([
            ':dh_id' => $order_id,
            ':sp_id' => $prod['id'],
            ':sp_ten'=> isset($prod['name']) ? $prod['name'] : 'Sản phẩm',
            ':mau'   => $prod['color'],
            ':size'  => $prod['size'],
            ':sl'    => $prod['qty'],
            ':gia'   => $prod['price']
        ]);

        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }
    }

    $conn->commit();
    $success = true;

} catch (Exception $e) {
    $conn->rollBack();
    echo "Có lỗi xảy ra: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả đặt hàng</title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/xulythanhtoan.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="result-page">
        <div class="result-box">
            
            <?php if ($pt_thanhtoan == 'COD'): ?>
                <i class="fa-solid fa-circle-check icon-success"></i>
                <div class="success-title">Đặt hàng thành công!</div>
                <div class="success-desc">
                    Mã đơn hàng: <strong>#<?php echo $order_id; ?></strong><br>
                    Cảm ơn bạn đã mua sắm. Chúng tôi sẽ sớm liên hệ để giao hàng.
                </div>
                
                <div class="btn-group">
                    <a href="index.php" class="btn btn-cancel">Về trang chủ</a>
                    <a href="profile.php" class="btn btn-track">Theo dõi đơn hàng</a>
                </div>

            <?php else: ?>
                <div class="qr-section">
                    <h2>Thanh toán qua Ngân hàng</h2>
                    <p>Quét mã QR để chuyển khoản nhanh</p>

                    <?php
                        $content_ck = "DH" . $order_id;
                        $qr_link = "https://img.vietqr.io/image/{$MY_BANK_ID}-{$MY_ACCOUNT}-{$TEMPLATE}.png?amount={$tong_tien}&addInfo={$content_ck}";
                    ?>
                    
                    <img src="<?php echo $qr_link; ?>" alt="QR Code" class="qr-img">
                    
                    <div class="qr-note">
                        <i class="fa-solid fa-circle-info"></i> Nội dung chuyển khoản: <strong><?php echo $content_ck; ?></strong>
                    </div>

                    <div class="btn-group">
                        <a href="index.php" class="btn btn-cancel">Hủy bỏ</a>
                        <a href="profile.php" class="btn btn-track">Đã chuyển khoản</a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>
</html>