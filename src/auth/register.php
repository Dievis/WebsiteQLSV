<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\auth\register.php -->
<?php
session_start();
require '../../vendor/autoload.php';

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'internship_management');
if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

// Xử lý đăng ký tài khoản
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($username, PASSWORD_DEFAULT); // Mật khẩu mặc định là tên tài khoản
    $role = 'student'; // Mặc định vai trò là sinh viên
    $must_change_password = 1; // Đặt cờ bắt buộc đổi mật khẩu

    // Kiểm tra xem tên đăng nhập đã tồn tại chưa
    $checkUser = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($checkUser->num_rows > 0) {
        $registerError = "Tên đăng nhập đã tồn tại!";
    } else {
        // Thêm tài khoản mới vào bảng users
        $insertUserQuery = $conn->prepare("INSERT INTO users (username, password, role, must_change_password) VALUES (?, ?, ?, ?)");
        $insertUserQuery->bind_param("sssi", $username, $password, $role, $must_change_password);
        if ($insertUserQuery->execute()) {
            $user_id = $insertUserQuery->insert_id; // Lấy user_id vừa tạo

            // Thêm sinh viên mới vào bảng students
            $insertStudentQuery = $conn->prepare("INSERT INTO students (user_id, student_code, first_name, last_name, email) VALUES (?, ?, ?, ?, ?)");
            $student_code = strtoupper($username); // Mã sinh viên mặc định là tên đăng nhập (viết hoa)
            $first_name = "Chưa cập nhật"; // Giá trị mặc định
            $last_name = "Chưa cập nhật"; // Giá trị mặc định
            $email = "example@example.com"; // Giá trị mặc định
            $insertStudentQuery->bind_param("issss", $user_id, $student_code, $first_name, $last_name, $email);
            $insertStudentQuery->execute();

            $registerSuccess = "Đăng ký tài khoản thành công! Bạn có thể đăng nhập.";
        } else {
            $registerError = "Đã xảy ra lỗi khi tạo tài khoản!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Đăng Ký</h1>
        <?php if (isset($registerError)): ?>
            <p style="color: red;"><?php echo $registerError; ?></p>
        <?php endif; ?>
        <?php if (isset($registerSuccess)): ?>
            <p style="color: green;"><?php echo $registerSuccess; ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" id="username" name="username" required>

            <button type="submit" name="register">Đăng Ký</button>
        </form>
        <br>
        <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </div>
</body>
</html>