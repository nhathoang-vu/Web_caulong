<?php
require_once '../connect.php';
include 'includes/header.php';

// --- XỬ LÝ: ADMIN BẤM ĐỒNG Ý (CỘNG KHO) - (GIỮ NGUYÊN CODE CŨ) ---
if (isset($_GET['action']) && $_GET['action'] == 'confirm' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $conn->beginTransaction(); 

        // 1. Kiểm tra xem đơn này đã được hoàn kho chưa?
        $stmtCheck = $conn->prepare("SELECT ly_do_huy, trang_thai FROM donhang WHERE id = :id");
        $stmtCheck->execute([':id' => $id]);
        $order = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        // Chỉ xử lý nếu trạng thái là 4 và CHƯA có chữ [Đã hoàn kho]
        if ($order && $order['trang_thai'] == 4 && strpos($order['ly_do_huy'], '[Đã hoàn kho]') === false) {
            
            // 2. LẤY SẢN PHẨM ĐỂ CỘNG KHO
            $sql_items = "SELECT sanpham_id, so_luong, mau, size FROM chitiet_donhang WHERE donhang_id = :id";
            $stmt_items = $conn->prepare($sql_items);
            $stmt_items->execute([':id' => $id]);
            $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            // 3. CỘNG KHO
            $sql_restock = "UPDATE bienthe_sanpham 
                            SET so_luong_ton = so_luong_ton + :qty 
                            WHERE sanpham_id = :sp_id 
                            AND TRIM(mau_sac) = TRIM(:mau) 
                            AND TRIM(kich_thuoc) = TRIM(:size)";
            $stmt_restock = $conn->prepare($sql_restock);
            
            $count = 0;
            foreach ($items as $item) {
                $stmt_restock->execute([
                    ':qty'   => $item['so_luong'],
                    ':sp_id' => $item['sanpham_id'],
                    ':mau'   => $item['mau'],
                    ':size'  => $item['size']
                ]);
                $count++;
            }

            // 4. ĐÁNH DẤU LÀ ĐÃ XONG
            $new_reason = "[Đã hoàn kho] " . $order['ly_do_huy'];
            $stmtUpdate = $conn->prepare("UPDATE donhang SET ly_do_huy = :new_reason WHERE id = :id");
            $stmtUpdate->execute([':new_reason' => $new_reason, ':id' => $id]);

            $conn->commit();
            
            // Giữ lại query string khi reload để không mất bộ lọc
            $query_string = $_SERVER['QUERY_STRING'];
            parse_str($query_string, $queryParams);
            unset($queryParams['action']);
            unset($queryParams['id']);
            $new_query = http_build_query($queryParams);

            echo "<script>alert('Đã cộng kho thành công cho $count sản phẩm!'); window.location.href='quanlydoitra.php" . ($new_query ? '?' . $new_query : '') . "';</script>";
        } else {
            $conn->rollBack();
            echo "<script>alert('Đơn này đã được hoàn kho rồi hoặc không hợp lệ!'); window.location.href='quanlydoitra.php';</script>";
        }

    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('Lỗi: ".$e->getMessage()."');</script>";
    }
}

// --- LẤY DANH SÁCH ĐƠN HỦY (CÓ BỘ LỌC) ---

// 1. Nhận tham số tìm kiếm
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// 2. Xây dựng câu SQL động
$sql = "SELECT * FROM donhang WHERE trang_thai = 4";
$params = [];

if (!empty($keyword)) {
    $sql .= " AND (ten_nguoi_nhan LIKE :keyword OR id = :id_search)";
    $params[':keyword'] = "%$keyword%";
    $params[':id_search'] = is_numeric($keyword) ? $keyword : -1;
}

if (!empty($date_from)) {
    $sql .= " AND DATE(ngay_dat) >= :date_from";
    $params[':date_from'] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND DATE(ngay_dat) <= :date_to";
    $params[':date_to'] = $date_to;
}

$sql .= " ORDER BY id DESC";

// 3. Thực thi
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="page-header">
        <h2 class="page-title" style="color:#e74c3c;">QUẢN LÝ YÊU CẦU TRẢ HÀNG / HỦY ĐƠN</h2>
        <a href="donhang.php" class="btn-back">Quay lại DS Đơn</a>
    </div>

    <div class="search-container">
        <form method="GET" action="" class="search-form">
            <div class="input-group">
                <label>Từ khóa:</label>
                <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Tên khách hoặc Mã ĐH...">
            </div>
            
            <div class="input-group">
                <label>Từ ngày:</label>
                <input type="date" name="date_from" value="<?php echo $date_from; ?>">
            </div>

            <div class="input-group">
                <label>Đến ngày:</label>
                <input type="date" name="date_to" value="<?php echo $date_to; ?>">
            </div>

            <div class="btn-group-search">
                <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
                <a href="quanlydoitra.php" class="btn-reset" title="Làm mới"><i class="fa-solid fa-rotate-right"></i></a>
            </div>
        </form>
    </div>
    <table class="table-data">
        <thead>
            <tr>
                <th>Mã ĐH</th>
                <th>Khách hàng</th>
                <th width="35%">Lý do hủy</th>
                <th>Tổng tiền</th>
                <th>Trạng thái kho</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($requests) > 0): ?>
                <?php foreach($requests as $row): ?>
                <?php 
                    // Kiểm tra xem đã hoàn kho chưa dựa vào text
                    $is_restocked = (strpos($row['ly_do_huy'], '[Đã hoàn kho]') !== false);
                ?>
                <tr>
                    <td><b>#<?php echo $row['id']; ?></b></td>
                    <td>
                        <div class="customer-info">
                            <span class="name"><?php echo htmlspecialchars($row['ten_nguoi_nhan']); ?></span><br>
                            <span class="phone"><?php echo htmlspecialchars($row['sdt_nguoi_nhan']); ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="reason-box">
                            <i class="fa-solid fa-quote-left"></i>
                            <?php echo htmlspecialchars($row['ly_do_huy']); ?>
                        </div>
                    </td>
                    <td style="color:#c0392b; font-weight:bold;">
                        <?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>đ
                    </td>
                    <td>
                        <?php if($is_restocked): ?>
                            <span class="badge badge-success" style="background:green; color:white; padding:4px 8px; border-radius:4px;">Đã cộng kho</span>
                        <?php else: ?>
                            <span class="badge badge-warning" style="background:orange; color:white; padding:4px 8px; border-radius:4px;">Chờ xử lý</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="xem_donhang.php?id=<?php echo $row['id']; ?>" class="btn-action btn-view" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></a>
                        
                        <?php if(!$is_restocked): ?>
                            <a href="?action=confirm&id=<?php echo $row['id']; ?>&<?php echo http_build_query($_GET); ?>" 
                               class="btn-action btn-approve" 
                               onclick="return confirm('Bạn có chắc chắn muốn nhận lại hàng và cộng vào kho?')"
                               title="Đồng ý hoàn kho">
                               <i class="fa-solid fa-check"></i> Duyệt
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">Không tìm thấy đơn hủy nào phù hợp.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
/* CSS CŨ GIỮ NGUYÊN */
.table-data { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.table-data th { background: #fee; color: #c0392b; padding: 10px; text-align: left; }
.table-data td { padding: 10px; border-bottom: 1px solid #eee; }
.btn-action { padding: 5px 10px; text-decoration: none; border-radius: 4px; color: #fff; display: inline-block; margin-right: 5px;}
.btn-view { background: #3498db; }
.btn-approve { background: #27ae60; font-weight: bold;}
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.btn-back { text-decoration: none; background: #555; color: #fff; padding: 8px 15px; border-radius: 4px; }

/* CSS MỚI CHO BỘ LỌC (SEARCH BAR) */
.search-container { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
.search-form { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; }
.search-form .input-group { display: flex; flex-direction: column; gap: 5px; }
.search-form label { font-size: 13px; font-weight: 600; color: #555; }
.search-form input[type="text"], .search-form input[type="date"] { padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; outline: none; height: 38px; box-sizing: border-box; }
.search-form input[type="text"] { width: 250px; }
.btn-group-search { display: flex; gap: 5px; }
.btn-search { background: #e74c3c; color: white; border: none; padding: 0 15px; height: 38px; border-radius: 5px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 5px; }
.btn-search:hover { background: #c0392b; }
.btn-reset { background: #ecf0f1; color: #7f8c8d; border: 1px solid #ddd; width: 38px; height: 38px; border-radius: 5px; display: flex; align-items: center; justify-content: center; text-decoration: none; }
.btn-reset:hover { background: #bdc3c7; color: #fff; }
</style>