<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

$id_manga = (int)($_GET['id_manga'] ?? 0);
if ($id_manga == 0) {
    header("Location: index.php?method=QL_Manga-manga");
    exit;
}

// --- LẤY THÔNG TIN TRUYỆN ---
$db->query("SELECT id_manga, manga_name, slug FROM manga WHERE id_manga = :id LIMIT 1");
$db->bind(':id', $id_manga);
$manga = $db->single();
if (!$manga) {
    header("Location: index.php?method=QL_Manga-manga");
    exit;
}

// --- XỬ LÝ XÓA CHƯƠNG ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'xoa_chuong') {
        $id_chap = (int)($_POST['id_chap'] ?? 0);
        if ($id_chap > 0) {
            $db->query("DELETE FROM chap WHERE id_chap = :id AND id_manga = :mid");
            $db->bind(':id',  $id_chap);
            $db->bind(':mid', $id_manga);
            $db->execute();
            $_SESSION['success_msg'] = "Đã xóa chương thành công!";
            header("Location: index.php?method=QL_Manga-chuong&id_manga={$id_manga}");
            exit;
        }
    }
}

// --- LẤY DANH SÁCH CHƯƠNG ---
$db->query("
    SELECT id_chap, so_chuong, tieu_de_chuong, ngay_dang,
           CASE WHEN danh_sach_anh IS NOT NULL AND danh_sach_anh != 'null' THEN 'Ảnh'
                WHEN noi_dung IS NOT NULL THEN 'Chữ'
                ELSE 'Trống' END AS loai_noi_dung,
           CASE WHEN danh_sach_anh IS NOT NULL AND danh_sach_anh != 'null'
                THEN JSON_LENGTH(danh_sach_anh) ELSE 0 END AS so_anh
    FROM chap
    WHERE id_manga = :mid
    ORDER BY so_chuong ASC
");
$db->bind(':mid', $id_manga);
$chapters = $db->resultSet();
?>

<div class="um-container">
    <!-- BREADCRUMB -->
    <div style="margin-bottom:15px; font-size:14px; color:#666;">
        <a href="index.php?method=QL_Manga-manga" style="color:#007bff; text-decoration:none;">
            <i class="fas fa-book"></i> Danh sách truyện
        </a>
        <i class="fas fa-chevron-right" style="margin:0 8px; font-size:11px;"></i>
        <strong><?= htmlspecialchars($manga['manga_name']) ?></strong>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 class="um-title" style="margin:0;">
            <i class="fas fa-list-ol"></i> Quản lý chương —
            <span style="color:#007bff;"><?= htmlspecialchars($manga['manga_name']) ?></span>
        </h2>
        <a href="index.php?method=QL_Manga-them_chuong&id_manga=<?= $id_manga ?>"
           style="background:#28a745; color:#fff; padding:8px 18px; border-radius:6px;
                  text-decoration:none; font-weight:600;">
            <i class="fas fa-plus"></i> Thêm chương mới
        </a>
    </div>

    <?php if (isset($_SESSION['success_msg'])): ?>
    <div style="background:#d4edda; color:#155724; padding:12px 18px; border-radius:6px;
                margin-bottom:18px; border:1px solid #c3e6cb;">
        <i class="fas fa-check-circle"></i> <?= $_SESSION['success_msg'] ?>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <!-- THỐNG KÊ NHANH -->
    <div style="display:flex; gap:15px; margin-bottom:20px;">
        <div style="background:#fff; border-radius:8px; padding:15px 25px;
                    box-shadow:0 2px 6px rgba(0,0,0,0.08); text-align:center;">
            <div style="font-size:28px; font-weight:700; color:#007bff;"><?= count($chapters) ?></div>
            <div style="font-size:13px; color:#666;">Tổng số chương</div>
        </div>
        <?php if (!empty($chapters)): ?>
        <div style="background:#fff; border-radius:8px; padding:15px 25px;
                    box-shadow:0 2px 6px rgba(0,0,0,0.08); text-align:center;">
            <div style="font-size:28px; font-weight:700; color:#28a745;">
                <?= $chapters[count($chapters)-1]['so_chuong'] ?>
            </div>
            <div style="font-size:13px; color:#666;">Chương mới nhất</div>
        </div>
        <?php endif; ?>
    </div>

    <div class="um-table-wrapper">
        <table class="um-table">
            <thead>
                <tr>
                    <th>Số chương</th>
                    <th>Tiêu đề</th>
                    <th class="text-center">Loại nội dung</th>
                    <th class="text-center">Số ảnh/Nội dung</th>
                    <th class="text-center">Ngày đăng</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chapters as $chap): ?>
                <tr>
                    <td>
                        <span style="background:#007bff; color:#fff; padding:3px 12px;
                                     border-radius:12px; font-weight:600; font-size:13px;">
                            Chương <?= $chap['so_chuong'] ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($chap['tieu_de_chuong']) ?></td>
                    <td class="text-center">
                        <?php if ($chap['loai_noi_dung'] == 'Ảnh'): ?>
                        <span style="background:#17a2b8; color:#fff; padding:2px 10px;
                                     border-radius:12px; font-size:12px;">
                            <i class="fas fa-image"></i> Manga (ảnh)
                        </span>
                        <?php elseif ($chap['loai_noi_dung'] == 'Chữ'): ?>
                        <span style="background:#6f42c1; color:#fff; padding:2px 10px;
                                     border-radius:12px; font-size:12px;">
                            <i class="fas fa-font"></i> Novel (chữ)
                        </span>
                        <?php else: ?>
                        <span style="background:#6c757d; color:#fff; padding:2px 10px;
                                     border-radius:12px; font-size:12px;">Trống</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center" style="color:#666; font-size:13px;">
                        <?php if ($chap['loai_noi_dung'] == 'Ảnh'): ?>
                            <i class="fas fa-images"></i> <?= $chap['so_anh'] ?> ảnh
                        <?php elseif ($chap['loai_noi_dung'] == 'Chữ'): ?>
                            <i class="fas fa-check" style="color:#28a745;"></i> Có nội dung
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td class="text-center" style="font-size:13px; color:#666;">
                        <i class="far fa-calendar-alt"></i>
                        <?= date('d/m/Y', strtotime($chap['ngay_dang'])) ?>
                    </td>
                    <td class="text-center" style="white-space:nowrap;">
                        <!-- Nút Sửa chương -->
                        <a href="index.php?method=QL_Manga-sua_chuong&id_chap=<?= $chap['id_chap'] ?>&id_manga=<?= $id_manga ?>"
                           style="display:inline-block; background:#ffc107; color:#212529;
                                  padding:5px 10px; border-radius:5px; font-size:12px;
                                  text-decoration:none; margin-right:4px;">
                            <i class="fas fa-edit"></i> Sửa
                        </a>

                        <!-- Nút Xóa chương -->
                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('Xóa chương <?= $chap['so_chuong'] ?>: <?= htmlspecialchars(addslashes($chap['tieu_de_chuong'])) ?>?');">
                            <input type="hidden" name="action" value="xoa_chuong">
                            <input type="hidden" name="id_chap" value="<?= $chap['id_chap'] ?>">
                            <button type="submit"
                                    style="background:#dc3545; color:#fff; border:none;
                                           padding:5px 10px; border-radius:5px; font-size:12px;
                                           cursor:pointer;">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($chapters)): ?>
        <div class="um-empty">
            <i class="fas fa-book-open"></i>
            <p>Truyện này chưa có chương nào.
               <a href="index.php?method=QL_Manga-them_chuong&id_manga=<?= $id_manga ?>">Thêm chương đầu tiên</a>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>