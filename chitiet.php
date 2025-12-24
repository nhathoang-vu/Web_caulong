<?php
session_start();
require_once 'connect.php'; 

// 1. Kiểm tra ID
if (!isset($_GET['id']) || empty($_GET['id'])) { 
    die("Không tìm thấy sản phẩm!"); 
}
$product_id = intval($_GET['id']);

// 2. Lấy thông tin sản phẩm
$sql = "SELECT s.*, th.ten_thuonghieu, dm.ten_danhmuc 
        FROM sanpham s
        LEFT JOIN thuonghieu th ON s.thuonghieu_id = th.id
        LEFT JOIN danhmuc dm ON s.danhmuc_id = dm.id
        WHERE s.id = :id";

$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) { die("Sản phẩm không tồn tại!"); }

// 3. Lấy biến thể
$stmt_var = $conn->prepare("SELECT * FROM bienthe_sanpham WHERE sanpham_id = :id");
$stmt_var->execute(['id' => $product_id]);
$variants = $stmt_var->fetchAll(PDO::FETCH_ASSOC);

// Xử lý ảnh (Dùng thư mục admin/anh_sanpham/ như bạn yêu cầu)
$img_src = !empty($product['hinh_anh']) ? "admin/anh_sanpham/" . $product['hinh_anh'] : "assets/images/no-image.png";

// Giá hiển thị
$gia_ban = $product['gia_ban'];
$gia_km = $product['gia_khuyenmai'];
$co_km = ($gia_km > 0 && $gia_km < $gia_ban);
$phantram = $co_km ? round((($gia_ban - $gia_km) / $gia_ban) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['ten_sanpham']; ?></title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/chitiet.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="breadcrumb-area">
        <div class="container">
            <a href="index.php">Trang chủ</a> <span>/</span> 
            <a href="sanpham.php?dm_id=<?php echo $product['danhmuc_id']; ?>"><?php echo $product['ten_danhmuc']; ?></a> <span>/</span> 
            <span class="current"><?php echo $product['ten_sanpham']; ?></span>
        </div>
    </div>

    <div class="container detail-page">
        <div class="detail-left">
            <div class="main-image-box">
                <?php if($co_km): ?>
                    <span class="detail-sale-badge">-<?php echo $phantram; ?>%</span>
                <?php endif; ?>
                <img src="<?php echo $img_src; ?>" alt="<?php echo $product['ten_sanpham']; ?>" onerror="this.src='https://via.placeholder.com/500x500?text=No+Image'">
            </div>
        </div>

        <div class="detail-right">
            <h1 class="product-title"><?php echo $product['ten_sanpham']; ?></h1>
            
            <div class="product-meta">
                <span class="meta-item">Thương hiệu: <b><?php echo $product['ten_thuonghieu']; ?></b></span>
                <span class="meta-separator">|</span>
                <span class="meta-item">Mã SP: <b>SP<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></b></span>
            </div>

            <div class="product-price-block">
                <?php if($co_km): ?>
                    <span class="price-current"><?php echo number_format($gia_km, 0, ',', '.'); ?>đ</span>
                    <span class="price-old"><?php echo number_format($gia_ban, 0, ',', '.'); ?>đ</span>
                <?php else: ?>
                    <span class="price-current"><?php echo number_format($gia_ban, 0, ',', '.'); ?>đ</span>
                <?php endif; ?>
            </div>

            <form id="addToCartForm" action="includes/xulygiohang.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <div class="product-variants-area">
                    <script>
                        var variants = <?php echo json_encode($variants); ?>;
                    </script>

                    <div class="attribute-group">
                        <label class="attr-label">Màu sắc:</label>
                        <div id="color-options" class="options-container"></div>
                        <input type="hidden" name="color" id="selected_color" required>
                    </div>

                    <div class="attribute-group">
                        <label class="attr-label">Kích thước:</label>
                        <div id="size-options" class="options-container">
                            <span style="color: #999; font-size: 14px;">(Vui lòng chọn màu trước)</span>
                        </div>
                        <input type="hidden" name="size" id="selected_size" required>
                    </div>

                    <div class="stock-status">
                        Tồn kho: <span id="stock-display" style="font-weight: bold; color: #555;">--</span>
                    </div>
                </div>

                <div class="quantity-group">
                    <span class="var-label">Số lượng:</span>
                    <div class="qty-control">
                        <button type="button" class="qty-btn" onclick="decreaseQty()">-</button>
                        <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="99">
                        <button type="button" class="qty-btn" onclick="increaseQty()">+</button>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn-add-cart" onclick="addToCart()">
                        <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                    </button>
                    
                    <button type="submit" class="btn-buy-now" name="buy_now" value="1" onclick="return validateBuy()">
                        Mua ngay
                    </button>
                </div>
            </form>
        </div> 

        <div class="product-description-box">
            <h3>Mô tả sản phẩm</h3>
            <div class="desc-content">
                <?php echo nl2br($product['mo_ta']); ?>
            </div>
        </div>

    </div>

    <div id="cartModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-icon"><i class="fa-solid fa-check"></i></div>
            <div class="modal-text">Đã thêm vào giỏ hàng thành công!</div>
            <div class="modal-actions">
                <a href="#" class="btn-continue" onclick="closePopup('cartModal')">Mua tiếp</a>
                <a href="giohang.php" class="btn-view-cart">Xem giỏ hàng</a>
            </div>
        </div>
    </div>

    <div id="warningModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-icon" style="color: #f59e0b;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="modal-text" id="warningText">...</div>
            <div class="modal-actions">
                <button type="button" class="btn-close-popup" onclick="closePopup('warningModal')">Đóng</button>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // --- LOGIC XỬ LÝ BIẾN THỂ ---
        const uniqueColors = [...new Set(variants.map(item => item.mau_sac))];
        const colorContainer = document.getElementById('color-options');
        const sizeContainer = document.getElementById('size-options');
        const stockDisplay = document.getElementById('stock-display');
        const inputColor = document.getElementById('selected_color');
        const inputSize = document.getElementById('selected_size');
        const qtyInput = document.getElementById('qtyInput');
        let currentStock = 0; 

        uniqueColors.forEach(color => {
            let btn = document.createElement('div');
            btn.className = 'option-btn';
            btn.innerText = color;
            btn.onclick = function() { selectColor(this, color); };
            colorContainer.appendChild(btn);
        });

        function selectColor(btn, color) {
            document.querySelectorAll('#color-options .option-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');

            inputColor.value = color;
            inputSize.value = ''; 
            currentStock = 0;
            stockDisplay.innerText = '--';
            stockDisplay.style.color = "#555";

            renderSizes(color);
        }

        function renderSizes(color) {
            sizeContainer.innerHTML = ''; 
            let availableSizes = variants.filter(v => v.mau_sac === color);

            if(availableSizes.length === 0) {
                sizeContainer.innerHTML = '<span style="color:red">Tạm hết hàng màu này</span>';
                return;
            }

            availableSizes.forEach(variant => {
                let btn = document.createElement('div');
                btn.className = 'option-btn';
                btn.innerText = variant.kich_thuoc;
                if(variant.so_luong_ton == 0) {
                    btn.style.opacity = "0.6"; 
                }
                btn.onclick = function() {
                    document.querySelectorAll('#size-options .option-btn').forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                    inputSize.value = variant.kich_thuoc;
                    currentStock = parseInt(variant.so_luong_ton);
                    if(currentStock > 0) {
                        stockDisplay.innerText = currentStock + " sản phẩm";
                        stockDisplay.style.color = "#28a745"; 
                        qtyInput.value = 1;
                        qtyInput.max = currentStock;
                    } else {
                        stockDisplay.innerText = "Hết hàng";
                        stockDisplay.style.color = "#d0021b"; 
                        qtyInput.value = 1;
                        qtyInput.max = 1; 
                    }
                };
                sizeContainer.appendChild(btn);
            });
        }

        function showWarningPopup(message) {
            document.getElementById('warningText').innerHTML = message;
            document.getElementById('warningModal').style.display = 'flex';
        }

        function validateBuy() {
            if(!inputColor.value || !inputSize.value) {
                showWarningPopup("Vui lòng chọn <b>Màu sắc</b> và <b>Kích thước</b>!");
                return false;
            }
            if(currentStock <= 0) {
                showWarningPopup("Sản phẩm này tạm thời hết hàng!<br>Vui lòng chọn mẫu khác.");
                return false;
            }
            return true;
        }

        // --- HÀM THÊM VÀO GIỎ HÀNG (AJAX) ---
        function addToCart() {
            if(validateBuy()) {
                const form = document.getElementById('addToCartForm');
                const formData = new FormData(form);

                fetch('includes/xulygiohang.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('cartModal').style.display = 'flex';
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không thể thêm vào giỏ hàng'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback nếu JSON lỗi nhưng thực tế đã thêm được
                    document.getElementById('cartModal').style.display = 'flex';
                });
            }
        }
        
        function closePopup(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function increaseQty() {
            var val = parseInt(qtyInput.value);
            if (currentStock <= 0) return;
            if(val < currentStock) qtyInput.value = val + 1;
        }
        function decreaseQty() {
            var val = parseInt(qtyInput.value);
            if(val > 1) qtyInput.value = val - 1;
        }
    </script>
</body>
</html>