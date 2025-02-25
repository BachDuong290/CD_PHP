<?php
include "connect.php";

// lấy sản phẩm cùng danh mục
$sql = "SELECT p.id, p.image, p.name, p.price, p.volume, p.brand, p.stock, p.created_at, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id";
$result = $connect->query($sql);

$products = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// tìm kiếm 
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT p.id, p.image, p.name, p.price, p.volume, p.brand, p.stock, p.created_at, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.name LIKE ? OR p.price LIKE ? OR p.brand LIKE ?";

$stmt = $connect->prepare($sql);
$search = "%$search%";

$stmt->bind_param("sss", $search, $search, $search);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$stmt->close();

$connect->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm</title>
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
            <div class="table-wrapper">
                <h2 class="text-center mb-4">Quản Lý Sản Phẩm</h2>

                <!-- Thanh tìm kiếm -->
                <form method="GET" action="" class="text-end form-inline mb-3">
                    <input type="text" name="search" class="form-control mr-2"
                        placeholder="Tìm kiếm (ví dụ: sản phẩm, giá, thương hiệu)"
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </form>

                <!-- Bảng danh sách sản phẩm -->
                <table class="table table-bordered table-hover text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Danh mục</th>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá tiền</th>
                            <th>Số lượng</th>
                            <th>Dung tích</th>
                            <th>Thương hiệu</th>
                            <th>Ngày tạo</th>
                            <th>Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Hình ảnh sản phẩm"
                                                style="width: 100px; height: auto;">
                                        <?php else: ?>
                                            <span>Không có hình</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo number_format($product['price'], 0, ',', '.'); ?> VND</td>
                                    <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                    <td><?php echo htmlspecialchars($product['volume']); ?> ml</td>
                                    <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                    <td><?php echo htmlspecialchars($product['created_at']); ?></td>
                                    <td>
                                        <a href="edit_product_admin.php?id=<?php echo $product['id']; ?>"
                                            class="btn btn-warning btn-sm">Sửa</a>
                                        <a href="delete_product_admin.php?id=<?php echo $product['id']; ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">Không tìm thấy sản phẩm phù hợp</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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
