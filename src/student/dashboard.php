<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\student\dashboard.php -->
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
$query = $conn->prepare("SELECT first_name, last_name FROM students WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$student = $result->fetch_assoc();

// Kiểm tra và gán giá trị cho $full_name
$full_name = isset($student['first_name'], $student['last_name']) ? $student['first_name'] . ' ' . $student['last_name'] : '';

// Xử lý yêu cầu đăng ký làm giáo viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_teacher'])) {
    // Kiểm tra xem sinh viên đã là giáo viên hay đã gửi yêu cầu trước đó chưa
    $checkQuery = $conn->prepare("SELECT * FROM lecturer_requests WHERE user_id = ?");
    $checkQuery->bind_param("i", $user_id);
    $checkQuery->execute();
    $checkResult = $checkQuery->get_result();

    if ($checkResult->num_rows > 0) {
        $message = "Bạn đã gửi yêu cầu đăng ký làm giáo viên trước đó. Vui lòng chờ phê duyệt.";
    } else {
        // Thêm yêu cầu vào bảng lecturer_requests
        $insertQuery = $conn->prepare("INSERT INTO lecturer_requests (user_id, status) VALUES (?, 'pending')");
        $insertQuery->bind_param("i", $user_id);

        if ($insertQuery->execute()) {
            $message = "Yêu cầu đăng ký làm giáo viên đã được gửi. Vui lòng chờ phê duyệt.";
        } else {
            $message = "Đã xảy ra lỗi khi gửi yêu cầu. Vui lòng thử lại.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sinh Viên</title>
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

    <div class="content">
        <h1 class="text-center">Chào mừng <?php echo htmlspecialchars($full_name); ?>!</h1>
        <p class="text-center mt-4">Chọn một chức năng từ menu bên trái để bắt đầu.</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>