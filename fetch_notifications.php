<?php
require 'db_connect.php';

$res = mysqli_query($conn, "SELECT * FROM notification ORDER BY created_at DESC LIMIT 5");

if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $icon = ($row['title'] == 'Expiry Warning') ? 'fa-calendar-times' : 'fa-exclamation-triangle';
        $unreadClass = ($row['status'] == 'unread') ? 'unread' : '';
        $time = date('H:i', strtotime($row['created_at']));

        echo "<li class='$unreadClass'>
                <i class='fas $icon'></i>
                <div>
                    <p><strong>{$row['title']}:</strong> {$row['message']}</p>
                    <span>$time</span>
                </div>
              </li>";
    }
} else {
    echo "<li><div style='text-align:center; width:100%;'><p>No notifications</p></div></li>";
}
?>