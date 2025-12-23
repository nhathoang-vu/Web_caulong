<?php
// =================================================================================
// === CODE CHỐT: THÊM SP -> GÁN SESSION -> CHUYỂN HƯỚNG VỀ SANPHAM.PHP ============
// =================================================================================

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. START SESSION ĐỂ TRUYỀN THÔNG BÁO SANG TRANG DANH SÁCH
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../connect.php'; 

if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    else if (isset($db)) $conn = $db;
}

// 2. Lấy danh mục & Thương hiệu cho Select box
$stmt_dm = $conn->prepare("SELECT * FROM danhmuc");
$stmt_dm->execute();
$list_dm = $stmt_dm->fetchAll(PDO::FETCH_ASSOC);

$stmt_th = $conn->prepare("SELECT * FROM thuonghieu");
$stmt_th->execute();
$list_th = $stmt_th->fetchAll(PDO::FETCH_ASSOC);

$error_msg = "";

// 3. XỬ LÝ KHI BẤM LƯU
if (isset($_POST['btn_add'])) {
    try {
        // --- LẤY DỮ LIỆU INPUT ---
        $ten_sp        = $_POST['ten_sanpham'];
        $danhmuc_id    = $_POST['danhmuc_id'];
        $thuonghieu_id = !empty($_POST['thuonghieu_id']) ? $_POST['thuonghieu_id'] : null;
        $gia_nhap      = !empty($_POST['gia_nhap']) ? $_POST['gia_nhap'] : 0;
        $gia_ban       = !empty($_POST['gia_ban']) ? $_POST['gia_ban'] : 0;
        $gia_km        = !empty($_POST['gia_khuyenmai']) ? $_POST['gia_khuyenmai'] : 0;
        $mo_ta         = $_POST['mo_ta'];
        
        $str_mau_sac    = isset($_POST['mau_sac']) ? $_POST['mau_sac'] : '';
        $str_kich_thuoc = isset($_POST['kich_thuoc']) ? $_POST['kich_thuoc'] : '';

        // --- XỬ LÝ ẢNH ---
        $hinh_anh = "";
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['name'] != "") {
            $target_dir = "anh_sanpham/"; 
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true); 
            
            $hinh_anh = basename($_FILES["hinh_anh"]["name"]);
            
            if (!move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_dir . $hinh_anh)) {
                throw new Exception("Không thể lưu file ảnh. Kiểm tra quyền ghi thư mục.");
            }
        }

        $conn->beginTransaction(); 

        // --- BƯỚC 1: INSERT VÀO BẢNG SANPHAM ---
        $sql = "INSERT INTO sanpham (ten_sanpham, danhmuc_id, thuonghieu_id, gia_nhap, gia_ban, gia_khuyenmai, hinh_anh, mo_ta) 
                VALUES (:ten, :dm, :th, :gn, :gb, :gkm, :img, :mt)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':ten' => $ten_sp, 
            ':dm'  => $danhmuc_id, 
            ':th'  => $thuonghieu_id,
            ':gn'  => $gia_nhap, 
            ':gb'  => $gia_ban, 
            ':gkm' => $gia_km,
            ':img' => $hinh_anh, 
            ':mt'  => $mo_ta
        ]);
        
        // --- BƯỚC 2: LẤY ID CỦA SẢN PHẨM VỪA TẠO ---
        $new_sp_id = $conn->lastInsertId();

        // --- BƯỚC 3: TẠO BIẾN THỂ (KHÔNG CÓ CỘT SO_LUONG) ---
        $arr_mau  = array_filter(array_map('trim', explode(',', $str_mau_sac)));
        $arr_size = array_filter(array_map('trim', explode(',', $str_kich_thuoc)));

        if (empty($arr_mau))  $arr_mau = ['']; 
        if (empty($arr_size)) $arr_size = [''];

        $sql_variant = "INSERT INTO bienthe_sanpham (sanpham_id, mau_sac, kich_thuoc) VALUES (:sp_id, :mau, :size)";
        $stmt_var = $conn->prepare($sql_variant);

        foreach ($arr_mau as $mau) {
            foreach ($arr_size as $size) {
                $stmt_var->execute([
                    ':sp_id' => $new_sp_id,
                    ':mau'   => $mau,
                    ':size'  => $size
                ]);
            }
        }

        $conn->commit(); 
        
        // ============================================================
        // === LOGIC MỚI: GÁN SESSION VÀ CHUYỂN HƯỚNG NGAY ===
        // ============================================================
        $_SESSION['success_msg'] = "Đã thêm mới sản phẩm <b>$ten_sp</b> thành công!";
        header("Location: sanpham.php");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        $error_msg = "Lỗi hệ thống: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm mới</title>
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
        <a href="sanpham.php" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            Quay lại
        </a>
        <h3 class="page-title">Thêm sản phẩm mới</h3>
        <div style="width: 100px;"></div>
    </div>

    <div class="form-container">
        
        <?php if(!empty($error_msg)): ?>
            <div class="alert-error">
                <strong>Không thêm được!</strong> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="addProductForm">
            
            <div class="form-row">
                <div class="form-col" style="flex: 2;">
                    <div class="form-group">
                        <label class="form-label">Tên sản phẩm <span style="color:red">*</span></label>
                        <input type="text" name="ten_sanpham" class="form-control" required placeholder="Nhập tên sản phẩm...">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Danh mục <span style="color:red">*</span></label>
                        <select name="danhmuc_id" class="form-control" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach($list_dm as $dm): ?>
                                <option value="<?php echo $dm['id']; ?>"><?php echo $dm['ten_danhmuc']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Giá nhập</label>
                        <input type="number" name="gia_nhap" class="form-control" placeholder="0" min="0">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Giá bán <span style="color:red">*</span></label>
                        <input type="number" name="gia_ban" class="form-control" required placeholder="0" min="0">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Giá khuyến mãi</label>
                        <input type="number" name="gia_khuyenmai" class="form-control" placeholder="0" min="0">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Thương hiệu</label>
                        <select name="thuonghieu_id" class="form-control">
                            <option value="">-- Chọn hãng (Không bắt buộc) --</option>
                            <?php foreach($list_th as $th): ?>
                                <option value="<?php echo $th['id']; ?>"><?php echo $th['ten_thuonghieu']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Màu sắc</label>
                        <input type="text" name="mau_sac" class="form-control" placeholder="VD: Đỏ, Xanh (ngăn cách dấu phẩy)">
                    </div>
                </div>
                
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Kích thước</label>
                        <input type="text" name="kich_thuoc" class="form-control" placeholder="VD: S, M, L (ngăn cách dấu phẩy)">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col" style="flex: 1.5;">
                    <div class="form-group">
                        <label class="form-label">Hình ảnh sản phẩm</label>
                        <input type="file" name="hinh_anh" id="imageInput" class="form-control" accept="image/*" style="padding: 9px;">
                    </div>
                </div>
                <div class="form-col" style="flex: 1;">
                    <div class="form-group">
                        <label class="form-label">Xem trước ảnh</label>
                        <div class="image-preview-container">
                            <span class="no-image-text">Chưa chọn ảnh</span>
                            <img id="imagePreview" class="image-preview" src="#" alt="Ảnh xem trước">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Mô tả chi tiết</label>
                <textarea name="mo_ta" class="form-control" placeholder="Nhập thông tin chi tiết về sản phẩm..." rows="15"></textarea>
            </div>

            <div style="text-align: right; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <button type="submit" name="btn_add" class="btn-submit">
                    Lưu
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