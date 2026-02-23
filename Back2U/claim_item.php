<?php
session_start();
include 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (isset($_POST['submit_claim'])) {

    $item_id = $_POST['item_id'];
    $user_id = $_SESSION['user_id'];
    $lost_location = $_POST['lost_location'];
    $lost_date = $_POST['lost_date'];
    $description = $_POST['description'];
    // Check if already claimed
    $check = mysqli_query($conn, 
        "SELECT * FROM claims 
         WHERE item_id='$item_id' 
         AND claimer_id='$user_id'");

    if (mysqli_num_rows($check) == 0) {

        mysqli_query($conn, "INSERT INTO claims 
            (item_id, claimer_id, lost_location, lost_date, description)
            VALUES 
            ('$item_id', '$user_id', '$lost_location', '$lost_date', '$description')");

        // Change status to discussion (once)
        mysqli_query($conn, 
            "UPDATE items 
             SET status='discussion' 
             WHERE id='$item_id'");
    }
    // Redirect to GROUP CHAT
    header("Location: chat.php?item_id=$item_id");
    exit();
}
?>
