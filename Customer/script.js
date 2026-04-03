document.addEventListener('DOMContentLoaded', () => {
    // ---------------------------------------------------------
    // 1. TÍNH NĂNG RESPONSIVE NAVBAR CHUYỂN THÀNH SIDEBAR MENU
    // ---------------------------------------------------------
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const closeMenuBtn = document.getElementById('close-menu-btn');
    const navbar = document.getElementById('navbar');
    const overlay = document.getElementById('overlay');

    // Hàm mở/đóng menu
    function toggleMenu() {
        // Thêm/Xoá class .active để kích hoạt CSS left: 0
        navbar.classList.toggle('active');

        // Hiện lớp phủ
        if (navbar.classList.contains('active')) {
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Ngăn cuộn trang phía sau
        } else {
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto'; // Cho phép cuộn lại
        }
    }

    // Gán sự kiện click
    if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleMenu);
    if (closeMenuBtn) closeMenuBtn.addEventListener('click', toggleMenu);
    if (overlay) overlay.addEventListener('click', toggleMenu);


    // ---------------------------------------------------------
    // 2. TÍNH NĂNG ĐÓNG/MỞ CHATBOX AI
    // ---------------------------------------------------------
    const chatboxToggle = document.getElementById('chatbox-toggle');
    const chatboxWindow = document.getElementById('chatbox-window');
    const chatboxClose = document.getElementById('chatbox-close');

    if (chatboxToggle) {
        chatboxToggle.addEventListener('click', () => {
            chatboxWindow.classList.toggle('active');

            // Xóa animation nảy lên nảy xuống khi đã mở
            if (chatboxWindow.classList.contains('active')) {
                chatboxToggle.style.animation = 'none';
                // Focus vào ô input luôn cho tiện
                setTimeout(() => document.getElementById('chat-input').focus(), 300);
            }
        });
    }

    if (chatboxClose) {
        chatboxClose.addEventListener('click', () => {
            chatboxWindow.classList.remove('active');
        });
    }


    // ---------------------------------------------------------
    // 3. LOGIC HOẠT ĐỘNG CỦA CHATBOX AI 
    // (Bao gồm chi tiết cho PHP backend)
    // ---------------------------------------------------------
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('send-btn');
    const chatboxMessages = document.getElementById('chatbox-messages');

    // Tạo HTML cho biểu tượng đang gõ "..."
    const typingIndicator = document.createElement('div');
    typingIndicator.className = 'typing-indicator';
    typingIndicator.innerHTML = '<span></span><span></span><span></span>';
    chatboxMessages.appendChild(typingIndicator);

    // Hàm thêm tin nhắn vào màn hình chat
    function addMessage(message, sender) {
        // Tạm ẩn typing indicator
        typingIndicator.style.display = 'none';

        const msgDiv = document.createElement('div');
        msgDiv.className = `message ${sender}-message`;
        msgDiv.textContent = message;

        // Chèn trước cái typing indicator
        chatboxMessages.insertBefore(msgDiv, typingIndicator);

        // Cuộn xuống cuối
        chatboxMessages.scrollTop = chatboxMessages.scrollHeight;
    }

    // Hàm xử lý gửi tin nhắn của User tới AI
    // Giải thích chi tiết cho việc liên kết PHP và API:
    /*
     * TRONG THỰC TẾ ĐỂ CHATBOT AI HOẠT ĐỘNG THÔNG MINH BẰNG PHP:
     * 1. Bạn sẽ không xử lý bằng hàm if-else ở JS này.
     * 2. Bạn sẽ gửi tin nhắn của người dùng qua AJAX tới 1 file PHP (ví dụ: api_chat.php).
     * 3. Bên trong file `api_chat.php`:
     *    - Bạn lấy nội dung người dùng: $userMessage = $_POST['message'];
     *    - Bạn sử dụng cURL trong PHP để gửi $userMessage tới API của OpenAI (ChatGPT) 
     *      hoặc Google Gemini (cần có API_KEY).
     *    - Nhận phản hồi JSON từ AI, trích xuất text trả lời.
     *    - Echo trả về chuỗi đó lại cho đoạn Javascript bên dưới.
     * 4. Script bên dưới nhận phản hồi và hiển thị hàm addMessage(data, 'ai').
     */
    async function processAiResponse(userMessage) {
        // Hiện biểu tượng AI đang "suy nghĩ"
        typingIndicator.style.display = 'flex';
        chatboxMessages.scrollTop = chatboxMessages.scrollHeight;

        // Code thực tế gọi PHP (bạn có thể bỏ comment khi đã viết file PHP)
        /*
        try {
            // Gửi dữ liệu tới file PHP bằng fetch API
            const formData = new FormData();
            formData.append('message', userMessage);
            
            const response = await fetch('api_chat.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            // Thêm tin nhắn của AI vào
            addMessage(data.reply, 'ai');
            
        } catch (error) {
            addMessage("Xin lỗi, hệ thống máy chủ đang bận. Bạn thử lại sau nhé!", 'ai');
        }
        */

        // DƯỚI ĐÂY LÀ ĐOẠN MÔ PHỎNG GIẢ LẬP ĐỂ DEMO TRÊN FRONTEND KHI CHƯA CÓ BACKEND:
        setTimeout(() => {
            let aiResponse = "Xin lỗi, hiện tại tôi chưa kết nối với backend. Bạn có thể xây dựng file PHP kết nối API OpenAI để tôi thông minh hơn nhé!";

            const text = userMessage.toLowerCase();
            if (text.includes('chào') || text.includes('hi ') || text === 'hi') {
                aiResponse = "Chào bạn! Chào mừng đến với Truyện Hay. Tôi có thể giúp bạn tìm truyện gì không?";
            } else if (text.includes('tìm truyện') || text.includes('có truyện')) {
                aiResponse = "Web có hơn 10.000 truyện ở các thể loại: Hành động, Tình cảm, Trinh thám, Xuyên không. Bạn thích thể loại nào?";
            } else if (text.includes('nạp') || text.includes('tiền') || text.includes('vip')) {
                aiResponse = "Web hoàn toàn miễn phí! Đăng ký tài khoản sẽ giúp bạn lưu lịch sử đọc và theo dõi truyện mới.";
            } else if (text.includes('lỗi') || text.includes('không đọc được')) {
                aiResponse = "Rất xin lỗi về sự cố. Bạn có thể cho tôi biết truyện nào đang bị lỗi hiển thị hình ảnh không?";
            }

            // Gọi hàm thêm tin nhắn vào DOM
            addMessage(aiResponse, 'ai');
        }, 1500); // Giả vờ mất 1.5s để server AI suy nghĩ trả về
    }

    // Xử lý sự kiện click nút gửi
    function handleSend() {
        const message = chatInput.value.trim();
        if (message) {
            // Hiển thị tin nhắn user
            addMessage(message, 'user');
            chatInput.value = ''; // Xóa ô input

            // Gửi tới AI xử lý
            processAiResponse(message);
        }
    }

    // Lắng nghe sự kiện
    if (sendBtn) sendBtn.addEventListener('click', handleSend);
    if (chatInput) {
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                handleSend();
            }
        });
    }

    // ---------------------------------------------------------
    // 4. NÚT BACK TO TOP
    // ---------------------------------------------------------
    // Tạo nút động và thêm vào body
    const backToTopBtn = document.createElement('button');
    backToTopBtn.id = 'back-to-top';
    backToTopBtn.title = 'Lên đầu trang';
    backToTopBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    document.body.appendChild(backToTopBtn);

    // Hiện/ẩn nút khi cuộn
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    // Click để cuộn lên đầu
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});