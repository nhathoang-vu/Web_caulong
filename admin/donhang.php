<?php
// admin/donhang.php
require_once '../connect.php';
include 'includes/header.php';

// --- PHẦN 1: XỬ LÝ TRẠNG THÁI (GIỮ NGUYÊN KHÔNG ĐỔI) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    try {
        // A. DUYỆT ĐƠN (0 -> 1)
        if ($action == 'confirm') {
            $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 1 WHERE id = :id AND trang_thai = 0");
            $stmt->execute([':id' => $id]);
        }
        // B. GIAO HÀNG (1 -> 2)
        elseif ($action == 'shipping') {
            $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 2 WHERE id = :id AND trang_thai = 1");
            $stmt->execute([':id' => $id]);
        } 
        // C. HOÀN TẤT (2 -> 3)
        elseif ($action == 'complete') {
            $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 3 WHERE id = :id AND trang_thai = 2");
            $stmt->execute([':id' => $id]);
        } 
        // D. HỦY ĐƠN (4 & Hoàn kho)
        elseif ($action == 'cancel') {
            $stmt_check = $conn->prepare("SELECT trang_thai FROM donhang WHERE id = :id");
            $stmt_check->execute([':id' => $id]);
            $stt = $stmt_check->fetchColumn();

            if ($stt != 3 && $stt != 4) {
                $sql_dt = "SELECT sanpham_id, so_luong, mau_sac, kich_thuoc FROM chitiet_donhang WHERE donhang_id = :id";
                $stmt_dt = $conn->prepare($sql_dt);
                $stmt_dt->execute([':id' => $id]);
                $items = $stmt_dt->fetchAll(PDO::FETCH_ASSOC);

                $stmt = $conn->prepare("UPDATE donhang SET trang_thai = 4, ly_do_huy = 'Admin hủy trực tiếp' WHERE id = :id");
                $stmt->execute([':id' => $id]);

                if ($stmt->rowCount() > 0) {
                    $sql_kho = "UPDATE bienthe_sanpham 
                                SET so_luong_ton = so_luong_ton + :sl 
                                WHERE sanpham_id = :sp AND TRIM(mau_sac) = TRIM(:mau) AND TRIM(kich_thuoc) = TRIM(:size)";
                    $stmt_kho = $conn->prepare($sql_kho);
                    
                    foreach ($items as $item) {
                        $stmt_kho->execute([
                            ':sl'   => $item['so_luong'],
                            ':sp'   => $item['sanpham_id'],
                            ':mau'  => $item['mau_sac'],
                            ':size' => $item['kich_thuoc']
                        ]);
                    }
                }
            }
        }
        
        // Giữ lại các tham số tìm kiếm khi reload trang để không bị mất kết quả lọc
        $query_string = $_SERVER['QUERY_STRING'];
        // Loại bỏ action và id khỏi query string cũ để tránh lặp lại hành động
        parse_str($query_string, $queryParams);
        unset($queryParams['action']);
        unset($queryParams['id']);
        $new_query = http_build_query($queryParams);
        
        echo "<script>window.location.href='donhang.php" . ($new_query ? '?' . $new_query : '') . "';</script>";
        exit;

    } catch (Exception $e) {
        echo "<script>alert('Lỗi: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}

// --- PHẦN 2: LẤY DANH SÁCH ĐƠN HÀNG (CÓ TÌM KIẾM) ---

// Khởi tạo biến tìm kiếm
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Câu truy vấn cơ bản
$sql = "SELECT * FROM donhang WHERE trang_thai IN (0, 1, 2, 3, 4)";
$params = [];

// Thêm điều kiện tìm kiếm từ khóa (Tên khách hoặc Mã đơn)
if (!empty($keyword)) {
    $sql .= " AND (ten_nguoi_nhan LIKE :keyword OR id = :id_search)";
    $params[':keyword'] = "%$keyword%";
    $params[':id_search'] = is_numeric($keyword) ? $keyword : -1; // Chỉ tìm ID nếu nhập số
}

// Thêm điều kiện ngày bắt đầu
if (!empty($date_from)) {
    $sql .= " AND DATE(ngay_dat) >= :date_from";
    $params[':date_from'] = $date_from;
}

// Thêm điều kiện ngày kết thúc
if (!empty($date_to)) {
    $sql .= " AND DATE(ngay_dat) <= :date_to";
    $params[':date_to'] = $date_to;
}

// Sắp xếp
$sql .= " ORDER BY ngay_dat DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="page-header">
        <h2 class="page-title">QUẢN LÝ ĐƠN HÀNG</h2>
        
        <div class="header-buttons">
            <a href="phieuxuat.php" class="btn-link-gray">Quản lý Phiếu In</a>
            <a href="quanlydoitra.php" class="btn-link-cancel">Yêu cầu Đổi/Trả</a>
        </div>
    </div>

    <div class="search-container">
        <form method="GET" action="" class="search-form">
            <div class="input-group">
                <label>Từ khóa:</label>
                <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Tên khách hàng hoặc Mã ĐH...">
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
                <a href="donhang.php" class="btn-reset" title="Làm mới"><i class="fa-solid fa-rotate-right"></i></a>
            </div>
        </form>
    </div>
    <table class="table-data">
        <thead>
            <tr>
                <th>Mã ĐH</th>
                <th>Khách hàng</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th width="10%" class="text-center">Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if(count($orders) > 0): ?>
            <?php foreach($orders as $row): ?>
            <tr>
                <td><b>#<?php echo $row['id']; ?></b></td>
                <td>
                    <div class="customer-name"><?php echo htmlspecialchars($row['ten_nguoi_nhan']); ?></div>
                    <div class="customer-phone"><?php echo htmlspecialchars($row['sdt_nguoi_nhan']); ?></div>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['ngay_dat'])); ?></td>
                <td class="price-col"><?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>đ</td>
                
                <td class="action-col">
                    <div class="cell-wrapper">
                        <div class="status-part">
                            <?php
                                if($row['trang_thai'] == 0) echo '<span class="badge badge-warning">Mới đặt</span>';
                                elseif($row['trang_thai'] == 1) echo '<span class="badge badge-new">Đã xác nhận</span>';
                                elseif($row['trang_thai'] == 2) echo '<span class="badge badge-primary">Đang giao</span>';
                                elseif($row['trang_thai'] == 3) echo '<span class="badge badge-success">Hoàn tất</span>';
                                elseif($row['trang_thai'] == 4) echo '<span class="badge badge-danger">Đã hủy</span>';
                            ?>
                        </div>

                        <div class="buttons-part">
                            <a href="xem_donhang.php?id=<?php echo $row['id']; ?>" class="btn-action btn-view" title="Xem chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            <?php if($row['trang_thai'] == 0): ?>
                                <a href="?action=confirm&id=<?php echo $row['id']; ?>&<?php echo http_build_query($_GET); ?>" class="btn-action btn-confirm" onclick="return confirm('Duyệt đơn hàng này?')" title="Duyệt đơn">
                                    <i class="fa-solid fa-check"></i>
                                </a>
                                <a href="?action=cancel&id=<?php echo $row['id']; ?>&<?php echo http_build_query($_GET); ?>" class="btn-action btn-cancel" onclick="return confirm('Hủy đơn hàng này?')" title="Hủy đơn">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>

                            <?php elseif($row['trang_thai'] == 1): ?>
                                <a href="?action=shipping&id=<?php echo $row['id']; ?>&<?php echo http_build_query($_GET); ?>" class="btn-action btn-ship" onclick="return confirm('Xác nhận giao hàng?')" title="Giao hàng">
                                    <i class="fa-solid fa-truck"></i>
                                </a>
                                <a href="?action=cancel&id=<?php echo $row['id']; ?>&<?php echo http_build_query($_GET); ?>" class="btn-action btn-cancel" onclick="return confirm('Hủy đơn và hoàn kho ngay lập tức?')" title="Hủy đơn">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                                <a href="xem_donhang.php?id=<?php echo $row['id']; ?>" class="btn-action btn-print" title="In phiếu">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            
                            <?php elseif($row['trang_thai'] == 2): ?>
                                <a href="?action=complete&id=<?php echo $row['id']; ?>&<?php echo http_build_query($_GET); ?>" class="btn-action btn-done" onclick="return confirm('Khách đã nhận hàng?')" title="Hoàn tất">
                                    <i class="fa-solid fa-check-double"></i>
                                </a>
                                <a href="?action=cancel&id=<?php echo $row['id']; ?>&<?php echo http_build_query($_GET); ?>" class="btn-action btn-cancel" onclick="return confirm('Hủy đơn và hoàn kho?')" title="Hủy đơn">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                                <a href="xem_donhang.php?id=<?php echo $row['id']; ?>" class="btn-action btn-print" title="In phiếu">
                                    <i class="fa-solid fa-print"></i>
                                </a>

                            <?php elseif($row['trang_thai'] == 3): ?>
                                    <a href="xem_donhang.php?id=<?php echo $row['id']; ?>" class="btn-action btn-print" title="In phiếu">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center; padding: 20px;">Không tìm thấy đơn hàng nào phù hợp.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
/* CSS GIỮ NGUYÊN TỪ CODE CŨ */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.header-buttons { display: flex; gap: 10px; }
.btn-link-cancel { text-decoration: none; color: #fff; background: #e74c3c; font-weight: bold; font-size: 13px; padding: 8px 15px; border-radius: 5px; transition: 0.3s; display: flex; align-items: center; gap: 5px; }
.btn-link-cancel:hover { background: #c0392b; }
.btn-link-gray { text-decoration: none; color: #fff; background: #555; font-weight: bold; font-size: 13px; padding: 8px 15px; border-radius: 5px; transition: 0.3s; display: flex; align-items: center; gap: 5px; }
.btn-link-gray:hover { background: #333; }
.table-data { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
.table-data th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #eee; color: #555; font-size: 14px; }
.table-data td { padding: 8px 12px; border-bottom: 1px solid #eee; vertical-align: middle; font-size: 14px; }
.table-data tr:hover { background-color: #fcfcfc; }
.cell-wrapper { display: flex; justify-content: space-between; align-items: center; width: 100%; }
.status-part { flex-shrink: 0; margin-right: 10px; }
.buttons-part { display: flex; justify-content: flex-end; gap: 4px; flex-wrap: nowrap; }
.badge { padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; display: inline-block; color: #fff; min-width: 85px; text-align: center; }
.badge-warning { background: #f1c40f; color: #333; } 
.badge-new { background: #3498db; } 
.badge-primary { background: #2980b9; } 
.badge-success { background: #2ecc71; } 
.badge-danger { background: #e74c3c; }
.btn-action { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 4px; text-decoration: none; transition: 0.2s; color: #fff; font-size: 12px; }
.btn-view { background: #7f8c8d; }
.btn-confirm { background: #27ae60; } 
.btn-ship { background: #f39c12; }
.btn-done { background: #27ae60; }
.btn-cancel { background: #e74c3c; }
.btn-print { background: #2c3e50; }
.btn-action:hover { opacity: 0.9; transform: translateY(-1px); }

/* --- CSS MỚI CHO THANH TÌM KIẾM --- */
.search-container {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.search-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end; /* Căn đáy để input và button thẳng hàng */
}

.search-form .input-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.search-form label {
    font-size: 13px;
    font-weight: 600;
    color: #555;
}

.search-form input[type="text"],
.search-form input[type="date"] {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    outline: none;
    transition: 0.3s;
    height: 38px; /* Cố định chiều cao */
    box-sizing: border-box;
}

.search-form input[type="text"] {
    width: 250px;
}

.search-form input:focus {
    border-color: #3498db;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.2);
}

.btn-group-search {
    display: flex;
    gap: 5px;
}

.btn-search {
    background: #3498db;
    color: white;
    border: none;
    padding: 0 15px;
    height: 38px;
    border-radius: 5px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: 0.3s;
}

.btn-search:hover {
    background: #2980b9;
}

.btn-reset {
    background: #ecf0f1;
    color: #7f8c8d;
    border: 1px solid #ddd;
    width: 38px;
    height: 38px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: 0.3s;
}

.btn-reset:hover {
    background: #bdc3c7;
    color: #fff;
}
</style>