<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\student\view_internship.php -->
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

// Lấy danh sách kỳ thực tập của sinh viên hiện tại
$user_id = $_SESSION['user']['user_id'];
$query = $conn->prepare("
    SELECT 
        i.id, 
        c.course_name, 
        i.company_name, 
        i.start_date, 
        i.end_date, 
        i.job_position, 
        i.status 
    FROM internship_details i
    JOIN students s ON i.student_id = s.student_id
    JOIN internship_courses c ON i.course_id = c.course_id
    WHERE s.user_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Kỳ Thực Tập</title>
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
        <h1 class="text-center">Danh Sách Kỳ Thực Tập</h1>
        <table class="table table-bordered mt-4">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Khóa Học</th>
                    <th>Công Ty</th>
                    <th>Ngày Bắt Đầu</th>
                    <th>Ngày Kết Thúc</th>
                    <th>Vị Trí</th>
                    <th>Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($internship = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $internship['id']; ?></td>
                        <td><?php echo htmlspecialchars($internship['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($internship['company_name']); ?></td>
                        <td><?php echo $internship['start_date']; ?></td>
                        <td><?php echo $internship['end_date']; ?></td>
                        <td><?php echo htmlspecialchars($internship['job_position']); ?></td>
                        <td>
                            <?php 
                                if ($internship['status'] === 'pending') {
                                    echo '<span class="badge bg-warning">Đang Chờ</span>';
                                } elseif ($internship['status'] === 'approved') {
                                    echo '<span class="badge bg-success">Đã Duyệt</span>';
                                } else {
                                    echo '<span class="badge bg-danger">Từ Chối</span>';
                                }
                            ?>
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