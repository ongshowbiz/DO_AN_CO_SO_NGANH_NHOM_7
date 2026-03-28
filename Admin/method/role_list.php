<?php
require_once __DIR__ . '/../../include/db.php';
$db = new Database();

// Xử lý Form POST (Thêm chức vụ hoặc Ẩn/Hiện)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        
        // 1. Thêm Vai trò mới
        if ($_POST['action'] == 'add_role') {
            $ten_vaitro = trim($_POST['ten_vaitro'] ?? '');
            if (!empty($ten_vaitro)) {
                try {
                    $db->query("INSERT INTO role (TEN_VAITRO, TRANGTHAI) VALUES (:ten, 1)");
                    $db->bind(':ten', $ten_vaitro);
                    $db->execute();
                    echo "<script>alert('Tuyệt vời! Bạn vừa cấu hình thêm chức vụ mới.'); window.location.href='index.php?page=role-list';</script>";
                } catch (PDOException $e) {
                    echo "<script>alert('Lỗi! Tên chức vụ này có thể đã tồn tại rồi.'); window.location.href='index.php?page=role-list';</script>";
                }
                exit;
            }
        }
        
        // 2. Ẩn/Hiện chức vụ
        if ($_POST['action'] == 'toggle_role_status') {
            $id_vaitro = $_POST['id_vaitro'] ?? 0;
            $current_status = $_POST['current_status'] ?? 0;
            $new_status = ($current_status == 1) ? 0 : 1;
            
            if ($id_vaitro) {
                if ($id_vaitro == 1 || $id_vaitro == 2) {
                    echo "<script>alert('Từ chối! Không thể thay đổi các vai trò bảo mật mặc định (Admin, Customer).'); window.location.href='index.php?page=role-list';</script>";
                    exit;
                }

                $db->query("UPDATE role SET TRANGTHAI = :status WHERE ID_VAITRO = :id");
                $db->bind(':status', $new_status);
                $db->bind(':id', $id_vaitro);
                $db->execute();
                echo "<script>window.location.href='index.php?page=role-list';</script>";
                exit;
            }
        }
    }
}

// Hàm lấy Load tất cả vai trò
$db->query("SELECT * FROM role ORDER BY ID_VAITRO ASC");
$roles = $db->resultSet();
?>

<!-- HTML ĐÃ LỌC BỎ INLINE CSS -->
<div class="um-flex-layout">
    <!-- CỘT TRÁI: FORM -->
    <div class="um-col-left">
        <h3 class="um-col-title">
            <i class="fas fa-plus-circle"></i> Thiết Lập Vai Trò Nhanh
        </h3>
        
        <form method="POST">
            <input type="hidden" name="action" value="add_role">
            <div class="um-input-group">
                <label>Tên Chức Vụ / Vai Trò:</label>
                <input type="text" name="ten_vaitro" required placeholder="Gõ tên tuỳ ý (VD: Biên tập viên)" class="um-input">
            </div>
            <button type="submit" class="um-btn um-btn-add">
                <i class="fas fa-check"></i> LƯU XUỐNG DỮ LIỆU
            </button>
        </form>

        <div class="um-help-box">
            <p>
                <strong><i class="fas fa-info-circle"></i> Trợ giúp:</strong> Sau khi vai trò được lưu vào hệ thống, bạn có thể nhảy sang góc <strong><a href="index.php?page=user-list">Quản lý Danh sách User</a></strong> để chọn cấp chức vụ này cho từng người dùng tương ứng.
            </p>
        </div>
    </div>

    <!-- CỘT PHẢI: TABLE -->
    <div class="um-col-right">
        <h2 class="um-title">
            <i class="fas fa-user-tag"></i> Ngăn Xếp Phân Quyền
        </h2>

        <div class="um-table-wrapper">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>Mã (ID)</th>
                        <th>Danh Xưng Vai Trò</th>
                        <th class="text-center">Hiệu Lực</th>
                        <th class="text-center" style="width:150px;">Công Cụ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($roles as $r): ?>
                    <tr>
                        <td class="um-id"><strong>#<?= $r['ID_VAITRO'] ?></strong></td>
                        
                        <td>
                            <span class="um-role-tag">
                                <?= htmlspecialchars($r['TEN_VAITRO']) ?>
                            </span>
                        </td>
                        
                        <td class="text-center">
                            <?php if ($r['TRANGTHAI'] == 1): ?>
                                <span class="um-role-active"><i class="fas fa-eye"></i> Kích Hoạt</span>
                            <?php else: ?>
                                <span class="um-role-hidden"><i class="fas fa-eye-slash"></i> Đã Ẩn</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                            <form method="POST">
                                <input type="hidden" name="action" value="toggle_role_status">
                                <input type="hidden" name="id_vaitro" value="<?= $r['ID_VAITRO'] ?>">
                                <input type="hidden" name="current_status" value="<?= $r['TRANGTHAI'] ?>">
                                
                                <?php if ($r['ID_VAITRO'] == 1 || $r['ID_VAITRO'] == 2): ?>
                                    <button type="button" disabled class="um-btn um-btn-disabled" title="Vai trò hệ thống cốt lõi không được phép tinh chỉnh">
                                        <i class="fas fa-shield-alt"></i> Khóa Mặc Định
                                    </button>
                                <?php else: ?>
                                    <?php if ($r['TRANGTHAI'] == 1): ?>
                                        <button type="submit" class="um-btn um-btn-hide" onclick="return confirm('Bạn có chắc muốn Ẩn chức vụ này đi khỏi form chọn phân quyền?');">
                                            Vô hiệu hóa
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="um-btn um-btn-show">
                                            Bật Hiện Lại
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
