<?php
session_start(); 

include "connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } else {
        header("Location: login.php");
        exit();
    }
    
    // nhận id và kt 
    $product_id = intval($_POST['product_id']);
    $sql = "SELECT quantity FROM carts WHERE user_id = ? AND product_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $quantity = $row['quantity'];

        // kt nút tăng/ giảm
        if (isset($_POST['increase'])) {
            $quantity++;
        } elseif (isset($_POST['decrease']) && $quantity > 1) {
            $quantity--;
        }

        // truy vấn csdl carts
        $sql = "UPDATE carts SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        $stmt->execute();
    }
}

header("Location: cart.php");
exit();
?>
