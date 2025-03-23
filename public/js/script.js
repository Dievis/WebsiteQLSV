window.onload = function () {
    document.getElementById("main-container").style.display = "block"; // Hiện trang sau khi tải CSS
};

document.addEventListener("DOMContentLoaded", function () {
    // Xử lý chuyển đổi giữa đăng nhập & đăng ký
    const loginCard = document.getElementById("login-card");
    const registerCard = document.getElementById("register-card");
    const showRegister = document.getElementById("show-register");
    const showLogin = document.getElementById("show-login");

    if (showRegister && showLogin) {
        showRegister.addEventListener("click", function (e) {
            e.preventDefault();
            loginCard.style.display = "none";
            registerCard.style.display = "block";
        });

        showLogin.addEventListener("click", function (e) {
            e.preventDefault();
            registerCard.style.display = "none";
            loginCard.style.display = "block";
        });
    }

    // Xử lý xác nhận trước khi xóa khóa học
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function (e) {
            if (!confirm("Bạn có chắc muốn xóa khóa này?")) {
                e.preventDefault();
            }
        });
    });
});
