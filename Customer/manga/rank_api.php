<?php
/**
 * rank_api.php
 * Trả về top 10 manga theo lượt xem dạng JSON.
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();

// Customer/manga/ -> lên 2 cấp -> đến include/db.php
require_once '../../include/db.php';

$period = trim($_GET['period'] ?? 'week');

// Xác định điều kiện lọc ngày theo period
switch ($period) {
    case 'month':
        $date_condition = "AND ld.ngay >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        break;
    case 'all':
        $date_condition = ""; // Không lọc ngày → toàn thời gian
        break;
    case 'week':
    default:
        $date_condition = "AND ld.ngay >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
}

try {
    $db = new Database();

    $db->query("
        SELECT
            m.manga_name,
            m.slug,
            m.anh,
            COALESCE(SUM(ld.so_luot_doc), 0) AS tong_view
        FROM manga m
        LEFT JOIN luot_doc ld
            ON ld.id_manga = m.id_manga
            {$date_condition}
        WHERE m.status = 1
        GROUP BY m.id_manga, m.manga_name, m.slug, m.anh
        ORDER BY tong_view DESC
        LIMIT 10
    ");

    $results = $db->resultSet();

    // Đảm bảo tong_view là số nguyên
    foreach ($results as &$row) {
        $row['tong_view'] = (int) $row['tong_view'];
    }

    echo json_encode($results, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi server: ' . $e->getMessage()]);
}