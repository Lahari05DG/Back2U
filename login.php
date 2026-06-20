<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'php/db.php';

    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query  = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        }
    }

    // Login failed
    header("Location: index.php?msg=error");
    exit();
}

// Not a POST — send to the main login page
header("Location: index.php");
exit();
?>
