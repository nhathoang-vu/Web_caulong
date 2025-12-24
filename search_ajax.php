<?php
require_once 'connect.php'; // Gọi file kết nối CSDL của bạn

if (isset($_POST['keyword'])) {
    $keyword = $_POST['keyword'];
    $keyword = trim($keyword); // Xóa khoảng trắng thừa

    if (strlen($keyword) > 0) {
        // Tìm kiếm sản phẩm theo tên, giới hạn 6 kết quả
        $sql = "SELECT * FROM sanpham WHERE ten_sanpham LIKE :key LIMIT 6";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':key', "%$keyword%");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            echo '<ul class="live-search-list">';
            foreach ($result as $row) {
                // Xử lý đường dẫn ảnh
                $img_path = 'admin/anh_sanpham/' . $row['hinh_anh'];
                if (empty($row['hinh_anh'])) {
                    $img_path = 'assets/images/no-image.png';
                }

                // Xử lý giá
                $gia_hien_thi = number_format($row['gia_ban'], 0, ',', '.') . 'đ';
                if ($row['gia_khuyenmai'] > 0 && $row['gia_khuyenmai'] < $row['gia_ban']) {
                    $gia_hien_thi = number_format($row['gia_khuyenmai'], 0, ',', '.') . 'đ';
                }

                // Xuất HTML từng dòng kết quả
                echo '
                <li onclick="window.location.href=\'chitiet.php?id=' . $row['id'] . '\'">
                    <div class="search-item-img">
                        <img src="' . $img_path . '" alt="' . $row['ten_sanpham'] . '">
                    </div>
                    <div class="search-item-info">
                        <div class="search-item-name">' . $row['ten_sanpham'] . '</div>
                        <div class="search-item-price">' . $gia_hien_thi . '</div>
                    </div>
                </li>';
            }
            echo '</ul>';
        } else {
            echo '<div class="no-result">Không tìm thấy sản phẩm nào.</div>';
        }
    }
}
?>