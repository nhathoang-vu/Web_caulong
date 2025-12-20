<?php
session_start();
// require_once 'connect.php'; 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Cầu Lông - HBG</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/footer.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main style="padding-top: 0; background: #fff;">
        
        <div class="slider-container">
            <div class="slides-wrapper">
                
                <div class="slide">
                    <img src="assets/images/banner/banner1.jpg" alt="Banner Khuyến Mãi">
                </div>

                <div class="slide">
                    <img src="assets/images/banner/banner2.jpg" alt="Banner Mới">
                </div>

                <div class="slide">
                    <img src="assets/images/banner/banner3.jpg" alt="Banner Vợt">
                </div>

            </div>

            <button class="prev-btn" onclick="moveSlide(-1)"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="next-btn" onclick="moveSlide(1)"><i class="fa-solid fa-chevron-right"></i></button>
            
            <div class="dots-container">
                <span class="dot active" onclick="currentSlide(0)"></span>
                <span class="dot" onclick="currentSlide(1)"></span>
                <span class="dot" onclick="currentSlide(2)"></span>
            </div>
        </div>

        <div class="container">
            <h2 style="text-align:center; margin-top: 50px; color: #ccc;">
                (Phần sản phẩm sẽ hiện ở đây)
            </h2>
        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        let slideIndex = 0;
        const slidesWrapper = document.querySelector('.slides-wrapper');
        const dots = document.querySelectorAll('.dot');
        const totalSlides = document.querySelectorAll('.slide').length;

        function showSlides(n) {
            if (n >= totalSlides) slideIndex = 0;
            else if (n < 0) slideIndex = totalSlides - 1;
            else slideIndex = n;

            slidesWrapper.style.transform = `translateX(-${slideIndex * 100}%)`;

            dots.forEach(d => d.classList.remove('active'));
            if(dots[slideIndex]) dots[slideIndex].classList.add('active');
        }

        function moveSlide(n) {
            showSlides(slideIndex + n);
            resetTimer();
        }

        function currentSlide(n) {
            showSlides(n);
            resetTimer();
        }

        let timer = setInterval(() => { moveSlide(1); }, 5000);

        function resetTimer() {
            clearInterval(timer);
            timer = setInterval(() => { moveSlide(1); }, 5000);
        }
    </script>
</body>
</html>