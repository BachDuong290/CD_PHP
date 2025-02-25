<?php
include "connect.php";

// lấy dữ liệu danh mục
$sql = "SELECT id, name, description FROM categories";
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

// dữ liệu chi tiết
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<p class='text-danger'>Không tìm thấy sản phẩm.</p>");
}

$product_id = intval($_GET['id']);

$sql = "SELECT p.id, p.image, p.name, p.price, p.volume, p.brand, p.stock,p.description, pd.detail_name, pd.detail_value 
        FROM products p
        LEFT JOIN product_details pd ON p.id = pd.product_id
        WHERE p.id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("<p class='text-danger'>Không tìm thấy sản phẩm.</p>");
}

$product = [];
while ($row = $result->fetch_assoc()) {
    if (empty($product)) {
        $product = [
            'id' => $row['id'],
            'image' => $row['image'],
            'name' => $row['name'],
            'price' => $row['price'],
            'volume' => $row['volume'],
            'brand' => $row['brand'],
            'stock' => $row['stock'],
            'description' => $row['description'],
            'details' => []
        ];
    }
    if (!empty($row['detail_name'])) {
        $product['details'][] = [
            'name' => $row['detail_name'],
            'value' => $row['detail_value']
        ];
    }
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
                <div class="row">
                    <div class="col-md-5">
                        <img src="<?php echo $product['image']; ?>" class="img-fluid"
                            alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="col-md-7">
                        <h2><?php echo $product['name']; ?></h2>

                        <div class="d-flex align-items-center">
                            <p class="me-3"><b>Thương hiệu: </b><span
                                    class="text-danger"><?php echo $product['brand']; ?></p></span>
                            <p><b>Tình trạng:</b>
                                <span
                                    class="fw-bold <?php echo ($product['stock'] > 0) ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($product['stock'] > 0) ? 'Còn hàng' : 'Hết hàng'; ?>
                                </span>
                            </p>
                        </div>

                        <p>
                            <span class="fs-3 fw-bold text-danger">
                                <?php echo number_format($product['price'], 0, ',', '.'); ?> VND
                            </span>
                            <span class="text-muted text-decoration-line-through me-3">
                                <?php echo number_format($product['price'] * 1.2, 0, ',', '.'); ?> VND
                            </span>
                        </p>

                        <p><b>Dung tích:</b> <?php echo $product['volume']; ?></p>

                        <div>
                            <b>Thông số kỹ thuật:</b>
                            <ul>
                                <?php foreach ($product['details'] as $detail) { ?>
                                    <li><?php echo $detail['name'] . ": " . $detail['value']; ?></li>
                                <?php } ?>
                            </ul>
                        </div>

                        <div class="mt-2 d-flex align-items-center">
                            <b class="me-2">Số lượng:</b>
                            <div class="border rounded d-flex align-items-center px-2">
                                <button class="btn btn-outline-secondary px-3" onclick="changeQuantity(-1)">-</button>
                                <input type="text" id="quantity" value="1" size="1" class="text-center border-0 mx-2"
                                    style="width: 40px;" readonly>
                                <button class="btn btn-outline-secondary px-3" onclick="changeQuantity(1)">+</button>
                            </div>
                        </div>
                        <form action="addtocart.php?user_id=<?php echo $user_id; ?>" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $product['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                            <input type="hidden" name="product_image" value="<?php echo $product['image']; ?>">
                            <input type="hidden" id="hiddenQuantity" name="quantity" value="1">
                            <button type="submit" class="btn text-white fw-bold mt-3 px-4 py-2"
                                style="background-color: red;" id="addToCart">Thêm vào giỏ hàng</button>
                        </form>
                        </a>
                    </div>
                </div>
            </section>
        </div>

        <div class="mt-4">
            <ul class="nav nav-tabs" id="productTabs">
                <li class="nav-item">
                    <a class="nav-link" id="info-tab" data-bs-toggle="tab" href="#information">Thông tin</a>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <div class="tab-pane fade show active" id="description">
                    <h5 class="fw-bold">Thông tin sản phẩm</h5>
                    <p><?php echo $product['description']; ?></p>
                </div>
            </div>
        </div>
    </main>


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
        function changeQuantity(amount) {
            let quantityInput = document.getElementById("quantity");
            let hiddenQuantityInput = document.getElementById("hiddenQuantity");
            let currentValue = parseInt(quantityInput.value);

            let newValue = currentValue + amount;
            if (newValue < 1) newValue = 1;

            quantityInput.value = newValue;
            hiddenQuantityInput.value = newValue;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>