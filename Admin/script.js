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

// Nút đóng/mở Sidebar trên Header
const sidebarToggleBtn = document.querySelector('[data-widget="pushmenu"]');
if (sidebarToggleBtn) {
    sidebarToggleBtn.addEventListener('click', function (e) {
        e.preventDefault(); // Ngăn trình duyệt chuyển hướng khi bấm thẻ <a>
        document.body.classList.toggle('sidebar-collapse');
    });
}
