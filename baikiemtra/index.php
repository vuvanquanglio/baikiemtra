<?php
include 'config.php';
include 'functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thư Viện Trực Tuyến</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { font-family: 'Arial', sans-serif; background: #f8f9fa; }
        .navbar { background: #007bff; }
        .carousel-item img { height: 500px; object-fit: cover; }
        .card { transition: transform 0.3s; }
        .card:hover { transform: scale(1.05); }
        .search-bar { max-width: 600px; margin: auto; }
        .footer { background: #343a40; color: white; padding: 20px; text-align: center; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Thư Viện Mquang.Vux</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!isLoggedIn()): ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Đăng Nhập</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Đăng Ký</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Bảng Điều Khiển</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Đăng Xuất</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

   <!-- Carousel sách nổi bật với hoạt ảnh -->
<style>
    #carouselExample .carousel-item img {
        height: 500px;
        object-fit: cover;
    }
</style>

<div id="carouselExample"
     class="carousel slide animate__animated animate__fadeIn"
     data-bs-ride="carousel"
     data-bs-interval="3000">

    <div class="carousel-inner">

        <?php
        $banners = [
            'banner/1.jpg',
            'banner/2.jpg',
            'banner/3.jpg'
        ];

        $isActive = true;
        foreach ($banners as $img):
        ?>
            <div class="carousel-item <?php echo $isActive ? 'active' : ''; ?>">
                <img src="<?php echo $img; ?>" class="d-block w-100" alt="Banner">
            </div>
        <?php
            $isActive = false;
        endforeach;
        ?>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>
    <!-- Tìm kiếm sách -->
    <div class="container my-5">
        <h2 class="text-center animate__animated animate__bounceIn">Tìm Kiếm Sách</h2>
        <form class="search-bar" method="GET" action="search.php">
            <div class="input-group">
                <input type="text" name="query" class="form-control" placeholder="Tìm sách theo tên hoặc tác giả...">
                <button class="btn btn-primary" type="submit">Tìm</button>
            </div>
        </form>
    </div>

    <!-- Danh sách sách mẫu -->
    <div class="container">
    <h3 class="text-center">Sách Phổ Biến</h3>
   <div class="row">
    <?php
    $stmt = $pdo->query("SELECT * FROM sach WHERE hot = 1 LIMIT 3");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
        $img = !empty($row['hinh_anh'])
            ? "linkanh/" . $row['hinh_anh']
            : "linkanh/no-image.png";
    ?>
        <div class="col-md-4 mb-4">
            <!-- Link bao quanh toàn bộ card để click bất kỳ đâu cũng vào chi tiết -->
            <a href="book_detail.php?id=<?php echo $row['id']; ?>" class="text-decoration-none">
                <div class="card animate__animated animate__zoomIn h-100 shadow-sm border-0">
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
                        <?php if (isLoggedIn() && getUserRole() === 'user'): ?>
                            <button class="btn btn-success btn-sm w-100" 
                                    onclick="event.stopPropagation(); window.location.href='borrow.php?id=<?php echo $row['id']; ?>'">
                                Mượn sách
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
    <?php endwhile; ?>
</div>
</div>
    <footer class="footer mt-5">
        &copy; 2025 Thư Viện Mquang.vux All rights reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>