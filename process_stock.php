<?php
require 'db_connect.php';

$p_id = $_POST['product_id'];
$qty = $_POST['quantity'];
$type = $_POST['type'];
// Grab expiry date and check if it's empty
$expiry = !empty($_POST['expiry_date']) ? "'" . $_POST['expiry_date'] . "'" : "NULL";

$sql = "INSERT INTO stock_transaction (
    transaction_id, product_id, transaction_type, quantity, transaction_date, buy_amount, sell_amount, expiry_date
) VALUES (
    NULL, '$p_id', '$type', '$qty', NOW(), NULL, NULL, $expiry
)";

if (mysqli_query($conn, $sql)) {
    echo "success";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>