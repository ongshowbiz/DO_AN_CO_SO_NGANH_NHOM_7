<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/membership.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (empty($_SESSION['cart']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$db = new Database();

$fullname = trim($_POST['fullname'] ?? '');
$phone    = trim($_POST['phone']    ?? '');
$address  = trim($_POST['address']  ?? '');
$note     = trim($_POST['note']     ?? '');
$payment  = trim($_POST['payment_method'] ?? 'cod');

if (empty($fullname) || empty($phone) || empty($address)) {
    die('<script>alert("Vui lòng nhập đủ thông tin!"); history.back();</script>');
}

// Tính tổng tiền từ Session (giá đã áp dụng giảm giá membership)
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['gia_ban'] * $item['qty'];
}
$ship      = ($subtotal >= 300000) ? 0 : 30000;
$tong_tien = $subtotal + $ship;

// Kiểm tra tồn kho
foreach ($_SESSION['cart'] as $id_sp => $item) {
    $db->query("SELECT so_luong_kho FROM sanpham_manga WHERE id_spmanga = :id");
    $db->bind(':id', $id_sp);
    $check_kho = $db->single();

    if (!$check_kho || $check_kho['so_luong_kho'] < $item['qty']) {
        $ten_truyen = $item['manga_name'];
        $ton_kho    = $check_kho ? $check_kho['so_luong_kho'] : 0;
        echo "<script>alert('Xin lỗi! Truyện \"$ten_truyen\" chỉ còn $ton_kho cuốn!'); window.location.href='cart.php';</script>";
        exit;
    }
}

$pt_text           = ($payment === 'bank') ? 'Chuyển khoản' : 'COD';
$dia_chi_giao_hang = "Nhận: $fullname | SĐT: $phone | ĐC: $address | PTTT: $pt_text";
if ($note !== '') $dia_chi_giao_hang .= " | Note: $note";
$id_taikhoan = $_SESSION['user_id'];

try {
    $db->query("START TRANSACTION");
    $db->execute();

    // 1. Lưu đơn hàng
    $db->query("INSERT INTO don_hang (id_taikhoan, tong_tien, trang_thai_thanh_toan, dia_chi_giao_hang, ngay_dat)
                VALUES (:id_taikhoan, :tong_tien, 0, :dia_chi, NOW())");
    $db->bind(':id_taikhoan', $id_taikhoan);
    $db->bind(':tong_tien',   $tong_tien);
    $db->bind(':dia_chi',     $dia_chi_giao_hang);
    $db->execute();

    $db->query("SELECT LAST_INSERT_ID() AS new_id");
    $id_order = $db->single()['new_id'];

    // 2. Lưu chi tiết + cập nhật kho
    foreach ($_SESSION['cart'] as $id_spmanga => $item) {
        $db->query("INSERT INTO chi_tiet_don_hang (id_order, id_spmanga, so_luong, gia_tai_thoi_diem_mua)
                    VALUES (:id_order, :id_spmanga, :so_luong, :gia)");
        $db->bind(':id_order',   $id_order);
        $db->bind(':id_spmanga', $id_spmanga);
        $db->bind(':so_luong',   $item['qty']);
        $db->bind(':gia',        $item['gia_ban']); // giá đã giảm
        $db->execute();

        $db->query("UPDATE sanpham_manga SET so_luong_kho = so_luong_kho - :sl WHERE id_spmanga = :id");
        $db->bind(':sl', $item['qty']);
        $db->bind(':id', $id_spmanga);
        $db->execute();
    }

    // 3. Cộng điểm tích lũy theo hệ số membership
    // Cơ sở: cứ 10.000đ = 1 điểm cơ bản, rồi nhân hệ số gói
    $base_diem   = (int)floor($tong_tien / 10000);
    $diem_thuc   = MembershipHelper::calcDiem($base_diem, $id_taikhoan);

    if ($diem_thuc > 0) {
        $db->query("UPDATE taikhoan SET diem_tich_luy = diem_tich_luy + :d WHERE ID_TAIKHOAN = :uid");
        $db->bind(':d',   $diem_thuc);
        $db->bind(':uid', $id_taikhoan);
        $db->execute();
    }

    $db->query("COMMIT");
    $db->execute();

    unset($_SESSION['cart']);
    header("Location: order_success.php?id=" . $id_order);
    exit;

} catch (PDOException $e) {
    $db->query("ROLLBACK");
    $db->execute();
    die("Lỗi SQL: " . $e->getMessage());
}