<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php"); 
    exit();
}

$admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';

// --- LOGIC MỚI: LẤY THÔNG BÁO CHO DROPDOWN (PDO) ---
$new_msg = 0;
$list_notif = [];

if (isset($conn)) {
    try {
        // 1. Đếm tổng số tin chưa xem
        $stmt_count = $conn->query("SELECT COUNT(*) as solhuong FROM lienhe WHERE trang_thai = 0");
        $row_count = $stmt_count->fetch(PDO::FETCH_ASSOC);
        $new_msg = $row_count['solhuong'];

        // 2. Lấy 5 tin nhắn mới nhất (kể cả đã xem hay chưa xem, ưu tiên chưa xem lên đầu)
        // Logic: Sắp xếp trạng thái (0 lên trước), sau đó đến ngày gửi mới nhất
        $stmt_list = $conn->query("SELECT * FROM lienhe ORDER BY trang_thai ASC, ngay_gui DESC LIMIT 5");
        $list_notif = $stmt_list->fetchAll(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        $new_msg = 0;
    }
}
?>

<link rel="stylesheet" href="assets/style_admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<header class="admin-header">
    <a href="index.php" class="admin-logo">
        <i class="fa-solid fa-feather-pointed"></i> HBG ADMIN
    </a>

    <ul class="admin-menu">
        <li class="menu-item">
            <a href="index.php" class="menu-link"><i class="fa-solid fa-house"></i> Tổng quan</a>
        </li>
        <li class="menu-item">
            <a href="#" class="menu-link"><i class="fa-solid fa-box-open"></i> Sản phẩm <i class="fa-solid fa-chevron-down arrow-down"></i></a>
            <ul class="dropdown">
                <li><a href="sanpham.php?danhmuc=1">Vợt cầu lông</a></li>
                <li><a href="sanpham.php?danhmuc=2">Giày cầu lông</a></li>
                <li><a href="sanpham.php?danhmuc=4">Quần áo thể thao</a></li>
                <li><a href="sanpham.php?danhmuc=3">Balo & Túi vợt</a></li>
                <li><a href="sanpham.php?danhmuc=5">Phụ kiện</a></li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="#" class="menu-link"><i class="fa-solid fa-warehouse"></i> Kho hàng <i class="fa-solid fa-chevron-down arrow-down"></i></a>
            <ul class="dropdown">
                <li><a href="tonkho.php">Danh mục nhà cung cấp</a></li>
                <li><a href="tonkho.php">Xem tồn kho</a></li>
                <li><a href="nhapkho.php">Tạo phiếu nhập</a></li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="#" class="menu-link"><i class="fa-solid fa-box-open"></i> Đơn hàng <i class="fa-solid fa-chevron-down arrow-down"></i></a>
            <ul class="dropdown">
                <li><a href="tonkho.php">Quản lý đơn hàng</a></li>
                <li><a href="nhapkho.php">Quản lý đổi trả</a></li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="#" class="menu-link"><i class="fa-solid fa-chart-line"></i> Thống kê <i class="fa-solid fa-chevron-down arrow-down"></i></a>
            <ul class="dropdown">
                <li><a href="doanhthu.php">Báo cáo doanh thu</a></li>
                <li><a href="baocao.php">Báo cáo lợi nhuận</a></li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="phanhoi.php" class="menu-link"><i class="fa-solid fa-comments"></i> Phản hồi</a>
        </li>
    </ul>

    <div class="admin-right">
        
        <div class="notif-wrapper">
            <div class="notif-icon" onclick="toggleNotif(event)">
                <i class="fa-solid fa-bell"></i>
                <?php if($new_msg > 0): ?>
                    <span class="badge"><?php echo $new_msg; ?></span>
                <?php endif; ?>
            </div>

            <div class="notif-dropdown" id="notifBox">
                <div class="notif-header">
                    <span>Thông báo mới</span>
                    <small><?php echo $new_msg; ?> tin mới</small>
                </div>
                
                <div class="notif-body">
                    <?php if(count($list_notif) > 0): ?>
                        <?php foreach($list_notif as $item): ?>
                            <?php 
                                // Tô đậm nếu chưa đọc
                                $style_unread = ($item['trang_thai'] == 0) ? 'background:#fff3e0;' : ''; 
                            ?>
                            <a href="phanhoi.php?id=<?php echo $item['id']; ?>" class="notif-item" style="<?php echo $style_unread; ?>">
                                <div class="notif-item-top">
                                    <span class="notif-name"><?php echo htmlspecialchars($item['ho_ten']); ?></span>
                                    <span class="notif-time"><?php echo date('d/m H:i', strtotime($item['ngay_gui'])); ?></span>
                                </div>
                                <span class="notif-content">
                                    <?php echo htmlspecialchars($item['noi_dung']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: #999;">
                            <i class="fa-regular fa-envelope-open" style="font-size:20px; margin-bottom:5px;"></i><br>
                            Không có tin nhắn nào
                        </div>
                    <?php endif; ?>
                </div>

                <div class="notif-footer">
                    <a href="phanhoi.php">Xem tất cả phản hồi</a>
                </div>
            </div>
        </div>
        <div class="user-pill">
            <div class="user-text">
                <small>Xin chào,</small>
                <strong><?php echo htmlspecialchars($admin_name); ?></strong>
            </div>
            
            <a href="logout.php" class="btn-logout-circle" title="Đăng xuất">
                <i class="fa-solid fa-power-off"></i>
            </a>
        </div>
    </div>
</header>

<script>
    function toggleNotif(event) {
        event.stopPropagation(); // Ngăn click lan ra ngoài
        var box = document.getElementById("notifBox");
        box.classList.toggle("active"); // Thêm/Bớt class active để hiện/ẩn
    }

    // Bấm ra ngoài màn hình thì tự tắt menu
    window.onclick = function(event) {
        var box = document.getElementById("notifBox");
        // Nếu click không trúng vào cái chuông hoặc cái hộp
        if (!event.target.closest('.notif-wrapper')) {
            if (box && box.classList.contains('active')) {
                box.classList.remove('active');
            }
        }
    }
</script>