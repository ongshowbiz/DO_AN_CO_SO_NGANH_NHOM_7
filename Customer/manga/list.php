<?php
// manga/list.php — Trang danh sách & tìm kiếm truyện

// require_once '../config/db.php';
$search  = trim($_GET['search'] ?? '');
$genre   = trim($_GET['genre'] ?? '');
$sort    = trim($_GET['sort'] ?? 'new');
$page    = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;

// --- DỮ LIỆU MẪU DEMO ---
$genres = ['Hành Động','Tình Cảm','Hài Hước','Kinh Dị','Phiêu Lưu','Trinh Thám','Xuyên Không','Học Đường'];
$mangas = [];
for($i=1; $i<=12; $i++) {
    $mangas[] = [
        'id_manga'   => $i,
        'manga_name' => 'Tên Truyện Mẫu ' . $i,
        'slug'       => 'ten-truyen-mau-' . $i,
        'tacgia'     => 'Tác giả ' . $i,
       'anh' => "https://picsum.photos/seed/$i/200/280",
        'the_loai'   => $genres[array_rand($genres)] . ', ' . $genres[array_rand($genres)],
        'tong_view'  => rand(1000, 50000),
        'so_chuong'  => rand(5, 100),
        'sratus'     => rand(0, 1),
    ];
}
$total = 36; // tổng số truyện (giả lập)
$total_pages = ceil($total / $per_page);

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
            <!-- Tìm kiếm -->
            <div class="filter-search">
                <input type="text" name="search" placeholder="Tìm tên truyện, tác giả..."
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>

            <!-- Lọc thể loại -->
            <select name="genre" onchange="this.form.submit()">
                <option value="">-- Thể loại --</option>
                <?php foreach($genres as $g): ?>
                <option value="<?php echo urlencode($g); ?>"
                    <?php echo $genre===$g?'selected':''; ?>>
                    <?php echo htmlspecialchars($g); ?>
                </option>
                <?php endforeach; ?>
            </select>

            <!-- Sắp xếp -->
            <select name="sort" onchange="this.form.submit()">
                <option value="new"  <?php echo $sort==='new'?'selected':''; ?>>Mới cập nhật</option>
                <option value="hot"  <?php echo $sort==='hot'?'selected':''; ?>>Xem nhiều nhất</option>
                <option value="name" <?php echo $sort==='name'?'selected':''; ?>>Tên A-Z</option>
            </select>
        </form>

        <!-- Tags thể loại nhanh -->
        <div class="genre-tags-bar">
            <a href="list.php" class="genre-tag-btn <?php echo empty($genre)?'active':''; ?>">Tất cả</a>
            <?php foreach($genres as $g): ?>
            <a href="list.php?genre=<?php echo urlencode($g); ?>"
               class="genre-tag-btn <?php echo $genre===$g?'active':''; ?>">
                <?php echo htmlspecialchars($g); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- KẾT QUẢ -->
    <section class="manga-section">
        <?php if($search || $genre): ?>
        <p class="search-result-info">
            <i class="fas fa-info-circle"></i>
            Tìm thấy <strong><?php echo $total; ?></strong> kết quả
            <?php if($search): ?> cho "<strong><?php echo htmlspecialchars($search); ?></strong>"<?php endif; ?>
            <?php if($genre): ?> thể loại "<strong><?php echo htmlspecialchars($genre); ?></strong>"<?php endif; ?>
        </p>
        <?php endif; ?>

        <div class="manga-grid">
            <?php foreach($mangas as $m): ?>
            <a href="../truyen/<?php echo $m['slug']; ?>" class="manga-card" style="text-decoration:none;color:inherit;">
                <img src="<?php echo $m['anh']; ?>" alt="<?php echo htmlspecialchars($m['manga_name']); ?>">
                <span class="manga-status-label <?php echo $m['sratus']?'ongoing':'completed'; ?>">
                    <?php echo $m['sratus']?'Đang ra':'Hoàn thành'; ?>
                </span>
                <div class="manga-info">
                    <h3><?php echo htmlspecialchars($m['manga_name']); ?></h3>
                    <p class="genres">Thể loại: <?php echo htmlspecialchars($m['the_loai']); ?></p>
                    <div class="manga-meta">
                        <span><i class="fas fa-eye"></i> <?php echo number_format($m['tong_view']); ?></span>
                        <span><i class="fas fa-book"></i> <?php echo $m['so_chuong']; ?> chương</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- PHÂN TRANG -->
        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
            <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort; ?>"
               class="page-btn"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>

            <?php for($p=1; $p<=$total_pages; $p++): ?>
            <a href="?page=<?php echo $p; ?>&search=<?php echo urlencode($search); ?>&genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort; ?>"
               class="page-btn <?php echo $p===$page?'active':''; ?>">
                <?php echo $p; ?>
            </a>
            <?php endfor; ?>

            <?php if($page < $total_pages): ?>
            <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort; ?>"
               class="page-btn"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>