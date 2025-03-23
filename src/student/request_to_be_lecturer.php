<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\student\request_to_be_lecturer.php -->
<?php
session_start();
require '../../vendor/autoload.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: ../../auth/login.php');
    exit;
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'internship_management');
if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

// Xử lý gửi yêu cầu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user']['user_id'];

    // Kiểm tra xem yêu cầu đã tồn tại chưa
    $checkRequestQuery = $conn->prepare("SELECT * FROM lecturer_requests WHERE user_id = ?");
    $checkRequestQuery->bind_param("i", $user_id);
    $checkRequestQuery->execute();
    $result = $checkRequestQuery->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Bạn đã gửi yêu cầu trước đó. Vui lòng chờ phản hồi.";
    } else {
        // Thêm yêu cầu mới
        $insertRequestQuery = $conn->prepare("INSERT INTO lecturer_requests (user_id) VALUES (?)");
        $insertRequestQuery->bind_param("i", $user_id);
        if ($insertRequestQuery->execute()) {
            $success_message = "Yêu cầu của bạn đã được gửi thành công!";
        } else {
            $error_message = "Đã xảy ra lỗi khi gửi yêu cầu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu Cầu Làm Giảng Viên</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar a i {
            margin-right: 10px;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Dashboard</h3>
        <a href="dashboard.php"><i class="fas fa-home"></i> Trang Chủ</a>
        <a href="view_profile.php"><i class="fas fa-user"></i> Xem Thông Tin</a>
        <a href="update_profile.php"><i class="fas fa-user-edit"></i> Cập Nhật Thông Tin</a>
        <a href="request_to_be_lecturer.php"><i class="fas fa-user-plus"></i> Yêu Cầu Làm Giảng Viên</a>
        <a href="view_courses.php"><i class="fas fa-book"></i> Danh Sách Khóa</a>
        <a href="view_internship.php"><i class="fas fa-briefcase"></i> Thực Tập</a>
        <a href="../auth/logout.php" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');"><i class="fas fa-sign-out-alt"></i> Đăng Xuất</a>
    </div>

    <!-- Content -->
    <div class="content">
        <h1 class="text-center">Yêu Cầu Làm Giảng Viên</h1>

        <!-- Hiển thị thông báo -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Form gửi yêu cầu -->
        <form method="post" class="mt-4">
            <p>Bạn có chắc chắn muốn gửi yêu cầu làm giảng viên?</p>
            <button type="submit" class="btn btn-primary">Gửi Yêu Cầu</button>
            <a href="dashboard.php" class="btn btn-secondary">Quay Lại</a>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>