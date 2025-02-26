<?php
include "connect.php";

// Lấy danh sách sản phẩm 4 sản phẩm nổi bật
$sql = "SELECT id, image, name, price, stock FROM products LIMIT 4";
$result = $connect->query($sql);

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) { // kt dữ liệu -> lặp kq -> lưu
        $products[] = $row;
    }
}

// Lấy danh sách danh mục
$sql = "SELECT id, name FROM categories";
$result = $connect->query($sql);

$categories = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// lấy danh sách danh mục theo category_id
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

$filtered_products = [];
if ($category_id > 0) {
    $sql = "SELECT * FROM products WHERE category_id = $category_id";
    $result = $connect->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $filtered_products[] = $row;
        }
    }
}

// Xử lý tìm kiếm sản phẩm
$search = isset($_GET['search']) ? trim($_GET['search']) : ''; // lấy từ khóa
$search_results = [];

if (!empty($search)) {
    $sql = "SELECT * FROM products WHERE name LIKE ?";
    $stmt = $connect->prepare($sql);
    $search_param = "%$search%";
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
    }
    $stmt->close();
}

// Lấy giỏ hàng của user
$sql = "SELECT c.product_id, c.quantity, p.name, p.price 
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

$connect->close();

?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beauty Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
                <form class="d-flex" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Tìm kiếm sản phẩm..."
                        required>
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

    <main class="container mt-3">
        <div class="row">
            <aside class="col-md-3">
                <div class="dropdown">
                    <button class="btn btn-primary text-white dropdown-toggle w-100" type="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-list"></i> Danh mục sản phẩm
                    </button>
                    <ul class="dropdown-menu w-100">
                        <?php foreach ($categories as $category) { ?>
                            <li>
                                <a class="dropdown-item" href="product.php?category_id=<?php echo $category['id']; ?>">
                                    <?php echo $category['name']; ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </aside>
            <section class="col-md-9">
                <img src="images/bannerquangcao.png" class="img-fluid" alt="Banner quảng cáo">
            </section>
        </div>
    </main>


    <div class="container py-4">
        <?php if (!empty($search)) { ?>
            <h4 class="text-primary fw-bold text-center mb-4">Kết quả tìm kiếm cho:
                "<?php echo htmlspecialchars($search); ?>"</h4>
            <div class="row">
                <?php if (!empty($search_results)) {
                    foreach ($search_results as $product) { ?>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="border rounded p-3 text-center">
                                <img src="<?php echo $product['image']; ?>" class="img-fluid" alt="<?php echo $product['name']; ?>">
                                <p class="mt-2 fw-bold"><?php echo $product['name']; ?></p>
                                <p class="text-danger fw-bold"><?php echo number_format($product['price'], 0, ',', '.'); ?> VND</p>
                                <a class="btn btn-success btn-sm w-100" href="cart.php?user_id" .$id>Thêm vào giỏ</a>
                                <a class="btn btn-outline-success btn-sm w-100 mt-2" href="product_detail.php">Xem chi tiết</a>
                            </div>
                        </div>
                    <?php }
                } else { ?>
                    <p class="text-center text-danger">Không tìm thấy sản phẩm nào.</p>
                <?php } ?>
            </div>
        <?php } else { ?>
            <h4 class="text-danger fw-bold text-center mb-4">SẢN PHẨM NỔI BẬT</h4>
            <div class="row">
                <?php foreach ($products as $product) { ?>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="border rounded p-3 text-center">
                            <img src="<?php echo $product['image']; ?>" class="img-fluid" alt="<?php echo $product['name']; ?>">
                            <p class="mt-2 fw-bold"><?php echo $product['name']; ?></p>
                            <p class="text-muted text-decoration-line-through">
                                <?php echo number_format($product['price'] * 1.2, 0, ',', '.'); ?> VND
                            </p>
                            <p class="text-danger fw-bold"><?php echo number_format($product['price'], 0, ',', '.'); ?> VND</p>
                            <form action="addtocart.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo $product['name']; ?>">
                                <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                                <input type="hidden" name="product_image" value="<?php echo $product['image']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-success btn-sm w-100" name="add_to_cart">Thêm vào
                                    giỏ</button>
                            </form>
                            <a class="btn btn-outline-success btn-sm w-100 mt-2"
                                href="product_detail.php?id=<?php echo $product['id']; ?>">Xem chi tiết</a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>