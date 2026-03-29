<?php
require_once '../include/db.php';
$db = new Database();

// -------------------------------------------------------
// TRUYỆN MỚI CẬP NHẬT (8 truyện mới nhất)
// -------------------------------------------------------
$db->query("
    SELECT
        m.id_manga, m.manga_name, m.slug, m.anh, m.status,
        GROUP_CONCAT(DISTINCT tl.ten_theloai ORDER BY tl.ten_theloai SEPARATOR ', ') AS the_loai,
        COALESCE(SUM(ld.so_luot_doc), 0) AS tong_view,
        COUNT(DISTINCT c.id_chap) AS so_chuong
    FROM   manga m
    LEFT JOIN manga_theloai mt ON mt.id_manga         = m.id_manga
    LEFT JOIN theloai        tl ON tl.id_theloaimanga  = mt.id_theloaimanga
    LEFT JOIN luot_doc       ld ON ld.id_manga         = m.id_manga
    LEFT JOIN chap            c ON c.id_manga           = m.id_manga
    GROUP BY m.id_manga
    ORDER BY m.create_day DESC
    LIMIT 8
");
$new_mangas = $db->resultSet();

$page_title   = 'Truyện Hay - Đọc truyện tranh online';
$current_page = 'home';
$base_url     = './';
require_once 'includes/header.php';
?>

<!-- PHẦN BODY (Nội dung chính) -->
<main class="main-content">
    <section class="manga-section">
        <div class="section-header">
            <h2><i class="fas fa-book-open"></i> Truyện mới cập nhật</h2>
        </div>
        <div class="manga-grid">
            <?php if (empty($new_mangas)): ?>
            <p style="color:#888; padding:20px;">Chưa có truyện nào.</p>
            <?php else: ?>
            <?php foreach ($new_mangas as $m): ?>
            <a href="truyen/<?php echo htmlspecialchars($m['slug']); ?>"
               class="manga-card" style="text-decoration:none;color:inherit;">
                <img src="<?php echo htmlspecialchars($m['anh']); ?>"
                     alt="<?php echo htmlspecialchars($m['manga_name']); ?>"
                     loading="lazy"
                     onerror="this.src='assets/img/no-cover.jpg'">
                <span class="manga-status-label <?php echo $m['status'] ? 'ongoing' : 'completed'; ?>">
                    <?php echo $m['status'] ? 'Đang ra' : 'Hoàn thành'; ?>
                </span>
                <div class="manga-info">
                    <h3><?php echo htmlspecialchars($m['manga_name']); ?></h3>
                    <p class="genres">
                        Thể loại: <?php echo htmlspecialchars($m['the_loai'] ?? 'Chưa phân loại'); ?>
                    </p>
                    <div class="manga-meta">
                        <span><i class="fas fa-eye"></i> <?php echo number_format((int)$m['tong_view']); ?></span>
                        <span><i class="fas fa-book"></i> <?php echo (int)$m['so_chuong']; ?> chương</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>