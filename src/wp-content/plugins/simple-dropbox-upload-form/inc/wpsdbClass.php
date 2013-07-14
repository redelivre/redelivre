<?php

class wpsdbFunction{
	
	const WPSDB_PREFIX = "wpsdb";
	
	const WPSDB_SGROUP = "wp_db-settings-group";
	
	function optionPrefix(){
		return self::WPSDB_PREFIX;
	}
	
	public function addSettingsGroup ($wpsdbAddWhat,$wpsdbSgroup = self::WPSDB_SGROUP){
		for( $a = 0; $a < count( $wpsdbAddWhat ); $a++){
			register_setting( $wpsdbSgroup , $wpsdbAddWhat[$a] );
		}
	}
	
	public function removeSettingsGroup($wpsdb_query=NULL,$wpsdbPrefix = self::WPSDB_PREFIX){
		global $wpdb;
		
		$buildQuery = "SELECT * FROM $wpdb->options WHERE option_name LIKE '$wpsdbPrefix\_%' ";
		if($wpsdb_query){
			$buildQuery .= $wpsdb_query;
		}
		
		//echo $buildQuery;

		$wpsdb_delete_settings = $wpdb->get_results($buildQuery);
		//print_r($wpsdb_delete_settings);
		
		for($t = 0; $t < count($wpsdb_delete_settings); $t++){
			//echo "<br />ID: ".$wpsdb_delete_settings[$t]->option_id;
			$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_id = %d",$wpsdb_delete_settings[$t]->option_id));
		}
	}
	
	public function updateSettingsGroup($wpsdbExcludeWhat){
		if($wpsdbExcludeWhat){
			//$buildQuery = "SELECT * FROM $wpdb->options WHERE option_name LIKE '$wpsdbPrefix\_%' ";
			if(is_array($wpsdbExcludeWhat)){
				$wpsdbExcludeWhat = array_filter($wpsdbExcludeWhat);
				//print_r($wpsdbExcludeWhat);
				$addQuery = "AND option_name <> ";
				
				for($l=0;$l<count($wpsdbExcludeWhat);$l++){
					$addQuery .= "'".$wpsdbExcludeWhat[$l]."' ";
					//echo $wpsdb_exclude[$l];
					if($l != (count($wpsdbExcludeWhat)-1)){
						$addQuery .= "AND option_name <> ";
					}
				}
				//echo $addQuery;
			}else{
				$addQuery .= "AND option_name <> '$wpsdbExcludeWhat'";
			}
		}
		$this->removeSettingsGroup($addQuery);
	}
	
	public function removeOldSettingsGroup($oldSetting){
		$oldSetting = array_filter($oldSetting);
		for($o = 0; $o < count($oldSetting); $o++){
			if(get_option($oldSetting[$o]) || get_option($oldSetting[$o])==''){
				update_option( 'wps'.$oldSetting[$o] , get_option($oldSetting[$o]) );
				delete_option( $oldSetting[$o] );
			}
		}
	}
}

?>