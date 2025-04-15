<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
include 'db.php';

// Fetch some quick stats
$pendingOrders = $conn->query("SELECT COUNT(*) AS total FROM food_orders WHERE status = 'Pending'")->fetch_assoc()['total'];
$activeCheckins = $conn->query("SELECT COUNT(*) AS total FROM checkins WHERE checkout_time IS NULL")->fetch_assoc()['total'];
$todayTotal = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Green Trees Resort | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #00c9ff 0%, #92fe9d 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 10px;
        }

        .dashboard-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 100%;
            max-width: 700px;
        }

        h2 {
            font-size: 32px;
            font-weight: 800;
            color: #2f2f2f;
            margin-bottom: 10px;
        }

        .welcome {
            font-size: 18px;
            margin-bottom: 25px;
            font-weight: 500;
            color: #444;
        }

        .stats-boxes {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .stat-card {
            flex: 1 1 150px;
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 20px;
            color: #333;
        }

        .stat-card p {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #007bff;
        }

        .nav-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }

        .nav-buttons a {
            text-decoration: none;
            padding: 14px;
            border-radius: 12px;
            background: linear-gradient(145deg, #ff9a9e, #fad0c4);
            font-weight: bold;
            font-size: 17px;
            color: #333;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .nav-buttons a:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .footer {
            margin-top: 30px;
            font-size: 13px;
            opacity: 0.7;
        }

        @media (max-width: 500px) {
            .stat-card h3 { font-size: 16px; }
            .stat-card p { font-size: 20px; }
            .nav-buttons a { font-size: 15px; padding: 12px; }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <h2>🌴 Green Trees Resort</h2>
    <div class="welcome">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</div>

    <!-- Stats -->
    <div class="stats-boxes">
        <div class="stat-card">
            <h3>Pending Food Orders</h3>
            <p><?= $pendingOrders ?></p>
        </div>
        <div class="stat-card">
            <h3>Checked-in Guests</h3>
            <p><?= $activeCheckins ?></p>
        </div>
        <div class="stat-card">
            <h3>Today's Revenue</h3>
            <p>₹ <?= number_format($todayTotal, 2) ?></p>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="nav-buttons">
        <a href="checkin.php">🛎️ Customer Check-In</a>
        <a href="checkout.php">🧾 Customer Checkout</a>
        <a href="food_order.php">🍔 Food Order</a>
        <a href="kitchen_dashboard.php">👨‍🍳 Kitchen Dashboard</a>
        <a href="add_transaction.php">➕ Add Transaction</a>
        <a href="view_balance.php">📊 View Balance</a>
        <a href="menu_manager.php">📋 Menu Manager</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="footer">
        © 2025 Green Trees Resort | All rights reserved
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
