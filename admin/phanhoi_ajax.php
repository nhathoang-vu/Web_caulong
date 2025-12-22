<?php
// admin/phanhoi_ajax.php
require_once '../connect.php';

// --- XỬ LÝ LOGIC LỌC (Giống hệt logic cũ) ---
$where_clauses = [];
$params = [];

// 1. Trạng thái
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
if ($status !== 'all' && $status !== '') {
    $where_clauses[] = "trang_thai = :status";
    $params[':status'] = $status;
}

// 2. Từ khóa
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
if (!empty($keyword)) {
    $where_clauses[] = "(ho_ten LIKE :kw OR email LIKE :kw OR sdt LIKE :kw)";
    $params[':kw'] = "%$keyword%";
}

// 3. Thời gian
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
if (!empty($date_from)) {
    $where_clauses[] = "DATE(ngay_gui) >= :date_from";
    $params[':date_from'] = $date_from;
}
if (!empty($date_to)) {
    $where_clauses[] = "DATE(ngay_gui) <= :date_to";
    $params[':date_to'] = $date_to;
}

// 4. Ghép SQL
$sql_where = "";
if (count($where_clauses) > 0) {
    $sql_where = "WHERE " . implode(" AND ", $where_clauses);
}

// --- TRUY VẤN DỮ LIỆU ---
$sql = "SELECT * FROM lienhe $sql_where ORDER BY trang_thai ASC, ngay_gui DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$lienhes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- TRẢ VỀ HTML (Chỉ trả về các dòng <tr>) ---
if(count($lienhes) > 0){
    foreach($lienhes as $row){
        $is_unread = ($row['trang_thai'] == 0);
        $row_class = $is_unread ? 'unread' : '';
        $status_badge = $is_unread 
            ? '<span class="status-badge status-new">Mới</span>' 
            : '<span class="status-badge status-read">Đã xem</span>';

        echo "<tr class='$row_class'>";
        echo "<td><b>#{$row['id']}</b></td>";
        echo "<td>
                <div class='sender-box'>
                    <strong>{$row['ho_ten']}</strong>
                    <span><i class='fa-solid fa-envelope'></i> {$row['email']}</span>
                    <span><i class='fa-solid fa-phone'></i> {$row['sdt']}</span>
                </div>
              </td>";
        echo "<td><div class='content-box'>{$row['noi_dung']}</div></td>";
        echo "<td>
                <div class='time-box'>
                    ".date('d/m/Y', strtotime($row['ngay_gui']))."
                    <small>".date('H:i', strtotime($row['ngay_gui']))."</small>
                </div>
              </td>";
        echo "<td>$status_badge</td>";
        echo "<td>
                <div class='action-container'>";
                    if($is_unread){
                        echo "<a href='?mark_read={$row['id']}' class='action-btn btn-check' title='Đánh dấu đã đọc'><i class='fa-solid fa-check'></i></a>";
                    }
        echo "      <a href='?delete={$row['id']}' class='action-btn btn-trash' onclick=\"return confirm('Xóa tin nhắn này?');\" title='Xóa'><i class='fa-solid fa-trash'></i></a>
                </div>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' style='text-align:center; padding: 60px; color: #999;'>
            <i class='fa-solid fa-filter' style='font-size:40px; margin-bottom:15px; display:block; color:#ddd;'></i>
            Không tìm thấy tin nhắn nào phù hợp!
          </td></tr>";
}
?>