<?php
// admin/quanly_phieuin.php
require_once '../connect.php';
include 'includes/header.php';

// --- PHẦN 1: XỬ LÝ HÀNH ĐỘNG (XÓA PHIẾU) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    try {
        if ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM phieu_xuat WHERE ma_phieu = :id");
            $stmt->execute([':id' => $id]);
            echo "<script>alert('Đã xóa phiếu in!'); window.location.href='quanly_phieuin.php';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Lỗi: " . $e->getMessage() . "');</script>";
    }
}

// --- PHẦN 2: LẤY DỮ LIỆU TỪ BẢNG PHIEU_XUAT ---
$sql = "SELECT * FROM phieu_xuat ORDER BY ma_phieu DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$phieu_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="page-header">
        <h2 class="page-title">QUẢN LÝ LỊCH SỬ PHIẾU IN</h2>
        <div class="header-buttons">
            <a href="donhang.php" class="btn-link-gray">
                <i class="fa-solid fa-arrow-left"></i> Quay lại Đơn hàng
            </a>
        </div>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th width="10%">Mã Phiếu</th>
                <th width="15%">Ngày tạo</th>
                <th width="20%">Khách hàng</th>
                <th width="25%">Địa chỉ</th>
                <th width="15%">Tổng tiền</th>
                <th>Ghi chú</th>
                <th width="10%">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($phieu_list) > 0): ?>
                <?php foreach($phieu_list as $row): ?>
                <tr>
                    <td><b>#PX<?php echo $row['ma_phieu']; ?></b></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['ngay_tao'])); ?></td>
                    <td>
                        <div class="customer-name"><?php echo htmlspecialchars($row['ten_khach_hang']); ?></div>
                    </td>
                    <td>
                        <span style="font-size: 13px; color: #555;"><?php echo htmlspecialchars($row['dia_chi']); ?></span>
                    </td>
                    <td class="price-col">
                        <?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>đ
                    </td>
                    <td>
                        <em style="font-size: 12px; color: #777;"><?php echo htmlspecialchars($row['ghi_chu']); ?></em>
                    </td>
                    <td>
                        <button onclick="printOldSlip(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="btn-action btn-print" title="In lại phiếu này">
                            <i class="fa-solid fa-print"></i>
                        </button>

                        <a href="?action=delete&id=<?php echo $row['ma_phieu']; ?>" class="btn-action btn-cancel" onclick="return confirm('Bạn chắc chắn muốn xóa lịch sử phiếu này?')" title="Xóa">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">Chưa có phiếu in nào được lưu.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="print-area-template" style="display: none;">
    <div style="font-family: 'Times New Roman', serif; padding: 20px; border: 1px solid #000; width: 100%; max-width: 800px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 20px; border-bottom: 1px solid #000; padding-bottom: 10px;">
            <h2 style="margin: 0;">PHIẾU XUẤT KHO</h2>
            <p style="margin: 5px 0;">Mã phiếu: <span id="p-ma"></span> - Ngày: <span id="p-ngay"></span></p>
        </div>
        
        <div style="margin-bottom: 20px; line-height: 1.6;">
            <p><strong>Khách hàng:</strong> <span id="p-khach"></span></p>
            <p><strong>Địa chỉ:</strong> <span id="p-diachi"></span></p>
            <p><strong>Ghi chú:</strong> <span id="p-ghichu"></span></p>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="border: 1px solid #000; padding: 8px;">Nội dung</th>
                    <th style="border: 1px solid #000; padding: 8px;">Tổng tiền thanh toán</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #000; padding: 20px; text-align: center;">Chi tiết theo đơn hàng</td>
                    <td style="border: 1px solid #000; padding: 20px; text-align: center; font-weight: bold; font-size: 18px;">
                        <span id="p-tien"></span> VNĐ
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 40px; display: flex; justify-content: space-between; text-align: center;">
            <div style="width: 40%;">
                <strong>Người nhận hàng</strong><br>
                (Ký, họ tên)
            </div>
            <div style="width: 40%;">
                <strong>Người lập phiếu</strong><br>
                (Ký, họ tên)
            </div>
        </div>
    </div>
</div>

<script>
    // Hàm xử lý in lại phiếu cũ từ dữ liệu hàng
    function printOldSlip(data) {
        // 1. Điền dữ liệu vào mẫu in
        document.getElementById('p-ma').innerText = "#PX" + data.ma_phieu;
        document.getElementById('p-ngay').innerText = new Date(data.ngay_tao).toLocaleDateString('vi-VN');
        document.getElementById('p-khach').innerText = data.ten_khach_hang;
        document.getElementById('p-diachi').innerText = data.dia_chi;
        document.getElementById('p-ghichu').innerText = data.ghi_chu;
        
        // Format tiền
        let tien = new Intl.NumberFormat('vi-VN').format(data.tong_tien);
        document.getElementById('p-tien').innerText = tien;

        // 2. Tạo cửa sổ in mới
        let printContent = document.getElementById('print-area-template').innerHTML;
        let originalContent = document.body.innerHTML;

        // Cách in đơn giản: Ẩn hết body, hiện mỗi printContent
        document.body.innerHTML = printContent;
        window.print();
        
        // Sau khi in xong (hoặc hủy), load lại trang để hiện lại giao diện cũ
        // Lưu ý: Chrome đôi khi block việc set lại innerHTML sau print, nên tốt nhất là reload
        setTimeout(function() {
             location.reload(); 
        }, 500);
    }
</script>

<style>
/* Kế thừa CSS từ trang donhang.php của bạn để đồng bộ */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.header-buttons { display: flex; gap: 10px; }
.btn-link-gray { text-decoration: none; color: #fff; background: #555; font-weight: bold; font-size: 13px; padding: 8px 15px; border-radius: 5px; display: flex; align-items: center; gap: 5px; }
.btn-link-gray:hover { background: #333; }

.table-data { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
.table-data th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #eee; color: #555; font-size: 14px; }
.table-data td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; font-size: 14px; }
.table-data tr:hover { background-color: #fcfcfc; }

.customer-name { font-weight: bold; color: #333; }
.price-col { color: #d32f2f; font-weight: bold; }

.btn-action {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 4px;
    margin-right: 4px; text-decoration: none; transition: 0.2s; color: #fff;
    font-size: 13px; border: none; cursor: pointer;
}
.btn-print { background: #2c3e50; }
.btn-cancel { background: #e74c3c; }
.btn-action:hover { opacity: 0.9; }
</style>