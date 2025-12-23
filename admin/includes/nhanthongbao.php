<?php
// admin/get_notif.php
header('Content-Type: application/json');

// Kết nối CSDL (Copy logic kết nối từ header của bạn)
if (file_exists('../connect.php')) {
    require_once '../connect.php';
} elseif (file_exists('connect.php')) {
    require_once 'connect.php';
} else {
    echo json_encode(['error' => 'No DB connection']);
    exit;
}

try {
    // 1. Đếm số tin chưa đọc (trang_thai = 0)
    $stmt_count = $conn->query("SELECT COUNT(*) as solhuong FROM lienhe WHERE trang_thai = 0");
    $row_count = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $count = $row_count['solhuong'];

    // 2. Lấy 5 tin mới nhất (Sắp xếp chưa xem lên trước, rồi đến ngày mới nhất)
    $stmt_list = $conn->query("SELECT * FROM lienhe ORDER BY trang_thai ASC, ngay_gui DESC LIMIT 5");
    $list = $stmt_list->fetchAll(PDO::FETCH_ASSOC);

    // Trả về kết quả dạng JSON để Javascript đọc
    echo json_encode([
        'count' => $count,
        'list'  => $list
    ]);

} catch (PDOException $e) {
    echo json_encode(['count' => 0, 'list' => []]);
}
?>