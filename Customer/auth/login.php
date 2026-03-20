<?php
// auth/login.php
// require_once '../config/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $username = trim($_POST['username'] ?? '');
    // $password = $_POST['password'] ?? '';
    // $stmt = $conn->prepare("SELECT * FROM taikhoan WHERE TENTAIKHOAN=? AND TRANGTHAI=1");
    // $stmt->bind_param('s',$username); $stmt->execute();
    // $user = $stmt->get_result()->fetch_assoc();
    // if($user && password_verify($password, $user['MATKHAU'])) {
    //     session_start();
    //     $_SESSION['user_id'] = $user['ID_TAIKHOAN'];
    //     $_SESSION['username'] = $user['TENTAIKHOAN'];
    //     header('Location: ../index.php'); exit;
    // } else { $error = 'Tên đăng nhập hoặc mật khẩu không đúng!'; }
    $error = 'Chức năng đăng nhập chưa kết nối DB.';
}

$base_url     = '../';
$page_title   = 'Đăng nhập - Truyện Hay';
$current_page = '';
$extra_css    = ['../css/auth.css'];
require_once '../includes/header.php';
?>

<main class="main-content auth-page">
    <div class="auth-container-box">
        <div class="auth-logo">
            <div class="logo" style="width:60px;height:60px;font-size:1.4rem;margin:0 auto 15px;">
                <h1>TH</h1>
            </div>
            <h2>Đăng nhập</h2>
            <p>Chào mừng bạn trở lại Truyện Hay!</p>
        </div>

        <?php if($error): ?>
        <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="login.php">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Tên đăng nhập</label>
                <input type="text" name="username" placeholder="Nhập tên đăng nhập..." required
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Mật khẩu</label>
                <div class="input-password">
                    <input type="password" name="password" id="password" placeholder="Nhập mật khẩu..." required>
                    <button type="button" class="btn-toggle-pw" onclick="togglePw()">
                        <i class="fas fa-eye" id="pw-icon"></i>
                    </button>
                </div>
            </div>
            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
                </label>
                <a href="#" class="forgot-link">Quên mật khẩu?</a>
            </div>
            <button type="submit" class="btn-auth">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </button>
        </form>

        <div class="auth-divider"><span>hoặc</span></div>

        <div class="auth-switch">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
<script>
function togglePw() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('pw-icon');
    if(pw.type === 'password') { pw.type='text'; icon.className='fas fa-eye-slash'; }
    else { pw.type='password'; icon.className='fas fa-eye'; }
}
</script>