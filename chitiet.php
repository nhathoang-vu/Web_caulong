<?php
session_start();
require_once 'connect.php'; 

// 1. Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) { 
    die("Không tìm thấy sản phẩm!"); 
}
$product_id = intval($_GET['id']);

// 2. Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT * FROM sanpham WHERE id = :id");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) { die("Sản phẩm không tồn tại!"); }

// 3. Lấy các biến thể (Màu sắc, Kích thước)
$stmt_var = $conn->prepare("SELECT * FROM bienthe_sanpham WHERE sanpham_id = :id AND so_luong_ton > 0");
$stmt_var->execute(['id' => $product_id]);
$variants = $stmt_var->fetchAll(PDO::FETCH_ASSOC);

// Tách mảng Màu và Size (loại bỏ trùng lặp)
$colors = array_unique(array_column($variants, 'mau_sac'));
$sizes = array_unique(array_column($variants, 'kich_thuoc'));

$img_src = !empty($product['hinh_anh']) ? "assets/images/" . $product['hinh_anh'] : "assets/images/no-image.png";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['ten_sanpham']; ?> - HBG Shop</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/chitiet.css"> 
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main class="container">
        <form id="addToCartForm" action="includes/xulygiohang.php" method="POST">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <div class="product-detail-container">
                <div class="product-image-col">
                    <img src="<?php echo $img_src; ?>" alt="<?php echo $product['ten_sanpham']; ?>">
                </div>

                <div class="product-info-col">
                    <h1 class="product-title"><?php echo $product['ten_sanpham']; ?></h1>
                    
                    <div class="product-price">
                        <?php if($product['gia_khuyenmai'] > 0): ?>
                            <span style="text-decoration: line-through; color: #999; font-size: 18px; margin-right: 10px;">
                                <?php echo number_format($product['gia_ban'], 0, ',', '.'); ?>đ
                            </span>
                            <span><?php echo number_format($product['gia_khuyenmai'], 0, ',', '.'); ?>đ</span>
                        <?php else: ?>
                            <span><?php echo number_format($product['gia_ban'], 0, ',', '.'); ?>đ</span>
                        <?php endif; ?>
                    </div>

                    <?php if(!empty($colors) && !empty($colors[0])): ?>
                    <div class="variant-group">
                        <label class="variant-label">Màu sắc:</label>
                        <div class="variant-options">
                            <?php foreach($colors as $k => $c): ?>
                                <div class="variant-option">
                                    <input type="radio" name="color" id="color_<?php echo $k; ?>" value="<?php echo $c; ?>" <?php echo ($k==0)?'checked':''; ?>>
                                    <label for="color_<?php echo $k; ?>"><?php echo $c; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty($sizes) && !empty($sizes[0])): ?>
                    <div class="variant-group">
                        <label class="variant-label">Kích thước:</label>
                        <div class="variant-options">
                            <?php foreach($sizes as $k => $s): ?>
                                <div class="variant-option">
                                    <input type="radio" name="size" id="size_<?php echo $k; ?>" value="<?php echo $s; ?>" <?php echo ($k==0)?'checked':''; ?>>
                                    <label for="size_<?php echo $k; ?>"><?php echo $s; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="variant-group" style="display: flex; align-items: center; margin-top: 20px;">
                        <label class="variant-label" style="margin-bottom: 0; margin-right: 15px;">Số lượng:</label>
                        <div style="display: flex;">
                            <input type="number" name="quantity" class="qty-input" value="1" min="1">
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn-add-cart" onclick="showCartPopup()">
                            <i class="fa-solid fa-cart-plus"></i> Thêm Vào Giỏ Hàng
                        </button>
                        
                        <button type="submit" name="buy_now" class="btn-buy-now">
                            MUA NGAY
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div style="background: #fff; padding: 20px; margin-top: 20px; border-radius: 8px;">
            <h3 style="border-bottom: 2px solid #ff6600; display: inline-block; padding-bottom: 5px; margin-bottom: 20px;">
                Mô tả sản phẩm
            </h3>
            <div style="line-height: 1.6;">
                <?php echo $product['mo_ta']; ?>
            </div>
        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

    <div id="cartModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div class="modal-text">Sản phẩm đã được thêm vào Giỏ hàng</div>
            <div class="modal-actions">
                <a href="#" class="btn-continue" onclick="closeCartPopup()">Tiếp tục mua sắm</a>
                <a href="giohang.php" class="btn-view-cart">Xem giỏ hàng</a>
            </div>
        </div>
    </div>

    <script>
        function showCartPopup() {
            const form = document.getElementById('addToCartForm');
            const formData = new FormData(form);

            fetch('includes/xulygiohang.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('cartModal').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function closeCartPopup() {
            document.getElementById('cartModal').style.display = 'none';
            event.preventDefault(); 
        }

        window.onclick = function(event) {
            var modal = document.getElementById('cartModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>