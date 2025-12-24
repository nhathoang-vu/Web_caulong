<?php
require_once '../connect.php';
include 'includes/header.php';

// --- XỬ LÝ: ADMIN BẤM ĐỒNG Ý (CỘNG KHO) ---
if (isset($_GET['action']) && $_GET['action'] == 'confirm' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $conn->beginTransaction(); 

        // 1. Kiểm tra xem đơn này đã được hoàn kho chưa?
        // (Dựa vào dấu hiệu trong ly_do_huy hoặc kiểm tra logic của bạn)
        $stmtCheck = $conn->prepare("SELECT ly_do_huy, trang_thai FROM donhang WHERE id = :id");
        $stmtCheck->execute([':id' => $id]);
        $order = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        // Chỉ xử lý nếu trạng thái là 4 và CHƯA có chữ [Đã hoàn kho]
        if ($order && $order['trang_thai'] == 4 && strpos($order['ly_do_huy'], '[Đã hoàn kho]') === false) {
            
            // 2. LẤY SẢN PHẨM ĐỂ CỘNG KHO (Bảng bienthe_sanpham)
            $sql_items = "SELECT sanpham_id, so_luong, mau, size FROM chitiet_donhang WHERE donhang_id = :id";
            $stmt_items = $conn->prepare($sql_items);
            $stmt_items->execute([':id' => $id]);
            $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            // 3. CỘNG KHO
            $sql_restock = "UPDATE bienthe_sanpham 
                            SET so_luong_ton = so_luong_ton + :qty 
                            WHERE sanpham_id = :sp_id 
                            AND TRIM(mau_sac) = TRIM(:mau) 
                            AND TRIM(kich_thuoc) = TRIM(:size)";
            $stmt_restock = $conn->prepare($sql_restock);
            
            $count = 0;
            foreach ($items as $item) {
                $stmt_restock->execute([
                    ':qty'   => $item['so_luong'],
                    ':sp_id' => $item['sanpham_id'],
                    ':mau'   => $item['mau'],
                    ':size'  => $item['size']
                ]);
                $count++;
            }

            // 4. ĐÁNH DẤU LÀ ĐÃ XONG (Sửa lý do hủy để không bị duyệt lại)
            $new_reason = "[Đã hoàn kho] " . $order['ly_do_huy'];
            $stmtUpdate = $conn->prepare("UPDATE donhang SET ly_do_huy = :new_reason WHERE id = :id");
            $stmtUpdate->execute([':new_reason' => $new_reason, ':id' => $id]);

            $conn->commit();
            echo "<script>alert('Đã cộng kho thành công cho $count sản phẩm!'); window.location.href='quanlydoitra.php';</script>";
        } else {
            $conn->rollBack();
            echo "<script>alert('Đơn này đã được hoàn kho rồi hoặc không hợp lệ!'); window.location.href='quanlydoitra.php';</script>";
        }

    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('Lỗi: ".$e->getMessage()."');</script>";
    }
}

// --- LẤY DANH SÁCH ĐƠN HỦY (TRẠNG THÁI 4) ---
$sql = "SELECT * FROM donhang WHERE trang_thai = 4 ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="page-header">
        <h2 class="page-title" style="color:#e74c3c;">QUẢN LÝ YÊU CẦU TRẢ HÀNG / HỦY ĐƠN</h2>
        <a href="donhang.php" class="btn-back">Quay lại DS Đơn</a>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th>Mã ĐH</th>
                <th>Khách hàng</th>
                <th width="35%">Lý do hủy</th>
                <th>Tổng tiền</th>
                <th>Trạng thái kho</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($requests) > 0): ?>
                <?php foreach($requests as $row): ?>
                <?php 
                    // Kiểm tra xem đã hoàn kho chưa dựa vào text
                    $is_restocked = (strpos($row['ly_do_huy'], '[Đã hoàn kho]') !== false);
                ?>
                <tr>
                    <td><b>#<?php echo $row['id']; ?></b></td>
                    <td>
                        <div class="customer-info">
                            <span class="name"><?php echo htmlspecialchars($row['ten_nguoi_nhan']); ?></span><br>
                            <span class="phone"><?php echo htmlspecialchars($row['sdt_nguoi_nhan']); ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="reason-box">
                            <i class="fa-solid fa-quote-left"></i>
                            <?php echo htmlspecialchars($row['ly_do_huy']); ?>
                        </div>
                    </td>
                    <td style="color:#c0392b; font-weight:bold;">
                        <?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>đ
                    </td>
                    <td>
                        <?php if($is_restocked): ?>
                            <span class="badge badge-success" style="background:green; color:white; padding:4px 8px; border-radius:4px;">Đã cộng kho</span>
                        <?php else: ?>
                            <span class="badge badge-warning" style="background:orange; color:white; padding:4px 8px; border-radius:4px;">Chờ xử lý</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="xem_donhang.php?id=<?php echo $row['id']; ?>" class="btn-action btn-view" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></a>
                        
                        <?php if(!$is_restocked): ?>
                            <a href="?action=confirm&id=<?php echo $row['id']; ?>" 
                               class="btn-action btn-approve" 
                               onclick="return confirm('Bạn có chắc chắn muốn nhận lại hàng và cộng vào kho?')"
                               title="Đồng ý hoàn kho">
                               <i class="fa-solid fa-check"></i> Duyệt
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">Không có đơn hủy nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
/* CSS giữ nguyên như cũ */
.table-data { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.table-data th { background: #fee; color: #c0392b; padding: 10px; text-align: left; }
.table-data td { padding: 10px; border-bottom: 1px solid #eee; }
.btn-action { padding: 5px 10px; text-decoration: none; border-radius: 4px; color: #fff; display: inline-block; margin-right: 5px;}
.btn-view { background: #3498db; }
.btn-approve { background: #27ae60; font-weight: bold;}
</style>