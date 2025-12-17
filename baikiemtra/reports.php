<?php
include 'config.php';
include 'functions.php';

checkManagerOrAdmin(); // Cho phép cả Manager và Admin xem (Admin cũng có thể dùng để xem báo cáo)

// Nếu là Manager thì chỉ được xem, không được làm gì khác
$role = getUserRole();

// Lấy dữ liệu báo cáo
// 1. Thống kê sách
$stmt_books = $pdo->query("SELECT COUNT(*) AS tong_sach, SUM(so_luong_con_lai) AS con_lai FROM sach");
$stats_books = $stmt_books->fetch(PDO::FETCH_ASSOC);

// 2. Thống kê người dùng theo role
$stmt_users = $pdo->query("
    SELECT 
        role, 
        COUNT(*) AS so_luong 
    FROM users 
    GROUP BY role
");
$user_stats = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// 3. Thống kê giao dịch mượn trả
$stmt_borrows = $pdo->query("
    SELECT 
        tinh_trang, 
        COUNT(*) AS so_luong 
    FROM muon_tra 
    GROUP BY tinh_trang
");
$borrow_stats = $stmt_borrows->fetchAll(PDO::FETCH_ASSOC);

// 4. Danh sách sách chi tiết
$books = $pdo->query("SELECT * FROM sach ORDER BY ten_sach")->fetchAll(PDO::FETCH_ASSOC);

// 5. Danh sách người dùng
$users = $pdo->query("SELECT id, username, full_name, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// 6. Danh sách giao dịch mượn trả đầy đủ
$borrows = $pdo->query("
    SELECT mt.*, s.ten_sach, u.full_name, u.username 
    FROM muon_tra mt
    JOIN sach s ON mt.ma_sach = s.id
    JOIN users u ON mt.ma_nguoi_dung = u.id
    ORDER BY mt.ngay_muon DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo Cáo & Thống Kê - Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); transition: all 0.3s; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .badge-role-admin { background-color: #dc3545; }
        .badge-role-manager { background-color: #fd7e14; }
        .badge-role-user { background-color: #28a745; }
        .table th { background-color: #f8f9fa; }
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-5 animate__animated animate__fadeInDown">
            <i class="fas fa-chart-bar me-2"></i> Báo Cáo & Thống Kê Hệ Thống Thư Viện
        </h2>

        <!-- Nút in báo cáo -->
        <div class="text-end mb-4 no-print">
            <button onclick="window.print()" class="btn btn-success btn-lg">
                <i class="fas fa-print"></i> In Báo Cáo
            </button>
            <a href="dashboard.php" class="btn btn-secondary btn-lg ms-2">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="row mb-5">
            <div class="col-md-4 mb-3">
                <div class="card stat-card card-hover text-center p-4 animate__animated animate__fadeInLeft">
                    <h3><i class="fas fa-book fa-2x mb-3"></i></h3>
                    <h4><?php echo $stats_books['tong_sach']; ?> cuốn sách</h4>
                    <p>Còn lại: <?php echo $stats_books['con_lai']; ?> cuốn</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card card-hover text-center p-4 animate__animated animate__fadeInUp">
                    <h3><i class="fas fa-users fa-2x mb-3"></i></h3>
                    <h4><?php 
                        $total_users = array_sum(array_column($user_stats, 'so_luong'));
                        echo $total_users;
                    ?> người dùng</h4>
                    <p><?php 
                        foreach($user_stats as $us) {
                            echo ucfirst($us['role']) . ": " . $us['so_luong'] . " | ";
                        }
                    ?></p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card card-hover text-center p-4 animate__animated animate__fadeInRight">
                    <h3><i class="fas fa-exchange-alt fa-2x mb-3"></i></h3>
                    <h4><?php echo array_sum(array_column($borrow_stats, 'so_luong')); ?> giao dịch</h4>
                    <p><?php 
                        foreach($borrow_stats as $bs) {
                            echo str_replace('_', ' ', ucfirst($bs['tinh_trang'])) . ": " . $bs['so_luong'] . " | ";
                        }
                    ?></p>
                </div>
            </div>
        </div>

        <!-- Báo cáo 1: Danh sách sách -->
        <div class="card mb-4 card-hover animate__animated animate__fadeIn">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-book"></i> Danh Sách Sách Trong Thư Viện</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tên sách</th>
                                <th>Tác giả</th>
                                <th>Năm XB</th>
                                <th>Số lượng còn</th>
                                <th>Mô tả</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?php echo $book['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($book['ten_sach']); ?></strong></td>
                                <td><?php echo htmlspecialchars($book['tac_gia']); ?></td>
                                <td><?php echo $book['nam_xuat_ban']; ?></td>
                                <td><span class="badge <?php echo $book['so_luong_con_lai'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $book['so_luong_con_lai']; ?>
                                </span></td>
                                <td><?php echo htmlspecialchars($book['mo_ta'] ?: '-'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Báo cáo 2: Danh sách người dùng -->
        <div class="card mb-4 card-hover animate__animated animate__fadeIn">
            <div class="card-header bg-info text-white">
                <h5><i class="fas fa-users"></i> Danh Sách Người Dùng</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tên đăng nhập</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Ngày đăng ký</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge badge-role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Báo cáo 3: Lịch sử mượn trả -->
        <div class="card card-hover animate__animated animate__fadeIn">
            <div class="card-header bg-dark text-white">
                <h5><i class="fas fa-history"></i> Lịch Sử Giao Dịch Mượn/Trả</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Người mượn</th>
                                <th>Sách</th>
                                <th>Ngày mượn</th>
                                <th>Trả dự kiến</th>
                                <th>Trả thực tế</th>
                                <th>Tình trạng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($borrows as $row): ?>
                            <tr <?php echo $row['tinh_trang'] === 'qua_han' ? 'class="table-danger"' : ''; ?>>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['full_name'] . ' (@' . $row['username'] . ')'); ?></td>
                                <td><?php echo htmlspecialchars($row['ten_sach']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['ngay_muon'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['ngay_tra_du_kien'])); ?></td>
                                <td><?php echo $row['ngay_tra_thuc_te'] ? date('d/m/Y', strtotime($row['ngay_tra_thuc_te'])) : '-'; ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo $row['tinh_trang'] === 'da_tra' ? 'bg-success' : 
                                            ($row['tinh_trang'] === 'qua_han' ? 'bg-danger' : 'bg-warning');
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $row['tinh_trang'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 text-muted no-print">
            <small>Báo cáo được tạo ngày <?php echo date('d/m/Y H:i'); ?> | Hệ thống quản lý thư viện trực tuyến</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>