<?php
/**
 * header.php — Shared navigation header for all authenticated pages.
 *
 * Variables to set BEFORE including this file:
 *   $pageTitle   — (optional) Page title shown in <title> tag
 *   $currentPage — (required) One of: 'dashboard','upload','find','chat','claim'
 *   $cssPath     — (optional) Relative path prefix to reach the project root (default '')
 *   $basePath    — (optional) Relative path prefix for page links (default '')
 *
 * Example usage from a root-level page (dashboard.php):
 *   $pageTitle   = 'Dashboard';
 *   $currentPage = 'dashboard';
 *   $cssPath     = '';
 *   $basePath    = '';
 *   include 'php/header.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . ($basePath ?? '') . 'index.php');
    exit();
}

$cssPath  = $cssPath  ?? '';
$basePath = $basePath ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Bck2U' : 'Bck2U — Lost &amp; Found'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPath); ?>css/style.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPath); ?>css/dark-mode.css">
</head>
<body>

<!-- ===== Page Loader ===== -->
<div class="page-loader" id="pageLoader">
    <div class="loader-spinner"></div>
</div>

<!-- ===== Navigation Bar ===== -->
<nav class="navbar" id="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <a href="<?php echo htmlspecialchars($basePath); ?>dashboard.php" class="nav-logo">
            <i class="fas fa-map-marker-alt"></i>
            <span>Bck2U</span>
        </a>

        <!-- Nav Links -->
        <ul class="nav-links" id="navLinks">
            <li>
                <a href="<?php echo htmlspecialchars($basePath); ?>dashboard.php"
                   class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?php echo htmlspecialchars($basePath); ?>upload_item.php"
                   class="nav-link <?php echo ($currentPage === 'upload') ? 'active' : ''; ?>">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Upload Item</span>
                </a>
            </li>
            <li>
                <a href="<?php echo htmlspecialchars($basePath); ?>find_item.php"
                   class="nav-link <?php echo ($currentPage === 'find') ? 'active' : ''; ?>">
                    <i class="fas fa-search"></i>
                    <span>Find Items</span>
                </a>
            </li>
        </ul>

        <!-- Right-side controls -->
        <div class="nav-actions">

            <!-- Notification Bell -->
            <div class="nav-notification" id="notificationBell">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge">0</span>
            </div>

            <!-- Dark Mode Toggle -->
            <div class="dark-mode-toggle">
                <input type="checkbox" id="darkModeSwitch" class="dark-mode-checkbox">
                <label for="darkModeSwitch" class="dark-mode-label" title="Toggle dark mode">
                    <i class="fas fa-sun"></i>
                    <i class="fas fa-moon"></i>
                    <span class="dark-mode-ball"></span>
                </label>
            </div>

            <!-- User Greeting -->
            <span class="nav-user-name">
                Hi, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
            </span>

            <!-- Logout -->
            <a href="<?php echo htmlspecialchars($basePath); ?>logout.php" class="nav-link nav-logout" title="Log out">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>

            <!-- Mobile Hamburger -->
            <button class="nav-hamburger" id="navHamburger" aria-label="Toggle navigation menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Hamburger toggle script (inline so it works immediately) -->
<script>
(function () {
    var hamburger = document.getElementById('navHamburger');
    var navLinks  = document.getElementById('navLinks');
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function () {
            navLinks.classList.toggle('show');
            // Swap icon between bars and times
            var icon = hamburger.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            }
        });

        // Close menu when a link is clicked (mobile UX)
        navLinks.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                navLinks.classList.remove('show');
                var icon = hamburger.querySelector('i');
                if (icon) {
                    icon.classList.add('fa-bars');
                    icon.classList.remove('fa-times');
                }
            });
        });
    }
})();
</script>
