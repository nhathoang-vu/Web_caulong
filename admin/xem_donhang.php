<?php
// admin/xem_donhang.php
require_once '../connect.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: donhang.php"); exit;
}
$id = $_GET['id'];

// 1. LẤY ĐƠN HÀNG
$stmt = $conn->prepare("SELECT * FROM donhang WHERE id = :id");
$stmt->execute([':id' => $id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) { echo "Đơn hàng không tồn tại"; exit; }

// 2. LẤY CHI TIẾT
$sql_dt = "SELECT ct.*, s.ten_sanpham, s.hinh_anh 
           FROM chitiet_donhang ct 
           JOIN sanpham s ON ct.sanpham_id = s.id 
           WHERE ct.donhang_id = :id";
$stmt_dt = $conn->prepare($sql_dt);
$stmt_dt->execute([':id' => $id]);
$items = $stmt_dt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="page-header">
        <h2 class="page-title">CHI TIẾT ĐƠN HÀNG #<?php echo $order['id']; ?></h2>
        <?php if($order['trang_thai'] == 0): ?>
            <a href="quanlydoitra.php" class="btn-back">Quay lại DS Hủy</a>
        <?php else: ?>
            <a href="donhang.php" class="btn-back">Quay lại DS Đơn</a>
        <?php endif; ?>
    </div>

    <div class="invoice-box" id="invoice-content">
        <div class="info-row">
            <div class="col-left">
                <h4>Thông tin người nhận</h4>
                <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['ten_nguoi_nhan']); ?></p>
                <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['sdt_nguoi_nhan']); ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['dia_chi_giao']); ?></p>
                <p><strong>Ghi chú:</strong> <em style="color:#d35400"><?php echo htmlspecialchars($order['ghichu']); ?></em></p>
            </div>
            <div class="col-right text-right">
                <h4>Trạng thái đơn</h4>
                <div>
                    <?php
                        if ($order['trang_thai'] == 1) echo '<span class="badge badge-new">Đơn mới</span>';
                        elseif ($order['trang_thai'] == 2) echo '<span class="badge badge-warning">Đang giao</span>';
                        elseif ($order['trang_thai'] == 3) echo '<span class="badge badge-success">Hoàn tất</span>';
                        elseif ($order['trang_thai'] == 0) echo '<span class="badge badge-cancel">Đã hủy</span>';
                    ?>
                </div>
                <p style="margin-top:10px;">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['ngay_dat'])); ?></p>
                
                <?php if($order['trang_thai'] == 0 && !empty($order['ly_do_huy'])): ?>
                    <div class="cancel-reason">Lý do hủy: <?php echo htmlspecialchars($order['ly_do_huy']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <table class="table-invoice">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Phân loại</th>
                    <th>Đơn giá</th>
                    <th>SL</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="../assets/images/<?php echo $item['hinh_anh']; ?>" style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                            <span><?php echo htmlspecialchars($item['ten_sanpham']); ?></span>
                        </div>
                    </td>
                    <td>
                        <?php echo (isset($item['mau']) ? $item['mau'] : $item['mau_sac']) . ' / ' . (isset($item['size']) ? $item['size'] : $item['kich_thuoc']); ?>
                    </td>
                    <td><?php echo number_format($item['don_gia'], 0, ',', '.'); ?>đ</td>
                    <td>x<?php echo $item['so_luong']; ?></td>
                    <td style="color:#d32f2f; font-weight:bold;">
                        <?php echo number_format($item['don_gia'] * $item['so_luong'], 0, ',', '.'); ?>đ
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4" style="text-align:right; font-weight:bold;">TỔNG CỘNG:</td>
                    <td style="color:#d32f2f; font-weight:bold; font-size:18px;">
                        <?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>đ
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div class="action-buttons" style="text-align:right; margin-top:20px;">
            <button onclick="inVaLuu(<?php echo $id; ?>)" class="btn-print">
                <i class="fa-solid fa-print"></i> LƯU & IN HÓA ĐƠN
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function inVaLuu(idDonHang) {
    // Đổi nút thành đang xử lý để tránh bấm nhiều lần
    var btn = $('.btn-print');
    var oldText = btn.html();
    btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...');
    btn.prop('disabled', true);

    $.ajax({
        url: 'save_phieu_ajax.php', // Gọi file xử lý ngầm đã tạo ở bước trước
        type: 'POST',
        data: { id: idDonHang },
        success: function(response) {
            console.log("Kết quả lưu: " + response);
            // Dù lưu thành công hay thất bại thì vẫn mở cửa sổ in
            window.print();
        },
        error: function(xhr, status, error) {
            console.error("Lỗi AJAX: " + error);
            alert("Có lỗi khi lưu vào lịch sử, nhưng vẫn sẽ tiến hành in.");
            window.print();
        },
        complete: function() {
            // Trả lại trạng thái nút sau khi hộp thoại in tắt (hoặc khi code chạy xong)
            btn.html(oldText);
            btn.prop('disabled', false);
        }
    });
}
</script>

<style>
/* Style cũ */
.invoice-box { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.info-row { display: flex; justify-content: space-between; margin-bottom: 25px; border-bottom: 2px solid #f5f5f5; padding-bottom: 20px; }
.table-invoice { width: 100%; border-collapse: collapse; }
.table-invoice th, .table-invoice td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
.btn-back { padding: 8px 15px; background: #eee; text-decoration: none; color: #333; border-radius: 4px; font-weight: 500; }
.btn-print { padding: 10px 20px; background: #2c3e50; color: white; border: none; cursor: pointer; border-radius: 4px; font-size: 15px; font-weight: bold; }
.btn-print:hover { background: #34495e; }
.cancel-reason { margin-top: 10px; color: #c0392b; font-weight: bold; background: #fff5f5; padding: 5px; border: 1px dashed #e74c3c; display: inline-block; }
.badge { padding: 5px 10px; border-radius: 4px; color: #fff; font-size: 12px; }
.badge-new { background: #3498db; } .badge-warning { background: #f39c12; } .badge-success { background: #2ecc71; } .badge-cancel { background: #c0392b; }

/* [LOGIC MỚI] Style cho chế độ IN (Print) */
@media print {
    /* Ẩn tất cả các thành phần không cần thiết */
    body * {
        visibility: hidden;
    }
    /* Ẩn thanh sidebar, header, nút bấm */
    .sidebar, .page-header, .btn-back, .action-buttons, .header-buttons {
        display: none !important;
    }
    
    /* Chỉ hiện vùng hóa đơn */
    #invoice-content, #invoice-content * {
        visibility: visible;
    }
    
    /* Căn chỉnh vùng hóa đơn để in đẹp */
    #invoice-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none; /* Bỏ bóng khi in */
        border: none;
    }
    
    /* Đảm bảo bảng in ra full khổ giấy */
    .table-invoice {
        width: 100% !important;
    }
}
</style>