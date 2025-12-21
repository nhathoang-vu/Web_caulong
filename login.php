<?php
session_start();
require_once 'connect.php'; // Gọi file kết nối CSDL

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $email = $_POST['username']; 
    $password_input = $_POST['password'];

    // 1. Mã hóa mật khẩu người dùng nhập vào thành MD5 để so sánh
    $password_md5 = md5($password_input);

    try {
        // 2. Tìm user trong database bằng Email và Mật khẩu (đã mã hóa)
        $sql = "SELECT * FROM user WHERE email = :email AND password = :password";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_md5); // So sánh chuỗi MD5
        
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Nếu tìm thấy user (tức là Email đúng và Pass đúng)
        if ($user) {
            
            // Lưu Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['quyenhan'];

            // 4. Phân quyền chuyển hướng
            if ($user['quyenhan'] == 1) {
                // Admin -> Vào trang quản trị
                header("Location: admin/index.php"); 
            } else {
                // Khách (0) -> Về trang chủ
                header("Location: index.php");
            }
            exit();

        } else {
            $error_message = "Email hoặc Mật khẩu không chính xác!";
        }

    } catch (PDOException $e) {
        $error_message = "Lỗi hệ thống: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - HBG Shop</title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main class="login-page">
        <div class="login-container">
            <h2 class="login-title">ĐĂNG NHẬP</h2>
            
            <?php if(!empty($error_message)): ?>
                <div style="color: red; text-align: center; margin-bottom: 15px; font-weight: bold;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Email" required value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                </div>

                <button type="submit" class="btn-login">ĐĂNG NHẬP</button>
                
                <div class="form-links">
                    <a href="forgot_password.php">Quên mật khẩu</a>
                    <a href="register.php">Đăng ký tại đây</a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>