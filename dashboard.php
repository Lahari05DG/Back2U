<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: index.php'); exit(); }
include 'php/db.php';
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
include 'php/header.php';

// Get stats
$userId = $_SESSION['user_id'];
$totalItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM items WHERE user_id='$userId'"))['c'];
$claimedItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT claims.item_id) as c FROM claims JOIN items ON claims.item_id=items.id WHERE items.user_id='$userId'"))['c'];
$resolvedItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM items WHERE user_id='$userId' AND status='resolved'"))['c'];
$activeDiscussions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM items WHERE user_id='$userId' AND status='discussion'"))['c'];

// Get user name
$userName = $_SESSION['user_name'] ?? 'User';
?>

<div class="content-wrapper">
  <div class="container">

    <!-- Welcome Header -->
    <div class="page-header animate-fadeIn">
      <h1 class="section-title">Welcome back, <?= htmlspecialchars($userName) ?> 👋</h1>
      <p class="section-subtitle">Here's what's happening with your items</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid animate-slideUp">
      <div class="stat-card">
        <div class="stat-icon primary">
          <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value" data-count="<?= $totalItems ?>">0</span>
          <span class="stat-label">Total Items</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon warning">
          <i class="fas fa-hand-paper"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value" data-count="<?= $claimedItems ?>">0</span>
          <span class="stat-label">Claimed Items</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon success">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value" data-count="<?= $resolvedItems ?>">0</span>
          <span class="stat-label">Resolved</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon danger">
          <i class="fas fa-comments"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value" data-count="<?= $activeDiscussions ?>">0</span>
          <span class="stat-label">Active Discussions</span>
        </div>
      </div>
    </div>

    <!-- My Uploaded Items -->
    <div class="mt-4 animate-slideUp">
      <h2 class="section-title">My Uploaded Items</h2>
      <p class="section-subtitle">Items you've found and uploaded</p>

      <?php
      $itemsQuery = mysqli_query($conn, "SELECT items.*, (SELECT COUNT(*) FROM claims WHERE claims.item_id=items.id) as claim_count FROM items WHERE user_id='$userId' ORDER BY id DESC");
      ?>

      <?php if(mysqli_num_rows($itemsQuery) > 0): ?>
        <div class="items-grid mt-2">
          <?php while($item = mysqli_fetch_assoc($itemsQuery)): ?>
            <div class="item-card animate-fadeIn">
              <div class="item-card-header">
                <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['category']) ?>">
                <div class="item-card-badge">
                  <span class="status-badge <?= $item['status'] == 'resolved' ? 'resolved' : 'discussion' ?>">
                    <?= $item['status'] == 'resolved' ? 'Resolved' : 'In Discussion' ?>
                  </span>
                </div>
              </div>
              <div class="item-card-body">
                <span class="item-card-category"><?= htmlspecialchars($item['category']) ?></span>
                <p class="item-card-desc"><?= htmlspecialchars(mb_strimwidth($item['description'], 0, 80, '...')) ?></p>
                <div class="item-card-meta">
                  <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['location']) ?></span>
                  <span><i class="fas fa-clock"></i> <?= date('M d, Y', strtotime($item['created_at'])) ?></span>
                  <span><i class="fas fa-users"></i> <?= $item['claim_count'] ?> claim<?= $item['claim_count'] != 1 ? 's' : '' ?></span>
                </div>
              </div>
              <div class="item-card-footer">
                <?php if($item['status'] == 'discussion'): ?>
                  <a href="chat.php?item_id=<?= $item['id'] ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-comments"></i> Open Chat
                  </a>
                  <button class="btn btn-warning btn-sm" onclick="resolveItem(<?= $item['id'] ?>)">
                    <i class="fas fa-check"></i> Resolve
                  </button>
                <?php else: ?>
                  <span class="status-badge resolved">
                    <i class="fas fa-check-circle"></i> Resolved
                  </span>
                <?php endif; ?>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="empty-state mt-2">
          <i class="fas fa-box-open"></i>
          <h3>No Items Uploaded Yet</h3>
          <p>Found something on campus? Upload it to help someone find their lost belongings.</p>
          <a href="upload_item.php" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Item
          </a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Items I Claimed -->
    <div class="mt-4 animate-slideUp">
      <h2 class="section-title">Items I Claimed</h2>
      <p class="section-subtitle">Items you've submitted a claim for</p>

      <?php
      $claimedQuery = mysqli_query($conn, "SELECT items.* FROM claims JOIN items ON claims.item_id=items.id WHERE claims.claimer_id='$userId' ORDER BY items.id DESC");
      ?>

      <?php if(mysqli_num_rows($claimedQuery) > 0): ?>
        <div class="items-grid mt-2">
          <?php while($item = mysqli_fetch_assoc($claimedQuery)): ?>
            <div class="item-card animate-fadeIn">
              <div class="item-card-header">
                <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['category']) ?>">
                <div class="item-card-badge">
                  <span class="status-badge <?= $item['status'] == 'resolved' ? 'resolved' : 'discussion' ?>">
                    <?= $item['status'] == 'resolved' ? 'Resolved' : 'In Discussion' ?>
                  </span>
                </div>
              </div>
              <div class="item-card-body">
                <span class="item-card-category"><?= htmlspecialchars($item['category']) ?></span>
                <p class="item-card-desc"><?= htmlspecialchars(mb_strimwidth($item['description'], 0, 80, '...')) ?></p>
                <div class="item-card-meta">
                  <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['location']) ?></span>
                  <span><i class="fas fa-clock"></i> <?= date('M d, Y', strtotime($item['created_at'])) ?></span>
                </div>
              </div>
              <div class="item-card-footer">
                <a href="chat.php?item_id=<?= $item['id'] ?>" class="btn btn-primary btn-sm">
                  <i class="fas fa-comments"></i> Open Discussion
                </a>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="empty-state mt-2">
          <i class="fas fa-search"></i>
          <h3>No Claims Yet</h3>
          <p>Lost something? Browse found items and submit a claim.</p>
          <a href="find_item.php" class="btn btn-primary">
            <i class="fas fa-search"></i> Find Items
          </a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
// Animate stat counters on page load
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.stat-value[data-count]').forEach(function(el) {
    const target = parseInt(el.getAttribute('data-count'));
    animateCounter(el, target, 1000);
  });

  // Check URL params for flash messages
  const msg = getUrlParam('msg');
  if (msg === 'resolved') {
    showToast('Item has been marked as resolved!', 'success');
  } else if (msg === 'uploaded') {
    showToast('Item uploaded successfully!', 'success');
  }
});

// Resolve item with confirmation
function resolveItem(itemId) {
  showConfirm(
    'Resolve Item',
    'Are you sure you want to mark this item as resolved? This means the owner has been found.',
    'success',
    'Yes, Resolve',
    'Cancel'
  ).then(function(confirmed) {
    if (confirmed) {
      window.location.href = 'resolve_item.php?item_id=' + itemId;
    }
  });
}
</script>

<?php include 'php/footer.php'; ?>