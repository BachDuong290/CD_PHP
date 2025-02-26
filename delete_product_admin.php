<?php
include "connect.php";

// lấy id sp -> xóa
if (isset($_GET['id'])) {
    $idToDelete = $_GET['id'];

    // xóa sp từ db
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $idToDelete);
    
    if ($stmt->execute()) {
        header("Location: home_admin.php");
        exit();
    } else {
        echo "Lỗi khi xóa sản phẩm.";
    }

    $stmt->close();
} else {
    echo "ID sản phẩm không được cung cấp.";
}

$connect->close();
?>
