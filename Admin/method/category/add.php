<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

$category_name = '';
$category_desc = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = trim($_POST['ten_theloai'] ?? '');
    $category_desc = trim($_POST['mota'] ?? '');

    if (empty($category_name)) { 
        $errors[] = "Tên thể loại không được để trống!"; 
    }

    // Kiểm tra trùng tên
    $db->query('SELECT id_theloaimanga FROM theloai WHERE ten_theloai = :name');
    $db->bind(':name', $category_name);
    if ($db->single()) {
        $errors[] = "Thể loại '{$category_name}' đã tồn tại trong hệ thống.";
    }

    if (empty($errors)) {
        try {
            $db->query('INSERT INTO theloai (ten_theloai, mota, status) VALUES (:name, :mota, 1)');
            $db->bind(':name', $category_name);
            $db->bind(':mota', $category_desc);

            if ($db->execute()) {
                $_SESSION['success_message'] = "Thêm thể loại '{$category_name}' thành công!";
                header('Location: index.php?method=category-list');
                exit();
            } else {
                $errors[] = "Có lỗi xảy ra, không thể thêm thể loại.";
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
            <i class="fas fa-plus-circle"></i> Thêm Thể loại Truyện mới
        </h2>
        <a href="index.php?method=category-list"
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
                       placeholder="Ví dụ: Hành động, Phiêu lưu..."
                       value="<?= htmlspecialchars($category_name) ?>"
                       style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                       required>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-align-left"></i> Mô tả thể loại
                </label>
                <textarea name="mota" rows="4" 
                          placeholder="Mô tả ngắn gọn về đặc điểm của thể loại này..."
                          style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;
                                 font-size:14px; resize:vertical;"><?= htmlspecialchars($category_desc) ?></textarea>
            </div>

            <div style="display:flex; gap:12px; margin-top: 10px;">
                <button type="submit"
                        style="background:#28a745; color:#fff; border:none; padding:12px 30px;
                               border-radius:6px; font-size:15px; font-weight:600; cursor:pointer;">
                    <i class="fas fa-check"></i> Xác nhận thêm
                </button>
                <a href="index.php?method=category-list"
                   style="background:#6c757d; color:#fff; padding:12px 24px; border-radius:6px;
                          text-decoration:none; font-size:15px; font-weight:600;">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>