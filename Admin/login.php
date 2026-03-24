<?php 
session_start();
require_once '../config.php';
require_once '../include/db.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['EMAIL'] ?? '');
    $password = $_POST['MATKHAU'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập email và mật khẩu.';
    } else {
        // Lấy thông tin tài khoản theo email
        $db->query('SELECT ID_TAIKHOAN, ID_VAITRO, ANH, EMAIL, MATKHAU, TENTAIKHOAN 
                    FROM taikhoan 
                    WHERE EMAIL = :EMAIL');
        $db->bind(':EMAIL', $email);
        $taikhoan = $db->single();

        if ($taikhoan && password_verify($password, $taikhoan['MATKHAU'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['ID_TAIKHOAN'] = $taikhoan['ID_TAIKHOAN'];
            $_SESSION['ANH'] = $taikhoan['ANH'];
            $_SESSION['ID_VAITRO'] = $taikhoan['ID_VAITRO'];
            $_SESSION['TENTAIKHOAN'] = $taikhoan['TENTAIKHOAN'];
            try {
                $db->query('UPDATE taikhoan SET last_login = NOW() WHERE ID_TAIKHOAN = :ID_TAIKHOAN');
                $db->bind(':ID_TAIKHOAN', $taikhoan['ID_TAIKHOAN']);
                $db->execute();
            } catch (PDOException $e) {
                error_log("Login update failed: " . $e->getMessage());
            }
            switch ($taikhoan['ID_VAITRO']) {
            case 1: // Admin
            case 2: // Staff
                header('Location: index.php');
                break;
            case 3: // Customer
                header('Location: user_index.php');
                break;
            default:
                header('Location: login.php?error=role_not_found');
        }
            exit;
        } else {
            $error = 'Email hoặc mật khẩu không đúng.';
        }
    }
} 


?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="./css/style-login.css">
</head>
<body>
    <div style="position: relative;">
    <a href="index.php" style="text-decoration: none;">
        <img src="./anh/zzz.png" class="cat-login">
        </a>
    <div class="login-card">
    <?php if (!empty($error)): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        <h2>Đăng nhập</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-group">
             <input type="email" class="form-control" name="EMAIL" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" class="form-control" name="MATKHAU" placeholder="Password" required>
            </div>
            <a href="index.php" style="text-decoration: none;">
                <button type="submit" class="btn-login">Đăng nhập</button>
            </a>
            <div class="divider"> hoặc </div>
            <a href="register.php" style="text-decoration:none ;">
                <button type="button" class="btn-register" href="register.php">Đăng ký</button>
            </a>
            <div class="divider">
            <a class = "forgot_password" href="./method/forgot_password.php">
            Quên mật khẩu?
                </a>
            </div>
        </form>
    </div>
</div>
</body>
</html>