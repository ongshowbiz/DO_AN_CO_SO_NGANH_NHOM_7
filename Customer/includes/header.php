<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($current_page)) $current_page = '';
if (!isset($page_title))   $page_title   = 'Truyện Hay - Đọc truyện tranh online';
if (!isset($base_url))     $base_url     = '/';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if (!empty($extra_css)): foreach ((array)$extra_css as $css): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
    <?php endforeach; endif; ?>
</head>
<body>

<!-- PHẦN HEADER -->
<header class="header">
    <div class="logo-container">
        <a href="<?php echo $base_url; ?>index.php" style="display:flex;align-items:center;gap:12px;text-decoration:none;">
            <div class="logo"><h1>TH</h1></div>
            <span class="site-name">Truyện Hay</span>
        </a>
    </div>

    <div class="search-container">
        <form action="<?php echo $base_url; ?>manga/list.php" method="GET" style="display:flex;width:100%;">
            <input type="text" name="search" placeholder="Tìm kiếm truyện..."
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="auth-container">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?php echo $base_url; ?>user/profile.php" class="btn-login">
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Tài khoản'); ?>
            </a>
            <a href="<?php echo $base_url; ?>auth/logout.php" class="btn-register">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        <?php else: ?>
            <a href="<?php echo $base_url; ?>auth/login.php" class="btn-login">Đăng nhập</a>
            <a href="<?php echo $base_url; ?>auth/register.php" class="btn-register">Đăng ký</a>
        <?php endif; ?>
    </div>

    <button class="mobile-menu-btn" id="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>
</header>

<!-- PHẦN NAV -->
<nav class="navbar" id="navbar">
    <ul class="nav-links">
        <li><a href="<?php echo $base_url; ?>index.php"
               class="<?php echo $current_page==='home'?'active-nav':''; ?>">
            <i class="fas fa-home"></i> Trang chủ</a></li>
        <li class="dropdown">
    <a href="#" class="<?php echo $current_page==='list'?'active-nav':''; ?>">
        <i class="fas fa-list"></i> Thể loại truyện
        <i class="fas fa-chevron-down dropdown-arrow"></i>
    </a>
    <div class="dropdown-menu">
        <?php
        $genres = ['Hành Động','Tình Cảm','Hài Hước','Kinh Dị',
                   'Phiêu Lưu','Trinh Thám','Xuyên Không','Học Đường',
                   'Cổ Đại','Ngôn Tình','Hệ Thống','Võ Hiệp'];
        foreach($genres as $g): ?>
        <a href="<?php echo $base_url; ?>manga/list.php?genre=<?php echo urlencode($g); ?>">
            <?php echo htmlspecialchars($g); ?>
        </a>
        <?php endforeach; ?>
    </div>
</li>
        <li><a href="<?php echo $base_url; ?>manga/list.php?search=1"
               class="<?php echo $current_page==='search'?'active-nav':''; ?>">
            <i class="fas fa-search"></i> Tìm truyện</a></li>
        <li><a href="<?php echo $base_url; ?>manga/list.php?filter=1"
               class="<?php echo $current_page==='filter'?'active-nav':''; ?>">
            <i class="fas fa-filter"></i> Lọc truyện</a></li>
        <li><a href="<?php echo $base_url; ?>shop/cart.php"
               class="<?php echo $current_page==='cart'?'active-nav':''; ?>">
            <i class="fas fa-shopping-cart"></i> Giỏ hàng</a></li>
    </ul>
    <div class="close-menu-btn" id="close-menu-btn">
        <i class="fas fa-times"></i> Đóng
    </div>
</nav>

<div class="overlay" id="overlay"></div>