<?php
session_start();
require '../../vendor/autoload.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'internship_management');
if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $user_id = $_SESSION['user_id'];

    $updateQuery = $conn->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE user_id = ?");
    $updateQuery->bind_param("si", $new_password, $user_id);

    if ($updateQuery->execute()) {
        unset($_SESSION['user_id']); // Xóa session tạm thời
        header("Location: login.php?password_changed=1");
        exit;
    } else {
        $error_message = "Đã xảy ra lỗi khi đổi mật khẩu.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi Mật Khẩu</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Đổi Mật Khẩu</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Mật khẩu mới:</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Nhập mật khẩu mới" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary w-100">Đổi Mật Khẩu</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none">Quay lại trang đăng nhập</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
