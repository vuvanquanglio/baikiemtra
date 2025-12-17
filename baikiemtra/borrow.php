<?php
// borrow.php - Mượn sách cho user
include 'config.php';
include 'functions.php';

checkUser();
if (getUserRole() !== 'user') redirect('index.php');

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT so_luong_con_lai FROM sach WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if ($book['so_luong_con_lai'] > 0) {
    $ngay_muon = date('Y-m-d');
	
	$check = $pdo->prepare("SELECT id FROM muon_tra WHERE ma_sach = ? AND ma_nguoi_dung = ? AND tinh_trang IN ('dang_muon', 'da_duyet')");
	$check->execute([$sach_id, $user_id]);
	if ($check->rowCount() > 0) {
    $_SESSION['error'] = "Bạn đã mượn sách \"{$book['ten_sach']}\" rồi, không thể mượn thêm!";
    redirect("book_detail.php?id=$sach_id");
}
    if (isset($_GET['return_datetime']) && !empty($_GET['return_datetime'])) {
    $return_input = trim($_GET['return_datetime']); // định dạng: YYYY-MM-DDTHH:MM

    // Chuyển sang định dạng MySQL DATETIME
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $return_input);
    if ($dt && $dt->format('Y-m-d\TH:i') === $return_input) {
        $ngay_tra_du_kien = $dt->format('Y-m-d H:i:s');
        
        // Kiểm tra ngày trả có hợp lệ không (phải sau ngày hiện tại)
        if ($dt < new DateTime()) {
            $_SESSION['error'] = "Ngày hẹn trả phải sau thời điểm hiện tại!";
            redirect("book_detail.php?id=$sach_id");
        }
    } else {
        // Nếu ngày không hợp lệ → dùng mặc định +7 ngày
        $ngay_tra_du_kien = date('Y-m-d H:i:s', strtotime('+7 days'));
        $_SESSION['warning'] = "Ngày trả không hợp lệ, hệ thống đã đặt mặc định 7 ngày.";
    }
} else {
    // Nếu không có tham số → mặc định +7 ngày
    $ngay_tra_du_kien = date('Y-m-d H:i:s', strtotime('+7 days'));
}
    $stmt = $pdo->prepare("INSERT INTO muon_tra (ma_sach, ma_nguoi_dung, ngay_muon, ngay_tra_du_kien) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $_SESSION['user_id'], $ngay_muon, $ngay_tra_du_kien]);

    $stmt = $pdo->prepare("UPDATE sach SET so_luong_con_lai = so_luong_con_lai - 1 WHERE id = ?");
    $stmt->execute([$id]);

    echo "Mượn thành công!";
} else {
    echo "Sách hết hàng!";
}
redirect('books.php');