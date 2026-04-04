-- --------------------------------------------------------
-- File: readmanga_backup.sql
-- Cách dùng: Chạy SAU KHI đã chạy readmanga.sql
-- --------------------------------------------------------

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

-- XÓA DATA CŨ
DELETE FROM tiendo_doc;
DELETE FROM luot_doc;
DELETE FROM manga_theloai;
DELETE FROM chi_tiet_don_hang;
DELETE FROM don_hang;
DELETE FROM comment;
DELETE FROM sanpham_manga;
DELETE FROM chap;
DELETE FROM manga;
DELETE FROM theloai;
DELETE FROM taikhoan;
DELETE FROM role;

-- RESET AUTO_INCREMENT
ALTER TABLE chap              AUTO_INCREMENT = 1;
ALTER TABLE chi_tiet_don_hang AUTO_INCREMENT = 1;
ALTER TABLE comment           AUTO_INCREMENT = 1;
ALTER TABLE don_hang          AUTO_INCREMENT = 1;
ALTER TABLE luot_doc          AUTO_INCREMENT = 1;
ALTER TABLE manga             AUTO_INCREMENT = 1;
ALTER TABLE sanpham_manga     AUTO_INCREMENT = 1;
ALTER TABLE taikhoan          AUTO_INCREMENT = 1;
ALTER TABLE theloai           AUTO_INCREMENT = 1;
ALTER TABLE role              AUTO_INCREMENT = 1;

-- THÊM CỘT SLUG NẾU CHƯA CÓ
ALTER TABLE manga ADD COLUMN IF NOT EXISTS slug VARCHAR(255) NULL AFTER manga_name;
ALTER TABLE manga ADD UNIQUE KEY IF NOT EXISTS uq_slug (slug);
ALTER TABLE luot_doc ADD UNIQUE KEY IF NOT EXISTS uq_manga_ngay (id_manga, ngay);

-- INSERT DATA MẪU

-- ROLE
INSERT INTO `role` (`ID_VAITRO`, `TEN_VAITRO`, `TRANGTHAI`) VALUES
(1, 'admin',    1),
(2, 'customer', 1);

-- TÀI KHOẢN (mật khẩu: kurumi1006)
INSERT INTO `taikhoan` (`ID_TAIKHOAN`, `ID_VAITRO`, `TENTAIKHOAN`, `MATKHAU`, `EMAIL`, `NGAYLAP`, `TRANGTHAI`) VALUES
(1, 1, 'admin',       '$2y$10$.pRl60VJXz5Ted2KABGkI.sUrWvG.TWf0nq6p.zoDASXtscPa/qcS', 'admin@gmail.com',                    '2026-03-22 10:51:05', 1),
(2, 1, 'Lylinth',     '$2y$10$.pRl60VJXz5Ted2KABGkI.sUrWvG.TWf0nq6p.zoDASXtscPa/qcS', 'huuphuoc191019@gmail.com',           '2026-03-22 10:51:05', 1),
(3, 2, 'vaalnakynth', '$2y$10$jq0y04f/nK8C.WC.g.LJnub7e6SmMD9054o9Xz4tCRsk.1.RhswsC', 'kurumitokisaki15111006@gmail.com', '2026-03-22 11:51:03', 1);

-- THỂ LOẠI
INSERT INTO `theloai` (`id_theloaimanga`, `ten_theloai`, `mota`, `status`) VALUES
(1, 'Hành Động', 'Truyện hành động gay cấn',  1),
(2, 'Tình Cảm',  'Truyện tình cảm lãng mạn',  1),
(3, 'Hài Hước',  'Truyện hài hước vui vẻ',    1),
(4, 'Phiêu Lưu', 'Truyện phiêu lưu mạo hiểm', 1);

-- MANGA (id_chap tạm = 1, không còn FK ràng buộc nên không lỗi)
INSERT INTO `manga` (`id_manga`, `id_theloaimanga`, `id_taikhoan`, `id_chap`, `manga_name`, `slug`, `mota`, `tacgia`, `anh`, `status`) VALUES
(1, 1, 2, 1, 'Naruto',       'naruto',       'Câu chuyện về ninja Naruto Uzumaki với ước mơ trở thành Hokage.', 'Masashi Kishimoto', 'https://picsum.photos/seed/naruto/200/280',      1),
(2, 2, 2, 1, 'One Piece',    'one-piece',    'Hành trình của Luffy tìm kho báu One Piece.',                    'Eiichiro Oda',      'https://picsum.photos/seed/onepiece/200/280',    1),
(3, 1, 2, 1, 'Demon Slayer', 'demon-slayer', 'Tanjiro chiến đấu với ác quỷ để cứu em gái.',                    'Koyoharu Gotouge',  'https://picsum.photos/seed/demonslayer/200/280', 1),
(4, 3, 2, 1, 'Doraemon',     'doraemon',     'Chú mèo máy đến từ tương lai giúp đỡ Nobita.',                   'Fujiko F. Fujio',   'https://picsum.photos/seed/doraemon/200/280',    1);

-- CHƯƠNG
INSERT INTO `chap` (`id_chap`, `id_manga`, `so_chuong`, `tieu_de_chuong`, `danh_sach_anh`, `ngay_dang`) VALUES
(1, 1, 1, 'Uzumaki Naruto',   '["https://picsum.photos/seed/n1/800/1200","https://picsum.photos/seed/n2/800/1200"]',   NOW()),
(2, 1, 2, 'Kẻ thù đầu tiên', '["https://picsum.photos/seed/n3/800/1200","https://picsum.photos/seed/n4/800/1200"]',   NOW()),
(3, 2, 1, 'Tôi là Luffy',    '["https://picsum.photos/seed/op1/800/1200","https://picsum.photos/seed/op2/800/1200"]', NOW()),
(4, 3, 1, 'Tanjiro và Nezuko','["https://picsum.photos/seed/ds1/800/1200","https://picsum.photos/seed/ds2/800/1200"]', NOW()),
(5, 4, 1, 'Doraemon đến!',   '["https://picsum.photos/seed/dr1/800/1200","https://picsum.photos/seed/dr2/800/1200"]', NOW());

-- CẬP NHẬT id_chap cho manga
UPDATE `manga` SET `id_chap` = 1 WHERE `slug` = 'naruto';
UPDATE `manga` SET `id_chap` = 3 WHERE `slug` = 'one-piece';
UPDATE `manga` SET `id_chap` = 4 WHERE `slug` = 'demon-slayer';
UPDATE `manga` SET `id_chap` = 5 WHERE `slug` = 'doraemon';

-- GÁN THỂ LOẠI
INSERT INTO `manga_theloai` (`id_manga`, `id_theloaimanga`) VALUES
(1, 1), (1, 4),
(2, 1), (2, 4),
(3, 1),
(4, 3);

-- LƯỢT XEM
INSERT INTO `luot_doc` (`id_manga`, `ngay`, `so_luot_doc`) VALUES
(1, CURDATE(),                            500),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 350),
(2, CURDATE(),                            420),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 280),
(3, CURDATE(),                            300),
(4, CURDATE(),                            200);

-- SẢN PHẨM SHOP
INSERT INTO `sanpham_manga` (`id_manga`, `gia_ban`, `so_luong_kho`, `nha_xuat_ban`) VALUES
(1, 45000,  50, 'NXB Kim Đồng'),
(2, 55000,  30, 'NXB Trẻ'),
(3, 65000,  20, 'NXB Kim Đồng'),
(4, 35000, 100, 'NXB Trẻ');

SET FOREIGN_KEY_CHECKS = 1;