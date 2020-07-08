<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Return the array of forms objects
 *
 * @since 1.0
 * @return mixed
 */
function forminator_get_forms() {
	$forminator = Forminator_Core::get_instance();

	return $forminator->forms;
}

/**
 * Return specific form by ID
 *
 * @since 1.0
 *
 * @param $id
 *
 * @return bool
 */
function forminator_get_form( $id ) {
	$forms = forminator_get_forms();

	return isset( $forms[ $id ] ) && ! empty( $forms[ $id ] ) ? $forms[ $id ] : false;
}

/**
 * Return local timestamp
 *
 * @since 1.0
 *
 * @param $timestamp
 *
 * @return mixed
 */
function forminator_local_timestamp( $timestamp = null ) {
	// If no timestamp, get it current
	if ( is_null( $timestamp ) ) {
		$timestamp = time();
	}

	return $timestamp + ( get_option( 'gmt_offset' ) * 3600 );
}

/**
 * Return user IP
 *
 * @since 1.0
 * @return string
 */
function forminator_user_ip() {
	return Forminator_Geo::get_user_ip();
}

/**
 * Return user property
 *
 * @since 1.0
 * @return string
 */
function forminator_get_user_data( $property ) {
	global $current_user;

	return $current_user->get( $property );
}

/**
 * Return user property
 *
 * @since 1.0
 *
 * @param        $property
 * @param int $post_id
 * @param string $default
 *
 * @return string
 */
function forminator_get_post_data( $property, $post_id = null, $default = '' ) {
	global $post;

	if( $post_id ) {
		$post_object = get_post( $post_id );
		// make sure its wp_post
		if ( $post_object instanceof WP_Post ) {
			// set global $post as $post_object retrieved from `get_post` for next usage
			$post = $post_object; // phpcs:ignore
		}
	}

	if ( ! $post ) {
		// fallback on wp_ajax, `global $post` not available
		$wp_referer = wp_get_referer();
		if ( $wp_referer ) {
			$post_id = url_to_postid( $wp_referer );
			if ( $post_id ) {
				$post_object = get_post( $post_id );
				// make sure its wp_post
				if ( $post_object instanceof WP_Post ) {
					// set global $post as $post_object retrieved from `get_post` for next usage
					$post = $post_object; // phpcs:ignore
				}
			}
		}
	}

	$post_data = forminator_object_to_array( $post );
	if ( isset( $post_data[ $property ] ) ) {
		return $post_data[ $property ];
	} else {
		return $default;
	}
}

/**
 * Return total custom form records
 *
 * @param string $status
 * @since 1.0
 *
 * @return int
 */

function forminator_cforms_total( $status = '' ) {
	return Forminator_Custom_Form_Model::model()->count_all( $status );
}

/**
 * Return custom forms
 *
 * @since 1.0
 * @return Forminator_Base_Form_Model[]
 */
function forminator_custom_forms() {
	return Forminator_Custom_Form_Model::model()->get_all_paged();
}

/**
 * Return custom forms modules
 *
 * @since 1.0
 * @since 1.6.3 add $status arg
 *
 * @param int          $limit
 * @param string|array $status status of cform
 *
 * @return mixed
 */
function forminator_cform_modules( $limit = 4, $status = '' ) {
	$modules   = array();
	$models    = Forminator_Custom_Form_Model::model()->get_models( $limit, $status );
	$form_view = Forminator_Form_Views_Model::get_instance();

	if ( ! empty( $models ) ) {
		foreach ( $models as $model ) {
			$modules[] = array(
				'id'      => $model->id,
				'title'   => $model->name,
				'entries' => Forminator_Form_Entry_Model::count_entries( $model->id ),
				'views'   => $form_view->count_views( $model->id ),
				'date'    => date( get_option( 'date_format' ), strtotime( $model->raw->post_date ) ), // phpcs:ignore
				'status'  => $model->status,
			);
		}
	}

	return $modules;
}

/**
 * Return conversion rate from module
 *
 * @since 1.0
 * @return mixed
 */
function forminator_get_rate( $module ) {
	if ( 0 === $module['views'] ) {
		$rate = 0;
	} else {
		$rate = round( ( $module['entries'] * 100 ) / $module['views'], 1 );
	}

	return $rate;
}

/**
 * Return total polls form records
 *
 * @param string $status
 * @since 1.0
 *
 * @return int
 */
function forminator_polls_total( $status = '' ) {
	return Forminator_Poll_Form_Model::model()->count_all( $status );
}

/**
 * Return polls
 *
 * @since 1.0
 * @return Forminator_Base_Form_Model[]
 */

function forminator_polls_forms() {
	return Forminator_Poll_Form_Model::model()->get_all_paged();
}

/**
 * Return polls modules
 *
 * @since 1.0
 * @since 1.6.3 add $status arg
 *
 * @param int          $limit
 * @param string|array $status
 *
 * @return array
 */
function forminator_polls_modules( $limit = 4, $status = '' ) {
	$modules   = array();
	$models    = Forminator_Poll_Form_Model::model()->get_models( $limit, $status );
	$form_view = Forminator_Form_Views_Model::get_instance();

	if ( ! empty( $models ) ) {
		foreach ( $models as $model ) {
			$modules[] = array(
				'id'      => $model->id,
				'title'   => $model->name,
				'entries' => Forminator_Form_Entry_Model::count_entries( $model->id ),
				'views'   => $form_view->count_views( $model->id ),
				'date'    => date( get_option( 'date_format' ), strtotime( $model->raw->post_date ) ), // phpcs:ignore
				'status'  => $model->status,
				'name'    => forminator_get_name_from_model( $model ),
			);
		}
	}

	return $modules;
}

/**
 * Return total quizzes records
 *
 * @param string $status
 * @since 1.0
 *
 * @return int
 */
function forminator_quizzes_total( $status = '' ) {
	return Forminator_Quiz_Form_Model::model()->count_all( $status );
}

/**
 * Return quizzes
 *
 * @since 1.0
 * @return Forminator_Base_Form_Model[]
 */
function forminator_quizzes_forms() {
	return Forminator_Quiz_Form_Model::model()->get_all_paged();
}

/**
 * Return polls modules
 *
 * @since 1.0
 * @since 1.6.3 add $status arg
 *
 * @param int          $limit
 * @param string|array $status
 *
 * @return array
 */
function forminator_quizzes_modules( $limit = 4, $status = '' ) {
	$modules   = array();
	$models    = Forminator_Quiz_Form_Model::model()->get_models( $limit, $status );
	$form_view = Forminator_Form_Views_Model::get_instance();

	if ( ! empty( $models ) ) {
		foreach ( $models as $model ) {
			$modules[] = array(
				'id'      => $model->id,
				'title'   => $model->name,
				'entries' => Forminator_Form_Entry_Model::count_entries( $model->id ),
				'views'   => $form_view->count_views( $model->id ),
				'type'    => $model->quiz_type,
				'date'    => date( get_option( 'date_format' ), strtotime( $model->raw->post_date ) ), // phpcs:ignore
				'status'  => $model->status,
				'name'    => forminator_get_name_from_model( $model ),
			);
		}
	}

	return $modules;
}

/**
 * Return quiz edit url
 *
 * @since 1.0
 *
 * @param $module
 * @param $id
 *
 * @return mixed
 */
function forminator_quiz_get_edit_url( $module, $id ) {
	if ( isset( $module['type'] ) && 'nowrong' === $module['type'] ) {
		return admin_url( 'admin.php?page=forminator-nowrong-wizard&id=' . $id );
	} else {
		return admin_url( 'admin.php?page=forminator-knowledge-wizard&id=' . $id );
	}
}

/**
 * Return total forms
 *
 * $param  $status
 * @since 1.0
 *
 * @return int
 */
function forminator_total_forms( $status = '' ) {
	$modules = array(
		forminator_cforms_total( $status ),
		forminator_polls_total( $status ),
		forminator_quizzes_total( $status ),
	);

	return array_sum( $modules );
}

/**
 * Return form nice name by id
 *
 * @since 1.0
 *
 * @param        $id
 * @param string $type
 *
 * @return mixed
 */
function forminator_get_form_name( $id, $type = 'custom_form' ) {
	if ( 'custom_form' === $type ) {
		$model = Forminator_Custom_Form_Model::model()->load( $id );
	} elseif ( 'poll' === $type ) {
		$model = Forminator_Poll_Form_Model::model()->load( $id );
	} elseif ( 'quiz' === $type ) {
		$model = Forminator_Quiz_Form_Model::model()->load( $id );
	}

	//Fallback just in case
	if ( ! empty( $model->settings['formName'] ) ) {
		return $model->settings['formName'];
	} else {
		return $model->raw->post_title;
	}
}

/**
 * Return top converting form name
 *
 * @since 1.0
 * @return string
 */
function forminator_top_converting_form() {
	$form_view      = Forminator_Form_Views_Model::get_instance();
	$top_conversion = $form_view->top_converting_form( 'forminator_forms' );

	// If no forms, break
	if ( ! isset( $top_conversion->form_id ) ) {
		return '-';
	}

	return forminator_get_form_name( $top_conversion->form_id, 'custom_form' );
}

/**
 * Return top quiz
 *
 * @since 1.0
 * @return string
 */
function forminator_most_shared_quiz() {
	$form_view    = Forminator_Form_Views_Model::get_instance();
	$most_popular = $form_view->most_popular_form( 'forminator_quizzes' );

	// If no forms, break
	if ( ! isset( $most_popular->form_id ) ) {
		return '-';
	}

	return forminator_get_form_name( $most_popular->form_id, 'quiz' );
}

/**
 * Return most popular poll
 *
 * @since 1.0
 * @return string
 */
function forminator_most_popular_poll() {
	$form_view    = Forminator_Form_Views_Model::get_instance();
	$most_popular = $form_view->most_popular_form( 'forminator_polls' );

	// If no forms, break
	if ( ! isset( $most_popular->form_id ) ) {
		return '-';
	}

	return forminator_get_form_name( $most_popular->form_id, 'poll' );
}

/**
 * Central per page for form view
 *
 * @since 1.0
 * @return int
 */
function forminator_form_view_per_page( $type = 'listings' ) {

	if ( 'entries' === $type ) {
		$per_page = get_option( 'forminator_pagination_entries', 10 );
	} else {
		$per_page = get_option( 'forminator_pagination_listings', 10 );
	}

	// force at least 1 data per page
	if ( $per_page < 1 ) {
		$per_page = 1;
	}
	return apply_filters( 'forminator_form_per_page', $per_page, $type );
}

/**
 * Return admin page url by slug
 *
 * @since 1.0
 * @return mixed
 */
function forminator_get_admin_link( $slug ) {
	return menu_page_url( $slug, false );
}

/**
 * Return JS model to form model
 *
 * @since 1.0
 *
 * @param $data
 *
 * @return array
 */
function forminator_data_to_model_form( $data ) {
	$model = array();

	if ( empty( $data ) ) {
		return $model;
	}

	// Set wrappers
	$model['wrappers'] = $data['wrappers'];

	// Remove wrappers to get all form settings
	unset( $data['wrappers'] );

	// Set settings
	$model['settings'] = $data['settings'];

	return $model;
}

/**
 * Return JS model to form model
 *
 * @since 1.0
 *
 * @param $data
 *
 * @return array
 */
function forminator_data_to_model_poll( $data ) {
	$model = array();

	if ( empty( $data ) ) {
		return $model;
	}

	if ( isset( $data['answers'] ) ) {
		// Set wrappers
		$model['answers'] = $data['answers'];

		// Remove wrappers to get all form settings
		unset( $data['answers'] );
	}

	// Set settings
	$model['settings'] = $data['settings'];

	return $model;
}


/**
 * Return JS model to form model
 *
 * @since 1.0
 *
 * @param $data
 *
 * @return array
 */
function forminator_data_to_model_quiz( $data ) {
	$model = array();

	if ( empty( $data ) ) {
		return $model;
	}

	if ( isset( $data['type'] ) ) {
		$model['type'] = $data['type'];
		unset( $data['type'] );
	}

	// Set results
	if ( isset( $data['results'] ) ) {
		$model['results'] = $data['results'];
		unset( $data['results'] );
	}

	// Set results
	if ( isset( $data['questions'] ) ) {
		$model['questions'] = $data['questions'];
		unset( $data['questions'] );
	}

	// Set settings
	$model['settings'] = $data;
	if ( isset( $data['settings'] ) ) {
		$model['settings'] = $data['settings'];
	}

	return $model;
}

/**
 * Prepares the custom css string
 *
 * @since 1.0
 *
 * @param            $css_string
 * @param            $prefix
 * @param bool|false $as_array
 * @param bool|true  $separate_prefix
 *
 * @return array|string
 */
function forminator_prepare_css( $css_string, $prefix, $as_array = false, $separate_prefix = true, $wildcard = '' ) {
	$css_array = array(); // master array to hold all values
	$elements  = explode( '}', $css_string );
	// Output is the final processed CSS string.
	$output          = '';
	$prepared        = '';
	$have_media      = false;
	$media_names     = array();
	$media_names_key = 0;
	$index           = 0;
	foreach ( $elements as $element ) {
		// We need to null prepared else styles are multiplied
		$prepared = '';

		$check_element = trim( $element );
		if ( empty( $check_element ) ) {
			// Still increment $index even if empty.
			$index ++;
			continue;
		}

		// get the name of the CSS element
		$a_name = explode( '{', $element );
		$name   = $a_name[0];

		// check if @media is  present
		$media_name = '';
		if ( strpos( $name, '@media' ) !== false && isset( $a_name[1] ) ) {
			$have_media                      = true;
			$media_name                      = $name;
			$media_names[ $media_names_key ] = array(
				'name' => $media_name,
			);
			$name                            = $a_name[1];
			$media_names_key ++;
		}

		if ( $have_media ) {
			$prepared = '';
		}

		// get all the key:value pair styles
		$a_styles = explode( ';', $element );
		// remove element name from first property element
		$remove_element_name = ( ! empty( $media_name ) ) ? $media_name . '{' . $name : $name;
		$a_styles[0]         = str_replace( $remove_element_name . '{', '', $a_styles[0] );
		$names               = explode( ',', $name );
		foreach ( $names as $name ) {
			if ( $separate_prefix && empty( $wildcard ) ) {
				$space_needed = true;
			} elseif ( $separate_prefix && ! empty( $wildcard ) ) {
				// wildcard is the sibling class of target selector e.g. "wph-modal"
				if ( strpos( $name, $wildcard ) ) {
					$space_needed = false;
				} else {
					$space_needed = true;
				}
			} else {
				$space_needed = false;
			}
			$maybe_put_space = ( $space_needed ) ? ' ' : '';
			$prepared       .= ( $prefix . $maybe_put_space . trim( $name ) . ',' );
		}
		$prepared  = trim( $prepared, ',' );
		$prepared .= '{';
		// loop through each style and split apart the key from the value
		$count = count( $a_styles );
		for ( $a = 0; $a < $count; $a ++ ) {
			if ( trim( $a_styles[ $a ] ) !== '' ) {
				$a_key_value = explode( ':', $a_styles[ $a ] );
				// build the master css array
				if ( count( $a_key_value ) > 2 ) {
					$a_key_value_to_join = array_slice( $a_key_value, 1 );
					$a_key_value[1]      = implode( ':', $a_key_value_to_join );
				}
				$css_array[ $name ][ $a_key_value[0] ] = $a_key_value[1];
				$prepared                             .= ( $a_key_value[0] . ': ' . $a_key_value[1] );// . strpos($a_key_value[1], "!important") === false ? " !important;": ";";
				if ( strpos( $a_key_value[1], '!important' ) === false ) {
					$prepared .= ' !important';
				}
				$prepared .= ';';
			}
		}
		$prepared .= '}';

		// if have @media earlier, append these styles
		$prev_media_names_key = $media_names_key - 1;
		if ( isset( $media_names[ $prev_media_names_key ] ) ) {
			if ( isset( $media_names[ $prev_media_names_key ]['styles'] ) ) {
				// See if there were two closing '}' or just one.
				// (each element is exploded/split on '}' symbol, so having two empty strings afterward in the elements array means two '}'s.
				$next_element = isset( $elements[ $index + 2 ] ) ? trim( $elements[ $index + 2 ] ) : false;
				// If inside @media block.
				if ( ! empty( $next_element ) ) {
					$media_names[ $prev_media_names_key ]['styles'] .= $prepared;
				} else {
					// If outside of @media block, add to output.
					$output .= $prepared;
				}
			} else {
				$media_names[ $prev_media_names_key ]['styles'] = $prepared;
			}
		} else {
			// If no @media, add styles to $output outside @media.
			$output .= $prepared;
		}
		// Increase index.
		$index ++;
	}

	// if have @media, populate styles using $media_names
	if ( $have_media ) {
		// reset first $prepared styles
		$prepared = '';
		foreach ( $media_names as $media ) {
			$prepared .= $media['name'] . '{ ' . $media['styles'] . ' }';
		}
		// Add @media styles to output.
		$output .= $prepared;
	}

	return $as_array ? $css_array : $output;
}

/**
 * Replaces font-weight from regular to normal
 *
 * @since 1.7.2
 *
 * @param $properties
 *
 * @return mixed
 */
function forminator_normalize_font_weight( $properties ) {
	foreach ( $properties as $key => $property ) {
		if ( is_array( $properties[ $key ] ) ) {
			$properties[ $key ] = forminator_normalize_font_weight( $property );
		} else {
			$properties[ $key ] = str_replace( 'regular', 'normal', $property );
		}
	}

	return $properties;
}

/**
 * Handle all pagination
 *
 * @since 1.0
 *
 * @param int $total - the total records
 * @param string $type - The type of page (listings or entries)
 *
 * @return string
 */
function forminator_list_pagination( $total, $type = 'listings' ) {
	$pagenum     = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0; // phpcs:ignore
	$page_number = max( 1, $pagenum );
	$per_page    = forminator_form_view_per_page( $type );
	if ( 'entries' === $type ) {
		$per_page = forminator_form_view_per_page( 'entries' );
	}
	if ( $total > $per_page ) {
		$removable_query_args = wp_removable_query_args();

		$current_url   = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url   = remove_query_arg( $removable_query_args, $current_url );
		$current       = $page_number + 1;
		$total_pages   = ceil( $total / $per_page );
		$total_pages   = absint( $total_pages );
		$disable_first = false;
		$disable_last  = false;
		$disable_prev  = false;
		$disable_next  = false;
		$mid_size      = 2;
		$end_size      = 1;
		$show_skip     = false;

		if ( $total_pages > 10 ) {
			$show_skip = true;
		}

		if ( $total_pages >= 4 ) {
			$disable_prev = true;
			$disable_next = true;
		}

		if ( 1 === $page_number ) {
			$disable_first = true;
		}

		if ( $page_number === $total_pages ) {
			$disable_last = true;

		}

		?>
		<ul class="sui-pagination">

			<?php if ( ! $disable_first ) : ?>
				<?php
				$prev_url  = esc_url( add_query_arg( 'paged', min( $total_pages, $page_number - 1 ), $current_url ) );
				$first_url = esc_url( add_query_arg( 'paged', min( 1, $total_pages ), $current_url ) );
				?>
				<?php if ( $show_skip ) : ?>
					<li class="wpmudev-pagination--prev">
						<a href="<?php echo esc_attr( $first_url ); ?>"><i class="sui-icon-arrow-skip-start" aria-hidden="true"></i></a>
					</li>
				<?php endif; ?>
				<?php if ( $disable_prev ) : ?>
					<li class="wpmudev-pagination--prev">
						<a href="<?php echo esc_attr( $prev_url ); ?>"><i class="sui-icon-chevron-left" aria-hidden="true"></i></a>
					</li>
				<?php endif; ?>
			<?php endif; ?>
			<?php
			$dots = false;
			for ( $i = 1; $i <= $total_pages; $i ++ ) :
				$class = ( $page_number === $i ) ? 'sui-active' : '';
				$url   = esc_url( add_query_arg( 'paged', ( $i ), $current_url ) );
				if ( ( $i <= $end_size || ( $current && $i >= $current - $mid_size && $i <= $current + $mid_size ) || $i > $total_pages - $end_size ) ) {
					?>
					<li class="<?php echo esc_attr( $class ); ?>"><a href="<?php echo esc_attr( $url ); ?>" class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $i ); ?></a></li>
					<?php
					$dots = true;
				} elseif ( $dots ) {
					?>
					<li class="sui-pagination-dots"><span><?php esc_html_e( '&hellip;' ); ?></span></li>
					<?php
					$dots = false;
				}

				?>

			<?php endfor; ?>

			<?php if ( ! $disable_last ) : ?>
				<?php
				$next_url = esc_url( add_query_arg( 'paged', min( $total_pages, $page_number + 1 ), $current_url ) );
				$last_url = esc_url( add_query_arg( 'paged', max( $total_pages, $page_number - 1 ), $current_url ) );
				?>
				<?php if ( $disable_next ) : ?>
					<li class="wpmudev-pagination--next">
						<a href="<?php echo esc_attr( $next_url ); ?>"><i class="sui-icon-chevron-right" aria-hidden="true"></i></a>
					</li>
				<?php endif; ?>
				<?php if ( $show_skip ) : ?>
					<li class="wpmudev-pagination--next">
						<a href="<?php echo esc_attr( $last_url ); ?>"><i class="sui-icon-arrow-skip-end" aria-hidden="true"></i></a>
					</li>
				<?php endif; ?>
			<?php endif; ?>
		</ul>
		<?php
	}
}

/**
 * Get Form Model from id
 *
 * @since 1.0.5
 *
 * @param $id
 *
 * @return bool|Forminator_Base_Form_Model|null
 */
function forminator_get_model_from_id( $id ) {
	$post = get_post( $id );
	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	$custom_form_model = Forminator_Custom_Form_Model::model();
	$quiz_form_model   = Forminator_Quiz_Form_Model::model();
	$poll_form_model   = Forminator_Poll_Form_Model::model();

	switch ( $post->post_type ) {
		case $custom_form_model->get_post_type():
			$form_model = $custom_form_model->load( $id );
			break;
		case $quiz_form_model->get_post_type():
			$form_model = $quiz_form_model->load( $id );
			break;
		case $poll_form_model->get_post_type():
			$form_model = $poll_form_model->load( $id );
			break;
		default:
			$form_model = null;
			break;
	}

	return $form_model;
}

/**
 * Get Latest entry based on $entry_type
 * [custom-forms, quizzes, poll]
 * will return null if there is no entry
 *
 * @param $entry_type
 *
 * @return Forminator_Form_Entry_Model|null
 */
function forminator_get_latest_entry( $entry_type ) {
	$latest_entry = Forminator_Form_Entry_Model::get_latest_entry( $entry_type );

	return $latest_entry;
}

/**
 * Get Time of latest entry created based on $entry_type
 * [custom-forms, quizzes, poll]
 *
 * @param $entry_type
 *
 * @return string
 */
function forminator_get_latest_entry_time( $entry_type ) {
	$latest_entry = forminator_get_latest_entry( $entry_type );
	if ( $latest_entry instanceof Forminator_Form_Entry_Model ) {
		$last_entry_time = mysql2date( 'U', $latest_entry->date_created_sql );
		$time_diff       = human_time_diff( current_time( 'timestamp' ), $last_entry_time );
		$last_entry_time = sprintf( /* translators: ... */ __( '%s ago', Forminator::DOMAIN ), $time_diff );

		return $last_entry_time;
	} else {
		return __( 'Never', Forminator::DOMAIN );
	}
}

/**
 * Get Latest entry based on $form_id
 * will return null if there is no entry
 *
 * @param $form_id
 *
 * @return Forminator_Form_Entry_Model|null
 */
function forminator_get_latest_entry_by_form_id( $form_id ) {
	$latest_entry = Forminator_Form_Entry_Model::get_latest_entry_by_form_id( $form_id );

	return $latest_entry;
}

/**
 * Get Time of latest entry created based on $form_id
 *
 * @param $form_id
 *
 * @return string
 */
function forminator_get_latest_entry_time_by_form_id( $form_id ) {
	$latest_entry = forminator_get_latest_entry_by_form_id( $form_id );
	if ( $latest_entry instanceof Forminator_Form_Entry_Model ) {
		return $latest_entry->time_created;
	} else {
		return esc_html__( 'Never', Forminator::DOMAIN );
	}
}

/**
 * Update Form Submission retention
 *
 * @since 1.0.6
 *
 * @param $form_id
 * @param $retention_number
 * @param $retention_unit
 */
function forminator_update_form_submissions_retention( $form_id, $retention_number, $retention_unit ) {
	$opt = get_option( 'forminator_form_privacy_settings', array() );
	if ( is_null( $retention_number ) && is_null( $retention_unit ) ) {
		//deletion mode
		unset( $opt[ $form_id ] );
	} else {
		$opt[ $form_id ] = array(
			'submissions_retention_number' => (int) $retention_number,
			'submissions_retention_unit'   => $retention_unit,
		);
	}

	update_option( 'forminator_form_privacy_settings', $opt );
}

/**
 * Clone form submission retention
 *
 * @since 1.0.6
 *
 * @param $old_id
 * @param $new_id
 */
function forminator_clone_form_submissions_retention( $old_id, $new_id ) {
	$opt = get_option( 'forminator_form_privacy_settings', array() );
	if ( isset( $opt[ $old_id ] ) ) {
		$opt[ $new_id ] = $opt[ $old_id ];
	}
	update_option( 'forminator_form_privacy_settings', $opt );
}

/**
 * Update poll submission retention
 *
 * @since 1.0.6
 *
 * @param $poll_id
 * @param $retention_number
 * @param $retention_unit
 */
function forminator_update_poll_ip_address_retention( $poll_id, $retention_number, $retention_unit ) {
	$opt = get_option( 'forminator_poll_privacy_settings', array() );
	if ( is_null( $retention_number ) && is_null( $retention_unit ) ) {
		//deletion mode
		unset( $opt[ $poll_id ] );
	} else {
		$opt[ $poll_id ] = array(
			'ip_address_retention_number' => (int) $retention_number,
			'ip_address_retention_unit'   => $retention_unit,
		);
	}

	update_option( 'forminator_poll_privacy_settings', $opt );
}

/**
 * Clone poll ip retention
 *
 * @since 1.0.6
 *
 * @param $old_id
 * @param $new_id
 */
function forminator_clone_poll_ip_address_retention( $old_id, $new_id ) {
	$opt = get_option( 'forminator_poll_privacy_settings', array() );
	if ( isset( $opt[ $old_id ] ) ) {
		$opt[ $new_id ] = $opt[ $old_id ];
	}
	update_option( 'forminator_poll_privacy_settings', $opt );
}

/**
 * Return form nice name by model
 *
 * @since 1.6.1
 *
 * @param Forminator_Base_Form_Model $model
 *
 * @return string
 */
function forminator_get_name_from_model( $model ) {
	//Fallback just in case
	if ( ! empty( $model->settings['formName'] ) ) {
		return $model->settings['formName'];
	} else {
		return $model->raw->post_title;
	}
}

/**
 * Return social share message
 *
 * @since 1.10
 *
 * @param $settings
 * @param $title
 * @param $result
 * @param $data
 * @return mixed|string
 */
function forminator_get_social_message( $settings, $title, $result, $data = array() ) {
    $message = __( "I got {quiz_result} on {quiz_name} quiz!", Forminator::DOMAIN );
    if( isset( $settings['social-share-message'] ) && ! empty( $settings['social-share-message'] ) ) {
        $message = $settings['social-share-message'];
    }

	if ( ! isset( $data['current_url'] ) || empty( $data['current_url'] ) ) {
		$data['current_url'] = forminator_get_current_url();
	}
	$post_id = url_to_postid( $data['current_url'] );
    $message = forminator_replace_variables( $message, false, $data['current_url'], $post_id );
    $message = str_ireplace( '{quiz_name}', $title, $message );
    $message = str_ireplace( '{quiz_result}', $result, $message );

	return $message;
}

/**
 * Get Chart data of Poll
 *
 * @param Forminator_Poll_Form_Model $poll
 *
 * @return array
 */
function forminator_get_chart_data( Forminator_Poll_Form_Model $poll ) {
	$chart_colors         = forminator_get_poll_chart_colors( $poll->id );
	$default_chart_colors = $chart_colors;
	$chart_datas          = array();

	$form_settings        = $poll->settings;
	$number_votes_enabled = false; // TO-DO: Remove later. This will be handled through ChartJS function.

	$fields_array = $poll->get_fields_as_array();
	$map_entries  = Forminator_Form_Entry_Model::map_polls_entries( $poll->id, $fields_array );
	$fields       = $poll->get_fields();

	if ( ! is_null( $fields ) ) {

		foreach ( $fields as $field ) {

			// Label
			$label = addslashes( $field->title );

			// Votes
			$slug    = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
			$entries = 0;

			if ( in_array( $slug, array_keys( $map_entries ), true ) ) {
				$entries = $map_entries[ $slug ];
			}

			$color = $field->color;

			if ( empty( $color ) ) {
				// Colors
				if ( empty( $chart_colors ) ) {
					$chart_colors = $default_chart_colors;
				}

				$color = array_shift( $chart_colors );
			}

			$chart_datas[] = array(
				(string) $label,
				(int) $entries,
				(string) $color,
			);
		}
	}

	return $chart_datas;
}

/**
 * Get a specific property of an array.
 *
 * @since  1.11
 *
 * @param array  $array   Array from which the property's value should be retrieved.
 * @param string $prop    Name of the property to be retrieved.
 * @param string $default Optional. Value that should be returned if the property is not set or empty. Defaults to null.
 *
 * @return null|string|mixed The value
 */
function forminator_get_property( $array, $prop, $default = null ) {

	if ( ! is_array( $array ) && ! ( is_object( $array ) && $array instanceof ArrayAccess ) ) {
		return $default;
	}

	$value = isset( $array[ $prop ] ) ? $array[ $prop ] : '';

	return empty( $value ) && $default !== null ? $default : $value;
}

/**
 * Flag whether this is the main site or not
 *
 * @since 1.11
 * @return bool
 */
function forminator_is_main_site() {

	return ( is_multisite() && is_main_site() );
}

/**
 * Flag whether this is subdomain network or not
 *
 * @since 1.11
 * @return bool
 */
function forminator_is_subdomain_network() {

	return ( is_multisite() && is_subdomain_install() );
}
