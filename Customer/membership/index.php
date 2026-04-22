<?php
// Customer/membership/index.php
session_start();

require_once '../../include/db.php';

$db = new Database();

// Lấy danh sách gói đang active, sắp xếp theo sort_order
$db->query("SELECT * FROM membership_package WHERE is_active = 1 ORDER BY sort_order ASC");
$packages = $db->resultSet();

// Lấy gói hiện tại của user (nếu đã đăng nhập)
$current_membership = null;
if (isset($_SESSION['user_id'])) {
    $db->query("
        SELECT um.*, mp.ten_goi, mp.sort_order
        FROM user_membership um
        JOIN membership_package mp ON um.id_package = mp.id_package
        WHERE um.id_taikhoan = :uid
          AND um.trang_thai = 'active'
          AND um.ngay_het_han >= CURDATE()
        ORDER BY um.ngay_het_han DESC
        LIMIT 1
    ");
    $db->bind(':uid', $_SESSION['user_id']);
    $current_membership = $db->single();
}

// Lấy promotion đang active (nếu có)
$db->query("
    SELECT mp2.*, mp.ten_goi
    FROM membership_promotion mp2
    JOIN membership_package mp ON mp2.id_package = mp.id_package
    WHERE mp2.is_active = 1
      AND mp2.ngay_bat_dau <= CURDATE()
      AND mp2.ngay_ket_thuc >= CURDATE()
");
$promotions = $db->resultSet();
// Chuyển thành mảng theo id_package để dễ tra cứu
$promo_map = [];
foreach ($promotions as $p) {
    $promo_map[$p['id_package']] = $p;
}

// Hàm tính giá theo chu kỳ
function calcPrice(float $gia_thang, string $chu_ky, float $promo_pct = 0): array {
    $months   = ['month' => 1, 'quarter' => 3, 'year' => 12][$chu_ky] ?? 1;
    $discount = ['month' => 0, 'quarter' => 10, 'year' => 20][$chu_ky] ?? 0;
    $base     = $gia_thang * $months;
    $after_cycle = $base * (1 - $discount / 100);
    $after_promo = $after_cycle * (1 - $promo_pct / 100);
    return [
        'original'      => $base,
        'cycle_discount'=> $discount,
        'promo_discount'=> $promo_pct,
        'final'         => $after_promo,
    ];
}

$base_url     = '../';
$page_title   = 'Gói thành viên - Truyện Hay';
$current_page = 'membership';
$extra_css    = ['../style.css', 'membership.css'];
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo $base_url; ?>membership/membership.css">

<main class="main-content">

<!-- HERO BANNER -->
<div class="mem-hero">
    <div class="mem-hero-inner">
        <div class="mem-hero-badge"><i class="fas fa-crown"></i> Thành viên đặc quyền</div>
        <h1 class="mem-hero-title">Nâng cấp trải nghiệm đọc truyện</h1>
        <p class="mem-hero-sub">Chọn gói phù hợp — hủy bất cứ lúc nào, không ràng buộc.</p>

        <!-- Chọn chu kỳ thanh toán -->
        <div class="cycle-switcher">
            <button class="cycle-btn active" data-cycle="month">Theo tháng</button>
            <button class="cycle-btn" data-cycle="quarter">
                Theo quý <span class="cycle-save">-10%</span>
            </button>
            <button class="cycle-btn" data-cycle="year">
                Theo năm <span class="cycle-save">-20%</span>
            </button>
        </div>
    </div>
</div>

<!-- THÔNG BÁO GÓI HIỆN TẠI -->
<?php if ($current_membership): ?>
<div class="mem-current-alert">
    <i class="fas fa-check-circle"></i>
    Bạn đang dùng gói <strong><?php echo htmlspecialchars($current_membership['ten_goi']); ?></strong>
    — hết hạn ngày <strong><?php echo date('d/m/Y', strtotime($current_membership['ngay_het_han'])); ?></strong>.
    <a href="manage.php">Quản lý thẻ &rarr;</a>
</div>
<?php endif; ?>

<!-- BẢNG SO SÁNH GÓI -->
<div class="packages-wrapper">
    <?php foreach ($packages as $pkg):
        $id  = $pkg['id_package'];
        $is_free    = ($pkg['gia_thang'] == 0);
        $is_premium = ($pkg['ten_goi'] === 'Premium');
        $is_vip     = ($pkg['ten_goi'] === 'VIP');
        $promo      = $promo_map[$id] ?? null;
        $promo_pct  = $promo ? (float)$promo['giam_phan_tram'] : 0;

        // Tính giá cho 3 chu kỳ, lưu vào data-attribute
        $price_month   = calcPrice($pkg['gia_thang'], 'month',   $promo_pct);
        $price_quarter = calcPrice($pkg['gia_thang'], 'quarter', $promo_pct);
        $price_year    = calcPrice($pkg['gia_thang'], 'year',    $promo_pct);

        // Xác định user đang dùng gói này không
        $is_current = $current_membership && ($current_membership['id_package'] == $id);
        $current_sort = $current_membership ? (int)$current_membership['sort_order'] : 1;
        $this_sort = (int)$pkg['sort_order'];
        $is_upgrade   = (!$is_current && $current_sort > 0 && $this_sort > $current_sort);
        $is_downgrade = (!$is_current && $current_sort > 0 && $this_sort < $current_sort);

        // Giải mã quà tặng
        $qua_tang = $pkg['qua_tang'] ? json_decode($pkg['qua_tang'], true) : null;
    ?>
    <div class="package-card <?php echo $is_premium ? 'card-popular' : ''; ?> <?php echo $is_vip ? 'card-vip' : ''; ?> <?php echo $is_current ? 'card-current' : ''; ?>"
         data-price-month="<?php echo $price_month['final']; ?>"
         data-price-quarter="<?php echo $price_quarter['final']; ?>"
         data-price-year="<?php echo $price_year['final']; ?>"
         data-original-month="<?php echo $price_month['original']; ?>"
         data-original-quarter="<?php echo $price_quarter['original']; ?>"
         data-original-year="<?php echo $price_year['original']; ?>">

        <!-- BADGE -->
        <?php if ($is_premium): ?>
            <div class="pkg-badge badge-popular"><i class="fas fa-fire"></i> Phổ biến nhất</div>
        <?php elseif ($is_vip): ?>
            <div class="pkg-badge badge-vip"><i class="fas fa-gem"></i> Tiết kiệm nhất</div>
        <?php elseif ($is_current): ?>
            <div class="pkg-badge badge-current"><i class="fas fa-check"></i> Đang dùng</div>
        <?php endif; ?>

        <!-- TÊN & GIÁ -->
        <div class="pkg-header">
            <div class="pkg-icon">
                <?php
                $icons = ['Free'=>'fas fa-book-open','Basic'=>'fas fa-star','Premium'=>'fas fa-crown','VIP'=>'fas fa-gem'];
                echo '<i class="' . ($icons[$pkg['ten_goi']] ?? 'fas fa-tag') . '"></i>';
                ?>
            </div>
            <h2 class="pkg-name"><?php echo htmlspecialchars($pkg['ten_goi']); ?></h2>
            <p class="pkg-desc"><?php echo htmlspecialchars($pkg['mo_ta'] ?? ''); ?></p>

            <?php if ($is_free): ?>
                <div class="pkg-price"><span class="price-big">Miễn phí</span></div>
            <?php else: ?>
                <!-- Promotion banner -->
                <?php if ($promo): ?>
                <div class="promo-banner">
                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($promo['ten_promo']); ?>
                    — giảm thêm <?php echo $promo['giam_phan_tram']; ?>%
                </div>
                <?php endif; ?>

                <div class="pkg-price">
                    <!-- Giá cũ (nếu có promo hoặc chu kỳ dài) -->
                    <span class="price-original js-price-original"
                          style="<?php echo $promo_pct == 0 ? 'display:none' : ''; ?>">
                        <?php echo number_format($price_month['original'], 0, ',', '.'); ?>đ
                    </span>
                    <span class="price-big js-price-final">
                        <?php echo $is_free ? 'Miễn phí' : number_format($price_month['final'], 0, ',', '.') . 'đ'; ?>
                    </span>
                    <span class="price-unit js-price-unit">/tháng</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- DANH SÁCH QUYỀN LỢI -->
        <ul class="pkg-benefits">
            <?php
            $benefits = [
                ['icon'=>'fas fa-book','label'=>'Đọc truyện miễn phí không giới hạn','val'=>$pkg['doc_vo_han']],
                ['icon'=>'fas fa-lock-open','label'=>'Đọc truyện trả phí','val'=>$pkg['doc_tra_phi']],
                ['icon'=>'fas fa-tags','label'=>'Giảm '.$pkg['giam_gia_mua'].'% khi mua sách','val'=>$pkg['giam_gia_mua']>0],
                ['icon'=>'fas fa-bolt','label'=>'Đọc trước chương mới','val'=>$pkg['doc_truoc']],
                ['icon'=>'fas fa-coins','label'=>'Hệ số tích điểm x'.$pkg['he_so_diem'],'val'=>true],
            ];
            foreach ($benefits as $b):
                $has = (bool)$b['val'];
            ?>
            <li class="benefit-row <?php echo $has ? 'benefit-yes' : 'benefit-no'; ?>">
                <span class="benefit-check">
                    <i class="fas fa-<?php echo $has ? 'check' : 'times'; ?>"></i>
                </span>
                <span><?php echo $b['label']; ?></span>
            </li>
            <?php endforeach; ?>

            <!-- Quà tặng khi đăng ký mới -->
            <?php if ($qua_tang && isset($qua_tang['mo_ta'])): ?>
            <li class="benefit-row benefit-gift">
                <span class="benefit-check"><i class="fas fa-gift"></i></span>
                <span><?php echo htmlspecialchars($qua_tang['mo_ta']); ?></span>
            </li>
            <?php endif; ?>
        </ul>

        <!-- NÚT HÀNH ĐỘNG -->
        <div class="pkg-action">
            <?php if ($is_free): ?>
                <?php if (!$current_membership || $current_membership['ten_goi'] === 'Free'): ?>
                    <button class="btn-pkg btn-current-plan" disabled>Gói hiện tại</button>
                <?php else: ?>
                    <a href="manage.php?action=cancel" class="btn-pkg btn-downgrade">Hạ xuống Free</a>
                <?php endif; ?>

            <?php elseif ($is_current): ?>
                <a href="manage.php" class="btn-pkg btn-manage">
                    <i class="fas fa-cog"></i> Quản lý thẻ
                </a>

            <?php elseif (!isset($_SESSION['user_id'])): ?>
                <a href="../auth/login.php?redirect=membership/index.php" class="btn-pkg btn-subscribe">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập để đăng ký
                </a>

            <?php elseif ($is_upgrade): ?>
                <a href="subscribe.php?package=<?php echo $id; ?>" class="btn-pkg btn-upgrade">
                    <i class="fas fa-arrow-up"></i> Nâng cấp
                </a>

            <?php elseif ($is_downgrade): ?>
                <a href="subscribe.php?package=<?php echo $id; ?>" class="btn-pkg btn-downgrade-link">
                    <i class="fas fa-arrow-down"></i> Hạ cấp
                </a>

            <?php else: ?>
                <a href="subscribe.php?package=<?php echo $id; ?>" class="btn-pkg btn-subscribe">
                    <i class="fas fa-crown"></i> Đăng ký ngay
                </a>
            <?php endif; ?>
        </div>

    </div>
    <?php endforeach; ?>
</div>

<!-- FAQ ngắn -->
<div class="mem-faq">
    <h2><i class="fas fa-question-circle"></i> Câu hỏi thường gặp</h2>
    <div class="faq-grid">
        <div class="faq-item">
            <strong>Tôi có thể hủy bất cứ lúc nào không?</strong>
            <p>Có. Bạn vẫn giữ nguyên đặc quyền đến hết chu kỳ đã thanh toán và không bị tính thêm phí.</p>
        </div>
        <div class="faq-item">
            <strong>Nâng cấp giữa chừng tính tiền như thế nào?</strong>
            <p>Hệ thống tính số ngày còn lại của gói cũ và trừ vào giá gói mới (pro-rata), bạn chỉ trả phần chênh lệch.</p>
        </div>
        <div class="faq-item">
            <strong>Thanh toán theo quý/năm có được hoàn tiền không?</strong>
            <p>Chính sách không hoàn tiền sau khi thanh toán, nhưng bạn vẫn dùng đầy đủ đặc quyền đến hết hạn.</p>
        </div>
        <div class="faq-item">
            <strong>Quà tặng chào mừng được nhận khi nào?</strong>
            <p>Ngay sau khi thanh toán thành công, quà tặng tự động được cộng vào tài khoản của bạn.</p>
        </div>
    </div>
</div>

</main>

<?php require_once '../includes/footer.php'; ?>

<script>
// Chuyển đổi chu kỳ thanh toán
const cycleBtns = document.querySelectorAll('.cycle-btn');
const cards     = document.querySelectorAll('.package-card');

const unitLabel = {
    month:   '/tháng',
    quarter: '/quý (3 tháng)',
    year:    '/năm'
};

cycleBtns.forEach(btn => {
    btn.addEventListener('click', function () {
        cycleBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const cycle = this.dataset.cycle;
        cards.forEach(card => {
            const finalEl    = card.querySelector('.js-price-final');
            const originalEl = card.querySelector('.js-price-original');
            const unitEl     = card.querySelector('.js-price-unit');
            if (!finalEl) return; // Free card

            const final    = parseFloat(card.dataset['price' + cycle.charAt(0).toUpperCase() + cycle.slice(1)]);
            const original = parseFloat(card.dataset['original' + cycle.charAt(0).toUpperCase() + cycle.slice(1)]);

            if (isNaN(final) || final === 0) {
                finalEl.textContent = 'Miễn phí';
                if (originalEl) originalEl.style.display = 'none';
            } else {
                finalEl.textContent = formatVND(final) + 'đ';
                // Hiện giá gốc nếu có giảm
                if (originalEl) {
                    if (final < original) {
                        originalEl.textContent = formatVND(original) + 'đ';
                        originalEl.style.display = 'inline';
                    } else {
                        originalEl.style.display = 'none';
                    }
                }
            }
            if (unitEl) unitEl.textContent = unitLabel[cycle];

            // Cập nhật link đăng ký với chu kỳ
            const subLink = card.querySelector('a[href*="subscribe.php"]');
            if (subLink) {
                const url = new URL(subLink.href, window.location.href);
                url.searchParams.set('cycle', cycle);
                subLink.href = url.toString();
            }
        });
    });
});

function formatVND(num) {
    return Math.round(num).toLocaleString('vi-VN');
}
</script>