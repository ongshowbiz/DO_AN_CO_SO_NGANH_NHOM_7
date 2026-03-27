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

-- Dumping data for table readmanga.chap: ~0 rows (approximately)

-- Dumping data for table readmanga.chi_tiet_don_hang: ~0 rows (approximately)

-- Dumping data for table readmanga.comment: ~0 rows (approximately)

-- Dumping data for table readmanga.don_hang: ~0 rows (approximately)

-- Dumping data for table readmanga.luot_doc: ~0 rows (approximately)

-- Dumping data for table readmanga.manga: ~0 rows (approximately)

-- Dumping data for table readmanga.manga_theloai: ~0 rows (approximately)

-- Dumping data for table readmanga.role: ~2 rows (approximately)
INSERT INTO `role` (`ID_VAITRO`, `TEN_VAITRO`, `TRANGTHAI`) VALUES
	(1, 'admin', 1),
	(2, 'customer', 1);

-- Dumping data for table readmanga.sanpham_manga: ~0 rows (approximately)

-- Dumping data for table readmanga.taikhoan: ~2 rows (approximately)
INSERT INTO `taikhoan` (`ID_TAIKHOAN`, `ID_VAITRO`, `TENTAIKHOAN`, `MATKHAU`, `EMAIL`, `SDT`, `NGAYLAP`, `ANH`, `TRANGTHAI`, `GIOITINH`, `last_login`, `reset_token`, `reset_expiry`) VALUES
	(2, 1, 'Lylinth', '$2y$10$.pRl60VJXz5Ted2KABGkI.sUrWvG.TWf0nq6p.zoDASXtscPa/qcS', 'huuphuoc191019@gmail.com', NULL, '2026-03-22 10:51:05', NULL, 1, NULL, '2026-03-22 16:24:48.000000', 'eff5dca3cbc2b9cad1ec44d0053434cf', '2026-03-22 10:45:03'),
	(3, 2, 'vaalnakynth', '$2y$10$jq0y04f/nK8C.WC.g.LJnub7e6SmMD9054o9Xz4tCRsk.1.RhswsC', 'kurumitokisaki15111006@gmail.com', NULL, '2026-03-22 11:51:03', NULL, 1, NULL, NULL, NULL, NULL);

-- Dumping data for table readmanga.theloai: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

ALTER TABLE manga 
ADD COLUMN slug VARCHAR(255) NULL AFTER manga_name,
ADD UNIQUE KEY uq_slug (slug);

SET FOREIGN_KEY_CHECKS = 0;

-- 1. TRUYỆN (insert manga trước, id_chap tạm để 1)
INSERT INTO `manga` (`id_theloaimanga`, `id_taikhoan`, `id_chap`, `manga_name`, `slug`, `mota`, `tacgia`, `anh`, `sratus`) VALUES
(1, 2, 1, 'Naruto', 'naruto', 'Naruto Uzumaki là một ninja trẻ tuổi với ước mơ trở thành Hokage.', 'Masashi Kishimoto', 'https://picsum.photos/seed/naruto/200/280', 1);

-- 2. CHƯƠNG (giờ manga id=1 đã tồn tại)
INSERT INTO `chap` (`id_manga`, `so_chuong`, `tieu_de_chuong`, `danh_sach_anh`, `ngay_dang`) VALUES
(1, 1, 'Chương mở đầu', '["https://picsum.photos/seed/p1/800/1200","https://picsum.photos/seed/p2/800/1200","https://picsum.photos/seed/p3/800/1200"]', NOW()),
(1, 2, 'Kẻ thù xuất hiện', '["https://picsum.photos/seed/p4/800/1200","https://picsum.photos/seed/p5/800/1200"]', NOW()),
(1, 3, 'Trận chiến lớn', '["https://picsum.photos/seed/p6/800/1200","https://picsum.photos/seed/p7/800/1200"]', NOW());

-- 3. GÁN THỂ LOẠI
INSERT INTO `manga_theloai` (`id_manga`, `id_theloaimanga`) VALUES (1, 1), (1, 3);

-- 4. LƯỢT XEM
INSERT INTO `luot_doc` (`id_manga`, `ngay`, `so_luot_doc`) VALUES
(1, CURDATE(), 150),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 200);

SET FOREIGN_KEY_CHECKS = 1;