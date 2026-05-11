<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = mysqli_real_escape_string($conn, trim($_POST['product_name']));
    $product_sku  = mysqli_real_escape_string($conn, trim($_POST['product_sku']));
    $category_id  = intval($_POST['category_id']);
    $cost         = floatval($_POST['cost']);
    $price        = floatval($_POST['price']);

    // Basic validation
    if (empty($product_name) || empty($product_sku) || $category_id <= 0 || $cost < 0 || $price < 0) {
        echo "Invalid input. Please fill in all fields correctly.";
        exit;
    }

    $sql = "INSERT INTO product (product_name, product_sku, category_id, cost, price)
            VALUES ('$product_name', '$product_sku', $category_id, $cost, $price)";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "Database error: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request method.";
}
?>


// harold ako to