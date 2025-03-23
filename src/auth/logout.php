<?php
session_start();
session_unset();
session_destroy();
header('Location: login.php?logged_out=true'); // Chuyển hướng về trang đăng nhập
exit;
?>