<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $check = "SELECT * FROM kitchen_staff WHERE username='$username'";
    $checkResult = $conn->query($check);

    if ($checkResult->num_rows > 0) {
        echo "<script>alert('Username already exists');</script>";
    } else {
        $sql = "INSERT INTO kitchen_staff (username, password) VALUES ('$username', '$password')";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['kitchen_staff_id'] = $conn->insert_id;
            $_SESSION['kitchen_username'] = $username;
            header("Location: kitchen_dashboard.php");
        } else {
            echo "<script>alert('Registration failed');</script>";
        }
    }
}
?>

<!-- Use similar HTML style as register.php -->
