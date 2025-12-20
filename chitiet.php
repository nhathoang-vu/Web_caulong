<?php
session_start();
require_once 'connect.php'; 

if (!isset($_GET['id']) || empty($_GET['id'])) { die("Không tìm thấy sản phẩm!"); }
$product_id = $_GET['id'];

try {
    // Lấy thông tin sản phẩm
    $sql = "SELECT * FROM sanpham WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) { die("Sản phẩm không tồn tại!"); }

    // Lấy biến thể (Size/Màu) có số lượng > 0
    $sql_variant = "SELECT * FROM bienthe_sanpham WHERE sanpham_id = :id AND so_luong_ton > 0";
    $stmt_var = $conn->prepare($sql_variant);
    $stmt_var->bindParam(':id', $product_id);
    $stmt_var->execute();
    $variants = $stmt_var->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { die("Lỗi: " . $e->getMessage()); }

$img_src = !empty($product['hinh_anh']) ? "assets/images/" . $product['hinh_anh'] : "assets/images/no-image.png";
$gia_hien_tai = ($product['gia_khuyenmai'] > 0) ? $product['gia_khuyenmai'] : $product['gia_ban'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['ten_sanpham']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css"> 
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    
    <style>
        .container-detail { width: 1100px; margin: 30px auto; display: flex; gap: 40px; }
        .detail-left { width: 45%; }
        .main-image { width: 100%; border: 1px solid #eee; padding: 10px; border-radius: 8px; }
        .detail-right { width: 55%; }
        .product-title { font-size: 26px; font-weight: bold; margin-bottom: 15px; }
        .product-price { font-size: 24px; color: #d0021b; font-weight: bold; margin-bottom: 20px; }
        .product-price del { color: #999; font-size: 16px; margin-left: 10px; font-weight: normal; }
        
        .variant-section { margin-bottom: 20px; }
        .variant-label { font-weight: bold; margin-bottom: 8px; display: block; }
        .variant-options { display: flex; gap: 10px; flex-wrap: wrap; }
        .variant-btn { padding: 8px 15px; border: 1px solid #ccc; cursor: pointer; background: #fff; border-radius: 4px; user-select: none; }
        .variant-btn.active { border-color: #ff6600; background-color: #fff5f0; color: #ff6600; font-weight: bold; }
        
        .quantity-input { width: 60px; padding: 8px; text-align: center; border: 1px solid #ccc; border-radius: 4px; }
        .btn-buy-now { background: #ff6600; color: white; padding: 12px 40px; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        .btn-buy-now:hover { background: #e65c00; }
        
        .tech-specs { margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px; }
        .spec-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .spec-table td { padding: 10px; border-bottom: 1px solid #f0f0f0; }
        .spec-table td:first-child { font-weight: bold; width: 150px; background: #f9f9f9; }
        input[type="radio"] { display: none; }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <div class="container-detail">
            <div class="detail-left">
                <img src="<?php echo $img_src; ?>" alt="<?php echo $product['ten_sanpham']; ?>" class="main-image">
            </div>

            <div class="detail-right">
                <h1 class="product-title"><?php echo $product['ten_sanpham']; ?></h1>
                
                <div class="product-price">
                    <?php echo number_format($gia_hien_tai, 0, ',', '.'); ?>đ
                    <?php if ($product['gia_khuyenmai'] > 0 && $product['gia_khuyenmai'] < $product['gia_ban']): ?>
                        <del><?php echo number_format($product['gia_ban'], 0, ',', '.'); ?>đ</del>
                    <?php endif; ?>
                </div>

                <form action="giohang_xuly.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <?php if (count($variants) > 0): ?>
                        <div class="variant-section">
                            <span class="variant-label">Chọn phân loại:</span>
                            <div class="variant-options">
                                <?php foreach ($variants as $index => $var): ?>
                                    <label class="variant-btn <?php echo ($index === 0) ? 'active' : ''; ?>" onclick="selectVariant(this)">
                                        <input type="radio" name="variant_id" value="<?php echo $var['id']; ?>" <?php echo ($index === 0) ? 'checked' : ''; ?>>
                                        <?php echo $var['kich_thuoc']; if(!empty($var['mau_sac'])) echo " - " . $var['mau_sac']; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <span class="variant-label">Số lượng:</span>
                            <input type="number" name="quantity" class="quantity-input" value="1" min="1" max="10">
                        </div>

                        <button type="submit" class="btn-buy-now"><i class="fa-solid fa-cart-plus"></i> THÊM VÀO GIỎ</button>
                    <?php else: ?>
                        <p style="color: red; font-weight: bold; font-size: 18px;">Hiện đang hết hàng!</p>
                    <?php endif; ?>
                </form>

                <?php 
                $thong_so = json_decode($product['thong_so_ky_thuat'], true);
                if ($thong_so) {
                ?>
                    <div class="tech-specs">
                        <h3>Thông số kỹ thuật</h3>
                        <table class="spec-table">
                            <?php foreach ($thong_so as $key => $value): ?>
                            <tr><td><?php echo ucfirst(str_replace('_', ' ', $key)); ?></td><td><?php echo $value; ?></td></tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>
        
        <div style="width: 1100px; margin: 0 auto 50px; padding: 0 15px;">
            <h3>Mô tả chi tiết</h3>
            <hr>
            <div style="margin-top: 15px; line-height: 1.6;">
                <?php echo $product['mo_ta']; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function selectVariant(label) {
            document.querySelectorAll('.variant-btn').forEach(btn => btn.classList.remove('active'));
            label.classList.add('active');
        }
    </script>
</body>
</html>