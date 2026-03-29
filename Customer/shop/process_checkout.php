<?php
session_start();
require_once '../../include/db.php'; 

// Chặn nếu giỏ hàng trống hoặc không phải POST
if (empty($_SESSION['cart']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$db = new Database();

$fullname = trim($_POST['fullname'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$address  = trim($_POST['address'] ?? '');
$note     = trim($_POST['note'] ?? '');
$payment  = trim($_POST['payment_method'] ?? 'cod');

if (empty($fullname) || empty($phone) || empty($address)) {
    die('<script>alert("Vui lòng nhập đủ thông tin!"); history.back();</script>');
}

// Tính tổng tiền từ Session Giỏ hàng
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['gia_ban'] * $item['qty'];
}
$ship = ($subtotal >= 300000) ? 0 : 30000;
$tong_tien = $subtotal + $ship;
// Kiểm tra tồn kho cho từng sản phẩm trong giỏ hàng trước khi tạo đơn hàng
foreach ($_SESSION['cart'] as $id_sp => $item) {
    // Truy vấn lấy số lượng thực tế trong kho của sản phẩm này
    $db->query("SELECT so_luong_kho FROM sanpham_manga WHERE id_spmanga = :id");
    $db->bind(':id', $id_sp);
    $check_kho = $db->single();
    
    // Nếu kho rỗng hoặc số lượng khách mua lớn hơn số lượng trong kho
    if (!$check_kho || $check_kho['so_luong_kho'] < $item['qty']) {
        $ten_truyen = $item['manga_name']; 
        $ton_kho = $check_kho ? $check_kho['so_luong_kho'] : 0;
        
        // echo lỗi
        echo "<script>
            alert('Xin lỗi! Truyện \"$ten_truyen\" chỉ còn $ton_kho cuốn trong kho. Vui lòng giảm số lượng!'); 
            window.location.href = 'cart.php';
        </script>";
        exit; 
    }
}
// Gộp thông tin
$pt_text = ($payment === 'bank') ? 'Chuyển khoản' : 'COD';
$dia_chi_giao_hang = "Nhận: $fullname | SĐT: $phone | ĐC: $address | PTTT: $pt_text";
if ($note !== '') $dia_chi_giao_hang .= " | Note: $note";
$id_taikhoan = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 2; 

try {
    $db->query("START TRANSACTION");
    $db->execute();

    // 1. Lưu vào bảng don_hang
    $db->query("INSERT INTO don_hang (id_taikhoan, tong_tien, trang_thai_thanh_toan, dia_chi_giao_hang, ngay_dat) 
                VALUES (:id_taikhoan, :tong_tien, 0, :dia_chi_giao_hang, NOW())");
    $db->bind(':id_taikhoan', $id_taikhoan);
    $db->bind(':tong_tien', $tong_tien);
    $db->bind(':dia_chi_giao_hang', $dia_chi_giao_hang);
    $db->execute();

    // Lấy ID đơn hàng vừa tạo
    $db->query("SELECT LAST_INSERT_ID() AS new_id");
    $id_order = $db->single()['new_id'];

    // 2. Lặp qua Giỏ hàng để lưu vào bảng chi_tiet_don_hang
    $sql_detail = "INSERT INTO chi_tiet_don_hang (id_order, id_spmanga, so_luong, gia_tai_thoi_diem_mua) 
                   VALUES (:id_order, :id_spmanga, :so_luong, :gia)";
                   
    $sql_update_stock = "UPDATE sanpham_manga 
                         SET so_luong_kho = so_luong_kho - :so_luong_tru 
                         WHERE id_spmanga = :id_spmanga_tru";
    
    foreach ($_SESSION['cart'] as $id_spmanga => $item) {
        $db->query($sql_detail);
        $db->bind(':id_order', $id_order);
        $db->bind(':id_spmanga', $id_spmanga);
        $db->bind(':so_luong', $item['qty']);
        $db->bind(':gia', $item['gia_ban']);
        $db->execute();

        $db->query($sql_update_stock);
        $db->bind(':so_luong_tru', $item['qty']);
        $db->bind(':id_spmanga_tru', $id_spmanga);
        $db->execute();
    }

    // Commit transaction 
    $db->query("COMMIT");
    $db->execute();

    // 3. XOÁ GIỎ HÀNG SAU KHI ĐẶT THÀNH CÔNG
    unset($_SESSION['cart']);
    header("Location: order_success.php?id=" . $id_order);
    exit;

} catch (PDOException $e) {
    $db->query("ROLLBACK");
    $db->execute();
    die("Lỗi SQL: " . $e->getMessage());
}
?>