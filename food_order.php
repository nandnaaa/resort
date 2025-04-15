<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$customers = $conn->query("SELECT * FROM checkins WHERE checkout_time IS NULL");
$menu_items = $conn->query("SELECT * FROM menu_items ORDER BY category, name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $items = $_POST['item'];
    $quantities = $_POST['quantity'];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO food_orders (checkin_id, status, created_at) VALUES (?, 'Pending', NOW())");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        $stmt_item = $conn->prepare("INSERT INTO food_order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($items as $index => $item_id) {
            $quantity = $quantities[$index];
            $price_stmt = $conn->prepare("SELECT price, is_variable_price FROM menu_items WHERE id = ?");
            $price_stmt->bind_param("i", $item_id);
            $price_stmt->execute();
            $price_result = $price_stmt->get_result()->fetch_assoc();

            $price = $price_result['is_variable_price'] ? $_POST['custom_price'][$index] : $price_result['price'];

            $stmt_item->bind_param("iiid", $order_id, $item_id, $quantity, $price);
            $stmt_item->execute();
        }

        $conn->commit();
        echo "<script>alert('Order placed successfully'); location.href='food_order.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error placing order: {$e->getMessage()}');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Food Order</title>
    <style>
        body { font-family: sans-serif; background: #f5f5f5; padding: 20px; }
        form { background: white; padding: 20px; border-radius: 10px; max-width: 700px; margin: auto; }
        label, select, input { display: block; margin-bottom: 10px; width: 100%; }
        table { width: 100%; margin-top: 10px; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    </style>
</head>
<body>
<h2>Place Food Order</h2>
<form method="POST">
    <label>Customer</label>
    <select name="customer_id" required>
        <option value="">-- Select --</option>
        <?php while ($row = $customers->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?> - <?= $row['room_number'] ?></option>
        <?php endwhile; ?>
    </select>

    <table id="orderTable">
        <thead>
        <tr><th>Item</th><th>Qty</th><th>Custom Price</th><th>Remove</th></tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <select name="item[]">
                    <?php $menu_items->data_seek(0); while ($item = $menu_items->fetch_assoc()): ?>
                        <option value="<?= $item['id'] ?>"><?= $item['name'] ?> (<?= $item['category'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </td>
            <td><input type="number" name="quantity[]" min="1" required></td>
            <td><input type="number" name="custom_price[]" step="0.01"></td>
            <td><button type="button" onclick="removeRow(this)">X</button></td>
        </tr>
        </tbody>
    </table>
    <button type="button" onclick="addRow()">Add More</button><br><br>
    <button type="submit">Submit Order</button>
</form>

<script>
function addRow() {
    const table = document.querySelector("#orderTable tbody");
    const newRow = table.rows[0].cloneNode(true);
    newRow.querySelectorAll("input").forEach(input => input.value = "");
    table.appendChild(newRow);
}
function removeRow(btn) {
    const row = btn.closest("tr");
    const table = row.parentElement;
    if (table.rows.length > 1) row.remove();
}
</script>
</body>
</html>
