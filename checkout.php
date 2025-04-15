<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) header("Location: login.php");

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_id'])) {
    $id = $_POST['checkout_id'];

    $stmt = $conn->prepare("UPDATE checkins SET checkout_time = NOW(), status = 'checked_out' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<script>alert('Customer Checked Out Successfully!'); window.location.href='checkout.php';</script>";
    exit;
}

// Get all currently checked-in customers
$result = $conn->query("SELECT * FROM checkins WHERE status = 'checked_in' ORDER BY checkin_time DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(to right, #fbc2eb, #a6c1ee);
            display: flex;
            justify-content: center;
            align-items: start;
            padding: 40px;
            min-height: 100vh;
            margin: 0;
        }

        .checkout-container {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            width: 700px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border-bottom: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f3f3f3;
        }

        form {
            display: inline;
        }

        button {
            background-color: #ff5252;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #ff3b3b;
        }
    </style>
</head>
<body>
<div class="checkout-container">
    <h2>Customer Checkout</h2>
    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Room</th>
            <th>Phone</th>
            <th>Check-in</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['room_number']) ?> (<?= $row['room_type'] ?>)</td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= date("d M Y, h:i A", strtotime($row['checkin_time'])) ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="checkout_id" value="<?= $row['id'] ?>">
                    <button type="submit">Check Out</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>No customers currently checked in.</p>
    <?php endif; ?>
</div>
</body>
</html>
