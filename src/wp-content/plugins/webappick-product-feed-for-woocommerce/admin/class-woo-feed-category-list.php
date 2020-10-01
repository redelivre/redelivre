<?php ob_start();

/**
 * Category List
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed_List_Table
 * @author     Ohidul Islam <wahid@webappick.com>
 */
class Woo_Feed_Category_list extends Woo_Feed_List_Table {
	
	/** ************************************************************************
	 * Normally we would be querying data from a database and manipulating that
	 * for use in your list table. For this example, we're going to simplify it
	 * slightly and create a pre-built array. Think of this as the data that might
	 * be returned by $wpdb->query()
	 *
	 * In a real-world scenario, you would make your own custom query inside
	 * this class' prepare_items() method.
	 *
	 * @var array
	 **************************************************************************/
	
	
	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	function __construct() {
		// Set parent defaults
		parent::__construct(
			array(
				'singular' => __( 'mapping', 'woo-feed' ),     // singular name of the listed records
				'plural'   => __( 'mappings', 'woo-feed' ),    // plural name of the listed records
				'ajax'     => false,        // does this table support ajax?
			)
		);
		
	}
	
	
	/** ************************************************************************
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title()
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as
	 * possible.
	 *
	 * Since we have defined a column_title() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 *
	 * For more detailed insight into how columns are handled, take a look at
	 * WP_List_Table::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 * @param array $column_name The name/slug of the column to be processed
	 *
	 * @return string Text or HTML to be placed inside the column <td>
	 **************************************************************************/
	function column_default( $item, $column_name ) {
		// return $item[$column_name];
		$getItem  = $item['option_name'];
		$itemInfo = maybe_unserialize( get_option( $getItem ) );
		global $plugin_page;
		switch ( $column_name ) {
			case 'option_name':
				return $itemInfo['mappingname'];
			case 'provider':
				return $itemInfo['mappingprovider'];
			case 'view':
				$edit_nonce   = wp_create_nonce( 'wf_edit_mapping' );
				$delete_nonce = wp_create_nonce( 'wf_delete_mapping' );
				
				return sprintf(
					'<a class="button button-primary" href="?page=%s&action=%s&cmapping=%s&_wpnonce=%s">' . __( 'Edit', 'woo-feed' ) . '</a>',
					esc_attr( $plugin_page ),
					'edit-mapping',
					$item['option_name'],
					$edit_nonce
		        ) . '&nbsp;' .
	            sprintf(
			        '<a val="?page=%s&action=%s&cmapping=%s&_wpnonce=%s" class="button single-category-delete" style="cursor: pointer;">' . __( 'Delete', 'woo-feed' ) . '</a>',
			        esc_attr( $plugin_page ),
			        'delete-mapping',
			        absint( $item['option_id'] ),
			        $delete_nonce
				);
			default:
				return false;
		}
	}
	
	
	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 *
	 * @return string Text to be placed inside the column <td> (movie title only)
	 * *************************************************************************@see WP_List_Table::::single_row_columns()
	 */
	function column_option_name( $item ) {
		global $plugin_page;
		$edit_nonce   = wp_create_nonce( 'wf_edit_mapping' );
		$delete_nonce = wp_create_nonce( 'wf_delete_mapping' );
		$actions = array(
			'edit'   => sprintf( '<a href="?page=%s&action=%s&cmapping=%s&_wpnonce=%s">' . __( 'Edit',
					'woo-feed' ) . '</a>',
				esc_attr( $plugin_page ),
				'edit-mapping',
				$item['option_name'],
				$edit_nonce ),
			'delete' => sprintf( '<a val="?page=%s&action=%s&cmapping=%s&_wpnonce=%s" class="single-category-delete" style="cursor: pointer;">' . __( 'Delete',
					'woo-feed' ) . '</a>',
				esc_attr( $plugin_page ),
				'delete-mapping',
				absint( $item['option_id'] ),
				$delete_nonce ),
		);
		
		
		// Return the title contents
		$getItem  = $item['option_name'];
		$itemInfo = maybe_unserialize( get_option( $getItem ) );
		$title    = $itemInfo['mappingname'];
		
		return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			/*$1%s*/
			$title,
			/*$2%s*/
			$item['option_id'],
			/*$3%s*/
			$this->row_actions( $actions )
		);
	}
	
	public static function get_mappings() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s;", 'wf_cmapping_%' ), 'ARRAY_A' );
		
		return $result;
	}
	
	/**
	 * Delete a contact record.
	 *
	 * @param int $id customer ID
	 *
	 * @return false|int
	 */
	public static function delete_mapping( $id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->delete( "{$wpdb->prefix}options", array( 'option_id' => $id ), array( '%d' ) );
	}
	
	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_var( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name like %s", 'woo_cmapping_%' ) );
	}
	
	/** Text displayed when no contact data is available */
	public function no_items() {
		_e( 'No mapping available.', 'woo-feed' );
	}
	
	
	/** ************************************************************************
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 *
	 * @return string Text to be placed inside the column <td> (movie title only)
	 * *************************************************************************@see WP_List_Table::::single_row_columns()
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			$this->_args['singular'],  // Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/
			$item['option_id']                // The value of the checkbox should be the record's id
		);
	}
	
	
	function column_name( $item ) {
		global $plugin_page;
		$edit_nonce   = wp_create_nonce( 'wf_edit_mapping' );
		$delete_nonce = wp_create_nonce( 'wf_delete_mapping' );
		$title        = '<strong>' . $item['option_name'] . '</strong>';
		$actions      = array(
			'edit'   => sprintf( '<a href="?page=%s&action=%s&cmapping=%s&_wpnonce=%s">' . __( 'Edit', 'woo-feed' ) . '</a>',
				esc_attr( $plugin_page ),
				'edit-mapping',
				absint( $item['option_id'] ),
				$edit_nonce ),
			'delete' => sprintf( '<a val="?page=%s&action=%s&cmapping=%s&_wpnonce=%s" class="single-category-delete" style="cursor: pointer;">' . __( 'Delete', 'woo-feed' ) . '</a>',
				esc_attr( $plugin_page ),
				'delete-mapping',
				absint( $item['option_id'] ),
				$delete_nonce ),
		);
		
		return $title . $this->row_actions( $actions );
	}
	
	/** ************************************************************************
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 * *************************************************************************@see WP_List_Table::::single_row_columns()
	 */
	function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />', // Render a checkbox instead of text
			'option_name' => __( 'Category Name', 'woo-feed' ),
			'provider'    => __( 'Category Type', 'woo-feed' ),
			'view'        => __( 'Action', 'woo-feed' ),
		);
		
		return $columns;
	}
	
	
	/** ************************************************************************
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
	 * you will need to register it here. This should return an array where the
	 * key is the column that needs to be sortable, and the value is db column to
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 **************************************************************************/
	function get_sortable_columns() {
		$sortable_columns = array(
			'option_name' => array( 'option_name', false ),
		);
		
		return $sortable_columns;
	}
	
	
	/** ************************************************************************
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 *
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', 'woo-feed' ),
		);
		return $actions;
	}
	
	
	/** ************************************************************************
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 **************************************************************************/
	public function process_bulk_action() {
		$nonce = isset( $_REQUEST['_wpnonce'] ) && ! empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
		// Detect when a bulk action is being triggered...
		if ( 'delete-mapping' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			if ( ! wp_verify_nonce( $nonce, 'wf_delete_mapping' ) ) {
				// die(_e('You do not have sufficient permission to delete!'));
				update_option( 'wpf_message', esc_html__( 'Failed To Delete Mapping. You do not have sufficient permission to delete.', 'woo-feed' ), false );
				wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=error' ) );
				die();
			} else {
				if ( isset( $_GET['cmapping'] ) && self::delete_mapping( absint( $_GET['cmapping'] ) ) ) {
					update_option( 'wpf_message', esc_html__( 'Mapping Deleted Successfully', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=success' ) );
					die();
				} else {
					update_option( 'wpf_message', esc_html__( 'Failed To Delete Mapping', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=error' ) );
					die();
				}
			}
		}
		// Detect when a bulk action is being triggered...
		if ( 'edit-mapping' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			if ( ! wp_verify_nonce( $nonce, 'wf_edit_mapping' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permission to delete!', 'woo-feed' ), 403 );
			}
		}
		
		// If the delete bulk action is triggered
		if ( ( isset( $_POST['mapping'] ) ) && ( isset( $_POST['action'] ) && 'bulk-delete' == $_POST['action'] ) || ( isset( $_POST['action2'] ) && 'bulk-delete' == $_POST['action2'] ) ) {
			if ( 'bulk-delete' === $this->current_action() ) {
				if ( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
					update_option( 'wpf_message', esc_html__( 'Failed To Delete Mapping. You do not have sufficient permission to delete.', 'woo-feed' ), false );
					wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=error' ) );
					die();
				} else {
					$delete_ids = array_map( 'absint', $_POST['mapping'] );
					$delete_ids = array_filter( $delete_ids );
					// loop over the array of record IDs and delete them
					if ( ! empty( $delete_ids ) ) {
						$count = count( $delete_ids );
						foreach ( $delete_ids as $id ) {
							self::delete_mapping( $id );
						}
						$message = sprintf(
						/* translators: 1: number of item deleted. */
							esc_html( _n( '%d Mapping Successfully Deleted.',
								'%d Mappings Successfully Deleted.',
								$count,
								'woo-feed' ) ),
							$count
						);
						update_option( 'wpf_message', $message, false );
						wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=success' ) );
						die();
					}
				}
			}
		}
	}
	
	
	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	function prepare_items() {
		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 10;
		
		
		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		
		
		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );
		
		
		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();
		
		
		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		$data = $this->get_mappings();
		
		usort( $data, 'woo_feed_usort_reorder' );
		
		
		/***********************************************************************
		 * ---------------------------------------------------------------------
		 *
		 * In a real-world situation, this is where you would place your query.
		 *
		 * For information on making queries in WordPress, see this Codex entry:
		 * http://codex.wordpress.org/Class_Reference/wpdb
		 *
		 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
		 * ---------------------------------------------------------------------
		 */
		
		
		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();
		
		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = count( $data );
		
		
		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		
		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				// WE have to calculate the total number of items.
				'per_page'    => $per_page,
				// WE have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $per_page ),
				// WE have to calculate the total number of pages.
			)
		);

// $this->set_pagination_args( array(
// 'total_items' => $total_items,                  //WE have to calculate the total number of items
// 'per_page'    => $per_page                     //WE have to determine how many items to show on a page
// ) );
		
		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;
	}
}
