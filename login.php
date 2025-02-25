<?php
session_start();
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "connect.php";
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: home_admin.php");
            } else if ($user['role'] === 'user') {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Sai email hoặc mật khẩu!";
        }
        $stmt->close();
    }
    $connect->close();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="cs/demo.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid vh-100 d-flex">
        <div class="col-md-5 logo-section">
            <img src="images/banner.png" alt="">
        </div>
        <div class="col-md-7 d-flex align-items-center justify-content-center position-relative" style="background-color: #B0E2FF;">
            <div class="register-container">
                <h2 class="text-center fw-bold">Đăng nhập</h2>
                <?php if (!empty($error)) { ?>
                    <p class="text-danger text-center"><?php echo $error; ?></p>
                <?php } ?>
                <form method="POST">
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="mb-3 position-relative">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Mật khẩu" required>

                        <i class="bi bi-eye-fill position-absolute" id="passwordToggle" onclick="togglePassword()" style="right: 10px; top: 10px; cursor: pointer;"></i>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Đăng nhập</button>
                </form>
                <div class="text-center mt-3">
                    <p>Hoặc</p>
                    <button type="button" class="btn btn-danger">Google</button>
                    <button type="button" class="btn btn-primary">Facebook</button>
                </div>
                <p class="text-center mt-3">Bạn chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById("password");
            var passwordToggle = document.getElementById("passwordToggle");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordToggle.classList.remove("bi-eye-fill");
                passwordToggle.classList.add("bi-eye-slash-fill");
            } else {
                passwordInput.type = "password";
                passwordToggle.classList.remove("bi-eye-slash-fill");
                passwordToggle.classList.add("bi-eye-fill");
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
