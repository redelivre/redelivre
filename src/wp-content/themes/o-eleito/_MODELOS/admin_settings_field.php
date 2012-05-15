 <?php 
 
 /*
  * 
  * 
  * VER A PAGINA wp-admin/options-reading.php para entender a estrutura de uma pagina de opções
  * 
  * 
  * 
 */
 
 
 // ------------------------------------------------------------------
 // Add all your sections, fields and settings during admin_init
 // ------------------------------------------------------------------
 //
 
 function SLUG_settings_api_init() {
 	
    $settings_page = 'reading'; // general, writing, reading, discussion, media, privacy, permalink
    $option_name = 'example'; //mudar também em SLUG_setting_callback_function()
    $option_label = 'example';
    $section_id = 'default';
    
 	// Add the field with the names and function to use for our new settings, put it in our new section
 	add_settings_field($option_name, $option_label, 'SLUG_setting_callback_function', $settings_page, $section_id);
 	
 	// Register our setting so that $_POST handling is done for us and our callback function just has to echo the <input>
 	register_setting($settings_page, $option_name);
 }
 
 add_action('admin_init', 'SLUG_settings_api_init');
 
  
 // ------------------------------------------------------------------
 // Callback function for our example setting
 // ------------------------------------------------------------------
 //
 // creates a checkbox true/false option. Other types are surely possible
 //
 
 function SLUG_setting_callback_function() {
 	$checked = "";
 	
    $option_name = 'example';
    
 	// Mark our checkbox as checked if the setting is already true
 	if (get_option($option_name)) 
 		$checked = " checked='checked' ";
 
 	echo "<input {$checked} name='{$option_name}' id='{$option_name}' type='checkbox' value='1' class='code' /> Explanation text";
 } 
?> 
