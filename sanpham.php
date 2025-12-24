<?php
session_start();
require_once 'connect.php'; // File kết nối PDO

// 1. NHẬN DỮ LIỆU TỪ URL
$dm_id = isset($_GET['dm_id']) ? $_GET['dm_id'] : 0;        
$th_ids = isset($_GET['th']) ? $_GET['th'] : [];            
$muc_gia = isset($_GET['gia']) ? $_GET['gia'] : '';         

// 2. LẤY TÊN DANH MỤC
$ten_danhmuc = "Tất cả sản phẩm";
if ($dm_id > 0) {
    $stmt_dm = $conn->prepare("SELECT ten_danhmuc FROM danhmuc WHERE id = :id");
    $stmt_dm->execute([':id' => $dm_id]);
    $dm_row = $stmt_dm->fetch(PDO::FETCH_ASSOC);
    if ($dm_row) {
        $ten_danhmuc = $dm_row['ten_danhmuc'];
    }
}

// 3. LẤY DANH SÁCH THƯƠNG HIỆU
$sql_th = "SELECT DISTINCT th.id, th.ten_thuonghieu 
           FROM thuonghieu th 
           JOIN sanpham sp ON th.id = sp.thuonghieu_id";
if ($dm_id > 0) {
    $sql_th .= " WHERE sp.danhmuc_id = $dm_id";
}
$list_thuonghieu = $conn->query($sql_th)->fetchAll(PDO::FETCH_ASSOC);


// 4. XÂY DỰNG CÂU TRUY VẤN LỌC
$sql = "SELECT * FROM sanpham WHERE 1=1";
$params = [];

// -> Lọc theo Danh mục
if ($dm_id > 0) {
    $sql .= " AND danhmuc_id = :dm_id";
    $params[':dm_id'] = $dm_id;
}

// -> Lọc theo Thương hiệu
if (!empty($th_ids)) {
    $placeholders = implode(',', array_fill(0, count($th_ids), '?'));
    $sql .= " AND thuonghieu_id IN ($placeholders)";
    $clean_ids = array_map('intval', $th_ids);
    $ids_string = implode(',', $clean_ids);
    $sql = str_replace("AND thuonghieu_id IN ($placeholders)", "AND thuonghieu_id IN ($ids_string)", $sql);
}

// -> Lọc theo Giá
if (!empty($muc_gia)) {
    if ($muc_gia == 'duoi-500k') $sql .= " AND gia_ban < 500000";
    elseif ($muc_gia == '500k-1tr') $sql .= " AND gia_ban BETWEEN 500000 AND 1000000";
    elseif ($muc_gia == '1-3tr') $sql .= " AND gia_ban BETWEEN 1000000 AND 3000000";
    elseif ($muc_gia == 'tren-3tr') $sql .= " AND gia_ban > 3000000";
}

$sql .= " ORDER BY id DESC";

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
    <link rel="stylesheet" href="assets/css/style.css"> <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="container-page">
        
        <aside class="sidebar">
            <form action="sanpham.php" method="GET" id="filterForm">
                
                <?php if($dm_id > 0): ?>
                    <input type="hidden" name="dm_id" value="<?php echo $dm_id; ?>">
                <?php endif; ?>

                <div class="filter-group">
                    <span class="filter-title">Mức giá</span>
                    <ul class="filter-list">
                        <li><label><input type="radio" name="gia" value="" onchange="this.form.submit()" <?php if($muc_gia == '') echo 'checked'; ?>> Tất cả</label></li>
                        <li><label><input type="radio" name="gia" value="duoi-500k" onchange="this.form.submit()" <?php if($muc_gia == 'duoi-500k') echo 'checked'; ?>> Dưới 500k</label></li>
                        <li><label><input type="radio" name="gia" value="500k-1tr" onchange="this.form.submit()" <?php if($muc_gia == '500k-1tr') echo 'checked'; ?>> 500k - 1 triệu</label></li>
                        <li><label><input type="radio" name="gia" value="1-3tr" onchange="this.form.submit()" <?php if($muc_gia == '1-3tr') echo 'checked'; ?>> 1 - 3 triệu</label></li>
                        <li><label><input type="radio" name="gia" value="tren-3tr" onchange="this.form.submit()" <?php if($muc_gia == 'tren-3tr') echo 'checked'; ?>> Trên 3 triệu</label></li>
                    </ul>
                </div>

                <div class="filter-group">
                    <span class="filter-title">Thương hiệu</span>
                    <ul class="filter-list">
                        <?php foreach($list_thuonghieu as $th): ?>
                            <li>
                                <label>
                                    <input type="checkbox" name="th[]" value="<?php echo $th['id']; ?>" 
                                    onchange="this.form.submit()"
                                    <?php if(in_array($th['id'], $th_ids)) echo 'checked'; ?>> 
                                    <?php echo $th['ten_thuonghieu']; ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

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
            $img = !empty($row['hinh_anh']) ? 'admin/anh_sanpham/'.$row['hinh_anh'] : 'assets/images/no-image.png';
            
            // --- LOGIC TÍNH GIẢM GIÁ ---
            $gia_ban = $row['gia_ban'];
            $gia_km = $row['gia_khuyenmai'];
            $co_km = ($gia_km > 0 && $gia_km < $gia_ban); // Kiểm tra có giảm giá không
            
            $phantram_giam = 0;
            if($co_km){
                $phantram_giam = round((($gia_ban - $gia_km) / $gia_ban) * 100);
            }
        ?>
            <div class="product-item">
                
                <?php if($co_km): ?>
                    <div class="discount-badge">-<?php echo $phantram_giam; ?>%</div>
                <?php endif; ?>

                <a href="chitiet.php?id=<?php echo $row['id']; ?>">
                    <img src="<?php echo $img; ?>" class="product-img" alt="<?php echo $row['ten_sanpham']; ?>">
                </a>
                
                <div class="product-name">
                    <a href="chitiet.php?id=<?php echo $row['id']; ?>"><?php echo $row['ten_sanpham']; ?></a>
                </div>

                <div class="price-container">
                    <?php if($co_km): ?>
                        <span class="price-current"><?php echo number_format($gia_km, 0, ',', '.'); ?>đ</span>
                        <span class="price-old"><?php echo number_format($gia_ban, 0, ',', '.'); ?>đ</span>
                    <?php else: ?>
                        <span class="price-current"><?php echo number_format($gia_ban, 0, ',', '.'); ?>đ</span>
                        <span class="price-old" style="visibility: hidden;">0đ</span>
                    <?php endif; ?>
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