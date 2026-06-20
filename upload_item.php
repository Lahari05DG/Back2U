<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: index.php'); exit(); }
include 'php/db.php';

// Handle upload POST
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['item_image'])){
    $user_id = $_SESSION['user_id'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    
    $image_name = $_FILES['item_image']['name'];
    $tmp_name = $_FILES['item_image']['tmp_name'];
    $upload_folder = 'uploads/';
    $new_name = time() . '_' . $image_name;
    $image_path = $upload_folder . $new_name;
    
    // Check duplicate
    $check = mysqli_query($conn, "SELECT * FROM items WHERE category='$category' AND description='$description' AND location='$location'");
    
    if(mysqli_num_rows($check) > 0){
        header('Location: upload_item.php?msg=duplicate');
        exit();
    }
    
    if(move_uploaded_file($tmp_name, $image_path)){
        $query = "INSERT INTO items (user_id,image,category,description,location,status) VALUES ('$user_id','$new_name','$category','$description','$location','discussion')";
        mysqli_query($conn, $query);
        header('Location: dashboard.php?msg=uploaded');
        exit();
    } else {
        header('Location: upload_item.php?msg=failed');
        exit();
    }
}

$pageTitle = 'Upload Item';
$currentPage = 'upload';
include 'php/header.php';
?>

<div class="content-wrapper">
  <div class="container" style="max-width: 700px;">

    <!-- Page Header -->
    <div class="page-header animate-fadeIn">
      <h1 class="section-title">Upload Found Item</h1>
      <p class="section-subtitle">Help someone find their lost belongings by uploading details of the item you found</p>
    </div>

    <!-- Upload Form Card -->
    <div class="card animate-slideUp">
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="uploadForm">

          <!-- Upload Zone -->
          <div class="form-group">
            <label class="form-label">Item Photo</label>
            <div class="upload-zone" id="uploadZone">
              <i class="fas fa-cloud-upload-alt"></i>
              <p>Drag &amp; drop an image here or click to browse</p>
              <p class="text-muted">Supports JPG, PNG, WEBP (Max 5MB)</p>
            </div>
            <input type="file" id="imageInput" name="item_image" accept="image/*" style="display:none;" required>
            <div class="upload-preview" id="uploadPreview" style="display:none;">
              <img id="previewImg" src="" alt="Preview">
              <button type="button" class="btn btn-danger btn-sm" id="removeImage" style="margin-top: 0.5rem;">
                <i class="fas fa-trash"></i> Remove
              </button>
            </div>
          </div>

          <!-- Category -->
          <div class="form-group">
            <label class="form-label" for="category">Category</label>
            <select class="form-select" name="category" id="category" required>
              <option value="" disabled selected>Select a category</option>
              <option value="ID Card">ID Card</option>
              <option value="Mobile">Mobile</option>
              <option value="Bag">Bag</option>
              <option value="Bottle">Bottle</option>
              <option value="Keys">Keys</option>
              <option value="Books">Books</option>
              <option value="Glasses">Glasses</option>
              <option value="Charger">Charger</option>
              <option value="Others">Others</option>
            </select>
          </div>

          <!-- Description -->
          <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea class="form-textarea" name="description" id="description" rows="4" placeholder="Describe the item — color, brand, any identifying marks..." required></textarea>
          </div>

          <!-- Location -->
          <div class="form-group">
            <label class="form-label" for="location">Location Found</label>
            <div class="form-input-icon">
              <i class="fas fa-map-marker-alt"></i>
              <input class="form-input" type="text" name="location" id="location" placeholder="e.g., Library 2nd Floor, Room 301..." required>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="button" class="btn btn-primary btn-block" id="submitBtn">
            <i class="fas fa-upload"></i> Upload Item
          </button>

        </form>
      </div>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const uploadZone = document.getElementById('uploadZone');
  const imageInput = document.getElementById('imageInput');
  const uploadPreview = document.getElementById('uploadPreview');
  const previewImg = document.getElementById('previewImg');
  const removeImage = document.getElementById('removeImage');
  const uploadForm = document.getElementById('uploadForm');
  const submitBtn = document.getElementById('submitBtn');

  // Click to upload
  uploadZone.addEventListener('click', function() {
    imageInput.click();
  });

  // Drag & drop
  uploadZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadZone.style.borderColor = 'var(--primary)';
    uploadZone.style.background = 'rgba(99, 102, 241, 0.05)';
  });

  uploadZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadZone.style.borderColor = '';
    uploadZone.style.background = '';
  });

  uploadZone.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadZone.style.borderColor = '';
    uploadZone.style.background = '';
    if (e.dataTransfer.files.length) {
      imageInput.files = e.dataTransfer.files;
      showPreview(e.dataTransfer.files[0]);
    }
  });

  // File input change
  imageInput.addEventListener('change', function() {
    if (imageInput.files.length) {
      showPreview(imageInput.files[0]);
    }
  });

  // Show image preview
  function showPreview(file) {
    if (!file.type.startsWith('image/')) {
      showToast('Please select an image file', 'error');
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      showToast('Image must be less than 5MB', 'error');
      return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
      previewImg.src = e.target.result;
      uploadZone.style.display = 'none';
      uploadPreview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  }

  // Remove image
  removeImage.addEventListener('click', function() {
    imageInput.value = '';
    previewImg.src = '';
    uploadPreview.style.display = 'none';
    uploadZone.style.display = '';
  });

  // Submit with confirmation
  submitBtn.addEventListener('click', function() {
    if (!uploadForm.checkValidity()) {
      uploadForm.reportValidity();
      return;
    }
    showConfirm(
      'Upload Item',
      'Are you sure you want to upload this found item? It will be visible to all users.',
      'info',
      'Upload',
      'Cancel'
    ).then(function(confirmed) {
      if (confirmed) {
        uploadForm.submit();
      }
    });
  });

  // Flash messages from URL
  const msg = getUrlParam('msg');
  if (msg === 'duplicate') {
    showToast('This item appears to already exist in the system.', 'error');
  } else if (msg === 'failed') {
    showToast('Failed to upload image. Please try again.', 'error');
  }
});
</script>

<?php include 'php/footer.php'; ?>