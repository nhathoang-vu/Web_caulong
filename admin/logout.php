<?php
session_start();

// Xóa toàn bộ session
session_unset();
session_destroy();

// Quay ra ngoài thư mục gốc để vào trang login
header("Location: ../login.php"); 
exit();
?>