<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h2>Welcome, <?php echo $_SESSION['user_name']; ?></h2>

<div class="dashboard-buttons">
    <a href="upload_item.html"><button>Upload Item</button></a>
    <a href="find_item.php"><button>Find Item</button></a>
    <a href="logout.php"><button>Logout</button></a>
</div>

</body>
</html>
