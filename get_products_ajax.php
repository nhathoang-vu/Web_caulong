<?php
require_once 'connect.php'; // Đảm bảo đường dẫn đúng tới file connect

// Lấy ID thương hiệu từ yêu cầu Ajax (Mặc định là 1 - Yonex nếu không có)
$brand_id = isset($_GET['brand_id']) ? intval($_GET['brand_id']) : 1;

try {
    // Truy vấn: Lấy 6 sản phẩm thuộc danh mục Vợt (id=1) và Thương hiệu được chọn
    // Sắp xếp theo giá giảm dần (đắt nhất trước)
    $sql = "SELECT * FROM sanpham 
            WHERE danhmuc_id = 1 AND thuonghieu_id = :brand_id 
            ORDER BY gia_ban DESC 
            LIMIT 6";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':brand_id', $brand_id, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($products) > 0) {
        foreach ($products as $row) {
            // Tính phần trăm giảm giá nếu có
            $phantram = 0;
            if ($row['gia_ban'] > 0 && $row['gia_khuyenmai'] > 0 && $row['gia_khuyenmai'] < $row['gia_ban']) {
                $phantram = round((($row['gia_ban'] - $row['gia_khuyenmai']) / $row['gia_ban']) * 100);
            }

            $img = !empty($row['hinh_anh']) ? 'assets/images/' . $row['hinh_anh'] : 'assets/images/no-image.png';
            
            // Render HTML Card (Dùng class .fs-card nhưng bỏ bớt các chi tiết thừa)
            ?>
            <div class="fs-card" onclick="window.location.href='chitiet.php?id=<?php echo $row['id']; ?>'">
                <?php if ($phantram > 0): ?>
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
                    <button style="background:#fd4e00; color:#fff; border:none; width:100%; padding:5px; border-radius:4px; cursor:pointer; font-weight:bold;">
                        Xem chi tiết
                    </button>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<p style="grid-column: 1/-1; text-align: center; padding: 20px;">Chưa có sản phẩm cho thương hiệu này.</p>';
    }

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>