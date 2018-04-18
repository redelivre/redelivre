<?php
class EM_WPML_Permalnks {

    public static function init(){
		remove_filter('rewrite_rules_array',array('EM_Permalinks','rewrite_rules_array'));
		add_filter('rewrite_rules_array',array('EM_WPML_Permalnks','rewrite_rules_array'));
        //suggested fix by WPML
        if ( preg_match('/[0-9]{4}\-[0-9]{2}-[0-9]{2}\/?$/', $_SERVER['REQUEST_URI']) ) {
        	add_filter( 'query', array( 'EM_WPML_Permalnks', 'get_page_by_path_filter' ) );
    	}
    }
    
    /**
     * This function replaces EM's EM_Permalinks::rewrite_rules_array() function/filter so that we switch languages to the default language when rewriting permalinks. 
     * Otherwise some of our permalink paths will be based off the translated pages and not the main EM pages.  
     * @param array $rules
     * @return array
     */
    public static function rewrite_rules_array( $rules ){
    	global $sitepress;
    	//check and switch blog to original language if necessary
    	$current_lang = $sitepress->get_current_language();
    	$default_lang = $sitepress->get_default_language();
    	if( $current_lang != $default_lang ) $sitepress->switch_lang($default_lang);
    	//run the EM permalinks within the original language context
    	$em_rules = EM_Permalinks::rewrite_rules_array(array());
    	$em_rules = self::rewrite_rules_array_langs($em_rules);
    	//switch blog back to current languate
    	if( $current_lang != $default_lang ) $sitepress->switch_lang($current_lang);
		return $em_rules + $rules;
    }
    
    /**
     * Adds extra permalink structures to the rewrites array to account for different variations of tralsnated pages.
     * Specifically, this deals with the calendar day pages showing a list of events on a specific date, which has a dynamic date endpoint in the URL.
     * @param array $em_rules
     * @return array
     */
    public static function rewrite_rules_array_langs($em_rules){
        global $sitepress;
		$events_page = get_post( get_option('dbem_events_page') );
		//Detect if there's an event page
		if( is_object($events_page) ){
			//get event page, current language, translations and real wpml home url of this site for use later on
			$trid = $sitepress->get_element_trid($events_page->ID);
		    $translations = $sitepress->get_element_translations($trid);
		    $current_lang = $sitepress->get_current_language();
		    $wpml_url_converter = new WPML_URL_Converter_Url_Helper();
		    $home_url = $wpml_url_converter->get_abs_home();
		    //get settings for current URL structure
		    $wpml_settings = $sitepress->get_settings();
		    $language_negotiation_type = !empty($wpml_settings['language_negotiation_type']) ? $wpml_settings['language_negotiation_type'] : 0;
		    //go through each translation and generate a permalink rule for the calendar day page
		    foreach( $translations as $lang => $translation ){
		        if( $lang != $current_lang && $translation->post_status == 'publish'){
		        	//get translated urls for processing permalink matching translation of events page
				    $home_url_translated = $sitepress->convert_url($home_url, $lang); //translated base URL
				    $event_page_translated = get_permalink($translation->element_id); //translated events page used as base for rewrite rule
				    //if we are using parameters for the language we need to strip the parameter from the urls here for correct insertion into rewrite rules
				    if( $language_negotiation_type == '3' ){
				    	$home_url_translated_parts = explode('?', $home_url_translated);
				    	$home_url_translated = $home_url_translated_parts[0];
				    	$event_page_translated_parts = explode('?', $event_page_translated);
				    	$event_page_translated = $event_page_translated_parts[0];
				    }
				    //remove the base URL from the events slug
		        	$events_slug = urldecode( preg_replace('/\/$/', '', str_replace(trailingslashit($home_url_translated), '', $event_page_translated)) );
				    //remove the language query parameter from the start of the link if we have directory-based permalinks e.g. /fr/events/etc/ => /events/etc/
				    if( $language_negotiation_type == '2' ) $events_slug = preg_replace('/^'.$lang.'\//', '', $events_slug);
				    //add the rewrite preg structure to end of events slug
				    $events_preg = trailingslashit($events_slug).'(\d{4}-\d{2}-\d{2})$';
				    //NUANCE - we can only add the rewrite rule if the events page slug of translations isn't the same as the original page, otherwise see get_page_by_path_filter workaround by WPML team
				    if( empty($em_rules[$events_preg]) ){
				    	$em_rules[$events_preg] = 'index.php?page_id='.$translation->element_id.'&calendar_day=$matches[1]'; //event calendar date search
		        	}
		        }
		    }
		}
		//echo "<pre>"; print_r(trailingslashit(home_url())); echo "</pre>"; die();
        return $em_rules;
    }
    
    /**
     * Suggested fix by WPML
     * Fixes permalink issues when viewing calendar day pages which acts as a dynamic endpoint on the events page.
     * Specifically fixes the issue when the translated events page slug is the same as the original page language.
     * @param string $query
     * @return string
     */
    public static function get_page_by_path_filter( $query ) {
    	global $sitepress, $wpdb; /* @var SitePress $sitepress */
        $trace = version_compare( ICL_SITEPRESS_VERSION, '3.8.2', '>=' ) ? 7:6;
        $debug_backtrace = $sitepress->get_backtrace( $trace, true );

        if ( isset( $debug_backtrace[$trace-1]['function'] ) && $debug_backtrace[$trace-1]['function'] == 'get_page_by_path' ) {
            $where = $wpdb->prepare( "ID IN ( SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE language_code = %s AND element_type LIKE 'post_%%' ) AND ", $sitepress->get_current_language() );
            $query = str_replace( "WHERE ", "WHERE " . $where, $query );
        }

        return $query;
    }
}
EM_WPML_Permalnks::init();