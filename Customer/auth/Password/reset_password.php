<?php
session_start();
$base = "../../../";
require_once $base.'config.php';
require_once $base.'include/db.php';
$db = new Database();
$token = $_GET['token'] ?? '';
$error = ''; $success = '';
$token = trim($_GET['token'] ?? '');
// Kiểm tra token có khớp và còn hạn không
$db->query('SELECT ID_TAIKHOAN FROM taikhoan WHERE reset_token = :token');
$db->bind(':token', $token);
$user = $db->single();

if (!$user) { die("Liên kết không hợp lệ hoặc đã hết hạn."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['PASS'];
    $confirm = $_POST['CONFIRM'];

    if (strlen($pass) < 6) { $error = "Mật khẩu tối thiểu 6 ký tự."; }
    elseif ($pass !== $confirm) { $error = "Mật khẩu không khớp."; }
    else {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $db->query('UPDATE taikhoan SET MATKHAU = :pass, reset_token = NULL, reset_expiry = NULL WHERE ID_TAIKHOAN = :id');
        $db->bind(':pass', $hashed);
        $db->bind(':id', $user['ID_TAIKHOAN']);
        if ($db->execute()) {
            $success = "Đổi mật khẩu thành công! Chuyển hướng về Đăng nhập...";
            header("Refresh:3; url=../login.php");
        }
    }
}
?>
<!-- HTML Form Reset đơn giản -->
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Đặt lại mật khẩu</title><link rel="stylesheet" href="css/style-login.css"></head>
<body>
<div style="position: relative;">
<a href="../../login.php" style="text-decoration: none;">
        <img src="../Anh/zzz.png" class="cat-login">
        </a>
    <div class="login-card">
        <h2>Mật khẩu mới</h2>
        <?php if($error) echo "<p style='color:red'>$error</p>"; ?>
        <?php if($success) echo "<p style='color:green'>$success</p>"; ?>
        <form method="POST">
            <input type="password" name="PASS" placeholder="Mật khẩu mới" required><br><br>
            <input type="password" name="CONFIRM" placeholder="Nhập lại mật khẩu" required><br><br>
            <button type="submit" class="btn-login">Xác nhận đổi</button>
        </form>
    </div>
</body>
</html>
