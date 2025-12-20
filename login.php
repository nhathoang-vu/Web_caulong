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
            
            <form action="" method="POST">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Email" required>
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