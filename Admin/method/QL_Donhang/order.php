<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'toggle_payment_status') {
        $id_order = $_POST['id_order'] ?? 0;
        $current_status = $_POST['current_status'] ?? 0;
        $new_status = ($current_status == 1) ? 0 : 1;
        
        if ($id_order) {
            $db->query("UPDATE don_hang SET trang_thai_thanh_toan = :status WHERE id_order = :id");
            $db->bind(':status', $new_status);
            $db->bind(':id', $id_order);
            $db->execute();
            $msg = ($new_status == 1) ? 'Đã Thanh Toán / Đã Xử Lý' : 'Chờ Xử Lý';
            $_SESSION['success_msg'] = "Đã cập nhật trạng thái đơn hàng #{$id_order} thành: {$msg}!";
            header("Location: index.php?method=QL_Donhang-order");
            exit;
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'cancel_order') {
        $id_order = $_POST['id_order'] ?? 0;
        if ($id_order) {
            $db->query("UPDATE don_hang SET trang_thai_thanh_toan = 3 WHERE id_order = :id");
            $db->bind(':id', $id_order);
            $db->execute();
            $_SESSION['success_msg'] = "Đã hủy đơn hàng #{$id_order} thành công!";
            header("Location: index.php?method=QL_Donhang-order");
            exit;
        }
    }
}

// Truy xuất dữ liệu đơn hàng
$db->query("
    SELECT d.*, t.TENTAIKHOAN, t.EMAIL 
    FROM don_hang d
    LEFT JOIN taikhoan t ON d.id_taikhoan = t.ID_TAIKHOAN
    ORDER BY d.id_order DESC
");
$orders = $db->resultSet();
?>

<div class="um-container">
    <h2 class="um-title" style="margin-bottom: 20px; color: #333; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-file-invoice-dollar" style="color: #007bff;"></i> Danh Sách Đơn Hàng
    </h2>
    
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['success_msg'] ?>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>
    
    <div class="um-table-wrapper" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <table class="um-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 12px; text-align: left;">Mã ĐH</th>
                    <th style="padding: 12px; text-align: left;">Khách Hàng</th>
                    <th style="padding: 12px; text-align: left;">Tổng Tiền</th>
                    <th style="padding: 12px; text-align: left;">Ngày Đặt</th>
                    <th style="padding: 12px; text-align: left;">Địa Chỉ Nhận</th>
                    <th style="padding: 12px; text-align: center;">Trạng Thái</th>
                    <th style="padding: 12px; text-align: center;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $o): ?>
                <tr style="border-bottom: 1px solid #e9ecef; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f1f3f5';" onmouseout="this.style.backgroundColor='transparent';">
                    <td style="padding: 12px; vertical-align: middle;"><strong>#<?= $o['id_order'] ?></strong></td>
                    
                    <td style="padding: 12px; vertical-align: middle;">
                        <div style="font-weight: 600; color: #2c3e50;"><?= htmlspecialchars($o['TENTAIKHOAN'] ?? 'Khách Ẩn Danh') ?></div>
                        <div style="font-size: 0.85em; color: #7f8c8d;"><?= htmlspecialchars($o['EMAIL'] ?? '') ?></div>
                    </td>
                    
                    <td style="padding: 12px; vertical-align: middle; color: #e53e3e; font-weight: bold;">
                        <?= number_format($o['tong_tien'], 0, ',', '.') ?>đ
                    </td>
                    
                    <td style="padding: 12px; vertical-align: middle; color: #555;">
                        <i class="far fa-calendar-alt"></i> <?= date('d/m/Y H:i', strtotime($o['ngay_dat'])) ?>
                    </td>

                    <td style="padding: 12px; vertical-align: middle;">
                        <span style="display: inline-block; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($o['dia_chi_giao_hang']) ?>">
                            <?= htmlspecialchars($o['dia_chi_giao_hang']) ?>
                        </span>
                    </td>

                    <td style="padding: 12px; vertical-align: middle; text-align: center;">
                        <?php if ($o['trang_thai_thanh_toan'] == 1): ?>
                            <span style="background-color: #28a745; color: white; padding: 5px 10px; border-radius: 12px; font-size: 0.8em; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fas fa-check-circle"></i> Đã xử lý / Đã TT
                            </span>
                        <?php elseif ($o['trang_thai_thanh_toan'] == 2): ?>
                            <span style="background-color: #17a2b8; color: white; padding: 5px 10px; border-radius: 12px; font-size: 0.8em; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fas fa-check-double"></i> Hoàn thành
                            </span>
                        <?php elseif ($o['trang_thai_thanh_toan'] == 3): ?>
                            <span style="background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 12px; font-size: 0.8em; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fas fa-times-circle"></i> Đã bị hủy
                            </span>
                        <?php else: ?>
                            <span style="background-color: #ffc107; color: #212529; padding: 5px 10px; border-radius: 12px; font-size: 0.8em; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fas fa-hourglass-half"></i> Chờ xử lý
                            </span>
                        <?php endif; ?>
                    </td>

                    <td style="padding: 12px; vertical-align: middle; text-align: center;">
                        <div style="display: flex; justify-content: center; gap: 8px;">
                            <a href="index.php?method=QL_Donhang-order_detail&id=<?= $o['id_order'] ?>" style="background-color: #17a2b8; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.9em; transition: 0.2s;" onmouseover="this.style.opacity='0.8';" onmouseout="this.style.opacity='1';">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a>
                            <?php if ($o['trang_thai_thanh_toan'] != 3): ?>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="toggle_payment_status">
                                    <input type="hidden" name="id_order" value="<?= $o['id_order'] ?>">
                                    <input type="hidden" name="current_status" value="<?= $o['trang_thai_thanh_toan'] ?>">
                                    <?php if ($o['trang_thai_thanh_toan'] == 1 || $o['trang_thai_thanh_toan'] == 2): ?>
                                        <button type="submit" style="background-color: #6c757d; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 0.9em; transition: 0.2s;" onmouseover="this.style.opacity='0.8';" onmouseout="this.style.opacity='1';" onclick="return confirm('Chuyển trạng thái đơn hàng này về Chờ xử lý?');">
                                            <i class="fas fa-undo"></i> Hủy duyệt
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" style="background-color: #28a745; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 0.9em; transition: 0.2s;" onmouseover="this.style.opacity='0.8';" onmouseout="this.style.opacity='1';" onclick="return confirm('Xác nhận Cập nhật thành Đã xử lý?');">
                                            <i class="fas fa-check"></i> Duyệt ĐH
                                        </button>
                                    <?php endif; ?>
                                </form>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="cancel_order">
                                    <input type="hidden" name="id_order" value="<?= $o['id_order'] ?>">
                                    <button type="submit" style="background-color: #dc3545; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 0.9em; transition: 0.2s;" onmouseover="this.style.opacity='0.8';" onmouseout="this.style.opacity='1';" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');">
                                        <i class="fas fa-trash-alt"></i> Hủy ĐH
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="background-color: #f8d7da; color: #721c24; padding: 6px 12px; border-radius: 4px; font-size: 0.9em; border: 1px solid #f5c6cb;">Đã Hủy</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if(empty($orders)): ?>
            <div style="text-align: center; padding: 40px; color: #868e96;">
                <i class="fas fa-box-open" style="font-size: 3em; margin-bottom: 15px; display: block;"></i>
                <h4 style="margin: 0;">Chưa có đơn hàng nào</h4>
                <p style="margin-top: 5px;">Khi khách hàng đặt mua trên web, đơn hàng sẽ xuất hiện tại đây.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
