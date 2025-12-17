-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 17, 2025 lúc 01:30 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `library_management`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `muon_tra`
--

CREATE TABLE `muon_tra` (
  `id` int(11) NOT NULL,
  `ma_sach` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ngay_muon` date NOT NULL,
  `ngay_tra_du_kien` datetime NOT NULL,
  `ngay_tra_thuc_te` datetime DEFAULT NULL,
  `tinh_trang` enum('dang_muon','da_tra','qua_han','da_duyet') NOT NULL DEFAULT 'dang_muon',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `muon_tra`
--

INSERT INTO `muon_tra` (`id`, `ma_sach`, `ma_nguoi_dung`, `ngay_muon`, `ngay_tra_du_kien`, `ngay_tra_thuc_te`, `tinh_trang`, `created_at`) VALUES
(13, 1, 7, '2025-12-16', '2025-12-24 19:43:00', '2025-12-17 13:15:38', 'da_tra', '2025-12-16 14:39:27'),
(14, 1, 7, '2025-12-16', '2025-12-30 15:39:00', '2025-12-17 13:14:55', 'da_tra', '2025-12-16 14:39:49'),
(15, 1, 7, '2025-12-16', '2025-12-30 15:39:00', '2025-12-17 13:14:43', 'da_tra', '2025-12-16 14:39:58'),
(16, 1, 7, '2025-12-17', '2026-01-02 17:23:00', '2025-12-17 13:18:05', 'da_tra', '2025-12-17 12:17:57'),
(17, 1, 7, '2025-12-17', '2025-12-31 13:21:00', '2025-12-17 13:21:27', 'da_tra', '2025-12-17 12:21:21'),
(18, 1, 7, '2025-12-17', '2025-12-25 19:29:00', '2025-12-17 19:25:40', 'da_tra', '2025-12-17 12:25:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sach`
--

CREATE TABLE `sach` (
  `id` int(11) NOT NULL,
  `ten_sach` varchar(255) NOT NULL,
  `tac_gia` varchar(100) NOT NULL,
  `nam_xuat_ban` year(4) NOT NULL,
  `so_luong_con_lai` int(11) NOT NULL DEFAULT 0,
  `mo_ta` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hinh_anh` varchar(255) DEFAULT NULL,
  `hot` bit(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sach`
--

INSERT INTO `sach` (`id`, `ten_sach`, `tac_gia`, `nam_xuat_ban`, `so_luong_con_lai`, `mo_ta`, `created_at`, `hinh_anh`, `hot`) VALUES
(1, 'Harry Potter', 'J.K. Rowling', '1997', 5, 'Câu chuyện về cậu bé phù thủy', '2025-12-13 10:02:45', 'harry_potter.jpg', b'1'),
(2, 'Lord of the Rings', 'J.R.R. Tolkien', '1954', 1, 'Hành trình tiêu diệt chiếc nhẫn', '2025-12-13 10:02:45', 'lord_of_the_rings.jpg', b'1'),
(3, 'To Kill a Mockingbird', 'Harper Lee', '1960', 2, 'Câu chuyện về công lý và phân biệt chủng tộc', '2025-12-13 10:02:45', 'to_kill_a_mockingbird.jpg', b'1');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','manager','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$examplehashadmin', 'Admin User', 'admin@example.com', 'admin', '2025-12-13 10:02:44'),
(2, 'quang', 'quang', 'Manager User', 'manager@example.com', 'manager', '2025-12-13 10:02:44'),
(3, 'user1', '$2y$10$examplehashuser', 'User One', 'user1@example.com', 'user', '2025-12-13 10:02:44'),
(4, 'quang2k4yeu', '$2y$10$Ix22ppwG6oUqQc79XhJSn.T25kyC2jJyKJ2XJPh3v4WbwOYuTT7AK', 'vu quang', 'vannquangg04@gmail.com', 'admin', '2025-12-13 10:17:32'),
(5, 'tuông', '$2y$10$8rY4hCuMN8NL8CnKYDx6H.bZgaF6UzBsw6wmy/h2/Np0v2/.KQGga', 'chi tuong', 'nct2004@gmail.com', 'manager', '2025-12-15 13:03:43'),
(7, 'nghia', '$2y$10$JgArHHcqWCOLBau9zUqMouvRyNG5tC1BC7pWSA75npTnIyv9T9XTq', 'huu nghia ', 'lhn2003@gmail.com', 'user', '2025-12-15 13:15:46');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `muon_tra`
--
ALTER TABLE `muon_tra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ma_sach` (`ma_sach`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Chỉ mục cho bảng `sach`
--
ALTER TABLE `sach`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `muon_tra`
--
ALTER TABLE `muon_tra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `sach`
--
ALTER TABLE `sach`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `muon_tra`
--
ALTER TABLE `muon_tra`
  ADD CONSTRAINT `muon_tra_ibfk_1` FOREIGN KEY (`ma_sach`) REFERENCES `sach` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `muon_tra_ibfk_2` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
