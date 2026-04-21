<?php $role_id = $_SESSION['ID_VAITRO'] ?? 0; ?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="index.php" class="brand-link">
    <?php if ($role_id == 1): ?>
    <img src="./anh/admin.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="width: 60px; height: 60px; object-fit: cover; margin-top: -3px; margin-right: 5px;">
    <span class="brand-text font-weight-light">TRANG QUẢN TRỊ</span>
    <?php elseif ($role_id == 3): ?>
    <img src="./anh/supplier.jpg" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="width: 60px; height: 60px; object-fit: cover; margin-top: -3px; margin-right: 5px;">
    <span class="brand-text font-weight-light">NHÀ CUNG CẤP</span>
    <?php elseif ($role_id == 4): ?>
    <img src="./anh/staff.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="width: 60px; height: 60px; object-fit: cover; margin-top: -3px; margin-right: 5px;">
    <span class="brand-text font-weight-light">NHÂN VIÊN</span>
    <?php endif; ?>
  </a>

  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="./index.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <li class="nav-header">QUẢN LÝ TRUYỆN</li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-box-open"></i>
              <p>
                Quản lý truyện
                <i class="right fas fa-angle-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
               <a href="index.php?method=QL_Manga-manga" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Danh sách truyện</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php?method=category-list" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Quản lý Thể Loại</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php?method=film-list" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Số lượng truyện</p>
                </a>
              </li>
            </ul>
          </li>
          <?php if ($role_id == 1): ?>
          <li class="nav-header">QUẢN LÝ HỆ THỐNG</li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>
                Quản lý User
                <i class="right fas fa-angle-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="index.php?method=QL_User-user" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Danh sách User</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php?method=QL_User-role" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Quản lý Vai trò</p>
                </a>
              </li>
            </ul>
          </li>
          <?php endif; ?>
        <?php if ($role_id != 3): ?>
          <li class="nav-header">QUẢN LÝ ĐƠN HÀNG</li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-shopping-cart"></i>
              <p>
                Quản lý Đơn Hàng
                <i class="right fas fa-angle-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="index.php?method=QL_Donhang-order" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Danh sách Đơn Hàng</p>
                </a>
              </li>
              <?php if ($role_id == 1): ?>
              <li class="nav-item">
                <a href="index.php?method=QL_Donhang-report" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Báo cáo doanh thu</p>
                </a>
              </li>
              <?php endif; ?>
            </ul>
          </li>
          <?php endif; ?>
          <?php if ($role_id == 4): ?>
          <li class="nav-header">QUẢN LÝ COMMENT</li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fa-solid fa-comment"></i>
                <p>
                  Quản lý Comment 
                  <i class="right fas fa-angle-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="index.php?method=QL_Cmt-comment" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>bình luận của truyện</p>
                  </a>
                </li>
              </ul>
          </li>
          <?php endif; ?>
    </nav>
  </div>
