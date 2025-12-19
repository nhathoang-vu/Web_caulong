<header class="main-header">
    <div class="container">
        <div class="logo-area" style="margin-left: 100px;">
            <a href="index.php">
                <img src="assets/images/logo/Logo.png" alt="Logo HBG Shop" class="logo-img" style="position: relative; left: 30px;">
            </a>
        </div>

        <div class="header-info">
            <div class="info-item">
                <i class="fa-solid fa-headset icon-orange"></i>
                <div class="info-text">
                    <span class="label">HOTLINE:</span>
                    <span class="highlight">0977.508.430</span>
                </div>
            </div>
            
            <div class="info-item">
                <i class="fa-solid fa-location-dot icon-orange"></i>
                <span class="label">HỆ THỐNG CỬA HÀNG</span>
            </div>
        </div>

        <div class="search-area">
            <form action="search.php" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Bạn tìm gì hôm nay?">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>

        <div class="header-actions">
            <a href="#" class="action-item">
                <div class="icon-circle">
                    <i class="fa-solid fa-binoculars"></i>
                </div>
                <span>Tra cứu</span>
            </a>

            <a href="login.php" class="action-item">
                <div class="icon-circle">
                    <i class="fa-solid fa-user"></i>
                </div>
                <span>Tài khoản</span>
            </a>

            <a href="cart.php" class="action-item">
                <div class="icon-circle">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="cart-badge"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span> 
                </div>
                <span>Giỏ hàng</span>
            </a>
        </div>
    </div>
</header>

<nav class="main-menu-bar">
    <div class="container-menu"> 
        <ul class="menu-list">
            
            <li class="menu-item"><a href="index.php">Trang chủ</a></li>
            
            <li class="menu-item">
                <a href="#">Sản phẩm <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i></a>
                <ul class="sub-menu">
                    <li><a href="sanpham.php?dm_id=1">Vợt cầu lông</a></li>
                    <li><a href="sanpham.php?dm_id=2">Giày cầu lông</a></li>
                    <li><a href="sanpham.php?dm_id=3">Balo - Túi Vợt</a></li>
                    <li><a href="sanpham.php?dm_id=4">Quần Áo</a></li>
                    <li><a href="sanpham.php?dm_id=5">Phụ Kiện</a></li>
                </ul>
            </li>

            <li class="menu-item">
<a href="#">Hướng dẫn <i class="fa-solid fa-chevron-down"></i></a>
                <ul class="sub-menu">
                    <li><a href="#">Hướng dẫn thanh toán</a></li>
                    <li><a href="#">Hướng dẫn mua hàng</a></li>
                    <li><a href="#">Hướng dẫn chọn vợt cầu lông</a></li>
                </ul>
            </li>

            <li class="menu-item"><a href="#">Giới thiệu</a></li>
            <li class="menu-item"><a href="#">Liên hệ</a></li>

        </ul>
    </div>
</nav>