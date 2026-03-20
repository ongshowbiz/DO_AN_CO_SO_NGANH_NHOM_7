<?php
// manga/read.php — Trang đọc chương
// URL: /doc/{slug}/chuong-{so_chuong}  →  manga/read.php?slug={slug}&chap={so_chuong}

$slug     = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$chap_num = isset($_GET['chap']) ? (int)$_GET['chap'] : 1;
if(empty($slug)) { header('Location: ../index.php'); exit; }

// --- KẾT NỐI DB (bỏ comment khi có) ---
// require_once '../config/db.php';
// $stmt = $conn->prepare("SELECT m.manga_name, m.slug FROM manga m WHERE m.slug=?");
// $stmt->bind_param('s',$slug); $stmt->execute();
// $manga = $stmt->get_result()->fetch_assoc();
// $stmt2 = $conn->prepare("SELECT * FROM chap WHERE id_manga=? AND so_chuong=?");
// $stmt2->bind_param('ii',$manga['id_manga'],$chap_num); $stmt2->execute();
// $chapter = $stmt2->get_result()->fetch_assoc();
// $images = json_decode($chapter['danh_sach_anh'] ?? '[]', true);
// $stmt3 = $conn->prepare("SELECT so_chuong FROM chap WHERE id_manga=? ORDER BY so_chuong ASC");
// $stmt3->bind_param('i',$manga['id_manga']); $stmt3->execute();
// $all_chaps = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);

// --- DỮ LIỆU MẪU DEMO ---
$manga   = ['manga_name' => 'Tên Truyện Mẫu', 'slug' => $slug];
$chapter = ['so_chuong' => $chap_num, 'tieu_de_chuong' => 'Tiêu đề chương mẫu'];
$images  = array_map(fn($i) => "https://via.placeholder.com/800x1200?text=Trang+$i", range(1, 5));
$all_chaps = array_map(fn($i) => ['so_chuong' => $i], range(1, 10));
$prev_chap = $chap_num > 1 ? $chap_num - 1 : null;
$next_chap = $chap_num < count($all_chaps) ? $chap_num + 1 : null;

$base_url     = '../';
$page_title   = 'Chương ' . $chap_num . ' - ' . htmlspecialchars($manga['manga_name']) . ' - Truyện Hay';
$current_page = '';
require_once '../includes/header.php';
?>

<!-- BREADCRUMB -->
<div class="breadcrumb">
    <a href="../index.php"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <a href="../truyen/<?php echo $slug; ?>"><?php echo htmlspecialchars($manga['manga_name']); ?></a>
    <i class="fas fa-chevron-right"></i>
    <span>Chương <?php echo $chap_num; ?></span>
</div>

<main class="main-content read-page">

    <!-- THANH ĐIỀU KHIỂN TRÊN -->
    <div class="read-controls">
        <div class="read-controls-inner">
            <a href="../truyen/<?php echo $slug; ?>" class="ctrl-btn">
                <i class="fas fa-list"></i> Danh sách chương
            </a>
            <div class="chap-nav">
                <?php if($prev_chap): ?>
                <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo $prev_chap; ?>" class="ctrl-btn">
                    <i class="fas fa-chevron-left"></i> Chương trước
                </a>
                <?php else: ?>
                <button class="ctrl-btn disabled" disabled><i class="fas fa-chevron-left"></i> Chương trước</button>
                <?php endif; ?>

                <!-- Dropdown chọn chương -->
                <select class="chap-select" onchange="window.location.href='../doc/<?php echo $slug; ?>/chuong-'+this.value">
                    <?php foreach($all_chaps as $c): ?>
                    <option value="<?php echo $c['so_chuong']; ?>" <?php echo $c['so_chuong']==$chap_num?'selected':''; ?>>
                        Chương <?php echo $c['so_chuong']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <?php if($next_chap): ?>
                <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo $next_chap; ?>" class="ctrl-btn">
                    Chương sau <i class="fas fa-chevron-right"></i>
                </a>
                <?php else: ?>
                <button class="ctrl-btn disabled" disabled>Chương sau <i class="fas fa-chevron-right"></i></button>
                <?php endif; ?>
            </div>
            <button class="ctrl-btn" id="btn-font-size"><i class="fas fa-text-height"></i> Cỡ chữ</button>
        </div>
    </div>

    <!-- TIÊU ĐỀ CHƯƠNG -->
    <div class="chapter-title-bar">
        <h1><?php echo htmlspecialchars($manga['manga_name']); ?></h1>
        <h2>Chương <?php echo $chap_num; ?>: <?php echo htmlspecialchars($chapter['tieu_de_chuong']); ?></h2>
    </div>

    <!-- NỘI DUNG CHƯƠNG (ảnh dọc) -->
    <div class="chapter-content" id="chapter-content">
        <?php foreach($images as $idx => $img): ?>
        <img src="<?php echo $img; ?>"
             alt="Trang <?php echo $idx+1; ?>"
             loading="lazy"
             class="chapter-image">
        <?php endforeach; ?>
    </div>

    <!-- THANH ĐIỀU KHIỂN DƯỚI (giống trên) -->
    <div class="read-controls read-controls-bottom">
        <div class="read-controls-inner">
            <a href="../truyen/<?php echo $slug; ?>" class="ctrl-btn">
                <i class="fas fa-list"></i> Danh sách chương
            </a>
            <div class="chap-nav">
                <?php if($prev_chap): ?>
                <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo $prev_chap; ?>" class="ctrl-btn">
                    <i class="fas fa-chevron-left"></i> Chương trước
                </a>
                <?php else: ?>
                <button class="ctrl-btn disabled" disabled><i class="fas fa-chevron-left"></i> Chương trước</button>
                <?php endif; ?>

                <select class="chap-select" onchange="window.location.href='../doc/<?php echo $slug; ?>/chuong-'+this.value">
                    <?php foreach($all_chaps as $c): ?>
                    <option value="<?php echo $c['so_chuong']; ?>" <?php echo $c['so_chuong']==$chap_num?'selected':''; ?>>
                        Chương <?php echo $c['so_chuong']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <?php if($next_chap): ?>
                <a href="../doc/<?php echo $slug; ?>/chuong-<?php echo $next_chap; ?>" class="ctrl-btn">
                    Chương sau <i class="fas fa-chevron-right"></i>
                </a>
                <?php else: ?>
                <button class="ctrl-btn disabled" disabled>Chương sau <i class="fas fa-chevron-right"></i></button>
                <?php endif; ?>
            </div>
        </div>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>