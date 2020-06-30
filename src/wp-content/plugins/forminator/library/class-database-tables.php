<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Upgrade
 *
 * Handle any installation upgrade or install tasks
 */
class Forminator_Database_Tables {

	/**
	 * Table name keys
	 */
	const FORM_ENTRY 		= 'form_entry';
	const FORM_ENTRY_META 	= 'form_entry_meta';
	const FORM_VIEWS 		= 'form_views';


	/**
     * Current tables
	 *
     */
	private static $tables = array();

	/**
     * Get all the used table names
     *
	 * @since 1.0
     * @return array
     */
    private static function table_names( $db = false ) {
		if ( !$db ) {
			global $wpdb;
			$db = $wpdb;
		}

        return array(
			self::FORM_ENTRY		=> $db->prefix . 'frmt_form_entry',
			self::FORM_ENTRY_META	=> $db->prefix . 'frmt_form_entry_meta',
			self::FORM_VIEWS		=> $db->prefix . 'frmt_form_views',
        );
	}


	/**
     * Get Table Name
     *
	 * @since 1.0
     * @param string $name - the name of the table
     *
     * @return string|bool
     */
    public static function get_table_name( $name ) {
        if ( empty( self::$tables ) ) {
            self::$tables = self::table_names();
        }
        return isset( self::$tables[$name] ) ? self::$tables[$name] : false;
    }

	/**
	 * Set up custom database tables
	 *
	 * @since 1.0
	 */
	public static function install_database_tables() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$wpdb->hide_errors();

		$max_index_length 	= 191;
		$charset_collate 	= $wpdb->get_charset_collate();

		//Form entry
		$table_name = self::get_table_name( self::FORM_ENTRY );
		if ( $table_name ) {
			$sql = "CREATE TABLE {$table_name} (
				`entry_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`entry_type` VARCHAR(191) NOT NULL,
				`form_id` bigint(20) unsigned NOT NULL,
				`is_spam` TINYINT(1) NOT NULL DEFAULT 0,
				`date_created` datetime NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY (`entry_id`),
				KEY `entry_is_spam` (`is_spam` ASC ),
				KEY `entry_type` (`entry_type`($max_index_length)),
				KEY `entry_form_id` (`form_id`))
				$charset_collate;";
			dbDelta( $sql );
		}

		//Form entry meta
		//Each entry is unique to each form
		$table_name = self::get_table_name( self::FORM_ENTRY_META );
		if ( $table_name ) {
			$sql = "CREATE TABLE {$table_name} (
				`meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`entry_id` bigint(20) unsigned NOT NULL,
				`meta_key` VARCHAR(191) default NULL,
				`meta_value` LONGTEXT NULL,
				`date_created` datetime NOT NULL default '0000-00-00 00:00:00',
				`date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY (`meta_id`),
				KEY `meta_key` (`meta_key`($max_index_length)),
				KEY `meta_entry_id` (`entry_id` ASC ),
				KEY `meta_key_object` (`entry_id` ASC, `meta_key` ASC))
				$charset_collate;";
			dbDelta( $sql );
		}

		//Views
		$table_name = self::get_table_name( self::FORM_VIEWS );
		if ( $table_name ) {
			$sql = "CREATE TABLE {$table_name} (
				`view_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`form_id` bigint(20) unsigned NOT NULL,
				`page_id` bigint(20) unsigned NOT NULL,
				`ip` VARCHAR(191) default NULL,
				`count` mediumint(8) unsigned not null default 1,
				`date_created` datetime NOT NULL default '0000-00-00 00:00:00',
				`date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY (`view_id`),
				KEY `view_form_id` (`form_id` ASC ),
				KEY `view_ip` (`ip`($max_index_length)),
				KEY `view_form_object` (`form_id` ASC, `view_id` ASC),
				KEY `view_form_object_ip` (`form_id` ASC, `view_id` ASC, `ip` ASC))
				$charset_collate;";
			dbDelta( $sql );
		}
	}

	/**
	 * Delete custom tables
	 *
	 * @deprecated on 1.1 use forminator_drop_custom_tables on uninstall.php
	 * @since 1.0
	 */
	public static function uninstall_database_tables() {
		_deprecated_function(__METHOD__, '1.1', 'forminator_drop_custom_tables');
		global $wpdb;
		$tables = self::table_names( $wpdb );
		$wpdb->hide_errors();

        foreach ( $tables as $name => $table_name ){
            if ( ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) ) {
	            $wpdb->query( $wpdb->prepare( "DROP TABLE %s" , $table_name) );
            }
        }
	}
}
