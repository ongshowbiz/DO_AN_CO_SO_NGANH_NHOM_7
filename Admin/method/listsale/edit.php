<?php
// Tệp: Admin/method/listsale/edit.php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

$id_manga = $_GET['id'] ?? null;
if (!$id_manga) {
    header('Location: index.php?method=listsale-list');
    exit();
}

$role_id = $_SESSION['ID_VAITRO'] ?? 0;
$errors = [];

// 1. Lấy thông tin hiện tại (Bổ sung lấy thêm nha_xuat_ban)
$db->query('SELECT m.manga_name, m.anh, sp.gia_ban, sp.so_luong_kho, sp.nha_xuat_ban 
            FROM manga m 
            LEFT JOIN sanpham_manga sp ON m.id_manga = sp.id_manga 
            WHERE m.id_manga = :id');
$db->bind(':id', $id_manga);
$data = $db->single();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gia_ban = $_POST['gia_ban'] ?? 0;
    $nxb =($role_id == 3) ?  trim($_POST['nha_xuat_ban'] ?? ''): ($data['so_luong_kho'] ?? 0);
    $so_luong = ($role_id == 3) ? ($_POST['so_luong_kho'] ?? 0) : ($data['so_luong_kho'] ?? 0);

    if (empty($nxb)) {
        $errors[] = "Vui lòng nhập tên Nhà xuất bản.";
    }

    if (empty($errors)) {
        try {
            // Kiểm tra tồn tại để quyết định INSERT hay UPDATE
            $db->query('SELECT id_spmanga FROM sanpham_manga WHERE id_manga = :id');
            $db->bind(':id', $id_manga);
            
            if ($db->single()) {
                // UPDATE: Thêm nha_xuat_ban vào câu lệnh
                $db->query('UPDATE sanpham_manga 
                            SET gia_ban = :gia, so_luong_kho = :sl, nha_xuat_ban = :nxb 
                            WHERE id_manga = :id');
            } else {
                // INSERT: Thêm nha_xuat_ban vào danh sách cột
                $db->query('INSERT INTO sanpham_manga (id_manga, gia_ban, so_luong_kho, nha_xuat_ban) 
                            VALUES (:id, :gia, :sl, :nxb)');
            }

            $db->bind(':id', $id_manga);
            $db->bind(':gia', $gia_ban);
            $db->bind(':sl', $so_luong);
            $db->bind(':nxb', $nxb); // Bind giá trị NXB

            if ($db->execute()) {
                $_SESSION['success_message'] = "Cập nhật thành công!";
                header('Location: index.php?method=listsale-list');
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
?>

<div class="um-container" style="padding: 20px;">
    <form method="POST">
        
        <?php if ($role_id == 1): ?>
        <div style="margin-bottom: 15px;">
            <label style="display:block; font-weight:bold;">Giá bán (VNĐ):</label>
            <input type="number" name="gia_ban" value="<?= $data['gia_ban'] ?? 0 ?>" style="width:100%; padding:8px;">
        </div>
        <?php endif; ?>

        <?php if ($role_id == 3): ?>
        <div style="margin-bottom: 15px;">
            <label style="display:block; font-weight:bold;">Nhà xuất bản:</label>
            <input type="text" name="nha_xuat_ban" value="<?= htmlspecialchars($data['nha_xuat_ban'] ?? '') ?>" 
                   placeholder="VD: NXB Kim Đồng, NXB Trẻ..." style="width:100%; padding:8px;" required>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display:block; font-weight:bold;">Số lượng kho:</label>
            <input type="number" name="so_luong_kho" value="<?= $data['so_luong_kho'] ?? 0 ?>" style="width:100%; padding:8px;">
        </div>
        <?php endif; ?>

        <button type="submit" style="background:#28a745; color:white; padding:10px 20px; border:none; border-radius:4px; cursor:pointer;">Lưu thay đổi</button>
    </form>
</div>