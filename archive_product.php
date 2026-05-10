<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);

    if ($product_id <= 0) {
        echo "Invalid product ID.";
        exit;
    }

    // Get current stock so we can zero it out
    $stock_query = "SELECT 
                        SUM(CASE WHEN transaction_type = 'IN'  THEN quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN transaction_type = 'OUT' THEN quantity ELSE 0 END) as total_out
                    FROM stock_transaction
                    WHERE product_id = $product_id";

    $stock_res = mysqli_query($conn, $stock_query);
    $stock_row = mysqli_fetch_assoc($stock_res);
    $current_stock = ($stock_row['total_in'] ?? 0) - ($stock_row['total_out'] ?? 0);

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // 1. Set product as inactive
        $archive_sql = "UPDATE product SET is_active = 0 WHERE product_id = $product_id";
        if (!mysqli_query($conn, $archive_sql)) {
            throw new Exception(mysqli_error($conn));
        }

        // 2. If there's remaining stock, add an OUT transaction to zero it
        if ($current_stock > 0) {
            $zero_sql = "INSERT INTO stock_transaction (product_id, transaction_type, quantity, transaction_date)
                         VALUES ($product_id, 'OUT', $current_stock, NOW())";
            if (!mysqli_query($conn, $zero_sql)) {
                throw new Exception(mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
        echo "success";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Database error: " . $e->getMessage();
    }

} else {
    echo "Invalid request method.";
}
?>
