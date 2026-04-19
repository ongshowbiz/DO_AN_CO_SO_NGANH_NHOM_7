<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

$id_order = $_GET['id'] ?? 0;

if (!$id_order) {
    echo "<script>alert('Mã đơn hàng không hợp lệ!'); window.location.href='index.php?method=QL_Donhang-order';</script>";
    exit;
}

// 1. Lấy thông tin chung của Đơn Hàng
$db->query("
    SELECT d.*, t.TENTAIKHOAN, t.EMAIL, t.SDT
    FROM don_hang d
    LEFT JOIN taikhoan t ON d.id_taikhoan = t.ID_TAIKHOAN
    WHERE d.id_order = :id
");
$db->bind(':id', $id_order);
$orderInfo = $db->single();

if (!$orderInfo) {
    echo "<script>alert('Đơn hàng không tồn tại!'); window.location.href='index.php?method=QL_Donhang-order';</script>";
    exit;
}

// 2. Lấy danh sách sản phẩm (Items) trong đơn hàng
$db->query("
    SELECT c.*, m.manga_name, m.anh 
    FROM chi_tiet_don_hang c 
    JOIN sanpham_manga s ON c.id_spmanga = s.id_spmanga
    JOIN manga m ON s.id_manga = m.id_manga
    WHERE c.id_order = :id
");
$db->bind(':id', $id_order);
$orderItems = $db->resultSet();
?>

<div class="um-container" style="max-width: 1000px; margin: 0 auto;">
    
    <!-- Tiêu đề và nút Quay Lại -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="um-title" style="margin: 0; color: #333; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-file-invoice" style="color: #17a2b8;"></i> Chi Tiết Đơn Hàng #<?= $orderInfo['id_order'] ?>
        </h2>
        <a href="index.php?method=QL_Donhang-order" style="background-color: #6c757d; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: flex; align-items: center; gap: 5px;">
            <i class="fas fa-arrow-left"></i> Quay Loại Danh Sách
        </a>
    </div>

    <!-- Khối Thông Tin Khách Hàng -->
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <h4 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0; color: #555;">Thông tin giao hàng</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
            <div>
                <p style="margin: 5px 0;"><strong>Khách hàng:</strong> <?= htmlspecialchars($orderInfo['TENTAIKHOAN'] ?? '—') ?></p>
                <p style="margin: 5px 0;"><strong>Email:</strong> <?= htmlspecialchars($orderInfo['EMAIL'] ?? '—') ?></p>
                
            </div>
            <div>
                <p style="margin: 5px 0;"><strong>Ngày đặt:</strong> <?= date('d/m/Y - H:i:s', strtotime($orderInfo['ngay_dat'])) ?></p>
                <p style="margin: 5px 0;"><strong>Địa chỉ nhận hàng:</strong> <?= htmlspecialchars($orderInfo['dia_chi_giao_hang']) ?></p>
                <p style="margin: 5px 0;"><strong>Trạng thái TT:</strong> 
                    <?php if ($orderInfo['trang_thai_thanh_toan'] == 1): ?>
                        <span style="color: #28a745; font-weight: bold;"><i class="fas fa-check"></i> Đã thanh toán</span>
                    <?php else: ?>
                        <span style="color: #dc3545; font-weight: bold;"><i class="fas fa-times"></i> Chưa thanh toán</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Khối Danh Sách Sản Phẩm -->
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h4 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0; color: #555;">Sản phẩm đã đặt</h4>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <thead>
                <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 12px; text-align: left; width: 60px;">Ảnh</th>
                    <th style="padding: 12px; text-align: left;">Tên Truyện</th>
                    <th style="padding: 12px; text-align: center;">Đơn Giá (lúc mua)</th>
                    <th style="padding: 12px; text-align: center;">Số Lượng</th>
                    <th style="padding: 12px; text-align: right;">Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $tong_tien_tinh_duoc = 0;
                foreach($orderItems as $item): 
                    $thanh_tien = $item['gia_tai_thoi_diem_mua'] * $item['so_luong'];
                    $tong_tien_tinh_duoc += $thanh_tien;
                ?>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 12px;">
                        <img src="<?= htmlspecialchars($item['anh']) ?>" alt="Manga" style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    </td>
                    <td style="padding: 12px; vertical-align: middle;">
                        <strong style="color: #2c3e50; font-size: 1.1em;"><?= htmlspecialchars($item['manga_name']) ?></strong>
                    </td>
                    <td style="padding: 12px; vertical-align: middle; text-align: center;">
                        <?= number_format($item['gia_tai_thoi_diem_mua'], 0, ',', '.') ?>đ
                    </td>
                    <td style="padding: 12px; vertical-align: middle; text-align: center; font-weight: bold;">
                        x<?= $item['so_luong'] ?>
                    </td>
                    <td style="padding: 12px; vertical-align: middle; text-align: right; color: #e53e3e; font-weight: bold;">
                        <?= number_format($thanh_tien, 0, ',', '.') ?>đ
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(empty($orderItems)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: gray;">
                        Chưa có sản phẩm nào trong đơn hàng này (Dữ liệu lỗi).
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Tổng Kết Tiền -->
        <div style="margin-top: 20px; border-top: 2px dashed #ddd; padding-top: 15px; text-align: right;">
            <p style="margin: 5px 0; font-size: 1.1em;">
                Tổng giá trị items: <strong><?= number_format($tong_tien_tinh_duoc, 0, ',', '.') ?>đ</strong>
            </p>
            <p style="margin: 5px 0; font-size: 1.3em; color: #e53e3e;">
                Tổng Tiền Thanh Toán: <strong><?= number_format($orderInfo['tong_tien'], 0, ',', '.') ?>đ</strong>
            </p>
        </div>
    </div>
</div>
