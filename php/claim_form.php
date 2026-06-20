<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit(); }
$item_id = isset($_GET['item_id']) ? mysqli_real_escape_string($conn, $_GET['item_id']) : '';
if(!$item_id) { header('Location: ../find_item.php'); exit(); }

// Get item details
$item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM items WHERE id='$item_id'"));
if(!$item) { header('Location: ../find_item.php'); exit(); }

// Check if user already claimed this item
$alreadyClaimed = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM claims WHERE item_id='$item_id' AND claimer_id='".$_SESSION['user_id']."'"));

$pageTitle = 'Claim Item';
$currentPage = 'find';
$cssPath = '../';
$basePath = '../';
include 'header.php';
?>

<div class="content-wrapper">
  <div class="container" style="max-width: 700px;">

    <!-- Page Header -->
    <div class="page-header animate-fadeIn">
      <h1 class="section-title">Claim This Item</h1>
      <p class="section-subtitle">Provide details to verify this item belongs to you</p>
    </div>

    <!-- Item Preview -->
    <div class="item-card animate-slideUp" style="margin-bottom: 1.5rem;">
      <div class="item-card-header" style="height: 200px;">
        <img src="../uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['category']) ?>">
        <div class="item-card-badge">
          <span class="status-badge <?= $item['status'] == 'resolved' ? 'resolved' : 'discussion' ?>">
            <?= $item['status'] == 'resolved' ? 'Resolved' : 'In Discussion' ?>
          </span>
        </div>
      </div>
      <div class="item-card-body">
        <span class="item-card-category"><?= htmlspecialchars($item['category']) ?></span>
        <p class="item-card-desc"><?= htmlspecialchars($item['description']) ?></p>
        <div class="item-card-meta">
          <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['location']) ?></span>
          <span><i class="fas fa-clock"></i> <?= date('M d, Y', strtotime($item['created_at'])) ?></span>
        </div>
      </div>
    </div>

    <?php if($alreadyClaimed > 0): ?>
      <!-- Already Claimed Notice -->
      <div class="empty-state animate-fadeIn">
        <i class="fas fa-check-circle" style="color: var(--success);"></i>
        <h3>Already Claimed</h3>
        <p>You have already submitted a claim for this item. Check the discussion for updates.</p>
        <a href="../chat.php?item_id=<?= $item_id ?>" class="btn btn-primary">
          <i class="fas fa-comments"></i> Open Discussion
        </a>
      </div>
    <?php elseif($item['status'] == 'resolved'): ?>
      <!-- Resolved Notice -->
      <div class="empty-state animate-fadeIn">
        <i class="fas fa-lock" style="color: var(--text-muted);"></i>
        <h3>Item Resolved</h3>
        <p>This item has already been returned to its owner.</p>
        <a href="../find_item.php" class="btn btn-outline">
          <i class="fas fa-arrow-left"></i> Back to Search
        </a>
      </div>
    <?php else: ?>
      <!-- Claim Form -->
      <div class="card animate-slideUp">
        <div class="card-body">
          <form action="../claim_item.php" method="POST" id="claimForm">
            <input type="hidden" name="item_id" value="<?= htmlspecialchars($item_id) ?>">

            <!-- Lost Location -->
            <div class="form-group">
              <label class="form-label" for="lost_location">Where did you lose it?</label>
              <div class="form-input-icon">
                <i class="fas fa-map-marker-alt"></i>
                <input class="form-input" type="text" name="lost_location" id="lost_location" placeholder="e.g., Library, Cafeteria, Room 204..." required>
              </div>
            </div>

            <!-- Lost Date -->
            <div class="form-group">
              <label class="form-label" for="lost_date">When did you lose it?</label>
              <div class="form-input-icon">
                <i class="fas fa-calendar-alt"></i>
                <input class="form-input" type="date" name="lost_date" id="lost_date" required max="<?= date('Y-m-d') ?>">
              </div>
            </div>

            <!-- Description -->
            <div class="form-group">
              <label class="form-label" for="description">Describe any identifying marks</label>
              <textarea class="form-textarea" name="description" id="description" rows="4" placeholder="Describe specific details that prove this item belongs to you — scratches, stickers, case color, lock screen, etc." required></textarea>
            </div>

            <!-- Submit -->
            <button type="button" class="btn btn-primary btn-block" id="submitClaimBtn" name="submit_claim">
              <i class="fas fa-hand-paper"></i> Submit Claim
            </button>

            <div style="text-align: center; margin-top: 1rem;">
              <a href="../find_item.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Search
              </a>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const submitBtn = document.getElementById('submitClaimBtn');
  const claimForm = document.getElementById('claimForm');

  if (submitBtn && claimForm) {
    submitBtn.addEventListener('click', function() {
      if (!claimForm.checkValidity()) {
        claimForm.reportValidity();
        return;
      }
      showConfirm(
        'Submit Claim',
        'Are you sure you want to claim this item? The item owner will be notified and a discussion will be started.',
        'info',
        'Submit Claim',
        'Cancel'
      ).then(function(confirmed) {
        if (confirmed) {
          // Add the submit_claim field dynamically so the backend detects it
          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = 'submit_claim';
          hidden.value = '1';
          claimForm.appendChild(hidden);
          claimForm.submit();
        }
      });
    });
  }
});
</script>

<?php $cssPath = '../'; include 'footer.php'; ?>
