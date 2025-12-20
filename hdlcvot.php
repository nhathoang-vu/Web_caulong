<?php 
session_start(); 
require_once 'connect.php'; 

// --- 1. HÀM PHP TẠO LINK LỌC THÔNG MINH ---
function createLink($key, $value) {
    $params = $_GET; 
    if (isset($params[$key]) && $params[$key] === $value) {
        unset($params[$key]); 
    } else {
        $params[$key] = $value; 
    }
    if (!empty($params)) {
        return '?' . http_build_query($params) . '#ketqua';
    } else {
        return 'hdlcvot.php#ketqua';
    }
}

function isActive($key, $value) {
    return (isset($_GET[$key]) && $_GET[$key] === $value) ? 'active-choice' : '';
}

// --- 2. XỬ LÝ SQL ---
$sql_condition = "WHERE 1=1"; 
$params_sql = [];
$filter_tags = [];

if (isset($_GET['hang'])) {
    $sql_condition .= " AND hang_sx = :hang";
    $params_sql['hang'] = $_GET['hang'];
    $filter_tags[] = "Hãng " . strtoupper($_GET['hang']);
}

if (isset($_GET['gia'])) {
    $g = $_GET['gia'];
    if ($g == 'duoi-1-trieu') {
        $sql_condition .= " AND gia_ban < 1000000";
        $filter_tags[] = "Giá < 1 Triệu";
    } elseif ($g == '1-2-trieu') {
        $sql_condition .= " AND gia_ban BETWEEN 1000000 AND 2000000";
        $filter_tags[] = "Giá 1 - 2 Triệu";
    } elseif ($g == '2-3-trieu') {
        $sql_condition .= " AND gia_ban BETWEEN 2000000 AND 3000000";
        $filter_tags[] = "Giá 2 - 3 Triệu";
    } elseif ($g == 'tren-3-trieu') {
        $sql_condition .= " AND gia_ban > 3000000";
        $filter_tags[] = "Giá > 3 Triệu";
    }
}

if (isset($_GET['style'])) {
    $sql_condition .= " AND loi_choi = :style";
    $params_sql['style'] = $_GET['style'];
    $filter_tags[] = "Lối chơi " . ucfirst($_GET['style']);
}

if (isset($_GET['weight'])) {
    $sql_condition .= " AND trong_luong = :w";
    $params_sql['w'] = $_GET['weight'];
    $filter_tags[] = "Size " . strtoupper($_GET['weight']);
}

$title_result = empty($filter_tags) ? "Tất cả sản phẩm nổi bật" : "Kết quả lọc: " . implode(" + ", $filter_tags);

$sql = "SELECT * FROM sanpham $sql_condition ORDER BY (gia_ban < gia_goc) DESC, gia_ban ASC"; 

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params_sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $products = []; 
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tư vấn chọn vợt - HBG Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/huongdan.css">

</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="guide-container">
        
       <aside class="sidebar-guide">
    <h3><i class="fa-solid fa-list-ul"></i> &nbsp; DANH MỤC</h3>
    <ul class="category-list">
        <li>
            <a href="sanpham.php?dm_id=1"> <span>Vợt Cầu Lông</span>
                <i class="fa-solid fa-plus"></i>
            </a>
        </li>
        <li>
            <a href="sanpham.php?dm_id=2"> <span>Giày Cầu Lông</span>
                <i class="fa-solid fa-plus"></i>
            </a>
        </li>
        <li>
            <a href="sanpham.php?dm_id=3">
                <span>Balo - Túi Vợt</span>
                <i class="fa-solid fa-plus"></i>
            </a>
        </li>
        <li>
            <a href="sanpham.php?dm_id=4">
                <span>Quần Áo</span>
                <i class="fa-solid fa-plus"></i>
            </a>
        </li>
        <li>
            <a href="sanpham.php?dm_id=5">
                <span>Phụ Kiện</span>
                <i class="fa-solid fa-plus"></i>
            </a>
        </li>
    </ul>

    <div class="sidebar-box">
        <h3><i class="fa-solid fa-fire"></i> &nbsp; Bán chạy nhất</h3>
        </div>
</aside>

        <main class="main-guide">
            <h1>Tư vấn chọn vợt cầu lông</h1>
            <p class="sub-desc">Hệ thống lọc thông minh giúp bạn tìm kiếm cây vợt phù hợp nhất.</p>

            <?php if(!empty($_GET)): ?>
                <a href="hdlcvot.php" class="btn-reset" style="display:inline-block; padding:8px 15px; background:#444; color:#fff; border-radius:20px; text-decoration:none; font-size:13px; margin-bottom:20px;"><i class="fa-solid fa-rotate-left"></i> &nbsp; Xóa bộ lọc / Làm mới</a>
            <?php endif; ?>

            <div class="selection-group">
                <div class="group-title"><i class="fa-solid fa-star"></i> Chọn Thương hiệu</div>
                <div class="grid-box">
                    <a href="<?php echo createLink('hang', 'yonex'); ?>" class="select-item <?php echo isActive('hang', 'yonex'); ?>">
                        <img src="assets/images/huongdan/logo-yonex.png" alt="Yonex">
                        <span class="item-name">YONEX</span>
                    </a>
                    <a href="<?php echo createLink('hang', 'victor'); ?>" class="select-item <?php echo isActive('hang', 'victor'); ?>">
                        <img src="assets/images/huongdan/logo-victor.png" alt="Victor">
                        <span class="item-name">VICTOR</span>
                    </a>
                    <a href="<?php echo createLink('hang', 'lining'); ?>" class="select-item <?php echo isActive('hang', 'lining'); ?>">
                        <img src="assets/images/huongdan/logo-lining.png" alt="Lining">
                        <span class="item-name">LINING</span>
                    </a>
                    <a href="<?php echo createLink('hang', 'mizuno'); ?>" class="select-item <?php echo isActive('hang', 'mizuno'); ?>">
                        <img src="assets/images/huongdan/logo-mizuno.png" alt="Mizuno">
                        <span class="item-name">MIZUNO</span>
                    </a>
                </div>
            </div>

            <div class="selection-group">
                <div class="group-title"><i class="fa-solid fa-wallet"></i> Chọn Mức giá</div>
                <div class="grid-box">
                    <a href="<?php echo createLink('gia', 'duoi-1-trieu'); ?>" class="select-item <?php echo isActive('gia', 'duoi-1-trieu'); ?>">
                        <i class="fa-solid fa-tag"></i><span class="item-name">Dưới 1 Triệu</span>
                    </a>
                    <a href="<?php echo createLink('gia', '1-2-trieu'); ?>" class="select-item <?php echo isActive('gia', '1-2-trieu'); ?>">
                        <i class="fa-solid fa-sack-dollar"></i><span class="item-name">1 - 2 Triệu</span>
                    </a>
                    <a href="<?php echo createLink('gia', '2-3-trieu'); ?>" class="select-item <?php echo isActive('gia', '2-3-trieu'); ?>">
                        <i class="fa-solid fa-gem"></i><span class="item-name">2 - 3 Triệu</span>
                    </a>
                    <a href="<?php echo createLink('gia', 'tren-3-trieu'); ?>" class="select-item <?php echo isActive('gia', 'tren-3-trieu'); ?>">
                        <i class="fa-solid fa-crown"></i><span class="item-name">Trên 3 Triệu</span>
                    </a>
                </div>
            </div>

            <div class="selection-group">
                <div class="group-title"><i class="fa-solid fa-fire"></i> Chọn Lối chơi</div>
                <div class="grid-box">
                    <a href="<?php echo createLink('style', 'tan-cong'); ?>" class="select-item <?php echo isActive('style', 'tan-cong'); ?>">
                        <i class="fa-solid fa-gavel"></i><span class="item-name">Thiên Công</span>
                    </a>
                    <a href="<?php echo createLink('style', 'cong-thu'); ?>" class="select-item <?php echo isActive('style', 'cong-thu'); ?>">
                        <i class="fa-solid fa-scale-balanced"></i><span class="item-name">Công Thủ</span>
                    </a>
                    <a href="<?php echo createLink('style', 'phong-thu'); ?>" class="select-item <?php echo isActive('style', 'phong-thu'); ?>">
                        <i class="fa-solid fa-shield-halved"></i><span class="item-name">Phòng Thủ</span>
                    </a>
                    <a href="<?php echo createLink('style', 'nguoi-moi'); ?>" class="select-item <?php echo isActive('style', 'nguoi-moi'); ?>">
                        <i class="fa-solid fa-seedling"></i><span class="item-name">Người Mới</span>
                    </a>
                </div>
            </div>

            <div class="selection-group">
                <div class="group-title"><i class="fa-solid fa-weight-hanging"></i> Chọn Trọng lượng</div>
                <div class="grid-box">
                    <a href="<?php echo createLink('weight', '3u'); ?>" class="select-item <?php echo isActive('weight', '3u'); ?>">
                        <span class="item-name" style="font-size:16px;">3U</span><span class="item-name" style="font-weight:400; font-size:10px;">(85-89g)</span>
                    </a>
                    <a href="<?php echo createLink('weight', '4u'); ?>" class="select-item <?php echo isActive('weight', '4u'); ?>">
                        <span class="item-name" style="font-size:16px;">4U</span><span class="item-name" style="font-weight:400; font-size:10px;">(80-84g)</span>
                    </a>
                    <a href="<?php echo createLink('weight', '5u'); ?>" class="select-item <?php echo isActive('weight', '5u'); ?>">
                        <span class="item-name" style="font-size:16px;">5U</span><span class="item-name" style="font-weight:400; font-size:10px;">(< 80g)</span>
                    </a>
                     <a href="<?php echo createLink('weight', 'f'); ?>" class="select-item <?php echo isActive('weight', 'f'); ?>">
                        <span class="item-name" style="font-size:16px;">F</span><span class="item-name" style="font-weight:400; font-size:10px;">(Siêu nhẹ)</span>
                    </a>
                </div>
            </div>

            <div id="ketqua" class="result-section">
                <h2 class="result-title"><?php echo $title_result; ?></h2>
                <div class="product-grid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $row): ?>
                            <?php 
                                $is_sale = ($row['gia_ban'] < $row['gia_goc']);
                                $percent = $is_sale ? round((($row['gia_goc'] - $row['gia_ban']) / $row['gia_goc']) * 100) : 0;
                                
                                // XỬ LÝ ẢNH LOCAL
                                $image_url = 'img/' . $row['hinh_anh'];
                                if (empty($row['hinh_anh']) || !file_exists('img/' . $row['hinh_anh'])) { 
                                    $image_url = 'img/no-image.png'; 
                                }
                            ?>
                            <div class="product-card">
                                <?php if($is_sale): ?>
                                    <span class="badge-sale">-<?php echo $percent; ?>%</span>
                                <?php endif; ?>
                                <div class="img-container">
                                    <img src="<?php echo $image_url; ?>" alt="<?php echo $row['ten_sp']; ?>" class="product-img">
                                </div>
                                <div class="product-info">
                                    <a href="chitiet.php?id=<?php echo $row['id']; ?>" class="p-name"><?php echo $row['ten_sp']; ?></a>
                                    <div class="p-price-row">
                                        <?php if($is_sale): ?>
                                            <span class="p-price-old"><?php echo number_format($row['gia_goc'], 0, ',', '.'); ?>đ</span>
                                        <?php endif; ?>
                                        <span class="p-price-new"><?php echo number_format($row['gia_ban'], 0, ',', '.'); ?>đ</span>
                                    </div>
                                    <a href="chitiet.php?id=<?php echo $row['id']; ?>" class="btn-detail">Xem chi tiết</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-product">
                            <i class="fa-solid fa-magnifying-glass-minus" style="font-size:40px; color:#ccc; margin-bottom:15px;"></i>
                            <p>Không tìm thấy sản phẩm nào phù hợp.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>