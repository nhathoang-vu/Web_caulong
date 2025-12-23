<?php
require_once '../connect.php';
include 'includes/header.php';

// Cập nhật trạng thái đơn hàng nếu có yêu cầu
if(isset($_GET['cn_status']) && isset($_GET['id'])){
    $stmt = $conn->prepare("UPDATE donhang SET trang_thai = :st WHERE id = :id");
    $stmt->execute([':st' => $_GET['cn_status'], ':id' => $_GET['id']]);
    echo "<script>window.location.href='donhang.php';</script>";
}

// Lấy danh sách đơn hàng
$sql = "SELECT * FROM donhang ORDER BY ngay_dat DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="page-header">
        <h2 class="page-title">QUẢN LÝ ĐƠN HÀNG</h2>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th>Mã ĐH</th>
                <th>Khách hàng</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Thanh toán</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $row): ?>
            <tr>
                <td><b>#<?php echo $row['id']; ?></b></td>
                <td>
                    <?php echo htmlspecialchars($row['ho_ten_nguoi_nhan']); ?><br>
                    <small><?php echo $row['sdt_nguoi_nhan']; ?></small>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['ngay_dat'])); ?></td>
                <td style="color: #d32f2f; font-weight: bold;">
                    <?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>đ
                </td>
                <td><?php echo $row['phuong_thuc_thanh_toan']; ?></td>
                <td>
                    <?php 
                        if($row['trang_thai'] == 0) echo '<span class="badge-new">Chờ duyệt</span>';
                        elseif($row['trang_thai'] == 1) echo '<span class="badge-done">Đã duyệt</span>';
                        else echo '<span class="badge-cancel">Đã hủy</span>';
                    ?>
                </td>
                <td>
                    <a href="xem_donhang.php?id=<?php echo $row['id']; ?>" class="btn-view">Xem & In</a>
                    <?php if($row['trang_thai'] == 0): ?>
                        <a href="?cn_status=1&id=<?php echo $row['id']; ?>" class="btn-approve" onclick="return confirm('Duyệt đơn này?')">Duyệt</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
/* CSS Nhanh cho bảng */
.table-data { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
.table-data th, .table-data td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
.badge-new { background: #ff9800; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-done { background: #4caf50; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.btn-view { background: #2196F3; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
.btn-approve { background: #4caf50; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
</style>