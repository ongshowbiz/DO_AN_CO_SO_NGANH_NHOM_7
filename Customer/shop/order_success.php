<?php
// shop/order_success.php — Trang đặt hàng thành công
// Thực tế: lấy order_id từ session/GET sau khi INSERT vào DB

$order_id   = 'TH' . strtoupper(substr(md5(time()), 0, 8));
$order_date = date('d/m/Y H:i');

$base_url     = '../';
$page_title   = 'Đặt hàng thành công - Truyện Hay';
$current_page = 'shop';
$extra_css = ['../shop.css'];
require_once '../includes/header.php';
?>


<!-- Confetti -->
<div class="confetti-wrap" id="confetti"></div>

<div class="success-page">

    <!-- Icon -->
    <div class="success-icon"><i class="fas fa-check"></i></div>

    <h1>Đặt hàng thành công! 🎉</h1>
    <p>Cảm ơn bạn đã mua hàng tại Truyện Hay. Chúng tôi sẽ xử lý đơn hàng của bạn sớm nhất!</p>

    <!-- Thông tin đơn hàng -->
    <div class="order-info-box">
        <h2><i class="fas fa-receipt"></i> Thông tin đơn hàng</h2>
        <div class="info-row">
            <span>Mã đơn hàng</span>
            <span class="order-id-badge"><?php echo $order_id; ?></span>
        </div>
        <div class="info-row">
            <span>Ngày đặt</span>
            <span><?php echo $order_date; ?></span>
        </div>
        <div class="info-row">
            <span>Trạng thái</span>
            <span style="color:#e67e22;"><i class="fas fa-clock"></i> Đang xử lý</span>
        </div>
        <div class="info-row">
            <span>Thanh toán</span>
            <span style="color:#2ecc71;"><i class="fas fa-check-circle"></i> Đã xác nhận</span>
        </div>
        <div class="info-row">
            <span>Giao hàng dự kiến</span>
            <span><?php echo date('d/m/Y', strtotime('+5 days')); ?></span>
        </div>
    </div>

    <!-- Các bước tiếp theo -->
    <div class="next-steps">
        <div class="next-step">
            <i class="fas fa-envelope"></i>
            <strong>Email xác nhận</strong>
            <span>Kiểm tra email để xem chi tiết đơn hàng</span>
        </div>
        <div class="next-step">
            <i class="fas fa-box"></i>
            <strong>Đóng gói & giao</strong>
            <span>Đơn hàng sẽ được đóng gói và giao trong 2–5 ngày</span>
        </div>
        <div class="next-step">
            <i class="fas fa-smile"></i>
            <strong>Nhận hàng</strong>
            <span>Kiểm tra hàng trước khi thanh toán COD</span>
        </div>
    </div>

    <!-- Nút hành động -->
    <div class="action-buttons">
        <a href="../index.php" class="btn-home">
            <i class="fas fa-home"></i> Về trang chủ
        </a>
        <a href="index.php" class="btn-shop">
            <i class="fas fa-store"></i> Tiếp tục mua sắm
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
<script>
// Tạo confetti
const colors = ['#e74c3c','#f39c12','#2ecc71','#3498db','#9b59b6','#e91e63'];
const wrap = document.getElementById('confetti');
for (let i = 0; i < 60; i++) {
    const el = document.createElement('span');
    el.style.left        = Math.random() * 100 + 'vw';
    el.style.background  = colors[Math.floor(Math.random() * colors.length)];
    el.style.animationDuration = (Math.random() * 3 + 2) + 's';
    el.style.animationDelay    = (Math.random() * 2) + 's';
    el.style.opacity     = Math.random() * 0.8 + 0.2;
    el.style.transform   = `rotate(${Math.random()*360}deg)`;
    wrap.appendChild(el);
}
</script>