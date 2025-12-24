<?php
// FILE: tonkho.php
session_start();
require_once '../connect.php'; 

// =======================================================
// PHẦN 1: XỬ LÝ AJAX (Khi Javascript gọi lên)
// =======================================================
if (isset($_GET['action']) && $_GET['action'] == 'ajax_search') {
    
    $q = isset($_GET['q']) ? $_GET['q'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    
    $where_clauses = [];
    $params = [];

    // 1. Lọc theo tên
    if (!empty($q)) {
        $where_clauses[] = "sp.ten_sanpham LIKE :search";
        $params[':search'] = "%" . $q . "%";
    }

    // 2. Lọc theo trạng thái tồn kho
    if (!empty($status)) {
        if ($status == 'out') {
            $where_clauses[] = "bt.so_luong_ton <= 0";
        } elseif ($status == 'low') {
            $where_clauses[] = "bt.so_luong_ton > 0 AND bt.so_luong_ton <= 5";
        } elseif ($status == 'ok') {
            $where_clauses[] = "bt.so_luong_ton > 5";
        }
    }

    $sql_where = "";
    if (!empty($where_clauses)) {
        $sql_where = "WHERE " . implode(" AND ", $where_clauses);
    }

    // SQL lấy dữ liệu
    $sql = "SELECT 
                bt.id as bienthe_id,
                bt.mau_sac,
                bt.kich_thuoc,
                bt.so_luong_ton, 
                sp.id as sp_id,
                sp.ten_sanpham, 
                sp.hinh_anh
            FROM bienthe_sanpham bt
            JOIN sanpham sp ON bt.sanpham_id = sp.id
            $sql_where
            ORDER BY bt.so_luong_ton ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Xuất HTML
    if (empty($products)) {
        echo '<tr><td colspan="4" style="text-align: center; padding: 40px; color: #999;">Không tìm thấy sản phẩm nào phù hợp.</td></tr>';
    } else {
        foreach ($products as $row) {
            $img_url = !empty($row['hinh_anh']) ? "anh_sanpham/".$row['hinh_anh'] : "https://via.placeholder.com/45";
            
            $qty = $row['so_luong_ton'];
            $status_html = '';
            if ($qty <= 0) {
                $status_html = '<span class="status-pill st-out">🔴 Hết hàng</span>';
            } elseif ($qty <= 5) {
                $status_html = '<span class="status-pill st-low">🟠 Sắp hết</span>';
            } else {
                $status_html = '<span class="status-pill st-ok">🟢 Có sẵn</span>';
            }

            echo '<tr>
                <td style="padding-left: 20px;">
                    <div class="prod-info">
                        <img src="'.$img_url.'" class="prod-img" onerror="this.src=\'https://via.placeholder.com/45\'">
                        <div>
                            <span class="prod-name">'.htmlspecialchars($row['ten_sanpham']).'</span>
                            <span class="prod-sub">ID: #'.$row['sp_id'].'</span>
                        </div>
                    </div>
                </td>
                <td style="color: #555;">
                    <b>'.$row['mau_sac'].'</b>';
                    if(!empty($row['kich_thuoc'])) echo ' - ' . $row['kich_thuoc'];
            echo '</td>
                <td style="text-align: center;">
                    <span class="stock-num">'.$row['so_luong_ton'].'</span>
                </td>
                <td style="text-align: center;">
                    '.$status_html.'
                </td>
            </tr>';
        }
    }
    exit; // Dừng code PHP tại đây khi chạy AJAX
}

// =======================================================
// PHẦN 2: GIAO DIỆN CHÍNH
// =======================================================
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tồn Kho</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* CSS GIỮ NGUYÊN STYLE CŨ, CHỈ CHỈNH TOOLBAR */
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; color: #333; margin: 0; }
        .wrap-content { padding: 25px; max-width: 1200px; margin: 0 auto; }
        
        .page-header { margin-bottom: 25px; }
        .page-title { font-size: 24px; font-weight: 700; color: #2c3e50; margin: 0; }
        
        /* CHỈNH SỬA TOOLBAR CHIA 2 CỘT */
        .toolbar { 
            background: #fff; padding: 15px; border-radius: 8px; 
            box-shadow: 0 2px 6px rgba(0,0,0,0.04); margin-bottom: 20px; 
            display: flex; gap: 15px; /* Khoảng cách giữa 2 ô */
        }
        
        /* Ô tìm kiếm chiếm 50% (flex: 1) */
        .search-box { position: relative; flex: 1; }
        .search-input { 
            width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #ddd; 
            border-radius: 6px; outline: none; box-sizing: border-box; 
            font-size: 14px; transition: 0.3s;
        }
        .search-input:focus { border-color: #4a69bd; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888; }
        
        /* Ô chọn trạng thái chiếm 50% (flex: 1) */
        .status-select {
            flex: 1; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; 
            outline: none; font-size: 14px; cursor: pointer; color: #444; background: #fff;
        }
        .status-select:focus { border-color: #4a69bd; }

        /* Bảng dữ liệu */
        .table-container { background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #eaeaea; min-height: 400px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #f8fafc; color: #64748b; font-weight: 700; font-size: 13px; text-transform: uppercase; padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .data-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: middle; color: #334155; }
        .data-table tr:hover { background-color: #f8fafc; }

        .prod-info { display: flex; align-items: center; gap: 12px; }
        .prod-img { width: 45px; height: 45px; border-radius: 6px; object-fit: cover; border: 1px solid #eee; }
        .prod-name { font-weight: 600; color: #2c3e50; display: block; margin-bottom: 4px; }
        .prod-sub { font-size: 12px; color: #888; }

        .stock-num { font-weight: 700; font-size: 15px; }
        .status-pill { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        
        .st-ok { background: #dcfce7; color: #166534; }
        .st-low { background: #fef9c3; color: #854d0e; }
        .st-out { background: #fee2e2; color: #991b1b; }

        #loading { display: none; text-align: center; padding: 20px; color: #999; font-style: italic; }
    </style>
</head>
<body>

<?php 
if (file_exists('includes/header.php')) include 'includes/header.php';
elseif (file_exists('header.php')) include 'header.php';
?>

<div class="wrap-content">
    <div class="page-header">
        <h3 class="page-title">Quản lý Tồn Kho</h3>
    </div>

    <div class="toolbar">
        <div class="search-box">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="keyword" class="search-input" placeholder="Nhập tên sản phẩm...">
        </div>
        
        <select id="status_filter" class="status-select">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="out">🔴 Hết hàng</option>
            <option value="low">🟠 Sắp hết</option>
            <option value="ok">🟢 Có sẵn</option>
        </select>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="padding-left: 20px;">Sản phẩm</th>
                    <th>Phân loại</th>
                    <th style="text-align: center;">Tồn kho</th>
                    <th style="text-align: center;">Trạng thái</th>
                </tr>
            </thead>
            <tbody id="table-body">
                </tbody>
        </table>
        <div id="loading">Đang tải dữ liệu...</div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Tải dữ liệu ban đầu
        loadData();

        // 1. Sự kiện gõ tìm kiếm (có delay 300ms)
        let timeout = null;
        $('#keyword').on('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                loadData();
            }, 300);
        });

        // 2. Sự kiện chọn dropdown (nhảy luôn không cần đợi)
        $('#status_filter').on('change', function() {
            loadData();
        });
    });

    function loadData() {
        let kw = $('#keyword').val();
        let st = $('#status_filter').val();

        // Hiệu ứng mờ khi đang tải
        $('#loading').show();
        $('#table-body').css('opacity', '0.5');

        $.ajax({
            url: 'tonkho.php?action=ajax_search',
            type: 'GET',
            data: { q: kw, status: st }, // Gửi cả từ khóa và trạng thái
            success: function(response) {
                $('#table-body').html(response);
                $('#table-body').css('opacity', '1');
                $('#loading').hide();
            },
            error: function() {
                $('#loading').hide();
            }
        });
    }
</script>

</body>
</html>