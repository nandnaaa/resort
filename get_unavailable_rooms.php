<?php
include 'db.php';

// Only fetch currently occupied rooms (those without checkout time)
$query = "SELECT room_number FROM checkins WHERE checkout_time IS NULL";
$result = $conn->query($query);

$unavailable = [];
while ($row = $result->fetch_assoc()) {
    $unavailable[] = $row['room_number'];
}

echo json_encode($unavailable);
?>
