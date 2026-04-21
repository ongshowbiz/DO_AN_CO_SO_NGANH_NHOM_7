<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

// 1. Tổng doanh thu tất cả
$db->query("SELECT SUM(tong_tien) as total_revenue, COUNT(id_order) as total_orders FROM don_hang WHERE trang_thai_thanh_toan = 1");
$overall = $db->single();
$total_revenue = $overall['total_revenue'] ?? 0;
$total_orders = $overall['total_orders'] ?? 0;

// 2. Doanh thu theo tháng
$db->query("
    SELECT 
        DATE_FORMAT(ngay_dat, '%m/%Y') as thang, 
        SUM(tong_tien) as doanh_thu, 
        COUNT(id_order) as so_don 
    FROM don_hang 
    WHERE trang_thai_thanh_toan = 1 
    GROUP BY DATE_FORMAT(ngay_dat, '%m/%Y')
    ORDER BY MIN(ngay_dat) DESC
");
$revenue_by_month = $db->resultSet();

// 3. Doanh thu theo ngày (15 ngày gần nhất)
$db->query("
    SELECT 
        DATE(ngay_dat) as ngay, 
        SUM(tong_tien) as doanh_thu, 
        COUNT(id_order) as so_don 
    FROM don_hang 
    WHERE trang_thai_thanh_toan = 1 
    GROUP BY DATE(ngay_dat)
    ORDER BY ngay DESC
    LIMIT 15
");
$revenue_by_day = $db->resultSet();
?>

<div class="um-container">
    <h2 class="um-title" style="margin-bottom: 20px; color: #333; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-chart-line" style="color: #28a745;"></i> Báo Cáo Doanh Thu
    </h2>
    
    <!-- Summary Cards -->
    <div style="display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #28a745;">
            <h4 style="margin: 0; color: #6c757d; font-size: 16px;">Tổng Doanh Thu</h4>
            <h2 style="margin: 10px 0 0; color: #333; font-size: 28px;"><?= number_format($total_revenue, 0, ',', '.') ?>đ</h2>
        </div>
        <div style="flex: 1; min-width: 250px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #007bff;">
            <h4 style="margin: 0; color: #6c757d; font-size: 16px;">Đơn Hàng Giao Dịch Thành Công</h4>
            <h2 style="margin: 10px 0 0; color: #333; font-size: 28px;"><?= $total_orders ?> đơn</h2>
        </div>
    </div>

    <!-- Revenue by Month Table -->
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px;">
        <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 18px; color: #495057;">
            <i class="fas fa-calendar-alt" style="color: #17a2b8;"></i> Doanh Thu Theo Tháng
        </h3>
        <table class="um-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 12px; text-align: left;">Tháng</th>
                    <th style="padding: 12px; text-align: left;">Số Đơn Hàng</th>
                    <th style="padding: 12px; text-align: right;">Doanh Thu</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($revenue_by_month)): ?>
                    <?php foreach($revenue_by_month as $rm): ?>
                    <tr style="border-bottom: 1px solid #e9ecef; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f1f3f5';" onmouseout="this.style.backgroundColor='transparent';">
                        <td style="padding: 12px;"><strong><?= htmlspecialchars($rm['thang']) ?></strong></td>
                        <td style="padding: 12px; color: #555;"><?= $rm['so_don'] ?> đơn</td>
                        <td style="padding: 12px; text-align: right; color: #e53e3e; font-weight: bold;">
                            <?= number_format($rm['doanh_thu'], 0, ',', '.') ?>đ
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="padding: 20px; text-align: center; color: #868e96;">Chưa có dữ liệu giao dịch thành công.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Revenue by Day Table -->
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 18px; color: #495057;">
            <i class="fas fa-clock" style="color: #ffc107;"></i> Doanh Thu Tính Theo Ngày Giao Dịch
        </h3>
        <table class="um-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 12px; text-align: left;">Ngày</th>
                    <th style="padding: 12px; text-align: left;">Số Đơn Hàng</th>
                    <th style="padding: 12px; text-align: right;">Doanh Thu</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($revenue_by_day)): ?>
                    <?php foreach($revenue_by_day as $rd): ?>
                    <tr style="border-bottom: 1px solid #e9ecef; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f1f3f5';" onmouseout="this.style.backgroundColor='transparent';">
                        <td style="padding: 12px;"> 
                            <i class="far fa-calendar-alt" style="color: #6c757d; margin-right: 5px;"></i>
                            <?= date('d/m/Y', strtotime($rd['ngay'])) ?>
                        </td>
                        <td style="padding: 12px; color: #555;"><?= $rd['so_don'] ?> đơn</td>
                        <td style="padding: 12px; text-align: right; color: #28a745; font-weight: bold;">
                            +<?= number_format($rd['doanh_thu'], 0, ',', '.') ?>đ
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="padding: 20px; text-align: center; color: #868e96;">Chưa có dữ liệu giao dịch.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
