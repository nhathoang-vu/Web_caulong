<?php
// admin/export_profit_pdf.php
require_once '../connect.php';

// --- LẤY DỮ LIỆU (Giống file báo cáo chính) ---
$d = isset($_GET['day']) ? $_GET['day'] : '';
$m = isset($_GET['month']) ? $_GET['month'] : date('m');
$y = isset($_GET['year']) ? $_GET['year'] : date('Y');

$where_clauses = [];
$params = [];
if ($d) { $where_clauses[] = "DAY(ngay_tao) = ?"; $params[] = $d; }
if ($m) { $where_clauses[] = "MONTH(ngay_tao) = ?"; $params[] = $m; }
if ($y) { $where_clauses[] = "YEAR(ngay_tao) = ?"; $params[] = $y; }
$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// 1. Doanh thu
$stmt1 = $conn->prepare("SELECT SUM(tong_tien) as revenue FROM phieu_xuat $where_sql");
$stmt1->execute($params);
$revenue = $stmt1->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;

// 2. Chi phí
$stmt2 = $conn->prepare("SELECT SUM(thanh_tien) as cost FROM phieunhap $where_sql");
$stmt2->execute($params);
$cost = $stmt2->fetch(PDO::FETCH_ASSOC)['cost'] ?? 0;

// 3. Lợi nhuận
$profit = $revenue - $cost;

// Tiêu đề báo cáo
$time_text = "Năm $y";
if($m) $time_text = "Tháng $m/$y";
if($d) $time_text = "Ngày $d/$m/$y";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo lợi nhuận - <?php echo $time_text; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Times New Roman', serif; padding: 40px; text-align: center; }
        .report-header { margin-bottom: 30px; }
        h2 { margin: 0; text-transform: uppercase; }
        p { margin: 5px 0; color: #555; }
        
        .chart-container {
            width: 400px; /* Kích thước biểu đồ trong PDF */
            margin: 0 auto 30px auto;
        }

        .summary-table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 16px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }
        .summary-table th { background: #f0f0f0; }
        .num { text-align: right; font-weight: bold; }
        .green { color: #27ae60; }
        .red { color: #c0392b; }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="report-header">
        <h2>BÁO CÁO TÀI CHÍNH & LỢI NHUẬN</h2>
        <p>Thời gian: <?php echo $time_text; ?></p>
        <p>Ngày xuất báo cáo: <?php echo date('d/m/Y H:i'); ?></p>
    </div>

    <div class="chart-container">
        <canvas id="profitChart"></canvas>
    </div>

    <table class="summary-table">
        <tr>
            <th>Khoản mục</th>
            <th class="num">Số tiền (VNĐ)</th>
        </tr>
        <tr>
            <td><span style="color:#2ecc71">■</span> Tổng Doanh Thu</td>
            <td class="num green"><?php echo number_format($revenue, 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td><span style="color:#e74c3c">■</span> Tổng Chi Phí Nhập</td>
            <td class="num red"><?php echo number_format($cost, 0, ',', '.'); ?></td>
        </tr>
        <tr style="background-color: #fafafa;">
            <td><strong>LỢI NHUẬN RÒNG</strong></td>
            <td class="num" style="color: <?php echo $profit>=0 ? '#27ae60' : '#c0392b'; ?>">
                <?php echo number_format($profit, 0, ',', '.'); ?>
            </td>
        </tr>
    </table>

    <div class="no-print" style="margin-top: 30px;">
       
    </div>

    <script>
        // Cấu hình biểu đồ
        const ctx = document.getElementById('profitChart').getContext('2d');
        const profitChart = new Chart(ctx, {
            type: 'pie', // Dạng biểu đồ tròn
            data: {
                labels: ['Doanh Thu', 'Chi Phí'],
                datasets: [{
                    label: 'Số tiền (VNĐ)',
                    data: [<?php echo $revenue; ?>, <?php echo $cost; ?>],
                    backgroundColor: [
                        '#2ecc71', // Màu xanh cho Doanh thu
                        '#e74c3c'  // Màu đỏ cho Chi phí
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Biểu đồ tỷ lệ Doanh Thu / Chi Phí'
                    }
                }
            }
        });

        // Tự động in sau khi tải trang xong
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 800); // Đợi 0.8s để biểu đồ vẽ xong rồi mới in
        }
    </script>
</body>
</html>