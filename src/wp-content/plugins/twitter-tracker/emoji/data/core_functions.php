
	$GLOBALS['emoji_maps']['html_to_unified'] = array_flip($GLOBALS['emoji_maps']['unified_to_html']);


	#
	# functions to convert incoming data into the unified format
	#

	function tt_emoji_docomo_to_unified(	$text){ return tt_emoji_convert($text, 'docomo_to_unified'); }
	function tt_emoji_kddi_to_unified(		$text){ return tt_emoji_convert($text, 'kddi_to_unified'); }
	function tt_emoji_softbank_to_unified(	$text){ return tt_emoji_convert($text, 'softbank_to_unified'); }
	function tt_emoji_google_to_unified(	$text){ return tt_emoji_convert($text, 'google_to_unified'); }


	#
	# functions to convert unified data into an outgoing format
	#

	function tt_emoji_unified_to_docomo(	$text){ return tt_emoji_convert($text, 'unified_to_docomo'); }
	function tt_emoji_unified_to_kddi(		$text){ return tt_emoji_convert($text, 'unified_to_kddi'); }
	function tt_emoji_unified_to_softbank(	$text){ return tt_emoji_convert($text, 'unified_to_softbank'); }
	function tt_emoji_unified_to_google(	$text){ return tt_emoji_convert($text, 'unified_to_google'); }
	function tt_emoji_unified_to_html(		$text){ return tt_emoji_convert($text, 'unified_to_html'); }
	function tt_emoji_html_to_unified(		$text){ return tt_emoji_convert($text, 'html_to_unified'); }



	function tt_emoji_convert($text, $map){

		return str_replace(array_keys($GLOBALS['emoji_maps'][$map]), $GLOBALS['emoji_maps'][$map], $text);
	}

	function tt_emoji_get_name($unified_cp){

		return $GLOBALS['emoji_maps']['names'][$unified_cp] ? $GLOBALS['emoji_maps']['names'][$unified_cp] : '?';
	}
