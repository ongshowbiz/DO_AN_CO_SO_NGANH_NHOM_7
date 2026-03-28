<?php
require_once '../../include/db.php'; // Gọi kết nối DB
$db = new Database();

$id_from_db = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Nếu không có ID, đẩy về trang chủ
if ($id_from_db === 0) {
    header('Location: ../index.php');
    exit;
}

// Truy vấn lấy thông tin đơn hàng vừa đặt
$db->query("SELECT tong_tien, dia_chi_giao_hang, ngay_dat FROM don_hang WHERE id_order = :id");
$db->bind(':id', $id_from_db);
$order = $db->single();

if (!$order) {
    die("Đơn hàng không tồn tại!");
}

// Khởi tạo các biến để dùng cho HTML bên dưới
$order_id   = 'DH' . str_pad($id_from_db, 5, '0', STR_PAD_LEFT);
$order_date = date('d/m/Y H:i', strtotime($order['ngay_dat']));
$tong_tien  = $order['tong_tien'];

// Tìm chữ "Chuyển khoản" trong chuỗi địa chỉ giao hàng để xác định phương thức
$is_bank = strpos($order['dia_chi_giao_hang'], 'PTTT: Chuyển khoản') !== false;

// --- GỌI API VIETQR NẾU LÀ CHUYỂN KHOẢN ---
$qr_url = "";
if ($is_bank) {
    $bank_id       = "vietcombank"; 
    $account_no    = "0123456789";  
    $account_name  = "NGUYEN VAN A"; 
    $transfer_text = "Thanh toan don hang " . $order_id;
    $qr_url = "https://img.vietqr.io/image/{$bank_id}-{$account_no}-compact2.png?amount={$tong_tien}&addInfo=" . urlencode($transfer_text) . "&accountName=" . urlencode($account_name);
}

$base_url     = '../';
$page_title   = 'Đặt hàng thành công - Truyện Hay';
$current_page = 'shop';
$extra_css    = ['../shop.css'];
require_once '../includes/header.php';
?>
<?php if ($is_bank): ?>
        <div class="qr-payment-box">
            <h2><i class="fas fa-qrcode"></i> Vui lòng quét mã QR để thanh toán</h2>
            <p class="qr-payment-desc">
                Mở App Ngân hàng hoặc Momo để quét mã QR dưới đây. Nội dung và số tiền sẽ được điền tự động!
            </p>
            
            <div class="qr-image-wrapper">
                <img src="<?php echo $qr_url; ?>" alt="QR Code">
            </div>
            
            <div class="qr-info-details">
                <p>Ngân hàng: <strong>VIETCOMBANK</strong></p>
                <p>Số tài khoản: <span class="text-highlight">0123456789</span></p>
                <p>Chủ tài khoản: <strong>NGUYEN VAN A</strong></p>
                <p>Số tiền: <strong><?php echo number_format($tong_tien, 0, ',', '.'); ?>đ</strong></p>
                <p>Nội dung CK: <strong><?php echo $transfer_text; ?></strong></p>
            </div>
            <p class="qr-note">* Đơn hàng sẽ được xử lý ngay sau khi nhận được thanh toán.</p>
        </div>
    <?php else: ?>
        <div class="qr-payment-box" style="border-color: #2ecc71;">
            <h2><i class="fas fa-money-bill-wave" style="color:#2ecc71;"></i> Thanh toán khi nhận hàng</h2>
            <p>Bạn đã chọn phương thức thanh toán <strong>Tiền mặt khi nhận hàng (COD)</strong>.</p>
            <p>Vui lòng chuẩn bị sẵn số tiền <strong style="color: #e74c3c; font-size: 1.2rem;"><?php echo number_format($tong_tien, 0, ',', '.'); ?>đ</strong> khi Shipper gọi nhé!</p>
        </div>
    <?php endif; ?>

<!-- Confetti -->
<div class="confetti-wrap" id="confetti"></div>

<div class="success-page">

    <!-- Icon -->
    <div class="success-icon"><i class="fas fa-check"></i></div>

    <h1>Đặt hàng thành công! </h1>
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