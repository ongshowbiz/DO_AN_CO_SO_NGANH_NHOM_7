<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Truyện Hay - Quản lý web</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="./css/admin.css">
    <link rel="stylesheet" href="./css/dashboard.css">
    <link rel="stylesheet" href="./css/user_management.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <img src="./anh/admin.png"
                            class="img-circle"
                            alt="User Image"
                            style="width: 60px; height: 60px; object-fit: cover; margin-top: -3px; margin-right: 5px;">
                        <span></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <a href="index.php?page=profile" class="dropdown-item">
                            <i class="fas fa-user-cog mr-2"></i> Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <div class="content">
            <?php
            // Lấy tham số page từ giao diện, mặc định là dashboard
            $page = $_GET['page'] ?? 'dashboard';
            
            // Xử lý routing (điều hướng) các trang
            if ($page == 'dashboard') {
                require_once './method/dashboard.php';
            } elseif ($page == 'user-list') {
                require_once './method/user_list.php';
            } elseif ($page == 'role-list') {
                require_once './method/role_list.php';
            } else {
                echo '<div style="background:#fff; padding:20px; border-radius:10px;">
                        <h2>Đang phát triển...</h2>
                        <p>Trang này chưa được xây dựng hoặc đường dẫn không hợp lệ.</p>
                      </div>';
            }
            ?>
        </div>
        <?php
        require_once './method/sidebar.php';
        ?>
    <script src="script.js"></script>
</body>
</html>
