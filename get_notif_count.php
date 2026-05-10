<?php
require 'db_connect.php';
$query = "SELECT COUNT(*) as total FROM notification WHERE status = 'unread'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);
echo $data['total'];
?>
