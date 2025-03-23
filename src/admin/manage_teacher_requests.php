<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\admin\manage_teacher_requests.php -->
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

// Xử lý chấp nhận yêu cầu
if (isset($_GET['accept'])) {
    $user_id = (int)$_GET['accept'];

    // Lấy thông tin từ bảng students
    $studentQuery = $conn->prepare("SELECT first_name, last_name, email FROM students WHERE user_id = ?");
    $studentQuery->bind_param("i", $user_id);
    $studentQuery->execute();
    $studentResult = $studentQuery->get_result();

    if ($studentResult->num_rows > 0) {
        $student = $studentResult->fetch_assoc();

        // Chuyển thông tin sang bảng lecturers
        $insertLecturerQuery = $conn->prepare("INSERT INTO lecturers (user_id, first_name, last_name, email) 
                                               VALUES (?, ?, ?, ?)");
        $insertLecturerQuery->bind_param(
            "isss",
            $user_id,
            $student['first_name'],
            $student['last_name'],
            $student['email']
        );

        if ($insertLecturerQuery->execute()) {
            // Cập nhật vai trò người dùng thành 'lecturer'
            $updateRoleQuery = $conn->prepare("UPDATE users SET role = 'lecturer' WHERE user_id = ?");
            $updateRoleQuery->bind_param("i", $user_id);
            $updateRoleQuery->execute();

            // Cập nhật trạng thái yêu cầu thành 'approved'
            $updateRequestQuery = $conn->prepare("UPDATE lecturer_requests SET status = 'approved' WHERE user_id = ?");
            $updateRequestQuery->bind_param("i", $user_id);
            $updateRequestQuery->execute();

            $success_message = "Yêu cầu đã được chấp nhận và thông tin đã được chuyển sang bảng giảng viên!";
        } else {
            $error_message = "Đã xảy ra lỗi khi chuyển thông tin sang bảng giảng viên.";
        }
    } else {
        $error_message = "Không tìm thấy thông tin sinh viên để chuyển.";
    }
}

// Xử lý từ chối yêu cầu
if (isset($_GET['reject'])) {
    $user_id = (int)$_GET['reject'];
    $updateRequestQuery = $conn->prepare("UPDATE lecturer_requests SET status = 'rejected' WHERE user_id = ?");
    $updateRequestQuery->bind_param("i", $user_id);
    if ($updateRequestQuery->execute()) {
        $success_message = "Yêu cầu đã bị từ chối!";
    } else {
        $error_message = "Đã xảy ra lỗi khi từ chối yêu cầu.";
    }
}

// Lấy danh sách yêu cầu làm giáo viên
$query = "SELECT lr.user_id, u.username, lr.status 
          FROM lecturer_requests lr 
          JOIN users u ON lr.user_id = u.user_id 
          WHERE lr.status = 'pending'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Yêu Cầu Làm Giáo Viên</title>
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
        <h1 class="text-center">Quản Lý Yêu Cầu Làm Giáo Viên</h1>

        <!-- Hiển thị thông báo -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Danh sách yêu cầu -->
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered mt-4">
                <thead class="table-dark">
                    <tr>
                        <th>Tên Đăng Nhập</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>
                                <a href="?accept=<?php echo $row['user_id']; ?>" class="btn btn-success btn-sm">Chấp Nhận</a>
                                <a href="?reject=<?php echo $row['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn từ chối yêu cầu này?');">Từ Chối</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center mt-4">Không có yêu cầu nào.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>