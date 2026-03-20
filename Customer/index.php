<?php
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
            <?php for($i=1; $i<=8; $i++): ?>
            <a href="truyen/ten-truyen-mau-<?php echo $i; ?>" class="manga-card" style="text-decoration:none;color:inherit;">
                <img src="https://via.placeholder.com/200x280?text=Truyen+<?php echo $i; ?>" alt="Truyện <?php echo $i; ?>">
                <div class="manga-info">
                    <h3>Tên Truyện Mẫu <?php echo $i; ?></h3>
                    <p class="genres">Thể loại: Hành động, Hài Hước</p>
                    <div class="manga-meta">
                        <span><i class="fas fa-eye"></i> 10.5K</span>
                        <span><i class="fas fa-star"></i> 4.5</span>
                    </div>
                </div>
            </a>
            <?php endfor; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>