<?php
session_start();
require_once 'config.php';
require_once 'include/db.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php');
    exit;
}
$db = new Database(); 
$full_name = "";
$email = "";
$error = "";
$success = "";

// Chỉ cố lấy dữ liệu khi form được submit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu một cách an toàn
    $full_name = trim(
        $_POST['TENTAIKHOAN']  
        ?? $_POST['full_name']  
        ?? $_POST['TENTK']  
        ?? ''
    );

    $email = trim(
        $_POST['EMAIL']
        ?? $_POST['email']
        ?? ''
    );

    $password = $_POST['MATKHAU'] ?? $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 2. Kiem tra tinh hop le
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = "Vui lòng điền đầy đủ thông tin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Định dạng email không hợp lệ.";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp.";
    } else {
        // 3. Kiem tra email co ton tai khong
        try {
            $db->query('SELECT ID_TAIKHOAN FROM taikhoan WHERE EMAIL = :EMAIL');
            $db->bind(':EMAIL', $email);
            $db->execute();

            if ($db->rowCount() > 0) {
                $error = "Email này đã được sử dụng.";
            } else {
                // 4. Ma hoa mat khau
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);

                // 5. Luu vao CSDL
                $db->query('INSERT INTO taikhoan (TENTAIKHOAN, EMAIL, MATKHAU, ID_VAITRO) VALUES (:TENTAIKHOAN, :EMAIL, :MATKHAU, :ID_VAITRO )');
                $db->bind(':TENTAIKHOAN', $full_name);
                $db->bind(':EMAIL', $email);
                $db->bind(':MATKHAU', $password_hashed);
                $db->bind(':ID_VAITRO', 2);

                if ($db->execute()) {
                    $success = "Đăng ký thành công! Chuyển đến trang đăng nhập...";
                    header("Refresh:3; url=login.php");
                    // Không exit ngay để vẫn hiển thị thông báo success trên trang nếu cần
                } else {
                    $error = "Đã có lỗi xảy ra, vui lòng thử lại.";
                }
            }
        } catch (PDOException $e) {
            // Ghi log nếu cần, nhưng hiển thị thông báo chung cho user
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Kí</title>
    <link rel="stylesheet" href="style-login.css">
</head>
<body>
    <div style="position: relative;">
    <a href="index.php" style="text-decoration: none;">
        <img src="zzz.png" class="cat-login">
        </a>
    <div class="login-card">
        <h2>Đăng Kí</h2>
        <?php if(!empty($error)): ?>
                <div class="alert alert-danger text-center"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success text-center"><?php echo $success; ?></div>
            <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-group">
                 <label>Tên tài khoản</label>
                 <input type="text" class="form-control" name="TENTAIKHOAN" placeholder="Tên của bạn" value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" name="EMAIL" placeholder="Email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" class="form-control" name="MATKHAU" placeholder="Mật khẩu" required>
                <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
            </div>
            <div class="form-group">
                <label>Nhập lại mật khẩu</label>
                <input type="password" class="form-control" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
            </div>
                <button type="submit" class="btn-login">Đăng Kí</button>
        </form>
    </div>
</div>
</body>
</html>