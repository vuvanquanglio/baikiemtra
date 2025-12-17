<?php
// navbar.php
// Đảm bảo session và functions đã được load (nếu chưa thì include ở trang chính)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'functions.php'; // Nếu chưa include ở trang chính
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
    <div class="container">
        <!-- Logo / Tên thư viện -->
        <a class="navbar-brand fw-bold fs-4" href="index.php">
            <i class="fas fa-book-open me-2"></i>Thư Viện Mqang.Vux
        </a>

        <!-- Nút collapse cho mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <!-- Trang chủ luôn hiển thị -->
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active fw-bold' : ''; ?>" 
                       href="index.php">
                        <i class="fas fa-home me-1"></i>Trang Chủ
                    </a>
                </li>

                <?php if (!isLoggedIn()): ?>
                    <!-- Chưa đăng nhập -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Đăng Nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light ms-2 px-4" href="register.php">Đăng Ký</a>
                    </li>

                <?php else: ?>
                    <!-- Đã đăng nhập -->
                    <?php if (getUserRole() === 'user'): ?>
                        <!-- User (Độc giả) -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active fw-bold' : ''; ?>" 
                               href="books.php">Danh Sách Sách</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_borrows.php' ? 'active fw-bold' : ''; ?>" 
                               href="my_borrows.php">Lịch Sử Mượn</a>
                        </li>

                    <?php elseif (getUserRole() === 'manager'): ?>
                        <!-- Manager -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active fw-bold' : ''; ?>" 
                               href="reports.php">
                                <i class="fas fa-chart-bar me-1"></i>Báo Cáo
                            </a>
                        </li>

                    <?php elseif (getUserRole() === 'admin'): ?>
                        <!-- Admin -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-tools me-1"></i>Quản Trị
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="admin_books.php">Quản Lý Sách</a></li>
                                <li><a class="dropdown-item" href="admin_users.php">Quản Lý Người Dùng</a></li>
                                <li><a class="dropdown-item" href="admin_borrows.php">Quản Lý Mượn/Trả</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="reports.php">Xem Báo Cáo</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Thông tin người dùng + Đăng xuất -->
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" 
                           data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-lg me-2"></i>
                            <span class="d-none d-lg-inline">
                                <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?>
                                <small class="text-light opacity-75 d-block">
                                    (<?php echo ucfirst(getUserRole()); ?>)
                                </small>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Bảng Điều Khiển
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng Xuất
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Để tránh nội dung bị che bởi navbar fixed-top -->
<div style="padding-top: 80px;"></div>

<!-- Font Awesome cho icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>