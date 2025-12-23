<?php
// =================================================================================
// === QUẢN LÝ SẢN PHẨM (ĐÃ FIX HEADER & THÔNG BÁO) ================================
// =================================================================================
session_start(); // <--- 1. BẮT BUỘC CÓ ĐỂ NHẬN THÔNG BÁO TỪ SESSION

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../connect.php'; 

if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    else if (isset($db)) $conn = $db;
}

// Hàm render Badge
function renderBadges($string_list, $class_name) {
    if (empty($string_list)) {
        return '<span class="empty-dash">---</span>';
    }
    $items = explode(',', $string_list);
    $html = '<div class="badge-container">';
    foreach ($items as $item) {
        $item = trim($item);
        if (!empty($item)) {
            $html .= '<span class="'.$class_name.'">'.$item.'</span>';
        }
    }
    $html .= '</div>';
    return $html;
}

// Xử lý Ajax
if (isset($_POST['action']) && $_POST['action'] == 'search_ajax') {
    $keyword = $_POST['keyword'];
    $dm_id   = isset($_POST['danhmuc_id']) ? (int)$_POST['danhmuc_id'] : 0;
    
    $sql_search = "SELECT 
                    sp.*, 
                    dm.ten_danhmuc, 
                    th.ten_thuonghieu,
                    GROUP_CONCAT(DISTINCT bt.kich_thuoc ORDER BY bt.kich_thuoc SEPARATOR ',') as list_kich_thuoc,
                    GROUP_CONCAT(DISTINCT bt.mau_sac ORDER BY bt.mau_sac SEPARATOR ',') as list_mau_sac
                FROM sanpham sp
                LEFT JOIN danhmuc dm ON sp.danhmuc_id = dm.id
                LEFT JOIN thuonghieu th ON sp.thuonghieu_id = th.id
                LEFT JOIN bienthe_sanpham bt ON sp.id = bt.sanpham_id
                WHERE (sp.ten_sanpham LIKE :key OR sp.id LIKE :key)";

    if ($dm_id > 0) {
        $sql_search .= " AND sp.danhmuc_id = :dm_id";
    }
    
    $sql_search .= " GROUP BY sp.id ORDER BY sp.id DESC";
                
    $stmt = $conn->prepare($sql_search);
    $stmt->bindValue(':key', "%$keyword%");
    
    if ($dm_id > 0) {
        $stmt->bindValue(':dm_id', $dm_id);
    }

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($result) > 0) {
        foreach ($result as $row) {
            $img_path = "anh_sanpham/" . $row['hinh_anh'];
            if(empty($row['hinh_anh'])) $img_path = "https://via.placeholder.com/100"; 
            
            $price_display = '';
            if ($row['gia_khuyenmai'] > 0) {
                $price_display = '<span class="price-final">'.number_format($row['gia_khuyenmai']).'đ</span>
                                    <span class="price-origin">'.number_format($row['gia_ban']).'đ</span>';
            } else {
                $price_display = '<span class="price-final">'.number_format($row['gia_ban']).'đ</span>';
            }
            
            $mau_sac_html = renderBadges($row['list_mau_sac'], 'tag-color');
            $kich_thuoc_html = renderBadges($row['list_kich_thuoc'], 'tag-size');

            echo '<tr>
                    <td>'.$row['id'].'</td>
                    <td><img src="'.$img_path.'" class="product-img" alt="Img"></td>
                    
                    <td class="cell-name"> <div style="font-weight: 500; font-size: 15px;">'.$row['ten_sanpham'].'</div>
                        <div class="text-muted" style="font-size: 12px; margin-top: 4px;">'.$row['ten_danhmuc'].'</div>
                        <div class="text-muted" style="font-size: 11px; margin-top: 2px; color: #999;">Ngày tạo: '.date('d/m/Y', strtotime($row['ngay_tao'])).'</div>
                    </td>

                    <td><span style="font-weight: 600; color: #17a2b8;">'.number_format($row['gia_nhap']).'đ</span></td>
                    <td><div class="price-group">'.$price_display.'</div></td>
                    
                    <td class="cell-variants">'.$mau_sac_html.'</td>
                    <td class="cell-variants">'.$kich_thuoc_html.'</td>
                    
                    <td>'.$row['ten_thuonghieu'].'</td>
                    <td>
                        <a href="sua_sanpham.php?id='.$row['id'].'" class="btn btn-edit">Sửa</a>
                        <a href="xoa_sanpham.php?id='.$row['id'].'" class="btn btn-del">Xóa</a>
                    </td>
                </tr>';
        }
    } else {
        echo '<tr><td colspan="9" style="text-align: center; padding: 30px; color: #999;">Không tìm thấy kết quả phù hợp.</td></tr>';
    }
    exit; 
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Sản phẩm</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        /* CSS CƠ BẢN */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; color: #333; margin: 0; }
        .wrap-content { padding: 25px; max-width: 100%; margin: 0 auto; }
        .page-header { margin-bottom: 25px; } 
        .page-title { font-size: 24px; font-weight: 600; color: #2c3e50; margin: 0; }
        
        .toolbar { background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); border: 1px solid #eaeaea; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; gap: 20px; }
        .filter-group { display: flex; align-items: center; gap: 15px; flex-shrink: 0; }
        .filter-select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; color: #555; outline: none; min-width: 200px; }
        
        .search-group { flex-grow: 1; display: flex; justify-content: center; }
        .search-box { position: relative; width: 100%; max-width: 400px; }
        .search-input { width: 100%; padding: 9px 15px 9px 35px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; outline: none; transition: border 0.2s; box-sizing: border-box; }
        .search-input:focus { border-color: #4a69bd; box-shadow: 0 0 0 3px rgba(74, 105, 189, 0.1); }
        .search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #999; pointer-events: none; }

        .btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block; cursor: pointer; transition: all 0.2s; border: none; }
        .btn-create { background-color: #eef2f7; color: #4a69bd; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0; }
        .btn-create:hover { background-color: #dbeafe; color: #1e3a8a; }
        .btn-edit { background-color: #eef2f7; color: #4a69bd; font-weight: 600; }
        .btn-edit:hover { background-color: #dbeafe; color: #1e3a8a; }
        .btn-del { background-color: #fff1f2; color: #e11d48; font-weight: 600; margin-left: 5px; }
        .btn-del:hover { background-color: #ffe4e6; color: #be123c; }

        .table-container { background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #eaeaea; }
        
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table th { background-color: #fff; color: #636e72; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; padding: 15px 10px; border-bottom: 2px solid #f1f2f6; text-align: center; }
        table td { padding: 12px 10px; border-bottom: 1px solid #f1f2f6; font-size: 14px; vertical-align: middle; color: #4b5563; text-align: center; word-wrap: break-word; }
        
        .cell-name { text-align: center !important; padding-left: 0 !important; }
        .product-img { width: 100px; height: 100px; border-radius: 6px; object-fit: cover; border: 1px solid #eee; display: inline-block; }
        
        .price-group { display: flex; flex-direction: column; align-items: center; }
        .price-final { font-weight: 600; color: #2d3436; }
        .price-origin { font-size: 11px; text-decoration: line-through; color: #b2bec3; }
        .text-muted { color: #888; }
        
        .badge-container { display: flex; flex-wrap: wrap; gap: 5px; justify-content: center; }
        .tag-color { display: inline-block; background: #fdf2f8; color: #db2777; padding: 4px 8px; border-radius: 5px; font-size: 11px; font-weight: 600; border: 1px solid #fbcfe8; white-space: nowrap; }
        .tag-size { display: inline-block; background: #f3f4f6; color: #374151; padding: 4px 8px; border-radius: 5px; font-size: 11px; font-weight: 600; border: 1px solid #e5e7eb; white-space: nowrap; }
        .empty-dash { color: #d1d5db; font-size: 12px; }
        .cell-variants { padding: 10px !important; }

        /* Modal */
        .simple-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: none; justify-content: center; align-items: center; z-index: 9999; }
        .simple-box { background: #fff; width: 350px; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); text-align: center; }
        .simple-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #333; }
        .simple-text { font-size: 14px; color: #555; margin-bottom: 20px; }
        .simple-actions { display: flex; justify-content: center; gap: 10px; }
        .btn-simple { padding: 8px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; }
        .btn-simple-cancel { background: #e0e0e0; color: #333; }
        .btn-simple-cancel:hover { background: #d0d0d0; }
        .btn-simple-confirm { background: #d9534f; color: #fff; }
        .btn-simple-confirm:hover { background: #c9302c; }
    </style>
</head>
<body>

<?php 
// 2. INCLUDE HEADER (ĐÃ SỬA LẠI ĐƯỜNG DẪN CHO GIỐNG NCC.PHP)
if (file_exists('includes/header.php')) {
    include 'includes/header.php';
} elseif (file_exists('header.php')) {
    include 'header.php';
} else {
    echo "<div style='background:red; color:white; padding:5px; text-align:center'>Không tìm thấy file header.php</div>";
}

// --- XỬ LÝ DỮ LIỆU BAN ĐẦU ---
$danhmuc_id = isset($_GET['danhmuc']) ? $_GET['danhmuc'] : 0;
$stmt_dm = $conn->prepare("SELECT * FROM danhmuc");
$stmt_dm->execute();
$list_danhmuc = $stmt_dm->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT 
            sp.*, 
            dm.ten_danhmuc, 
            th.ten_thuonghieu,
            GROUP_CONCAT(DISTINCT bt.kich_thuoc ORDER BY bt.kich_thuoc SEPARATOR ',') as list_kich_thuoc,
            GROUP_CONCAT(DISTINCT bt.mau_sac ORDER BY bt.mau_sac SEPARATOR ',') as list_mau_sac
        FROM sanpham sp
        LEFT JOIN danhmuc dm ON sp.danhmuc_id = dm.id
        LEFT JOIN thuonghieu th ON sp.thuonghieu_id = th.id
        LEFT JOIN bienthe_sanpham bt ON sp.id = bt.sanpham_id"; 

if ($danhmuc_id > 0) {
    $sql .= " WHERE sp.danhmuc_id = :dm_id";
}

$sql .= " GROUP BY sp.id ORDER BY sp.id DESC";

$stmt = $conn->prepare($sql);
if ($danhmuc_id > 0) {
    $stmt->bindParam(':dm_id', $danhmuc_id);
}
$stmt->execute();
$list_sanpham = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="wrap-content">
    
    <div class="page-header">
        <h3 class="page-title">Quản lý sản phẩm</h3>
    </div>

    <div class="toolbar">
        <div class="filter-group">
            <span style="font-weight: 500; color: #666;">Bộ lọc:</span>
            <form action="" method="GET" id="filterForm" style="margin: 0;">
                <select name="danhmuc" id="danhmuc_select" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="0">Tất cả danh mục</option>
                    <?php foreach ($list_danhmuc as $dm): ?>
                        <option value="<?php echo $dm['id']; ?>" <?php if($danhmuc_id == $dm['id']) echo 'selected'; ?>>
                            <?php echo $dm['ten_danhmuc']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="search-group">
            <div class="search-box">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="ajax_search" class="search-input" placeholder="Tìm kiếm sản phẩm...">
            </div>
        </div>

        <a href="them_sanpham.php" class="btn btn-create">+ Thêm sản phẩm</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 130px;">Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th style="width: 120px;">Giá nhập</th>  
                    <th style="width: 120px;">Giá bán</th>   
                    <th style="width: 200px;">Màu sắc</th>   
                    <th style="width: 140px;">Size</th>      
                    <th style="width: 120px;">Thương hiệu</th>      
                    <th style="width: 150px;">Thao tác</th> 
                </tr>
            </thead>
            <tbody id="table_data">
                <?php if (count($list_sanpham) > 0): ?>
                    <?php foreach ($list_sanpham as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <?php 
                                    $img_path = "anh_sanpham/" . $row['hinh_anh'];
                                    if(empty($row['hinh_anh'])) $img_path = "https://via.placeholder.com/100";
                                ?>
                                <img src="<?php echo $img_path; ?>" class="product-img" alt="Img">
                            </td>
                            
                            <td class="cell-name">
                                <div style="font-weight: 500; font-size: 15px;"><?php echo $row['ten_sanpham']; ?></div>
                                <div class="text-muted" style="font-size: 12px; margin-top: 4px;"><?php echo $row['ten_danhmuc']; ?></div>
                                <div class="text-muted" style="font-size: 11px; margin-top: 2px; color: #999;">
                                    Ngày tạo: <?php echo date('d/m/Y', strtotime($row['ngay_tao'])); ?>
                                </div>
                            </td>

                            <td><span style="font-weight: 600; color: #17a2b8;"><?php echo number_format($row['gia_nhap']); ?>đ</span></td>
                            <td>
                                <div class="price-group">
                                    <?php if ($row['gia_khuyenmai'] > 0): ?>
                                        <span class="price-final"><?php echo number_format($row['gia_khuyenmai']); ?>đ</span>
                                        <span class="price-origin"><?php echo number_format($row['gia_ban']); ?>đ</span>
                                    <?php else: ?>
                                        <span class="price-final"><?php echo number_format($row['gia_ban']); ?>đ</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td class="cell-variants">
                                <?php echo renderBadges($row['list_mau_sac'], 'tag-color'); ?>
                            </td>

                            <td class="cell-variants">
                                <?php echo renderBadges($row['list_kich_thuoc'], 'tag-size'); ?>
                            </td>

                            <td><?php echo $row['ten_thuonghieu']; ?></td>
                            <td>
                                <a href="sua_sanpham.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Sửa</a>
                                <a href="xoa_sanpham.php?id=<?php echo $row['id']; ?>" class="btn btn-del">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" style="text-align: center; padding: 30px; color: #999;">Không tìm thấy sản phẩm nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="simple-overlay" id="confirmModal">
    <div class="simple-box">
        <div class="simple-title">Xác nhận xóa</div>
        <div class="simple-text">Bạn có chắc chắn muốn xóa sản phẩm này không?</div>
        
        <div class="simple-actions">
            <button class="btn-simple btn-simple-cancel" id="cancelDelete">Hủy</button>
            <button class="btn-simple btn-simple-confirm" id="confirmDelete">Đồng ý</button>
        </div>
    </div>
</div>

<?php 
// 3. INCLUDE FILE THÔNG BÁO (POPUP)
if(file_exists('thongbao.php')) include 'thongbao.php'; 
?>

<script>
$(document).ready(function(){
    var deleteLink = ''; 

    // 1. Tìm kiếm Ajax
    $('#ajax_search').on('keyup', function(){
        var txt = $(this).val();
        var dm_val = $('#danhmuc_select').val(); 
        
        $.ajax({
            url: '', method: 'POST',
            data: { action: 'search_ajax', keyword: txt, danhmuc_id: dm_val },
            success: function(response){ $('#table_data').html(response); }
        });
    });

    // 2. Mở Modal Đơn giản
    $(document).on('click', '.btn-del', function(e){
        e.preventDefault();
        deleteLink = $(this).attr('href'); 
        $('#confirmModal').css('display', 'flex'); 
    });

    // 3. Xử lý nút "Đồng ý"
    $('#confirmDelete').click(function(){
        if(deleteLink) {
            window.location.href = deleteLink; 
        }
    });

    // 4. Xử lý đóng Modal
    $('#cancelDelete, .simple-overlay').click(function(e){
        if(e.target === this) {
            $('#confirmModal').hide();
        }
    });
});
</script>
</body>
</html>