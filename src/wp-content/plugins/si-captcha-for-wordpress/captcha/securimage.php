<?php
//error_reporting(E_ALL); // Report all errors and warnings (very strict, use for testing only)
//ini_set('display_errors', 1); // turn error reporting on
//ini_set('log_errors', 1); // log errors
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); // where to log errors

/**
 * Project:     Securimage: A PHP class for creating and managing form CAPTCHA images<br />
 * File:        securimage.php<br />
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or any later version.<br /><br />
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.<br /><br />
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA<br /><br />
 *
 * Any modifications to the library should be indicated clearly in the source code
 * to inform users that the changes are not a part of the original software.<br /><br />
 *
 * If you found this script useful, please take a quick moment to rate it.<br />
 * http://www.hotscripts.com/rate/49400.html  Thanks.
 *
 * @link http://www.phpcaptcha.org Securimage PHP CAPTCHA
 * @link http://www.phpcaptcha.org/latest.zip Download Latest Version
 * @link http://www.phpcaptcha.org/Securimage_Docs/ Online Documentation
 * @copyright 2009 Drew Phillips
 * @author Drew Phillips <drew@drew-phillips.com>
 * @version 2.0 BETA (November 15, 2009)
 * @package Securimage
 *
 */

/**
 ChangeLog

  Fixes contributed by Mike Challis: http://www.642weather.com/weather/scripts.php
   - Remove image type constants (I ran into an odd installation that errored because they cannot be redeclared)
   - Easier color settings (now uses hex codes like #336699)
   - Fix for sound files on Safari (safari was trying to download securimage.wav.php instead of securimage.wav)
   - Improvement for PHP installation is configured without "--with-ttf".
     It will automatically fail over to GD Fonts when TTF Fonts are not enabled in PHP.
     Some users were reporting there was no error and the captcha was not working.

 2.0.0
 - Add mathematical distortion to characters (using code from HKCaptcha)
 - Improved session support
 - Added Securimage_Color class for easier color definitions
 - Add distortion to audio output to prevent binary comparison attack (proposed by Sven "SavageTiger" Hagemann [insecurity.nl])
 - Flash button to stream mp3 audio (Douglas Walsh www.douglaswalsh.net)
 - Audio output is mp3 format by default
 - Change font to AlteHaasGrotesk by yann le coroller
 - Some code cleanup

 1.0.4 (unreleased)
 - Ability to output audible codes in mp3 format to stream from flash

 1.0.3.1
 - Error reading from wordlist in some cases caused words to be cut off 1 letter short

 1.0.3
 - Removed shadow_text from code which could cause an undefined property error due to removal from previous version

 1.0.2
 - Audible CAPTCHA Code wav files
 - Create codes from a word list instead of random strings

 1.0
 - Added the ability to use a selected character set, rather than a-z0-9 only.
 - Added the multi-color text option to use different colors for each letter.
 - Switched to automatic session handling instead of using files for code storage
 - Added GD Font support if ttf support is not available.  Can use internal GD fonts or load new ones.
 - Added the ability to set line thickness
 - Added option for drawing arced lines over letters
 - Added ability to choose image type for output

 */

/**
 * Securimage CAPTCHA Class.
 *
 * @package    Securimage
 * @subpackage classes
 *
 */
class Securimage_Captcha_si {

	/**
	 * The desired width of the CAPTCHA image.
	 *
	 * @var int
	 */
	var $image_width;

	/**
	 * The desired width of the CAPTCHA image.
	 *
	 * @var int
	 */
	var $image_height;

	/**
	 * The image format for output.<br />
	 * Valid options: png, jpg, gif
	 *
	 * @var string
	 */
	var $image_type;

	/**
	 * The length of the code to generate.
	 *
	 * @var int
	 */
	var $code_length;

	/**
	 * Form id (for multi-captchas on same page).
	 *
	 * @var string
	 */
	var $form_id;

	/**
	 * The character set for individual characters in the image.<br />
	 * Letters are converted to uppercase.<br />
	 * The font must support the letters or there may be problematic substitutions.
	 *
	 * @var string
	 */
	var $charset;


	/**
	 * Note: Use of GD fonts is not recommended as many distortion features are not available<br />
	 * The GD font to use.<br />
	 * Internal gd fonts can be loaded by their number.<br />
	 * Alternatively, a file path can be given and the font will be loaded from file.
	 *
	 * @var mixed
	 */
	var $gd_font_file;

	/**
	 * The approximate size of the font in pixels.<br />
	 * This does not control the size of the font because that is determined by the GD font itself.<br />
	 * This is used to aid the calculations of positioning used by this class.<br />
	 *
	 * @var int
	 */
	var $gd_font_size;

	/**
	 * Use a gd font instead of TTF
	 *
	 * @var bool true for gd font, false for TTF
	 */
	var $use_gd_font;

	// Note: These font options below do not apply if you set $use_gd_font to true with the exception of $text_color

	/**
	 * The path to the TTF font file to load.
	 *
	 * @var string
	 */
	var $ttf_file;

	/**
	 * How much to distort image, higher = more distortion.<br />
	 * Distortion is only available when using TTF fonts.<br />
	 *
	 * @var float
	 */
	var $perturbation;

	/**
	 * The minimum angle in degrees, with 0 degrees being left-to-right reading text.<br />
	 * Higher values represent a counter-clockwise rotation.<br />
	 * For example, a value of 90 would result in bottom-to-top reading text.<br />
	 * This value along with maximum angle distance do not need to be very high with perturbation
	 *
	 * @var int
	 */
	var $text_angle_minimum;

	/**
	 * The minimum angle in degrees, with 0 degrees being left-to-right reading text.<br />
	 * Higher values represent a counter-clockwise rotation.<br />
	 * For example, a value of 90 would result in bottom-to-top reading text.
	 *
	 * @var int
	 */
	var $text_angle_maximum;

	/**
	 * The X-Position on the image where letter drawing will begin.<br />
	 * This value is in pixels from the left side of the image.
	 *
	 * @var int
	 * @deprecated 2.0
	 */
	var $text_x_start;

	/**
	 * The background color for the image as a Securimage_Color.<br />
	 *
	 * @var Securimage_Color
	 */
	var $image_bg_color;

	/**
	 * Scan this directory for gif, jpg, and png files to use as background images.<br />
	 * A random image file will be picked each time.<br />
	 * Change from null to the full path to your directory.<br />
	 * i.e. var $background_directory = $_SERVER['DOCUMENT_ROOT'] . '/securimage/backgrounds';
	 * Make sure not to pass a background image to the show function, otherwise this directive is ignored.
	 *
	 * @var string
	 */
	var $background_directory = null; //'./backgrounds';

    var $ttf_font_directory = null; //'./ttffonts';
    var $gd_font_directory = null; //'./gdfonts';


	/**
	 * The text color to use for drawing characters as a Securimage_Color.<br />
	 * This value is ignored if $use_multi_text is set to true.<br />
	 * Make sure this contrasts well with the background color or image.<br />
	 *
	 * @see Securimage::$use_multi_text
	 * @var Securimage_Color
	 */
	var $text_color;

	/**
	 * Set to true to use multiple colors for each character.
	 *
	 * @see Securimage::$multi_text_color
	 * @var boolean
	 */
	var $use_multi_text;

	/**
	 * Array of Securimage_Colors which will be randomly selected for each letter.<br />
	 *
	 * @var array
	 */
	var $multi_text_color;

	/**
	 * Set to true to make the characters appear transparent.
	 *
	 * @see Securimage::$text_transparency_percentage
	 * @var boolean
	 */
	var $use_transparent_text;

	/**
	 * The percentage of transparency, 0 to 100.<br />
	 * A value of 0 is completely opaque, 100 is completely transparent (invisble)
	 *
	 * @see Securimage::$use_transparent_text
	 * @var int
	 */
	var $text_transparency_percentage;


	// Line options
	/**
	* Draw vertical and horizontal lines on the image.
	*
	* @see Securimage::$line_color
	* @see Securimage::$draw_lines_over_text
	* @var boolean
	*/
	var $num_lines;

	/**
	 * Color of lines drawn over text
	 *
	 * @var string
	 */
	var $line_color;

	/**
	 * Draw the lines over the text.<br />
	 * If fales lines will be drawn before putting the text on the image.
	 *
	 * @var boolean
	 */
	var $draw_lines_over_text;



	//END USER CONFIGURATION
	//There should be no need to edit below unless you really know what you are doing.

	/**
	 * The gd image resource.
	 *
	 * @access private
	 * @var resource
	 */
	var $im;

	/**
	 * Temporary image for rendering
	 *
	 * @access private
	 * @var resource
	 */
	var $tmpimg;

	/**
	 * Internal scale factor for anti-alias @hkcaptcha
	 *
	 * @access private
	 * @since 2.0
	 * @var int
	 */
	var $iscale; // internal scale factor for anti-alias @hkcaptcha

	/**
	 * The background image resource
	 *
	 * @access private
	 * @var resource
	 */
	var $bgimg;

	/**
	 * The code generated by the script
	 *
	 * @access private
	 * @var string
	 */
	var $code;

	/**
	 * The code that was entered by the user
	 *
	 * @access private
	 * @var string
	 */
	var $code_entered;

	/**
	 * Whether or not the correct code was entered
	 *
	 * @access private
	 * @var boolean
	 */
	var $correct_code;

    var $captcha_word;
    var $captcha_path;
    var $ctf_sm_captcha;
    var $prefix;
    var $nosession;

	/**
	 * Class constructor.<br />
	 * Because the class uses sessions, this will attempt to start a session if there is no previous one.<br />
	 * If you do not start a session before calling the class, the constructor must be called before any
	 * output is sent to the browser.
	 *
	 * <code>
	 *   $securimage = new Securimage_Captcha_si();
	 * </code>
	 *
	 */
	function __construct()
	{

		// Set Default Values
        $this->working_directory = getcwd();
        $this->form_id = 'com';
        $this->nosession = false;
        $this->prefix = '000000';
        $this->image_width   = 175;
		$this->image_height  = 60;

		$this->image_type    = 'png'; // png, jpg or gif

	$this->code_length   = 4;
	$this->charset       = 'ABCDEFGHKLMNPRSTUVWYZabcdeghmnpsuvwyz23456789';
    //$this->charset = 'ABCDEFHKLMNPRSTUVWYZ234578';

	$this->gd_font_file  = $this->working_directory . '/gdfonts/bubblebath.gdf';
	$this->use_gd_font   = false;
	$this->gd_font_size  = 24;
	$this->text_x_start  = 15;

	$this->ttf_file      = $this->working_directory . '/ttffonts/ahg-bold.ttf';
    $this->background_directory = $this->working_directory . '/backgrounds';
    $this->ttf_font_directory  = $this->working_directory . '/ttffonts';
    $this->gd_font_directory  = $this->working_directory . '/gdfonts';

	$this->perturbation       = 0.4;
	$this->iscale             = 5;
	$this->text_angle_minimum = 0;
	$this->text_angle_maximum = 0;

	$this->image_bg_color   = '#ffffff';
    $this->text_color       = '#ff0000';
    $this->multi_text_color = array('#6666FF','#660000','#3333CC','#993300','#0060CC','#339900','#6633CC','#330000','#006666','#CC3366');
	$this->use_multi_text   = true;

	$this->use_transparent_text         = true;
	$this->text_transparency_percentage = 30;

	$this->num_lines            = 4;
	$this->line_color           = '#3d3d3d';
	$this->draw_lines_over_text = true;

        // Initialize session or attach to existing
        // no session has been started yet, which is needed for validation
        if ( $this->nosession == false && session_id() == '' ) { // play nice with other plugins
            //set the $_SESSION cookie into HTTPOnly mode for better security
            if (version_compare(PHP_VERSION, '5.2.0') >= 0)  // supported on PHP version 5.2.0  and higher
            @ini_set("session.cookie_httponly", 1);
            session_cache_limiter ('private, must-revalidate');
            session_start();
		}
	}

	/**
	 * Generate a code and output the image to the browser.
	 *
	 * <code>
	 *   <?php
	 *   include 'securimage.php';
	 *   $securimage = new Securimage_si();
	 *   $securimage->show('bg.jpg');
	 *   ?>
	 * </code>
	 *
	 * @param string $background_image  The path to an image to use as the background for the CAPTCHA
	 */
	function show($background_image = "")
	{
		if($background_image != "" && is_readable($background_image)) {
			$this->bgimg = $background_image;
		}
		$this->doImage();
	}

	/**
	 * Validate the code entered by the user.
	 *
	 * <code>
	 *   $code = $_POST['code'];
	 *   if ($securimage->check($code) == false) {
	 *     die("Sorry, the code entered did not match.");
	 *   } else {
	 *     $valid = true;
	 *   }
	 * </code>
	 * @param string $code  The code the user entered
	 * @return boolean  true if the code was correct, false if not
	 */
	function check($code)
	{
		$this->code_entered = $code;
		$this->validate();
		return $this->correct_code;
	}


	/**
	 * Generate and output the image
	 *
	 * @access private
	 *
	 */
	function doImage()
	{
        $bg_color = $this->getColorArray($this->image_bg_color, '#ffffff');

        if ($this->use_gd_font == true) {
			$this->iscale = 1;
		}
		if($this->use_transparent_text == true || $this->bgimg != "") {
			$this->im = imagecreatetruecolor($this->image_width, $this->image_height);
			$bgcolor = imagecolorallocate($this->im, $bg_color[0], $bg_color[1], $bg_color[2]);
			imagefilledrectangle($this->im, 0, 0, $this->image_width * $this->iscale, $this->image_height * $this->iscale, $bgcolor);


			$this->tmpimg = imagecreatetruecolor($this->image_width * $this->iscale, $this->image_height * $this->iscale);
			imagepalettecopy($this->tmpimg, $this->im);
			imagefilledrectangle($this->tmpimg, 0, 0, $this->image_width * $this->iscale, $this->image_height * $this->iscale, $bgcolor);

		} else { //no transparency
			$this->im = imagecreate($this->image_width, $this->image_height);
			$bgcolor = imagecolorallocate($this->im, $bg_color[0], $bg_color[1], $bg_color[2]);

			$this->tmpimg = imagecreate($this->image_width * $this->iscale, $this->image_height * $this->iscale);
			imagepalettecopy($this->tmpimg, $this->im);
		}

		$this->setBackground();

		$this->createCode();

		//if (!$this->draw_lines_over_text && $this->num_lines > 0) $this->drawLines();

		$this->drawWord();
	   	if ($this->use_gd_font == false) $this->distortedCopy();

		if ($this->draw_lines_over_text && $this->num_lines > 0) $this->drawLines();

		$this->output();

	}

	/**
	 * Set the background of the CAPTCHA image
	 *
	 * @access private
	 *
	 */
	function setBackground()
	{
		if ($this->bgimg == '') {
			if ($this->background_directory != null && is_dir($this->background_directory) && is_readable($this->background_directory)) {
				$img = $this->getBackgroundFromDirectory();
				if ($img != false) {
					$this->bgimg = $img;
				}
			}
		}

        if ($this->bgimg == '') {
            return;
        }

		$dat = @getimagesize($this->bgimg);
		if($dat == false) { return; }

		switch($dat[2]) {
			case 1:  $newim = @imagecreatefromgif($this->bgimg); break;
			case 2:  $newim = @imagecreatefromjpeg($this->bgimg); break;
			case 3:  $newim = @imagecreatefrompng($this->bgimg); break;
			default: return;
		}

		if(!$newim) return;

		imagecopyresized($this->im, $newim, 0, 0, 0, 0, $this->image_width, $this->image_height, imagesx($newim), imagesy($newim));
	}

	/**
	 * Return the full path to a random gif, jpg, or png from the background directory.
	 *
	 * @see Securimage::$background_directory
	 * @return mixed  false if none found, string $path if found
	 */
	function getBackgroundFromDirectory()
	{
		$images = array();

		if ($dh = opendir($this->background_directory)) {
			while (($file = readdir($dh)) !== false) {
               $supported_formats = array();
               $gd_support = extension_loaded('gd');
               if ($gd_support) $gd_info = gd_info(); else $gd_info = array();
               if ($gd_support && ( (isset($gd_info['JPG Support']) && $gd_info['JPG Support'] === true) || isset($gd_info['JPEG Support']) && $gd_info['JPEG Support'] === true ) ) $supported_formats[] = 'jpg';
               if ($gd_support && $gd_info['PNG Support']) $supported_formats[] = 'png';
               if ($gd_support && $gd_info['GIF Create Support']) $supported_formats[] = 'gif';
               if (preg_match('/('.implode('|', $supported_formats).')$/i', $file)) $images[] = $file;
            }

			closedir($dh);

			if (sizeof($images) > 0) {
				return rtrim($this->background_directory, '/') . '/' . $images[rand(0, sizeof($images)-1)];
			}
		}

		return false;
	}

     /**
	 * Return the full path to a random font from the font directory.
	 *
	 *
	 * @return mixed  false if none found, string $path if found
	 */
	function getFontFromDirectory($path,$type = 'ttf')
	{
		$fonts = array();

		if ($dh = opendir($path)) {
			while (($file = readdir($dh)) !== false) {
				if (preg_match("/($type)$/i", $file)) $fonts[] = $file;
			}

			closedir($dh);

			if (sizeof($fonts) > 0) {
				return rtrim($path, '/') . '/' . $fonts[rand(0, sizeof($fonts)-1)];
			}
		}

		return false;
	}

	/**
	 * Draw random curvy lines over the image<br />
	 * Modified code from HKCaptcha
	 *
	 * @since 2.0
	 * @access private
	 *
	 */
	function drawLines()
	{
        $line_color = $this->getColorArray($this->line_color, '#3d3d3d');

		$linecolor = imagecolorallocate($this->im, $line_color[0], $line_color[1], $line_color[2]);

		for ($line = 0; $line < $this->num_lines; ++$line) {
			$x = $this->image_width * (1 + $line) / ($this->num_lines + 1);
			$x += (0.5 - $this->frand()) * $this->image_width / $this->num_lines;
			$y = rand($this->image_height * 0.1, $this->image_height * 0.9);
			 
			$theta = ($this->frand()-0.5) * M_PI * 0.7;
			$w = $this->image_width;
			$len = rand($w * 0.4, $w * 0.7);
			$lwid = rand(0, 2);

			$k = $this->frand() * 0.6 + 0.2;
			$k = $k * $k * 0.5;
			$phi = $this->frand() * 6.28;
			$step = 0.5;
			$dx = $step * cos($theta);
			$dy = $step * sin($theta);
			$n = $len / $step;
			$amp = 1.5 * $this->frand() / ($k + 5.0 / $len);
			$x0 = $x - 0.5 * $len * cos($theta);
			$y0 = $y - 0.5 * $len * sin($theta);
			 
			$ldx = round(-$dy * $lwid);
			$ldy = round($dx * $lwid);
			 
			for ($i = 0; $i < $n; ++$i) {
				$x = $x0 + $i * $dx + $amp * $dy * sin($k * $i * $step + $phi);
				$y = $y0 + $i * $dy - $amp * $dx * sin($k * $i * $step + $phi);
				imagefilledrectangle($this->im, $x, $y, $x + $lwid, $y + $lwid, $linecolor);
			}
		}
	}

	/**
	 * Draw the CAPTCHA code over the image
	 *
	 * @access private
	 *
	 */
	function drawWord()
	{
		$width2 = $this->image_width * $this->iscale;
		$height2 = $this->image_height * $this->iscale;
		$text_color = $this->text_color;

        $gd_info = gd_info();
		if ($this->use_gd_font == true || !function_exists('imagettftext') || $gd_info['FreeType Support'] == false ) {
            $this->gd_font_file = $this->getFontFromDirectory($this->gd_font_directory,'gdf');
			if (!is_int($this->gd_font_file)) { //is a file name
				$font = @imageloadfont($this->gd_font_file);
				if ($font == false) {
					trigger_error("Failed to load GD Font file {$this->gd_font_file} ", E_USER_WARNING);
					return;
				}
			} else { //gd font identifier
				$font = $this->gd_font_file;
			}

			$color = imagecolorallocate($this->im, hexdec(substr($text_color, 1, 2)), hexdec(substr($text_color, 3, 2)), hexdec(substr($text_color, 5, 2)));
			imagestring($this->im, $font, $this->text_x_start, ($this->image_height / 2) - ($this->gd_font_size / 2), $this->code, $color);
		} else { //ttf font
            $this->ttf_file = $this->getFontFromDirectory($this->ttf_font_directory,'ttf');
            $text_color = $this->getColorArray($this->text_color, '#3d3d3d');
			$font_size = $height2 * .6; // was .35 but fonts wre too small
			$bb = imagettfbbox($font_size, 0, $this->ttf_file, $this->code);
                        // repeat this line to fix random missing text on some Debian servers
			$bb = imagettfbbox($font_size, 0, $this->ttf_file, $this->code);
			$tx = $bb[4] - $bb[0];
			$ty = $bb[5] - $bb[1];
			$x  = floor($width2 / 2 - $tx / 2 - $bb[0]);
			$y  = round($height2 / 2 - $ty / 2 - $bb[1]);

			if($this->use_transparent_text == true) {
				$alpha = intval($this->text_transparency_percentage / 100 * 127);
				$font_color = imagecolorallocatealpha($this->tmpimg, $text_color[0], $text_color[1], $text_color[2], $alpha);
			} else { //no transparency
				$font_color = imagecolorallocate($this->tmpimg, $text_color[0], $text_color[1], $text_color[2]);
			}

			$strlen = strlen($this->code);
			if (!is_array($this->multi_text_color)) $this->use_multi_text = false;


			if ($this->use_multi_text == false && $this->text_angle_minimum == 0 && $this->text_angle_maximum == 0) { // no angled or multi-color characters
				imagettftext($this->tmpimg, $font_size, 0, $x, $y, $font_color, $this->ttf_file, $this->code);
			} else {
                $this->multi_text_color = $this->convertMultiTextColor($this->multi_text_color);
				for($i = 0; $i < $strlen; ++$i) {
					$angle = rand($this->text_angle_minimum, $this->text_angle_maximum);
					$y = rand($y - 2, $y + 2); // up/down align was 5 , but diff was too steep
                    $x = $x - 5;  // left placement
					if ($this->use_multi_text == true) {
						$idx = rand(0, sizeof($this->multi_text_color) - 1);

						if($this->use_transparent_text == true) {
							$font_color = imagecolorallocatealpha($this->tmpimg, $this->multi_text_color[$idx][0], $this->multi_text_color[$idx][1], $this->multi_text_color[$idx][2], $alpha);
						} else {
							$font_color = imagecolorallocate($this->tmpimg, $this->multi_text_color[$idx][0], $this->multi_text_color[$idx][1], $this->multi_text_color[$idx][2]);
						}
					}
					 
					$ch = $this->code{$i};
					 
					imagettftext($this->tmpimg, $font_size, $angle, $x, $y, $font_color, $this->ttf_file, $ch);
					 
					// estimate character widths to increment $x without creating spaces that are too large or too small
					// these are best estimates to align text but may vary between fonts
					// for optimal character widths, do not use multiple text colors or character angles and the complete string will be written by imagettftext
					if (strpos('abcdeghknopqsuvxyz', $ch) !== false) {
						$min_x = $font_size - ($this->iscale * 6);
						$max_x = $font_size - ($this->iscale * 8);
					} else if (strpos('ilI1', $ch) !== false) {
						$min_x = $font_size / 5;
						$max_x = $font_size / 3;
					} else if (strpos('fjrt', $ch) !== false) {
						$min_x = $font_size - ($this->iscale * 12);
						$max_x = $font_size - ($this->iscale * 12);
					} else if ($ch == 'wm') {
						$min_x = $font_size;
						$max_x = $font_size + ($this->iscale * 3);
					} else { // numbers, capitals or unicode
						$min_x = $font_size + ($this->iscale * 2);
						$max_x = $font_size + ($this->iscale * 8);
					}
					 
					$x += rand($min_x, $max_x);
				} //for loop
			} // angled or multi-color
		} //else ttf font
		//$this->im = $this->tmpimg;
		//$this->output();
	} //function

	/**
	 * Warp text from temporary image onto final image.<br />
	 * Modified for securimage
	 *
	 * @access private
	 * @since 2.0
	 * @author Han-Kwang Nienhuys modified
	 * @copyright Han-Kwang Neinhuys
	 *
	 */
	function distortedCopy()
	{
		$numpoles = 3; // distortion factor
		 
		// make array of poles AKA attractor points
		for ($i = 0; $i < $numpoles; ++$i) {
			$px[$i]  = rand($this->image_width * 0.3, $this->image_width * 0.8);
			$py[$i]  = rand($this->image_height * 0.3, $this->image_height * 0.8);
			$rad[$i] = rand($this->image_width * 0.4, $this->image_width * 0.8);
			$tmp     = -$this->frand() * 0.15 - 0.15;
			$amp[$i] = $this->perturbation * $tmp;
		}
		 
		$bgCol   = imagecolorat($this->tmpimg, 0, 0);
		$width2  = $this->iscale * $this->image_width;
		$height2 = $this->iscale * $this->image_height;
		 
		imagepalettecopy($this->im, $this->tmpimg); // copy palette to final image so text colors come across
		 
		// loop over $img pixels, take pixels from $tmpimg with distortion field
		for ($ix = 0; $ix < $this->image_width; ++$ix) {
			for ($iy = 0; $iy < $this->image_height; ++$iy) {
				$x = $ix;
				$y = $iy;
					
				for ($i = 0; $i < $numpoles; ++$i) {
					$dx = $ix - $px[$i];
					$dy = $iy - $py[$i];
					if ($dx == 0 && $dy == 0) continue;

					$r = sqrt($dx * $dx + $dy * $dy);
					if ($r > $rad[$i]) continue;

					$rscale = $amp[$i] * sin(3.14 * $r / $rad[$i]);
					$x += $dx * $rscale;
					$y += $dy * $rscale;
				}
					
				$c = $bgCol;
				$x *= $this->iscale;
				$y *= $this->iscale;

				if ($x >= 0 && $x < $width2 && $y >= 0 && $y < $height2) {
					$c = imagecolorat($this->tmpimg, $x, $y);
				}

				if ($c != $bgCol) { // only copy pixels of letters to preserve any background image
					imagesetpixel($this->im, $ix, $iy, $c);
				}
			}
		}
	}

	/**
	 * Create a code and save to the session
	 *
	 * @since 1.0.1
	 *
	 */
	function createCode()
	{

		$this->code = $this->generateCode($this->code_length);


		$this->saveData();
	}

	/**
	 * Generate a code
	 *
	 * @access private
	 * @param int $len  The code length
	 * @return string
	 */
	function generateCode($len)
	{
        //mchallis modified so that a 4 letter swear word could never appear
//		$code = '';
//		for($i = 1, $cslen = strlen($this->charset); $i <= $len; ++$i) {
//			$code .= $this->charset{rand(0, $cslen - 1)};
//		}
        $chars_num = '2345789'; // do not change this or the code will break!!
        // one random position always has to be a number so that a 4 letter swear word could never appear
        $rand_pos = mt_rand( 0, $len - 1 );
        $code = '';
		for($i = 0; $i < $len; $i++ ) {
           // this rand character position is a number only so that a 4 letter swear word could never appear
           if($i == $rand_pos) {
                  $pos = mt_rand( 0, strlen( $chars_num ) - 1 );
                  $char = $chars_num[$pos];
           } else {
                  $pos = mt_rand( 0, strlen( $this->charset ) - 1 );
                  $char = $this->charset[$pos];
           }
	       $code .= $char;
		}
        if ( $this->nosession == true )
           return $this->captcha_word;

        if ( $this->nosession == false )
		  return $code;
	}


	/**
	 * Output image to the browser
	 *
	 * @access private
	 *
	 */
	function output()
	{
	    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

        if ( strtolower($this->image_type) == 'gif' ) $this->image_type = '1';
        if ( strtolower($this->image_type) == 'jpg' ) $this->image_type = '2';

		switch($this->image_type)
		{
			case '1':
			       header("Content-Type: image/gif");
                   imagegif($this->im);
				break;
			case '2':
			      header("Content-Type: image/jpeg");
                  imagejpeg($this->im, null, 90);
				break;
			default:
                  header("Content-Type: image/png");
                  imagepng($this->im);
				break;
		}
		imagedestroy($this->im);
		//exit;
	}

	/**
	 * Save the code in the session
	 *
	 * @access private
	 *
	 */
	function saveData()
	{
		$_SESSION['securimage_code_si_'.$this->form_id] = strtolower($this->code);
	}

	/**
	 * Validate the code to the user code
	 *
	 * @access private
	 *
	 */
	function validate()
	{
		if ( isset($_SESSION['securimage_code_si_'.$this->form_id]) && !empty($_SESSION['securimage_code_si_'.$this->form_id]) ) {
			if ( strtolower($_SESSION['securimage_code_si_'.$this->form_id]) == strtolower(trim($this->code_entered)) ) {
				$this->correct_code = true;
				$_SESSION['securimage_code_si_'.$this->form_id] = '';  // clear code to prevent session re-use
			} else {
				$this->correct_code = false;
			}
		} else {
			$this->correct_code = false; // value was never set or is blank
		}
	}

	/**
	 * Get the captcha code
	 *
	 * @since 1.0.1
	 * @return string
	 */
	function getCode()
	{

        if (isset($_SESSION['securimage_code_si_'.$this->form_id]) && !empty($_SESSION['securimage_code_si_'.$this->form_id])) {
			return strtolower($_SESSION['securimage_code_si_'.$this->form_id]);
		} else {
			return '';
		}
	}

	/**
	 * Check if the user entered code was correct
	 *
	 * @access private
	 * @return boolean
	 */
	function checkCode()
	{
		return $this->correct_code;
	}


	/**
	 * Generate random number less than 1
	 * @since 2.0
	 * @access private
	 * @return float
	 */
	function frand()
	{
		return 0.0001*rand(0,9999);
	}



    /**
     *
	 * Create a color array based on user setting.
     * @since 2.0
     * contributed by Mike Challis
	 * Specify the red, green, and blue components using their HTML hex code.<br />
	 * i.e. #4A203C
	 *
	 * @param $color color the user has set
	 * @param $default default color to use if color the user set does not validate
	 */
	function getColorArray($color, $default)
	{

      if ( is_object($color) ) {
        // This method: new Securimage_Color(0xea, 0xea, 0xea);
        return array('0' => $color->r, '1' => $color->g, '2' => $color->b);
      }
      
      // This method: $this->text_color = '#3d3d3d';
      if ( !$this->validateHexColor($color) ) $color = $default; // color was not valid, use default
      $color =  str_replace('#','',$color);
      $color_int = hexdec("#$color");
      $color_arr = array('0' => 0xFF & ($color_int >> 0x10),'1' => 0xFF & ($color_int >> 0x8),'2' => 0xFF & $color_int);
	  //$color_arr[0] = red, $color_arr[1] = green, $color_arr[2] = blue

      return $color_arr;
	}

    /**
     *
	 * Validate a CSS HEX color, i.e. #4A203C
     * @since 2.0
	 * contributed by Mike Challis
	 * only allow simple 6 char hex codes with or without # like this 336699 or #336699
	 *
	 * @param $color color the user has set
	 */
    function validateHexColor($color)
    {

      if ( preg_match("/^#[a-f0-9]{6}$/i", $color) ) {
         return true;
      }
      if ( preg_match("/^[a-f0-9]{6}$/i", $color) ) {
         return true;
      }
      return false;
    }

    /**
     * Process the multi_text_color array
	 *
     * @since 2.0
	 * contributed by Mike Challis
	 *
	 *
	 * @param $color_arr array of HEX colors the user has set
	 */
    function convertMultiTextColor($color_arr)
    {

       $colors = array();
       foreach($color_arr as $color){
          $colors[]= $this->getColorArray($color, '#0020CC');
       }
       return $colors;

    }
    
 // needed for emptying temp directories for captcha session files
function clean_temp_dir($dir, $minutes = 30) {
    // deletes all files over xx minutes old in a temp directory
  	if ( ! is_dir( $dir ) || ! is_readable( $dir ) || ! is_writable( $dir ) )
		return false;

	$count = 0;
    $list = array();
	if ( $handle = @opendir( $dir ) ) {
		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( $file == '.' || $file == '..' || $file == '.htaccess' || $file == 'index.php')
				continue;

			$stat = @stat( $dir . $file );
			if ( ( $stat['mtime'] + $minutes * 60 ) < time() ) {
			    @unlink( $dir . $file );
				$count += 1;
			} else {
               $list[$stat['mtime']] = $file;
            }
		}
		closedir( $handle );
        // purge xx amount of files based on age to limit a DOS flood attempt. Oldest ones first, limit 500
        if( isset($list) && count($list) > 499) {
          ksort($list);
          $ct = 1;
          foreach ($list as $k => $v) {
            if ($ct > 499) @unlink( $dir . $v );
            $ct += 1;
          }
       }
	}
	return $count;
}

} /* class Securimage_si */

/**
 * Color object for Securimage_si CAPTCHA
 *
 * @since 2.0
 * @package Securimage
 * @subpackage classes
 *
 */
class Securimage_Color_si {
	/**
	 * Red component: 0-255
	 *
	 * @var int
	 */
	var $r;
	/**
	 * Green component: 0-255
	 *
	 * @var int
	 */
	var $g;
	/**
	 * Blue component: 0-255
	 *
	 * @var int
	 */
	var $b;

	/**
	 * Create a new Securimage_Color_si object.<br />
	 * Specify the red, green, and blue components using their HTML hex code equivalent.<br />
	 * i.e. #4A203C is declared as new Securimage_Color(0x4A, 0x20, 0x3C)
	 *
	 * @param $red Red component 0-255
	 * @param $green Green component 0-255
	 * @param $blue Blue component 0-255
	 */
	function __construct($red, $green, $blue)
	{
		if ($red < 0) $red       = 0;
		if ($red > 255) $red     = 255;
		if ($green < 0) $green   = 0;
		if ($green > 255) $green = 255;
		if ($blue < 0) $blue     = 0;
		if ($blue > 255) $blue   = 255;

		$this->r = $red;
		$this->g = $green;
		$this->b = $blue;
	}
}

// end of file
