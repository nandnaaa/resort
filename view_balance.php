<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    mysqli_query($conn, "DELETE FROM transactions WHERE id = $delete_id");
}

// Handle Filters
$filter_user = $_GET['user'] ?? '';
$filter_type = $_GET['type'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$conditions = [];
if ($filter_user !== '') $conditions[] = "t.user_id = '$filter_user'";
if ($filter_type !== '') $conditions[] = "t.type = '$filter_type'";
if ($start_date !== '') $conditions[] = "t.created_at >= '$start_date'";
if ($end_date !== '') $conditions[] = "t.created_at <= '$end_date'";

$where = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get all users for dropdown
$users = mysqli_query($conn, "SELECT id, username FROM users");

// Get filtered transactions
$transactions = mysqli_query($conn, "
    SELECT u.username, t.* 
    FROM transactions t 
    JOIN users u ON t.user_id = u.id 
    $where
    ORDER BY t.created_at DESC
");

// Chart Data Preparation
$chart_data = [];
$usernames = [];
$totals = [];

$res_chart = mysqli_query($conn, "
    SELECT u.username,
        SUM(CASE WHEN t.type='income' THEN t.amount ELSE 0 END) AS income,
        SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END) AS expense
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    $where
    GROUP BY t.user_id
");

while ($row = mysqli_fetch_assoc($res_chart)) {
    $usernames[] = $row['username'];
    $totals[] = $row['income'] - $row['expense'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Filtered View | Resort Tally</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
        }
        .filter-form, .chart-container, .table-container {
            width: 90%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .filter-form h3 {
            margin-bottom: 10px;
            color: #1e293b;
        }
        .filter-form form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        .filter-form select,
        .filter-form input {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .filter-form button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: #334155;
            color: white;
        }
        .delete-form {
            margin: 0;
        }
        .delete-form button {
            background-color: #ef4444;
            border: none;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
        }
        a {
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 20px;
            color: white;
            background: #10b981;
            padding: 10px;
            border-radius: 8px;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>

    <div class="filter-form">
        <h3>Filter Transactions</h3>
        <form method="GET">
            <select name="user">
                <option value="">All Users</option>
                <?php while ($user = mysqli_fetch_assoc($users)) { ?>
                    <option value="<?= $user['id'] ?>" <?= $filter_user == $user['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['username']) ?>
                    </option>
                <?php } ?>
            </select>
            <select name="type">
                <option value="">All Types</option>
                <option value="income" <?= $filter_type == 'income' ? 'selected' : '' ?>>Income</option>
                <option value="expense" <?= $filter_type == 'expense' ? 'selected' : '' ?>>Expense</option>
            </select>
            <input type="date" name="start_date" value="<?= $start_date ?>">
            <input type="date" name="end_date" value="<?= $end_date ?>">
            <button type="submit">Apply Filters</button>
        </form>
    </div>

    <div class="chart-container">
        <canvas id="balanceChart"></canvas>
    </div>

    <div class="table-container">
        <h3>Transaction List</h3>
        <table>
            <tr>
                <th>User</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($transactions)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= ucfirst($row['type']) ?></td>
                    <td>₹<?= number_format($row['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <form method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <a href="dashboard.php">← Back to Dashboard</a>

    <script>
        const ctx = document.getElementById('balanceChart').getContext('2d');
        const balanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($usernames) ?>,
                datasets: [{
                    label: 'Net Balance',
                    data: <?= json_encode($totals) ?>,
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderColor: 'rgba(59,130,246,1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (₹)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
