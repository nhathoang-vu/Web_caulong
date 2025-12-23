<?php
// =================================================================================
// === SỬA NHÀ CUNG CẤP (FIX REDIRECT PHP) ========================================
// =================================================================================
session_start(); // <--- BẮT BUỘC ĐỂ LƯU SESSION THÔNG BÁO

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../connect.php'; 

if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    else if (isset($db)) $conn = $db;
}

// --- 1. KIỂM TRA ID ---
if (!isset($_GET['id'])) {
    header("Location: ncc.php"); // Không có ID thì về danh sách
    exit();
}

$id = $_GET['id'];
$error_msg = "";

// --- 2. LẤY DỮ LIỆU CŨ ---
try {
    $stmt_get = $conn->prepare("SELECT * FROM thuonghieu WHERE id = :id");
    $stmt_get->execute([':id' => $id]);
    $row = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("Không tìm thấy nhà cung cấp này!");
    }
} catch (Exception $e) {
    die("Lỗi: " . $e->getMessage());
}

// --- 3. XỬ LÝ FORM SUBMIT ---
if (isset($_POST['btn_update'])) {
    try {
        // Lấy dữ liệu input
        $ten_ncc       = $_POST['ten_thuonghieu'];
        $email         = $_POST['email'];
        $hotline       = $_POST['so_dien_thoai'];
        $nguoi_lien_he = $_POST['nguoi_lien_he'];
        $sdt_lien_he   = $_POST['sdt_nguoi_lien_he'];
        $dia_chi       = $_POST['dia_chi'];

        // Mặc định giữ logo cũ
        $logo = $row['logo'];

        // Kiểm tra nếu có upload ảnh mới
        if (isset($_FILES['logo']) && $_FILES['logo']['name'] != "") {
            $target_dir = "assets/img/"; 
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true); 
            
            $file_extension = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
            $new_img_name = "brand_" . time() . "." . $file_extension;
            
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_dir . $new_img_name)) {
                $logo = $new_img_name; // Cập nhật tên ảnh mới
            }
        }

        // Update vào DB
        $sql = "UPDATE thuonghieu SET 
                ten_thuonghieu = :ten,
                logo = :logo,
                dia_chi = :dc,
                so_dien_thoai = :sdt,
                email = :email,
                nguoi_lien_he = :nlh,
                sdt_nguoi_lien_he = :sdt_lh
                WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':ten'    => $ten_ncc, 
            ':logo'   => $logo, 
            ':dc'     => $dia_chi,
            ':sdt'    => $hotline, 
            ':email'  => $email, 
            ':nlh'    => $nguoi_lien_he,
            ':sdt_lh' => $sdt_lien_he,
            ':id'     => $id
        ]);
        
        // --- THÀNH CÔNG: TẠO SESSION VÀ CHUYỂN HƯỚNG NGAY ---
        $_SESSION['success_msg'] = "Cập nhật nhà cung cấp thành công!";
        header("Location: ncc.php"); 
        exit(); // Dừng code tại đây

    } catch (Exception $e) {
        $error_msg = "Lỗi hệ thống: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Nhà cung cấp</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .alert-error {
            background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; 
            border: 1px solid #f5c6cb; border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="wrap-content">
    
    <div class="form-header">
        <a href="ncc.php" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            Quay lại
        </a>
        <h3 class="page-title">Cập nhật Nhà cung cấp</h3>
        <div style="width: 150px;"></div>
    </div>

    <div class="form-container">
        
        <?php if(!empty($error_msg)): ?>
            <div class="alert-error">
                <strong>Lỗi!</strong> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="editNCCForm">
            
            <div class="form-row">
                <div class="form-col" style="flex: 2;">
                    <div class="form-group">
                        <label class="form-label">Tên Nhà cung cấp <span style="color:red">*</span></label>
                        <input type="text" name="ten_thuonghieu" class="form-control" required 
                               value="<?php echo htmlspecialchars($row['ten_thuonghieu']); ?>"
                               placeholder="Nhập tên công ty/nhãn hàng...">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Email công ty</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($row['email']); ?>"
                               placeholder="company@example.com">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Hotline Công ty</label>
                        <input type="text" name="so_dien_thoai" class="form-control" 
                               value="<?php echo htmlspecialchars($row['so_dien_thoai']); ?>"
                               placeholder="">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Người đại diện</label>
                        <input type="text" name="nguoi_lien_he" class="form-control" 
                               value="<?php echo htmlspecialchars($row['nguoi_lien_he']); ?>"
                               placeholder="Tên người liên hệ...">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">SĐT Cá nhân</label>
                        <input type="text" name="sdt_nguoi_lien_he" class="form-control" 
                               value="<?php echo htmlspecialchars($row['sdt_nguoi_lien_he']); ?>"
                               placeholder="">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col" style="flex: 2;">
                    <div class="form-group">
                        <label class="form-label">Địa chỉ trụ sở</label>
                        <textarea name="dia_chi" class="form-control" 
                                  style="height: 187px !important; min-height: 187px !important; max-height: 187px !important; resize: none;" 
                                  placeholder="Nhập địa chỉ chi tiết..."><?php echo htmlspecialchars($row['dia_chi']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-col" style="flex: 1;">
                    <div class="form-group">
                        <label class="form-label">Logo thương hiệu</label>
                        <input type="file" name="logo" id="imageInput" class="form-control" accept="image/*" style="padding: 9px;">
                    </div>
                    <div class="form-group" style="margin-top: 10px;">
                        <div class="image-preview-container" style="height: 100px;"> 
                            
                            <?php if(!empty($row['logo'])): ?>
                                <span class="no-image-text" style="display: none;">Chưa chọn logo</span>
                                <img id="imagePreview" class="image-preview" src="assets/img/<?php echo $row['logo']; ?>" alt="Xem trước logo" style="display: block;">
                            <?php else: ?>
                                <span class="no-image-text">Chưa chọn logo</span>
                                <img id="imagePreview" class="image-preview" src="#" alt="Xem trước logo" style="display: none;">
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>

            <div style="text-align: right; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <button type="submit" name="btn_update" class="btn-submit">
                    Lưu thay đổi
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const noImageText = document.querySelector('.no-image-text');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                noImageText.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    });
</script>

</body>
</html>