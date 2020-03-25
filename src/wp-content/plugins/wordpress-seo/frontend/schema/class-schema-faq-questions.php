<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Frontend\Schema
 */

/**
 * Returns a question object for each question in an FAQ block.
 *
 * @since 11.1
 *
 * @property array                 $data     The Schema array.
 * @property WP_Block_Parser_Block $block    The block we're taking the questions out of.
 * @property WPSEO_Schema_Context  $context  A value object with context variables.
 * @property int                   $position The position in the list.
 */
class WPSEO_Schema_FAQ_Questions {

	/**
	 * The Schema array.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * All the blocks of this block-type.
	 *
	 * @var WP_Block_Parser_Block
	 */
	private $block;

	/**
	 * Position in the list.
	 *
	 * @var int
	 */
	private $position;

	/**
	 * WPSEO_Schema_FAQ_Questions constructor.
	 *
	 * @param array                 $data    Our schema graph.
	 * @param WP_Block_Parser_Block $block   The FAQ block of this type.
	 * @param WPSEO_Schema_Context  $context A value object with context variables.
	 */
	public function __construct( $data, $block, $context ) {
		$this->data     = $data;
		$this->block    = $block;
		$this->context  = $context;
		$this->position = 0;
	}

	/**
	 * Returns the Question and Answers schema data.
	 *
	 * @return array The Schema with Questions and Answers added.
	 */
	public function generate() {
		foreach ( $this->block['attrs']['questions'] as $question ) {
			if ( ! isset( $question['jsonAnswer'] ) || empty( $question['jsonAnswer'] ) ) {
				continue;
			}
			$this->data[] = $this->generate_question_block( $question );
		}
		return $this->data;
	}

	/**
	 * Generates a Question and Answer piece.
	 *
	 * @param array $question The question to generate schema for.
	 *
	 * @return array Schema.org Question piece.
	 */
	protected function generate_question_block( $question ) {
		$url = $this->context->canonical . '#' . esc_attr( $question['id'] );

		$data = [
			'@type'          => 'Question',
			'@id'            => $url,
			'position'       => $this->position ++,
			'url'            => $url,
			'name'           => wp_strip_all_tags( $question['jsonQuestion'] ),
			'answerCount'    => 1,
			'acceptedAnswer' => $this->add_accepted_answer_property( $question ),
		];

		$data = WPSEO_Schema_Utils::add_piece_language( $data );

		return $data;
	}

	/**
	 * Adds the Questions `acceptedAnswer` property.
	 *
	 * @param array $question The question to add the acceptedAnswer to.
	 *
	 * @return array Schema.org Question piece.
	 */
	protected function add_accepted_answer_property( $question ) {
		$data = [
			'@type' => 'Answer',
			'text'  => strip_tags( $question['jsonAnswer'], '<h1><h2><h3><h4><h5><h6><br><ol><ul><li><a><p><b><strong><i><em>' ),
		];

		$data = WPSEO_Schema_Utils::add_piece_language( $data );

		return $data;
	}
}
