<?php
	function delibera_widgets_add_meta_box()
	{
		add_meta_box( 'delibera_widgets-meta-box', __('Delibera'), 'delibera_widgets_nav_menu_item_link_meta_box', 'nav-menus', 'side', 'default' );
	}
	add_action( 'admin_init', 'delibera_widgets_add_meta_box' );
	
	function delibera_widgets_admin_script()
	{
		if(substr($_SERVER[REQUEST_URI], -(strlen('nav-menus.php'))) == 'nav-menus.php')
		{
			wp_enqueue_script('delibera_widgets_admin_script_nav_menus',WP_CONTENT_URL.'/plugins/delibera/js/delibera_nav_menu.js', array('jquery'));
		}
	}
	add_action( 'admin_print_scripts', 'delibera_widgets_admin_script' );
	
	function delibera_widgets_nav_menu_item_link_meta_box()
	{
		?>
	    <div class="custom-meta-box" id="custom-meta-box">
			<button class="button-secondary submit-add-to-menu" onclick="delibera_addMenuItemToBottom();"><?php _e('Adicionar o Delibera ao menu')?></button> 
	    </div>
	    <?php
	}
?>