<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$item_id = $_GET['item_id'];
?>

<h2>Claim Item</h2>

<form action="../claim_item.php" method="POST" enctype="multipart/form-data">
    
    <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">

    <label>Where did you lose it?</label><br>
    <input type="text" name="lost_location" required><br><br>

    <label>Date you lost it:</label><br>
    <input type="date" name="lost_date" required><br><br>

    <label>Describe identification marks:</label><br>
    <textarea name="description" required></textarea><br><br>

    <label>Upload Proof (optional):</label><br>
    <input type="file" name="proof_image"><br><br>

    <button type="submit" name="submit_claim">Submit Claim</button>
</form>
