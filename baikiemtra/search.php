<?php
// search.php - Tìm kiếm sách
include 'config.php';
include 'functions.php';

// Đặt múi giờ Việt Nam (rất quan trọng để giờ đúng)
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra người dùng đã đăng nhập chưa (tùy chọn: nếu muốn chỉ user mới tìm)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy query từ URL
if (!isset($_GET['query']) || trim($_GET['query']) === '') {
    $search_term = '';
    $results = [];
} else {
    $search_term = trim($_GET['query']);
    
    // Tìm kiếm theo tên sách, tác giả, mô tả, thể loại
    $stmt = $pdo->prepare("
        SELECT * FROM sach 
        WHERE ten_sach LIKE ? 
           OR tac_gia LIKE ? 
           OR mo_ta LIKE ? 
        ORDER BY ten_sach ASC
    ");
    $like = "%$search_term%";
    $stmt->execute([$like, $like, $like]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả tìm kiếm "<?php echo htmlspecialchars($search_term); ?>" - Thư Viện Mquang.Vux</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { background: #f8f9fa; }
        .search-header { background: #007bff; color: white; padding: 30px 0; }
        .book-card {
            transition: all 0.3s;
            cursor: pointer;
            height: 100%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .book-img { height: 300px; object-fit: cover; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Header tìm kiếm -->
    <div class="search-header text-center">
        <div class="container">
            <h1 class="display-5 fw-bold">
                <i class="fas fa-search me-3"></i>Kết Quả Tìm Kiếm
            </h1>
            <p class="lead">
                <?php if ($search_term): ?>
                    Tìm thấy <strong><?php echo count($results); ?></strong> kết quả cho từ khóa: 
                    <span class="badge bg-light text-dark fs-5">"<?php echo htmlspecialchars($search_term); ?>"</span>
                <?php else: ?>
                    Vui lòng nhập từ khóa để tìm kiếm sách.
                <?php endif; ?>
            </p>
            <a href="books.php" class="btn btn-light btn-lg mt-3">
                <i class="fas fa-book me-2"></i>Xem toàn bộ danh sách sách
            </a>
        </div>
    </div>

    <div class="container my-5">
        <?php if ($search_term && count($results) > 0): ?>
            <div class="row g-4">
                <?php foreach ($results as $book): ?>
                    <?php
                    $img = !empty($book['hinh_anh'])
                        ? "linkanh/" . $book['hinh_anh']
                        : "linkanh/no-image.png";
                    ?>
                    <div class="col-md-4 col-lg-3 animate__animated animate__fadeInUp">
                        <!-- Toàn bộ card click vào chi tiết -->
                        <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="text-decoration-none">
                            <div class="card book-card h-100">
                                <img src="<?php echo $img; ?>"
                                     class="card-img-top book-img"
                                     alt="<?php echo htmlspecialchars($book['ten_sach']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title text-dark">
                                        <?php echo htmlspecialchars($book['ten_sach']); ?>
                                    </h5>
                                    <p class="card-text text-muted small">
                                        <strong>Tác giả:</strong> <?php echo htmlspecialchars($book['tac_gia']); ?><br>
                                        <strong>Năm XB:</strong> <?php echo $book['nam_xuat_ban']; ?>
                                    </p>
                                    <p class="card-text text-secondary flex-grow-1">
                                        <?php 
                                        $mo_ta_ngan = $book['mo_ta'] ? mb_substr($book['mo_ta'], 0, 100, 'UTF-8') : 'Chưa có mô tả';
                                        echo htmlspecialchars($mo_ta_ngan) . (mb_strlen($book['mo_ta'] ?? '') > 100 ? '...' : '');
                                        ?>
                                    </p>
                                    <div class="mt-auto">
                                        <span class="badge <?php echo $book['so_luong_con_lai'] > 0 ? 'bg-success' : 'bg-danger'; ?> mb-2">
                                            Còn: <?php echo $book['so_luong_con_lai']; ?> cuốn
                                        </span>
                                        <?php if (isLoggedIn() && getUserRole() === 'user' && $book['so_luong_con_lai'] > 0): ?>
                                            <button class="btn btn-success btn-sm w-100"
                                                    onclick="event.stopPropagation(); 
                                                             if(confirm('Mượn sách: <?php echo addslashes($book['ten_sach']); ?>?')) {
                                                                 window.location.href='borrow.php?id=<?php echo $book['id']; ?>';
                                                             }">
                                                <i class="fas fa-shopping-cart me-2"></i>Mượn Ngay
                                            </button>
                                        <?php elseif ($book['so_luong_con_lai'] <= 0): ?>
                                            <button class="btn btn-secondary btn-sm w-100" disabled>Hết sách</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($search_term): ?>
            <div class="text-center py-5 animate__animated animate__fadeIn">
                <i class="fas fa-search fa-5x text-muted mb-4"></i>
                <h3>Không tìm thấy sách nào phù hợp</h3>
                <p class="text-muted">Thử tìm với từ khóa khác hoặc xem toàn bộ danh sách sách.</p>
                <a href="books.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-book-open me-2"></i>Xem Danh Sách Sách
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>