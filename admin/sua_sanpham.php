<?php
// =================================================================================
// === CODE CHỐT: SỬA SẢN PHẨM (LOGIC TÁCH BẢNG + SYNC BIẾN THỂ) ===================
// =================================================================================

// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../connect.php'; 

if (!isset($conn)) {
    if (isset($connect)) $conn = $connect;
    else if (isset($db)) $conn = $db;
}

// 1. KIỂM TRA ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: sanpham.php");
    exit;
}

$id = $_GET['id'];

// 2. LẤY DỮ LIỆU SẢN PHẨM (Thông tin chung)
$stmt = $conn->prepare("SELECT * FROM sanpham WHERE id = :id");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location='sanpham.php';</script>";
    exit;
}

// --- MỚI: LẤY DỮ LIỆU BIẾN THỂ ĐỂ HIỂN THỊ RA Ô INPUT ---
// Lấy danh sách màu
$stmt_mau = $conn->prepare("SELECT DISTINCT mau_sac FROM bienthe_sanpham WHERE sanpham_id = :id AND mau_sac != ''");
$stmt_mau->execute([':id' => $id]);
$arr_mau_db = $stmt_mau->fetchAll(PDO::FETCH_COLUMN);
$str_mau_hien_tai = implode(', ', $arr_mau_db); // VD: "Đỏ, Xanh"

// Lấy danh sách size
$stmt_size = $conn->prepare("SELECT DISTINCT kich_thuoc FROM bienthe_sanpham WHERE sanpham_id = :id AND kich_thuoc != ''");
$stmt_size->execute([':id' => $id]);
$arr_size_db = $stmt_size->fetchAll(PDO::FETCH_COLUMN);
$str_size_hien_tai = implode(', ', $arr_size_db); // VD: "L, XL"


// 3. LẤY DANH MỤC & THƯƠNG HIỆU
$stmt_dm = $conn->prepare("SELECT * FROM danhmuc");
$stmt_dm->execute();
$list_dm = $stmt_dm->fetchAll(PDO::FETCH_ASSOC);

$stmt_th = $conn->prepare("SELECT * FROM thuonghieu");
$stmt_th->execute();
$list_th = $stmt_th->fetchAll(PDO::FETCH_ASSOC);

$msg_success = false;
$error_msg = "";

// 4. XỬ LÝ KHI BẤM LƯU
if (isset($_POST['btn_update'])) {
    try {
        $ten_sp        = $_POST['ten_sanpham'];
        $danhmuc_id    = $_POST['danhmuc_id'];
        $thuonghieu_id = !empty($_POST['thuonghieu_id']) ? $_POST['thuonghieu_id'] : null;
        $gia_nhap      = !empty($_POST['gia_nhap']) ? $_POST['gia_nhap'] : 0;
        $gia_ban       = !empty($_POST['gia_ban']) ? $_POST['gia_ban'] : 0;
        $gia_km        = !empty($_POST['gia_khuyenmai']) ? $_POST['gia_khuyenmai'] : 0;
        $mo_ta         = $_POST['mo_ta'];
        
        // Lấy chuỗi màu/size mới từ input
        $str_mau_new  = isset($_POST['mau_sac']) ? $_POST['mau_sac'] : '';
        $str_size_new = isset($_POST['kich_thuoc']) ? $_POST['kich_thuoc'] : '';

        // Xử lý ảnh
        $hinh_anh = $row['hinh_anh']; // Mặc định giữ ảnh cũ
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['name'] != "") {
            $target_dir = "anh_sanpham/"; 
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true); 
            $new_img_name = basename($_FILES["hinh_anh"]["name"]);
            if(move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_dir . $new_img_name)){
                $hinh_anh = $new_img_name;
            }
        }

        $conn->beginTransaction();

        // --- BƯỚC A: UPDATE BẢNG SANPHAM (Thông tin chung) ---
        // Lưu ý: Đã bỏ cột thong_so, kich_thuoc trong bảng này
        $sql = "UPDATE sanpham SET 
                ten_sanpham = :ten, 
                danhmuc_id = :dm, 
                thuonghieu_id = :th, 
                gia_nhap = :gn, 
                gia_ban = :gb, 
                gia_khuyenmai = :gkm, 
                hinh_anh = :img, 
                mo_ta = :mt 
                WHERE id = :id";

        $stmt_update = $conn->prepare($sql);
        $stmt_update->execute([
            ':ten' => $ten_sp, ':dm' => $danhmuc_id, ':th' => $thuonghieu_id,
            ':gn' => $gia_nhap, ':gb' => $gia_ban, ':gkm' => $gia_km,
            ':img' => $hinh_anh, ':mt' => $mo_ta, ':id' => $id
        ]);

        // --- BƯỚC B: CẬP NHẬT BIẾN THỂ (XÓA CŨ -> TẠO MỚI) ---
        // 1. Xóa toàn bộ biến thể cũ của sản phẩm này
        $stmt_del = $conn->prepare("DELETE FROM bienthe_sanpham WHERE sanpham_id = :id");
        $stmt_del->execute([':id' => $id]);

        // 2. Tạo lại biến thể từ chuỗi nhập mới
        $arr_mau = array_filter(array_map('trim', explode(',', $str_mau_new)));
        $arr_size = array_filter(array_map('trim', explode(',', $str_size_new)));

        if (empty($arr_mau)) $arr_mau = ['']; 
        if (empty($arr_size)) $arr_size = [''];

        // Insert lại (Không có cột so_luong)
        $sql_variant = "INSERT INTO bienthe_sanpham (sanpham_id, mau_sac, kich_thuoc) VALUES (:sp_id, :mau, :size)";
        $stmt_var = $conn->prepare($sql_variant);

        foreach ($arr_mau as $mau) {
            foreach ($arr_size as $size) {
                $stmt_var->execute([
                    ':sp_id' => $id, // ID sản phẩm đang sửa
                    ':mau'   => $mau,
                    ':size'  => $size
                ]);
            }
        }

        $conn->commit();
        $msg_success = true;

        // Cập nhật lại dữ liệu hiển thị sau khi lưu xong
        $str_mau_hien_tai = $str_mau_new;
        $str_size_hien_tai = $str_size_new;
        $row['hinh_anh'] = $hinh_anh; // Cập nhật ảnh hiển thị nếu có đổi

    } catch (PDOException $e) {
        $conn->rollBack();
        $error_msg = "Lỗi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật sản phẩm</title>
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
        <h3 class="page-title">Cập nhật sản phẩm</h3>
        <div style="width: 100px;"></div>
    </div>

    <div class="form-container">
        
        <?php if(!empty($error_msg)): ?>
            <div class="alert-error"><strong>Lỗi:</strong> <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="editProductForm">
            
            <div class="form-row">
                <div class="form-col" style="flex: 2;">
                    <div class="form-group">
                        <label class="form-label">Tên sản phẩm <span style="color:red">*</span></label>
                        <input type="text" name="ten_sanpham" class="form-control" required 
                               value="<?php echo htmlspecialchars($row['ten_sanpham']); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Danh mục <span style="color:red">*</span></label>
                        <select name="danhmuc_id" class="form-control" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach($list_dm as $dm): ?>
                                <option value="<?php echo $dm['id']; ?>" 
                                    <?php if($row['danhmuc_id'] == $dm['id']) echo 'selected'; ?>>
                                    <?php echo $dm['ten_danhmuc']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Giá nhập</label>
                        <input type="number" name="gia_nhap" class="form-control" min="0" 
                               value="<?php echo $row['gia_nhap']; ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Giá bán <span style="color:red">*</span></label>
                        <input type="number" name="gia_ban" class="form-control" required min="0"
                               value="<?php echo $row['gia_ban']; ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Giá khuyến mãi</label>
                        <input type="number" name="gia_khuyenmai" class="form-control" min="0"
                               value="<?php echo $row['gia_khuyenmai']; ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Thương hiệu</label>
                        <select name="thuonghieu_id" class="form-control">
                            <option value="">-- Chọn hãng --</option>
                            <?php foreach($list_th as $th): ?>
                                <option value="<?php echo $th['id']; ?>" 
                                    <?php if($row['thuonghieu_id'] == $th['id']) echo 'selected'; ?>>
                                    <?php echo $th['ten_thuonghieu']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Màu sắc</label>
                        <input type="text" name="mau_sac" class="form-control" 
                               placeholder="VD: Đỏ, Xanh"
                               value="<?php echo htmlspecialchars($str_mau_hien_tai); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Kích thước</label>
                        <input type="text" name="kich_thuoc" class="form-control" 
                               placeholder="VD: S, M, L"
                               value="<?php echo htmlspecialchars($str_size_hien_tai); ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col" style="flex: 1.5;">
                    <div class="form-group">
                        <label class="form-label">Hình ảnh sản phẩm (Chọn để thay đổi)</label>
                        <input type="file" name="hinh_anh" id="imageInput" class="form-control" accept="image/*" style="padding: 9px;">
                    </div>
                </div>
                <div class="form-col" style="flex: 1;">
                    <div class="form-group">
                        <label class="form-label">Xem trước ảnh</label>
                        <div class="image-preview-container">
                            <?php if(!empty($row['hinh_anh'])): ?>
                                <span class="no-image-text" style="display: none;">Chưa chọn ảnh</span>
                                <img id="imagePreview" class="image-preview" src="anh_sanpham/<?php echo $row['hinh_anh']; ?>" alt="Ảnh sản phẩm" style="display: block;">
                            <?php else: ?>
                                <span class="no-image-text">Chưa chọn ảnh</span>
                                <img id="imagePreview" class="image-preview" src="#" alt="Ảnh xem trước">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Mô tả chi tiết</label>
                <textarea name="mo_ta" class="form-control" rows="15"><?php echo htmlspecialchars($row['mo_ta']); ?></textarea>
            </div>

            <div style="text-align: right; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <button type="submit" name="btn_update" class="btn-submit">
                    Cập nhật
                </button>
            </div>

        </form>
    </div>
</div>

<div id="toast">
    <div class="toast-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
    </div>
    <div class="toast-message">Đã cập nhật thành công!</div>
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
                if(noImageText) noImageText.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    });

    <?php if($msg_success): ?>
    document.addEventListener("DOMContentLoaded", function() {
        var x = document.getElementById("toast");
        x.className = "show";
        setTimeout(function(){ 
            window.location.href = "sanpham.php"; 
        }, 1500);
    });
    <?php endif; ?>
</script>

</body>
</html>