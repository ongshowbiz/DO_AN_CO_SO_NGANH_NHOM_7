-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               12.2.2-MariaDB - MariaDB Server
-- Server OS:                    Win64
-- HeidiSQL Version:             12.14.0.7165
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table readmanga.chap: ~5 rows (approximately)
INSERT INTO `chap` (`id_chap`, `id_manga`, `so_chuong`, `tieu_de_chuong`, `noi_dung`, `danh_sach_anh`, `ngay_dang`) VALUES
	(1, 1, 1, 'Uzumaki Naruto', NULL, '["https://picsum.photos/seed/n1/800/1200","https://picsum.photos/seed/n2/800/1200"]', '2026-03-30 06:07:12'),
	(2, 1, 2, 'Kẻ thù đầu tiên', NULL, '["https://picsum.photos/seed/n3/800/1200","https://picsum.photos/seed/n4/800/1200"]', '2026-03-30 06:07:12'),
	(3, 2, 1, 'Tôi là Luffy', NULL, '["https://picsum.photos/seed/op1/800/1200","https://picsum.photos/seed/op2/800/1200"]', '2026-03-30 06:07:12'),
	(4, 3, 1, 'Tanjiro và Nezuko', NULL, '["https://picsum.photos/seed/ds1/800/1200","https://picsum.photos/seed/ds2/800/1200"]', '2026-03-30 06:07:12'),
	(5, 4, 1, 'Doraemon đến!', NULL, '["https://picsum.photos/seed/dr1/800/1200","https://picsum.photos/seed/dr2/800/1200"]', '2026-03-30 06:07:12');

-- Dumping data for table readmanga.chi_tiet_don_hang: ~0 rows (approximately)

-- Dumping data for table readmanga.comment: ~0 rows (approximately)

-- Dumping data for table readmanga.don_hang: ~0 rows (approximately)

-- Dumping data for table readmanga.luot_doc: ~6 rows (approximately)
INSERT INTO `luot_doc` (`id_view`, `id_manga`, `ngay`, `so_luot_doc`) VALUES
	(1, 1, '2026-03-30', 500),
	(2, 1, '2026-03-29', 350),
	(3, 2, '2026-03-30', 420),
	(4, 2, '2026-03-29', 280),
	(5, 3, '2026-03-30', 300),
	(6, 4, '2026-03-30', 200);

-- Dumping data for table readmanga.manga: ~4 rows (approximately)
INSERT INTO `manga` (`id_manga`, `id_theloaimanga`, `id_taikhoan`, `id_chap`, `manga_name`, `slug`, `mota`, `tacgia`, `anh`, `status`, `create_day`) VALUES
	(1, 1, 2, 1, 'Naruto', 'naruto', 'Câu chuyện về ninja Naruto Uzumaki với ước mơ trở thành Hokage.', 'Masashi Kishimoto', 'https://jumpg-assets.tokyo-cdn.com/secure/title/100018/title_thumbnail_portrait_list/313318.jpg?hash=vfQSOC-bbvsLAZyU8YSiQw&expires=2145884400', 1, '2026-03-30 06:07:12.1511'),
	(2, 2, 2, 3, 'One Piece', 'one-piece', 'Hành trình của Luffy tìm kho báu One Piece.', 'Eiichiro Oda', 'https://jumpg-assets.tokyo-cdn.com/secure/title/1000001/title_thumbnail_portrait_list/326475.jpg?hash=2FR6gxRFLFRRIEykVugGyw&expires=2145884400', 1, '2026-03-30 06:07:12.1511'),
	(3, 1, 2, 4, 'Demon Slayer', 'demon-slayer', 'Tanjiro chiến đấu với ác quỷ để cứu em gái.', 'Koyoharu Gotouge', 'https://m.media-amazon.com/images/I/51xmuopDo3S._SS400_.jpg', 1, '2026-03-30 06:07:12.1511'),
	(4, 3, 2, 5, 'Doraemon', 'doraemon', 'Chú mèo máy đến từ tương lai giúp đỡ Nobita.', 'Fujiko F. Fujio', 'https://images-na.ssl-images-amazon.com/images/I/91PLt7RSaHL.jpg', 1, '2026-03-30 06:07:12.1511');

-- Dumping data for table readmanga.manga_theloai: ~6 rows (approximately)
INSERT INTO `manga_theloai` (`id_manga`, `id_theloaimanga`) VALUES
	(1, 1),
	(1, 4),
	(2, 1),
	(2, 4),
	(3, 1),
	(4, 3);

-- Dumping data for table readmanga.role: ~2 rows (approximately)
INSERT INTO `role` (`ID_VAITRO`, `TEN_VAITRO`, `TRANGTHAI`) VALUES
	(1, 'admin', 1),
	(2, 'customer', 1);

-- Dumping data for table readmanga.sanpham_manga: ~4 rows (approximately)
INSERT INTO `sanpham_manga` (`id_spmanga`, `id_manga`, `gia_ban`, `so_luong_kho`, `nha_xuat_ban`) VALUES
	(1, 1, 45000.00, 50, 'NXB Kim Đồng'),
	(2, 2, 55000.00, 30, 'NXB Trẻ'),
	(3, 3, 65000.00, 20, 'NXB Kim Đồng'),
	(4, 4, 35000.00, 100, 'NXB Trẻ');

-- Dumping data for table readmanga.taikhoan: ~2 rows (approximately)
INSERT INTO `taikhoan` (`ID_TAIKHOAN`, `ID_VAITRO`, `TENTAIKHOAN`, `MATKHAU`, `EMAIL`, `SDT`, `NGAYLAP`, `ANH`, `TRANGTHAI`, `GIOITINH`, `last_login`, `reset_token`, `reset_expiry`) VALUES
	(2, 1, 'Lylinth', '$2y$10$6LZBmutF/naOb1zOIFn95uu8jKrhDVCpCP8OdP3LXNS8qS3pTnMuG', 'huuphuoc191019@gmail.com', '0123456789', '2026-03-22 10:51:05', 'assets/uploads/taikhoan/avatars_69ca208fd04a04.94533180.jpg', 1, 'Nữ', '2026-03-30 14:01:50.000000', NULL, NULL),
	(3, 2, 'vaalnakynth', '$2y$10$yUrz1WhXDXqd6PhNKzDj9eYaU6Ukzgrgp.sWww9jU0/uFFas7Pf6u', 'kurumitokisaki15111006@gmail.com', NULL, '2026-03-22 11:51:03', 'assets/uploads/taikhoan/avatars_69ca1ed97e3a37.93385478.jpg', 1, NULL, '2026-03-30 13:48:57.000000', NULL, NULL);

-- THỂ LOẠI
ALTER TABLE `theloai` AUTO_INCREMENT = 1;
INSERT INTO `theloai` (`id_theloaimanga`, `ten_theloai`, `mota`, `status`) VALUES
(1, 'Hành Động', 'Truyện hành động gay cấn', 1),
(2, 'Tình Cảm', 'Truyện tình cảm lãng mạn', 1),
(3, 'Hài Hước', 'Truyện hài hước vui vẻ', 1),
(4, 'Phiêu Lưu', 'Truyện phiêu lưu mạo hiểm', 1);

-- Dumping data for table readmanga.theloai: ~4 rows (approximately)
INSERT INTO `theloai` (`id_theloaimanga`, `ten_theloai`, `mota`, `status`) VALUES
	(1, 'Hành Động', 'Truyện hành động gay cấn', 1),
	(2, 'Tình Cảm', 'Truyện tình cảm lãng mạn', 1),
	(3, 'Hài Hước', 'Truyện hài hước vui vẻ', 1),
	(4, 'Phiêu Lưu', 'Truyện phiêu lưu mạo hiểm', 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
