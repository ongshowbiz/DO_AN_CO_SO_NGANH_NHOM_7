<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

$errors = [];
$success = false;

// --- XỬ LÝ KHI BẤM LƯU ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ① Lấy dữ liệu từ form
    $manga_name = trim($_POST['manga_name'] ?? '');
    $tacgia     = trim($_POST['tacgia'] ?? '');
    $mota       = trim($_POST['mota'] ?? '');
    $anh        = trim($_POST['anh'] ?? '');       // URL ảnh bìa
    $status     = (int)($_POST['status'] ?? 1);
    $the_loais  = $_POST['the_loai'] ?? [];         // Mảng thể loại được chọn

    // ② Validate
    if (empty($manga_name)) $errors[] = 'Tên truyện không được để trống!';
    if (empty($tacgia))     $errors[] = 'Tác giả không được để trống!';
    if (empty($anh))        $errors[] = 'URL ảnh bìa không được để trống!';
    if (empty($the_loais))  $errors[] = 'Phải chọn ít nhất 1 thể loại!';

    // ③ Tạo slug từ tên truyện
    if (empty($errors)) {
        // Hàm tạo slug tiếng Việt
        $slug = mb_strtolower($manga_name, 'UTF-8');
        $slug = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵ]/u', 'a', $slug);
        $slug = preg_replace('/[èéẹẻẽêềếệểễ]/u', 'e', $slug);
        $slug = preg_replace('/[ìíịỉĩ]/u', 'i', $slug);
        $slug = preg_replace('/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $slug);
        $slug = preg_replace('/[ùúụủũưừứựửữ]/u', 'u', $slug);
        $slug = preg_replace('/[ỳýỵỷỹ]/u', 'y', $slug);
        $slug = preg_replace('/đ/u', 'd', $slug);
        $slug = preg_replace('/[^a-z0-9\s]/u', '', $slug);
        $slug = preg_replace('/\s+/', '-', trim($slug));

        // Kiểm tra slug trùng → thêm số vào cuối
        $slug_goc = $slug;
        $dem = 1;
        while (true) {
            $db->query("SELECT COUNT(*) AS total FROM manga WHERE slug = :slug");
            $db->bind(':slug', $slug);
            $row = $db->single();
            if ($row['total'] == 0) break;
            $slug = $slug_goc . '-' . $dem;
            $dem++;
        }

        // ④ INSERT vào bảng manga
        try {
            $db->beginTransaction();

            $db->query("
                INSERT INTO manga (id_theloaimanga, id_taikhoan, id_chap, manga_name, slug, mota, tacgia, anh, status, create_day)
                VALUES (:id_tl, :id_tk, 0, :name, :slug, :mota, :tacgia, :anh, :status, NOW())
            ");
            $db->bind(':id_tl',  (int)($the_loais[0]));   // thể loại chính (cột id_theloaimanga)
            $db->bind(':id_tk',  (int)($_SESSION['ID_TAIKHOAN'] ?? 1));
            $db->bind(':name',   $manga_name);
            $db->bind(':slug',   $slug);
            $db->bind(':mota',   $mota);
            $db->bind(':tacgia', $tacgia);
            $db->bind(':anh',    $anh);
            $db->bind(':status', $status);
            $db->execute();

            $id_manga_moi = $db->lastInsertId();

            // ⑤ INSERT vào bảng manga_theloai (tất cả thể loại được chọn)
            foreach ($the_loais as $id_tl) {
                $db->query("INSERT IGNORE INTO manga_theloai (id_manga, id_theloaimanga) VALUES (:mid, :tid)");
                $db->bind(':mid', (int)$id_manga_moi);
                $db->bind(':tid', (int)$id_tl);
                $db->execute();
            }

            $db->commit();
            $_SESSION['success_msg'] = "Thêm truyện '{$manga_name}' thành công!";
            header("Location: index.php?method=QL_Manga-manga");
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}

// --- LẤY DANH SÁCH THỂ LOẠI ---
$db->query("SELECT id_theloaimanga, ten_theloai FROM theloai WHERE status = 1 ORDER BY ten_theloai ASC");
$the_loai_list = $db->resultSet();
?>

<div class="um-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 class="um-title" style="margin:0;">
            <i class="fas fa-plus-circle"></i> Thêm Truyện Mới
        </h2>
        <a href="index.php?method=QL_Manga-manga"
           style="background:#6c757d; color:#fff; padding:8px 18px; border-radius:6px;
                  text-decoration:none; font-weight:600;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- HIỂN THỊ LỖI -->
    <?php if (!empty($errors)): ?>
    <div style="background:#f8d7da; color:#721c24; padding:12px 18px; border-radius:6px;
                margin-bottom:18px; border:1px solid #f5c6cb;">
        <strong><i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra:</strong>
        <ul style="margin:8px 0 0 20px;">
            <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- FORM THÊM TRUYỆN -->
    <div style="background:#fff; border-radius:10px; padding:30px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
        <form method="POST">

            <!-- Tên truyện -->
            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-book"></i> Tên truyện <span style="color:red;">*</span>
                </label>
                <input type="text" name="manga_name"
                       value="<?= htmlspecialchars($_POST['manga_name'] ?? '') ?>"
                       placeholder="Nhập tên truyện..."
                       style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                       required>
            </div>

            <!-- Tác giả -->
            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-user-edit"></i> Tác giả <span style="color:red;">*</span>
                </label>
                <input type="text" name="tacgia"
                       value="<?= htmlspecialchars($_POST['tacgia'] ?? '') ?>"
                       placeholder="Nhập tên tác giả..."
                       style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                       required>
            </div>

            <!-- URL ảnh bìa -->
            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-image"></i> URL ảnh bìa <span style="color:red;">*</span>
                </label>
                <input type="text" name="anh" id="inp_anh"
                       value="<?= htmlspecialchars($_POST['anh'] ?? '') ?>"
                       placeholder="https://... (dán link ảnh vào đây)"
                       style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                       oninput="document.getElementById('preview_anh').src=this.value"
                       required>
                <!-- Preview ảnh -->
                <div style="margin-top:10px;">
                    <img id="preview_anh"
                         src="<?= htmlspecialchars($_POST['anh'] ?? '') ?>"
                         alt="Preview"
                         style="width:100px; height:140px; object-fit:cover; border-radius:6px;
                                border:2px dashed #ddd; display:<?= !empty($_POST['anh']) ? 'block' : 'none' ?>;"
                         onerror="this.style.display='none'"
                         onload="this.style.display='block'">
                </div>
            </div>

            <!-- Mô tả -->
            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-align-left"></i> Mô tả
                </label>
                <textarea name="mota" rows="4"
                          placeholder="Nhập mô tả nội dung truyện..."
                          style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;
                                 font-size:14px; resize:vertical;"><?= htmlspecialchars($_POST['mota'] ?? '') ?></textarea>
            </div>

            <!-- Thể loại -->
            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:10px;">
                    <i class="fas fa-tags"></i> Thể loại <span style="color:red;">*</span>
                    <small style="color:#888; font-weight:normal;">(Có thể chọn nhiều)</small>
                </label>
                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    <?php foreach ($the_loai_list as $tl): ?>
                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer;
                                  background:#f8f9fa; padding:6px 14px; border-radius:20px;
                                  border:2px solid #dee2e6; font-size:14px;">
                        <input type="checkbox" name="the_loai[]"
                               value="<?= $tl['id_theloaimanga'] ?>"
                               <?= in_array($tl['id_theloaimanga'], $_POST['the_loai'] ?? []) ? 'checked' : '' ?>
                               style="cursor:pointer;">
                        <?= htmlspecialchars($tl['ten_theloai']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Trạng thái -->
            <div style="margin-bottom:25px;">
                <label style="font-weight:600; display:block; margin-bottom:10px;">
                    <i class="fas fa-toggle-on"></i> Trạng thái
                </label>
                <div style="display:flex; gap:20px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px;">
                        <input type="radio" name="status" value="1"
                               <?= ($_POST['status'] ?? 1) == 1 ? 'checked' : '' ?>>
                        <span style="color:#28a745; font-weight:600;">🟢 Đang ra</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px;">
                        <input type="radio" name="status" value="0"
                               <?= ($_POST['status'] ?? 1) == 0 ? 'checked' : '' ?>>
                        <span style="color:#6c757d; font-weight:600;">⚪ Hoàn thành</span>
                    </label>
                </div>
            </div>

            <!-- Nút submit -->
            <div style="display:flex; gap:12px;">
                <button type="submit"
                        style="background:#28a745; color:#fff; border:none; padding:12px 30px;
                               border-radius:6px; font-size:15px; font-weight:600; cursor:pointer;">
                    <i class="fas fa-save"></i> Lưu truyện
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