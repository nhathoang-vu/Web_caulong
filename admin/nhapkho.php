<?php
// FILE: nhapkho.php
session_start();
require_once '../connect.php'; 

// --- XỬ LÝ XÓA PHIẾU ---
if (isset($_GET['delete_maphieu'])) {
    $ma = $_GET['delete_maphieu'];
    
    // Xóa trong database
    $stmt = $conn->prepare("DELETE FROM phieunhap WHERE ma_phieu = :ma");
    $stmt->execute([':ma' => $ma]);
    
    $_SESSION['success_msg'] = "Đã xóa toàn bộ phiếu nhập <b>$ma</b> thành công!";
    header("Location: nhapkho.php");
    exit;
}

// --- LẤY DỮ LIỆU & GOM NHÓM ---
$sql = "SELECT 
            pn.*,
            sp.ten_sanpham, sp.hinh_anh,
            th.ten_thuonghieu,
            bt.mau_sac, bt.kich_thuoc
        FROM phieunhap pn
        LEFT JOIN sanpham sp ON pn.sanpham_id = sp.id
        LEFT JOIN thuonghieu th ON pn.thuonghieu_id = th.id
        LEFT JOIN bienthe_sanpham bt ON pn.bienthe_id = bt.id
        ORDER BY pn.ngay_tao DESC, pn.ma_phieu DESC"; 
$stmt = $conn->prepare($sql);
$stmt->execute();
$all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý gom nhóm theo 'ma_phieu'
$grouped_data = [];
foreach ($all_data as $row) {
    $ma = $row['ma_phieu'];
    if (!isset($grouped_data[$ma])) {
        $grouped_data[$ma] = [
            'ma_phieu' => $ma,
            'ten_thuonghieu' => $row['ten_thuonghieu'],
            'ngay_tao' => $row['ngay_tao'],
            'tong_tien_phieu' => 0,
            'items' => []
        ];
    }
    
    $img_path = !empty($row['hinh_anh']) ? "anh_sanpham/".$row['hinh_anh'] : "https://via.placeholder.com/40";
    $row['full_image_path'] = $img_path;
    
    $grouped_data[$ma]['tong_tien_phieu'] += $row['thanh_tien'];
    $grouped_data[$ma]['items'][] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Nhập Kho</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        /* --- CSS GIỮ NGUYÊN --- */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; color: #333; margin: 0; }
        .wrap-content { padding: 25px; max-width: 1200px; margin: 0 auto; }
        
        .page-header { margin-bottom: 25px; } 
        .page-title { font-size: 24px; font-weight: 600; color: #2c3e50; margin: 0; }
        .toolbar { background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); border: 1px solid #eaeaea; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; gap: 20px; }
        
        .search-group { flex-grow: 1; display: flex; }
        .search-box { position: relative; width: 100%; max-width: 400px; }
        .search-input { width: 100%; padding: 9px 15px 9px 35px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; outline: none; transition: border 0.2s; box-sizing: border-box; }
        .search-input:focus { border-color: #4a69bd; box-shadow: 0 0 0 3px rgba(74, 105, 189, 0.1); }
        .search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #999; pointer-events: none; }

        .btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block; cursor: pointer; transition: all 0.2s; border: none; }
        .btn-create { background-color: #eef2f7; color: #4a69bd; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0; }
        .btn-create:hover { background-color: #dbeafe; color: #1e3a8a; }
        
        .btn-del { background-color: #fff1f2; color: #e11d48; font-weight: 600; font-size: 13px; padding: 6px 12px; border: 1px solid #fecdd3; }
        .btn-del:hover { background-color: #e11d48; color: #fff; border-color: #e11d48; }

        .table-container { background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #eaeaea; }
        
        .main-table { width: 100%; border-collapse: collapse; }
        .main-header th { background-color: #fff; color: #636e72; font-weight: 700; text-transform: uppercase; font-size: 12px; padding: 15px; border-bottom: 2px solid #f1f2f6; text-align: left; }
        
        .main-row { cursor: pointer; transition: 0.2s; border-bottom: 1px solid #f1f1f1; }
        .main-row:hover { background-color: #f0f4ff; } 
        .main-cell { padding: 15px; font-size: 14px; color: #4b5563; }
        
        .badge-ncc { background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .price-total { color: #dc2626; font-weight: 700; }

        /* --- CSS CHO MODAL CHI TIẾT --- */
        .modal-detail-overlay { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0, 0, 0, 0.6); z-index: 9000; 
            display: none; justify-content: center; align-items: center; 
            backdrop-filter: blur(2px);
        }
        
        .modal-detail-box { 
            background: #fff; width: 800px; max-width: 95%; max-height: 90vh; 
            border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); 
            display: flex; flex-direction: column; overflow: hidden;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .md-header { 
            padding: 20px; border-bottom: 1px solid #eee; display: flex; 
            justify-content: space-between; align-items: center; background: #f8fafc;
        }
        .md-title { font-size: 18px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; }
        .md-close { cursor: pointer; color: #94a3b8; transition: 0.2s; font-size: 24px; line-height: 1; }
        .md-close:hover { color: #ef4444; }

        .md-body { padding: 0; overflow-y: auto; }

        .detail-table { width: 100%; border-collapse: collapse; }
        .detail-table th { background: #fff; position: sticky; top: 0; z-index: 10; padding: 12px 20px; font-size: 13px; color: #64748b; border-bottom: 2px solid #e2e8f0; text-align: left; }
        .detail-table td { padding: 12px 20px; font-size: 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .detail-table tr:last-child td { border-bottom: none; }

        .md-footer { 
            padding: 15px 20px; border-top: 1px solid #eee; background: #fff; 
            display: flex; justify-content: space-between; align-items: center; 
        }

        .product-mini { display: flex; align-items: center; gap: 12px; }
        .product-mini img { width: 40px; height: 40px; border-radius: 6px; border: 1px solid #eee; object-fit: cover; }
        
        /* Modal Xác Nhận Xóa */
        .simple-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: none; justify-content: center; align-items: center; z-index: 9999; }
        .simple-box { background: #fff; width: 400px; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); text-align: center; }
        .simple-title { font-size: 20px; font-weight: 600; margin-bottom: 10px; color: #1f2937; }
        .simple-text { font-size: 15px; color: #6b7280; margin-bottom: 25px; line-height: 1.5; }
        .simple-actions { display: flex; justify-content: center; gap: 15px; }
        .btn-simple { padding: 10px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; transition: 0.2s; }
        .btn-simple-cancel { background: #f3f4f6; color: #374151; }
        .btn-simple-cancel:hover { background: #e5e7eb; }
        .btn-simple-confirm { background: #ef4444; color: #fff; }
        .btn-simple-confirm:hover { background: #dc2626; }
    </style>
</head>
<body>

<?php 
if (file_exists('includes/header.php')) include 'includes/header.php';
elseif (file_exists('header.php')) include 'header.php';
?>

<div class="wrap-content">
    <div class="page-header">
        <h3 class="page-title">Quản lý Nhập kho</h3>
    </div>

    <div class="toolbar">
        <div class="search-group">
            <div class="search-box">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="client_search" class="search-input" placeholder="Tìm nhanh theo Mã phiếu, NCC...">
            </div>
        </div>
        <a href="taophieunhap.php" class="btn btn-create">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Tạo phiếu nhập
        </a>
    </div>

    <div class="table-container">
        <table class="main-table">
            <thead>
                <tr class="main-header">
                    <th style="padding-left: 20px;">Mã Phiếu</th>
                    <th>Nhà Cung Cấp</th>
                    <th>Ngày Tạo</th>
                    <th style="text-align: right;">Tổng Tiền</th>
                    <th style="text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody id="table_body">
                <?php if(empty($grouped_data)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 40px; color: #999;">Chưa có dữ liệu phiếu nhập.</td></tr>
                <?php else: ?>
                    <?php foreach ($grouped_data as $ma => $phieu): ?>
                        <tr class="main-row view-detail-trigger" data-id="<?php echo $ma; ?>">
                            <td class="main-cell" style="padding-left: 20px;">
                                <span style="color: #4a69bd; margin-right: 5px;">👁</span>
                                <span style="font-weight: 600; color: #2c3e50;"><?php echo $ma; ?></span>
                            </td>
                            <td class="main-cell">
                                <span class="badge-ncc"><?php echo $phieu['ten_thuonghieu']; ?></span>
                            </td>
                            <td class="main-cell" style="color: #666; font-size: 13px;">
                                <?php echo date('d/m/Y H:i', strtotime($phieu['ngay_tao'])); ?>
                            </td>
                            <td class="main-cell" style="text-align: right;">
                                <span class="price-total"><?php echo number_format($phieu['tong_tien_phieu']); ?>đ</span>
                            </td>
                            <td class="main-cell" style="text-align: center;">
                                <button class="btn btn-del btn-delete-trigger" 
                                        data-href="nhapkho.php?delete_maphieu=<?php echo $ma; ?>" 
                                        data-id="<?php echo $ma; ?>">
                                    Xóa
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-detail-overlay" id="detailModal">
    <div class="modal-detail-box">
        <div class="md-header">
            <div class="md-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4a69bd" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Chi tiết phiếu nhập: <span id="md_maphieu" style="margin-left: 8px; color: #4a69bd;">...</span>
            </div>
            <div class="md-close" onclick="closeDetailModal()">×</div>
        </div>
        
        <div class="md-body">
            <div style="padding: 15px 20px; background: #fff; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; font-size: 14px;">
                <div>
                    <div style="color: #64748b; font-size: 12px; margin-bottom: 2px;">NHÀ CUNG CẤP</div>
                    <div style="font-weight: 600;" id="md_ncc">...</div>
                </div>
                <div style="text-align: right;">
                    <div style="color: #64748b; font-size: 12px; margin-bottom: 2px;">NGÀY NHẬP</div>
                    <div style="font-weight: 600;" id="md_ngay">...</div>
                </div>
            </div>

            <table class="detail-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Sản phẩm</th>
                        <th>Phân loại</th>
                        <th style="text-align: center;">SL</th>
                        <th style="text-align: right;">Đơn giá</th>
                        <th style="text-align: right; padding-right: 20px;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody id="md_table_body">
                    </tbody>
            </table>
        </div>

        <div class="md-footer">
            <div style="font-size: 16px;">
                Tổng tiền phiếu: <strong style="color: #dc2626; font-size: 18px;" id="md_total">0đ</strong>
            </div>
            </div>
    </div>
</div>

<div class="simple-overlay" id="confirmModal">
    <div class="simple-box">
        <div class="simple-title">Xác nhận xóa</div>
        <div class="simple-text">
            Bạn có chắc chắn muốn xóa phiếu nhập <b id="del_name_display">...</b> không?<br>
            <span style="font-size: 13px; color: #ef4444;">Hành động này sẽ xóa tất cả sản phẩm trong phiếu này!</span>
        </div>
        <div class="simple-actions">
            <button class="btn-simple btn-simple-cancel" id="cancelDelete">Hủy bỏ</button>
            <button class="btn-simple btn-simple-confirm" id="confirmDelete">Xóa ngay</button>
        </div>
    </div>
</div>

<?php 
if(file_exists('thongbao.php')) include 'thongbao.php'; 
?>

<script>
const phieuData = <?php echo json_encode($grouped_data, JSON_UNESCAPED_UNICODE); ?>;

$(document).ready(function(){
    var deleteUrl = '';

    // 1. CLICK VÀO DÒNG ĐỂ XEM CHI TIẾT (MODAL)
    $('.view-detail-trigger').click(function(e){
        if ($(e.target).closest('.btn-delete-trigger').length) return;

        let ma = $(this).data('id');
        showDetailModal(ma);
    });

    window.showDetailModal = function(ma) {
        if (!phieuData[ma]) return;
        let data = phieuData[ma];
        
        $('#md_maphieu').text(ma);
        $('#md_ncc').text(data.ten_thuonghieu);
        
        let dateObj = new Date(data.ngay_tao);
        let dateStr = dateObj.toLocaleDateString('vi-VN') + ' ' + dateObj.toLocaleTimeString('vi-VN').slice(0,5);
        $('#md_ngay').text(dateStr);

        let html = '';
        data.items.forEach(item => {
            html += `
                <tr>
                    <td style="padding-left: 20px;">
                        <div class="product-mini">
                            <img src="${item.full_image_path}" onerror="this.src='https://via.placeholder.com/40'">
                            <div>
                                <div style="font-weight: 600; color: #334155;">${item.ten_sanpham}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color: #64748b;">${item.mau_sac} - ${item.kich_thuoc}</td>
                    <td style="text-align: center; font-weight: 600;">${item.so_luong}</td>
                    <td style="text-align: right;">${parseInt(item.don_gia).toLocaleString()}</td>
                    <td style="text-align: right; padding-right: 20px; font-weight: 600; color: #059669;">
                        ${parseInt(item.thanh_tien).toLocaleString()}đ
                    </td>
                </tr>
            `;
        });
        $('#md_table_body').html(html);
        $('#md_total').text(parseInt(data.tong_tien_phieu).toLocaleString() + 'đ');

        $('#detailModal').css('display', 'flex');
    };

    window.closeDetailModal = function() {
        $('#detailModal').fadeOut(200);
    };

    $('#detailModal').click(function(e){
        if(e.target === this) closeDetailModal();
    });

    // 2. TÌM KIẾM NHANH
    $('#client_search').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#table_body tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // 3. XỬ LÝ XÓA
    $('.btn-delete-trigger').click(function(e){
        e.stopPropagation(); 
        deleteUrl = $(this).data('href');
        var name = $(this).data('id');
        $('#del_name_display').text(name);
        $('#confirmModal').fadeIn(200).css('display', 'flex');
    });

    $('#confirmDelete').click(function(){
        if(deleteUrl) window.location.href = deleteUrl;
    });

    $('#cancelDelete, .simple-overlay').click(function(e){
        if(e.target === this || e.target.id === 'cancelDelete') {
            $('#confirmModal').fadeOut(200);
        }
    });
});
</script>

</body>
</html>