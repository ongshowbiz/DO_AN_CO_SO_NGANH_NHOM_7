<?php
require_once __DIR__ . '/../../include/db.php';
$db = new Database();

// --- XỬ LÝ CẬP NHẬT CSDL QUA POST KHÔNG CHUYỂN TRANG ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $id_taikhoan = $_POST['id_taikhoan'] ?? 0;
        
        // 1. Xử lý lưu đổi phân quyền
        if ($_POST['action'] == 'change_role') {
            $new_role = $_POST['id_vaitro'] ?? null;
            if ($id_taikhoan && $new_role !== null) {
                if ($new_role === "") {
                    $db->query("UPDATE taikhoan SET ID_VAITRO = NULL WHERE ID_TAIKHOAN = :id");
                    $db->bind(':id', $id_taikhoan);
                } else {
                    $db->query("UPDATE taikhoan SET ID_VAITRO = :role WHERE ID_TAIKHOAN = :id");
                    $db->bind(':role', $new_role);
                    $db->bind(':id', $id_taikhoan);
                }
                
                $db->execute();
                echo "<script>alert('Cập nhật quyền của User #{$id_taikhoan} thành công!'); window.location.href='index.php?page=user-list';</script>";
                exit;
            }
        }
        
        // 2. Xử lý Khóa/Mở Khóa
        if ($_POST['action'] == 'toggle_status') {
            $current_status = $_POST['current_status'] ?? 0;
            $new_status = ($current_status == 1) ? 0 : 1;
            
            if ($id_taikhoan) {
                $db->query("UPDATE taikhoan SET TRANGTHAI = :status WHERE ID_TAIKHOAN = :id");
                $db->bind(':status', $new_status);
                $db->bind(':id', $id_taikhoan);
                $db->execute();
                $msg = ($new_status == 1) ? 'Mở khóa' : 'Khóa';
                echo "<script>alert('Đã {$msg} tài khoản #{$id_taikhoan} thành công!'); window.location.href='index.php?page=user-list';</script>";
                exit;
            }
        }
    }
}

// --- TRUY XUẤT DỮ LIỆU ĐỂ HIỂN THỊ ---
$db->query("SELECT * FROM role ORDER BY ID_VAITRO ASC");
$roles = $db->resultSet();

$db->query("
    SELECT t.*, r.TEN_VAITRO 
    FROM taikhoan t
    LEFT JOIN role r ON t.ID_VAITRO = r.ID_VAITRO
    ORDER BY t.ID_TAIKHOAN DESC
");
$users = $db->resultSet();
?>

<!-- GIAO DIỆN HTML TABLE ĐÃ TÁCH CSS -->
<div class="um-container">
    <h2 class="um-title">
        <i class="fas fa-users"></i> Quản Lý Tài Khoản (Danh Sách User)
    </h2>

    <div class="um-table-wrapper">
        <table class="um-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Tài Khoản</th>
                    <th>Email / SĐT</th>
                    <th>Ngày Lập</th>
                    <th>Phân Quyền (Role)</th>
                    <th class="text-center">Trạng Thái</th>
                    <th class="text-center">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td class="um-id"><strong>#<?= $u['ID_TAIKHOAN'] ?></strong></td>
                    
                    <td>
                        <div class="um-user-info">
                            <?php if (!empty($u['ANH'])): ?>
                                <img src="<?= htmlspecialchars($u['ANH']) ?>" class="um-avatar">
                            <?php else: ?>
                                <i class="fas fa-user-circle um-avatar-icon"></i>
                            <?php endif; ?>
                            <strong class="um-username"><?= htmlspecialchars($u['TENTAIKHOAN']) ?></strong>
                        </div>
                    </td>
                    
                    <td>
                        <div class="um-email"><?= htmlspecialchars($u['EMAIL'] ?? '—') ?></div>
                        <div class="um-phone"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($u['SDT'] ?? '—') ?></div>
                    </td>
                    
                    <td class="um-date">
                        <i class="far fa-calendar-alt"></i> <?= date('d/m/Y &bull; H:i', strtotime($u['NGAYLAP'])) ?>
                    </td>
                    
                    <td>
                        <form method="POST" class="um-role-form">
                            <input type="hidden" name="action" value="change_role">
                            <input type="hidden" name="id_taikhoan" value="<?= $u['ID_TAIKHOAN'] ?>">
                            <select name="id_vaitro" class="um-role-select">
                                <option value="">-- Trống --</option>
                                <?php foreach($roles as $r): ?>
                                    <option value="<?= $r['ID_VAITRO'] ?>" <?= ($u['ID_VAITRO'] == $r['ID_VAITRO']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['TEN_VAITRO']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" title="Lưu phân quyền" class="um-btn um-btn-save">
                                <i class="fas fa-save"></i>
                            </button>
                        </form>
                    </td>

                    <td class="text-center">
                        <?php if ($u['TRANGTHAI'] == 1): ?>
                            <span class="um-badge um-badge-active"><i class="fas fa-check-circle"></i> Đang hoạt động</span>
                        <?php else: ?>
                            <span class="um-badge um-badge-locked"><i class="fas fa-lock"></i> Đã khoá</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-center">
                        <form method="POST">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id_taikhoan" value="<?= $u['ID_TAIKHOAN'] ?>">
                            <input type="hidden" name="current_status" value="<?= $u['TRANGTHAI'] ?>">
                            <?php if ($u['TRANGTHAI'] == 1): ?>
                                <button type="submit" class="um-btn um-btn-lock" onclick="return confirm('CẢNH BÁO: Bạn có chắc muốn Khóa tài khoản <?= $u['TENTAIKHOAN'] ?>? Người này sẽ không đăng nhập được.');">
                                    <i class="fas fa-ban"></i> Khoá acc
                                </button>
                            <?php else: ?>
                                <button type="submit" class="um-btn um-btn-unlock" onclick="return confirm('Xác nhận Mở khóa cho phép <?= $u['TENTAIKHOAN'] ?> truy cập lại?');">
                                    <i class="fas fa-unlock"></i> Mở Khoá
                                </button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if(empty($users)): ?>
            <div class="um-empty">
                <i class="fas fa-box-open"></i>
                <p>Chưa có dữ liệu lịch sử thành viên nào.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
