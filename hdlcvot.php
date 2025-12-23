<?php 
session_start(); 
require_once 'connect.php'; 

// --- 1. HÀM PHP TẠO LINK LỌC ---
function createLink($key, $value) {
    $params = $_GET; 
    if ($value === '' || $value === 'all') {
        unset($params[$key]);
    } else {
        $params[$key] = $value; 
    }
    if(isset($params['page'])) unset($params['page']); 

    if (!empty($params)) {
        return '?' . http_build_query($params) . '#ketqua';
    } else {
        return 'hdlcvot.php#ketqua';
    }
}

// Hàm hỗ trợ selected
function checkSelected($key, $value) {
    if (!isset($_GET[$key]) && ($value === '' || $value === 'all')) return 'selected';
    if (isset($_GET[$key]) && $_GET[$key] == $value) return 'selected';
    return '';
}

// --- 2. LẤY DỮ LIỆU TỪ CSDL ---
try {
    // 2.1 Danh mục sản phẩm
    $stmt_dm = $conn->query("SELECT * FROM danhmuc ORDER BY ten_danhmuc ASC");
    $danhmuc_list = $stmt_dm->fetchAll(PDO::FETCH_ASSOC);

    // 2.2 Thương hiệu
    $stmt_brand = $conn->query("SELECT * FROM thuonghieu ORDER BY ten_thuonghieu ASC");
    $thuonghieu_list = $stmt_brand->fetchAll(PDO::FETCH_ASSOC);

    // 2.3 Màu sắc
    $stmt_color = $conn->query("SELECT DISTINCT mau_sac FROM bienthe_sanpham WHERE mau_sac != '' ORDER BY mau_sac ASC");
    $color_list = $stmt_color->fetchAll(PDO::FETCH_ASSOC);

    // 2.4 Kích thước
    $stmt_size = $conn->query("SELECT DISTINCT kich_thuoc FROM bienthe_sanpham WHERE kich_thuoc != '' ORDER BY kich_thuoc ASC");
    $size_list = $stmt_size->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $danhmuc_list = []; $thuonghieu_list = []; $color_list = []; $size_list = [];
}

// --- 3. XÂY DỰNG CÂU TRUY VẤN ---
$sql_condition = "WHERE 1=1"; 
$params_sql = [];
$filter_tags = [];

// A. Lọc Thương hiệu
if (isset($_GET['th']) && $_GET['th'] != '' && $_GET['th'] != 'all') {
    $th_id = $_GET['th'];
    $sql_condition .= " AND s.thuonghieu_id = :th";
    $params_sql['th'] = $th_id;
    foreach($thuonghieu_list as $br) { if($br['id'] == $th_id) { $filter_tags[] = "Hãng: " . $br['ten_thuonghieu']; break; } }
}

// B. Lọc Màu sắc
if (isset($_GET['mau']) && $_GET['mau'] != '' && $_GET['mau'] != 'all') {
    $mau = $_GET['mau'];
    $sql_condition .= " AND bt.mau_sac = :mau";
    $params_sql['mau'] = $mau;
    $filter_tags[] = "Màu: " . $mau;
}

// C. Lọc Kích thước
if (isset($_GET['size']) && $_GET['size'] != '' && $_GET['size'] != 'all') {
    $size = $_GET['size'];
    $sql_condition .= " AND bt.kich_thuoc = :size";
    $params_sql['size'] = $size;
    $filter_tags[] = "Size: " . $size;
}

// D. Lọc Giá
if (isset($_GET['gia']) && $_GET['gia'] != '' && $_GET['gia'] != 'all') {
    $g = $_GET['gia'];
    $price_col = "CASE WHEN s.gia_khuyenmai > 0 AND s.gia_khuyenmai < s.gia_ban THEN s.gia_khuyenmai ELSE s.gia_ban END";

    if ($g == 'duoi-1-trieu') $sql_condition .= " AND ($price_col < 1000000)";
    elseif ($g == '1-3-trieu') $sql_condition .= " AND ($price_col BETWEEN 1000000 AND 3000000)";
    elseif ($g == '3-5-trieu') $sql_condition .= " AND ($price_col BETWEEN 3000000 AND 5000000)";
    elseif ($g == 'tren-5-trieu') $sql_condition .= " AND ($price_col > 5000000)";
    
    if ($g == 'duoi-1-trieu') $filter_tags[] = "Dưới 1 triệu";
    elseif ($g == '1-3-trieu') $filter_tags[] = "1 - 3 triệu";
    elseif ($g == '3-5-trieu') $filter_tags[] = "3 - 5 triệu";
    elseif ($g == 'tren-5-trieu') $filter_tags[] = "Trên 5 triệu";
}

// E. Sắp xếp
$sort_sql = "ORDER BY s.ngay_tao DESC";
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == 'gia-tang') $sort_sql = "ORDER BY (CASE WHEN s.gia_khuyenmai > 0 THEN s.gia_khuyenmai ELSE s.gia_ban END) ASC";
    elseif ($_GET['sort'] == 'gia-giam') $sort_sql = "ORDER BY (CASE WHEN s.gia_khuyenmai > 0 THEN s.gia_khuyenmai ELSE s.gia_ban END) DESC";
    elseif ($_GET['sort'] == 'ten-az') $sort_sql = "ORDER BY s.ten_sanpham ASC";
}

// --- 4. THỰC THI TRUY VẤN ---
try {
    $sql_final = "SELECT DISTINCT s.* FROM sanpham s
                  LEFT JOIN bienthe_sanpham bt ON s.id = bt.sanpham_id
                  $sql_condition 
                  $sort_sql";
                  
    $stmt = $conn->prepare($sql_final);
    $stmt->execute($params_sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($products);
} catch(PDOException $e) {
    $products = []; $count = 0;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hướng Dẫn & Lựa Chọn Vợt | HBG Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css"> 
    <link rel="stylesheet" href="assets/css/huongdan.css"> 
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    
   
</head>
<body>

    <?php include 'includes/header.php'; ?>
    
    <div class="filter-container" id="ketqua">
        
        <aside class="filter-sidebar">
            <div class="sidebar-guide-sticky">
                
                <div class="sidebar-guide-box">
                    <h3><i class="fa-solid fa-book-open"></i> Hướng dẫn</h3>
                    <ul>
                        <li><a href="hdmh.php"><i class="fa-solid fa-cart-shopping"></i> HD Mua hàng</a></li>
                        <li><a href="hdtt.php"><i class="fa-solid fa-money-bill"></i> HD Thanh toán</a></li>
                        <li><a href="hdlcvot.php" class="active"><i class="fa-solid fa-table-tennis-paddle-ball"></i> HD Chọn vợt</a></li>
                    </ul>
                </div>

                <div class="sidebar-guide-box">
                    <h3><i class="fa-solid fa-layer-group"></i> Loại sản phẩm</h3>
                    <ul>
                        <li>
                            <a href="sanpham.php">
                                <i class="fa-solid fa-border-all"></i> Tất cả
                            </a>
                        </li>
                        <?php foreach($danhmuc_list as $dm): 
                            // --- LOGIC TỰ ĐỘNG CHỌN ICON DỰA TRÊN TÊN ---
                            $ten = mb_strtolower($dm['ten_danhmuc'], 'UTF-8');
                            $icon_class = "fa-solid fa-angle-right"; // Icon mặc định

                            if(strpos($ten, 'vợt') !== false) {
                                $icon_class = "fa-solid fa-table-tennis-paddle-ball";
                            } elseif(strpos($ten, 'giày') !== false) {
                                $icon_class = "fa-solid fa-shoe-prints";
                            } elseif(strpos($ten, 'áo') !== false || strpos($ten, 'quần') !== false) {
                                $icon_class = "fa-solid fa-shirt";
                            } elseif(strpos($ten, 'balo') !== false || strpos($ten, 'túi') !== false) {
                                $icon_class = "fa-solid fa-bag-shopping";
                            } elseif(strpos($ten, 'phụ kiện') !== false) {
                                $icon_class = "fa-solid fa-socks";
                            }
                        ?>
                            <li>
                                <a href="sanpham.php?dm_id=<?php echo $dm['id']; ?>">
                                    <i class="<?php echo $icon_class; ?>"></i> <?php echo htmlspecialchars($dm['ten_danhmuc']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>
        </aside>

        <main class="main-list">
            
            <div class="filter-dropdown-bar">
                
                <div class="filter-item">
                    <label class="filter-label">Thương hiệu</label>
                    <select class="custom-select" onchange="window.location.href=this.value">
                        <option value="<?php echo createLink('th', 'all'); ?>">-- Tất cả --</option>
                        <?php foreach($thuonghieu_list as $th): ?>
                            <option value="<?php echo createLink('th', $th['id']); ?>" <?php echo checkSelected('th', $th['id']); ?>>
                                <?php echo htmlspecialchars($th['ten_thuonghieu']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label class="filter-label">Mức giá</label>
                    <select class="custom-select" onchange="window.location.href=this.value">
                        <option value="<?php echo createLink('gia', 'all'); ?>">-- Tất cả --</option>
                        <option value="<?php echo createLink('gia', 'duoi-1-trieu'); ?>" <?php echo checkSelected('gia', 'duoi-1-trieu'); ?>>Dưới 1 triệu</option>
                        <option value="<?php echo createLink('gia', '1-3-trieu'); ?>" <?php echo checkSelected('gia', '1-3-trieu'); ?>>1 - 3 triệu</option>
                        <option value="<?php echo createLink('gia', '3-5-trieu'); ?>" <?php echo checkSelected('gia', '3-5-trieu'); ?>>3 - 5 triệu</option>
                        <option value="<?php echo createLink('gia', 'tren-5-trieu'); ?>" <?php echo checkSelected('gia', 'tren-5-trieu'); ?>>Trên 5 triệu</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label class="filter-label">Màu sắc</label>
                    <select class="custom-select" onchange="window.location.href=this.value">
                        <option value="<?php echo createLink('mau', 'all'); ?>">-- Tất cả --</option>
                        <?php foreach($color_list as $cl): ?>
                            <option value="<?php echo createLink('mau', $cl['mau_sac']); ?>" <?php echo checkSelected('mau', $cl['mau_sac']); ?>>
                                <?php echo htmlspecialchars($cl['mau_sac']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if(!empty($size_list)): ?>
                <div class="filter-item">
                    <label class="filter-label">Size / Chu vi</label>
                    <select class="custom-select" onchange="window.location.href=this.value">
                        <option value="<?php echo createLink('size', 'all'); ?>">-- Tất cả --</option>
                        <?php foreach($size_list as $sz): ?>
                            <option value="<?php echo createLink('size', $sz['kich_thuoc']); ?>" <?php echo checkSelected('size', $sz['kich_thuoc']); ?>>
                                <?php echo htmlspecialchars($sz['kich_thuoc']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="filter-item">
                    <label class="filter-label">Sắp xếp</label>
                    <select class="custom-select" onchange="window.location.href=this.value">
                        <option value="<?php echo createLink('sort', 'moi-nhat'); ?>" <?php echo checkSelected('sort', 'moi-nhat'); ?>>Mới nhất</option>
                        <option value="<?php echo createLink('sort', 'gia-tang'); ?>" <?php echo checkSelected('sort', 'gia-tang'); ?>>Giá tăng dần</option>
                        <option value="<?php echo createLink('sort', 'gia-giam'); ?>" <?php echo checkSelected('sort', 'gia-giam'); ?>>Giá giảm dần</option>
                        <option value="<?php echo createLink('sort', 'ten-az'); ?>" <?php echo checkSelected('sort', 'ten-az'); ?>>Tên A-Z</option>
                    </select>
                </div>

            </div>

            <div class="list-header">
                <div>
                    <span class="result-count">Tìm thấy <strong><?php echo $count; ?></strong> sản phẩm</span>
                    <?php if(!empty($filter_tags)): ?>
                        <span style="color: #fd4e00; font-size: 13px; margin-left: 10px; font-weight: 500;">
                            <i class="fa-solid fa-filter"></i> Đang lọc: <?php echo implode(", ", $filter_tags); ?>
                        </span>
                        <a href="hdlcvot.php" class="tag-reset"><i class="fa-solid fa-xmark"></i> Xóa</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="product-grid">
                <?php if ($count > 0): ?>
                    <?php foreach ($products as $row): 
                        $img_path = !empty($row['hinh_anh']) ? 'assets/images/'.$row['hinh_anh'] : 'assets/images/no-image.png';
                        $gia_ban = $row['gia_ban'];
                        $gia_km = $row['gia_khuyenmai'];
                        $has_sale = ($gia_km > 0 && $gia_km < $gia_ban);
                        $percent = $has_sale ? round((($gia_ban - $gia_km) / $gia_ban) * 100) : 0;
                    ?>
                        <div class="product-card">
                            <?php if($has_sale): ?>
                                <div class="sale-badge">-<?php echo $percent; ?>%</div>
                            <?php endif; ?>
                            
                            <a href="chitiet.php?id=<?php echo $row['id']; ?>" class="p-img-box">
                                <img src="<?php echo $img_path; ?>" alt="<?php echo htmlspecialchars($row['ten_sanpham']); ?>">
                            </a>
                            
                            <div class="p-info">
                                <a href="chitiet.php?id=<?php echo $row['id']; ?>" class="p-name">
                                    <?php echo htmlspecialchars($row['ten_sanpham']); ?>
                                </a>
                                
                                <div class="p-price-row">
                                    <?php if($has_sale): ?>
                                        <span class="p-price-new"><?php echo number_format($gia_km, 0, ',', '.'); ?>đ</span>
                                        <span class="p-price-old"><?php echo number_format($gia_ban, 0, ',', '.'); ?>đ</span>
                                    <?php else: ?>
                                        <span class="p-price-new"><?php echo number_format($gia_ban, 0, ',', '.'); ?>đ</span>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="chitiet.php?id=<?php echo $row['id']; ?>" class="btn-buy">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-product">
                        <i class="fa-solid fa-box-open" style="font-size: 50px; color: #ddd; margin-bottom: 20px;"></i>
                        <p style="font-size: 16px; color: #666;">Không tìm thấy sản phẩm nào phù hợp.</p>
                        <a href="hdlcvot.php" class="tag-reset" style="background: #fd4e00; margin-top: 15px; color: white;">
                            <i class="fa-solid fa-rotate-left"></i> Xem tất cả
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>
</html>