<?php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config.php';
require_once '../include/function.php';
require_once '../include/db.php';

$db = new Database();
$user_id = $_SESSION['ID_TAIKHOAN']; 
$message = '';
$message_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['TENTAIKHOAN'])) {
    $ten_taikhoan = trim($_POST['TENTAIKHOAN'] ?? '');
    $email = trim($_POST['EMAIL'] ?? '');
    $sdt = trim($_POST['SDT'] ?? '');
    $sex = trim($_POST['GIOITINH'] ?? '');

    if (empty($ten_taikhoan)) {
        $message = 'Tên tài khoản và Email không được để trống.';
        $message_type = 'danger';
    } else {
        try {
            $db->query('UPDATE taikhoan SET TENTAIKHOAN = :TEN, EMAIL = :EMAIL, SDT = :SDT, GIOITINH =:SEX WHERE ID_TAIKHOAN = :ID');
            $db->bind(':TEN', $ten_taikhoan);
            $db->bind(':EMAIL', $email);
            $db->bind(':SDT', $sdt);
            $db->bind(':SEX', $sex);
            $db->bind(':ID', $user_id);
            if ($db->execute()) {
                $message = 'Cập nhật thông tin thành công!';
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_avatar') {
    try {
        if (empty($user_id)) {
            throw new Exception('Không xác định được ID người dùng.');
        }
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Vui lòng chọn một tệp ảnh hợp lệ.');
        }
        $db->query('SELECT ANH FROM taikhoan WHERE ID_TAIKHOAN = :ID_TAIKHOAN');
        $db->bind(':ID_TAIKHOAN', $user_id);
        $old_image = $db->single()['ANH'] ?? null;

        if ($old_image) {
            $old_path = __DIR__ . '/../' . $old_image;
            if (file_exists($old_path) && strpos($old_image, 'admin.png') === false) {
                unlink($old_path);
            }
        }
        $target_path = uploadImage($_FILES['avatar'], 'TAIKHOAN');
        $db->query('UPDATE taikhoan SET ANH = :ANH WHERE ID_TAIKHOAN = :ID_TAIKHOAN');
        $db->bind(':ANH', $target_path);
        $db->bind(':ID_TAIKHOAN', $user_id);
        $db->execute();
        $_SESSION['ANH'] = $target_path;
        $message = 'Cập nhật ảnh đại diện thành công!';
        $message_type = 'success';
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = 'danger';
    }
}

// Lấy dữ liệu mới nhất để hiển thị
$db->query('SELECT * FROM taikhoan WHERE ID_TAIKHOAN = :ID');
$db->bind(':ID', $user_id);
$user = $db->single();
?>
<div class="profile-container" style="background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 800px; margin: 20px auto;">
    <h2 style="border-bottom: 2px solid #f4f4f4; padding-bottom: 10px; margin-bottom: 25px;">
        <i class="fas fa-user-circle mr-2"></i> Thông tin cá nhân
    </h2>

    <?php if (!empty($message)): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 30px;">
        
        <div style="flex: 1; text-align: center; min-width: 200px;">
            <div style="position: relative; display: inline-block; cursor: pointer;" onclick="document.getElementById('avatarInput').click();" title="Nhấp để đổi ảnh đại diện">
                <img src="<?php echo !empty($user['ANH']) ? htmlspecialchars($user['ANH']) : 'Anh/admin.png'; ?>" 
                     id="avatarPreview"
                     style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #eee; transition: 0.3s;"
                     onmouseover="this.style.opacity='0.75'; this.style.border='4px solid #007bff'" 
                     onmouseout="this.style.opacity='1'; this.style.border='4px solid #eee'">
                
                <div style="position: absolute; bottom: 5px; right: 5px; background: #007bff; color: #fff; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff;">
                    <i class="fas fa-camera"></i>
                </div>
            </div>

            <form id="avatarForm" method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="hidden" name="action" value="update_avatar">
                <input type="file" name="avatar" id="avatarInput" accept="image/*" onchange="submitAvatarForm()">
            </form>

            <div style="margin-top: 15px;">
                <p style="margin-bottom: 8px; color: #666; font-weight: 500;">ID người dùng: #<?php echo $user['ID_TAIKHOAN']; ?></p>
                <span style="display: inline-block; padding: 6px 18px; background: #007bff; color: #fff; border-radius: 20px; font-size: 0.85em; font-weight: bold;">
                    <i class="fas fa-shield-alt mr-1"></i> <?php echo ($user['ID_VAITRO'] == 1) ? 'Quản trị viên' : 'Nhân viên'; ?>
                </span>
            </div>
        </div>

        <div style="flex: 2; min-width: 300px;">
            <form method="POST" action="index.php?method=profile">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tên tài khoản</label>
                    <input type="text" name="TENTAIKHOAN" value="<?php echo htmlspecialchars($user['TENTAIKHOAN']); ?>" 
                           style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; outline: none; transition: border 0.3s;"
                           onfocus="this.style.border='1px solid #007bff'" onblur="this.style.border='1px solid #ddd'">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #888;">Email</label>
                    <input type="text" name="EMAIL" value="<?php echo htmlspecialchars($user['EMAIL'] ?? ''); ?>" readonly 
                           style="width: 100%; padding: 10px; border: 1px solid #eee; border-radius: 5px; background: #fdfdfd; color: #999; cursor: not-allowed;">
                </div>
                 <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Giới Tính</label>
                    <select name="GIOITINH" 
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; outline: none; background: white; cursor: pointer; transition: border 0.3s;"
                        onfocus="this.style.border='1px solid #007bff'" onblur="this.style.border='1px solid #ddd'">
                        <option value="Nam" <?php echo ($user['GIOITINH'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                        <option value="Nữ" <?php echo ($user['GIOITINH'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                        <option value="Khác" <?php echo ($user['GIOITINH'] == 'Khác') ? 'selected' : ''; ?>>Khác</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Số điện thoại</label>
                    <input type="text" name="SDT" value="<?php echo htmlspecialchars($user['SDT'] ?? ''); ?>" 
                           style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; outline: none;"
                           placeholder="Nhập số điện thoại"
                           onfocus="this.style.border='1px solid #007bff'" onblur="this.style.border='1px solid #ddd'">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #888;">Ngày tham gia</label>
                    <input type="text" value="<?php echo $user['NGAYLAP']; ?>" disabled 
                           style="width: 100%; padding: 10px; border: 1px solid #eee; border-radius: 5px; background: #fdfdfd; color: #999; cursor: not-allowed;">
                </div>

                <div style="padding-top: 10px; border-top: 1px solid #f4f4f4;">
                    <button type="submit" style="background: #28a745; color: #fff; border: none; padding: 12px 30px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.3s;"
                            onmouseover="this.style.background='#218838'" onmouseout="this.style.background='#28a745'">
                        <i class="fas fa-save mr-2"></i> Lưu thay đổi
                    </button>
                    <a href="index.php" style="text-decoration: none; color: #666; margin-left: 20px; font-size: 0.9em;">Hủy bỏ</a>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
function submitAvatarForm() {
    const fileInput = document.getElementById('avatarInput');
    if (fileInput.files && fileInput.files[0]) {
        // Có thể thêm hiệu ứng chờ (loading) ở đây nếu muốn
        document.getElementById('avatarForm').submit();
    }
}
</script>