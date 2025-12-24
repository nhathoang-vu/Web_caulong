<?php
session_start();
require_once 'connect.php';

// Kiểm tra giỏ hàng có trống không
$cart_empty = true;
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $cart_empty = false;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - HBG Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    
    <link rel="stylesheet" href="assets/css/giohang.css"> 
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main class="cart-page">
        <div class="cart-container-fluid">
            
            <div class="cart-page-title">
                <i class="fa-solid fa-cart-shopping"></i> Giỏ hàng của bạn
            </div>

            <?php if ($cart_empty): ?>
                <div class="empty-box">
                    <img src="assets/images/empty-cart.png" alt="Giỏ hàng trống" style="width: 250px; opacity: 0.6; margin-bottom: 20px;">
                    <p style="font-size: 18px; color: #666; margin-bottom: 20px;">Chưa có sản phẩm nào trong giỏ hàng</p>
                    <a href="index.php" style="background: #ee4d2d; color: #fff; padding: 12px 40px; text-decoration: none; border-radius: 4px; font-weight: bold;">MUA NGAY</a>
                </div>
            <?php else: ?>

                <form id="cartForm" action="thanhtoan.php" method="POST" onsubmit="return validateCheckout()">
                    
                    <div class="cart-header-row">
                        <div class="col-check">
                            <input type="checkbox" id="checkAllTop" onchange="toggleAll(this)">
                        </div>
                        <div class="col-product">Sản phẩm</div>
                        <div class="col-price">Đơn giá</div>
                        <div class="col-qty">Số lượng</div>
                        <div class="col-total">Thành tiền</div>
                        <div class="col-action">Xóa</div>
                    </div>

                    <?php foreach ($_SESSION['cart'] as $key => $item): 
                        $total_line = $item['price'] * $item['qty'];
                        
                        // --- SỬA LỖI ẢNH TẠI ĐÂY ---
                        // 1. Lấy tên file ảnh từ session (ưu tiên key 'img' hoặc 'image')
                        $img_name = isset($item['img']) ? $item['img'] : (isset($item['image']) ? $item['image'] : '');
                        
                        // 2. Tạo đường dẫn file thực tế
                        $target_file = 'admin/anh_sanpham/' . $img_name;
                        
                        // 3. Kiểm tra file có tồn tại không
                        if (!empty($img_name) && file_exists($target_file)) {
                            $display_img = $target_file;
                        } else {
                            // Nếu không có, dùng ảnh placeholder online để chắc chắn hiển thị
                            $display_img = 'https://via.placeholder.com/80x80.png?text=No+Image';
                        }
                    ?>
                        <div class="cart-item-row" id="item-<?php echo $key; ?>">
                            
                            <div class="col-check">
                                <input type="checkbox" name="selected_items[]" value="<?php echo $key; ?>" 
                                       class="item-checkbox" 
                                       data-price="<?php echo $item['price']; ?>"
                                       onchange="updateTotal()">
                            </div>
                            
                            <div class="col-product">
                                <div class="item-info">
                                    <img src="<?php echo $display_img; ?>" class="item-img" alt="SP">
                                    
                                    <div class="item-detail">
                                        <a href="chitiet.php?id=<?php echo $item['id']; ?>" class="item-name">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                        <div class="item-variant">
                                            <?php if(!empty($item['color'])) echo "Màu: " . $item['color']; ?>
                                            <?php if(!empty($item['size'])) echo " - Size: " . $item['size']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-price">
                                <?php echo number_format($item['price'], 0, ',', '.'); ?>đ
                            </div>

                            <div class="col-qty">
                                <div class="qty-box">
                                    <button type="button" class="qty-btn" onclick="updateQty('<?php echo $key; ?>', -1)">-</button>
                                    <input type="text" class="qty-input" id="qty-<?php echo $key; ?>" value="<?php echo $item['qty']; ?>" readonly>
                                    <button type="button" class="qty-btn" onclick="updateQty('<?php echo $key; ?>', 1)">+</button>
                                </div>
                            </div>

                            <div class="col-total" id="total-<?php echo $key; ?>">
                                <?php echo number_format($total_line, 0, ',', '.'); ?>đ
                            </div>

                            <div class="col-action">
                                <a href="includes/xulygiohang.php?action=delete&key=<?php echo $key; ?>" 
                                   class="btn-del" 
                                   title="Xóa sản phẩm này"
                                   onclick="return confirm('Bạn muốn xóa sản phẩm này?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="cart-footer-sticky">
                        <div class="footer-left">
                            <div style="display:flex; align-items:center;">
                                <input type="checkbox" id="checkAllBottom" onchange="toggleAll(this)" style="margin-right: 10px;">
                                <label for="checkAllBottom" style="cursor: pointer;">Chọn tất cả (<span id="count-selected">0</span>)</label>
                            </div>
                            <a href="includes/xulygiohang.php?action=clear" style="color: #333; text-decoration: none;" onclick="return confirm('Xóa hết?')">Xóa tất cả</a>
                        </div>

                        <div class="footer-right">
                            <div>
                                <span class="text-total-label">Tổng thanh toán: </span>
                                <span class="text-total-value" id="grand-total">0đ</span>
                            </div>
                            <button type="submit" class="btn-checkout-orange">Mua Hàng</button>
                        </div>
                    </div>

                </form>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function formatMoney(amount) {
            return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "đ";
        }

        function toggleAll(source) {
            let topCheck = document.getElementById('checkAllTop');
            let botCheck = document.getElementById('checkAllBottom');
            
            if(topCheck) topCheck.checked = source.checked;
            if(botCheck) botCheck.checked = source.checked;

            let checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(cb => { cb.checked = source.checked; });
            updateTotal();
        }

        function updateTotal() {
            let total = 0;
            let count = 0;
            let checkboxes = document.querySelectorAll('.item-checkbox:checked');

            checkboxes.forEach(cb => {
                let key = cb.value;
                let price = parseInt(cb.getAttribute('data-price'));
                let inputQty = document.getElementById('qty-' + key);
                if(inputQty){
                    let qty = parseInt(inputQty.value);
                    total += price * qty;
                    count++;
                }
            });

            document.getElementById('grand-total').innerText = formatMoney(total);
            document.getElementById('count-selected').innerText = count;
        }

        function updateQty(key, change) {
            let input = document.getElementById('qty-' + key);
            let currentQty = parseInt(input.value);
            let newQty = currentQty + change;

            if (newQty < 1) return;

            // Cập nhật UI ngay lập tức
            input.value = newQty;
            let checkbox = document.querySelector(`input[value="${key}"]`);
            let price = parseInt(checkbox.getAttribute('data-price'));
            let lineTotal = price * newQty;
            document.getElementById('total-' + key).innerText = formatMoney(lineTotal);
            
            // Nếu sản phẩm đó đang được tick chọn thì cập nhật cả tổng tiền
            if(checkbox.checked) {
                updateTotal();
            }

            // Gửi Ajax ngầm
            let formData = new FormData();
            formData.append('action', 'update_qty');
            formData.append('key', key);
            formData.append('qty', newQty);

            fetch('includes/xulygiohang.php', {
                method: 'POST',
                body: formData
            });
        }

        function validateCheckout() {
            let checkboxes = document.querySelectorAll('.item-checkbox:checked');
            if (checkboxes.length === 0) {
                alert("Bạn chưa chọn sản phẩm nào để thanh toán!");
                return false; 
            }
            return true; 
        }
    </script>
</body>
</html>