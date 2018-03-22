<?php

/**
 * Code related to the template.lib.php interface.
 *
 * @package Sucuri Security
 * @subpackage template.lib.php
 * @copyright Since 2010 Sucuri Inc.
 */

if (!defined('SUCURISCAN_INIT') || SUCURISCAN_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Read, parse and handle everything related with the templates.
 *
 * A web template system uses a template processor to combine web templates to
 * form finished web pages, possibly using some data source to customize the
 * pages or present a large amount of content on similar-looking pages. It is a
 * web publishing tool present in content management systems, web application
 * frameworks, and HTML editors.
 *
 * Web templates can be used like the template of a form letter to either
 * generate a large number of "static" (unchanging) web pages in advance, or to
 * produce "dynamic" web pages on demand.
 */
class SucuriScanTemplate extends SucuriScanRequest
{
    /**
     * Translates text using l10n and gettext.
     *
     * A translatable text can be inserted into any template file following this
     * format @@SUCURI.TextID@@ where "TextID" corresponds to the msgid in the
     * POT files. You can embed pseudo-variables into the translations like so:
     *
     * msgid "Copyright"
     * msgstr "Copyright %%SUCURI.Year%% Sucuri Inc"
     *
     * @see https://www.gnu.org/software/gettext/
     * @see https://codex.wordpress.org/I18n_for_WordPress_Developers
     * @see https://developer.wordpress.org/themes/functionality/internationalization/
     *
     * @param string $content Content of the template to be translated.
     * @return string New template content with the translated text.
     */
    private static function translateContent($content = '')
    {
        if (@preg_match_all('/@@SUCURI\.([0-9a-zA-Z\.\_]+)@@/', $content, $matches)) {
            foreach ($matches[0] as $key => $placeholder) {
                $translation = __($matches[1][$key], SUCURISCAN_TEXTDOMAIN);
                $content = str_replace($placeholder, $translation, $content);
            }
        }

        return $content;
    }

    /**
     * Replace all pseudo-variables from a string of characters.
     *
     * @see http://php.net/manual/en/function.gettype.php
     *
     * @param string $content The content of a template file which contains pseudo-variables.
     * @param array $params List of pseudo-variables that will be replaced in the template.
     * @return string The content of the template with the pseudo-variables replated.
     */
    private static function replacePseudoVars($content = '', $params = array())
    {
        $params = is_array($params) ? $params : array();

        $content = self::translateContent($content);

        foreach ($params as $keyname => $kvalue) {
            $tplkey = 'SUCURI.' . $keyname;
            $with_escape = '%%' . $tplkey . '%%';
            $wout_escape = '%%%' . $tplkey . '%%%';

            if (is_bool($kvalue)) {
                $kvalue = ($kvalue === true) ? 'True' : 'False';
            } elseif (!is_string($kvalue) && !is_numeric($kvalue)) {
                $kvalue = gettype($kvalue);
            }

            if (strpos($content, $wout_escape) !== false) {
                $content = str_replace($wout_escape, $kvalue, $content);
                continue;
            }

            if (strpos($content, $with_escape) !== false) {
                $kvalue = SucuriScan::escape($kvalue);
                $content = str_replace($with_escape, $kvalue, $content);
                continue;
            }
        }

        return $content;
    }

    /**
     * Gather and generate the information required globally by all the template files.
     *
     * @param string $target Scenario where the params are going to be replaced.
     * @param array $params Key-value array with variables shared with the template.
     * @return array Additional list of variables for the template files.
     */
    private static function sharedParams($target = null, $params = array())
    {
        $params = is_array($params) ? $params : array();

        // Base parameters, required to render all the pages.
        $params = self::linksAndNavbar($params);

        // Global parameters, used through out all the pages.
        $params['GenerateAPIKey.Modal'] = '';
        $params['GenerateAPIKey.Visibility'] = 'hidden';
        $params['PageNonce'] = wp_create_nonce('sucuriscan_page_nonce');
        $params['WordPressVersion'] = self::siteVersion();
        $params['PluginVersion'] = SUCURISCAN_VERSION;
        $params['CleanDomain'] = self::getDomain();
        $params['Year'] = date('Y');

        if (!array_key_exists('PageStyleClass', $params)) {
            $params['PageStyleClass'] = 'base';
        }

        if ($target === 'base'
            && current_user_can('manage_options')
            && !SucuriScanAPI::getPluginKey()
        ) {
            $params['GenerateAPIKey.Visibility'] = 'visible';
            $params['GenerateAPIKey.Modal'] = /* register-site */

            SucuriScanTemplate::getModal('register-site', array(
                'Title' => __('GenerateAPIKey', SUCURISCAN_TEXTDOMAIN),
                'Identifier' => 'register-site',
                'Visibility' => 'hidden',
            ));
        }

        // Get a list of admin users for the API key generation.
        if ($target === 'modal' && !SucuriScanAPI::getPluginKey()) {
            $admin_users = SucuriScan::getUsersForAPIKey();
            $params['AdminEmails'] = self::selectOptions($admin_users);
        }

        return $params;
    }

    /**
     * Return a string indicating the visibility of a HTML component.
     *
     * @param bool $visible Whether the condition executed returned a positive value or not.
     * @return string A string indicating the visibility of a HTML component.
     */
    public static function visibility($visible = false)
    {
        return ($visible === true ? 'visible' : 'hidden');
    }

    /**
     * Generate an URL pointing to the page indicated in the method and that must
     * be loaded through the administrator panel.
     *
     * @param string $page Short name of the page that will be generated.
     * @param bool $ajax True if the URL should point to the Ajax handler.
     * @return string Full string containing the link of the page.
     */
    public static function getUrl($page = '', $ajax = false)
    {
        $suffix = ($ajax === true) ? 'admin-ajax' : 'admin';
        $url_path = SucuriScan::adminURL($suffix . '.php?page=sucuriscan');

        if (!empty($page)) {
            $url_path .= '_' . strtolower($page);
        }

        /* convert URL to multisite format */
        $networkURL = str_replace(
            'wp-admin/network/admin-ajax.php',
            'wp-admin/admin-ajax.php',
            $url_path
        );

        return SucuriScan::isMultiSite() ? $networkURL : $url_path;
    }

    /**
     * Generate an URL pointing to the page indicated in the method and that must
     * be loaded through the Ajax handler of the administrator panel.
     *
     * @param string $page Short name of the page that will be generated.
     * @return string Full string containing the link of the page.
     */
    public static function getAjaxUrl($page = '')
    {
        return self::getUrl($page, true);
    }

    /**
     * Complement the list of pseudo-variables that will be used in the base
     * template files, this will also generate the navigation bar and detect which
     * items in it are selected by the current page.
     *
     * @param array $params Key-value array with pseudo-variables shared with the template.
     * @return array A complementary list of pseudo-variables for the template files.
     */
    private static function linksAndNavbar($params = array())
    {
        $pages = sucuriscan_pages();
        $params = is_array($params) ? $params : array();
        $sub_pages = is_array($pages) ? $pages : array();

        foreach ($sub_pages as $sub_page_func => $sub_page_title) {
            $func_parts = explode('_', $sub_page_func, 2);

            if (isset($func_parts[1])) {
                $unique_name = $func_parts[1];
                $pseudo_var = 'URL.' . ucwords($unique_name);
            } else {
                $unique_name = '';
                $pseudo_var = 'URL.Dashboard';
            }

            $params[$pseudo_var] = self::getUrl($unique_name);

            // Copy URL variable and create an Ajax handler.
            $pseudo_var_ajax = 'Ajax' . $pseudo_var;
            $params[$pseudo_var_ajax] = self::getAjaxUrl($unique_name);
        }

        return $params;
    }

    /**
     * Generate a HTML code using a template and replacing all the pseudo-variables
     * by the dynamic variables provided by the developer through one of the parameters
     * of the function.
     *
     * @param string $html The HTML content of a template file with its pseudo-variables parsed.
     * @param array $params Key-value array with pseudo-variables shared with the template.
     * @return string The formatted HTML content of the base template.
     */
    public static function getBaseTemplate($html = '', $params = array())
    {
        $params = is_array($params) ? $params : array();

        $params = self::sharedParams('base', $params);
        $params['PageContent'] = $html;

        return self::getTemplate('base', $params);
    }

    /**
     * Generate a HTML code using a template and replacing all the pseudo-variables
     * by the dynamic variables provided by the developer through one of the parameters
     * of the function.
     *
     * @param string $template Filename of the template that will be used to generate the page.
     * @param array $params Key-value array with pseudo-variables shared with the template.
     * @param string $type Template type; either page, section or snippet.
     * @return string Formatted HTML code after pseudo-variables replacement.
     */
    public static function getTemplate($template = '', $params = array(), $type = 'page')
    {
        $params = is_array($params) ? $params : array();

        $filenames = array(
            'page' => '%s/inc/tpl/%s.html.tpl',
            'section' => '%s/inc/tpl/%s.html.tpl',
            'snippet' => '%s/inc/tpl/%s.snippet.tpl',
        );

        if (!array_key_exists($type, $filenames)) {
            return (string) SucuriScan::throwException('Invalid template type');
        }

        $output = ''; /* initialize response */
        $_page = self::get('page', '_page');
        $params['SucuriURL'] = SUCURISCAN_URL;
        $trailing = $_page ? 'admin.php?page=' . $_page : '';
        $params['CurrentURL'] = SucuriScan::adminURL($trailing);

        /* load raw content from the specified template file */
        $fpath = sprintf($filenames[$type], SUCURISCAN_PLUGIN_PATH, $template);
        $output = SucuriScanFileInfo::fileContent($fpath);

        /* replace the global pseudo-variables in the section/snippets templates. */
        if ($template == 'base'
            && array_key_exists('PageContent', $params)
            && @preg_match('/%%SUCURI\.(.+)%%/', $params['PageContent'])
        ) {
            $params['PageContent'] = self::replacePseudoVars($params['PageContent'], $params);
        }

        $output = self::replacePseudoVars($output, $params);

        if ($template == 'base' || $type != 'page') {
            return $output;
        }

        return self::getBaseTemplate($output, $params);
    }

    /**
     * Generate a HTML code using a template and replacing all the pseudo-variables
     * by the dynamic variables provided by the developer through one of the parameters
     * of the function.
     *
     * @param string $template Filename of the template that will be used to generate the page.
     * @param array $params Key-value array with pseudo-variables shared with the template.
     * @return string The formatted HTML page after replace all the pseudo-variables.
     */
    public static function getSection($template = '', $params = array())
    {
        $params = self::sharedParams('section', $params);

        return self::getTemplate($template, $params, 'section');
    }

    /**
     * Generate a HTML code using a template and replacing all the pseudo-variables
     * by the dynamic variables provided by the developer through one of the parameters
     * of the function.
     *
     * @param string $template Filename of the template that will be used to generate the page.
     * @param array $params Key-value array with pseudo-variables shared with the template.
     * @return string The formatted HTML page after replace all the pseudo-variables.
     */
    public static function getModal($template = '', $params = array())
    {
        $required = array(
            'Title' => 'Lorem ipsum dolor sit amet',
            'Visibility' => 'visible',
            'Identifier' => 'foobar',
            'CssClass' => '',
            'Content' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicin'
            . 'g elit, sed do eiusmod tempor incididunt ut labore et dolore mag'
            . 'na aliqua. Ut enim ad minim veniam, quis nostrud exercitation ul'
            . 'lamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute'
            . ' irure dolor in reprehenderit in voluptate velit esse cillum dol'
            . 'ore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat '
            . 'non proident, sunt in culpa qui officia deserunt mollit anim id '
            . 'est laborum.</p>',
        );

        if (!empty($template) && $template !== 'none') {
            $params['Content'] = self::getSection($template);
        }

        foreach ($required as $param_name => $param_value) {
            if (!isset($params[$param_name])) {
                $params[$param_name] = $param_value;
            }
        }

        $params['Visibility'] = SUCURISCAN . '-' . $params['Visibility'];
        $params['Identifier'] = SUCURISCAN . '-' . $params['Identifier'] . '-modal';
        $params = self::sharedParams('modal', $params);

        return self::getTemplate('modalwindow', $params, 'section');
    }

    /**
     * Generate a HTML code using a template and replacing all the pseudo-variables
     * by the dynamic variables provided by the developer through one of the parameters
     * of the function.
     *
     * @param string $template Filename of the template that will be used to generate the page.
     * @param array $params Key-value array with pseudo-variables shared with the template.
     * @return string The formatted HTML page after replace all the pseudo-variables.
     */
    public static function getSnippet($template = '', $params = array())
    {
        $params = self::sharedParams('snippet', $params);

        return self::getTemplate($template, $params, 'snippet');
    }

    /**
     * Generate the HTML code necessary to render a list of options in a form.
     *
     * @param array $allowed_values List with keys and values allowed for the options.
     * @param string|int $selected_val Value of the option that will be selected by default.
     * @return string Option list for a select form field.
     */
    public static function selectOptions($allowed_values = array(), $selected_val = '')
    {
        $options = '';

        foreach ((array) $allowed_values as $option_name => $option_label) {
            $selected = '';

            if ($option_name === $selected_val) {
                $selected = "\x20selected=\"selected\"";
            }

            $options .= sprintf(
                "<option value=\"%s\"%s>%s</option>\n",
                SucuriScan::escape($option_name),
                $selected, /* do not escape HTML */
                SucuriScan::escape($option_label)
            );
        }

        return $options;
    }

    /**
     * Detect which number in a pagination was clicked.
     *
     * @return int Page number of the link clicked in a pagination.
     */
    public static function pageNumber()
    {
        $paged = self::get('paged', '[0-9]{1,5}');

        return ($paged ? intval($paged) : 1);
    }

    /**
     * Generate the HTML code to display a pagination.
     *
     * @param string $base_url Base URL for the links before the page number.
     * @param int $total_items Total quantity of items retrieved from a query.
     * @param int $max_per_page Maximum number of items that will be shown per page.
     * @return string HTML code for a pagination generated using the provided data.
     */
    public static function pagination($base_url = '', $total_items = 0, $max_per_page = 1)
    {
        // Calculate the number of links for the pagination.
        $html_links = '';
        $page_number = self::pageNumber();
        $max_pages = ceil($total_items / $max_per_page);
        $final_page = $max_pages;
        $start_page = 1;
        $extra_url = '';

        // Fix for inline anchor URLs.
        if (@preg_match('/^(.+)(#.+)$/', $base_url, $match)) {
            $base_url = $match[1];
            $extra_url = $match[2];
        }

        // Keep the number of pagination buttons at limit.
        if ($max_pages > SUCURISCAN_MAX_PAGINATION_BUTTONS) {
            $final_page = SUCURISCAN_MAX_PAGINATION_BUTTONS;
            $middle = $final_page / 2; /* middle point */

            if ($page_number > $middle) {
                $start_page = $page_number - ($middle - 1);
                $final_page = $page_number + $middle;
            }
        }

        // Generate the HTML links for the pagination.
        for ($j = $start_page; $j <= $final_page; $j++) {
            $link_class = 'sucuriscan-pagination-link';

            if ($page_number == $j) {
                $link_class .= "\x20sucuriscan-pagination-active";
            }

            $html_links .= sprintf(
                '<li><a href="%s&paged=%d%s" class="%s" data-page="%d">%s</a></li>',
                $base_url,
                $j,
                $extra_url,
                $link_class,
                $j,
                $j
            );
        }

        return $html_links;
    }
}
