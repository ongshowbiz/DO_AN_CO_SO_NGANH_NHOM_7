<?php
session_start();
$base_url     = '../';
$page_title   = 'Giỏ hàng - Shop Truyện Hay';
$current_page = 'cart';
$extra_css = ['../shop.css'];
require_once __DIR__ . '/../../include/db.php';
require_once __DIR__ . '/../includes/header.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
$totalQty = 0;
?>

<div class="cart-page">
    <h1><i class="fas fa-shopping-cart"></i> Giỏ hàng</h1>

    <?php if (empty($cart)): ?>
        <div class="cart-empty">
            <i class="fas fa-shopping-cart"></i>
            <p>Giỏ hàng của bạn đang trống</p>
            <a href="index.php"><i class="fas fa-store"></i> Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div>
                <div class="cart-table">
                    <div class="cart-table-head">
                        <span>Sản phẩm</span>
                        <span>Đơn giá</span>
                        <span>Số lượng</span>
                        <span>Thành tiền</span>
                    </div>

                    <?php foreach ($cart as $id => $item):
                        $item_total = $item['gia_ban'] * $item['qty'];
                        $subtotal  += $item_total;
                        $totalQty  += $item['qty'];
                        $max_qty    = (int)($item['so_luong_kho'] ?? 999);
                    ?>
                    <div class="cart-item"
                         data-id="<?php echo $id; ?>"
                         data-price="<?php echo (int)$item['gia_ban']; ?>"
                         data-max="<?php echo $max_qty; ?>">

                        <div class="cart-item-product">
                            <img src="<?php echo htmlspecialchars($item['anh']); ?>" alt="Cover">
                            <div class="cart-item-info">
                                <h3><?php echo htmlspecialchars($item['manga_name']); ?></h3>
                                <p>NXB: <?php echo htmlspecialchars($item['nha_xuat_ban'] ?? 'Đang cập nhật'); ?></p>
                            </div>
                        </div>

                        <div class="cart-item-price">
                            <?php echo number_format($item['gia_ban'], 0, ',', '.'); ?>đ
                        </div>

                        <div class="cart-item-qty">
                            <div class="qty-control">
                                <button class="qty-btn" onclick="changeQty(this, -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input class="qty-input"
                                       type="number"
                                       value="<?php echo (int)$item['qty']; ?>"
                                       min="1"
                                       max="<?php echo $max_qty; ?>"
                                       onchange="onQtyInput(this)">
                                <button class="qty-btn" onclick="changeQty(this, 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="cart-item-actions">
                            <span class="cart-item-total">
                                <?php echo number_format($item_total, 0, ',', '.'); ?>đ
                            </span>
                            <a href="cart_action.php?action=remove&id_spmanga=<?php echo $id; ?>"
                               class="btn-remove" title="Xóa khỏi giỏ">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <div class="cart-summary">
                    <h2>Tóm tắt đơn hàng</h2>
                    <div class="summary-row">
                        <span>Tổng sản phẩm:</span>
                        <span id="summary-qty"><?php echo $totalQty; ?> sản phẩm</span>
                    </div>
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span id="summary-subtotal"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span>
                    </div>
                    <?php $ship = ($subtotal >= 300000) ? 0 : 30000; ?>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span id="summary-ship">
                            <?php echo $ship == 0 ? 'Miễn phí' : number_format($ship, 0, ',', '.') . 'đ'; ?>
                        </span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span id="summary-total">
                            <?php echo number_format($subtotal + $ship, 0, ',', '.'); ?>đ
                        </span>
                    </div>
                    <a href="checkout.php" class="btn-checkout">
                        Tiến hành thanh toán
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
function formatVND(num) {
    return num.toLocaleString('vi-VN') + 'đ';
}

function updateServer(id, qty) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id_spmanga', id);
    formData.append('qty', qty);
    fetch('cart_action.php', { method: 'POST', body: formData });
}

function recalcSummary() {
    let subtotal = 0;
    let totalQty = 0;

    document.querySelectorAll('.cart-item').forEach(row => {
        const price = parseInt(row.dataset.price);
        const qty   = parseInt(row.querySelector('.qty-input').value) || 1;
        row.querySelector('.cart-item-total').textContent = formatVND(price * qty);
        subtotal += price * qty;
        totalQty += qty;
    });

    const ship = subtotal >= 300000 ? 0 : 30000;
    document.getElementById('summary-qty').textContent      = totalQty + ' sản phẩm';
    document.getElementById('summary-subtotal').textContent = formatVND(subtotal);
    document.getElementById('summary-ship').textContent     = ship === 0 ? 'Miễn phí' : formatVND(ship);
    document.getElementById('summary-total').textContent    = formatVND(subtotal + ship);
}

function changeQty(btn, delta) {
    const row   = btn.closest('.cart-item');
    const input = row.querySelector('.qty-input');
    const max   = parseInt(row.dataset.max) || 999;
    let val     = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(max, val));
    input.value = val;
    recalcSummary();
    updateServer(row.dataset.id, val);
}

function onQtyInput(input) {
    const row = input.closest('.cart-item');
    const max = parseInt(row.dataset.max) || 999;
    let val   = parseInt(input.value) || 1;
    val = Math.max(1, Math.min(max, val));
    input.value = val;
    recalcSummary();
    updateServer(row.dataset.id, val);
}
</script>