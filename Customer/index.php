<?php
require_once '../include/db.php';
$db = new Database();

// Hàm chuyển datetime → "vừa rồi / X phút / X giờ / X ngày trước"
function time_ago($datetime) {
    if (!$datetime) return 'Chưa có chương';
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->getTimestamp() - $ago->getTimestamp();

    if ($diff < 60)           return 'Vừa cập nhật';
    if ($diff < 3600)         return floor($diff / 60) . ' phút trước';
    if ($diff < 86400)        return floor($diff / 3600) . ' giờ trước';
    if ($diff < 86400 * 7)    return floor($diff / 86400) . ' ngày trước';
    if ($diff < 86400 * 30)   return floor($diff / (86400 * 7)) . ' tuần trước';
    if ($diff < 86400 * 365)  return floor($diff / (86400 * 30)) . ' tháng trước';
    return floor($diff / (86400 * 365)) . ' năm trước';
}

// TRUYỆN MỚI CẬP NHẬT (8 truyện mới nhất)
$db->query("
    SELECT m.id_manga, m.manga_name, m.slug, m.anh, m.status,
        GROUP_CONCAT(DISTINCT tl.ten_theloai ORDER BY tl.ten_theloai SEPARATOR ', ') AS the_loai,
        COALESCE(SUM(ld.so_luot_doc), 0) AS tong_view,
        COUNT(DISTINCT c.id_chap) AS so_chuong,
        MAX(c.ngay_dang) AS cap_nhat_moi_nhat
    FROM manga m
    LEFT JOIN manga_theloai mt ON mt.id_manga = m.id_manga
    LEFT JOIN theloai tl ON tl.id_theloaimanga = mt.id_theloaimanga
    LEFT JOIN luot_doc ld ON ld.id_manga = m.id_manga
    LEFT JOIN chap c ON c.id_manga = m.id_manga
    GROUP BY m.id_manga
    ORDER BY cap_nhat_moi_nhat DESC, m.create_day DESC
    LIMIT 8
");
$new_mangas = $db->resultSet();

// TRUYỆN ĐANG THỊNH HÀNH (top 8 theo lượt xem 7 ngày gần nhất)
$db->query("
    SELECT m.id_manga, m.manga_name, m.slug, m.anh, m.status,
        GROUP_CONCAT(DISTINCT tl.ten_theloai ORDER BY tl.ten_theloai SEPARATOR ', ') AS the_loai,
        COALESCE(SUM(ld.so_luot_doc), 0) AS tong_view,
        COUNT(DISTINCT c.id_chap) AS so_chuong,
        MAX(c.ngay_dang) AS cap_nhat_moi_nhat
    FROM manga m
    LEFT JOIN manga_theloai mt ON mt.id_manga = m.id_manga
    LEFT JOIN theloai tl ON tl.id_theloaimanga = mt.id_theloaimanga
    LEFT JOIN luot_doc ld ON ld.id_manga = m.id_manga AND ld.ngay >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    LEFT JOIN chap c ON c.id_manga = m.id_manga
    GROUP BY m.id_manga
    ORDER BY tong_view DESC
    LIMIT 8
");
$hot_mangas = $db->resultSet();

// THỂ LOẠI NỔI BẬT (top 6 thể loại nhiều truyện nhất)
$db->query("
    SELECT tl.id_theloaimanga, tl.ten_theloai,
        COUNT(DISTINCT mt.id_manga) AS so_truyen
    FROM theloai tl
    LEFT JOIN manga_theloai mt ON mt.id_theloaimanga = tl.id_theloaimanga
    WHERE tl.status = 1
    GROUP BY tl.id_theloaimanga
    ORDER BY so_truyen DESC
    LIMIT 6
");
$top_genres = $db->resultSet();

// TOP 5 BẢNG XẾP HẠNG (tổng lượt xem all-time)
$db->query("
    SELECT m.manga_name, m.slug, m.anh,
        COALESCE(SUM(ld.so_luot_doc), 0) AS tong_view
    FROM manga m
    LEFT JOIN luot_doc ld ON ld.id_manga = m.id_manga
    GROUP BY m.id_manga
    ORDER BY tong_view DESC
    LIMIT 5
");
$top_mangas = $db->resultSet();

$page_title   = 'Truyện Hay - Đọc truyện tranh online';
$current_page = 'home';
$base_url     = './';
require_once 'includes/header.php';
?>

<main class="main-content" style="padding:20px 0;">
<div style="max-width:1200px; margin:0 auto; padding:0 16px;">
<div class="home-wrapper">

    <!-- ===== CỘT CHÍNH ===== -->
    <div class="home-main">

        <!-- TRUYỆN MỚI CẬP NHẬT -->
        <section class="home-section">
            <div class="section-header">
                <h2><i class="fas fa-clock"></i> Truyện mới cập nhật</h2>
                <a href="manga/list.php" class="section-view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="manga-grid">
                <?php if (empty($new_mangas)): ?>
                <p style="color:#888;">Chưa có truyện nào.</p>
                <?php else: ?>
                <?php foreach ($new_mangas as $m): ?>
                <a href="truyen/<?php echo htmlspecialchars($m['slug']); ?>" class="manga-card">
                    <img src="<?php echo htmlspecialchars($m['anh']); ?>"
                         alt="<?php echo htmlspecialchars($m['manga_name']); ?>"
                         loading="lazy" onerror="this.src='assets/img/no-cover.jpg'">
                    <span class="manga-status-label <?php echo $m['status'] ? 'ongoing' : 'completed'; ?>">
                        <?php echo $m['status'] ? 'Đang ra' : 'Xong'; ?>
                    </span>
                    <div class="manga-info">
                        <h3><?php echo htmlspecialchars($m['manga_name']); ?></h3>
                        <p class="genres"><?php echo htmlspecialchars($m['the_loai'] ?? 'Chưa phân loại'); ?></p>
                        <div class="manga-meta">
                            <span><i class="fas fa-eye"></i> <?php echo number_format((int)$m['tong_view']); ?></span>
                            <span><i class="fas fa-book"></i> <?php echo (int)$m['so_chuong']; ?> Chương</span>
                        </div>
                        <div class="manga-updated">
                            <i class="fas fa-clock"></i>
                            <?php echo time_ago($m['cap_nhat_moi_nhat']); ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- TRUYỆN ĐANG THỊNH HÀNH -->
        <section class="home-section">
            <div class="section-header">
                <h2><i class="fas fa-fire"></i> Đang thịnh hành</h2>
                <a href="manga/list.php?sort=hot" class="section-view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="manga-grid">
                <?php if (empty($hot_mangas)): ?>
                <p style="color:#888;">Chưa có dữ liệu.</p>
                <?php else: ?>
                <?php foreach ($hot_mangas as $i => $m): ?>
                <a href="truyen/<?php echo htmlspecialchars($m['slug']); ?>" class="manga-card">
                    <img src="<?php echo htmlspecialchars($m['anh']); ?>"
                         alt="<?php echo htmlspecialchars($m['manga_name']); ?>"
                         loading="lazy" onerror="this.src='assets/img/no-cover.jpg'">
                    <?php if ($i < 3): ?>
                    <span class="hot-badge">🔥 HOT</span>
                    <?php endif; ?>
                    <span class="manga-status-label <?php echo $m['status'] ? 'ongoing' : 'completed'; ?>">
                        <?php echo $m['status'] ? 'Đang ra' : 'Xong'; ?>
                    </span>
                    <div class="manga-info">
                        <h3><?php echo htmlspecialchars($m['manga_name']); ?></h3>
                        <p class="genres">Thể loại: <?php echo htmlspecialchars($m['the_loai'] ?? 'Chưa phân loại'); ?></p>
                        <div class="manga-meta">
                            <span><i class="fas fa-eye"></i> <?php echo number_format((int)$m['tong_view']); ?></span>
                            <span><i class="fas fa-book"></i> <?php echo (int)$m['so_chuong']; ?> Chương</span>
                        </div>
                        <div class="manga-updated">
                            <i class="fas fa-clock"></i>
                            <?php echo time_ago($m['cap_nhat_moi_nhat']); ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- THỂ LOẠI NỔI BẬT -->
        <section class="home-section">
            <div class="section-header">
                <h2><i class="fas fa-tags"></i> Thể loại nổi bật</h2>
                <a href="manga/list.php" class="section-view-all">Tất cả thể loại <i class="fas fa-arrow-right"></i></a>
            </div>
            <?php
            $genre_icons = [
                'Hành Động'  => 'fas fa-fist-raised',
                'Tình Cảm'   => 'fas fa-heart',
                'Hài Hước'   => 'fas fa-laugh',
                'Phiêu Lưu'  => 'fas fa-compass',
                'Kinh Dị'    => 'fas fa-skull',
                'Thể Thao'   => 'fas fa-running',
                'Học Đường'  => 'fas fa-graduation-cap',
                'Viễn Tưởng' => 'fas fa-rocket',
            ];
            ?>
            <div class="genre-grid">
                <?php foreach ($top_genres as $g): ?>
                <a href="manga/list.php?genre=<?php echo urlencode($g['ten_theloai']); ?>" class="genre-card">
                    <i class="<?php echo $genre_icons[$g['ten_theloai']] ?? 'fas fa-book'; ?>"></i>
                    <span><?php echo htmlspecialchars($g['ten_theloai']); ?></span>
                    <span class="genre-count"><?php echo (int)$g['so_truyen']; ?> truyện</span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

    </div><!-- /.home-main -->

    <!-- ===== SIDEBAR ===== -->
    <aside class="home-sidebar">

        <!-- BXH TOP 5 -->
        <section class="home-section">
            <div class="section-header">
                <h2><i class="fas fa-trophy"></i> BXH tuần</h2>
            </div>
            <div class="rank-strip">
                <?php if (empty($top_mangas)): ?>
                <p style="color:#888; font-size:0.85rem; text-align:center; padding:10px;">Chưa có dữ liệu.</p>
                <?php else: ?>
                <?php foreach ($top_mangas as $i => $m): ?>
                <a href="truyen/<?php echo htmlspecialchars($m['slug']); ?>" class="rank-strip-item">
                    <span class="rank-num r<?php echo $i + 1; ?>"><?php echo $i + 1; ?></span>
                    <img src="<?php echo htmlspecialchars($m['anh']); ?>"
                         alt="<?php echo htmlspecialchars($m['manga_name']); ?>"
                         onerror="this.src='assets/img/no-cover.jpg'">
                    <div class="rank-strip-info">
                        <strong><?php echo htmlspecialchars($m['manga_name']); ?></strong>
                        <span><i class="fas fa-eye"></i> <?php echo number_format((int)$m['tong_view']); ?> lượt</span>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- ĐỀ XUẤT HÔM NAY -->
        <section class="home-section" style="margin-top:28px;">
            <div class="section-header">
                <h2><i class="fas fa-star"></i> Đề xuất hôm nay</h2>
            </div>
            <div class="rank-strip">
                <?php foreach (array_slice($new_mangas, 0, 4) as $m): ?>
                <a href="truyen/<?php echo htmlspecialchars($m['slug']); ?>" class="rank-strip-item">
                    <img src="<?php echo htmlspecialchars($m['anh']); ?>"
                         alt="<?php echo htmlspecialchars($m['manga_name']); ?>"
                         onerror="this.src='assets/img/no-cover.jpg'"
                         style="width:44px;height:60px;object-fit:cover;border-radius:6px;flex-shrink:0;">
                    <div class="rank-strip-info">
                        <strong><?php echo htmlspecialchars($m['manga_name']); ?></strong>
                        <span><?php echo htmlspecialchars($m['the_loai'] ?? 'Chưa phân loại'); ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

    </aside>

</div><!-- /.home-wrapper -->
</div>
</main>

<?php require_once 'includes/footer.php'; ?>