<?php
include "connect.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Bạn chưa đăng nhập!");
}

$user_id = $_SESSION['user_id']; 


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone_number = trim($_POST["phone_number"]);
    $address = trim($_POST["address"]);
    $payment_method = $_POST["payment_method"] ?? 'Cash'; 

    if (empty($full_name) || empty($email) || empty($phone_number) || empty($address)) {
        die("Lỗi: Tất cả các trường thông tin đều phải được điền đầy đủ!");
    }

    // Kiểm tra giỏ hàng 
    $sql_total = "SELECT c.product_id, c.quantity, p.price, (c.quantity * p.price) AS item_total FROM carts c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = ?";
    
    $stmt_total = $connect->prepare($sql_total);
    $stmt_total->bind_param('i', $user_id);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    
    $cart_items = []; 

    while ($row = $result_total->fetch_assoc()) {
        $cart_items[] = $row; 
        $total += $row['item_total']; 
    }

    if ($total <= 0) {
        die("Lỗi: Giỏ hàng trống!");
    }

    $connect->begin_transaction();
    try {
        // Tạo mới đơn hàng (lưu vào bảng orders)
        $sql_insert_order = "INSERT INTO orders (user_id, total, created_at, address, phone_number, status) 
                             VALUES (?, ?, NOW(), ?, ?, ?)";
        $status = 'Pending';  
        $stmt_order = $connect->prepare($sql_insert_order);
        $stmt_order->bind_param('iisss', $user_id, $total, $address, $phone_number, $status);

        if (!$stmt_order->execute()) {
            throw new Exception("Lỗi khi chèn đơn hàng: " . $stmt_order->error);
        }

        $order_id = $stmt_order->insert_id;  

        // Lưu sản phẩm từ giỏ hàng vào bảng order_items
        $sql_insert_order_item = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                                  VALUES (?, ?, ?, ?)";
        $stmt_order_item = $connect->prepare($sql_insert_order_item);

        // Lặp qua sản phẩm trong giỏ hàng và lưu vào bảng order_items
        foreach ($cart_items as $item) {
            $stmt_order_item->bind_param('iiid', $order_id, $item['product_id'], $item['quantity'], $item['price']);
            if (!$stmt_order_item->execute()) {
                throw new Exception("Lỗi khi lưu sản phẩm vào order_items: " . $stmt_order_item->error);
            }
        }

        // Lưu thông tin thanh toán 
        $sql_insert_payment = "INSERT INTO payments (order_id, payment_method, payment_status, created_at) 
                               VALUES (?, ?, 'Pending', NOW())";
        $stmt_payment = $connect->prepare($sql_insert_payment);
        $stmt_payment->bind_param('is', $order_id, $payment_method);

        if (!$stmt_payment->execute()) {
            throw new Exception("Lỗi khi lưu thông tin thanh toán: " . $stmt_payment->error);
        }

        $sql_update_payment = "UPDATE payments SET payment_status = 'Paid' WHERE order_id = ?";
        $stmt_update_payment = $connect->prepare($sql_update_payment);
        $stmt_update_payment->bind_param('i', $order_id);

        if (!$stmt_update_payment->execute()) {
            throw new Exception("Lỗi khi cập nhật trạng thái thanh toán: " . $stmt_update_payment->error);
        }

        // Xóa 
        $sql_delete_cart = "DELETE FROM carts WHERE user_id = ?";
        $stmt_delete_cart = $connect->prepare($sql_delete_cart);
        $stmt_delete_cart->bind_param('i', $user_id);

        if (!$stmt_delete_cart->execute()) {
            throw new Exception("Lỗi khi xóa giỏ hàng: " . $stmt_delete_cart->error);
        }

        $connect->commit();

        header("Location: order_success.php?order_id=$order_id");
        exit();
    } catch (Exception $e) {

        $connect->rollback();
        die("Lỗi khi xử lý thanh toán: " . $e->getMessage());
    }
}
?>
