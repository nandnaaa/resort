<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
include 'db.php';

$result = $conn->query("SELECT * FROM food_orders");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Food Orders</title>
</head>
<body>
<h1>Food Orders</h1>

<table>
    <tr>
        <th>Order ID</th>
        <th>Customer Name</th>
        <th>Food Items</th>
        <th>Status</th>
    </tr>
    <?php
    while ($row = $result->fetch_assoc()) {
        $customer_result = $conn->query("SELECT name FROM checkins WHERE id={$row['customer_id']}");
        $customer_name = $customer_result->fetch_assoc()['name'];
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$customer_name}</td>";
        echo "<td>{$row['items']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "</tr>";
    }
    ?>
</table>
</body>
</html>
