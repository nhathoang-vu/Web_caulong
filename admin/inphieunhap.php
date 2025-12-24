<?php
// FILE: inphieunhap.php
session_start();
require_once '../connect.php'; 

if (!isset($_GET['ma_phieu'])) {
    die("Không tìm thấy mã phiếu.");
}

$ma_phieu = $_GET['ma_phieu'];

// 1. Lấy thông tin chung
$stmt = $conn->prepare("SELECT pn.*, th.ten_thuonghieu, th.dia_chi, th.email 
                        FROM phieunhap pn 
                        JOIN thuonghieu th ON pn.thuonghieu_id = th.id 
                        WHERE pn.ma_phieu = :ma LIMIT 1");
$stmt->execute([':ma' => $ma_phieu]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$info) die("Phiếu không tồn tại.");

// 2. Lấy chi tiết
$stmt2 = $conn->prepare("SELECT pn.*, sp.ten_sanpham, bt.mau_sac, bt.kich_thuoc 
                          FROM phieunhap pn
                          JOIN sanpham sp ON pn.sanpham_id = sp.id
                          JOIN bienthe_sanpham bt ON pn.bienthe_id = bt.id
                          WHERE pn.ma_phieu = :ma");
$stmt2->execute([':ma' => $ma_phieu]);
$items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$tong_cong = 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In Phiếu Nhập - <?php echo $ma_phieu; ?></title>
    <style>
        /* CSS CHO TRANG IN (KHỔ A4) */
        body { 
            font-family: 'Times New Roman', serif; 
            font-size: 14px; 
            background: #525659; /* Màu nền xám đậm giống trình xem PDF */
            margin: 0; 
            padding: 30px 0; 
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        /* KHUNG CHỨA NÚT BẤM (TOOLBAR) */
        .print-btn-container {
            width: 210mm; /* Rộng bằng khổ giấy A4 */
            display: flex;
            justify-content: space-between; /* Nút back trái, nút in phải */
            margin-bottom: 15px;
            padding: 0;
            box-sizing: border-box;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-family: 'Segoe UI', sans-serif; /* Font chữ nút hiện đại hơn */
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .btn-back {
            background: #fff;
            color: #333;
        }
        .btn-back:hover { background: #f1f1f1; transform: translateY(-1px); }

        .btn-print {
            background: #2980b9;
            color: #fff;
        }
        .btn-print:hover { background: #1c6ea4; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,0,0,0.3); }

        /* TRANG GIẤY A4 */
        .page-a4 {
            background: #fff; 
            width: 210mm; 
            min-height: 297mm; 
            padding: 20mm; 
            box-sizing: border-box; 
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            position: relative;
        }
        
        /* HEADER */
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .company-info h1 { margin: 0; font-size: 20px; text-transform: uppercase; margin-bottom: 5px; }
        .bill-info { text-align: right; }

        /* TITLE */
        .bill-title { text-align: center; font-size: 26px; font-weight: bold; margin: 25px 0; text-transform: uppercase; }

        /* INFO */
        .info-section { margin-bottom: 20px; line-height: 1.6; }

        /* TABLE */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 10px 8px; font-size: 13px; }
        th { background: #f5f5f5; text-align: center; font-weight: bold; text-transform: uppercase; }
        .col-num { text-align: center; width: 40px; }
        .col-money { text-align: right; }
        
        /* FOOTER */
        .footer { margin-top: 50px; display: flex; justify-content: space-between; text-align: center; }
        .sign-box { margin-top: 20px; font-weight: bold; min-width: 150px; }
        .sign-box span { display: block; margin-top: 5px; font-weight: normal; font-style: italic; font-size: 12px; }

        /* --- CSS KHI IN (ẨN NÚT, XÓA NỀN) --- */
        @media print {
            body { background: #fff; padding: 0; display: block; }
            .print-btn-container { display: none !important; }
            .page-a4 { 
                width: 100%; margin: 0; padding: 0; 
                box-shadow: none; border: none; 
                min-height: auto;
            }
            @page { margin: 20mm; size: A4; }
        }
    </style>
</head>
<body>

    <div class="print-btn-container">
        <a href="nhapkho.php" class="btn btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Quay lại kho
        </a>
        
        <button onclick="window.print()" class="btn btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            IN PHIẾU / LƯU PDF
        </button>
    </div>

    <div class="page-a4">
        <div class="header">
            <div class="company-info">
                <h1>CỬA HÀNG THỂ THAO HBG</h1>
                <div>Địa chỉ: 123, Lạch Tray, Ngô Quyền, Hải Phòng</div>
                <div>Hotline: 0904.082.576</div>
            </div>
            <div class="bill-info">
                <div><strong>Số phiếu:</strong> <?php echo $ma_phieu; ?></div>
                <div><strong>Ngày nhập:</strong> <?php echo date('d/m/Y H:i', strtotime($info['ngay_tao'])); ?></div>
            </div>
        </div>

        <div class="bill-title">PHIẾU NHẬP KHO</div>

        <div class="info-section">
            <table style="border: none; margin: 0;">
                <tr>
                    <td style="border: none; padding: 4px 0; width: 120px;"><strong>Nhà cung cấp:</strong></td>
                    <td style="border: none; padding: 4px 0;"><?php echo $info['ten_thuonghieu']; ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 4px 0;"><strong>Địa chỉ:</strong></td>
                    <td style="border: none; padding: 4px 0;"><?php echo $info['dia_chi'] ? $info['dia_chi'] : '---'; ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 4px 0;"><strong>Email:</strong></td>
                    <td style="border: none; padding: 4px 0;"><?php echo $info['email'] ? $info['email'] : '---'; ?></td>
                </tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-num">STT</th>
                    <th>Tên sản phẩm</th>
                    <th>Quy cách (Màu - Size)</th>
                    <th>SL</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach($items as $item): ?>
                    <?php $tong_cong += $item['thanh_tien']; ?>
                    <tr>
                        <td class="col-num"><?php echo $i++; ?></td>
                        <td>
                            <strong><?php echo $item['ten_sanpham']; ?></strong>
                        </td>
                        <td style="text-align: center;"><?php echo $item['mau_sac'] . ' - ' . $item['kich_thuoc']; ?></td>
                        <td style="text-align: center; font-weight: bold;"><?php echo $item['so_luong']; ?></td>
                        <td class="col-money"><?php echo number_format($item['don_gia']); ?></td>
                        <td class="col-money"><?php echo number_format($item['thanh_tien']); ?></td>
                    </tr>
                <?php endforeach; ?>
                
                <tr style="background-color: #fafafa;">
                    <td colspan="5" style="text-align: right; font-weight: bold; font-size: 14px; padding-right: 15px; text-transform: uppercase;">Tổng cộng:</td>
                    <td class="col-money" style="font-weight: bold; font-size: 15px;"><?php echo number_format($tong_cong); ?> đ</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 30px; font-style: italic;">
            <strong>Bằng chữ:</strong> .............................................................................................................................................................
        </div>

        <div class="footer">
            <div class="sign-box">
                Người lập phiếu
                <span>(Ký, ghi rõ họ tên)</span>
            </div>
            <div class="sign-box">
                Nhà cung cấp
                <span>(Ký, ghi rõ họ tên)</span>
            </div>
            <div class="sign-box">
                Thủ kho
                <span>(Ký, ghi rõ họ tên)</span>
            </div>
        </div>
    </div>

</body>
</html>