<?php
// shop/checkout.php — Trang thanh toán (demo)
$base_url     = '../';
$page_title   = 'Thanh toán - Shop Truyện Hay';
$current_page = 'shop';
$extra_css = ['../shop.css'];
require_once '../includes/header.php';
?>


<div class="checkout-page">
    <h1><i class="fas fa-lock"></i> Thanh toán</h1>

    <!-- BƯỚC -->
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

    <div class="checkout-layout">

        <!-- FORM BÊN TRÁI -->
        <div>
            <!-- Thông tin giao hàng (ẩn nếu toàn KTS) -->
            <div class="form-section" id="shipping-section">
                <h2><i class="fas fa-map-marker-alt"></i> Thông tin giao hàng</h2>
                <div class="type-notice physical">
                    <i class="fas fa-info-circle"></i>
                    Điền địa chỉ nhận hàng cho sản phẩm truyện giấy. Sản phẩm kỹ thuật số sẽ được kích hoạt ngay sau thanh toán.
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Họ và tên *</label>
                        <input type="text" placeholder="Nguyễn Văn A" id="fullname">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại *</label>
                        <input type="tel" placeholder="0901 234 567" id="phone">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" placeholder="email@example.com" id="email">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tỉnh / Thành phố *</label>
                        <select id="province">
                            <option value="">-- Chọn tỉnh/thành --</option>
                            <option>TP. Hồ Chí Minh</option>
                            <option>Hà Nội</option>
                            <option>Đà Nẵng</option>
                            <option>Cần Thơ</option>
                            <option>Hải Phòng</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quận / Huyện *</label>
                        <input type="text" placeholder="Quận 1" id="district">
                    </div>
                </div>
                <div class="form-group">
                    <label>Địa chỉ cụ thể *</label>
                    <input type="text" placeholder="Số nhà, tên đường, phường/xã..." id="address">
                </div>
                <div class="form-group">
                    <label>Ghi chú đơn hàng</label>
                    <textarea rows="2" placeholder="Ghi chú thêm cho người giao hàng (không bắt buộc)..." id="note"></textarea>
                </div>
            </div>

            <!-- Phương thức thanh toán -->
            <div class="form-section">
                <h2><i class="fas fa-credit-card"></i> Phương thức thanh toán</h2>
                <div class="payment-options" id="payment-options">
                    <label class="payment-option selected" onclick="selectPayment(this)">
                        <input type="radio" name="payment" value="cod" checked>
                        <span class="payment-icon" style="color:#e67e22;">💵</span>
                        <div class="payment-info">
                            <strong>Tiền mặt (COD)</strong>
                            <span>Thanh toán khi nhận hàng</span>
                        </div>
                    </label>
                    <label class="payment-option" onclick="selectPayment(this)">
                        <input type="radio" name="payment" value="bank">
                        <span class="payment-icon" style="color:#3498db;">🏦</span>
                        <div class="payment-info">
                            <strong>Chuyển khoản</strong>
                            <span>Ngân hàng nội địa</span>
                        </div>
                    </label>
                    <label class="payment-option" onclick="selectPayment(this)">
                        <input type="radio" name="payment" value="momo">
                        <span class="payment-icon">💜</span>
                        <div class="payment-info">
                            <strong>Ví MoMo</strong>
                            <span>Thanh toán qua MoMo</span>
                        </div>
                    </label>
                    <label class="payment-option" onclick="selectPayment(this)">
                        <input type="radio" name="payment" value="vnpay">
                        <span class="payment-icon" style="color:#e74c3c;">💳</span>
                        <div class="payment-info">
                            <strong>VNPay</strong>
                            <span>Thẻ ATM / Visa / Master</span>
                        </div>
                    </label>
                </div>

                <!-- Thông tin chuyển khoản (hiện khi chọn bank) -->
                <div id="bank-info" style="display:none; margin-top:16px;">
                    <div class="type-notice digital">
                        <i class="fas fa-university"></i>
                        <div>
                            <strong style="color:#e0e0e0;">Thông tin chuyển khoản:</strong><br>
                            Ngân hàng: <strong>Vietcombank</strong> — Chi nhánh HCM<br>
                            Số TK: <strong>1234 5678 9012</strong> — Chủ TK: <strong>TRUYEN HAY</strong><br>
                            Nội dung CK: <em>Họ tên + SĐT</em>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ĐƠN HÀNG BÊN PHẢI -->
        <div class="order-summary">
            <h2><i class="fas fa-shopping-bag"></i> Đơn hàng của bạn</h2>
            <div id="order-items-list">
                <!-- Render bởi JS -->
            </div>
            <hr class="summary-divider">
            <div class="summary-row"><span>Tạm tính</span><span id="co-subtotal">0₫</span></div>
            <div class="summary-row"><span>Phí vận chuyển</span><span id="co-ship">30.000₫</span></div>
            <div class="summary-row total"><span>Tổng cộng</span><span id="co-total">0₫</span></div>

            <button class="btn-place-order" onclick="placeOrder()">
                <i class="fas fa-lock"></i> Đặt hàng ngay
            </button>
            <div class="secure-note">
                <i class="fas fa-shield-alt"></i> Thông tin được mã hóa & bảo mật
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
<script>
function fmt(n) { return n.toLocaleString('vi-VN') + '₫'; }
function getCart() { return JSON.parse(localStorage.getItem('truyen_hay_cart') || '[]'); }

function renderOrderSummary() {
    const cart = getCart();
    if (cart.length === 0) { window.location.href = 'cart.php'; return; }

    const listEl = document.getElementById('order-items-list');
    listEl.innerHTML = cart.map(item => `
        <div class="order-item">
            <img src="https://picsum.photos/seed/${item.id}/200/280" alt="${item.name}">
            <div class="order-item-name">
                ${item.name}
                <div class="order-item-sub">x${item.qty}</div>
            </div>
            <div class="order-item-price">${fmt(item.price * item.qty)}</div>
        </div>
    `).join('');

    const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
    const ship     = subtotal >= 300000 ? 0 : 30000;
    document.getElementById('co-subtotal').textContent = fmt(subtotal);
    document.getElementById('co-ship').textContent     = ship === 0 ? 'Miễn phí' : fmt(ship);
    document.getElementById('co-ship').style.color     = ship === 0 ? '#2ecc71' : '';
    document.getElementById('co-total').textContent    = fmt(subtotal + ship);
}

function selectPayment(el) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
    const bankInfo = document.getElementById('bank-info');
    bankInfo.style.display = el.querySelector('input').value === 'bank' ? 'block' : 'none';
}

function placeOrder() {
    const name    = document.getElementById('fullname')?.value.trim();
    const phone   = document.getElementById('phone')?.value.trim();
    const email   = document.getElementById('email')?.value.trim();
    const address = document.getElementById('address')?.value.trim();

    if (!name || !phone || !email || !address) {
        alert('Vui lòng điền đầy đủ thông tin giao hàng!');
        return;
    }

    // Thực tế: gửi POST tới PHP xử lý INSERT don_hang + chi_tiet_don_hang rồi redirect
    localStorage.removeItem('truyen_hay_cart');
    window.location.href = 'order_success.php';
}

renderOrderSummary();
</script>