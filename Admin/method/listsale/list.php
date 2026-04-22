<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();
// Đảm bảo session đã được start ở index.php
require_once 'manga_sale_logic.php'; 

$role_id = $_SESSION['role_id'] ?? 0; 
?>

<div class="um-container" style="padding: 20px;">
    <div class="um-header" style="margin-bottom: 20px;">
        <h2><i class="fas fa-coins"></i> Quản lý Giá & Kho hàng</h2>
        <p class="text-muted">Cập nhật thông tin bán hàng cho các đầu truyện.</p>
    </div>

    <div class="um-table-wrapper" style="background:#fff; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1); overflow:hidden;">
        <table class="um-table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding:15px;">ID</th>
                    <th>Ảnh</th>
                    <th>Tên truyện</th>
                    <th>Giá bán (VNĐ)</th>
                    <th class="text-center">Số lượng kho</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mangas)): ?>
                    <tr><td colspan="6" class="text-center" style="padding:20px;">Chưa có dữ liệu truyện.</td></tr>
                <?php else: ?>
                    <?php foreach ($mangas as $m): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding:15px;">#<?= $m['id_manga'] ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($m['anh']) ?>" 
                                 style="width:45px; height:60px; object-fit:cover; border-radius:4px;"
                                 onerror="this.src='../Anh/default.png'">
                        </td>
                        <td><strong><?= htmlspecialchars($m['manga_name']) ?></strong></td>
                        
                        <td>
                            <span style="color:#d9534f; font-weight:bold;">
                                <?= isset($m['gia_ban']) ? number_format($m['gia_ban']) . 'đ' : '<i style="color:#999">Chưa đặt giá</i>' ?>
                            </span>
                        </td>
                            <td class="text-center">
                                <?php 
                                // Kiểm tra nếu tồn tại số lượng và số lượng phải lớn hơn 0
                                if (isset($m['so_luong_kho']) && $m['so_luong_kho'] > 0): 
                                ?>
                                    <span class="badge" style="background:<?= $m['so_luong_kho'] > 10 ? '#28a745' : '#f0ad4e' ?>; color:#fff; padding:5px 10px; border-radius:12px;">
                                        <?= $m['so_luong_kho'] ?> cuốn
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background:#dc3545; color:#fff; padding:5px 10px; border-radius:12px;">
                                        Hết hàng
                                    </span>
                                <?php endif; ?>
                            </td>

                        <td class="text-center">
                            <a href="index.php?method=listsale-edit&id=<?= $m['id_manga'] ?>" 
                               style="background:#007bff; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none; font-size:13px;">
                                <i class="fas fa-edit"></i> Cập nhật
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>