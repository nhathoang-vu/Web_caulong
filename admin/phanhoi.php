<?php
// 1. KẾT NỐI & HEADER
require_once '../connect.php'; 
include 'includes/header.php'; 

// Xử lý Xóa/Đánh dấu đọc (Vẫn giữ logic PHP thuần cho an toàn)
if(isset($_GET['delete'])){
    $stmt = $conn->prepare("DELETE FROM lienhe WHERE id=:id");
    $stmt->execute([':id'=>$_GET['delete']]);
    echo "<script>window.location.href='phanhoi.php';</script>";
    exit();
}
if(isset($_GET['mark_read'])){
    $stmt = $conn->prepare("UPDATE lienhe SET trang_thai = 1 WHERE id=:id");
    $stmt->execute([':id'=>$_GET['mark_read']]);
    echo "<script>window.location.href='phanhoi.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Phản hồi - HBG Shop</title>
    <link rel="stylesheet" href="assets/phanhoi.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    
    <div class="main-content"> 
        <div class="page-header">
            <h2 class="page-title"><i class="fa-solid fa-inbox"></i> HỘP THƯ KHÁCH HÀNG</h2>
            <div id="loading-spinner" style="display:none; color: #004e92;">
                <i class="fa-solid fa-spinner fa-spin"></i> Đang lọc...
            </div>
        </div>

        <div class="filter-bar" id="filterForm">
            
            <div class="filter-group">
                <select name="status" id="status" class="filter-input">
                    <option value="all">-- Tất cả trạng thái --</option>
                    <option value="0">● Chưa xem (Mới)</option>
                    <option value="1">● Đã xem</option>
                </select>
            </div>

            <div class="filter-group">
                <i class="fa-solid fa-magnifying-glass search-icon-overlay"></i>
                <input type="text" name="keyword" id="keyword" class="filter-input" placeholder="Nhập tên, email, sđt...">
            </div>

            <div class="filter-group">
                <span class="filter-label">Từ:</span>
                <input type="date" name="date_from" id="date_from" class="filter-input">
            </div>
            <div class="filter-group">
                <span class="filter-label">Đến:</span>
                <input type="date" name="date_to" id="date_to" class="filter-input">
            </div>

            <button type="button" id="btn-reset" class="btn-reset" style="display:none;">
                <i class="fa-solid fa-rotate-left"></i> Xóa lọc
            </button>
        </div>

        <table>
            <colgroup>
                <col style="width: 5%;">  
                <col style="width: 25%;"> 
                <col style="width: 40%;"> 
                <col style="width: 12%;"> 
                <col style="width: 10%;"> 
                <col style="width: 8%;">  
            </colgroup>
            
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người gửi</th>
                    <th>Nội dung</th>
                    <th>Ngày gửi</th>
                    <th>Trạng thái</th>
                    <th style="text-align:center;">Xử lý</th>
                </tr>
            </thead>
            
            <tbody id="table-body">
                <?php
                // Code load dữ liệu ban đầu (Logic y hệt file ajax)
                $sql_init = "SELECT * FROM lienhe ORDER BY trang_thai ASC, ngay_gui DESC";
                $stmt = $conn->prepare($sql_init);
                $stmt->execute();
                $lienhes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if(count($lienhes) > 0){
                    foreach($lienhes as $row){
                        $is_unread = ($row['trang_thai'] == 0);
                        $row_class = $is_unread ? 'unread' : '';
                        $status_badge = $is_unread ? '<span class="status-badge status-new">Mới</span>' : '<span class="status-badge status-read">Đã xem</span>';

                        echo "<tr class='$row_class'>";
                        echo "<td><b>#{$row['id']}</b></td>";
                        echo "<td><div class='sender-box'><strong>{$row['ho_ten']}</strong><span><i class='fa-solid fa-envelope'></i> {$row['email']}</span><span><i class='fa-solid fa-phone'></i> {$row['sdt']}</span></div></td>";
                        echo "<td><div class='content-box'>{$row['noi_dung']}</div></td>";
                        echo "<td><div class='time-box'>".date('d/m/Y', strtotime($row['ngay_gui']))."<small>".date('H:i', strtotime($row['ngay_gui']))."</small></div></td>";
                        echo "<td>$status_badge</td>";
                        echo "<td><div class='action-container'>";
                        if($is_unread) echo "<a href='?mark_read={$row['id']}' class='action-btn btn-check'><i class='fa-solid fa-check'></i></a>";
                        echo "<a href='?delete={$row['id']}' class='action-btn btn-trash' onclick=\"return confirm('Xóa?');\"><i class='fa-solid fa-trash'></i></a></div></td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
    $(document).ready(function(){
        
        // Hàm chính để gọi AJAX
        function filterData() {
            // 1. Lấy giá trị từ các ô input
            var status = $('#status').val();
            var keyword = $('#keyword').val();
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();

            // Hiển thị loading
            $('#loading-spinner').fadeIn(200);

            // 2. Gửi yêu cầu sang file phanhoi_ajax.php
            $.ajax({
                url: 'phanhoi_ajax.php',
                type: 'GET',
                data: {
                    status: status,
                    keyword: keyword,
                    date_from: date_from,
                    date_to: date_to
                },
                success: function(response){
                    // 3. Nhận kết quả HTML và đổ vào <tbody>
                    $('#table-body').html(response);
                    
                    // Tắt loading
                    $('#loading-spinner').fadeOut(200);

                    // 4. Kiểm tra để hiện nút Reset
                    if(status !== 'all' || keyword !== '' || date_from !== '' || date_to !== '') {
                        $('#btn-reset').fadeIn();
                    } else {
                        $('#btn-reset').fadeOut();
                    }
                },
                error: function(){
                    alert('Có lỗi xảy ra khi tải dữ liệu!');
                    $('#loading-spinner').fadeOut();
                }
            });
        }

        // --- BẮT SỰ KIỆN ---
        
        // 1. Khi chọn Select hoặc Date -> Lọc ngay
        $('#status, #date_from, #date_to').on('change', function(){
            filterData();
        });

        // 2. Khi gõ phím vào ô tìm kiếm (Dùng kỹ thuật Debounce - Chờ người dùng ngừng gõ 0.5s mới lọc)
        var timeout = null;
        $('#keyword').on('input', function(){
            clearTimeout(timeout);
            timeout = setTimeout(function(){
                filterData();
            }, 500); // Chờ 500ms sau khi ngừng gõ
        });

        // 3. Nút Reset
        $('#btn-reset').on('click', function(){
            $('#status').val('all');
            $('#keyword').val('');
            $('#date_from').val('');
            $('#date_to').val('');
            filterData(); // Gọi lọc lại với giá trị rỗng (Hiện tất cả)
        });
    });
    </script>
</body>
</html>