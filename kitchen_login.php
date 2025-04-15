<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM kitchen_staff WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 1) {
        $staff = $result->fetch_assoc();
        $_SESSION['kitchen_staff_id'] = $staff['id'];
        $_SESSION['kitchen_username'] = $staff['username'];
        header("Location: kitchen_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Invalid credentials');</script>";
    }
}
?>

<!-- Same modern styled HTML as your login.php -->
