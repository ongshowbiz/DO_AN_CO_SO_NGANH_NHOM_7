<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/membership.php'; 

$db = new Database();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) die("Sản phẩm không hợp lệ!");

$db->query("SELECT sp.id_spmanga, sp.gia_ban, sp.nha_xuat_ban, sp.so_luong_kho,
               m.manga_name, m.slug, m.anh, m.tacgia, m.mota, m.id_theloaimanga, m.create_day
        FROM sanpham_manga sp
        LEFT JOIN manga m ON sp.id_manga = m.id_manga
        WHERE sp.id_spmanga = :id");
$db->bind(':id', $id);
$product = $db->single();
if (!$product) die("Không tìm thấy sản phẩm!");

$db->query("SELECT sp.id_spmanga, sp.gia_ban, m.manga_name, m.anh
            FROM sanpham_manga sp
            JOIN manga m ON sp.id_manga = m.id_manga
            WHERE m.id_theloaimanga = :id_theloai AND sp.id_spmanga != :id_sp_hien_tai
            LIMIT 4");
$db->bind(':id_theloai', $product['id_theloaimanga'] ?? 0);
$db->bind(':id_sp_hien_tai', $id);
$related = $db->resultSet();
$product['type'] = 'physical';

// Lấy quyền membership & tính giá
$perms    = MembershipHelper::get($_SESSION['user_id'] ?? 0);
$disc     = $perms['giam_gia_mua'];
$gia_goc  = (float)$product['gia_ban'];
$gia_hien = MembershipHelper::applyDiscount($gia_goc, $_SESSION['user_id'] ?? 0);
$co_giam  = ($gia_hien < $gia_goc);

$base_url     = '../';
$page_title   = htmlspecialchars($product['manga_name']) . ' - Shop Truyện Hay';
$current_page = 'shop';
$extra_css    = ['../shop.css'];
require_once '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="../index.php"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <a href="index.php"><i class="fas fa-store"></i> Shop</a>
    <i class="fas fa-chevron-right"></i>
    <span><?php echo htmlspecialchars($product['manga_name']); ?></span>
</div>

<div class="product-detail-wrap">
    <div class="product-main">

        <!-- ẢNH BÌA -->
        <div>
            <div class="product-cover">
                <img src="<?php echo $product['anh']; ?>"
                     alt="<?php echo htmlspecialchars($product['manga_name']); ?>">
            </div>
            <div style="text-align:center;">
                <span class="cover-badge physical">
                    <i class="fas fa-book"></i> Truyện Giấy
                </span>
            </div>
        </div>

        <!-- THÔNG TIN -->
        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['manga_name']); ?></h1>

            <div class="meta-tags">
                <div class="meta-tag"><i class="fas fa-user-edit"></i> <span>Tác giả: <strong><?php echo htmlspecialchars($product['tacgia']); ?></strong></span></div>
                <div class="meta-tag"><i class="fas fa-building"></i> <span>NXB: <strong><?php echo htmlspecialchars($product['nha_xuat_ban']); ?></strong></span></div>
                <div class="meta-tag"><i class="fas fa-calendar"></i> <span>Ngày tạo: <strong><?php echo $product['create_day']; ?></strong></span></div>
                <div class="meta-tag"><i class="fas fa-box"></i> <span>Kho: <strong><?php echo $product['so_luong_kho']; ?></strong> cuốn</span></div>
            </div>

            <!-- GIÁ (có hiển thị giảm giá membership) -->
            <div class="price-box">
                <div>
                    <?php if ($co_giam): ?>
                        <!-- Badge ưu đãi membership -->
                        <div style="display:inline-flex;align-items:center;gap:6px;
                                    background:#fff3f4;border:1px solid #ffb3b8;
                                    color:#ff4757;border-radius:20px;
                                    padding:4px 12px;font-size:.82rem;font-weight:700;margin-bottom:8px;">
                            <i class="fas fa-crown"></i>
                            Ưu đãi <?= htmlspecialchars($perms['ten_goi']) ?> –<?= $disc ?>%
                        </div>
                        <div style="text-decoration:line-through;color:#999;font-size:1rem;margin-bottom:2px;">
                            <?= number_format($gia_goc, 0, ',', '.') ?>₫
                        </div>
                        <div class="price-big" style="color:#ff4757;">
                            <?= number_format($gia_hien, 0, ',', '.') ?>₫
                        </div>
                        <div class="price-label">Giá sau ưu đãi thành viên</div>
                    <?php else: ?>
                        <div class="price-big"><?= number_format($gia_goc, 0, ',', '.') ?>₫</div>
                        <div class="price-label">Giá bán lẻ đã bao gồm VAT</div>
                        <?php if (!$perms['is_active']): ?>
                            <div style="font-size:.8rem;color:#a4b0be;margin-top:4px;">
                                <i class="fas fa-crown"></i>
                                <a href="../membership/index.php" style="color:#ff4757;">Đăng ký thành viên</a>
                                để được giảm giá lên đến 25%
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if ($product['so_luong_kho'] <= 0): ?>
                    <span class="stock-badge out"><i class="fas fa-times-circle"></i> Hết hàng</span>
                <?php elseif ($product['so_luong_kho'] < 10): ?>
                    <span class="stock-badge low"><i class="fas fa-exclamation-circle"></i> Còn <?php echo $product['so_luong_kho']; ?> cuốn</span>
                <?php else: ?>
                    <span class="stock-badge ok"><i class="fas fa-check-circle"></i> Còn hàng (<?php echo $product['so_luong_kho']; ?>)</span>
                <?php endif; ?>
            </div>

            <!-- MUA -->
            <?php if ($product['so_luong_kho'] > 0): ?>
            <div class="buy-row">
                <div class="qty-box">
                    <button onclick="changeQty(-1)"><i class="fas fa-minus"></i></button>
                    <input type="number" id="qty" value="1" min="1" max="<?php echo $product['so_luong_kho']; ?>">
                    <button onclick="changeQty(1)"><i class="fas fa-plus"></i></button>
                </div>
                <button class="btn-cart" onclick="addToCart()"><i class="fas fa-cart-plus"></i> Thêm giỏ hàng</button>
                <button class="btn-buy-now" onclick="buyNow()">
                    <i class="fas fa-bolt"></i> Mua ngay
                </button>
            </div>
            <?php else: ?>
            <button class="btn-cart" disabled style="opacity:.4;cursor:not-allowed;">
                <i class="fas fa-times"></i> Hết hàng
            </button>
            <?php endif; ?>

            <!-- MÔ TẢ -->
            <div class="desc-box">
                <h3><i class="fas fa-align-left"></i> Mô tả sản phẩm</h3>
                <p><?php echo nl2br(htmlspecialchars($product['mota'])); ?></p>
            </div>

            <!-- SHIP INFO -->
            <div class="ship-info">
                <div class="ship-item"><i class="fas fa-shipping-fast"></i><span><strong>Giao hàng toàn quốc</strong>2–5 ngày làm việc</span></div>
                <div class="ship-item"><i class="fas fa-undo-alt"></i><span><strong>Đổi trả 7 ngày</strong>Nếu sản phẩm lỗi</span></div>
                <div class="ship-item"><i class="fas fa-shield-alt"></i><span><strong>Hàng chính hãng</strong>Có tem NXB</span></div>
            </div>
        </div>
    </div>

    <!-- SẢN PHẨM LIÊN QUAN -->
    <div class="related-section">
        <h2><i class="fas fa-th-large"></i> Sản phẩm liên quan</h2>
        <div class="related-grid">
            <?php foreach ($related as $r):
                $r_giam = MembershipHelper::applyDiscount((float)$r['gia_ban'], $_SESSION['user_id'] ?? 0);
            ?>
            <a href="product.php?id=<?php echo $r['id_spmanga']; ?>" class="related-card">
                <img src="<?php echo $r['anh']; ?>"
                     alt="<?php echo htmlspecialchars($r['manga_name']); ?>" loading="lazy">
                <div class="related-card-body">
                    <p><?php echo htmlspecialchars($r['manga_name']); ?></p>
                    <?php if ($r_giam < (float)$r['gia_ban']): ?>
                        <span style="text-decoration:line-through;color:#999;font-size:.8rem;">
                            <?= number_format($r['gia_ban'], 0, ',', '.') ?>₫
                        </span>
                        <strong style="color:#ff4757;"><?= number_format($r_giam, 0, ',', '.') ?>₫</strong>
                    <?php else: ?>
                        <strong><?= number_format($r['gia_ban'], 0, ',', '.') ?>₫</strong>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="cart-toast" id="cart-toast"><i class="fas fa-check"></i> Đã thêm vào giỏ hàng!</div>

<?php require_once '../includes/footer.php'; ?>

<script>
// Giá đã áp dụng giảm giá membership
const GIA_HIEN = <?php echo (int)$gia_hien; ?>;

function changeQty(delta) {
    const input = document.getElementById('qty');
    const max = parseInt(input.max) || 99;
    let val = parseInt(input.value) + delta;
    input.value = Math.max(1, Math.min(max, val));
}

function addToCart() {
    const qty = parseInt(document.getElementById('qty')?.value || 1);
    const id_spmanga = <?php echo (int)$product['id_spmanga']; ?>;
    fetch('cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        // Truyền gia_hien thay vì gia_ban để giỏ hàng phản ánh giá đúng
        body: JSON.stringify({ action: 'add', id_spmanga: id_spmanga, qty: qty, gia_override: GIA_HIEN })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            const toast = document.getElementById('cart-toast');
            if (toast) { toast.classList.add('show'); setTimeout(() => toast.classList.remove('show'), 3000); }
        } else { alert(data.message); }
    })
    .catch(() => alert("Có lỗi xảy ra!"));
}

function buyNow() {
    const qty = parseInt(document.getElementById('qty')?.value || 1);
    const id_spmanga = <?php echo (int)$product['id_spmanga']; ?>;
    fetch('cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', id_spmanga: id_spmanga, qty: qty, gia_override: GIA_HIEN })
    })
    .then(r => r.json())
    .then(data => { if (data.status === 'success') window.location.href = 'checkout.php'; else alert(data.message); })
    .catch(() => alert("Có lỗi xảy ra!"));
}
</script>