<?php 
// Chỉ require nếu class Database chưa được load
if (!class_exists('Database')) {
    require_once __DIR__ . '/../../include/db.php';
}
?>

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
    <div id="cart-toast" class="cart-toast" style="display: none;">
        <i class="fas fa-check-circle"></i> <span id="toast-message">Đã thêm vào giỏ hàng!</span>
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


<script src="<?php echo $base_url; ?>script.js"></script>

<script>
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('send-btn');
    const chatMessages = document.getElementById('chatbox-messages');

    // Mở/đóng chatbox
    document.getElementById('chatbox-toggle').addEventListener('click', () => {
        document.getElementById('chatbox-window').style.display = 'flex';
    });
    document.getElementById('chatbox-close').addEventListener('click', () => {
        document.getElementById('chatbox-window').style.display = 'none';
    });

    // Gửi tin nhắn
    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });

    async function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        // 1. In tin nhắn của người dùng ra màn hình
        chatMessages.innerHTML += `
            <div class="message user-message">${message}</div>`;
        chatInput.value = '';
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // 2. Hiện trạng thái AI đang gõ
        const loadingId = 'loading-' + Date.now();
        chatMessages.innerHTML += `
            <div id="${loadingId}" class="message ai-message" style="background:#f1f1f1; margin: 8px 0; padding: 10px; border-radius: 10px; max-width: 80%;">
                <i class="fas fa-circle-notch fa-spin"></i> Đang gõ...
            </div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // 3. Gọi API (Fetch data)
        try {
            // SỬ DỤNG $base_url ĐỂ ĐƯỜNG DẪN LUÔN CHUẨN XÁC
            const response = await fetch('<?php echo $base_url; ?>chat_api.php', { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            });
            
            if (!response.ok) {
                throw new Error('Lỗi máy chủ');
            }

            const data = await response.json();

            // 4. Thay thế chữ "Đang gõ..." bằng câu trả lời thật
            document.getElementById(loadingId).innerHTML = data.reply;
        } catch (error) {
            document.getElementById(loadingId).innerHTML = '<span style="color:red">Lỗi kết nối API. Vui lòng kiểm tra lại!</span>';
            console.error(error);
        }
        
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    function addToCart(id_spmanga, name, price) {
    // Mặc định ở trang chủ khi bấm thêm sẽ là 1 cuốn
    const qty = 1;

    // Gửi request ngầm tới file cart_action.php
    fetch('<?php echo $base_url; ?>shop/cart_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ 
            action: 'add', 
            id_spmanga: id_spmanga, 
            qty: qty 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Hiện thông báo Toast xịn xò
            const toast = document.getElementById('cart-toast');
            const toastMsg = document.getElementById('toast-message');
            
            if (toast && toastMsg) {
                toastMsg.innerText = 'Đã thêm "' + name + '" vào giỏ!';
                toast.style.display = 'block';
                toast.classList.add('show');
                
                // Tự động ẩn sau 3 giây
                setTimeout(() => {
                    toast.classList.remove('show');
                    toast.style.display = 'none';
                }, 3000);
            } else {
                // Sơ cua nếu không có toast
                alert('Đã thêm "' + name + '" vào giỏ hàng!');
            }

            // (Tuỳ chọn) Cập nhật số lượng giỏ hàng trên Header nếu bạn có ID hiển thị
            // document.getElementById('cart-count').innerText = data.total_items;
            
        } else {
            alert(data.message || "Lỗi: Không thể thêm vào giỏ hàng.");
        }
    })
    .catch(error => {
        console.error("Lỗi Fetch:", error);
        alert("Có lỗi kết nối! Vui lòng thử lại.");
    });
}
</script>