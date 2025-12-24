<?php
session_start();
require_once 'connect.php';

// --- CẤU HÌNH NGÂN HÀNG ---
$MY_BANK_ID   = 'TPB';           
$MY_ACCOUNT   = '88228102004';   
$TEMPLATE     = 'compact2';     
// --------------------------

// Nếu không phải bấm nút đặt hàng thì quay về trang chủ
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

try {
    // BẮT ĐẦU GIAO DỊCH
    $conn->beginTransaction();

    // A. KIỂM TRA TỒN KHO (Bỏ FOR UPDATE để tránh bị lag/treo máy)
    foreach ($products as $prod) {
        $checkKho = "SELECT so_luong_ton FROM bienthe_sanpham 
                     WHERE sanpham_id = :id AND mau_sac = :mau AND kich_thuoc = :size"; 
        $stmtCheck = $conn->prepare($checkKho);
        $stmtCheck->execute([
            ':id'   => $prod['id'],
            ':mau'  => $prod['color'],
            ':size' => $prod['size']
        ]);
        $current_stock = $stmtCheck->fetchColumn();

        // Xử lý tên sản phẩm an toàn (tránh lỗi nếu không lấy được tên)
        $ten_sp_hien_thi = isset($prod['name']) ? $prod['name'] : (isset($prod['ten_sanpham']) ? $prod['ten_sanpham'] : 'Sản phẩm ID ' . $prod['id']);

        if ($current_stock === false) {
             throw new Exception("Sản phẩm $ten_sp_hien_thi không tồn tại hoặc đã bị xóa.");
        }
        if ($current_stock < $prod['qty']) {
            throw new Exception("Sản phẩm $ten_sp_hien_thi (" . $prod['color'] . " - " . $prod['size'] . ") hiện không đủ hàng (Còn: $current_stock).");
        }
    }

    // B. LƯU ĐƠN HÀNG
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

    // C. LƯU CHI TIẾT VÀ TRỪ KHO
    $sqlDetail = "INSERT INTO chitiet_donhang (donhang_id, sanpham_id, ten_sanpham, mau, size, so_luong, don_gia) 
                  VALUES (:dh_id, :sp_id, :sp_ten, :mau, :size, :sl, :gia)";
    $stmtDetail = $conn->prepare($sqlDetail);

    $sqlUpdateKho = "UPDATE bienthe_sanpham 
                     SET so_luong_ton = so_luong_ton - :sl 
                     WHERE sanpham_id = :sp_id AND mau_sac = :mau AND kich_thuoc = :size";
    $stmtUpdateKho = $conn->prepare($sqlUpdateKho);

    foreach ($products as $key => $prod) {
        // Lấy tên an toàn lần nữa cho chắc chắn
        $ten_sp_safe = isset($prod['name']) ? $prod['name'] : (isset($prod['ten_sanpham']) ? $prod['ten_sanpham'] : 'Sản phẩm');

        // 1. Lưu chi tiết
        $stmtDetail->execute([
            ':dh_id' => $order_id,
            ':sp_id' => $prod['id'],
            ':sp_ten'=> $ten_sp_safe, 
            ':mau'   => $prod['color'],
            ':size'  => $prod['size'],
            ':sl'    => $prod['qty'],
            ':gia'   => $prod['price']
        ]);

        // 2. Trừ kho
        $stmtUpdateKho->execute([
            ':sl'    => $prod['qty'],
            ':sp_id' => $prod['id'],
            ':mau'   => $prod['color'],
            ':size'  => $prod['size']
        ]);

        // 3. Xóa khỏi giỏ hàng
        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }
    }

    // XÁC NHẬN GIAO DỊCH THÀNH CÔNG
    $conn->commit();

} catch (Exception $e) {
    // NẾU CÓ LỖI -> HOÀN TÁC MỌI THỨ
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    // Dùng JS alert để báo lỗi rõ ràng rồi quay về giỏ hàng
    echo "<script>
            alert('Lỗi đặt hàng: " . addslashes($e->getMessage()) . "');
            window.location.href='giohang.php';
          </script>";
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
                    Hệ thống đã ghi nhận đơn hàng và trừ kho số lượng sản phẩm bạn đã mua.<br>
                    Chúng tôi sẽ sớm liên hệ để giao hàng.
                </div>
                <div class="btn-group">
                    <a href="index.php" class="btn btn-cancel">Về trang chủ</a>
                    <a href="profile.php" class="btn btn-track">Theo dõi đơn hàng</a>
                </div>

            <?php else: ?>
                <div class="qr-section">
                    <h2>Thanh toán qua Ngân hàng</h2>
                    <p>Đơn hàng <strong>#<?php echo $order_id; ?></strong> đã được tạo.</p>
                    <p>Vui lòng quét mã QR để hoàn tất thanh toán.</p>

                    <?php
                        $content_ck = "DH" . $order_id;
                        $qr_link = "https://img.vietqr.io/image/{$MY_BANK_ID}-{$MY_ACCOUNT}-{$TEMPLATE}.png?amount={$tong_tien}&addInfo={$content_ck}";
                    ?>
                    
                    <img src="<?php echo $qr_link; ?>" alt="QR Code" class="qr-img">
                    
                    <div class="qr-note">
                        <i class="fa-solid fa-circle-info"></i> Nội dung chuyển khoản: <strong><?php echo $content_ck; ?></strong>
                    </div>

                    <div class="btn-group">
                        <a href="index.php" class="btn btn-cancel">Về trang chủ</a>
                        <a href="profile.php" class="btn btn-track">Tôi đã thanh toán</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>