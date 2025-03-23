<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\lecturer\view_students.php -->
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

// Xử lý thêm sinh viên
if (isset($_POST['add_student'])) {
    $student_code = $conn->real_escape_string($_POST['student_code']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $major = $conn->real_escape_string($_POST['major']);
    $dob = !empty($_POST['dob']) ? $conn->real_escape_string($_POST['dob']) : NULL; // Kiểm tra giá trị rỗng
    $class_code = $conn->real_escape_string($_POST['class_code']);

    $insertQuery = $conn->prepare("INSERT INTO students (student_code, first_name, last_name, phone, email, major, dob, class_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insertQuery->bind_param("ssssssss", $student_code, $first_name, $last_name, $phone, $email, $major, $dob, $class_code);

    if ($insertQuery->execute()) {
        $success_message = "Sinh viên đã được thêm thành công!";
    } else {
        $error_message = "Đã xảy ra lỗi khi thêm sinh viên.";
    }
}

// Xử lý xóa sinh viên
if (isset($_GET['delete'])) {
    $student_id = (int)$_GET['delete'];

    $deleteQuery = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $deleteQuery->bind_param("i", $student_id);

    if ($deleteQuery->execute()) {
        $success_message = "Sinh viên đã được xóa thành công!";
    } else {
        $error_message = "Đã xảy ra lỗi khi xóa sinh viên.";
    }
}

// Xử lý cập nhật sinh viên
if (isset($_POST['update_student'])) {
    $student_id = (int)$_POST['student_id'];
    $student_code = $conn->real_escape_string($_POST['student_code']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $major = $conn->real_escape_string($_POST['major']);
    $dob = !empty($_POST['dob']) ? $conn->real_escape_string($_POST['dob']) : NULL; // Kiểm tra giá trị rỗng
    $class_code = $conn->real_escape_string($_POST['class_code']);

    $updateQuery = $conn->prepare("UPDATE students SET student_code = ?, first_name = ?, last_name = ?, phone = ?, email = ?, major = ?, dob = ?, class_code = ? WHERE student_id = ?");
    $updateQuery->bind_param("ssssssssi", $student_code, $first_name, $last_name, $phone, $email, $major, $dob, $class_code, $student_id);

    if ($updateQuery->execute()) {
        $success_message = "Thông tin sinh viên đã được cập nhật thành công!";
    } else {
        $error_message = "Đã xảy ra lỗi khi cập nhật thông tin sinh viên.";
    }
}

// Lấy danh sách sinh viên
$query = "SELECT * FROM students";
$result = $conn->query($query);

// Lấy thông tin sinh viên để chỉnh sửa
$edit_student = null;
if (isset($_GET['edit'])) {
    $student_id = (int)$_GET['edit'];
    $editQuery = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $editQuery->bind_param("i", $student_id);
    $editQuery->execute();
    $editResult = $editQuery->get_result();
    $edit_student = $editResult->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Sinh Viên</title>
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
            overflow-x: auto; /* Thêm thanh cuộn ngang nếu bảng quá rộng */
        }
        .table {
            width: 100%; /* Đảm bảo bảng chiếm toàn bộ chiều rộng */
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
        <h1 class="text-center">Danh Sách Sinh Viên</h1>

        <!-- Hiển thị thông báo -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Form chỉnh sửa sinh viên -->
        <?php if ($edit_student): ?>
            <form method="post" class="mb-4">
                <h3>Chỉnh Sửa Sinh Viên</h3>
                <input type="hidden" name="student_id" value="<?php echo $edit_student['student_id']; ?>">
                <div class="mb-3">
                    <label for="student_code" class="form-label">Mã Sinh Viên:</label>
                    <input type="text" id="student_code" name="student_code" class="form-control" value="<?php echo htmlspecialchars($edit_student['student_code']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="first_name" class="form-label">Họ:</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($edit_student['first_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Tên:</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($edit_student['last_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Số Điện Thoại:</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($edit_student['phone']); ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_student['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="major" class="form-label">Ngành Học:</label>
                    <input type="text" id="major" name="major" class="form-control" value="<?php echo htmlspecialchars($edit_student['major']); ?>">
                </div>
                <div class="mb-3">
                    <label for="dob" class="form-label">Ngày Sinh:</label>
                    <input type="date" id="dob" name="dob" class="form-control" value="<?php echo htmlspecialchars($edit_student['dob']); ?>">
                </div>
                <div class="mb-3">
                    <label for="class_code" class="form-label">Mã Lớp:</label>
                    <input type="text" id="class_code" name="class_code" class="form-control" value="<?php echo htmlspecialchars($edit_student['class_code']); ?>">
                </div>
                <button type="submit" name="update_student" class="btn btn-primary">Cập Nhật</button>
                <a href="view_students.php" class="btn btn-secondary">Hủy</a>
            </form>
        <?php endif; ?>

        <!-- Form thêm sinh viên -->
        <form method="post" class="mb-4">
            <h3>Thêm Sinh Viên</h3>
            <div class="mb-3">
                <label for="student_code" class="form-label">Mã Sinh Viên:</label>
                <input type="text" id="student_code" name="student_code" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="first_name" class="form-label">Họ:</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Tên:</label>
                <input type="text" id="last_name" name="last_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Số Điện Thoại:</label>
                <input type="text" id="phone" name="phone" class="form-control">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="major" class="form-label">Ngành Học:</label>
                <input type="text" id="major" name="major" class="form-control">
            </div>
            <div class="mb-3">
                <label for="dob" class="form-label">Ngày Sinh:</label>
                <input type="date" id="dob" name="dob" class="form-control">
            </div>
            <div class="mb-3">
                <label for="class_code" class="form-label">Mã Lớp:</label>
                <input type="text" id="class_code" name="class_code" class="form-control">
            </div>
            <button type="submit" name="add_student" class="btn btn-primary">Thêm Sinh Viên</button>
        </form>

        <!-- Danh sách sinh viên -->
        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive"> <!-- Thêm lớp này để bảng tự điều chỉnh -->
                <table class="table table-bordered mt-4">
                    <thead class="table-dark">
                        <tr>
                            <th>Mã Sinh Viên</th>
                            <th>Họ</th>
                            <th>Tên</th>
                            <th>Số Điện Thoại</th>
                            <th>Email</th>
                            <th>Ngành Học</th>
                            <th>Ngày Sinh</th>
                            <th>Mã Lớp</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_code'] ?? "Không xác định"); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name'] ?? "Không xác định"); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] ?? "Không xác định"); ?></td>
                                <td><?php echo htmlspecialchars($row['phone'] ?? "Không xác định"); ?></td>
                                <td><?php echo htmlspecialchars($row['email'] ?? "Không xác định"); ?></td>
                                <td><?php echo htmlspecialchars($row['major'] ?? "Không xác định"); ?></td>
                                <td><?php echo htmlspecialchars($row['dob'] ?? "Không xác định"); ?></td>
                                <td><?php echo htmlspecialchars($row['class_code'] ?? "Không xác định"); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $row['student_id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                                    <a href="?delete=<?php echo $row['student_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa sinh viên này?');">Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center mt-4">Không có sinh viên nào trong danh sách.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>