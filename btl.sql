-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 22, 2025 lúc 12:44 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `btl`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bienthe_sanpham`
--

CREATE TABLE `bienthe_sanpham` (
  `id` int(11) NOT NULL,
  `sanpham_id` int(11) NOT NULL,
  `kich_thuoc` varchar(50) DEFAULT NULL COMMENT 'Size giày (40,41) hoặc Trọng lượng vợt (3U, 4U)',
  `mau_sac` varchar(50) DEFAULT NULL COMMENT 'Màu sắc (Đỏ, Xanh...)',
  `so_luong_ton` int(11) DEFAULT 0 COMMENT 'Số lượng còn trong kho'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitiet_donhang`
--

CREATE TABLE `chitiet_donhang` (
  `id` int(11) NOT NULL,
  `donhang_id` int(11) NOT NULL,
  `sanpham_id` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `don_gia` decimal(10,0) NOT NULL COMMENT 'Giá lúc mua',
  `kich_thuoc` varchar(50) DEFAULT NULL COMMENT 'Khách chọn size nào (40 hay 3U)',
  `mau_sac` varchar(50) DEFAULT NULL COMMENT 'Khách chọn màu gì'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmuc`
--

CREATE TABLE `danhmuc` (
  `id` int(11) NOT NULL,
  `ten_danhmuc` varchar(100) NOT NULL COMMENT 'Tên danh mục (Vợt cầu lông, Giày...)',
  `trang_thai` tinyint(4) DEFAULT 1 COMMENT '1: Hiện, 0: Ẩn'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danhmuc`
--

INSERT INTO `danhmuc` (`id`, `ten_danhmuc`, `trang_thai`) VALUES
(1, 'Vợt Cầu Lông', 1),
(2, 'Giày Cầu Lông', 1),
(3, 'Balo - Túi Vợt', 1),
(4, 'Quần Áo', 1),
(5, 'Phụ Kiện', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID tài khoản mua (nếu có)',
  `nguoi_nhan` varchar(100) NOT NULL COMMENT 'Tên người nhận hàng',
  `sdt_nhan` varchar(20) NOT NULL,
  `dia_chi_nhan` varchar(255) NOT NULL,
  `tong_tien` decimal(10,0) NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `trang_thai` tinyint(4) DEFAULT 1 COMMENT '1: Mới, 2: Đang giao, 3: Hoàn thành, 0: Hủy',
  `ngay_dat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lienhe`
--

CREATE TABLE `lienhe` (
  `id` int(11) NOT NULL,
  `ho_ten` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sdt` varchar(20) NOT NULL,
  `noi_dung` text NOT NULL,
  `trang_thai` tinyint(4) DEFAULT 0 COMMENT '0: Chưa xem, 1: Đã xem',
  `ngay_gui` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lienhe`
--

INSERT INTO `lienhe` (`id`, `ho_ten`, `email`, `sdt`, `noi_dung`, `trang_thai`, `ngay_gui`) VALUES
(4, 'bao', 'bao@gmail.com', '456454', 'xin chao ', 0, '2025-12-22 10:40:15'),
(5, 'giang', 'giang95674@st.vimaru.edu.vn', '5641313', 'mua hang', 1, '2025-12-22 10:40:36'),
(6, 'hoang', 'hoang@gmail.com', '546546', 'địa chỉ', 1, '2025-12-22 10:41:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham`
--

CREATE TABLE `sanpham` (
  `id` int(11) NOT NULL,
  `danhmuc_id` int(11) NOT NULL COMMENT 'Thuộc danh mục nào',
  `thuonghieu_id` int(11) DEFAULT NULL COMMENT 'Thuộc hãng nào',
  `ten_sanpham` varchar(255) NOT NULL,
  `gia_nhap` decimal(15,0) DEFAULT 0,
  `hinh_anh` varchar(255) DEFAULT NULL COMMENT 'Ảnh đại diện chính',
  `gia_ban` decimal(10,0) NOT NULL,
  `gia_khuyenmai` decimal(10,0) DEFAULT 0,
  `kich_thuoc` varchar(100) DEFAULT NULL COMMENT 'Chiều dài vợt',
  `thong_so` varchar(255) DEFAULT NULL COMMENT '3U, 4U, 5U',
  `mo_ta` longtext DEFAULT NULL,
  `luot_xem` int(11) DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham`
--

INSERT INTO `sanpham` (`id`, `danhmuc_id`, `thuonghieu_id`, `ten_sanpham`, `gia_nhap`, `hinh_anh`, `gia_ban`, `gia_khuyenmai`, `kich_thuoc`, `thong_so`, `mo_ta`, `luot_xem`, `ngay_tao`) VALUES
(1, 1, 1, 'Vợt Yonex Astrox 77 Pro', 2200000, 'vot1.png', 3200000, 2560000, '675mm', '4U', NULL, 0, '2025-12-18 01:22:05'),
(2, 1, 2, 'Vợt Lining Axforce 80', 3100000, 'vot2.jpg', 4500000, 3600000, '700mm', '5U', NULL, 0, '2025-12-18 01:22:05'),
(3, 2, 5, 'Giày Kawasaki 173', 650000, 'giay1.jpg', 950000, 760000, '600mm', '3U', NULL, 0, '2025-12-18 01:22:05'),
(4, 3, NULL, 'Balo Yonex 2024', 400000, 'balo1.jpg', 650000, 520000, NULL, NULL, NULL, 0, '2025-12-18 01:22:05'),
(7, 1, 1, 'Vợt Yonex 100ZZ', 2600000, 'vot_yonex_zz.jpg', 3800000, 3500000, NULL, NULL, NULL, 0, '2025-12-18 02:57:07'),
(8, 2, 1, 'Giày Yonex 65Z3', 1800000, 'giay_yonex.jpg', 2600000, 2300000, NULL, NULL, NULL, 0, '2025-12-18 02:57:07'),
(9, 2, 2, 'Giày Lining AYzs01', 800000, 'giay_lining.jpg', 1200000, 0, NULL, NULL, NULL, 0, '2025-12-18 02:57:07');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thuonghieu`
--

CREATE TABLE `thuonghieu` (
  `id` int(11) NOT NULL,
  `ten_thuonghieu` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thuonghieu`
--

INSERT INTO `thuonghieu` (`id`, `ten_thuonghieu`, `logo`) VALUES
(1, 'Yonex', NULL),
(2, 'Lining', NULL),
(3, 'Victor', NULL),
(4, 'Mizuno', NULL),
(5, 'Kawasaki', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sdt` varchar(10) NOT NULL,
  `quyenhan` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `user`
--

INSERT INTO `user` (`id`, `name`, `password`, `email`, `sdt`, `quyenhan`) VALUES
(1, 'bao', '$2y$10$Hbnb7XpSPjyVvVSpt.lseulMWBHekgywTqwXDKPq.iLcZS9z7IVRm', 'bao96219@st.vimaru.edu.vn', '0868149341', 0),
(2, 'admin', '202cb962ac59075b964b07152d234b70', 'admin@gmail.com', '0868149341', 1),
(3, 'Thảo', '$2y$10$amk/7Hch.psvHv5F.yXZGuIcVZ4PZhN/3e08n9bHBE2cKN6q/fQOC', 'hucthanhthao198@gmail.com', '0382344082', 0);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bienthe_sanpham`
--
ALTER TABLE `bienthe_sanpham`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sanpham_id` (`sanpham_id`);

--
-- Chỉ mục cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donhang_id` (`donhang_id`),
  ADD KEY `sanpham_id` (`sanpham_id`);

--
-- Chỉ mục cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `lienhe`
--
ALTER TABLE `lienhe`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`id`),
  ADD KEY `danhmuc_id` (`danhmuc_id`),
  ADD KEY `thuonghieu_id` (`thuonghieu_id`);

--
-- Chỉ mục cho bảng `thuonghieu`
--
ALTER TABLE `thuonghieu`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bienthe_sanpham`
--
ALTER TABLE `bienthe_sanpham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lienhe`
--
ALTER TABLE `lienhe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `thuonghieu`
--
ALTER TABLE `thuonghieu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bienthe_sanpham`
--
ALTER TABLE `bienthe_sanpham`
  ADD CONSTRAINT `bienthe_sanpham_ibfk_1` FOREIGN KEY (`sanpham_id`) REFERENCES `sanpham` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  ADD CONSTRAINT `chitiet_donhang_ibfk_1` FOREIGN KEY (`donhang_id`) REFERENCES `donhang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitiet_donhang_ibfk_2` FOREIGN KEY (`sanpham_id`) REFERENCES `sanpham` (`id`);

--
-- Các ràng buộc cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `sanpham_ibfk_1` FOREIGN KEY (`danhmuc_id`) REFERENCES `danhmuc` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sanpham_ibfk_2` FOREIGN KEY (`thuonghieu_id`) REFERENCES `thuonghieu` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
