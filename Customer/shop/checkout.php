<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

require_once __DIR__ . '/../../include/db.php';
require_once __DIR__ . '/../../include/membership.php'; 

// Kiểm tra xem giỏ hàng có giá đã giảm chưa (thêm từ product.php thì đã giảm rồi)
// Trường hợp user thêm từ index.php qua addToCart() cũ chưa có override → áp dụng lại
$perms = MembershipHelper::get($_SESSION['user_id']);
$disc  = $perms['giam_gia_mua'];

$subtotal       = 0;
$subtotal_goc   = 0; // tổng giá gốc (trước giảm) để hiển thị so sánh

foreach ($cart as $item) {
    $subtotal     += $item['gia_ban'] * $item['qty'];
    $gia_goc_item  = $item['gia_goc'] ?? $item['gia_ban'];
    $subtotal_goc += $gia_goc_item * $item['qty'];
}

$tiet_kiem  = $subtotal_goc - $subtotal; // số tiền tiết kiệm được
$ship       = ($subtotal >= 300000) ? 0 : 30000;
$total      = $subtotal + $ship;

$base_url     = '../';
$page_title   = 'Thanh toán - Shop Truyện Hay';
$current_page = 'shop';
$extra_css    = ['../shop.css'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="checkout-page">
    <h1><i class="fas fa-lock"></i> Thanh toán</h1>

    <div class="checkout-steps">
        <div class="step done"><div class="step-num"><i class="fas fa-check"></i></div>Giỏ hàng</div>
        <div class="step-line done"></div>
        <div class="step active"><div class="step-num">2</div>Thanh toán</div>
        <div class="step-line"></div>
        <div class="step"><div class="step-num">3</div>Hoàn tất</div>
    </div>

    <form action="process_checkout.php" method="POST" id="checkout-form">
        <div class="checkout-layout">

            <div>
                <!-- THÔNG TIN GIAO HÀNG -->
                <div class="checkout-section">
                    <h2><i class="fas fa-map-marker-alt"></i> Thông tin giao hàng</h2>
                    <div class="form-group">
                        <label>Họ và tên <span style="color:red">*</span></label>
                        <input type="text" name="fullname" required placeholder="Nhập họ tên người nhận">
                    </div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Số điện thoại <span style="color:red">*</span></label>
                            <input type="tel" name="phone" required placeholder="Nhập số điện thoại">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="Nhập email (để nhận hóa đơn)">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ nhận hàng <span style="color:red">*</span></label>
                        <input type="text" name="address" required placeholder="Số nhà, Tên đường, Phường, Quận, Tỉnh/TP">
                    </div>
                    <div class="form-group">
                        <label>Ghi chú đơn hàng</label>
                        <textarea name="note" rows="3" placeholder="Giao giờ hành chính, gọi trước khi giao..."></textarea>
                    </div>
                </div>

                <!-- PHƯƠNG THỨC THANH TOÁN -->
                <div class="checkout-section">
                    <h2><i class="fas fa-wallet"></i> Phương thức thanh toán</h2>
                    <label class="payment-option selected" onclick="selectPayment(this)">
                        <input type="radio" name="payment_method" value="cod" checked style="display:none;">
                        <i class="fas fa-money-bill-wave" style="color:#2ecc71;font-size:1.5rem;margin-right:10px;"></i>
                        <div>
                            <strong style="display:block;margin-bottom:4px;">Thanh toán khi nhận hàng (COD)</strong>
                            <span style="font-size:.85rem;color:#777;">Thanh toán bằng tiền mặt khi giao hàng.</span>
                        </div>
                    </label>
                    <label class="payment-option" onclick="selectPayment(this)">
                        <input type="radio" name="payment_method" value="bank" style="display:none;">
                        <i class="fas fa-university" style="color:#3498db;font-size:1.5rem;margin-right:10px;"></i>
                        <div>
                            <strong style="display:block;margin-bottom:4px;">Chuyển khoản qua Ngân hàng</strong>
                            <span style="font-size:.85rem;color:#777;">Sử dụng App ngân hàng để quét mã QR.</span>
                        </div>
                    </label>
                    <div id="bank-info" style="display:none;margin-top:15px;padding:15px;background:#f4faff;border:1px dashed #3498db;border-radius:8px;">
                        <p style="margin:0 0 5px;"><strong>Ngân hàng:</strong> Vietcombank</p>
                        <p style="margin:0 0 5px;"><strong>Số tài khoản:</strong> 1234567890</p>
                        <p style="margin:0 0 5px;"><strong>Chủ tài khoản:</strong> NGUYEN VAN A</p>
                        <p style="color:#e74c3c;font-size:.9rem;margin-top:10px;font-style:italic;">
                            * Vui lòng bấm ĐẶT HÀNG để lấy Mã Đơn Hàng quét QR tự động.
                        </p>
                    </div>
                </div>
            </div>

            <!-- TÓM TẮT ĐƠN HÀNG -->
            <div class="checkout-section">
                <h2>Đơn hàng của bạn</h2>

                <div class="summary-row">
                    <span>Tạm tính (giá gốc):</span>
                    <span><?php echo number_format($subtotal_goc, 0, ',', '.'); ?>đ</span>
                </div>

                <?php if ($tiet_kiem > 0): ?>
                <!-- ★ Dòng tiết kiệm từ membership -->
                <div class="summary-row" style="color:#ff4757;">
                    <span>
                        <i class="fas fa-crown"></i>
                        Ưu đãi <?= htmlspecialchars($perms['ten_goi']) ?> (–<?= $disc ?>%):
                    </span>
                    <span>–<?php echo number_format($tiet_kiem, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="summary-row">
                    <span>Sau ưu đãi:</span>
                    <span><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span>
                </div>
                <?php else: ?>
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <span><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span>
                </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span style="color:<?php echo $ship==0?'#2ecc71':'inherit'; ?>">
                        <?php echo $ship==0 ? 'Miễn phí' : number_format($ship,0,',','.') . 'đ'; ?>
                    </span>
                </div>
                <div class="summary-row total">
                    <span>Tổng cộng:</span>
                    <span><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                </div>

                <?php if ($tiet_kiem > 0): ?>
                <div style="background:#fff3f4;border:1px solid #ffb3b8;border-radius:8px;
                            padding:8px 12px;margin:10px 0;font-size:.85rem;color:#ff4757;text-align:center;">
                    <i class="fas fa-piggy-bank"></i>
                    Bạn tiết kiệm được <strong><?= number_format($tiet_kiem, 0, ',', '.') ?>đ</strong> nhờ ưu đãi thành viên!
                </div>
                <?php endif; ?>

                <button type="submit" class="btn-checkout">ĐẶT HÀNG</button>
            </div>

        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
function selectPayment(el) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
    document.getElementById('bank-info').style.display =
        el.querySelector('input').value === 'bank' ? 'block' : 'none';
}
</script>