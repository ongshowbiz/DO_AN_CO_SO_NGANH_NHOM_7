<?php
// shop/product.php — Trang chi tiết sản phẩm
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// --- DỮ LIỆU MẪU DEMO ---
// Thực tế: SELECT sp.*, m.* FROM sanpham_manga sp JOIN manga m ON sp.id_manga=m.id_manga WHERE sp.id_spmanga=?
$product = [
    'id_spmanga'   => $id,
    'manga_name'   => 'Tên Truyện Mẫu ' . $id,
    'slug'         => 'ten-truyen-mau-' . $id,
    'tacgia'       => 'Tác Giả Mẫu',
    'nha_xuat_ban' => 'NXB Kim Đồng',
    'mota'         => 'Đây là bộ truyện tranh hành động đỉnh cao với nội dung hấp dẫn, kịch tính. Nhân vật chính phải vượt qua vô số thử thách để bảo vệ những người thân yêu. Được xuất bản bởi NXB Kim Đồng với chất lượng in ấn tuyệt vời, màu sắc sắc nét và giấy in cao cấp.',
    'anh'          => "https://picsum.photos/seed/{$id}/300/420",
    'gia_ban'      => 85000,
    'so_luong_kho' => 23,
    'the_loai'     => 'Hành Động, Phiêu Lưu',
    'type'         => ($id % 2 === 0) ? 'digital' : 'physical',
    'so_tap'       => 12,
    'nam_xb'       => 2023,
];
$related = [];
for ($i = 1; $i <= 5; $i++) {
    $ri = ($id + $i - 1) % 12 + 1;
    $related[] = [
        'id_spmanga' => $ri,
        'manga_name' => 'Truyện Liên Quan ' . $ri,
        'anh'        => "https://picsum.photos/seed/" . ($ri + 30) . "/200/280",
        'gia_ban'    => rand(45, 130) * 1000,
        'type'       => $ri % 2 === 0 ? 'digital' : 'physical',
    ];
}

$base_url     = '../';
$page_title   = htmlspecialchars($product['manga_name']) . ' - Shop Truyện Hay';
$current_page = 'shop';
$extra_css = ['../shop.css'];
require_once '../includes/header.php';
?>


<!-- BREADCRUMB -->
<div class="breadcrumb">
    <a href="../index.php"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <a href="index.php"><i class="fas fa-store"></i> Shop</a>
    <i class="fas fa-chevron-right"></i>
    <span><?php echo htmlspecialchars($product['manga_name']); ?></span>
</div>

<div class="product-detail-wrap">

    <!-- 2 CỘT CHÍNH -->
    <div class="product-main">

        <!-- ẢNH BÌA -->
        <div>
            <div class="product-cover">
                <img src="<?php echo $product['anh']; ?>" alt="<?php echo htmlspecialchars($product['manga_name']); ?>">
            </div>
            <div style="text-align:center;">
                <span class="cover-badge <?php echo $product['type']; ?>">
                    <?php echo $product['type']==='physical' ? '<i class="fas fa-book"></i> Truyện Giấy' : '<i class="fas fa-tablet-alt"></i> Kỹ Thuật Số'; ?>
                </span>
            </div>
        </div>

        <!-- THÔNG TIN -->
        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['manga_name']); ?></h1>

            <div class="meta-tags">
                <div class="meta-tag"><i class="fas fa-user-edit"></i> <span>Tác giả: <strong><?php echo htmlspecialchars($product['tacgia']); ?></strong></span></div>
                <div class="meta-tag"><i class="fas fa-building"></i> <span>NXB: <strong><?php echo htmlspecialchars($product['nha_xuat_ban']); ?></strong></span></div>
                <div class="meta-tag"><i class="fas fa-tags"></i> <span><?php echo htmlspecialchars($product['the_loai']); ?></span></div>
                <div class="meta-tag"><i class="fas fa-calendar"></i> <span><?php echo $product['nam_xb']; ?></span></div>
                <?php if ($product['type'] === 'physical'): ?>
                <div class="meta-tag"><i class="fas fa-layer-group"></i> <span><?php echo $product['so_tap']; ?> tập</span></div>
                <?php endif; ?>
            </div>

            <!-- GIÁ -->
            <div class="price-box">
                <div>
                    <div class="price-big"><?php echo number_format($product['gia_ban'], 0, ',', '.'); ?>₫</div>
                    <div class="price-label">Giá bán lẻ đã bao gồm VAT</div>
                </div>
                <?php if ($product['type'] === 'digital'): ?>
                    <span class="stock-badge inf"><i class="fas fa-infinity"></i> Không giới hạn</span>
                <?php elseif ($product['so_luong_kho'] <= 0): ?>
                    <span class="stock-badge out"><i class="fas fa-times-circle"></i> Hết hàng</span>
                <?php elseif ($product['so_luong_kho'] < 10): ?>
                    <span class="stock-badge low"><i class="fas fa-exclamation-circle"></i> Còn <?php echo $product['so_luong_kho']; ?> cuốn</span>
                <?php else: ?>
                    <span class="stock-badge ok"><i class="fas fa-check-circle"></i> Còn hàng (<?php echo $product['so_luong_kho']; ?>)</span>
                <?php endif; ?>
            </div>

            <!-- MUA -->
            <?php if ($product['type'] === 'digital' || $product['so_luong_kho'] > 0): ?>
            <div class="buy-row">
                <?php if ($product['type'] === 'physical'): ?>
                <div class="qty-box">
                    <button onclick="changeQty(-1)"><i class="fas fa-minus"></i></button>
                    <input type="number" id="qty" value="1" min="1" max="<?php echo $product['so_luong_kho']; ?>">
                    <button onclick="changeQty(1)"><i class="fas fa-plus"></i></button>
                </div>
                <?php endif; ?>
                <button class="btn-cart" onclick="addToCart()"><i class="fas fa-cart-plus"></i> Thêm giỏ hàng</button>
                <button class="btn-buy-now" onclick="buyNow()">
                    <i class="fas fa-<?php echo $product['type']==='digital' ? 'unlock' : 'bolt'; ?>"></i>
                    <?php echo $product['type']==='digital' ? 'Mua & Đọc ngay' : 'Mua ngay'; ?>
                </button>
            </div>
            <?php else: ?>
            <button class="btn-cart" disabled style="opacity:.4;cursor:not-allowed;"><i class="fas fa-times"></i> Hết hàng</button>
            <?php endif; ?>

            <!-- MÔ TẢ -->
            <div class="desc-box">
                <h3><i class="fas fa-align-left"></i> Mô tả sản phẩm</h3>
                <p><?php echo nl2br(htmlspecialchars($product['mota'])); ?></p>
            </div>

            <!-- SHIP INFO (chỉ hiện với truyện giấy) -->
            <?php if ($product['type'] === 'physical'): ?>
            <div class="ship-info">
                <div class="ship-item">
                    <i class="fas fa-shipping-fast"></i>
                    <span><strong>Giao hàng toàn quốc</strong>2–5 ngày làm việc</span>
                </div>
                <div class="ship-item">
                    <i class="fas fa-undo-alt"></i>
                    <span><strong>Đổi trả 7 ngày</strong>Nếu sản phẩm lỗi</span>
                </div>
                <div class="ship-item">
                    <i class="fas fa-shield-alt"></i>
                    <span><strong>Hàng chính hãng</strong>Có tem NXB</span>
                </div>
            </div>
            <?php else: ?>
            <div class="ship-info">
                <div class="ship-item">
                    <i class="fas fa-bolt"></i>
                    <span><strong>Truy cập ngay</strong>Sau khi thanh toán</span>
                </div>
                <div class="ship-item">
                    <i class="fas fa-mobile-alt"></i>
                    <span><strong>Đọc mọi thiết bị</strong>PC, điện thoại, máy tính bảng</span>
                </div>
                <div class="ship-item">
                    <i class="fas fa-infinity"></i>
                    <span><strong>Trọn đời</strong>Không hết hạn</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SẢN PHẨM LIÊN QUAN -->
    <div class="related-section">
        <h2><i class="fas fa-th-large"></i> Sản phẩm liên quan</h2>
        <div class="related-grid">
            <?php foreach ($related as $r): ?>
            <a href="product.php?id=<?php echo $r['id_spmanga']; ?>" class="related-card">
                <img src="<?php echo $r['anh']; ?>" alt="<?php echo htmlspecialchars($r['manga_name']); ?>" loading="lazy">
                <div class="related-card-body">
                    <p><?php echo htmlspecialchars($r['manga_name']); ?></p>
                    <strong><?php echo number_format($r['gia_ban'], 0, ',', '.'); ?>₫</strong>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="cart-toast" id="cart-toast"><i class="fas fa-check"></i> Đã thêm vào giỏ hàng!</div>

<?php require_once '../includes/footer.php'; ?>
<script>
function changeQty(delta) {
    const input = document.getElementById('qty');
    const max = parseInt(input.max) || 99;
    let val = parseInt(input.value) + delta;
    input.value = Math.max(1, Math.min(max, val));
}

function getCart() { return JSON.parse(localStorage.getItem('truyen_hay_cart') || '[]'); }
function saveCart(c) { localStorage.setItem('truyen_hay_cart', JSON.stringify(c)); }

function addToCart() {
    const qty = parseInt(document.getElementById('qty')?.value || 1);
    const cart = getCart();
    const id = <?php echo $product['id_spmanga']; ?>;
    const idx = cart.findIndex(i => i.id === id);
    if (idx >= 0) cart[idx].qty += qty;
    else cart.push({ id, name: <?php echo json_encode($product['manga_name']); ?>, price: <?php echo $product['gia_ban']; ?>, qty });
    saveCart(cart);
    const toast = document.getElementById('cart-toast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
}

function buyNow() {
    addToCart();
    window.location.href = 'cart.php';
}
</script>