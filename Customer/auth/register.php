<?php
// Customer/auth/register.php
session_start();

// Gọi file class Database
require_once '../../include/db.php';

$error = ''; 
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // 1. Kiểm tra nhập liệu cơ bản
    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } elseif ($password !== $confirm) { 
        $error = 'Mật khẩu xác nhận không khớp!'; 
    } elseif (strlen($password) < 6) { 
        $error = 'Mật khẩu tối thiểu 6 ký tự!'; 
    } else {
        try {
            // Khởi tạo class Database
            $db = new Database();

            // 2. Kiểm tra xem Tên đăng nhập hoặc Email đã tồn tại chưa
            $db->query("SELECT ID_TAIKHOAN FROM taikhoan WHERE TENTAIKHOAN = :username OR EMAIL = :email");
            $db->bind(':username', $username);
            $db->bind(':email', $email);
            $db->execute();

            // Hàm rowCount() để đếm số dòng kết quả trả về
            if ($db->rowCount() > 0) {
                $error = 'Tên đăng nhập hoặc Email này đã được sử dụng!';
            } else {
                // 3. Mã hóa mật khẩu và tạo thời gian
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ngaylap = date('Y-m-d H:i:s');
                
                // 4. Thêm tài khoản mới (Quyền Customer: ID_VAITRO = 2, TRANGTHAI = 1)
                $db->query("INSERT INTO taikhoan (ID_VAITRO, TENTAIKHOAN, MATKHAU, EMAIL, NGAYLAP, TRANGTHAI) 
                            VALUES (2, :username, :matkhau, :email, :ngaylap, 1)");
                
                $db->bind(':username', $username);
                $db->bind(':matkhau', $hash);
                $db->bind(':email', $email);
                $db->bind(':ngaylap', $ngaylap);

                if ($db->execute()) { 
                    $success = 'Đăng ký thành công! Hãy chuyển sang trang đăng nhập.'; 
                    // Reset lại form để người dùng không bấm đăng ký 2 lần
                    $_POST['username'] = '';
                    $_POST['email'] = '';
                } else { 
                    $error = 'Có lỗi xảy ra trong quá trình đăng ký, vui lòng thử lại!'; 
                }
            }
        } catch (Exception $e) {
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}

$base_url     = '../';
$page_title   = 'Đăng ký - Truyện Hay';
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
            <h2>Đăng ký</h2>
            <p>Tạo tài khoản để trải nghiệm đầy đủ!</p>
        </div>

        <?php if($error): ?>
        <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if($success): ?>
        <div class="auth-success" style="color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="register.php">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Tên đăng nhập</label>
                <input type="text" name="username" placeholder="Tối thiểu 4 ký tự..." required minlength="4"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" placeholder="example@email.com" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Mật khẩu</label>
                <div class="input-password">
                    <input type="password" name="password" id="password" placeholder="Tối thiểu 6 ký tự..." required minlength="6">
                    <button type="button" class="btn-toggle-pw" onclick="togglePw('password','pw-icon')">
                        <i class="fas fa-eye" id="pw-icon"></i>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Xác nhận mật khẩu</label>
                <div class="input-password">
                    <input type="password" name="confirm" id="confirm" placeholder="Nhập lại mật khẩu..." required>
                    <button type="button" class="btn-toggle-pw" onclick="togglePw('confirm','cf-icon')">
                        <i class="fas fa-eye" id="cf-icon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-auth">
                <i class="fas fa-user-plus"></i> Đăng ký
            </button>
        </form>

        <div class="auth-divider"><span>hoặc</span></div>

        <div class="auth-switch">
            Đã có tài khoản? <a href="login.php">Đăng nhập</a>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
<script>
function togglePw(id, iconId) {
    const pw = document.getElementById(id);
    const icon = document.getElementById(iconId);
    if(pw.type==='password') { pw.type='text'; icon.className='fas fa-eye-slash'; }
    else { pw.type='password'; icon.className='fas fa-eye'; }
}
</script>