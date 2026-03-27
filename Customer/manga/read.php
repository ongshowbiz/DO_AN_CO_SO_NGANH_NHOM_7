<?php
// manga/read.php — Trang đọc chương (KẾT NỐI CSDL + TỰ ĐỘNG TĂNG LƯỢT XEM)
// URL: /doc/{slug}/chuong-{so_chuong}  →  manga/read.php?slug={slug}&chap={so_chuong}

require_once '../../include/db.php';

$slug     = trim($_GET['slug'] ?? '');
$chap_num = max(1, (int)($_GET['chap'] ?? 1));
if (empty($slug)) { header('Location: ../index.php'); exit; }

$db = new Database();

// -------------------------------------------------------
// 1. LẤY THÔNG TIN TRUYỆN
// -------------------------------------------------------
$db->query("SELECT id_manga, manga_name, slug FROM manga WHERE slug = :slug LIMIT 1");
$db->bind(':slug', $slug);
$manga = $db->single();
if (!$manga) { header('Location: ../index.php'); exit; }

$id_manga = (int)$manga['id_manga'];

// -------------------------------------------------------
// 2. LẤY THÔNG TIN CHƯƠNG HIỆN TẠI
// -------------------------------------------------------
$db->query("
    SELECT id_chap, so_chuong, tieu_de_chuong, noi_dung, danh_sach_anh
    FROM   chap
    WHERE  id_manga = :mid AND so_chuong = :chap
    LIMIT  1
");
$db->bind(':mid',  $id_manga);
$db->bind(':chap', $chap_num);
$chapter = $db->single();

// Nếu không có chương → về trang chi tiết
if (!$chapter) {
    header("Location: ../truyen/{$slug}");
    exit;
}

// -------------------------------------------------------
// 3. PARSE DANH SÁCH ẢNH (lưu dạng JSON trong CSDL)
//    Ví dụ: ["uploads/chap/1/trang1.jpg", "uploads/chap/1/trang2.jpg"]
// -------------------------------------------------------
$images = [];
if (!empty($chapter['danh_sach_anh'])) {
    $decoded = json_decode($chapter['danh_sach_anh'], true);
    if (is_array($decoded)) {
        $images = $decoded;
    }
}

// -------------------------------------------------------
// 4. LẤY TẤT CẢ CHƯƠNG (dùng cho dropdown chọn chương)
// -------------------------------------------------------
$db->query("
    SELECT so_chuong, tieu_de_chuong
    FROM   chap
    WHERE  id_manga = :mid
    ORDER BY so_chuong ASC
");
$db->bind(':mid', $id_manga);
$all_chaps = $db->resultSet();

// -------------------------------------------------------
// 5. XÁC ĐỊNH CHƯƠNG TRƯỚC / SAU
// -------------------------------------------------------
$prev_chap = null;
$next_chap = null;
foreach ($all_chaps as $c) {
    if ($c['so_chuong'] < $chap_num) $prev_chap = (int)$c['so_chuong'];
    if ($c['so_chuong'] > $chap_num && $next_chap === null) $next_chap = (int)$c['so_chuong'];
}

// -------------------------------------------------------
// 6. TỰ ĐỘNG TĂNG LƯỢT XEM (upsert theo ngày)
//    Mỗi phiên (session) chỉ đếm 1 lần cho mỗi chương
// -------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) session_start();
$view_key = "viewed_chap_{$id_manga}_{$chap_num}";

if (empty($_SESSION[$view_key])) {
    try {
        $today = date('Y-m-d');
        $db->query("
            INSERT INTO luot_doc (id_manga, ngay, so_luot_doc)
            VALUES (:mid, :today, 1)
            ON DUPLICATE KEY UPDATE so_luot_doc = so_luot_doc + 1
        ");
        $db->bind(':mid',   $id_manga);
        $db->bind(':today', $today);
        $db->execute();
        $_SESSION[$view_key] = true;   // Đánh dấu đã đếm trong phiên này
    } catch (Exception $e) {
        error_log('luot_doc error: ' . $e->getMessage());
    }
}

// -------------------------------------------------------
// HEADER
// -------------------------------------------------------
$base_url     = '../';
$page_title   = 'Chương ' . $chap_num . ' - ' . htmlspecialchars($manga['manga_name']) . ' - Truyện Hay';
$current_page = '';
require_once '../includes/header.php';
?>

<!-- BREADCRUMB -->
<div class="breadcrumb">
    <a href="../index.php"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <a href="../truyen/<?php echo htmlspecialchars($slug); ?>">
        <?php echo htmlspecialchars($manga['manga_name']); ?>
    </a>
    <i class="fas fa-chevron-right"></i>
    <span>Chương <?php echo $chap_num; ?></span>
</div>

<main class="main-content read-page">

    <!-- THANH ĐIỀU KHIỂN TRÊN -->
    <div class="read-controls">
        <div class="read-controls-inner">
            <a href="../truyen/<?php echo htmlspecialchars($slug); ?>" class="ctrl-btn">
                <i class="fas fa-list"></i> Danh sách chương
            </a>
            <?php echo render_chap_nav($slug, $chap_num, $prev_chap, $next_chap, $all_chaps); ?>
            <button class="ctrl-btn" id="btn-font-size">
                <i class="fas fa-text-height"></i> Cỡ chữ
            </button>
        </div>
    </div>

    <!-- TIÊU ĐỀ CHƯƠNG -->
    <div class="chapter-title-bar">
        <h1><?php echo htmlspecialchars($manga['manga_name']); ?></h1>
        <h2>Chương <?php echo $chap_num; ?>: <?php echo htmlspecialchars($chapter['tieu_de_chuong']); ?></h2>
    </div>

    <!-- NỘI DUNG CHƯƠNG -->
    <div class="chapter-content" id="chapter-content">
        <?php if (!empty($images)): ?>
            <?php foreach ($images as $idx => $img_path): ?>
            <img src="<?php echo htmlspecialchars($img_path); ?>"
                 alt="Trang <?php echo $idx + 1; ?>"
                 loading="lazy"
                 class="chapter-image"
                 onerror="this.style.display='none'">
            <?php endforeach; ?>

        <?php elseif (!empty($chapter['noi_dung'])): ?>
            <!-- Truyện chữ (light novel) -->
            <div class="chapter-text">
                <?php echo nl2br(htmlspecialchars($chapter['noi_dung'])); ?>
            </div>

        <?php else: ?>
            <div style="text-align:center; padding:60px 20px; color:#888;">
                <i class="fas fa-image" style="font-size:3rem; margin-bottom:15px; display:block;"></i>
                <p>Nội dung chương này chưa được cập nhật.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- THANH ĐIỀU KHIỂN DƯỚI -->
    <div class="read-controls read-controls-bottom">
        <div class="read-controls-inner">
            <a href="../truyen/<?php echo htmlspecialchars($slug); ?>" class="ctrl-btn">
                <i class="fas fa-list"></i> Danh sách chương
            </a>
            <?php echo render_chap_nav($slug, $chap_num, $prev_chap, $next_chap, $all_chaps); ?>
        </div>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>

<script>
// Điều chỉnh cỡ chữ (dành cho truyện chữ)
const btnFont = document.getElementById('btn-font-size');
const content = document.getElementById('chapter-content');
let fontSize = 18;
if (btnFont) {
    btnFont.addEventListener('click', () => {
        fontSize = fontSize >= 24 ? 14 : fontSize + 2;
        content.style.fontSize = fontSize + 'px';
    });
}

// Phím tắt ← → chuyển chương
document.addEventListener('keydown', e => {
    <?php if ($prev_chap): ?>
    if (e.key === 'ArrowLeft')  window.location.href = '../doc/<?php echo $slug; ?>/chuong-<?php echo $prev_chap; ?>';
    <?php endif; ?>
    <?php if ($next_chap): ?>
    if (e.key === 'ArrowRight') window.location.href = '../doc/<?php echo $slug; ?>/chuong-<?php echo $next_chap; ?>';
    <?php endif; ?>
});
</script>

<?php
// -------------------------------------------------------
// HELPER: render thanh điều hướng chương (dùng 2 lần)
// -------------------------------------------------------
function render_chap_nav(string $slug, int $cur, ?int $prev, ?int $next, array $all): string {
    $html = '<div class="chap-nav">';

    // Nút chương trước
    if ($prev !== null) {
        $html .= "<a href='../doc/{$slug}/chuong-{$prev}' class='ctrl-btn'>
                      <i class='fas fa-chevron-left'></i> Chương trước</a>";
    } else {
        $html .= "<button class='ctrl-btn disabled' disabled>
                      <i class='fas fa-chevron-left'></i> Chương trước</button>";
    }

    // Dropdown chọn chương
    $html .= "<select class='chap-select'
                      onchange=\"window.location.href='../doc/{$slug}/chuong-'+this.value\">";
    foreach ($all as $c) {
        $sel   = $c['so_chuong'] == $cur ? 'selected' : '';
        $title = htmlspecialchars($c['tieu_de_chuong']);
        $html .= "<option value='{$c['so_chuong']}' {$sel}>Chương {$c['so_chuong']}: {$title}</option>";
    }
    $html .= '</select>';

    // Nút chương sau
    if ($next !== null) {
        $html .= "<a href='../doc/{$slug}/chuong-{$next}' class='ctrl-btn'>
                      Chương sau <i class='fas fa-chevron-right'></i></a>";
    } else {
        $html .= "<button class='ctrl-btn disabled' disabled>
                      Chương sau <i class='fas fa-chevron-right'></i></button>";
    }

    $html .= '</div>';
    return $html;
}
?>