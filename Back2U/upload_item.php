<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include "php/db.php";

$user_id = $_SESSION['user_id'];
$category = $_POST['category'];
$description = $_POST['description'];
$location = $_POST['location'];

$image_name = $_FILES['item_image']['name'];
$tmp_name = $_FILES['item_image']['tmp_name'];

$upload_folder = "uploads/";
$new_name = time() . "_" . $image_name;
$image_path = $upload_folder . $new_name;

if(move_uploaded_file($tmp_name, $image_path)){
    $query = "INSERT INTO items (user_id, image, category, description, location, status) 
              VALUES ('$user_id', '$new_name', '$category', '$description', '$location', 'available')";
    mysqli_query($conn, $query);
    echo "Item uploaded successfully <br><br><a href='../dashboard.php'>Back to Dashboard</a>";
} else {
    echo "Image upload failed";
}
?>
