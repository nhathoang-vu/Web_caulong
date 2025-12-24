<?php
// =================================================================================
// === QUẢN LÝ KHÁCH HÀNG (ĐÃ FIX LỖI SQL LỊCH SỬ ĐƠN HÀNG) ========================
// =================================================================================
session_start();
require_once '../connect.php'; 

// Kiểm tra kết nối
if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    else if (isset($db)) $conn = $db;
}

// ---------------------------------------------------------------------------------
// 1. XỬ LÝ AJAX: XEM LỊCH SỬ MUA HÀNG (FIXED)
// ---------------------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] == 'view_history') {
    $user_id = $_POST['user_id'];
    
    // [FIX]: Đã xóa ctdh.mau_sac và ctdh.kich_thuoc vì trong bảng chitiet_donhang không có 2 cột này
    $sql_history = "SELECT 
                        dh.id as donhang_id,
                        dh.ngay_dat,
                        dh.tong_tien,
                        dh.trang_thai,
                        GROUP_CONCAT(
                            CONCAT(sp.ten_sanpham, ' (x', ctdh.so_luong, ')') 
                            SEPARATOR '<br>'
                        ) as chi_tiet_sp
                    FROM donhang dh
                    JOIN chitiet_donhang ctdh ON dh.id = ctdh.donhang_id
                    JOIN sanpham sp ON ctdh.sanpham_id = sp.id
                    WHERE dh.user_id = :uid
                    GROUP BY dh.id
                    ORDER BY dh.ngay_dat DESC";

    $stmt = $conn->prepare($sql_history);
    $stmt->bindValue(':uid', $user_id);
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($history) > 0) {
        echo '<table class="history-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Sản phẩm</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($history as $order) {
            // Xử lý trạng thái từ số sang chữ (1: Mới, 2: Giao, 3: Xong, 0: Hủy)
            $status_text = 'Không xác định';
            $status_bg = '#6c757d';

            switch ($order['trang_thai']) {
                case 1: $status_text = 'Mới đặt'; $status_bg = '#17a2b8'; break; // Xanh dương nhạt
                case 2: $status_text = 'Đang giao'; $status_bg = '#ffc107'; break; // Vàng
                case 3: $status_text = 'Hoàn thành'; $status_bg = '#28a745'; break; // Xanh lá
                case 0: $status_text = 'Đã hủy'; $status_bg = '#dc3545'; break; // Đỏ
            }

            echo '<tr>
                    <td style="font-weight:bold;">#'.$order['donhang_id'].'</td>
                    <td>'.date('d/m/Y', strtotime($order['ngay_dat'])).'</td>
                    <td style="text-align:left; font-size:13px; line-height:1.5;">'.$order['chi_tiet_sp'].'</td>
                    <td style="color:#d0021b; font-weight:bold;">'.number_format($order['tong_tien']).'đ</td>
                    <td><span style="background:'.$status_bg.'; color:#fff; padding:3px 8px; border-radius:4px; font-size:11px;">'.$status_text.'</span></td>
                  </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div style="text-align:center; padding:30px; color:#999;">Khách hàng này chưa có đơn hàng nào.</div>';
    }
    exit;
}

// ---------------------------------------------------------------------------------
// 2. HÀM LẤY DANH SÁCH KHÁCH HÀNG (Dùng chung cho Load ban đầu & Tìm kiếm)
// ---------------------------------------------------------------------------------
function getCustomers($conn, $keyword = '') {
    // Chỉ lấy quyenhan = 0 (Khách hàng)
    $sql = "SELECT 
                u.id, 
                u.name, 
                u.email, 
                u.sdt, 
                u.tendaydu, 
                u.diachi,
                COUNT(dh.id) as tong_don,
                SUM(CASE WHEN dh.trang_thai != 0 THEN dh.tong_tien ELSE 0 END) as tong_chi_tieu
            FROM user u
            LEFT JOIN donhang dh ON u.id = dh.user_id
            WHERE u.quyenhan = 0 ";

    if (!empty($keyword)) {
        $sql .= " AND (u.tendaydu LIKE :key OR u.name LIKE :key OR u.sdt LIKE :key OR u.email LIKE :key) ";
    }

    $sql .= " GROUP BY u.id ORDER BY u.id DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($keyword)) {
        $stmt->bindValue(':key', "%$keyword%");
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ---------------------------------------------------------------------------------
// 3. XỬ LÝ AJAX: TÌM KIẾM
// ---------------------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] == 'search_user') {
    $users = getCustomers($conn, $_POST['keyword']);
    if (count($users) > 0) {
        foreach ($users as $row) {
            renderUserRow($row);
        }
    } else {
        echo '<tr><td colspan="6" style="text-align:center; padding:30px; color:#999;">Không tìm thấy kết quả.</td></tr>';
    }
    exit;
}

// Hàm hiển thị dòng HTML (tránh lặp code)
function renderUserRow($row) {
    // Ưu tiên hiển thị Tên đầy đủ, nếu không có thì lấy tên đăng nhập (name)
    $displayName = !empty($row['tendaydu']) ? $row['tendaydu'] : $row['name'];
    $address = !empty($row['diachi']) ? $row['diachi'] : 'Chưa cập nhật';
    
    // Avatar
    $avatar = "https://ui-avatars.com/api/?name=".urlencode($displayName)."&background=random&color=fff&size=40";

    echo '<tr>
            <td>'.$row['id'].'</td>
            <td>
                <div style="display:flex; align-items:center; gap:10px;">
                    <img src="'.$avatar.'" style="border-radius:50%;">
                    <div>
                        <div style="font-weight:600; color:#333;">'.$displayName.'</div>
                        <div style="font-size:12px; color:#888;">ID Login: '.$row['name'].'</div>
                        <div style="font-size:11px; color:#999;">'.$row['email'].'</div>
                    </div>
                </div>
            </td>
            <td>
                <div style="font-weight:500; color:#004e92;">'.$row['sdt'].'</div>
                <div style="font-size:12px; color:#666; max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.$address.'">
                    '.$address.'
                </div>
            </td>
            <td style="text-align:center;">
                <span class="badge-count">'.$row['tong_don'].' đơn</span>
            </td>
            <td style="text-align:center;">
                <span style="font-weight:bold; color:#28a745;">'.number_format($row['tong_chi_tieu']).'đ</span>
            </td>
            <td style="text-align:center;">
                <button class="btn-history" onclick="viewHistory('.$row['id'].', \''.$displayName.'\')">
                    <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử
                </button>
            </td>
          </tr>';
}

// ---------------------------------------------------------------------------------
// 4. LOAD DỮ LIỆU BAN ĐẦU
// ---------------------------------------------------------------------------------
$list_users = getCustomers($conn);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Khách hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; color: #333; margin: 0; }
        .wrap-content { padding: 30px; }
        
        /* HEADER & SEARCH */
        .page-header { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .page-title { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .toolbar { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .search-box { position: relative; max-width: 400px; }
        .search-input { width: 100%; padding: 10px 15px 10px 40px; border: 1px solid #ddd; border-radius: 6px; outline: none; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; }

        /* TABLE */
        .table-card { background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        table th { background: #fff; color: #636e72; font-weight: 700; font-size: 13px; text-transform: uppercase; padding: 15px; border-bottom: 2px solid #f1f2f6; text-align: left; }
        table td { padding: 12px 15px; border-bottom: 1px solid #f1f2f6; font-size: 14px; vertical-align: middle; }
        
        .badge-count { background: #e9ecef; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; color: #495057; }
        .btn-history { background: #e3f2fd; color: #0d47a1; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 13px; transition: 0.2s; }
        .btn-history:hover { background: #bbdefb; }

        /* MODAL */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: #fff; width: 800px; max-width: 90%; max-height: 90vh; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; }
        .modal-header { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; }
        .modal-body { padding: 20px; overflow-y: auto; }
        .modal-close { cursor: pointer; font-size: 20px; color: #666; }
        
        .history-table th { background: #004e92; color: #fff; text-align: center; }
        .history-table td { text-align: center; vertical-align: middle; }
    </style>
</head>
<body>

<?php 
// Include Header
if (file_exists('includes/header.php')) include 'includes/header.php';
elseif (file_exists('header.php')) include 'header.php';
?>

<div class="wrap-content">
    <div class="page-header">
        <div class="page-title">Quản lý Khách hàng</div>
    </div>

    <div class="toolbar">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Tìm tên, SĐT, Email...">
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th width="50">ID</th>
                    <th>Thông tin khách hàng</th>
                    <th>Liên hệ & Địa chỉ</th>
                    <th width="100" style="text-align:center;">Số đơn</th>
                    <th width="150" style="text-align:center;">Đã chi tiêu</th>
                    <th width="120" style="text-align:center;">Hành động</th>
                </tr>
            </thead>
            <tbody id="customerTable">
                <?php if (count($list_users) > 0): ?>
                    <?php foreach ($list_users as $row): renderUserRow($row); endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">Chưa có khách hàng nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="historyModal">
    <div class="modal-content">
        <div class="modal-header">
            <h4 style="margin:0;">Lịch sử đơn hàng: <span id="modalUserName" style="color:#004e92; font-weight:bold;"></span></h4>
            <div class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></div>
        </div>
        <div class="modal-body" id="modalBody">
            </div>
    </div>
</div>

<script>
    // TÌM KIẾM
    $('#searchInput').on('keyup', function() {
        var txt = $(this).val();
        $.ajax({
            url: '', 
            method: 'POST',
            data: { action: 'search_user', keyword: txt },
            success: function(res) {
                $('#customerTable').html(res);
            }
        });
    });

    // XEM LỊCH SỬ
    function viewHistory(id, name) {
        $('#modalUserName').text(name);
        $('#historyModal').fadeIn(200).css('display', 'flex');
        $('#modalBody').html('<div style="text-align:center; padding:30px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</div>');

        $.ajax({
            url: '',
            method: 'POST',
            data: { action: 'view_history', user_id: id },
            success: function(res) {
                $('#modalBody').html(res);
            }
        });
    }

    // ĐÓNG MODAL
    function closeModal() {
        $('#historyModal').fadeOut(200);
    }
    
    $(window).click(function(e) {
        if ($(e.target).is('#historyModal')) closeModal();
    });
</script>

</body>
</html>