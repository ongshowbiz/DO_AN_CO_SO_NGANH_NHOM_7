<?php
session_start();
$base = "../../../";
require_once $base.'config.php';
require_once $base.'include/db.php';

// NHÚNG THƯ VIỆN PHPMAILER
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require $base.'include/PHPMailer/Exception.php';
require $base.'include/PHPMailer/PHPMailer.php';
require $base.'include/PHPMailer/SMTP.php';

$db = new Database();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['EMAIL'] ?? '');

    if (empty($email)) {
        $error = 'Vui lòng nhập email.';
    } else {
        $db->query('SELECT ID_TAIKHOAN FROM taikhoan WHERE EMAIL = :EMAIL');
        $db->bind(':EMAIL', $email);
        $user = $db->single();

        if ($user) {
            $token = bin2hex(random_bytes(16));
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));
            
            $db->query('UPDATE taikhoan SET reset_token = :token, reset_expiry = :expiry WHERE ID_TAIKHOAN = :id');
            $db->bind(':token', $token);
            $db->bind(':expiry', $expiry);
            $db->bind(':id', $user['ID_TAIKHOAN']);
            $db->execute();

            // --- CẤU HÌNH SMTP MAILTRAP ---
            $mail = new PHPMailer(true);

            try {
                // Cấu hình Server (Thông tin từ ảnh cURL của bạn)
                $mail->isSMTP();
                $mail->Host       = 'sandbox.smtp.mailtrap.io';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'e1dbfd414c5dca'; // User trong ảnh
                $mail->Password   = 'fa6c5ad7b785d6'; // Pass trong ảnh
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 2525;
                $mail->CharSet    = 'UTF-8';

                // Người nhận & Gửi
                $mail->setFrom('admin@readmanga.com', 'ReadManga Support');
                $mail->addAddress($email);

                // Nội dung
                $resetLink = "http://localhost/DO_AN_CO_SO_NGANH_NHOM_7/Admin/method/Password/reset_password.php?token=" . $token;
                $mail->isHTML(true);
                $mail->Subject = 'Khôi phục mật khẩu - ReadManga';
                $mail->Body    = "<h3>Yêu cầu đổi mật khẩu</h3>
                                  <p>Nhấn vào link: <a href='$resetLink'>$resetLink</a></p>";

                $mail->send();
                $_SESSION['success_message'] = "Yêu cầu khôi phục đã được gửi! Vui lòng kiểm tra email.";
                header("Location:https://mailtrap.io/inboxes/4480401/messages");
                exit();
            } catch (Exception $e) {
                // Ghi log lỗi nếu cần: $mail->ErrorInfo;
            }
        }
        $success = 'Nếu email tồn tại, một liên kết khôi phục đã được gửi đi.';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="../../css/style-login.css">
</head>
<body>
<div style="position: relative;">
<a href="../../index.php" style="text-decoration: none;">
        <img src="../../anh/zzz.png" class="cat-login">
        </a>
    <div class="login-card">
        <h2>Quên mật khẩu</h2>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="color: green;"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="form-group">
                <label>Nhập Email khôi phục</label>
                <input type="email" name="EMAIL" class="form-control" required>
            </div>
            <button type="submit" class="btn-login">Gửi yêu cầu</button>
        </form>
    </div>
</body>
</html>
