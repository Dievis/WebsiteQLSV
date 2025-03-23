<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\admin\manage_courses.php -->
<?php
session_start();
require '../../vendor/autoload.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'internship_management');
if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

// Xử lý thêm khóa học
if (isset($_POST['add_course'])) {
    $course_code = $conn->real_escape_string($_POST['course_code']);
    $course_name = $conn->real_escape_string($_POST['course_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $lecturer_id = (int)$_POST['lecturer_id'];

    $insertQuery = $conn->prepare("INSERT INTO internship_courses (course_code, course_name, description, lecturer_id) VALUES (?, ?, ?, ?)");
    $insertQuery->bind_param("sssi", $course_code, $course_name, $description, $lecturer_id);

    if ($insertQuery->execute()) {
        $success_message = "Khóa học đã được thêm thành công!";
    } else {
        $error_message = "Đã xảy ra lỗi khi thêm khóa học.";
    }
}

// Xử lý cập nhật khóa học
if (isset($_POST['update_course'])) {
    $course_id = (int)$_POST['course_id'];
    $course_code = $conn->real_escape_string($_POST['course_code']);
    $course_name = $conn->real_escape_string($_POST['course_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $lecturer_id = (int)$_POST['lecturer_id'];

    $updateQuery = $conn->prepare("UPDATE internship_courses SET course_code = ?, course_name = ?, description = ?, lecturer_id = ? WHERE course_id = ?");
    $updateQuery->bind_param("sssii", $course_code, $course_name, $description, $lecturer_id, $course_id);

    if ($updateQuery->execute()) {
        $success_message = "Khóa học đã được cập nhật thành công!";
    } else {
        $error_message = "Đã xảy ra lỗi khi cập nhật khóa học.";
    }
}

// Xử lý xóa khóa học
if (isset($_GET['delete'])) {
    $course_id = (int)$_GET['delete'];

    $deleteQuery = $conn->prepare("DELETE FROM internship_courses WHERE course_id = ?");
    $deleteQuery->bind_param("i", $course_id);

    if ($deleteQuery->execute()) {
        $success_message = "Khóa học đã được xóa thành công!";
    } else {
        $error_message = "Đã xảy ra lỗi khi xóa khóa học.";
    }
}

// Hiển thị thông tin khóa học cần chỉnh sửa
$edit_course = null;
if (isset($_GET['edit'])) {
    $course_id = (int)$_GET['edit'];
    $editQuery = $conn->prepare("SELECT * FROM internship_courses WHERE course_id = ?");
    $editQuery->bind_param("i", $course_id);
    $editQuery->execute();
    $editResult = $editQuery->get_result();
    if ($editResult->num_rows > 0) {
        $edit_course = $editResult->fetch_assoc();
    } else {
        $error_message = "Không tìm thấy khóa học.";
    }
}

// Lấy danh sách khóa học
$courses = $conn->query("SELECT ic.*, l.first_name, l.last_name FROM internship_courses ic JOIN lecturers l ON ic.lecturer_id = l.lecturer_id");
$lecturers = $conn->query("SELECT * FROM lecturers");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khóa Học</title>
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
        <h1 class="text-center">Quản Lý Khóa Học</h1>

        <!-- Hiển thị thông báo -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Form thêm khóa học -->
        <form method="post" class="mb-4">
            <h3>Thêm Khóa Học</h3>
            <div class="mb-3">
                <label for="course_code" class="form-label">Mã Khóa:</label>
                <input type="text" id="course_code" name="course_code" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="course_name" class="form-label">Tên Khóa:</label>
                <input type="text" id="course_name" name="course_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Mô Tả:</label>
                <textarea id="description" name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="mb-3">
                <label for="lecturer_id" class="form-label">Giảng Viên:</label>
                <select id="lecturer_id" name="lecturer_id" class="form-select" required>
                    <?php while ($lecturer = $lecturers->fetch_assoc()): ?>
                        <option value="<?php echo $lecturer['lecturer_id']; ?>">
                            <?php echo $lecturer['first_name'] . ' ' . $lecturer['last_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="add_course" class="btn btn-primary">Thêm</button>
        </form>

        <!-- Form chỉnh sửa khóa học -->
        <?php if ($edit_course): ?>
            <form method="post" class="mb-4">
                <h3>Chỉnh Sửa Khóa Học</h3>
                <input type="hidden" name="course_id" value="<?php echo $edit_course['course_id']; ?>">
                <div class="mb-3">
                    <label for="edit_course_code" class="form-label">Mã Khóa:</label>
                    <input type="text" id="edit_course_code" name="course_code" class="form-control" value="<?php echo htmlspecialchars($edit_course['course_code']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_course_name" class="form-label">Tên Khóa:</label>
                    <input type="text" id="edit_course_name" name="course_name" class="form-control" value="<?php echo htmlspecialchars($edit_course['course_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_description" class="form-label">Mô Tả:</label>
                    <textarea id="edit_description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($edit_course['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="edit_lecturer_id" class="form-label">Giảng Viên:</label>
                    <select id="edit_lecturer_id" name="lecturer_id" class="form-select" required>
                        <?php
                        $lecturers->data_seek(0); // Reset kết quả truy vấn giảng viên
                        while ($lecturer = $lecturers->fetch_assoc()): ?>
                            <option value="<?php echo $lecturer['lecturer_id']; ?>" <?php echo $lecturer['lecturer_id'] == $edit_course['lecturer_id'] ? 'selected' : ''; ?>>
                                <?php echo $lecturer['first_name'] . ' ' . $lecturer['last_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="update_course" class="btn btn-success">Cập Nhật</button>
                <a href="manage_courses.php" class="btn btn-secondary">Hủy</a>
            </form>
        <?php endif; ?>

        <!-- Danh sách khóa học -->
        <h3>Danh Sách Khóa Học</h3>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Mã Khóa</th>
                    <th>Tên Khóa</th>
                    <th>Mô Tả</th>
                    <th>Giảng Viên</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $course['course_id']; ?></td>
                        <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($course['description']); ?></td>
                        <td><?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></td>
                        <td>
                            <a href="?edit=<?php echo $course['course_id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                            <a href="?delete=<?php echo $course['course_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa khóa học này?');">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>