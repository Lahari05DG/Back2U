<?php
/**
 * footer.php — Shared footer for all authenticated pages.
 *
 * Expects $cssPath and $basePath to be set by the including page
 * (same variables used by header.php).
 */

$cssPath  = $cssPath  ?? '';
$basePath = $basePath ?? '';
?>

<!-- ===== Footer ===== -->
<footer class="site-footer">
    <div class="footer-container">

        <!-- Column 1: About -->
        <div class="footer-col">
            <h3 class="footer-heading">
                <i class="fas fa-map-marker-alt"></i> Bck2U
            </h3>
            <p class="footer-text">
                Bck2U is a student-driven lost and found platform designed to help
                college students recover their lost belongings quickly and securely.
            </p>
        </div>

        <!-- Column 2: Quick Links -->
        <div class="footer-col">
            <h3 class="footer-heading">Quick Links</h3>
            <ul class="footer-links">
                <li>
                    <a href="<?php echo htmlspecialchars($basePath); ?>dashboard.php">
                        <i class="fas fa-chevron-right"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($basePath); ?>upload_item.php">
                        <i class="fas fa-chevron-right"></i> Upload Item
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($basePath); ?>find_item.php">
                        <i class="fas fa-chevron-right"></i> Find Items
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($basePath); ?>profile.php">
                        <i class="fas fa-chevron-right"></i> My Profile
                    </a>
                </li>
            </ul>
        </div>

        <!-- Column 3: Need Help? -->
        <div class="footer-col">
            <h3 class="footer-heading">Need Help?</h3>
            <p class="footer-text">
                Lost something on campus? Upload the details and let the community
                help you find it.
            </p>
            <a href="mailto:support@bck2u.com" class="footer-email">
                <i class="fas fa-envelope"></i> support@bck2u.com
            </a>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
        <p>&copy; 2026 Bck2U &mdash; Made with &#10084;&#65039; for students</p>
    </div>
</footer>

<!-- ===== Scripts ===== -->
<script src="<?php echo htmlspecialchars($cssPath); ?>js/app.js"></script>
</body>
</html>
