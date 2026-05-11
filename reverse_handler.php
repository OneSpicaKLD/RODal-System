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
        $target_id = $transaction_id;

        if ($type === "IN") {
            // It was a RESTOCK: Use the buy_amount, FORCE sell_amount to 0 to stop the trigger
            $val_buy = (float) $orig['buy_amount'] * -1;
            $insert = "INSERT INTO stock_transaction 
                   (product_id, transaction_type, quantity, buy_amount, sell_amount, transaction_date, related_tid) 
                   VALUES ($p_id, 'ADJUSTMENT', $neg_qty, $val_buy, 0, NOW(), $target_id)";
        } elseif ($type === "OUT") {
            // It was a SALE: Use the sell_amount, FORCE buy_amount to 0 to stop the trigger
            $val_sell = (float) $orig['sell_amount'] * -1;
            $insert = "INSERT INTO stock_transaction 
                   (product_id, transaction_type, quantity, buy_amount, sell_amount, transaction_date, related_tid) 
                   VALUES ($p_id, 'ADJUSTMENT', $neg_qty, 0, $val_sell, NOW(), $target_id)";
        }


        // Run the specific query built above
        if (isset($insert) && mysqli_query($conn, $insert)) {
            $new_id = mysqli_insert_id($conn);
            mysqli_query($conn, "UPDATE stock_transaction SET related_tid = $new_id WHERE transaction_id = $target_id");
            echo "success";
        }
    }

}
?>