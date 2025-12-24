<?php
session_start();
require_once 'connect.php'; 

// --- XỬ LÝ LOGIC LẤY DỮ LIỆU ---
$dm_id = isset($_GET['dm_id']) ? $_GET['dm_id'] : 0;
$th_ids = isset($_GET['th']) ? $_GET['th'] : [];
$muc_gia = isset($_GET['gia']) ? $_GET['gia'] : '';

// Lấy tên danh mục
$ten_danhmuc = "Tất cả sản phẩm";
if ($dm_id > 0) {
    $stmt = $conn->prepare("SELECT ten_danhmuc FROM danhmuc WHERE id = ?");
    $stmt->execute([$dm_id]);
    $res = $stmt->fetch();
    if($res) $ten_danhmuc = $res['ten_danhmuc'];
}

// Lấy thương hiệu để lọc
$sql_th = "SELECT DISTINCT th.id, th.ten_thuonghieu FROM thuonghieu th JOIN sanpham sp ON th.id = sp.thuonghieu_id";
if($dm_id > 0) $sql_th .= " WHERE sp.danhmuc_id = $dm_id";
$list_thuonghieu = $conn->query($sql_th)->fetchAll(PDO::FETCH_ASSOC);

// Query sản phẩm chính
$sql = "SELECT * FROM sanpham WHERE 1=1";
$params = [];

if ($dm_id > 0) {
    $sql .= " AND danhmuc_id = :dm_id";
    $params[':dm_id'] = $dm_id;
}
if (!empty($th_ids)) {
    // Xử lý mảng ID thương hiệu an toàn
    $in_str = implode(',', array_map('intval', $th_ids));
    $sql .= " AND thuonghieu_id IN ($in_str)";
}
if (!empty($muc_gia)) {
    if ($muc_gia == 'duoi-500k') $sql .= " AND gia_ban < 500000";
    elseif ($muc_gia == '500k-1tr') $sql .= " AND gia_ban BETWEEN 500000 AND 1000000";
    elseif ($muc_gia == '1-3tr') $sql .= " AND gia_ban BETWEEN 1000000 AND 3000000";
    elseif ($muc_gia == 'tren-3tr') $sql .= " AND gia_ban > 3000000";
}
$sql .= " ORDER BY id DESC"; // Sản phẩm mới nhất lên đầu

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($products);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $ten_danhmuc; ?></title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/sanpham.css"> 
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="page-container">
        <aside class="sidebar">
            <form action="sanpham.php" method="GET" id="autoFilterForm">
                
                <?php if($dm_id > 0): ?>
                    <input type="hidden" name="dm_id" value="<?php echo $dm_id; ?>">
                <?php endif; ?>

                <div class="filter-group">
                    <span class="filter-title">Khoảng giá</span>
                    <ul class="filter-list">
                        <li><label><input type="radio" name="gia" value="" onchange="this.form.submit()" <?php echo ($muc_gia == '') ? 'checked' : ''; ?>> Tất cả</label></li>
                        <li><label><input type="radio" name="gia" value="duoi-500k" onchange="this.form.submit()" <?php echo ($muc_gia == 'duoi-500k') ? 'checked' : ''; ?>> Dưới 500k</label></li>
                        <li><label><input type="radio" name="gia" value="500k-1tr" onchange="this.form.submit()" <?php echo ($muc_gia == '500k-1tr') ? 'checked' : ''; ?>> 500k - 1 triệu</label></li>
                        <li><label><input type="radio" name="gia" value="1-3tr" onchange="this.form.submit()" <?php echo ($muc_gia == '1-3tr') ? 'checked' : ''; ?>> 1 - 3 triệu</label></li>
                        <li><label><input type="radio" name="gia" value="tren-3tr" onchange="this.form.submit()" <?php echo ($muc_gia == 'tren-3tr') ? 'checked' : ''; ?>> Trên 3 triệu</label></li>
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
                                           <?php echo in_array($th['id'], $th_ids) ? 'checked' : ''; ?>>
                                    <?php echo $th['ten_thuonghieu']; ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </form>
        </aside>

        <main class="main-content">
            <div class="cat-header">
                <h2><?php echo $ten_danhmuc; ?></h2>
                <p>Tìm thấy <b><?php echo $count; ?></b> sản phẩm</p>
            </div>

            <div class="sp-grid">
                <?php if ($count > 0): ?>
                    <?php foreach ($products as $row): 
                        // Logic đường dẫn ảnh
                        $img_path = !empty($row['hinh_anh']) ? 'admin/anh_sanpham/'.$row['hinh_anh'] : 'assets/images/no-image.png';
                        
                        // Logic tính giá KM
                        $gia_goc = $row['gia_ban'];
                        $gia_km = $row['gia_khuyenmai'];
                        $co_km = ($gia_km > 0 && $gia_km < $gia_goc);
                        
                        $phantram = 0;
                        if($co_km) {
                            $phantram = round((($gia_goc - $gia_km) / $gia_goc) * 100);
                        }
                    ?>
                        <div class="sp-item">
                            <?php if($co_km): ?>
                                <div class="sale-badge">-<?php echo $phantram; ?>%</div>
                            <?php endif; ?>

                            <div class="sp-img-box">
                                <a href="chitiet.php?id=<?php echo $row['id']; ?>">
                                    <img src="<?php echo $img_path; ?>" alt="<?php echo $row['ten_sanpham']; ?>">
                                </a>
                            </div>

                            <div class="sp-name">
                                <a href="chitiet.php?id=<?php echo $row['id']; ?>"><?php echo $row['ten_sanpham']; ?></a>
                            </div>

                            <div class="sp-price-box">
                                <?php if($co_km): ?>
                                    <span class="price-now"><?php echo number_format($gia_km, 0, ',', '.'); ?>đ</span>
                                    <span class="price-old"><?php echo number_format($gia_goc, 0, ',', '.'); ?>đ</span>
                                <?php else: ?>
                                    <span class="price-now"><?php echo number_format($gia_goc, 0, ',', '.'); ?>đ</span>
                                    <span class="price-old" style="visibility:hidden">0đ</span> 
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert-empty">Không có sản phẩm nào phù hợp.</div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>
</html>