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

// Lấy thông tin giảng viên hiện tại
$user_id = $_SESSION['user']['user_id'];
$lecturerQuery = $conn->prepare("SELECT * FROM lecturers WHERE user_id = ?");
$lecturerQuery->bind_param("i", $user_id);
$lecturerQuery->execute();
$lecturerResult = $lecturerQuery->get_result();

if ($lecturerResult->num_rows > 0) {
    $lecturer = $lecturerResult->fetch_assoc();
} else {
    die("Không tìm thấy thông tin giảng viên.");
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $department = $conn->real_escape_string($_POST['department']);

    $updateQuery = $conn->prepare("UPDATE lecturers SET first_name = ?, last_name = ?, email = ?, department = ? WHERE user_id = ?");
    $updateQuery->bind_param("ssssi", $first_name, $last_name, $email, $department, $user_id);

    if ($updateQuery->execute()) {
        $success_message = "Thông tin của bạn đã được cập nhật thành công!";
        // Cập nhật thông tin trong session nếu cần
        $_SESSION['user']['first_name'] = $first_name;
        $_SESSION['user']['last_name'] = $last_name;
    } else {
        $error_message = "Đã xảy ra lỗi khi cập nhật thông tin.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập Nhật Thông Tin</title>
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
        <h1 class="text-center">Cập Nhật Thông Tin</h1>

        <!-- Hiển thị thông báo -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Form cập nhật thông tin -->
        <form method="post" class="mt-4">
            <div class="mb-3">
                <label for="first_name" class="form-label">Họ:</label>
                <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($lecturer['first_name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Tên:</label>
                <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($lecturer['last_name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($lecturer['email'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="department" class="form-label">Khoa:</label>
                <input type="text" id="department" name="department" class="form-control" value="<?php echo htmlspecialchars($lecturer['department'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Cập Nhật</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>