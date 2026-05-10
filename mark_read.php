<?php
require 'db_connect.php';

// Set all unread notifications to read
$sql = "UPDATE notification SET status = 'read' WHERE status = 'unread'";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "success";
} else {
    echo "error: " . mysqli_error($conn);
}
?>