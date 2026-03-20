<?php
// manga/detail.php — Trang chi tiết truyện
// URL: /truyen/{slug}  →  manga/detail.php?slug={slug}  (qua .htaccess)

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (empty($slug)) { header('Location: ../index.php'); exit; }

// --- KẾT NỐI DB (bỏ comment khi có) ---
// require_once '../config/db.php';
// $stmt = $conn->prepare("SELECT m.*, GROUP_CONCAT(t.ten_theloai SEPARATOR ', ') AS the_loai
//     FROM manga m
//     LEFT JOIN manga_theloai mt ON m.id_manga = mt.id_manga
//     LEFT JOIN theloai t ON mt.id_theloaimanga = t.id_theloaimanga
//     WHERE m.slug = ? GROUP BY m.id_manga");
// $stmt->bind_param('s', $slug); $stmt->execute();
// $manga = $stmt->get_result()->fetch_assoc();
// if (!$manga) { http_response_code(404); exit('Không tìm thấy truyện.'); }
// $chap_stmt = $conn->prepare("SELECT * FROM chap WHERE id_manga=? ORDER BY so_chuong DESC LIMIT 20");
// $chap_stmt->bind_param('i', $manga['id_manga']); $chap_stmt->execute();
// $chapters = $chap_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
// $cmt_stmt = $conn->prepare("SELECT c.*,tk.TENTAIKHOAN,tk.ANH FROM comment c JOIN taikhoan tk ON c.id_taikhoan=tk.ID_TAIKHOAN WHERE c.id_manga=? ORDER BY c.ngay_tao DESC LIMIT 10");
// $cmt_stmt->bind_param('i', $manga['id_manga']); $cmt_stmt->execute();
// $comments = $cmt_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// --- DỮ LIỆU MẪU DEMO ---
$manga = [
    'id_manga'    => 1,
    'manga_name'  => 'Tên Truyện Mẫu',
    'slug'        => $slug,
    'tacgia'      => 'Tác Giả Mẫu',
    'mota'        => 'Đây là phần mô tả ngắn về bộ truyện. Nội dung hấp dẫn, kịch tính với nhiều tình tiết bất ngờ. Nhân vật chính phải vượt qua vô số thử thách để đạt được mục tiêu của mình. Đây là một bộ truyện không thể bỏ lỡ dành cho những ai yêu thích thể loại hành động và phiêu lưu.',
    'anh'         => 'https://via.placeholder.com/220x310?text=BìaTruyện',
    'sratus'      => 1,
    'the_loai'    => 'Hành Động, Phiêu Lưu, Hài Hước',
    'create_day'  => '2024-01-15',
];
$views = 14144;
$chapters = array_map(fn($i) => [
    'so_chuong'      => $i,
    'tieu_de_chuong' => 'Chương ' . $i . ': Tiêu đề chương mẫu',
    'ngay_dang'      => date('Y-m-d', strtotime("-{$i} days")),
], range(10, 1));
$comments = [
    ['TENTAIKHOAN'=>'User123','ANH'=>'','noi_dung'=>'Truyện hay lắm, mong tác giả ra thêm chương mới sớm!','ngay_tao'=>'2024-01-20 08:30:00'],
    ['TENTAIKHOAN'=>'MangaFan','ANH'=>'','noi_dung'=>'Chapter mới nhất quá đỉnh!','ngay_tao'=>'2024-01-19 20:15:00'],
];
$top_manga = array_map(fn($i) => ['manga_name'=>'Truyện Hot #'.$i,'slug'=>'truyen-hot-'.$i,'tong_view'=>rand(200,500)], range(1,10));

$base_url     = '../';
$page_title   = htmlspecialchars($manga['manga_name']) . ' - Truyện Hay';
$current_page = 'list';
require_once '../includes/header.php';
?>

<!-- BREADCRUMB -->
<div class="breadcrumb">
    <a href="../index.php"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <a href="../manga/list.php">Truyện</a>
    <i class="fas fa-chevron-right"></i>
    <span><?php echo htmlspecialchars($manga['manga_name']); ?></span>
</div>

<main class="main-content detail-page">
<div class="detail-wrapper">

    <!-- CỘT CHÍNH -->
    <div class="detail-main">

        <!-- THÔNG TIN TRUYỆN -->
        <section class="info-section card-box">
            <div class="section-header">
                <h2><i class="fas fa-book-open"></i> Thông tin truyện</h2>
            </div>
            <div class="manga-detail-card">
                <div class="manga-cover">
                    <img src="<?php echo htmlspecialchars($manga['anh']); ?>" alt="<?php echo htmlspecialchars($manga['manga_name']); ?>">
                    <div class="rating-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="rating-text">Đánh giá: <strong>4.5</strong> / 10 từ <strong>10</strong> lượt</p>
                </div>
                <div class="manga-detail-info">
                    <h1 class="manga-title"><?php echo htmlspecialchars($manga['manga_name']); ?></h1>
                    <div class="manga-stats">
                        <span class="stat-item"><i class="fas fa-eye"></i> Lượt xem: <strong><?php echo number_format($views); ?></strong></span>
                        <span class="status-badge <?php echo $manga['sratus']?'ongoing':'completed'; ?>">
                            <i class="fas fa-circle"></i> <?php echo $manga['sratus']?'Đang tiến hành':'Hoàn thành'; ?>
                        </span>
                    </div>
                    <div class="manga-description">
                        <p id="desc-text" class="desc-collapsed"><?php echo nl2br(htmlspecialchars($manga['mota'])); ?></p>
                        <button class="btn-toggle-desc" id="btn-toggle-desc">Xem thêm <i class="fas fa-chevron-down"></i></button>
                    </div>
                    <div class="manga-meta-info">
                        <div class="meta-row">
                            <span class="meta-label"><i class="fas fa-user-edit"></i> Tác giả:</span>
                            <span><?php echo htmlspecialchars($manga['tacgia']); ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label"><i class="fas fa-tags"></i> Thể loại:</span>
                            <span><?php foreach(explode(', ',$manga['the_loai']) as $g): ?>
                                <a href="../manga/list.php?genre=<?php echo urlencode(trim($g)); ?>" class="genre-tag"><?php echo htmlspecialchars(trim($g)); ?></a>
                            <?php endforeach; ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label"><i class="fas fa-calendar"></i> Ngày đăng:</span>
                            <span><?php echo date('d/m/Y', strtotime($manga['create_day'])); ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label"><i class="fas fa-book"></i> Số chương:</span>
                            <span><?php echo count($chapters); ?> chương</span>
                        </div>
                    </div>
                    <div class="manga-actions">
                        <a href="../doc/<?php echo $slug; ?>/chuong-1" class="btn-read btn-primary"><i class="fas fa-book-reader"></i> Đọc từ đầu</a>
                        <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo $chapters[0]['so_chuong']; ?>" class="btn-read btn-secondary"><i class="fas fa-forward"></i> Đọc chương mới</a>
                        <button class="btn-read btn-follow" id="btn-follow"><i class="far fa-heart"></i> Theo dõi</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- TÌM CHƯƠNG -->
        <section class="card-box" style="padding:20px 25px;margin-bottom:20px;">
            <div class="chapter-search-bar">
                <input type="number" id="chap-input" placeholder="Nhập số chương..." min="1">
                <button onclick="goToChap('<?php echo $slug; ?>')"><i class="fas fa-search"></i></button>
                <a href="../doc/<?php echo $slug; ?>/chuong-1" class="btn-read-action"><i class="fas fa-play"></i> Đọc từ đầu</a>
                <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo $chapters[0]['so_chuong']; ?>" class="btn-read-action btn-new"><i class="fas fa-fast-forward"></i> Đọc chương mới</a>
            </div>
        </section>

        <!-- DANH SÁCH CHƯƠNG -->
        <section class="card-box" style="margin-bottom:20px;">
            <div class="section-header">
                <h2><i class="fas fa-list-ol"></i> Danh sách chương</h2>
                <span class="badge-count"><?php echo count($chapters); ?> chương</span>
            </div>
            <div class="chapter-list">
                <?php foreach($chapters as $chap): ?>
                <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo $chap['so_chuong']; ?>" class="chapter-item">
                    <span class="chap-num"><i class="fas fa-book"></i> Chương <?php echo $chap['so_chuong']; ?></span>
                    <span class="chap-title"><?php echo htmlspecialchars($chap['tieu_de_chuong']); ?></span>
                    <span class="chap-date"><?php echo date('d/m/Y', strtotime($chap['ngay_dang'])); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- BÌNH LUẬN -->
        <section class="card-box" style="margin-bottom:20px;">
            <div class="section-header">
                <h2><i class="fas fa-comments"></i> Bình luận</h2>
            </div>
            <?php if(isset($_SESSION['user_id'])): ?>
            <div class="comment-form">
                <div class="comment-avatar"><i class="fas fa-user-circle"></i></div>
                <div class="comment-input-wrap">
                    <textarea id="comment-text" placeholder="Viết bình luận..." rows="3"></textarea>
                    <button class="btn-submit-comment"><i class="fas fa-paper-plane"></i> Gửi bình luận</button>
                </div>
            </div>
            <?php else: ?>
            <div class="comment-login-prompt">
                <i class="fas fa-lock"></i>
                <p>Vui lòng <a href="../auth/login.php">đăng nhập</a> để bình luận.</p>
            </div>
            <?php endif; ?>
            <div class="comment-list">
                <?php foreach($comments as $cmt): ?>
                <div class="comment-item">
                    <div class="comment-avatar"><i class="fas fa-user-circle"></i></div>
                    <div class="comment-body">
                        <div class="comment-header">
                            <strong><?php echo htmlspecialchars($cmt['TENTAIKHOAN']); ?></strong>
                            <span class="comment-time"><?php echo date('d/m/Y H:i', strtotime($cmt['ngay_tao'])); ?></span>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($cmt['noi_dung'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <!-- SIDEBAR -->
    <aside class="detail-sidebar">
        <div class="card-box">
            <div class="section-header" style="margin-bottom:15px;">
                <h2 style="font-size:1.1rem;"><i class="fas fa-trophy"></i> Bảng xếp hạng</h2>
            </div>
            <div class="rank-tabs">
                <button class="rank-tab active">Tuần</button>
                <button class="rank-tab">Tháng</button>
                <button class="rank-tab">Tất cả</button>
            </div>
            <div class="rank-list">
                <?php foreach($top_manga as $idx => $item): ?>
                <a href="../truyen/<?php echo $item['slug']; ?>" class="rank-item">
                    <span class="rank-number rank-<?php echo $idx+1; ?>"><?php echo $idx+1; ?></span>
                    <div class="rank-info">
                        <span class="rank-name"><?php echo htmlspecialchars($item['manga_name']); ?></span>
                        <span class="rank-views"><i class="fas fa-eye"></i> <?php echo number_format($item['tong_view']); ?> lượt</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>

</div>
</main>

<?php require_once '../includes/footer.php'; ?>
<script>
const descText = document.getElementById('desc-text');
const btnToggle = document.getElementById('btn-toggle-desc');
if(btnToggle) btnToggle.addEventListener('click', () => {
    descText.classList.toggle('desc-collapsed');
    btnToggle.innerHTML = descText.classList.contains('desc-collapsed')
        ? 'Xem thêm <i class="fas fa-chevron-down"></i>'
        : 'Thu gọn <i class="fas fa-chevron-up"></i>';
});
const btnFollow = document.getElementById('btn-follow');
if(btnFollow) btnFollow.addEventListener('click', () => {
    btnFollow.classList.toggle('followed');
    btnFollow.innerHTML = btnFollow.classList.contains('followed')
        ? '<i class="fas fa-heart"></i> Đang theo dõi'
        : '<i class="far fa-heart"></i> Theo dõi';
});
document.querySelectorAll('.rank-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.rank-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
    });
});
function goToChap(slug) {
    const n = document.getElementById('chap-input').value;
    if(n > 0) window.location.href = `../doc/${slug}/chuong-${n}`;
}
document.getElementById('chap-input')?.addEventListener('keypress', e => {
    if(e.key==='Enter') goToChap('<?php echo $slug; ?>');
});
</script>