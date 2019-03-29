<?php
/**
 * Unit test class for WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

/**
 * Unit test class for the I18n sniff.
 *
 * @package WPCS\WordPressCodingStandards
 * @since   0.10.0
 */
class WordPress_Tests_WP_I18nUnitTest extends AbstractSniffUnitTest {

	/**
	 * Fill in the $text_domain property to test domain check functionality.
	 */
	protected function setUp() {
		parent::setUp();
		PHP_CodeSniffer::setConfigData( 'text_domain', 'my-slug,default', true );
	}

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the file being tested.
	 * @return array <int line number> => <int number of errors>
	 */
	public function getErrorList( $testFile = 'I18nUnitTest.inc' ) {

		switch ( $testFile ) {
			case 'I18nUnitTest.inc':
				return array(
					3 => 1,
					6 => 1,
					9 => 1,
					11 => 1,
					13 => 1,
					15 => 1,
					17 => 1,
					19 => 1,
					21 => 1,
					23 => 1,
					25 => 1,
					27 => 1,
					33 => 1,
					35 => 1,
					37 => 1,
					39 => 1,
					41 => 1,
					43 => 1,
					45 => 1,
					47 => 1,
					48 => 1,
					50 => 1,
					52 => 1,
					53 => 1,
					55 => 1,
					56 => 2,
					58 => 1,
					59 => 1,
					60 => 1,
					62 => 1,
					63 => 2,
					65 => 1,
					66 => 1,
					67 => 1,
					72 => 1,
					74 => 1,
					75 => 1,
					76 => 1,
					77 => 1,
					78 => 1,
					93 => 1,
					95 => 2,
					100 => 1,
					101 => 1,
					102 => 1,
					103 => 1,
					105 => 1,
					106 => 1,
					107 => 1,
					120 => 1,
					121 => 1,
					122 => 1,
					123 => 1,
					124 => 1,
					125 => 1,
					128 => 1,
					129 => 1,
					132 => ( PHP_VERSION_ID >= 50300 ) ? 1 : 2, // PHPCS on PHP 5.2 does not recognize T_NOWDOC.
					138 => 1,
					143 => 1,
					148 => 1,
				);

			case 'I18nUnitTest.1.inc':
				return array(
					104 => 2,
				);

			default:
				return array();

		} // End switch().

	} // end getErrorList()

	/**
	 * Returns the lines where warnings should occur.
	 *
	 * @param string $testFile The name of the file being tested.
	 * @return array <int line number> => <int number of warnings>
	 */
	public function getWarningList( $testFile = 'I18nUnitTest.inc' ) {
		switch ( $testFile ) {
			case 'I18nUnitTest.inc':
				return array(
					69 => 1,
					70 => 1,
					100 => 1,
					101 => 1,
					102 => 1,
					103 => 1,
				);

			case 'I18nUnitTest.1.inc':
				return array(
					8 => 1,
					43 => 1,
					49 => 1,
					52 => 1,
					74 => 1,
					85 => 1,
				);

			default:
				return array();

		}
	}

} // End class.
