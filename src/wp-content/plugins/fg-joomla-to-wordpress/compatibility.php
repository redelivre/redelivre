<?php
/**
 * Get the last occurred error
 * for PHP < 5.2.0
 */
if (!function_exists('error_get_last')) {
	function error_get_last() {
		$__error_get_last_retval__ = array(
			'type'        => '',
			'message'     => '',
			'file'        => '',
			'line'        => ''
		);
		return $__error_get_last_retval__;
	}

}

/**
 * Set a post thumbnail
 * for WordPress < 3.1
 */
if (!function_exists('set_post_thumbnail')) {
	function set_post_thumbnail( $post, $thumbnail_id ) {
		$post = get_post( $post );
		$thumbnail_id = absint( $thumbnail_id );
		if ( $post && $thumbnail_id && get_post( $thumbnail_id ) ) {
			$thumbnail_html = wp_get_attachment_image( $thumbnail_id, 'thumbnail' );
			if ( ! empty( $thumbnail_html ) ) {
				return update_post_meta( $post->ID, '_thumbnail_id', $thumbnail_id );
			}
		}
		return false;
	}
}

/**
 * Suspend the cache
 * for WordPress < 3.3
 */
if (!function_exists('wp_suspend_cache_addition')) {
	function wp_suspend_cache_addition() {
	}
}

/**
 * Parse a configuration string
 * for PHP < 5.3.0
 */
if (!function_exists('parse_ini_string')) {
	function parse_ini_string($str) {
		
		if(empty($str)) return false;

		$lines = explode("\n", $str);
		$ret = Array();
		$inside_section = false;

		foreach($lines as $line) {
			
			$line = trim($line);

			if(!$line || $line[0] == "#" || $line[0] == ";") continue;
			
			if($line[0] == "[" && $endIdx = strpos($line, "]")){
				$inside_section = substr($line, 1, $endIdx-1);
				continue;
			}

			if(!strpos($line, '=')) continue;

			$tmp = explode("=", $line, 2);

			if($inside_section) {
				
				$key = rtrim($tmp[0]);
				$value = ltrim($tmp[1]);

				if(preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value)) {
					$value = mb_substr($value, 1, mb_strlen($value) - 2);
				}

				$t = preg_match("^\[(.*?)\]^", $key, $matches);
				if(!empty($matches) && isset($matches[0])) {

					$arr_name = preg_replace('#\[(.*?)\]#is', '', $key);

					if(!isset($ret[$inside_section][$arr_name]) || !is_array($ret[$inside_section][$arr_name])) {
						$ret[$inside_section][$arr_name] = array();
					}

					if(isset($matches[1]) && !empty($matches[1])) {
						$ret[$inside_section][$arr_name][$matches[1]] = $value;
					} else {
						$ret[$inside_section][$arr_name][] = $value;
					}

				} else {
					$ret[$inside_section][trim($tmp[0])] = $value;
				}            

			} else {
				
				$ret[trim($tmp[0])] = ltrim($tmp[1]);

			}
		}
		return $ret;
	}
}

?>
