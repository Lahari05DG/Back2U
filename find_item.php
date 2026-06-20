<?php
session_start();
include 'php/db.php';
if(!isset($_SESSION['user_id'])){ header('Location: index.php'); exit(); }

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$query = "SELECT * FROM items WHERE 1=1";
if($search != '') $query .= " AND (category LIKE '%$search%' OR location LIKE '%$search%' OR description LIKE '%$search%')";
if($category != '') $query .= " AND category='$category'";
if($status != '') $query .= " AND status='$status'";
else $query .= " AND status='discussion'";
$query .= " ORDER BY id DESC";
$result = mysqli_query($conn, $query);

$pageTitle = 'Find Items';
$currentPage = 'find';
include 'php/header.php';
?>

<div class="content-wrapper">
  <div class="container">

    <!-- Page Header -->
    <div class="page-header animate-fadeIn">
      <h1 class="section-title">Find Lost Items</h1>
      <p class="section-subtitle">Search through items found on campus</p>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="animate-slideUp">
      <div class="filter-bar">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search by keyword, location, category..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <select class="filter-select" name="category">
          <option value="">All Categories</option>
          <option value="ID Card" <?= $category == 'ID Card' ? 'selected' : '' ?>>ID Card</option>
          <option value="Mobile" <?= $category == 'Mobile' ? 'selected' : '' ?>>Mobile</option>
          <option value="Bag" <?= $category == 'Bag' ? 'selected' : '' ?>>Bag</option>
          <option value="Bottle" <?= $category == 'Bottle' ? 'selected' : '' ?>>Bottle</option>
          <option value="Keys" <?= $category == 'Keys' ? 'selected' : '' ?>>Keys</option>
          <option value="Books" <?= $category == 'Books' ? 'selected' : '' ?>>Books</option>
          <option value="Glasses" <?= $category == 'Glasses' ? 'selected' : '' ?>>Glasses</option>
          <option value="Charger" <?= $category == 'Charger' ? 'selected' : '' ?>>Charger</option>
          <option value="Others" <?= $category == 'Others' ? 'selected' : '' ?>>Others</option>
        </select>
        <select class="filter-select" name="status">
          <option value="discussion" <?= $status == 'discussion' || $status == '' ? 'selected' : '' ?>>Available</option>
          <option value="resolved" <?= $status == 'resolved' ? 'selected' : '' ?>>Resolved</option>
        </select>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-filter"></i> Filter
        </button>
      </div>
    </form>

    <!-- Results Grid -->
    <?php if(mysqli_num_rows($result) > 0): ?>
      <div class="items-grid mt-3 animate-slideUp">
        <?php while($item = mysqli_fetch_assoc($result)):
          $claimCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM claims WHERE item_id='".$item['id']."'"))['c'];
        ?>
          <div class="item-card animate-fadeIn">
            <div class="item-card-header">
              <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['category']) ?>">
              <div class="item-card-badge">
                <span class="status-badge <?= $item['status'] == 'resolved' ? 'resolved' : 'available' ?>">
                  <?= $item['status'] == 'resolved' ? 'Resolved' : 'Available' ?>
                </span>
              </div>
            </div>
            <div class="item-card-body">
              <span class="item-card-category"><?= htmlspecialchars($item['category']) ?></span>
              <p class="item-card-desc"><?= htmlspecialchars(mb_strimwidth($item['description'], 0, 80, '...')) ?></p>
              <div class="item-card-meta">
                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['location']) ?></span>
                <span><i class="fas fa-clock"></i> <?= date('M d, Y', strtotime($item['created_at'])) ?></span>
                <span><i class="fas fa-users"></i> <?= $claimCount ?> claim<?= $claimCount != 1 ? 's' : '' ?></span>
              </div>
            </div>
            <div class="item-card-footer">
              <?php
                $userId = $_SESSION['user_id'];
                $isOwner = ($item['user_id'] == $userId);
                $userClaimed = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM claims WHERE item_id='".$item['id']."' AND claimer_id='$userId'"));
              ?>
              <?php if($item['status'] == 'resolved'): ?>
                <span class="status-badge resolved">
                  <i class="fas fa-check-circle"></i> Resolved
                </span>
              <?php elseif($isOwner): ?>
                <a href="chat.php?item_id=<?= $item['id'] ?>" class="btn btn-primary btn-sm">
                  <i class="fas fa-comments"></i> Open Chat
                </a>
              <?php elseif($userClaimed > 0): ?>
                <a href="chat.php?item_id=<?= $item['id'] ?>" class="btn btn-success btn-sm">
                  <i class="fas fa-comments"></i> Go to Discussion
                </a>
                <span class="status-badge discussion" style="margin-left:auto;">
                  <i class="fas fa-check"></i> Already Claimed
                </span>
              <?php elseif($claimCount > 0): ?>
                <a href="php/claim_form.php?item_id=<?= $item['id'] ?>" class="btn btn-outline btn-sm">
                  <i class="fas fa-hand-paper"></i> Claim Too
                </a>
                <span class="status-badge discussion" style="margin-left:auto;">
                  <i class="fas fa-user-check"></i> <?= $claimCount ?> already claimed
                </span>
              <?php else: ?>
                <a href="php/claim_form.php?item_id=<?= $item['id'] ?>" class="btn btn-primary btn-sm">
                  <i class="fas fa-hand-paper"></i> Claim Item
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="empty-state mt-3 animate-fadeIn">
        <i class="fas fa-search"></i>
        <h3>No Items Found</h3>
        <p>No items found matching your search. Try adjusting your filters.</p>
        <a href="find_item.php" class="btn btn-outline">
          <i class="fas fa-redo"></i> Clear Filters
        </a>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'php/footer.php'; ?>