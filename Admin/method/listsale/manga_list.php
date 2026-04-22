<?php
require_once __DIR__ . '/../../../include/function.php';

$db->query('SELECT id_theloaimanga, ten_theloai FROM theloai ORDER BY ten_theloai ASC');
$categories = $db->resultSet();

try {

    $base_sql = 'SELECT 
                m.id_manga, m.manga_name, m.tacgia, m.anh, m.status, m.create_day,
                tl.ten_theloai,
                sp.gia_ban, sp.so_luong_kho,
                (SELECT COUNT(c.id_chap) FROM chap c WHERE c.id_manga = m.id_manga) as chapter_count,
                (SELECT SUM(ld.so_luot_doc) FROM luot_doc ld WHERE ld.id_manga = m.id_manga) as tong_view
             FROM manga m 
             JOIN theloai tl ON m.id_theloaimanga = tl.id_theloaimanga
             INNER JOIN sanpham_manga sp ON m.id_manga = sp.id_manga';

    $where_conditions = [];
    $params = [];
    
    $where_conditions[] = 'sp.so_luong_kho > 0';
    if (!empty($_GET['search_keyword'])) {
        $keyword = '%' . $_GET['search_keyword'] . '%';
        $where_conditions[] = '(m.manga_name LIKE :keyword OR m.tacgia LIKE :keyword_tg)';
        $params[':keyword'] = $keyword;
        $params[':keyword_tg'] = $keyword;
    }
    if (!empty($_GET['filter_category'])) {
        $where_conditions[] = 'm.id_theloaimanga = :filter_category';
        $params[':filter_category'] = $_GET['filter_category'];
    }
    if (!empty($where_conditions)) {
        $base_sql .= ' WHERE ' . implode(' AND ', $where_conditions);
    }
    
    $base_sql .= ' ORDER BY m.id_manga DESC';
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

    $pagination_data = generate_pagination($total_items, $items_per_page, $current_page, 'index.php', $_GET);
    $pagination_html = $pagination_data['html'];
    $offset = $pagination_data['offset'];
    $data_sql = $base_sql . " LIMIT :limit OFFSET :offset";
    $db->query($data_sql);
    if (!empty($params)) {
        foreach ($params as $key_name => $param_value) {
            $db->bind($key_name, $param_value);
        }
    }
    $db->bind(':limit', $items_per_page, PDO::PARAM_INT);
    $db->bind(':offset', $offset, PDO::PARAM_INT);
    $mangas = $db->resultSet(); 

} catch (PDOException $e) {
    echo '<script>console.error("Lỗi CSDL: ' . addslashes($e->getMessage()) . '");</script>';
    $mangas = [];
    $pagination_html = '';
}
?>