<?php
// admin/baocao_doanhthu.php
require_once '../connect.php';
include 'includes/header.php';

// --- XỬ LÝ BỘ LỌC ---
$d = isset($_GET['day']) && $_GET['day'] != '' ? $_GET['day'] : '';
$m = isset($_GET['month']) && $_GET['month'] != '' ? $_GET['month'] : date('m');
$y = isset($_GET['year']) && $_GET['year'] != '' ? $_GET['year'] : date('Y');

// Tạo câu điều kiện WHERE động (Dùng cột ngay_tao)
$where_clauses = [];
$params = [];

if ($d) {
    $where_clauses[] = "DAY(ngay_tao) = ?";
    $params[] = $d;
}
if ($m) {
    $where_clauses[] = "MONTH(ngay_tao) = ?";
    $params[] = $m;
}
if ($y) {
    $where_clauses[] = "YEAR(ngay_tao) = ?";
    $params[] = $y;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// 1. TÍNH TỔNG DOANH THU (Cộng cột tong_tien từ bảng phieu_xuat)
$sql_total = "SELECT SUM(tong_tien) as total_revenue FROM phieu_xuat $where_sql";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->execute($params);
$total_revenue = $stmt_total->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

// 2. LẤY DANH SÁCH CHI TIẾT
$sql_list = "SELECT * FROM phieu_xuat $where_sql ORDER BY ngay_tao DESC";
$stmt_list = $conn->prepare($sql_list);
$stmt_list->execute($params);
$list_phieu = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="page-header">
        <h2 class="page-title">BÁO CÁO DOANH THU</h2>
    </div>

    <div class="filter-box" style="background:#fff; padding:15px; margin-bottom:20px; border-radius:5px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
        <form method="GET" style="display:flex; gap:10px; align-items:center;">
            <div>
                <label>Ngày:</label>
                <select name="day" style="padding:5px; border:1px solid #ddd; border-radius:4px;">
                    <option value="">Tất cả</option>
                    <?php for($i=1;$i<=31;$i++) echo "<option value='$i' ".($d==$i?'selected':'').">$i</option>"; ?>
                </select>
            </div>
            <div>
                <label>Tháng:</label>
                <select name="month" style="padding:5px; border:1px solid #ddd; border-radius:4px;">
                    <option value="">Tất cả</option>
                    <?php for($i=1;$i<=12;$i++) echo "<option value='$i' ".($m==$i?'selected':'').">Tháng $i</option>"; ?>
                </select>
            </div>
            <div>
                <label>Năm:</label>
                <select name="year" style="padding:5px; border:1px solid #ddd; border-radius:4px;">
                    <?php 
                    $curr_year = date('Y');
                    for($i=$curr_year; $i>=$curr_year-5; $i--) 
                        echo "<option value='$i' ".($y==$i?'selected':'').">$i</option>"; 
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-filter" style="background:#3498db; color:white; border:none; padding:6px 15px; border-radius:3px; cursor:pointer;">
                <i class="fa-solid fa-filter"></i> Lọc
            </button>
        </form>
    </div>

    <div class="summary-card" style="background:#2ecc71; color:white; padding:20px; margin-bottom:20px; border-radius:5px; text-align:center;">
        <h3 style="margin:0; font-size:18px;">TỔNG DOANH THU</h3>
        <div style="font-size:32px; font-weight:bold; margin-top:10px;">
            <?php echo number_format($total_revenue, 0, ',', '.'); ?> VNĐ
        </div>
    </div>

    <table class="table-data" style="width:100%; border-collapse:collapse; background:white; border-radius:8px; overflow:hidden;">
        <thead>
            <tr style="background:#f8f9fa; border-bottom:2px solid #ddd;">
                <th style="padding:12px; text-align:left;">Mã phiếu</th>
                <th style="padding:12px; text-align:left;">Ngày tạo</th>
                <th style="padding:12px; text-align:left;">Khách hàng</th>
                <th style="padding:12px; text-align:left;">Ghi chú</th>
                <th style="padding:12px; text-align:right;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($list_phieu) > 0): ?>
                <?php foreach($list_phieu as $row): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:12px;"><b>#<?php echo $row['ma_phieu']; ?></b></td>
                    <td style="padding:12px;"><?php echo date('d/m/Y H:i', strtotime($row['ngay_tao'])); ?></td>
                    <td style="padding:12px;"><?php echo htmlspecialchars($row['ten_khach_hang']); ?></td>
                    <td style="padding:12px; color:#666; font-style:italic;"><?php echo htmlspecialchars($row['ghi_chu']); ?></td>
                    <td style="padding:12px; text-align:right; font-weight:bold; color:#27ae60;">
                        <?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>đ
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="padding:20px; text-align:center; color:#777;">Chưa có dữ liệu nào phù hợp.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>