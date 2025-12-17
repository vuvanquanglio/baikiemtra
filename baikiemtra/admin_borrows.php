<?php
include 'config.php';
include 'functions.php';

checkAdmin();

// Xử lý cập nhật tình trạng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $tinh_trang = $_POST['tinh_trang'];
    $ngay_tra_thuc_te = ($tinh_trang === 'da_tra') ? date('Y-m-d') : null;

    $stmt = $pdo->prepare("UPDATE muon_tra SET tinh_trang = ?, ngay_tra_thuc_te = ? WHERE id = ?");
    $stmt->execute([$tinh_trang, $ngay_tra_thuc_te, $id]);

    // Nếu trả sách -> tăng số lượng sách
    if ($tinh_trang === 'da_tra') {
        $stmt = $pdo->prepare("UPDATE sach s JOIN muon_tra mt ON s.id = mt.ma_sach SET s.so_luong_con_lai = s.so_luong_con_lai + 1 WHERE mt.id = ?");
        $stmt->execute([$id]);
    }

    $success = "Cập nhật tình trạng thành công!";
}

// Lấy danh sách mượn trả kèm thông tin sách và người dùng
$stmt = $pdo->query("
    SELECT mt.*, s.ten_sach, u.full_name, u.username 
    FROM muon_tra mt
    JOIN sach s ON mt.ma_sach = s.id
    JOIN users u ON mt.ma_nguoi_dung = u.id
    ORDER BY mt.created_at DESC
");
$borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Mượn Trả - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4 animate__animated animate__fadeInDown">Quản Lý Giao Dịch Mượn/Trả</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success animate__animated animate__bounceIn"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card animate__animated animate__fadeIn">
            <div class="card-header bg-dark text-white">
                <h5>Danh Sách Giao Dịch</h5>
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
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($borrows as $row): ?>
                            <tr <?php echo $row['tinh_trang'] === 'qua_han' ? 'class="table-danger"' : ''; ?>>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['full_name'] . ' (@' . $row['username'] . ')'); ?></td>
                                <td><?php echo htmlspecialchars($row['ten_sach']); ?></td>
                                <td><?php echo $row['ngay_muon']; ?></td>
                                <td><?php echo $row['ngay_tra_du_kien']; ?></td>
                                <td><?php echo $row['ngay_tra_thuc_te'] ?? '-'; ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo $row['tinh_trang'] === 'da_tra' ? 'bg-success' : 
                                            ($row['tinh_trang'] === 'qua_han' ? 'bg-danger' : 'bg-warning');
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $row['tinh_trang'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <select name="tinh_trang" class="form-select form-select-sm d-inline w-auto" required>
                                            <option value="dang_muon" <?php echo $row['tinh_trang']==='dang_muon'?'selected':''; ?>>Đang mượn</option>
                                            <option value="da_duyet" <?php echo $row['tinh_trang']==='da_duyet'?'selected':''; ?>>Đã duyệt</option>
                                            <option value="da_tra" <?php echo $row['tinh_trang']==='da_tra'?'selected':''; ?>>Đã trả</option>
                                            <option value="qua_han" <?php echo $row['tinh_trang']==='qua_han'?'selected':''; ?>>Quá hạn</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary">Cập nhật</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>