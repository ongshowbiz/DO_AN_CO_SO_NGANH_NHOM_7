<?php
// shop/cart.php — Trang giỏ hàng
// Giỏ hàng được lưu localStorage ở client (JS xử lý)
$base_url     = '../';
$page_title   = 'Giỏ hàng - Shop Truyện Hay';
$current_page = 'cart';
$extra_css = ['../shop.css'];
require_once '../includes/header.php';
?>


<div class="cart-page">
    <h1><i class="fas fa-shopping-cart"></i> Giỏ hàng</h1>

    <!-- Giỏ rỗng (hiện khi JS load xong và giỏ trống) -->
    <div id="cart-empty-state" style="display:none;">
        <div class="cart-empty">
            <i class="fas fa-shopping-cart"></i>
            <p>Giỏ hàng của bạn đang trống</p>
            <a href="index.php"><i class="fas fa-store"></i> Tiếp tục mua sắm</a>
        </div>
    </div>

    <!-- Giỏ có hàng -->
    <div id="cart-has-items" style="display:none;">
        <div class="cart-layout">

            <!-- DANH SÁCH SẢN PHẨM -->
            <div>
                <div class="cart-table">
                    <div class="cart-table-head">
                        <span>Sản phẩm</span>
                        <span>Đơn giá</span>
                        <span style="text-align:center;">Số lượng</span>
                        <span style="text-align:right;">Thành tiền</span>
                    </div>
                    <div id="cart-items-list"></div>
                </div>
            </div>

            <!-- TỔNG KẾT -->
            <div class="cart-summary">
                <h2><i class="fas fa-receipt"></i> Tóm tắt đơn hàng</h2>
                <div class="summary-row">
                    <span id="summary-count">0 sản phẩm</span>
                    <span id="summary-subtotal">0₫</span>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển</span>
                    <span id="summary-ship">30.000₫</span>
                </div>
                <div class="summary-row total">
                    <span>Tổng cộng</span>
                    <span id="summary-total">0₫</span>
                </div>
                <a href="checkout.php" class="btn-checkout">
                    <i class="fas fa-lock"></i> Tiến hành thanh toán
                </a>
                <a href="index.php" class="btn-continue">
                    <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                </a>
                <div class="ship-note">
                    <i class="fas fa-truck"></i>
                    Miễn phí vận chuyển cho đơn từ 300.000₫
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
<script>
function fmt(n) { return n.toLocaleString('vi-VN') + '₫'; }
function getCart() { return JSON.parse(localStorage.getItem('truyen_hay_cart') || '[]'); }
function saveCart(c) { localStorage.setItem('truyen_hay_cart', JSON.stringify(c)); renderCart(); }

function renderCart() {
    const cart = getCart();
    const emptyEl = document.getElementById('cart-empty-state');
    const hasEl   = document.getElementById('cart-has-items');
    const listEl  = document.getElementById('cart-items-list');

    if (cart.length === 0) {
        emptyEl.style.display = 'block';
        hasEl.style.display   = 'none';
        return;
    }
    emptyEl.style.display = 'none';
    hasEl.style.display   = 'block';

    // Render items
    listEl.innerHTML = cart.map((item, idx) => `
        <div class="cart-item">
            <div class="cart-item-info">
                <img src="https://picsum.photos/seed/${item.id}/200/280" alt="${item.name}">
                <div>
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-type"><i class="fas fa-book"></i> Truyện giấy</div>
                </div>
            </div>
            <div class="cart-item-price">${fmt(item.price)}</div>
            <div>
                <div class="cart-qty">
                    <button onclick="updateQty(${idx}, -1)"><i class="fas fa-minus"></i></button>
                    <span>${item.qty}</span>
                    <button onclick="updateQty(${idx}, 1)"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <span class="cart-item-total">${fmt(item.price * item.qty)}</span>
                <button class="btn-remove" onclick="removeItem(${idx})" title="Xóa"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    `).join('');

    // Tính tổng
    const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
    const totalQty = cart.reduce((s, i) => s + i.qty, 0);
    const ship     = subtotal >= 300000 ? 0 : 30000;
    const total    = subtotal + ship;

    document.getElementById('summary-count').textContent    = totalQty + ' sản phẩm';
    document.getElementById('summary-subtotal').textContent = fmt(subtotal);
    document.getElementById('summary-ship').textContent     = ship === 0 ? 'Miễn phí' : fmt(ship);
    document.getElementById('summary-total').textContent    = fmt(total);
    document.getElementById('summary-ship').style.color     = ship === 0 ? '#2ecc71' : '';
}

function updateQty(idx, delta) {
    const cart = getCart();
    cart[idx].qty = Math.max(1, cart[idx].qty + delta);
    saveCart(cart);
}

function removeItem(idx) {
    const cart = getCart();
    cart.splice(idx, 1);
    saveCart(cart);
}

renderCart();
</script>