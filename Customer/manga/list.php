<?php
// manga/list.php — Trang danh sách & tìm kiếm truyện (KẾT NỐI CSDL)

require_once '../../include/db.php';

$db      = new Database();
$search  = trim($_GET['search'] ?? '');
$genre   = trim($_GET['genre'] ?? '');   // tên thể loại
$sort    = trim($_GET['sort']   ?? 'new');
$page    = max(1, (int)($_GET['page']   ?? 1));
$per_page = 12;

// -------------------------------------------------------
// 1. LẤY DANH SÁCH THỂ LOẠI (cho dropdown & tag bar)
// -------------------------------------------------------
$db->query("SELECT id_theloaimanga, ten_theloai FROM theloai WHERE status = 1 ORDER BY ten_theloai ASC");
$genres = $db->resultSet();   // mảng [['id_theloaimanga'=>…,'ten_theloai'=>…], …]

// -------------------------------------------------------
// 2. ĐẾM TỔNG SỐ TRUYỆN (dùng để phân trang)
// -------------------------------------------------------
$count_sql = "
    SELECT COUNT(DISTINCT m.id_manga) AS total
    FROM   manga m
    LEFT JOIN manga_theloai mt ON mt.id_manga = m.id_manga
    LEFT JOIN theloai        tl ON tl.id_theloaimanga = mt.id_theloaimanga
    WHERE  1=1
";
$params_count = [];

if ($search !== '') {
    $count_sql .= " AND (m.manga_name LIKE :search OR m.tacgia LIKE :search)";
    $params_count[':search'] = '%' . $search . '%';
}
if ($genre !== '') {
    $count_sql .= " AND tl.ten_theloai = :genre";
    $params_count[':genre'] = $genre;
}

$db->query($count_sql);
foreach ($params_count as $k => $v) $db->bind($k, $v);
$total       = (int)($db->single()['total'] ?? 0);
$total_pages = $total > 0 ? ceil($total / $per_page) : 1;
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per_page;

// -------------------------------------------------------
// 3. LẤY DANH SÁCH TRUYỆN (có phân trang, lọc, tìm kiếm)
// -------------------------------------------------------
$order_clause = match($sort) {
    'hot'  => 'ORDER BY tong_view DESC',
    'name' => 'ORDER BY m.manga_name ASC',
    default => 'ORDER BY m.create_day DESC',   // 'new'
};

$list_sql = "
    SELECT
        m.id_manga,
        m.manga_name,
        m.slug,
        m.tacgia,
        m.anh,
        m.sratus,
        m.create_day,
        GROUP_CONCAT(DISTINCT tl.ten_theloai ORDER BY tl.ten_theloai SEPARATOR ', ') AS the_loai,
        COALESCE(SUM(ld.so_luot_doc), 0) AS tong_view,
        COUNT(DISTINCT c.id_chap)        AS so_chuong
    FROM   manga m
    LEFT JOIN manga_theloai mt ON mt.id_manga        = m.id_manga
    LEFT JOIN theloai        tl ON tl.id_theloaimanga = mt.id_theloaimanga
    LEFT JOIN luot_doc       ld ON ld.id_manga        = m.id_manga
    LEFT JOIN chap            c ON c.id_manga          = m.id_manga
    WHERE  1=1
";
$params_list = [];

if ($search !== '') {
    $list_sql .= " AND (m.manga_name LIKE :search OR m.tacgia LIKE :search)";
    $params_list[':search'] = '%' . $search . '%';
}
if ($genre !== '') {
    $list_sql .= " AND tl.ten_theloai = :genre";
    $params_list[':genre'] = $genre;
}

$list_sql .= " GROUP BY m.id_manga {$order_clause} LIMIT :limit OFFSET :offset";

$db->query($list_sql);
foreach ($params_list as $k => $v) $db->bind($k, $v);
$db->bind(':limit',  $per_page);
$db->bind(':offset', $offset);
$mangas = $db->resultSet();

// -------------------------------------------------------
// HEADER
// -------------------------------------------------------
$base_url     = '../';
$page_title   = 'Danh sách truyện - Truyện Hay';
$current_page = 'list';
require_once '../includes/header.php';
?>

<main class="main-content">

    <!-- TIÊU ĐỀ + BỘ LỌC -->
    <section class="list-filter-section">
        <div class="filter-header">
            <h2><i class="fas fa-list"></i> Danh sách truyện</h2>
        </div>

        <form class="filter-bar" method="GET" action="list.php">
            <div class="filter-search">
                <input type="text" name="search" placeholder="Tìm tên truyện, tác giả..."
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
            <select name="genre" onchange="this.form.submit()">
                <option value="">-- Thể loại --</option>
                <?php foreach ($genres as $g): ?>
                <option value="<?php echo htmlspecialchars($g['ten_theloai']); ?>"
                    <?php echo $genre === $g['ten_theloai'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($g['ten_theloai']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <select name="sort" onchange="this.form.submit()">
                <option value="new"  <?php echo $sort === 'new'  ? 'selected' : ''; ?>>Mới cập nhật</option>
                <option value="hot"  <?php echo $sort === 'hot'  ? 'selected' : ''; ?>>Xem nhiều nhất</option>
                <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
            </select>
        </form>

        <!-- Tags thể loại nhanh -->
        <div class="genre-tags-bar">
            <a href="list.php?sort=<?php echo $sort; ?>"
               class="genre-tag-btn <?php echo $genre === '' ? 'active' : ''; ?>">Tất cả</a>
            <?php foreach ($genres as $g): ?>
            <a href="list.php?genre=<?php echo urlencode($g['ten_theloai']); ?>&sort=<?php echo $sort; ?>"
               class="genre-tag-btn <?php echo $genre === $g['ten_theloai'] ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($g['ten_theloai']); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- KẾT QUẢ -->
    <section class="manga-section">
        <?php if ($search !== '' || $genre !== ''): ?>
        <p class="search-result-info">
            <i class="fas fa-info-circle"></i>
            Tìm thấy <strong><?php echo $total; ?></strong> kết quả
            <?php if ($search !== ''): ?> cho "<strong><?php echo htmlspecialchars($search); ?></strong>"<?php endif; ?>
            <?php if ($genre  !== ''): ?> thể loại "<strong><?php echo htmlspecialchars($genre); ?></strong>"<?php endif; ?>
        </p>
        <?php endif; ?>

        <?php if (empty($mangas)): ?>
        <div style="text-align:center; padding:60px 0; color:#888;">
            <i class="fas fa-book-open" style="font-size:3rem; margin-bottom:15px; display:block;"></i>
            <p>Không tìm thấy truyện nào phù hợp.</p>
            <a href="list.php" style="color:var(--primary);">Xem tất cả truyện</a>
        </div>
        <?php else: ?>
        <div class="manga-grid">
            <?php foreach ($mangas as $m): ?>
            <a href="../truyen/<?php echo htmlspecialchars($m['slug']); ?>"
               class="manga-card" style="text-decoration:none;color:inherit;">
                <img src="<?php echo htmlspecialchars($m['anh']); ?>"
                     alt="<?php echo htmlspecialchars($m['manga_name']); ?>"
                     loading="lazy"
                     onerror="this.src='../assets/img/no-cover.jpg'">
                <span class="manga-status-label <?php echo $m['sratus'] ? 'ongoing' : 'completed'; ?>">
                    <?php echo $m['sratus'] ? 'Đang ra' : 'Hoàn thành'; ?>
                </span>
                <div class="manga-info">
                    <h3><?php echo htmlspecialchars($m['manga_name']); ?></h3>
                    <p class="genres">Thể loại: <?php echo htmlspecialchars($m['the_loai'] ?? 'Chưa phân loại'); ?></p>
                    <div class="manga-meta">
                        <span><i class="fas fa-eye"></i> <?php echo number_format((int)$m['tong_view']); ?></span>
                        <span><i class="fas fa-book"></i> <?php echo (int)$m['so_chuong']; ?> chương</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- PHÂN TRANG -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort; ?>"
               class="page-btn"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>

            <?php
            // Hiển thị tối đa 5 trang xung quanh trang hiện tại
            $start_p = max(1, $page - 2);
            $end_p   = min($total_pages, $page + 2);
            if ($start_p > 1) echo '<span class="page-btn" style="pointer-events:none">…</span>';
            for ($p = $start_p; $p <= $end_p; $p++):
            ?>
            <a href="?page=<?php echo $p; ?>&search=<?php echo urlencode($search); ?>&genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort; ?>"
               class="page-btn <?php echo $p === $page ? 'active' : ''; ?>">
                <?php echo $p; ?>
            </a>
            <?php endfor;
            if ($end_p < $total_pages) echo '<span class="page-btn" style="pointer-events:none">…</span>';
            ?>

            <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort; ?>"
               class="page-btn"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>