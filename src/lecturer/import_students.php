<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\import_students.php -->
<?php
session_start();
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

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

// Xử lý tải lên file Excel
if (isset($_POST['upload'])) {
    $file = $_FILES['excel']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    foreach ($sheetData as $index => $row) {
        // Bỏ qua dòng tiêu đề (dòng đầu tiên)
        if ($index == 1) continue;

        $student_code = $row['A'];
        $last_name = $row['B'];
        $first_name = $row['C'];
        $phone = $row['D'];
        $email = $row['E'];
        $major = $row['F'];
        $dob = $row['G'];
        $class_code = $row['H'];

        // Chuyển đổi định dạng ngày sinh từ DD/MM/YYYY sang YYYY-MM-DD
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $dob)) {
            $dobParts = explode('/', $dob);
            $dob = $dobParts[2] . '-' . str_pad($dobParts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dobParts[0], 2, '0', STR_PAD_LEFT);
        }

        // Kiểm tra định dạng ngày sinh sau khi chuyển đổi
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            echo "<p class='text-danger'>Ngày sinh không hợp lệ ở dòng $index: $dob</p>";
            continue; // Bỏ qua dòng này
        }

        // Kiểm tra xem mã sinh viên đã tồn tại chưa
        $checkQuery = $conn->prepare("SELECT * FROM students WHERE student_code = ?");
        $checkQuery->bind_param("s", $student_code);
        $checkQuery->execute();
        $checkResult = $checkQuery->get_result();

        if ($checkResult->num_rows > 0) {
            echo "<p class='text-warning'>Mã sinh viên đã tồn tại ở dòng $index: $student_code</p>";
            continue; // Bỏ qua dòng này
        }

        // Kiểm tra xem tài khoản người dùng đã tồn tại chưa
        $checkUserQuery = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $username = strtolower($student_code); // Sử dụng mã sinh viên làm tên đăng nhập
        $checkUserQuery->bind_param("s", $username);
        $checkUserQuery->execute();
        $userResult = $checkUserQuery->get_result();

        if ($userResult->num_rows > 0) {
            // Lấy user_id nếu tài khoản đã tồn tại
            $userRow = $userResult->fetch_assoc();
            $user_id = $userRow['user_id'];
        } else {
            // Tạo tài khoản người dùng mới
            $password = password_hash("123456", PASSWORD_DEFAULT); // Mật khẩu mặc định
            $role = "student";
            $insertUserQuery = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $insertUserQuery->bind_param("sss", $username, $password, $role);
            if (!$insertUserQuery->execute()) {
                echo "<p class='text-danger'>Lỗi khi tạo tài khoản người dùng ở dòng $index: " . $conn->error . "</p>";
                continue; // Bỏ qua dòng này nếu không thể tạo tài khoản
            }
            $user_id = $insertUserQuery->insert_id; // Lấy user_id vừa tạo
        }

        // Chèn dữ liệu vào bảng students
        $insertStudentQuery = $conn->prepare("INSERT INTO students (user_id, student_code, last_name, first_name, phone, email, major, dob, class_code) 
                                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insertStudentQuery->bind_param("issssssss", $user_id, $student_code, $last_name, $first_name, $phone, $email, $major, $dob, $class_code);

        if (!$insertStudentQuery->execute()) {
            echo "<p class='text-danger'>Lỗi MySQL ở dòng $index: " . $conn->error . "</p>";
        }
    }
    echo "<p class='text-success'>Danh sách sinh viên đã được nhập thành công!</p>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập Danh Sách Sinh Viên</title>
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
        <h1 class="text-center">Import Danh Sách Sinh Viên</h1>
        <form method="post" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
                <label for="excel" class="form-label">Chọn file Excel:</label>
                <input type="file" id="excel" name="excel" class="form-control" required>
            </div>
            <button type="submit" name="upload" class="btn btn-primary">Tải Lên</button>
        </form>
        <div class="mt-3">
            <p>Tải file mẫu Excel tại đây: <a href="../../public/templates/student_template.xlsx" class="btn btn-link">Tải File Mẫu</a></p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>