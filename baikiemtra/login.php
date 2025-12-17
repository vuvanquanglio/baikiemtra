<?php
// login.php - Trang đăng nhập đẹp với hoạt ảnh
include 'config.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && verifyPassword($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
 	$_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        redirect('dashboard.php');
    } else {
        $error = "Tài khoản hoặc mật khẩu sai!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { background: linear-gradient(to right, #6a11cb, #2575fc); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { max-width: 400px; padding: 20px; border-radius: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="card login-card bg-light animate__animated animate__fadeInUp">
        <div class="card-body">
            <h3 class="text-center">Đăng Nhập</h3>
            <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Tài Khoản</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mật Khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng Nhập</button>
            </form>
            <p class="text-center mt-3">Chưa có tài khoản? <a href="register.php">Đăng Ký</a></p>
        </div>
    </div>
</body>
</html>