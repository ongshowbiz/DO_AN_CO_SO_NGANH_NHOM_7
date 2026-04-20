<?php
session_start();
require_once __DIR__ . '/../../include/db.php';
$db = new Database();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$base_url = '../';

// Xử lý hủy đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $id_to_cancel = (int)$_POST['cancel_order_id'];

    // Kiểm tra quyền sở hữu và trạng thái đơn (chỉ cho hủy khi đang chờ xử lý - 0)
    $db->query("SELECT trang_thai_thanh_toan FROM don_hang WHERE id_order = :id AND id_taikhoan = :uid");
    $db->bind(':id', $id_to_cancel);
    $db->bind(':uid', $user_id);
    $check = $db->single();

    if ($check && $check['trang_thai_thanh_toan'] == 0) {
        // 1. Cập nhật trạng thái thành 3 (Đã hủy)
        $db->query("UPDATE don_hang SET trang_thai_thanh_toan = 3 WHERE id_order = :id");
        $db->bind(':id', $id_to_cancel);
        
        if ($db->execute()) {
            // 2. Lấy danh sách sản phẩm để hoàn lại số lượng vào kho
            $db->query("SELECT id_spmanga, so_luong FROM chi_tiet_don_hang WHERE id_order = :id");
            $db->bind(':id', $id_to_cancel);
            $items_to_return = $db->resultSet();

            foreach ($items_to_return as $item) {
                $db->query("UPDATE sanpham_manga SET so_luong_kho = so_luong_kho + :qty WHERE id_spmanga = :pid");
                $db->bind(':qty', $item['so_luong']);
                $db->bind(':pid', $item['id_spmanga']);
                $db->execute();
            }
            header("Location: chi_tiet_don_hang.php?id=" . $id_to_cancel . "&msg=cancelled");
            exit;
        }
    }
}

// Lấy thông tin đơn hàng
$db->query("SELECT * FROM don_hang WHERE id_order = :id AND id_taikhoan = :uid");
$db->bind(':id', $order_id);
$db->bind(':uid', $user_id);
$order = $db->single();

if (!$order) {
    die("<div style='text-align:center; padding:50px;'><h3>Đơn hàng không tồn tại!</h3><a href='lich_su_don_hang.php'>Quay lại</a></div>");
}

// Lấy chi tiết sản phẩm trong đơn
$db->query("SELECT ct.*, m.manga_name, m.anh 
            FROM chi_tiet_don_hang ct 
            JOIN sanpham_manga sp ON ct.id_spmanga = sp.id_spmanga 
            JOIN manga m ON sp.id_manga = m.id_manga 
            WHERE ct.id_order = :id");
$db->bind(':id', $order_id);
$order_items = $db->resultSet();

$page_title = 'Chi tiết đơn hàng #' . $order_id;
$extra_css = ['../shop.css', '../dashboard.css'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container user-dashboard" style="margin-top: 30px; margin-bottom: 50px;">
    <div class="order-detail-container" style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #f4f4f4; padding-bottom: 15px;">
            <h2 style="margin: 0;">Đơn hàng #<?= str_pad($order['id_order'], 5, '0', STR_PAD_LEFT) ?></h2>
            <div>
                <?php
                    $stt = $order['trang_thai_thanh_toan'];
                    if($stt == 0) echo '<span class="badge status-pending">Chờ xử lý</span>';
                    elseif($stt == 1) echo '<span class="badge status-shipping">Đang giao</span>';
                    elseif($stt == 2) echo '<span class="badge status-completed">Hoàn thành</span>';
                    elseif($stt == 3) echo '<span class="badge status-cancelled">Đã hủy</span>';
                ?>
            </div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'cancelled'): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                Đã hủy đơn hàng thành công và hoàn lại số lượng vào kho.
            </div>
        <?php endif; ?>

        <div class="info-section">
            <div class="info-box">
                <h4><i class="fas fa-map-marker-alt"></i> Địa chỉ nhận hàng</h4>
                <p><?= nl2br(htmlspecialchars($order['dia_chi_giao_hang'])) ?></p>
            </div>
            <div class="info-box">
                <h4><i class="fas fa-clock"></i> Thời gian</h4>
                <p><?= date('d/m/Y H:i', strtotime($order['ngay_dat'])) ?></p>
            </div>
        </div>

        <table class="item-table" style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr style="background: #f8f9fa; text-align: left;">
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6;">Sản phẩm</th>
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: center;">Số lượng</th>
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: right;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                foreach ($order_items as $item): 
                    $thanhtien = $item['gia_tai_thoi_diem_mua'] * $item['so_luong'];
                    $subtotal += $thanhtien;
                ?>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 15px;">
                        <img src="<?= (strpos($item['anh'], 'http') === 0) ? $item['anh'] : $base_url . $item['anh'] ?>" width="50" style="border-radius: 4px; object-fit: cover;">
                        <span><?= htmlspecialchars($item['manga_name']) ?></span>
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee; text-align: center;">x<?= $item['so_luong'] ?></td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee; text-align: right;"><?= number_format($thanhtien, 0, ',', '.') ?>đ</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-section" style="margin-left: auto; width: 300px; margin-top: 20px;">
            <div class="summary-row" style="display: flex; justify-content: space-between; padding: 5px 0;">
                <span>Tạm tính:</span>
                <span><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
            </div>
            <div class="summary-row" style="display: flex; justify-content: space-between; padding: 5px 0;">
                <span>Phí vận chuyển:</span>
                <span><?= ($order['tong_tien'] - $subtotal > 0) ? number_format($order['tong_tien'] - $subtotal, 0, ',', '.') . 'đ' : 'Miễn phí' ?></span>
            </div>
            <div class="summary-row grand-total" style="display: flex; justify-content: space-between; padding: 15px 0; border-top: 2px solid #eee; margin-top: 10px; font-weight: bold; font-size: 1.2em; color: #e74c3c;">
                <span>Tổng cộng:</span>
                <span><?= number_format($order['tong_tien'], 0, ',', '.') ?>đ</span>
            </div>
        </div>

        <div class="order-actions" style="margin-top: 40px; display: flex; gap: 15px; border-top: 1px solid #eee; padding-top: 20px;">
            <?php if ($order['trang_thai_thanh_toan'] == 0): ?>
                <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                    <input type="hidden" name="cancel_order_id" value="<?= $order_id ?>">
                    <button type="submit" class="btn-action" style="background: #e74c3c; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-times-circle"></i> Hủy đơn hàng
                    </button>
                </form>
            <?php endif; ?>
            
            <a href="lich_su_don_hang.php" class="btn-action" style="background: #95a5a6; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px;">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            
            <a href="https://zalo.me/0123456789" target="_blank" class="btn-action" style="background: #3498db; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; margin-left: auto;">
                <i class="fas fa-comment-dots"></i> Hỗ trợ Zalo
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>