<?php
// Customer/membership/manage.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?redirect=membership/manage.php');
    exit;
}

require_once '../../include/db.php';

$db      = new Database();
$user_id = (int)$_SESSION['user_id'];
$msg     = '';
$msg_type = 'success';

// === XỬ LÝ HÀNH ĐỘNG ===

// 1. Tắt / bật tự động gia hạn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_auto_renew') {
    $val = ($_POST['auto_renew'] ?? '0') === '1' ? 1 : 0;
    $db->query("UPDATE user_membership SET tu_dong_gia_han = :v WHERE id_taikhoan = :uid AND trang_thai = 'active'");
    $db->bind(':v', $val);
    $db->bind(':uid', $user_id);
    $db->execute();
    $msg = $val ? 'Đã bật tự động gia hạn.' : 'Đã tắt tự động gia hạn.';
}

// 2. Hủy thẻ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $ly_do = trim($_POST['ly_do'] ?? 'Không có lý do');
    $db->query("UPDATE user_membership SET trang_thai = 'cancelled', tu_dong_gia_han = 0, ly_do_huy = :ly_do WHERE id_taikhoan = :uid AND trang_thai = 'active'");
    $db->bind(':ly_do', $ly_do);
    $db->bind(':uid',   $user_id);
    $db->execute();
    $msg = 'Đã hủy thẻ. Bạn vẫn giữ đặc quyền đến hết chu kỳ đã thanh toán.';
    $msg_type = 'warning';
}

// === LẤY DỮ LIỆU ===

// Membership hiện tại (active)
$db->query("
    SELECT um.*, mp.ten_goi, mp.gia_thang, mp.doc_vo_han, mp.doc_tra_phi,
           mp.giam_gia_mua, mp.doc_truoc, mp.he_so_diem, mp.sort_order
    FROM user_membership um
    JOIN membership_package mp ON um.id_package = mp.id_package
    WHERE um.id_taikhoan = :uid AND um.trang_thai = 'active'
      AND um.ngay_het_han >= CURDATE()
    ORDER BY um.ngay_het_han DESC
    LIMIT 1
");
$db->bind(':uid', $user_id);
$active = $db->single();

// Lịch sử thanh toán (tất cả membership)
$db->query("
    SELECT um.*, mp.ten_goi
    FROM user_membership um
    JOIN membership_package mp ON um.id_package = mp.id_package
    WHERE um.id_taikhoan = :uid
    ORDER BY um.created_at DESC
    LIMIT 20
");
$db->bind(':uid', $user_id);
$history = $db->resultSet();

// Quà tặng còn lại
$db->query("SELECT * FROM membership_reward WHERE id_taikhoan = :uid AND da_su_dung = 0 ORDER BY created_at DESC");
$db->bind(':uid', $user_id);
$rewards = $db->resultSet();

// User info (điểm tích lũy)
$db->query("SELECT TENTAIKHOAN, diem_tich_luy FROM taikhoan WHERE ID_TAIKHOAN = :uid");
$db->bind(':uid', $user_id);
$user_info = $db->single();

// Ngày còn lại
$days_left = 0;
if ($active) {
    $now     = new DateTime();
    $expire  = new DateTime($active['ngay_het_han']);
    $days_left = max(0, $now->diff($expire)->days);
}

// Danh sách gói để gia hạn/nâng cấp
$db->query("SELECT * FROM membership_package WHERE is_active = 1 AND gia_thang > 0 ORDER BY sort_order");
$all_packages = $db->resultSet();

$base_url     = '../';
$page_title   = 'Quản lý thẻ thành viên - Truyện Hay';
$current_page = 'membership';
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo $base_url; ?>membership/membership.css">

<main class="main-content">
<div class="manage-wrapper">

    <div class="manage-header">
        <h1><i class="fas fa-id-card"></i> Quản lý thẻ thành viên</h1>
        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Xem các gói</a>
    </div>

    <!-- THÔNG BÁO -->
    <?php if ($msg): ?>
    <div class="manage-alert manage-alert-<?php echo $msg_type; ?>">
        <i class="fas fa-<?php echo $msg_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
        <?php echo htmlspecialchars($msg); ?>
    </div>
    <?php endif; ?>

    <?php if ($active): ?>
    <!-- ===== GÓI ĐANG DÙNG ===== -->
    <div class="manage-grid">

        <!-- CARD GÓI HIỆN TẠI -->
        <div class="manage-card card-active">
            <div class="card-active-header">
                <div class="card-active-rank rank-<?php echo strtolower($active['ten_goi']); ?>">
                    <i class="fas fa-crown"></i>
                    <?php echo htmlspecialchars($active['ten_goi']); ?>
                </div>
                <span class="status-badge-active">
                    <i class="fas fa-circle"></i> Đang hoạt động
                </span>
            </div>

            <!-- Thanh thời gian còn lại -->
            <div class="expiry-bar-wrap">
                <?php
                $total_days = ['month'=>30,'quarter'=>90,'year'=>365][$active['chu_ky']] ?? 30;
                $pct = min(100, round(($days_left / $total_days) * 100));
                $bar_color = $days_left <= 7 ? '#ff4757' : ($days_left <= 15 ? '#f39c12' : '#2ed573');
                ?>
                <div class="expiry-labels">
                    <span>Còn <strong><?php echo $days_left; ?> ngày</strong></span>
                    <span>Hết hạn: <strong><?php echo date('d/m/Y', strtotime($active['ngay_het_han'])); ?></strong></span>
                </div>
                <div class="expiry-bar">
                    <div class="expiry-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $bar_color; ?>"></div>
                </div>
                <?php if ($days_left <= 7): ?>
                <p class="expiry-warning"><i class="fas fa-exclamation-triangle"></i> Sắp hết hạn! Gia hạn ngay để không gián đoạn.</p>
                <?php endif; ?>
            </div>

            <!-- Thông tin chi tiết -->
            <div class="active-info-grid">
                <div class="ainfo-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Chu kỳ</span>
                    <strong><?php echo ['month'=>'Theo tháng','quarter'=>'Theo quý','year'=>'Theo năm'][$active['chu_ky']]; ?></strong>
                </div>
                <div class="ainfo-item">
                    <i class="fas fa-coins"></i>
                    <span>Đã trả</span>
                    <strong><?php echo number_format($active['so_tien'],0,',','.'); ?>đ</strong>
                </div>
                <div class="ainfo-item">
                    <i class="fas fa-<?php echo $active['tu_dong_gia_han'] ? 'check-circle' : 'times-circle'; ?>"></i>
                    <span>Tự động gia hạn</span>
                    <strong style="color:<?php echo $active['tu_dong_gia_han'] ? '#2ed573' : '#ff4757'; ?>">
                        <?php echo $active['tu_dong_gia_han'] ? 'Bật' : 'Tắt'; ?>
                    </strong>
                </div>
                <div class="ainfo-item">
                    <i class="fas fa-star"></i>
                    <span>Điểm tích lũy</span>
                    <strong><?php echo number_format($user_info['diem_tich_luy'] ?? 0, 0, ',', '.'); ?></strong>
                </div>
            </div>

            <!-- Toggle auto renew -->
            <form method="POST" class="toggle-form">
                <input type="hidden" name="action" value="toggle_auto_renew">
                <label class="auto-toggle-row">
                    <span>Tự động gia hạn</span>
                    <label class="switch">
                        <input type="checkbox" name="auto_renew" value="1"
                               <?php echo $active['tu_dong_gia_han'] ? 'checked' : ''; ?>
                               onchange="this.form.submit()">
                        <span class="switch-slider"></span>
                    </label>
                </label>
            </form>

            <!-- NÚT HÀNH ĐỘNG -->
            <div class="active-actions">
                <button class="btn-act btn-renew" onclick="document.getElementById('renew-section').scrollIntoView({behavior:'smooth'})">
                    <i class="fas fa-redo"></i> Gia hạn
                </button>
                <button class="btn-act btn-cancel-trigger" onclick="document.getElementById('cancel-modal').style.display='flex'">
                    <i class="fas fa-times-circle"></i> Hủy thẻ
                </button>
            </div>
        </div>

        <!-- QUYỀN LỢI ĐANG CÓ -->
        <div class="manage-card">
            <h2><i class="fas fa-star"></i> Quyền lợi của bạn</h2>
            <ul class="benefit-list-manage">
                <?php
                $bens = [
                    ['fas fa-book','Đọc truyện miễn phí không giới hạn', $active['doc_vo_han']],
                    ['fas fa-lock-open','Đọc truyện trả phí',            $active['doc_tra_phi']],
                    ['fas fa-tags','Giảm '.$active['giam_gia_mua'].'% mua sách', $active['giam_gia_mua']>0],
                    ['fas fa-bolt','Đọc trước chương mới',               $active['doc_truoc']],
                    ['fas fa-coins','Hệ số điểm x'.$active['he_so_diem'], true],
                ];
                foreach ($bens as $b):
                    $has = (bool)$b[2]; ?>
                <li class="<?php echo $has ? 'ben-yes' : 'ben-no'; ?>">
                    <i class="<?php echo $b[0]; ?>"></i>
                    <span><?php echo $b[1]; ?></span>
                    <i class="fas fa-<?php echo $has ? 'check' : 'times'; ?> ben-status"></i>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Quà tặng còn lại -->
            <?php if (!empty($rewards)): ?>
            <div class="rewards-section">
                <h3><i class="fas fa-gift"></i> Quà tặng chưa dùng</h3>
                <?php foreach ($rewards as $r): ?>
                <div class="reward-item">
                    <i class="fas fa-<?php echo ['diem'=>'coins','ma_giam_gia'=>'tag','sach_mien_phi'=>'book'][$r['loai_qua']] ?? 'gift'; ?>"></i>
                    <span><?php echo htmlspecialchars($r['gia_tri']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div><!-- /.manage-grid -->

    <!-- ===== GIA HẠN / NÂNG-HẠ CẤP ===== -->
    <div class="manage-card" id="renew-section">
        <h2><i class="fas fa-redo"></i> Gia hạn / Thay đổi gói</h2>
        <div class="renew-packages">
            <?php foreach ($all_packages as $pkg):
                $is_current = ($pkg['id_package'] == $active['id_package']);
            ?>
            <div class="renew-pkg <?php echo $is_current ? 'renew-current' : ''; ?>">
                <div class="renew-pkg-name"><?php echo htmlspecialchars($pkg['ten_goi']); ?></div>
                <div class="renew-pkg-price"><?php echo number_format($pkg['gia_thang'],0,',','.'); ?>đ<span>/tháng</span></div>
                <?php if ($is_current): ?>
                    <a href="subscribe.php?package=<?php echo $pkg['id_package']; ?>&cycle=month" class="btn-renew-pkg btn-renew-same">
                        <i class="fas fa-redo"></i> Gia hạn gói này
                    </a>
                <?php elseif ($pkg['sort_order'] > $active['sort_order']): ?>
                    <a href="subscribe.php?package=<?php echo $pkg['id_package']; ?>&cycle=month" class="btn-renew-pkg btn-upgrade-pkg">
                        <i class="fas fa-arrow-up"></i> Nâng cấp
                    </a>
                <?php else: ?>
                    <button class="btn-renew-pkg btn-downgrade-pkg"
                            onclick="confirmDowngrade(<?php echo $pkg['id_package']; ?>, '<?php echo htmlspecialchars($pkg['ten_goi']); ?>')">
                        <i class="fas fa-arrow-down"></i> Hạ cấp
                    </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="renew-note">
            <i class="fas fa-info-circle"></i>
            Khi nâng cấp, giá trị còn lại của gói hiện tại sẽ được trừ vào giá gói mới (pro-rata).
            Khi hạ cấp, gói mới áp dụng từ kỳ thanh toán tiếp theo — bạn vẫn dùng gói cũ đến hết chu kỳ.
        </p>
    </div>

    <?php else: ?>
    <!-- Không có gói active -->
    <div class="manage-card no-membership">
        <i class="fas fa-id-card-alt"></i>
        <h2>Bạn chưa có gói thành viên nào đang hoạt động</h2>
        <p>Đăng ký ngay để tận hưởng các đặc quyền độc quyền!</p>
        <a href="index.php" class="btn-act btn-renew">
            <i class="fas fa-crown"></i> Chọn gói ngay
        </a>
    </div>
    <?php endif; ?>

    <!-- ===== LỊCH SỬ THANH TOÁN ===== -->
    <?php if (!empty($history)): ?>
    <div class="manage-card">
        <h2><i class="fas fa-history"></i> Lịch sử đăng ký</h2>
        <div class="history-table-wrap">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Gói</th>
                        <th>Chu kỳ</th>
                        <th>Số tiền</th>
                        <th>Bắt đầu</th>
                        <th>Hết hạn</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h):
                        $st_label = ['active'=>'Đang dùng','expired'=>'Hết hạn','cancelled'=>'Đã hủy','pending'=>'Chờ xử lý'][$h['trang_thai']] ?? $h['trang_thai'];
                        $st_class = ['active'=>'st-active','expired'=>'st-expired','cancelled'=>'st-cancelled','pending'=>'st-pending'][$h['trang_thai']] ?? '';
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($h['ten_goi']); ?></strong></td>
                        <td><?php echo ['month'=>'Tháng','quarter'=>'Quý','year'=>'Năm'][$h['chu_ky']]; ?></td>
                        <td><?php echo number_format($h['so_tien'],0,',','.'); ?>đ</td>
                        <td><?php echo date('d/m/Y', strtotime($h['ngay_bat_dau'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($h['ngay_het_han'])); ?></td>
                        <td><span class="status-tag <?php echo $st_class; ?>"><?php echo $st_label; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /.manage-wrapper -->

<!-- MODAL HỦY THẺ -->
<div class="modal-overlay" id="cancel-modal" style="display:none">
    <div class="modal-box">
        <button class="modal-close" onclick="document.getElementById('cancel-modal').style.display='none'">
            <i class="fas fa-times"></i>
        </button>
        <div class="cancel-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h2>Bạn chắc muốn hủy thẻ?</h2>
        <ul class="cancel-warnings">
            <li><i class="fas fa-times"></i> Mất tất cả đặc quyền sau khi hết chu kỳ</li>
            <li><i class="fas fa-times"></i> Không hoàn tiền</li>
            <li><i class="fas fa-check"></i> Vẫn dùng được đến <strong><?php echo $active ? date('d/m/Y', strtotime($active['ngay_het_han'])) : ''; ?></strong></li>
        </ul>
        <form method="POST">
            <input type="hidden" name="action" value="cancel">
            <label class="cancel-reason-label">Lý do hủy (giúp chúng tôi cải thiện):</label>
            <select name="ly_do" class="cancel-select">
                <option value="Quá đắt">Quá đắt</option>
                <option value="Không dùng nhiều">Không dùng nhiều</option>
                <option value="Tìm được dịch vụ tốt hơn">Tìm được dịch vụ tốt hơn</option>
                <option value="Vấn đề kỹ thuật">Vấn đề kỹ thuật</option>
                <option value="Khác">Khác</option>
            </select>
            <div class="cancel-btns">
                <button type="submit" class="btn-act btn-cancel-confirm">
                    <i class="fas fa-times-circle"></i> Xác nhận hủy
                </button>
                <button type="button" class="btn-act btn-keep"
                        onclick="document.getElementById('cancel-modal').style.display='none'">
                    <i class="fas fa-heart"></i> Giữ lại thẻ
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL HẠ CẤP -->
<div class="modal-overlay" id="downgrade-modal" style="display:none">
    <div class="modal-box">
        <button class="modal-close" onclick="document.getElementById('downgrade-modal').style.display='none'">
            <i class="fas fa-times"></i>
        </button>
        <h2>Xác nhận hạ cấp gói</h2>
        <p id="downgrade-msg" style="color:#747d8c;margin:14px 0;font-size:.95rem;"></p>
        <p style="font-size:.88rem;color:#a4b0be;">
            Gói mới sẽ được áp dụng từ kỳ thanh toán tiếp theo. Bạn vẫn giữ gói hiện tại đến hết hạn.
        </p>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <a id="downgrade-link" href="#" class="btn-act btn-cancel-confirm" style="text-align:center;flex:1">
                <i class="fas fa-arrow-down"></i> Xác nhận hạ cấp
            </a>
            <button class="btn-act btn-keep" style="flex:1"
                    onclick="document.getElementById('downgrade-modal').style.display='none'">
                Hủy bỏ
            </button>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
function confirmDowngrade(pkgId, pkgName) {
    document.getElementById('downgrade-msg').textContent =
        `Bạn muốn hạ xuống gói "${pkgName}" từ kỳ tiếp theo?`;
    document.getElementById('downgrade-link').href =
        `subscribe.php?package=${pkgId}&cycle=month&downgrade=1`;
    document.getElementById('downgrade-modal').style.display = 'flex';
}
// Đóng modal khi click ngoài
['cancel-modal','downgrade-modal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>