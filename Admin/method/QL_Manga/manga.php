<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

// --- XỬ LÝ XÓA TRUYỆN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $id_manga = (int)($_POST['id_manga'] ?? 0);

    if ($_POST['action'] == 'xoa_truyen' && $id_manga > 0) {
        // Xóa truyện (chap sẽ tự xóa theo vì có ON DELETE CASCADE)
        $db->query("DELETE FROM manga WHERE id_manga = :id");
        $db->bind(':id', $id_manga);
        $db->execute();
        $_SESSION['success_msg'] = "Đã xóa truyện #{$id_manga} thành công!";
        header("Location: index.php?method=QL_Manga-manga");
        exit;
    }

    if ($_POST['action'] == 'doi_trangthai' && $id_manga > 0) {
        $status_hien_tai = (int)($_POST['status_hien_tai'] ?? 1);
        $status_moi = $status_hien_tai == 1 ? 0 : 1;
        $db->query("UPDATE manga SET status = :s WHERE id_manga = :id");
        $db->bind(':s', $status_moi);
        $db->bind(':id', $id_manga);
        $db->execute();
        $ten_trang_thai = $status_moi == 1 ? 'Đang ra' : 'Hoàn thành';
        $_SESSION['success_msg'] = "Đã đổi trạng thái thành '{$ten_trang_thai}'!";
        header("Location: index.php?method=QL_Manga-manga");
        exit;
    }
}

// --- LẤY DANH SÁCH TRUYỆN ---
$db->query("
    SELECT
        m.id_manga, m.manga_name, m.tacgia, m.anh, m.status, m.create_day,
        GROUP_CONCAT(DISTINCT tl.ten_theloai SEPARATOR ', ') AS the_loai,
        COUNT(DISTINCT c.id_chap) AS so_chuong,
        COALESCE(SUM(ld.so_luot_doc), 0) AS tong_view
    FROM manga m
    LEFT JOIN manga_theloai mt ON mt.id_manga = m.id_manga
    LEFT JOIN theloai tl ON tl.id_theloaimanga = mt.id_theloaimanga
    LEFT JOIN chap c ON c.id_manga = m.id_manga
    LEFT JOIN luot_doc ld ON ld.id_manga = m.id_manga
    GROUP BY m.id_manga
    ORDER BY m.create_day DESC
");
$mangas = $db->resultSet();
?>

<div class="um-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 class="um-title" style="margin:0;">
            <i class="fas fa-book"></i> Quản Lý Truyện
        </h2>
        <?php if ($_SESSION['ID_VAITRO'] == 3): // tại chỉ có supplier mới có quyền thêm truyện, chứ admin chỉ có nhận, sửa, xóa chuyện?>
        <a href="index.php?method=QL_Manga-them_truyen"
           style="background:#28a745; color:#fff; padding:8px 18px; border-radius:6px;
                  text-decoration:none; font-weight:600;">
            <i class="fas fa-plus"></i> Thêm truyện mới
        </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div style="background:#d4edda; color:#155724; padding:12px 18px; border-radius:6px;
                    margin-bottom:18px; border:1px solid #c3e6cb;">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['success_msg'] ?>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <div class="um-table-wrapper">
        <table class="um-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh bìa</th>
                    <th>Tên truyện</th>
                    <th>Tác giả</th>
                    <th>Thể loại</th>
                    <th class="text-center">Số chương</th>
                    <th class="text-center">Lượt xem</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mangas as $m): ?>
                <tr>
                    <td><strong>#<?= $m['id_manga'] ?></strong></td>

                    <td>
                        <img src="<?= htmlspecialchars($m['anh']) ?>"
                             alt="bia"
                             style="width:50px; height:70px; object-fit:cover; border-radius:4px;"
                             onerror="this.src='../Anh/sad.png'">
                    </td>

                    <td><strong><?= htmlspecialchars($m['manga_name']) ?></strong></td>

                    <td><?= htmlspecialchars($m['tacgia'] ?? '—') ?></td>

                    <td style="font-size:12px; color:#666;">
                        <?= htmlspecialchars($m['the_loai'] ?? 'Chưa phân loại') ?>
                    </td>

                    <td class="text-center">
                        <span style="background:#17a2b8; color:#fff; padding:2px 10px;
                                     border-radius:12px; font-size:13px;">
                            <?= (int)$m['so_chuong'] ?> chương
                        </span>
                    </td>

                    <td class="text-center">
                        <i class="fas fa-eye" style="color:#888;"></i>
                        <?= number_format((int)$m['tong_view']) ?>
                    </td>

                    <td class="text-center">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="doi_trangthai">
                            <input type="hidden" name="id_manga" value="<?= $m['id_manga'] ?>">
                            <input type="hidden" name="status_hien_tai" value="<?= $m['status'] ?>">
                            <button type="submit"
                                style="border:none; cursor:pointer; padding:4px 12px;
                                       border-radius:12px; font-size:12px; font-weight:600;
                                       background:<?= $m['status'] ? '#28a745' : '#6c757d' ?>;
                                       color:#fff;">
                                <?= $m['status'] ? 'Đang ra' : 'Hoàn thành' ?>
                            </button>
                        </form>
                    </td>

                    <td class="text-center" style="white-space:nowrap;">
                        <!-- Nút Quản lý chương -->
                        <a href="index.php?method=QL_Manga-chuong&id_manga=<?= $m['id_manga'] ?>"
                           style="display:inline-block; background:#17a2b8; color:#fff;
                                  padding:5px 10px; border-radius:5px; font-size:12px;
                                  text-decoration:none; margin-right:4px;"
                           title="Quản lý chương">
                            <i class="fas fa-list"></i> Chương
                        </a>

                        <!-- Nút Sửa -->
                        <a href="index.php?method=QL_Manga-sua_truyen&id_manga=<?= $m['id_manga'] ?>"
                           style="display:inline-block; background:#ffc107; color:#212529;
                                  padding:5px 10px; border-radius:5px; font-size:12px;
                                  text-decoration:none; margin-right:4px;"
                           title="Sửa truyện">
                            <i class="fas fa-edit"></i> Sửa
                        </a>

                        <!-- Nút Xóa -->
                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('CẢNH BÁO: Xóa truyện sẽ xóa luôn TẤT CẢ chương! Bạn chắc chắn?');">
                            <input type="hidden" name="action" value="xoa_truyen">
                            <input type="hidden" name="id_manga" value="<?= $m['id_manga'] ?>">
                            <button type="submit"
                                style="background:#dc3545; color:#fff; border:none;
                                       padding:5px 10px; border-radius:5px; font-size:12px;
                                       cursor:pointer;"
                                title="Xóa truyện">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($mangas)): ?>
        <div class="um-empty">
            <i class="fas fa-book-open"></i>
            <p>Chưa có truyện nào. <a href="index.php?method=QL_Manga-them_truyen">Thêm ngay</a></p>
        </div>
        <?php endif; ?>
    </div>
</div>