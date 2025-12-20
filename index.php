<?php
session_start();
require_once 'connect.php';

// --- PHẦN 1: XỬ LÝ LOGIC (LẤY DỮ LIỆU) ---
$flash_sales = []; // Mảng chứa sản phẩm sale

try {
    // Truy vấn lấy 6 sản phẩm giảm giá sâu nhất
    $sql_fs = "SELECT * FROM sanpham 
               WHERE gia_khuyenmai > 0 
               ORDER BY ((gia_ban - gia_khuyenmai) / gia_ban) DESC 
               LIMIT 6";
    $stmt = $conn->prepare($sql_fs);
    $stmt->execute();
    $flash_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { 
    // Ghi lỗi vào log thay vì hiện ra màn hình để tránh vỡ giao diện
    error_log("Lỗi truy vấn: " . $e->getMessage());
}
    $sql_thuonghieu = "SELECT * FROM thuonghieu ORDER BY id ASC";
    $stmt_th = $conn->prepare($sql_thuonghieu);
    $stmt_th->execute();
    $thuonghieu_list = $stmt_th->fetchAll(PDO::FETCH_ASSOC);

    $default_brand_id = 1; 
    $sql_default = "SELECT * FROM sanpham WHERE danhmuc_id = 1 AND thuonghieu_id = :id ORDER BY gia_ban DESC LIMIT 6";
    $stmt_def = $conn->prepare($sql_default);
    $stmt_def->bindParam(':id', $default_brand_id);
    $stmt_def->execute();
    $default_products = $stmt_def->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Cầu Lông - HBG</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main style="padding-top: 0; background: #f9f9f9; padding-bottom: 50px;">
        
        <div class="slider-container">
            <div class="slides-wrapper">
                <div class="slide"><img src="assets/images/banner/banner1.jpg" alt="Banner 1"></div>
                <div class="slide"><img src="assets/images/banner/banner2.jpg" alt="Banner 2"></div>
                <div class="slide"><img src="assets/images/banner/banner3.jpg" alt="Banner 3"></div>
            </div>

            <button class="prev-btn" onclick="moveSlide(-1)"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="next-btn" onclick="moveSlide(1)"><i class="fa-solid fa-chevron-right"></i></button>
            
            <div class="dots-container">
                <span class="dot active" onclick="currentSlide(0)"></span>
                <span class="dot" onclick="currentSlide(1)"></span>
                <span class="dot" onclick="currentSlide(2)"></span>
            </div>
        </div>

        <div class="container">
            <div class="policy-section">
                <div class="policy-item">
                    <div class="policy-icon"><i class="fa-solid fa-truck-fast"></i></div>
                    <div class="policy-text">
                        <div class="policy-title">Vận chuyển <strong>TOÀN QUỐC</strong></div>
                        <div class="policy-desc">Thanh toán khi nhận hàng</div>
                    </div>
                </div>
                
                <div class="policy-item">
                    <div class="policy-icon"><i class="fa-solid fa-medal"></i></div>
                    <div class="policy-text">
                        <div class="policy-title">Bảo đảm <strong>CHẤT LƯỢNG</strong></div>
                        <div class="policy-desc">Sản phẩm chính hãng 100%</div>
                    </div>
                </div>

                <div class="policy-item">
                    <div class="policy-icon"><i class="fa-regular fa-credit-card"></i></div>
                    <div class="policy-text">
                        <div class="policy-title">Tiến hành <strong>THANH TOÁN</strong></div>
                        <div class="policy-desc">Với nhiều phương thức</div>
                    </div>
                </div>

                <div class="policy-item">
                    <div class="policy-icon"><i class="fa-solid fa-rotate"></i></div>
                    <div class="policy-text">
                        <div class="policy-title">Đổi sản phẩm <strong>MỚI</strong></div>
                        <div class="policy-desc">Nếu sản phẩm có lỗi</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="section-title">
            <h2>Sản phẩm nổi bật</h2>
        </div>
        <div class="container">
            <div class="flash-sale-section">
                
                <div class="fs-header">
                    <div class="fs-title">
                        FLA<i class="fa-solid fa-bolt lightning-icon"></i>H SALE
                    </div>
                    <div class="countdown">
                        <span>Kết thúc sau:</span>
                        <div class="time-box" id="cd-hours">00</div> :
                        <div class="time-box" id="cd-min">00</div> :
                        <div class="time-box" id="cd-sec">00</div>
                    </div>
                </div>

                <div class="fs-list">
                    <?php if (count($flash_sales) > 0): ?>
                        <?php foreach ($flash_sales as $row): 
                            // Xử lý dữ liệu hiển thị
                            $phantram = 0;
                            if($row['gia_ban'] > 0){
                                $phantram = round((($row['gia_ban'] - $row['gia_khuyenmai']) / $row['gia_ban']) * 100);
                            }
                            $img = !empty($row['hinh_anh']) ? 'assets/images/'.$row['hinh_anh'] : 'assets/images/no-image.png';
                            
                            // Tạo thanh trạng thái giả lập
                            $da_ban = rand(5, 50);
                            $tong = $da_ban + rand(5, 20);
                            $percent_bar = ($da_ban / $tong) * 100;
                        ?>
                            <div class="fs-card" onclick="window.location.href='chitiet.php?id=<?php echo $row['id']; ?>'">
                                <div class="discount-badge" style="left: auto; right: 10px;">-<?php echo $phantram; ?>%</div>
                                <img src="<?php echo $img; ?>" alt="<?php echo $row['ten_sanpham']; ?>">
                                
                                <div class="fs-name"><?php echo $row['ten_sanpham']; ?></div>
                                
                                <div class="fs-price">
                                    <span class="price-new"><?php echo number_format($row['gia_khuyenmai'], 0, ',', '.'); ?>đ</span>
                                    <span class="price-old"><?php echo number_format($row['gia_ban'], 0, ',', '.'); ?>đ</span>
                                </div>

                                <div class="sell-status">
                                    <div class="sell-bar" style="width: <?php echo rand(60, 90); ?>%;"></div>
                                    <span class="sell-text"><i class="fa-solid fa-fire"></i> ĐANG DIỄN RA</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:#fff; grid-column: 1/-1; text-align: center;">Đang cập nhật Flash Sale...</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="section-title">
            <h2>Vợt cầu lông</h2>
        </div>
        <div class="orange-section-container">
    
    <div class="orange-section-header">
        <div class="os-title">
        </div>
        
        <div class="os-tabs">
            <?php foreach($thuonghieu_list as $th): ?>
                <span class="os-tab-item <?php echo ($th['id'] == 1) ? 'active' : ''; ?>" 
                      onclick="loadProducts(<?php echo $th['id']; ?>, this)">
                    <?php echo $th['ten_thuonghieu']; ?>
                </span>
            <?php endforeach; ?>
            
            <a href="sanpham.php?danhmuc=1" class="os-view-all">
                Xem Tất Cả <i class="fa-solid fa-angle-right"></i>
            </a>
        </div>
    </div>

    <div class="orange-product-box">
        <div class="fs-list" id="ajax-product-list">
            <?php if (count($default_products) > 0): ?>
                <?php foreach ($default_products as $row): 
                    $phantram = 0;
                    if($row['gia_ban'] > 0 && $row['gia_khuyenmai'] > 0 && $row['gia_khuyenmai'] < $row['gia_ban']){
                        $phantram = round((($row['gia_ban'] - $row['gia_khuyenmai']) / $row['gia_ban']) * 100);
                    }
                    $img = !empty($row['hinh_anh']) ? 'assets/images/'.$row['hinh_anh'] : 'assets/images/no-image.png';
                ?>
                    <div class="fs-card" onclick="window.location.href='chitiet.php?id=<?php echo $row['id']; ?>'">
                        <?php if($phantram > 0): ?>
                            <div class="discount-badge">-<?php echo $phantram; ?>%</div>
                        <?php endif; ?>
                        
                        <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($row['ten_sanpham']); ?>">
                        <div class="fs-name"><?php echo htmlspecialchars($row['ten_sanpham']); ?></div>
                        
                        <div class="fs-price">
                            <?php if($row['gia_khuyenmai'] > 0 && $row['gia_khuyenmai'] < $row['gia_ban']): ?>
                                <span class="price-new"><?php echo number_format($row['gia_khuyenmai'], 0, ',', '.'); ?>đ</span>
                                <span class="price-old"><?php echo number_format($row['gia_ban'], 0, ',', '.'); ?>đ</span>
                            <?php else: ?>
                                <span class="price-new"><?php echo number_format($row['gia_ban'], 0, ',', '.'); ?>đ</span>
                            <?php endif; ?>
                        </div>
                        
                        <div style="margin-top:auto; width:100%;">
                            <button style="background:#fd4e00; color:#fff; border:none; width:100%; padding:5px; border-radius:4px; cursor:pointer; font-weight:bold;">Mua Ngay</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#fff; padding:10px;">Chưa có sản phẩm.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // --- 1. SLIDER LOGIC ---
        let slideIndex = 0;
        const slidesWrapper = document.querySelector('.slides-wrapper');
        const dots = document.querySelectorAll('.dot');
        const totalSlides = document.querySelectorAll('.slide').length;

        function showSlides(n) {
            if (n >= totalSlides) slideIndex = 0;
            else if (n < 0) slideIndex = totalSlides - 1;
            else slideIndex = n;
            slidesWrapper.style.transform = `translateX(-${slideIndex * 100}%)`;
            dots.forEach(d => d.classList.remove('active'));
            if(dots[slideIndex]) dots[slideIndex].classList.add('active');
        }

        function moveSlide(n) { showSlides(slideIndex + n); resetTimer(); }
        function currentSlide(n) { showSlides(n); resetTimer(); }

        let timer = setInterval(() => { moveSlide(1); }, 5000);
        function resetTimer() { clearInterval(timer); timer = setInterval(() => { moveSlide(1); }, 5000); }

        // --- 2. FLASH SALE COUNTDOWN LOGIC ---
        function startDailyCountdown() {
            const now = new Date();
            const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
            let diff = endOfDay - now;

            const x = setInterval(function() {
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                document.getElementById("cd-hours").innerText = hours < 10 ? "0" + hours : hours;
                document.getElementById("cd-min").innerText = minutes < 10 ? "0" + minutes : minutes;
                document.getElementById("cd-sec").innerText = seconds < 10 ? "0" + seconds : seconds;

                diff -= 1000;
                if (diff < 0) {
                    clearInterval(x);
                    location.reload(); 
                }
            }, 1000);
        }
        document.addEventListener('DOMContentLoaded', startDailyCountdown);
    </script>
<script>
function loadProducts(id, element) {
    // 1. XỬ LÝ GIAO DIỆN (Active Tab)
    // Tìm tất cả các nút tab và bỏ class active cũ đi
    var allTabs = document.querySelectorAll('.os-tab-item');
    allTabs.forEach(function(tab) {
        tab.classList.remove('active');
    });

    // Thêm active cho nút vừa bấm
    if (element) {
        element.classList.add('active');
    }

    // 2. GỌI AJAX (Sửa lỗi: dùng đúng tên biến 'brand_id')
    var xhr = new XMLHttpRequest();
    
    // LƯU Ý QUAN TRỌNG: Ở đây phải là brand_id giống hệt trong file PHP
    xhr.open('GET', 'get_products_ajax.php?brand_id=' + id, true);
    
    // Hiệu ứng mờ nhẹ khi đang tải
    var listContainer = document.getElementById('ajax-product-list');
    listContainer.style.opacity = '0.5';
    
    xhr.onload = function() {
        if (this.status == 200) {
            // Cập nhật nội dung
            listContainer.innerHTML = this.responseText;
            // Trả lại độ sáng
            listContainer.style.opacity = '1';
        }
    };
    xhr.send();
}
</script>
</body>
</html>