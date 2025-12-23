<?php
// thongbao.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['success_msg'])): 
    $msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']); // Xóa ngay để không hiện lại khi F5
?>
    <style>
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }

        .emerald-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999999; /* Luôn nổi lên trên cùng */
            background-color: #ffffff;
            border-left: 5px solid #10b981; /* Màu xanh ngọc bích */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 320px;
            /* Animation: Hiện ra trong 0.5s, chờ 3.5s, rồi ẩn đi trong 0.5s */
            animation: slideInRight 0.5s ease forwards, fadeOut 0.5s ease 3.5s forwards;
        }

        .emerald-icon {
            color: #10b981;
            background: #ecfdf5;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .emerald-content { flex: 1; }
        
        .emerald-title {
            color: #064e3b;
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 2px;
            font-family: 'Segoe UI', sans-serif;
        }

        .emerald-msg {
            color: #4b5563;
            font-size: 14px;
            font-family: 'Segoe UI', sans-serif;
        }
    </style>

    <div class="emerald-toast">
        <div class="emerald-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <div class="emerald-content">
            <div class="emerald-title">Thành công!</div>
            <div class="emerald-msg"><?php echo $msg; ?></div>
        </div>
    </div>
<?php endif; ?>