<?php

/**
 * Code related to the globals.php interface.
 *
 * @package Sucuri Security
 * @subpackage globals.php
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
 * Plugin's global variables.
 *
 * These variables will be defined globally to allow the inclusion in multiple
 * methods and classes defined in the libraries loaded by this plugin. The
 * conditional will act as a container helping in the readability of the code
 * considering the total number of lines that this file will have.
 */
if (defined('SUCURISCAN')) {
    /**
     * Define the prefix for some actions and filters that rely in the differen-
     * tiation of the type of site where the extension is being used. There are
     * a few differences between a single site installation that must be
     * correctly defined when the extension is in a different environment, for
     * example, in a multisite installation.
     *
     * @var string
     */
    $sucuriscan_action_prefix = SucuriScan::isMultiSite() ? 'network_' : '';

    /**
     * Settings options.
     *
     * The following global variables are mostly associative arrays where the
     * key is linked to an option that will be stored in the database, and their
     * correspondent values are the description of the option. These variables
     * will be used in the settings page to offer the user a way to configure
     * the behaviour of the plugin.
     *
     * @var string
     */
    $sucuriscan_date_format = get_option('date_format');
    $sucuriscan_time_format = get_option('time_format');

    /**
     * Remove the WordPress generator meta-tag from the source code.
     */
    remove_action('wp_head', 'wp_generator');

    /**
     * Run a specific method defined in the plugin's code to locate every
     * directory and file, collect their checksum and file size, and send this
     * information to the Sucuri API service where a security and integrity scan
     * will be performed against the hashes provided and the official versions.
     */
    add_action('sucuriscan_scheduled_scan', 'SucuriScan::runScheduledTask');

    /**
     * Initialize the execute of the main plugin's functions.
     *
     * This will load the menu options in the WordPress administrator panel, and
     * execute the bootstrap method of the plugin.
     */
    add_action('init', 'SucuriScanInterface::initialize', 1);
    add_action('init', 'SucuriScanBlockedUsers::blockUserLogin', 1);
    add_action('admin_enqueue_scripts', 'SucuriScanInterface::enqueueScripts', 1);

    if (SucuriScan::runAdminInit()) {
        add_action('admin_init', 'SucuriScanInterface::handleOldPlugins');
        add_action('admin_init', 'SucuriScanInterface::createStorageFolder');
        add_action('admin_init', 'SucuriScanInterface::noticeAfterUpdate');
    }

    /**
     * List an associative array with the sub-pages of this plugin.
     *
     * @return array List of sub-pages of this plugin.
     */
    function sucuriscan_pages()
    {
        return array(
            'sucuriscan' => __('Dashboard', SUCURISCAN_TEXTDOMAIN),
            'sucuriscan_firewall' => __('Firewall', SUCURISCAN_TEXTDOMAIN),
            'sucuriscan_lastlogins' => __('LastLogins', SUCURISCAN_TEXTDOMAIN),
            'sucuriscan_settings' => __('Settings', SUCURISCAN_TEXTDOMAIN),
        );
    }

    if (function_exists('load_plugin_textdomain')) {
        /**
         * Loads the language files for the entire interface.
         *
         * Internationalization is the process of developing your plugin so it
         * can be translated into other languages. Localization describes the
         * process of translating an internationalized plugin. Internationaliza-
         * tion is often abbreviated as i18n (there are 18 letters between the
         * i and the n) and localization is abbreviated as l10n (there are 10
         * letters between the l and the n).
         *
         * @see https://codex.wordpress.org/I18n_for_WordPress_Developers
         */
        function sucuriscan_load_plugin_textdomain()
        {
            global $locale;

            $pofile = sprintf(
                '%s/languages/%s-%s.po',
                SUCURISCAN_PLUGIN_PATH,
                SUCURISCAN_TEXTDOMAIN,
                $locale
            );
            $mofile = sprintf(
                '%s/languages/%s-%s.mo',
                SUCURISCAN_PLUGIN_PATH,
                SUCURISCAN_TEXTDOMAIN,
                $locale
            );

            /* attempt to import the English POT file into LOCALE */
            if (!file_exists($pofile) || !file_exists($mofile)) {
                $en_pofile = sprintf(
                    '%s/languages/%s-en_US.po',
                    SUCURISCAN_PLUGIN_PATH,
                    SUCURISCAN_TEXTDOMAIN
                );
                $en_mofile = sprintf(
                    '%s/languages/%s-en_US.mo',
                    SUCURISCAN_PLUGIN_PATH,
                    SUCURISCAN_TEXTDOMAIN
                );

                @copy($en_pofile, $pofile);
                @copy($en_mofile, $mofile);
            }

            /* fallback to English on language import failure */
            if (!file_exists($pofile) || !file_exists($mofile)) {
                $locale = 'en_US';
                setlocale(LC_ALL, 'en_US');
            }

            load_plugin_textdomain(
                SUCURISCAN_TEXTDOMAIN,
                false, /* deprecated */
                SUCURISCAN_PLUGIN_FOLDER . '/languages/'
            );
        }

        add_action('init', 'sucuriscan_load_plugin_textdomain');
    }

    if (function_exists('add_action')) {
        /**
         * Display extension menu and submenu items in the correct interface.
         * For single site installations the menu items can be displayed
         * normally as always but for multisite installations the menu items
         * must be available only in the network panel and hidden in the
         * administration panel of the subsites.
         *
         * @codeCoverageIgnore
         */
        function sucuriscan_add_menu_page()
        {
            $pages = sucuriscan_pages();

            add_menu_page(
                'Sucuri Security',
                'Sucuri Security',
                'manage_options',
                'sucuriscan',
                'sucuriscan_page',
                SUCURISCAN_URL . '/inc/images/menuicon.png'
            );

            foreach ($pages as $sub_page_func => $sub_page_title) {
                add_submenu_page(
                    'sucuriscan',
                    $sub_page_title,
                    $sub_page_title,
                    'manage_options',
                    $sub_page_func,
                    $sub_page_func . '_page'
                );
            }
        }

        /* Attach HTTP request handlers for the internal plugin pages */
        add_action($sucuriscan_action_prefix . 'admin_menu', 'sucuriscan_add_menu_page');

        /* Attach HTTP request handlers for the AJAX requests */
        add_action('wp_ajax_sucuriscan_ajax', 'sucuriscan_ajax');
    }

    /**
     * Function call interceptors.
     *
     * Define the names for the hooks that will intercept specific method calls in
     * the admin interface and parts of the external site, an event report will be
     * sent to the API service and an email notification to the administrator of the
     * site.
     *
     * @see Class SucuriScanHook
     */
    if (class_exists('SucuriScanHook')) {
        add_action('activated_plugin', 'SucuriScanHook::hookPluginActivate', 50, 2);
        add_action('add_attachment', 'SucuriScanHook::hookAttachmentAdd', 50, 5);
        add_action('add_link', 'SucuriScanHook::hookLinkAdd', 50, 5);
        add_action('before_delete_post', 'SucuriScanHook::hookPostBeforeDelete', 50, 5);
        add_action('create_category', 'SucuriScanHook::hookCategoryCreate', 50, 5);
        add_action('deactivated_plugin', 'SucuriScanHook::hookPluginDeactivate', 50, 2);
        add_action('delete_post', 'SucuriScanHook::hookPostDelete', 50, 5);
        add_action('delete_user', 'SucuriScanHook::hookUserDelete', 50, 5);
        add_action('edit_link', 'SucuriScanHook::hookLinkEdit', 50, 5);
        add_action('login_form_resetpass', 'SucuriScanHook::hookLoginFormResetpass', 50, 5);
        add_action('publish_page', 'SucuriScanHook::hookPublishPage', 50, 5);
        add_action('publish_phone', 'SucuriScanHook::hookPublishPhone', 50, 5);
        add_action('publish_post', 'SucuriScanHook::hookPublishPost', 50, 5);
        add_action('retrieve_password', 'SucuriScanHook::hookRetrievePassword', 50, 5);
        add_action('switch_theme', 'SucuriScanHook::hookThemeSwitch', 50, 5);
        add_action('transition_post_status', 'SucuriScanHook::hookPostStatus', 50, 3);
        add_action('user_register', 'SucuriScanHook::hookUserRegister', 50, 5);
        add_action('wp_login', 'SucuriScanHook::hookLoginSuccess', 50, 5);
        add_action('wp_login_failed', 'SucuriScanHook::hookLoginFailure', 50, 5);
        add_action('wp_trash_post', 'SucuriScanHook::hookPostTrash', 50, 5);
        add_action('xmlrpc_publish_post', 'SucuriScanHook::hookPublishPostXMLRPC', 50, 5);

        if (SucuriScan::runAdminInit()) {
            add_action('admin_init', 'SucuriScanHook::hookCoreUpdate');
            add_action('admin_init', 'SucuriScanHook::hookOptionsManagement');
            add_action('admin_init', 'SucuriScanHook::hookPluginDelete');
            add_action('admin_init', 'SucuriScanHook::hookPluginEditor');
            add_action('admin_init', 'SucuriScanHook::hookPluginInstall');
            add_action('admin_init', 'SucuriScanHook::hookPluginUpdate');
            add_action('admin_init', 'SucuriScanHook::hookThemeDelete');
            add_action('admin_init', 'SucuriScanHook::hookThemeEditor');
            add_action('admin_init', 'SucuriScanHook::hookThemeInstall');
            add_action('admin_init', 'SucuriScanHook::hookThemeUpdate');
            add_action('admin_init', 'SucuriScanHook::hookWidgetAdd');
            add_action('admin_init', 'SucuriScanHook::hookWidgetDelete');
        }
    }

    /**
     * Clear the firewall cache if necessary.
     *
     * Every time a page or post is modified and saved into the database the
     * plugin will send a HTTP request to the firewall API service and except
     * that, if the API key is valid, the cache is reset. Notice that the cache
     * of certain files is going to stay as it is due to the configuration on the
     * edge of the servers.
     */
    add_action('save_post', 'SucuriScanFirewall::clearCacheHook');
}
