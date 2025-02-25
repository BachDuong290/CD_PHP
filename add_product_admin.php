<?php
include "connect.php";

$error = '';
$success = '';

// Lấy danh sách danh mục 
$sql_categories = "SELECT id, name FROM categories";
$result_categories = $connect->query($sql_categories);
$categories = [];

// Kiểm tra kết quả trả về truy vấn
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $brand = $_POST['brand'];
    $volume = $_POST['volume'];
    $description = $_POST['description'];
    $created_at = $_POST['created_at'];
    $category_id = $_POST['category_id'];
    $image = $_FILES['image']['name'];

    $target_dir = "images/";
    $target_file = $target_dir . basename($image);

    // Kiểm tra ID đã tồn tại
    $sql_check_id = "SELECT id FROM products WHERE id = ?";
    $stmt_check = $connect->prepare($sql_check_id);
    $stmt_check->bind_param("s", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $error = 'ID sản phẩm đã tồn tại. Vui lòng nhập một ID khác.';
    } else {
        if (empty($error)) {
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Thêm sản phẩm vào cơ sở dữ liệu
                $sql = "INSERT INTO products (id, name, price, stock, brand, volume, description, created_at, category_id, image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param("ssdisssss", $id, $name, $price, $stock, $brand, $volume, $description, $category_id, $target_file);

                if ($stmt->execute()) {
                    $success = 'Thêm sản phẩm thành công!';
                    header("Location: home_admin.php");
                    exit();
                } else {
                    $error = 'Có lỗi xảy ra khi thêm sản phẩm: ' . $stmt->error;
                }

                $stmt->close();
            } else {
                $error = 'Có lỗi khi tải lên hình ảnh.';
            }
        }
    }

    $stmt_check->close();
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="cs/demo.css">
</head>

<body>
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="dashboard-container">
        <div class="col-md-2 sidebar flex-column mySidebar" id="sidebar">
            <h4 class="mt-3"> Dashboard</h4>
            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="home_admin.php">
                        <i class="bi bi-house-door"></i> Quản lý Sản Phẩm
                    </a>
                </li>
                <li class="nav-item">
                    <a href="add_product_admin.php" class="nav-link">
                        <i class="bi bi-plus-square"></i>Thêm sản phẩm
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                    </a>
                </li>
            </ul>
        </div>

        <div class="table-container" id="main">
            <h2 class="text-center">Thêm sản phẩm</h2>
            <a href="home_admin.php" class="btn btn-secondary mb-4">
                <i class="bi bi-arrow-left-circle"></i> Quay lại
            </a>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="id">ID sản phẩm</label>
                        <input type="text" class="form-control" id="id" name="id" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="name">Tên sản phẩm</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="price">Giá tiền</label>
                        <input type="number" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="stock">Số lượng</label>
                        <input type="number" class="form-control" id="stock" name="stock" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="volume">Dung tích</label>
                        <input type="text" class="form-control" id="volume" name="volume" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="brand">Thương hiệu</label>
                        <input type="text" class="form-control" id="brand" name="brand" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="created_at">Ngày tạo</label>
                        <input type="date" class="form-control" id="created_at" name="created_at" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="description">Thông tin sản phẩm</label>
                        <textarea class="form-control" name="description" id="description" required></textarea>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="category_id">Danh mục</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Chọn danh mục</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>">
                                    <?= $category['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="image">Hình ảnh sản phẩm</label>
                        <input type="file" class="form-control-file" id="image" name="image" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Thêm sản phẩm</button>
            </form>
        </div>

        <script>
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('hidden');
                const mainContent = document.getElementById('main');
                mainContent.classList.toggle('shifted');
            }
        </script>
</body>

</html>