<?php
// manga/detail.php — Trang chi tiết truyện (KẾT NỐI CSDL)
// URL: /truyen/{slug}  →  manga/detail.php?slug={slug}  (qua .htaccess)

require_once '../../include/db.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) { header('Location: ../index.php'); exit; }

$db = new Database();

// -------------------------------------------------------
// 1. LẤY THÔNG TIN TRUYỆN
// -------------------------------------------------------
$db->query("
    SELECT
        m.id_manga, m.manga_name, m.slug, m.tacgia, m.mota,
        m.anh, m.status, m.create_day,
        COALESCE(SUM(ld.so_luot_doc), 0) AS tong_view,
        GROUP_CONCAT(DISTINCT tl.ten_theloai ORDER BY tl.ten_theloai SEPARATOR ', ') AS the_loai
    FROM   manga m
    LEFT JOIN manga_theloai mt ON mt.id_manga        = m.id_manga
    LEFT JOIN theloai        tl ON tl.id_theloaimanga = mt.id_theloaimanga
    LEFT JOIN luot_doc       ld ON ld.id_manga        = m.id_manga
    WHERE  m.slug = :slug
    GROUP BY m.id_manga
");
$db->bind(':slug', $slug);
$manga = $db->single();

if (!$manga) { header('Location: ../index.php'); exit; }

// -------------------------------------------------------
// 2. XỬ LÝ GỬI BÌNH LUẬN (POST)
// -------------------------------------------------------
$comment_error   = '';
$comment_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action']) && $_POST['action'] === 'comment'
    && isset($_SESSION['user_id'])
) {
    $noi_dung = trim($_POST['noi_dung'] ?? '');
    if (empty($noi_dung)) {
        $comment_error = 'Nội dung bình luận không được để trống!';
    } elseif (mb_strlen($noi_dung) > 1000) {
        $comment_error = 'Bình luận tối đa 1000 ký tự!';
    } else {
        try {
            $db->query("
                INSERT INTO comment (id_taikhoan, id_manga, noi_dung, ngay_tao)
                VALUES (:uid, :mid, :nd, NOW())
            ");
            $db->bind(':uid', (int)$_SESSION['user_id']);
            $db->bind(':mid', (int)$manga['id_manga']);
            $db->bind(':nd',  $noi_dung);
            $db->execute();
            $comment_success = true;
            // Redirect để tránh re-submit khi F5
            header("Location: ../truyen/{$slug}#comments");
            exit;
        } catch (Exception $e) {
            $comment_error = 'Có lỗi xảy ra, vui lòng thử lại!';
            error_log('comment insert error: ' . $e->getMessage());
        }
    }
}

// -------------------------------------------------------
// 3. LẤY DANH SÁCH CHƯƠNG
// -------------------------------------------------------
$db->query("
    SELECT id_chap, so_chuong, tieu_de_chuong, ngay_dang
    FROM   chap
    WHERE  id_manga = :mid
    ORDER BY so_chuong DESC
");
$db->bind(':mid', (int)$manga['id_manga']);
$chapters = $db->resultSet();

// -------------------------------------------------------
// 4. LẤY BÌNH LUẬN (20 bình luận mới nhất)
// -------------------------------------------------------
$db->query("
    SELECT c.noi_dung, c.ngay_tao,
           tk.TENTAIKHOAN, tk.ANH
    FROM   comment c
    JOIN   taikhoan tk ON tk.ID_TAIKHOAN = c.id_taikhoan
    WHERE  c.id_manga = :mid
    ORDER BY c.ngay_tao DESC
    LIMIT  20
");
$db->bind(':mid', (int)$manga['id_manga']);
$comments = $db->resultSet();

// -------------------------------------------------------
// 5. BẢNG XẾP HẠNG SIDEBAR (top 10 theo lượt xem tuần)
// -------------------------------------------------------
$db->query("
    SELECT m.manga_name, m.slug,
           COALESCE(SUM(ld.so_luot_doc), 0) AS tong_view
    FROM   manga m
    LEFT JOIN luot_doc ld ON ld.id_manga = m.id_manga
                          AND ld.ngay >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY m.id_manga
    ORDER BY tong_view DESC
    LIMIT 10
");
$top_manga = $db->resultSet();

// -------------------------------------------------------
// HEADER
// -------------------------------------------------------
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
                    <img src="<?php echo htmlspecialchars($manga['anh']); ?>"
                         alt="<?php echo htmlspecialchars($manga['manga_name']); ?>"
                         onerror="this.src='../assets/img/no-cover.jpg'">
                    <div class="rating-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                </div>
                <div class="manga-detail-info">
                    <h1 class="manga-title"><?php echo htmlspecialchars($manga['manga_name']); ?></h1>
                    <div class="manga-stats">
                        <span class="stat-item">
                            <i class="fas fa-eye"></i> Lượt xem:
                            <strong><?php echo number_format((int)$manga['tong_view']); ?></strong>
                        </span>
                        <span class="status-badge <?php echo $manga['status'] ? 'ongoing' : 'completed'; ?>">
                            <i class="fas fa-circle"></i>
                            <?php echo $manga['status'] ? 'Đang tiến hành' : 'Hoàn thành'; ?>
                        </span>
                    </div>

                    <?php if (!empty($manga['mota'])): ?>
                    <div class="manga-description">
                        <p id="desc-text" class="desc-collapsed">
                            <?php echo nl2br(htmlspecialchars($manga['mota'])); ?>
                        </p>
                        <button class="btn-toggle-desc" id="btn-toggle-desc">
                            Xem thêm <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="manga-meta-info">
                        <div class="meta-row">
                            <span class="meta-label"><i class="fas fa-user-edit"></i> Tác giả:</span>
                            <span><?php echo htmlspecialchars($manga['tacgia'] ?? 'Đang cập nhật'); ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label"><i class="fas fa-tags"></i> Thể loại:</span>
                            <span>
                                <?php foreach (explode(', ', $manga['the_loai'] ?? '') as $g):
                                      $g = trim($g); if (!$g) continue; ?>
                                <a href="../manga/list.php?genre=<?php echo urlencode($g); ?>"
                                   class="genre-tag"><?php echo htmlspecialchars($g); ?></a>
                                <?php endforeach; ?>
                            </span>
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
                        <?php if (!empty($chapters)): ?>
                        <?php $first_chap = end($chapters);   // chapter nhỏ nhất (sort DESC nên cuối mảng)
                              $last_chap  = $chapters[0];     // chapter mới nhất ?>
                        <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo $first_chap['so_chuong']; ?>"
                           class="btn-read btn-primary"><i class="fas fa-book-reader"></i> Đọc từ đầu</a>
                        <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo $last_chap['so_chuong']; ?>"
                           class="btn-read btn-secondary"><i class="fas fa-forward"></i> Đọc chương mới</a>
                        <?php endif; ?>
                        <button class="btn-read btn-follow" id="btn-follow">
                            <i class="far fa-heart"></i> Theo dõi
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- TÌM CHƯƠNG -->
        <?php if (!empty($chapters)): ?>
        <section class="card-box" style="padding:20px 25px;margin-bottom:20px;">
            <div class="chapter-search-bar">
                <input type="number" id="chap-input" placeholder="Nhập số chương..." min="1"
                       max="<?php echo $chapters[0]['so_chuong']; ?>">
                <button onclick="goToChap('<?php echo $slug; ?>')"><i class="fas fa-search"></i></button>
                <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo end($chapters)['so_chuong']; ?>"
                   class="btn-read-action"><i class="fas fa-play"></i> Đọc từ đầu</a>
                <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo $chapters[0]['so_chuong']; ?>"
                   class="btn-read-action btn-new"><i class="fas fa-fast-forward"></i> Đọc chương mới</a>
            </div>
        </section>
        <?php endif; ?>

        <!-- DANH SÁCH CHƯƠNG -->
        <section class="card-box" style="margin-bottom:20px;">
            <div class="section-header">
                <h2><i class="fas fa-list-ol"></i> Danh sách chương</h2>
                <span class="badge-count"><?php echo count($chapters); ?> chương</span>
            </div>
            <?php if (empty($chapters)): ?>
            <p style="padding:20px; color:#888; text-align:center;">Chưa có chương nào được đăng.</p>
            <?php else: ?>
            <div class="chapter-list">
                <?php foreach ($chapters as $chap): ?>
                <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo $chap['so_chuong']; ?>"
                   class="chapter-item">
                    <span class="chap-num"><i class="fas fa-book"></i> Chương <?php echo $chap['so_chuong']; ?></span>
                    <span class="chap-title"><?php echo htmlspecialchars($chap['tieu_de_chuong']); ?></span>
                    <span class="chap-date"><?php echo date('d/m/Y', strtotime($chap['ngay_dang'])); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>

        <!-- BÌNH LUẬN -->
        <section class="card-box" id="comments" style="margin-bottom:20px;">
            <div class="section-header">
                <h2><i class="fas fa-comments"></i> Bình luận
                    <span class="badge-count"><?php echo count($comments); ?></span>
                </h2>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
            <!-- FORM GỬI BÌNH LUẬN -->
            <?php if ($comment_error): ?>
            <div style="color:#e74c3c; background:#2c1a1a; border:1px solid #e74c3c;
                        padding:10px 15px; border-radius:8px; margin-bottom:15px; font-size:14px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($comment_error); ?>
            </div>
            <?php endif; ?>
            <form class="comment-form" method="POST"
                  action="../truyen/<?php echo $slug; ?>#comments">
                <input type="hidden" name="action" value="comment">
                <div class="comment-avatar"><i class="fas fa-user-circle"></i></div>
                <div class="comment-input-wrap">
                    <textarea name="noi_dung" id="comment-text"
                              placeholder="Viết bình luận..." rows="3" maxlength="1000"
                              required><?php echo htmlspecialchars($_POST['noi_dung'] ?? ''); ?></textarea>
                    <button type="submit" class="btn-submit-comment">
                        <i class="fas fa-paper-plane"></i> Gửi bình luận
                    </button>
                </div>
            </form>
            <?php else: ?>
            <div class="comment-login-prompt">
                <i class="fas fa-lock"></i>
                <p>Vui lòng <a href="../auth/login.php">đăng nhập</a> để bình luận.</p>
            </div>
            <?php endif; ?>

            <!-- DANH SÁCH BÌNH LUẬN -->
            <div class="comment-list">
                <?php if (empty($comments)): ?>
                <p style="text-align:center; color:#888; padding:20px;">
                    Chưa có bình luận nào. Hãy là người đầu tiên!
                </p>
                <?php else: ?>
                <?php foreach ($comments as $cmt): ?>
                <div class="comment-item">
                    <div class="comment-avatar">
                        <?php if (!empty($cmt['ANH'])): ?>
                        <img src="<?php echo htmlspecialchars($cmt['ANH']); ?>"
                             alt="avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="comment-body">
                        <div class="comment-header">
                            <strong><?php echo htmlspecialchars($cmt['TENTAIKHOAN']); ?></strong>
                            <span class="comment-time">
                                <?php echo date('d/m/Y H:i', strtotime($cmt['ngay_tao'])); ?>
                            </span>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($cmt['noi_dung'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div><!-- /.detail-main -->

    <!-- SIDEBAR -->
    <aside class="detail-sidebar">
        <div class="card-box">
            <div class="section-header" style="margin-bottom:15px;">
                <h2 style="font-size:1.1rem;"><i class="fas fa-trophy"></i> Bảng xếp hạng</h2>
            </div>
            <div class="rank-tabs">
                <button class="rank-tab active" data-period="week">Tuần</button>
                <button class="rank-tab" data-period="month">Tháng</button>
                <button class="rank-tab" data-period="all">Tất cả</button>
            </div>
            <div class="rank-list" id="rank-list">
                <?php foreach ($top_manga as $idx => $item): ?>
                <a href="../truyen/<?php echo htmlspecialchars($item['slug']); ?>" class="rank-item">
                    <span class="rank-number rank-<?php echo $idx + 1; ?>"><?php echo $idx + 1; ?></span>
                    <div class="rank-info">
                        <span class="rank-name"><?php echo htmlspecialchars($item['manga_name']); ?></span>
                        <span class="rank-views">
                            <i class="fas fa-eye"></i> <?php echo number_format((int)$item['tong_view']); ?> lượt
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php if (empty($top_manga)): ?>
                <p style="padding:15px; color:#888; text-align:center; font-size:13px;">Chưa có dữ liệu.</p>
                <?php endif; ?>
            </div>
        </div>
    </aside>

</div>
</main>

<?php require_once '../includes/footer.php'; ?>

<script>
// Toggle mô tả
const descText  = document.getElementById('desc-text');
const btnToggle = document.getElementById('btn-toggle-desc');
if (btnToggle) {
    btnToggle.addEventListener('click', () => {
        descText.classList.toggle('desc-collapsed');
        btnToggle.innerHTML = descText.classList.contains('desc-collapsed')
            ? 'Xem thêm <i class="fas fa-chevron-down"></i>'
            : 'Thu gọn <i class="fas fa-chevron-up"></i>';
    });
}

// Nút theo dõi (có thể kết nối API sau)
const btnFollow = document.getElementById('btn-follow');
if (btnFollow) {
    btnFollow.addEventListener('click', () => {
        btnFollow.classList.toggle('followed');
        btnFollow.innerHTML = btnFollow.classList.contains('followed')
            ? '<i class="fas fa-heart"></i> Đang theo dõi'
            : '<i class="far fa-heart"></i> Theo dõi';
    });
}

// Chuyển trang chương
function goToChap(slug) {
    const n = parseInt(document.getElementById('chap-input').value);
    if (n > 0) window.location.href = `../doc/${slug}/chuong-${n}`;
}
document.getElementById('chap-input')?.addEventListener('keypress', e => {
    if (e.key === 'Enter') goToChap('<?php echo addslashes($slug); ?>');
});

// Bảng xếp hạng — load động qua AJAX khi click tab
document.querySelectorAll('.rank-tab').forEach(tab => {
    tab.addEventListener('click', async () => {
        document.querySelectorAll('.rank-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const period = tab.dataset.period;
        const rankList = document.getElementById('rank-list');
        rankList.innerHTML = '<p style="text-align:center;padding:20px;color:#888;"><i class="fas fa-spinner fa-spin"></i></p>';

        try {
            const res  = await fetch(`../manga/rank_api.php?period=${period}`);
            const data = await res.json();
            if (!data.length) {
                rankList.innerHTML = '<p style="padding:15px;color:#888;text-align:center;font-size:13px;">Chưa có dữ liệu.</p>';
                return;
            }
            rankList.innerHTML = data.map((item, idx) => `
                <a href="../truyen/${item.slug}" class="rank-item">
                    <span class="rank-number rank-${idx+1}">${idx+1}</span>
                    <div class="rank-info">
                        <span class="rank-name">${item.manga_name}</span>
                        <span class="rank-views"><i class="fas fa-eye"></i> ${Number(item.tong_view).toLocaleString('vi-VN')} lượt</span>
                    </div>
                </a>`).join('');
        } catch (e) {
            rankList.innerHTML = '<p style="padding:15px;color:#e74c3c;text-align:center;font-size:13px;">Lỗi tải dữ liệu.</p>';
        }
    });
});
</script>