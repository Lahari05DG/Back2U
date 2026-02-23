<?php
session_start();
include 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$item_id = $_GET['item_id'];
$user_id = $_SESSION['user_id'];

// Send message
if (isset($_POST['send'])) {
    $message = $_POST['message'];

    mysqli_query($conn, "INSERT INTO messages 
        (item_id, sender_id, message) 
        VALUES 
        ('$item_id', '$user_id', '$message')");
}
?>

<h3>Group Chat</h3>

<div style="border:1px solid #ccc; height:300px; overflow:auto;">
<?php
$messages = mysqli_query($conn, 
    "SELECT messages.*, users.name 
     FROM messages 
     JOIN users ON messages.sender_id = users.id
     WHERE item_id='$item_id'
     ORDER BY messages.created_at ASC");

while ($msg = mysqli_fetch_assoc($messages)) {
    echo "<p><b>".$msg['name']."</b>: ".$msg['message']."</p>";
}
?>
</div>


<form method="POST">
    <input type="text" name="message" required>
    <button name="send">Send</button>
</form>
