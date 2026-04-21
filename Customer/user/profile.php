<?php
// Customer/user/profile.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../include/db.php';
require_once '../../include/function.php';

$db      = new Database();
$user_id = $_SESSION['user_id'];
$message = '';
$message_type = 'success';

// CẬP NHẬT THÔNG TIN CÁ NHÂN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['TENTAIKHOAN'])) {
    $ten_taikhoan = trim($_POST['TENTAIKHOAN'] ?? '');
    $email        = trim($_POST['EMAIL']       ?? '');
    $sdt          = trim($_POST['SDT']         ?? '');
    $sex          = trim($_POST['GIOITINH']    ?? '');

    if (empty($ten_taikhoan) || empty($email)) {
        $message      = 'Tên tài khoản và Email không được để trống.';
        $message_type = 'danger';
    } else {
        try {
            $db->query('UPDATE taikhoan SET TENTAIKHOAN = :TEN, EMAIL = :EMAIL, SDT = :SDT, GIOITINH = :SEX WHERE ID_TAIKHOAN = :ID');
            $db->bind(':TEN',   $ten_taikhoan);
            $db->bind(':EMAIL', $email);
            $db->bind(':SDT',   $sdt);
            $db->bind(':SEX',   $sex);
            $db->bind(':ID',    $user_id);
            if ($db->execute()) {
                $_SESSION['username'] = $ten_taikhoan;
                $message      = 'Cập nhật thông tin thành công!';
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message      = 'Lỗi: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// CẬP NHẬT ẢNH ĐẠI DIỆN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_avatar') {
    try {
        if (empty($user_id)) {
            throw new Exception('Không xác định được ID người dùng.');
        }
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Vui lòng chọn một tệp ảnh hợp lệ.');
        }
        $db->query('SELECT ANH FROM taikhoan WHERE ID_TAIKHOAN = :ID');
        $db->bind(':ID', $user_id);
        $old_image = $db->single()['ANH'] ?? null;
        if ($old_image) {
            $old_path = __DIR__ . '/../../' . $old_image;
            if (file_exists($old_path)) unlink($old_path);
        }
        $target_path = uploadImage($_FILES['avatar'], 'taikhoan');
        $db->query('UPDATE taikhoan SET ANH = :ANH WHERE ID_TAIKHOAN = :ID');
        $db->bind(':ANH', $target_path);
        $db->bind(':ID',  $user_id);
        $db->execute();
        $_SESSION['ANH'] = $target_path;
        $message      = 'Cập nhật ảnh đại diện thành công!';
        $message_type = 'success';
    } catch (Exception $e) {
        $message      = $e->getMessage();
        $message_type = 'danger';
    }
}

// ĐỔI MẬT KHẨU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $pw_current = $_POST['pw_current'] ?? '';
    $pw_new     = $_POST['pw_new']     ?? '';
    $pw_confirm = $_POST['pw_confirm'] ?? '';

    $db->query('SELECT MATKHAU FROM taikhoan WHERE ID_TAIKHOAN = :ID');
    $db->bind(':ID', $user_id);
    $row = $db->single();

    if (!password_verify($pw_current, $row['MATKHAU'])) {
        $message      = 'Mật khẩu hiện tại không đúng.';
        $message_type = 'danger';
    } elseif (strlen($pw_new) < 6) {
        $message      = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        $message_type = 'danger';
    } elseif ($pw_new !== $pw_confirm) {
        $message      = 'Xác nhận mật khẩu không khớp.';
        $message_type = 'danger';
    } else {
        $db->query('UPDATE taikhoan SET MATKHAU = :PW WHERE ID_TAIKHOAN = :ID');
        $db->bind(':PW', password_hash($pw_new, PASSWORD_DEFAULT));
        $db->bind(':ID', $user_id);
        $db->execute();
        $message      = 'Đổi mật khẩu thành công!';
        $message_type = 'success';
    }
}

// LẤY THÔNG TIN MỚI NHẤT
$db->query('SELECT * FROM taikhoan WHERE ID_TAIKHOAN = :ID');
$db->bind(':ID', $user_id);
$user = $db->single();

// LẤY GÓI MEMBERSHIP HIỆN TẠI
$db->query("
    SELECT um.*, mp.ten_goi, mp.doc_vo_han, mp.doc_tra_phi,
           mp.giam_gia_mua, mp.doc_truoc, mp.he_so_diem, mp.sort_order
    FROM user_membership um
    JOIN membership_package mp ON um.id_package = mp.id_package
    WHERE um.id_taikhoan = :uid
      AND um.trang_thai = 'active'
      AND um.ngay_het_han >= CURDATE()
    ORDER BY um.ngay_het_han DESC
    LIMIT 1
");
$db->bind(':uid', $user_id);
$membership = $db->single();

// LẤY LỊCH SỬ ĐỌC TRUYỆN (10 gần nhất)
$db->query("
    SELECT td.so_chuong, td.ngay_doc, m.manga_name, m.slug, m.anh
    FROM tiendo_doc td
    JOIN manga m ON td.id_manga = m.id_manga
    WHERE td.id_taikhoan = :uid
    ORDER BY td.ngay_doc DESC
    LIMIT 10
");
$db->bind(':uid', $user_id);
$reading_history = $db->resultSet();

// LẤY LỊCH SỬ ĐƠN HÀNG (10 gần nhất)
$db->query("
    SELECT id_order, ngay_dat, tong_tien, trang_thai_thanh_toan
    FROM don_hang
    WHERE id_taikhoan = :uid
    ORDER BY ngay_dat DESC
    LIMIT 10
");
$db->bind(':uid', $user_id);
$order_history = $db->resultSet();

// HEADER
$base_url     = '../';
$page_title   = 'Thông tin cá nhân - Truyện Hay';
$current_page = '';
require_once '../includes/header.php';
?>

<main class="main-content">
<div class="profile-container" style="background: var(--color-background-secondary); padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 800px; margin: 30px auto;">

    <h2 style="border-bottom: 2px solid var(--color-border-tertiary); padding-bottom: 10px; margin-bottom: 25px;">
        <i class="fas fa-user-circle"></i> Thông tin cá nhân
    </h2>

    <?php if (!empty($message)): ?>
    <div style="background: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>;
                color:       <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>;
                padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 30px;">

        <!-- ẢNH ĐẠI DIỆN -->
        <div style="flex: 1; text-align: center; min-width: 200px;">
            <div style="position: relative; display: inline-block; cursor: pointer;"
                 onclick="document.getElementById('avatarInput').click();"
                 title="Nhấp để đổi ảnh đại diện">
                <img src="<?php echo !empty($user['ANH']) ? htmlspecialchars($user['ANH']) : '../assets/img/default-avatar.jpg'; ?>"
                     id="avatarPreview"
                     style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--color-border-tertiary); transition: 0.3s;"
                     onmouseover="this.style.opacity='0.75'; this.style.border='4px solid var(--primary)'"
                     onmouseout="this.style.opacity='1'; this.style.border='4px solid var(--color-border-tertiary)'"
                     onerror="this.src='../assets/img/default-avatar.jpg'">
                <div style="position: absolute; bottom: 5px; right: 5px; background: var(--primary, #e74c3c); color: #fff; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff;">
                    <i class="fas fa-camera"></i>
                </div>
            </div>

            <form id="avatarForm" method="POST" enctype="multipart/form-data" action="profile.php" style="display: none;">
                <input type="hidden" name="action" value="update_avatar">
                <input type="file" name="avatar" id="avatarInput" accept="image/*" onchange="submitAvatarForm()">
            </form>

            <div style="margin-top: 15px;">
                <p style="margin-bottom: 8px; color: var(--color-text-secondary); font-weight: 500;">
                    ID: #<?php echo $user['ID_TAIKHOAN']; ?>
                </p>
                <?php
                $mem_rank    = $membership ? strtolower($membership['ten_goi']) : 'free';
                $rank_colors = ['basic'=>'#3498db','premium'=>'#ff4757','vip'=>'#f39c12','free'=>'#95a5a6'];
                $rank_icons  = ['basic'=>'fa-star','premium'=>'fa-crown','vip'=>'fa-gem','free'=>'fa-book-open'];
                $rcolor = $rank_colors[$mem_rank] ?? '#95a5a6';
                $ricon  = $rank_icons[$mem_rank]  ?? 'fa-user';
                ?>
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 18px;background:<?php echo $rcolor; ?>;color:#fff;border-radius:20px;font-size:.85em;font-weight:700;">
                    <i class="fas <?php echo $ricon; ?>"></i>
                    <?php echo $membership ? htmlspecialchars($membership['ten_goi']).' Member' : 'Free Member'; ?>
                </span>
                <?php if (($user['diem_tich_luy'] ?? 0) > 0): ?>
                <p style="margin-top:10px;font-size:.85em;color:var(--color-text-secondary);">
                    <i class="fas fa-coins" style="color:#f39c12"></i>
                    <?php echo number_format($user['diem_tich_luy'],0,',','.'); ?> điểm tích lũy
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- FORM THÔNG TIN -->
        <div style="flex: 2; min-width: 300px;">
            <form method="POST" action="profile.php">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tên tài khoản</label>
                    <input type="text" name="TENTAIKHOAN"
                           value="<?php echo htmlspecialchars($user['TENTAIKHOAN']); ?>"
                           style="width: 100%; padding: 10px; border: 1px solid var(--color-border-tertiary); border-radius: 5px; outline: none; background: var(--color-background-primary); color: var(--color-text-primary);"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--color-border-tertiary)'">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email</label>
                    <input type="email" name="EMAIL"
                           value="<?php echo htmlspecialchars($user['EMAIL'] ?? ''); ?>"
                           style="width: 100%; padding: 10px; border: 1px solid var(--color-border-tertiary); border-radius: 5px; outline: none; background: var(--color-background-primary); color: var(--color-text-primary);"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--color-border-tertiary)'">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Giới tính</label>
                    <select name="GIOITINH"
                            style="width: 100%; padding: 10px; border: 1px solid var(--color-border-tertiary); border-radius: 5px; outline: none; background: var(--color-background-primary); color: var(--color-text-primary); cursor: pointer;"
                            onfocus="this.style.borderColor='var(--primary)'"
                            onblur="this.style.borderColor='var(--color-border-tertiary)'">
                        <option value="">-- Chọn giới tính --</option>
                        <option value="Nam"  <?php echo ($user['GIOITINH'] === 'Nam')  ? 'selected' : ''; ?>>Nam</option>
                        <option value="Nữ"   <?php echo ($user['GIOITINH'] === 'Nữ')   ? 'selected' : ''; ?>>Nữ</option>
                        <option value="Khác" <?php echo ($user['GIOITINH'] === 'Khác') ? 'selected' : ''; ?>>Khác</option>
                    </select>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Số điện thoại</label>
                    <input type="text" name="SDT"
                           value="<?php echo htmlspecialchars($user['SDT'] ?? ''); ?>"
                           placeholder="Nhập số điện thoại"
                           style="width: 100%; padding: 10px; border: 1px solid var(--color-border-tertiary); border-radius: 5px; outline: none; background: var(--color-background-primary); color: var(--color-text-primary);"
                           onfocus="this.style.borderColor='var(--primary)'"
                           onblur="this.style.borderColor='var(--color-border-tertiary)'">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--color-text-secondary);">Ngày tham gia</label>
                    <input type="text" value="<?php echo $user['NGAYLAP']; ?>" disabled
                           style="width: 100%; padding: 10px; border: 1px solid var(--color-border-tertiary); border-radius: 5px; background: var(--color-background-tertiary); color: var(--color-text-secondary); cursor: not-allowed;">
                </div>
                <div style="padding-top: 10px; border-top: 1px solid var(--color-border-tertiary);">
                    <button type="submit"
                            style="background: #28a745; color: #fff; border: none; padding: 12px 30px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.3s;"
                            onmouseover="this.style.background='#218838'"
                            onmouseout="this.style.background='#28a745'">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                    <a href="../index.php" style="text-decoration: none; color: var(--color-text-secondary); margin-left: 20px; font-size: 0.9em;">
                        Hủy bỏ
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MEMBERSHIP CARD ===== -->
<div style="background:var(--color-background-secondary);padding:30px;border-radius:15px;box-shadow:0 4px 6px rgba(0,0,0,.1);max-width:800px;margin:20px auto;">
    <h2 style="border-bottom:2px solid var(--color-border-tertiary);padding-bottom:10px;margin-bottom:20px;">
        <i class="fas fa-crown" style="color:#f39c12"></i> Gói thành viên
    </h2>
    <?php if ($membership): ?>
    <?php
    $days_left  = max(0, (new DateTime())->diff(new DateTime($membership['ngay_het_han']))->days);
    $total_days = ['month'=>30,'quarter'=>90,'year'=>365][$membership['chu_ky']] ?? 30;
    $bar_pct    = min(100, round($days_left / $total_days * 100));
    $bar_color  = $days_left <= 7 ? '#ff4757' : ($days_left <= 15 ? '#f39c12' : '#2ed573');
    ?>
    <div style="display:flex;flex-wrap:wrap;gap:20px;align-items:flex-start;">
        <div style="flex:1;min-width:220px;">
            <div style="display:inline-flex;align-items:center;gap:8px;padding:8px 20px;background:<?php echo $rcolor; ?>;color:#fff;border-radius:25px;font-weight:800;font-size:1rem;margin-bottom:16px;">
                <i class="fas <?php echo $rank_icons[$mem_rank]; ?>"></i>
                <?php echo htmlspecialchars($membership['ten_goi']); ?> Member
            </div>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;font-size:.85rem;color:var(--color-text-secondary);margin-bottom:6px;">
                    <span>Còn <strong style="color:<?php echo $bar_color; ?>"><?php echo $days_left; ?> ngày</strong></span>
                    <span>Hết hạn: <strong><?php echo date('d/m/Y', strtotime($membership['ngay_het_han'])); ?></strong></span>
                </div>
                <div style="background:#e0e0e0;border-radius:6px;height:8px;overflow:hidden;">
                    <div style="width:<?php echo $bar_pct; ?>%;height:100%;background:<?php echo $bar_color; ?>;border-radius:6px;transition:.4s;"></div>
                </div>
                <?php if ($days_left <= 7): ?>
                <p style="color:#ff4757;font-size:.8rem;margin-top:6px;"><i class="fas fa-exclamation-triangle"></i> Sắp hết hạn! Gia hạn ngay.</p>
                <?php endif; ?>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:.85rem;">
                <div style="background:var(--color-background-primary);padding:10px;border-radius:8px;text-align:center;">
                    <div style="color:var(--color-text-secondary);margin-bottom:3px;">Chu kỳ</div>
                    <strong><?php echo ['month'=>'Tháng','quarter'=>'Quý','year'=>'Năm'][$membership['chu_ky']]; ?></strong>
                </div>
                <div style="background:var(--color-background-primary);padding:10px;border-radius:8px;text-align:center;">
                    <div style="color:var(--color-text-secondary);margin-bottom:3px;">Đã trả</div>
                    <strong><?php echo number_format($membership['so_tien'],0,',','.'); ?>đ</strong>
                </div>
                <div style="background:var(--color-background-primary);padding:10px;border-radius:8px;text-align:center;">
                    <div style="color:var(--color-text-secondary);margin-bottom:3px;">Hệ số điểm</div>
                    <strong style="color:#f39c12;">x<?php echo $membership['he_so_diem']; ?></strong>
                </div>
                <div style="background:var(--color-background-primary);padding:10px;border-radius:8px;text-align:center;">
                    <div style="color:var(--color-text-secondary);margin-bottom:3px;">Tự động gia hạn</div>
                    <strong style="color:<?php echo $membership['tu_dong_gia_han'] ? '#2ed573' : '#ff4757'; ?>">
                        <?php echo $membership['tu_dong_gia_han'] ? 'Bật' : 'Tắt'; ?>
                    </strong>
                </div>
            </div>
        </div>
        <div style="flex:1;min-width:200px;">
            <p style="font-weight:700;margin-bottom:10px;color:var(--color-text-primary);">Quyền lợi của bạn:</p>
            <?php
            $benefits = [
                ['fas fa-book',      'Đọc không giới hạn',   $membership['doc_vo_han']],
                ['fas fa-lock-open', 'Đọc truyện trả phí',   $membership['doc_tra_phi']],
                ['fas fa-tags',      'Giảm '.$membership['giam_gia_mua'].'% mua sách', $membership['giam_gia_mua'] > 0],
                ['fas fa-bolt',      'Đọc trước chương mới', $membership['doc_truoc']],
            ];
            foreach ($benefits as $b): $has = (bool)$b[2]; ?>
            <div style="display:flex;align-items:center;gap:8px;padding:5px 0;font-size:.88rem;color:<?php echo $has ? 'var(--color-text-primary)' : '#aaa'; ?>;">
                <i class="fas fa-<?php echo $has ? 'check' : 'times'; ?>" style="color:<?php echo $has ? '#2ed573' : '#ff4757'; ?>;width:14px;"></i>
                <i class="<?php echo $b[0]; ?>" style="width:16px;opacity:.7;"></i>
                <?php echo $b[1]; ?>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:18px;display:flex;flex-direction:column;gap:8px;">
                <a href="../membership/manage.php"
                   style="display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 20px;background:#2f3542;color:#fff;border-radius:8px;text-decoration:none;font-weight:700;font-size:.88rem;"
                   onmouseover="this.style.background='#57606f'"
                   onmouseout="this.style.background='#2f3542'">
                    <i class="fas fa-cog"></i> Quản lý thẻ
                </a>
                <a href="../membership/subscribe.php?package=<?php echo $membership['id_package']; ?>&cycle=<?php echo $membership['chu_ky']; ?>"
                   style="display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 20px;background:<?php echo $rcolor; ?>;color:#fff;border-radius:8px;text-decoration:none;font-weight:700;font-size:.88rem;"
                   onmouseover="this.style.opacity='.85'"
                   onmouseout="this.style.opacity='1'">
                    <i class="fas fa-redo"></i> Gia hạn ngay
                </a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:30px 0;">
        <i class="fas fa-crown" style="font-size:3rem;color:#ddd;margin-bottom:16px;display:block;"></i>
        <p style="color:var(--color-text-secondary);margin-bottom:18px;">Bạn chưa có gói thành viên nào.<br>Đăng ký để tận hưởng đặc quyền đọc truyện không giới hạn!</p>
        <a href="../membership/index.php"
           style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:linear-gradient(135deg,#f39c12,#e67e22);color:#fff;border-radius:25px;text-decoration:none;font-weight:800;font-size:.95rem;box-shadow:0 4px 14px rgba(243,156,18,.35);">
            <i class="fas fa-crown"></i> Xem các gói thành viên
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- ===== ĐỔI MẬT KHẨU ===== -->
<div style="background:var(--color-background-secondary);padding:30px;border-radius:15px;box-shadow:0 4px 6px rgba(0,0,0,.1);max-width:800px;margin:20px auto;">
    <h2 style="border-bottom:2px solid var(--color-border-tertiary);padding-bottom:10px;margin-bottom:20px;">
        <i class="fas fa-lock"></i> Đổi mật khẩu
    </h2>
    <form method="POST" action="profile.php" style="max-width:400px;">
        <input type="hidden" name="action" value="change_password">
        <div style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;font-weight:bold;">Mật khẩu hiện tại</label>
            <input type="password" name="pw_current" required placeholder="Nhập mật khẩu hiện tại"
                   style="width:100%;padding:10px;border:1px solid var(--color-border-tertiary);border-radius:5px;outline:none;background:var(--color-background-primary);color:var(--color-text-primary);"
                   onfocus="this.style.borderColor='var(--primary)'"
                   onblur="this.style.borderColor='var(--color-border-tertiary)'">
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;font-weight:bold;">Mật khẩu mới <small style="color:#aaa;font-weight:normal;">(ít nhất 6 ký tự)</small></label>
            <input type="password" name="pw_new" required placeholder="Nhập mật khẩu mới"
                   style="width:100%;padding:10px;border:1px solid var(--color-border-tertiary);border-radius:5px;outline:none;background:var(--color-background-primary);color:var(--color-text-primary);"
                   onfocus="this.style.borderColor='var(--primary)'"
                   onblur="this.style.borderColor='var(--color-border-tertiary)'">
        </div>
        <div style="margin-bottom:20px;">
            <label style="display:block;margin-bottom:5px;font-weight:bold;">Xác nhận mật khẩu mới</label>
            <input type="password" name="pw_confirm" required placeholder="Nhập lại mật khẩu mới"
                   style="width:100%;padding:10px;border:1px solid var(--color-border-tertiary);border-radius:5px;outline:none;background:var(--color-background-primary);color:var(--color-text-primary);"
                   onfocus="this.style.borderColor='var(--primary)'"
                   onblur="this.style.borderColor='var(--color-border-tertiary)'">
        </div>
        <button type="submit"
                style="background:#3498db;color:#fff;border:none;padding:12px 30px;border-radius:5px;cursor:pointer;font-weight:bold;"
                onmouseover="this.style.background='#2980b9'"
                onmouseout="this.style.background='#3498db'">
            <i class="fas fa-key"></i> Đổi mật khẩu
        </button>
    </form>
</div>

<!-- ===== LỊCH SỬ ĐỌC TRUYỆN ===== -->
<div style="background:var(--color-background-secondary);padding:30px;border-radius:15px;box-shadow:0 4px 6px rgba(0,0,0,.1);max-width:800px;margin:20px auto;">
    <h2 style="border-bottom:2px solid var(--color-border-tertiary);padding-bottom:10px;margin-bottom:20px;">
        <i class="fas fa-book-open" style="color:#2ed573"></i> Lịch sử đọc truyện
    </h2>
    <?php if (empty($reading_history)): ?>
        <p style="color:var(--color-text-secondary);text-align:center;padding:20px 0;">
            Bạn chưa đọc truyện nào. <a href="../manga/list.php" style="color:var(--primary);">Khám phá ngay</a>
        </p>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($reading_history as $r): ?>
            <a href="../truyen/<?php echo htmlspecialchars($r['slug']); ?>/chuong-<?php echo $r['so_chuong']; ?>"
               style="display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;background:var(--color-background-primary);text-decoration:none;color:inherit;">
                <img src="<?php echo htmlspecialchars($r['anh']); ?>" alt=""
                     style="width:45px;height:60px;object-fit:cover;border-radius:4px;flex-shrink:0;"
                     onerror="this.src='../assets/img/no-cover.jpg'">
                <div>
                    <p style="font-weight:700;margin:0 0 4px;color:var(--color-text-primary);">
                        <?php echo htmlspecialchars($r['manga_name']); ?>
                    </p>
                    <p style="font-size:.85rem;color:var(--color-text-secondary);margin:0;">
                        Chương <?php echo $r['so_chuong']; ?> &nbsp;·&nbsp;
                        <?php echo date('d/m/Y', strtotime($r['ngay_doc'])); ?>
                    </p>
                </div>
            </a>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ===== LỊCH SỬ ĐƠN HÀNG ===== -->
<div style="background:var(--color-background-secondary);padding:30px;border-radius:15px;box-shadow:0 4px 6px rgba(0,0,0,.1);max-width:800px;margin:20px auto 40px;">
    <h2 style="border-bottom:2px solid var(--color-border-tertiary);padding-bottom:10px;margin-bottom:20px;">
        <i class="fas fa-shopping-bag" style="color:#ff4757"></i> Lịch sử đơn hàng
    </h2>
    <?php if (empty($order_history)): ?>
        <p style="color:var(--color-text-secondary);text-align:center;padding:20px 0;">
            Bạn chưa có đơn hàng nào. <a href="../shop/index.php" style="color:var(--primary);">Ghé Shop ngay</a>
        </p>
    <?php else: ?>
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
                <tr style="background:var(--color-background-tertiary);color:var(--color-text-secondary);text-align:left;">
                    <th style="padding:10px;">Mã ĐH</th>
                    <th style="padding:10px;">Ngày đặt</th>
                    <th style="padding:10px;text-align:right;">Tổng tiền</th>
                    <th style="padding:10px;text-align:center;">Thanh toán</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($order_history as $o): ?>
                <tr style="border-bottom:1px solid var(--color-border-tertiary);">
                    <td style="padding:10px;font-weight:700;">#<?php echo $o['id_order']; ?></td>
                    <td style="padding:10px;color:var(--color-text-secondary);"><?php echo date('d/m/Y', strtotime($o['ngay_dat'])); ?></td>
                    <td style="padding:10px;text-align:right;font-weight:700;"><?php echo number_format($o['tong_tien'],0,',','.'); ?>đ</td>
                    <td style="padding:10px;text-align:center;">
                        <?php if ($o['trang_thai_thanh_toan']): ?>
                            <span style="color:#28a745;font-weight:700;"><i class="fas fa-check-circle"></i> Đã thanh toán</span>
                        <?php else: ?>
                            <span style="color:#f39c12;font-weight:700;"><i class="fas fa-clock"></i> Chờ thanh toán</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

</main>

<?php require_once '../includes/footer.php'; ?>

<script>
function submitAvatarForm() {
    const fileInput = document.getElementById('avatarInput');
    if (fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(fileInput.files[0]);
        document.getElementById('avatarForm').submit();
    }
}
</script>