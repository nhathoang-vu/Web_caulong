<?php
    // 1. Gọi file kết nối database
    include 'connect.php';

    $errors = []; // Mảng chứa lỗi

    // Kiểm tra xem người dùng đã bấm nút đăng ký chưa
    if (isset($_POST['btn_reg'])) {
        // Lấy dữ liệu và dùng trim() để loại bỏ khoảng trắng thừa đầu/cuối
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $sdt = trim($_POST['sdt']);
        $pass = $_POST['password'];
        $repass = $_POST['repassword'];

        // --- VALIDATE DỮ LIỆU ---

        // 1. Kiểm tra rỗng
        if (empty($name) || empty($email) || empty($pass) || empty($repass)) {
            $errors[] = "Vui lòng nhập đầy đủ thông tin bắt buộc (*)";
        }

        // 2. Validate mật khẩu (Regex)
        // Lưu ý: Nếu muốn test nhanh có thể tạm đóng phần này lại
        if (!preg_match('/[A-Z]/', $pass)) {
            $errors[] = "Mật khẩu phải chứa ít nhất 1 ký tự IN HOA.";
        }
        if (!preg_match('/[0-9]/', $pass)) {
            $errors[] = "Mật khẩu phải chứa ít nhất 1 chữ số.";
        }
        if (!preg_match('/[\W]/', $pass)) { // \W là ký tự không phải chữ và số (ký tự đặc biệt)
            $errors[] = "Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt.";
        }
        if (strlen($pass) < 6) {
            $errors[] = "Mật khẩu phải dài ít nhất 6 ký tự.";
        }

        // 3. Kiểm tra mật khẩu nhập lại
        if ($pass != $repass) {
            $errors[] = "Mật khẩu nhập lại không khớp!";
        }

        // 4. Kiểm tra Email đã tồn tại chưa
        if (empty($errors)) {
            try {
                $stmt = $conn->prepare("SELECT email FROM user WHERE email = :email"); 
                $stmt->execute(['email' => $email]);
                $checkEmail = $stmt->fetch();

                if ($checkEmail) {
                    $errors[] = "Email này đã được sử dụng, vui lòng chọn email khác!";
                }
            } catch(PDOException $e) {
                $errors[] = "Lỗi kiểm tra email: " . $e->getMessage();
            }
        }

        // --- THÊM VÀO CSDL ---
        if (empty($errors)) {
            // Mã hóa mật khẩu an toàn (Thay thế MD5)
            $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
            
            // Mặc định quyền hạn là 0 (Khách hàng)
            $quyenhan = 0; 

            try {
                $sql = "INSERT INTO user (name, password, email, sdt, quyenhan) VALUES (:name, :pass, :email, :sdt, :quyenhan)";
                $stmt = $conn->prepare($sql);
                
                $result = $stmt->execute([
                    'name' => $name,
                    'pass' => $pass_hash, // Lưu mật khẩu đã mã hóa
                    'email' => $email,
                    'sdt' => $sdt,
                    'quyenhan' => $quyenhan
                ]);

                if ($result) {
                    // Dùng JavaScript để thông báo và chuyển hướng
                    echo "<script>
                        alert('Đăng ký thành công! Bạn có thể đăng nhập ngay.');
                        window.location='login.php';
                    </script>";
                    exit();
                }
            } catch(PDOException $e) {
                $errors[] = "Lỗi hệ thống khi đăng ký: " . $e->getMessage();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - HBG Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main class="register-page">
        <div class="register-container">
            <h2 class="register-title">ĐĂNG KÝ TÀI KHOẢN</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="error-msg" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #f5c6cb;">
                    <?php foreach($errors as $err) echo "<p><i class='fas fa-exclamation-circle'></i> $err</p>"; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                
                <div class="form-group">
                    <label>Họ và tên (*)</label>
                    <input type="text" name="name" placeholder="Nhập họ tên" required 
                           value="<?php echo isset($name) ? $name : '' ?>">
                </div>

                <div class="form-group">
                    <label>Email (*)</label>
                    <input type="email" name="email" placeholder="Nhập email" required 
                           value="<?php echo isset($email) ? $email : '' ?>">
                </div>

                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="sdt" placeholder="Nhập số điện thoại" 
                           value="<?php echo isset($sdt) ? $sdt : '' ?>">
                </div>

                <div class="form-group">
                    <label>Mật khẩu (*)</label>
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                    <small style="display:block; font-size: 11px; color:#666; margin-top:5px; font-style:italic;">
                        * Mật khẩu cần: Chữ hoa, số và ký tự đặc biệt (@, #, !,...).
                    </small>
                </div>

                <div class="form-group">
                    <label>Nhập lại mật khẩu (*)</label>
                    <input type="password" name="repassword" placeholder="Nhập lại mật khẩu" required>
                </div>

                <button type="submit" name="btn_reg" class="btn-register">ĐĂNG KÝ</button>

                <div class="form-links">
                    <span>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></span>
                </div>

            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>