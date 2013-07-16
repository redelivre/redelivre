<?php
/**
 * Plugin Name: FG Joomla to WordPress
 * Plugin Uri:  http://wordpress.org/extend/plugins/fg-joomla-to-wordpress/
 * Description: A plugin to migrate categories, posts, images and medias from Joomla to WordPress
 * Version:     1.13.0
 * Author:      Frédéric GILLES
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !defined('WP_LOAD_IMPORTERS') )
	return;

require_once 'compatibility.php';

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( !class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) )
		require_once $class_wp_importer;
}

add_action( 'plugins_loaded', 'fgj2wp_load', 20 );

if ( !function_exists( 'fgj2wp_load' ) ) {
	function fgj2wp_load() {
		$fgj2wp = new fgj2wp();
	}
}

if ( !class_exists('fgj2wp', false) ) {
	class fgj2wp extends WP_Importer {
		
		public $plugin_options;				// Plug-in options
		protected $post_type = 'post';		// post or page
		
		/**
		 * Sets up the plugin
		 *
		 */
		public function __construct() {
			$this->plugin_options = array();

			add_action( 'init', array (&$this, 'init') ); // Hook on init
			add_action( 'admin_enqueue_scripts', array (&$this, 'enqueue_scripts') );
		}

		/**
		 * Initialize the plugin
		 */
		public function init() {
			load_plugin_textdomain( 'fgj2wp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
			register_importer('fgj2wp', __('Joomla (FG)', 'fgj2wp'), __('Import categories, articles and medias (images, attachments) from a Joomla database into WordPress.', 'fgj2wp'), array ($this, 'dispatch'));
			
			// Suspend the cache during the migration to avoid exhausted memory problem
			wp_suspend_cache_addition(true);
			wp_suspend_cache_invalidation(true);
		}
		
		/**
		 * Loads Javascripts in the admin
		 */
		public function enqueue_scripts() {
			wp_enqueue_script('jquery');
		}
		
		/**
		 * Display admin notice
		 */
		public function display_admin_notice( $message )	{
			echo '<div class="updated"><p>['.__CLASS__.'] '.$message.'</p></div>';
		}

		/**
		 * Display admin error
		 */
		public function display_admin_error( $message )	{
			echo '<div class="error"><p>['.__CLASS__.'] '.$message.'</p></div>';
		}

		/**
		 * Dispatch the actions
		 */
		public function dispatch() {
			set_time_limit(7200);
			
			// Default values
			$this->plugin_options = array(
				'url'					=> null,
				'version'				=> '1.5',
				'hostname'				=> 'localhost',
				'port'					=> 3306,
				'database'				=> null,
				'username'				=> 'root',
				'password'				=> '',
				'prefix'				=> 'jos_',
				'introtext_in_excerpt'	=> 1,
				'skip_media'			=> 0,
				'import_featured'		=> 1,
				'import_external'		=> 0,
				'force_media_import'	=> 0,
				'meta_keywords_in_tags'	=> 0,
				'import_as_pages'		=> 0,
			);
			$options = get_option('fgj2wp_options');
			if ( is_array($options) ) {
				$this->plugin_options = array_merge($this->plugin_options, $options);
			}
			
			if ( isset($_POST['empty']) ) {

				// Delete content
				if ( check_admin_referer( 'empty', 'fgj2wp_nonce' ) ) { // Security check
					if ($this->empty_database($_POST['empty_action'])) { // Empty WP database
						$this->display_admin_notice(__('WordPress content removed', 'fgj2wp'));
					} else {
						$this->display_admin_error(__('Couldn\'t remove content', 'fgj2wp'));
					}
					wp_cache_flush();
				}
			}
			
			elseif ( isset($_POST['save']) ) {
				
				// Save database options
				$this->save_plugin_options();
				$this->display_admin_notice(__('Settings saved', 'fgj2wp'));
			}
			
			elseif ( isset($_POST['test']) ) {
				
				// Save database options
				$this->save_plugin_options();
				
				// Test the database connection
				if ( check_admin_referer( 'parameters_form', 'fgj2wp_nonce' ) ) { // Security check
					$this->test_database_connection();
				}
			}
			
			elseif ( isset($_POST['import']) ) {
				
				// Save database options
				$this->save_plugin_options();
				
				// Import content
				if ( check_admin_referer( 'parameters_form', 'fgj2wp_nonce' ) ) { // Security check
					$this->import();
				}
			}
			
			elseif ( isset($_POST['remove_cat_prefix']) ) {

				// Remove the prefixes from the categories
				if ( check_admin_referer( 'remove_cat_prefix', 'fgj2wp_nonce' ) ) { // Security check
					$result = $this->remove_category_prefix();
					$this->display_admin_notice(__('Prefixes removed from categories', 'fgj2wp'));
				}
			}
			
			elseif ( isset($_POST['modify_links']) ) {

				// Modify internal links
				if ( check_admin_referer( 'modify_links', 'fgj2wp_nonce' ) ) { // Security check
					$result = $this->modify_links();
					$this->display_admin_notice(sprintf(_n('%d internal link modified', '%d internal links modified', $result['links_count'], 'fgj2wp'), $result['links_count']));
				}
			}
			
			$this->admin_build_page(); // Display the form
		}
		
		/**
		 * Build the option page
		 * 
		 */
		private function admin_build_page() {
			$posts_count = wp_count_posts('post');
			$pages_count = wp_count_posts('page');
			$media_count = wp_count_posts('attachment');
			$cat_count = count(get_categories(array('hide_empty' => 0)));
			$tags_count = count(get_tags(array('hide_empty' => 0)));
			
			$data = $this->plugin_options;
			
			$data['title'] = __('Import Joomla (FG)', 'fgj2wp');
			$data['description'] = __('This plugin will import sections, categories, posts and medias (images, attachments) from a Joomla database into WordPress.<br />Compatible with Joomla versions 1.5, 1.6, 1.7, 2.5 and 3.0.', 'fgj2wp');
			$data['posts_count'] = $posts_count->publish + $posts_count->draft + $posts_count->future + $posts_count->pending;
			$data['pages_count'] = $pages_count->publish + $pages_count->draft + $pages_count->future + $pages_count->pending;
			$data['media_count'] = $media_count->inherit;
			$data['database_info'] = array(
				sprintf(_n('%d category', '%d categories', $cat_count, 'fgj2wp'), $cat_count),
				sprintf(_n('%d post', '%d posts', $data['posts_count'], 'fgj2wp'), $data['posts_count']),
				sprintf(_n('%d page', '%d pages', $data['pages_count'], 'fgj2wp'), $data['pages_count']),
				sprintf(_n('%d media', '%d medias', $data['media_count'], 'fgj2wp'), $data['media_count']),
				sprintf(_n('%d tag', '%d tags', $tags_count, 'fgj2wp'), $tags_count),
			);
			
			// Hook for modifying the admin page
			$data = apply_filters('fgj2wp_pre_display_admin_page', $data);
			
			include('admin_build_page.tpl.php');
			
			// Hook for doing other actions after displaying the admin page
			do_action('fgj2wp_post_display_admin_page');
			
		}

		/**
		 * Delete all posts, medias and categories from the database
		 *
		 * @param string $action:	newposts = removes only new imported posts
		 * 							all = removes all
		 * @return boolean
		 */
		private function empty_database($action) {
			global $wpdb;
			$result = true;
			
			$wpdb->show_errors();
			$sql_queries = array();
			
			if ( $action == 'all' ) {
				// Remove all content
				$start_id = 1;
				update_option('fgj2wp_start_id', $start_id);
				
				$sql_queries[] = "TRUNCATE $wpdb->commentmeta";
				$sql_queries[] = "TRUNCATE $wpdb->comments";
				$sql_queries[] = "TRUNCATE $wpdb->term_relationships";
				$sql_queries[] = "TRUNCATE $wpdb->postmeta";
				$sql_queries[] = "TRUNCATE $wpdb->posts";
				$sql_queries[] = <<<SQL
-- Delete Terms
DELETE FROM $wpdb->terms
WHERE term_id > 1 -- non-classe
SQL;
				$sql_queries[] = <<<SQL
-- Delete Terms taxonomies
DELETE FROM $wpdb->term_taxonomy
WHERE term_id > 1 -- non-classe
SQL;
				$sql_queries[] = "ALTER TABLE $wpdb->terms AUTO_INCREMENT = 2";
				$sql_queries[] = "ALTER TABLE $wpdb->term_taxonomy AUTO_INCREMENT = 2";
			} else {
				// Remove only new imported posts
				// WordPress post ID to start the deletion
				$start_id = intval(get_option('fgj2wp_start_id'));
				if ( $start_id == 0) {
					$start_id = $this->get_next_post_autoincrement();
					update_option('fgj2wp_start_id', $start_id);
					
					$sql_queries[] = <<<SQL
-- Delete Comments meta
DELETE FROM $wpdb->commentmeta
WHERE comment_id IN
	(
	SELECT comment_ID FROM $wpdb->comments
	WHERE comment_post_ID IN
		(
		SELECT ID FROM $wpdb->posts
		WHERE (post_type IN ('post', 'page', 'attachment', 'revision')
		OR post_status = 'trash'
		OR post_title = 'Brouillon auto')
		AND ID >= $start_id
		)
	);
SQL;

					$sql_queries[] = <<<SQL
-- Delete Comments
DELETE FROM $wpdb->comments
WHERE comment_post_ID IN
	(
	SELECT ID FROM $wpdb->posts
	WHERE (post_type IN ('post', 'page', 'attachment', 'revision')
	OR post_status = 'trash'
	OR post_title = 'Brouillon auto')
	AND ID >= $start_id
	);
SQL;

					$sql_queries[] = <<<SQL
-- Delete Term relashionships
DELETE FROM $wpdb->term_relationships
WHERE `object_id` IN
	(
	SELECT ID FROM $wpdb->posts
	WHERE (post_type IN ('post', 'page', 'attachment', 'revision')
	OR post_status = 'trash'
	OR post_title = 'Brouillon auto')
	AND ID >= $start_id
	);
SQL;

					$sql_queries[] = <<<SQL
-- Delete Post meta
DELETE FROM $wpdb->postmeta
WHERE post_id IN
	(
	SELECT ID FROM $wpdb->posts
	WHERE (post_type IN ('post', 'page', 'attachment', 'revision')
	OR post_status = 'trash'
	OR post_title = 'Brouillon auto')
	AND ID >= $start_id
	);
SQL;

					$sql_queries[] = <<<SQL
-- Delete Posts
DELETE FROM $wpdb->posts
WHERE (post_type IN ('post', 'page', 'attachment', 'revision')
OR post_status = 'trash'
OR post_title = 'Brouillon auto')
AND ID >= $start_id;
SQL;
				}
			}
			
			// Execute SQL queries
			if ( count($sql_queries) > 0 ) {
				foreach ( $sql_queries as $sql ) {
					$result &= $wpdb->query($sql);
				}
			}
			
			// Hook for doing other actions after emptying the database
			do_action('fgj2wp_post_empty_database', $action);
			
			// Reset the Joomla last imported post ID
			update_option('fgj2wp_last_joomla_id', 0);
			
			// Re-count categories and tags items
			$this->terms_count();
			
			// Update cache
			$this->clean_cache();
			
			$this->optimize_database();
			
			$wpdb->hide_errors();
			return ($result !== false);
		}

		/**
		 * Optimize the database
		 *
		 */
		protected function optimize_database() {
			global $wpdb;
			
			$sql = <<<SQL
OPTIMIZE TABLE 
`$wpdb->commentmeta` ,
`$wpdb->comments` ,
`$wpdb->options` ,
`$wpdb->postmeta` ,
`$wpdb->posts` ,
`$wpdb->terms` ,
`$wpdb->term_relationships` ,
`$wpdb->term_taxonomy`
SQL;
			$wpdb->query($sql);
		}
		
		/**
		 * Test the database connection
		 * 
		 * @return boolean
		 */
		function test_database_connection() {
			try {
				$db = new PDO('mysql:host=' . $this->plugin_options['hostname'] . ';port=' . $this->plugin_options['port'] . ';dbname=' . $this->plugin_options['database'], $this->plugin_options['username'], $this->plugin_options['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
				
				$prefix = $this->plugin_options['prefix'];
				
				// Test that the "content" table exists
				$result = $db->query("DESC ${prefix}content");
				if ( !is_a($result, 'PDOStatement') ) {
					$errorInfo = $db->errorInfo();
					throw new PDOException($errorInfo[2], $errorInfo[1]);
				}
				
				$this->display_admin_notice(__('Connected with success to the Joomla database', 'fgj2wp'));
				return true;
				
			} catch ( PDOException $e ) {
				$this->display_admin_error(__('Couldn\'t connect to the Joomla database. Please check your parameters. And be sure the WordPress server can access the Joomla database.', 'fgj2wp') . '<br />' . $e->getMessage());
				return false;
			}
		}
		
		/**
		 * Save the plugin options
		 *
		 */
		private function save_plugin_options() {
			$this->plugin_options = array_merge($this->plugin_options, $this->validate_form_info());
			update_option('fgj2wp_options', $this->plugin_options);
			
			// Hook for doing other actions after saving the options
			do_action('fgj2wp_post_save_plugin_options');
		}
		
		/**
		 * Validate POST info
		 *
		 * @return array Form parameters
		 */
		private function validate_form_info() {
			return array(
				'url'					=> $_POST['url'],
				'version'				=> $_POST['version'],
				'hostname'				=> $_POST['hostname'],
				'port'					=> intval($_POST['port']),
				'database'				=> $_POST['database'],
				'username'				=> $_POST['username'],
				'password'				=> $_POST['password'],
				'prefix'				=> $_POST['prefix'],
				'introtext_in_excerpt'	=> !empty($_POST['introtext_in_excerpt']),
				'skip_media'			=> !empty($_POST['skip_media']),
				'import_featured'		=> !empty($_POST['import_featured']),
				'import_external'		=> !empty($_POST['import_external']),
				'force_media_import'	=> !empty($_POST['force_media_import']),
				'meta_keywords_in_tags'	=> !empty($_POST['meta_keywords_in_tags']),
				'import_as_pages'		=> !empty($_POST['import_as_pages']),
			);
		}
		
		/**
		 * Import
		 *
		 */
		private function import() {
			
			// Check prerequesites before the import
			$do_import = apply_filters('fgj2wp_pre_import_check', true);
			if ( !$do_import) return;
			
			$this->post_type = ($this->plugin_options['import_as_pages'] == 1) ? 'page' : 'post';

			// Hook for doing other actions before the import
			do_action('fgj2wp_pre_import');
			
			// Categories
			if ($this->post_type == 'post') {
				$cat_count = $this->import_categories();
				$this->display_admin_notice(sprintf(_n('%d category imported', '%d categories imported', $cat_count, 'fgj2wp'), $cat_count));
			}
			
			// Posts and medias
			$result = $this->import_posts();
			switch ($this->post_type) {
				case 'page':
					$this->display_admin_notice(sprintf(_n('%d page imported', '%d pages imported', $result['posts_count'], 'fgj2wp'), $result['posts_count']));
					break;
				case 'post':
				default:
					$this->display_admin_notice(sprintf(_n('%d post imported', '%d posts imported', $result['posts_count'], 'fgj2wp'), $result['posts_count']));
			}
			$this->display_admin_notice(sprintf(_n('%d media imported', '%d medias imported', $result['media_count'], 'fgj2wp'), $result['media_count']));
			
			// Tags
			if ($this->post_type == 'post') {
				if ( $this->plugin_options['meta_keywords_in_tags'] ) {
					$this->display_admin_notice(sprintf(_n('%d tag imported', '%d tags imported', $result['tags_count'], 'fgj2wp'), $result['tags_count']));
				}
			}
			
			// Hook for doing other actions after the import
			do_action('fgj2wp_post_import');
			
			// Hook for other notices
			do_action('fgj2wp_import_notices');
			
			$this->display_admin_notice(__("Don't forget to modify internal links.", 'fgj2wp'));
			
			wp_cache_flush();
		}

		/**
		 * Import categories
		 *
		 * @return int Number of categories imported
		 */
		private function import_categories() {
			$cat_count = 0;
			if ( version_compare($this->plugin_options['version'], '1.5', '<=') ) {
				$sections = $this->get_sections(); // Get the Joomla sections
			} else {
				$sections = array();
			}
			$categories = $this->get_categories(); // Get the Joomla categories
			$categories = array_merge($sections, $categories);
			if ( is_array($categories) ) {
				$terms = array('1'); // unclassified category
				foreach ( $categories as $category ) {
					
					if ( get_category_by_slug($category['name']) ) {
						continue; // Do not import already imported category
					}
					
					// Insert the category
					$new_category = array(
						'cat_name' 				=> $category['title'],
						'category_description'	=> $category['description'],
						'category_nicename'		=> $category['name'], // slug
					);
					if ( $cat_id = wp_insert_category($new_category) ) {
						$cat_count++;
						$terms[] = $cat_id;
					}
				}
				
				// Update the categories with their parent ids
				// We need to do it in a second step because the children categories
				// may have been imported before their parent
				foreach ( $categories as $category ) {
					$cat = get_category_by_slug($category['name']);
					if ( $cat ) {
						// Parent category
						if ( !empty($category['parent']) ) {
							$parent_cat = get_category_by_slug($category['parent']);
							if ( $parent_cat ) {
								wp_update_term($cat->term_id, 'category', array(
									'parent' => $parent_cat->term_id
								));
							}
						}
					}
				}
				
				// Update cache
				wp_update_term_count_now($terms, 'category');
				$this->clean_cache($terms);
			}
			return $cat_count;
		}
		
		/**
		 * Clean the cache
		 * 
		 */
		private function clean_cache($terms = array()) {
			delete_option("category_children");
			clean_term_cache($terms, 'category');
		}

		/**
		 * Import posts
		 *
		 * @return array:
		 * 		int posts_count: Number of posts imported
		 * 		int media_count: Number of medias imported
		 */
		private function import_posts() {
			$posts_count = 0;
			$media_count = 0;
			$imported_tags = array();
			$step = 1000; // to limit the results
			
			$tab_categories = $this->tab_categories(); // Get the categories list
			
			// Hook for doing other actions before the import
			do_action('fgj2wp_pre_import_posts');
			
			do {
				$posts = $this->get_posts($step); // Get the Joomla posts
				
				if ( is_array($posts) ) {
					foreach ( $posts as $post ) {
						
						// Hook for modifying the Joomla post before processing
						$post = apply_filters('fgj2wp_pre_process_post', $post);
						
						// Medias
						if ( !$this->plugin_options['skip_media'] ) {
							// Extra featured image
							$featured_image = '';
							list($featured_image, $post) = apply_filters('fgj2wp_pre_import_media', array($featured_image, $post));
							// Import media
							$result = $this->import_media($featured_image . $post['introtext'] . $post['fulltext'], $post['date']);
							$post_media = $result['media'];
							$media_count += $result['media_count'];
						} else {
							// Skip media
							$post_media = array();
						}
						
						// Categories IDs
						$categories = array($post['category']);
						// Hook for modifying the post categories
						$categories = apply_filters('fgj2wp_post_categories', $categories, $post);
						$categories_ids = array();
						foreach ( $categories as $category_name ) {
							$category = sanitize_title($category_name);
							if ( array_key_exists($category, $tab_categories) ) {
								$categories_ids[] = $tab_categories[$category];
							}
						}
						if ( count($categories_ids) == 0 ) {
							$categories_ids[] = 1; // default category
						}
						
						// Define excerpt and post content
						if ( empty($post['fulltext']) ) {
							// Posts without a "Read more" link
							$excerpt = '';
							$content = $post['introtext'];
						} else {
							// Posts with a "Read more" link
							if ( $this->plugin_options['introtext_in_excerpt'] ) {
								// Introtext imported in excerpt
								$excerpt = $post['introtext'];
								$content = $post['fulltext'];
							} else {
								// Introtext imported in post content with a "Read more" tag
								$excerpt = '';
								$content = $post['introtext'] . "\n<!--more-->\n" . $post['fulltext'];
							}
						}
						
						// Process content
						$excerpt = $this->process_content($excerpt, $post_media);
						$content = $this->process_content($content, $post_media);
						
						// Status
						$status = ($post['state'] == 1)? 'publish' : 'draft';
						
						// Tags
						$tags = array();
						if ( $this->plugin_options['meta_keywords_in_tags'] && !empty($post['metakey']) ) {
							$tags = explode(',', $post['metakey']);
							$imported_tags = array_merge($imported_tags, $tags);
						}
						
						// Insert the post
						$new_post = array(
							'post_category'		=> $categories_ids,
							'post_content'		=> $content,
							'post_date'			=> $post['date'],
							'post_excerpt'		=> $excerpt,
							'post_status'		=> $status,
							'post_title'		=> $post['title'],
							'post_name'			=> $post['alias'],
							'post_type'			=> $this->post_type,
							'tags_input'		=> $tags,
							'menu_order'        => $post['ordering'],
						);
						
						// Hook for modifying the WordPress post just before the insert
						$new_post = apply_filters('fgj2wp_pre_insert_post', $new_post, $post);
						
						$new_post_id = wp_insert_post($new_post);
						if ( $new_post_id ) { 
							// Add links between the post and its medias
							$this->add_post_media($new_post_id, $new_post, $post_media, $this->plugin_options['import_featured']);
							
							// Add the Joomla ID as a post meta in order to modify links after
							add_post_meta($new_post_id, '_fgj2wp_old_id', $post['id'], true);
							
							// Increment the Joomla last imported post ID
							update_option('fgj2wp_last_joomla_id', $post['id']);

							$posts_count++;
							
							// Hook for doing other actions after inserting the post
							do_action('fgj2wp_post_insert_post', $new_post_id, $post);
						}
					}
				}
			} while ( ($posts != null) && (count($posts) > 0) );
			
			// Hook for doing other actions after the import
			do_action('fgj2wp_post_import_posts');
			
			return array(
				'posts_count'	=> $posts_count,
				'media_count'	=> $media_count,
				'tags_count'	=> count(array_unique($imported_tags)),
			);
		}
		
		/**
		 * Get Joomla sections
		 *
		 * @return array of Sections
		 */
		private function get_sections() {
			$sections = array();

			try {
				$db = new PDO('mysql:host=' . $this->plugin_options['hostname'] . ';port=' . $this->plugin_options['port'] . ';dbname=' . $this->plugin_options['database'], $this->plugin_options['username'], $this->plugin_options['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
				$prefix = $this->plugin_options['prefix'];
				$sql = "
					SELECT s.title, CONCAT('s', s.id, '-', IF(s.alias <> '', s.alias, s.name)) AS name, s.description
					FROM ${prefix}sections s
				";
				$sql = apply_filters('fgj2wp_get_sections_sql', $sql, $prefix);
				
				$query = $db->query($sql);
				if ( is_object($query) ) {
					foreach ( $query as $row ) {
						$sections[] = $row;
					}
				}
				$db = null;
				
				$sections = apply_filters('fgj2wp_get_sections', $sections);
				
			} catch ( PDOException $e ) {
				$this->display_admin_error(__('Error:', 'fgj2wp') . $e->getMessage());
			}
			return $sections;		
		}
		
		/**
		 * Get Joomla categories
		 *
		 * @return array of Categories
		 */
		private function get_categories() {
			$categories = array();

			try {
				$db = new PDO('mysql:host=' . $this->plugin_options['hostname'] . ';port=' . $this->plugin_options['port'] . ';dbname=' . $this->plugin_options['database'], $this->plugin_options['username'], $this->plugin_options['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
				$prefix = $this->plugin_options['prefix'];
				switch ( $this->plugin_options['version'] ) {
					case '1.5':
						$sql = "
							SELECT c.title, CONCAT('c', c.id, '-', IF(c.alias <> '', c.alias, c.name)) AS name, c.description, CONCAT('s', s.id, '-', IF(s.alias <> '', s.alias, s.name)) AS parent
							FROM ${prefix}categories c
							INNER JOIN ${prefix}sections AS s ON s.id = c.section
						";
						break;
					
					case '1.6':
					case '2.5':
					default:
						$sql = "
							SELECT c.title, CONCAT('c', c.id, '-', c.alias) AS name, c.description, CONCAT('c', cp.id, '-', cp.alias) AS parent
							FROM ${prefix}categories c
							INNER JOIN ${prefix}categories AS cp ON cp.id = c.parent_id
							WHERE c.extension = 'com_content'
							ORDER BY c.lft
						";
						break;
				}
				$sql = apply_filters('fgj2wp_get_categories_sql', $sql, $prefix);
				
				$query = $db->query($sql);
				if ( is_object($query) ) {
					foreach ( $query as $row ) {
						$categories[] = $row;
					}
				}
				$db = null;
				
				$categories = apply_filters('fgj2wp_get_categories', $categories);
				
			} catch ( PDOException $e ) {
				$this->display_admin_error(__('Error:', 'fgj2wp') . $e->getMessage());
			}
			return $categories;		
		}
		
		/**
		 * Get Joomla posts
		 *
		 * @param int limit Number of posts max
		 * @return array of Posts
		 */
		protected function get_posts($limit=1000) {
			$posts = array();
			
			$last_joomla_id = (int)get_option('fgj2wp_last_joomla_id'); // to restore the import where it left

			try {
				$db = new PDO('mysql:host=' . $this->plugin_options['hostname'] . ';port=' . $this->plugin_options['port'] . ';dbname=' . $this->plugin_options['database'], $this->plugin_options['username'], $this->plugin_options['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
				
				$prefix = $this->plugin_options['prefix'];
				
				// The "name" column disappears in version 1.6+
				if ( version_compare($this->plugin_options['version'], '1.5', '<=') ) {
					$cat_field = "IF(c.alias <> '', c.alias, c.name)";
				} else {
					$cat_field = 'c.alias';
				}
				
				// Hooks for adding extra cols and extra joins
				$extra_cols = apply_filters('fgj2wp_get_posts_add_extra_cols', '');
				$extra_joins = apply_filters('fgj2wp_get_posts_add_extra_joins', '');
				
				$sql = "
					SELECT p.id, p.title, p.alias, p.introtext, p.fulltext, p.state, CONCAT('c', c.id, '-', $cat_field) AS category, p.modified, p.created AS `date`, p.metakey, p.metadesc, p.ordering
					$extra_cols
					FROM ${prefix}content p
					LEFT JOIN ${prefix}categories AS c ON p.catid = c.id
					$extra_joins
					WHERE p.state >= 0 -- don't get the trash
					AND p.id > '$last_joomla_id'
					ORDER BY p.id
					LIMIT $limit
				";
				$sql = apply_filters('fgj2wp_get_posts_sql', $sql, $prefix, $cat_field, $extra_cols, $extra_joins, $last_joomla_id, $limit);
				
				$query = $db->query($sql);
				if ( is_object($query) ) {
					foreach ( $query as $row ) {
						$posts[] = $row;
					}
				}
				$db = null;
			} catch ( PDOException $e ) {
				$this->display_admin_error(__('Error:', 'fgj2wp') . $e->getMessage());
			}
			return $posts;		
		}
		
		/**
		 * Return an array with all the categories sorted by name
		 *
		 * @return array categoryname => id
		 */
		public function tab_categories() {
			$tab_categories = array();
			$categories = get_categories(array('hide_empty' => '0'));
			if ( is_array($categories) ) {
				foreach ( $categories as $category ) {
					$tab_categories[$category->slug] = $category->term_id;
				}
			}
			return $tab_categories;
		}
		
		/**
		 * Import post medias
		 *
		 * @param string $content post content
		 * @param date $post_date Post date (for storing media)
		 * @return array:
		 * 		array media: Medias imported
		 * 		int media_count:   Medias count
		 */
		public function import_media($content, $post_date) {
			$media = array();
			$media_count = 0;
			
			if ( preg_match_all('#<(img|a)(.*?)(src|href)="(.*?)"(.*?)>#', $content, $matches, PREG_SET_ORDER) > 0 ) {
				if ( is_array($matches) ) {
					foreach ($matches as $match ) {
						$filename = $match[4];
						$filename = str_replace("%20", " ", $filename); // for filenames with spaces
						$other_attributes = $match[2] . $match[5];
						
						$filetype = wp_check_filetype($filename);
						if ( empty($filetype['type']) || ($filetype['type'] == 'text/html') ) { // Unrecognized file type
							continue;
						}
						
						// Upload the file from the Joomla web site to WordPress upload dir
						if ( preg_match('/^http/', $filename) ) {
							if ( preg_match('#^' . $this->plugin_options['url'] . '#', $filename) // Local file
								|| ($this->plugin_options['import_external'] == 1) ) { // External file 
								$old_filename = $filename;
							} else {
								continue;
							}
						} else {
							$old_filename = untrailingslashit($this->plugin_options['url']) . '/' . $filename;
						}
						$old_filename = str_replace(" ", "%20", $old_filename); // for filenames with spaces
						$date = strftime('%Y/%m', strtotime($post_date));
						$uploads = wp_upload_dir($date);
						$new_upload_dir = $uploads['path'];
						
						$new_filename = $new_upload_dir . '/' . basename($filename);
						
						// print "Copy \"$old_filename\" => $new_filename<br />";
						if ( ! @$this->remote_copy($old_filename, $new_filename) ) {
							$error = error_get_last();
							$error_message = $error['message'];
							$this->display_admin_error("Can't copy $old_filename to $new_filename : $error_message");
							continue;
						}

						$post_name = preg_replace('/\.[^.]+$/', '', basename($filename));
						
						// If the attachment does not exist yet, insert it in the database
						$attachment = $this->get_attachment_from_name($post_name);
						if ( !$attachment ) {
							$attachment_data = array(
								'post_mime_type'	=> $filetype['type'],
								'post_name'			=> $post_name,
								'post_title'		=> $post_name,
								'post_status'		=> 'inherit'
							);
							$attach_id = wp_insert_attachment($attachment_data, $new_filename);
							$attachment = get_post($attach_id);
							$post_name = $attachment->post_name; // Get the real post name
							$media_count++;
						}
						$attach_id = $attachment->ID;
						
						$media[$filename] = array(
							'id'	=> $attach_id,
							'name'	=> $post_name,
						);
						
						if ( preg_match('/image/', $filetype['type']) ) { // Images
							// you must first include the image.php file
							// for the function wp_generate_attachment_metadata() to work
							require_once(ABSPATH . 'wp-admin/includes/image.php');
							$attach_data = wp_generate_attachment_metadata( $attach_id, $new_filename );
							wp_update_attachment_metadata( $attach_id, $attach_data );

							// Image Alt
							if (preg_match('#alt="(.*?)"#', $other_attributes, $alt_matches) ) {
								$image_alt = wp_strip_all_tags(stripslashes($alt_matches[1]), true);
								update_post_meta($attach_id, '_wp_attachment_image_alt', addslashes($image_alt)); // update_meta expects slashed
							}
						}
					}
				}
			}
			return array(
				'media'			=> $media,
				'media_count'	=> $media_count
			);
		}

		/**
		 * Check if the attachment exists in the database
		 *
		 * @param string $name
		 * @return object Post
		 */
		private function get_attachment_from_name($name) {
			$name = preg_replace('/\.[^.]+$/', '', basename($name));
			$r = array(
				'name'			=> $name,
				'post_type'		=> 'attachment',
				'numberposts'	=> 1,
			);
			$posts_array = get_posts($r);
			if ( is_array($posts_array) && (count($posts_array) > 0) ) {
				return $posts_array[0];
			}
			else {
				return false;
			}
		}
		
		/**
		 * Process the post content
		 *
		 * @param string $content Post content
		 * @param array $post_media Post medias
		 * @return string Processed post content
		 */
		public function process_content($content, $post_media) {
			
			if ( !empty($content) ) {
				// Replace media URLs with the new URLs
				$content = $this->process_content_media_links($content, $post_media);
			}

			return $content;
		}

		/**
		 * Replace media URLs with the new URLs
		 *
		 * @param string $content Post content
		 * @param array $post_media Post medias
		 * @return string Processed post content
		 */
		private function process_content_media_links($content, $post_media) {
			if ( is_array($post_media) ) {
				foreach ( $post_media as $old_filename => $media ) {
					$post_media_name = $media['name'];
					$attachment = $this->get_attachment_from_name($post_media_name);
					if ( $attachment ) {
						if ( preg_match('/image/', $attachment->post_mime_type) ) {
							// Image
							$image_src = wp_get_attachment_image_src($attachment->ID, 'full');
							$url = $image_src[0];
						} else {
							// Other media
							$url = wp_get_attachment_url($attachment->ID);
						}
						$url = str_replace(" ", "%20", $url); // for filenames with spaces
						$content = str_replace($old_filename, $url, $content);
						$old_filename = str_replace(" ", "%20", $old_filename); // for filenames with spaces
						$content = str_replace($old_filename, $url, $content);
					}
				}
			}
			return $content;
		}

		/**
		 * Add a link between a media and a post (parent id + thumbnail)
		 *
		 * @param int $post_id Post ID
		 * @param array $post_data Post data
		 * @param array $post_media Post medias
		 * @param boolean $set_featured_image Set the featured image?
		 */
		public function add_post_media($post_id, $post_data, $post_media, $set_featured_image=true) {
			$thumbnail_is_set = false;
			if ( is_array($post_media) ) {
				foreach ( $post_media as $old_filename => $media ) {
					$post_media_name = $media['name'];
					$attachment = $this->get_attachment_from_name($post_media_name);
					$attachment->post_parent = $post_id; // Attach the post to the media
					$attachment->post_date = $post_data['post_date'] ;// Define the media's date
					wp_update_post($attachment);

					// Set the featured image. If not defined, it is the first image of the content.
					if ( $set_featured_image && !$thumbnail_is_set ) {
						set_post_thumbnail($post_id, $attachment->ID);
						$thumbnail_is_set = true;
					}
				}
			}
		}

		/**
		 * Modify the internal links of all posts
		 *
		 * @return array:
		 * 		int links_count: Links count
		 */
		private function modify_links() {
			$links_count = 0;
			$step = 1000; // to limit the results
			$offset = 0;
			
			$this->post_type = ($this->plugin_options['import_as_pages'] == 1) ? 'page' : 'post';
			
			do {
				$args = array(
					'numberposts'	=> $step,
					'offset'		=> $offset,
					'orderby'		=> 'ID',
					'order'			=> 'ASC',
					'post_type'		=> $this->post_type,
				);
				$posts = get_posts($args);
				foreach ( $posts as $post ) {
					$content = $post->post_content;
					if ( preg_match_all('#<a(.*?)href="(.*?)"(.*?)>#', $content, $matches, PREG_SET_ORDER) > 0 ) {
						if ( is_array($matches) ) {
							foreach ($matches as $match ) {
								$link = $match[2];
								// Is it an internal link ?
								if ( $this->is_internal_link($link) ) {
									$meta_key_value = $this->get_joomla_id_in_link($link);
									// Can we find an ID in the link ?
									if ( $meta_key_value['meta_value'] != 0 ) {
										// Get the linked post
										$linked_posts = get_posts(array(
											'numberposts'	=> 1,
											'post_type'		=> $this->post_type,
											'meta_key'		=> $meta_key_value['meta_key'],
											'meta_value'	=> $meta_key_value['meta_value'],
										));
										if ( count($linked_posts) > 0 ) {
											$new_link = get_permalink($linked_posts[0]->ID);
											$content = str_replace("href=\"$link\"", "href=\"$new_link\"", $content);
											// Update the post
											wp_update_post(array(
												'ID'			=> $post->ID,
												'post_content'	=> $content,
											));
											$links_count++;
										}
										unset($linked_posts);
									}
								}
							}
						}
					}
				}
				$offset += $step;
			} while ( ($posts != null) && (count($posts) > 0) );
			
			return array('links_count' => $links_count);
		}

		/**
		 * Test if the link is an internal link or not
		 *
		 * @param string $link
		 * @return bool
		 */
		private function is_internal_link($link) {
			$result = (preg_match("#^".$this->plugin_options['url']."#", $link) > 0) ||
				(preg_match("#^http#", $link) == 0);
			return $result;
		}

		/**
		 * Get the Joomla ID in a link
		 *
		 * @param string $link
		 * @return array('meta_key' => $meta_key, 'meta_value' => $meta_value)
		 */
		private function get_joomla_id_in_link($link) {
			$meta_key_value = array(
				'meta_key'		=> '',
				'meta_value'	=> 0);
			$meta_key_value = apply_filters('fgj2wp_pre_get_joomla_id_in_link', $meta_key_value, $link);
			if ($meta_key_value['meta_value'] == 0) {
				$meta_key_value['meta_key'] = '_fgj2wp_old_id';
				// Without URL rewriting
				if ( preg_match("#id=(\d+)#", $link, $matches) ) {
					$meta_key_value['meta_value'] = $matches[1];
				}
				// With URL rewriting
				elseif ( preg_match("#(.*)/(\d+)-(.*)#", $link, $matches) ) {
					$meta_key_value['meta_value'] = $matches[2];
				} else {
					$meta_key_value = apply_filters('fgj2wp_post_get_joomla_id_in_link', $meta_key_value);
				}
			}
			return $meta_key_value;
		}
		
		/**
		 * Copy a remote file
		 * in replacement of the copy function
		 * 
		 * @param string $url URL of the source file
		 * @param string $path destination file
		 * @return boolean
		 */
		private function remote_copy($url, $path) {
			
			/*
			 * cwg enhancement: if destination already exists, just return true
			 *  this allows rebuilding the wp media db without moving files
			 */
			if ( !$this->plugin_options['force_media_import'] && file_exists($path) && (filesize($path) > 0) ) {
				return true;
			}
			
			$response = wp_remote_get($url); // Uses WordPress HTTP API
			
			if ( is_wp_error($response) ) {
				trigger_error($response->get_error_message(), E_USER_WARNING);
				return false;
			} elseif ( $response['response']['code'] != 200 ) {
				trigger_error($response['response']['message'], E_USER_WARNING);
				return false;
			} else {
				file_put_contents($path, wp_remote_retrieve_body($response));
				return true;
			}
		}
		
		/**
		 * Recount the items for a taxonomy
		 * 
		 * @return boolean
		 */
		private function terms_tax_count($taxonomy) {
			$terms = get_terms(array($taxonomy));
			// Get the term taxonomies
			$terms_taxonomies = array();
			foreach ( $terms as $term ) {
				$terms_taxonomies[] = $term->term_taxonomy_id;
			}
			if ( !empty($terms_taxonomies) ) {
				return wp_update_term_count_now($terms_taxonomies, $taxonomy);
			} else {
				return true;
			}
		}
		
		/**
		 * Recount the items for each category and tag
		 * 
		 * @return boolean
		 */
		private function terms_count() {
			$result = $this->terms_tax_count('category');
			$result |= $this->terms_tax_count('post_tag');
		}
		
		/**
		 * Get the next post autoincrement
		 * 
		 * @return int post ID
		 */
		private function get_next_post_autoincrement() {
			global $wpdb;
			
			$sql = "SHOW TABLE STATUS LIKE '$wpdb->posts'";
			$row = $wpdb->get_row($sql);
			if ( $row ) {
				return $row->Auto_increment;
			} else {
				return 0;
			}
		}
		
		/**
		 * Remove the prefixes categories
		 */
		private function remove_category_prefix() {
			$categories = get_terms( 'category', array('hide_empty' => 0) );
			if ( !empty($categories) ) {
				foreach ( $categories as $cat ) {
					if ( preg_match('/^(s|ck?)\d+-(.*)/', $cat->slug, $matches) ) {
						wp_update_term($cat->term_id, 'category', array(
							'slug' => $matches[2]
						));
					}
				}
			}
		}
	}
}
?>
