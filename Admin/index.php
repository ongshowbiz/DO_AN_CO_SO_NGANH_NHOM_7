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
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=admincart-view" title="Giỏ hàng cá nhân">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-count-badge" class="badge badge-danger navbar-badge">
                        </span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <img src=""
                            class="img-circle"
                            alt="User Image"
                            style="width: 25px; height: 25px; object-fit: cover; margin-top: -3px; margin-right: 5px;">
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
        <?php
        require_once './method/sidebar.php';
        ?>
<script>
document.querySelectorAll('.dropdown').forEach(item => {
    item.addEventListener('click', () => {
        const menu = item.querySelector('.dropdown-menu');
        menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
    });
});

document.querySelectorAll('.nav-item > .nav-link').forEach(link => {
    link.addEventListener('click', function (e) {
        const parent = this.parentElement;
        const submenu = parent.querySelector('.nav-treeview');

        if (submenu) {
            e.preventDefault();
            parent.classList.toggle('open');
        }
    });
});
</script>
</body>
</html>
