<?php
global $wp_widget_factory;
$layouts = apply_filters( 'siteorigin_panels_prebuilt_layouts', array() );
?>

<div id="panels" data-animations="<?php echo siteorigin_panels_setting('animations') ? 'true' : 'false' ?>">

	<?php do_action('siteorigin_panels_before_interface') ?>

	<div id="panels-container">
	</div>
	
	<div id="add-to-panels">
		<button class="panels-add" data-tooltip="<?php esc_attr_e('Add Widget','siteorigin-panels') ?>"><?php _e('Add Widget', 'siteorigin-panels') ?></button>
		<button class="grid-add" data-tooltip="<?php esc_attr_e('Add Row','siteorigin-panels') ?>"><?php _e('Add Row', 'siteorigin-panels') ?></button>
		<?php if(!empty($layouts)) : ?>
			<button class="prebuilt-set" data-tooltip="<?php esc_attr_e('Prebuilt Layouts','siteorigin-panels') ?>"><?php _e('Prebuilt Layouts', 'siteorigin-panels') ?></button>
		<?php endif; ?>
		<div class="clear"></div>
	</div>
	
	<?php // The add new widget dialog ?>
	
	<div id="panels-dialog" data-title="<?php esc_attr_e('Add New Widget','siteorigin-panels') ?>" class="panels-admin-dialog">
		<div id="panels-dialog-inner">
			<div class="panels-text-filter">
				<input type="search" class="widefat" placeholder="Filter" id="panels-text-filter-input" />
			</div>

			<ul class="panel-type-list">

				<?php foreach($wp_widget_factory->widgets as $class => $widget_obj) : ?>
					<li class="panel-type"
						data-class="<?php echo esc_attr($class) ?>"
						data-title="<?php echo esc_attr($widget_obj->name) ?>"
						>
						<div class="panel-type-wrapper">
							<h3><?php echo esc_html($widget_obj->name) ?></h3>
							<?php if(!empty($widget_obj->widget_options['description'])) : ?>
								<small class="description"><?php echo esc_html($widget_obj->widget_options['description']) ?></small>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>

				<div class="clear"></div>
			</ul>

			<?php do_action('siteorigin_panels_after_widgets'); ?>
		</div>
		
	</div>

	<?php // The add row dialog ?>
	
	<div id="grid-add-dialog" data-title="<?php esc_attr_e('Add Row','siteorigin-panels') ?>" class="panels-admin-dialog">
		<p><label><strong><?php _e('Columns', 'siteorigin-panels') ?></strong></label></p>
		<p><input type="text" id="grid-add-dialog-input" name="column_count" class="small-text" value="3" /></p>
	</div>

	<?php // The prebuilt layouts dialog ?>

	<?php if(!empty($layouts)) : ?>
		<div id="grid-prebuilt-dialog" data-title="<?php esc_attr_e('Insert Prebuilt Page Layout','siteorigin-panels') ?>" class="panels-admin-dialog">
			<p><label><strong><?php _e('Page Layout', 'siteorigin-panels') ?></strong></label></p>
			<p>
				<select type="text" id="grid-prebuilt-input" name="prebuilt_layout" style="width:580px;" placeholder="<?php esc_attr_e('Select Layout', 'siteorigin-panels') ?>" >
					<option class="empty" <?php selected(true) ?> value=""></option>
					<?php foreach($layouts as $id => $data) : ?>
						<option id="panel-prebuilt-<?php echo esc_attr($id) ?>" data-layout-id="<?php echo esc_attr($id) ?>" class="prebuilt-layout">
							<?php echo isset($data['name']) ? $data['name'] : __('Untitled Layout', 'siteorigin-panels') ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
	<?php endif; ?>

	<?php // The styles dialog ?>
	<div id="grid-styles-dialog" data-title="<?php esc_attr_e('Row Visual Style','siteorigin-panels') ?>" class="panels-admin-dialog">
		<?php siteorigin_panels_style_dialog_form() ?>
	</div>

	<?php wp_nonce_field('save', '_sopanels_nonce') ?>
	<?php do_action('siteorigin_panels_metabox_end'); ?>
</div>