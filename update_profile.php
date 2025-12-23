<?php
session_start();
require_once 'connect.php'; 

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$msg_type = ''; 

// --- XỬ LÝ FORM CẬP NHẬT THÔNG TIN CÁ NHÂN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_info'])) {
    $ho_ten  = trim($_POST['tendaydu']);
    $sdt     = trim($_POST['sdt']);
    $email   = trim($_POST['email']);
    $dia_chi = trim($_POST['diachi']);

    if (empty($ho_ten) || empty($email)) {
        $message = "Vui lòng nhập Họ tên và Email!";
        $msg_type = 'error';
    } else {
        try {
            $sql = "UPDATE user SET tendaydu = :tendaydu, sdt = :sdt, email = :email, diachi = :diachi WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':tendaydu' => $ho_ten, ':sdt' => $sdt, ':email' => $email, ':diachi' => $dia_chi, ':id' => $user_id]);
            
            $_SESSION['user_fullname'] = $ho_ten;
            $message = "Cập nhật thông tin thành công!";
            $msg_type = 'success';
            header("Refresh:1.5");
        } catch (PDOException $e) {
            $message = "Lỗi: " . $e->getMessage();
            $msg_type = 'error';
        }
    }
}

// Lấy thông tin user hiển thị
try {
    $sql = "SELECT * FROM user WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) { echo "Lỗi: " . $e->getMessage(); exit(); }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật hồ sơ</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/update_profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main class="update-page">
        <div class="container update-container">
            
            <div class="form-card">
                <div class="form-header">
                    <h2>THÔNG TIN CÁ NHÂN</h2>
                    <p>Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert <?php echo $msg_type; ?>">
                        <i class="fa-solid <?php echo ($msg_type == 'success') ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="profile-form">
                    <input type="hidden" name="update_info" value="1">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tên đăng nhập</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-id-card"></i>
                                <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly class="input-readonly">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Mật khẩu</label>
                            <div class="input-group-append">
                                <div class="input-with-icon" style="flex:1;">
                                    <i class="fa-solid fa-lock"></i>
                                    <input type="password" value="********" readonly class="input-readonly">
                                </div>
                                <button type="button" class="btn-change-pass-trigger" id="openModalBtn">
                                    <i class="fa-solid fa-pen-to-square"></i> Đổi
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Họ và tên (*)</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" name="tendaydu" value="<?php echo htmlspecialchars($user['tendaydu']); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Số điện thoại</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-phone"></i>
                                <input type="text" name="sdt" value="<?php echo htmlspecialchars($user['sdt']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email (*)</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ nhận hàng</label>
                        <div class="input-with-icon textarea-icon">
                            <i class="fa-solid fa-location-dot"></i>
                            <textarea name="diachi" rows="2"><?php echo htmlspecialchars($user['diachi']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save">Lưu thông tin</button>
                        <a href="profile.php" class="btn-cancel">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            
            <div class="form-header">
                <h2>BẢO MẬT</h2>
                <p>Đổi mật khẩu đăng nhập</p>
            </div>

            <div id="password-alert" class="alert" style="display: none;"></div>

            <form id="changePassForm" class="profile-form">
                
                <div class="form-group">
                    <label>Mật khẩu hiện tại</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-key"></i>
                        <input type="password" name="current_password" id="curr_pass" placeholder="Nhập mật khẩu cũ" required>
                        <i class="fa-solid fa-eye toggle-password" onclick="togglePass('curr_pass', this)"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mật khẩu mới</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="new_password" id="new_pass" placeholder="Nhập mật khẩu mới" required>
                        <i class="fa-solid fa-eye toggle-password" onclick="togglePass('new_pass', this)"></i>
                    </div>
                    <small style="color: #666; font-size: 11px; margin-top: 5px; display: block; font-style: italic;">
                        * Yêu cầu: Có ít nhất 1 chữ in hoa, 1 số và 1 ký tự đặc biệt.
                    </small>
                </div>

                <div class="form-group">
                    <label>Xác nhận mật khẩu mới</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-circle-check"></i>
                        <input type="password" name="confirm_password" id="conf_pass" placeholder="Nhập lại mật khẩu mới" required>
                        <i class="fa-solid fa-eye toggle-password" onclick="togglePass('conf_pass', this)"></i>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save" id="btnChangePass">Xác nhận đổi</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // 1. Xử lý Modal (Mở/Đóng)
        const modal = document.getElementById("passwordModal");
        const btnOpen = document.getElementById("openModalBtn");
        const spanClose = document.getElementsByClassName("close-modal")[0];

        btnOpen.onclick = function() {
            modal.style.display = "flex"; // Hiện modal
            document.getElementById('changePassForm').reset(); // Xóa trắng form cũ
            document.getElementById('password-alert').style.display = 'none'; // Ẩn thông báo cũ
        }

        spanClose.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // 2. Xử lý Ẩn/Hiện mật khẩu (Mắt thần)
        function togglePass(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        // 3. Xử lý AJAX Đổi mật khẩu + Validate Client
        document.getElementById('changePassForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnChangePass');
            const alertBox = document.getElementById('password-alert');
            
            // Lấy giá trị các ô input
            const currPass = document.getElementById('curr_pass').value;
            const newPass  = document.getElementById('new_pass').value;
            const confPass = document.getElementById('conf_pass').value;

            // --- VALIDATE PHÍA CLIENT (JAVASCRIPT) ---
            
            // 1. Kiểm tra mật khẩu mới trùng mật khẩu cũ
            if (currPass === newPass) {
                alertBox.style.display = 'flex';
                alertBox.className = 'alert error';
                alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Mật khẩu mới không được trùng với mật khẩu cũ!';
                return; 
            }

            // 2. Kiểm tra độ phức tạp (Regex: Hoa + Số + Ký tự đặc biệt)
            const complexityRegex = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_])/;
            
            if (!complexityRegex.test(newPass)) {
                alertBox.style.display = 'flex';
                alertBox.className = 'alert error';
                alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Mật khẩu mới cần có ít nhất: 1 chữ hoa, 1 số và 1 ký tự đặc biệt!';
                return;
            }

            // 3. Kiểm tra nhập lại mật khẩu
            if (newPass !== confPass) {
                alertBox.style.display = 'flex';
                alertBox.className = 'alert error';
                alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Mật khẩu xác nhận không khớp!';
                return;
            }

            // --- GỬI AJAX ---
            const formData = new FormData(this);
            const originalText = btn.innerText;
            
            btn.innerText = 'Đang xử lý...';
            btn.disabled = true;

            fetch('change_password_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.innerText = originalText;
                btn.disabled = false;
                alertBox.style.display = 'flex';
                
                if (data.status === 'success') {
                    alertBox.className = 'alert success';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + data.message;
                    document.getElementById('changePassForm').reset();
                    // Tự đóng modal sau 2s
                    setTimeout(() => { 
                        document.getElementById("passwordModal").style.display = "none"; 
                        alertBox.style.display = 'none';
                    }, 2000);
                } else {
                    alertBox.className = 'alert error';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + data.message;
                }
            })
            .catch(error => {
                btn.innerText = originalText;
                btn.disabled = false;
                alertBox.style.display = 'flex';
                alertBox.className = 'alert error';
                alertBox.innerHTML = 'Lỗi kết nối server!';
            });
        });
    </script>

</body>
</html>