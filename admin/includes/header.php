<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 1. KẾT NỐI CSDL ---
if (!isset($conn)) {
    if (file_exists('connect.php')) {
        require_once 'connect.php';
    } elseif (file_exists('../connect.php')) {
        require_once '../connect.php';
    }
}

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php"); 
    exit();
}

$admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';

// --- 2. LOGIC LẤY THÔNG BÁO BAN ĐẦU (Để hiện ngay khi load trang) ---
$new_msg = 0;
$list_notif = [];

try {
    if (isset($conn)) { 
        $stmt_count = $conn->query("SELECT COUNT(*) as solhuong FROM lienhe WHERE trang_thai = 0");
        $row_count = $stmt_count->fetch(PDO::FETCH_ASSOC);
        $new_msg = $row_count['solhuong'];

        $stmt_list = $conn->query("SELECT * FROM lienhe ORDER BY trang_thai ASC, ngay_gui DESC LIMIT 5");
        $list_notif = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    $new_msg = 0;
}
?>

<link rel="stylesheet" href="assets/style_admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<header class="admin-header">
    <a href="index.php" class="admin-logo">
        <i class="fa-solid fa-feather-pointed"></i> HBG ADMIN
    </a>

    <ul class="admin-menu">
        <li class="menu-item">
            <a href="index.php" class="menu-link"><i class="fa-solid fa-house"></i> Tổng quan</a>
        </li>
        <li class="menu-item">
            <a href="#" class="menu-link"><i class="fa-solid fa-box-open"></i> Sản phẩm <i class="fa-solid fa-chevron-down arrow-down"></i></a>
            <ul class="dropdown">
                <li><a href="sanpham.php">Tất cả danh mục</a></li>
                <li><a href="sanpham.php?danhmuc=1">Vợt cầu lông</a></li>
                <li><a href="sanpham.php?danhmuc=2">Giày cầu lông</a></li>
                <li><a href="sanpham.php?danhmuc=4">Quần áo thể thao</a></li>
                <li><a href="sanpham.php?danhmuc=3">Balo & Túi vợt</a></li>
                <li><a href="sanpham.php?danhmuc=5">Phụ kiện</a></li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="#" class="menu-link"><i class="fa-solid fa-warehouse"></i> Kho hàng <i class="fa-solid fa-chevron-down arrow-down"></i></a>
            <ul class="dropdown">
                <li><a href="ncc.php">Danh mục nhà cung cấp</a></li>
                <li><a href="tonkho.php">Xem tồn kho</a></li>
                <li><a href="nhapkho.php">Tạo phiếu nhập</a></li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="#" class="menu-link"><i class="fa-solid fa-box-open"></i> Đơn hàng <i class="fa-solid fa-chevron-down arrow-down"></i></a>
            <ul class="dropdown">
                <li><a href="donhang.php">Quản lý đơn hàng</a></li>
                <li><a href="doitra.php">Quản lý đổi trả</a></li>
                <li><a href="phieuxuat.php">Quản lý phiếu xuất</a></li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="#" class="menu-link"><i class="fa-solid fa-chart-line"></i> Thống kê <i class="fa-solid fa-chevron-down arrow-down"></i></a>
            <ul class="dropdown">
                <li><a href="doanhthu.php">Báo cáo doanh thu</a></li>
                <li><a href="baocao.php">Báo cáo lợi nhuận</a></li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="khachhang.php" class="menu-link"><i class="fa-solid fa-comments"></i> Khách hàng <i class="fa-solid fa-chevron-down arrow-down"></i></a>
            <ul class="dropdown">
                <li><a href="khachhang.php">Quản lý khách hàng</a></li>
                <li><a href="phanhoi.php">Phản hồi</a></li>
            </ul>
        </li>
    </ul>

    <div class="admin-right">
        
        <div class="notif-wrapper">
            <div class="notif-icon" onclick="toggleNotif(event)">
                <i class="fa-solid fa-bell"></i>
                <span class="badge" id="notifBadge" style="display: <?php echo ($new_msg > 0) ? 'flex' : 'none'; ?>">
                    <?php echo $new_msg; ?>
                </span>
            </div>

            <div class="notif-dropdown" id="notifBox">
                <div class="notif-header">
                    <span>Thông báo mới</span>
                    <small id="notifTextCount"><?php echo $new_msg; ?> tin mới</small>
                </div>
                
                <div class="notif-body" id="notifListBody">
                    <?php if(count($list_notif) > 0): ?>
                        <?php foreach($list_notif as $item): ?>
                            <?php 
                                $style_unread = ($item['trang_thai'] == 0) ? 'background:#fff3e0;' : ''; 
                            ?>
                            <a href="phanhoi.php?id=<?php echo $item['id']; ?>" class="notif-item" style="<?php echo $style_unread; ?>">
                                <div class="notif-item-top">
                                    <span class="notif-name"><?php echo htmlspecialchars($item['ho_ten']); ?></span>
                                    <span class="notif-time"><?php echo date('d/m H:i', strtotime($item['ngay_gui'])); ?></span>
                                </div>
                                <span class="notif-content">
                                    <?php echo htmlspecialchars($item['noi_dung']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: #999;">
                            <i class="fa-regular fa-envelope-open" style="font-size:20px; margin-bottom:5px;"></i><br>
                            Không có tin nhắn nào
                        </div>
                    <?php endif; ?>
                </div>

                <div class="notif-footer">
                    <a href="phanhoi.php">Xem tất cả phản hồi</a>
                </div>
            </div>
        </div>
        <div class="user-pill">
            <div class="user-text">
                <small>Xin chào,</small>
                <strong><?php echo htmlspecialchars($admin_name); ?></strong>
            </div>
            
            <a href="logout.php" class="btn-logout-circle" title="Đăng xuất">
                <i class="fa-solid fa-power-off"></i>
            </a>
        </div>
    </div>
</header>

<script>
    function toggleNotif(event) {
        event.stopPropagation();
        var box = document.getElementById("notifBox");
        box.classList.toggle("active");
    }

    window.onclick = function(event) {
        var box = document.getElementById("notifBox");
        if (!event.target.closest('.notif-wrapper')) {
            if (box && box.classList.contains('active')) {
                box.classList.remove('active');
            }
        }
    }

    // --- SCRIPT AJAX TỰ ĐỘNG CẬP NHẬT (MỚI THÊM VÀO) ---
    // Cứ 3 giây (3000ms) sẽ tự động chạy đoạn này
    setInterval(function() {
        fetch('get_notif.php') // Gọi file PHP lấy dữ liệu
        .then(response => response.json())
        .then(data => {
            // 1. Cập nhật số lượng đỏ (Badge)
            const badge = document.getElementById('notifBadge');
            const textCount = document.getElementById('notifTextCount');
            
            if (data.count > 0) {
                badge.innerText = data.count;
                badge.style.display = 'flex'; // Hiện badge
                textCount.innerText = data.count + ' tin mới';
            } else {
                badge.style.display = 'none'; // Ẩn badge
                textCount.innerText = '0 tin mới';
            }

            // 2. Cập nhật danh sách tin nhắn
            const listBody = document.getElementById('notifListBody');
            let htmlContent = '';

            if (data.list.length > 0) {
                data.list.forEach(item => {
                    // Xử lý định dạng ngày tháng
                    const dateObj = new Date(item.ngay_gui);
                    const dateStr = dateObj.getDate().toString().padStart(2,'0') + '/' + 
                                    (dateObj.getMonth()+1).toString().padStart(2,'0') + ' ' + 
                                    dateObj.getHours().toString().padStart(2,'0') + ':' + 
                                    dateObj.getMinutes().toString().padStart(2,'0');
                    
                    // Nếu chưa đọc (trang_thai = 0) thì có nền màu cam nhạt
                    const bgStyle = (item.trang_thai == 0) ? 'background:#fff3e0;' : '';

                    // Tạo HTML cho từng dòng tin nhắn
                    htmlContent += `
                        <a href="phanhoi.php?id=${item.id}" class="notif-item" style="${bgStyle}">
                            <div class="notif-item-top">
                                <span class="notif-name">${escapeHtml(item.ho_ten)}</span>
                                <span class="notif-time">${dateStr}</span>
                            </div>
                            <span class="notif-content">${escapeHtml(item.noi_dung)}</span>
                        </a>
                    `;
                });
            } else {
                htmlContent = `
                    <div style="padding: 20px; text-align: center; color: #999;">
                        <i class="fa-regular fa-envelope-open" style="font-size:20px; margin-bottom:5px;"></i><br>
                        Không có tin nhắn nào
                    </div>
                `;
            }
            // Gán HTML mới vào hộp thông báo
            listBody.innerHTML = htmlContent;
        })
        .catch(error => console.error('Error:', error));
    }, 3000); // 3000ms = 3 giây

    // Hàm phụ để bảo mật, chống lỗi hiển thị khi có ký tự lạ
    function escapeHtml(text) {
        if (!text) return "";
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>