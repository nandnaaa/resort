<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

if (isset($_POST['save'])) {
    $uid = $_SESSION['user_id'];
    $type = $_POST['type'];
    $amt = $_POST['amount'];
    $desc = $_POST['description'];
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $next_due_date = $is_recurring ? date('Y-m-01', strtotime('+1 month')) : null;

    $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description, is_recurring, next_due_date) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdsis", $uid, $type, $amt, $desc, $is_recurring, $next_due_date);
    $stmt->execute();

    echo "<script>alert('Transaction added successfully!'); window.location.href='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Transaction</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #89f7fe, #66a6ff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 400px;
            max-width: 90%;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        select, input[type="number"], input[type="text"] {
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        label {
            margin-bottom: 10px;
            font-weight: 600;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .checkbox-container input {
            margin-right: 10px;
        }

        button {
            padding: 12px;
            background: #4a90e2;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #357ABD;
        }

        .back {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #4a90e2;
            text-decoration: none;
            font-weight: bold;
        }

        .back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Add Transaction</h2>
    <form method="POST">
        <label for="type">Type</label>
        <select name="type" id="type" required>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
        </select>

        <label for="amount">Amount</label>
        <input type="number" step="0.01" name="amount" id="amount" placeholder="Enter amount" required>

        <label for="description">Description</label>
        <input type="text" name="description" id="description" placeholder="Enter source or purpose" required>

        <div class="checkbox-container">
            <input type="checkbox" name="is_recurring" id="is_recurring" value="1">
            <label for="is_recurring">Mark as Monthly Recurring</label>
        </div>

        <button name="save">Add Transaction</button>
    </form>
    <a class="back" href="dashboard.php">← Back to Dashboard</a>
</div>

</body>
</html>
