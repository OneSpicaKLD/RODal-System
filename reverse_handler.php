<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Siguraduhin na 'transaction_id' ang gamit (ito ang galing sa script.js)
    $transaction_id = intval($_POST['transaction_id']);

    $sql = "SELECT * FROM stock_transaction WHERE transaction_id = $transaction_id";
    $result = mysqli_query($conn, $sql);
    $orig = mysqli_fetch_assoc($result);

    if ($orig) {
        $p_id = $orig['product_id'];
        $type = $orig['transaction_type'];
        $neg_qty = $orig['quantity'] * -1;
        $neg_buy = $orig['buy_amount'] * -1;
        $neg_sell = $orig['sell_amount'] * -1;

        // 2. Insert the Adjustment
        $insert = "INSERT INTO stock_transaction 
                   (product_id, transaction_type, quantity, buy_amount, sell_amount, transaction_date, related_tid) 
                   VALUES ($p_id, 'ADJUSTMENT', $neg_qty, $neg_buy, $neg_sell, NOW(), $transaction_id)";

        if (mysqli_query($conn, $insert)) {
            // 3. Get the ID of the new adjustment we just made
            $new_id = mysqli_insert_id($conn);

            // 4. Update the ORIGINAL row (Using $transaction_id, not $tid)
            $update_orig = "UPDATE stock_transaction SET related_tid = $new_id WHERE transaction_id = $transaction_id";

            if (mysqli_query($conn, $update_orig)) {
                echo "success";
            } else {
                echo "Update Error: " . mysqli_error($conn);
            }
        } else {
            echo "Insert Error: " . mysqli_error($conn);
        }
    } else {
        echo "Transaction not found.";
    }
}
?>