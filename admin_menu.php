<?php
session_start();
include 'db.php';

// --- Authentication (Optional: Replace with admin check) ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- Handle form submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Category
    if (isset($_POST['new_category'])) {
        $cat = $_POST['new_category'];
        $stmt = $conn->prepare("INSERT INTO menu_categories (name) VALUES (?)");
        $stmt->bind_param("s", $cat);
        $stmt->execute();
    }

    // Add Menu Item
    if (isset($_POST['item_name'])) {
        $name = $_POST['item_name'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $is_veg = $_POST['is_veg'];
        $seasonal = isset($_POST['seasonal']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO menu_items (name, category_id, price, is_veg, seasonal_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siddi", $name, $category_id, $price, $is_veg, $seasonal);
        $stmt->execute();
    }
}

// --- Fetch categories and items ---
$categories = $conn->query("SELECT * FROM menu_categories ORDER BY name ASC");
$items = $conn->query("SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN menu_categories c ON m.category_id = c.id ORDER BY c.name, m.name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Menu Manager</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 30px; }
        h2 { margin-bottom: 20px; }
        .box { background: #fff; padding: 20px; margin-bottom: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 10px; }
        input, select { padding: 8px; width: 100%; margin-top: 5px; }
        button { margin-top: 15px; padding: 10px 20px; }
        table { width: 100%; margin-top: 30px; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>🍽️ Admin Menu Manager</h2>

    <div class="box">
        <h3>Add New Category</h3>
        <form method="POST">
            <label>Category Name</label>
            <input type="text" name="new_category" required>
            <button type="submit">Add Category</button>
        </form>
    </div>

    <div class="box">
        <h3>Add New Menu Item</h3>
        <form method="POST">
            <label>Item Name</label>
            <input type="text" name="item_name" required>

            <label>Category</label>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label>Price</label>
            <input type="number" name="price" step="0.01" required>

            <label>Veg/Non-Veg</label>
            <select name="is_veg">
                <option value="1">Veg</option>
                <option value="0">Non-Veg</option>
            </select>

            <label><input type="checkbox" name="seasonal"> Seasonal/Variable Price</label>

            <button type="submit">Add Item</button>
        </form>
    </div>

    <div class="box">
        <h3>📋 Current Menu Items</h3>
        <table>
            <tr>
                <th>Item</th>
                <th>Category</th>
                <th>Price</th>
                <th>Type</th>
                <th>Seasonal</th>
            </tr>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['category_name']) ?></td>
                    <td><?= number_format($item['price'], 2) ?></td>
                    <td><?= $item['is_veg'] ? 'Veg' : 'Non-Veg' ?></td>
                    <td><?= $item['seasonal_price'] ? 'Yes' : 'No' ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
