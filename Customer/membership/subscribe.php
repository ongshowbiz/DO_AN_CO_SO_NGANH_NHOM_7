<?php
// Customer/membership/subscribe.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?redirect=membership/subscribe.php');
    exit;
}

require_once '../../include/db.php';

$db      = new Database();
$user_id = $_SESSION['user_id'];

// Lấy package được chọn
$id_package = isset($_GET['package']) ? (int)$_GET['package'] : 0;
$cycle      = in_array($_GET['cycle'] ?? 'month', ['month','quarter','year'])
              ? ($_GET['cycle'] ?? 'month') : 'month';

if ($id_package === 0) {
    header('Location: index.php');
    exit;
}

$db->query("SELECT * FROM membership_package WHERE id_package = :id AND is_active = 1");
$db->bind(':id', $id_package);
$package = $db->single();

if (!$package) {
    header('Location: index.php');
    exit;
}

// Gói Free không cần thanh toán
if ($package['gia_thang'] == 0) {
    header('Location: index.php');
    exit;
}

// Membership hiện tại
$db->query("
    SELECT um.*, mp.ten_goi, mp.gia_thang, mp.sort_order
    FROM user_membership um
    JOIN membership_package mp ON um.id_package = mp.id_package
    WHERE um.id_taikhoan = :uid
      AND um.trang_thai = 'active'
      AND um.ngay_het_han >= CURDATE()
    ORDER BY um.ngay_het_han DESC
    LIMIT 1
");
$db->bind(':uid', $user_id);
$current = $db->single();

// Promotion
$db->query("
    SELECT * FROM membership_promotion
    WHERE id_package = :pid AND is_active = 1
      AND ngay_bat_dau <= CURDATE() AND ngay_ket_thuc >= CURDATE()
    LIMIT 1
");
$db->bind(':pid', $id_package);
$promo = $db->single();
$promo_pct = $promo ? (float)$promo['giam_phan_tram'] : 0;

// Tính giá
$months_map = ['month'=>1,'quarter'=>3,'year'=>12];
$discount_map = ['month'=>0,'quarter'=>10,'year'=>20];
$months       = $months_map[$cycle];
$cycle_disc   = $discount_map[$cycle];

$base_price   = $package['gia_thang'] * $months;
$after_cycle  = $base_price * (1 - $cycle_disc / 100);
$final_price  = $after_cycle * (1 - $promo_pct / 100);

// Tính pro-rata nếu đang nâng cấp
$prorata_credit = 0;
if ($current && (int)$current['sort_order'] < (int)$package['sort_order']) {
    // Số ngày còn lại của gói cũ
    $days_left  = (new DateTime())->diff(new DateTime($current['ngay_het_han']))->days;
    $total_days = (int)($current['so_tien'] > 0 ? 30 : 30); // giả sử 30 ngày/tháng
    $prorata_credit = round(($current['so_tien'] / 30) * $days_left);
}

$pay_amount = max(0, $final_price - $prorata_credit);

// Thông tin user
$db->query("SELECT TENTAIKHOAN, EMAIL FROM taikhoan WHERE ID_TAIKHOAN = :id");
$db->bind(':id', $user_id);
$user = $db->single();

$cycle_label = ['month'=>'1 tháng','quarter'=>'3 tháng (quý)','year'=>'12 tháng (1 năm)'][$cycle];

$base_url     = '../';
$page_title   = 'Xác nhận đăng ký - Truyện Hay';
$current_page = 'membership';
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo $base_url; ?>membership/membership.css">

<main class="main-content">

<!-- BƯỚC THANH TOÁN -->
<div class="sub-steps">
    <div class="sub-step done"><span>1</span> Chọn gói</div>
    <div class="sub-step-line done"></div>
    <div class="sub-step active"><span>2</span> Xác nhận</div>
    <div class="sub-step-line"></div>
    <div class="sub-step"><span>3</span> Hoàn tất</div>
</div>

<div class="sub-layout">

    <!-- CỘT TRÁI: chi tiết gói & thanh toán -->
    <div class="sub-left">

        <!-- Thông tin gói -->
        <div class="sub-card">
            <h2><i class="fas fa-crown"></i> Gói đã chọn</h2>
            <div class="chosen-pkg">
                <div class="chosen-pkg-name"><?php echo htmlspecialchars($package['ten_goi']); ?></div>
                <div class="chosen-pkg-cycle"><i class="fas fa-calendar-alt"></i> <?php echo $cycle_label; ?></div>
                <?php if ($promo): ?>
                <div class="chosen-promo">
                    <i class="fas fa-tag"></i> Ưu đãi: <?php echo htmlspecialchars($promo['ten_promo']); ?>
                    (–<?php echo $promo['giam_phan_tram']; ?>%)
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pro-rata nếu nâng cấp -->
        <?php if ($prorata_credit > 0): ?>
        <div class="sub-card prorata-box">
            <h2><i class="fas fa-exchange-alt"></i> Nâng cấp từ <?php echo htmlspecialchars($current['ten_goi']); ?></h2>
            <p>Gói hiện tại còn <strong><?php echo (new DateTime())->diff(new DateTime($current['ngay_het_han']))->days; ?> ngày</strong> — hệ thống tính bù vào giá gói mới.</p>
            <div class="prorata-row">
                <span>Giá trị còn lại gói cũ</span>
                <span class="prorata-credit">–<?php echo number_format($prorata_credit,0,',','.'); ?>đ</span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Phương thức thanh toán -->
        <div class="sub-card">
            <h2><i class="fas fa-wallet"></i> Phương thức thanh toán</h2>
            <div class="payment-options">
                <label class="pay-opt selected">
                    <input type="radio" name="pt" value="qr" checked>
                    <i class="fas fa-qrcode" style="color:#3498db"></i>
                    <div>
                        <strong>Chuyển khoản QR</strong>
                        <span>Quét mã QR VietQR tự động</span>
                    </div>
                </label>
                <label class="pay-opt">
                    <input type="radio" name="pt" value="simulate">
                    <i class="fas fa-mouse-pointer" style="color:#9b59b6"></i>
                    <div>
                        <strong>Thanh toán thử (Demo)</strong>
                        <span>Kích hoạt ngay, không cần chuyển tiền</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Tự động gia hạn -->
        <div class="sub-card">
            <label class="auto-renew-toggle">
                <input type="checkbox" id="auto_renew" checked>
                <span class="toggle-slider"></span>
                <div>
                    <strong>Tự động gia hạn</strong>
                    <span>Hệ thống tự gia hạn trước 3 ngày hết hạn. Bạn có thể tắt bất cứ lúc nào.</span>
                </div>
            </label>
        </div>

    </div><!-- /.sub-left -->

    <!-- CỘT PHẢI: tóm tắt & nút thanh toán -->
    <div class="sub-right">
        <div class="sub-card summary-card">
            <h2>Tóm tắt đơn</h2>

            <div class="sum-row">
                <span>Gói <?php echo htmlspecialchars($package['ten_goi']); ?> × <?php echo $months; ?> tháng</span>
                <span><?php echo number_format($base_price,0,',','.'); ?>đ</span>
            </div>
            <?php if ($cycle_disc > 0): ?>
            <div class="sum-row discount">
                <span>Giảm chu kỳ (<?php echo $cycle_disc; ?>%)</span>
                <span>–<?php echo number_format($base_price - $after_cycle,0,',','.'); ?>đ</span>
            </div>
            <?php endif; ?>
            <?php if ($promo_pct > 0): ?>
            <div class="sum-row discount">
                <span>Ưu đãi (<?php echo $promo_pct; ?>%)</span>
                <span>–<?php echo number_format($after_cycle - $final_price,0,',','.'); ?>đ</span>
            </div>
            <?php endif; ?>
            <?php if ($prorata_credit > 0): ?>
            <div class="sum-row discount">
                <span>Bù giá trị gói cũ</span>
                <span>–<?php echo number_format($prorata_credit,0,',','.'); ?>đ</span>
            </div>
            <?php endif; ?>

            <div class="sum-divider"></div>
            <div class="sum-total">
                <span>Tổng thanh toán</span>
                <span class="total-price"><?php echo number_format($pay_amount,0,',','.'); ?>đ</span>
            </div>

            <!-- NÚT THANH TOÁN -->
            <button class="btn-pay" id="btn-pay"
                    data-package="<?php echo $id_package; ?>"
                    data-cycle="<?php echo $cycle; ?>"
                    data-amount="<?php echo $pay_amount; ?>">
                <i class="fas fa-lock"></i> Thanh toán ngay
            </button>
            <p class="pay-note"><i class="fas fa-shield-alt"></i> Thông tin thanh toán được bảo mật</p>
        </div>

        <!-- Quyền lợi nhanh -->
        <div class="sub-card quick-benefits">
            <h3>Quyền lợi bạn nhận được</h3>
            <?php
            $qb = [];
            if ($package['doc_vo_han'])  $qb[] = ['fas fa-book','Đọc không giới hạn truyện miễn phí'];
            if ($package['doc_tra_phi']) $qb[] = ['fas fa-lock-open','Truy cập truyện trả phí'];
            if ($package['giam_gia_mua'] > 0) $qb[] = ['fas fa-tags','Giảm '.$package['giam_gia_mua'].'% mua sách'];
            if ($package['doc_truoc'])   $qb[] = ['fas fa-bolt','Đọc trước chương mới'];
            $qb[] = ['fas fa-coins','Hệ số tích điểm x'.$package['he_so_diem']];
            foreach ($qb as $q): ?>
            <div class="quick-benefit-row">
                <i class="<?php echo $q[0]; ?>"></i>
                <span><?php echo $q[1]; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div><!-- /.sub-right -->

</div><!-- /.sub-layout -->

<!-- MODAL QR THANH TOÁN -->
<div class="modal-overlay" id="modal-qr" style="display:none">
    <div class="modal-box">
        <button class="modal-close" id="modal-close"><i class="fas fa-times"></i></button>
        <h2><i class="fas fa-qrcode"></i> Quét QR để thanh toán</h2>
        <p class="modal-desc">Mở App ngân hàng và quét mã bên dưới. Số tiền & nội dung tự động điền.</p>
        <div id="qr-img-wrap">
            <!-- QR sẽ được inject bằng JS -->
        </div>
        <div class="qr-bank-info">
            <p>Ngân hàng: <strong>Vietcombank</strong></p>
            <p>Số TK: <strong>0123456789</strong></p>
            <p>Chủ TK: <strong>TRUYEN HAY</strong></p>
            <p>Số tiền: <strong id="qr-amount-label"></strong></p>
            <p>Nội dung: <strong id="qr-content-label"></strong></p>
        </div>
        <button class="btn-confirm-paid" id="btn-confirm-paid">
            <i class="fas fa-check-circle"></i> Tôi đã chuyển khoản xong
        </button>
        <p style="font-size:.8rem;color:#a4b0be;margin-top:8px;">
            * Nhấn xác nhận sau khi chuyển khoản thành công.
        </p>
    </div>
</div>

<!-- FORM ẨN GỬI SANG process_subscribe.php -->
<form id="sub-form" action="process_subscribe.php" method="POST" style="display:none">
    <input type="hidden" name="id_package"   value="<?php echo $id_package; ?>">
    <input type="hidden" name="cycle"         value="<?php echo $cycle; ?>">
    <input type="hidden" name="so_tien"       value="<?php echo $pay_amount; ?>">
    <input type="hidden" name="auto_renew"    id="hid-auto-renew" value="1">
    <input type="hidden" name="pt_thanh_toan" id="hid-pt" value="qr">
</form>

</main>

<?php require_once '../includes/footer.php'; ?>

<script>
// Chọn phương thức thanh toán
document.querySelectorAll('.pay-opt').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.pay-opt').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input').checked = true;
        document.getElementById('hid-pt').value = this.querySelector('input').value;
    });
});

// Auto renew toggle
document.getElementById('auto_renew').addEventListener('change', function() {
    document.getElementById('hid-auto-renew').value = this.checked ? '1' : '0';
});

// Nút thanh toán
document.getElementById('btn-pay').addEventListener('click', function() {
    const pt = document.querySelector('input[name="pt"]:checked').value;

    if (pt === 'qr') {
        openQRModal();
    } else {
        // Simulate: submit thẳng
        document.getElementById('sub-form').submit();
    }
});

function openQRModal() {
    const amount  = <?php echo (int)$pay_amount; ?>;
    const content = 'MEM<?php echo $id_package; ?>U<?php echo $user_id; ?>';
    const qrUrl   = `https://img.vietqr.io/image/vietcombank-0123456789-compact2.png?amount=${amount}&addInfo=${encodeURIComponent(content)}&accountName=${encodeURIComponent('TRUYEN HAY')}`;

    document.getElementById('qr-img-wrap').innerHTML =
        `<img src="${qrUrl}" alt="QR" style="width:220px;height:220px;border-radius:12px;display:block;margin:16px auto;">`;
    document.getElementById('qr-amount-label').textContent =
        amount.toLocaleString('vi-VN') + 'đ';
    document.getElementById('qr-content-label').textContent = content;

    document.getElementById('modal-qr').style.display = 'flex';
}

document.getElementById('modal-close').addEventListener('click', () => {
    document.getElementById('modal-qr').style.display = 'none';
});

document.getElementById('btn-confirm-paid').addEventListener('click', () => {
    document.getElementById('hid-pt').value = 'qr';
    document.getElementById('sub-form').submit();
});

// Đóng modal khi click ngoài
document.getElementById('modal-qr').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>