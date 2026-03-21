<?php
// Bạn có thể thêm code PHP kết nối database ở đây
// $conn = new mysqli('localhost', 'root', '', 'readmanga');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truyện Hay - Quản lý web</title>
    <link rel="stylesheet" href="style.css">
    <!-- FontAwesome cho các icon (dùng CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- PHẦN HEADER -->
    <header class="header">
        <div class="logo-container">
            <div class="logo">
                <!-- Bạn có thể thay bằng thẻ img src="logo.png" khi có ảnh thật -->
                <h1>TH</h1>
            </div>
            <span class="site-name">Truyện Hay</span>
        </div>
        
        <div class="search-container">
            <input type="text" placeholder="Tìm kiếm truyện...">
            <button><i class="fas fa-search"></i></button>
        </div>
        
        <div class="auth-container">
            <a href="login.php" class="btn-login">Đăng nhập</a>
            <a href="register.php" class="btn-register">Đăng ký</a>
        </div>
        
        <!-- Nút mở menu trên giao diện mobile -->
        <button class="mobile-menu-btn" id="mobile-menu-btn">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <!-- PHẦN NAV (Menu) -->
    <nav class="navbar" id="navbar">
        <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li><a href="#"><i class="fas fa-list"></i> Thể loại truyện</a></li>
            <li><a href="#"><i class="fas fa-search"></i> Tìm truyện</a></li>
            <li><a href="#"><i class="fas fa-filter"></i> Lọc truyện</a></li>
            <li><a href="#"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a></li>
        </ul>
        <!-- Nút đóng menu trên mobile -->
        <div class="close-menu-btn" id="close-menu-btn">
            <i class="fas fa-times"></i> Đóng
        </div>
    </nav>
    
    <!-- Lớp phủ tối khi mở menu mobile -->
    <div class="overlay" id="overlay"></div>

    <!-- PHẦN BODY (Nội dung chính) -->
    <main class="main-content">
        <section class="manga-section">
            <div class="section-header">
                <h2><i class="fas fa-book-open"></i> Truyện mới cập nhật</h2>
            </div>
            <div class="manga-grid">
                <!-- Vòng lặp PHP đỗ dữ liệu có thể đặt ở đây -->
                <?php for($i=1; $i<=8; $i++): ?>
                <div class="manga-card">
                    <!-- Ảnh placeholder, thay thế bằng dữ liệu từ DB -->
                    <img src="https://via.placeholder.com/200x280?text=Truyen+<?php echo $i; ?>" alt="Truyện <?php echo $i; ?>">
                    <div class="manga-info">
                        <h3>Tên Truyện Mẫu <?php echo $i; ?></h3>
                        <p class="genres">Thể loại: Hành động, Hài Hước</p>
                        <div class="manga-meta">
                            <span><i class="fas fa-eye"></i> 10.5K</span>
                            <span><i class="fas fa-star"></i> 4.5</span>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </section>
    </main>

    <!-- PHẦN FOOTER -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-about">
                <h3><i class="fas fa-info-circle"></i> Về Truyện Hay</h3>
                <p>Website đọc truyện tranh online miễn phí cập nhật liên tục các bộ truyện mới nhất. Giao diện thân thiện, dễ nhìn với chất lượng cao nhất.</p>
            </div>
            <div class="footer-links">
                <h3>Liên kết nhanh</h3>
                <ul>
                    <li><a href="#">Điều khoản sử dụng</a></li>
                    <li><a href="#">Chính sách bảo mật</a></li>
                    <li><a href="#">Liên hệ gửi phản hồi</a></li>
                    <li><a href="#">Quy định bản quyền</a></li>
                </ul>
            </div>
            <div class="footer-social">
                <h3>Theo dõi chúng tôi</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                    <a href="#"><i class="fab fa-discord"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2023 Truyện Hay. All rights reserved.</p>
        </div>
    </footer>

    <!-- CON CHATBOX AI GÓC DƯỚI BÊN PHẢI -->
    <div class="chatbox-container">
        <!-- Nút bấm để mở/đóng -->
        <button class="chatbox-toggle" id="chatbox-toggle">
            <i class="fas fa-robot"></i>
        </button>
        
        <!-- Cửa sổ chat -->
        <div class="chatbox-window" id="chatbox-window">
            <div class="chatbox-header">
                <span><i class="fas fa-robot"></i> Trợ lý Truyện Hay AI</span>
                <button id="chatbox-close"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="chatbox-messages" id="chatbox-messages">
                <div class="message ai-message">
                    Xin chào! Tôi là trợ lý AI chuyên hỗ trợ độc giả. Bạn có câu hỏi nào về trang web hoặc muốn tìm truyện gì không?
                </div>
                <!-- Tin nhắn sẽ được thêm bằng Javascript -->
            </div>
            
            <div class="chatbox-input">
                <input type="text" id="chat-input" placeholder="Nhập câu hỏi của bạn...">
                <button id="send-btn"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <!-- Script Javascript -->
    <script src="script.js"></script>
</body>
</html>