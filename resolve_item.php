<?php
session_start();
include "php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$item_id = $_GET['item_id'];
$user_id = $_SESSION['user_id'];

$item_id = mysqli_real_escape_string($conn, $item_id);
$user_id = mysqli_real_escape_string($conn, $user_id);

$query = "UPDATE items SET status='resolved' WHERE id='$item_id' AND user_id='$user_id'";
mysqli_query($conn, $query);

header("Location: dashboard.php?msg=resolved");
exit();
?>