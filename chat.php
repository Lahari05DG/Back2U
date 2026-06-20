<?php
session_start();
include 'php/db.php';
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }

$item_id = isset($_GET['item_id']) ? mysqli_real_escape_string($conn, $_GET['item_id']) : '';
if(!$item_id) { header('Location: dashboard.php'); exit(); }
$user_id = $_SESSION['user_id'];

// Handle resolve POST
if(isset($_POST['resolve'])){
    mysqli_query($conn, "UPDATE items SET status='resolved' WHERE id='$item_id' AND user_id='$user_id'");
    header('Location: dashboard.php?msg=resolved');
    exit();
}

// Get item data
$itemData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM items WHERE id='$item_id'"));
if(!$itemData) { header('Location: dashboard.php'); exit(); }

// Get claimers
$claimersResult = mysqli_query($conn, "SELECT users.name FROM claims JOIN users ON claims.claimer_id=users.id WHERE claims.item_id='$item_id'");
$claimers = [];
while($c = mysqli_fetch_assoc($claimersResult)) { $claimers[] = $c['name']; }

// Get item owner name
$owner = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE id='".$itemData['user_id']."'"));

$pageTitle = 'Discussion';
$currentPage = 'chat';
include 'php/header.php';
?>

<div class="content-wrapper">
  <div class="chat-wrapper">

    <!-- Chat Header -->
    <div class="chat-header">
      <img class="chat-header-img" src="uploads/<?= htmlspecialchars($itemData['image']) ?>" alt="<?= htmlspecialchars($itemData['category']) ?>">
      <div class="chat-header-info">
        <h3><?= htmlspecialchars($itemData['category']) ?></h3>
        <span>Uploaded by <?= htmlspecialchars($owner['name'] ?? 'Unknown') ?></span>
      </div>
      <span class="status-badge <?= $itemData['status'] == 'resolved' ? 'resolved' : 'discussion' ?>">
        <?= $itemData['status'] == 'resolved' ? 'Resolved' : 'In Discussion' ?>
      </span>
      <?php if($itemData['user_id'] == $user_id && $itemData['status'] == 'discussion'): ?>
        <form method="POST" id="resolveForm" style="margin-left: auto;">
          <button type="button" class="btn btn-danger btn-sm" id="resolveBtn">
            <i class="fas fa-check-circle"></i> Resolve
          </button>
          <input type="hidden" name="resolve" value="1">
        </form>
      <?php endif; ?>
    </div>

    <!-- Claimers Bar -->
    <?php if(count($claimers) > 0): ?>
      <div class="chat-claimers">
        <span><i class="fas fa-users"></i> Claimants:</span>
        <?php foreach($claimers as $claimer): ?>
          <span class="claimer-chip"><?= htmlspecialchars($claimer) ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Chat Messages (loaded via AJAX) -->
    <div class="chat-messages" id="chatMessages">
      <div class="empty-state" id="chatLoading">
        <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem;"></i>
        <p>Loading messages...</p>
      </div>
    </div>

    <!-- Chat Input Bar -->
    <?php if($itemData['status'] == 'discussion'): ?>
      <div class="chat-input-bar">
        <input type="text" class="form-input" id="chatInput" placeholder="Type your message..." autocomplete="off">
        <button class="btn btn-primary" id="sendBtn">
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
    <?php else: ?>
      <div class="chat-input-bar" style="justify-content: center; opacity: 0.6;">
        <p style="margin: 0;"><i class="fas fa-lock"></i> This discussion has been resolved</p>
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
  const ITEM_ID = <?= (int)$item_id ?>;
</script>
<script src="js/chat.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Resolve confirmation
  const resolveForm = document.getElementById('resolveForm');
  const resolveBtn = document.getElementById('resolveBtn');

  if (resolveBtn && resolveForm) {
    resolveBtn.addEventListener('click', function() {
      showConfirm(
        'Resolve Item',
        'Are you sure you want to mark this item as resolved? This will close the discussion and indicate the owner has been found.',
        'success',
        'Yes, Resolve',
        'Cancel'
      ).then(function(confirmed) {
        if (confirmed) {
          resolveForm.submit();
        }
      });
    });
  }
});
</script>

<?php include 'php/footer.php'; ?>