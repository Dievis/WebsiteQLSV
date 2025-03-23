<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\auth\login.php -->
<?php
session_start();
require '../../vendor/autoload.php';

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'internship_management');
if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

// Tạo tài khoản admin mặc định nếu chưa tồn tại
$adminUsername = 'admin';
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT); // Mật khẩu mặc định là "admin123"
$adminRole = 'admin'; // Vai trò admin

$checkAdminQuery = $conn->prepare("SELECT * FROM users WHERE username = ?");
$checkAdminQuery->bind_param("s", $adminUsername);
$checkAdminQuery->execute();
$adminResult = $checkAdminQuery->get_result();

if ($adminResult->num_rows === 0) {
    // Nếu tài khoản admin chưa tồn tại, thêm vào cơ sở dữ liệu
    $insertAdminQuery = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $insertAdminQuery->bind_param("sss", $adminUsername, $adminPassword, $adminRole);

    if ($insertAdminQuery->execute()) {
        echo "Tài khoản admin đã được tạo thành công!<br>";
    } else {
        echo "Lỗi khi tạo tài khoản admin: " . $insertAdminQuery->error . "<br>";
    }
}

// Kiểm tra nếu có thông báo đăng xuất
$logged_out_message = '';
if (isset($_GET['logged_out']) && $_GET['logged_out'] === 'true') {
    $logged_out_message = "Bạn đã đăng xuất thành công!";
}

// Xử lý đăng nhập
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sử dụng Prepared Statements để tránh SQL Injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Kiểm tra mật khẩu bằng password_verify
        if (password_verify($password, $user['password'])) {
            // Kiểm tra cờ must_change_password
            if ($user['must_change_password'] == 1) {
                $_SESSION['user_id'] = $user['user_id']; // Lưu user_id vào session
                header('Location: change_password.php'); // Chuyển hướng đến trang đổi mật khẩu
                exit;
            }

            // Lưu thông tin người dùng vào session
            $_SESSION['user'] = $user;

            // Điều hướng theo vai trò
            if ($user['role'] == 'admin') {
                header('Location: ../admin/dashboard.php'); // Điều hướng đến trang admin
            } elseif ($user['role'] == 'lecturer') {
                header('Location: ../lecturer/dashboard.php');
            } elseif ($user['role'] == 'student') {
                header('Location: ../student/dashboard.php');
            }
            exit;
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
        }
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <?php if ($logged_out_message): ?>
            <p style="color: green;"><?php echo $logged_out_message; ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['password_changed']) && $_GET['password_changed'] == 1): ?>
            <p style="color: green;">Mật khẩu đã được đổi thành công! Vui lòng đăng nhập lại.</p>
        <?php endif; ?>

        <h1>Đăng Nhập</h1>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="post">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" name="login">Đăng Nhập</button>
        </form>
        <br>
        <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
    </div>
</body>
</html>