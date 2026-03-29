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
TRUNCATE TABLE luot_doc;
TRUNCATE TABLE manga_theloai;
TRUNCATE TABLE chap;
TRUNCATE TABLE sanpham_manga;
TRUNCATE TABLE manga;
TRUNCATE TABLE theloai;
SET FOREIGN_KEY_CHECKS = 1;

SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `manga` (`id_theloaimanga`, `id_taikhoan`, `id_chap`, `manga_name`, `slug`, `mota`, `tacgia`, `anh`, `status`) VALUES
(1, 2, 1, 'Naruto', 'naruto', 'Câu chuyện về ninja Naruto Uzumaki với ước mơ trở thành Hokage.', 'Masashi Kishimoto', 'https://picsum.photos/seed/naruto/200/280', 1),
(2, 2, 1, 'One Piece', 'one-piece', 'Hành trình của Luffy tìm kho báu One Piece.', 'Eiichiro Oda', 'https://picsum.photos/seed/onepiece/200/280', 1),
(1, 2, 1, 'Demon Slayer', 'demon-slayer', 'Tanjiro chiến đấu với ác quỷ để cứu em gái.', 'Koyoharu Gotouge', 'https://picsum.photos/seed/demonslayer/200/280', 1),
(3, 2, 1, 'Doraemon', 'doraemon', 'Chú mèo máy đến từ tương lai giúp đỡ Nobita.', 'Fujiko F. Fujio', 'https://picsum.photos/seed/doraemon/200/280', 1);

INSERT INTO `chap` (`id_manga`, `so_chuong`, `tieu_de_chuong`, `danh_sach_anh`, `ngay_dang`) VALUES
(1, 1, 'Uzumaki Naruto', '["https://picsum.photos/seed/n1/800/1200","https://picsum.photos/seed/n2/800/1200"]', NOW()),
(1, 2, 'Kẻ thù đầu tiên', '["https://picsum.photos/seed/n3/800/1200","https://picsum.photos/seed/n4/800/1200"]', NOW()),
(2, 1, 'Tôi là Luffy', '["https://picsum.photos/seed/op1/800/1200","https://picsum.photos/seed/op2/800/1200"]', NOW()),
(3, 1, 'Tanjiro và Nezuko', '["https://picsum.photos/seed/ds1/800/1200","https://picsum.photos/seed/ds2/800/1200"]', NOW()),
(4, 1, 'Doraemon đến!', '["https://picsum.photos/seed/dr1/800/1200","https://picsum.photos/seed/dr2/800/1200"]', NOW());

UPDATE `manga` SET `id_chap` = 1 WHERE `id_manga` = 1;
UPDATE `manga` SET `id_chap` = 3 WHERE `id_manga` = 2;
UPDATE `manga` SET `id_chap` = 4 WHERE `id_manga` = 3;
UPDATE `manga` SET `id_chap` = 5 WHERE `id_manga` = 4;

INSERT INTO `manga_theloai` (`id_manga`, `id_theloaimanga`) VALUES
(1, 1), (1, 4),
(2, 1), (2, 4),
(3, 1),
(4, 3);

INSERT INTO `luot_doc` (`id_manga`, `ngay`, `so_luot_doc`) VALUES
(1, CURDATE(), 500), (1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 350),
(2, CURDATE(), 420), (2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 280),
(3, CURDATE(), 300),
(4, CURDATE(), 200);

INSERT INTO `sanpham_manga` (`id_manga`, `gia_ban`, `so_luong_kho`, `nha_xuat_ban`) VALUES
(1, 45000, 50, 'NXB Kim Đồng'),
(2, 55000, 30, 'NXB Trẻ'),
(3, 65000, 20, 'NXB Kim Đồng'),
(4, 35000, 100, 'NXB Trẻ');

SET FOREIGN_KEY_CHECKS = 1;

SET FOREIGN_KEY_CHECKS = 0;

-- Lấy id_manga thực tế của từng truyện để sửa chap và luot_doc
UPDATE chap c
JOIN manga m ON m.slug = 'naruto'
SET c.id_manga = m.id_manga
WHERE c.tieu_de_chuong IN ('Uzumaki Naruto', 'Kẻ thù đầu tiên');

UPDATE chap c
JOIN manga m ON m.slug = 'one-piece'
SET c.id_manga = m.id_manga
WHERE c.tieu_de_chuong = 'Tôi là Luffy';

UPDATE chap c
JOIN manga m ON m.slug = 'demon-slayer'
SET c.id_manga = m.id_manga
WHERE c.tieu_de_chuong = 'Tanjiro và Nezuko';

UPDATE chap c
JOIN manga m ON m.slug = 'doraemon'
SET c.id_manga = m.id_manga
WHERE c.tieu_de_chuong = 'Doraemon đến!';

-- Sửa luot_doc theo đúng id_manga
UPDATE luot_doc ld
JOIN manga m ON m.slug = 'naruto'
SET ld.id_manga = m.id_manga
WHERE ld.id_manga = 1;

UPDATE luot_doc ld
JOIN manga m ON m.slug = 'one-piece'
SET ld.id_manga = m.id_manga
WHERE ld.id_manga = 2;

UPDATE luot_doc ld
JOIN manga m ON m.slug = 'demon-slayer'
SET ld.id_manga = m.id_manga
WHERE ld.id_manga = 3;

UPDATE luot_doc ld
JOIN manga m ON m.slug = 'doraemon'
SET ld.id_manga = m.id_manga
WHERE ld.id_manga = 4;

-- Sửa manga_theloai tương tự
UPDATE manga_theloai mt
JOIN manga m ON m.slug = 'naruto'
SET mt.id_manga = m.id_manga
WHERE mt.id_manga = 1;

UPDATE manga_theloai mt
JOIN manga m ON m.slug = 'one-piece'
SET mt.id_manga = m.id_manga
WHERE mt.id_manga = 2;

UPDATE manga_theloai mt
JOIN manga m ON m.slug = 'demon-slayer'
SET mt.id_manga = m.id_manga
WHERE mt.id_manga = 3;

UPDATE manga_theloai mt
JOIN manga m ON m.slug = 'doraemon'
SET mt.id_manga = m.id_manga
WHERE mt.id_manga = 4;

-- Thêm UNIQUE KEY cho luot_doc (cần cho trang đọc)
ALTER TABLE luot_doc
  ADD UNIQUE KEY IF NOT EXISTS uq_manga_ngay (id_manga, ngay);

SET FOREIGN_KEY_CHECKS = 1;