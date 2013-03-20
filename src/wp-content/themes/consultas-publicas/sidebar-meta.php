<aside id="main-sidebar" class="span-6 append-1 last">
    <?php if (get_option('users_can_register')) : ?>
	    <div class="participation-button"><a href="<?php echo home_url('wp-login.php?action=register'); ?>"><?php _oi('Participe!', 'Texto do botão Participe'); ?></a></div>
	<?php endif; ?>
	<?php dynamic_sidebar('Meta'); ?>
</aside>
