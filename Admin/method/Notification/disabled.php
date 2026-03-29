<?php
// Customer/auth/disabled.php
$base_url     = '../../';
$current_page = '';
?>

<main class="main-content auth-page" style="min-height: 90vh; display: flex; align-items: center; justify-content: center;">
    <div class="auth-container-box" style="text-align: center; max-width: 500px; padding: 40px 30px;">
        <div class="auth-logo">
            <img src="../../anh/sad.png" class="logo" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto 20px; color: #dc3545; border: 2px solid #dc3545; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
            <h2 style="color: #dc3545; margin-bottom: 10px; font-size: 24px;">Tài Khoản Bị Vô Hiệu Hóa</h2>
            <p style="color: #666; font-size: 15px;">Rất tiếc, tài khoản này hiện không thể truy cập hệ thống.</p>
        </div>

        <div class="auth-error" style="background: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.3); padding: 20px; border-radius: 8px; margin: 25px 0; text-align: left; line-height: 1.6; font-size: 14.5px;">
            <strong>Nguyên nhân thường gặp:</strong>
            <ul style="margin-top: 10px; padding-left: 20px;">
                <li>Tài khoản vi phạm chính sách của Truyện Hay.</li>
                <li>Hệ thống phát hiện hoạt động đăng nhập bất thường.</li>
                <li>Tài khoản bị khóa bởi Quản trị viên (Vai trò số 0).</li>
            </ul>
            <p style="margin-top: 10px; margin-bottom: 0;">Vui lòng liên hệ với bộ phận CSKH để được hỗ trợ và cấp lại quyền truy cập.</p>
        </div>

        <div class="form-options" style="display: flex; flex-direction: column; gap: 15px;">
            <a href="../../login.php" class="btn-auth" style="display: block; text-align: center; text-decoration: none; padding: 12px; font-size: 16px;">
                <i class="fas fa-arrow-left"></i> Quay lại Đăng nhập
            </a>
        </div>
    </div>
</main>
