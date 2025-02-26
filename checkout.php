<?php

session_start();
include "connect.php";

// Kiểm tra xem người dùng đã đăng nhập chưa
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Lấy thông tin người dùng từ cơ sở dữ liệu
    $sql = "SELECT id, full_name, email, phone_number, address FROM users WHERE id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // kt dữ liệu
    if ($result->num_rows > 0) {
        $user_info = $result->fetch_assoc();
    } else {
        echo "Không tìm thấy người dùng.";
        exit;
    }

    // Lấy sản phẩm trong giỏ hàng
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
} else {
    echo "Vui lòng đăng nhập để tiếp tục.";
    exit;
}

?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beauty Store - Thanh Toán</title>
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

    <!-- Thanh toán -->
    <div class="container mt-4">
        <a href="cart.php" class="btn btn-outline-primary">⬅ Trở về trang giỏ hàng</a>
        <h2 class="text-center"><i class="bi bi-credit-card"></i> Thanh toán</h2>
        <p class="text-center text-muted">Vui lòng kiểm tra thông tin trước khi đặt hàng</p>
        <div class="row">
            <div class="col-md-7">
                <h4>Thông tin khách hàng</h4>
                <form id="checkoutForm" action="checkout_info.php" method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $user_info['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Tên khách hàng: <span class="required">*</span></label>
                        <input type="text" name="full_name" class="form-control"
                            value="<?php echo $user_info['full_name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email: <span class="required">*</span></label>
                        <input type="text" name="email" class="form-control" value="<?php echo $user_info['email']; ?>"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại: <span class="required">*</span></label>
                        <input type="text" name="phone_number" class="form-control"
                            value="<?php echo $user_info['phone_number']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ giao hàng: <span class="required">*</span></label>
                        <input type="text" name="address" class="form-control"
                            value="<?php echo $user_info['address']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú đơn hàng:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú nếu có"></textarea>
                    </div>
                </form>
            </div>
            
            <div class="col-md-5">
                <h4>Đơn hàng <span class="badge bg-secondary"></span></h4>
                <ul class="list-group">
                    <?php $total_price = 0; ?>
                    <?php foreach ($cart_items as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo $item['name']; ?></strong>
                                <p class="text-muted">SL: <?php echo $item['quantity']; ?> x
                                    <?php echo number_format($item['price'], 0, ',', '.'); ?> VND
                                </p>
                            </div>
                            <span
                                class="badge bg-primary"><?php echo number_format($item['quantity'] * $item['price'], 0, ',', '.'); ?>
                                VND</span>
                        </li>
                        <?php $total_price += $item['quantity'] * $item['price']; ?>
                    <?php endforeach; ?>
                </ul>
                <!-- Hiển thị tổng tiền -->
                <div>
                    <form action="checkout_info.php" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <h5>Hình thức thanh toán</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="cash" checked>
                            <label class="form-check-label">Tiền mặt</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="bank_transfer"
                                onclick="toggleBankTransfer(true)">
                            <label class="form-check-label">Chuyển khoản ngân hàng</label>
                        </div>

                        <div id="bank-transfer-info" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Số tài khoản: 102475689910 </label>
                                <label class="form-label">Tên tài khoản ngân hàng: Beauty Store</label>
                                <label class="form-label">Tên ngân hàng: Ngân hàng VietinBank</label>
                            </div>
                        </div>
                        <div> <strong>Tổng tiền: <strong id="cart-total">
                                    <?php
                                    echo number_format($total_price, 0, ',', '.');
                                    ?> VND</strong></strong></div>
                        <button type="button" class="btn btn-danger w-100 mt-3" onclick="confirmOrder()">Thanh
                            toán</button>
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

    <script>
        function toggleBankTransfer(show) {
            var bankTransferInfo = document.getElementById("bank-transfer-info");
            if (show) {
                bankTransferInfo.style.display = "block";  // Hiển thị các trường ngân hàng
            } else {
                bankTransferInfo.style.display = "none";  // Ẩn các trường ngân hàng
            }
        }

        function confirmOrder() {
            if (confirm("Xác nhận thanh toán!")) {
                document.getElementById("checkoutForm").submit();
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>