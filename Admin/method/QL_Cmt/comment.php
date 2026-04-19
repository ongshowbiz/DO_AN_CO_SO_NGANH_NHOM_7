<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

// Xử lý Xóa Bình Luận
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'delete_cmt') {
        $id_comment = $_POST['id_comment'] ?? 0;
        
        if ($id_comment) {
            try {
                $db->query("DELETE FROM comment WHERE id_comment = :id");
                $db->bind(':id', $id_comment);
                $db->execute();
                
                $_SESSION['success_msg'] = "Đã xóa vĩnh viễn bình luận #{$id_comment} khỏi hệ thống!";
                header("Location: index.php?method=QL_Cmt-comment");
                exit;
            } catch (PDOException $e) {
                echo "<script>alert('Lỗi khi xóa bình luận: " . addslashes($e->getMessage()) . "'); window.location.href='index.php?method=QL_Cmt-comment';</script>";
                exit;
            }
        }
    }
}

// Truy xuất dữ liệu bình luận
$db->query("
    SELECT c.*, t.TENTAIKHOAN, t.ANH, m.manga_name 
    FROM comment c
    JOIN taikhoan t ON c.id_taikhoan = t.ID_TAIKHOAN
    JOIN manga m ON c.id_manga = m.id_manga
    ORDER BY c.id_comment DESC
");
$comments = $db->resultSet();
?>

<div class="um-container" style="max-width: 1200px; margin: 0 auto;">
    <h2 class="um-title" style="margin-bottom: 20px; color: #333; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-comments" style="color: #6f42c1;"></i> Quản Lý Bình Luận Truyện
    </h2>
    
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i> <?= $_SESSION['success_msg'] ?>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>
    
    <div class="um-table-wrapper" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        
        <div style="margin-bottom: 20px; color: #666; font-size: 0.95em;">
            <i class="fas fa-info-circle"></i> Trợ giúp: Tại đây bạn có quyền xem xét và gỡ bỏ những bình luận lăng mạ, vi phạm tiêu chuẩn cộng đồng.
        </div>

        <table class="um-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 12px; text-align: left; width: 60px;">ID</th>
                    <th style="padding: 12px; text-align: left; width: 200px;">Người Bình Luận</th>
                    <th style="padding: 12px; text-align: left;">Nội Dung</th>
                    <th style="padding: 12px; text-align: left; width: 250px;">Bình Luận Tại Truyện</th>
                    <th style="padding: 12px; text-align: center; width: 150px;">Ngày Tạo</th>
                    <th style="padding: 12px; text-align: center; width: 100px;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($comments as $c): ?>
                <tr style="border-bottom: 1px solid #e9ecef; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f1f3f5';" onmouseout="this.style.backgroundColor='transparent';">
                    
                    <td style="padding: 12px; vertical-align: middle;">
                        <strong style="color: #6c757d;">#<?= $c['id_comment'] ?></strong>
                    </td>
                    
                    <td style="padding: 12px; vertical-align: middle;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <?php if (!empty($c['ANH'])): ?>
                                <img src="<?= htmlspecialchars($c['ANH']) ?>" alt="Avatar" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd;">
                            <?php else: ?>
                                <div style="width: 35px; height: 35px; border-radius: 50%; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; color: #adb5bd;">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <strong style="color: #2c3e50; font-size: 0.95em;">
                                <?= htmlspecialchars($c['TENTAIKHOAN'] ?? 'Người dùng ẩn danh') ?>
                            </strong>
                        </div>
                    </td>
                    
                    <td style="padding: 12px; vertical-align: middle;">
                        <div style="background-color: #f8f9fa; padding: 10px; border-radius: 6px; border: 1px solid #eee; font-size: 0.95em; color: #212529; overflow-wrap: anywhere;">
                            <?= nl2br(htmlspecialchars($c['noi_dung'])) ?>
                        </div>
                    </td>
                    
                    <td style="padding: 12px; vertical-align: middle;">
                        <span style="display: inline-block; background-color: #e3f2fd; color: #0d47a1; padding: 5px 10px; border-radius: 4px; font-weight: 500; font-size: 0.85em;">
                            <i class="fas fa-book-open" style="margin-right: 4px;"></i> <?= htmlspecialchars($c['manga_name']) ?>
                        </span>
                    </td>

                    <td style="padding: 12px; vertical-align: middle; text-align: center; color: #555; font-size: 0.9em;">
                        <?= date('d/m/Y <br> H:i:s', strtotime($c['ngay_tao'])) ?>
                    </td>

                    <td style="padding: 12px; vertical-align: middle; text-align: center;">
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="delete_cmt">
                            <input type="hidden" name="id_comment" value="<?= $c['id_comment'] ?>">
                            <button type="submit" style="background-color: #dc3545; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 0.9em; transition: 0.2s;" onmouseover="this.style.opacity='0.8';" onmouseout="this.style.opacity='1';" onclick="return confirm('CẢNH BÁO: Bức bình luận này sẽ bốc hơi vĩnh viễn và không thể khôi phục. Bạn có chắc chắn Xóa?');" title="Xóa Cmt này">
                                <i class="fas fa-trash-alt"></i> Xóa
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if(empty($comments)): ?>
            <div style="text-align: center; padding: 40px; color: #868e96;">
                <i class="fa-regular fa-comment-dots" style="font-size: 3em; margin-bottom: 15px; display: block;"></i>
                <h4 style="margin: 0;">Truyện của bạn chưa có bình luận nào</h4>
                <p style="margin-top: 5px;">Khi người dùng đánh giá bộ truyện, nội dung sẽ xuất hiện ở đây.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
