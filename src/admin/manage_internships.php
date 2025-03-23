<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\admin\manage_internships.php -->
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

// Lấy danh sách sinh viên
$students = $conn->query("SELECT student_id, first_name, last_name FROM students");

// Lấy danh sách khóa học
$courses = $conn->query("SELECT course_id, course_name FROM internship_courses");

// Xử lý thêm kỳ thực tập
if (isset($_POST['add_internship'])) {
    $student_id = (int)$_POST['student_id'];
    $course_id = (int)$_POST['course_id'];
    $company_name = $conn->real_escape_string($_POST['company_name']);
    $company_address = $conn->real_escape_string($_POST['company_address']);
    $industry = $conn->real_escape_string($_POST['industry']);
    $supervisor_name = $conn->real_escape_string($_POST['supervisor_name']);
    $supervisor_phone = $conn->real_escape_string($_POST['supervisor_phone']);
    $supervisor_email = $conn->real_escape_string($_POST['supervisor_email']);
    $start_date = $conn->real_escape_string($_POST['start_date']);
    $end_date = $conn->real_escape_string($_POST['end_date']);
    $job_position = $conn->real_escape_string($_POST['job_position']);
    $job_description = $conn->real_escape_string($_POST['job_description']);

    $insertQuery = $conn->prepare("INSERT INTO internship_details (student_id, course_id, company_name, company_address, industry, supervisor_name, supervisor_phone, supervisor_email, start_date, end_date, job_position, job_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertQuery->bind_param("iissssssssss", $student_id, $course_id, $company_name, $company_address, $industry, $supervisor_name, $supervisor_phone, $supervisor_email, $start_date, $end_date, $job_position, $job_description);

    if ($insertQuery->execute()) {
        $success_message = "Kỳ thực tập đã được thêm thành công!";
    } else {
        $error_message = "Đã xảy ra lỗi khi thêm kỳ thực tập.";
    }
}

// Xử lý xóa kỳ thực tập
if (isset($_GET['delete'])) {
    $internship_id = (int)$_GET['delete'];

    $deleteQuery = $conn->prepare("DELETE FROM internship_details WHERE id = ?");
    $deleteQuery->bind_param("i", $internship_id);

    if ($deleteQuery->execute()) {
        $success_message = "Kỳ thực tập đã được xóa thành công!";
    } else {
        $error_message = "Đã xảy ra lỗi khi xóa kỳ thực tập.";
    }
}

// Lấy danh sách kỳ thực tập
$internships = $conn->query("
    SELECT 
        i.id, 
        CONCAT(s.first_name, ' ', s.last_name) AS student_name, 
        c.course_name, 
        i.company_name, 
        i.start_date, 
        i.end_date, 
        i.status 
    FROM internship_details i
    JOIN students s ON i.student_id = s.student_id
    JOIN internship_courses c ON i.course_id = c.course_id
");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Kỳ Thực Tập</title>
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
        <a href="manage_internships.php"><i class="fas fa-briefcase"></i> Quản Lý Kỳ Thực Tập</a>
        <a href="manage_teacher_requests.php"><i class="fas fa-user-check"></i> Quản Lý Yêu Cầu Làm Giáo Viên</a>
        <a href="../auth/logout.php" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');"><i class="fas fa-sign-out-alt"></i> Đăng Xuất</a>
    </div>

    <!-- Content -->
    <div class="content">
        <h1 class="text-center">Quản Lý Kỳ Thực Tập</h1>

        <!-- Hiển thị thông báo -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Form thêm kỳ thực tập -->
        <h3>Thêm Kỳ Thực Tập</h3>
        <form method="post" class="mb-4">
            <div class="mb-3">
                <label for="student_id" class="form-label">Sinh Viên:</label>
                <select id="student_id" name="student_id" class="form-select" required>
                    <option value="">Chọn Sinh Viên</option>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <option value="<?php echo $student['student_id']; ?>">
                            <?php echo $student['first_name'] . ' ' . $student['last_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="course_id" class="form-label">Khóa Học:</label>
                <select id="course_id" name="course_id" class="form-select" required>
                    <option value="">Chọn Khóa Học</option>
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $course['course_id']; ?>">
                            <?php echo $course['course_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="company_name" class="form-label">Tên Công Ty:</label>
                <input type="text" id="company_name" name="company_name" class="form-control" placeholder="Nhập Tên Công Ty" required>
            </div>
            <div class="mb-3">
                <label for="company_address" class="form-label">Địa Chỉ Công Ty:</label>
                <textarea id="company_address" name="company_address" class="form-control" rows="3" placeholder="Nhập Địa Chỉ Công Ty" required></textarea>
            </div>
            <div class="mb-3">
                <label for="industry" class="form-label">Ngành Công Nghiệp:</label>
                <input type="text" id="industry" name="industry" class="form-control" placeholder="Nhập Ngành Công Nghiệp" required>
            </div>
            <div class="mb-3">
                <label for="supervisor_name" class="form-label">Tên Người Giám Sát:</label>
                <input type="text" id="supervisor_name" name="supervisor_name" class="form-control" placeholder="Nhập Tên Người Giám Sát" required>
            </div>
            <div class="mb-3">
                <label for="supervisor_phone" class="form-label">Số Điện Thoại Người Giám Sát:</label>
                <input type="text" id="supervisor_phone" name="supervisor_phone" class="form-control" placeholder="Nhập Số Điện Thoại" required>
            </div>
            <div class="mb-3">
                <label for="supervisor_email" class="form-label">Email Người Giám Sát:</label>
                <input type="email" id="supervisor_email" name="supervisor_email" class="form-control" placeholder="Nhập Email" required>
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Ngày Bắt Đầu:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">Ngày Kết Thúc:</label>
                <input type="date" id="end_date" name="end_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="job_position" class="form-label">Vị Trí Công Việc:</label>
                <input type="text" id="job_position" name="job_position" class="form-control" placeholder="Nhập Vị Trí Công Việc" required>
            </div>
            <div class="mb-3">
                <label for="job_description" class="form-label">Mô Tả Công Việc:</label>
                <textarea id="job_description" name="job_description" class="form-control" rows="4" placeholder="Nhập Mô Tả Công Việc" required></textarea>
            </div>
            <button type="submit" name="add_internship" class="btn btn-primary">Thêm Kỳ Thực Tập</button>
        </form>

        <!-- Danh sách kỳ thực tập -->
        <h3>Danh Sách Kỳ Thực Tập</h3>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Sinh Viên</th>
                    <th>Khóa Học</th>
                    <th>Công Ty</th>
                    <th>Ngày Bắt Đầu</th>
                    <th>Ngày Kết Thúc</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($internship = $internships->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $internship['id']; ?></td>
                        <td><?php echo htmlspecialchars($internship['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($internship['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($internship['company_name']); ?></td>
                        <td><?php echo $internship['start_date']; ?></td>
                        <td><?php echo $internship['end_date']; ?></td>
                        <td><?php echo $internship['status']; ?></td>
                        <td>
                            <a href="?edit=<?php echo $internship['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                            <a href="?delete=<?php echo $internship['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa kỳ thực tập này?');">Xóa</a>
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