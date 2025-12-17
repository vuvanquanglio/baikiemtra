<?php
include 'config.php';
include 'functions.php';

checkUser(); // Phải đăng nhập mới vào được

$role = getUserRole();

// Lấy full_name từ session hoặc từ database nếu chưa có
if (!isset($_SESSION['full_name'])) {
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['full_name'] = $user['full_name'] ?? 'Người dùng';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bảng Điều Khiển - Thư Viện Mquang.Vux </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .dashboard-card {
            transition: all 0.3s;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .dashboard-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .btn-dashboard {
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 12px;
            min-width: 220px;
        }
        .welcome-text {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
        }
    </style>
</head>
<body class="bg-light">

    <?php include 'navbar.php'; // Thanh menu đẹp đã có ?>

    <div class="container my-5 py-5">
        <div class="text-center mb-5 animate__animated animate__fadeInDown">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-tachometer-alt me-3"></i>Bảng Điều Khiển
            </h1>
            <p class="welcome-text mt-4">
                Xin chào, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>! 
                <span class="text-muted">(Vai trò: <span class="badge bg-info text-dark"><?php echo ucfirst($role); ?></span>)</span>
            </p>
        </div>

        <div class="row justify-content-center g-4">
            <?php if ($role === 'admin'): ?>
                <!-- Admin -->
                <div class="col-md-4">
                    <div class="card dashboard-card text-center p-4 animate__animated animate__zoomIn">
                        <i class="fas fa-book fa-3x text-primary mb-3"></i>
                        <h4>Quản Lý Sách</h4>
                        <a href="admin_books.php" class="btn btn-primary btn-dashboard mt-3">Vào Quản Lý</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card text-center p-4 animate__animated animate__zoomIn" style="animation-delay: 0.2s;">
                        <i class="fas fa-users-cog fa-3x text-warning mb-3"></i>
                        <h4>Quản Lý Người Dùng</h4>
                        <a href="admin_users.php" class="btn btn-warning btn-dashboard mt-3">Vào Quản Lý</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card text-center p-4 animate__animated animate__zoomIn" style="animation-delay: 0.4s;">
                        <i class="fas fa-exchange-alt fa-3x text-success mb-3"></i>
                        <h4>Quản Lý Mượn/Trả</h4>
                        <a href="admin_borrows.php" class="btn btn-success btn-dashboard mt-3">Vào Quản Lý</a>
                    </div>
                </div>

            <?php elseif ($role === 'manager'): ?>
    <!-- Manager -->
    <div class="col-md-6 offset-md-0.5">  <!-- Thay offset-md-3 thành offset-md-2 -->
        <div class="card dashboard-card text-center p-5 animate__animated animate__fadeInUp">
            <i class="fas fa-chart-bar fa-4x text-info mb-4"></i>
            <h3>Xem Báo Cáo & Thống Kê</h3>
            <p class="text-muted">Theo dõi tình hình thư viện một cách chi tiết</p>
            <a href="reports.php" class="btn btn-info btn-dashboard btn-lg mt-3">Xem Báo Cáo Ngay</a>
        </div>
    </div>

            <?php elseif ($role === 'user'): ?>
                <!-- User (Độc giả) -->
                <div class="col-md-6">
                    <div class="card dashboard-card text-center p-4 animate__animated animate__fadeInLeft">
                        <i class="fas fa-book-open fa-4x text-success mb-4"></i>
                        <h4>Danh Sách Sách</h4>
                        <p>Tìm và mượn sách yêu thích</p>
                        <a href="books.php" class="btn btn-success btn-dashboard mt-3">Xem Sách</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card dashboard-card text-center p-4 animate__animated animate__fadeInRight">
                        <i class="fas fa-history fa-4x text-purple mb-4"></i>
                        <h4>Lịch Sử Mượn Trả</h4>
                        <p>Xem các sách bạn đã mượn</p>
                        <a href="my_borrows.php" class="btn btn-purple btn-dashboard mt-3">Xem Lịch Sử</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Nút Quay Lại Trang Chủ - Nổi bật ở dưới cùng -->
        <div class="text-center mt-5 pt-4 animate__animated animate__fadeInUp">
            <a href="index.php" class="btn btn-outline-secondary btn-lg px-5 py-3">
                <i class="fas fa-arrow-left me-2"></i>
                Quay Lại Trang Chủ
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>