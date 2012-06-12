<?php if (is_home()) :?>
    <?php dynamic_sidebar('Home Sidebar'); ?>
<?php else: ?>
    <?php dynamic_sidebar('Sidebar'); ?>
<?php endif; ?>
