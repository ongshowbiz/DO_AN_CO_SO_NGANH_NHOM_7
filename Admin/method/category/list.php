<?php
require_once 'function/categories_list.php'; 
?>

<div class="um-container" style="padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 class="um-title" style="margin:0; font-size: 24px; font-weight: bold;">
            <i class="fas fa-tags"></i> Quản Lý Thể Loại Truyện
        </h2>
        <a href="index.php?method=category-add"
           style="background:#28a745; color:#fff; padding:8px 18px; border-radius:6px;
                  text-decoration:none; font-weight:600;">
            <i class="fas fa-plus"></i> Thêm thể loại mới
        </a>
    </div>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div style="background:#d4edda; color:#155724; padding:12px 18px; border-radius:6px;
                    margin-bottom:18px; border:1px solid #c3e6cb;">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['success_message'] ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="um-table-wrapper" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
        <table class="um-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left;">
                    <th style="padding: 12px; width: 80px;">ID</th>
                    <th style="padding: 12px;">Tên Thể Loại</th>
                    <th style="padding: 12px;">Mô tả</th>
                    <th style="padding: 12px;" class="text-center">Số lượng truyện</th>
                    <th style="padding: 12px;" class="text-center">Trạng thái</th>
                    <th style="padding: 12px;" class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center; color: #666;">
                            <i class="fas fa-folder-open"></i> Chưa có thể loại nào được thêm.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;"><strong>#<?= $category['id_theloaimanga'] ?></strong></td>
                        
                        <td style="padding: 12px;">
                            <strong><?= htmlspecialchars($category['ten_theloai']) ?></strong>
                        </td>

                        <td style="padding: 12px; color: #666; font-size: 13px;">
                            <?= htmlspecialchars($category['mota'] ?? '—') ?>
                        </td>

                        <td class="text-center" style="padding: 12px; text-align: center;">
                            <span style="background:#17a2b8; color:#fff; padding:2px 12px;
                                         border-radius:12px; font-size:13px; font-weight: 600;">
                                <i class="fas fa-book"></i> <?= (int)$category['film_count'] ?> truyện
                            </span>
                        </td>

                        <td class="text-center" style="padding: 12px; text-align: center;">
                            <span style="padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;
                                         background: <?= ($category['status'] == 1) ? '#28a745' : '#6c757d' ?>; color: #fff;">
                                <?= ($category['status'] == 1) ? 'Hiện' : 'Ẩn' ?>
                            </span>
                        </td>

                        <td class="text-center" style="padding: 12px; text-align: center; white-space:nowrap;">
                            <a href="index.php?method=category-edit&id=<?= $category['id_theloaimanga'] ?>"
                               style="display:inline-block; background:#ffc107; color:#212529;
                                      padding:5px 10px; border-radius:5px; font-size:12px;
                                      text-decoration:none; margin-right:4px;"
                               title="Sửa">
                                <i class="fas fa-edit"></i> Sửa
                            </a>

                            <a href="method/category/delete.php?id=<?= $category['id_theloaimanga'] ?>" 
                               style="display:inline-block; background:#dc3545; color:#fff;
                                      padding:5px 10px; border-radius:5px; font-size:12px;
                                      text-decoration:none;"
                               onclick="return confirm('Bạn có chắc muốn xóa thể loại này?');"
                               title="Xóa">
                                <i class="fas fa-trash"></i> Xóa
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($pagination_html)): ?>
            <div style="padding: 15px; border-top: 1px solid #eee; background: #fcfcfc;">
                <?= $pagination_html ?>
            </div>
        <?php endif; ?>
    </div>
</div>