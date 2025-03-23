<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\student\update_profile.php -->
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

// Lấy thông tin sinh viên hiện tại
$user_id = $_SESSION['user']['user_id'];
$query = $conn->prepare("SELECT s.*, u.username FROM students s JOIN users u ON s.user_id = u.user_id WHERE s.user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$student = $result->fetch_assoc();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = !empty($_POST['password']) ? password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT) : null;
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $dob = !empty($_POST['dob']) ? $conn->real_escape_string($_POST['dob']) : null;
    $major = $conn->real_escape_string($_POST['major']);
    $class_code = $conn->real_escape_string($_POST['class_code']);

    // Cập nhật thông tin tài khoản
    $updateUserQuery = $conn->prepare("UPDATE users SET username = ?" . ($password ? ", password = ?" : "") . " WHERE user_id = ?");
    if ($password) {
        $updateUserQuery->bind_param("ssi", $username, $password, $user_id);
    } else {
        $updateUserQuery->bind_param("si", $username, $user_id);
    }
    if (!$updateUserQuery->execute()) {
        $errorMessage = "Đã xảy ra lỗi khi cập nhật tài khoản!";
    }

    // Cập nhật thông tin sinh viên
    $updateStudentQuery = $conn->prepare("UPDATE students SET first_name = ?, last_name = ?, phone = ?, email = ?, dob = ?, major = ?, class_code = ? WHERE user_id = ?");
    if ($dob) {
        $updateStudentQuery->bind_param("sssssssi", $first_name, $last_name, $phone, $email, $dob, $major, $class_code, $user_id);
    } else {
        $updateStudentQuery = $conn->prepare("UPDATE students SET first_name = ?, last_name = ?, phone = ?, email = ?, dob = NULL, major = ?, class_code = ? WHERE user_id = ?");
        $updateStudentQuery->bind_param("ssssssi", $first_name, $last_name, $phone, $email, $major, $class_code, $user_id);
    }

    if ($updateStudentQuery->execute()) {
        $successMessage = "Cập nhật thông tin thành công!";
        $_SESSION['user']['username'] = $username; // Cập nhật session với tên đăng nhập mới
    } else {
        $errorMessage = "Đã xảy ra lỗi khi cập nhật thông tin!";
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
        <h1 class="text-center">Cập Nhật Thông Tin Cá Nhân</h1>
        <?php if (isset($successMessage)): ?>
            <p class="text-success text-center"><?php echo $successMessage; ?></p>
        <?php endif; ?>
        <?php if (isset($errorMessage)): ?>
            <p class="text-danger text-center"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
        <form method="post" class="mt-4">
            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập (Yêu cầu dùng MSSV do trường cấp để làm tài khoản):</label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo $student['username']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu mới (Để trống nếu không đổi):</label>
                <input type="password" id="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label for="first_name" class="form-label">Họ:</label>
                <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo $student['first_name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Tên:</label>
                <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo $student['last_name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại:</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo $student['phone']; ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo $student['email']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="dob" class="form-label">Ngày sinh:</label>
                <input type="date" id="dob" name="dob" class="form-control" value="<?php echo $student['dob']; ?>">
            </div>
            <div class="mb-3">
                <label for="major" class="form-label">Ngành học:</label>
                <input type="text" id="major" name="major" class="form-control" value="<?php echo $student['major']; ?>">
            </div>
            <div class="mb-3">
                <label for="class_code" class="form-label">Mã lớp:</label>
                <input type="text" id="class_code" name="class_code" class="form-control" value="<?php echo $student['class_code']; ?>">
            </div>
            <button type="submit" class="btn btn-primary">Cập Nhật</button>
        </form>
    </div>
</body>
</html>