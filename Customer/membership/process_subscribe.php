<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

require_once '../../include/db.php';
require_once '../../include/membership.php'; 

$db      = new Database();
$user_id = (int)$_SESSION['user_id'];

$id_package    = (int)($_POST['id_package']   ?? 0);
$cycle         = in_array($_POST['cycle'] ?? '', ['month','quarter','year']) ? $_POST['cycle'] : 'month';
$so_tien       = (float)($_POST['so_tien']    ?? 0);
$auto_renew    = ($_POST['auto_renew']  ?? '0') === '1' ? 1 : 0;
$pt_thanh_toan = in_array($_POST['pt_thanh_toan'] ?? '', ['qr','simulate']) ? $_POST['pt_thanh_toan'] : 'simulate';

if ($id_package === 0 || $so_tien < 0) {
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

$months_map = ['month'=>1,'quarter'=>3,'year'=>12];
$months     = $months_map[$cycle] ?? 1;

$ngay_bat_dau = date('Y-m-d');
$ngay_het_han = date('Y-m-d', strtotime("+{$months} months"));

$ma_giao_dich = 'MEM' . strtoupper(substr(uniqid('', true), -8));
$qua_tang = $package['qua_tang'] ? json_decode($package['qua_tang'], true) : null;

try {
    $db->beginTransaction();

    // 1. Huỷ gói cũ đang active
    $db->query("UPDATE user_membership SET trang_thai = 'cancelled' WHERE id_taikhoan = :uid AND trang_thai = 'active'");
    $db->bind(':uid', $user_id);
    $db->execute();

    // 2. Tạo membership mới
    $db->query("
        INSERT INTO user_membership
            (id_taikhoan, id_package, chu_ky, so_tien, ngay_bat_dau, ngay_het_han,
             trang_thai, tu_dong_gia_han, pt_thanh_toan, ma_giao_dich)
        VALUES
            (:uid, :pid, :cycle, :tien, :start, :end,
             'active', :auto, :pt, :ma)
    ");
    $db->bind(':uid',   $user_id);
    $db->bind(':pid',   $id_package);
    $db->bind(':cycle', $cycle);
    $db->bind(':tien',  $so_tien);
    $db->bind(':start', $ngay_bat_dau);
    $db->bind(':end',   $ngay_het_han);
    $db->bind(':auto',  $auto_renew);
    $db->bind(':pt',    $pt_thanh_toan);
    $db->bind(':ma',    $ma_giao_dich);
    $db->execute();
    $id_membership = $db->lastInsertId();

    // 3. Lưu quà tặng
    if ($qua_tang) {
        if (!empty($qua_tang['diem']) && $qua_tang['diem'] > 0) {
            $db->query("UPDATE taikhoan SET diem_tich_luy = diem_tich_luy + :diem WHERE ID_TAIKHOAN = :uid");
            $db->bind(':diem', (int)$qua_tang['diem']);
            $db->bind(':uid',  $user_id);
            $db->execute();

            $db->query("INSERT INTO membership_reward (id_membership, id_taikhoan, loai_qua, gia_tri) VALUES (:mid, :uid, 'diem', :val)");
            $db->bind(':mid', $id_membership);
            $db->bind(':uid', $user_id);
            $db->bind(':val', (string)$qua_tang['diem']);
            $db->execute();
        }
        if (!empty($qua_tang['ma_giam_gia'])) {
            $db->query("INSERT INTO membership_reward (id_membership, id_taikhoan, loai_qua, gia_tri) VALUES (:mid, :uid, 'ma_giam_gia', :val)");
            $db->bind(':mid', $id_membership);
            $db->bind(':uid', $user_id);
            $db->bind(':val', $qua_tang['ma_giam_gia']);
            $db->execute();
        }
        if (!empty($qua_tang['sach_mien_phi'])) {
            $db->query("INSERT INTO membership_reward (id_membership, id_taikhoan, loai_qua, gia_tri) VALUES (:mid, :uid, 'sach_mien_phi', '1 cuốn sách miễn phí')");
            $db->bind(':mid', $id_membership);
            $db->bind(':uid', $user_id);
            $db->execute();
        }
    }

    $db->commit();

    MembershipHelper::clearCache($user_id);

    $_SESSION['mem_success'] = [
        'id_membership' => $id_membership,
        'ten_goi'       => $package['ten_goi'],
        'ngay_het_han'  => $ngay_het_han,
        'so_tien'       => $so_tien,
        'pt'            => $pt_thanh_toan,
        'ma_giao_dich'  => $ma_giao_dich,
        'qua_tang'      => $qua_tang,
    ];

    header('Location: success.php');
    exit;

} catch (Exception $e) {
    $db->rollBack();
    error_log('process_subscribe error: ' . $e->getMessage());
    $_SESSION['mem_error'] = 'Đã xảy ra lỗi khi xử lý đăng ký. Vui lòng thử lại.';
    header('Location: subscribe.php?package=' . $id_package . '&cycle=' . $cycle);
    exit;
}