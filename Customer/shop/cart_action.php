<?php
session_start();
require_once __DIR__ . '/../../include/db.php';
$db = new Database();

// Nhận dữ liệu 
$data = json_decode(file_get_contents('php://input'), true);
// Ưu tiên lấy action từ JSON, nếu không có thì lấy từ POST, cuối cùng là GET
$action = $data['action'] ?? ($_POST['action'] ?? ($_GET['action'] ?? ''));

// 1. THÊM SẢN PHẨM VÀO GIỎ HÀNG (Dùng AJAX/JSON)
if ($action === 'add') {
    $id_spmanga = (int)($data['id_spmanga'] ?? 0);
    $qty = (int)($data['qty'] ?? 1);

    if ($id_spmanga > 0 && $qty > 0) {
        $db->query("SELECT sp.id_spmanga, sp.gia_ban, sp.nha_xuat_ban, sp.so_luong_kho,
               m.manga_name, m.slug, m.anh, m.tacgia, m.mota, m.id_theloaimanga, m.create_day
        FROM sanpham_manga sp
        LEFT JOIN manga m ON sp.id_manga = m.id_manga
        WHERE sp.id_spmanga = :id");
        $db->bind(':id', $id_spmanga);
        $product = $db->single();

        if ($product) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            if (isset($_SESSION['cart'][$id_spmanga])) {
                $_SESSION['cart'][$id_spmanga]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$id_spmanga] = [
                    'id_spmanga' => $product['id_spmanga'],
                    'manga_name' => $product['manga_name'],
                    'anh'        => $product['anh'],
                    'gia_ban'    => $product['gia_ban'],
                    'qty'        => $qty,
                    'nha_xuat_ban' => $product['nha_xuat_ban'],
                    'so_luong_kho' => $product['so_luong_kho']
                ];
            }
            
            $total_items = array_sum(array_column($_SESSION['cart'], 'qty'));
            echo json_encode(['status' => 'success', 'message' => 'Đã thêm vào giỏ hàng!', 'total_items' => $total_items]);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'Lỗi: Sản phẩm không hợp lệ.']);
    exit;
}

// 2. XỬ LÝ CẬP NHẬT SỐ LƯỢNG 
if ($action === 'update') {
    $id_spmanga = (int)($_POST['id_spmanga'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 1);

    if ($id_spmanga > 0 && isset($_SESSION['cart'][$id_spmanga])) {
        // Lấy số lượng tồn kho để kiểm tra
        $ton_kho = $_SESSION['cart'][$id_spmanga]['so_luong_kho'] ?? 999;
    
        if ($qty > $ton_kho) {
            $_SESSION['cart'][$id_spmanga]['qty'] = $ton_kho; // Ép về số Max
        } elseif ($qty < 1) {
            $_SESSION['cart'][$id_spmanga]['qty'] = 1; // Ép về số Min
        } else {
            $_SESSION['cart'][$id_spmanga]['qty'] = $qty; // Cập nhật bình thường
        }
    }
    header('Location: cart.php');
    exit;
}
// 3. XÓA SẢN PHẨM KHỎI GIỎ HÀNG 
if ($action === 'remove') {
    $id_spmanga = (int)($_GET['id_spmanga'] ?? 0);
    
    // Nếu tìm thấy ID trong giỏ hàng thì xóa phần tử đó đi
    if ($id_spmanga > 0 && isset($_SESSION['cart'][$id_spmanga])) {
        unset($_SESSION['cart'][$id_spmanga]);
    }
    // Chuyển hướng về lại trang giỏ hàng
    header('Location: cart.php');
    exit;
}header('Location: cart.php');
    exit;
?>