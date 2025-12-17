<?php
include 'config.php';
include 'functions.php';
checkAdmin(); // Chỉ admin mới được vào

$success = $error = '';

// Xử lý thêm người dùng mới
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($password)) {
        $error = "Mật khẩu không được để trống khi thêm người dùng mới!";
    } else {
        $hashed_password = hashPassword($password);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $full_name, $email, $role]);
            $success = "Thêm người dùng thành công!";
        } catch (PDOException $e) {
            $error = "Lỗi: " . ($e->getCode() == 23000 ? "Tên đăng nhập hoặc email đã tồn tại!" : $e->getMessage());
        }
    }
}

// Xử lý sửa thông tin
if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $role, $id]);
        $success = "Cập nhật thông tin người dùng thành công!";
    } catch (PDOException $e) {
        $error = "Lỗi cập nhật: " . $e->getMessage();
    }
}

// Xử lý đổi mật khẩu
if (isset($_POST['reset_password'])) {
    $id = $_POST['id'];
    $new_password = $_POST['new_password'];
    if (empty($new_password)) {
        $error = "Mật khẩu mới không được để trống!";
    } else {
        $hashed = hashPassword($new_password);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $id]);
        $success = "Đặt lại mật khẩu thành công!";
    }
}

// Xử lý xóa
if (isset($_POST['delete_user'])) {
    $id = $_POST['id'];
    if ($id == $_SESSION['user_id']) {
        $error = "Bạn không thể tự xóa tài khoản của chính mình!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Xóa người dùng thành công!";
    }
}

// Lấy danh sách người dùng
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Người Dùng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); transition: all 0.3s; }
        .badge-role-admin { background-color: #dc3545; }
        .badge-role-manager { background-color: #fd7e14; }
        .badge-role-user { background-color: #28a745; }
    </style>
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h2 class="text-center mb-4 animate__animated animate__fadeInDown">Quản Lý Người Dùng</h2>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form thêm người dùng -->
        <div class="card mb-4 card-hover">
            <div class="card-header bg-primary text-white">
                <h5>Thêm Người Dùng Mới</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label>Tên đăng nhập</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Họ tên</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Vai trò</label>
                            <select name="role" class="form-select" required>
                                <option value="user">User (Độc giả)</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-3 align-self-end">
                            <button type="submit" name="add_user" class="btn btn-success w-100">Thêm Người Dùng</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách người dùng -->
        <div class="card card-hover">
            <div class="card-header bg-dark text-white">
                <h5>Danh Sách Người Dùng</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tên đăng nhập</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $user['id']; ?>">
                                        Sửa
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#passwordModal<?php echo $user['id']; ?>">
                                        Đổi MK
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa người dùng này? Tất cả dữ liệu liên quan sẽ bị xóa!');">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-sm btn-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ĐẶT TẤT CẢ MODAL RA NGOÀI TABLE (CUỐI BODY) ĐỂ HOẠT ĐỘNG ĐÚNG -->
    <?php foreach ($users as $user): ?>
    <!-- Modal Sửa Thông Tin -->
    <div class="modal fade" id="editModal<?php echo $user['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Sửa thông tin: <?php echo htmlspecialchars($user['username']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                        <div class="mb-3">
                            <label>Họ tên</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Vai trò</label>
                            <select name="role" class="form-select" required>
                                <option value="user" <?php echo $user['role']=='user'?'selected':''; ?>>User</option>
                                <option value="manager" <?php echo $user['role']=='manager'?'selected':''; ?>>Manager</option>
                                <option value="admin" <?php echo $user['role']=='admin'?'selected':''; ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="edit_user" class="btn btn-warning">Cập Nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Đổi Mật Khẩu -->
    <div class="modal fade" id="passwordModal<?php echo $user['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Đặt lại mật khẩu: <?php echo htmlspecialchars($user['username']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                        <div class="mb-3">
                            <label>Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="reset_password" class="btn btn-info">Đặt Lại Mật Khẩu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
