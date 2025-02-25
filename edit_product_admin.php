<?php
// Kết nối tới cơ sở dữ liệu
include "connect.php";

$error = '';
$success = '';

// Lấy danh sách danh mục từ bảng categories
$sql_categories = "SELECT id, name FROM categories";
$result_categories = $connect->query($sql_categories);
$categories = [];

// Kiểm tra nếu có kết quả trả về từ truy vấn
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Lấy thông tin sản phẩm cần sửa
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $sql_product = "SELECT * FROM products WHERE id = ?";
    $stmt_product = $connect->prepare($sql_product);
    $stmt_product->bind_param("s", $product_id);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();
    $product = $result_product->fetch_assoc();
    $stmt_product->close();
}

// Xử lý khi người dùng cập nhật sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $brand = $_POST['brand'];
    $volume = $_POST['volume'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $image = $_FILES['image']['name'];

    // Lấy ngày người dùng chọn và tự động thêm giờ hiện tại
    $date = $_POST['created_at'];
    $time = date("H:i:s");
    $created_at = $date . ' ' . $time;

    // Nếu có ảnh mới thì cập nhật ảnh
    if (!empty($image)) {
        $target_dir = "images/";
        $target_file = $target_dir . basename($image);

        // Di chuyển ảnh vào thư mục images
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_query = ", image = '$target_file'";
        } else {
            $error = 'Có lỗi khi tải lên hình ảnh.';
        }
    } else {
        $image_query = ''; // Không cập nhật ảnh nếu không có ảnh mới
    }

    // Nếu không có lỗi
    if (empty($error)) {
        $sql_update = "UPDATE products SET name = ?, price = ?, stock = ?, brand = ?, volume = ?, description = ?, category_id = ?, created_at = ? $image_query WHERE id = ?";
        $stmt_update = $connect->prepare($sql_update);
        $stmt_update->bind_param("sdissssss", $name, $price, $stock, $brand, $volume, $description, $category_id, $created_at, $id);

        if ($stmt_update->execute()) {
            $success = 'Cập nhật sản phẩm thành công!';
            header("Location: home_admin.php"); // Chuyển hướng sau khi cập nhật thành công
            exit();
        } else {
            $error = 'Có lỗi xảy ra khi cập nhật sản phẩm: ' . $stmt_update->error;
        }

        $stmt_update->close();
    }
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="cs/demo.css">
</head>

<body>
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="dashboard-container">
        <div class="col-md-2 sidebar flex-column mySidebar" id="sidebar">
            <h4 class="mt-3">Dashboard</h4>
            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="home_admin.php">
                        <i class="bi bi-house-door"></i> Quản lý Sản Phẩm
                    </a>
                </li>
                <li class="nav-item">
                    <a href="add_product_admin.php" class="nav-link active">
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
            <h2 class="text-center">Sửa sản phẩm</h2>
            <a href="home_admin.php" class="btn btn-secondary mb-4">
                <i class="bi bi-arrow-left-circle"></i> Quay lại
            </a>

            <!-- Hiển thị thông báo lỗi hoặc thành công -->
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <!-- Form sửa sản phẩm -->
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="id">ID sản phẩm</label>
                        <input type="text" class="form-control" id="id" name="id" value="<?= $product['id'] ?>"
                            readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="name">Tên sản phẩm</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= $product['name'] ?>"
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="price">Giá tiền</label>
                        <input type="number" class="form-control" id="price" name="price"
                            value="<?= $product['price'] ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="stock">Số lượng</label>
                        <input type="number" class="form-control" id="stock" name="stock"
                            value="<?= $product['stock'] ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="volume">Dung tích</label>
                        <input type="text" class="form-control" id="volume" name="volume"
                            value="<?= $product['volume'] ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="brand">Thương hiệu</label>
                        <input type="text" class="form-control" id="brand" name="brand" value="<?= $product['brand'] ?>"
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="created_at">Ngày tạo</label>
                        <input type="date" class="form-control" id="created_at" name="created_at"
                            value="<?= date('Y-m-d', strtotime($product['created_at'])) ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="description">Thông tin sản phẩm</label>
                        <textarea class="form-control" name="description" id="description"
                            required><?= $product['description'] ?></textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="category_id">Danh mục</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Chọn danh mục</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                    <?= $category['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="image">Hình ảnh sản phẩm</label>
                        <input type="file" class="form-control-file" id="image" name="image">
                        <!-- Hiển thị ảnh hiện tại -->
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= $product['image'] ?>" alt="Hình ảnh sản phẩm" width="100">
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Cập nhật sản phẩm</button>
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