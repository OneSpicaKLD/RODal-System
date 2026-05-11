<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $qty = mysqli_real_escape_string($conn, $_POST['quantity']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);

    // Capture the amounts from your form
    // If it's a Restock (IN), we use buy_amount. If Sale (OUT), we use sell_amount.
    $buy_amount = !empty($_POST['buy_amount']) ? mysqli_real_escape_string($conn, $_POST['buy_amount']) : 0;
    $sell_amount = !empty($_POST['sell_amount']) ? mysqli_real_escape_string($conn, $_POST['sell_amount']) : 0;

    $expiry = !empty($_POST['expiry_date']) ? "'" . mysqli_real_escape_string($conn, $_POST['expiry_date']) . "'" : "NULL";

    // UPDATED SQL: Now inserts the actual amounts instead of hardcoded NULLs
    $sql = "INSERT INTO stock_transaction (
        product_id, 
        transaction_type, 
        quantity, 
        transaction_date, 
        buy_amount, 
        sell_amount, 
        expiry_date
    ) VALUES (
        '$p_id', 
        '$type', 
        '$qty', 
        NOW(), 
        '$buy_amount', 
        '$sell_amount', 
        $expiry
    )";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>