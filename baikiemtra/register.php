<?php
// register.php - Trang đăng ký tương tự login, đẹp mắt
include 'config.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = hashPassword($_POST['password']);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $full_name, $email]);
        redirect('login.php');
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { background: linear-gradient(to right, #6a11cb, #2575fc); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .register-card { max-width: 400px; padding: 20px; border-radius: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="card register-card bg-light animate__animated animate__fadeInUp">
        <div class="card-body">
            <h3 class="text-center">Đăng Ký</h3>
            <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Tài Khoản</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Họ Tên</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mật Khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng Ký</button>
            </form>
            <p class="text-center mt-3">Đã có tài khoản? <a href="login.php">Đăng Nhập</a></p>
        </div>
    </div>
</body>
</html>