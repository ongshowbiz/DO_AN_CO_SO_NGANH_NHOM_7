<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

$category_id = (int)($_GET['id'] ?? 0);
if ($category_id == 0) {
    header('Location: index.php?method=category-list');
    exit();
}

// --- LẤY THÔNG TIN THỂ LOẠI HIỆN TẠI ---
$db->query('SELECT id_theloaimanga, ten_theloai, mota, status FROM theloai WHERE id_theloaimanga = :id LIMIT 1');
$db->bind(':id', $category_id);
$category = $db->single();

if (!$category) {
    header('Location: index.php?method=category-list');
    exit();
}

$errors = [];
$category_name = $category['ten_theloai'];
$category_desc = $category['mota'];
$status        = $category['status'];

// --- XỬ LÝ KHI BẤM LƯU ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = trim($_POST['ten_theloai'] ?? '');
    $category_desc = trim($_POST['mota'] ?? '');
    $status        = (int)($_POST['status'] ?? 1);

    if (empty($category_name)) { 
        $errors[] = "Tên thể loại không được để trống!"; 
    }

    // Kiểm tra trùng tên trừ chính nó
    $db->query('SELECT id_theloaimanga FROM theloai WHERE ten_theloai = :name AND id_theloaimanga != :id');
    $db->bind(':name', $category_name);
    $db->bind(':id', $category_id);
    if ($db->single()) {
        $errors[] = "Thể loại '{$category_name}' đã tồn tại hệ thống!";
    }

    if (empty($errors)) {
        try {
            $db->query('UPDATE theloai SET ten_theloai = :name, mota = :mota, status = :status WHERE id_theloaimanga = :id');
            $db->bind(':name', $category_name);
            $db->bind(':mota', $category_desc);
            $db->bind(':status', $status);
            $db->bind(':id', $category_id);

            if ($db->execute()) {
                $_SESSION['success_message'] = "Cập nhật thể loại '{$category_name}' thành công!";
                header('Location: index.php?method=category-list');
                exit();
            } else {
                $errors[] = "Có lỗi xảy ra, không thể cập nhật.";
            }
        } catch (PDOException $e) {
            $errors[] = "Lỗi CSDL: " . $e->getMessage();
        }
    }
}
?>

<div class="um-container" style="padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 class="um-title" style="margin:0; font-size: 24px; font-weight: bold;">
            <i class="fas fa-edit"></i> Chỉnh sửa Thể loại — 
            <span style="color:#ffc107;"><?= htmlspecialchars($category['ten_theloai']) ?></span>
        </h2>
        <a href="index.php?page=category-list"
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
                    <i class="fas fa-tag"></i> Tên Thể loại <span style="color:red;">*</span>
                </label>
                <input type="text" name="ten_theloai"
                       value="<?= htmlspecialchars($category_name) ?>"
                       style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                       required>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-align-left"></i> Mô tả
                </label>
                <textarea name="mota" rows="4"
                          style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;
                                 font-size:14px; resize:vertical;"><?= htmlspecialchars($category_desc) ?></textarea>
            </div>

            <div style="margin-bottom:25px;">
                <label style="font-weight:600; display:block; margin-bottom:10px;">
                    <i class="fas fa-toggle-on"></i> Trạng thái hiển thị
                </label>
                <div style="display:flex; gap:20px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px;">
                        <input type="radio" name="status" value="1" <?= $status == 1 ? 'checked' : '' ?>>
                        <span style="color:#28a745; font-weight:600;">🟢 Hiện (Active)</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px;">
                        <input type="radio" name="status" value="0" <?= $status == 0 ? 'checked' : '' ?>>
                        <span style="color:#6c757d; font-weight:600;">⚪ Ẩn (Hidden)</span>
                    </label>
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit"
                        style="background:#ffc107; color:#212529; border:none; padding:12px 30px;
                               border-radius:6px; font-size:15px; font-weight:600; cursor:pointer;">
                    <i class="fas fa-save"></i> Lưu cập nhật
                </button>
                <a href="index.php?method=category-list"
                   style="background:#6c757d; color:#fff; padding:12px 24px; border-radius:6px;
                          text-decoration:none; font-size:15px; font-weight:600;">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>