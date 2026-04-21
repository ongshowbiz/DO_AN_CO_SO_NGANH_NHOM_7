<?php
$total = 0;
session_start();
require_once '../../include/db.php';
require_once '../../include/membership.php'; 

$db = new Database();

// Lấy quyền membership của user hiện tại
$perms   = MembershipHelper::get($_SESSION['user_id'] ?? 0);
$disc    = $perms['giam_gia_mua'];   // % giảm giá (0 nếu Free)

$search   = trim($_GET['search'] ?? '');
$sort     = trim($_GET['sort'] ?? 'new');
$type     = trim($_GET['type'] ?? '');
$cat_id   = trim($_GET['cat_id'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset   = ($page - 1) * $per_page;

$db->query("SELECT id_theloaimanga, ten_theloai FROM theloai");
$categories = $db->resultSet();

$sql = "SELECT sp.id_spmanga, sp.gia_ban, sp.nha_xuat_ban, sp.so_luong_kho,
               m.id_manga, m.manga_name, m.slug, m.anh, m.tacgia,
               'physical' AS type
        FROM sanpham_manga sp
        JOIN manga m ON sp.id_manga = m.id_manga
        WHERE 1=1";

$sql_count = "SELECT COUNT(*) as total
              FROM sanpham_manga sp
              JOIN manga m ON sp.id_manga = m.id_manga
              WHERE 1=1";

if ($search !== '') {
    $sql       .= " AND m.manga_name LIKE :search";
    $sql_count .= " AND m.manga_name LIKE :search_count";
}
if ($cat_id !== '') {
    $sql       .= " AND m.id_theloaimanga = :cat_id";
    $sql_count .= " AND m.id_theloaimanga = :cat_count";
}

if ($sort === 'price_asc')       $sql .= " ORDER BY sp.gia_ban ASC";
elseif ($sort === 'price_desc')  $sql .= " ORDER BY sp.gia_ban DESC";
else                             $sql .= " ORDER BY sp.id_spmanga DESC";

$sql .= " LIMIT $per_page OFFSET $offset";

$db->query($sql);
if ($search !== '') $db->bind(':search', "%$search%");
if ($cat_id !== '') $db->bind(':cat_id', $cat_id);
$products = $db->resultSet();

$db->query($sql_count);
if ($search !== '') $db->bind(':search_count', "%$search%");
if ($cat_id !== '') $db->bind(':cat_count', $cat_id);
$total_products = $db->single()['total'];
$total_pages = ceil($total_products / $per_page);

$base_url     = '../';
$page_title   = 'Cửa hàng - Shop Truyện Hay';
$current_page = 'shop';
$extra_css    = ['../shop.css'];
require_once '../includes/header.php';
?>

<div class="shop-hero">
    <h1><i class="fas fa-store"></i> Shop <span>Truyện Hay</span></h1>
    <p>Mua truyện giấy chính hãng hoặc mở khoá đọc truyện kỹ thuật số</p>
</div>

<?php if ($disc > 0): ?>
<!-- BANNER GIẢM GIÁ MEMBERSHIP -->
<div style="background:linear-gradient(135deg,#ff4757,#ff6b6b);color:#fff;text-align:center;padding:10px 20px;font-size:.95rem;font-weight:600;border-radius:8px;margin:0 0 18px;">
    <i class="fas fa-crown"></i>
    Ưu đãi <?= $perms['ten_goi'] ?> — Giảm <?= $disc ?>% toàn bộ sản phẩm!
</div>
<?php endif; ?>

<!-- FILTER -->
<div class="shop-filter">
    <form method="GET" action="index.php" style="display:contents;">
        <div class="shop-filter-search">
            <input type="text" name="search" placeholder="Tìm tên truyện, tác giả, NXB..."
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
        <select name="sort" onchange="this.form.submit()">
            <option value="new"        <?php echo $sort==='new'?'selected':''; ?>>Mới nhất</option>
            <option value="price_asc"  <?php echo $sort==='price_asc'?'selected':''; ?>>Giá tăng dần</option>
            <option value="price_desc" <?php echo $sort==='price_desc'?'selected':''; ?>>Giá giảm dần</option>
            <option value="hot"        <?php echo $sort==='hot'?'selected':''; ?>>Bán chạy</option>
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
        — <?php echo $total_products; ?> sản phẩm
    </p>
    <?php endif; ?>

    <!-- Tags thể loại -->
    <div class="category-tags" style="margin-bottom:25px;display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">
        <a href="?cat_id=&type=<?php echo $type; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>"
           class="tag <?php echo $cat_id===''?'active':''; ?>"
           style="padding:8px 16px;border-radius:20px;border:1px solid #ddd;text-decoration:none;color:#333;font-size:14px;">
            Tất cả Thể Loại
        </a>
        <?php foreach ($categories as $cat): ?>
        <a href="?cat_id=<?php echo $cat['id_theloaimanga']; ?>&type=<?php echo $type; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>"
           class="tag <?php echo $cat_id==$cat['id_theloaimanga']?'active':''; ?>"
           style="padding:8px 16px;border-radius:20px;border:1px solid #ddd;text-decoration:none;color:#333;font-size:14px;">
            <?php echo htmlspecialchars($cat['ten_theloai']); ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="shop-grid">
        <?php foreach ($products as $p):
            $outOfStock  = ($p['type'] === 'physical' && $p['so_luong_kho'] <= 0);
            $gia_goc     = (float)$p['gia_ban'];
            // Tính giá sau giảm membership
            $gia_hien    = MembershipHelper::applyDiscount($gia_goc, $_SESSION['user_id'] ?? 0);
            $co_giam     = ($gia_hien < $gia_goc);
        ?>
        <div class="product-card" style="cursor:pointer;"
             onclick="window.location='product.php?id=<?php echo $p['id_spmanga']; ?>'">
            <div class="product-card-img">
                <img src="<?php echo $p['anh']; ?>"
                     alt="<?php echo htmlspecialchars($p['manga_name']); ?>" loading="lazy">
                <span class="badge-type <?php echo $p['type']; ?>">
                    <?php echo $p['type']==='physical'
                        ? '<i class="fas fa-book"></i> Giấy'
                        : '<i class="fas fa-tablet-alt"></i> KTS'; ?>
                </span>
                <?php if ($outOfStock): ?>
                    <div class="badge-outstock">HẾT HÀNG</div>
                <?php endif; ?>
                <?php if ($co_giam): ?>
                    <!-- Badge % giảm giá -->
                    <div style="position:absolute;top:8px;right:8px;background:#ff4757;color:#fff;
                                font-size:.72rem;font-weight:700;padding:3px 7px;border-radius:20px;">
                        -<?= $disc ?>%
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-card-body">
                <div class="product-name"><?php echo htmlspecialchars($p['manga_name']); ?></div>
                <div class="product-author"><i class="fas fa-user-edit"></i> <?php echo htmlspecialchars($p['tacgia']); ?></div>
                <div class="product-publisher"><i class="fas fa-building"></i> <?php echo htmlspecialchars($p['nha_xuat_ban']); ?></div>

                <!-- Giá: hiển thị giá gốc gạch ngang nếu có giảm giá -->
                <div class="product-price">
                    <?php if ($co_giam): ?>
                        <span style="text-decoration:line-through;color:#999;font-size:.85rem;margin-right:4px;">
                            <?= number_format($gia_goc, 0, ',', '.') ?>₫
                        </span>
                        <span style="color:#ff4757;">
                            <?= number_format($gia_hien, 0, ',', '.') ?>₫
                        </span>
                    <?php else: ?>
                        <?= number_format($gia_goc, 0, ',', '.') ?>₫
                    <?php endif; ?>
                </div>

                <div class="product-stock <?php echo ($p['so_luong_kho'] < 10 && $p['so_luong_kho'] > 0) ? 'low' : ''; ?>">
                    <?php if ($p['so_luong_kho'] <= 0): ?>
                        <i class="fas fa-times-circle"></i> Hết hàng
                    <?php elseif ($p['so_luong_kho'] < 10): ?>
                        <i class="fas fa-exclamation-circle"></i> Còn <?php echo $p['so_luong_kho']; ?> cuốn
                    <?php else: ?>
                        <i class="fas fa-check-circle" style="color:#2ecc71"></i> Còn hàng
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$outOfStock): ?>
            <button class="btn-add-cart"
                    onclick="event.stopPropagation(); addToCart(<?php echo $p['id_spmanga']; ?>, '<?php echo htmlspecialchars($p['manga_name'], ENT_QUOTES); ?>', <?php echo (int)$gia_hien; ?>)"
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

<div class="cart-toast" id="cart-toast"><i class="fas fa-check"></i> Đã thêm vào giỏ hàng!</div>

<?php require_once '../includes/footer.php'; ?>