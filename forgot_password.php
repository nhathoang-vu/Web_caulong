<?php
    session_start();
    // Gọi file kết nối (Dùng require để nếu sai đường dẫn nó báo lỗi rõ ràng)
    require_once 'connect.php'; 

    $message = "";
    $error = "";

    // LOGIC ĐỔI MẬT KHẨU ĐƠN GIẢN (UPDATE TRỰC TIẾP)
    if (isset($_POST['btn_reset'])) {
        $email = trim($_POST['email']);
        $pass = $_POST['password'];
        $repass = $_POST['repassword'];

        if (empty($email) || empty($pass) || empty($repass)) {
            $error = "Vui lòng nhập đầy đủ thông tin!";
        } elseif ($pass != $repass) {
            $error = "Mật khẩu nhập lại không khớp!";
        } else {
            // Kiểm tra kết nối trước khi dùng $conn
            if(isset($conn)) {
                try {
                    // Kiểm tra Email có tồn tại không
                    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
                    $stmt->execute(['email' => $email]);
                    $checkEmail = $stmt->fetch();

                    if ($checkEmail) {
                        // Email tồn tại -> Update mật khẩu
                        // (Lưu ý: Nếu trong database bạn lưu mật khẩu thường thì bỏ hàm password_hash đi)
                        $pass_hash = password_hash($pass, PASSWORD_DEFAULT); 

                        $sql = "UPDATE user SET password = :pass WHERE email = :email";
                        $stmtUpdate = $conn->prepare($sql);
                        $stmtUpdate->execute(['pass' => $pass_hash, 'email' => $email]);

                        $message = "Đổi mật khẩu thành công! Hãy đăng nhập lại.";
                    } else {
                        $error = "Email này chưa đăng ký tài khoản!";
                    }
                } catch(PDOException $e) {
                    $error = "Lỗi: " . $e->getMessage();
                }
            } else {
                $error = "Lỗi kết nối CSDL (Biến \$conn chưa được định nghĩa)";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - HBG Shop</title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Thêm chút CSS hiển thị thông báo lỗi ngay tại trang này */
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 14px; text-align: center; display: block; }
        .alert-error { background-color: #fce4e4; color: #cc0000; border: 1px solid #fcc2c3; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main class="login-page">
        <div class="login-container">
            <h2 class="login-title">QUÊN MẬT KHẨU</h2>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if(!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Nhập Email đã đăng ký" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Mật khẩu mới" required>
                </div>

                <div class="form-group">
                    <input type="password" name="repassword" placeholder="Nhập lại mật khẩu mới" required>
                </div>

                <button type="submit" name="btn_reset" class="btn-login">CẬP NHẬT MẬT KHẨU</button>
                
                <div class="form-links">
                    <a href="login.php">Quay lại Đăng nhập</a>
                    <a href="register.php">Đăng ký tài khoản mới</a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>