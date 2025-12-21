<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php"); 
    exit();
}

$admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';
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