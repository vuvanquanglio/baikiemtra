<?php
include 'config.php';
include 'functions.php';

checkAdmin(); // Chỉ admin mới vào được

$success = $error = '';

// Thư mục lưu ảnh
$upload_dir = 'images/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Xử lý thêm sách
if (isset($_POST['add'])) {
    $ten = trim($_POST['ten_sach']);
    $tac_gia = trim($_POST['tac_gia']);
    $nam = $_POST['nam_xuat_ban'];
    $so_luong = $_POST['so_luong_con_lai'];
    $mo_ta = $_POST['mo_ta'];
    $hot = isset($_POST['hot']) ? 1 : 0;

    $hinh_anh = 'images/default-book.jpg'; // ảnh mặc định nếu không upload

    // Xử lý upload ảnh
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['hinh_anh'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $file['size'] <= 5*1024*1024) { // max 5MB
            $new_name = uniqid('book_') . '.' . $ext;
            $destination = $upload_dir . $new_name;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $hinh_anh = $destination;
            } else {
                $error = "Lỗi khi lưu ảnh!";
            }
        } else {
            $error = "Ảnh không hợp lệ! Chỉ chấp nhận jpg, jpeg, png, gif và dưới 5MB.";
        }
    }

    if (empty($error)) {
        $stmt = $pdo->prepare("INSERT INTO sach (ten_sach, tac_gia, nam_xuat_ban, so_luong_con_lai, mo_ta, hinh_anh, hot) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ten, $tac_gia, $nam, $so_luong, $mo_ta, $hinh_anh, $hot]);
        $success = "Thêm sách thành công!";
    }
}

// Xử lý sửa sách
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $ten = trim($_POST['ten_sach']);
    $tac_gia = trim($_POST['tac_gia']);
    $nam = $_POST['nam_xuat_ban'];
    $so_luong = $_POST['so_luong_con_lai'];
    $mo_ta = $_POST['mo_ta'];
    $hot = isset($_POST['hot']) ? 1 : 0;

    $hinh_anh = $_POST['hinh_anh_hien_tai']; // giữ ảnh cũ nếu không upload mới

    // Xử lý upload ảnh mới
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['hinh_anh'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $file['size'] <= 5*1024*1024) {
            $new_name = uniqid('book_') . '.' . $ext;
            $destination = $upload_dir . $new_name;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Xóa ảnh cũ nếu không phải default
                if ($hinh_anh !== 'images/default-book.jpg' && file_exists($hinh_anh)) {
                    unlink($hinh_anh);
                }
                $hinh_anh = $destination;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE sach SET ten_sach=?, tac_gia=?, nam_xuat_ban=?, so_luong_con_lai=?, mo_ta=?, hinh_anh=?, hot=? WHERE id=?");
    $stmt->execute([$ten, $tac_gia, $nam, $so_luong, $mo_ta, $hinh_anh, $hot, $id]);
    $success = "Cập nhật sách thành công!";
}

// Xử lý xóa sách
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("SELECT hinh_anh FROM sach WHERE id = ?");
    $stmt->execute([$id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("DELETE FROM sach WHERE id = ?");
    $stmt->execute([$id]);

    // Xóa ảnh nếu không phải default
    if ($book && $book['hinh_anh'] !== 'images/default-book.jpg' && file_exists($book['hinh_anh'])) {
        unlink($book['hinh_anh']);
    }

    $success = "Xóa sách thành công!";
}

// Lấy danh sách sách
$stmt = $pdo->query("SELECT * FROM sach ORDER BY created_at DESC");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Sách - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); transition: all 0.3s; }
        .book-img-preview { max-height: 200px; object-fit: cover; }
        .hot-badge { position: absolute; top: 10px; right: 10px; z-index: 2; }
    </style>
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h2 class="text-center mb-4 animate__animated animate__fadeInDown">
            <i class="fas fa-book me-2"></i>Quản Lý Sách
        </h2>

        <?php if ($success): ?>
            <div class="alert alert-success animate__animated animate__bounceIn"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger animate__animated animate__shake"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Form thêm/sửa sách -->
        <div class="card mb-4 card-hover animate__animated animate__fadeInLeft">
            <div class="card-header bg-primary text-white">
                <h5 id="form-title">Thêm Sách Mới</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="hinh_anh_hien_tai" id="hinh_anh_hien_tai" value="images/default-book.jpg">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên sách</label>
                            <input type="text" name="ten_sach" id="ten_sach" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tác giả</label>
                            <input type="text" name="tac_gia" id="tac_gia" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Năm xuất bản</label>
                            <input type="number" name="nam_xuat_ban" id="nam_xuat_ban" class="form-control" min="1000" max="2100" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Số lượng còn lại</label>
                            <input type="number" name="so_luong_con_lai" id="so_luong_con_lai" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ảnh bìa</label>
                            <input type="file" name="hinh_anh" id="hinh_anh" class="form-control" accept="image/*">
                            <div class="mt-2">
                                <img id="preview_img" src="images/default-book.jpg" class="img-fluid book-img-preview rounded" alt="Preview">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea name="mo_ta" id="mo_ta" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="hot" id="hot" value="1">
                                <label class="form-check-label text-danger fw-bold">
                                    <i class="fas fa-fire me-1"></i> Đánh dấu là sách HOT / Nổi bật
                                </label>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" name="add" id="btn_add" class="btn btn-success btn-lg">Thêm Sách</button>
                            <button type="submit" name="edit" id="btn_edit" class="btn btn-warning btn-lg" style="display:none;">Cập Nhật</button>
                            <button type="button" id="btn_cancel" class="btn btn-secondary btn-lg" style="display:none;">Hủy</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách sách -->
        <div class="card card-hover animate__animated animate__fadeInRight">
            <div class="card-header bg-dark text-white">
                <h5>Danh Sách Sách</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Ảnh</th>
                                <th>Tên sách</th>
                                <th>Tác giả</th>
                                <th>Năm XB</th>
                                <th>Còn lại</th>
                                <th>Hot</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                            <tr>
                                <td class="text-center position-relative">
                                    <?php if ($book['hot']): ?>
                                        <span class="badge bg-danger hot-badge">HOT</span>
                                    <?php endif; ?>
                                    <img src="<?php echo htmlspecialchars($book['hinh_anh'] ?: 'images/default-book.jpg'); ?>" 
                                         class="img-thumbnail" style="width:80px; height:100px; object-fit:cover;" alt="Bìa sách">
                                </td>
                                <td><strong><?php echo htmlspecialchars($book['ten_sach']); ?></strong></td>
                                <td><?php echo htmlspecialchars($book['tac_gia']); ?></td>
                                <td><?php echo $book['nam_xuat_ban']; ?></td>
                                <td>
                                    <span class="badge <?php echo $book['so_luong_con_lai'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $book['so_luong_con_lai']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php echo $book['hot'] ? '<i class="fas fa-fire text-danger fa-lg"></i>' : '-'; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-edit me-1"
                                        data-id="<?php echo $book['id']; ?>"
                                        data-ten="<?php echo htmlspecialchars($book['ten_sach']); ?>"
                                        data-tacgia="<?php echo htmlspecialchars($book['tac_gia']); ?>"
                                        data-nam="<?php echo $book['nam_xuat_ban']; ?>"
                                        data-soluong="<?php echo $book['so_luong_con_lai']; ?>"
                                        data-mota="<?php echo htmlspecialchars($book['mo_ta']); ?>"
                                        data-hinh="<?php echo htmlspecialchars($book['hinh_anh']); ?>"
                                        data-hot="<?php echo $book['hot']; ?>">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Xóa sách này? Ảnh bìa cũng sẽ bị xóa!')">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
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
    <script>
        // Preview ảnh khi chọn file
        document.getElementById('hinh_anh').addEventListener('change', function(e) {
            if (e.target.files[0]) {
                document.getElementById('preview_img').src = URL.createObjectURL(e.target.files[0]);
            }
        });

        // Nút sửa
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('form-title').textContent = 'Chỉnh Sửa Sách';
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('ten_sach').value = this.dataset.ten;
                document.getElementById('tac_gia').value = this.dataset.tacgia;
                document.getElementById('nam_xuat_ban').value = this.dataset.nam;
                document.getElementById('so_luong_con_lai').value = this.dataset.soluong;
                document.getElementById('mo_ta').value = this.dataset.mota;
                document.getElementById('hinh_anh_hien_tai').value = this.dataset.hinh;
                document.getElementById('preview_img').src = this.dataset.hinh;
                document.getElementById('hot').checked = (this.dataset.hot == 1);

                document.getElementById('btn_add').style.display = 'none';
                document.getElementById('btn_edit').style.display = 'inline-block';
                document.getElementById('btn_cancel').style.display = 'inline-block';
                window.scrollTo(0, 0);
            });
        });

        // Hủy chỉnh sửa
        document.getElementById('btn_cancel').addEventListener('click', function() {
            document.querySelector('form').reset();
            document.getElementById('preview_img').src = 'images/default-book.jpg';
            document.getElementById('form-title').textContent = 'Thêm Sách Mới';
            document.getElementById('edit_id').value = '';
            document.getElementById('hinh_anh_hien_tai').value = 'images/default-book.jpg';
            document.getElementById('hot').checked = false;

            document.getElementById('btn_add').style.display = 'inline-block';
            document.getElementById('btn_edit').style.display = 'none';
            this.style.display = 'none';
        });
    </script>
</body>
</html>