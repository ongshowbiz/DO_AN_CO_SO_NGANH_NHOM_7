document.addEventListener('DOMContentLoaded', () => {

    // DARK MODE TOGGLE

    const darkModeBtn = document.getElementById('dark-mode-toggle');
    const body = document.body;

    // Áp dụng theme đã lưu khi DOM sẵn sàng
    function applyTheme(theme) {
        if (theme === 'dark') {
            body.classList.add('dark-mode');
        } else {
            body.classList.remove('dark-mode');
        }
        // Xóa class preload (đã hoàn thành việc ngăn flash)
        document.documentElement.classList.remove('dark-mode-preload');
    }

    // Lấy theme từ localStorage, mặc định là 'light'
    const savedTheme = localStorage.getItem('th-theme') || 'light';
    applyTheme(savedTheme);

    // Xử lý click nút toggle
    if (darkModeBtn) {
        darkModeBtn.addEventListener('click', () => {
            const isDark = body.classList.contains('dark-mode');
            const newTheme = isDark ? 'light' : 'dark';
            applyTheme(newTheme);
            localStorage.setItem('th-theme', newTheme);
        });
    }

    // TÍNH NĂNG RESPONSIVE NAVBAR CHUYỂN THÀNH SIDEBAR MENU

    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const closeMenuBtn = document.getElementById('close-menu-btn');
    const navbar = document.getElementById('navbar');
    const overlay = document.getElementById('overlay');

    // Hàm mở/đóng menu
    function toggleMenu() {
        navbar.classList.toggle('active');

        if (navbar.classList.contains('active')) {
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        } else {
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleMenu);
    if (closeMenuBtn) closeMenuBtn.addEventListener('click', toggleMenu);
    if (overlay) overlay.addEventListener('click', toggleMenu);



    // TÍNH NĂNG ĐÓNG/MỞ CHATBOX AI

    const chatboxToggle = document.getElementById('chatbox-toggle');
    const chatboxWindow = document.getElementById('chatbox-window');
    const chatboxClose = document.getElementById('chatbox-close');

    if (chatboxToggle) {
        chatboxToggle.addEventListener('click', () => {
            chatboxWindow.classList.toggle('active');

            if (chatboxWindow.classList.contains('active')) {
                chatboxToggle.style.animation = 'none';
                setTimeout(() => document.getElementById('chat-input').focus(), 300);
            }
        });
    }

    if (chatboxClose) {
        chatboxClose.addEventListener('click', () => {
            chatboxWindow.classList.remove('active');
        });
    }

    // LOGIC HOẠT ĐỘNG CỦA CHATBOX AI

    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('send-btn');
    const chatboxMessages = document.getElementById('chatbox-messages');

    if (chatboxMessages) {
        const typingIndicator = document.createElement('div');
        typingIndicator.className = 'typing-indicator';
        typingIndicator.innerHTML = '<span></span><span></span><span></span>';
        chatboxMessages.appendChild(typingIndicator);

        function addMessage(message, sender) {
            typingIndicator.style.display = 'none';

            const msgDiv = document.createElement('div');
            msgDiv.className = `message ${sender}-message`;
            msgDiv.textContent = message;

            chatboxMessages.insertBefore(msgDiv, typingIndicator);
            chatboxMessages.scrollTop = chatboxMessages.scrollHeight;
        }

        async function processAiResponse(userMessage) {
            typingIndicator.style.display = 'flex';
            chatboxMessages.scrollTop = chatboxMessages.scrollHeight;

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
                } else if (text.includes('dark') || text.includes('tối') || text.includes('theme')) {
                    aiResponse = "Bạn có thể bật chế độ tối bằng nút toggle trên góc phải header nhé! 🌙";
                }

                addMessage(aiResponse, 'ai');
            }, 1500);
        }

        function handleSend() {
            const message = chatInput.value.trim();
            if (message) {
                addMessage(message, 'user');
                chatInput.value = '';
                processAiResponse(message);
            }
        }

        if (sendBtn) sendBtn.addEventListener('click', handleSend);
        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') handleSend();
            });
        }
    }

    // NÚT BACK TO TOP

    const backToTopBtn = document.createElement('button');
    backToTopBtn.id = 'back-to-top';
    backToTopBtn.title = 'Lên đầu trang';
    backToTopBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    document.body.appendChild(backToTopBtn);

    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});