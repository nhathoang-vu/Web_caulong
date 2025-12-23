<?php
// =================================================================================
// === THÊM NHÀ CUNG CẤP (FIXED REDIRECT) ==========================================
// =================================================================================
session_start(); // <--- BẮT BUỘC ĐỂ LƯU THÔNG BÁO

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../connect.php'; 

if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    else if (isset($db)) $conn = $db;
}

$error_msg = "";

if (isset($_POST['btn_add'])) {
    try {
        // --- LẤY DỮ LIỆU INPUT ---
        $ten_ncc       = $_POST['ten_thuonghieu'];
        $email         = $_POST['email'];
        $hotline       = $_POST['so_dien_thoai'];
        $nguoi_lien_he = $_POST['nguoi_lien_he'];
        $sdt_lien_he   = $_POST['sdt_nguoi_lien_he'];
        $dia_chi       = $_POST['dia_chi'];

        // --- XỬ LÝ ẢNH LOGO ---
        $logo = "";
        if (isset($_FILES['logo']) && $_FILES['logo']['name'] != "") {
            $target_dir = "assets/img/"; 
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true); 
            
            $file_extension = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
            $new_img_name = "brand_" . time() . "." . $file_extension;
            
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_dir . $new_img_name)) {
                $logo = $new_img_name;
            }
        }

        // --- INSERT VÀO DATABASE ---
        $sql = "INSERT INTO thuonghieu (ten_thuonghieu, logo, dia_chi, so_dien_thoai, email, nguoi_lien_he, sdt_nguoi_lien_he) 
                VALUES (:ten, :logo, :dc, :sdt, :email, :nlh, :sdt_lh)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':ten'    => $ten_ncc, 
            ':logo'   => $logo, 
            ':dc'     => $dia_chi,
            ':sdt'    => $hotline, 
            ':email'  => $email, 
            ':nlh'    => $nguoi_lien_he,
            ':sdt_lh' => $sdt_lien_he
        ]);
        
        // --- THÀNH CÔNG: TẠO SESSION VÀ CHUYỂN HƯỚNG NGAY ---
        $_SESSION['success_msg'] = "Thêm nhà cung cấp mới thành công!";
        header("Location: ncc.php"); 
        exit(); // Dừng code tại đây để chuyển trang

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
    <title>Thêm Nhà cung cấp mới</title>
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
        <h3 class="page-title">Thêm Nhà cung cấp mới</h3>
        <div style="width: 150px;"></div>
    </div>

    <div class="form-container">
        
        <?php if(!empty($error_msg)): ?>
            <div class="alert-error">
                <strong>Không thêm được!</strong> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="addNCCForm">
            
            <div class="form-row">
                <div class="form-col" style="flex: 2;">
                    <div class="form-group">
                        <label class="form-label">Tên Nhà cung cấp <span style="color:red">*</span></label>
                        <input type="text" name="ten_thuonghieu" class="form-control" required placeholder="Nhập tên công ty/nhãn hàng...">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Email công ty</label>
                        <input type="email" name="email" class="form-control" placeholder="company@example.com">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Hotline Công ty</label>
                        <input type="text" name="so_dien_thoai" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Người đại diện</label>
                        <input type="text" name="nguoi_lien_he" class="form-control" placeholder="Tên người liên hệ...">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">SĐT Cá nhân</label>
                        <input type="text" name="sdt_nguoi_lien_he" class="form-control" placeholder="">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col" style="flex: 2;">
                    <div class="form-group">
                        <label class="form-label">Địa chỉ trụ sở</label>
                        <textarea name="dia_chi" class="form-control" 
                                  style="height: 187px !important; min-height: 187px !important; max-height: 187px !important; resize: none;" 
                                  placeholder="Nhập địa chỉ chi tiết..."></textarea>
                    </div>
                </div>
                
                <div class="form-col" style="flex: 1;">
                    <div class="form-group">
                        <label class="form-label">Logo thương hiệu</label>
                        <input type="file" name="logo" id="imageInput" class="form-control" accept="image/*" style="padding: 9px;">
                    </div>
                    <div class="form-group" style="margin-top: 10px;">
                        <div class="image-preview-container" style="height: 100px;"> 
                            <span class="no-image-text">Chưa chọn logo</span>
                            <img id="imagePreview" class="image-preview" src="#" alt="Xem trước logo">
                        </div>
                    </div>
                </div>
            </div>

            <div style="text-align: right; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <button type="submit" name="btn_add" class="btn-submit">
                    + Thêm mới
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
        } else {
            imagePreview.src = '#';
            imagePreview.style.display = 'none';
            noImageText.style.display = 'inline';
        }
    });
</script>

</body>
</html>