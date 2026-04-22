<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();
require_once 'manga_list.php'; 
?>

<div class="um-table-wrapper">
    <table class="um-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh bìa</th>
                <th>Tên truyện</th>
                <th>Giá bán</th>
                <th class="text-center">Kho hàng</th> 
                <th class="text-center">Số chương</th>
                <th class="text-center">Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($mangas)): ?>
                <tr>
                    <td colspan="8" class="text-center">Không có truyện nào đang bán hoặc còn hàng.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($mangas as $m): ?>
                <tr>
                    <td><strong>#<?= $m['id_manga'] ?></strong></td>
                    <td>
                        <img src="<?= htmlspecialchars($m['anh']) ?>" 
                             style="width:50px; height:70px; object-fit:cover; border-radius:4px;"
                             onerror="this.src='../Anh/sad.png'">
                    </td>
                    <td><strong><?= htmlspecialchars($m['manga_name']) ?></strong></td>
                    
                    <td><span style="color: #e74c3c; font-weight: bold;"><?= number_format($m['gia_ban']) ?>đ</span></td>

                    <td class="text-center">
                        <span style="background:#28a745; color:#fff; padding:2px 10px; border-radius:12px; font-size:13px;">
                            Còn <?= (int)$m['so_luong_kho'] ?> cuốn
                        </span>
                    </td>

                    <td class="text-center"><?= (int)$m['chapter_count'] ?> chương</td>

                    <td class="text-center">
                        <span style="background:<?= $m['status'] ? '#28a745' : '#6c757d' ?>; color:#fff; padding:4px 12px; border-radius:12px; font-size:12px;">
                            <?= $m['status'] ? 'Đang ra' : 'Hoàn thành' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>