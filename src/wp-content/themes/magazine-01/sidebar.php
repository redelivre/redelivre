<aside id="main-sidebar" class="col-4 clearfix">
	<?php if (!dynamic_sidebar('Sidebar')): ?>

		<?php if (current_user_can('publish_posts')): ?>
			<h3>Widgets</h3>
			<div class="empty-feature">
				<p>Para exibir widgets aqui acesse o <a href="<?php echo admin_url('widgets.php'); ?>">painel de administração</a> e arraste widgets para o box "Sidebar".</p>
			</div>
			
			
		<?php endif; ?>
	
	<?php endif; ?>
</aside>
<!-- #main-sidebar -->

