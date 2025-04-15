<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];

    // Fetch food order details
    $order_query = "SELECT * FROM food_orders WHERE customer_id=$customer_id";
    $order_result = $conn->query($order_query);
    $food_total = 0;

    while ($order = $order_result->fetch_assoc()) {
        // Assuming prices are stored or can be calculated dynamically based on the items
        $food_items = explode(',', $order['items']);
        $quantities = explode(',', $order['quantities']);
        foreach ($food_items as $key => $item) {
            $item_query = $conn->query("SELECT price FROM menu WHERE id=$item");
            $item_price = $item_query->fetch_assoc()['price'];
            $food_total += $item_price * $quantities[$key];
        }
    }

    // Calculate total bill (room stay, food, discounts)
    $checkin_query = "SELECT * FROM checkins WHERE id=$customer_id";
    $checkin_result = $conn->query($checkin_query);
    $stay = $checkin_result->fetch_assoc()['stay_duration']; // Assume duration stored
    $discount = 0;  // Replace with discount logic if available

    $total_bill = $stay + $food_total - $discount;

    echo "Total Bill: $total_bill";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Consolidated Bill</title>
</head>
<body>
<h1>Generate Bill</h1>
<form method="POST">
    <label>Customer ID</label>
    <input type="number" name="customer_id" required><br>
    <button type="submit">Generate Bill</button>
</form>
</body>
</html>
