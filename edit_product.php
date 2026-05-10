<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id   = intval($_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, trim($_POST['product_name']));
    $product_sku  = mysqli_real_escape_string($conn, trim($_POST['product_sku']));
    $category_id  = intval($_POST['category_id']);
    $cost         = floatval($_POST['cost']);
    $price        = floatval($_POST['price']);

    if ($product_id <= 0 || empty($product_name) || empty($product_sku) || $category_id <= 0) {
        echo "Invalid input. Please fill in all fields correctly.";
        exit;
    }

    $sql = "UPDATE product 
            SET product_name = '$product_name',
                product_sku  = '$product_sku',
                category_id  = $category_id,
                cost         = $cost,
                price        = $price
            WHERE product_id = $product_id";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "Database error: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request method.";
}
?>
