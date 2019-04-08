<aside id="main-sidebar" class="span-6 append-1 last">
	
    <?php $participe = get_theme_option('pagina_participe'); ?>
    
    <?php if ($participe && $participe_link = get_permalink($participe)): ?>
    
        <div class="participation-button"><a href="<?php echo $participe_link; ?>"><?php _e('Participe!', 'consulta'); ?></a></div>
    
    <?php endif; ?>
    
	<?php dynamic_sidebar('Sidebar'); ?>
    
</aside>
