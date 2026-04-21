<?php
// Customer/membership/success.php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['mem_success'])) {
    header('Location: index.php');
    exit;
}

$data = $_SESSION['mem_success'];
unset($_SESSION['mem_success']); // Xoá sau khi dùng

$icons = ['Free'=>'fas fa-book-open','Basic'=>'fas fa-star','Premium'=>'fas fa-crown','VIP'=>'fas fa-gem'];
$rank_icon = $icons[$data['ten_goi']] ?? 'fas fa-crown';

$base_url     = '../';
$page_title   = 'Đăng ký thành công - Truyện Hay';
$current_page = 'membership';
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo $base_url; ?>membership/membership.css">

<main class="main-content">
<div class="mem-success-page">

    <!-- CONFETTI -->
    <div class="confetti-wrap" id="confetti"></div>

    <!-- ICON SUCCESS -->
    <div class="success-icon-wrap">
        <div class="success-icon"><i class="fas fa-check"></i></div>
    </div>

    <h1>Chào mừng bạn đến với <?php echo htmlspecialchars($data['ten_goi']); ?>!</h1>
    <p class="success-sub">Gói thành viên của bạn đã được kích hoạt thành công.</p>

    <!-- BADGE RANK -->
    <div class="rank-badge-big rank-<?php echo strtolower($data['ten_goi']); ?>">
        <i class="<?php echo $rank_icon; ?>"></i>
        <?php echo htmlspecialchars($data['ten_goi']); ?> Member
    </div>

    <!-- THÔNG TIN ĐƠN -->
    <div class="success-info-box">
        <h2><i class="fas fa-receipt"></i> Thông tin đăng ký</h2>
        <div class="sinfo-row">
            <span>Mã giao dịch</span>
            <strong><?php echo htmlspecialchars($data['ma_giao_dich']); ?></strong>
        </div>
        <div class="sinfo-row">
            <span>Gói</span>
            <strong><?php echo htmlspecialchars($data['ten_goi']); ?></strong>
        </div>
        <div class="sinfo-row">
            <span>Số tiền</span>
            <strong><?php echo $data['so_tien'] > 0 ? number_format($data['so_tien'],0,',','.') . 'đ' : 'Miễn phí'; ?></strong>
        </div>
        <div class="sinfo-row">
            <span>Hết hạn</span>
            <strong><?php echo date('d/m/Y', strtotime($data['ngay_het_han'])); ?></strong>
        </div>
        <div class="sinfo-row">
            <span>Thanh toán</span>
            <strong style="color:#2ed573"><i class="fas fa-check-circle"></i> Đã xác nhận</strong>
        </div>
    </div>

    <!-- QUÀ TẶNG -->
    <?php if (!empty($data['qua_tang'])): $qt = $data['qua_tang']; ?>
    <div class="gift-box">
        <div class="gift-header"><i class="fas fa-gift"></i> Quà tặng chào mừng</div>
        <div class="gift-items">
            <?php if (!empty($qt['diem']) && $qt['diem'] > 0): ?>
            <div class="gift-item">
                <i class="fas fa-coins"></i>
                <span>+<?php echo $qt['diem']; ?> điểm tích lũy đã được cộng vào tài khoản</span>
            </div>
            <?php endif; ?>
            <?php if (!empty($qt['ma_giam_gia'])): ?>
            <div class="gift-item">
                <i class="fas fa-tag"></i>
                <span>Mã giảm <?php echo number_format($qt['ma_giam_gia'],0,',','.'); ?>đ cho đơn mua sách tiếp theo</span>
            </div>
            <?php endif; ?>
            <?php if (!empty($qt['sach_mien_phi'])): ?>
            <div class="gift-item">
                <i class="fas fa-book"></i>
                <span>1 cuốn sách miễn phí — vào <a href="../shop/index.php">Shop</a> để chọn ngay!</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- CÁC BƯỚC TIẾP THEO -->
    <div class="success-next-steps">
        <div class="next-s">
            <i class="fas fa-envelope"></i>
            <strong>Email xác nhận</strong>
            <span>Kiểm tra hòm thư để xem hóa đơn</span>
        </div>
        <div class="next-s">
            <i class="fas fa-book-open"></i>
            <strong>Bắt đầu đọc</strong>
            <span>Tận hưởng đặc quyền ngay bây giờ</span>
        </div>
        <div class="next-s">
            <i class="fas fa-cog"></i>
            <strong>Quản lý thẻ</strong>
            <span>Gia hạn, hủy hoặc nâng cấp bất cứ lúc nào</span>
        </div>
    </div>

    <!-- NÚT HÀNH ĐỘNG -->
    <div class="success-actions">
        <a href="../manga/list.php" class="btn-action btn-primary-action">
            <i class="fas fa-book-open"></i> Đọc truyện ngay
        </a>
        <a href="manage.php" class="btn-action btn-secondary-action">
            <i class="fas fa-cog"></i> Quản lý thẻ
        </a>
        <a href="../user/profile.php" class="btn-action btn-outline-action">
            <i class="fas fa-user"></i> Xem hồ sơ
        </a>
    </div>

</div>
</main>

<?php require_once '../includes/footer.php'; ?>

<style>
.mem-success-page {
    max-width: 680px;
    margin: 0 auto;
    padding: 50px 5%;
    text-align: center;
}
.confetti-wrap { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999; overflow: hidden; }
.confetti-wrap span { position: absolute; top: -20px; width: 12px; height: 12px; border-radius: 3px; animation: fall linear forwards; }
@keyframes fall { to { transform: translateY(110vh) rotate(720deg); opacity: 0; } }

.success-icon-wrap { margin-bottom: 24px; }
.success-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg,#2ed573,#27ae60);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; color: #fff;
    margin: 0 auto;
    box-shadow: 0 8px 30px rgba(46,213,115,.4);
    animation: pop .5s cubic-bezier(.175,.885,.32,1.275);
}
@keyframes pop { from { transform: scale(0); } to { transform: scale(1); } }

.mem-success-page h1 { font-size: clamp(1.4rem,4vw,2rem); font-weight: 900; color: #2f3542; margin-bottom: 10px; }
body.dark-mode .mem-success-page h1 { color: #e2e8f0; }
.success-sub { color: #747d8c; margin-bottom: 24px; }

/* RANK BADGE */
.rank-badge-big {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 28px;
    border-radius: 40px;
    font-size: 1.1rem;
    font-weight: 900;
    letter-spacing: .5px;
    margin-bottom: 32px;
}
.rank-basic   { background: linear-gradient(135deg,#74b9ff,#0984e3); color: #fff; box-shadow: 0 6px 20px rgba(9,132,227,.3); }
.rank-premium { background: linear-gradient(135deg,#ff4757,#ff6b6b); color: #fff; box-shadow: 0 6px 20px rgba(255,71,87,.3); }
.rank-vip     { background: linear-gradient(135deg,#f39c12,#e67e22); color: #fff; box-shadow: 0 6px 20px rgba(243,156,18,.35); }
.rank-free    { background: #f1f2f6; color: #747d8c; }

/* INFO BOX */
.success-info-box {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,.07);
    text-align: left;
    margin-bottom: 24px;
}
body.dark-mode .success-info-box { background: #1e2533; }
.success-info-box h2 {
    font-size: 1rem;
    font-weight: 800;
    color: #2f3542;
    margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px;
}
.success-info-box h2 i { color: #ff4757; }
body.dark-mode .success-info-box h2 { color: #e2e8f0; }
.sinfo-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f1f2f6;
    font-size: .9rem;
    color: #747d8c;
}
.sinfo-row:last-child { border-bottom: none; }
.sinfo-row strong { color: #2f3542; }
body.dark-mode .sinfo-row { border-color: #2d3748; color: #94a3b8; }
body.dark-mode .sinfo-row strong { color: #e2e8f0; }

/* GIFT BOX */
.gift-box {
    background: linear-gradient(135deg,rgba(255,71,87,.06),rgba(243,156,18,.06));
    border: 2px solid rgba(255,71,87,.2);
    border-radius: 16px;
    padding: 20px 24px;
    text-align: left;
    margin-bottom: 28px;
}
.gift-header { font-size: 1rem; font-weight: 800; color: #ff4757; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.gift-items { display: flex; flex-direction: column; gap: 10px; }
.gift-item { display: flex; align-items: center; gap: 12px; font-size: .9rem; color: #2f3542; }
body.dark-mode .gift-item { color: #cbd5e0; }
.gift-item i { color: #f39c12; font-size: 1rem; width: 20px; }
.gift-item a { color: #ff4757; font-weight: 700; }

/* NEXT STEPS */
.success-next-steps { display: flex; gap: 16px; margin-bottom: 32px; flex-wrap: wrap; justify-content: center; }
.next-s {
    flex: 1; min-width: 150px;
    background: #fff;
    border-radius: 12px;
    padding: 18px 14px;
    box-shadow: 0 3px 12px rgba(0,0,0,.07);
    display: flex; flex-direction: column; align-items: center; gap: 6px;
}
body.dark-mode .next-s { background: #1e2533; }
.next-s i { font-size: 1.5rem; color: #ff4757; }
.next-s strong { font-size: .9rem; color: #2f3542; }
body.dark-mode .next-s strong { color: #e2e8f0; }
.next-s span { font-size: .78rem; color: #a4b0be; text-align: center; }

/* ACTION BUTTONS */
.success-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.btn-action { padding: 12px 24px; border-radius: 25px; font-size: .9rem; font-weight: 700; text-decoration: none; transition: all .25s; display: flex; align-items: center; gap: 7px; }
.btn-primary-action { background: #ff4757; color: #fff; box-shadow: 0 4px 14px rgba(255,71,87,.3); }
.btn-primary-action:hover { background: #ff6b6b; transform: translateY(-2px); }
.btn-secondary-action { background: #2f3542; color: #fff; }
.btn-secondary-action:hover { background: #57606f; transform: translateY(-2px); }
.btn-outline-action { border: 2px solid #dfe4ea; color: #747d8c; }
.btn-outline-action:hover { border-color: #ff4757; color: #ff4757; }
</style>

<script>
// Confetti
const colors = ['#ff4757','#f39c12','#2ed573','#3498db','#9b59b6','#ff6b6b'];
const wrap   = document.getElementById('confetti');
for (let i = 0; i < 70; i++) {
    const el = document.createElement('span');
    el.style.left              = Math.random() * 100 + 'vw';
    el.style.background        = colors[Math.floor(Math.random() * colors.length)];
    el.style.animationDuration = (Math.random() * 3 + 2) + 's';
    el.style.animationDelay    = (Math.random() * 2) + 's';
    el.style.opacity           = Math.random() * 0.8 + 0.2;
    el.style.transform         = `rotate(${Math.random()*360}deg)`;
    wrap.appendChild(el);
}
</script>