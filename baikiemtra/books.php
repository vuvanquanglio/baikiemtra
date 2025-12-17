<?php
include 'config.php';
include 'functions.php';
checkUser(); // Phải đăng nhập
if (getUserRole() !== 'user') {
    redirect('dashboard.php'); // Chỉ user mới được xem trang này
}

// Xử lý mượn sách
if (isset($_GET['muon']) && is_numeric($_GET['muon'])) {
    $sach_id = $_GET['muon'];
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT so_luong_con_lai, ten_sach FROM sach WHERE id = ?");
    $stmt->execute([$sach_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($book && $book['so_luong_con_lai'] > 0) {
        $check = $pdo->prepare("SELECT id FROM muon_tra WHERE ma_sach = ? AND ma_nguoi_dung = ? AND tinh_trang IN ('dang_muon', 'da_duyet')");
        $check->execute([$sach_id, $user_id]);
        if ($check->rowCount() == 0) {
            $ngay_muon = date('Y-m-d');
            $ngay_tra_du_kien = date('Y-m-d', strtotime('+14 days'));
            $stmt = $pdo->prepare("INSERT INTO muon_tra (ma_sach, ma_nguoi_dung, ngay_muon, ngay_tra_du_kien, tinh_trang) VALUES (?, ?, ?, ?, 'dang_muon')");
            $stmt->execute([$sach_id, $user_id, $ngay_muon, $ngay_tra_du_kien]);
            $pdo->prepare("UPDATE sach SET so_luong_con_lai = so_luong_con_lai - 1 WHERE id = ?")->execute([$sach_id]);
            $success = "Bạn đã mượn thành công sách: <strong>" . htmlspecialchars($book['ten_sach']) . "</strong>!";
        } else {
            $error = "Bạn đang mượn sách này rồi, không thể mượn thêm!";
        }
    } else {
        $error = "Sách đã hết hoặc không tồn tại!";
    }
}

// Tìm kiếm
$search = '';
if (isset($_GET['q'])) {
    $search = trim($_GET['q']);
}

// Lấy danh sách sách
$sql = "SELECT * FROM sach";
$params = [];
if ($search !== '') {
    $sql .= " WHERE ten_sach LIKE ? OR tac_gia LIKE ? OR mo_ta LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY ten_sach ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh Sách Sách - Thư Viện XYZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .book-card {
            transition: all 0.3s;
            cursor: pointer;
        }
        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .book-img {
            height: 300px;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container my-5 py-5">
        <div class="text-center mb-5 animate__animated animate__fadeInDown">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-book-open me-3"></i>Danh Sách Sách
            </h1>
            <p class="text-muted">Tìm và mượn sách yêu thích của bạn ngay hôm nay!</p>
        </div>

        <!-- Thông báo -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success animate__animated animate__bounceIn text-center"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger animate__animated animate__shake text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Ô tìm kiếm -->
        <div class="row mb-4">
            <div class="col-md-8 offset-md-2">
                <form method="GET" class="d-flex">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                           class="form-control form-control-lg me-2" placeholder="Tìm sách theo tên, tác giả, mô tả...">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                </form>
            </div>
        </div>

        <!-- Danh sách sách -->
        <?php if (count($books) > 0): ?>
            <div class="row g-4">
                <?php foreach ($books as $row): ?>
                    <?php
                    $img = !empty($row['hinh_anh'])
                        ? "linkanh/" . $row['hinh_anh']
                        : "linkanh/no-image.png";
                    ?>
                    <div class="col-md-4 col-lg-3">
                        <!-- Đoạn card bạn muốn dùng - đã được tích hợp hoàn chỉnh -->
                        <a href="book_detail.php?id=<?php echo $row['id']; ?>" class="text-decoration-none">
                            <div class="card animate__animated animate__zoomIn h-100 shadow-sm border-0 book-card">
                                <img src="<?php echo $img; ?>"
                                     class="card-img-top"
                                     alt="<?php echo htmlspecialchars($row['ten_sach']); ?>"
                                     style="height: 300px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title text-dark mb-2">
                                        <?php echo htmlspecialchars($row['ten_sach']); ?>
                                    </h5>
                                    <p class="card-text text-muted small">
                                        Tác giả: <?php echo htmlspecialchars($row['tac_gia']); ?><br>
                                        Năm: <?php echo $row['nam_xuat_ban']; ?><br>
                                        Còn lại: <strong><?php echo $row['so_luong_con_lai']; ?></strong>
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <?php if ($row['so_luong_con_lai'] > 0): ?>
                                        <button class="btn btn-success btn-sm w-100"
                                                onclick="event.stopPropagation(); 
                                                         if(confirm('Bạn có chắc muốn mượn sách:\n<?php echo addslashes(htmlspecialchars($row['ten_sach'])); ?>?')) {
                                                             window.location.href='books.php?muon=<?php echo $row['id']; ?>';
                                                         }">
                                            Mượn sách
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm w-100" disabled>Hết sách</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-5x text-muted mb-4"></i>
                <h3>Không tìm thấy sách nào</h3>
                <p class="text-muted">Thử tìm với từ khóa khác nhé!</p>
            </div>
        <?php endif; ?>

        <!-- Nút quay lại -->
        <div class="text-center mt-5">
            <a href="dashboard.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Quay Lại Bảng Điều Khiển
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>