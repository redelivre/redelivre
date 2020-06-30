<?php

/**
 * Interface Forminator_Addon_Interface
 *
 * @since 1.1
 */
interface Forminator_Addon_Interface {
	const SHORT_TITLE_MAX_LENGTH = 10;

	/**
	 * Get current instance
	 *
	 * @since 1.1
	 * @return self
	 */
	public static function get_instance();

	/**
	 * Action to execute on activation
	 *
	 * @since 1.1
	 * @return bool
	 */
	public function activate();

	/**
	 * Action to execute on de-activation
	 *
	 * @since 1.1
	 * @return bool
	 */
	public function deactivate();
}
