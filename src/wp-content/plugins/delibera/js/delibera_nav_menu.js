jQuery(document).ready(
	function()
	{
		jQuery('[value="delibera-menu-item"]').each(
			function()
			{
				var idf = jQuery(this).attr('id');
				var id = idf.substr(idf.lastIndexOf('-') + 1);
				delibera_custom_nav_menu(id);
			}
		);
		function delibera_custom_nav_menu( id )
		{
			jQuery('#menu-item-'+id+' .item-type').text('Delibera')
			jQuery('#menu-item-'+id+' .field-url').hide();
		}
		function delibera_addMenuItemToBottom_processMethod( menuMarkup, req )
		{
			jQuery(menuMarkup).hideAdvancedMenuItemFields().appendTo( wpNavMenu.targetList );
			var idf = jQuery(menuMarkup).attr('id');
			var id = idf.substr(idf.lastIndexOf('-') + 1);
			delibera_custom_nav_menu(id);
		}
		function delibera_addMenuItemToBottom()
		{
			//var processMethod = wpNavMenu.addMenuItemToBottom;
			var processMethod = delibera_addMenuItemToBottom_processMethod;
			var callback = function(){
			};

			var url = "<?php echo get_post_type_archive_link('pauta') ?>";
			var label = "Delibera";

			wpNavMenu.addItemToMenu({
				'-1': {
					'menu-item-type': 'custom',
					'menu-item-url': url,
					'menu-item-title': label,
					'menu-item-classes': 'delibera-menu-item'
				}
			}, processMethod, callback);
		}
	}
);