<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../include/db.php'; 
$db = new Database(); 

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['error_message'] = "ID thể loại không hợp lệ.";
    header('Location: ../../index.php?method=category-list'); 
    exit();
}

try {
    // Xóa thể loại
    $db->query('DELETE FROM theloai WHERE id_theloaimanga = :id'); 
    $db->bind(':id', $id);

    if ($db->execute()) {
        $_SESSION['success_message'] = "Xóa thể loại thành công!";
    } else {
        $_SESSION['error_message'] = "Xóa thất bại. Có thể thể loại này đang được liên kết với truyện.";
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Lỗi CSDL: " . $e->getMessage();
}

header('Location: ../../index.php?method=category-list'); 
exit();