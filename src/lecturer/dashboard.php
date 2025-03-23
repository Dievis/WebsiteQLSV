<?php
session_start();
require '../../vendor/autoload.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header('Location: ../../auth/login.php');
    exit;
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'internship_management');
if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

// Lấy thông tin giảng viên từ bảng lecturers
$user_id = $_SESSION['user']['user_id'];
$query = $conn->prepare("SELECT first_name, last_name FROM lecturers WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$lecturer = $result->fetch_assoc();

// Kiểm tra và gán giá trị cho $full_name
$full_name = isset($lecturer['first_name'], $lecturer['last_name']) ? $lecturer['first_name'] . ' ' . $lecturer['last_name'] : '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Giảng Viên</title>
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
        <h3>Lecturer Dashboard</h3>
        <a href="dashboard.php"><i class="fas fa-home"></i> Trang Chủ</a>
        <a href="view_profile.php"><i class="fas fa-user-edit"></i> Thông Tin Cá Nhân</a>
        <a href="update_profile.php"><i class="fas fa-user-edit"></i> Cập Nhật Thông Tin</a>
        <a href="import_students.php"><i class="fas fa-upload"></i> Import Sinh Viên</a>
        <a href="view_students.php"><i class="fas fa-users"></i> Danh Sách Sinh Viên</a>
        <a href="view_courses.php"><i class="fas fa-book"></i> Danh Sách Khóa</a>
        <a href="manage_internships.php"><i class="fas fa-book"></i> Quản lý kỳ thực tập</a>
        <a href="../auth/logout.php" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');"><i class="fas fa-sign-out-alt"></i> Đăng Xuất</a>
    </div>


    <!-- Content -->
    <div class="content">
        <h1 class="text-center">Chào mừng <?php echo htmlspecialchars($full_name); ?>!</h1>
        <p class="text-center mt-4">Chọn một chức năng từ menu bên trái để bắt đầu.</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
