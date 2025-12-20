<footer class="site-footer">
    <div class="footer-container">
        
        <div class="footer-col">
            <h3>HBG SHOP</h3>
            <p>Đối tác tin cậy của các lông thủ. Chuyên cung cấp dụng cụ cầu lông chính hãng, bảo hành uy tín và dịch vụ căng vợt chuẩn quốc tế.</p>
            
            <div class="contact-item">
                <div class="icon-box"><i class="fa-solid fa-location-dot"></i></div>
                <div><strong>Địa chỉ:</strong><br>123 Lạch Tray, Ngô Quyền, HP</div>
            </div>
            
            <div class="contact-item">
                <div class="icon-box"><i class="fa-solid fa-envelope"></i></div>
                <div><strong>Email:</strong><br>baodcad73@gmail.com</div>
            </div>
        </div>

        <div class="footer-col">
            <h3>VỀ CHÚNG TÔI</h3>
            <ul>
                <li><a href="gioithieu.php"><i class="fa-solid fa-chevron-right"></i> Giới thiệu</a></li>
                <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Tuyển dụng</a></li>
                <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Hệ thống cửa hàng</a></li>
                <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Tin tức sự kiện</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h3>HỖ TRỢ KHÁCH HÀNG</h3>
            <ul>
                <li><a href="huongdan/hdmh.php"><i class="fa-solid fa-chevron-right"></i> Hướng dẫn mua hàng</a></li>
                <li><a href="huongdan/hdtt.php"><i class="fa-solid fa-chevron-right"></i> Chính sách thanh toán</a></li>
                <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Chính sách đổi trả</a></li>
                <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Tra cứu đơn hàng</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h3>ĐĂNG KÝ NHẬN TIN</h3>
            <p>Nhận ngay mã giảm giá <strong>10%</strong> cho đơn hàng đầu tiên.</p>
            
            <form class="newsletter-form" action="#" method="POST">
                <input type="email" placeholder="Nhập email...">
                <button type="submit"><i class="fa-regular fa-paper-plane"></i></button>
            </form>

           <div class="social-links">
    <a href="https://www.facebook.com" target="_blank" class="social-btn facebook">
        <i class="fa-brands fa-facebook-f"></i>
    </a>

    <a href="https://www.tiktok.com/vi-VN/" target="_blank" class="social-btn tiktok">
        <i class="fa-brands fa-tiktok"></i>
    </a>

    <a href="https://www.youtube.com/watch?v=C8BnD0up5qA" target="_blank" class="social-btn youtube">
        <i class="fa-brands fa-youtube"></i>
    </a>

    <a href="https://www.instagram.com/accounts/login/" target="_blank" class="social-btn instagram">
        <i class="fa-brands fa-instagram"></i>
    </a>
</div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            Copyright © 2025 <strong>HBG Shop</strong>. All rights reserved.
        </div>
    </div>
</footer>

<div class="float-group-left">
    
    <a href="tel:0904082576" class="float-btn phone">
        <i class="fa-solid fa-phone-volume"></i>
        <span class="float-text">0904.082.576</span>
    </a>

    <a href="https://zalo.me/0904082576" target="_blank" class="float-btn zalo">
        <img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" alt="Zalo">
        <span class="float-text">Chat Zalo</span>
    </a>

</div>

<div class="float-group-right">

    <a href="#" class="float-btn back-to-top" id="btnBackToTop" onclick="scrollToTop(event)">
        <span class="float-text">Lên đầu trang</span>
        <i class="fa-solid fa-arrow-up"></i>
    </a>

    <a href="https://www.facebook.com/share/16XgWuJ2dA/" target="_blank" class="float-btn messenger">
        <span class="float-text">Chat Facebook</span>
        <i class="fa-brands fa-facebook-messenger"></i>
    </a>

</div>

<script>
    function scrollToTop(e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    window.addEventListener('scroll', function() {
        var btn = document.getElementById('btnBackToTop');
        if (window.scrollY > 400) {
            btn.classList.add('show');
        } else {
            btn.classList.remove('show');
        }
    });
</script>