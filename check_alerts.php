<?php
require 'db_connect.php';

// 1. Check for Low Stock
$lowStockQuery = "SELECT p.product_id, p.product_name, 
                  COALESCE(MAX(t.transaction_id), 0) as latest_tid, 
                  IFNULL(SUM(CASE WHEN t.transaction_type = 'IN' THEN t.quantity ELSE -t.quantity END), 0) as total_qty 
                  FROM product p 
                  LEFT JOIN stock_transaction t ON p.product_id = t.product_id 
                  GROUP BY p.product_id 
                  HAVING total_qty <= 10";

$lowResult = mysqli_query($conn, $lowStockQuery);
$stmtLow = $conn->prepare("INSERT INTO notification (product_id, transaction_id, title, message) VALUES (?, ?, 'Low Stock', ?)");

while ($row = mysqli_fetch_assoc($lowResult)) {
    $pid = $row['product_id'];
    $tid = ($row['latest_tid'] == 0) ? NULL : $row['latest_tid'];
    $current_qty = $row['total_qty'];
    $msg = $row['product_name'] . " is running low ($current_qty left).";

    // This check is very strict: it requires the SAME product AND the SAME transaction ID.
    // Since restocking from 0 to 5 creates a NEW transaction ID, 
    // this check will return 0 and trigger a NEW notification.
    $check = "SELECT id FROM notification 
              WHERE product_id = '$pid' 
              AND IFNULL(transaction_id, 0) = IFNULL('$tid', 0) 
              AND title = 'Low Stock'";

    $check_res = mysqli_query($conn, $check);

    if (mysqli_num_rows($check_res) == 0) {
        $stmtLow->bind_param("iis", $pid, $tid, $msg);
        $stmtLow->execute();
    }
}
$stmtLow->close();

// 2. Check for EVERY Batch Expiring within 30 Days
$expiryQuery = "SELECT 
                    p.product_id, 
                    p.product_name, 
                    t.transaction_id AS latest_tid,
                    t.expiry_date AS nearest_expiry, 
                    t.quantity AS batch_qty
                FROM product p
                JOIN stock_transaction t ON p.product_id = t.product_id 
                WHERE t.transaction_type = 'IN' 
                  AND t.expiry_date IS NOT NULL
                  AND t.expiry_date >= CURDATE()
                  AND t.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";



$expResult = mysqli_query($conn, $expiryQuery);

// Prepare the Expiry Insert Statement
$stmtExp = $conn->prepare("INSERT INTO notification (product_id, transaction_id, title, message) VALUES (?, ?, 'Expiry Warning', ?)");

while ($row = mysqli_fetch_assoc($expResult)) {
    $pid = $row['product_id'];
    $tid = $row['latest_tid'];
    $expiryDate = $row['nearest_expiry'];
    $qty = $row['batch_qty'];

    // Updated message to specify exactly how many are in THIS batch
    $msg = "Expiring soon: " . $row['product_name'] . " [Batch #$tid] ($qty units) on $expiryDate";

    // Duplicate Check: Look for this SPECIFIC Transaction ID
    $check = "SELECT id FROM notification 
              WHERE transaction_id = '$tid' 
              AND title = 'Expiry Warning' 
              AND status IN ('unread', 'read')";

    if (mysqli_num_rows(mysqli_query($conn, $check)) == 0) {
        $stmtExp->bind_param("iis", $pid, $tid, $msg);
        $stmtExp->execute();
    }
}
$stmtExp->close();


?>