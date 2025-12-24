<?php
session_start();
require_once 'connect.php'; 

// Tăng giới hạn nối chuỗi để tránh lỗi hiển thị khi đơn hàng có quá nhiều sản phẩm
$conn->exec("SET SESSION group_concat_max_len = 1000000");

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user']['id'])) {
        $_SESSION['user_id'] = $_SESSION['user']['id'];
    } else {
        header("Location: login.php");
        exit();
    }
}
$user_id = $_SESSION['user_id'];

try {
    // 2. Lấy thông tin User
    $sql = "SELECT * FROM user WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $ten_hien_thi = !empty($user['tendaydu']) ? $user['tendaydu'] : $user['name'];

    // 3. Lấy Đơn hàng
    // ĐÃ SỬA: Đường dẫn ảnh từ 'uploads/' thành 'admin/anh_sanpham/'
    $sqlOrder = "SELECT d.*, 
                 GROUP_CONCAT(
                    CONCAT(
                        '<div class=\"order-item-row\">',
                            '<div class=\"item-thumb\">',
                                '<img src=\"admin/anh_sanpham/', IFNULL(s.hinh_anh, 'no-image.png'), '\" alt=\"sp\" onerror=\"this.onerror=null; this.src=\'https://via.placeholder.com/60x60.png?text=NO+IMG\';\">',
                            '</div>',
                            '<div class=\"item-info\">',
                                '<div class=\"item-name\">', REPLACE(IFNULL(s.ten_sanpham, ct.ten_sanpham), '\"', ''), '</div>',
                                '<div class=\"item-meta\">',
                                    '<span class=\"item-attr\">', IFNULL(ct.mau, ''), ' / ', IFNULL(ct.size, ''), '</span>',
                                    '<span class=\"item-qty\">x', ct.so_luong, '</span>',
                                '</div>',
                            '</div>',
                        '</div>'
                    ) 
                    SEPARATOR ''
                 ) as chi_tiet_sp
                 FROM donhang d
                 LEFT JOIN chitiet_donhang ct ON d.id = ct.donhang_id
                 LEFT JOIN sanpham s ON ct.sanpham_id = s.id
                 WHERE d.user_id = :uid
                 GROUP BY d.id
                 ORDER BY d.trang_thai ASC, d.id DESC";
                 
    $stmtOrder = $conn->prepare($sqlOrder);
    $stmtOrder->execute([':uid' => $user_id]);
    $orders = $stmtOrder->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage(); exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/profile.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="profile-page-wrapper">
        <div class="container-fluid-custom">
            <div class="profile-layout">
                
                <aside class="profile-sidebar">
                    <div class="profile-box info-box">
                        <div class="box-header">
                            <h3 class="box-title">Thông tin cá nhân</h3>
                        </div>
                        <div class="box-content">
                            <div class="info-row">
                                <label><i class="fa-solid fa-user"></i> Họ tên</label>
                                <span><?php echo htmlspecialchars($ten_hien_thi); ?></span>
                            </div>
                            <div class="info-row">
                                <label><i class="fa-solid fa-phone"></i> Sđt</label>
                                <span><?php echo !empty($user['sdt']) ? htmlspecialchars($user['sdt']) : '...'; ?></span>
                            </div>
                            <div class="info-row">
                                <label><i class="fa-solid fa-envelope"></i> Email</label>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="info-row">
                                <label><i class="fa-solid fa-location-dot"></i> Địa chỉ</label>
                                <span><?php echo !empty($user['diachi']) ? htmlspecialchars($user['diachi']) : '...'; ?></span>
                            </div>
                            <a href="update_profile.php" class="btn-edit-profile">
                                <i class="fa-solid fa-pen-to-square"></i> Cập nhật
                            </a>
                        </div>
                    </div>
                </aside>

                <main class="profile-main">
                    <div class="profile-box order-box">
                        <div class="box-header">
                            <h3 class="box-title">Đơn hàng của tôi</h3>
                        </div>
                        <div class="box-content p-0">
                            <div class="table-responsive">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Mã</th>
                                            <th>Ngày đặt</th>
                                            <th class="col-product">Sản phẩm</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($orders) > 0): ?>
                                            <?php foreach ($orders as $order): ?>
                                                <tr>
                                                    <td class="text-center bold-text">#<?php echo $order['id']; ?></td>
                                                    <td class="text-center small-text">
                                                        <?php echo date('d/m/Y', strtotime($order['ngay_dat'])); ?>
                                                    </td>
                                                    <td class="product-cell">
                                                        <?php echo $order['chi_tiet_sp']; ?>
                                                    </td>
                                                    <td class="text-center price-cell">
                                                        <div><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>đ</div>
                                                        <small class="payment-method">
                                                            <?php echo ($order['pt_thanhtoan'] == 'BANK') ? 'CK' : 'COD'; ?>
                                                        </small>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php 
                                                            $stt = $order['trang_thai'];
                                                            $cls = 'badge-secondary'; $txt = 'Không xác định';
                                                            
                                                            // Logic hiển thị badge trạng thái
                                                            if($stt==0){ $cls='badge-warning'; $txt='Mới đặt'; }
                                                            elseif($stt==1){ $cls='badge-info'; $txt='Đã xác nhận'; }
                                                            elseif($stt==2){ $cls='badge-primary'; $txt='Đang giao'; }
                                                            elseif($stt==3){ $cls='badge-success'; $txt='Giao thành công'; }
                                                            elseif($stt==4){ $cls='badge-danger'; $txt='Đã hủy'; }
                                                        ?>
                                                        <div style="margin-bottom: 8px;">
                                                            <span class="status-badge <?php echo $cls; ?>">
                                                                <?php echo $txt; ?>
                                                            </span>
                                                        </div>

                                                        <?php 
                                                        // --- LOGIC NÚT BẤM (Sửa theo yêu cầu) ---
                                                        // Cho phép Hủy/Trả khi trạng thái là 0, 1, 2 hoặc 3
                                                        if (in_array($stt, [0, 1, 2, 3])): 
                                                            // Đổi tên nút cho hợp ngữ cảnh (Trải nghiệm người dùng tốt hơn)
                                                            if ($stt == 2 || $stt == 3) {
                                                                $btnLabel = "Yêu cầu trả hàng";
                                                                $btnIcon  = "fa-rotate-left";
                                                            } else {
                                                                $btnLabel = "Hủy đơn";
                                                                $btnIcon  = "fa-xmark";
                                                            }
                                                        ?>
                                                            <button type="button" class="btn-cancel-order" onclick="openCancelModal(<?php echo $order['id']; ?>)">
                                                                <i class="fa-solid <?php echo $btnIcon; ?>"></i> <?php echo $btnLabel; ?>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="empty-state">
                                                    Bạn chưa có đơn hàng nào.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <div id="cancelModal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-modal" onclick="closeCancelModal()">&times;</span>
            <h3>Xác nhận Hủy / Trả hàng</h3>
            <p>Vui lòng nhập lý do để chúng tôi hỗ trợ bạn tốt nhất:</p>
            
            <form action="cancel_order.php" method="POST">
                <input type="hidden" name="order_id" id="modal_order_id" value="">
                
                <textarea name="ly_do" class="modal-textarea" rows="4" placeholder="VD: Đổi địa chỉ, đặt nhầm, hàng lỗi..." required></textarea>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeCancelModal()">Đóng</button>
                    <button type="submit" class="btn-danger">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCancelModal(orderId) {
            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('cancelModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>