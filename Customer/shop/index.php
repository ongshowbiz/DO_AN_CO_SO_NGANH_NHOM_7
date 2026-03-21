<?php
// shop/index.php — Trang danh sách sản phẩm bán truyện

// require_once '../config/db.php';
$search  = trim($_GET['search'] ?? '');
$sort    = trim($_GET['sort'] ?? 'new');
$type    = trim($_GET['type'] ?? '');  // 'physical' | 'digital' | ''
$page    = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;

// --- DỮ LIỆU MẪU DEMO ---
// Thực tế: JOIN sanpham_manga + manga + manga_theloai + theloai
$products = [];
$types = ['physical', 'digital'];
for ($i = 1; $i <= 12; $i++) {
    $ptype = $types[$i % 2];
    $products[] = [
        'id_spmanga'   => $i,
        'id_manga'     => $i,
        'manga_name'   => 'Tên Truyện Mẫu ' . $i,
        'slug'         => 'ten-truyen-mau-' . $i,
        'anh'          => "https://picsum.photos/seed/{$i}/200/280",
        'tacgia'       => 'Tác giả ' . $i,
        'nha_xuat_ban' => 'NXB Kim Đồng',
        'gia_ban'      => rand(45, 150) * 1000,
        'so_luong_kho' => rand(0, 50),
        'the_loai'     => 'Hành Động',
        'type'         => $ptype, // physical | digital
        'luot_xem'     => rand(1000, 99000),
    ];
}
$total       = 36;
$total_pages = ceil($total / $per_page);

$base_url     = '../';
$page_title   = 'Shop Truyện - Truyện Hay';
$current_page = 'shop';
$extra_css = ['../shop.css'];
require_once '../includes/header.php';
?>


<!-- HERO -->
<div class="shop-hero">
    <h1><i class="fas fa-store"></i> Shop <span>Truyện Hay</span></h1>
    <p>Mua truyện giấy chính hãng hoặc mở khoá đọc truyện kỹ thuật số</p>
</div>

<!-- FILTER -->
<div class="shop-filter">
    <form method="GET" action="index.php" style="display:contents;">
        <div class="shop-filter-search">
            <input type="text" name="search" placeholder="Tìm tên truyện, tác giả, NXB..."
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
        <select name="sort" onchange="this.form.submit()">
            <option value="new"   <?php echo $sort==='new'?'selected':''; ?>>Mới nhất</option>
            <option value="price_asc"  <?php echo $sort==='price_asc'?'selected':''; ?>>Giá tăng dần</option>
            <option value="price_desc" <?php echo $sort==='price_desc'?'selected':''; ?>>Giá giảm dần</option>
            <option value="hot"   <?php echo $sort==='hot'?'selected':''; ?>>Bán chạy</option>
        </select>
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
    </form>

    <div class="type-tabs">
        <a href="index.php?sort=<?php echo $sort; ?>" class="type-tab <?php echo $type===''?'active':''; ?>">
            <i class="fas fa-th"></i> Tất cả
        </a>
        <a href="index.php?type=physical&sort=<?php echo $sort; ?>" class="type-tab <?php echo $type==='physical'?'active':''; ?>">
            <i class="fas fa-book"></i> Truyện giấy
        </a>
        <a href="index.php?type=digital&sort=<?php echo $sort; ?>" class="type-tab <?php echo $type==='digital'?'active':''; ?>">
            <i class="fas fa-tablet-alt"></i> Kỹ thuật số
        </a>
    </div>
</div>

<!-- PRODUCTS -->
<div class="shop-content">

    <?php if ($search): ?>
    <p style="color:#aaa; margin-bottom:20px; font-size:14px;">
        <i class="fas fa-info-circle"></i>
        Kết quả cho "<strong style="color:#e0e0e0;"><?php echo htmlspecialchars($search); ?></strong>"
        — <?php echo $total; ?> sản phẩm
    </p>
    <?php endif; ?>

    <div class="shop-grid">
        <?php foreach ($products as $p): ?>
        <?php $outOfStock = ($p['type'] === 'physical' && $p['so_luong_kho'] <= 0); ?>
        <div class="product-card" style="cursor:pointer;" onclick="window.location='product.php?id=<?php echo $p['id_spmanga']; ?>'">
            <div class="product-card-img">
                <img src="<?php echo $p['anh']; ?>" alt="<?php echo htmlspecialchars($p['manga_name']); ?>" loading="lazy">

                <!-- Badge loại -->
                <span class="badge-type <?php echo $p['type']; ?>">
                    <?php echo $p['type']==='physical' ? '<i class="fas fa-book"></i> Giấy' : '<i class="fas fa-tablet-alt"></i> KTS'; ?>
                </span>

                <?php if ($outOfStock): ?>
                <div class="badge-outstock">HẾT HÀNG</div>
                <?php endif; ?>
            </div>

            <div class="product-card-body">
                <div class="product-name"><?php echo htmlspecialchars($p['manga_name']); ?></div>
                <div class="product-author"><i class="fas fa-user-edit"></i> <?php echo htmlspecialchars($p['tacgia']); ?></div>
                <div class="product-publisher"><i class="fas fa-building"></i> <?php echo htmlspecialchars($p['nha_xuat_ban']); ?></div>
                <div class="product-price"><?php echo number_format($p['gia_ban'], 0, ',', '.'); ?>₫</div>
                <?php if ($p['type'] === 'physical'): ?>
                <div class="product-stock <?php echo $p['so_luong_kho'] < 10 && $p['so_luong_kho'] > 0 ? 'low' : ''; ?>">
                    <?php if ($p['so_luong_kho'] <= 0): ?>
                        <i class="fas fa-times-circle"></i> Hết hàng
                    <?php elseif ($p['so_luong_kho'] < 10): ?>
                        <i class="fas fa-exclamation-circle"></i> Còn <?php echo $p['so_luong_kho']; ?> cuốn
                    <?php else: ?>
                        <i class="fas fa-check-circle" style="color:#2ecc71"></i> Còn hàng
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="product-stock" style="color:#3498db;">
                    <i class="fas fa-infinity"></i> Không giới hạn
                </div>
                <?php endif; ?>
            </div>

            <?php if (!$outOfStock): ?>
            <button class="btn-add-cart"
                    onclick="event.stopPropagation(); addToCart(<?php echo $p['id_spmanga']; ?>, '<?php echo htmlspecialchars($p['manga_name'], ENT_QUOTES); ?>', <?php echo $p['gia_ban']; ?>)"
                    title="Thêm vào giỏ">
                <i class="fas fa-cart-plus"></i>
            </button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- PHÂN TRANG -->
    <?php if ($total_pages > 1): ?>
    <div class="shop-pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&type=<?php echo $type; ?>">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php endif; ?>
        <?php for ($p2 = 1; $p2 <= $total_pages; $p2++): ?>
        <a href="?page=<?php echo $p2; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&type=<?php echo $type; ?>"
           class="<?php echo $p2===$page?'active':''; ?>">
            <?php echo $p2; ?>
        </a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&type=<?php echo $type; ?>">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Toast thông báo -->
<div class="cart-toast" id="cart-toast"><i class="fas fa-check"></i> Đã thêm vào giỏ hàng!</div>

<?php require_once '../includes/footer.php'; ?>

<script>
// Giỏ hàng lưu vào localStorage
function getCart() {
    return JSON.parse(localStorage.getItem('truyen_hay_cart') || '[]');
}
function saveCart(cart) {
    localStorage.setItem('truyen_hay_cart', JSON.stringify(cart));
}

function addToCart(id, name, price) {
    const cart = getCart();
    const idx = cart.findIndex(i => i.id === id);
    if (idx >= 0) {
        cart[idx].qty++;
    } else {
        cart.push({ id, name, price, qty: 1 });
    }
    saveCart(cart);
    updateCartCount();
    showToast();
}

function updateCartCount() {
    const cart = getCart();
    const total = cart.reduce((s, i) => s + i.qty, 0);
    const badge = document.getElementById('cart-count-badge');
    if (badge) {
        badge.textContent = total;
        badge.style.display = total > 0 ? 'flex' : 'none';
    }
}

function showToast() {
    const toast = document.getElementById('cart-toast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
}

updateCartCount();
</script>