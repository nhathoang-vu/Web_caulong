<?php
session_start();
require_once 'connect.php'; // Gọi file kết nối CSDL

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // SỬA: Lấy giá trị tên đăng nhập thay vì email
    $username_input = $_POST['username']; 
    $password_input = $_POST['password'];

    // 1. Mã hóa mật khẩu người dùng nhập vào thành MD5 để so sánh
    $password_md5 = md5($password_input);

    try {
        // 2. SỬA: Tìm user trong database bằng cột 'name' thay vì 'email'
        $sql = "SELECT * FROM user WHERE name = :username AND password = :password";
        $stmt = $conn->prepare($sql);
        
        // SỬA: Bind tham số theo username
        $stmt->bindParam(':username', $username_input);
        $stmt->bindParam(':password', $password_md5); // So sánh chuỗi MD5
        
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Nếu tìm thấy user (tức là Tên đăng nhập đúng và Pass đúng)
        if ($user) {
            
            // Lưu Session
            // Lưu ý: Đảm bảo tên cột trong DB khớp với code này (id, name, quyenhan)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['quyenhan'];
            
            // Lưu thêm tên đầy đủ để hiển thị cho đẹp (nếu cần)
            $_SESSION['user_fullname'] = $user['tendaydu']; 

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
            // SỬA: Thông báo lỗi phù hợp hơn
            $error_message = "Tên đăng nhập hoặc Mật khẩu không chính xác!";
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
                    <input type="text" name="username" placeholder="Tên đăng nhập" required value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
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