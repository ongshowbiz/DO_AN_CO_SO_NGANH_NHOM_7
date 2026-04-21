<?php
// function Upload hình ảnh- dùng chung
function uploadImage(array $fileInput, string $tableName): string {
    // Định nghĩa thư mục gốc cho tất cả các file upload (chỉ định nghĩa 1 lần)
    if (!defined('UPLOAD_BASE_PATH')) {
       define('UPLOAD_BASE_PATH', dirname(__DIR__) . '/Customer/assets/uploads/');
    }

    // === XÁC ĐỊNH THƯ MỤC VÀ TIỀN TỐ CHO FILE
    $subDir = strtolower($tableName); // tên thư mục con là tên bảng viết thường
    $targetDir = UPLOAD_BASE_PATH . $subDir . '/';

    // Xác định tiền tố cho tên file để dễ nhận biết
    $fileNamePrefix = 'file_';
    switch ($subDir) {
        case 'taikhoan':
            $fileNamePrefix = 'avatars_';
            break;
        case 'phim':
            $fileNamePrefix = 'products_';
            break;
    }

    // Tạo thư mục nếu nó chưa tồn tại
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true); // tham số thứ 3 (true) cho phép tạo thư mục lồng nhau
    }

    // 1️⃣ Kiểm tra lỗi upload
    if (!isset($fileInput['error']) || $fileInput['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Có lỗi xảy ra trong quá trình upload file.');
    }

    // 2️⃣ Kiểm tra loại file và kích thước (bảo mật)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($fileInput['tmp_name']);
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5 MB

    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception('Lỗi: Chỉ cho phép upload file JPG, PNG, GIF.');
    }
    if ($fileInput['size'] > $max_size) {
        throw new Exception('Lỗi: Kích thước file không được vượt quá 5MB.');
    }

    // 3️⃣ Tạo tên file mới và đường dẫn đích
    $file_extension = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));
    $new_filename = $fileNamePrefix . uniqid('', true) . '.' . $file_extension;
    $target_path    = $targetDir . $new_filename;        
    $relative_path  = 'assets/uploads/' . $subDir . '/' . $new_filename; 
    if (move_uploaded_file($fileInput['tmp_name'], $target_path)) {
        return $relative_path; // trả về relative path để hiển thị ảnh đúng
    } else {
        throw new Exception('Không thể di chuyển file đã upload. Vui lòng kiểm tra quyền ghi của thư mục.');
    }
}

/**
 * Phân trang và tính toán các giá trị LIMIT/OFFSET.
 */
function generate_pagination($total_items, $items_per_page = 10, 
        $current_page = 1, $base_url = 'index.php', $query_params = [])
{
    // --- Tính toán các giá trị cần thiết ---
    if ($current_page < 1) { // Xác thực trang hiện tại
        $current_page = 1;
    }

    $total_pages = 0; // Khởi tạo tổng số trang
    if ($total_items > 0 && $items_per_page > 0) { // Tránh chia cho 0
        $total_pages = ceil($total_items / $items_per_page); // Tính tổng số trang
    }
    
    if ($current_page > $total_pages && $total_pages > 0) { // Xác thực trang hiện tại
        $current_page = $total_pages; // Không vượt quá tổng số trang
    }

    $offset = ($current_page - 1) * $items_per_page; // Tính OFFSET chỉ định lấy các bản ghi tại vị trí nào
    $limit = $items_per_page; // Giới hạn bản ghi mỗi lần lấy kết quả

    // --- Tạo HTML hiển thị---
    $pagination_html = '';

    if ($total_pages > 1) {
        $pagination_html .= '<nav><ul class="pagination justify-content-center">';
        
        // Loại bỏ 'p' (page) khỏi mảng query_params nếu có
        unset($query_params['p']);

        // Nút "Previous" 
        $prev_class = ($current_page <= 1) ? 'disabled' : '';
        $query_params['p'] = $current_page - 1;
        $prev_link = $base_url . '?' . http_build_query($query_params);
        $pagination_html .= "<li class='page-item {$prev_class}'>
            <a class='page-link' href='{$prev_link}'>&laquo; </a></li>";

        // Các trang số
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = ($i == $current_page) ? 'active' : '';
            $query_params['p'] = $i;
            $page_link = $base_url . '?' . http_build_query($query_params);
            $pagination_html .= "<li class='page-item {$active_class}'>
                    <a class='page-link' href='{$page_link}'>{$i}</a></li>";
        }

        // Nút "Next" (Trang sau)
        $next_class = ($current_page >= $total_pages) ? 'disabled' : '';
        $query_params['p'] = $current_page + 1;
        $next_link = $base_url . '?' . http_build_query($query_params);
        $pagination_html .= "<li class='page-item {$next_class}'><a class='page-link' href='{$next_link}'>&raquo;</a></li>";
        
        $pagination_html .= '</ul></nav>';
    }
    
    // Trả về một mảng chứa kết quả
    return [
        'html' => $pagination_html, // Mã HTML để hiển thị
        'offset' => $offset,         // Dùng cho SQL OFFSET
        'limit' => $limit,           // Dùng cho SQL LIMIT
        'total_pages' => $total_pages,   // Tổng số trang
        'current_page' => $current_page  // Trang hiện tại (đã được xác thực)
    ];
}



// === CÁC HÀM QUẢN LÝ GIỎ HÀNG CÁ NHÂN (USER_CART) ===

/* Thêm sản phẩm vào giỏ hàng cá nhân. */
function add_to_cart(Database $db, int $ID_TAIKHOAN, int $variant_id, int $product_id, int $quantity, string $product_name, string $size, float $price, string $image_url) {
    if ($quantity <= 0 || $ID_TAIKHOAN <= 0 || $variant_id <= 0) {
        return; 
    }

    // 1. Cập nhật SESSION (cho giao diện)
    if (!isset($_SESSION['user_cart'])) {
        $_SESSION['user_cart'] = [];
    }
    
    // Tính toán số lượng TỔNG CỘNG
    $new_quantity = $quantity;
    if (isset($_SESSION['user_cart'][$variant_id])) {
        $new_quantity = $_SESSION['user_cart'][$variant_id]['quantity'] + $quantity;
    }
    
    // Cập nhật session với số lượng TỔNG CỘNG
    $_SESSION['user_cart'][$variant_id] = [
        'product_id' => $product_id,
        'variant_id' => $variant_id,
        'product_name' => $product_name,
        'size' => $size,
        'price' => $price,
        'image_url' => $image_url,
        'quantity' => $new_quantity // Luôn là số lượng tổng
    ];

    // 2. Cập nhật CSDL (để lưu trữ)
    try {
        // === THAY ĐỔI SQL ===
        // Sử dụng schema mới (add_date, edit_date)
        // Logic được tối ưu: dùng new_quantity cho cả INSERT và UPDATE
        $db->query('
            INSERT INTO CartItems (user_id, variant_id, quantity, add_date, edit_date) 
            VALUES (:user_id, :variant_id, :new_quantity, NOW(), NOW())
            ON DUPLICATE KEY UPDATE quantity = :new_quantity, edit_date = NOW()
        ');
        $db->bind(':user_id', $ID_TAIKHOAN);
        $db->bind(':variant_id', $variant_id);
        $db->bind(':new_quantity', $new_quantity); // Dùng tổng số lượng
        $db->execute();
        // ===================
    } catch (PDOException $e) {
        error_log('Lỗi add_to_cart CSDL: ' . $e->getMessage());
    }
}

/* Cập nhật số lượng của một sản phẩm trong giỏ hàng cá nhân */
function update_cart_quantity(Database $db, int $user_id, int $variant_id, int $quantity) {
    if ($user_id <= 0 || $variant_id <= 0) {
        return;
    }

    if ($quantity <= 0) {
        // Nếu số lượng là 0, gọi hàm xóa
        remove_from_cart($db, $user_id, $variant_id);
    } else {
        // 1. Cập nhật SESSION
        if (isset($_SESSION['user_cart'][$variant_id])) {
            $_SESSION['user_cart'][$variant_id]['quantity'] = $quantity;
        }

        // 2. Cập nhật CSDL
        try {
            // === THAY ĐỔI SQL ===
            // Thêm cập nhật edit_date
            $db->query('
                UPDATE CartItems SET quantity = :quantity, edit_date = NOW()
                WHERE user_id = :user_id AND variant_id = :variant_id
            ');
            // ===================
            $db->bind(':quantity', $quantity);
            $db->bind(':user_id', $user_id);
            $db->bind(':variant_id', $variant_id);
            $db->execute();
        } catch (PDOException $e) {
            error_log('Lỗi update_cart_quantity CSDL: ' . $e->getMessage());
        }
    }
}

/* Xóa một sản phẩm khỏi giỏ hàng cá nhân */
function remove_from_cart(Database $db, int $user_id, int $variant_id) {
    if ($user_id <= 0 || $variant_id <= 0) {
        return;
    }
    
    // 1. Xóa khỏi SESSION
    if (isset($_SESSION['user_cart'][$variant_id])) {
        unset($_SESSION['user_cart'][$variant_id]);
    }

    // 2. Xóa khỏi CSDL
    try {
        $db->query('
            DELETE FROM CartItems 
            WHERE user_id = :user_id AND variant_id = :variant_id
        ');
        $db->bind(':user_id', $user_id);
        $db->bind(':variant_id', $variant_id);
        $db->execute();
    } catch (PDOException $e) {
        error_log('Lỗi remove_from_cart CSDL: ' . $e->getMessage());
    }
}

/** Xóa toàn bộ giỏ hàng cá nhân. */
function clear_cart(Database $db, int $user_id) {
    if ($user_id <= 0) {
        return;
    }
    
    // 1. Xóa khỏi SESSION
    $_SESSION['user_cart'] = [];

    // 2. Xóa khỏi CSDL theo user_id
    try {
        $db->query('DELETE FROM CartItems WHERE user_id = :user_id');
        $db->bind(':user_id', $user_id);
        $db->execute();
    } catch (PDOException $e) {
        error_log('Lỗi clear_cart CSDL: ' . $e->getMessage());
    }
}

/* Lấy toàn bộ nội dung của giỏ hàng cá nhân. */
function get_cart_contents(): array {
    return $_SESSION['user_cart'] ?? [];
}

/* Tính tổng tiền của giỏ hàng cá nhân. */
function get_cart_total(): float {
    $total = 0;
    $cart = get_cart_contents(); 
    foreach ($cart as $item) {
        $total += (float)$item['price'] * (int)$item['quantity'];
    }
    return $total;
}


?>