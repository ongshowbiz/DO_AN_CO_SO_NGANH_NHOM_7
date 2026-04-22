<?php
require_once __DIR__ . '/../../../../include/db.php';
$db = new Database();
require_once 'function.php'; 

try {
    $base_sql = 'SELECT 
                tl.id_theloaimanga, 
                tl.ten_theloai,
                tl.mota,
                tl.status,
                -- Đếm số lượng truyện (manga) thuộc thể loại này
                (SELECT COUNT(m.id_manga) FROM manga m WHERE m.id_theloaimanga = tl.id_theloaimanga) as film_count
             FROM theloai tl';

    $where_conditions = [];
    $params = [];
    
    if (!empty($_GET['search_keyword'])) {
        $keyword = '%' . $_GET['search_keyword'] . '%';
        $where_conditions[] = 'tl.ten_theloai LIKE :keyword';
        $params[':keyword'] = $keyword;
    }
    
    if (!empty($where_conditions)) {
        $base_sql .= ' WHERE ' . implode(' AND ', $where_conditions);
    }
    $base_sql .= ' ORDER BY tl.id_theloaimanga ASC';

    $count_sql = "SELECT COUNT(*) as total FROM (" . $base_sql . ") as count_table";
    $db->query($count_sql);

    if (!empty($params)) {
        foreach ($params as $key_name => $param_value) {
            $db->bind($key_name, $param_value);
        }
    }

    $total_items = $db->single()['total'] ?? 0;

    $items_per_page = 10;
    $current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;

    $pagination_data = generate_pagination(
        $total_items,
        $items_per_page,
        $current_page,
        'index.php',
        $_GET
    );

    $pagination_html = $pagination_data['html'];
    $offset = $pagination_data['offset'];

    // === BƯỚC 4: LẤY DỮ LIỆU ===
    $data_sql = $base_sql . " LIMIT :limit OFFSET :offset";
    $db->query($data_sql);

    if (!empty($params)) {
        foreach ($params as $key_name => $param_value) {
            $db->bind($key_name, $param_value);
        }
    }

    $db->bind(':limit', $items_per_page, PDO::PARAM_INT);
    $db->bind(':offset', $offset, PDO::PARAM_INT);

    $categories = $db->resultSet();

} catch (PDOException $e) {
    echo '<script>console.error("Lỗi: ' . addslashes($e->getMessage()) . '");</script>';
    $categories = [];
    $pagination_html = '';
}
?>