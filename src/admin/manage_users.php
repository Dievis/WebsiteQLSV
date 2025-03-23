<!-- filepath: d:\laragon\www\PTPMMNM\baitap2php\src\admin\manage_users.php -->
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

// Xử lý thêm người dùng
if (isset($_POST['add_user'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT);
    $role = $conn->real_escape_string($_POST['role']);

    $insertQuery = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $insertQuery->bind_param("sss", $username, $password, $role);

    if ($insertQuery->execute()) {
        $success_message = "Người dùng đã được thêm thành công!";
    } else {
        $error_message = "Đã xảy ra lỗi khi thêm người dùng.";
    }
}

// Xử lý cập nhật người dùng
if (isset($_POST['update_user'])) {
    $user_id = (int)$_POST['user_id'];
    $username = $conn->real_escape_string($_POST['username']);
    $role = $conn->real_escape_string($_POST['role']);

    $updateQuery = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE user_id = ?");
    $updateQuery->bind_param("ssi", $username, $role, $user_id);

    if ($updateQuery->execute()) {
        $success_message = "Người dùng đã được cập nhật thành công!";
    } else {
        $error_message = "Đã xảy ra lỗi khi cập nhật người dùng.";
    }
}

// Xử lý xóa người dùng
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];

    $deleteQuery = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $deleteQuery->bind_param("i", $user_id);

    if ($deleteQuery->execute()) {
        $success_message = "Người dùng đã được xóa thành công!";
    } else {
        $error_message = "Đã xảy ra lỗi khi xóa người dùng.";
    }
}

// Lấy danh sách người dùng
$result = $conn->query("SELECT * FROM users");

// Hiển thị thông tin người dùng cần chỉnh sửa
$edit_user = null;
if (isset($_GET['edit'])) {
    $user_id = (int)$_GET['edit'];
    $editQuery = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $editQuery->bind_param("i", $user_id);
    $editQuery->execute();
    $editResult = $editQuery->get_result();
    if ($editResult->num_rows > 0) {
        $edit_user = $editResult->fetch_assoc();
    } else {
        $error_message = "Không tìm thấy người dùng.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
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
        <h1 class="text-center">Quản Lý Người Dùng</h1>

        <!-- Hiển thị thông báo -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Form thêm người dùng -->
        <form method="post" class="mb-4">
            <h3>Thêm Người Dùng</h3>
            <div class="mb-3">
                <label for="username" class="form-label">Tên Đăng Nhập:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật Khẩu:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Vai Trò:</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="admin">Admin</option>
                    <option value="lecturer">Giảng Viên</option>
                    <option value="student">Sinh Viên</option>
                </select>
            </div>
            <button type="submit" name="add_user" class="btn btn-primary">Thêm</button>
        </form>

        <!-- Form chỉnh sửa người dùng -->
        <?php if ($edit_user): ?>
            <form method="post" class="mb-4">
                <h3>Chỉnh Sửa Người Dùng</h3>
                <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
                <div class="mb-3">
                    <label for="edit_username" class="form-label">Tên Đăng Nhập:</label>
                    <input type="text" id="edit_username" name="username" class="form-control" value="<?php echo htmlspecialchars($edit_user['username']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_role" class="form-label">Vai Trò:</label>
                    <select id="edit_role" name="role" class="form-select" required>
                        <option value="admin" <?php echo $edit_user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="lecturer" <?php echo $edit_user['role'] === 'lecturer' ? 'selected' : ''; ?>>Giảng Viên</option>
                        <option value="student" <?php echo $edit_user['role'] === 'student' ? 'selected' : ''; ?>>Sinh Viên</option>
                    </select>
                </div>
                <button type="submit" name="update_user" class="btn btn-success">Cập Nhật</button>
                <a href="manage_users.php" class="btn btn-secondary">Hủy</a>
            </form>
        <?php endif; ?>

        <!-- Danh sách người dùng -->
        <h3>Danh Sách Người Dùng</h3>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Tên Đăng Nhập</th>
                    <th>Vai Trò</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td>
                            <a href="?edit=<?php echo $row['user_id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                            <a href="?delete=<?php echo $row['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">Xóa</a>
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