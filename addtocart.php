<?php
include "connect.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

$user_id = $_SESSION['user_id']; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1; 

    // Kiểm tra sản phẩm trong cart
    $check_sql = "SELECT quantity FROM carts WHERE user_id = ? AND product_id = ?";
    $stmt = $connect->prepare($check_sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows > 0) {
        // sản phẩm đã có cập nhật số lượng
        $row = $check_result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity; 

        $update_sql = "UPDATE carts SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $update_stmt = $connect->prepare($update_sql);
        $update_stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
        $update_stmt->execute();
    } else {
        // thêm vào giỏ hàng mới
        $insert_sql = "INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $insert_stmt = $connect->prepare($insert_sql);
        $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $insert_stmt->execute();
    }

    header("Location: cart.php");
    exit();
}

$connect->close();
?>
