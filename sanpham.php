<?php
session_start();
require_once 'connect.php'; // File kết nối PDO

// 1. NHẬN DỮ LIỆU TỪ URL (Menu & Bộ lọc gửi lên)
$dm_id = isset($_GET['dm_id']) ? $_GET['dm_id'] : 0;        // ID Danh mục
$th_ids = isset($_GET['th']) ? $_GET['th'] : [];            // Mảng ID Thương hiệu (Checkbox)
$muc_gia = isset($_GET['gia']) ? $_GET['gia'] : '';         // Mức giá

// 2. LẤY TÊN DANH MỤC (Để hiện lên tiêu đề & Breadcrumb)
$ten_danhmuc = "Tất cả sản phẩm";
if ($dm_id > 0) {
    $stmt_dm = $conn->prepare("SELECT ten_danhmuc FROM danhmuc WHERE id = :id");
    $stmt_dm->execute([':id' => $dm_id]);
    $dm_row = $stmt_dm->fetch(PDO::FETCH_ASSOC);
    if ($dm_row) {
        $ten_danhmuc = $dm_row['ten_danhmuc'];
    }
}

// 3. LẤY DANH SÁCH THƯƠNG HIỆU (Để hiện checkbox ở Sidebar)
// Chỉ lấy những thương hiệu ĐANG CÓ sản phẩm để đỡ bị trống
$sql_th = "SELECT DISTINCT th.id, th.ten_thuonghieu 
           FROM thuonghieu th 
           JOIN sanpham sp ON th.id = sp.thuonghieu_id";
// Nếu đang xem danh mục cụ thể (vd: Vợt), chỉ hiện thương hiệu Vợt
if ($dm_id > 0) {
    $sql_th .= " WHERE sp.danhmuc_id = $dm_id";
}
$list_thuonghieu = $conn->query($sql_th)->fetchAll(PDO::FETCH_ASSOC);


// 4. XÂY DỰNG CÂU TRUY VẤN LỌC SẢN PHẨM (CORE LOGIC)
$sql = "SELECT * FROM sanpham WHERE 1=1";
$params = [];

// -> Lọc theo Danh mục (nếu chọn từ menu)
if ($dm_id > 0) {
    $sql .= " AND danhmuc_id = :dm_id";
    $params[':dm_id'] = $dm_id;
}

// -> Lọc theo Thương hiệu (nếu tích checkbox)
if (!empty($th_ids)) {
    // Tạo chuỗi placeholders (?,?,?)
    $placeholders = implode(',', array_fill(0, count($th_ids), '?'));
    $sql .= " AND thuonghieu_id IN ($placeholders)";
    // Gộp tham số vào mảng params (lưu ý: cách này dùng execute mảng tuần tự cho IN)
    // Để đơn giản với PDO khi dùng IN, ta sẽ nối thẳng ID vào chuỗi (với điều kiện ép kiểu int cho an toàn)
    $clean_ids = array_map('intval', $th_ids);
    $ids_string = implode(',', $clean_ids);
    // Sửa lại đoạn trên một chút để tránh xung đột params
    $sql = str_replace("AND thuonghieu_id IN ($placeholders)", "AND thuonghieu_id IN ($ids_string)", $sql);
}

// -> Lọc theo Giá
if (!empty($muc_gia)) {
    if ($muc_gia == 'duoi-500k') $sql .= " AND gia_ban < 500000";
    elseif ($muc_gia == '500k-1tr') $sql .= " AND gia_ban BETWEEN 500000 AND 1000000";
    elseif ($muc_gia == '1-3tr') $sql .= " AND gia_ban BETWEEN 1000000 AND 3000000";
    elseif ($muc_gia == 'tren-3tr') $sql .= " AND gia_ban > 3000000";
}

$sql .= " ORDER BY id DESC"; // Mới nhất lên đầu

// 5. THỰC THI
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($products);
} catch(PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo $ten_danhmuc; ?> - HBG Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
  
    <link rel="stylesheet" href="assets/css/footer.css">
    <style>
        /* Bố cục trang 2 cột */
        .container-page { width: 1200px; margin: 20px auto; display: flex; gap: 30px; padding: 0 15px; }
        
        /* 1. SIDEBAR BÊN TRÁI */
        .sidebar { width: 250px; flex-shrink: 0; }
        .filter-group { margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .filter-title { font-weight: bold; font-size: 16px; margin-bottom: 15px; display: block; text-transform: uppercase; }
        
        .filter-list { list-style: none; padding: 0; }
        .filter-list li { margin-bottom: 10px; }
        .filter-list label { cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 14px; color: #333; }
        .filter-list input[type="checkbox"], .filter-list input[type="radio"] { transform: scale(1.2); }
        
        .btn-filter-submit { width: 100%; background: #ff6600; color: white; border: none; padding: 10px; font-weight: bold; border-radius: 4px; cursor: pointer; transition: 0.3s; }
        .btn-filter-submit:hover { background: #e65c00; }

        /* 2. NỘI DUNG BÊN PHẢI */
        .main-content { flex-grow: 1; }
        
        /* Breadcrumb (Đường dẫn) */
        .breadcrumb { margin-bottom: 20px; font-size: 14px; color: #666; }
        .breadcrumb a { text-decoration: none; color: #333; }
        .breadcrumb span { margin: 0 5px; }
        
        /* Banner danh mục */
        .cat-banner { width: 100%; height: 250px; object-fit: cover; border-radius: 8px; margin-bottom: 20px; }

        /* Lưới sản phẩm (Grid) */
        .product-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .product-item { border: 1px solid #eee; border-radius: 8px; overflow: hidden; transition: 0.3s; padding-bottom: 10px; text-align: center; }
        .product-item:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-color: #ff6600; }
        .product-img { width: 100%; height: 220px; object-fit: contain; }
        .product-name { font-size: 15px; font-weight: bold; margin: 10px 0; height: 40px; overflow: hidden; padding: 0 10px; }
        .product-name a { text-decoration: none; color: #333; }
        .product-price { color: #d0021b; font-weight: bold; font-size: 16px; }

        .alert-empty { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; width: 100%; text-align: center; }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="container-page">
        <aside class="sidebar">
            <form action="sanpham.php" method="GET">
                <?php if($dm_id > 0): ?>
                    <input type="hidden" name="dm_id" value="<?php echo $dm_id; ?>">
                <?php endif; ?>

                <div class="filter-group">
                    <span class="filter-title">Mức giá</span>
                    <ul class="filter-list">
                        <li><label><input type="radio" name="gia" value="" <?php if($muc_gia == '') echo 'checked'; ?>> Tất cả</label></li>
                        <li><label><input type="radio" name="gia" value="duoi-500k" <?php if($muc_gia == 'duoi-500k') echo 'checked'; ?>> Dưới 500k</label></li>
                        <li><label><input type="radio" name="gia" value="500k-1tr" <?php if($muc_gia == '500k-1tr') echo 'checked'; ?>> 500k - 1 triệu</label></li>
                        <li><label><input type="radio" name="gia" value="1-3tr" <?php if($muc_gia == '1-3tr') echo 'checked'; ?>> 1 - 3 triệu</label></li>
                        <li><label><input type="radio" name="gia" value="tren-3tr" <?php if($muc_gia == 'tren-3tr') echo 'checked'; ?>> Trên 3 triệu</label></li>
                    </ul>
                </div>

                <div class="filter-group">
                    <span class="filter-title">Thương hiệu</span>
                    <ul class="filter-list">
                        <?php foreach($list_thuonghieu as $th): ?>
                            <li>
                                <label>
                                    <input type="checkbox" name="th[]" value="<?php echo $th['id']; ?>" 
                                    <?php if(in_array($th['id'], $th_ids)) echo 'checked'; ?>> 
                                    <?php echo $th['ten_thuonghieu']; ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <button type="submit" class="btn-filter-submit">ÁP DỤNG BỘ LỌC</button>
            </form>
        </aside>

        <main class="main-content">
            <div class="breadcrumb">
                <a href="index.php">Trang chủ</a> <span>/</span> <?php echo $ten_danhmuc; ?>
            </div>

            <img src="https://shopvnb.com/uploads/images/banner/vot-cau-long-taro-new.webp" class="cat-banner" alt="Banner">

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;"><?php echo $ten_danhmuc; ?> <span style="font-size: 14px; font-weight: normal;">(<?php echo $count; ?> sản phẩm)</span></h2>
            </div>

            <div class="product-grid">
                <?php if ($count > 0): ?>
                    <?php foreach ($products as $row): 
                        $img = !empty($row['hinh_anh']) ? 'assets/images/'.$row['hinh_anh'] : 'assets/images/no-image.png';
                    ?>
                        <div class="product-item">
                            <a href="chitiet.php?id=<?php echo $row['id']; ?>">
                                <img src="<?php echo $img; ?>" class="product-img" alt="<?php echo $row['ten_sanpham']; ?>">
                            </a>
                            <div class="product-name">
                                <a href="chitiet.php?id=<?php echo $row['id']; ?>"><?php echo $row['ten_sanpham']; ?></a>
                            </div>
                            <div class="product-price">
                                <?php echo number_format($row['gia_ban'], 0, ',', '.'); ?>đ
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert-empty">
                        Không tìm thấy sản phẩm nào phù hợp với tiêu chí lọc của bạn.
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>