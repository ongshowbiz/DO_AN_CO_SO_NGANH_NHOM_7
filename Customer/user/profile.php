<?php
// Customer/user/profile.php
session_start();

// Kiểm tra đăng nhập theo chuẩn Customer
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../include/db.php';
require_once '../../include/function.php';

$db      = new Database();
$user_id = $_SESSION['user_id'];
$message = '';
$message_type = 'success';

// -------------------------------------------------------
// CẬP NHẬT THÔNG TIN CÁ NHÂN
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['TENTAIKHOAN'])) {
    $ten_taikhoan = trim($_POST['TENTAIKHOAN'] ?? '');
    $email        = trim($_POST['EMAIL']       ?? '');
    $sdt          = trim($_POST['SDT']         ?? '');
    $sex          = trim($_POST['GIOITINH']    ?? '');

    if (empty($ten_taikhoan) || empty($email)) {
        $message      = 'Tên tài khoản và Email không được để trống.';
        $message_type = 'danger';
    } else {
        try {
            $db->query('UPDATE taikhoan SET TENTAIKHOAN = :TEN, EMAIL = :EMAIL, SDT = :SDT, GIOITINH = :SEX WHERE ID_TAIKHOAN = :ID');
            $db->bind(':TEN',   $ten_taikhoan);
            $db->bind(':EMAIL', $email);
            $db->bind(':SDT',   $sdt);
            $db->bind(':SEX',   $sex);
            $db->bind(':ID',    $user_id);
            if ($db->execute()) {
                $_SESSION['username'] = $ten_taikhoan;
                $message      = 'Cập nhật thông tin thành công!';
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message      = 'Lỗi: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// -------------------------------------------------------
// CẬP NHẬT ẢNH ĐẠI DIỆN
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_avatar') {
    try {
        if (empty($user_id)) {
            throw new Exception('Không xác định được ID người dùng.');
        }
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Vui lòng chọn một tệp ảnh hợp lệ.');
        }

        // Xóa ảnh cũ nếu có
        $db->query('SELECT ANH FROM taikhoan WHERE ID_TAIKHOAN = :ID');
        $db->bind(':ID', $user_id);
        $old_image = $db->single()['ANH'] ?? null;
        if ($old_image) {
            $old_path = __DIR__ . '/../../' . $old_image;
            if (file_exists($old_path)) {
                unlink($old_path);
            }
        }

        $target_path = uploadImage($_FILES['avatar'], 'taikhoan');

        $db->query('UPDATE taikhoan SET ANH = :ANH WHERE ID_TAIKHOAN = :ID');
        $db->bind(':ANH', $target_path);
        $db->bind(':ID',  $user_id);
        $db->execute();

        $_SESSION['ANH'] = $target_path;
        $message      = 'Cập nhật ảnh đại diện thành công!';
        $message_type = 'success';
    } catch (Exception $e) {
        $message      = $e->getMessage();
        $message_type = 'danger';
    }
}

// -------------------------------------------------------
// LẤY THÔNG TIN MỚI NHẤT
// -------------------------------------------------------
$db->query('SELECT * FROM taikhoan WHERE ID_TAIKHOAN = :ID');
$db->bind(':ID', $user_id);
$user = $db->single();

// -------------------------------------------------------
// HEADER
// -------------------------------------------------------
$base_url     = '../';
$page_title   = 'Thông tin cá nhân - Truyện Hay';
$current_page = '';
require_once '../includes/header.php';
?>

<main class="main-content">
<div class="profile-container" style="background: var(--color-background-secondary); padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 800px; margin: 30px auto;">

    <h2 style="border-bottom: 2px solid var(--color-border-tertiary); padding-bottom: 10px; margin-bottom: 25px;">
        <i class="fas fa-user-circle"></i> Thông tin cá nhân
    </h2>

    <?php if (!empty($message)): ?>
    <div style="background: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>;
                color:       <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>;
                padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 30px;">

        <!-- ẢNH ĐẠI DIỆN -->
        <div style="flex: 1; text-align: center; min-width: 200px;">
            <div style="position: relative; display: inline-block; cursor: pointer;"
                 onclick="document.getElementById('avatarInput').click();"
                 title="Nhấp để đổi ảnh đại diện">
                <img src="<?php echo !empty($user['ANH']) ? htmlspecialchars($user['ANH']) : '../assets/img/default-avatar.jpg'; ?>"
                     id="avatarPreview"
                     style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--color-border-tertiary); transition: 0.3s;"
                     onmouseover="this.style.opacity='0.75'; this.style.border='4px solid var(--primary)'"
                     onmouseout="this.style.opacity='1'; this.style.border='4px solid var(--color-border-tertiary)'"
                     onerror="this.src='../assets/img/default-avatar.jpg'">
                <div style="position: absolute; bottom: 5px; right: 5px; background: var(--primary, #e74c3c); color: #fff; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff;">
                    <i class="fas fa-camera"></i>
                </div>
            </div>

            <form id="avatarForm" method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="hidden" name="action" value="update_avatar">
                <input type="file" name="avatar" id="avatarInput" accept="image/*" onchange="submitAvatarForm()">
            </form>

            <div style="margin-top: 15px;">
                <p style="margin-bottom: 8px; color: var(--color-text-secondary); font-weight: 500;">
                    ID: #<?php echo $user['ID_TAIKHOAN']; ?>
                </p>
                <span style="display: inline-block; padding: 6px 18px; background: var(--primary, #e74c3c); color: #fff; border-radius: 20px; font-size: 0.85em; font-weight: bold;">
                    <i class="fas fa-user"></i> Thành viên
                </span>
            </div>
        </div>

        <!-- FORM THÔNG TIN -->
        <div style="flex: 2; min-width: 300px;">
            <form method="POST" action="profile.php">

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tên tài khoản</label>
                    <input type="text" name="TENTAIKHOAN"
                           value="<?php echo htmlspecialchars($user['TENTAIKHOAN']); ?>"
                           style="width: 100%; padding: 10px; border: 1px solid var(--color-border-tertiary); border-radius: 5px; outline: none; background: var(--color-background-primary); color: var(--color-text-primary);"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--color-border-tertiary)'">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email</label>
                    <input type="email" name="EMAIL"
                           value="<?php echo htmlspecialchars($user['EMAIL'] ?? ''); ?>"
                           style="width: 100%; padding: 10px; border: 1px solid var(--color-border-tertiary); border-radius: 5px; outline: none; background: var(--color-background-primary); color: var(--color-text-primary);"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--color-border-tertiary)'">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Giới tính</label>
                    <select name="GIOITINH"
                            style="width: 100%; padding: 10px; border: 1px solid var(--color-border-tertiary); border-radius: 5px; outline: none; background: var(--color-background-primary); color: var(--color-text-primary); cursor: pointer;"
                            onfocus="this.style.borderColor='var(--primary)'"
                            onblur="this.style.borderColor='var(--color-border-tertiary)'">
                        <option value="">-- Chọn giới tính --</option>
                        <option value="Nam"  <?php echo ($user['GIOITINH'] === 'Nam')  ? 'selected' : ''; ?>>Nam</option>
                        <option value="Nữ"   <?php echo ($user['GIOITINH'] === 'Nữ')   ? 'selected' : ''; ?>>Nữ</option>
                        <option value="Khác" <?php echo ($user['GIOITINH'] === 'Khác') ? 'selected' : ''; ?>>Khác</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Số điện thoại</label>
                    <input type="text" name="SDT"
                           value="<?php echo htmlspecialchars($user['SDT'] ?? ''); ?>"
                           placeholder="Nhập số điện thoại"
                           style="width: 100%; padding: 10px; border: 1px solid var(--color-border-tertiary); border-radius: 5px; outline: none; background: var(--color-background-primary); color: var(--color-text-primary);"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--color-border-tertiary)'">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--color-text-secondary);">Ngày tham gia</label>
                    <input type="text" value="<?php echo $user['NGAYLAP']; ?>" disabled
                           style="width: 100%; padding: 10px; border: 1px solid var(--color-border-tertiary); border-radius: 5px; background: var(--color-background-tertiary); color: var(--color-text-secondary); cursor: not-allowed;">
                </div>

                <div style="padding-top: 10px; border-top: 1px solid var(--color-border-tertiary);">
                    <button type="submit"
                            style="background: #28a745; color: #fff; border: none; padding: 12px 30px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.3s;"
                            onmouseover="this.style.background='#218838'"
                            onmouseout="this.style.background='#28a745'">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                    <a href="../index.php" style="text-decoration: none; color: var(--color-text-secondary); margin-left: 20px; font-size: 0.9em;">
                        Hủy bỏ
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>
</main>

<?php require_once '../includes/footer.php'; ?>

<script>
function submitAvatarForm() {
    const fileInput = document.getElementById('avatarInput');
    if (fileInput.files && fileInput.files[0]) {
        // Preview ảnh trước khi upload
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(fileInput.files[0]);
        document.getElementById('avatarForm').submit();
    }
}
</script>