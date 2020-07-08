<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Form Entry model
 *
 * @since 1.0
 */
class Forminator_Entries_List_Table extends WP_List_Table {

	/**
	 * The current form model
	 *
	 * @var object
	 */
	protected $model = null;

	/**
	 * The visible header fields
	 *
	 * @var array
	 */
	protected $visible_fields = array();

	/**
	 * Total items to display
	 *
	 * @var int
	 */
	protected $total_items = 0;

	/**
	 * Forminator_Entries_List_Table constructor.
	 *
	 * @since 1.0
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		if ( isset( $args['model'] ) ) {
			$this->model = $args['model'];
			unset( $args['model'] );
		}
		if ( isset( $args['visible_fields'] ) ) {
			$this->visible_fields = $args['visible_fields'];
			unset( $args['visible_fields'] );
		}
		parent::__construct(
			array_merge(
				array(
					'plural'     => '',
					'autoescape' => false,
					'screen'     => 'forminator-entries',
				),
				$args
			)
		);
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.0
	 */
	public function no_items() {
		esc_html_e( 'No entries found.', Forminator::DOMAIN );
	}

	/**
	 * Table columns
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'   => '<input type="checkbox" />',
			'date' => esc_html__( 'Date added', Forminator::DOMAIN ),
		);

		if ( ! empty( $this->visible_fields ) && ! in_array( 'date', $this->visible_fields, true ) ) {
			unset( $columns['date'] );
		}

		if ( is_object( $this->model ) ) {
			$fields = $this->model->get_fields();
			if ( ! is_null( $fields ) ) {
				foreach ( $fields as $field ) {
					$label = $field->__get( 'field_label' );
					if ( ! $label ) {
						$label = $field->title;
					}
					$slug = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
					if ( ! empty( $this->visible_fields ) ) {
						if ( in_array( $slug, $this->visible_fields, true ) ) {
							$columns[ $slug ] = $label;
						}
					} else {
						$columns[ $slug ] = $label;
					}
				}
			}
		}

		return $columns;
	}

	/**
	 * Prepare items for display
	 *
	 * @since 1.0
	 */
	public function prepare_items() {
		$paged    = $this->get_pagenum();
		$per_page = 10;
		$offset   = ( $paged - 1 ) * $per_page;
		$form_id  = 0;
		if ( is_object( $this->model ) ) {
			$form_id = $this->model->id;
		}

		$this->total_items = Forminator_Form_Entry_Model::count_entries( $form_id );

		$this->set_pagination_args(
			array(
				'total_items' => $this->total_items,
				'total_pages' => ceil( $this->total_items / $per_page ),
				'per_page'    => $per_page,
			)
		);

		$this->items           = Forminator_Form_Entry_Model::list_entries( $form_id, $per_page, $offset );
		$this->_column_headers = array( $this->get_columns(), array(), array() );
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since 1.0
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) ) { // WPCS: CSRF OK
			$current_orderby = sanitize_text_field( $_GET['orderby'] );
		} else {
			$current_orderby = '';
		}

		if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) { // phpcs:ignore
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb']     = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				. '<div class="wpmudev-checkbox"><input id="cb-select-all-' . $cb_counter . '" type="checkbox" /><label for="cb-select-all-' . $cb_counter . '" class="wpdui-icon wpdui-icon-check"></label></div>';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( "column-$column_key" );

			if ( in_array( $column_key, $hidden, true ) ) {
				$class[] = 'hidden';
			}

			if ( 'cb' === $column_key ) {
				$class[] = 'wpmudev-head-check check-column';
			}

			if ( $column_key === $primary ) {
				$class[] = 'column-primary';
			}

			if ( isset( $sortable[ $column_key ] ) ) {
				list( $orderby, $desc_first ) = $sortable[ $column_key ];

				if ( $current_orderby === $orderby ) {
					$order   = 'asc' === $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order   = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$tag   = 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id    = $with_id ? "id='$column_key'" : '';

			if ( ! empty( $class ) ) {
				$class = "class='" . join( ' ', $class ) . "'";
			}

			echo "<$tag $scope $id $class>$column_display_name</$tag>"; // phpcs:ignore
		}
	}


	/**
	 * Display the table
	 *
	 * @since 1.0
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
		<table class="wpmudev-list-table" cellspacing="0" cellpadding="0">
			<thead class="wpmudev-table-head">
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>

			<tbody class="wpmudev-table-body">
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Handles the checkbox column output.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param Forminator_Form_Entry_Model $item
	 */
	public function column_cb( $item ) {
		?>
		<div class="wpmudev-checkbox">
			<input type="checkbox" id="wpf-cform-check_entry_<?php echo esc_attr( $item->entry_id ); ?>" name="entry[]" value="<?php echo esc_attr( $item->entry_id ); ?>">
			<label for="wpf-cform-check_entry_<?php echo esc_attr( $item->entry_id ); ?>" class=""></label>
		</div>
		<?php
	}

	/**
	 * Handles the date column output.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param Forminator_Form_Entry_Model $item
	 */
	public function column_date( $item ) {
		?>
		<p class="wpmudev-cell-content"><?php echo esc_attr( $item->date_created ); ?></p>
		<?php
	}

	/**
	 * Dynamic column support
	 *
	 * @since 1.0
	 * @param Forminator_Form_Entry_Model $item - the current item
	 * @param string $column_name - the column name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		$data = $item->get_meta( $column_name, '' );
		if ( $data ) {
			if ( is_array( $data ) ) {
				$output       = '';
				$product_cost = 0;
				$is_product   = false;
				foreach ( $data as $key => $value ) {
					if ( is_array( $value ) ) {
						if ( 'file' === $key && isset( $value['file_url'] ) ) {
							$file_name = basename( $value['file_url'] );
							$file_name = "<a href='" . $value['file_url'] . "' target='_blank' rel='noreferrer' title='" . __( 'View File', Forminator::DOMAIN ) . "'>$file_name</a> ,";
							$output   .= $file_name;
						}
					} else {
						if ( ! is_int( $key ) ) {
							if ( 'postdata' === $key ) {
								$url     = get_edit_post_link( $value );
								$name    = get_the_title( $value );
								$output .= "<a href='" . $url . "' target='_blank' rel='noreferrer' title='" . __( 'Edit Post', Forminator::DOMAIN ) . "'>$name</a> ,";
							} else {
								if ( is_string( $key ) ) {
									if ( 'product-id' === $key || 'product-quantity' === $key ) {
										if ( 0 === $product_cost ) {
											$product_cost = $value;
										} else {
											$product_cost = $product_cost * $value;
										}
										$is_product = true;
									} else {
										$output .= "$value $key , ";
									}
								}
							}
						}
					}
				}
				if ( $is_product ) {
					$output = sprintf( /* translators: ... */ __( 'Total %d', Forminator::DOMAIN ), $product_cost );
				} else {
					if ( ! empty( $output ) ) {
						$output = substr( trim( $output ), 0, -1 );
					} else {
						$output = implode( ',', $data );
					}
				}

				return $output;
			} else {
				return $data;
			}
		}
		return '';
	}

	/**
	 * Return total items
	 *
	 * @since 1.0
	 * @return int
	 */
	public function total_items() {
		return $this->total_items;
	}
}
