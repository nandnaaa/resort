<?php
session_start();
if (!isset($_SESSION['kitchen_staff_id'])) {
    header("Location: kitchen_login.php");
    exit();
}
?>

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['kitchen_id'])) {
    header("Location: kitchen_login.php");
    exit;
}

// Fetch pending orders
$sql = "SELECT fo.id AS order_id, c.name AS customer_name, mi.name AS item_name, foi.quantity, fo.status 
        FROM food_orders fo 
        JOIN checkins c ON fo.customer_id = c.id 
        JOIN food_order_items foi ON fo.id = foi.order_id
        JOIN menu_items mi ON foi.item_id = mi.id
        WHERE fo.status IN ('Pending', 'Preparing', 'Ready')
        ORDER BY fo.id DESC";
$orders = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $conn->query("UPDATE food_orders SET status = '$new_status' WHERE id = $order_id");

    $stmt = $conn->prepare("INSERT INTO order_status_updates (order_id, status, updated_by) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $order_id, $new_status, $_SESSION['kitchen_username']);
    $stmt->execute();

    header("Location: kitchen_dashboard.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kitchen Dashboard</title>
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['kitchen_username']) ?></h2>
    <a href="kitchen_logout.php">Logout</a>
    <h3>Current Orders</h3>
    <table border="1">
        <tr><th>Order ID</th><th>Customer</th><th>Item</th><th>Qty</th><th>Status</th><th>Update</th></tr>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= $order['order_id'] ?></td>
            <td><?= htmlspecialchars($order['customer_name']) ?></td>
            <td><?= htmlspecialchars($order['item_name']) ?></td>
            <td><?= $order['quantity'] ?></td>
            <td><?= $order['status'] ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                    <select name="status">
                        <option value="Preparing">Preparing</option>
                        <option value="Ready">Ready</option>
                        <option value="Served">Served</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
