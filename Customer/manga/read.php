<?php

function render_chap_nav(string $slug, int $cur, ?int $prev, ?int $next, array $all, string $base): string {
    $doc  = '/DO_AN_CO_SO_NGANH_NHOM_7/Customer/doc/';
    $html = '<div class="chap-nav">';

    if ($prev !== null) {
        $html .= "<a href='{$doc}{$slug}/chuong-{$prev}' class='ctrl-btn'>
                      <i class='fas fa-chevron-left'></i> Chương trước</a>";
    } else {
        $html .= "<button class='ctrl-btn disabled' disabled>
                      <i class='fas fa-chevron-left'></i> Chương trước</button>";
    }

    $html .= "<select class='chap-select'
                      onchange=\"window.location.href='{$doc}{$slug}/chuong-'+this.value\">";
    foreach ($all as $c) {
        $sel   = $c['so_chuong'] == $cur ? 'selected' : '';
        $title = htmlspecialchars($c['tieu_de_chuong']);
        $html .= "<option value='{$c['so_chuong']}' {$sel}>Chương {$c['so_chuong']}: {$title}</option>";
    }
    $html .= '</select>';

    if ($next !== null) {
        $html .= "<a href='{$doc}{$slug}/chuong-{$next}' class='ctrl-btn'>
                      Chương sau <i class='fas fa-chevron-right'></i></a>";
    } else {
        $html .= "<button class='ctrl-btn disabled' disabled>
                      Chương sau <i class='fas fa-chevron-right'></i></button>";
    }

    $html .= '</div>';
    return $html;
}

if (session_status() === PHP_SESSION_NONE) session_start();

require_once '../../include/db.php';
require_once '../../include/membership.php'; 

$slug     = trim($_GET['slug'] ?? '');
$chap_num = max(1, (int)($_GET['chap'] ?? 1));
if (empty($slug)) { header('Location: ../index.php'); exit; }

$db = new Database();

// Lấy thông tin truyện
$db->query("SELECT id_manga, manga_name, slug, la_tra_phi FROM manga WHERE slug = :slug LIMIT 1");
$db->bind(':slug', $slug);
$manga = $db->single();
if (!$manga) { header('Location: ../index.php'); exit; }

$id_manga    = (int)$manga['id_manga'];
// la_tra_phi = 1 nghĩa là truyện trả phí — nếu cột chưa có thì mặc định 0
$la_tra_phi  = (bool)($manga['la_tra_phi'] ?? 0);

// Lấy quyền membership
$perms = MembershipHelper::get($_SESSION['user_id'] ?? 0);

// KIỂM TRA QUYỀN ĐỌC TRUYỆN TRẢ PHÍ
if ($la_tra_phi && !$perms['doc_tra_phi']) {
    $base_url   = '../../';
    $page_title = htmlspecialchars($manga['manga_name']) . ' - Truyện Hay';
    require_once '../includes/header.php';
    ?>
    <div style="max-width:520px;margin:80px auto;text-align:center;padding:40px 30px;
                background:#fff;border-radius:20px;box-shadow:0 8px 30px rgba(0,0,0,.1);">
        <div style="font-size:3rem;margin-bottom:16px;">🔒</div>
        <h2 style="font-size:1.4rem;font-weight:900;color:#2f3542;margin-bottom:10px;">
            Truyện dành cho thành viên trả phí
        </h2>
        <p style="color:#747d8c;margin-bottom:24px;line-height:1.6;">
            <strong><?= htmlspecialchars($manga['manga_name']) ?></strong>
            yêu cầu gói <strong>Basic</strong> trở lên để đọc.
        </p>
        <a href="../membership/index.php"
           style="display:inline-block;background:#ff4757;color:#fff;
                  padding:12px 28px;border-radius:25px;font-weight:700;
                  text-decoration:none;font-size:.95rem;">
            <i class="fas fa-crown"></i> Xem các gói thành viên
        </a>
        <br><br>
        <a href="../truyen/<?= htmlspecialchars($slug) ?>"
           style="color:#747d8c;font-size:.9rem;">
            ← Quay lại trang truyện
        </a>
    </div>
    <?php
    require_once '../includes/footer.php';
    exit;
}

// Lấy thông tin chương
$db->query("SELECT id_chap, so_chuong, tieu_de_chuong, noi_dung, danh_sach_anh, ngay_dang
            FROM chap
            WHERE id_manga = :mid AND so_chuong = :chap
            LIMIT 1");
$db->bind(':mid',  $id_manga);
$db->bind(':chap', $chap_num);
$chapter = $db->single();

if (!$chapter) {
    header("Location: ../truyen/{$slug}");
    exit;
}

// KIỂM TRA QUYỀN ĐỌC TRƯỚC (chương chưa phát hành công khai)
// Giả định: nếu ngay_dang > NOW() thì chương chưa phát hành công khai
$chua_phat_hanh = (strtotime($chapter['ngay_dang']) > time());

if ($chua_phat_hanh && !$perms['doc_truoc']) {
    $ngay_ra        = date('d/m/Y H:i', strtotime($chapter['ngay_dang']));
    $base_url       = '../../';
    $page_title     = 'Chương chưa phát hành - Truyện Hay';
    require_once '../includes/header.php';
    ?>
    <div style="max-width:520px;margin:80px auto;text-align:center;padding:40px 30px;
                background:#fff;border-radius:20px;box-shadow:0 8px 30px rgba(0,0,0,.1);">
        <div style="font-size:3rem;margin-bottom:16px;">⏳</div>
        <h2 style="font-size:1.4rem;font-weight:900;color:#2f3542;margin-bottom:10px;">
            Chương chưa phát hành công khai
        </h2>
        <p style="color:#747d8c;margin-bottom:8px;line-height:1.6;">
            Chương này sẽ mở cho tất cả vào lúc <strong><?= $ngay_ra ?></strong>.
        </p>
        <p style="color:#747d8c;margin-bottom:24px;">
            Nâng cấp gói <strong>Premium</strong> để đọc trước ngay hôm nay!
        </p>
        <a href="../membership/index.php"
           style="display:inline-block;background:#ff4757;color:#fff;
                  padding:12px 28px;border-radius:25px;font-weight:700;
                  text-decoration:none;font-size:.95rem;">
            <i class="fas fa-crown"></i> Nâng cấp ngay
        </a>
        <br><br>
        <a href="../truyen/<?= htmlspecialchars($slug) ?>"
           style="color:#747d8c;font-size:.9rem;">
            ← Quay lại trang truyện
        </a>
    </div>
    <?php
    require_once '../includes/footer.php';
    exit;
}

// Parse danh sách ảnh
$images = [];
if (!empty($chapter['danh_sach_anh'])) {
    $decoded = json_decode($chapter['danh_sach_anh'], true);
    if (is_array($decoded)) $images = $decoded;
}

// Tất cả chương (dropdown)
$db->query("SELECT so_chuong, tieu_de_chuong FROM chap WHERE id_manga = :mid ORDER BY so_chuong ASC");
$db->bind(':mid', $id_manga);
$all_chaps = $db->resultSet();

// ★ Nếu user không có quyền doc_truoc, ẩn chương chưa phát hành khỏi dropdown
if (!$perms['doc_truoc']) {
    $now       = time();
    $all_chaps = array_filter($all_chaps, fn($c) => strtotime($c['ngay_dang'] ?? '2000-01-01') <= $now);
    $all_chaps = array_values($all_chaps);
}

// Xác định chương trước / sau
$prev_chap = null;
$next_chap = null;
foreach ($all_chaps as $c) {
    if ($c['so_chuong'] < $chap_num) $prev_chap = (int)$c['so_chuong'];
    if ($c['so_chuong'] > $chap_num && $next_chap === null) $next_chap = (int)$c['so_chuong'];
}

// Tăng lượt xem
$view_key = "viewed_chap_{$id_manga}_{$chap_num}";
if (empty($_SESSION[$view_key])) {
    try {
        $today = date('Y-m-d');
        $db->query("INSERT INTO luot_doc (id_manga, ngay, so_luot_doc) VALUES (:mid, :today, 1)
                    ON DUPLICATE KEY UPDATE so_luot_doc = so_luot_doc + 1");
        $db->bind(':mid',   $id_manga);
        $db->bind(':today', $today);
        $db->execute();
        $_SESSION[$view_key] = true;
    } catch (Exception $e) {
        error_log('luot_doc error: ' . $e->getMessage());
    }
}

// Lưu tiến độ đọc
if (!empty($_SESSION['user_id'])) {
    try {
        $db->query("INSERT INTO tiendo_doc (id_taikhoan, id_manga, so_chuong, ngay_doc)
                    VALUES (:uid, :mid, :chap, NOW())
                    ON DUPLICATE KEY UPDATE so_chuong = VALUES(so_chuong), ngay_doc = NOW()");
        $db->bind(':uid',  (int)$_SESSION['user_id']);
        $db->bind(':mid',  $id_manga);
        $db->bind(':chap', $chap_num);
        $db->execute();
    } catch (Exception $e) {
        error_log('tiendo_doc error: ' . $e->getMessage());
    }
}

$base_url         = '../../';
$page_title       = 'Chương ' . $chap_num . ' - ' . htmlspecialchars($manga['manga_name']) . ' - Truyện Hay';
$current_page     = '';
$extra_body_class = 'read-page-body';
require_once '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="../index.php"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <a href="<?php echo $base_url; ?>truyen/<?php echo htmlspecialchars($slug); ?>">
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
            <?php echo render_chap_nav($slug, $chap_num, $prev_chap, $next_chap, $all_chaps, $base_url); ?>
            <button class="ctrl-btn" id="btn-font-size">
                <i class="fas fa-text-height"></i> Cỡ chữ
            </button>
        </div>
    </div>

    <!-- TIÊU ĐỀ CHƯƠNG -->
    <div class="chapter-title-bar">
        <h1><?php echo htmlspecialchars($manga['manga_name']); ?></h1>
        <h2>Chương <?php echo $chap_num; ?>: <?php echo htmlspecialchars($chapter['tieu_de_chuong']); ?></h2>
        <?php if ($la_tra_phi): ?>
        <!-- Badge truyện trả phí (chỉ hiện khi user đã có quyền) -->
        <span style="display:inline-flex;align-items:center;gap:5px;
                     background:#fff3f4;border:1px solid #ffb3b8;
                     color:#ff4757;border-radius:20px;
                     padding:3px 12px;font-size:.8rem;font-weight:700;margin-top:6px;">
            <i class="fas fa-crown"></i> Nội dung dành riêng cho thành viên
        </span>
        <?php endif; ?>
    </div>

    <!-- NỘI DUNG CHƯƠNG -->
    <div class="chapter-content" id="chapter-content">
        <?php if (!empty($images)): ?>
            <?php foreach ($images as $idx => $img_path): ?>
            <img src="<?php echo htmlspecialchars($img_path); ?>"
                 alt="Trang <?php echo $idx + 1; ?>"
                 loading="lazy" class="chapter-image"
                 onerror="this.style.display='none'">
            <?php endforeach; ?>
        <?php elseif (!empty($chapter['noi_dung'])): ?>
            <div class="chapter-text">
                <?php echo nl2br(htmlspecialchars($chapter['noi_dung'])); ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:60px 20px;color:#888;">
                <i class="fas fa-image" style="font-size:3rem;margin-bottom:15px;display:block;"></i>
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
            <?php echo render_chap_nav($slug, $chap_num, $prev_chap, $next_chap, $all_chaps, $base_url); ?>
        </div>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>

<script>
const btnFont = document.getElementById('btn-font-size');
const content = document.getElementById('chapter-content');
let fontSize = 18;
if (btnFont) {
    btnFont.addEventListener('click', () => {
        fontSize = fontSize >= 24 ? 14 : fontSize + 2;
        content.style.fontSize = fontSize + 'px';
    });
}

document.addEventListener('keydown', e => {
    <?php if ($prev_chap): ?>
    if (e.key === 'ArrowLeft') window.location.href = '/DO_AN_CO_SO_NGANH_NHOM_7/Customer/doc/<?php echo $slug; ?>/chuong-<?php echo $prev_chap; ?>';
    <?php endif; ?>
    <?php if ($next_chap): ?>
    if (e.key === 'ArrowRight') window.location.href = '/DO_AN_CO_SO_NGANH_NHOM_7/Customer/doc/<?php echo $slug; ?>/chuong-<?php echo $next_chap; ?>';
    <?php endif; ?>
});
</script>