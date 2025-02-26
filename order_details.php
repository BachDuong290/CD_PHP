<?php

include "connect.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Bạn chưa đăng nhập!");
}

$user_id = $_SESSION['user_id'];

// Truy vấn để lấy thông tin đơn hàng, sản phẩm, thanh toán
$sql_orders = "
    SELECT o.id AS order_id, o.created_at AS order_date, o.total, o.address, o.phone_number, o.status AS order_status,
           GROUP_CONCAT(p.image) AS product_images, 
           GROUP_CONCAT(p.name SEPARATOR ', ') AS product_names, 
           SUM(oi.quantity) AS total_quantity, 
           SUM(oi.price * oi.quantity) AS total_price, 
           pay.payment_method, pay.payment_status
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN payments pay ON o.id = pay.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC";
    // cb và thực thi truy vấn 
$stmt_orders = $connect->prepare($sql_orders);
$stmt_orders->bind_param('i', $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();

// Lấy giỏ hàng của user
$sql = "SELECT c.product_id, c.quantity, p.name, p.price 
        FROM carts c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

// lưu giỏ hàng
$cart_items = [];
while ($row = $cart_result->fetch_assoc()) {
    $cart_items[] = $row;
}

$stmt_orders->close();
$connect->close();
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Đơn Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="cs/demo.css">

</head>

<body>
    <header class="bg-custom text-white py-2">
        <div class="container d-flex justify-content-between align-items-center">
            <span>Chào mừng bạn đến Beauty Store</span>
            <div>
                <a href="#" class="text-white me-2"><i class="fab fa-facebook"></i></a>
                <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-white"><i class="fab fa-google"></i></a>
            </div>
        </div>
    </header>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand text-primary fw-bold fs-3" href="#">Beauty Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link text-dark fs-6" href="dashboard.php">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fs-6" href="product.php">Sản phẩm</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fs-6" href="cart.php">Giỏ hàng</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fs-6" href="account.php">Tài khoản</a></li>
                </ul>
                <form class="d-flex">
                    <input class="form-control me-2" type="search" placeholder="Tìm kiếm sản phẩm...">
                    <button class="btn btn-light" type="submit">
                        <i class="fas fa-search fs-6 text-primary"></i>
                    </button>
                </form>
                <div class="dropdown ms-3">
                    <a href="#" class="text-primary dropdown-toggle fs-6" id="userDropdown" role="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="account.php">Thông tin tài khoản</a></li>
                        <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                    </ul>
                </div>
                <a href="cart.php?user_id=<?php echo $user_id; ?>" class="ms-3 text-primary position-relative">
                    <i class="fas fa-shopping-bag fs-6"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo count($cart_items); ?>
                    </span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <a href="account.php" class="btn btn-outline-primary">⬅ Trở về trang tài khoản</a>
        <div class="cart-header">Chi tiết Đơn Hàng</div>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Giá</th>
                    <th>Tổng tiền</th>
                    <th>Ngày mua</th>
                    <th>Địa chỉ giao hàng</th>
                    <th>Phương thức thanh toán</th>
                    <th>Trạng thái thanh toán</th>
                    <th>Trạng thái đơn hàng</th>
                </tr>
            </thead>
            <tbody>
            <tbody>
                <?php if ($result_orders->num_rows > 0) {
                    while ($order = $result_orders->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <?php
                                $images = explode(',', $order['product_images']);
                                $names = explode(', ', $order['product_names']);
                                foreach ($images as $index => $img) {
                                    echo '<img src="' . $img . '" class="product-img" alt="Sản phẩm">';
                                    echo '<span>' . htmlspecialchars($names[$index]) . '</span><br>';
                                }
                                ?>
                            </td>

                            <td><?php echo $order['total_quantity']; ?></td>

                            <td><?php echo number_format($order['total_price'], 0, ',', '.'); ?> VND</td>

                            <td><?php echo number_format($order['total'], 0, ',', '.'); ?> VND</td>

                            <td><?php echo date("d/m/Y", strtotime($order['order_date'])); ?></td>

                            <td><?php echo htmlspecialchars($order['address']); ?></td>

                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>

                            <td><?php echo htmlspecialchars($order['payment_status']); ?></td>

                            <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td colspan="9" class="text-center">Không có đơn hàng nào.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <footer class="bg-light-green p-4">
        <div class="container">
            <div class="row">
                <div class="col-md-5 mb-3">
                    <h3><strong>Beauty Store</strong></h3>
                    <p>Beauty Store tự hào là điểm đến tin cậy cho phái đẹp, mang đến những sản phẩm mỹ phẩm chính hãng
                        từ các thương hiệu nổi tiếng trên toàn thế giới. Với sứ mệnh chăm sóc và tôn vinh vẻ đẹp tự
                        nhiên, chúng tôi cung cấp đa dạng các sản phẩm như chăm sóc da, trang điểm, dưỡng tóc và nước
                        hoa. Mỗi sản phẩm đều được chọn lọc kỹ lưỡng để đảm bảo an toàn, hiệu quả và mang lại sự tự tin
                        cho khách hàng.</p>
                </div>

                <div class="col-md-2 mb-3">
                    <h6>Chính Sách</h6>
                    <ul class="list-unstyled">
                        <li><a href="dashboard.php">Trang chủ</a></li>
                        <li><a href="product.php">Sản phẩm</a></li>
                        <li><a href="cart.php">Giỏ hàng</a></li>
                        <li><a href="account.php">Tài khoản</a></li>
                    </ul>
                </div>

                <div class="col-md-5 mb-3">
                    <h6>Thông Tin Chung</h6>
                    <p><strong>Địa chỉ:</strong> 386, Lê Văn Sỹ Phường 14, Quận 3, TP.HCM</p>
                    <p><strong>Điện thoại:</strong> (+84) 1900 6750</p>
                    <p><strong>Email: </strong>beautystore@gmail.com</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>