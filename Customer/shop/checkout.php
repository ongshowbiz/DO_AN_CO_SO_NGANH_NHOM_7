<?php
session_start();

// 1. KIỂM TRA GIỎ HÀNG TỪ SESSION
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Nếu giỏ trống, ép quay về trang cart.php
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// 2. TÍNH TIỀN 
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['gia_ban'] * $item['qty'];
}
$ship = ($subtotal >= 300000) ? 0 : 30000;
$total = $subtotal + $ship;

// Khai báo giao diện
$base_url     = '../';
$page_title   = 'Thanh toán - Shop Truyện Hay';
$current_page = 'shop';
$extra_css    = ['../shop.css'];
require_once '../includes/header.php';
?>

<div class="checkout-page">
    <h1><i class="fas fa-lock"></i> Thanh toán</h1>

    <div class="checkout-steps">
        <div class="step done">
            <div class="step-num"><i class="fas fa-check"></i></div>
            Giỏ hàng
        </div>
        <div class="step-line done"></div>
        <div class="step active">
            <div class="step-num">2</div>
            Thông tin
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-num">3</div>
            Thanh toán
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-num">4</div>
            Hoàn tất
        </div>
    </div>
    
    <form action="process_checkout.php" method="POST" id="checkout-form">
        <div class="checkout-layout">
            
            <div>
                <div class="checkout-section" id="shipping-section">
                    <h2><i class="fas fa-map-marker-alt"></i> Thông tin giao hàng</h2>
                    <div class="form-group">
                        <label>Họ và tên <span style="color:red">*</span></label>
                        <input type="text" id="fullname" name="fullname" required placeholder="Nhập họ tên người nhận">
                    </div>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group">
                            <label>Số điện thoại <span style="color:red">*</span></label>
                            <input type="tel" id="phone" name="phone" required placeholder="Nhập số điện thoại">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" id="email" name="email" placeholder="Nhập email (để nhận hóa đơn)">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ nhận hàng <span style="color:red">*</span></label>
                        <input type="text" id="address" name="address" required placeholder="Số nhà, Tên đường, Phường, Quận/Huyện, Tỉnh/TP">
                    </div>

                    <div class="form-group">
                        <label>Ghi chú đơn hàng</label>
                        <textarea id="note" name="note" rows="3" placeholder="Giao giờ hành chính, gọi trước khi giao..."></textarea>
                    </div>
                </div>

                <div class="checkout-section">
                    <h2><i class="fas fa-wallet"></i> Phương thức thanh toán</h2>
                    
                    <label class="payment-option selected" onclick="selectPayment(this)" style="display:block; padding:15px; border:1px solid #ddd; border-radius:6px; margin-bottom:10px; cursor:pointer;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <input type="radio" name="payment_method" value="cod" checked style="display:none;">
                            <i class="fas fa-money-bill-wave" style="color:#2ecc71; font-size:1.2rem;"></i>
                            <strong>Thanh toán khi nhận hàng (COD)</strong>
                        </div>
                    </label>

                    <label class="payment-option" onclick="selectPayment(this)" style="display:block; padding:15px; border:1px solid #ddd; border-radius:6px; cursor:pointer;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <input type="radio" name="payment_method" value="bank" style="display:none;">
                            <i class="fas fa-university" style="color:#3498db; font-size:1.2rem;"></i>
                            <strong>Chuyển khoản ngân hàng</strong>
                        </div>
                    </label>

                    <div id="bank-info" style="display:none; margin-top:15px; padding:15px; background:#f8f9fa; border:1px dashed #3498db; border-radius:8px;">
                        <p style="margin:0 0 5px 0;"><strong>Ngân hàng:</strong> Vietcombank</p>
                        <p style="margin:0 0 5px 0;"><strong>Số tài khoản:</strong> 1234567890</p>
                        <p style="margin:0 0 5px 0;"><strong>Chủ tài khoản:</strong> NGUYEN VAN A</p>
                        <p style="color:#e74c3c; font-size:0.9rem; margin-top:10px; font-style:italic;">
                            * Vui lòng chờ sau khi bấm Đặt Hàng để lấy Mã Đơn Hàng quét mã QR tự động.
                        </p>
                    </div>
                </div>
            </div>

            <div class="checkout-section" style="background:#fff;">
                <h2>Đơn hàng của bạn</h2>
                <div class="summary-row" style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span>Tạm tính:</span>
                    <span id="co-subtotal"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="summary-row" style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span>Phí vận chuyển:</span>
                    <span id="co-ship" style="color: <?php echo $ship == 0 ? '#2ecc71' : 'inherit'; ?>">
                        <?php echo $ship == 0 ? 'Miễn phí' : number_format($ship, 0, ',', '.') . 'đ'; ?>
                    </span>
                </div>
                <div class="summary-row total" style="display:flex; justify-content:space-between; margin-top:15px; padding-top:15px; border-top:1px solid #eee; font-weight:bold; font-size:1.2rem;">
                    <span>Tổng cộng:</span>
                    <span id="co-total" style="color:#e74c3c;"><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                </div>
                
                <button type="submit" class="btn-checkout" style="width:100%; padding:15px; background:#e74c3c; color:#fff; font-weight:bold; font-size:1.1rem; border:none; border-radius:6px; margin-top:20px; cursor:pointer;">
                    ĐẶT HÀNG
                </button>
            </div>

        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
// Chuyển đổi lựa chọn phương thức thanh toán
function selectPayment(el) {
    // Bỏ chọn tất cả
    document.querySelectorAll('.payment-option').forEach(o => {
        o.classList.remove('selected');
        o.style.borderColor = '#ddd';
    });
    
    // Đánh dấu cái được chọn
    el.classList.add('selected');
    el.style.borderColor = '#3498db';
    
    // Check ngầm thẻ input radio bên trong
    const radio = el.querySelector('input[type="radio"]');
    radio.checked = true;
    
    // Ẩn/hiện thông tin ngân hàng
    const bankInfo = document.getElementById('bank-info');
    if (radio.value === 'bank') {
        bankInfo.style.display = 'block';
    } else {
        bankInfo.style.display = 'none';
    }
}
</script>