<?php
session_start();
include 'db.php';

// Check if admin (reuse user login or implement admin check)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'] !== '' ? $_POST['price'] : null;
    $seasonal_price = $_POST['seasonal_price'] ?? null;
    $is_variable = isset($_POST['is_variable']) ? 1 : 0;
    $type = $_POST['type'];

    if ($id) {
        $stmt = $conn->prepare("UPDATE menu_items SET category=?, name=?, price=?, seasonal_price=?, is_variable_price=?, type=? WHERE id=?");
        $stmt->bind_param("ssddisi", $category, $name, $price, $seasonal_price, $is_variable, $type, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO menu_items (category, name, price, seasonal_price, is_variable_price, type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddis", $category, $name, $price, $seasonal_price, $is_variable, $type);
    }
    $stmt->execute();
    header("Location: menu_manager.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM menu_items WHERE id = $id");
    header("Location: menu_manager.php");
    exit;
}

// Fetch all items
$items = $conn->query("SELECT * FROM menu_items ORDER BY category, name")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            padding: 20px;
            background: #f4f7fb;
            margin: 0;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: auto;
        }

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        input, select, button {
            padding: 12px;
            margin: 8px 0;
            width: 100%;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        input[type="checkbox"] {
            width: auto;
        }

        .btn {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f1f1f1;
        }

        .edit-btn {
            background: #28a745;
        }

        .edit-btn:hover {
            background: #218838;
        }

        .delete-btn {
            background: #dc3545;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .search-bar {
            width: 100%;
            max-width: 300px;
            padding: 8px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .filters {
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 15px;
            }

            .btn {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Menu Manager</h2>

        <!-- Search and Filters -->
        <div class="filters">
            <input type="text" class="search-bar" id="search" placeholder="Search menu items...">
        </div>

        <!-- Form for Adding/Editing Items -->
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="id" id="item_id">
                <label>Category</label>
                <input type="text" name="category" id="category" required>

                <label>Item Name</label>
                <input type="text" name="name" id="name" required>

                <label>Price (leave blank for seasonal)</label>
                <input type="number" name="price" id="price" step="0.01">

                <label>
                    <input type="checkbox" name="is_variable" id="is_variable"> Seasonal/Variable Price
                </label>

                <div id="seasonal_price_container" style="display: none;">
                    <label>Seasonal Price</label>
                    <input type="number" name="seasonal_price" id="seasonal_price" step="0.01">
                </div>

                <label>Type</label>
                <select name="type" id="type">
                    <option value="na">Not Applicable</option>
                    <option value="veg">Veg</option>
                    <option value="non-veg">Non-Veg</option>
                    <option value="egg">Egg</option>
                </select>

                <button type="submit" class="btn">Save Item</button>
            </form>
        </div>

        <!-- Menu Items Table -->
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="menu-items">
                <?php foreach ($items as $item): ?>
                    <tr class="menu-item">
                        <td><?= htmlspecialchars($item['category']) ?></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['is_variable_price'] ? 'Variable' : number_format($item['price'], 2) ?></td>
                        <td><?= $item['type'] ?></td>
                        <td>
                            <a href="?edit=<?= $item['id'] ?>" class="btn edit-btn" onclick="fillForm(<?= htmlspecialchars(json_encode($item)) ?>)">Edit</a>
                            <a href="?delete=<?= $item['id'] ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Show or hide seasonal price field based on checkbox
        document.getElementById('is_variable').addEventListener('change', function() {
            document.getElementById('seasonal_price_container').style.display = this.checked ? 'block' : 'none';
        });

        // Populate the form for editing
        function fillForm(data) {
            document.getElementById('item_id').value = data.id;
            document.getElementById('name').value = data.name;
            document.getElementById('category').value = data.category;
            document.getElementById('price').value = data.price;
            document.getElementById('seasonal_price').value = data.seasonal_price || '';
            document.getElementById('is_variable').checked = data.is_variable_price == 1;
            document.getElementById('type').value = data.type;
            document.getElementById('seasonal_price_container').style.display = data.is_variable_price == 1 ? 'block' : 'none';
            event.preventDefault();
        }

        // Search functionality
        document.getElementById('search').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('.menu-item');
            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const category = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                row.style.display = name.includes(filter) || category.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
