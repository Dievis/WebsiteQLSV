<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\student\view_profile.php -->
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

// Lấy thông tin sinh viên từ bảng students
$user_id = $_SESSION['user']['user_id'];
$query = $conn->prepare("SELECT student_code, first_name, last_name, email, phone, major, dob, class_code FROM students WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Không tìm thấy thông tin sinh viên.");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Cá Nhân</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <h1 class="text-center">Thông Tin Cá Nhân</h1>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Mã Sinh Viên</h5>
                <p class="card-text"><?php echo htmlspecialchars($student['student_code']); ?></p>

                <h5 class="card-title">Họ và Tên</h5>
                <p class="card-text"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>

                <h5 class="card-title">Email</h5>
                <p class="card-text"><?php echo htmlspecialchars($student['email']); ?></p>

                <h5 class="card-title">Số Điện Thoại</h5>
                <p class="card-text"><?php echo htmlspecialchars($student['phone'] ?? 'Chưa cập nhật'); ?></p>

                <h5 class="card-title">Chuyên Ngành</h5>
                <p class="card-text"><?php echo htmlspecialchars($student['major'] ?? 'Chưa cập nhật'); ?></p>

                <h5 class="card-title">Ngày Sinh</h5>
                <p class="card-text"><?php echo htmlspecialchars($student['dob'] ?? 'Chưa cập nhật'); ?></p>

                <h5 class="card-title">Mã Lớp</h5>
                <p class="card-text"><?php echo htmlspecialchars($student['class_code'] ?? 'Chưa cập nhật'); ?></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>