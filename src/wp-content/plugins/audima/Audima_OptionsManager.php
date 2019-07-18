<?php

/*
    "WordPress Plugin Template" Copyright (C) 2017 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

class Audima_OptionsManager
{

    public function getOptionNamePrefix()
    {
        return get_class($this) . '_';
    }


    /**
     * Define your options meta data here as an array, where each element in the array
     *
     * @return array of key=>display-name and/or key=>array(display-name, choice1, choice2, ...)
     * key: an option name for the key (this name will be given a prefix when stored in
     * the database to ensure it does not conflict with other plugin options)
     * value: can be one of two things:
     *   (1) string display name for displaying the name of the option to the user on a web page
     *   (2) array where the first element is a display name (as above) and the rest of
     *       the elements are choices of values that the user can select
     * e.g.
     * array(
     *   'item' => 'Item:',             // key => display-name
     *   'rating' => array(             // key => array ( display-name, choice1, choice2, ...)
     *       'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor',
     *     'Subscriber'),
     *       'Rating:', 'Excellent', 'Good', 'Fair', 'Poor')
     */
    public function getOptionMetaData()
    {
        return array();
    }

    /**
     * @return array of string name of options
     */
    public function getOptionNames()
    {
        return array_keys($this->getOptionMetaData());
    }

    /**
     * Override this method to initialize options to default values and save to the database with add_option
     *
     * @return void
     */
    protected function initOptions()
    {
    }

    /**
     * Cleanup: remove all options from the DB
     *
     * @return void
     */
    protected function deleteSavedOptions()
    {
        $optionMetaData = $this->getOptionMetaData();
        if (is_array($optionMetaData)) {
            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                $prefixedOptionName = $this->prefix($aOptionKey); // how it is stored in DB
                delete_option($prefixedOptionName);
            }
        }
    }

    /**
     * @return string display name of the plugin to show as a name/title in HTML.
     * Just returns the class name. Override this method to return something more readable
     */
    public function getPluginDisplayName()
    {
        return get_class($this);
    }

    /**
     * Get the prefixed version input $name suitable for storing in WP options
     * Idempotent: if $optionName is already prefixed, it is not prefixed again, it is returned without change
     *
     * @param  $name string option name to prefix. Defined in settings.php and set as keys of $this->optionMetaData
     * @return string
     */
    public function prefix($name)
    {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) { // 0 but not false
            return $name; // already prefixed
        }

        return $optionNamePrefix . $name;
    }

    /**
     * Remove the prefix from the input $name.
     * Idempotent: If no prefix found, just returns what was input.
     *
     * @param  $name string
     * @return string $optionName without the prefix.
     */
    public function &unPrefix($name)
    {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) {
            return substr($name, strlen($optionNamePrefix));
        }

        return $name;
    }

    /**
     * A wrapper function delegating to WP get_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     *
     * @param $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param $default string default value to return if the option is not set
     * @return string the value from delegated call to get_option(), or optional default value
     * if option is not set.
     */
    public function getOption($optionName, $default = null)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        $retVal = get_option($prefixedOptionName);
        if (!$retVal && $default) {
            $retVal = $default;
        }

        return $retVal;
    }

    /**
     * A wrapper function delegating to WP delete_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     *
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @return bool from delegated call to delete_option()
     */
    public function deleteOption($optionName)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB

        return delete_option($prefixedOptionName);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     *
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function addOption($optionName, $value)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB

        return add_option($prefixedOptionName, $value);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     *
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function updateOption($optionName, $value)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB

        return update_option($prefixedOptionName, $value);
    }

    /**
     * A Role Option is an option defined in getOptionMetaData() as a choice of WP standard roles, e.g.
     * 'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor',
     * 'Subscriber') The idea is use an option to indicate what role level a user must minimally have in order to do
     * some operation. So if a Role Option 'CanDoOperationX' is set to 'Editor' then users which role 'Editor' or above
     * should be able to do Operation X. Also see: canUserDoRoleOption()
     *
     * @param  $optionName
     * @return string role name
     */
    public function getRoleOption($optionName)
    {
        $roleAllowed = $this->getOption($optionName);
        if (!$roleAllowed || $roleAllowed == '') {
            $roleAllowed = 'Administrator';
        }

        return $roleAllowed;
    }

    /**
     * Given a WP role name, return a WP capability which only that role and roles above it have
     * http://codex.wordpress.org/Roles_and_Capabilities
     *
     * @param  $roleName
     * @return string a WP capability or '' if unknown input role
     */
    protected function roleToCapability($roleName)
    {
        switch ($roleName) {
            case 'Super Admin':
                return 'manage_options';
            case 'Administrator':
                return 'manage_options';
            case 'Editor':
                return 'publish_pages';
            case 'Author':
                return 'publish_posts';
            case 'Contributor':
                return 'edit_posts';
            case 'Subscriber':
                return 'read';
            case 'Anyone':
                return 'read';
        }

        return '';
    }

    /**
     * @param $roleName string a standard WP role name like 'Administrator'
     * @return bool
     */
    public function isUserRoleEqualOrBetterThan($roleName)
    {
        if ('Anyone' == $roleName) {
            return true;
        }
        $capability = $this->roleToCapability($roleName);

        return current_user_can($capability);
    }

    /**
     * @param  $optionName string name of a Role option (see comments in getRoleOption())
     * @return bool indicates if the user has adequate permissions
     */
    public function canUserDoRoleOption($optionName)
    {
        $roleAllowed = $this->getRoleOption($optionName);
        if ('Anyone' == $roleAllowed) {
            return true;
        }

        return $this->isUserRoleEqualOrBetterThan($roleAllowed);
    }

    /**
     * see: http://codex.wordpress.org/Creating_Options_Pages
     *
     * @return void
     */
    public function createSettingsMenu()
    {
        $pluginName = $this->getPluginDisplayName();
        //create new top-level menu
        add_menu_page($pluginName . ' Plugin Settings',
            $pluginName,
            'administrator',
            get_class($this),
            array(&$this, 'settingsPage')
        /*,plugins_url('/images/icon.png', __FILE__)*/); // if you call 'plugins_url; be sure to "require_once" it

        //call register settings function
        add_action('admin_init', array(&$this, 'registerSettings'));
    }

    public function registerSettings()
    {
        $settingsGroup = get_class($this) . '-settings-group';
        $optionMetaData = $this->getOptionMetaData();
        foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
            register_setting($settingsGroup, $aOptionMeta);
        }
    }

    public function validateData($validation, $data){

        switch ($validation){
            case 'sanitize_email':
                $r = sanitize_email($data);
                break;
            case 'sanitize_file_name':
                $r = sanitize_file_name($data);
                break;
            case 'sanitize_html_class':
                $r = sanitize_html_class($data);
                break;
            case 'sanitize_key':
                $r = sanitize_key($data);
                break;
            case 'sanitize_meta':
                $r = sanitize_meta($data);
                break;
            case 'sanitize_mime_type':
                $r = sanitize_mime_type($data);
                break;
            case 'sanitize_option':
                $r = sanitize_option($data);
                break;
            case 'sanitize_sql_orderby':
                $r = sanitize_sql_orderby($data);
                break;
            case 'sanitize_text_field':
                $r = sanitize_text_field($data);
                break;
            case 'sanitize_textarea_field':
                $r = sanitize_textarea_field($data);
                break;
            case 'sanitize_title':
                $r = sanitize_title($data);
                break;
            case 'sanitize_title_for_query':
                $r = sanitize_title_for_query($data);
                break;
            case 'sanitize_title_with_dashes':
                $r = sanitize_title_with_dashes($data);
                break;
            case 'sanitize_user':
                $r = sanitize_user($data);
                break;
        }

        return $r;
    }

    /**
     * Creates HTML for the Administration page to set options for this plugin.
     * Override this method to create a customized page.
     *
     * @return void
     */
    public function settingsPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'audima'));
        }

        $optionMetaData = $this->getOptionMetaData();

        // Save Posted Options
        if ($optionMetaData != null) {
            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                $post = $_POST[$aOptionKey];
                $postCheck = null;

                if(isset($post)){
                    if(!is_array($post)){
                        $singlePost = sanitize_text_field($post);
                        $postCheck = !empty($singlePost)? $singlePost : "";
                    }else{
                        $arrayPost=[];
                        foreach ($post as $p){
                            $s = sanitize_text_field($p);
                            if(!empty($s)) array_push($arrayPost,$s);
                        }
                        if(sizeof($post) == sizeof($arrayPost))
                            $postCheck = $arrayPost;
                    }
                }
                if ($postCheck != null) {
                    if ($aOptionKey == 'clearCache' && $postCheck == 'true') {
                        $message = ($this->clearCache())? "Cache cleanned." : "Error on clear cache";
                        echo '<script>alert("'.__($message, 'audima').'")</script>';
                    } else {
                        $selectedOption = is_array($postCheck)? json_encode($postCheck) : $postCheck;
                        $this->updateOption($aOptionKey, $selectedOption);
                    }
                }
            }
        }

        // HTML for the page
        $settingsGroup = get_class($this) . '-settings-group';
        ?>
        <style>
            .empty {
                border: 1px solid red !important; background-color: #ffcccc !important;
            }
        </style>
        <div class="wrap">
            <h2><?php _e('System Settings', 'audima'); ?></h2>
            <table cellspacing="1" cellpadding="2">
                <tbody>
                <tr>
                    <td><?php _e('System', 'audima'); ?></td>
                    <td><?php echo php_uname(); ?></td>
                </tr>
                <tr>
                    <td><?php _e('PHP Version', 'audima'); ?></td>
                    <td><?php echo phpversion(); ?>
                        <?php
                        if (version_compare('5.3', phpversion()) > 0) {
                            echo '&nbsp;&nbsp;&nbsp;<span style="background-color: #ffcc00;">';
                            _e('(WARNING: This plugin may not work properly with versions earlier than PHP 5.3)',
                                'audima');
                            echo '</span>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo 'CURL' ?></td>
                    <td>
                        <?php
                          if (!curl_version()) :
                                echo '<p>';
                                echo '<strong>Error:</strong> The Audima plugin requires <a target="_blank" href="http://php.net/manual/en/book.curl.php">cURL PHP extension</a> and access to https://audio.audima.co';
                                echo  '</p>';
                                echo '</div>';
                          else :
                               echo 'Curl installed';
                          endif;
                          ?>
                    </td>
                </tr>
                <tr>
                    <td><?php _e('MySQL Version', 'audima'); ?></td>
                    <td><?php echo $this->getMySqlVersion() ?>
                        <?php
                        echo '&nbsp;&nbsp;&nbsp;<span style="background-color: #ffcc00;">';
                        if (version_compare('5.0', $this->getMySqlVersion()) > 0) {
                            _e('(WARNING: This plugin may not work properly with versions earlier than MySQL 5.0)',
                                'audima');
                        }
                        echo '</span>';
                        ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <h2><?php echo $this->getPluginDisplayName();
                echo ' ';
                _e('Settings', 'audima'); ?></h2>

            <form method="post" action="<?php include_once('audima_init.php'); Upgrade();?>">
                <?php settings_fields($settingsGroup); ?>
                <style type="text/css">
                    table.plugin-options-table {
                        width: 100%;
                        padding: 0;
                    }

                    table.plugin-options-table tr:nth-child(even) {
                        background: #f9f9f9
                    }

                    table.plugin-options-table tr:nth-child(odd) {
                        background: #FFF
                    }

                    table.plugin-options-table tr:first-child {
                        width: 35%;
                    }

                    table.plugin-options-table td {
                        vertical-align: middle;
                    }

                    table.plugin-options-table td + td {
                        width: auto
                    }

                    table.plugin-options-table td > p {
                        margin-top: 0;
                        margin-bottom: 0;
                    }
                </style>
                <table class="plugin-options-table">
                    <tbody>
                    <?php
                    if ($optionMetaData != null) {
                        foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                            $displayText = is_array($aOptionMeta) ? $aOptionMeta[0] : $aOptionMeta;
                            ?>
                            <tr valign="top">
                                <th scope="row"><p><label
                                                for="<?php echo $aOptionKey ?>"><?php echo $displayText ?></label></p>
                                </th>
                                <td>
                                    <?php $this->createFormControl($aOptionKey, $aOptionMeta,
                                        $this->getOption($aOptionKey)); ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>

                <?php

                $idBlog = $this->getOption('IdBlog');
                $plan = $this->getOption('Plan');
                ?>
                <p class="submit">
                    <input type="submit" class="button-primary"
                           value="<?php _e('Save Changes', 'audima') ?>"/>
                </p>
            </form>
        </div>
        <script>
            function checkRequired(me, unexpected) {
                if (jQuery.trim(me.val()) == unexpected) {
                    me.addClass('empty');
                } else {
                    me.removeClass('empty');
                }
            }

            jQuery('#clearCache').change(function(event){
                if(jQuery(this).val() == 'true') {
                    if(!confirm("<?php echo __("Are you sure you want to delete the cache?", 'audima') ?>")) {
                        jQuery(this).val('false')
                    }
                }
            });

            jQuery('#IdBlog').change(function(event) {
                checkRequired(jQuery(this), '');
            });
            jQuery('#TtsVoice').change(function(event) {
                checkRequired(jQuery(this), '');
            });
            jQuery('#AutoGenerateTts').change(function(event) {
                checkRequired(jQuery(this), 'false');
            });
            jQuery('#Agree').change(function(event) {
                checkRequired(jQuery(this), 'false');
            });

            checkRequired(jQuery('#IdBlog'), '');
            checkRequired(jQuery('#TtsVoice'), '');
            checkRequired(jQuery('#AutoGenerateTts'), 'false');
            checkRequired(jQuery('#Agree'), 'false');
        </script>
        <?php

    }

    /**
     * Helper-function outputs the correct form element (input tag, select tag) for the given item
     *
     * @param  $aOptionKey string name of the option (un-prefixed)
     * @param  $aOptionMeta mixed meta-data for $aOptionKey (either a string display-name or an array(display-name,
     *     option1, option2, ...)
     * @param  $savedOptionValue string current value for $aOptionKey
     * @return void
     */
    protected function createFormControl($aOptionKey, $aOptionMeta, $savedOptionValue)
    {
    	$aSavedOptionValue = json_decode($savedOptionValue);
    	if(is_null($aSavedOptionValue)) $aSavedOptionValue = array();
        if (is_array($aOptionMeta) && count($aOptionMeta) >= 2) { // Drop-down list
            if ($aOptionKey !== "Plans") :

            if(in_array(array('multiple'), $aOptionMeta)){
                $choices = array_slice( $aOptionMeta, 1 );
                array_pop($choices)
                ?>
                <script>
                    function select<?php echo $aOptionKey ?>() {
                        jQuery("#<?php echo $aOptionKey ?> > option").prop("selected","selected");
                        jQuery("#<?php echo $aOptionKey ?>").trigger("change");
                    }
                </script>
                <p><select name="<?php echo $aOptionKey ?>[]" id="<?php echo $aOptionKey ?>" multiple style="width: 75%">
                        <?php
                        foreach ( $choices as $aChoice ) {
                        	$selected = ( in_array($aChoice, $aSavedOptionValue) ) ? 'selected' : '';
                            ?>
                            <option
                                value="<?php echo $aChoice ?>" <?php echo $selected ?>><?php echo $this->getOptionValueI18nString( $aChoice ) ?></option>
                            <?php
                        }
                        ?>
                    </select><br/>
                    <input type="button" onclick="select<?php echo $aOptionKey ?>()" value="Select All" />
                </p>
                <?php
            }else {
                $choices = array_slice( $aOptionMeta, 1 );
                ?>
                <p><select name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>">
                        <?php
                        foreach ( $choices as $aChoice ) {
                            $selected = ( $aChoice == $savedOptionValue ) ? 'selected' : '';
                            ?>
                            <option
                                value="<?php echo $aChoice ?>" <?php echo $selected ?>><?php echo $this->getOptionValueI18nString( $aChoice ) ?></option>
                            <?php
                        }
                        ?>
                    </select></p>
                <?php
            }
           endif;
        } else { // Simple input field

            if ($aOptionKey !== "Plans") :
            ?>
            <p><input type="text" name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>"
                      value="<?php echo esc_attr($savedOptionValue) ?>" size="50"/> </p>

         <?php

            endif;
        }
    }

    /**
     * Override this method and follow its format.
     * The purpose of this method is to provide i18n display strings for the values of options.
     * For example, you may create a options with values 'true' or 'false'.
     * In the options page, this will show as a drop down list with these choices.
     * But when the the language is not English, you would like to display different strings
     * for 'true' and 'false' while still keeping the value of that option that is actually saved in
     * the DB as 'true' or 'false'.
     * To do this, follow the convention of defining option values in getOptionMetaData() as canonical names
     * (what you want them to literally be, like 'true') and then add each one to the switch statement in this
     * function, returning the "__()" i18n name of that string.
     *
     * @param  $optionValue string
     * @return string __($optionValue) if it is listed in this method, otherwise just returns $optionValue
     */
    protected function getOptionValueI18nString($optionValue)
    {
        switch ($optionValue) {
            case 'true':
                return __('true', 'audima');
            case 'false':
                return __('false', 'audima');

            case 'Administrator':
                return __('Administrator', 'audima');
            case 'Editor':
                return __('Editor', 'audima');
            case 'Author':
                return __('Author', 'audima');
            case 'Contributor':
                return __('Contributor', 'audima');
            case 'Subscriber':
                return __('Subscriber', 'audima');
            case 'Anyone':
                return __('Anyone', 'audima');
        }

        return $optionValue;
    }

    /**
     * Query MySQL DB for its version
     *
     * @return string|false
     */
    protected function getMySqlVersion()
    {
        global $wpdb;
        $rows = $wpdb->get_results('select version() as mysqlversion');
        if (!empty($rows)) {
            return $rows[0]->mysqlversion;
        }

        return false;
    }

    /**
     * If you want to generate an email address like "no-reply@your-site.com" then
     * you can use this to get the domain name part.
     * E.g.  'no-reply@' . $this->getEmailDomain();
     * This code was stolen from the wp_mail function, where it generates a default
     * from "wordpress@your-site.com"
     *
     * @return string domain name
     */
    public function getEmailDomain()
    {
        // Get the site domain and get rid of www.
        $sitename = strtolower($_SERVER['SERVER_NAME']);
        if (substr($sitename, 0, 4) == 'www.') {
            $sitename = substr($sitename, 4);
        }

        return $sitename;
    }
}

