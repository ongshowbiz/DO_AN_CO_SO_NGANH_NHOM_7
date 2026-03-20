<?php // includes/footer.php ?>

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
        <p>&copy; <?php echo date('Y'); ?> Truyện Hay. All rights reserved.</p>
    </div>
</footer>

<!-- CON CHATBOX AI GÓC DƯỚI BÊN PHẢI -->
<div class="chatbox-container">
    <button class="chatbox-toggle" id="chatbox-toggle">
        <i class="fas fa-robot"></i>
    </button>
    <div class="chatbox-window" id="chatbox-window">
        <div class="chatbox-header">
            <span><i class="fas fa-robot"></i> Trợ lý Truyện Hay AI</span>
            <button id="chatbox-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="chatbox-messages" id="chatbox-messages">
            <div class="message ai-message">
                Xin chào! Tôi là trợ lý AI chuyên hỗ trợ độc giả. Bạn có câu hỏi nào về trang web hoặc muốn tìm truyện gì không?
            </div>
        </div>
        <div class="chatbox-input">
            <input type="text" id="chat-input" placeholder="Nhập câu hỏi của bạn...">
            <button id="send-btn"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<!-- SCRIPT CHUNG -->
<script src="<?php echo $base_url; ?>script.js"></script>
<?php if (!empty($extra_js)): foreach ((array)$extra_js as $js): ?>
<script src="<?php echo htmlspecialchars($js); ?>"></script>
<?php endforeach; endif; ?>

</body>
</html>