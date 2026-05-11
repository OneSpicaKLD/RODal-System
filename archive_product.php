<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);

    if ($product_id <= 0) {
        echo "Invalid product ID.";
        exit;
    }

    $sql = "UPDATE product SET is_active = 0 WHERE product_id = $product_id";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "Database error: " . mysqli_error($conn);
    }

} else {
    echo "Invalid request method.";
}
?>