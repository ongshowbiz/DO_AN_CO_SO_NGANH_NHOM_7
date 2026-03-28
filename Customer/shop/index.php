<?php
session_start();
require_once '../../include/db.php'; 
$db = new Database();

$search   = trim($_GET['search'] ?? '');
$sort     = trim($_GET['sort'] ?? 'new');
$type     = trim($_GET['type'] ?? '');       
$cat_id   = trim($_GET['cat_id'] ?? '');    
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset   = ($page - 1) * $per_page;

$db->query("SELECT id_theloaimanga, ten_theloai FROM theloai"); 
$categories = $db->resultSet();

// 1. TẠO CÂU LỆNH SQL CHÍNH
$sql = "SELECT sp.id_spmanga, sp.gia_ban, sp.nha_xuat_ban, sp.so_luong_kho,
               m.id_manga, m.manga_name, m.slug, m.anh, m.tacgia,
               'physical' AS type
        FROM sanpham_manga sp
        JOIN manga m ON sp.id_manga = m.id_manga
        WHERE 1=1";

// 2. TẠO CÂU LỆNH ĐẾM TỔNG SỐ 
$sql_count = "SELECT COUNT(*) as total 
              FROM sanpham_manga sp 
              JOIN manga m ON sp.id_manga = m.id_manga 
              WHERE 1=1";

// ĐIỀU KIỆN TÌM KIẾM
if ($search !== '') {
    $sql       .= " AND m.manga_name LIKE :search";
    $sql_count .= " AND m.manga_name LIKE :search_count";
}

// ĐIỀU KIỆN THỂ LOẠI 
if ($cat_id !== '') {
    $sql       .= " AND m.id_theloaimanga = :cat_id";
    $sql_count .= " AND m.id_theloaimanga = :cat_count";
}

// SẮP XẾP
if ($sort === 'price_asc') {
    $sql .= " ORDER BY sp.gia_ban ASC";
} elseif ($sort === 'price_desc') {
    $sql .= " ORDER BY sp.gia_ban DESC";
} else {
    $sql .= " ORDER BY sp.id_spmanga DESC"; 
}

// PHÂN TRANG (LIMIT & OFFSET)
$sql .= " LIMIT $per_page OFFSET $offset";

// THỰC THI SQL CHÍNH
$db->query($sql);
if ($search !== '') {
    $db->bind(':search', "%$search%");
}
if ($cat_id !== '') {
    $db->bind(':cat_id', $cat_id);
}
$products = $db->resultSet();

// THỰC THI SQL ĐẾM SỐ LƯỢNG
$db->query($sql_count);
if ($search !== '') {
    $db->bind(':search_count', "%$search%");
}
if ($cat_id !== '') {
    $db->bind(':cat_count', $cat_id);
}
$total_row = $db->single();
$total_products = $total_row['total']; 
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
    <div class="category-tags" style="margin-bottom: 25px; display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
        <a href="?cat_id=&type=<?php echo $type; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>" 
        class="tag <?php echo $cat_id === '' ? 'active' : ''; ?>"
        style="padding: 8px 16px; border-radius: 20px; border: 1px solid #ddd; text-decoration: none; color: #333; font-size: 14px; transition: 0.2s;">
        Tất cả Thể Loại
        </a>

        <?php foreach ($categories as $cat): ?>
            <a href="?cat_id=<?php echo $cat['id_theloaimanga']; ?>&type=<?php echo $type; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>" 
            class="tag <?php echo $cat_id == $cat['id_theloaimanga'] ? 'active' : ''; ?>"
            style="padding: 8px 16px; border-radius: 20px; border: 1px solid #ddd; text-decoration: none; color: #333; font-size: 14px; transition: 0.2s;">
            <?php echo htmlspecialchars($cat['ten_theloai']); ?>
            </a>
        <?php endforeach; ?>
    </div>
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



