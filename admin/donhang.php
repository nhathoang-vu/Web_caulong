<?php
// admin/donhang.php
require_once '../connect.php';
include 'includes/header.php';

// --- PHẦN 1: XỬ LÝ TRẠNG THÁI ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    try {
        // A. DUYỆT ĐƠN (Từ Mới đặt [0] -> Đã xác nhận [1]) -> MỚI THÊM
        if ($action == 'confirm') {
            $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 1 WHERE id = :id AND trang_thai = 0");
            $stmt->execute([':id' => $id]);
        }
        // B. GIAO HÀNG (Từ Đã xác nhận [1] -> Đang giao [2])
        elseif ($action == 'shipping') {
            $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 2 WHERE id = :id AND trang_thai = 1");
            $stmt->execute([':id' => $id]);
        } 
        // C. HOÀN TẤT (Từ Đang giao [2] -> Hoàn thành [3])
        elseif ($action == 'complete') {
            $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 3 WHERE id = :id AND trang_thai = 2");
            $stmt->execute([':id' => $id]);
        } 
        // D. HỦY ĐƠN TRỰC TIẾP BỞI ADMIN (Chuyển về [4] và HOÀN KHO NGAY LẬP TỨC)
        elseif ($action == 'cancel') {
            // 1. Kiểm tra trạng thái hiện tại (Chỉ hủy đơn chưa hoàn thành)
            $stmt_check = $conn->prepare("SELECT trang_thai FROM donhang WHERE id = :id");
            $stmt_check->execute([':id' => $id]);
            $stt = $stmt_check->fetchColumn();

            // Chỉ xử lý khi đơn chưa hoàn thành (3) và chưa bị hủy (4)
            if ($stt != 3 && $stt != 4) {
                // 2. Lấy sản phẩm trong đơn để hoàn kho
                $sql_dt = "SELECT sanpham_id, so_luong, mau_sac, kich_thuoc FROM chitiet_donhang WHERE donhang_id = :id";
                $stmt_dt = $conn->prepare($sql_dt);
                $stmt_dt->execute([':id' => $id]);
                $items = $stmt_dt->fetchAll(PDO::FETCH_ASSOC);

                // 3. Cập nhật trạng thái sang 4 (Đã Hủy) - SỬA LẠI TỪ 0 THÀNH 4
                $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 4, ly_do_huy = 'Admin hủy trực tiếp' WHERE id = :id");
                $stmt->execute([':id' => $id]);

                // 4. Cộng lại số lượng tồn kho (bảng bienthe_sanpham)
                if ($stmt->rowCount() > 0) {
                    $sql_kho = "UPDATE bienthe_sanpham 
                                SET so_luong_ton = so_luong_ton + :sl 
                                WHERE sanpham_id = :sp AND TRIM(mau_sac) = TRIM(:mau) AND TRIM(kich_thuoc) = TRIM(:size)";
                    $stmt_kho = $conn->prepare($sql_kho);
                    
                    foreach ($items as $item) {
                        $stmt_kho->execute([
                            ':sl'   => $item['so_luong'],
                            ':sp'   => $item['sanpham_id'],
                            ':mau'  => $item['mau_sac'],
                            ':size' => $item['kich_thuoc']
                        ]);
                    }
                }
            }
        }
        
        echo "<script>window.location.href='donhang.php';</script>";
        exit;

    } catch (Exception $e) {
        echo "<script>alert('Lỗi: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}

// --- LẤY DANH SÁCH ĐƠN HÀNG ---
// SỬA: Thêm trạng thái 0 vào danh sách lấy
$sql = "SELECT * FROM donhang WHERE trang_thai IN (0, 1, 2, 3) ORDER BY ngay_dat DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="page-header">
        <h2 class="page-title">QUẢN LÝ ĐƠN HÀNG</h2>
        
        <div class="header-buttons">
            <a href="phieuxuat.php" class="btn-link-gray">
                 Quản lý Phiếu In
            </a>
            <a href="quanlydoitra.php" class="btn-link-cancel">
                 Yêu cầu Đổi/Trả
            </a>
        </div>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th>Mã ĐH</th>
                <th>Khách hàng</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th width="22%">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $row): ?>
            <tr>
                <td><b>#<?php echo $row['id']; ?></b></td>
                <td>
                    <div class="customer-name"><?php echo htmlspecialchars($row['ten_nguoi_nhan']); ?></div>
                    <div class="customer-phone"><?php echo htmlspecialchars($row['sdt_nguoi_nhan']); ?></div>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['ngay_dat'])); ?></td>
                <td class="price-col"><?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>đ</td>
                <td>
                    <?php
                        // Sửa lại hiển thị badge cho đủ trạng thái
                        if($row['trang_thai'] == 0) echo '<span class="badge badge-warning">Mới đặt</span>';
                        elseif($row['trang_thai'] == 1) echo '<span class="badge badge-new">Đã xác nhận</span>';
                        elseif($row['trang_thai'] == 2) echo '<span class="badge badge-primary">Đang giao</span>';
                        elseif($row['trang_thai'] == 3) echo '<span class="badge badge-success">Hoàn tất</span>';
                    ?>
                </td>
                <td>
                    <a href="xem_donhang.php?id=<?php echo $row['id']; ?>" class="btn-action btn-view" title="Xem chi tiết">
                        <i class="fa-solid fa-eye"></i>
                    </a>

                    <?php if($row['trang_thai'] == 0): ?>
                        <a href="?action=confirm&id=<?php echo $row['id']; ?>" class="btn-action btn-confirm" onclick="return confirm('Duyệt đơn hàng này?')" title="Duyệt đơn">
                            <i class="fa-solid fa-check"></i>
                        </a>
                        <a href="?action=cancel&id=<?php echo $row['id']; ?>" class="btn-action btn-cancel" onclick="return confirm('Hủy đơn hàng này?')" title="Hủy đơn">
                            <i class="fa-solid fa-xmark"></i>
                        </a>

                    <?php elseif($row['trang_thai'] == 1): ?>
                        <a href="?action=shipping&id=<?php echo $row['id']; ?>" class="btn-action btn-ship" onclick="return confirm('Xác nhận giao hàng?')" title="Giao hàng">
                            <i class="fa-solid fa-truck"></i>
                        </a>
                        <a href="?action=cancel&id=<?php echo $row['id']; ?>" class="btn-action btn-cancel" onclick="return confirm('Hủy đơn và hoàn kho ngay lập tức?')" title="Hủy đơn">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                      
                        <a href="xem_donhang.php?id=<?php echo $row['id']; ?>" class="btn-action btn-print" title="Vào xem để In phiếu">
                            <i class="fa-solid fa-print"></i>
                        </a>
                    
                    <?php elseif($row['trang_thai'] == 2): ?>
                        <a href="?action=complete&id=<?php echo $row['id']; ?>" class="btn-action btn-done" onclick="return confirm('Khách đã nhận hàng?')" title="Hoàn tất">
                            <i class="fa-solid fa-check-double"></i>
                        </a>
                        <a href="?action=cancel&id=<?php echo $row['id']; ?>" class="btn-action btn-cancel" onclick="return confirm('Hủy đơn và hoàn kho?')" title="Hủy đơn">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                        <a href="xem_donhang.php?id=<?php echo $row['id']; ?>" class="btn-action btn-print" title="Vào xem để In phiếu">
                            <i class="fa-solid fa-print"></i>
                        </a>

                    <?php elseif($row['trang_thai'] == 3): ?>
                         <a href="xem_donhang.php?id=<?php echo $row['id']; ?>" class="btn-action btn-print" title="Vào xem để In phiếu">
                            <i class="fa-solid fa-print"></i>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
/* CSS đầy đủ */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.header-buttons { display: flex; gap: 10px; }

.btn-link-cancel { text-decoration: none; color: #fff; background: #e74c3c; font-weight: bold; font-size: 13px; padding: 8px 15px; border-radius: 5px; transition: 0.3s; display: flex; align-items: center; gap: 5px; }
.btn-link-cancel:hover { background: #c0392b; }

.btn-link-gray { text-decoration: none; color: #fff; background: #555; font-weight: bold; font-size: 13px; padding: 8px 15px; border-radius: 5px; transition: 0.3s; display: flex; align-items: center; gap: 5px; }
.btn-link-gray:hover { background: #333; }

.table-data { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
.table-data th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #eee; color: #555; font-size: 14px; }
.table-data td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; font-size: 14px; }
.table-data tr:hover { background-color: #fcfcfc; }

.customer-name { font-weight: bold; color: #333; }
.customer-phone { font-size: 12px; color: #777; }
.price-col { color: #d32f2f; font-weight: bold; }

/* Badges */
.badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; color: #fff; }
.badge-warning { background: #f1c40f; color: #333; } /* Màu vàng cho Mới đặt */
.badge-new { background: #3498db; } /* Đã xác nhận */
.badge-primary { background: #2980b9; } /* Đang giao */
.badge-success { background: #2ecc71; } /* Hoàn tất */

/* Nút hành động */
.btn-action {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 4px;
    margin-right: 4px; text-decoration: none; transition: 0.2s; color: #fff;
    font-size: 13px;
}
.btn-view { background: #7f8c8d; }
.btn-confirm { background: #27ae60; } /* Nút duyệt xanh lá */
.btn-ship { background: #f39c12; }
.btn-done { background: #27ae60; }
.btn-cancel { background: #e74c3c; }
.btn-print { background: #2c3e50; }

.btn-action:hover { opacity: 0.9; transform: translateY(-1px); }
</style>