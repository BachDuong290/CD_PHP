<?php
session_start(); // Bắt đầu session để theo dõi người dùng đăng nhập

// Kiểm tra xem người dùng đã đăng nhập chưa, nếu chưa thì chuyển hướng đến trang đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
    exit();
}

include "connect.php";
$user_id = $_SESSION['user_id']; // Lấy user_id từ session

// Truy vấn giỏ hàng của người dùng đã đăng nhập
$sql = "SELECT c.product_id, c.quantity, p.name, p.price, p.image
        FROM carts c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

$cart_items = [];
while ($row = $cart_result->fetch_assoc()) {
    $cart_items[] = $row;
}


// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    if ($total == 0) {
        echo "<script>alert('Giỏ hàng trống! Không thể Đặt hàng.');</script>";
    } else {
        echo "<script>alert('Đặt hàng thành công! Tổng đơn hàng: " . number_format($total, 2) . " VNĐ');</script>";

        header("Location: checkout.php?total=" . $total);
        exit();
    }
}

$connect->close();
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beauty Store - Giỏ Hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="cs/demo.css">
</head>

<body>
    <!-- Header -->
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
                <a href="cart.php" class="ms-3 text-primary position-relative">
                    <i class="fas fa-shopping-bag fs-6"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo count($cart_items); ?>
                    </span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Giỏ hàng -->
    <div class="container mt-3">
        <h2 class="text-center">Giỏ Hàng</h2>
        <a href="product.php" class="btn btn-outline-primary">⬅ Trở về trang sản phẩm</a>
        <div class="row mt-4">
            <div class="col-md-8">
                <?php if (count($cart_items) > 0) { ?>
                    <table class="cart-table table table-bordered">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item) { ?>
                                <tr>
                                    <td class="d-flex align-items-center">
                                        <img src="<?php echo $item['image']; ?>" width="50" class="me-2"
                                            alt="<?php echo $item['name']; ?>">
                                        <span><?php echo $item['name']; ?></span>
                                    </td>
                                    <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VND</td>
                                    <td>
                                        <form method="POST" action="updatecart.php"
                                            class="quantity-container d-flex align-items-center">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">

                                            <button type="submit" name="decrease" class="btn btn-primary btn-sm">-</button>

                                            <input type="text" name="quantity" value="<?php echo $item['quantity']; ?>"
                                                class="form-control text-center mx-2 qty-input" style="width: 50px;" readonly>

                                            <button type="submit" name="increase" class="btn btn-primary btn-sm">+</button>
                                        </form>
                                    </td>
                                    <td class="total-price" data-id="<?php echo $item['product_id']; ?>"
                                        data-price="<?php echo $item['price']; ?>">
                                        <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VND
                                    </td>
                                    <td>
                                        <a href="cart.php?delete_id=<?php echo $item['product_id']; ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                            Xóa
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="text-center text-muted fs-5 mt-3">🛒 Giỏ hàng của bạn đang trống!</p>
                <?php } ?>
            </div>

            <!-- Tổng giỏ hàng -->
            <div class="col-md-4">
                <div class="summary p-4">
                    <h4>Tổng Giỏ Hàng</h4>
                    <ul id="product-list">
                        <?php foreach ($cart_items as $item) { ?>
                            <li data-id="<?php echo $item['product_id']; ?>">
                                <strong><?php echo $item['name']; ?></strong> (x<span
                                    class="product-qty"><?php echo $item['quantity']; ?></span>)
                            </li>
                        <?php } ?>
                    </ul>

                    <hr>

                    <p>Tổng tiền: <strong id="cart-total">
                            <?php
                            $total = 0;
                            foreach ($cart_items as $item) {
                                $total += $item['price'] * $item['quantity'];
                            }
                            echo number_format($total, 0, ',', '.');
                            ?> VND</strong></p>
                    <form method="POST">
                        <button type="submit" name="checkout" class="btn btn-success w-100 mt-3">Đặt hàng</button>
                    </form>
                </div>
            </div>
        </div>
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
                    <p><strong>Email:</strong>beautystore@gmail.com</p>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
