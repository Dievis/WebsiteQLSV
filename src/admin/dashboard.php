<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\admin\dashboard.php -->
<?php
session_start();
require '../../vendor/autoload.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
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
        <h3>Admin Dashboard</h3>
        <a href="dashboard.php"><i class="fas fa-home"></i> Trang Chủ</a>
        <a href="manage_users.php"><i class="fas fa-users"></i> Quản Lý Người Dùng</a>
        <a href="manage_courses.php"><i class="fas fa-book"></i> Quản Lý Khóa</a>
        <a href="manage_internships.php"><i class="fas fa-book"></i> Quản Lý Kỳ Thực Tập</a>
        <a href="manage_teacher_requests.php"><i class="fas fa-user-check"></i> Quản Lý Yêu Cầu Làm Giáo Viên</a>
        <a href="../auth/logout.php" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');"><i class="fas fa-sign-out-alt"></i> Đăng Xuất</a>
    </div>

    <!-- Content -->
    <div class="content">
        <h1 class="text-center">Chào mừng, Admin!</h1>
        <p class="text-center mt-4">Chọn một chức năng từ menu bên trái để bắt đầu quản lý hệ thống.</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>