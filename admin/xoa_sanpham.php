<?php
require_once '../connect.php'; 

if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    else if (isset($db)) $conn = $db;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // 1. Xóa ảnh cũ
        $stmt_get = $conn->prepare("SELECT hinh_anh FROM sanpham WHERE id = :id");
        $stmt_get->bindParam(':id', $id);
        $stmt_get->execute();
        $row = $stmt_get->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $path = "../uploads/" . $row['hinh_anh'];
            if (!empty($row['hinh_anh']) && file_exists($path)) {
                unlink($path);
            }

            // 2. Xóa dữ liệu
            $stmt_del = $conn->prepare("DELETE FROM sanpham WHERE id = :id");
            $stmt_del->bindParam(':id', $id);
            
            if ($stmt_del->execute()) {
                echo 'success'; // Trả về tín hiệu thành công cho Ajax
            } else {
                echo 'error_db';
            }
        } else {
            echo 'error_not_found';
        }

    } catch (PDOException $e) {
        echo 'error_exception';
    }
}
?>