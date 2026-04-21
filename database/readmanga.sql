SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS readmanga;
USE readmanga;

CREATE TABLE `chap` (
  `id_chap` int(11) NOT NULL,
  `id_manga` int(11) NOT NULL,
  `so_chuong` int(11) NOT NULL,
  `tieu_de_chuong` varchar(255) NOT NULL,
  `noi_dung` longtext DEFAULT NULL,
  `danh_sach_anh` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`danh_sach_anh`)),
  `ngay_dang` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `chi_tiet_don_hang` (
  `id_oritem` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_spmanga` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `gia_tai_thoi_diem_mua` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `comment` (
  `id_comment` int(11) NOT NULL,
  `id_taikhoan` int(11) NOT NULL,
  `id_manga` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `ngay_tao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `don_hang` (
  `id_order` int(11) NOT NULL,
  `id_taikhoan` int(11) NOT NULL,
  `ngay_dat` datetime NOT NULL DEFAULT current_timestamp(),
  `tong_tien` decimal(12,2) NOT NULL,
  `trang_thai_thanh_toan` tinyint(1) NOT NULL DEFAULT 0,
  `dia_chi_giao_hang` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `luot_doc` (
  `id_view` int(11) NOT NULL,
  `id_manga` int(11) NOT NULL,
  `ngay` date NOT NULL,
  `so_luot_doc` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `manga` (
  `id_manga` int(11) NOT NULL,
  `id_theloaimanga` int(11) NOT NULL,
  `id_taikhoan` int(11) NOT NULL,
  `manga_name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `mota` varchar(255) DEFAULT NULL,
  `tacgia` varchar(255) DEFAULT NULL,
  `anh` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `create_day` datetime(4) NOT NULL DEFAULT current_timestamp(4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `manga_theloai` (
  `id_manga` int(11) NOT NULL,
  `id_theloaimanga` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `role` (
  `ID_VAITRO` int(11) NOT NULL,
  `TEN_VAITRO` varchar(50) NOT NULL,
  `TRANGTHAI` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `role` (`ID_VAITRO`, `TEN_VAITRO`, `TRANGTHAI`) VALUES
(1, 'admin', 1),
(2, 'customer', 1);

CREATE TABLE `sanpham_manga` (
  `id_spmanga` int(11) NOT NULL,
  `id_manga` int(11) NOT NULL,
  `gia_ban` decimal(12,2) NOT NULL,
  `so_luong_kho` int(11) NOT NULL DEFAULT 0,
  `nha_xuat_ban` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `taikhoan` (
  `ID_TAIKHOAN` int(11) NOT NULL,
  `ID_VAITRO` int(11) DEFAULT NULL,
  `TENTAIKHOAN` varchar(100) NOT NULL,
  `MATKHAU` varchar(255) NOT NULL,
  `EMAIL` varchar(100) DEFAULT NULL,
  `SDT` varchar(15) DEFAULT NULL,
  `NGAYLAP` datetime DEFAULT current_timestamp(),
  `ANH` varchar(255) DEFAULT NULL,
  `TRANGTHAI` tinyint(4) DEFAULT 1,
  `GIOITINH` varchar(4) DEFAULT NULL,
  `last_login` datetime(6) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `theloai` (
  `id_theloaimanga` int(11) NOT NULL,
  `ten_theloai` varchar(100) NOT NULL,
  `mota` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tiendo_doc` (
  `id_taikhoan` int(11) NOT NULL,
  `id_manga` int(11) NOT NULL,
  `so_chuong` int(11) NOT NULL,
  `ngay_doc` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_taikhoan`, `id_manga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `chap`
  ADD PRIMARY KEY (`id_chap`),
  ADD KEY `ibfk_mg` (`id_manga`);

ALTER TABLE `chi_tiet_don_hang`
  ADD PRIMARY KEY (`id_oritem`),
  ADD KEY `ibfk_od` (`id_order`),
  ADD KEY `ibfk_sp` (`id_spmanga`);

ALTER TABLE `comment`
  ADD PRIMARY KEY (`id_comment`),
  ADD KEY `123` (`id_manga`),
  ADD KEY `1234` (`id_taikhoan`);

ALTER TABLE `don_hang`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `skbd` (`id_taikhoan`);

ALTER TABLE `luot_doc`
  ADD PRIMARY KEY (`id_view`),
  ADD KEY `view` (`id_manga`);

ALTER TABLE `manga`
  ADD PRIMARY KEY (`id_manga`),
  ADD UNIQUE KEY `uq_slug` (`slug`),
  ADD KEY `ibfk_tk` (`id_taikhoan`),
  ADD KEY `ibfk_lmg` (`id_theloaimanga`);

ALTER TABLE `manga_theloai`
  ADD PRIMARY KEY (`id_manga`,`id_theloaimanga`),
  ADD KEY `fk_mt_theloai` (`id_theloaimanga`);

ALTER TABLE `role`
  ADD PRIMARY KEY (`ID_VAITRO`),
  ADD UNIQUE KEY `uq_tenvaitro` (`TEN_VAITRO`);

ALTER TABLE `sanpham_manga`
  ADD PRIMARY KEY (`id_spmanga`),
  ADD KEY `ibfk_mgsp` (`id_manga`);

ALTER TABLE `taikhoan`
  ADD PRIMARY KEY (`ID_TAIKHOAN`),
  ADD KEY `ibfk_vt` (`ID_VAITRO`);

ALTER TABLE `theloai`
  ADD PRIMARY KEY (`id_theloaimanga`),
  ADD UNIQUE KEY `uq_tentheloai` (`ten_theloai`);

ALTER TABLE `chap`
  MODIFY `id_chap` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `chi_tiet_don_hang`
  MODIFY `id_oritem` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `comment`
  MODIFY `id_comment` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `don_hang`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `luot_doc`
  MODIFY `id_view` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `manga`
  MODIFY `id_manga` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `role`
  MODIFY `ID_VAITRO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `sanpham_manga`
  MODIFY `id_spmanga` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `taikhoan`
  MODIFY `ID_TAIKHOAN` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `theloai`
  MODIFY `id_theloaimanga` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `chap`
  ADD CONSTRAINT `ibfk_mg` FOREIGN KEY (`id_manga`) REFERENCES `manga` (`id_manga`);

ALTER TABLE `chi_tiet_don_hang`
  ADD CONSTRAINT `ibfk_od` FOREIGN KEY (`id_order`) REFERENCES `don_hang` (`id_order`),
  ADD CONSTRAINT `ibfk_sp` FOREIGN KEY (`id_spmanga`) REFERENCES `sanpham_manga` (`id_spmanga`);

ALTER TABLE `comment`
  ADD CONSTRAINT `123` FOREIGN KEY (`id_manga`) REFERENCES `manga` (`id_manga`),
  ADD CONSTRAINT `1234` FOREIGN KEY (`id_taikhoan`) REFERENCES `taikhoan` (`ID_TAIKHOAN`);

ALTER TABLE `don_hang`
  ADD CONSTRAINT `skbd` FOREIGN KEY (`id_taikhoan`) REFERENCES `taikhoan` (`ID_TAIKHOAN`);

ALTER TABLE `luot_doc`
  ADD CONSTRAINT `view` FOREIGN KEY (`id_manga`) REFERENCES `manga` (`id_manga`);

ALTER TABLE `manga`
  ADD CONSTRAINT `ibfk_tk` FOREIGN KEY (`id_taikhoan`) REFERENCES `taikhoan` (`ID_TAIKHOAN`);

ALTER TABLE `manga_theloai`
  ADD CONSTRAINT `fk_mt_manga` FOREIGN KEY (`id_manga`) REFERENCES `manga` (`id_manga`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mt_theloai` FOREIGN KEY (`id_theloaimanga`) REFERENCES `theloai` (`id_theloaimanga`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sanpham_manga`
  ADD CONSTRAINT `ibfk_mgsp` FOREIGN KEY (`id_manga`) REFERENCES `manga` (`id_manga`);

ALTER TABLE `taikhoan`
  ADD CONSTRAINT `ibfk_vt` FOREIGN KEY (`ID_VAITRO`) REFERENCES `role` (`ID_VAITRO`);

ALTER TABLE `tiendo_doc`
  ADD CONSTRAINT `fk_td_tk` FOREIGN KEY (`id_taikhoan`) REFERENCES `taikhoan` (`ID_TAIKHOAN`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_td_mg` FOREIGN KEY (`id_manga`) REFERENCES `manga` (`id_manga`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;