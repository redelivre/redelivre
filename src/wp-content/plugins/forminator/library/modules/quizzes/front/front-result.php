<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front render class for custom forms
 */
class Forminator_QForm_Result extends Forminator_Result {

	protected $post_type = 'quizzes';

	/**
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @return string
	 */
	public function get_og_description( $entry ) {
		$description = '';
		$quiz        = Forminator_Quiz_Form_Model::model()->load( $entry->form_id );
		if ( $quiz instanceof Forminator_Quiz_Form_Model ) {
			if ( 'knowledge' === $quiz->quiz_type ) {
				$description = $this->get_og_description_knowledge( $quiz, $entry );
			} else {
				$description = $this->get_og_description_nowrong( $quiz, $entry );
			}
		}

		return $description;
	}

	/**
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	public function get_og_title( $entry ) {
		$quiz     = Forminator_Quiz_Form_Model::model()->load( $entry->form_id );
		$entry_id = $entry->entry_id;

		/**
		 * Priority
		 * 1. Quiz name
		 * 2. Post/Page Title
		 * 3. Website name
		 */
		$title = isset( $quiz->settings['quiz_name'] ) ? $quiz->settings['quiz_name'] : '';
		if ( empty( $title ) ) {
			$title = single_post_title( '', false );
		}

		if ( empty( $title ) ) {
			$title = get_bloginfo( 'name' );
		}

		/**
		 * Filter Meta og:title for Quiz Result Page
		 *
		 * @since 1.7
		 *
		 * @param string                     $title
		 * @param Forminator_Quiz_Form_Model $quiz
		 * @param int                        $entry_id
		 *
		 * @return string
		 */
		$title = apply_filters( 'forminator_quiz_result_page_meta_title', $title, $quiz, $entry_id );

		return $title;
	}

	/**
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	public function get_og_url() {
		global $wp;

		$post_data  = $this->post_data;
		$query_args = array();
		$query      = wp_parse_url( $post_data );
		$permalink  = get_option( 'permalink_structure' );

		if ( empty( $permalink ) && isset( $query['query'] ) ) {
			$query_args = $query['query'];
		}

		$url = home_url( add_query_arg( $query_args, $wp->request ) );

		$url = trailingslashit( $url );

		/**
		 * Filter Meta og:url for Quiz Result Page
		 *
		 * @since 1.7
		 *
		 * @param string                      $url
		 * @param array                       $post_data
		 * @param Forminator_Form_Entry_Model $entry
		 *
		 * @return string
		 */
		$title = apply_filters( 'forminator_quiz_result_page_meta_url', $url, $post_data );

		return $title;
	}

	/**
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	public function get_og_image( $entry ) {
		$quiz     = Forminator_Quiz_Form_Model::model()->load( $entry->form_id );
		$entry_id = $entry->entry_id;

		/**
		 * Priority
		 * 1. Quiz Featured image
		 * 2. Post/page Featured image
		 * 3. Blog header image
		 */
		$image = isset( $quiz->settings['quiz_feat_image'] ) ? $quiz->settings['quiz_feat_image'] : '';
		if ( empty( $image ) ) {
			$image = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		}

		if ( empty( $image ) ) {
			$image = get_header_image();
		}

		/**
		 * Filter Meta og:image for Quiz Result Page
		 *
		 * @since 1.7
		 *
		 * @param string                     $image
		 * @param Forminator_Quiz_Form_Model $quiz
		 * @param int                        $entry_id
		 *
		 * @return string
		 */
		$image = apply_filters( 'forminator_quiz_result_page_meta_title', $image, $quiz, $entry_id );

		return $image;
	}

	/**
	 * @param Forminator_Quiz_Form_Model  $quiz
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @return string
	 */
	private function get_og_description_knowledge( $quiz, $entry ) {
		$total       = 0;
		$right       = 0;
		$description = '';
		$data        = $entry;
		$entry_id    = $entry->entry_id;
		$quiz_title  = '';
		$current_url = array();

		if ( isset( $quiz->settings['quiz_name'] ) ) {
			$quiz_title = esc_html( $quiz->settings['quiz_name'] );
		}

		if ( isset( $data->meta_data['entry'] ) ) {
			$answers = $data->meta_data['entry']['value'];
			if ( is_array( $answers ) ) {
				$total = count( $answers );
				foreach ( $answers as $key => $answer ) {
					if ( true === $answer['isCorrect'] ) {
						$right ++;
					}
				}
			}
		}
		if( isset( $data->meta_data['quiz_url'] ) ) {
			$current_url['current_url'] = $data->meta_data['quiz_url']['value'];
		}
		if ( $total > 0 ) {
		    $result = esc_html( $right ) . '/' . esc_html( $total );

            $description = forminator_get_social_message( $quiz->settings, $quiz_title, $result, $current_url );
		}

		/**
		 * Filter Meta og:description for Knowledge Quiz Result Page
		 *
		 * @since 1.5.2
		 *
		 * @param string                     $description
		 * @param Forminator_Quiz_Form_Model $quiz
		 * @param int                        $entry_id
		 * @param int                        $right      right answer
		 * @param int                        $total      total answer
		 * @param string                     $quiz_title Quiz name
		 */
		$description = apply_filters( 'forminator_quiz_knowledge_result_page_meta_description', $description, $quiz, $entry_id, $right, $total, $quiz_title );

		return $description;

	}

	/**
	 * @param Forminator_Quiz_Form_Model  $quiz
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @return string
	 */
	private function get_og_description_nowrong( $quiz, $entry ) {
		$result_slug  = null;
		$result       = null;
		$description  = '';
		$quiz_title   = '';
		$result_title = '';
		$current_url  = array();
		$entry_id     = $entry->entry_id;
		if ( isset( $quiz->settings['quiz_name'] ) ) {
			$quiz_title = esc_html( $quiz->settings['quiz_name'] );
		}

		if ( isset( $entry->meta_data['entry'] ) ) {
			$entry_value = $entry->meta_data['entry']['value'];
			if ( is_array( $entry_value ) && isset( $entry_value[0] ) ) {
				$entry_value = $entry_value[0];


				// its disgusting because of the way we saved it
				if ( isset( $entry_value['value'] ) ) {
					$result_value = $entry_value['value'];

					if ( isset( $result_value['result'] ) && isset( $result_value['result']['slug'] ) && ! empty( $result_value['result']['slug'] ) ) {
						$result_slug = $result_value['result']['slug'];
					}
				}
			}
		}

		if ( ! is_null( $result_slug ) ) {
			$result = $quiz->getResult( $result_slug );
		}

		if( isset( $entry->meta_data['quiz_url'] ) ) {
			$current_url['current_url'] = $entry->meta_data['quiz_url']['value'];
		}
		if ( $result ) {
			if ( isset( $result['title'] ) ) {
				$result_title = esc_html( $result['title'] );
			}

            $description = forminator_get_social_message( $quiz->settings, $quiz_title, $result_title, $current_url );
		}

		/**
		 * Filter Meta og:description for no wrong Quiz Result Page
		 *
		 * @since 1.5.2
		 *
		 * @param string                     $description
		 * @param Forminator_Quiz_Form_Model $quiz
		 * @param int                        $entry_id
		 * @param array                      $result     result detail
		 * @param string                     $quiz_title Quiz name
		 */
		$description = apply_filters( 'forminator_quiz_nowrong_result_page_meta_description', $description, $quiz, $entry_id, $result, $quiz_title );

		return $description;

	}

	/**
	 * Not printing any styles
	 * just filtering canonical URL
	 *
	 * @since 1.7
	 */
	public function print_styles() {
		parent::print_styles();
		$entry = new Forminator_Form_Entry_Model( $this->entry_id );
		if ( ! $this->is_public_allowed( $entry ) ) {
			return;
		}

		add_filter( 'get_canonical_url', array( $this, 'get_og_url' ) );
	}

	public function print_result_header() {
		$entry_id = $this->entry_id;
		$entry    = new Forminator_Form_Entry_Model( $this->entry_id );
		if ( ! $this->is_public_allowed( $entry ) ) {
			return;
		}

		$url         = $this->get_og_url();
		$title       = $this->get_og_title( $entry );
		$description = $this->get_og_description( $entry );
		$image       = $this->get_og_image( $entry );

		// make description as title
		// FB fix, og:description ignored if no og:image
		if ( empty( $image )) {
			$title = $description;
		}

		ob_start();
		?>
		<meta property="og:url" content="<?php echo esc_html( $url ); ?>"/>
		<meta property="og:title" content="<?php echo esc_textarea( $title ); ?>"/>
		<meta property="og:description" content="<?php echo esc_textarea( $description ); ?>"/>
		<meta property="og:type" content="article"/>
		<?php if ( ! empty( $image ) ) : ?>
			<meta property="og:image" content="<?php echo esc_html( $image ); ?>"/>
		<?php endif; ?>
		<?php
		$header = ob_get_clean();

		/**
		 * Filter Header for Quiz Result Page
		 *
		 * @since 1.5.2
		 *
		 * @param string $header
		 * @param int    $entry_id
		 */
		$header = apply_filters( 'forminator_quiz_result_page_header', $header, $entry_id );

		echo $header; // WPCS XSS OK.


	}

	/**
	 * @inheritdoc
	 */
	public function is_public_allowed( $entry ) {
		if ( empty( $entry->entry_id ) ) {
			return false;
		}

		if ( $entry->entry_type !== $this->post_type ) {
			return false;
		}

		$quiz = Forminator_Quiz_Form_Model::model()->load( $entry->form_id );

		if ( ! $quiz instanceof Forminator_Quiz_Form_Model ) {
			return false;
		}

		if ( ! $quiz->is_entry_share_enabled() ) {
			return false;
		}

		return true;
	}
}
