<?php
include 'config.php';
include 'functions.php';
checkUser(); // Phải đăng nhập
if (getUserRole() !== 'user') {
    redirect('dashboard.php');
}
$user_id = $_SESSION['user_id'];
date_default_timezone_set('Asia/Ho_Chi_Minh');
// XỬ LÝ TRẢ SÁCH
if (isset($_GET['tra']) && is_numeric($_GET['tra'])) {
    $muon_id = (int)$_GET['tra'];

    // Kiểm tra bản ghi thuộc user
    $check = $pdo->prepare("SELECT mt.id, mt.ma_sach, mt.tinh_trang 
                            FROM muon_tra mt 
                            WHERE mt.id = ? AND mt.ma_nguoi_dung = ?");
    $check->execute([$muon_id, $user_id]);
    $record = $check->fetch(PDO::FETCH_ASSOC);

    if ($record && in_array($record['tinh_trang'], ['dang_muon', 'da_duyet'])) {
        $now = date('Y-m-d H:i:s'); // Thời gian thực tế trả

        try {
            // Cập nhật ngày trả thực tế và tình trạng
            $stmt = $pdo->prepare("UPDATE muon_tra 
                                   SET ngay_tra_thuc_te = ?, tinh_trang = 'da_tra' 
                                   WHERE id = ?");
            $stmt->execute([$now, $muon_id]);

            // Tăng lại số lượng sách
            $stmt = $pdo->prepare("UPDATE sach SET so_luong_con_lai = so_luong_con_lai + 1 WHERE id = ?");
            $stmt->execute([$record['ma_sach']]);

            $_SESSION['success'] = "Bạn đã trả sách thành công lúc <strong>" . date('d/m/Y H:i', strtotime($now)) . "</strong>!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Có lỗi khi trả sách. Vui lòng thử lại!";
        }
    } else {
        $_SESSION['error'] = "Không thể trả sách này (đã trả hoặc không hợp lệ).";
    }

    // Redirect về chính trang hiện tại (không ghi tên file)
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Lấy lịch sử mượn trả
$stmt = $pdo->prepare("
    SELECT mt.*, s.ten_sach, s.tac_gia, s.nam_xuat_ban
    FROM muon_tra mt
    JOIN sach s ON mt.ma_sach = s.id
    WHERE mt.ma_nguoi_dung = ?
    ORDER BY mt.ngay_muon DESC
");
$stmt->execute([$user_id]);
$borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kiểm tra quá hạn
foreach ($borrows as &$borrow) {
    if (in_array($borrow['tinh_trang'], ['dang_muon', 'da_duyet'])) {
        $today = date('Y-m-d');
        if ($today > date('Y-m-d', strtotime($borrow['ngay_tra_du_kien']))) {
            $borrow['qua_han'] = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch Sử Mượn Trả - Thư Viện XYZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .borrow-card { transition: all 0.3s; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .borrow-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
        .status-badge { font-size: 1rem; padding: 0.6em 1.2em; }
        .table th { background-color: #f8f9fa; }
    </style>
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container my-5 py-5">
        <div class="text-center mb-5 animate__animated animate__fadeInDown">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-history me-3"></i>Lịch Sử Mượn Trả Sách
            </h1>
            <p class="text-muted">Theo dõi các sách bạn đã và đang mượn từ thư viện</p>
        </div>

        <!-- Thông báo thành công/lỗi -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show text-center">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show text-center">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (count($borrows) > 0): ?>
            <div class="table-responsive animate__animated animate__fadeInUp">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tên Sách</th>
                            <th>Tác Giả</th>
                            <th>Năm XB</th>
                            <th>Ngày Mượn</th>
                            <th>Trả Dự Kiến</th>
                            <th>Trả Thực Tế</th>
                            <th>Tình Trạng</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrows as $index => $row): ?>
                            <tr class="<?php echo isset($row['qua_han']) ? 'table-danger' : ''; ?>">
                                <td><strong><?php echo $index + 1; ?></strong></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['ten_sach']); ?></td>
                                <td><?php echo htmlspecialchars($row['tac_gia']); ?></td>
                                <td><?php echo $row['nam_xuat_ban']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['ngay_muon'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['ngay_tra_du_kien'])); ?></td>
                                <td>
                                    <?php echo $row['ngay_tra_thuc_te'] 
                                        ? date('d/m/Y H:i', strtotime($row['ngay_tra_thuc_te'])) 
                                        : '<span class="text-muted">-</span>'; ?>
                                </td>
                                <td>
                                    <?php if (isset($row['qua_han'])): ?>
                                        <span class="badge bg-danger status-badge">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Quá Hạn
                                        </span>
                                    <?php elseif ($row['tinh_trang'] === 'da_tra'): ?>
                                        <span class="badge bg-success status-badge">
                                            <i class="fas fa-check-circle me-1"></i>Đã Trả
                                        </span>
                                    <?php elseif ($row['tinh_trang'] === 'da_duyet'): ?>
                                        <span class="badge bg-info status-badge">
                                            <i class="fas fa-thumbs-up me-1"></i>Đã Duyệt
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning status-badge">
                                            <i class="fas fa-clock me-1"></i>Đang Mượn
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (in_array($row['tinh_trang'], ['dang_muon', 'da_duyet'])): ?>
                                        <a href="?tra=<?php echo $row['id']; ?>"
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Bạn có chắc chắn muốn trả sách:\n<?php echo addslashes(htmlspecialchars($row['ten_sach'])); ?>?\nThời gian trả sẽ được ghi nhận ngay lập tức.');">
                                            <i class="fas fa-undo me-1"></i>Trả sách ngay
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Đã xử lý</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (in_array(true, array_column($borrows, 'qua_han') ?? [])): ?>
                <div class="alert alert-warning mt-4 animate__animated animate__shake">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Bạn có sách đang bị quá hạn!</strong> Vui lòng trả sách sớm để tránh bị phạt.
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5 animate__animated animate__fadeIn">
                <i class="fas fa-book-reader fa-5x text-muted mb-4"></i>
                <h3>Bạn chưa mượn sách nào</h3>
                <p class="text-muted">Hãy ghé qua danh sách sách và chọn những cuốn hay để mượn nhé!</p>
                <a href="books.php" class="btn btn-primary btn-lg mt-3">
                    <i class="fas fa-book-open me-2"></i>Xem Danh Sách Sách Ngay
                </a>
            </div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-lg px-5">
                <i class="fas fa-arrow-left me-2"></i>Quay Lại Bảng Điều Khiển
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>