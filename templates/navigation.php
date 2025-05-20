<?php
// $current_page variable should be set by the calling script (index.php)
$active_class = 'active-link'; // CSS class for the active link
?>
<nav class="main-navigation">
    <ul>
        <li><a href="index.php?page=about-us" class="<?php echo ($current_page === 'about-us') ? $active_class : ''; ?>">ABOUT US</a></li>
        <li><a href="index.php?page=service-routes" class="<?php echo ($current_page === 'service-routes') ? $active_class : ''; ?>">SERVICE ROUTES</a></li>
        <li><a href="index.php?page=fleet" class="<?php echo ($current_page === 'fleet') ? $active_class : ''; ?>">FLEET</a></li>
        <li><a href="index.php?page=offices" class="<?php echo ($current_page === 'offices') ? $active_class : ''; ?>">OFFICES</a></li>
        <li><a href="index.php?page=career" class="<?php echo ($current_page === 'career') ? $active_class : ''; ?>">CAREER</a></li>
        <li><a href="index.php?page=contact-us" class="<?php echo ($current_page === 'contact-us') ? $active_class : ''; ?>">CONTACT US</a></li>
        <li><a href="index.php?page=news" class="<?php echo ($current_page === 'news') ? $active_class : ''; ?>">NEWS</a></li>
        <li><a href="index.php?page=information" class="<?php echo ($current_page === 'information') ? $active_class : ''; ?>">INFORMATION</a></li>
    </ul>
</nav>