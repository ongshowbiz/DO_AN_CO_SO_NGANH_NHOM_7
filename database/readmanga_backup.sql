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
