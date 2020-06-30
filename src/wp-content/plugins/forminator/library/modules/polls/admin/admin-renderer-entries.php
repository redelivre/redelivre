<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Poll_Renderer_Entries
 *
 * @since 1.0.5
 */
class Forminator_Poll_Renderer_Entries extends Forminator_Poll_View_Page {

	/** @noinspection PhpMissingParentConstructorInspection
	 *
	 * Construct Entries Renderer
	 *
	 * @since 1.0.5
	 *
	 * @param string $folder
	 */
	public function __construct( $folder ) {
		$this->folder = $folder;
		$this->register_content_boxes();
		$this->before_render();
		$this->trigger_before_render_action();
		$this->add_page_hooks();
	}

	/**
	 * Render Page Content Only for portability
	 *
	 * @since 1.0.5
	 */
	public function render() {
		$this->render_page_content();
	}
}
