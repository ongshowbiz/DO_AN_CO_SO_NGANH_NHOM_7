<?php
require_once __DIR__ . '/../../include/db.php';
$db = new Database();

// --- 1. THỐNG KÊ TỔNG QUÁT ---
$db->query("SELECT COUNT(*) as total_manga FROM manga");
$total_manga = $db->single()['total_manga'];

$db->query("SELECT COUNT(*) as total_order FROM don_hang");
$total_order = $db->single()['total_order'];

$db->query("SELECT COUNT(*) as total_user FROM taikhoan WHERE ID_VAITRO = 2");
$total_user = $db->single()['total_user'];

// --- 2. DỮ LIỆU BIỂU ĐỒ CỘT (Đơn hàng 7 ngày gần đây) ---
$db->query("
    SELECT DATE(ngay_dat) as date, COUNT(*) as count 
    FROM don_hang 
    WHERE ngay_dat >= DATE(NOW() - INTERVAL 7 DAY)
    GROUP BY DATE(ngay_dat) 
    ORDER BY DATE(ngay_dat) ASC
");
$ordersData = $db->resultSet();
$orderDates = [];
$orderCounts = [];
foreach ($ordersData as $row) {
    // Chỉ lấy phần "Ngày/Tháng" cho gọn biểu đồ
    $orderDates[] = date('d/m', strtotime($row['date']));
    $orderCounts[] = (int)$row['count'];
}

// --- 3. DỮ LIỆU BIỂU ĐỒ ĐƯỜNG (Người dùng mới 7 ngày) ---
$db->query("
    SELECT DATE(NGAYLAP) as date, COUNT(*) as count 
    FROM taikhoan 
    WHERE NGAYLAP >= DATE(NOW() - INTERVAL 7 DAY)
    GROUP BY DATE(NGAYLAP) 
    ORDER BY DATE(NGAYLAP) ASC
");
$usersData = $db->resultSet();
$userDates = [];
$userCounts = [];
foreach ($usersData as $row) {
    $userDates[] = date('d/m', strtotime($row['date']));
    $userCounts[] = (int)$row['count'];
}

// Nếu 7 ngày chưa có ai đăng kí/đơn hàng, thì trả mảng mặc định để chart ko bị lỗi
if (empty($orderDates)) { $orderDates = [date('d/m')]; $orderCounts = [0]; }
if (empty($userDates)) { $userDates = [date('d/m')]; $userCounts = [0]; }

// --- 4. TRUYỆN MỚI CẬP NHẬT ---
$db->query("
    SELECT m.manga_name, c.so_chuong, c.tieu_de_chuong, c.ngay_dang 
    FROM chap c
    JOIN manga m ON c.id_manga = m.id_manga
    ORDER BY c.ngay_dang DESC 
    LIMIT 6
");
$latestChaps = $db->resultSet();

// --- 5. ĐÁNH GIÁ (COMMENT) MỚI NHẤT ---
$db->query("
    SELECT c.noi_dung, c.ngay_tao, t.TENTAIKHOAN, m.manga_name 
    FROM comment c
    JOIN taikhoan t ON c.id_taikhoan = t.ID_TAIKHOAN
    JOIN manga m ON c.id_manga = m.id_manga
    ORDER BY c.ngay_tao DESC 
    LIMIT 6
");
$latestComments = $db->resultSet();
?>
<div class="dashboard-wrap">
    <!-- 1. HÀNG THỐNG KÊ (3 Ô) -->
    <div class="stat-boxes">
        <div class="stat-box">
            <div class="stat-icon"><i class="fas fa-book-open"></i></div>
            <div class="stat-info">
                <h3><?= number_format($total_manga) ?></h3>
                <p>Tổng Truyện</p>
            </div>
        </div>
        <div class="stat-box green">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-info">
                <h3><?= number_format($total_order) ?></h3>
                <p>Đơn Hàng</p>
            </div>
        </div>
        <div class="stat-box red">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3><?= number_format($total_user) ?></h3>
                <p>Khách Hàng</p>
            </div>
        </div>
    </div>

    <!-- 2. HÀNG BIỂU ĐỒ (2 Ô) -->
    <div class="charts-row">
        <div class="chart-card">
            <h4>Đơn hàng 7 ngày qua</h4>
            <canvas id="ordersChart" width="400" height="220"></canvas>
        </div>
        <div class="chart-card">
            <h4>Người dùng đăng ký 7 ngày qua</h4>
            <canvas id="usersChart" width="400" height="220"></canvas>
        </div>
    </div>

    <!-- 3. HÀNG HOẠT ĐỘNG (2/3 và 1/3) -->
    <div class="bottom-row">
        <!-- Bên TRÁI chiếm 2/3: Truyện / Chap mới cập nhật -->
        <div class="activity-card">
            <h4><i class="fas fa-history" style="color: #5451a6; margin-right: 6px;"></i> Quản lý Cập Nhật Gần Đây</h4>
            <?php foreach($latestChaps as $c): ?>
            <div class="activity-item">
                <div class="act-time"><?= date('d/m', strtotime($c['ngay_dang'])) ?></div>
                <div class="act-desc">
                    <strong><?= htmlspecialchars($c['manga_name']) ?></strong> cập nhật biểu tượng chương <?= htmlspecialchars($c['so_chuong']) ?> 
                    <br><span class="muted">Tên chap: <?= htmlspecialchars($c['tieu_de_chuong']) ?> • <?= date('H:i', strtotime($c['ngay_dang'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($latestChaps)) echo "<p style='color:#777;'>Chưa có dữ liệu cập nhật.</p>"; ?>
        </div>

        <!-- Bên PHẢI chiếm 1/3: Comment mới -->
        <div class="activity-card">
            <h4><i class="fas fa-comments" style="color: #e74c3c; margin-right: 6px;"></i> Đánh Giá Mới</h4>
            <?php foreach($latestComments as $cmt): ?>
            <div class="activity-item" style="flex-direction: column; align-items: flex-start; gap: 6px;">
                <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                    <strong style="color: #e74c3c; font-size: 14px;"><?= htmlspecialchars($cmt['TENTAIKHOAN']) ?></strong>
                    <span class="act-time"><?= date('d/m H:i', strtotime($cmt['ngay_tao'])) ?></span>
                </div>
                <div class="act-desc">
                    <?= htmlspecialchars(mb_strimwidth($cmt['noi_dung'], 0, 70, "...")) ?> <br>
                    <span class="muted">Truyện: <?= htmlspecialchars(mb_strimwidth($cmt['manga_name'], 0, 20, "...")) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($latestComments)) echo "<p style='color:#777;'>Khách hàng chưa bình luận gì.</p>"; ?>
        </div>
    </div>
</div>

<!-- ============================================= -->
<!-- Thêm thư viện Chart.js để vẽ biểu đồ -->
<!-- ============================================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // ------------------------------------------
    // Dữ liệu tạo biểu đồ từ CSDL (Dành cho Chart)
    const orderLabels = <?= json_encode($orderDates) ?>;
    const orderData   = <?= json_encode($orderCounts) ?>;

    const userLabels  = <?= json_encode($userDates) ?>;
    const userData    = <?= json_encode($userCounts) ?>;

    // ------------------------------------------
    // Cấu hình Biểu Đồ Cột (Bar Chart) - Đơn Hàng
    const ctxOrder = document.getElementById('ordersChart').getContext('2d');
    new Chart(ctxOrder, {
        type: 'bar',
        data: {
            labels: orderLabels,
            datasets: [{
                label: 'Số đơn hàng',
                data: orderData,
                backgroundColor: 'rgba(46, 204, 113, 0.7)',
                borderColor: '#2ecc71',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { display: false } // Ẩn label chú thích ở trên biểu đồ vì đã có Title
            }
        }
    });

    // ------------------------------------------
    // Cấu hình Biểu Đồ Đường (Line Chart) - Đăng Ký Mới
    const ctxUser = document.getElementById('usersChart').getContext('2d');
    new Chart(ctxUser, {
        type: 'line',
        data: {
            labels: userLabels,
            datasets: [{
                label: 'Số tài khoản lập mới',
                data: userData,
                backgroundColor: 'rgba(52, 152, 219, 0.2)',
                borderColor: '#3498db',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#3498db'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
