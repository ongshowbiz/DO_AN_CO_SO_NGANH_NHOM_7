<?php
session_start();
// Chặn nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$base_url     = '../';
$page_title   = 'Lịch sử đơn hàng - Truyện Hay';
$current_page = 'order_history';

require_once __DIR__ . '/../../include/db.php';
require_once __DIR__ . '/../includes/header.php';

$db = new Database();

// Lấy danh sách đơn hàng của User đang đăng nhập
$db->query("SELECT id_order, tong_tien, ngay_dat, trang_thai_thanh_toan 
            FROM don_hang 
            WHERE id_taikhoan = :user_id 
            ORDER BY ngay_dat DESC");
$db->bind(':user_id', $_SESSION['user_id']);
$orders = $db->resultSet();
?>

<div class="container user-dashboard">
    <div class="dashboard-layout">

        <main class="dashboard-content">
            <h2>Lịch sử đơn hàng của bạn</h2>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open" style="font-size: 50px; color: #ccc;"></i>
                    <p>Bạn chưa có đơn hàng nào.</p>
                    <a href="../shop/index.php" class="btn btn-primary">Mua sắm ngay</a>
                </div>
            <?php else: ?>
                <table class="table order-table">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): 
                            // Định dạng trạng thái đơn
                            $status_class = 'status-pending';
                            $status_text = 'Chờ xử lý';
                            if ($order['trang_thai_thanh_toan'] == 0) {
                                $status_class = 'status-pending'; $status_text = 'Chờ xử lý';
                            } elseif ($order['trang_thai_thanh_toan'] == 1) {
                                $status_class = 'status-shipping'; $status_text = 'Đã xử lý';
                            } elseif ($order['trang_thai_thanh_toan'] == 2) {
                                $status_class = 'status-completed'; $status_text = 'Hoàn thành';
                            } elseif ($order['trang_thai_thanh_toan'] == 3) {
                                $status_class = 'status-cancelled'; $status_text = 'Đã hủy';
                            }
                        ?>
                        <tr>
                            <td><strong>#DH<?= str_pad($order['id_order'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($order['ngay_dat'])) ?></td>
                            <td class="price"><?= number_format($order['tong_tien'], 0, ',', '.') ?>đ</td>
                            <td><span class="badge <?= $status_class ?>"><?= $status_text ?></span></td>
                            <td>
                                <a href="chi_tiet_don_hang.php?id=<?= $order['id_order'] ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> Xem chi tiết
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>
    </div>
</div>


<?php require_once '../includes/footer.php'; ?>