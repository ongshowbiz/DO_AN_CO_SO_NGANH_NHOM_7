<?php
session_start();
require_once __DIR__ . '/../../include/db.php';
require_once __DIR__ . '/../../include/membership.php'; 

$db = new Database();

$data   = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? ($_POST['action'] ?? ($_GET['action'] ?? ''));

// 1. THÊM SẢN PHẨM VÀO GIỎ
if ($action === 'add') {
    $id_spmanga = (int)($data['id_spmanga'] ?? 0);
    $qty        = (int)($data['qty'] ?? 1);

    if ($id_spmanga > 0 && $qty > 0) {
        $db->query("SELECT sp.id_spmanga, sp.gia_ban, sp.nha_xuat_ban, sp.so_luong_kho,
               m.manga_name, m.anh
        FROM sanpham_manga sp
        LEFT JOIN manga m ON sp.id_manga = m.id_manga
        WHERE sp.id_spmanga = :id");
        $db->bind(':id', $id_spmanga);
        $product = $db->single();

        if ($product) {
            //  Ưu tiên giá override (đã áp dụng giảm giá membership từ product.php)
            //   Nếu không có override (thêm từ index.php), tự tính lại
            if (isset($data['gia_override']) && $data['gia_override'] > 0) {
                $gia_cuoi = (float)$data['gia_override'];
            } else {
                $gia_cuoi = MembershipHelper::applyDiscount(
                    (float)$product['gia_ban'],
                    $_SESSION['user_id'] ?? 0
                );
            }

            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

            if (isset($_SESSION['cart'][$id_spmanga])) {
                $_SESSION['cart'][$id_spmanga]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$id_spmanga] = [
                    'id_spmanga'   => $product['id_spmanga'],
                    'manga_name'   => $product['manga_name'],
                    'anh'          => $product['anh'],
                    'gia_ban'      => $gia_cuoi,           // giá đã giảm
                    'gia_goc'      => $product['gia_ban'], // giữ lại để tham chiếu
                    'qty'          => $qty,
                    'nha_xuat_ban' => $product['nha_xuat_ban'],
                    'so_luong_kho' => $product['so_luong_kho'],
                ];
            }

            $total_items = array_sum(array_column($_SESSION['cart'], 'qty'));
            echo json_encode(['status' => 'success', 'message' => 'Đã thêm vào giỏ hàng!', 'total_items' => $total_items]);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không hợp lệ.']);
    exit;
}

// 2. CẬP NHẬT SỐ LƯỢNG
if ($action === 'update') {
    $id_spmanga = (int)($_POST['id_spmanga'] ?? 0);
    $qty        = (int)($_POST['qty'] ?? 1);

    if ($id_spmanga > 0 && isset($_SESSION['cart'][$id_spmanga])) {
        $ton_kho = $_SESSION['cart'][$id_spmanga]['so_luong_kho'] ?? 999;
        $_SESSION['cart'][$id_spmanga]['qty'] = max(1, min($ton_kho, $qty));
    }
    header('Location: cart.php');
    exit;
}

// 3. XÓA SẢN PHẨM
if ($action === 'remove') {
    $id_spmanga = (int)($_GET['id_spmanga'] ?? 0);
    if ($id_spmanga > 0 && isset($_SESSION['cart'][$id_spmanga])) {
        unset($_SESSION['cart'][$id_spmanga]);
    }
    header('Location: cart.php');
    exit;
}

header('Location: cart.php');
exit;