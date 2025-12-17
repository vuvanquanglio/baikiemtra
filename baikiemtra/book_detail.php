<?php
include 'config.php';
include 'functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

// Lấy thông tin sách
$stmt = $pdo->prepare("SELECT * FROM sach WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    echo "<div class='container my-5 text-center'><h3>Không tìm thấy sách!</h3><a href='index.php' class='btn btn-primary'>Quay lại trang chủ</a></div>";
    exit();
}

// Xử lý ảnh
$img = !empty($book['hinh_anh'])
    ? "linkanh/" . $book['hinh_anh']
    : "linkanh/no-image.png";

// Thiết lập giới hạn cho datetime-local
$now = new DateTime();
$min_datetime = (clone $now)->modify('+1 hour');
$max_datetime = (clone $now)->modify('+30 days');

$min_attr = $min_datetime->format('Y-m-d\TH:i');
$max_attr = $max_datetime->format('Y-m-d\TH:i');

$default_datetime = (clone $now)->modify('+14 days');
$default_attr = $default_datetime->format('Y-m-d\TH:i');

$min_display = $min_datetime->format('d/m/Y H:i');
$max_display = $max_datetime->format('d/m/Y H:i');
$default_display = $default_datetime->format('d/m/Y H:i');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($book['ten_sach']); ?> - Thư Viện Mquang.Vux</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .book-img { max-height: 500px; object-fit: contain; }
        .info-label { font-weight: bold; color: #495057; }
        .return-date-box {
            background: #e3f2fd;
            border-left: 5px solid #1976d2;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <div class="row">
            <!-- Ảnh sách -->
            <div class="col-md-4 text-center">
                <img src="<?php echo $img; ?>" class="img-fluid book-img shadow rounded" alt="<?php echo htmlspecialchars($book['ten_sach']); ?>">
            </div>

            <!-- Thông tin chi tiết -->
            <div class="col-md-8">
                <h1 class="mb-4"><?php echo htmlspecialchars($book['ten_sach']); ?></h1>

                <p class="info-label">Tác giả:</p>
                <p class="ms-3"><?php echo htmlspecialchars($book['tac_gia']); ?></p>

                <p class="info-label">Năm xuất bản:</p>
                <p class="ms-3"><?php echo $book['nam_xuat_ban']; ?></p>

                <p class="info-label">Thể loại:</p>
                <p class="ms-3"><?php echo htmlspecialchars($book['the_loai'] ?? 'Không rõ'); ?></p>

                <p class="info-label">Số lượng còn lại:</p>
                <p class="ms-3">
                    <span class="badge <?php echo $book['so_luong_con_lai'] > 0 ? 'bg-success' : 'bg-danger'; ?> fs-6 px-4 py-2">
                        <?php echo $book['so_luong_con_lai']; ?> cuốn
                    </span>
                </p>

                <p class="info-label">Mô tả:</p>
                <p class="ms-3"><?php echo nl2br(htmlspecialchars($book['mo_ta'] ?? 'Chưa có mô tả.')); ?></p>

                <!-- Phần chọn ngày + giờ trả (chỉ có input, KHÔNG có nút mượn ở đây nữa) -->
                <?php if (isLoggedIn() && getUserRole() === 'user' && $book['so_luong_con_lai'] > 0): ?>
                    <div class="return-date-box mb-5">
                        <h4 class="mb-4 text-primary">
                            <i class="fas fa-calendar-check me-3"></i>Chọn thời gian hẹn trả sách
                        </h4>
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <label for="return_datetime" class="form-label fw-bold fs-5">
                                    Ngày và giờ dự kiến trả sách:
                                </label>
                                <input type="datetime-local"
                                       id="return_datetime"
                                       class="form-control form-control-lg"
                                       min="<?php echo $min_attr; ?>"
                                       max="<?php echo $max_attr; ?>"
                                       value="<?php echo $default_attr; ?>"
                                       required>
                                <div class="form-text mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Bạn có thể chọn từ <strong><?php echo $min_display; ?></strong> 
                                    đến <strong><?php echo $max_display; ?></strong>.<br>
                                    <small class="text-muted">Mặc định: <?php echo $default_display; ?> (14 ngày sau)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif (isLoggedIn() && getUserRole() === 'user'): ?>
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-ban fa-2x me-3"></i>
                        <strong>Rất tiếc! Sách đã hết, không thể mượn lúc này.</strong>
                    </div>
                <?php endif; ?>

                <!-- Chỉ có 1 nút Mượn sách ngay ở đây, nằm ngang với Quay lại -->
                <div class="d-flex flex-wrap gap-3 align-items-center mt-4">
                    <a href="index.php" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại trang chủ
                    </a>

                    <?php if (isLoggedIn() && getUserRole() === 'user' && $book['so_luong_con_lai'] > 0): ?>
                        <button type="button" class="btn btn-success btn-lg px-5" id="borrowBtn">
                            <i class="fas fa-book-reader me-2"></i>Mượn sách ngay
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý nút mượn duy nhất ở dưới cùng
        document.getElementById('borrowBtn')?.addEventListener('click', function() {
            const returnDateTime = document.getElementById('return_datetime').value;
            if (!returnDateTime) {
                alert('Vui lòng chọn ngày và giờ hẹn trả sách!');
                return;
            }
            window.location.href = `borrow.php?id=<?php echo $book['id']; ?>&return_datetime=${returnDateTime}`;
        });
    </script>
</body>
</html>