<?php
// =================================================================================
// === QUẢN LÝ NHÀ CUNG CẤP (FIX HEADER PATH) ======================================
// =================================================================================
session_start(); // <--- BẮT BUỘC PHẢI CÓ Ở DÒNG ĐẦU TIÊN

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../connect.php'; 

if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    else if (isset($db)) $conn = $db;
}

// Hàm render Badge (Giữ nguyên)
function renderBadges($string_list, $class_name) {
    if (empty($string_list)) return '<span class="empty-dash">---</span>';
    $items = explode(',', $string_list);
    $html = '<div class="badge-container">';
    foreach ($items as $item) {
        $item = trim($item);
        if (!empty($item)) $html .= '<span class="'.$class_name.'">'.$item.'</span>';
    }
    $html .= '</div>';
    return $html;
}

// --- XỬ LÝ XÓA ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    // Kiểm tra ràng buộc sản phẩm
    $check = $conn->prepare("SELECT COUNT(*) FROM sanpham WHERE thuonghieu_id = ?");
    $check->execute([$id]);
    
    if ($check->fetchColumn() > 0) {
        echo "<script>alert('Không thể xóa! Hãng này đang có sản phẩm.'); window.location='ncc.php';</script>";
        exit;
    } else {
        $stmt = $conn->prepare("DELETE FROM thuonghieu WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_msg'] = "Đã xóa nhà cung cấp thành công!";
        header("Location: ncc.php");
        exit();
    }
}

// --- XỬ LÝ AJAX TÌM KIẾM ---
if (isset($_POST['action']) && $_POST['action'] == 'search_ajax') {
    $keyword = trim($_POST['keyword']);
    $sql_search = "SELECT * FROM thuonghieu WHERE ten_thuonghieu LIKE :key OR id LIKE :key ORDER BY id DESC";
    $stmt = $conn->prepare($sql_search);
    $stmt->bindValue(':key', "%$keyword%");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($result) > 0) {
        foreach ($result as $row) {
            $img_path = !empty($row['logo']) ? "assets/img/" . $row['logo'] : "https://via.placeholder.com/100?text=No+Logo";
            $contact_info = '';
            if ($row['so_dien_thoai']) $contact_info .= '<span class="price-final">📞 '.$row['so_dien_thoai'].'</span>';
            if ($row['email']) $contact_info .= '<span class="price-origin">✉️ '.$row['email'].'</span>';
            else $contact_info .= '<span class="price-origin">Chưa có Email</span>';
            
            $nguoi_lien_he_html = $row['nguoi_lien_he'] ? renderBadges($row['nguoi_lien_he'], 'tag-size') : '<span class="empty-dash">---</span>';
            $sdt_rieng_html     = $row['sdt_nguoi_lien_he'] ? renderBadges($row['sdt_nguoi_lien_he'], 'tag-color') : '<span class="empty-dash">---</span>';

            echo '<tr>
                    <td>'.$row['id'].'</td>
                    <td><img src="'.$img_path.'" class="product-img" alt="Logo"></td>
                    <td class="cell-name"> 
                        <div style="font-weight: 600; font-size: 15px; color:#2c3e50;">'.$row['ten_thuonghieu'].'</div>
                        <div class="text-muted" style="font-size: 12px; margin-top: 4px;">'.$row['dia_chi'].'</div>
                    </td>
                    <td><div class="price-group">'.$contact_info.'</div></td>
                    <td class="cell-variants">'.$nguoi_lien_he_html.'</td>
                    <td class="cell-variants">'.$sdt_rieng_html.'</td>
                    <td>
                        <a href="sua_ncc.php?id='.$row['id'].'" class="btn btn-edit">Sửa</a>
                        <a href="ncc.php?delete_id='.$row['id'].'" class="btn btn-del">Xóa</a>
                    </td>
                  </tr>';
        }
    } else {
        echo '<tr><td colspan="7" style="text-align: center; padding: 30px; color: #999;">Không tìm thấy kết quả nào.</td></tr>';
    }
    exit; 
}

// --- LẤY DỮ LIỆU BAN ĐẦU ---
$sql = "SELECT * FROM thuonghieu ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$list_ncc = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý NCC</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; color: #333; margin:0; }
        .wrap-content { padding: 25px; max-width: 100%; margin: 0 auto; }
        .page-header { margin-bottom: 25px; } 
        .page-title { font-size: 24px; font-weight: 600; color: #2c3e50; margin: 0; }
        
        .toolbar { background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); border: 1px solid #eaeaea; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; position: relative; min-height: 50px; }
        .search-group { width: 100%; max-width: 500px; display: flex; justify-content: center; }
        .search-box { position: relative; width: 100%; }
        .search-input { width: 100%; padding: 10px 15px 10px 40px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; outline: none; transition: border 0.2s; box-sizing: border-box; }
        .search-input:focus { border-color: #4a69bd; box-shadow: 0 0 0 3px rgba(74, 105, 189, 0.1); }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; pointer-events: none; }

        .btn-create { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); padding: 12px 24px; font-size: 15px; border-radius: 6px; background-color: #eef2f7; color: #4a69bd; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border: none; white-space: nowrap; transition: all 0.2s; }
        .btn-create:hover { background-color: #dbeafe; color: #1e3a8a; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        .table-container { background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #eaeaea; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { text-align: center !important; vertical-align: middle !important; padding: 15px 10px; }
        table th { background-color: #fff; color: #636e72; font-weight: 700; text-transform: uppercase; font-size: 12px; border-bottom: 2px solid #f1f2f6; white-space: nowrap; }
        table td { border-bottom: 1px solid #f1f2f6; font-size: 14px; color: #4b5563; }
        .product-img { width: 80px; height: 80px; border-radius: 6px; object-fit: contain; border: 1px solid #eee; padding: 2px; }
        .price-group { display: flex; flex-direction: column; align-items: center; gap: 3px; }
        .price-final { font-weight: 600; color: #2d3436; font-size: 13px; }
        .price-origin { font-size: 11px; color: #888; }
        .badge-container { display: flex; flex-wrap: wrap; gap: 5px; justify-content: center; }
        .tag-color { background: #fdf2f8; color: #db2777; padding: 4px 8px; border-radius: 5px; font-size: 11px; font-weight: 600; border: 1px solid #fbcfe8; }
        .tag-size { background: #f3f4f6; color: #374151; padding: 4px 8px; border-radius: 5px; font-size: 11px; font-weight: 600; border: 1px solid #e5e7eb; }
        .empty-dash { color: #d1d5db; font-size: 12px; }
        .btn { padding: 6px 12px; border-radius: 4px; font-size: 13px; font-weight: 500; text-decoration: none; display: inline-block; cursor: pointer; border: none; margin: 0 2px; }
        .btn-edit { background-color: #eef2f7; color: #4a69bd; }
        .btn-del { background-color: #fff1f2; color: #e11d48; }

        .simple-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: none; justify-content: center; align-items: center; z-index: 9999; }
        .simple-box { background: #fff; width: 350px; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); text-align: center; }
        .simple-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #333; }
        .simple-text { font-size: 14px; color: #555; margin-bottom: 20px; }
        .simple-actions { display: flex; justify-content: center; gap: 10px; }
        .btn-simple { padding: 8px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; }
        .btn-simple-cancel { background: #e0e0e0; color: #333; }
        .btn-simple-confirm { background: #d9534f; color: #fff; }
    </style>
</head>
<body>

<?php 
// --- SỬA LỖI ĐƯỜNG DẪN HEADER ---
// Tôi đã đổi về 'includes/header.php'. 
// Nếu vẫn lỗi, bạn thử đổi thành 'header.php' (nếu file nằm cùng cấp)
if (file_exists('includes/header.php')) {
    include 'includes/header.php';
} elseif (file_exists('header.php')) {
    include 'header.php';
} else {
    // Fallback: nếu không tìm thấy thì bỏ qua để không lỗi trang, nhưng hiện thông báo nhỏ
    echo "<div style='background:red; color:white; padding:5px; text-align:center'>Không tìm thấy file header.php</div>";
}
?>

<div class="wrap-content">
    <div class="page-header">
        <h3 class="page-title">Quản lý Nhà cung cấp</h3>
    </div>

    <div class="toolbar">
        <div class="search-group">
            <div class="search-box">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="ajax_search" class="search-input" placeholder="Nhập ID hoặc Tên nhà cung cấp...">
            </div>
        </div>
        <a href="them_ncc.php" class="btn btn-create">+ Thêm mới</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 120px;">Logo</th>
                    <th>Tên Nhà cung cấp</th>
                    <th>Liên hệ (Công ty)</th> 
                    <th>Người đại diện</th>   
                    <th>SĐT Riêng</th>      
                    <th style="width: 140px;">Thao tác</th> 
                </tr>
            </thead>
            <tbody id="table_data">
                <?php if (count($list_ncc) > 0): ?>
                    <?php foreach ($list_ncc as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <?php $img_path = !empty($row['logo']) ? "assets/img/" . $row['logo'] : "https://via.placeholder.com/100?text=No+Logo"; ?>
                                <img src="<?php echo $img_path; ?>" class="product-img" alt="Logo">
                            </td>
                            <td class="cell-name">
                                <div style="font-weight: 600; font-size: 15px; color:#2c3e50;"><?php echo $row['ten_thuonghieu']; ?></div>
                                <?php if($row['dia_chi']): ?>
                                    <div class="text-muted" style="font-size: 12px; margin-top: 4px;">📍 <?php echo $row['dia_chi']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="price-group">
                                    <?php if ($row['so_dien_thoai']): ?>
                                        <span class="price-final">📞 <?php echo $row['so_dien_thoai']; ?></span>
                                    <?php else: ?>
                                        <span class="empty-dash">--</span>
                                    <?php endif; ?>

                                    <?php if ($row['email']): ?>
                                        <span class="price-origin">✉️ <?php echo $row['email']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="cell-variants"><?php echo renderBadges($row['nguoi_lien_he'], 'tag-size'); ?></td>
                            <td class="cell-variants"><?php echo renderBadges($row['sdt_nguoi_lien_he'], 'tag-color'); ?></td>
                            <td>
                                <a href="sua_ncc.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Sửa</a>
                                <a href="ncc.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-del">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align: center; padding: 30px; color: #999;">Chưa có nhà cung cấp nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="simple-overlay" id="confirmModal">
    <div class="simple-box">
        <div class="simple-title">Xác nhận xóa</div>
        <div class="simple-text">Bạn có chắc chắn muốn xóa nhà cung cấp này không?</div>
        <div class="simple-actions">
            <button class="btn-simple btn-simple-cancel" id="cancelDelete">Hủy</button>
            <button class="btn-simple btn-simple-confirm" id="confirmDelete">Đồng ý</button>
        </div>
    </div>
</div>

<?php 
// Include thông báo (nếu có file này)
if(file_exists('thongbao.php')) include 'thongbao.php'; 
?>

<script>
$(document).ready(function(){
    var deleteLink = ''; 
    $('#ajax_search').on('keyup', function(){
        var txt = $(this).val();
        $.ajax({
            url: '', method: 'POST',
            data: { action: 'search_ajax', keyword: txt },
            success: function(response){ $('#table_data').html(response); }
        });
    });
    $(document).on('click', '.btn-del', function(e){
        e.preventDefault(); deleteLink = $(this).attr('href'); $('#confirmModal').css('display', 'flex'); 
    });
    $('#confirmDelete').click(function(){ if(deleteLink) window.location.href = deleteLink; });
    $('#cancelDelete, .simple-overlay').click(function(e){ if(e.target === this) $('#confirmModal').hide(); });
});
</script>
</body>
</html>