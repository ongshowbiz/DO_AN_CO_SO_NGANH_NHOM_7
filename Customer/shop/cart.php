<?php
session_start();
$base_url     = '../';
$page_title   = 'Giỏ hàng - Shop Truyện Hay';
$current_page = 'cart';
$extra_css = ['../shop.css'];
require_once '../includes/header.php';

// Lấy giỏ hàng từ Session
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
                    <div class="cart-table-head" style="display: grid; grid-template-columns: 2.5fr 1fr 1fr 1fr; align-items: center; font-weight: bold; padding-bottom: 12px; border-bottom: 2px solid #eee; color: #888; text-transform: uppercase; font-size: 0.85rem;">
                        <span>Sản phẩm</span>
                        <span style="text-align:center;">Đơn giá</span>
                        <span style="text-align:center;">Số lượng</span>
                        <span style="text-align:right;">Thành tiền</span>
                    </div>

                    <?php foreach ($cart as $id => $item): 
                        $item_total = $item['gia_ban'] * $item['qty'];
                        $subtotal += $item_total;
                        $totalQty += $item['qty'];
                    ?>
                    <div class="cart-item" style="display: grid; grid-template-columns: 2.5fr 1fr 1fr 1fr; align-items: center; padding: 20px 0; border-bottom: 1px solid #eee;">
                        
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <img src="<?php echo htmlspecialchars($item['anh']); ?>" alt="Cover" style="width: 70px; height: 100px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            <div class="cart-item-info">
                                <h3 style="margin: 0 0 6px 0; font-size: 1rem; color: #333; line-height: 1.3;"><?php echo htmlspecialchars($item['manga_name']); ?></h3>
                                <p style="margin: 0; font-size: 0.85rem; color: #888;">NXB: <?php echo htmlspecialchars($item['nha_xuat_ban'] ?? 'Đang cập nhật'); ?></p>
                            </div>
                        </div>
                        
                        <div class="cart-item-price" style="font-weight: 600; text-align:center; color: #444;">
                            <?php echo number_format($item['gia_ban'], 0, ',', '.'); ?>đ
                        </div>
                        
                        <div class="cart-item-qty" style="display: flex; justify-content: center;">
                            <form action="cart_action.php" method="POST" style="display:flex; align-items:center; gap:5px; background: #f9f9f9; padding: 4px; border-radius: 6px; border: 1px solid #ddd;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id_spmanga" value="<?php echo $id; ?>">
                                <input type="number" name="qty" value="<?php echo $item['qty']; ?>" min="1" max="<?php echo $item['so_luong_kho'] ?? 999; ?>" style="width: 45px; text-align:center; border: none; background: transparent; font-weight: bold; outline: none;">
                                <button type="submit" title="Cập nhật" style="padding: 6px 10px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; transition: 0.2s;">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </form>
                        </div>

                        <div style="display:flex; align-items:center; justify-content:flex-end; gap: 20px;">
                            <span class="cart-item-total" style="font-weight: bold; color: #e74c3c; font-size: 1.1rem;"><?php echo number_format($item_total, 0, ',', '.'); ?>đ</span>
                            <a href="cart_action.php?action=remove&id_spmanga=<?php echo $id; ?>" class="btn-remove" title="Xóa khỏi giỏ" style="color: #bbb; text-decoration: none; font-size: 1.2rem; transition: 0.2s;" onmouseover="this.style.color='#e74c3c'" onmouseout="this.style.color='#bbb'">
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
                        <span><?php echo $totalQty; ?> sản phẩm</span>
                    </div>
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span>
                    </div>
                    <?php $ship = ($subtotal >= 300000) ? 0 : 30000; ?>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span style="color: <?php echo $ship == 0 ? '#2ecc71' : 'inherit'; ?>">
                            <?php echo $ship == 0 ? 'Miễn phí' : number_format($ship, 0, ',', '.') . 'đ'; ?>
                        </span>
                    </div>
                    <div class="summary-row total" style="font-size: 1.2rem; font-weight: bold; margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
                        <span>Tổng cộng:</span>
                        <span style="color: #e74c3c;"><?php echo number_format($subtotal + $ship, 0, ',', '.'); ?>đ</span>
                    </div>
                    <a href="checkout.php" class="btn-checkout" style="display: block; text-align: center; background: #e74c3c; color: white; padding: 12px; text-decoration: none; border-radius: 8px; margin-top: 20px; font-weight: bold;">
                        Tiến hành thanh toán
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>