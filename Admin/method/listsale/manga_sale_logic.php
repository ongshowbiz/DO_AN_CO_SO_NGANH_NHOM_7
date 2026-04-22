<?php
$role_id = $_SESSION['role_id'] ?? 0;

try {
    $base_sql = "SELECT 
                    m.id_manga, m.manga_name, m.anh,
                    sp.id_spmanga, sp.gia_ban, sp.so_luong_kho
                 FROM manga m
                 LEFT JOIN sanpham_manga sp ON m.id_manga = sp.id_manga
                 ORDER BY m.id_manga DESC";

    $db->query($base_sql);
    $mangas = $db->resultSet();

} catch (PDOException $e) {
    $mangas = [];
    error_log($e->getMessage());
}
?>