<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

$id_manga = (int)($_GET['id_manga'] ?? 0);
if ($id_manga == 0) {
    header("Location: index.php?method=QL_Manga-manga");
    exit;
}

// --- LẤY THÔNG TIN TRUYỆN HIỆN TẠI ---
$db->query("SELECT * FROM manga WHERE id_manga = :id LIMIT 1");
$db->bind(':id', $id_manga);
$manga = $db->single();
if (!$manga) {
    header("Location: index.php?method=QL_Manga-manga");
    exit;
}

// --- LẤY THỂ LOẠI ĐANG ĐƯỢC CHỌN ---
$db->query("SELECT id_theloaimanga FROM manga_theloai WHERE id_manga = :id");
$db->bind(':id', $id_manga);
$the_loai_hien_tai = array_column($db->resultSet(), 'id_theloaimanga');

$errors = [];

// --- XỬ LÝ KHI BẤM LƯU ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $manga_name = trim($_POST['manga_name'] ?? '');
    $tacgia     = trim($_POST['tacgia'] ?? '');
    $mota       = trim($_POST['mota'] ?? '');
    $anh        = trim($_POST['anh'] ?? '');
    $status     = (int)($_POST['status'] ?? 1);
    $the_loais  = $_POST['the_loai'] ?? [];

    if (empty($manga_name)) $errors[] = 'Tên truyện không được để trống!';
    if (empty($tacgia))     $errors[] = 'Tác giả không được để trống!';
    if (empty($anh))        $errors[] = 'URL ảnh bìa không được để trống!';
    if (empty($the_loais))  $errors[] = 'Phải chọn ít nhất 1 thể loại!';

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // UPDATE bảng manga
            $db->query("
                UPDATE manga
                SET manga_name = :name, tacgia = :tacgia, mota = :mota,
                    anh = :anh, status = :status, id_theloaimanga = :id_tl
                WHERE id_manga = :id
            ");
            $db->bind(':name',   $manga_name);
            $db->bind(':tacgia', $tacgia);
            $db->bind(':mota',   $mota);
            $db->bind(':anh',    $anh);
            $db->bind(':status', $status);
            $db->bind(':id_tl',  (int)$the_loais[0]);
            $db->bind(':id',     $id_manga);
            $db->execute();

            // Xóa hết thể loại cũ rồi thêm lại
            $db->query("DELETE FROM manga_theloai WHERE id_manga = :id");
            $db->bind(':id', $id_manga);
            $db->execute();

            foreach ($the_loais as $id_tl) {
                $db->query("INSERT INTO manga_theloai (id_manga, id_theloaimanga) VALUES (:mid, :tid)");
                $db->bind(':mid', $id_manga);
                $db->bind(':tid', (int)$id_tl);
                $db->execute();
            }

            $db->commit();
            $_SESSION['success_msg'] = "Cập nhật truyện '{$manga_name}' thành công!";
            header("Location: index.php?method=QL_Manga-manga");
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }

    // Nếu có lỗi, giữ lại dữ liệu vừa nhập
    $manga['manga_name'] = $_POST['manga_name'] ?? $manga['manga_name'];
    $manga['tacgia']     = $_POST['tacgia'] ?? $manga['tacgia'];
    $manga['mota']       = $_POST['mota'] ?? $manga['mota'];
    $manga['anh']        = $_POST['anh'] ?? $manga['anh'];
    $manga['status']     = $_POST['status'] ?? $manga['status'];
    $the_loai_hien_tai   = array_map('intval', $_POST['the_loai'] ?? []);
}

// --- LẤY DANH SÁCH THỂ LOẠI ---
$db->query("SELECT id_theloaimanga, ten_theloai FROM theloai WHERE status = 1 ORDER BY ten_theloai ASC");
$the_loai_list = $db->resultSet();
?>

<div class="um-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 class="um-title" style="margin:0;">
            <i class="fas fa-edit"></i> Sửa Truyện —
            <span style="color:#ffc107;"><?= htmlspecialchars($manga['manga_name']) ?></span>
        </h2>
        <a href="index.php?method=QL_Manga-manga"
           style="background:#6c757d; color:#fff; padding:8px 18px; border-radius:6px;
                  text-decoration:none; font-weight:600;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div style="background:#f8d7da; color:#721c24; padding:12px 18px; border-radius:6px;
                margin-bottom:18px; border:1px solid #f5c6cb;">
        <strong><i class="fas fa-exclamation-triangle"></i> Có lỗi:</strong>
        <ul style="margin:8px 0 0 20px;">
            <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div style="background:#fff; border-radius:10px; padding:30px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
        <form method="POST">

            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-book"></i> Tên truyện <span style="color:red;">*</span>
                </label>
                <input type="text" name="manga_name"
                       value="<?= htmlspecialchars($manga['manga_name']) ?>"
                       style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                       required>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-user-edit"></i> Tác giả <span style="color:red;">*</span>
                </label>
                <input type="text" name="tacgia"
                       value="<?= htmlspecialchars($manga['tacgia'] ?? '') ?>"
                       style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                       required>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-image"></i> URL ảnh bìa <span style="color:red;">*</span>
                </label>
                <input type="text" name="anh"
                       value="<?= htmlspecialchars($manga['anh']) ?>"
                       style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                       oninput="document.getElementById('preview_anh').src=this.value"
                       required>
                <div style="margin-top:10px;">
                    <img id="preview_anh" src="<?= htmlspecialchars($manga['anh']) ?>"
                         style="width:100px; height:140px; object-fit:cover; border-radius:6px; border:2px solid #ddd;"
                         onerror="this.style.opacity='0.3'" onload="this.style.opacity='1'">
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-align-left"></i> Mô tả
                </label>
                <textarea name="mota" rows="4"
                          style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;
                                 font-size:14px; resize:vertical;"><?= htmlspecialchars($manga['mota'] ?? '') ?></textarea>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:10px;">
                    <i class="fas fa-tags"></i> Thể loại <span style="color:red;">*</span>
                </label>
                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    <?php foreach ($the_loai_list as $tl): ?>
                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer;
                                  background:#f8f9fa; padding:6px 14px; border-radius:20px;
                                  border:2px solid #dee2e6; font-size:14px;">
                        <input type="checkbox" name="the_loai[]"
                               value="<?= $tl['id_theloaimanga'] ?>"
                               <?= in_array($tl['id_theloaimanga'], $the_loai_hien_tai) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($tl['ten_theloai']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="margin-bottom:25px;">
                <label style="font-weight:600; display:block; margin-bottom:10px;">
                    <i class="fas fa-toggle-on"></i> Trạng thái
                </label>
                <div style="display:flex; gap:20px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px;">
                        <input type="radio" name="status" value="1" <?= $manga['status'] == 1 ? 'checked' : '' ?>>
                        <span style="color:#28a745; font-weight:600;">🟢 Đang ra</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px;">
                        <input type="radio" name="status" value="0" <?= $manga['status'] == 0 ? 'checked' : '' ?>>
                        <span style="color:#6c757d; font-weight:600;">⚪ Hoàn thành</span>
                    </label>
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit"
                        style="background:#ffc107; color:#212529; border:none; padding:12px 30px;
                               border-radius:6px; font-size:15px; font-weight:600; cursor:pointer;">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
                <a href="index.php?method=QL_Manga-manga"
                   style="background:#6c757d; color:#fff; padding:12px 24px; border-radius:6px;
                          text-decoration:none; font-size:15px; font-weight:600;">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>