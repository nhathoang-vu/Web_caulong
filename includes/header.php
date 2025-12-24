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
                    <span class="highlight">0904082576</span>
                </div>
            </div>
            
            <div class="info-item">
                <i class="fa-solid fa-location-dot icon-orange"></i>
                <span class="label">HỆ THỐNG CỬA HÀNG</span>
            </div>
        </div>

        <div class="search-area" style="position: relative;">
            <form action="search.php" method="GET" class="search-form">
                <input type="text" name="q" id="search-input" placeholder="Bạn tìm gì hôm nay?" autocomplete="off">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            
            <div id="search-result-box"></div>
        </div>

        <div class="header-actions">
            
            <a href="#" class="action-item">
                <div class="icon-circle">
                    <i class="fa-solid fa-binoculars"></i>
                </div>
                <span>Tra cứu</span>
            </a>

            <?php if (isset($_SESSION['user_name'])): ?>
                
                <div class="action-item user-dropdown-parent">
                    <div class="user-info-trigger">
                        <div class="icon-circle">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <span><?php echo $_SESSION['user_name']; ?></span>
                    </div>

                    <ul class="user-dropdown-menu">
                        <li><a href="profile.php">Trang cá nhân</a></li>
                        <li><a href="logout.php">Đăng xuất</a></li>
                    </ul>
                </div>

            <?php else: ?>
                
                <a href="login.php" class="action-item">
                    <div class="icon-circle">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <span>Tài khoản</span>
                </a>

            <?php endif; ?>
            <a href="giohang.php" class="action-item">
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
                    <li><a href="sanpham.php?danhmuc=1">Vợt cầu lông</a></li>
                    <li><a href="sanpham.php?danhmuc=2">Giày cầu lông</a></li>
                    <li><a href="sanpham.php?danhmuc=3">Balo - Túi Vợt</a></li>
                    <li><a href="sanpham.php?danhmuc=4">Quần Áo</a></li>
                    <li><a href="sanpham.php?danhmuc=5">Phụ Kiện</a></li>
                </ul>
            </li>
            <li class="menu-item">
                <a href="#">Hướng dẫn <i class="fa-solid fa-chevron-down"></i></a>
                <ul class="sub-menu">
                    <li><a href="hdtt.php">Hướng dẫn thanh toán</a></li>
                    <li><a href="hdmh.php">Hướng dẫn mua hàng</a></li>
                    <li><a href="hdlcvot.php">Hướng dẫn chọn vợt cầu lông</a></li>
                </ul>
            </li>
            <li class="menu-item"><a href="gioithieu.php">Giới thiệu</a></li>
            <li class="menu-item"><a href="lienhe.php">Liên hệ</a></li>
        </ul>
    </div>
</nav>

<style>
    /* Khung chứa kết quả */
    #search-result-box {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #fff;
        z-index: 9999;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        display: none; /* Mặc định ẩn */
        border: 1px solid #eee;
        border-top: none;
    }
    
    /* Danh sách UL */
    .live-search-list {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 400px;
        overflow-y: auto;
    }

    /* Từng dòng sản phẩm */
    .live-search-list li {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #f1f1f1;
        cursor: pointer;
        transition: background 0.2s;
    }

    .live-search-list li:last-child {
        border-bottom: none;
        border-radius: 0 0 8px 8px;
    }

    .live-search-list li:hover {
        background-color: #f9f9f9;
    }

    /* Ảnh nhỏ */
    .search-item-img {
        width: 50px;
        height: 50px;
        margin-right: 15px;
        flex-shrink: 0;
    }

    .search-item-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #eee;
    }

    /* Thông tin text */
    .search-item-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .search-item-name {
        font-size: 14px;
        color: #333;
        font-weight: 500;
        margin-bottom: 4px;
        /* Giới hạn 2 dòng nếu tên dài */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .search-item-price {
        font-size: 13px;
        color: #fd4e00; /* Màu cam chủ đạo */
        font-weight: 700;
    }
    
    .no-result {
        padding: 15px;
        text-align: center;
        color: #777;
        font-size: 14px;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $('#search-input').on('keyup', function(){
        var keyword = $(this).val();
        
        if(keyword.length > 1) { // Gõ trên 1 ký tự mới tìm
            $.ajax({
                url: 'search_ajax.php',
                method: 'POST',
                data: {keyword: keyword},
                success: function(data){
                    $('#search-result-box').html(data);
                    $('#search-result-box').fadeIn();
                }
            });
        } else {
            $('#search-result-box').fadeOut();
            $('#search-result-box').html('');
        }
    });

    // Ẩn bảng kết quả khi click ra ngoài
    $(document).on('click', function(e){
        if (!$(e.target).closest('.search-area').length) {
            $('#search-result-box').fadeOut();
        }
    });
});
</script>