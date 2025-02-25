<?php
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "connect.php";

    $fullname = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $address = trim($_POST['address']);
    $phone_number = trim($_POST['phone_number']);
    $checkbox = isset($_POST['checkbox']);
    $username = trim($_POST['username']);  

    if (!$checkbox) {
        $error = "Bạn phải đồng ý với điều khoản sử dụng!";
    } else if (empty($fullname) || empty($email) || empty($password) || empty($address) || empty($phone_number) || empty($username)) {
        $error = "Vui lòng điền đầy đủ thông tin!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } elseif (strlen($password) < 4) {
        $error = "Mật khẩu phải có ít nhất 4 ký tự!";
    } else {
        // Kiểm tra xem username tồn tại 
        $stmt = $connect->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Tên người dùng đã tồn tại!";
        } else {
            // truy vấn dữ liệu vào csdl
            $stmt = $connect->prepare("INSERT INTO Users (username, full_name, email, password, address, phone_number) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $fullname, $email, $password, $address, $phone_number);

            if ($stmt->execute()) {
                
                header("Location: login.php");
                exit();
            } else {
                $error = "Lỗi khi đăng ký: " . $connect->error;
            }
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
    <title>Đăng ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="cs/demo.css">
</head>

<body>
    <div class="container-fluid vh-100 d-flex">
        <div class="col-md-5 logo-section">
            <img src="images/banner.png" alt="">
        </div>
        <div class="col-md-7 d-flex align-items-center justify-content-center position-relative" style="background-color: #B0E2FF;">
            <div class="register-container">
                <h2 class="text-center fw-bold">Đăng ký</h2>
                <?php if ($error)
                    echo "<p class='text-danger text-center'>$error</p>"; ?>
                <form method="POST">
                    <div class="mb-2">
                        <input type="text" name="username" class="form-control" placeholder="Tên người dùng" value="<?php echo isset($username) ? $username : ''; ?>">
                    </div>
                    <div class="mb-2">
                        <input type="text" name="full_name" class="form-control" placeholder="Họ và tên" value="<?php echo isset($fullname) ? $fullname : ''; ?>">
                    </div>
                    <div class="mb-2">
                        <input type="email" name="email" class="form-control" placeholder="Email" value="<?php echo isset($email) ? $email : ''; ?>">
                    </div>
                    <div class="mb-2">
                        <input type="password" name="password" class="form-control" placeholder="Mật khẩu">
                    </div>
                    <div class="mb-2">
                        <input type="text" name="address" class="form-control" placeholder="Địa chỉ" value="<?php echo isset($address) ? $address : ''; ?>">
                    </div>
                    <div class="mb-2">
                        <input type="text" name="phone_number" class="form-control" placeholder="Số điện thoại" value="<?php echo isset($phone_number) ? $phone_number : ''; ?>">
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" name="checkbox" id="checkbox">
                        <label class="form-check-label" for="checkbox">Tôi đồng ý với điều khoản sử dụng</label>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Đăng ký</button>
                </form>
                <div class="text-center mt-2">
                    <p>Hoặc</p>
                    <button type="button" class="btn btn-danger">Google</button>
                    <button type="button" class="btn btn-primary">Facebook</button>
                </div>
                <p class="text-center mt-2">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>