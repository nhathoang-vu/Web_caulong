<?php
// admin/baocao_loinhuan.php
require_once '../connect.php';
include 'includes/header.php';

// --- BỘ LỌC ---
$d = isset($_GET['day']) && $_GET['day'] != '' ? $_GET['day'] : '';
$m = isset($_GET['month']) && $_GET['month'] != '' ? $_GET['month'] : date('m');
$y = isset($_GET['year']) && $_GET['year'] != '' ? $_GET['year'] : date('Y');

// 1. TẠO ĐIỀU KIỆN LỌC (Dùng chung cho cả 2 bảng vì đều có cột ngay_tao)
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

// A. TÍNH TỔNG DOANH THU (Bảng phieu_xuat)
$stmt1 = $conn->prepare("SELECT SUM(tong_tien) as revenue FROM phieu_xuat $where_sql");
$stmt1->execute($params);
$revenue = $stmt1->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;

// B. TÍNH TỔNG CHI PHÍ NHẬP (Bảng phieunhap)
// Cột thanh_tien trong phieunhap đã là (so_luong * don_gia)
$stmt2 = $conn->prepare("SELECT SUM(thanh_tien) as cost FROM phieunhap $where_sql");
$stmt2->execute($params);
$cost = $stmt2->fetch(PDO::FETCH_ASSOC)['cost'] ?? 0;

// C. TÍNH LỢI NHUẬN
$profit = $revenue - $cost;
?>

<div class="main-content">
    <div class="page-header">
        <h2 class="page-title">BÁO CÁO LỢI NHUẬN</h2>
    </div>

    <div class="filter-box" style="background:#fff; padding:15px; margin-bottom:20px; border-radius:5px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
        <form method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap: wrap;">
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
            

            <a href="export_profit_pdf.php?day=<?=$d?>&month=<?=$m?>&year=<?=$y?>" target="_blank" style="background:#c0392b; color:white; text-decoration:none; padding:6px 15px; border-radius:3px; display:inline-flex; align-items:center; border:none;">
                 <i class="fa-solid fa-file-pdf"></i> &nbsp;Xuất PDF Biểu Đồ
            </a>
        </form>
    </div>

    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 250px; background: #fff; border-left: 5px solid #2ecc71; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-radius: 4px;">
            <div style="color: #7f8c8d; font-size: 14px; font-weight: bold; text-transform: uppercase;">Doanh Thu (Phiếu Xuất)</div>
            <div style="font-size: 26px; font-weight: bold; color: #27ae60; margin-top: 10px;">
                +<?php echo number_format($revenue, 0, ',', '.'); ?>đ
            </div>
        </div>

        <div style="flex: 1; min-width: 250px; background: #fff; border-left: 5px solid #e74c3c; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-radius: 4px;">
            <div style="color: #7f8c8d; font-size: 14px; font-weight: bold; text-transform: uppercase;">Chi Phí (Phiếu Nhập)</div>
            <div style="font-size: 26px; font-weight: bold; color: #c0392b; margin-top: 10px;">
                -<?php echo number_format($cost, 0, ',', '.'); ?>đ
            </div>
        </div>

        <div style="flex: 1; min-width: 250px; background: <?php echo $profit >= 0 ? '#e8f5e9' : '#ffebee'; ?>; border-left: 5px solid <?php echo $profit >= 0 ? '#2ecc71' : '#e74c3c'; ?>; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-radius: 4px;">
            <div style="color: #333; font-size: 14px; font-weight: bold; text-transform: uppercase;">Lợi Nhuận Ròng</div>
            <div style="font-size: 30px; font-weight: bold; color: <?php echo $profit >= 0 ? '#27ae60' : '#c0392b'; ?>; margin-top: 10px;">
                <?php echo ($profit > 0 ? '+' : '') . number_format($profit, 0, ',', '.'); ?>đ
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 15px; background: #f1f2f6; border-radius: 5px; color: #57606f; font-style: italic;">
        <p><i class="fa-solid fa-circle-info"></i> <strong>Cách tính:</strong> Lấy tổng tiền bán ra (từ bảng <code>phieu_xuat</code>) trừ đi tổng tiền nhập hàng (từ bảng <code>phieunhap</code>) trong khoảng thời gian bạn đã chọn.</p>
    </div>
</div>