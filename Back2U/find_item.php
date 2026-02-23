<?php
session_start();
include "php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get unique items only
$query = "SELECT * FROM items WHERE status != 'resolved' GROUP BY id";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Find Item</title>
</head>
<body>

<h2>Lost Items</h2>

<?php while ($row = mysqli_fetch_assoc($result)) { ?>

    <div style="border:1px solid #ccc; padding:10px; margin:10px;">
        
        <img src="uploads/<?php echo $row['image']; ?>" width="150"><br>

        <b><?php echo $row['item_name']; ?></b><br>
        <?php echo $row['description']; ?><br>
        Location: <?php echo $row['location']; ?><br><br>

        <?php
        $item_id = $row['id'];

        // Check if THIS USER already claimed this item
        $checkClaim = mysqli_query($conn, 
            "SELECT * FROM claims 
             WHERE item_id='$item_id' 
             AND claimer_id='$user_id'"
        );

        if ($row['status'] == 'discussion') {
    echo "<a href='chat.php?item_id=".$row['id']."' 
           class='btn btn-warning'>In Discussion | Chat 💬</a>";
} else {
    echo "<a href='php/claim_form.php?item_id=".$row['id']."' 
           class='btn btn-primary'>This is Mine</a>";
}

        ?>

    </div>

<?php } ?>

</body>
</html>
