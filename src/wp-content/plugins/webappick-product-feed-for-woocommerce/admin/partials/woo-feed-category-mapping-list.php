<?php
/**
 * Category Mapping List View
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/admin/partial
 * @author     Ohidul Islam <wahid@webappick.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}
global $plugin_page;
$myListTable = new Woo_Feed_Category_list();
$myListTable->prepare_items();
?>
<div class="wrap">
	<h2><?php esc_html_e( 'Category Mapping List', 'woo-feed' ); ?><a href="<?php echo esc_url( admin_url( 'admin.php?page=webappick-feed-category-mapping&action=add-mapping' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New Mapping', 'woo-feed' ); ?></a></h2>
	<?php WPFFWMessage()->displayMessages(); ?>
	<form id="contact-filter" method="post">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>">
		<!-- Now we can render the completed list table -->
		<?php $myListTable->display(); ?>
	</form>
</div>
<script type="text/javascript">
	(function ($) {
		"use strict";
		$(document).ready(function () {
			$('body').find(".single-category-delete").click(function () {
				if (confirm('<?php esc_html_e( 'Are You Sure to Delete?', 'woo-feed' ); ?>')) {
					window.location.href = jQuery(this).attr('val');
				}
			});

			$('#doaction').click(function () {
				return confirm('<?php esc_html_e( 'Are You Sure to Delete?', 'woo-feed' ); ?>');
			});

			$('#doaction2').click(function () {
				return confirm('<?php esc_html_e( 'Are You Sure to Delete?', 'woo-feed' ); ?>');
			});
		});
	})(jQuery);
</script>
