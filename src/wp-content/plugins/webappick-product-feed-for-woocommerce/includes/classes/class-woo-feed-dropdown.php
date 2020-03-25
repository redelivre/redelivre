<?php
/**
 * The file that defines the merchants attributes dropdown
 *
 * A class definition that includes attributes dropdown and functions used across the admin area.
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Ohidul Islam <wahid@webappick.com>
 */

class Woo_Feed_Dropdown {
	
	public $cats = array();
	public $output_types = array(
		'1'  => 'Default',
		'2'  => 'Strip Tags',
		'3'  => 'UTF-8 Encode',
		'4'  => 'htmlentities',
		'5'  => 'Integer',
		'6'  => 'Price',
		'7'  => 'Remove Space',
		'10' => 'Remove ShortCodes',
		'9'  => 'Remove Special Character',
		'8'  => 'CDATA',
	);
	
	public function __construct() {
	}
	
	/**
	 * Dropdown of Merchant List
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function merchantsDropdown( $selected = '' ) {
		$selected   = esc_attr( $selected );
		$attributes = new Woo_Feed_Default_Attributes();
		$str        = '<option></option>';
		foreach ( $attributes->merchants() as $key => $value ) {
			if ( '--' == substr( $key, 0, 2 ) ) {
				$str .= "<optgroup label='$value'>";
			} elseif ( '---' == substr( $key, 0, 2 ) ) {
				$str .= '</optgroup>';
			} else {
				$sltd = '';
				if ( $selected == $key ) {
					$sltd = 'selected="selected"';
				}
				$str .= "<option $sltd value='$key'>" . $value . '</option>';
			}
		}
		
		return $str;
	}
	
	/**
	 * @param int|int[] $selected
	 *
	 * @return string
	 */
	public function outputTypes( $selected = 1 ) {
		$output_types = '';
		if ( ! is_array( $selected ) ) {
			$selected = (array) $selected;
		}
		foreach ( $this->output_types as $key => $value ) {
			$output_types .= "<option value=\"{$key}\"".selected( in_array( $key, $selected ), true, false ).">{$value}</option>";
		}
		// @TODO remove update_option( 'woo_feed_output_type_options', $output_types, false );
		
		return $output_types;
	}
	
	/**
	 * Read txt file which contains google taxonomy list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function googleTaxonomy( $selected = '' ) {
		// Get All Google Taxonomies
		$fileName           = WOO_FEED_FREE_ADMIN_PATH . '/partials/templates/google_taxonomy.txt';
		$customTaxonomyFile = fopen( $fileName, 'r' ); // phpcs:ignore
		$str                = '';
		if ( ! empty( $selected ) ) {
			$selected = trim( $selected );
			if ( ! is_numeric( $selected ) ) {
				$selected = html_entity_decode( $selected );
			} else {
				$selected = (int) $selected;
			}
		}
		if ( $customTaxonomyFile ) {
			// First line contains metadata, ignore it
			fgets( $customTaxonomyFile ); // phpcs:ignore
			while ( $line = fgets( $customTaxonomyFile ) ) { // phpcs:ignore
				list( $catId, $cat ) = explode( '-', $line );
				$catId = (int) trim( $catId );
				$cat   = trim( $cat );
				$str   .= sprintf(
					'<option value="%s" %s>%s</option>',
					$catId,
					selected( $selected, is_numeric( $selected ) ? $catId : $cat, false ),
					$cat
				);
			}
		}
		if ( ! empty( $str ) ) {
			$str = '<option></option>' . $str;
		}
		
		return $str;
	}
	
	/**
	 * Read txt file which contains google taxonomy list
	 *
	 * @return array
	 */
	public function googleTaxonomyArray() {
		// Get All Google Taxonomies
		$fileName           = WOO_FEED_FREE_ADMIN_PATH . '/partials/templates/google_taxonomy.txt';
		$customTaxonomyFile = fopen( $fileName, 'r' );  // phpcs:ignore
		$taxonomy           = array();
		if ( $customTaxonomyFile ) {
			// First line contains metadata, ignore it
			fgets( $customTaxonomyFile );  // phpcs:ignore
			while ( $line = fgets( $customTaxonomyFile ) ) {  // phpcs:ignore
				list( $catId, $cat ) = explode( '-', $line );
				$taxonomy[] = array(
					'value' => absint( trim( $catId ) ),
					'text'  => trim( $cat ),
				);
			}
		}
		$taxonomy = array_filter( $taxonomy );
		
		return $taxonomy;
	}
	
	/**
	 * Dropdown of Google Attribute List
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function googleAttributesDropdown( $selected = '' ) {
		$attributes = new Woo_Feed_Default_Attributes();
		$str        = '<option></option>';
		foreach ( $attributes->googleAttributes() as $key => $value ) {
			if ( substr( $key, 0, 2 ) == '--' ) {
				$str .= "<optgroup label='$value'>";
			} elseif ( substr( $key, 0, 2 ) == '---' ) {
				$str .= '</optgroup>';
			} else {
				$str .= "<option value='$key'>" . $value . '</option>';
			}
		}
		$google_attributes = $str;
		
		$pos = strpos( $google_attributes, "value='" . $selected . "'" );
		
		return substr_replace( $google_attributes, "selected='selected' ", $pos, 0 );
	}
	
	/**
	 * Facebook Attribute list
	 * Alias of google attribute dropdown for facebook
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function facebookAttributesDropdown( $selected = '' ) {
		return $this->googleAttributesDropdown( $selected );
	}
	
	/**
	 * Pinterest Attribute list
	 * Alias of google attribute dropdown for pinterest
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function pinterestAttributesDropdown( $selected = '' ) {
		return $this->googleAttributesDropdown( $selected );
	}
}
